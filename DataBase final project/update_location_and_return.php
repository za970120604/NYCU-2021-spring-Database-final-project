<?php
  session_start();
    // echo var_dump($_SESSION) ;

  $conn = mysqli_connect('localhost' , "root" , "" , "database_hw2") ; 

  if(!$conn){
      echo mysqli_connct_error() ; 
  }
  $new_longitude = mysqli_real_escape_string($conn , $_POST['newlongitude']) ; 
  $new_latitude = mysqli_real_escape_string($conn , $_POST['newlatitude']) ; 
  $pwd = $_SESSION['Password'] ; 


  try {
    $error  ; 
    if(!preg_match('/^(\+|-)?(?:90(?:(?:\.0{1,6})?)|(?:[0-9]|[1-8][0-9])(?:(?:\.[0-9]{1,6})?))$/', $new_latitude)){
      $error = "wrong format of new latitude!" ; 
      throw new Exception($error);
    }
    else if(!preg_match('/^(\+|-)?(?:180(?:(?:\.0{1,6})?)|(?:[0-9]|[1-9][0-9]|1[0-7][0-9])(?:(?:\.[0-9]{1,6})?))$/', $new_longitude)){
      $error = "wrong format of new longitude!" ; 
      throw new Exception($error);
    }
   
    $sql = "UPDATE users SET location = POINT($new_longitude , $new_latitude) WHERE password = '$pwd' " ;
    $result = mysqli_query($conn , $sql) ; 
    mysqli_close($conn)  ;
    echo "<script language= javascript>
      alert('成功更新個人位置');
      window.location.replace('nav.php');
    </script>" ; 
  } 
  catch (Exception $e) {
    $err = $e->getMessage() ; 
    echo "<script language= javascript>
      alert('錯誤更新值: = $err ') ; 
      window.location.replace('nav.php');
    </script>" ; 
  }
?>
