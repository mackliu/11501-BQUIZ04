<?php include_once "db.php";

if($Admin->count($_GET)){
    echo 1;
    $_SESSION['admin']=$_GET['acc'];
}else{
    echo 0;
}