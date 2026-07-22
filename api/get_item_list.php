<?php include_once "db.php";?>
<table class="all">
    <tr class="tt ct">
        <td>編號</td>
        <td>商品名稱</td>
        <td>庫存量</td>
        <td>狀態</td>
        <td style="width:150px;">操作</td>
    </tr>
    <?php
    $items=$Item->all();
    foreach($items as $item):
    ?>
    <tr class="pp ct">
        <td><?= $item['no'] ?></td>
        <td><?= $item['name'] ?></td>
        <td><?= $item['qt'] ?></td>
        <td><?= ($item['sh']==1)?'販售中':'已下架'; ?></td>
        <td>
            <button onclick="location.href='?do=edit_item&id=<?= $item['id'] ?>'">修改</button>
            <button onclick="del('Item',<?= $item['id']; ?>)">刪除</button>
            <button onclick="sh(1,<?= $item['id']; ?>)">上架</button>
            <button onclick="sh(2,<?= $item['id']; ?>)">下架</button>
        </td>
    </tr>
    <?php endforeach ;?>
</table>
<script>

function sh(type,id)    {
    $.post("./api/show.php",{type,id},()=>{
        getItemList()
        //location.reload()
    })
}
</script>