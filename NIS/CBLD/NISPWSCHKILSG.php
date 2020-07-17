<?php
include '../../NISPWSIFSCR.php';
function GetCBLDCheck($sPg,$sUr,$pwd)
{    //檢查領輸血核對存檔必要欄位
    $HOST_IP=$_SERVER['HTTP_HOST'];
    //帳號認證
    $http="http://".$HOST_IP.'/webservice/';
    $file=$http.'NISPWSCKUSR.php?str='.AESEnCode('sIdUser='.$sUr);

    $sPgNam='';
    if ($sPg=='B'){
        $sPgNam='領血覆核';
    }
    if ($sPg=='C'){
        $sPgNam='輸血覆核';
    }
    $stutas=file_get_contents($file);
    $decypect= AESDeCode($stutas);
    $json= json_decode($decypect);
    $result=['result'=>'true','re'=>'true'];
    $Status=$json[0]->Status;

    if($Status=='Error'){
        $result=['result'=>$sPgNam.'帳號錯誤','re'=>'false'];
        return json_encode($result,JSON_UNESCAPED_UNICODE);
    }

    //密碼認證
    $file=$http.'NISPWSCKPWD.php?str='.AESEnCode('sIdUser='.$sUr.'&sPassword='.$pwd);
    $reponse=file_get_contents($file);
    $de=AESDeCode($reponse);
    $pwd_re= json_decode($de);
    if($pwd_re->reponse=='false')
    {
        $result=['result'=>$sPgNam.'密碼錯誤','re'=>'false'];
        return json_encode($result,JSON_UNESCAPED_UNICODE);
    }
    return json_encode($result,JSON_UNESCAPED_UNICODE);
}
