<?php
  function getDistance($longitude1, $latitude1, $longitude2, $latitude2, $unit=2, $decimal=2){

    $EARTH_RADIUS = 6370.996; // 地球半径系数
    $PI = 3.1415926;

    $radLat1 = $latitude1 * $PI / 180.0;
    $radLat2 = $latitude2 * $PI / 180.0;

    $radLng1 = $longitude1 * $PI / 180.0;
    $radLng2 = $longitude2 * $PI /180.0;

    $a = $radLat1 - $radLat2;
    $b = $radLng1 - $radLng2;

    $distance = 2 * asin(sqrt(pow(sin($a/2),2) + cos($radLat1) * cos($radLat2) * pow(sin($b/2),2)));
    $distance = $distance * $EARTH_RADIUS * 1000;

    if($unit==2){
        $distance = $distance / 1000;
    }

    return round($distance, $decimal);

  }
?>


<?php
    session_start();
    // var_dump($_SESSION) ; 
    // var_dump($_POST) ;
    $content = <<<EOF
    <form action = check_order.php method = post>
    <div class="modal fade" id="macdonald"  data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Order</h4>
        </div>
        <div class="modal-body">
         <!--  -->
         <div class="row">
          <div class="  col-xs-12">
            <table class="table" style=" margin-top: 30px;">
              <thead>
                <tr>
                  <th scope="col">Picture</th>
                 
                  <th scope="col">meal name</th>
               
                  <th scope="col">price</th>
                  <th scope="col">Order Quantity</th>
                
                </tr>
              </thead>
              <tbody>
    EOF;
    echo $content ;
    $mysqli = new mysqli("localhost","root","","database_hw2");
    $shopname = $_POST['shopname'] ; 
    $sql = 'SELECT food.name FROM shops CROSS JOIN food WHERE food.shop_name = shops.name  and food.shop_name = "'.$shopname.'"' ;
    // echo $sql ; 
    $stmt = $mysqli->prepare($sql) ;
    if(!$stmt){
      echo "Prepare failed: (". $mysqli->errno.") ".$mysqli->error."<br>";
    }
    $stmt->execute() ; 
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
      $subtotal = 0 ; 
      while($row = $result->fetch_assoc()) {
        $name = $row["name"] ; 
        $sorder = $row["name"].'_order' ;
        $spic = $row["name"].'_pic' ; 
        $spic_file_type = $row["name"].'_pic_file_type' ; 
        $sprice = $row["name"].'_price' ;
        $sfoodname = $row["name"] ;

        $bugname = str_replace(' ','_',$row["name"]);
        $sorderbug = $bugname.'_order' ; 
        $spicbug = $bugname.'_pic' ; 
        $spic_file_typebug = $bugname.'_pic_file_type' ; 
        $spricebug = $bugname.'_price' ;
        $sfoodnamebug = $row["name"] ;

        if(isset($_POST[$sorder]) &&  $_POST[$sorder] != '0' ){
          $content = <<<EOF
                    <tr>
                        <td><img src="data:$_POST[$spic_file_type];base64, $_POST[$spic]" width="50%" height="50%"></td>
                        <td align="center">$sfoodname</td>
                        <td align="center">$_POST[$sprice]</td>
                        <td align="center">$_POST[$sorder] </td>
                        <input name = "$name" value = $_POST[$sorder] type = "hidden">
                    </tr>
                    EOF;
                    echo $content;
          $subtotal += $_POST[$sprice]*intval($_POST[$sorder]) ; 
        }
        else if(isset($_POST[$sorderbug]) &&  $_POST[$sorderbug] != '0'){
          $content = <<<EOF
                    <tr>
                        <td><img src="data:$_POST[$spic_file_typebug];base64, $_POST[$spicbug]" width="50%" height="50%"></td>
                        <td align="center">$sfoodnamebug</td>
                        <td align="center">$_POST[$spricebug]</td>
                        <td align="center">$_POST[$sorderbug] </td>
                        <input name = "$name" value = $_POST[$sorderbug] type = "hidden">
                    </tr>
                    EOF;
                    echo $content;
          $subtotal += $_POST[$spricebug]*intval($_POST[$sorderbug]) ;
        }
      }
    }

    $sql = 'SELECT ST_X(location) , ST_Y(location) FROM shops WHERE shops.name = "'.$shopname.'"' ;
    $stmt = $mysqli->prepare($sql) ;
    if(!$stmt){
      echo "Prepare failed: (". $mysqli->errno.") ".$mysqli->error."<br>";
    }
    $stmt->execute() ; 
    $result = $stmt->get_result();
    $fee = 0 ;
    if ($result->num_rows > 0 && $_POST['pick'] != 'Pick-up') {
      while($row = $result->fetch_assoc()) { 
        $distance = getDistance($_SESSION['x'], $_SESSION['y'], $row['ST_X(location)'], $row['ST_Y(location)'], 2) ;
        $fee = max(10 , $distance) ; 
      }
    }
    $ttl = round($subtotal + $fee) ; 
    $content = <<<EOF
              <input name = "shop" value = $shopname type = "hidden">
              <input name = "subttl" value = $subtotal type = "hidden">
              <input name = "fee" value = $fee type = "hidden">
              <input name = "ttl" value = $ttl type = "hidden">
              </tbody>
            </table>
          </div>
          <p>Subtotal :</p> $subtotal
          <p>Delivery fee : </p> $fee
          <p>Total Price : </p> $ttl
          <div class="modal-footer">
          <button type="submit" class="btn btn-default">Order</button>
          </div>
        </div>
    </form>
    EOF;
    echo $content ; 
     
?>