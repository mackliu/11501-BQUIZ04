<?php include_once 'db.php';
$item=$Item->find($_POST['id']);

switch($_POST['type']){
    case 1:
        $item['sh']=1;
    break;
    case 2:
        $item['sh']=0;
    break;
}

$Item->save($item);
