<?php include_once "db.php";

$db=${$_POST['model']};
$db->del($_POST['id']);