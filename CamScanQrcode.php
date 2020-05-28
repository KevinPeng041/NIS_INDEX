<?php
$page=$_GET['page'];
/*title*/
$title="";
switch ($page){
    case "B":
        $title="領血掃描";
        break;
    case "C":
        $title="輸血掃描";
        break;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Scanner</title>
    <script type="text/javascript" src="../jquery-3.4.1.js"></script>
    <link rel="stylesheet" href="http://localhost/bootstrap-4.3.1-dist/css/bootstrap.min.css">
    <script  type="text/javascript" src="../instascan.min.js"></script>
    <script type="text/javascript" src="../jquery.min.js"></script>
    <style>
        .wrap{
            max-width: 800px;
            margin: 0 auto;
        }
        .box1{
            width: 100%;
            z-index: 2 ;
            height: 100px;
            text-align: center;
        }
        .box2{
            height: 200px;
            text-align: center;
            margin-top: 5rem;
        }
        /*#wrapper{
            position: absolute;
            width: 100%;
            height: 100%;
            background-color: black;
            opacity: 0.5;
            z-index: -1;
        }
*/
    </style>
</head>
<script>
    /*if($("#page").val()=="B"){
        $("#titleTxt").append()
    }*/

    var constraints={video:{ width: 200, height: 200 }};
    navigator.mediaDevices.getUserMedia(constraints).then(function (mediaStream) {
        var video=document.querySelector('video');
        video.srcObject=mediaStream;
        video.onloadedmetadata=function (e) {
            video.play();
        }
    }).catch(function (err) {
        console.log(err.name+":"+err.message);
    });
    /*掃描*/
    var scanner = new Instascan.Scanner({
        continuous: true, // 連續掃描
        video: document.querySelector('video'), // 預覽
        facingMode: {
            exact: "environment"
        }
    });

    var arr2=[];
    scanner.addListener('scan', function (content) {
        if(content){
            if(arr2.indexOf(content)<0){
                arr2.push(content);
                $("#DATAList").children().remove();
                $.each(arr2,function (index) {
                    $("#DATAList").append(
                        "<tr>"+
                        "<td>"+
                        arr2[index]+
                        "</td>"
                        +"</tr>"
                    );
                });
                $("#QR_Codeval").val(arr2);
                console.log(arr2);
            }
        }
    });
    /*搜尋鏡頭*/
    Instascan.Camera.getCameras().then(function (cameras) {
        if (cameras.length > 0) {
            scanner.start(cameras[0]); // [0] 前鏡頭 [1] 後鏡頭
        } else {
            console.error('沒有找到相機');
        }
    }).catch(function (e) {
        console.error(e);
    });

      /*  window.onload = function(){
            var Scanform = document.getElementById('Scanform');
            Scanform.onsubmit = function() {
                var page=document.getElementById("page").value;
                var callbackData = {
                    B_ID:arr2,
                    PAGE:page
                };
                console.log(callbackData);
                var aesdata = JSON.stringify(callbackData);
                window.Scancallback(aesdata);
                return false
            }

        }*/

</script>

<body>
<input type="text" value="<?php  echo $page ?>" style="display: none" id="page">
<div class="box2">
    <video id="preview" autoplay playsinline ></video>
</div>
<textarea id="QR_Codeval"></textarea>
<div data-spy="scroll" data-target="#DATAUI" data-offset="0" style="height:150px;overflow:auto; position: relative;margin-top: 50px;">
    <div class="table-responsive" id="DATAUI">
        <table class="table" style="table-layout: fixed;text-align: center">
            <thead  class="theadtitle"  style=" font-size: 3.5vmin;">
            <th style=" padding-bottom: 5px !important">血袋號碼</th>
            </thead>
            <tbody style=" font-size: 3.5vmin;" id="DATAList">

            </tbody>
        </table>
    </div>
</div>
<!--<form id="Scanform" target="_parent" method="post" >
    <div id="wrapper"></div>
    <div class="wrap">
        <div class="box1" id="titleTxt">
            <h1 style="background-color: whitesmoke"><?php /*echo $title*/?></h1>
        </div>
        <div class="box2" style="margin-top: -50px">
            <div style="margin-top: -30px">
                <video id="preview" autoplay playsinline ></video>
            </div>
        </div>


    </div>
</form>-->
</body>
</html>
