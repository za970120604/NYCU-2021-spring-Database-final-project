<?php
session_start();
if(isset($_POST['transaction_filter'])){
    $filter = $_POST['transaction_filter'];
    unset($_POST['transaction_filter']);

    $_SESSION['transaction_filter'] = $filter;

    header("Location: nav.php");
    exit();
}
?>