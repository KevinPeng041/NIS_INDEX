<?php
$page=$_GET['page'];
$title='';
if ($page=='B'){
    $title='領血核對掃描';
}
if ($page=='C'){
    $title='輸血核對掃描';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script type="text/javascript" src="../../jquery-3.4.1.js"></script>
    <link rel="stylesheet" href="http://localhost/bootstrap-4.3.1-dist/css/bootstrap.min.css">
    <script src="../../bootstrap-4.3.1-dist/js/bootstrap.min.js" type="text/javascript"></script>
    <script src="../../quagga.min.js"></script>
    <script>
        var ckw=setInterval(function () {
            try {
                if(!window.opener) {
                    window.close();
                }
            }catch (e)
            {
                alert('錯誤訊息:'+e);
                window.close();
                clearInterval(ckw);
                return false;
            }

        },500);
    </script>
    <style>
        /* In order to place the tracking correctly */
        canvas.drawing, canvas.drawingBuffer {
            position: absolute;
            left: 0;
            top: 0;
        }

        #DATAUI{
            justify-content: center;
            border-style: solid;
            border-color:#E0E0E0 #FFFFFF #E0E0E0 #FFFFFF
        }
        input[type=text]{
           display: none;
        }
        #Scanform textarea{
            display: none;
        }
    </style>
</head>

<body>


<!-- Div to show the scanner -->
<div class="container">
    <h1><?php echo $title?></h1>
    <input value="<?php echo $page?>" type="text"  id="page">
    <div id="scanner-container"></div>
    <nav id="DATAUI" class="navbar navbar-default navbar-static" role="navigation" >
        <div style="font-weight: bolder">血袋號碼</div>
    </nav>

    <div data-spy="scroll" data-target="#navbar-example" data-offset="0"
         style="height:200px;overflow:auto; position: relative;margin-top: 50px;">
        <table class="table" style="table-layout: fixed;text-align: center">
            <tbody style=" font-size: 3.5vmin;" id="DATAList">

            </tbody>
        </table>
    </div>


        <form id="Scanform" target="_parent" method="post" >
            <textarea id="CodeVal" ></textarea>
            <button  type="submit" class="btn btn-primary btn-lg btn-block">確定</button>
        </form>

</div>
<input value="OK" type="text" id="Canable">

<!-- Include the image-diff library -->


<script>
    $(document).ready(function () {
        // Start/stop scanner
        var _scannerIsRunning = false;
        if (_scannerIsRunning) {
            Quagga.stop();
        } else {
            startScanner();
        }
        var Scanform=document.getElementById('Scanform');

        Scanform.onsubmit = function() {
            var val='';

            if($("#Canable").val()=="enble"){
                val=$("#Textarea1").val();
            }
            if($("#Canable").val()=="OK"){
                val=$('#CodeVal').val();
            }

            var Arr =val.split(',');
            try {
                var callbackData = {
                    B_ID: Arr,
                    PAGE: $("#page").val()
                };
                var aesdata = JSON.stringify(callbackData);
                window.Scancallback(aesdata);
                 window.close();
                return false
            } catch (e) {
                alert(e);
            }
        }
        function startScanner() {
            Quagga.init({
                inputStream: {
                    name: "Live",
                    type: "LiveStream",
                    target: document.querySelector('#scanner-container'),
                    constraints: {
                        width: 480,
                        height: 320,
                        facingMode: "environment"
                    }, area: { // defines rectangle of the detection/localization area
                        top: "0%",    // top offset
                        right: "0%",  // right offset
                        left: "0%",   // left offset
                        bottom: "0%"  // bottom offset
                    },singleChannel: true
                },
                decoder: {
                    readers: [
                        "code_128_reader"
                        /* "code_128_reader",,*/
                        /* "ean_reader"*/
                        /* "ean_8_reader",
                         "code_39_reader",
                         "code_39_vin_reader",
                         "codabar_reader",
                         "upc_reader",
                         "upc_e_reader",
                         "i2of5_reader"*/
                    ],
                    debug: {
                        drawBoundingBox:false,
                        showFrequency:false,
                        drawScanline:false,
                        showPattern:false,
                        showCanvas: true,
                        showPatches: true,
                        showFoundPatches: true,
                        showSkeleton: true,
                        showLabels: true,
                        showPatchLabels: true,
                        showRemainingPatchLabels: true,
                        boxFromPatches: {
                            showTransformed: true,
                            showTransformedBox: true,
                            showBB: true
                        }
                    },
                    multiple:false
                }

            }, function (err) {
                if (err) {
                    alert(err);
                    $('#Canable').val('enble');
                    $("#scanner-container").remove();
                    $("#page").after(
                        "<div class=\"form-group\">\n" +
                        "    <textarea class=\"form-control\" id=\"Textarea1\" rows=\"3\"></textarea>\n" +
                        "  </div>"
                    );

                    return
                }

                console.log("Initialization finished. Ready to start");
                Quagga.start();
                // Set flag to is running
                _scannerIsRunning = true;
            });



            Quagga.onProcessed(function (result) {
                var drawingCtx = Quagga.canvas.ctx.overlay,
                    drawingCanvas = Quagga.canvas.dom.overlay;
                if (result) {
                    if (result.boxes) {
                        drawingCtx.clearRect(0, 0, parseInt(drawingCanvas.getAttribute("width")), parseInt(drawingCanvas.getAttribute("height")));
                        result.boxes.filter(function (box) {
                            return box !== result.box;
                        }).forEach(function (box) {
                            Quagga.ImageDebug.drawPath(box, { x: 0, y: 1 }, drawingCtx, { color: "green", lineWidth: 2 });
                        });
                    }

                    if (result.box) {
                        Quagga.ImageDebug.drawPath(result.box, { x: 0, y: 1 }, drawingCtx, { color: "#00F", lineWidth: 2 });
                    }

                    if (result.codeResult && result.codeResult.code) {
                        Quagga.ImageDebug.drawPath(result.line, { x: 'x', y: 'y' }, drawingCtx, { color: 'red', lineWidth: 3 });
                    }
                }
            });

            var arr=[];
            Quagga.onDetected(function (result) {
                var code_BID=result.codeResult.code;
                if (code_BID.length!==10){
                    return  false;
                }
                if(arr.indexOf(code_BID)<0){
                    arr.push(code_BID);
                    var result=arr.filter(function (value, index, array) {
                        return arr.indexOf(value)===index;
                    });
                    $("#DATAList").children().remove();
                    $.each(result,function (index) {
                        $("#DATAList").append(
                            "<tr>"+
                            "<td>"+
                            result[index]+
                            "</td>"
                            +"</tr>"
                        );
                    });
                    $("#CodeVal").val(result);
                    console.log(result);
                }
            });
        }

    });
</script>
</body>

</html>
