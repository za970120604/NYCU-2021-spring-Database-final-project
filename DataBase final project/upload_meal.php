<?php
session_start();
//$_SESSION['Authenticated'] = false;
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

// $empty_place = null;
function oneEmpty(){
    return empty($_POST['name'])
    ||empty($_POST['price'])
    ||empty($_POST['quantity'])
    ||($_FILES['myFile']['size']==0);
}
$error = null;
function formatError(){
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];

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

function readPicture(){
    //開啟圖片檔
    $file = fopen($_FILES["myFile"]["tmp_name"], "rb");
    // 讀入圖片檔資料
    $fileContents = fread($file, filesize($_FILES["myFile"]["tmp_name"])); 
    //關閉圖片檔
    fclose($file);
    //讀取出來的圖片資料必須使用base64_encode()函數加以編碼：圖片檔案資料編碼
    $picture = base64_encode($fileContents);
    return $picture;
}

function read_picture_type(){
    //read img file type
    $picture_file_type=$_FILES["myFile"]["type"];
    return $picture_file_type;
}

function find_id(){
    $dbservername = $GLOBALS['dbservername'];
    $dbname = $GLOBALS['dbname'];
    $dbusername = $GLOBALS['dbusername'];
    $dbpassword = $GLOBALS['dbpassword'];
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
    
    //找id
    $id = -1;
    $stmt = $conn->prepare("select * from food");
    $stmt->execute();

    if($stmt->rowCount()==0){
        $id = 1;
    }
    else{
        $stmt = $conn->prepare("select max(id) from food");
        $stmt->execute();
        $row = $stmt->fetch();
        $id = $row['max(id)']+1;
    }
    return $id;
}
try{
    if(!isset($_POST['name'])||!isset($_POST['price'])||!isset($_POST['quantity'])||!isset($_FILES['myFile'])){
        header("Location: nav.php");
        exit();
    }
    if(oneEmpty()){
        throw new Exception('欄位空白');
    }
    else if(formatError()){
        throw new Exception("輸入格式錯誤:".$GLOBALS['error']);
    }
    
    $picture = readPicture();
    $picture_file_type = read_picture_type();
    $id = find_id();

    $stmt = $conn->prepare("INSERT INTO food (id,name,price,quantity,picture,picture_file_type,shop_name) 
                        VALUES (:id,:name,:price,:quantity,:picture,:picture_file_type,:shop_name)");

    $stmt->execute(array(
        'id' => $id,
        'name'=>$_POST['name'],
        'price'=>$_POST['price'],
        'quantity'=>$_POST['quantity'],
        'picture'=>$picture,
        'picture_file_type'=>$picture_file_type,
        'shop_name'=>$_SESSION['shop_name']
    ));
    $_SESSION['jump'] = true;
    header("Location: nav.php");
}

catch (Exception $e){
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