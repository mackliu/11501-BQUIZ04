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
<?php dd($items);?>
