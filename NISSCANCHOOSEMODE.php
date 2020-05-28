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
    <meta charset="UTF-8">
    <title>自動掃描</title>
    <script type="text/javascript" src="jquery-3.4.1.js"></script>
    <link rel="stylesheet" href="http://localhost/bootstrap-4.3.1-dist/css/bootstrap.min.css">
    <script src="bootstrap-4.3.1-dist/js/bootstrap.min.js" type="text/javascript"></script>
    <style>
        input[type=text]{
            display: none;
        }
        button{
            margin-top: 5px;
            margin-right: 5px;
            margin-left: 20px;
        }
        h1{
            text-align: center;
        }
    </style>
</head>
<h1><?php echo $title?></h1>
<div id="scan"></div>

<input value="" type="text" id="ScanMode">
<input value="<?php echo $page?>" type="text"  id="page">

<div class="row">
    <button id="BARCODE" class="btn btn-secondary">BARCODE</button>
    <button id="QRCODE" class="btn btn-secondary">QRCODE</button>
    <form id="Scanform" target="_parent" method="post" >
        <button  type="submit" class="btn btn-secondary">確定</button>
    </form>
</div>
<script>

    $(document).ready(function (){
        $("#BARCODE").on('click',function () {
            $("#scan").children().remove();
            $("#scan").append(
                "<iframe src='BARCODESCANNER.html' id='iframe1' width='500' height='700' frameborder='0'>"+"</iframe>"
            );
            $("#ScanMode").val('Bar');
        });
        $("#QRCODE").on('click',function () {
            $("#scan").children().remove();
            $("#scan").append(
                "<iframe src='test/CamScanQrcode.php' id='iframe2' width='500' height='700' frameborder='0'>"+"</iframe>"
            );
            $("#ScanMode").val('Qr');
        });


       function BarcodeVal(){
            var id=$('#scan').children()[0].id;
            var iframe=document.getElementById(id);
            var val=iframe.contentWindow.document.getElementById("Codeval").value;
            return val.split(',');
        }
        function QrcodeVal(){
            var id=$('#scan').children()[0].id;
            var iframe=document.getElementById(id);
            var val=iframe.contentWindow.document.getElementById("QR_Codeval").value;
            return  val.split(',');
        }

        var Scanform = document.getElementById('Scanform');
        Scanform.onsubmit = function() {
            var mode=$("#ScanMode").val();
            var Arr='';
            if (mode=='Bar'){
                Arr=BarcodeVal();
            }
            if(mode=='Qr'){
                Arr=QrcodeVal();
            }
            try {
                var callbackData = {
                    B_ID:Arr,
                    PAGE:$("#page").val()
                };
                console.log(callbackData);
                var aesdata = JSON.stringify(callbackData);
                window.Scancallback(aesdata);
                window.close();
                return false
            }catch (e) {
                alert(e);
            }

        }
    });

</script>



</body>
</html>