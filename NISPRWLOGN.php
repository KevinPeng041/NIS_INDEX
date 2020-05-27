<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/html">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>NISUSERLOGIN</title>
    <script type="text/javascript" src="jquery-3.4.1.js"></script>
    <link rel="stylesheet" href="http://localhost/bootstrap-4.3.1-dist/css/bootstrap.min.css">
    <script src="bootstrap-4.3.1-dist/js/bootstrap.min.js" type="text/javascript"></script>
    <script src="crypto-js.js"></script>
    <script src="AESCrypto.js"></script>
</head>
<script>
    $(function () {
           $("#passwd").keypress(function (e) {
               $("#CapsLockMsg").toggle(
                   //沒按下Shift鍵，卻輸入大寫字母
                   (e.which >= 65 && e.which <= 90 && !e.shiftKey) ||
                   //按下Shift鍵時，卻輸入小寫字母
                   (e.which >= 97 && e.which <= 122 && e.shiftKey)
               );
           }).focusout(function () { $("#CapsLockMsg").hide(); });
       });

    $(document).ready(function () {


        $("#sidName").attr('disabled',true);
        $("#passwd").attr('disabled',true);
        $("#loadword").hide();
        $("#acount").blur(function(){
            if(this.value==""){
                return false;
            }
            $("#loadword").show();
            $.ajax({
                url:'/webservice/NISPWSCKUSR.php?str='+AESEnCode('sIdUser='+ paddingLeft($("#acount").val().toUpperCase(),7)),
                type:'GET',
                dataType:'text',
                success:function (data) {
                    var re=AESDeCode(data);
                    console.log(JSON.parse(re));
                    var json=(JSON.parse(re))[0];
                    if(json.Errorcode)
                    {
                        alert(json.Errormsg);
                        return false;
                    }
                    if(json.Status==='Error'){
                        alert('帳號錯誤');
                        $("#loadword").hide();
                        return false;
                    }

                    if(json.Status==="Success"){
                        $("#sidName").val(json.NmUser);
                        $("#passwd").attr('disabled',false);
                        $("#passwd").focus();
                        $("#loadword").hide();
                    }

                },error:error
            });
        });
        $("#passwd").bind('input propertychange change',function () {
           var val=this.value;
           var arr=val.split("");
            var a_Regex=/(^[a-z])/;
            var A_Regex=/(^[A-Z])/;
            if(arr[arr.length-1].match(a_Regex)){
                $("#CapsLockMsg").hide();
            } else if(arr[arr.length-1].match(A_Regex)){
                $("#CapsLockMsg").show();
            }
        });

    });
    function paddingLeft(str,lenght){
        if(str.length >= lenght)
            return str;
        else
            return paddingLeft("0" +str,lenght);

    }

    function formSubmit(){
        var passwd=$("#passwd").val();
        var acount=$("#acount").val().toUpperCase();
        $.ajax({
            url:'/webservice/NISPWSCKPWD.php?str='+AESEnCode('sIdUser='+paddingLeft(acount,7)+'&sPassword='+passwd),
            type:'GET',
            dataType:'text',
            success:function (data) {
               var paswd=AESDeCode(data);
                console.log(paswd);
               if($("#passwd").val()==''){
                    $("#passwd").focus();
                    alert('密碼不得為空');
                    return false;
                }
                else if(paswd=='false'){
                    alert('密碼錯誤請重新檢查密碼');
                    return false;
                }else if(paswd=='true') {
                    window.open("./NISMENU.php?str="+AESEnCode('sIdUser='+acount+'&passwd='+$("#passwd").val()+"&user="+$("#sidName").val()),"系統作業選單",'width=500px,height=350px,scrollbars=yes,resizable=no');
                   window.close();
                }
            },error:error
        });
    }
    function error(XMLHttpResponse,textStatus,errorThrown) {
        console.log("1 返回失敗,XMLHttpResponse.readyState:"+XMLHttpResponse.readyState+XMLHttpResponse.responseText);
        console.log("2 返回失敗,XMLHttpResponse.status:"+XMLHttpResponse.status);
        console.log("3 返回失敗,textStatus:"+textStatus);
        console.log("4 返回失敗,errorThrown:"+errorThrown);
    }
</script>
<style>
    h2{
        margin-top: 40px;
    }


    #acount{
        text-transform:uppercase;
    }

    .word {
        color: #168;
        display: inline-block;
        text-align: center;
        font-size: 15px;
        line-height: 20px;
        font-family: 微軟正黑體, arial;
    }
</style>
<body>
<div class="container">
    <div class="row">
        <div class="col-xs-12 col-sm-8 col-md-6 col-sm-offset-2 col-md-offset-3">
            <h2>登入</h2>

            <form id="siduser" >
            <div class="row">
                <div class="col-xs-12 col-sm-12 col-md-12">
                    <div class="form-group">
                        帳號:<input type="text" class="form-control input-lg" id="acount"  value=""  placeholder="輸入帳號" name="sIduser" autocomplete="off">
                    </div>
                </div>
                <div class="col-xs-12 col-sm-12 col-md-12">
                    <div class="form-group">
                        姓名:<input type="text" class="form-control input-lg" id="sidName"  value="" name="sIdname" placeholder="使用者姓名">
                    </div>
                </div>
                <div class="col-xs-12 col-sm-12 col-md-12">
                    <div class="form-group">
                        密碼:<input type="password" class="form-control input-lg" id="passwd"  value="" name="sIdpasswd" placeholder="密碼" >
                    </div>
                </div>
            </div>

              <div class="row">
                    <div class="col-xs-6 col-md-6">
                        <input  type="button" class="btn btn-primary btn-block btn-lg" value="確認" onclick="formSubmit()"><span id="CapsLockMsg" style="display: none">提醒:大寫鎖定啟用中</span>
                    </div>
                    <div id="loadword" class="word col-xs-6 col-md-6" >帳號驗證中.....</div>
              </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
