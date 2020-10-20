<?php
include "NISPWSIFSCR.php";
$str=$_GET["str"];
$replaceSpace=str_replace(' ','+',$str);//空白先替換+
$EXPLODE_data=explode('&',AESDeCode($replaceSpace));

$sfm_STR=$EXPLODE_data[0];
$sIdUser_STR=$EXPLODE_data[1];
$passwd_STR=$EXPLODE_data[2];
$user_STR=$EXPLODE_data[3];

$sfm_STR_value=explode('=',$sfm_STR);
$sIdUser_value=explode('=',$sIdUser_STR);
$passwd_value=explode('=',$passwd_STR);
$user_value=explode('=',$user_STR);

$sfm=trim($sfm_STR_value[1]);
$sIdUser=trim($sIdUser_value[1]);/*帳號*/
$passwd=trim($passwd_value[1]);/*密碼*/
$sUr=trim($user_value[1]);/*使用者*/

$parameter='sfm='.$sfm.'&sIdUser='.$sIdUser.'&passwd='.$passwd."&user=".$sUr;

?>
<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/html">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>NISUSERLOGIN</title>
    <script type="text/javascript" src="jquery-3.4.1.js"></script>
    <link rel="stylesheet" href="bootstrap-4.3.1-dist/css/bootstrap.min.css">
    <script src="bootstrap-4.3.1-dist/js/bootstrap.min.js" type="text/javascript"></script>
    <script src="crypto-js.js"></script>
    <script src="AESCrypto.js"></script>
</head>
<script>
    let sfm="<?php echo $sfm?>";
    if (sfm !=""){
        window.location.href="NIS/"+sfm+"/NISPRW"+sfm+".php?str="+AESEnCode('<?PHP echo $parameter?>');
    }else {
        $(document).ready(function () {
         /*   $("#passwd").keypress(function (e) {
                $("#CapsLockMsg").toggle(
                    //沒按下Shift鍵，卻輸入大寫字母
                    (e.which >= 65 && e.which <= 90 && !e.shiftKey) ||
                    //按下Shift鍵時，卻輸入小寫字母
                    (e.which >= 97 && e.which <= 122 && e.shiftKey)
                );
            }).focusout(function () { $("#CapsLockMsg").hide(); });
            */

            $("#sidName").attr('disabled',true);
            $("#passwd").attr('disabled',true);
            $("#loadword").hide();
            $(document).on('keypress','input[type=text]',function (e) {
               console.log();
               let ID=$(this).attr('id');
               let code= e.keyCode ? e.keyCode : e.which;
               if(code===13){
                   if(CheckHasSpecialStr($(this).val())===false){
                       alert("禁止輸入特殊字元");
                       $(this).val("");
                       return  false;
                   }

                   switch (ID) {
                       case "acount":
                           if($("#acount").val()==""){
                               return false;
                           }
                           $("#loadword").show();
                           $.ajax({
                               url:'/webservice/NISPWSCKUSR.php?str='+AESEnCode('sIdUser='+ paddingLeft($("#acount").val().toUpperCase(),7)),
                               type:'GET',
                               dataType:'text',
                               success:function (data) {
                                   let re=AESDeCode(data);
                                   let json=(JSON.parse(re))[0];
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
                           break;
                       case "passwd":
                           let val=this.value;
                           if(val){
                               let arr=val.split("");
                               let a_Regex=/(^[a-z])/;
                               let A_Regex=/(^[A-Z])/;
                               if(arr[arr.length-1].match(a_Regex)){
                                   $("#CapsLockMsg").hide();
                               } else if(arr[arr.length-1].match(A_Regex)){
                                   $("#CapsLockMsg").show();
                               }
                           }
                           break;
                   }

               }
            });

        });

        function CheckHasSpecialStr(val)
        {
            let strReg=/^.[A-Za-z0-9]+$/;
            return  val.match(strReg)==null?false:true;
        }


        function paddingLeft(str,lenght){
            if(str.length >= lenght)
                return str;
            else
                return paddingLeft("0" +str,lenght);

        }

        function formSubmit(){
            let passwd=$("#passwd").val();
            let acount=$("#acount").val().toUpperCase();
            $.ajax({
                url:'/webservice/NISPWSCKPWD.php?str='+AESEnCode('sIdUser='+paddingLeft(acount,7)+'&sPassword='+passwd),
                type:'POST',
                dataType:'text',
                success:function (data) {
                    let json=JSON.parse(AESDeCode(data));
                    let re=json.reponse;
                    if($("#passwd").val()==''){
                        $("#passwd").focus();
                        alert('密碼不得為空');
                        return false;
                    }
                    else if(re=='false'){
                        alert('密碼錯誤請重新檢查密碼');
                        return false;
                    }else if(re=='true') {
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
