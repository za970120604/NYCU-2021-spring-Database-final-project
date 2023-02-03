<?php
    session_start();
    // var_dump($_SESSION) ; 
    // var_dump($_POST) ;
    $content = <<<EOF
    <form action = nav.php method = post>
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

    //fetch order info
    $detail  ;
    $mysqli = new mysqli("localhost","root","","database_hw2");
    $sql = 'SELECT detail FROM orders WHERE OID = '.$_POST['OID'];
    $stmt = $mysqli->prepare($sql) ;
    if(!$stmt){
      echo "Prepare failed: (". $mysqli->errno.") ".$mysqli->error."<br>";
    }
    $stmt->execute() ; 
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
      while($row = $result->fetch_assoc()) {
        $detail = $row['detail'];
      }
    }

    //parsing and printing
    // echo $detail ; 
    $detailarr = str_split($detail);
    // var_dump($detailarr) ; 

    $count = 0 ; 
    $substr = '' ;

    $subttl ; 
    $fee ; 
    $ttl ; 
    $search = array();

    for($i = 0 ; $i < strlen($detail) ; $i++){
      if($detailarr[$i] == '#' && $count == 0){
        $subttl = $substr ; 
        $substr = '' ; 
        $count += 1 ; 
      }
      else if($detailarr[$i] == '#' && $count == 1){
        $fee = $substr ; 
        $substr = '' ;
        $count += 1 ;
      }
      else if($detailarr[$i] == '#' && $count == 2){
        $ttl = $substr ; 
        $substr = '' ;
        $count += 1 ;
      }
      else if($detailarr[$i] == '#' && $count > 2){
        $split = strpos($substr, '/') ;
        $fid = substr($substr , 0 , $split) ; 
        $fnum = substr($substr , $split + 1) ;
        $search += array($fid => $fnum);
        $substr = '' ;
        $count += 1 ;
      }
      else{
        $substr = $substr.$detailarr[$i] ; 
      }
    }
    // var_dump($search) ;
    $sql = 'SELECT * FROM food WHERE id = ';
    foreach ($search as $key => $value){
      $sqli = $sql.$key ; 
      // echo $sqli ; 
      $stmt = $mysqli->prepare($sqli) ;
      if(!$stmt){
          echo "Prepare failed: (". $mysqli->errno.") ".$mysqli->error."<br>";
      }
      $stmt->execute() ;
      $result = $stmt->get_result();
      while($row = $result->fetch_assoc()) {
        $pictpe = 'picture_file_type' ; 
        $pic = 'picture' ; 
        $nn = 'name' ; 
        $price = 'price' ; 
        $content = <<<EOF
                    <tr>
                        <td><img src="data:$row[$pictpe];base64, $row[$pic]" width="50%" height="50%"></td>
                        <td align="center">$row[$nn]</td>
                        <td align="center">$row[$price]</td>
                        <td align="center">$value </td>
                    </tr>
                    EOF;
          echo $content;
      }
      $stmt->close() ;  
    }

    $content = <<<EOF
                </tbody>
            </table>
            </div>
            <p>Subtotal :</p> $subttl
            <p>Delivery fee : </p> $fee
            <p>Total Price : </p> $ttl
            <div class="modal-footer">
            <button type="submit" class="btn btn-default">Back To Home</button>
            </div>
        </div>
    </form>
    EOF ; 
    echo $content ;
?>