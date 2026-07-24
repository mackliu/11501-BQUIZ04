<?php 

if(isset($_GET['item'])){
    $_SESSION['cart'][$_GET['item']]=$_GET['qt'];
}

if(!isset($_SESSION['mem'])){
    to("?do=login");
    exit();
}
?>

<h2 class="ct"><?= $_SESSION['mem'] ?>的購物車</h2>

<!-- table.all>tr.tt.ct*2>td*7 -->
 <table class="all">
    <tr class="tt ct">
        <td>編號</td>
        <td>商品名稱</td>
        <td>數量</td>
        <td>庫存量</td>
        <td>單價</td>
        <td>小計</td>
        <td>刪除</td>
    </tr>
    <?php
    foreach($_SESSION['cart'] as $id => $qt):
        $item=$Item->find($id);
    ?>
    <tr class="pp ct">
        <td><?= $item['no'] ?></td>
        <td><?= $item['name'] ?></td>
        <td><?= $qt ?></td>
        <td><?= $item['qt'] ?></td>
        <td><?= $item['price'] ?></td>
        <td><?= $item['price'] * $qt ?></td>
        <td>
            <img src="./icon/0415.jpg" onclick="delItem(<?= $id ?>)">
        </td>
    </tr>
    <?php endforeach; ?>
 </table>

 <div class="ct">
    <img src="./icon/0411.jpg" onclick="location.href='?'">
    <img src="./icon/0412.jpg" onclick="location.href='?do=checkout'">
</div>

<script>
function delItem(id){
    $.post("./api/del_item.php",{id},()=>{
        location.href='?do=buycart'
    })
}
</script>

