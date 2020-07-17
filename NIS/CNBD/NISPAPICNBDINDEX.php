<!DOCTYPE html>
<html lang="en">

<head>
    <title>NISPAPICNBDINDEX</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script type="text/javascript" src="../../jquery-3.4.1.js"></script>
    <link rel="stylesheet" href="../../bootstrap-4.3.1-dist/css/bootstrap.min.css">
    <script src="../../bootstrap-4.3.1-dist/js/bootstrap.min.js" type="text/javascript"></script>
    <link rel="stylesheet" href="../../css/NIS/CNBD.css">
    <style>
        /* In order to place the tracking correctly */
        .container{
            max-width: 1140px;
        }
        canvas.drawing, canvas.drawingBuffer {
            position: absolute;
            left: 0;
            top: 0;
        }

        #DATAUI{
            font-weight: bolder;
            justify-content: center;
            border-style: solid;
            border-color:#E0E0E0 #FFFFFF #E0E0E0 #FFFFFF;
        }
        .List{
            height:200px;overflow:auto;
            position: relative;

        }
        .container button{
            color: white;
            font-size: 4.5vmin;
        }

        .btn {
            margin-top: 5px;
        }
        div{
            margin-top: 5px;
        }
    </style>
</head>

<body>
<div class="container">
    <h2>領用血袋簽收單作業</h2>
        <span style="margin-left:0 px">
             <button type="button" class="btn btn-secondary btn-md" disabled style="display: none">回主畫面</button>
            <button type="button"  class="btn btn-warning btn-md" style="margin-left: 1px"   id="sbed" >責任床位</button><span style="margin-left: 1px">
        </span>

         <span class="float-left">
            <button type="submit" id="SubmitBtn" class="btn btn-primary btn-md" >儲存</button>
            <button type="button" id="Serch" class="btn btn-primary btn-md" >查詢</button>
            <button type="button" id="DELMENU" class="btn btn-primary btn-md"  data-toggle="modal" data-target="#DELModal">作廢</button>
            <button type="reset" class="btn btn-primary btn-md"  onclick="Reset(1)">清除</button>
            <button type="button" class="btn btn-secondary btn-md" disabled style="margin-right: 3px ;display: none">預設</button>
        </span>
        <div class="input-group">
            <input id="DataTxt"  value="" type="text" readonly="readonly" style="font-size: 4vmin;width:100vmin;">
        </div>


         <div class="Otimer" >
            <div class="pageTime">
                <label style="font-size: 4vmin">評估時間:</label>
                <input  type="text" id="DateVal" value=""  placeholder="YYYMMDD" maxlength="7" autocomplete="off">
                <input type="text" id="TimeVal" value="" placeholder="HHMM" maxlength="4" autocomplete="off">
            </div>
        </div>
            <div>
                 <input type="text" placeholder="輸入血袋號碼">
                 <nav id="DATAUI" class="navbar navbar-default navbar-static" role="navigation" >
                    血袋號碼清單
                </nav>
                <div data-spy="scroll" data-target="#navbar-example" data-offset="0" class="List">
                    <table class="table" style="table-layout: fixed;text-align: center">
                        <tbody style=" font-size: 3.5vmin;" id="DATAList">
                            <tr><td>
                                    10213454345
                            </td></tr>
                         <tr><td>
                                    10213454345
                            </td></tr>
                         <tr><td>
                                    10213454345
                            </td></tr>
                         <tr><td>
                                    10213454345
                            </td></tr>
                         <tr><td>
                                    10213454345
                            </td></tr>
                         <tr><td>
                                    10213454345
                            </td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
    <!-- Div to show the scanner -->
<!--
    <div class="row">

    </div>
-->
</div>

<!-- Include the image-diff library -->
<script src="../../quagga.min.js"></script>

<!--<script>
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
            var val=$('#CodeVal').val();
            var Arr =val.split(',');
            try {
                var callbackData = {
                    B_ID: Arr
                };
                var aesdata = JSON.stringify(callbackData);
                window.Scancallback(aesdata);
                window.close();
                return false
            } catch (e) {
                alert(e);
            }
        };

        function startScanner() {
            Quagga.init({
                inputStream: {
                    name: "Live",
                    type: "LiveStream",
                    target: document.querySelector('#scanner-container'),
                    constraints: {
                        width: 360,
                        height: 300,
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
                    console.log(err);
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
</script>-->
</body>

</html>
