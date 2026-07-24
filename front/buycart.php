<?php 
if(!isset($_SESSION['mem'])){
    to("?do=login");
    exit();
}
?>

<h2 class="ct"><?= $_SESSION['mem'] ?>的購物車</h2>

購買<?= $_GET['item'] ?>,數量<?= $_GET['qt'] ?>

