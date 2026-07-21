<?php include_once "db.php";

if($Mem->count($_GET)){
    echo 1;
    $_SESSION['mem']=$_GET['acc'];
}else{
    echo 0;
}