<h2 class="ct">填寫資料</h2>
<?php
$mem=$Mem->find(['acc'=>$_SESSION['mem']]);
?>
<table class="all">
    <tr>
        <td class="tt ct">會員帳號</td>
        <td class="pp"><?= $mem['acc'] ?></td>
    </tr>
    <tr>
        <td class="tt ct">姓名</td>
        <td class="pp"><input type="text" name="name" id="name" value="<?= $mem['name'] ?>"></td>
    </tr>
    <tr>
        <td class="tt ct">電子信箱</td>
        <td class="pp"><input type="text" name="email" id="email" value="<?= $mem['email'] ?>"></td>
    </tr>
    <tr>
        <td class="tt ct">聯絡地址</td>
        <td class="pp"><input type="text" name="addr" id="addr" value="<?= $mem['addr'] ?>"></td>
    </tr>
    <tr>
        <td class="tt ct">聯絡電話</td>
        <td class="pp"><input type="text" name="tel" id="tel" value="<?= $mem['tel'] ?>"></td>
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
    $sum=0;
    foreach($_SESSION['cart'] as $id => $qt):
        $item=$Item->find($id);
    ?>
    <tr class="pp ct">
        <td><?= $item['name'] ?></td>
        <td><?= $item['no'] ?></td>
        <td><?= $qt ?></td>
        <td><?= $item['price'] ?></td>
        <td><?= $item['price'] * $qt ?></td>
    </tr>
    <?php
        $sum=$sum+($item['price'] * $qt);
    endforeach; ?>
 </table>
<div class="all tt ct">總價：<?= $sum ?></div>
<div class="ct">
    <button onclick="send()">確定送出</button>
    <button onclick="location.href='?do=buycart'">返回修改訂單</button>
</div>
<script>
function send(){
    let user={
        name:$("#name").val(),
        email:$("#email").val(),
        addr:$("#addr").val(),
        tel:$("#tel").val()
    }

    $.post("./api/checkout.php",user,()=>{
        alert("訂購成功\n感謝您的選購")
    })
}    
</script>