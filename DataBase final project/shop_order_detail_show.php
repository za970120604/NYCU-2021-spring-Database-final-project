<?php
session_start();

foreach($_POST as $name => $item){
    if(substr($name,0,strlen('order_id'))==='order_id'){
        $_SESSION['shop_order_detail'] = (int)substr($name,strlen('order_id'),strlen($item));
        unset($_POST[$name]);
        break;
    }
}

if(isset($_SESSION['shop_order_detail'])){
    header('Location: nav.php');
}
exit();
?>