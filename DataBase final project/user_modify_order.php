<?php
    session_start();
    var_dump($_SESSION) ; 
    var_dump($_POST) ;
    //Check order status
    try{
        $error = '' ; 
        $mysqli = new mysqli("localhost","root","","database_hw2");
        $sql = 'SELECT state FROM orders WHERE OID = '.$_POST['OIDcancel'];
        $stmt = $mysqli->prepare($sql) ;
        if(!$stmt){
          echo "Prepare failed: (". $mysqli->errno.") ".$mysqli->error."<br>";
        }
        $stmt->execute() ; 
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
          while($row = $result->fetch_assoc()) {
            if($row['state'] == 'Finished'){
                $error = "店家已完成訂單,無法取消" ;
            }
            else if($row['state'] == 'Canceled'){
                $error = "您已經取消過此訂單" ;
            }
            else if($row['state'] == 'Not Finish'){
                $error = '' ; 
            }
          }
        }
        throw new Exception($error);
    }
    catch (Exception $e){
        $err = $e->getMessage() ; 
        if($err == ''){
            //fetch order info
            $detail  ;
            $mysqli = new mysqli("localhost","root","","database_hw2");
            $sql = 'SELECT detail FROM orders WHERE OID = '.intval($_POST['OIDcancel']);
            $stmt = $mysqli->prepare($sql) ;
            if(!$stmt){
            echo "Prepare failed: (". $mysqli->errno.") ".$mysqli->error."<br>";
            }
            $stmt->execute() ; 
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $detail = $row['detail'];
                }
            }

            //parsing 
            $detailarr = str_split($detail);; 

            $count = 0 ; 
            $substr = '' ;
            $ttl ; 
            $modify = array();

            for($i = 0 ; $i < strlen($detail) ; $i++){
                if(($detailarr[$i] == '#' && $count == 0) || ($detailarr[$i] == '#' && $count == 1)){
                    $substr = '' ; 
                    $count += 1 ; 
                }
                else if($detailarr[$i] == '#' && $count == 2){
                    $ttl = $substr ; 
                    $substr = '' ;
                    $count += 1 ;
                }
                else if($detailarr[$i] == '#' && $count > 2){
                    $split = strpos($substr, '/') ;
                    $fid = substr($substr , 0 , $split) ; 
                    $fnum = substr($substr , $split + 1) ;
                    $modify += array($fid => $fnum);
                    $substr = '' ;
                    $count += 1 ;
                }
                else{
                    $substr = $substr.$detailarr[$i] ; 
                }
            }

            //update food number
            $sql = 'UPDATE food SET quantity = ' ;
            foreach ($modify as $key => $value){
                $getnum = 'SELECT quantity FROM food where id = "'.$key.'"';
                $stmt = $mysqli->prepare($getnum) ;
                if(!$stmt){
                    echo "Prepare failed: (". $mysqli->errno.") ".$mysqli->error."<br>";
                }
                $stmt->execute() ;
                $result = $stmt->get_result();
                if($result->num_rows > 0){
                    while($row = $result->fetch_assoc()) {
                        $getnum = $row['quantity']; 
                    }
                    $stmt->close() ;
                    // echo $getnum ;
                    
                    $correct_quantity = $getnum+$value ; 
                    $sqli = $sql.$correct_quantity.' WHERE id = "'.$key.'"'; 
                    // echo $sqli ; 
                    $stmt = $mysqli->prepare($sqli) ;
                    if(!$stmt){
                        echo "Prepare failed: (". $mysqli->errno.") ".$mysqli->error."<br>";
                    }
                    $stmt->execute() ;
                }
                $stmt->close() ;
            }

            //update buyer wallet
            $sql = 'UPDATE users SET wallet = '.$_SESSION['wallet'].'+'.$ttl.' WHERE account = "'.$_SESSION['account'].'"';
            $stmt = $mysqli->prepare($sql) ;
            if(!$stmt){
                echo "Prepare failed: (". $mysqli->errno.") ".$mysqli->error."<br>";
            }
            $stmt->execute() ;
            $stmt->close() ;

            //update seller wallet
            $findshopkeeper = 'SELECT boss from shops where name = "'.$_POST['shop'].'"';
            $stmt = $mysqli->prepare($findshopkeeper) ;
            if(!$stmt){
                echo "Prepare failed: (". $mysqli->errno.") ".$mysqli->error."<br>";
            }
            $stmt->execute() ;
            $result = $stmt->get_result();
            $boss ="" ; 
            if($result->num_rows > 0){
                while($row = $result->fetch_assoc()) {
                    $boss = $row['boss']; 
                }
                $earn = 'UPDATE users SET wallet = wallet - '.$_POST['ttl'].' WHERE account = "'.$boss.'"';
                $stmt = $mysqli->prepare($earn) ;
                if(!$stmt){
                    echo "Prepare failed: (". $mysqli->errno.") ".$mysqli->error."<br>";
                }
                $stmt->execute() ;
            }
            $stmt->close() ;

            //update order status
            $sql = 'UPDATE orders SET state = "Canceled" WHERE OID = '.intval($_POST['OIDcancel']);
            $stmt = $mysqli->prepare($sql) ;
            if(!$stmt){
                echo "Prepare failed: (". $mysqli->errno.") ".$mysqli->error."<br>";
            }
            $stmt->execute() ;
            $stmt->close() ;
            $err = '已取消此訂單並退款'.$ttl.'元' ;

            //create two new transaction record + update order's finish time
            $dbservername = 'localhost';
            $dbname = 'database_hw2';
            $dbusername = 'root';
            $dbpassword = '';

            $conn = new PDO(
                "mysql:host=$dbservername;dbname=$dbname",
                $dbusername,
                $dbpassword
            );
            
            $conn->setAttribute(
                PDO::ATTR_ERRMODE,
                PDO::ERRMODE_EXCEPTION
            );
            date_default_timezone_set('Asia/Taipei');
            $current_time = date("Y-m-d H:i:s",time());

            $stmt = $conn->prepare(
                "insert into records(action,time,amount_change,buyer,seller,type)
                values(:action,:time,:amount_change,:buyer,:seller,:type)"
            );
            
            $stmt->execute(
                array(
                    "action"=>"Payment",
                    "time"=>$current_time,
                    "amount_change"=>"-".$_POST['ttl'],
                    "buyer"=>$_SESSION['account'],
                    "seller"=>$_POST['shop'],
                    "type"=>"user_cancel_shop_refund"
                )
            );

            $stmt = $conn->prepare(
                "insert into records(action,time,amount_change,buyer,seller,type)
                values(:action,:time,:amount_change,:buyer,:seller,:type)"
            );
            
            $stmt->execute(
                array(
                    "action"=>"Receive",
                    "time"=>$current_time,
                    "amount_change"=>"+".$_POST['ttl'],
                    "buyer"=>$_SESSION['account'],
                    "seller"=>$_POST['shop'],
                    "type"=>"user_cancel_user_refund"
                )
            );

            $stmt = $conn->prepare(
                "UPDATE orders SET end = :time where OID = :id"
            );
            
            $stmt->execute(
                array(
                    "time"=>$current_time,
                    "id"=>intval($_POST['OIDcancel'])
                )
            );
        }

        echo "<script language= javascript>
          alert('系統提示 = $err ') ; 
          window.location.replace('nav.php');
        </script>" ;
    }
?>