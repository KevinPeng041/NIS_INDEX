<?php

function GetBSORIniJson($conn,$Idpt,$INPt,$ID_BED,$sTraID,$sSave,$date,$sUr,$JID_NSRANK,$FORMSEQANCE_WT){

    $SQL="SELECT ST_PREA,ST_PREB,ST_PREC FROM NISWSIT WHERE ID_TABFORM='BSOR'";

    $stid=oci_parse($conn,$SQL);
    oci_execute($stid);
    $arr='';
    while (oci_fetch_array($stid)){
        $MM_TEXT=oci_result($stid,"ST_PREA")->load();
        $Tittle_Nm=oci_result($stid,"ST_PREB")->load();
        $Tittle_CNm=oci_result($stid,"ST_PREC")->load();

        $arr=array("MM_TEXT"=>json_decode($MM_TEXT),"Tittle_Nm"=>json_decode($Tittle_Nm),"Tittle_CNm"=>json_decode($Tittle_CNm));
    }
    return json_encode($arr,JSON_UNESCAPED_UNICODE);
}
