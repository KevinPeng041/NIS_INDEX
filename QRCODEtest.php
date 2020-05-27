<?php
set_time_limit(0);
date_default_timezone_set('Asia/Taipei');

if(isset($_POST)&& !empty($_POST)){
    include ('library/phpqrcode/qrlib.php');
    $image_location="qrcodes/";
    $image_name=date('Y-m-d-h-i-s').'.png';

    $dataContent=$_POST['dataContent'];
    $ecc=$_POST['ecc'];
    $size=$_POST['size'];
    echo "dataContent:".$dataContent."ecc:".$ecc."size:".$size;
    QRcode::png($dataContent,$image_location.$image_name,$ecc,$size);
    echo '<img class="img-thumbnail" src="'.$image_location.$image_name.'"/>';
}else{
    echo "asda";
}