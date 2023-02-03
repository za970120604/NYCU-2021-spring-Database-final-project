<?php
session_start();
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
function check_food_in_orders($meal_id){
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

    $stmt = $conn->prepare(
        "select * from orders where state='Not Finish'"
    );
    $stmt->execute();

    $meal_in_order = false;
    while($row=$stmt->fetch()){
        if($meal_in_order===true){
            break;
        }
        $target_order_detail = $row['detail'];
        $arr = explode('#',$target_order_detail);
        array_pop($arr);

        for($i=3;$i<sizeof($arr);$i++){
            $str = $arr[$i];
            $small_arr = explode('/',$str);
            $food_id = $small_arr[0];
            if($food_id===strval($meal_id)){
                $meal_in_order = true;
                break;
            }
        }
    }
    return $meal_in_order;
}
try{
    $stmt = $conn->prepare("select * from food");
    $stmt->execute();
    $rowNumber = $stmt->rowCount();
    $meal_id = -1;
    for($i=1;$i<=$rowNumber;$i++){
        $submitName = "delete_id".strval($i);
        if(isset($_POST[$submitName])){
            $meal_id = $i;
            break;
        }
    }

    if($meal_id===-1){
        throw new Exception('餐點不存在');
    }
    else if(check_food_in_orders($meal_id)){
        throw new Exception('有訂單尚未完成，無法修改食物');
    }

    $stmt = $conn->prepare(
        " delete from food
        where id=:id"
    );
    $stmt->execute(array(
        'id'=>$meal_id
    ));
    $_SESSION['jump'] = true;
    header("Location: nav.php");
}
catch(Exception $e){
    $_SESSION['jump'] = true;
    $msg = $e->getMessage();
    // session_unset();
    // session_destroy();
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