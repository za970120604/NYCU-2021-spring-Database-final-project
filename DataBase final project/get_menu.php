<?php
    session_start() ;
    if(isset($_SESSION['menu'])){
        unset($_SESSION['menu']) ; 
    }
    // echo var_dump($_POST) ; 
    foreach($_SESSION['search_info'] as $target){
        if(isset($_POST[$target['shopname']])){
           $mysqli = new mysqli("localhost","root","","database_hw2");
           $sql = 'SELECT food.name , price , quantity , picture , picture_file_type FROM shops CROSS JOIN food WHERE food.shop_name = shops.name and food.shop_name = ?' ;
        //    echo $sql; 
           $stmt = $mysqli->prepare($sql) ;
           $stmt->bind_param("s" , $target['shopname']) ;
           $stmt->execute() ; 
           $menu = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
           $stmt -> close();
           $mysqli -> close(); 
           $_SESSION['menu'] = $menu ; 
           $_SESSION['menu'] += array('-1' => $target['shopname']); 
           break ; 
        }
    }
    //echo var_dump($_SESSION['menu']) ; 
    echo "<script language= javascript> 
      window.location.replace('nav.php');
    </script>"
?>