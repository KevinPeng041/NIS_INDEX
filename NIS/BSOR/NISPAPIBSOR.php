<?php

function GetBSORIniJson($conn,$sFm,$Idpt,$INPt,$ID_BED,$sTraID,$sSave,$date,$sUr,$JID_NSRANK,$FORMSEQANCE_WT){

    $SQL="SELECT ST_DATAA,ST_DATAB,ST_PREA,ST_PREB,ST_PREC,ST_PRED FROM NISWSIT WHERE ID_TABFORM='$sFm'";

    $stid=oci_parse($conn,$SQL);
    oci_execute($stid);
    $arr='';
    $ST_DATAA='';
    $ST_DATAB="";
    while (oci_fetch_array($stid)){
        $ST_DATAA=oci_result($stid,"ST_DATAA")->load();
        $ST_DATAB=oci_result($stid,"ST_DATAB")->load();
        $MM_TEXT=oci_result($stid,"ST_PREA")->load();
        $Tittle_Nm=oci_result($stid,"ST_PREB")->load();
        $Tittle_CNm=oci_result($stid,"ST_PREC")->load();
        $Data_Edit=oci_result($stid,"ST_PRED")->load();
        $arr=array(
            "MM_TEXT"=>json_decode($MM_TEXT),
            "T_NM"=>json_decode($Tittle_Nm),
            "T_CNM"=>GetStationOrder($conn,$Tittle_CNm),
            "D_EDIT"=>json_decode($Data_Edit)
        );



    }

    $isChange=ChangeChr($conn)!="Y"?"N":"Y";
    $arr['IS_CHANGE']=$isChange;
    $arr['sSave']=$sSave;
    $arr['sTraID']=$sTraID;
    $arr['ST_DATAB']=json_decode($ST_DATAB);

    $DATA=GetNoRegion($conn,$ST_DATAA,$ST_DATAB,$Idpt,$INPt,'B');


    if (!InsertTP($conn,$sFm,$sTraID,$DATA,$Idpt,$INPt,$ID_BED,$date,$sUr,$JID_NSRANK,$FORMSEQANCE_WT)){
        return false;
    }


    if ( $isChange!="Y"){
        return json_encode($arr,JSON_UNESCAPED_UNICODE);
    }

    return  str_replace("壓瘡","壓傷",json_encode($arr,JSON_UNESCAPED_UNICODE));
}
function GetBSORPageJson($conn,$sFm,$sPg,$sTraID){
    $TP_SQL="SELECT ST_DATA".$sPg." FROM HIS803.NISWSTP WHERE ID_TRANSACTION=:sTraID AND ID_TABFORM = '$sFm'";
    $TP_stid=oci_parse($conn,$TP_SQL);
    if (!$TP_stid){
        $e=oci_error($conn);
        return $e['message'];
    }

    oci_bind_by_name($TP_stid,":sTraID",$sTraID);
    $r=oci_execute($TP_stid);
    if (!$r){
        $e=oci_error($TP_stid);
        return $e['message'];
    }
    $DATA="";
    while (oci_fetch_array($TP_stid)){

        $DATA=oci_result($TP_stid,"ST_DATA".$sPg)->load();

    }
    return $DATA;
}
function ChangeChr($conn){
    //瘡=>傷

    $SQL=" SELECT IS_ACTIVE FROM NSCLSI WHERE CID_CLASS = 'BSOR' and IS_ACTIVE = 'Y' AND ST_TEXT1 = 'RETITLE'";
    $stid=oci_parse($conn,$SQL);
    oci_execute($stid);
    $is_Change="";
    while (oci_fetch_array($stid)){
        $is_Change=oci_result($stid,'IS_ACTIVE');
    }
    return $is_Change;
}
function GetNoRegion($conn,$ST_DATAA,$ST_DATAB,$ID_PATIENT,$ID_INPATIENT,$CID_BEDSORE){

$obj_A=json_decode($ST_DATAA);
$obj_B=json_decode($ST_DATAB);


$arr_A=[];
$arr_B=[];

$SQL="SELECT NSBSOR.NO_BEDSORE,TID_SOURCE,NM_ORGAN,IT_TOP,IT_LEFT,IT_WIDTH,IT_HEIGTH
        FROM NSBSOR, NSTBBS
        WHERE NSTBBS.FORMSEQANCE_BS = NSBSOR.FORMSEQANCE_BS
          AND NSBSOR.ID_PATIENT = :ID_PATIENT AND NSBSOR.ID_INPATIENT = :ID_INPATIENT
          AND NSBSOR.DM_CANCD = ' ' AND NSTBBS.DM_CANCD = ' '
          AND NSBSOR.CID_BEDSORE = :CID_BEDSORE
          AND NSTBBS.DT_EXCUTE||NSTBBS.TM_EXCUTE = 
          (
                  SELECT Max(CONCAT(tb.DT_EXCUTE,tb.TM_EXCUTE)) AS LAST_DTTM 
                  FROM NSTBBS tb, NSBSOR
                  WHERE tb.FORMSEQANCE_BS = NSBSOR.FORMSEQANCE_BS
                  AND NSBSOR.CID_BEDSORE =  :CID_BEDSORE
                  AND NSBSOR.ID_PATIENT = :ID_PATIENT AND NSBSOR.ID_INPATIENT = :ID_INPATIENT
                  AND NSBSOR.DM_CANCD = ' ' AND tb.DM_CANCD = ' '
        )
        ORDER BY NO_BEDSORE ASC
        ";
    $stid=oci_parse($conn,$SQL);
    oci_bind_by_name($stid,'ID_PATIENT',$ID_PATIENT);
    oci_bind_by_name($stid,'ID_INPATIENT',$ID_INPATIENT);
    oci_bind_by_name($stid,'CID_BEDSORE',$CID_BEDSORE);

    oci_execute($stid);
    $count=0;
  while (oci_fetch_array($stid)){
        $tmp_A = clone($obj_A);
        $tmp_B = clone($obj_B);

        $NO_BEDSORE=oci_result($stid,'NO_BEDSORE');
        $NM_ORGAN=oci_result($stid,'NM_ORGAN');
        $TID_SOURCE=oci_result($stid,'TID_SOURCE');
        $LEFT=oci_result($stid,'IT_LEFT');
        $TOP=oci_result($stid,'IT_TOP');
        $W_TH=oci_result($stid,'IT_WIDTH');
        $H_TH=oci_result($stid,'IT_HEIGTH');

        $tmp_A->NUM=$NO_BEDSORE;
        $tmp_A->TOP=$TOP;
        $tmp_A->LEFT=$LEFT;
        $tmp_A->W_TH=$W_TH;
        $tmp_A->H_TH=$H_TH;

        $tmp_B->NO_NUM=$NO_BEDSORE;
        $tmp_B->NM_ORGAN=$NM_ORGAN;
        $tmp_B->TID_SOURCE=$TID_SOURCE;
        array_push($arr_A,$tmp_A);
        array_push($arr_B,$tmp_B);
      $count++;
    }
    if ($count==0){
     //   array_push($arr_B,json_decode($ST_DATAB));
    }
    $result=array("A"=>$arr_A,"B"=>$arr_B);
    return $result;
}
function InsertTP($conn,$sfm,$sTraID,$data,$Idpt,$INPt,$ID_BED,$DM_PROCESS,$UR_PROCESS,$NSRANK,$FormSeq_WT){
    $SQL="INSERT INTO HIS803.NISWSTP(
                               ID_TABFORM,ID_TRANSACTION,ID_PATIENT,ID_INPATIENT,DT_EXCUTE,TM_EXCUTE,
                               ST_DATAA,ST_DATAB,ST_DATAC,ST_DATAD,ST_DATAE,ST_DATAF,ST_DATAG,ST_DATAH,
                               ID_BED,DM_PROCESS,UR_PROCESS,JID_NSRANK,FORMSEQANCE_WT) 
                                 VALUES (
                                '$sfm',:sTraID,:Idpt,:INPt,' ',' ',
                                EMPTY_CLOB(),EMPTY_CLOB(),' ',' ',' ',' ',' ',' ',
                               :BED,:DM_P,:UR_P,:NSRANK,:FormSeq)
                               RETURNING  ST_DATAA,ST_DATAB
                                INTO :ST_DATAA,:ST_DATAB";
    $TP_Stid = oci_parse($conn, $SQL);
    if(!$TP_Stid){
        $e=oci_error($conn);
        return $e['message'];
    }
    $clobA=oci_new_descriptor($conn,OCI_D_LOB);
    $clobB=oci_new_descriptor($conn,OCI_D_LOB);

    oci_bind_by_name($TP_Stid,":ST_DATAA",$clobA,-1,OCI_B_CLOB);
    oci_bind_by_name($TP_Stid,":ST_DATAB",$clobB,-1,OCI_B_CLOB);

    oci_bind_by_name($TP_Stid,":sTraID",$sTraID);
    oci_bind_by_name($TP_Stid,":Idpt",$Idpt);
    oci_bind_by_name($TP_Stid,":INPt",$INPt);
    oci_bind_by_name($TP_Stid,":BED",$ID_BED);
    oci_bind_by_name($TP_Stid,":DM_P",$DM_PROCESS);
    oci_bind_by_name($TP_Stid,":UR_P",$UR_PROCESS);
    oci_bind_by_name($TP_Stid,":NSRANK",$NSRANK);
    oci_bind_by_name($TP_Stid,":FormSeq",$FormSeq_WT);


    $result = oci_execute($TP_Stid,OCI_NO_AUTO_COMMIT);
    if(!$result){
        $e=oci_error($TP_Stid);
        oci_rollback($conn);
        echo $e['message'];
        return false;
    }


    $clobA->save(json_encode($data['A'],JSON_UNESCAPED_UNICODE));
    $clobB->save(json_encode($data['B'],JSON_UNESCAPED_UNICODE));
    oci_free_statement($TP_Stid);
    oci_commit($conn);
    return true;
}
function GetCurrentRegion($conn,$PIXEL_X,$PIXEL_Y){
    $SQL="SELECT NSPXEL.ID_REGION, NSPXRG.NM_REGION
          FROM NSPXEL, NSPXRG
        WHERE NSPXEL.ID_REGION = NSPXRG.ID_REGION
          AND NSPXEL.IT_PIXEL_X = :PIXEL_X AND NSPXEL.IT_PIXEL_Y = :PIXEL_Y
          AND NSPXEL.CID_REGION = 'BS'
        ";

    $stid=oci_parse($conn,$SQL);
    oci_bind_by_name($stid,':PIXEL_X',$PIXEL_X);
    oci_bind_by_name($stid,':PIXEL_Y',$PIXEL_Y);
    oci_execute($stid);
    $result="";
    while (oci_fetch_array($stid)){
        $result=array(
            "REGION"=>oci_result($stid,'ID_REGION'),
            "NM_REGION"=>oci_result($stid,'NM_REGION')
        );
    }
}
function GetStationOrder($conn,$CNM_arr){
    $NEW_arr=json_decode($CNM_arr);

    $SQL=" SELECT ID_STATION, NM_STATION FROM NIS_V_HNST_Q0 ORDER BY ID_STATION";
    $stid=oci_parse($conn,$SQL);
    oci_execute($stid);

    while (oci_fetch_array($stid)){
        array_push( $NEW_arr[2],array("ID_TABITEM"=>"","ST_LEFT"=>oci_result($stid,'ID_STATION')));

    }


    return $NEW_arr;
}