<?php
session_start();
$_SESSION['Authenticated'] = false;
$dbservername = 'localhost';
$dbname = 'database_hw2';
$dbusername = 'root';
$dbpassword = '';
try {
    if (!isset($_POST['account']) || !isset($_POST['password'])) {
        header("Location: index.php");
        exit();
    }
    if (empty($_POST['account']) || empty($_POST['password']))
        throw new Exception('登入失敗');
        
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
        "select account, password from users where account=:account"
    );
    $stmt->execute(array('account' => $_POST['account']));
    if ($stmt->rowCount() == 1) {
        $row = $stmt->fetch();
        if ($row['password'] == hash('sha256', $_POST['password'])) {
            $_SESSION['Password'] = $row['password'] ; // tsu part
            $_SESSION['Authenticated'] = true;
            $_SESSION['account'] = $row[0];
            // $_SESSION['jump'] = true;
            header("Location: nav.php");
            exit();
        } else
            throw new Exception('Login failed.');
    } else
        throw new Exception('Login failed.');
} catch (Exception $e) {
    $msg = $e->getMessage();
    session_unset();
    session_destroy();
    echo <<<EOT
        <!DOCTYPE html>
        <html>
        <body>
        <script>
        alert("$msg");
        window.location.replace("index.php");
        </script>
        </body>
        </html>
    EOT;
}
