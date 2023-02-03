<?php
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
function print_record_row($record_id,$action,$time,$buyer,$seller,$amount_change,$type){
    $trader="";
    if($type==='recharge'){
        $trader=$buyer;
    }
    else if($type==='take_order_user_paid'){
        $trader = $seller;
    }
    else if($type==='take_order_shop_get'){
        $trader = $buyer;
    }
    else if($type==='user_cancel_user_refund'){
        $trader = $seller;
    }
    else if($type==='user_cancel_shop_refund'){
        $trader = $buyer;
    }
    else if($type==='shop_cancel_user_refund'){
        $trader = $seller;
    }
    else if($type==='shop_cancel_shop_refund'){
        $trader = $buyer;
    }
    echo<<<EOT
    <tr>
        <th scope="row">{$record_id}</th>
        <td>{$action}</td>
        <td>{$time}</td>
        <td>{$trader} </td>
        <td>{$amount_change} </td>
        <td>{$type}</td>
    </tr>
    EOT;
}
function print_filter($type){
    echo<<<EOT
        <form action = 'transaction_record_filter.php' method = post>
            <label for="action">Action</label>
            <select name = 'transaction_filter' id="action">
    EOT;
    if($type==='All'){
        echo '<option selected = "t">All</option>';
    }
    else{
        echo '<option>All</option>';
    }
    if($type==='Payment'){
        echo '<option selected = "t">Payment</option>';
    }
    else{
        echo '<option>Payment</option>';
    }
    if($type==='Receive'){
        echo '<option selected = "t">Receive</option>';
    }
    else{
        echo '<option>Receive</option>';
    }
    if($type==='Recharge'){
        echo '<option selected = "t">Recharge</option>';
    }
    else{
        echo '<option>Recharge</option>';
    }
    echo<<<EOT
        </select>
        <input type="submit" class="btn btn-default" value="搜尋">
    </form>
    EOT;
}


function can_print($row){
    if($row['action']==='Recharge'&&$row['buyer']===$_SESSION['account']){
        return true;
    }
    else if(($row['buyer']===$_SESSION['account']) && (isset($_SESSION['shop_name'])&&$row['seller']===$_SESSION['shop_name'])){
        return true ;
    }
    else if($row['buyer']===$_SESSION['account']){
        if($row['type']==='take_order_user_paid'||$row['type']==='shop_cancel_user_refund'||$row['type']==='user_cancel_user_refund'){
            return true;
        }
        else{
            return false;
        }
    }
    else if(isset($_SESSION['shop_name'])&&$row['seller']===$_SESSION['shop_name']){
        if($row['type']==='take_order_shop_get'||$row['type']==='shop_cancel_shop_refund'||$row['type']==='user_cancel_shop_refund'){
            return true;
        }
        else{
            return false;
        }
    }
}

if(isset($_SESSION['transaction_filter'])&&$_SESSION['transaction_filter']!='All'){
    print_filter($_SESSION['transaction_filter']);
}
else{
    $filter = 'All';
    print_filter($filter);
}
echo<<<EOT
    <div class="row">
        <div class="  col-xs-8">
        <table class="table" style=" margin-top: 15px;">
            <thead>
            <tr>
                <th scope="col">Record ID</th>
                <th scope="col">Action</th>
                <th scope="col">Time</th>
                <th scope="col">Trader</th>
                <th scope="col">Amount change</th>
                <th scope="col">type</th>
            </tr>
            </thead>
            <tbody>
EOT;
$stmt = $conn->prepare(
    "select * from records"
);

$stmt->execute();

if(isset($_SESSION['transaction_filter'])&&$_SESSION['transaction_filter']!='All'){
    while($row = $stmt->fetch()){
        if($row['action']===$_SESSION['transaction_filter']){
            if(can_print($row)){
                print_record_row($row['record_id'],$row['action'],$row['time'],$row['buyer'],$row['seller'],$row['amount_change'],$row['type']);
            }
        }
    }
}
else{
    while($row = $stmt->fetch()){
        if(can_print($row)){
            print_record_row($row['record_id'],$row['action'],$row['time'],$row['buyer'],$row['seller'],$row['amount_change'],$row['type']);
        }
    }
}

echo<<<EOT
            </tbody>
        </table>
        </div>
    </div>
EOT;


?>