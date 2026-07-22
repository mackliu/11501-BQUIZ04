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
<div class="item-list">

</div>
 <script>
getItemList();
    
 function getItemList(){
    $.get("./api/get_item_list.php",(list)=>{
        $(".item-list").html(list)
    })
}   
 </script>