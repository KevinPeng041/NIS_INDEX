function paddingLeft(str,lenght){
    /*向左補0*/
    if(str.length >= lenght)
        return str;
    else
        return paddingLeft("0" +str,lenght);
}
function UrlCheck(Account,PassWd) {
    let url="/webservice/NISPWSCKPWD.php?str="+AESEnCode('sIdUser='+Account+'&sPassword='+PassWd);
    let re='';
    $.ajax({
        url:url,
        type:"POST",
        async:false,
        dataType: 'text',
        success:function (data) {
            re=data;
        },error:function (XMLHttpResponse,textStatus,errorThrown) {
            console.log(
                "1 返回失敗,XMLHttpResponse.readyState:"+XMLHttpResponse.readyState+XMLHttpResponse.responseText+
                "2 返回失敗,XMLHttpResponse.status:"+XMLHttpResponse.status+
                "3 返回失敗,textStatus:"+textStatus+
                "4 返回失敗,errorThrown:"+errorThrown
            );
        }
    });
    return re;
}