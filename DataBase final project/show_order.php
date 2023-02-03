<?php
    // var_dump($_SESSION) ;
    $content = <<<EOT
    <form action = 'filter.php' method = post>
        <select name = 'type'>
        <option selected = "t">All</option>
        <option>Finished</option>
        <option>Not Finish</option>
        <option>Canceled</option>
        </select>
        <button type="submit" class="btn btn-default">搜尋訂單</button>
    </form>
        <table class="table" style=" margin-top: 10px;">
            <thead>
            <tr>
                <th scope="col">OID</th>               
                <th scope="col">Status</th>
                <th scope="col">Start</th>
                <th scope="col">End</th>              
                <th scope="col">Shop name</th>
                <th scope="col">Total Price</th>
                <th scope="col">Order Details</th>
                <th scope="col">Action</th>                  
            </tr>
            </thead>


    EOT;
    echo $content ;
    // echo isset($_SESSION['order']) ; 
    $mysqli = new mysqli("localhost","root","","database_hw2");
    if(isset($_SESSION['order'])){     
        $sql = $_SESSION['order'] ; 
        $stmt = $mysqli->prepare($sql) ;
        if(!$stmt){
        echo "Prepare failed: (". $mysqli->errno.") ".$mysqli->error."<br>";
        }
        $stmt->execute() ; 
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) { 
                // var_dump($row) ; 
                $ID = $row['OID']  ; 
                $status = $row['state']  ;
                $start = $row['start']  ;
                $end = $row['end']  ;
                $shopname = $row['shop name'] ;
                $ttl = $row['total price'] ;
                $buyer = $row['buyer'] ; 
                $content = <<<EOT
                    <tbody>
                    <tr>
                        <td>$ID</td>
                        <td>$status</td>
                        <td>$start</td>
                        <td>$end</td>
                        <td>$shopname</td>
                        <td>$ttl</td>
                        <td>
                            <form action = 'user_get_order.php' , method = 'POST'>
                                <button type="submit" class="btn btn-info" value="$ID" name="OID">Order Detail</button>
                                <input name= "ttl" type="hidden" value = $ttl>
                                <input name= "buyer" type="hidden" value = $buyer>
                                <input name= "shop" type="hidden" value = $shopname>
                            </form>
                        </td>
                EOT;
                echo $content ; 
                if($status == "Not Finish"){
                    $content = <<<EOT
                        <td>
                            <form action = 'user_modify_order.php' , method = 'POST'>
                                <button type="submit" class="btn btn-danger" value="$ID" name="OIDcancel">Cancel</button>
                                <input name= "ttl" type="hidden" value = $ttl>
                                <input name= "buyer" type="hidden" value = $buyer>
                                <input name= "shop" type="hidden" value = $shopname>
                            </form>
                        </td>
                    </tr>
                    </tbody>
                    EOT ;
                    echo $content ;
                }  
            }
        }
    }
    else{
        $sql = 'SELECT * from orders where buyer = "'.$_SESSION['account'].'"'  ; 
        $stmt = $mysqli->prepare($sql) ;
        if(!$stmt){
        echo "Prepare failed: (". $mysqli->errno.") ".$mysqli->error."<br>";
        }
        $stmt->execute() ; 
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) { 
                // var_dump($row) ; 
                $ID = $row['OID']  ; 
                $status = $row['state']  ;
                $start = $row['start']  ;
                $end = $row['end']  ;
                $shopname = $row['shop name'] ;
                $ttl = $row['total price'] ;
                $buyer = $row['buyer'] ; 
                $content = <<<EOT
                    <tbody>
                    <tr>
                        <td>$ID</td>
                        <td>$status</td>
                        <td>$start</td>
                        <td>$end</td>
                        <td>$shopname</td>
                        <td>$ttl</td>
                        <td>
                            <form action = 'user_get_order.php' , method = 'POST'>
                                <button type="submit" class="btn btn-info" value="$ID" name="OID">Order Detail</button>
                                <input name= "ttl" type="hidden" value = $ttl>
                                <input name= "buyer" type="hidden" value = $buyer>
                                <input name= "shop" type="hidden" value = $shopname>
                            </form>
                        </td>
                EOT;
                echo $content ; 
                if($status == "Not Finish"){
                    $content = <<<EOT
                        <td>
                            <form action = 'user_modify_order.php' , method = 'POST'>
                                <button type="submit" class="btn btn-danger" value="$ID" name="OIDcancel">Cancel</button>
                                <input name= "ttl" type="hidden" value = $ttl>
                                <input name= "buyer" type="hidden" value = $buyer>
                                <input name= "shop" type="hidden" value = $shopname>
                            </form>
                        </td>
                    </tr>
                    </tbody>
                    EOT ;
                    echo $content ;
                }
            }
        }
    }
    $content = <<<EOT
        </table>
    EOT ; 
    echo $content ;  
    unset($_SESSION['order']) ; 
?>