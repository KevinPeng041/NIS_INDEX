<?php
function GetIOACIniJson($conn,$Idpt,$INPt,$ID_BED,$sTraID,$sSave,$date,$sUr,$JID_NSRANK,$FORMSEQANCE_WT){

    $SQL="SELECT NM_ITEM,CID_SPECIAL FROM NSCLSI WHERE CID_CLASS = 'IODT'";

    $stid=oci_parse($conn,$SQL);

    oci_execute($stid);
    $result=[];

    while (oci_fetch_array($stid)){
        $NM_ITEM=oci_result($stid,'NM_ITEM');
        $CID_SPECIAL=oci_result($stid,'CID_SPECIAL');
        $result[]=array("NM_ITEM"=>$NM_ITEM,"CID_S"=>$CID_SPECIAL);
    }

    $DT_Class=json_encode($result,JSON_UNESCAPED_UNICODE);
    $DT_DATA=Get_IOAC_DATA($conn,$Idpt,$INPt,substr($date,0,7));


    $TP_SQL="INSERT INTO HIS803.NISWSTP(
                    ID_TABFORM,ID_TRANSACTION,ID_PATIENT,ID_INPATIENT,DT_EXCUTE,TM_EXCUTE,
                    ST_DATAA,ST_DATAB,ST_DATAC,ST_DATAD,ST_DATAE,ST_DATAF,ST_DATAG,ST_DATAH,
                    ID_BED,DM_PROCESS,UR_PROCESS,JID_NSRANK,FORMSEQANCE_WT)
                     VALUES (
                     'IOAC','$sTraID','$Idpt','$INPt',' ',' ',
                     '$DT_Class','$DT_DATA',' ',' ',' ',' ',' ',' ',
                    '$ID_BED','$date','$sUr','$JID_NSRANK','$FORMSEQANCE_WT')";

    $TP_stid=oci_parse($conn,$TP_SQL);
    if (!$TP_stid){
        $e=oci_error($conn);
        return $e['message'];
    }
    $exe=oci_execute($TP_stid,OCI_NO_AUTO_COMMIT);
    if(!$exe){
        $e=oci_error($TP_stid);
        return $e['message'];
    }
    oci_commit($conn);

    $JsonBack=array('sTraID' => $sTraID, 'sSave' => $sSave,'FORMSEQANCE_WT'=>$FORMSEQANCE_WT,"JID_NSRANK"=>$JID_NSRANK);

    return json_encode($JsonBack,JSON_UNESCAPED_UNICODE);

}
function GetIOACPageJson($conn,$sPg,$sTraID){
    $TP_SQL="SELECT ST_DATA".$sPg." FROM HIS803.NISWSTP WHERE ID_TRANSACTION=:sTraID AND ID_TABFORM = 'IOAC'";
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



function PosIOACSave($conn,$sTraID,$sUr){
    date_default_timezone_set('Asia/Taipei');

    $Ssql="SELECT ID_INPATIENT,ID_PATIENT,ST_DATAC,ID_BED,JID_NSRANK,FORMSEQANCE_WT
           FROM HIS803.NISWSTP
           WHERE ID_TABFORM = 'IOAC'  AND ID_TRANSACTION = :id_TRANS";

    $Sstid=oci_parse($conn,$Ssql);
    if (!$Sstid){
        $e=oci_error($conn);
        return $e['message'];
    }

    oci_bind_by_name($Sstid,":id_TRANS",$sTraID);
    oci_execute($Sstid,OCI_NO_AUTO_COMMIT);

    $Idpt="";
    $INPt="";
    $ST_DATAC="";
    $ID_BED = '';
    $FORMSEQANCE_WT = '';
    $JID_NSRANK = '';

    while (oci_fetch_array($Sstid)){
        $Idpt=oci_result($Sstid,"ID_PATIENT");
        $INPt=oci_result($Sstid,"ID_INPATIENT");
        $ST_DATAC=oci_result($Sstid,"ST_DATAC")->load();
        $ID_BED=oci_result($Sstid,"ID_BED");
        $JID_NSRANK=oci_result($Sstid,"JID_NSRANK");
        $FORMSEQANCE_WT=oci_result($Sstid,"FORMSEQANCE_WT");
    }


    $OBj= json_decode($ST_DATAC);
    $DT=$OBj->sDT;
    $CID_EXECUTE=$OBj->CID_EXCUTE;


    $toY = (int)date('Y')-1911;
    $today=(string)$toY .date('mdHis');

    $Insert_SQL="INSERT INTO nsiocs(DATESEQANCE_FL,ID_PATIENT,ID_INPATIENT,NO_OPDSEQ,DT_REGISTER,DT_EXCUTE,CID_EXCUTE,ID_BED,JID_NSRANK,
                FORMSEQANCE_WT,DM_PROCESS,UR_PROCESS,DM_CANCD,UR_CANCD)
                VALUES (NIS_DATETIMESEQ,'$Idpt','$INPt','0',' ','$DT','$CID_EXECUTE','$ID_BED','$JID_NSRANK','$FORMSEQANCE_WT','$today','$sUr',' ',' ')";

    $result="true";
    $msg="";
    $IStid=oci_parse($conn,$Insert_SQL);
     if (!$IStid){

         $result='false';
         $msg=oci_error($conn)['message'];
     }
     $exe=oci_execute($IStid,OCI_NO_AUTO_COMMIT);
     if (!$exe){
         $result='false';
         $msg=oci_error($IStid)['message'];
     }
    $Response=array("result"=>$result,"message"=>$msg);

    return   json_encode($Response,JSON_UNESCAPED_UNICODE);
}
function PosIOAClassCancel($conn,$sFm,$sTraID,$sUr){
    $DateTime = date("YmdHis");
    $STR = substr($DateTime, 0, 4);
    $STR1 = substr($DateTime, -10, 10);
    $str = $STR - 1911;
    $NowDT = $str . $STR1;


    $S_SQL="SELECT ID_PATIENT,ID_INPATIENT,DT_EXCUTE,ST_DATAC 
            FROM  HIS803.NISWSTP
            WHERE ID_TABFORM = :ID_TABFORM  AND ID_TRANSACTION = :ID_TRANSACTION";

    $stid=oci_parse($conn,$S_SQL);
    oci_bind_by_name($stid,":ID_TABFORM",$sFm);
    oci_bind_by_name($stid,":ID_TRANSACTION",$sTraID);
    oci_execute($stid);
    $IdPt='';
    $INPt='';
  /*  $DT='';*/
    $ST_DATAC='';
    while (oci_fetch_array($stid)){
        $IdPt=oci_result($stid,"ID_PATIENT");
        $INPt=oci_result($stid,"ID_INPATIENT");
       /* $DT=oci_result($stid,"DT_EXCUTE");*/
        $ST_DATAC=oci_result($stid,'ST_DATAC')->load();
    }
    $DATAC_Obj=json_decode($ST_DATAC);
    $CID_E=$DATAC_Obj->CID_EXCUTE;
    $sDT=$DATAC_Obj->sDT;

    $Sql="UPDATE nsiocs SET DM_CANCD='$NowDT',UR_CANCD='$sUr'
            WHERE ID_PATIENT='$IdPt' AND ID_INPATIENT='$INPt' 
            AND DT_EXCUTE='$sDT' AND  CID_EXCUTE = '$CID_E'
            AND DM_CANCD=' '";

    $Ustid=oci_parse($conn,$Sql);
    if (!$Ustid){
        $e=oci_error($conn);
        return json_encode(array("message"=>$e['message'],"result"=>"false"));
    }


    $UP_re=oci_execute($Ustid,OCI_NO_AUTO_COMMIT);
    if(!$UP_re){
        $e=oci_error($Ustid);
        oci_rollback($conn);
        return json_encode(array("message"=>$e['message'],"result"=>"false"));
    }
    oci_commit($conn);
    return json_encode(array("message"=>"success","result"=>"true"));

}

//取病人三班時間紀錄資料=>INI ,SERCH
function GetIOACJson($conn,$Idpt,$INPt,$sUr,$sDt,$sTm,$sPg,$sFSq)
{

    $sql="SELECT (SELECT his803.nis_datetimeseq FROM DUAL) ID_TRANSB,
            his803.GetWSTPNEXTVAL ID_TRANSA, 
             CR.CA_BEDNO ID_BED, WM.formseqance_wt FORMSEQANCE_WT,
            (SELECT Max(CI.id_item) FROM HIS803.NSUSER UR, HIS803.NSCLSI CI
            WHERE  UR.jid_nsrank <> ' '
            AND UR.jid_nsrank = CI.jid_key AND CI.cid_class='RANK') JID_NSRANK,
            (SELECT PU.is_confirm FROM HIS803.NSPROU PU
            WHERE  PU.id_user  =  WM.id_user AND PU.id_program = 'NISCISLN') ID_COMFIRM   
            FROM HIS803.NSWKBD WD, HIS803.NSWKTM WM, HIS803.INACAR CR
            WHERE  CR.CA_MEDNO = :idPt AND CR.CA_INPSEQ = :INPt
            AND  WM.id_user(+) =:idUser
            AND  WM.dt_offwork(+) = ' ' AND  WM.dm_cancd(+) =' ' 
            AND  WM.formseqance_wt(+)= WD.formseqance_wt
            AND WD.id_bed(+) = CR.CA_BEDNO 
            AND CR.CA_CHECK = 'Y' AND CR.CA_DIVINSU = 'N'
            AND CR.CA_CLOSE='N'";


    $stid1=oci_parse($conn,$sql);

    oci_bind_by_name($stid1,':idPt',$Idpt);
    oci_bind_by_name($stid1,':INPt',$INPt);
    oci_bind_by_name($stid1,':idUser',$sUr);

    oci_execute($stid1);
    $ID_TRANSB='';
    $ID_TRANSA='';
    $ID_BED='';
    $FORMSEQANCE_WT='';
    $JID_NSRANK='';
    while ($row=oci_fetch_array($stid1)){
        $ID_TRANSB=$row[0];
        $ID_TRANSA=$row[1];//流水號
        $ID_BED=$row[2];
        $FORMSEQANCE_WT=$row[3];
        $JID_NSRANK=$row[4];
        $ID_COMFIRM=$row[5];
    }

    $TRANSA=str_pad($ID_TRANSA,8,0,STR_PAD_LEFT);
    $sTraID=$ID_TRANSB.'ILSGA'.$TRANSA;

    $DM_PR=$sDt.substr($sTm,0,2);

    $json=Get_IOAC_DATA($conn,$Idpt,$INPt,$sDt);

    $IN_TP="INSERT INTO HIS803.NISWSTP(
                    ID_TABFORM,ID_TRANSACTION,ID_PATIENT,ID_INPATIENT,DT_EXCUTE,TM_EXCUTE,
                    ST_DATAA,ST_DATAB,ST_DATAC,ST_DATAD,ST_DATAE,ST_DATAF,ST_DATAG,ST_DATAH,
                    ID_BED,DM_PROCESS,UR_PROCESS,JID_NSRANK,FORMSEQANCE_WT)
                     VALUES (
                     'IOAC','$sTraID','$Idpt','$INPt','$sDt',' ',
                     ' ','$json',' ',' ',' ',' ',' ',' ',
                    '$ID_BED','$DM_PR','$sUr','$JID_NSRANK','$FORMSEQANCE_WT')
                
                     ";

    $TP_Stid = oci_parse($conn, $IN_TP);
    if(!$TP_Stid){
        $e=oci_error($conn);
        return $e['message'];
    }
    $result = oci_execute($TP_Stid,OCI_NO_AUTO_COMMIT);
    if(!$result){
        $e=oci_error($TP_Stid);
        return $e['message'];
    }
    oci_free_statement($TP_Stid);
    oci_commit($conn);


    return json_encode(array("sTraID"=>$sTraID,"Data"=>$json));
}

//取評估人員
function GetConFirmUser($conn,$IdPt,$InPt,$DT){

    $SQL="SELECT CID_SPECIAL FROM NSCLSI WHERE CID_CLASS = 'IODT'";

    $stid=oci_parse($conn,$SQL);
    oci_execute($stid);
    $CID_E=[];

    while (oci_fetch_array($stid)){
        array_push($CID_E,oci_result($stid,"CID_SPECIAL"));
    }
    array_push($CID_E,"I");

    $reArr=[];
    foreach ($CID_E as $value){
        $Sql="SELECT  (SELECT NM_USER FROM HIS803.NSUSER WHERE ID_USER=UR_PROCESS )NM_ITEM 
          FROM nsiocs WHERE 
            ID_PATIENT='$IdPt' AND ID_INPATIENT='$InPt' AND
            DT_EXCUTE='$DT' AND  CID_EXCUTE = '$value' AND DM_CANCD=' '
            ";
        $stid=oci_parse($conn,$Sql);
        oci_execute($stid);
        $NM_ITEM="";
        while (oci_fetch_array($stid)){
            $NM_ITEM=oci_result($stid,'NM_ITEM');
        }
        $reArr[]=array("NM_ITEM"=>$NM_ITEM,"CID_EXCUTE"=>$value);
    }
    return json_encode($reArr,JSON_UNESCAPED_UNICODE);

}

function GetNewDateTime($Date,$Time,$Dnum,$Tnum){
//日期
    $Y=(int)substr($Date,0,3)+1911;

    $M=(int)substr($Date,3,2);

    $D=(int)substr($Date,5,2);

    $DT=(string)$Y."-".(string)$M."-".(string)$D;


//時間
    $arr=str_split($Time,2);
    $h=(int)$arr[0];
    $m=(int)$arr[1];
    $s=(int)$arr[2];



    $Date_C=date_create($DT);
    date_time_set($Date_C,$h,$m,$s);



    $Date_C->setDate($Y,$M,$D+$Dnum);
    $Date_C->setTime($h,$m,$Tnum);

    $Date_C->modify('-1911 year');

    return ltrim(date_format($Date_C,'YmdHis'),'0');


}

function Get_IOAC_DATA($conn,$Idpt,$INPt,$sDt){
    $SQL = "SELECT ID_ITEM, ST_TEXT1,ST_TEXT2,CID_SPECIAL FROM NSCLSI WHERE CID_CLASS = 'IODT'";

    $stid = oci_parse($conn, $SQL);
    oci_execute($stid);

    $Tm_Start=[];
    $TmSTtoE=[];
    while (oci_fetch_array($stid)) {
        $Tm_S = oci_result($stid, "ST_TEXT1");
        $Tm_E = oci_result($stid, "ST_TEXT2");
        $CID_SPECIAL= oci_result($stid, "CID_SPECIAL");
        array_push($Tm_Start,$Tm_S);
        $TmSTtoE[]=Array("Start"=>$Tm_S,"End"=>$Tm_E,"IO"=>$CID_SPECIAL);
    }

    $Dt_now=GetNewDateTime($sDt,$Tm_Start[0],0,0);
    $Dt_next=GetNewDateTime($sDt,$Tm_Start[0],1,-1);
    $S_Sql="SELECT ID_BED, DT_EXCUTE, TM_EXCUTE, CID_SPECIAL as CID_EXCUTE, CID_IO, P0.JID_KEY, QUANTITY, NM_COLOR, ST_LOSS, NM_PHARMACY,
            P0.NM_ITEM, P0.ID_ITEM, NM_USER, JID_NSRANK
            , MM_IO, DB_REMAIN, TM_START, TM_END, NM_IOWAY, CID_IOWAY, NM_TUBE_SHORT 
            FROM NIS_V_IOQA_P0 P0, NSCLSI IODT 
            WHERE ID_PATIENT = '$Idpt' AND ID_INPATIENT = '$INPt' AND
            (CONCAT(DT_EXCUTE, TM_EXCUTE) >= '$Dt_now' AND
                CONCAT(DT_EXCUTE, TM_EXCUTE) <= '$Dt_next' ) AND
            IODT.CID_CLASS = 'IODT'  and DT_EXCUTE >='$sDt'
            AND TM_EXCUTE >= IODT.ST_TEXT1 
            AND TM_EXCUTE <( CASE ST_TEXT2 WHEN '000000' THEN '235959' ELSE LPAD(TO_CHAR(TO_NUMBER(ST_TEXT2)-1),6,'0') END )
            ORDER BY DT_EXCUTE, TM_EXCUTE, CID_IO, P0.ID_ITEM";

    //echo $S_Sql;

    $S_stid=oci_parse($conn,$S_Sql);
    oci_execute($S_stid);
    $arr=[];

    while (oci_fetch_array($S_stid)){
        $BED=oci_result($S_stid,"ID_BED");
        $DT=oci_result($S_stid,"DT_EXCUTE");
        $TM=oci_result($S_stid,"TM_EXCUTE");
        $CID_EXCUTE=oci_result($S_stid,"CID_EXCUTE");
        $CID_IO=oci_result($S_stid,"CID_IO");
        $JID_KEY=oci_result($S_stid,"JID_KEY");
        $QUANTITY=oci_result($S_stid,"QUANTITY");
        $NM_COLOR=oci_result($S_stid,"NM_COLOR");
        $ST_LOSS=oci_result($S_stid,"ST_LOSS");
        $NM_PHARMACY=oci_result($S_stid,"NM_PHARMACY");
        $NM_ITEM=oci_result($S_stid,"NM_ITEM");
        $ID_ITEM=oci_result($S_stid,"ID_ITEM");
        //$NM_USER=oci_result($S_stid,"NM_USER");
        $JID_NSRANK=oci_result($S_stid,"JID_NSRANK");
        $MM_IO=oci_result($S_stid,"MM_IO");
        $DB_REMAIN=oci_result($S_stid,"DB_REMAIN");
        $TM_START=oci_result($S_stid,"TM_START");
        $TM_END=oci_result($S_stid,"TM_END");
        $NM_IOWAY=oci_result($S_stid,"NM_IOWAY")==null?"":oci_result($S_stid,"NM_IOWAY");
        $CID_IOWAY=oci_result($S_stid,"CID_IOWAY");
        $NM_TUBE_SHORT=oci_result($S_stid,"NM_TUBE_SHORT");
        $arr[]=array(
            "ID_BED"=>$BED,"DT"=>$DT,"TM"=>$TM,"CID_EXCUTE"=>$CID_EXCUTE,"CID_IO"=>$CID_IO,"QUANTITY"=>$QUANTITY,
            "NM_PHARMACY"=>$NM_PHARMACY,"NM_ITEM"=>$NM_ITEM,
            "NM_COLOR"=>$NM_COLOR,"ST_LOSS"=>$ST_LOSS,"ID_ITEM"=>$ID_ITEM,"MM_IO"=>$MM_IO,
            "TM_START"=>$TM_START,"TM_END"=>$TM_END,"NM_IOWAY"=>$NM_IOWAY,"CID_IOWAY"=>$CID_IOWAY
        );

    }
    return json_encode(ArrayGrouping($conn,$arr,$TmSTtoE,GetConFirmUser($conn,$Idpt,$INPt,$sDt)),JSON_UNESCAPED_UNICODE);
}

function ArrayGrouping($conn,$arr1,$TmSTtoE,$ComUser){

    $Sql="SELECT ST_TEXT1, ID_ITEM, NM_ITEM, MM_ITEM sSQL
      FROM NSCLSI
      WHERE CID_CLASS = 'IOTP' AND IS_ACTIVE = 'Y'
      ORDER BY ST_TEXT1, DB_DOWN";

    $stid=oci_parse($conn,$Sql);
    oci_execute($stid);
    $arr=[];
    while (oci_fetch_array($stid)){
        $ID=oci_result($stid,"ID_ITEM");
        $arr[$ID]=[];
    }
    $length=count($arr1);

    for ($i=0;$i<$length;$i++)
    {
        foreach ($arr as $key => $value){
            if ($key===$arr1[$i]['ID_ITEM']){
                array_push($arr[$key],$arr1[$i]);
            }
        };
        $arr['TmSTtoE']=$TmSTtoE;
        $arr['ComUser']=$ComUser;
    }
    return $arr;
}