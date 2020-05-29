NISSCANCHOOSEMODE
=================================================================================
合併 BARCODESCANNER,CamScanQrcode相機掃描。<br>
使用掃描前須引入各自對應的檔案:<br>
BARCODESCANNER=>quagga.min.js<br>
`<script src="quagga.min.js"></script>`<br>
CamScanQrcode=>instascan.min.js<br>
`<script  type="text/javascript" src="../instascan.min.js"></script>`<>br


BARCODESCANNER
---------------------------------------------------------------------------------
預設掃描型態為CODE-128,內附也包含其他型態,需手動啟動,但須注意每次開啟只能使用一種掃描的方式,<br>
 >decoder: {<br>
>>readers: [<br>
>>>"code_128_reader"<br>
>>>/* "ean_reader"<br>
>>>"ean_8_reader",<br>
>>>"code_39_reader",<br>
>>>"code_39_vin_reader",<br>
>>>"codabar_reader",<br>
>>>"upc_reader",<br>
>>>"upc_e_reader",<br>
>>>"i2of5_reader"*/<br>
>>>]<br>
>>}<br>
