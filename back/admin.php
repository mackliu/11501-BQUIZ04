<div class="ct">
    <button onclick="location.href='?do=add_admin'">新增管理員</button>
</div>
<table class="all">
    <tr class="tt ct">
        <td>帳號</td>
        <td>密碼</td>
        <td>管理</td>
    </tr>
    <?php 
    $users=$Admin->all();
    foreach($users as $user):

    ?>
    <tr class="pp ct">
        <td><?= $user['acc'] ?></td>
        <td><?= str_repeat("*",mb_strlen($user['pw'])) ?></td>
        <td>
            <?php
            if($user['acc']!='admin'):
            ?>
            <button onclick="location.href='?do=edit_admin&id=<?= $user['id'] ?>'">修改</button>
            <button onclick="del('Admin',<?= $user['id'] ?>)">刪除</button>
            <?php else:?>
                此號號為最高權限
            <?php endif;?>
        </td>
    </tr>
    <?php   endforeach;    ?>
</table>
<div class="ct">
    <button onclick="location.href='index.php'">返回</button>
</div>

<script>
function del(model,id){
    $.post("./api/del.php",{model,id},()=>{
        location.reload();
    })
}

</script>