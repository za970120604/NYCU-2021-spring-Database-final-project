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

$account = $_GET['account'];

$stmt = $conn->prepare("select * from users where account=:account");

$stmt->execute(
    array(
        "account" => $account
    )
);
if($stmt->rowCount()==0){
    echo "this account is ok";
}
else{
    echo "this account has been registered";
}
?>