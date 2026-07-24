<?php 
/* if(!isset($_SESSION['cart'])){
    $_SESSION['cart'];
} */

if(isset($_GET['item'])){
    $_SESSION['cart'][$_GET['item']]=$_GET['qt'];
}

if(!isset($_SESSION['mem'])){
    to("?do=login");
    exit();
}
?>

<h2 class="ct"><?= $_SESSION['mem'] ?>的購物車</h2>

<?php

dd($_SESSION['cart']);

