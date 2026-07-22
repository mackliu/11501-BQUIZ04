<?php include_once 'db.php'    ;?>

    <table class="all">
    <?php 
        $bigs=$Type->all(['main_id'=>0]);
        foreach($bigs as $big):
        ?>

        <tr class="tt">
            <td><?= $big['name'] ?></td>
            <td>
                <button onclick="editType(<?= $big['id'] ?>)">修改</button>
                <button onclick="del('Type',<?= $big['id'];?>)">刪除</button>
            </td>
        </tr>
        <?php 
            if($Type->count(['main_id'=>$big['id']])>0):
                $mids=$Type->all(['main_id'=>$big['id']]);
                foreach($mids as $mid):
            ?>
        <tr class="pp">
            <td class='ct'><?= $mid['name'] ?></td>
            <td>
                <button onclick="editType(<?= $mid['id'] ?>)">修改</button>
                <button onclick="del('Type',<?= $mid['id'] ?>)">刪除</button>
            </td>
        </tr>

        <?php
                endforeach;
            endif;
         endforeach ;?>
        
    </table>