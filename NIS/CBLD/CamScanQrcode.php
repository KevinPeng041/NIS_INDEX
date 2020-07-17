<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Scanner</title>
    <script type="text/javascript" src="../../jquery-3.4.1.js"></script>
    <link rel="stylesheet" href="../../bootstrap-4.3.1-dist/css/bootstrap.min.css">
    <script  type="text/javascript" src="../../instascan.min.js"></script>
    <script type="text/javascript" src="../../jquery.min.js"></script>
    <style>
        .box2{
            height: 200px;
            text-align: center;
            margin-top: 5rem;
        }
        #DATAUI{
            justify-content: center;
            border-style: solid;
            border-color:#E0E0E0 #FFFFFF #E0E0E0 #FFFFFF
        }
    </style>
</head>
<script>
    var constraints={video:{ width: 200, height: 200 }};
    navigator.mediaDevices.getUserMedia(constraints).then(function (mediaStream) {
        var video=document.querySelector('video');
        video.srcObject=mediaStream;
        video.onloadedmetadata=function (e) {
            video.play();
        }
    }).catch(function (err) {
        alert(err.name+":"+err.message);
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
</script>

<body>

<div class="box2">
    <video id="preview" autoplay playsinline ></video>
</div>
<textarea id="QR_Codeval" style="display: none"></textarea>
<nav id="DATAUI" class="navbar navbar-default navbar-static" role="navigation" >
    <div style="font-weight: bolder">血袋號碼</div>
</nav>

<div data-spy="scroll" data-target="#navbar-example" data-offset="0"
     style="height:200px;overflow:auto; position: relative;">
    <table class="table" style="table-layout: fixed;text-align: center">
        <tbody style=" font-size: 3.5vmin;" id="DATAList">

        </tbody>
    </table>
</div>
<div class="row">
    <form id="Scanform" target="_parent" method="post" >
        <textarea id="Codeval" ></textarea>
        <button  type="submit"  class="btn btn-secondary">確定</button>
    </form>
</div>
<script>
    $(document).ready(function () {
        var Scanform=document.getElementById('Scanform');

        Scanform.onsubmit = function() {
            var val=$('#QR_Codeval').val();
            var Arr =val.split(',');
            try {
                var callbackData = {
                    B_ID: Arr,
                    PAGE: $("#page").val()
                };
                console.log(callbackData);
                var aesdata = JSON.stringify(callbackData);
                window.Scancallback(aesdata);
                /* window.close();*/
                return false
            } catch (e) {
                alert(e);
            }


        }
    });
</script>
</body>
</html>
