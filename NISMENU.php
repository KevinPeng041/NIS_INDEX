<?php
include "NISPWSIFSCR.php";
$str=$_GET["str"];
$replaceSpace=str_replace(' ','+',$str);//空白先替換+
$EXPLODE_data=explode('&',AESDeCode($replaceSpace));

$sIdUser_STR=$EXPLODE_data[0];
$passwd_STR=$EXPLODE_data[1];
$user_STR=$EXPLODE_data[2];

$sIdUser_value=explode('=',$sIdUser_STR);
$passwd_value=explode('=',$passwd_STR);
$user_value=explode('=',$user_STR);

$sIdUser=trim($sIdUser_value[1]);/*帳號*/
$passwd=trim($passwd_value[1]);/*密碼*/
$sUr=trim($user_value[1]);/*使用者*/


$Account=strtoupper(str_pad($sIdUser,7,"0",STR_PAD_LEFT));
/*$parameter="sfm=CNAD".'&sIdUser='.$Account.'&passwd='.$passwd."&user=".$sUr;*/
$parameter='sIdUser='.$Account.'&passwd='.$passwd."&user=".$sUr;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>系統選單</title>
    <script type="text/javascript" src="jquery-3.4.1.js"></script>
    <link rel="stylesheet" href="bootstrap-4.3.1-dist/css/bootstrap.min.css">
    <script src="bootstrap-4.3.1-dist/js/bootstrap.min.js" type="text/javascript"></script>
    <script src="crypto-js.js"></script>
    <script src="AESCrypto.js"></script>
    <style>
        .row {
            display: flex;
            flex-wrap: wrap;
        }
        .col{
            flex-grow: 1;
            /* Try to resize window < 600px */
            min-width:200px;
            /* Below is not important */
            margin:15px 5px 0 0;
            min-height:1vh;
            text-align:center;
        }
        .btn{
           font-size: 19px;
        }

    </style>
</head>

<body>
<div class="container">
    <div class="row" style="margin-top: 5px">
        <div class="col-auto col-auto col-sm-auto col-md-auto col-lg-auto ">
            <h1><?php echo  $sUr?></h1>
        </div>
        <div class="col-auto col-auto col-sm-auto col-md-auto col-lg-auto ">
            <button id="LogOutModleUI" class="btn btn-sm btn-warning" style="margin-top: 4px"><b>登出</b></button>
        </div>
    </div>
    <div id="MenuBtn" class="row">

    </div>
</div>
<div class="modal fade" id="LogOutmodal" tabindex="-1" aria-labelledby="LogOutmodalCenterTitle" role="dialog" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-md" role="document">
        <div class="modal-content" style="height:20%;width: 90%;">
            <div class="modal-header">
                <h5 class="modal-title" id="LogOutmodalCenterTitle">訊息提示</h5>
            </div>
            <div class="modal-body">
                <p style="font-size: 25px;word-wrap: break-word">請確認是否要登出</p>
            </div>
            <div class="modal-footer">
                <button type="button" id="LogOut" class="btn btn-secondary" data-dismiss="modal" onclick='CancellLogOut()'>取消</button>
                <button type="button" id="LogOut" class="btn btn-secondary" data-dismiss="modal" onclick='WindowCloes()'>確定</button>
            </div>
        </div>
    </div>
</div>


</body>
<script>
    const BtnName=[
        {
            sFm:'ILSG',name:'血糖胰島素作業'
        },{
            sFm:'CBLD',name:'領輸血作業'
        },{
            sFm:'CNBD',name:'領用血袋簽收單作業'
        },{
            sFm:'CNAD',name:'發血覆核作業'
        },
        {
            sFm:'CNCD',name:'檢驗採檢辨識作業'
        }

    ];
    $.each(BtnName,function (index) {
        let sFm=BtnName[index].sFm;
        $('#MenuBtn').append(
            `
            <div class='col col-12 col-sm-6 col-md-4 col-lg-2'>
                 <button class='btn btn-primary btn-lg btn-block'  onclick='openwindow("${sFm}")'>${BtnName[index].name}</button>
            </div>
            `
        );
    });

let  myWindow='';
function openwindow(sFm) {
   myWindow= window.open("NIS/"+sFm+"/NISPRW"+sFm+".php?str="+AESEnCode('<?PHP echo $parameter?>'),$("#"+sFm).text(),'width=850px,height=750px,scrollbars=yes,resizable=no');
   console.log("http://10.10.230.73:8080/"+"NIS/"+sFm+"/NISPRW"+sFm+".php?str="+AESEnCode('<?PHP echo $parameter?>'));
    console.log("http://10.10.230.73:8080/"+"NIS/"+sFm+"/NISPRW"+sFm+".php?str="+'<?PHP echo $parameter?>');
}

function CloseWin() {
    if(myWindow){
        myWindow.close();
    }
}
function  ckw() {
    if(!myWindow){
        alert("window not opened");
    }else {
        if(myWindow.closed){
            alert("window closed")
        }else {
            alert("window not closed")
        }
    }
}
$("#LogOutModleUI").click(function () {
    $('#LogOutmodal').modal('show');
});
function CancellLogOut() {
    $('#LogOutmodal').modal('hide');
}
function WindowCloes(){

    window.close();
}
</script>
</html>



