NISSCANCHOOSEMODE
=================================================================================
合併 BARCODESCANNER,CamScanQrcode相機掃描。<br>
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
