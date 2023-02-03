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

$stmt = $conn->prepare("select max(OID) from orders");
$stmt->execute();
$max_oid = $stmt->fetch()['max(OID)'];

if($max_oid===null){
    $max_oid = 0;
}

function print_shop_order_row($order_id,$state,$start_time,$end_time,$shop_name,$total_price,$buyer,$detail){
    echo<<<EOT
    <tr>
        <th scope="row">{$order_id}</th>
        <td>{$state}</td>
        <td>{$start_time}</td>
        <td>{$end_time} </td>
        <td>{$shop_name} </td>
        <td>{$total_price} </td>
        <td>{$buyer} </td>
        <td>
          <form action="shop_order_detail_show.php" method="post">
            <input name="order_id{$order_id}" type="submit" value="order details" class="btn btn-info" data-toggle="modal">
          </form>
        </td>
    EOT;
    if($state==="Not Finish"){
        echo<<<EOT
            <td>
            <form action="shop_action.php" method="post">
                <input class="btn btn-success" type="submit" value="Done" name="action_done_{$order_id}">
            </form>
            <form action="shop_action.php" method="post">
                <input class="btn btn-danger" type="submit" value="Cancel" name="action_cancel_{$order_id}">
            </form>
            </td>
        EOT;
    }
    
    echo<<<EOT
    </tr>
    EOT;
}

function print_modal($oid){
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
    echo<<<EOT
    <!-- Modal -->
    <div class="modal fade" id="shop_order_detail_modal{$oid}" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
            <h5 class="modal-title" id="staticBackdropLabel">order</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            </div>
            <div class="modal-body">
            <div class="row">
                <div class="  col-xs-12">
                <table class="table" style="margin-top:15px"> 
                    <thead>
                    <tr>
                        <th scope="col">Picture</th>
                        <th scope="col">meal name</th>
                        <th scope="col">price</th>
                        <th scope="col">Quantity</th>
                    </tr>
                    </thead>
                    <tbody>
    EOT;

    $stmt = $conn->prepare("select detail from orders where OID=:oid");
    $stmt->execute(
        array(
            "oid"=>$oid
        )
    );
    $target_order_detail = $stmt->fetch()['detail'];
    $arr = explode('#',$target_order_detail);
    array_pop($arr);
    $subttl = $arr[0];
    $delivery_fee = $arr[1];
    $ttl = $arr[2];

    for($i=3;$i<sizeof($arr);$i++){
        $str = $arr[$i];
        $small_arr = explode('/',$str);
        $food_id = $small_arr[0];
        $food_number = $small_arr[1];

        $stmt = $conn->prepare("select * from food where id=:food_id");
        $stmt->execute(
            array(
                'food_id'=>$food_id
            )
        );
        $result = $stmt->fetch();
        if($result!=null){
            echo<<<EOT
            <tr>
                <th scope="row"><img src="data:{$result['picture_file_type']};base64,{$result['picture']}" width="50%" height="50%" alt="Hamburger"></th>
                <td>{$result['name']}</td>
                <td>{$result['price']}</td>
                <td>{$food_number}</td>
            </tr>
            EOT;
        }
    }

    echo<<<EOT
    </tbody>
            </table>
            </div>
        </div>
        </div>
        <div class=modal-foot>
        <p>subtotal:{$subttl}</p>
        <p>delivery fee:{$delivery_fee}</p>
        <p>total price:{$ttl}</p>
        </div>
    EOT;
    

    echo<<<EOT
        </div>
        </div>
    </div>
    <!--modal-->
    EOT;
}
function print_shop_order_filter($type){
    echo<<<EOT
    <form action = 'shop_order_filter.php' method = post>
        <label for="status">Status</label>
        <select name = 'shop_order_option' id="status">
    EOT;
    if($type==='All'){
        echo '<option selected = "t">All</option>';
    }
    else{
        echo '<option>All</option>';
    }
    if($type==='Finished'){
        echo '<option selected = "t">Finished</option>';
    }
    else{
        echo '<option>Finished</option>';
    }
    if($type==='Not Finish'){
        echo '<option selected = "t">Not Finish</option>';
    }
    else{
        echo '<option>Not Finish</option>';
    }
    if($type==='Canceled'){
        echo '<option selected = "t">Canceled</option>';
    }
    else{
        echo '<option>Canceled</option>';
    }
    echo<<<EOT
        </select>
        <input type="submit" class="btn btn-default" value="搜尋訂單">
    </form>
    EOT;
}

        
for($i=1;$i<=$max_oid;$i++){
    print_modal($i);
}

if(isset($_SESSION['shop_order_filter'])&&$_SESSION['shop_order_filter']!=='All'){
    print_shop_order_filter($_SESSION['shop_order_filter']);
}
else{
    $filter_type = 'All';
    print_shop_order_filter($filter_type);
}

echo<<<EOT
<div class="row">
    <div class="  col-xs-8">
    <table class="table" style=" margin-top: 15px;">
        <thead>
        <tr>
            <th scope="col">Order ID</th>
            <th scope="col">Finished</th>
            <th scope="col">Start</th>
            <th scope="col">End</th>
            <th scope="col">Shop name</th>
            <th scope="col">Total Price</th>
            <th scope="col">buyer</th>
            <th scope="col">Order Details</th>
            <th scope="col">Action</th>
        </tr>
        </thead>
        <tbody>
EOT;

$stmt = $conn->prepare(
    "select * from orders"
);
$stmt->execute();
if(isset($_SESSION['shop_order_filter'])&&$_SESSION['shop_order_filter']!=='All'){
    while($row=$stmt->fetch()){
        if(isset($_SESSION['shop_name'])&&$row['state']===$_SESSION['shop_order_filter']&&$row['shop name']===$_SESSION['shop_name']){
            print_shop_order_row($row['OID'],$row['state'],$row['start'],$row['end'],$row['shop name'],$row['total price'],$row['buyer'],$row['detail']);
        }
    }
}
else{
    while($row=$stmt->fetch()){
        if(isset($_SESSION['shop_name'])&&$row['shop name']===$_SESSION['shop_name']){
            print_shop_order_row($row['OID'],$row['state'],$row['start'],$row['end'],$row['shop name'],$row['total price'],$row['buyer'],$row['detail']);
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