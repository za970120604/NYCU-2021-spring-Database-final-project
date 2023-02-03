<?php
    // var_dump($_POST) ; 
    session_start() ; 
    $pattern = "/^[0-9]*[1-9][0-9]*$/" ; 
    if(preg_match($pattern , $_POST['add'])){
        $mysqli = new mysqli("localhost","root","","database_hw2");
        $sql = 'UPDATE users SET wallet = '.$_SESSION['wallet'].'+'.'?'.' WHERE account = "'.$_SESSION['account'].'"';
        $stmt = $mysqli->prepare($sql) ; 
        $stmt->bind_param("s" , $_POST['add']) ;
        if(!$stmt){
            echo "Prepare failed: (". $mysqli->errno.") ".$mysqli->error."<br>";
        }
        $stmt->execute() ;
        //lee
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

        date_default_timezone_set('Asia/Taipei');
        $current_time = date("Y-m-d H:i:s",time());

        $stmt = $conn->prepare(
          "insert into records(action,time,buyer,seller,amount_change,type)
          values(:action,:time,:buyer,:seller,:amount_change,:type)"
        );
        
        $stmt->execute(
          array(
            "action"=>"Recharge",
            "time"=>$current_time,
            "buyer"=>$_SESSION['account'],
            "seller"=>$_SESSION['account'],
            "amount_change"=>"+".strval($_POST['add']),
            "type"=>"recharge"
          )
        );
        //lee
        $err = "儲值成功" ;
        echo "<script language= javascript>
          alert('系統提示 = $err ') ; 
          window.location.replace('nav.php');
        </script>" ; 
    }
    else{
        $err = "加值非正整數" ;
        echo "<script language= javascript>
          alert('系統提示 = $err ') ; 
          window.location.replace('nav.php');
        </script>" ;
    }

?>