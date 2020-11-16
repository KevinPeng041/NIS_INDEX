<?php
include "NISPWSIFSCR.php";
$str=$_GET["str"];
$parameter=str_replace(' ','+',$str);//空白先替換+
$str=explode("&",AESDeCode($parameter));
$sUr=explode("=",$str[2])[1];
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
        #LogOutModleUI{
            font-weight: bold;
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
            <input  type="button" id="LogOutModleUI" class="btn btn-sm btn-warning" value="登出" style="margin-top: 4px">
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
                <button type="button" id="LogOutCel" class="btn btn-secondary" data-dismiss="modal" >取消</button>
                <button type="button" id="LogOut" class="btn btn-secondary" data-dismiss="modal">確定</button>
            </div>
        </div>
    </div>
</div>
</body>
<script>
    $(document).ready(function () {
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
                <div class='btn col-6'>
                     <button  type="button" class='btn btn-primary btn-lg btn-block' id='${sFm}'>${BtnName[index].name}</button>
                </div>
                `
            );
        });

        $(document).on('click','button',function () {
            let BtnID=$(this).attr('id');
            switch (BtnID) {
                case "LogOutCel":
                    CancellLogOut();
                    break;
                case "LogOut":
                    WindowCloes();
                    break;
                default:
                    openwindow(BtnID);
                    break;
            }
        });


        let  myWindow='';
        function openwindow(sFm) {
            let strWindowFeatures=
         `
            width=850px,
            height=750px,
            scrollbars=yes,
            resizable=no
            location=no,

        `;
            myWindow= window.open("NIS/"+sFm+"/NISPRW"+sFm+".php?str="+'<?PHP echo $parameter?>',$("#"+sFm).text(),strWindowFeatures);
            console.log("http://localhost"+"/NIS/"+sFm+"/NISPRW"+sFm+".php?str="+'<?PHP echo $parameter?>');
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

    });


</script>
</html>



