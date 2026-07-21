<?php include_once "db.php";

if($_SESSION['ans']==$_GET['code']){
    echo 1;
}else{
    echo 0;
}