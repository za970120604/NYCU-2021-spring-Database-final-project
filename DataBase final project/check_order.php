<?php
    session_start();
    // var_dump($_POST) ; 
    // var_dump($_SESSION) ; 

    $mysqli = new mysqli("localhost","root","","database_hw2");
    $sql = 'SELECT food.name , food.quantity FROM shops CROSS JOIN food WHERE food.shop_name = shops.name  and food.shop_name = "'.$_POST['shop'].'"' ;
    $stmt = $mysqli->prepare($sql) ;
    if(!$stmt){
        echo "Prepare failed: (". $mysqli->errno.") ".$mysqli->error."<br>";
    }
    $stmt->execute() ;
    $infos = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    // var_dump($infos) ;
    // echo sizeof($infos);
    $toolarge = array() ; 
    try{
        $error = "" ; 
        foreach($_POST as $key => $value) {
            $key = str_replace('_',' ',$key) ;
            if($key == 'ttl'){
                $order = intval($_POST['ttl']) ;  
                if($order > $_SESSION["wallet"]){ 
                    $error = "You dont have enough money" ;
                    break ; 
                }
            }
            if($key != 'ttl' && $key != 'shop' && $key != 'subttl' && $key != 'fee'){ 
                if(!preg_match("/^[0-9]*[1-9][0-9]*$/" , $value)){
                    $error = "Your ordering number is not a integer" ; 
                    break ; 
                }
                $flag = 0 ;
                for($i = 0 ; $i < sizeof($infos) ; $i++){
                    if($key == $infos[$i]['name']){
                        $flag = 1 ;  
                        if(intval($value) > $infos[$i]['quantity']){
                            $error = "Your order is too large for this shops" ; 
                            array_push($toolarge , $key);
                            break ; 
                        }
                    }
                }
                if($flag == 0){
                    // echo $key ; 
                    $error = "Food you order does not exist" ; 
                    $has_err = 1 ; 
                    break ;
                }
            }
        }
        // var_dump ($toolarge) ; 
        throw new Exception($error);    
    }
    catch (Exception $e) {
        $err = $e->getMessage() ;
        if($err == ""){
            //user 扣款
            $sql = 'UPDATE users SET wallet = '."wallet".'-'.$_POST['ttl'].' WHERE account = "'.$_SESSION['account'].'"';
            $stmt = $mysqli->prepare($sql) ;
            if(!$stmt){
                echo "Prepare failed: (". $mysqli->errno.") ".$mysqli->error."<br>";
            }
            $stmt->execute() ;
            $stmt->close() ; 
            
            //找OID
            $oid = 'SELECT MAX(OID) FROM orders' ; 
            $stmt1 = $mysqli->prepare($oid) ;
            if(!$stmt1){
                echo "Prepare failed: (". $mysqli->errno.") ".$mysqli->error."<br>";
            }
            $stmt1->execute() ;
            $maxid = mysqli_fetch_all(mysqli_stmt_get_result($stmt1), MYSQLI_ASSOC);
            $curid = $maxid[0]['MAX(OID)']+1 ;
            $stmt1->close() ; 
            
            //encode order detail into strings
            $orderdetail = $_POST['subttl'].'#'.$_POST['fee'].'#'.$_POST['ttl'].'#' ; 
            foreach($_POST as $key => $value) {
                $key1 = str_replace('_',' ',$key) ;
                if($key1 != 'ttl' && $key1 != 'shop' && $key1 != 'subttl' && $key1 != 'fee'){ 
                    $findfid = 'SELECT id from food where name = "'.$key1.'"' ;
                    $stmt2 = $mysqli->prepare($findfid) ;
                    if(!$stmt2){
                        echo "Prepare failed: (". $mysqli->errno.") ".$mysqli->error."<br>";
                    }
                    $stmt2->execute() ;
                    $result = $stmt2->get_result();
                    while($row = $result->fetch_assoc()) {
                        $orderdetail = $orderdetail.$row['id'].'/'.$_POST[strval($key)].'#';
                    }
                    $stmt2->close() ; 
                }
            }
            // echo $orderdetail ;
            //Insert order
            //lee
            date_default_timezone_set('Asia/Taipei');
            $current_time = date("Y-m-d H:i:s",time());
            //lee
            // var_dump($current_time);
            $sql1 = 'INSERT INTO orders (OID , state, start , `shop name` , `total price`, buyer , detail) VALUES ("'.$curid.'" , "Not Finish","'.$current_time.'" , "'.$_POST['shop'].'",'.intval($_POST['ttl']).', "'.$_SESSION['account'].'", "'.$orderdetail.'")' ;
            
            // echo $sql1 ; 
            $stmt3 = $mysqli->prepare($sql1) ;
            if(!$stmt3){
                echo "Prepare failed: (". $mysqli->errno.") ".$mysqli->error."<br>";
            }
            $stmt3->execute() ;
            $stmt3->close() ; 

            //Modify food number
            $sql = 'UPDATE food SET quantity = ' ;
            foreach ($_POST as $key => $value){
                $key1 = str_replace('_',' ',$key) ;
                if($key1 != 'ttl' && $key1 != 'shop' && $key1 != 'subttl' && $key1 != 'fee'){
                    $getquan = 'SELECT quantity FROM food where name = "'.$key1.'"';
                    $stmt4 = $mysqli->prepare($getquan) ;
                    if(!$stmt4){
                        echo "Prepare failed: (". $mysqli->errno.") ".$mysqli->error."<br>";
                    }
                    $stmt4->execute() ;
                    $result = $stmt4->get_result();
                    while($row = $result->fetch_assoc()) {
                        $getquan = $row['quantity']; 
                    }
                    
                    $correct_quantity = $getquan-$value ; 
                    $sqli = $sql.$correct_quantity.' WHERE name = "'.$key1.'"'; 
                    // echo $sqli ; 
                    $stmt4 = $mysqli->prepare($sqli) ;
                    if(!$stmt4){
                        echo "Prepare failed: (". $mysqli->errno.") ".$mysqli->error."<br>";
                    }
                    $stmt4->execute() ;
                    $stmt4->close() ;
                }
            }

            //Shopkeeper earn money
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
            }
            $earn = 'UPDATE users SET wallet = wallet + '.$_POST['ttl'].' WHERE account = "'.$boss.'"';
            // echo $earn ; 
            $stmt = $mysqli->prepare($earn) ;
            if(!$stmt){
                echo "Prepare failed: (". $mysqli->errno.") ".$mysqli->error."<br>";
            }
            $stmt->execute() ;
            $stmt->close() ;


            //lee
            $dbservername = 'localhost';
            $dbname = 'database_hw2';
            $dbusername = 'root';
            $dbpassword = '';

            //連結MySQL Server
            $conn = new PDO(
                "mysql:host=$dbservername;dbname=$dbname",
                $dbusername,
                $dbpassword
            );
            # set the PDO error mode to exception
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
                    "type"=>"take_order_user_paid"
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
                    "type"=>"take_order_shop_get"
                )
            );

        
            //lee


            //close connection
            $mysqli -> close();
            $err = "訂購成功 , 扣款".$_POST['ttl']."元" ;
        }
        else if($err == "Your order is too large for this shops"){
            $err = "Your order is too large for this shops , problem order : " ; 
            for($i = 0 ; $i < sizeof($toolarge) ; $i++){
                $f = $toolarge[$i] ; 
                $err = $err.$f.' , ' ; 
            }
            $err = substr($err , 0, -2);
        }
        
        //跳出系統訊息
        // echo $err ; 
        echo "<script language= javascript>
          alert('系統提示 = $err ') ; 
          window.location.replace('nav.php');
        </script>" ; 
    }

?>