<?php
$dbservername = 'localhost';
$dbname = 'database_hw2';
$dbusername = 'root';
$dbpassword = '';
session_start() ; 
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
try{
    $oid = -1;
    $type = "";
    foreach($_POST as $name=>$value){
        $arr = explode('_',$name);
        if($arr[0]==='action'){
            if($arr[1]==='done'){
                $oid = $arr[2];
                $type = 'done';
            }
            else if($arr[1]==='cancel'){
                $oid = $arr[2];
                $type = 'cancel';
            }
            unset($_POST[$name]);
            break;
        }
    }
    $message = "";
    $change_state = "";
    if($type==='done'){
        $change_state = 'Finished';
        $message = '訂單已完成';

        $stmt = $conn->prepare("select * from orders where OID=:oid");
        $stmt->execute(
            array(
                "oid" => $oid
            )
        );

        $data = $stmt->fetch();

        // detect exception
        $detail = $data['detail'];
        $arr = explode('#',$detail);
        array_pop($arr);
        $food_number_is_neg = false;
        for($i=3;$i<sizeof($arr);$i++){   //check food number
            $str = $arr[$i];
            $small_arr = explode('/',$str);
            $food_id = $small_arr[0];

            $stmt = $conn->prepare("select * from food where id=:food_id");
            $stmt->execute(
                array(
                    'food_id'=>$food_id
                )
            );
            $result = $stmt->fetch();

            if($result['quantity']<0){
                $food_number_is_neg = true;
                break;
            }
        }                                 //!check food 
        if($food_number_is_neg){
            throw new Exception("cannot finish:food number is negative");
        }
        // !detext exception
        $stmt = $conn->prepare(
            "update orders 
            set state=:state,
            end=:end
            where OID=:oid"
        );

        date_default_timezone_set('Asia/Taipei');
        $current_time = date("Y-m-d H:i:s",time());

        $stmt->execute(
            array(
                "state"=>$change_state,
                "end"=>$current_time,
                "oid"=>$oid
            )
        );

        $buyer=$data['buyer'];
        $shop_name = $data['shop name'];
        date_default_timezone_set('Asia/Taipei');
        $current_time = date("Y-m-d H:i:s",time());

        $stmt = $conn->prepare(
            "insert into records(action,time,buyer,seller,amount_change,type)
            values(:action,:time,:buyer,:seller,:amount_change,:type)"
        );
    }
    else if($type==='cancel'){
        $change_state = 'Canceled';
        $message = '訂單已取消';

        $stmt = $conn->prepare("select * from orders where OID=:oid");
        $stmt->execute(
            array(
                "oid" => $oid
            )
        );

        $data = $stmt->fetch();

        $stmt = $conn->prepare(
            "update orders 
            set state=:state,
            end=:end
            where OID=:oid"
        );

        date_default_timezone_set('Asia/Taipei');
        $current_time = date("Y-m-d H:i:s",time());

        $stmt->execute(
            array(
                "state"=>$change_state,
                "end"=>$current_time,
                "oid"=>$oid
            )
        );

        $detail = $data['detail'];
        $buyer = $data['buyer'];
        $shop_name = $data['shop name'];
        $arr = explode('#',$detail);
        array_pop($arr);
        $subttl = $arr[0];
        $delivery_fee = $arr[1];
        $ttl = $arr[2];

        for($i=3;$i<sizeof($arr);$i++){   //add food back
            $str = $arr[$i];
            $small_arr = explode('/',$str);
            $food_id = $small_arr[0];
            $food_number = $small_arr[1];

            $stmt = $conn->prepare("select * from food where id=:food_id");
            $stmt->execute(
                array(
                    'food_id'=>$food_id
                )
            );
            $result = $stmt->fetch();

            $stmt = $conn->prepare(
                "update food
                set quantity=:food_number
                where id=:food_id"
            );
            $stmt->execute(
                array(
                    'food_number'=>$result['quantity']+$food_number,
                    'food_id'=>$food_id
                )
            );
        }                                 //!add food back

        $stmt = $conn->prepare(                    //add money back to buyer
            "select * from users where account=:account"
        );
        $stmt->execute(
            array(
                'account'=>$buyer
            )
        );
        if($stmt->rowCount()!=1){
            throw new Exception('cancel error:使用者不存在');
        }
        else{
            $buyer_data = $stmt->fetch();
            $stmt = $conn->prepare(
                "update users
                set wallet=:new_money
                where account=:account"
            );
            $stmt->execute(
                array(
                    "new_money"=>$ttl+$buyer_data['wallet'],
                    "account"=>$buyer
                )
            );
        }                                           //!add money back to buyer

        $stmt = $conn->prepare(                    //minus money back to shop owner
            "select * from users where account=:account"
        );
        $stmt->execute(
            array(
                "account"=>$_SESSION['account']
            )
        );
        $shop_owner_detail = $stmt->fetch();
        $stmt = $conn->prepare(
            "update users
            set wallet=:new_money
            where account=:account"
        );
        $stmt->execute(
            array(
                "new_money"=>$shop_owner_detail['wallet']-$ttl,
                "account"=>$_SESSION['account']
            )
        );                                      //!minus money back to shop owner

        $stmt = $conn->prepare(  // save two record
            "insert into records(action,time,buyer,seller,amount_change,type)
            values(:action,:time,:buyer,:seller,:amount_change,:type)"
        );
        
        $stmt->execute(
            array(
                "action"=>"Receive",
                "time"=>$current_time,
                "buyer"=>$buyer,
                "seller"=>$shop_name,
                "amount_change"=>"+".$data["total price"],
                "type"=>"shop_cancel_user_refund"
            )
        );

        $stmt = $conn->prepare(
            "insert into records(action,time,buyer,seller,amount_change,type)
            values(:action,:time,:buyer,:seller,:amount_change,:type)"
        );
        
        $stmt->execute(
            array(
                "action"=>"Payment",
                "time"=>$current_time,
                "buyer"=>$buyer,
                "seller"=>$shop_name,
                "amount_change"=>"-".$data["total price"],
                "type"=>"shop_cancel_shop_refund"
            )
        );
    }  
    echo "<script language= javascript>
              alert('系統提示 = $message ') ; 
              window.location.replace('nav.php');
            </script>" ; 
    
}
catch(Exception $e){
    $msg = $e->getMessage();
    session_unset();
    session_destroy();
    echo <<<EOT
        <!DOCTYPE html>
        <html>
        <body>
        <script>
        alert("$msg");
        window.location.replace("nav.php");
        </script>
        </body>
        </html>
    EOT;
}


?>