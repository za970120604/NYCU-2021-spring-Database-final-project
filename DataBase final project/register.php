<?php
session_start();
$_SESSION['Authenticated'] = false;
$dbservername = 'localhost';
$dbname = 'database_hw2';
$dbusername = 'root';
$dbpassword = '';

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

function oneEmpty(){
    return empty($_POST['username'])
    ||empty($_POST['phone_number'])
    ||empty($_POST['account'])
    ||empty($_POST['password'])
    ||empty($_POST['re_type_password'])
    ||empty($_POST['latitude'])
    ||empty($_POST['longitude']);
}

function check_latitude($latitude){
    if(!preg_match('/([0-9]*\.[0-9]*)|([0-9]*)/',$latitude)){
        return false;
    }
    else{
        $latitude = doubleval($latitude);
        if($latitude<-90||$latitude>90){
            return false;
        }
    }
    return true;
}

function check_longitude($longitude){
    if(!preg_match('/([0-9]*\.[0-9]*)|([0-9]*)/',$longitude)){
        return false;
    }
    else{
        $longitude = doubleval($longitude);
        if($longitude<-180||$longitude>180){
            return false;
        }
    }
    return true;
}


$error = null;
function formatError(){
    $name = $_POST['username'];
    $phone_number = $_POST['phone_number'];
    $account = $_POST['account'];
    $password = $_POST['password'];
    $re_type_password = $_POST['re_type_password'];
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];

    if(preg_match('/[^A-Za-z0-9]/', $account)){
        $GLOBALS['error'] = "account";
        return true;
    }
    else if(preg_match('/[^A-Za-z0-9]/', $password)){
        $GLOBALS['error']  = "password";
        return true;
    }
    else if($password!=$re_type_password){
        $GLOBALS['error']  = "re_type_password";
        return true;
    }
    else if(preg_match('/[^A-Za-z]/', $name)){
        $GLOBALS['error']  = "name";
        return true;
    }
    else if(!preg_match('/^[0-9]{10}$/', $phone_number)){
        $GLOBALS['error']  = "phone_number";
        return true;
    }
    else if(!check_latitude($latitude)){
        $GLOBALS['error']  = "latitude";
        return true;
    }
    else if(!check_longitude($longitude)){
        $GLOBALS['error']  = "longitude";
        return true;
    }
    else{
        return false;
    }
}

try {
    $stmt = $conn->prepare(
        "select account from users where account=:account"
    );
    $stmt->execute(array('account'=>$_POST['account']));
    if (oneEmpty()){
        throw new Exception("欄位空白");
    }
    else if(formatError()){
        throw new Exception("輸入格式錯誤:".$GLOBALS['error']);
    }
    else if ($stmt->rowCount() != 0){
        throw new Exception("帳號已被註冊");
    }
    else if($_POST['password']!=$_POST['re_type_password']) {
        throw new Exception("密碼驗證 ≠ 密碼");
    }
    else {
        $stmt = $conn->prepare(
            "insert into users(
                account, password, username, phone_number, location, wallet
            ) values(
                :account,:password,:username,:phone_number,ST_GeomFromText('POINT(".$_POST['longitude']." ".$_POST['latitude'].")'),:wallet
            )"
        );
        $stmt->execute(
            array(
                'account' => $_POST['account'],
                'password' => hash('sha256', $_POST['password']),
                'username' => $_POST['username'],
                'phone_number' => $_POST['phone_number'],
                'wallet' => 0
            )
        );
        echo "<script>
                alert('註冊成功');
                location.href='index.php';
             </script>";
        exit();
    }
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
        window.location.replace("sign-up.php");
        </script>
        </body>
        </html>
    EOT;
}

?>
