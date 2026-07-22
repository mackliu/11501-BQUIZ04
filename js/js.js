// JavaScript Document
function lof(x)
{
	location.href=x
}

function del(model,id){
    $.post("./api/del.php",{model,id},()=>{
        location.reload();
    })
}
