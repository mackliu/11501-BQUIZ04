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
            <img src="#" alt="" id="captcha" onclick="getCode()"></img>
            <input type="text" name="code" id="code">
        </td>
    </tr>
</table>
<div class='ct'><button onclick="send()">確認</button></div>

<script>
getCode();
function getCode(){
    $.get("./api/captcha_image.php?reload=1",(res)=>{
        if(res.success){
            $("#captcha").attr("src",res.image)
        }
    })
}
function send(){
    let captcha=$("#code").val();
    let user={acc:$("#acc").val(),
               pw:$("#pw").val()}
    $.post("./api/captcha_verify.php",{captcha},(res)=>{
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
/* function send(){
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
} */
</script>