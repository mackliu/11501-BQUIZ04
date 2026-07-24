<?php 
$item=$Item->find($_GET['id']);
?>
<h2 class="ct"><?= $item['name'] ?></h2>
<div class="all" style='display:flex;height:300px'>
    <div style="width:65%;display:flex;align-items:center;justify-content:center" class='pp'>
        <table>
            <tr>
                <td>
                    <img src="./upload/<?= $item['img'] ?>" style="width:280px;height:220px;">
                </td>
            </tr>
        </table>
    </div>
    <div style="width:35%">
        <table style='height:100%'>
            <tr class="pp">
                <td>
                    分類:<?= $Type->find($item['big'])['name'].">".$Type->find($item['mid'])['name']; ?>
                </td>
            </tr>
            <tr>
                <td class="pp">
                    編號:<?= $item['no'] ?>
                </td>
            </tr>
            <tr>
                <td class="pp">
                    價格:<?= $item['price'] ?>
                </td>
            </tr>
            <tr>
                <td class="pp">詳細說明:<?= $item['intro'] ?></td>
            </tr>
            <tr>
                <td class="pp">庫存量:<?= $item['qt'] ?></td>
            </tr>
        </table>
    </div>
</div>
<div class="all tt ct">
    <input type="number" class="item-qt" value='1' style='width:60px'>
    <img src="./icon/0402.jpg"  onclick="location.href='?do=buycart'">
</div>
<div class="ct"><button onclick="location.href='?'">返回</button></div>