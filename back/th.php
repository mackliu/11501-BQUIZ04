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
    <table class="all">
        <tr class="tt">
            <td></td>
            <td>
                <button>修改</button>
                <button>刪除</button>
            </td>
        </tr>
        <tr class="pp">
            <td></td>
            <td>
                <button>修改</button>
                <button>刪除</button>
            </td>
        </tr>
    </table>
</div>
<script>
getBigs();    
function addBig(){
    let big=$("#big").val()
    $.post("./api/save_type.php",{'name':big,
                               'main_id':0},(res)=>{
        $("#big").val('')
        getBigs()
    })
}
function addMid(){
    let mid=$("#mid").val()
    let main_id=$("#bigSelect").val()
    $.post("./api/save_type.php",{'name':mid,
                               'main_id':main_id},(res)=>{
        $("#mid").val('')
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
    })

} */


function getBigs(){
    $.get("./api/get_bigs.php",(bigs)=>{
        $("#bigSelect").html(bigs);
    })
}    
</script>



<h2 class="ct">商品管理</h2>
<div class="ct">
    <button>新增商品</button>
</div>
<table class="all">
    <tr class="tt ct">
        <td>編號</td>
        <td>商品名稱</td>
        <td>庫存量</td>
        <td>狀態</td>
        <td>操作</td>
    </tr>
    <tr class="pp ct">
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td>
            <button>修改</button>
            <button>刪除</button>
            <button>上架</button>
            <button>下架</button>
        </td>
    </tr>
</table>