<h2 class="ct">商品分類</h2>
<div class="ct">
    <label for="big">新增大分類</label>
    <input type="text" name="big" id="big">
    <button onclick="addBig()">新增</button>
</div>
<!-- div.ct>select+input:text+button -->
 <div class="ct">
    <label for="bigSelect">新增中分類</label>
    <select name="big_select" id="bigSelect"></select>
    <input type="text" name="mid" id="mid">
    <button onclick="addMid()">新增</button>
</div>
<div class="type-list">

</div>
<script>
getBigs();
getTypeList();

function addBig(){
    let big=$("#big").val()
    $.post("./api/save_type.php",{'name':big,
                               'main_id':0},(res)=>{
        $("#big").val('')
        getBigs()
        getTypeList()
    })
}
function addMid(){
    let mid=$("#mid").val()
    let main_id=$("#bigSelect").val()
    $.post("./api/save_type.php",{'name':mid,
                               'main_id':main_id},(res)=>{
        $("#mid").val('')
        getTypeList()
    })
}

/* 可以合併兩個方法為一個

  function addType(type){
    let name='';
    let main_id=0;
    switch(type){
        case 'big':
            name=$("#big").val();
        break;
        case 'mid':
            name=$("#mid").val();
            main_id=$("#bigSelect").val()
        break;
    }
    $.post("./api/save_type.php",{name,main_id},(res)=>{
        $("#mid,#big").val('')
        getBigs()
        getTypeList()
    })

} */

function getTypeList(){
    $.get("./api/get_type_list.php",(list)=>{
        $(".type-list").html(list)
    })
}
function getBigs(){
    $.get("./api/get_bigs.php",(bigs)=>{
        $("#bigSelect").html(bigs);
    })
}    
</script>



<h2 class="ct">商品管理</h2>
<div class="ct">
    <button onclick="location.href='?do=add_item'">新增商品</button>
</div>
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
        location.reload()
    })
}
</script>