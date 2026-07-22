<?php include_once 'db.php';

$mids=$Type->all(['main_id'=>$_GET['big']]);
foreach($mids as $mid){
    echo "<option value='{$mid['id']}'>";
    echo $mid['name'];
    echo "</option>";
}