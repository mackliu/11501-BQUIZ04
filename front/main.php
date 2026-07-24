<?php 
$type_id=$_GET['type']??0;
$nav_str="全部商品";

$items=$Item->all(['sh'=>1]);

if($type_id!=0){
 $type=$Type->find($type_id);
 if($type['main_id']==0){ //是大分類
    $nav_str=$type['name'];

    $items=$Item->all(['sh'=>1,'big'=>$type['id']]);

 }else{  //是中分類
    //取出大分類
    $big=$Type->find($type['main_id']);
    $nav_str=$big['name']. " > ".$type['name'];

    $items=$Item->all(['sh'=>1,'mid'=>$type['id']]);
 }
}

?>

<h2><?= $nav_str ?></h2>
<?php foreach($items as $item):?>

<div class="all" style='display:flex;height:180px'>
    <div style="width:35%;display:flex;align-items:center;justify-content:center" class='pp'>
        <table>
            <tr>
                <td>
                    <a href="?do=detail&id=<?= $item['id'] ?>">
                        <img src="./upload/<?= $item['img'] ?>" style="width:180px;height:120px;">
                    </a>
                </td>
            </tr>
        </table>
    </div>
    <div style="width:65%">
        <table style='height:100%'>
            <tr>
                <td class="tt ct"><?= $item['name'] ?></td>
            </tr>
            <tr>
                <td class="pp">
                    價錢:<?= $item['price'] ?>
                    <img src="./icon/0402.jpg" style="float:right" onclick="location.href='?do=buycart&item=<?= $item['id'] ?>&qt=1'">
                </td>
            </tr>
            <tr>
                <td class="pp">規格:<?= $item['spec'] ?></td>
            </tr>
            <tr>
                <td class="pp">簡介:<?= mb_substr($item['intro'],0,25) ?>...</td>
            </tr>
        </table>
    </div>
</div>

<?php endforeach;?>    

