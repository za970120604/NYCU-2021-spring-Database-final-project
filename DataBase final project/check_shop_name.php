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

$shop_name = $_GET['shop_name'];

$stmt = $conn->prepare("select * from shops where name=:shop_name");

$stmt->execute(
    array(
        "shop_name" => $shop_name
    )
);
if($stmt->rowCount()==0){
    echo "this shop name is ok";
}
else{
    echo "this shop name has been registered";
}
?>