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

function find_meal_id(){
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
    $stmt = $conn->prepare("select max(id) from food");
    $stmt->execute();
    $row = $stmt->fetch();
    $max_id = $row['max(id)'];
    $meal_id = -1;
    for($i=1;$i<=$max_id;$i++){
        $submitName = "id".strval($i);
        if(isset($_POST[$submitName])){
            $meal_id = $i;
            break;
        }
    }
    return $meal_id;
}

function oneEmpty(){
    return empty($_POST['new_price'])
    ||empty($_POST['new_quantity']);
}
$error = null;
function formatError(){
    $price = $_POST['new_price'];
    $quantity = $_POST['new_quantity'];

    if(!preg_match("/^(?:[1-9][0-9]*|0)$/",$price)){
        $GLOBALS['error'] = "price";
        return true;
    }
    else if(!preg_match("/^(?:[1-9][0-9]*|0)$/",$quantity)){
        $GLOBALS['error'] = "quantity";
        return true;
    }
    else{
        return false;
    }
}
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
try {
    $meal_id = find_meal_id();
    if(!isset($_POST['new_price'])||!isset($_POST['new_quantity'])){
        header("Location: nav.php");
        exit();
    }
    else if(oneEmpty()){
        throw new Exception('欄位空白');
    }
    else if(formatError()){
        throw new Exception("輸入格式錯誤:".$GLOBALS['error']);
    }
    else if(check_food_in_orders($meal_id)){
        throw new Exception("有訂單尚未完成，無法修改食物");
    }
    $stmt = $conn->prepare(
        "update food
        set  price=:price, quantity=:quantity
        where id=:id"
    );
    $stmt->execute(array(
        'price'=>$_POST['new_price'],
        'quantity'=>$_POST['new_quantity'],
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