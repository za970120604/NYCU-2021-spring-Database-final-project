<?php
session_start();
if(isset($_POST['shop_order_option'])){
    $filter = $_POST['shop_order_option'];
    unset($_POST['shop_order_option']);

    $_SESSION['shop_order_filter'] = $filter;

    header("Location: nav.php");
    exit();
}
?>