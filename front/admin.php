<table class="all">
    <tr>
        <td class="tt ct">帳號</td>
        <td class="pp"><input type="text" name="acc" id="acc"></td>
    </tr>
    <tr>
        <td class="tt ct">密碼</td>
        <td class="pp"><input type="password" name="pw" id="pw"></td>
    </tr>
    <tr>
        <td class="tt ct">驗證碼</td>
        <td class="pp">
            <span>
            <?php
                $a=rand(10,99);
                $b=rand(10,99);
                $_SESSION['ans']=$a+$b;
                echo " $a + $b =";
            ?>
            </span>
            <input type="text" name="code" id="code">
        </td>
    </tr>
</table>
<div class='ct'><button onclick="send()">確認</button></div>

<script>
function send(){
    let code=$("#code").val();
    let user={acc:$("#acc").val(),
               pw:$("#pw").val()}
    $.get("./api/chk_ans.php",{code},(res)=>{
        if(parseInt(res)){
            $.get("./api/chk_admin.php",user,(res)=>{
                if(parseInt(res)){
                    location.href='back.php';
                }else{
                    alert("帳號或密碼錯誤\n請重新登入")
                    location.reload()        
                }
            })
        }else{
            alert("對不起，您輸入的驗證碼有誤\n請重新登入")
            location.reload()
        }
    })               
}
</script>