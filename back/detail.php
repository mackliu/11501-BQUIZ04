<?php 
$order=$Order->find($_GET['id']);

?>
<h2 class="ct">訂單編號<span style="color:red"><?= $order['no'] ?></span>的詳細資料</h2>
<table class="all">
    <tr>
        <td class="tt ct">會員帳號</td>
        <td class="pp"><?= $order['account'] ?></td>
    </tr>
    <tr>
        <td class="tt ct">姓名</td>
        <td class="pp"><?= $order['name'] ?></td>
    </tr>
    <tr>
        <td class="tt ct">電子信箱</td>
        <td class="pp"><?= $order['email'] ?></td>
    </tr>
    <tr>
        <td class="tt ct">聯絡地址</td>
        <td class="pp"><?= $order['addr'] ?></td>
    </tr>
    <tr>
        <td class="tt ct">聯絡電話</td>
        <td class="pp"><?= $order['tel'] ?></td>
    </tr>
</table>
<table class="all">
    <tr class="tt ct">
        <td>商品名稱</td>
        <td>編號</td>
        <td>數量</td>
        <td>單價</td>
        <td>小計</td>
    </tr>
    <?php
    $cart=unserialize($order['items']);
    foreach($cart as $id => $qt):
        $item=$Item->find($id);
    ?>
    <tr class="pp ct">
        <td><?= $item['name'] ?></td>
        <td><?= $item['no'] ?></td>
        <td><?= $qt ?></td>
        <td><?= $item['price'] ?></td>
        <td><?= $item['price'] * $qt ?></td>
    </tr>
    <?php endforeach;?>
</table>
<div class="all tt ct">總價：<?= $order['sum'] ?></div>
<div class="ct"><button onclick="location.href='?do=order'">返回</button></div>