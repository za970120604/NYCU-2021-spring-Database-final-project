<?php 
  session_start()  ; 
  if (isset($_SESSION['search_info'])){
      unset($_SESSION['search_info']) ; 
  }
  if(isset($GLOBALS)){
    unset($GLOBALS) ; 
  }

  try{
    $mysqli = new mysqli("localhost","root","","database_hw2");
    $sql = 'SELECT shops.name as shopname , shops.category , shops.location FROM shops CROSS JOIN food WHERE food.shop_name = shops.name' ;
 
    $GLOBALS['sname'] = (!empty($_POST['name'])) ? "%".strval($_POST['name'])."%" : "%" ; 
    $GLOBALS['smeal'] = (!empty($_POST['meal'])) ? strval($_POST['meal']) : "%" ;
    $GLOBALS['scat'] = (!empty($_POST['category'])) ? strval($_POST['category']) : "%" ; 
    
    $sql = $sql . " and shops.name like ?" ; 
    $sql = $sql . " and food.name like ?" ; 
    $sql = $sql . " and shops.category like ?" ;

    $GLOBALS['money_case'] = -1 ;

    if(!empty($_POST['price1'])){
      if(!empty($_POST['price2'])){
        $GLOBALS['sp1'] = min($_POST['price1'] , $_POST['price2']);
        $GLOBALS['sp2'] = max($_POST['price1'] , $_POST['price2']) ;
        $sql = $sql . " and food.price >= ? and food.price <= ?" ;
        $GLOBALS['money_case'] = 0 ; 
      }
      else{ 
        $GLOBALS['sp1'] = $_POST['price1'] ; 
        $sql = $sql . " and food.price >= ?" ;
        $GLOBALS['money_case'] = 1 ; 
      }
    }
    else if(empty($_POST['price1']) && !empty($_POST['price2'])){
      $GLOBALS['sp2'] = $_POST['price2'] ;
      $sql = $sql . " and food.price <= ?" ;
      $GLOBALS['money_case'] = 2 ; 
    }
    
    $userhome = "POINT(".strval($_SESSION['x']). "," .strval($_SESSION['y']).")" ; 
    // echo $userhome ; 

    if($GLOBALS['money_case'] == 0){//name , meal , cat , p1 , p2
      if($_POST['dist'] == 'near'){
        $sql = $sql . " and ST_Distance_Sphere(shops.location , ".$userhome.") < 1000" ;
      }
      else if($_POST['dist'] == 'medium'){
        $sql = $sql . " and ST_Distance_Sphere(shops.location , ".$userhome.") >= 1000 and ST_Distance_Sphere(location , $userhome) < 3000" ;
      }
      else{
        $sql = $sql . " and ST_Distance_Sphere(shops.location , ".$userhome.") >= 3000" ;
      }
      $stmt = $mysqli->prepare($sql) ;
      $stmt->bind_param("sssii" , $GLOBALS['sname'] , $GLOBALS['smeal'] , $GLOBALS['scat'] , $GLOBALS['sp1'] , $GLOBALS['sp2']) ;   
    }
    else if($GLOBALS['money_case'] == 1){//name , meal , cat , p1
      if($_POST['dist'] == 'near'){
        $sql = $sql . " and ST_Distance_Sphere(shops.location , ".$userhome.") < 1000" ;
      }
      else if($_POST['dist'] == 'medium'){
        $sql = $sql . " and ST_Distance_Sphere(shops.location , ".$userhome.") >= 1000 and ST_Distance_Sphere(location , $userhome) < 3000" ;
      }
      else{
        $sql = $sql . " and ST_Distance_Sphere(shops.location , ".$userhome.") >= 3000" ;
      }
      $stmt = $mysqli->prepare($sql) ;
      $stmt->bind_param("sssi" , $GLOBALS['sname'] , $GLOBALS['smeal'] , $GLOBALS['scat'] , $GLOBALS['sp1'] ) ;
    }
    else if($GLOBALS['money_case'] == 2){//name , meal , cat , p2
      if($_POST['dist'] == 'near'){
        $sql = $sql . " and ST_Distance_Sphere(shops.location , ".$userhome.") < 1000" ;
      }
      else if($_POST['dist'] == 'medium'){
        $sql = $sql . " and ST_Distance_Sphere(shops.location , ".$userhome.") >= 1000 and ST_Distance_Sphere(shops.location , $userhome) < 3000" ;
      }
      else{
        $sql = $sql . " and ST_Distance_Sphere(shops.location , ".$userhome.") >= 3000" ;
      }
      $stmt = $mysqli->prepare($sql) ;
      $stmt->bind_param("sssi" , $GLOBALS['sname'] , $GLOBALS['smeal'] , $GLOBALS['scat'] , $GLOBALS['sp2']) ;
    }
    else{//name , meal , cat 
      if($_POST['dist'] == 'near'){ 
        $sql = $sql . " and ST_Distance_Sphere(shops.location , ".$userhome.") < 1000" ;
      }
      else if($_POST['dist'] == 'medium'){
        $sql = $sql . " and ST_Distance_Sphere(shops.location , ".$userhome.") >= 1000 and ST_Distance_Sphere(shops.location , $userhome) < 3000" ;
      }
      else{
        $sql = $sql . " and ST_Distance_Sphere(shops.location , ".$userhome.") >= 3000" ;
      }
      
      $stmt = $mysqli->prepare($sql) ;
      $stmt->bind_param("sss" , $GLOBALS['sname'] , $GLOBALS['smeal'] , $GLOBALS['scat'] ) ;
    }
    $stmt->execute() ; 
    $infos = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    $stmt -> close();
    $mysqli -> close();
    
    if(empty($infos)){
      $error = "No such shops available!!" ; 
      throw new Exception($error);
    }
    
    // echo var_dump($infos) ; 
    // echo "<br>" ; 
    $infoss = array() ; 

    foreach($infos as $unique){
      $uni = $unique['shopname'] ; 
      if(!isset($infoss[$uni])){
        $infoss[$uni]['shopname'] = $unique['shopname'] ; 
        $infoss[$uni]['category'] = $unique['category'] ; 
        $infoss[$uni]['location'] = $unique['location'] ; 
        $infoss[$uni]['dist'] = $_POST['dist'] ; 
      }
    } 
    
    $_SESSION['search_info'] = $infoss ; 
    
    echo "<script language= javascript>
    window.location.replace('nav.php');
    </script>" ;
  } 
  catch (Exception $e) {
    $err = $e->getMessage() ; 
    echo "<script language= javascript>
      alert('Caught exception: = $err ') ; 
      window.location.replace('nav.php');
    </script>" ; 
  }
?>
