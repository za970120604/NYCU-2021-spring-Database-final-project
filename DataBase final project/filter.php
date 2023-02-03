<?php
    session_start() ; 
    if(isset($_SESSION['order'])){
        unset($_SESSION['order']) ; 
    } 
    $sql = 'SELECT * from orders where buyer = "'.$_SESSION['account'].'"' ; 
    if($_POST['type'] == 'All'){
        $sql = $sql ; 
    }
    else if($_POST['type'] == 'Finished'){
        $sql = $sql.' and state = "Finished"' ; 
    }
    else if($_POST['type'] == 'Not Finish'){
        $sql = $sql.' and state = "Not Finish"' ;
    }
    else{
        $sql = $sql.' and state = "Canceled"' ;
    }
    $_SESSION['order'] = $sql ; 
    $_SESSION['order_search_type'] = $_POST['type'] ; 

    echo "<script language= javascript>
      window.location.replace('nav.php');
    </script>" ; 
?>
