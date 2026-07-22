<?php 
$item=$Item->find($_GET['id']);
?>

<h2 class="ct">修改商品</h2>
<!-- form:post>table.all>tr*9>td.tt.ct+td.pp>input:text -->
 <form action="./api/save_item.php" method="post" enctype="multipart/form-data">
    <table class="all">
        <tr>
            <td class="tt ct">所屬大分類</td>
            <td class="pp">
                <select name="big" id="big"></select>
            </td>
        </tr>
        <tr>
            <td class="tt ct">所屬中分類</td>
            <td class="pp">
                <select name="mid" id="mid"></select>
            </td>
        </tr>
        <tr>
            <td class="tt ct">商品編號</td>
            <td class="pp"><?= $item['no']; ?></td>
        </tr>
        <tr>
            <td class="tt ct">商品名稱</td>
            <td class="pp"><input type="text" name="name" id="name" value="<?= $item['name'] ?>"></td>
        </tr>
        <tr>
            <td class="tt ct">商品價格</td>
            <td class="pp"><input type="text" name="price" id="price" value="<?= $item['price'] ?>"></td>
        </tr>
        <tr>
            <td class="tt ct">規格</td>
            <td class="pp"><input type="text" name="spec" id="spec" value="<?= $item['spec'] ?>"></td>
        </tr>
        <tr>
            <td class="tt ct">庫存量</td>
            <td class="pp"><input type="text" name="qt" id="qt" value="<?= $item['qt'] ?>"></td>
        </tr>
        <tr>
            <td class="tt ct">商品圖片</td>
            <td class="pp"><input type="file" name="img" id="img"></td>
        </tr>
        <tr>
            <td class="tt ct">商品介紹</td>
            <td class="pp">
                <textarea name="intro" id="intro"><?= $item['intro'] ?></textarea>
            </td>
        </tr>
    </table>
    <div class="ct">
        <input type="hidden" name="id" value='<?= $item['id'] ?>'>
        <input type="submit" value="修改">
        <input type="reset" value="重置">
        <input type="button" value="返回">
    </div>
 </form>
 
<script>
getBigs()


let selectedStatus=true;

$("#big").on('change',function(){
    getMid($(this).val())
})

function getBigs(){
    $.get("./api/get_bigs.php",(bigs)=>{
        $("#big").html(bigs);
        $("#big option[value=<?= $item['big'] ?>]").prop('selected',true)
        getMid($("#big").val())
    })
}

function getMid(big){
    $.get("./api/get_mids.php",{big},(mids)=>{
        $("#mid").html(mids);
        if(selectedStatus){ //增加狀態判斷,避免中分類選單一直選到商品的中分類項目,可不寫這段判斷
            $("#mid option[value=<?= $item['mid'] ?>]").prop('selected',true)
            selectedStatus=false;
        }
    })
}
</script>