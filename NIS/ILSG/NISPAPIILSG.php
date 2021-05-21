<?php
date_default_timezone_set('Asia/Taipei');
function GetILSGIniJson($conn,$idPt,$INPt,$sFm,$ID_BED,$sTraID,$sSave,$date,$sUr,$JID_NSRANK,$FORMSEQANCE_WT){
    $sql2 = "select ST_DATAA, ST_DATAB, ST_DATAC, ST_DATAD, ST_PREA, ST_PREB, ST_PREC from HIS803.NISWSIT WHERE ID_TABFORM = '$sFm'";
    $stid2 = oci_parse($conn, $sql2);
    oci_execute($stid2);

    $ST_DATAA = '';
    $ST_DATAB = '';
    $ST_DATAC = '';
    $ST_DATAD='';
    $ST_PREA = '';
    $ST_PREB = '';
    $ST_PREC = '';
    while ($row = oci_fetch_array($stid2)) {
        $ST_DATAA = $row[0]->load();
        $ST_DATAB = $row[1]->load();
        $ST_DATAC = $row[2]->load();
        $ST_DATAD = $row[3]->load();
        $ST_PREA = $row[4]->load();
        $ST_PREB = $row[5]->load();
        $ST_PREC = $row[6]->load();
    }
    $Default_DATAD_Val=array('"FORBID":[]','"IDGP":""','"LSTPT":""');

    $Replace_Default_Val=array('"FORBID":'.FORBID($conn,$idPt,$INPt),'"IDGP":'.'"'.IDGP_LSTPT($conn,$idPt,$INPt,'IDGP').'"','"LSTPT":'.'"'.IDGP_LSTPT($conn,$idPt,$INPt,'LSTPT').'"');

    $ST_DATAB=str_replace($Default_DATAD_Val,$Replace_Default_Val,$ST_DATAB);//取代後的禁打部位json
    $ST_DATD=str_replace("[]",FORBID($conn,$idPt,$INPt),$ST_DATAD);


    $ST_PREB_HasOrder=DCORDER($conn,$idPt,$ST_PREB,$ST_PREC);



    $TPsql = "INSERT INTO HIS803.NISWSTP(
                    ID_TABFORM,ID_TRANSACTION,ID_PATIENT,ID_INPATIENT,
                    DT_EXCUTE,TM_EXCUTE,ST_DATAA,ST_DATAB,ST_DATAC,ST_DATAD,ST_PREA,ST_PREB,
                    ST_PREC,ID_BED,DM_PROCESS,UR_PROCESS,JID_NSRANK,FORMSEQANCE_WT)
                     VALUES (
                     'ILSGA','$sTraID','$idPt','$INPt',
                     ' ',' ','$ST_DATAA','$ST_DATAB','$ST_DATAC','$ST_DATD','$ST_PREA',EMPTY_CLOB(),
                     '$ST_PREC','$ID_BED','$date','$sUr','$JID_NSRANK','$FORMSEQANCE_WT')
                     RETURNING  ST_PREB INTO :ST_PREB";

    $INTP_Stid = oci_parse($conn, $TPsql);
    $clob=oci_new_descriptor($conn,OCI_D_LOB);
    oci_bind_by_name($INTP_Stid,":ST_PREB",$clob,-1,OCI_B_CLOB);

    if ($INTP_Stid) {
        $result = oci_execute($INTP_Stid,OCI_NO_AUTO_COMMIT);
        if(!$result){
            $e=oci_error($INTP_Stid);
            return $e['message'];
        }
        $clob->save($ST_PREB_HasOrder);
        oci_commit($conn);

        $jsonback = array('sTraID' => $sTraID, 'sSave' => $sSave, 'FORMSEQANCE_WT' => $FORMSEQANCE_WT,'JID_NSRANK' => $JID_NSRANK);

        oci_free_statement($INTP_Stid);
        return json_encode($jsonback,JSON_UNESCAPED_UNICODE);
    } else {
        $e=oci_error($conn);
        oci_rollback($conn);
        return $e['message'];
    }

}
function GetILSGPageJson($conn,$sPg,$sTraID){


    if ($sPg=="B"){
        $SQL="SELECT ST_DATA".$sPg." ,ST_PREB FROM HIS803.NISWSTP WHERE ID_TRANSACTION=:sTraID AND ID_TABFORM = 'ILSGA'" ;
    }
    else{
        $SQL="SELECT ST_DATA".$sPg." FROM HIS803.NISWSTP WHERE ID_TRANSACTION=:sTraID AND ID_TABFORM = 'ILSGA'" ;
    }
    $stid=oci_parse($conn,$SQL);
    oci_bind_by_name($stid,':sTraID',$sTraID);
    oci_execute($stid);
    $ST_DATA='';
    $response="";
    $ST_PREB="";
    while (oci_fetch_array($stid)){
        $ST_DATA=oci_result($stid,"ST_DATA".$sPg)->load();

        if ($sPg=="B"){
            $ST_PREB=oci_result($stid,"ST_PREB")->load();
        }

    }

    if ($sPg=="B"){
        $response=array("ST_DATA"=>$ST_DATA,"ORDER"=>$ST_PREB);
    }else{
        $response=array("ST_DATA"=>$ST_DATA,"ORDER"=>"");
    }

    return json_encode($response,JSON_UNESCAPED_UNICODE);
}
function PosILSGSave($conn,$sTraID,$sFm,$sUr,$sPg,$sDt,$sTm,$pwd){


    $DateTime = date("YmdHis");
    $NowDT= substr($DateTime, 0, 4) - 1911 . substr($DateTime, -10, 10);
    $sTm=str_pad($sTm,'6','0',STR_PAD_RIGHT);


    if (!UP_TP_DATE($conn,$sDt,$sTm,$sTraID)){
        return false;
    }


    $Ssql="SELECT ST_DATAA, ST_DATAB, ST_DATAC,FORMSEQANCE,
            ID_INPATIENT,ID_PATIENT,ID_BED,DT_EXCUTE,
            TM_EXCUTE,JID_NSRANK,FORMSEQANCE_WT,UR_PROCESS FROM HIS803.NISWSTP
            WHERE ID_TABFORM = :id_TAB  AND ID_TRANSACTION = :id_TRANS";

    $Sstid=oci_parse($conn,$Ssql);

    oci_bind_by_name($Sstid,":id_TAB",$sFm);
    oci_bind_by_name($Sstid,":id_TRANS",$sTraID);
    oci_execute($Sstid,OCI_NO_AUTO_COMMIT);

    $Data='';
    while ($row=oci_fetch_array($Sstid)){
        $ST_DATAA=$row['ST_DATAA']->load();
        $ST_DATAB=$row['ST_DATAB']->load();
        $ST_DATAC=$row['ST_DATAC']->load();
        $FORMSEQANCE=$row['FORMSEQANCE'];
        $idinpt=$row['ID_INPATIENT'];
        $idpt=$row['ID_PATIENT'];
        $ID_BED=$row['ID_BED'];
        $DT_EXCUTE=$row['DT_EXCUTE'];
        $TM_EXCUTE=$row['TM_EXCUTE'];
        $JID_NSRANK=$row['JID_NSRANK'];
        $FORMSEQANCE_WT=$row['FORMSEQANCE_WT'];
        $UR_PROCESS=$row['UR_PROCESS'];
        $Data=array("ST_DATAA"=>$ST_DATAA,"ST_DATAB"=>$ST_DATAB,"ST_DATAC"=>$ST_DATAC,"ID_INPATIENT"=>$idinpt,"ID_PATIENT"=>$idpt
                    ,"FORMSEQANCE"=>$FORMSEQANCE,"ID_BED"=>$ID_BED,"DT_EXCUTE"=>$DT_EXCUTE,"TM_EXCUTE"=>$TM_EXCUTE,"JID_NSRANK"=>$JID_NSRANK
                     ,"FORMSEQANCE_WT"=>$FORMSEQANCE_WT,"UR_PROCESS"=>$UR_PROCESS);
    }

    $ST_DATAA= json_decode($Data['ST_DATAA']);
    $ST_DATAB= json_decode($Data['ST_DATAB']);
    $ST_DATAC= json_decode($Data['ST_DATAC']);

    $ID_INPATIENT=$Data['ID_INPATIENT'];
    $ID_PATIENT=$Data['ID_PATIENT'];
    $FORMSEQANCE=$Data['FORMSEQANCE'];
    $ID_BED=$Data['ID_BED'];
    $DT_EXCUTE=$Data['DT_EXCUTE'];
    $TM_EXCUTE=$Data['TM_EXCUTE'];
    $JID_NSRANK=$Data['JID_NSRANK'];
    $FORMSEQANCE_WT=$Data['FORMSEQANCE_WT'];
    $UR_PROCESS=$Data['UR_PROCESS'];

    $V_FrmSeq=GetFrmseQ($conn,'ISSG'); /*取frmseq*/



    $Has_A_QTY=$ST_DATAA[0]->{'SPRESS'}==""?$ST_DATAA[0]->{'STVAL'}:$ST_DATAA[0]->{'SPRESS'};
    $Has_B_QTY=array_filter($ST_DATAB,function ($value){
        return $value->{'SDOSE'}!="";
    });
    $Has_REGION=array_filter($ST_DATAC,function ($value){
        return count($value->{'REGION'}) >0;
    });



    if (trim($Has_A_QTY)=="" && count($Has_B_QTY)==0 && count($Has_REGION)==0){
        return json_encode(array("result"=>"false","message"=>"請確認是否有填值"),JSON_UNESCAPED_UNICODE);
    }


    //修改
    if ($sPg=="A" && $FORMSEQANCE !=""){
        tt_PosILSGCancel($conn,$ID_PATIENT,$ID_INPATIENT,'','',$sPg,$FORMSEQANCE,$sUr);

        if (!ISRecordForDateTime($conn,'ISSG',$ID_PATIENT,$ID_INPATIENT,$DT_EXCUTE,$TM_EXCUTE)){
            return json_encode(array("result"=>"false","message"=>"同一時間禁止重複輸入"),JSON_UNESCAPED_UNICODE);
        }
    }

    if ($sPg=="B" && $DT_EXCUTE!="" && $TM_EXCUTE!=""){
        tt_PosILSGCancel($conn,$ID_PATIENT,$ID_INPATIENT,$DT_EXCUTE,$TM_EXCUTE,$sPg,'',$sUr);
        if (!ISRecordForDateTime($conn,'ISLN',$ID_PATIENT,$ID_INPATIENT,$DT_EXCUTE,$TM_EXCUTE)){
            return json_encode(array("result"=>"false","message"=>"同一時間禁止重複輸入"),JSON_UNESCAPED_UNICODE);
        }
    }


    if ($sPg=="C" && $DT_EXCUTE!="" && $TM_EXCUTE!="" && $FORMSEQANCE !=""){
        tt_PosILSGCancel($conn,$ID_PATIENT,$ID_INPATIENT,$DT_EXCUTE,$TM_EXCUTE,$sPg,'',$sUr);
        if (!ISRecordForDateTime($conn,'ISLNN',$ID_PATIENT,$ID_INPATIENT,$DT_EXCUTE,$TM_EXCUTE)){
            return json_encode(array("result"=>"false","message"=>"同一時間禁止重複輸入"),JSON_UNESCAPED_UNICODE);
        }
    }



    //Insert A

    if (trim($Has_A_QTY)!="")
        {
            $resultA= json_decode(ISLG_INSERT($conn,$ST_DATAA,$ID_INPATIENT,$ID_PATIENT,$V_FrmSeq,$ID_BED,$DT_EXCUTE,$TM_EXCUTE,$JID_NSRANK,$FORMSEQANCE_WT,$NowDT,$UR_PROCESS));
            if ($resultA->{'result'}=="false"){
                return json_encode($resultA,JSON_UNESCAPED_UNICODE);
            }/*else{
                //小index儲存程序 1090401 add
                //InsertIndex($conn,'A',$sDt,$sTm,$ID_PATIENT,$ID_INPATIENT,$UR_PROCESS,$sUr,$pwd);

            };*/
        }
   //Insert B

    if (count($Has_B_QTY)>0){
       $PartAddNum =array_map(function ($value) use ($sTraID) {
            $IDGP=$value->{'IDGP'};
            $URL="http://".$_SERVER['HTTP_HOST']."/webservice/NISPWSCILREG.php?str=".AESEnCode("sFm=ILSGA&sTraID=".$sTraID."&sRgn=".$IDGP);
            $num=file_get_contents($URL);
            $LN_IDGP=$IDGP.(int)AESDeCode($num);

            $value->{'IDGP'}=$LN_IDGP;
            $value->{'FORBID'}="";
            return $value;
        },$Has_B_QTY);
        $resultB=ISLN_INSERT($conn,$PartAddNum,$ID_INPATIENT,$ID_PATIENT,$ID_BED,$DT_EXCUTE,$TM_EXCUTE,$JID_NSRANK,$FORMSEQANCE_WT,$NowDT,$UR_PROCESS);

      if ($resultB->{'result'}=="false"){
          return json_encode($resultB,JSON_UNESCAPED_UNICODE);
      }/*else{
          //小index儲存程序 1090401 add
          //InsertIndex($conn,'B',$sDt,$sTm,$ID_PATIENT,$ID_INPATIENT,$UR_PROCESS,$sUr,$pwd);
      }*/
}
   //Insert C

   if (count($Has_REGION)>0){
      if ( !ISLNC_INSERT($conn,$Has_REGION,$ID_INPATIENT,$ID_PATIENT,$ID_BED,$DT_EXCUTE,$TM_EXCUTE,$JID_NSRANK,$FORMSEQANCE_WT,$NowDT,$UR_PROCESS)){

          return json_encode( array("result"=>"false","message"=>"禁打資料有誤"),JSON_UNESCAPED_UNICODE);
      }/*else{
          //小index儲存程序 1090401 add
          //InsertIndex($conn,'B',$sDt,$sTm,$ID_PATIENT,$ID_INPATIENT,$UR_PROCESS,$sUr,$pwd);
      }*/
   }

    return json_encode(array("result"=>"true","message"=>""));
}
function GetILSGJson($conn,$IDPT,$INPt,$sUr,$sDt,$sTm,$sPg,$sFSq,$sDFL){

   $sql="SELECT (SELECT his803.nis_datetimeseq FROM DUAL) ID_TRANSB,
           his803.GetWSTPNEXTVAL ID_TRANSA, 
            CR.CA_BEDNO ID_BED, WM.formseqance_wt FORMSEQANCE_WT,
           (SELECT Max(CI.id_item) FROM HIS803.NSUSER UR, HIS803.NSCLSI CI
           WHERE  UR.jid_nsrank <> ' '
           AND UR.jid_nsrank = CI.jid_key AND CI.cid_class='RANK') JID_NSRANK,
           (SELECT PU.is_confirm FROM HIS803.NSPROU PU
           WHERE  PU.id_user  =  WM.id_user AND PU.id_program = 'NISCISLN') ID_COMFIRM   
           FROM HIS803.NSWKBD WD, HIS803.NSWKTM WM, HIS803.INACAR CR
           WHERE  CR.CA_MEDNO = '$IDPT' AND CR.CA_INPSEQ = '$INPt'
           AND  WM.id_user(+) ='$sUr'
           AND  WM.dt_offwork(+) = ' ' AND  WM.dm_cancd(+) =' ' 
           AND  WM.formseqance_wt(+)= WD.formseqance_wt
           AND WD.id_bed(+) = CR.CA_BEDNO 
           AND CR.CA_CHECK = 'Y' AND CR.CA_DIVINSU = 'N'
           AND CR.CA_CLOSE='N'";

   $stid1=oci_parse($conn,$sql);
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
   $TransKey=$ID_TRANSB.'ILSGA'.$TRANSA;

   $DM_PR=$sDt.substr($sTm,0,2);
   $responce='';


   switch ($sPg){
       case 'A':
            $responce=ISSG_Ser($conn,$sFSq,$IDPT,$INPt,$TransKey,$sDt,$sTm,$ID_BED,$DM_PR,$sUr,$JID_NSRANK,$FORMSEQANCE_WT);
           break;
       case 'B':
           $responce=ISLN_Ser($conn,$IDPT,$INPt,$sDt,$sTm,$sUr,$TransKey,$sFSq,$ID_BED,$DM_PR,$JID_NSRANK,$FORMSEQANCE_WT);
           break;
       case 'C':
          $responce=ISLN_Forbid($conn,$IDPT,$INPt,$sDt,$sTm,$sUr,$TransKey,$sFSq,$ID_BED,$DM_PR,$JID_NSRANK,$FORMSEQANCE_WT);
           break;
   }
   return $responce;
}

function PosILSGCancel($conn,$sFm,$sTraID,$sPg,$sUr,$sDataFlag){
   $DateTime = date("YmdHis");
   $STR = substr($DateTime, 0, 4);
   $STR1 = substr($DateTime, -10, 10);
   $str = $STR - 1911;
   $NowDT = $str . $STR1;

   $Ssql="SELECT ID_INPATIENT,ID_PATIENT,DT_EXCUTE,TM_EXCUTE,FORMSEQANCE from HIS803.NISWSTP
           WHERE ID_TABFORM = :ID_TABFORM  AND ID_TRANSACTION = :ID_TRANSACTION";
   $Sstid=oci_parse($conn,$Ssql);
   oci_bind_by_name($Sstid,":ID_TABFORM",$sFm);
   oci_bind_by_name($Sstid,":ID_TRANSACTION",$sTraID);

   oci_execute($Sstid,OCI_NO_AUTO_COMMIT);
   $idinpt='';
   $idpt='';
   $formseq='';
   $DT_EXCUTE='';
   $TM_EXCUTE='';
   while ($row=oci_fetch_array($Sstid)){
       $idinpt=$row['ID_INPATIENT'];
       $idpt=$row['ID_PATIENT'];
       $formseq=$row['FORMSEQANCE'];
       $DT_EXCUTE=$row['DT_EXCUTE'];
       $TM_EXCUTE=$row['TM_EXCUTE'];
   }


   $G_IS_USE_NXL=GetXmlType($conn,"ISLN")==''?'N':GetXmlType($conn,"ISLN");

   $sHops=GetHospital($conn);
   $idHospital=explode("/",$sHops)[0]; //醫院代碼
   if($G_IS_USE_NXL=="*" || $G_IS_USE_NXL=="A"){
       $sExeDTMofISSG="";
       $sExeDTMofISLN="";
       $sKeyEmr106New="";
       switch ($sPg){
           case "A":
               $sExeDTMofISSG=$DT_EXCUTE.$TM_EXCUTE;
               $sExeDTMofISLN="";
               $AHisSeq="ISLN@".$idinpt."-".$sExeDTMofISSG."-".$sExeDTMofISLN;
               $sKeyEmr106Prev=GetEMR106PrevSeq($conn,"ISLN",$AHisSeq);
               if($sExeDTMofISSG==""){
                   $sExeDTMofISSG=explode("-",$AHISLink)[1];
               }
               if($sExeDTMofISLN==""){
                   $sExeDTMofISLN=explode("-",$AHISLink)[2];
               }

               if($sDataFlag=="D"){
                   $sKeyEmr106New=ProcessEMR106($conn,$idHospital,"ISLN", "D",$sKeyEmr106Prev,$AHisSeq,$idpt,$idinpt,"ISLN"."C",$sUr);
               }else{
                   $sKeyEmr106New=ProcessEMR106($conn,$idHospital,"ISLN", "U",$sKeyEmr106Prev,$AHisSeq,$idpt,$idinpt,"ISLN"."C",$sUr);

               }
               break;
           case "B":
               $sExeDTMofISSG="";
               $sExeDTMofISLN=$DT_EXCUTE.$TM_EXCUTE;
               $AHisSeq="ISLN@".$idinpt."-".$sExeDTMofISSG."-".$sExeDTMofISLN;
               $sKeyEmr106Prev=GetEMR106PrevSeq($conn,"ISLN",$AHisSeq);
               if($sExeDTMofISSG==""){
                   $sExeDTMofISSG=explode("-",$AHISLink)[1];
               }
               if($sExeDTMofISLN==""){
                   $sExeDTMofISLN=explode("-",$AHISLink)[2];
               }

               if($sDataFlag=="D"){
                   $sKeyEmr106New=ProcessEMR106($conn,$idHospital,"ISLN", "D",$sKeyEmr106Prev,$AHisSeq,$idpt,$idinpt,"ISLN"."C",$sUr);
               }else{
                   $sKeyEmr106New=ProcessEMR106($conn,$idHospital,"ISLN", "U",$sKeyEmr106Prev,$AHisSeq,$idpt,$idinpt,"ISLN"."C",$sUr);

               }
               break;
       }
   }
   $json_reponce='';
   switch ($sPg){
       case 'A':

            $sql="UPDATE HIS803.NSISSG IG SET
                             IG.dm_cancd = :dm_cancd,
                             IG.ur_cancd = :ur_cancd
                           WHERE IG.id_patient = :id_patient
                             AND IG.id_inpatient =:id_inpatient
                             AND IG.formseqance =:formseqance  AND IG.DM_CANCD=' '";

           $stid=oci_parse($conn,$sql);
           oci_bind_by_name($stid,":dm_cancd",$NowDT);
           oci_bind_by_name($stid,":ur_cancd",$sUr);
           oci_bind_by_name($stid,":id_patient",$idpt);
           oci_bind_by_name($stid,":id_inpatient",$idinpt);
           oci_bind_by_name($stid,":formseqance",$formseq);

           $r=oci_execute($stid,OCI_NO_AUTO_COMMIT);
           if(!$r){
               $e=oci_error($stid);
               $json_reponce=json_encode(array("message"=>$e['message'],"result"=>"false"));
               oci_rollback($conn);
           }else{

               if ($G_IS_USE_NXL == "*" || $G_IS_USE_NXL == "A"){
                   // CallEmrXmlExe($sUr,"悅晟資訊","yc_his_ser","rojoyu0201","ISLN","$sDataFlag","@","",$idpt,$idinpt,$DT_EXCUTE,$TM_EXCUTE,$sKeyEmr106New);
               }
               $json_reponce=json_encode(array("message"=>"success","result"=>"true"));

           }
           break;
       case 'B':
           $sql2="UPDATE HIS803.NSISLN IL SET  
                     IL.dm_cancd =:dm_cancd,
                     IL.ur_cancd = :ur_cancd
                   WHERE IL.id_patient =:id_patient
                     AND IL.id_inpatient =:id_inpatient
                     AND IL.dt_excute = :dt_excute
                     AND IL.tm_excute = :tm_excute
                     AND IL.ur_process = :ur_process
                     AND IL.dm_cancd=' '
                     AND IL.id_order <> ' '";

           $stid2=oci_parse($conn,$sql2);
           oci_bind_by_name($stid2,":dm_cancd",$NowDT);
           oci_bind_by_name($stid2,":ur_cancd",$sUr);
           oci_bind_by_name($stid2,":id_patient",$idpt);
           oci_bind_by_name($stid2,":id_inpatient",$idinpt);
           oci_bind_by_name($stid2,":dt_excute",$DT_EXCUTE);
           oci_bind_by_name($stid2,":tm_excute",$TM_EXCUTE);
           oci_bind_by_name($stid2,":ur_process",$sUr);


           $r=oci_execute($stid2,OCI_NO_AUTO_COMMIT);
           if(!$r){
               oci_rollback($conn);
               $e=oci_error($stid2);
               $json_reponce=json_encode(array("message"=>$e['message'],"result"=>"false"));

           }else{
               if ($G_IS_USE_NXL == "*" || $G_IS_USE_NXL == "A"){
                   // CallEmrXmlExe($sUr,"悅晟資訊","yc_his_ser","rojoyu0201","ISLN","$sDataFlag","@","",$idpt,$idinpt,$DT_EXCUTE,$TM_EXCUTE,$sKeyEmr106New);
               }
               $json_reponce=json_encode(array("message"=>"success","result"=>"true"));

           }
           break;
       case 'C':
           $sql3="UPDATE HIS803.NSISLN IL SET
                  IL.dm_cancd =:dm_cancd,
                   IL.ur_cancd = :ur_cancd
                   WHERE IL.id_patient =:id_patient
                   AND IL.id_inpatient =:id_inpatient
                   AND IL.dt_excute = :dt_excute
                   AND IL.tm_excute = :tm_excute
                   AND IL.ur_process = :ur_process
                   AND IL.dm_cancd=' '
                 AND IL.id_order = ' '";

           $stid3=oci_parse($conn,$sql3);

           oci_bind_by_name($stid3,":dm_cancd",$NowDT);
           oci_bind_by_name($stid3,":ur_cancd",$sUr);
           oci_bind_by_name($stid3,":id_patient",$idpt);
           oci_bind_by_name($stid3,":id_inpatient",$idinpt);
           oci_bind_by_name($stid3,":dt_excute",$DT_EXCUTE);
           oci_bind_by_name($stid3,":tm_excute",$TM_EXCUTE);
           oci_bind_by_name($stid3,":ur_process",$sUr);

           $r=oci_execute($stid3,OCI_NO_AUTO_COMMIT);
           if(!$r){
               oci_rollback($conn);
               $e=oci_error($stid3);
               $json_reponce=json_encode(array("message"=>$e['message'],"result"=>"false"));
           }else{
               $json_reponce=json_encode(array("message"=>"success","result"=>"true"));
           }
           break;
   }
    oci_commit($conn);
   oci_free_statement($Sstid);
   return $json_reponce;
}
function ISLG_INSERT($conn,$arr=array(),$ID_INPATIENT,$ID_PATIENT,$V_FrmSeq,$ID_BED,$DT_EXCUTE,$TM_EXCUTE,$JID_NSRANK,$FORMSEQANCE_WT,$NowDT,$UR_PROCESS){
   $Execute_result=array_map(function ($value) use ($conn, $V_FrmSeq, $UR_PROCESS, $NowDT, $FORMSEQANCE_WT, $JID_NSRANK, $ID_BED, $TM_EXCUTE, $DT_EXCUTE, $ID_INPATIENT, $ID_PATIENT) {
        $idFrm=$value->{'idFrm'};
        $SFRMSEQ=$value->{'SFRMSEQ'};
        $IDTM=$value->{'IDTM'};
        $IDGP=$value->{'IDGP'};
        $STVAL=$value->{'STVAL'};
        $SPRESS=$value->{'SPRESS'};
        $MMVAL=$value->{'MMVAL'};

        $QTY=$STVAL ==''?$SPRESS:$STVAL;
        $sql="INSERT INTO his803.NSISSG(DATESEQANCE,FORMSEQANCE,ID_PATIENT,ID_INPATIENT,
               NO_OPDSEQ,DT_EXCUTE,TM_EXCUTE,ST_MEASURE,JID_UNIT,JID_TIME,
               CID_MEAL,JID_TOOL,MM_TPRS,ID_MESSAGE,
               ID_BED,JID_NSRANK,FORMSEQANCE_WT,
               FORMSEQANCE_FL,DM_PROCESS,UR_PROCESS,DM_CANCD,UR_CANCD)
               VALUES
               (his803.NIS_DATETIMESEQ,'$V_FrmSeq','$ID_PATIENT','$ID_INPATIENT',
               0,'$DT_EXCUTE','$TM_EXCUTE','$QTY',Nvl('ISSG00000001',' '),Nvl('$IDTM',' '),
               Nvl('$IDGP',' '),' ',Nvl('$MMVAL',' '),' ',
               Nvl('$ID_BED',' '),Nvl('$JID_NSRANK',' '),Nvl('$FORMSEQANCE_WT',' '),
              ' ','$NowDT','$UR_PROCESS',' ',' ')";

        $stid=oci_parse($conn,$sql);
        if (!$stid){
            return oci_error($conn)['message'];
        }
        $Execute= oci_execute($stid,OCI_NO_AUTO_COMMIT);
        if (!$Execute){
            return oci_error($stid)['message'];
        }
        return 'true';
    },$arr);

    $Has_ErrorMsg=array_filter($Execute_result,function ($value){
        return strrpos($value,"ORA",0) !==false;
    });

    $result=count($Has_ErrorMsg)>0?'false':'true';
    $Msg= str_replace('true','',join(" ",$Has_ErrorMsg));



    return   json_encode(array("result"=>$result,"message"=>$Msg),JSON_UNESCAPED_UNICODE);

}
function ISLN_INSERT($conn,$arr=array(),$ID_INPATIENT,$ID_PATIENT,$ID_BED,$DT_EXCUTE,$TM_EXCUTE,$JID_NSRANK,$FORMSEQANCE_WT,$NowDT,$UR_PROCESS){

    $Execute_result=array_map(function ($value) use ($conn, $UR_PROCESS, $NowDT, $FORMSEQANCE_WT, $JID_NSRANK, $ID_BED, $TM_EXCUTE, $DT_EXCUTE, $ID_INPATIENT, $ID_PATIENT) {
        $idFrm=$value->{'idFrm'};
        $SFRMDTSEQ=$value->{'SFRMDTSEQ'};
        $ITNO=$value->{'ITNO'};
        $IDTM=$value->{'IDTM'};
        $IDGP=$value->{'IDGP'};
        $MED_ID=$value->{'ID'};
        $MED_NM=$value->{'STM'};
        $USEF=$value->{'USEF'};
        $Last_PT=$value->{'LSTPT'};
        $Qty=$value->{'SDOSE'};
        $DBDOSE=$value->{'DBDOSE'};
        $FORBID=$value->{'FORBID'};

        $sql="INSERT INTO his803.NSISLN (DATESEQANCE,ID_PATIENT,ID_INPATIENT,NO_OPDSEQ,DT_EXCUTE, TM_EXCUTE,
                JID_TIME,ID_REGION, ID_ORDER, NM_ORDER,DB_DOSE, ST_DOSE, ST_USENO,
               DT_TAKEDRUG, TM_TAKEDRUG, JID_FORBID, NO_PAGE,ID_BED, JID_NSRANK,
                FORMSEQANCE_WT,DM_PROCESS, UR_PROCESS, DM_CANCD, UR_CANCD)
                VALUES 
                (his803.NIS_DATETIMESEQ,'$ID_PATIENT',Nvl('$ID_INPATIENT', ' '),0, '$DT_EXCUTE','$TM_EXCUTE',
                NVL('$IDTM',' '),'$IDGP',NVL('$MED_ID',' '),NVL('$MED_NM',' '),'-1','$Qty','$USEF',
                NVL('$DT_EXCUTE',' '),NVL('$TM_EXCUTE',' '),NVL(' ',' '),'$ITNO',NVL('$ID_BED',' '),NVL('$JID_NSRANK',' '),
                NVL('$FORMSEQANCE_WT',' '),'$NowDT','$UR_PROCESS',' ',' ')";
        $Stid=oci_parse($conn,$sql);
        if (!$Stid){
            return oci_error($conn)['message'];
        }
        $Execute=oci_execute($Stid,OCI_NO_AUTO_COMMIT);
        if (!$Execute){
            return oci_error($Stid)['message'];
        }

        return 'true';
    },$arr);

    $Has_ErrorMsg=array_filter($Execute_result,function ($value){
        return strrpos($value,"ORA",0) !==false;
    });

    $result=count($Has_ErrorMsg)>0?'false':'true';
    $Msg= str_replace('true','',join(" ",$Has_ErrorMsg));



    return   json_encode(array("result"=>$result,"message"=>$Msg),JSON_UNESCAPED_UNICODE);

   /*foreach ($arr as &$value){
       $idFrm=$value->{'idFrm'};
       $SFRMDTSEQ=$value->{'SFRMDTSEQ'};
       $ITNO=$value->{'ITNO'};
       $IDTM=$value->{'IDTM'};
       $IDGP=$value->{'IDGP'};
       $MED_ID=$value->{'ID'};
       $MED_NM=$value->{'STM'};
       $USEF=$value->{'USEF'};
       $Last_PT=$value->{'LSTPT'};
       $Qty=$value->{'SDOSE'};
       $DBDOSE=$value->{'DBDOSE'};
       $FORBID=$value->{'FORBID'};

       $sql="INSERT INTO his803.NSISLN (DATESEQANCE,ID_PATIENT,ID_INPATIENT,NO_OPDSEQ,DT_EXCUTE, TM_EXCUTE,
                JID_TIME,ID_REGION, ID_ORDER, NM_ORDER,DB_DOSE, ST_DOSE, ST_USENO,
               DT_TAKEDRUG, TM_TAKEDRUG, JID_FORBID, NO_PAGE,ID_BED, JID_NSRANK,
                FORMSEQANCE_WT,DM_PROCESS, UR_PROCESS, DM_CANCD, UR_CANCD)
                VALUES 
                (his803.NIS_DATETIMESEQ,'$ID_PATIENT',Nvl('$ID_INPATIENT', ' '),0, '$DT_EXCUTE','$TM_EXCUTE',
                NVL('$IDTM',' '),'$IDGP',NVL('$MED_ID',' '),NVL('$MED_NM',' '),'-1',NVL('$Qty',0),NVL('$USEF',' '),
                NVL('$DT_EXCUTE',' '),NVL('$TM_EXCUTE',' '),NVL(' ',' '),'$ITNO',NVL('$ID_BED',' '),NVL('$JID_NSRANK',' '),
                NVL('$FORMSEQANCE_WT',' '),'$NowDT','$UR_PROCESS',' ',' ')";
       $Stid=oci_parse($conn,$sql);
       if (!$Stid){

           echo oci_error($conn)['message'];
           return false;
           break;

       }
       $Execute=oci_execute($Stid,OCI_NO_AUTO_COMMIT);
       if (!$Execute){
           echo oci_error($Stid)['message'];
           return false;
           break;
       }
   }
   return true;*/


}
function ISLNC_INSERT($conn,$arr=array(),$ID_INPATIENT,$ID_PATIENT,$ID_BED,$DT_EXCUTE,$TM_EXCUTE,$JID_NSRANK,$FORMSEQANCE_WT,$NowDT,$UR_PROCESS){


    $Region_Arr=$arr[0]->{'REGION'};
    $Reason=$arr[0]->{'NO_MMVAL'};

    for($i=0;$i<count($Region_Arr);$i++){
        for ($j=1;$j<=8;$j++){
        $ID_REGION=$Region_Arr[$i].$j;

            $sql="INSERT INTO his803.NSISLN (DATESEQANCE,ID_PATIENT,ID_INPATIENT,NO_OPDSEQ,DT_EXCUTE, TM_EXCUTE,
                JID_TIME,ID_REGION, ID_ORDER, NM_ORDER,DB_DOSE, ST_DOSE, ST_USENO,
               DT_TAKEDRUG, TM_TAKEDRUG, JID_FORBID, NO_PAGE,ID_BED, JID_NSRANK,
                FORMSEQANCE_WT,DM_PROCESS, UR_PROCESS, DM_CANCD, UR_CANCD)
                VALUES 
                (his803.NIS_DATETIMESEQ,'$ID_PATIENT',Nvl('$ID_INPATIENT', ' '),0, '$DT_EXCUTE','$TM_EXCUTE',
                ' ','$ID_REGION',' ',' ','-1',' ',' ',
                NVL('$DT_EXCUTE',' '),NVL('$TM_EXCUTE',' '),'$Reason','-1',NVL('$ID_BED',' '),NVL('$JID_NSRANK',' '),
                NVL('$FORMSEQANCE_WT',' '),'$NowDT','$UR_PROCESS',' ',' ')";

            $stid=oci_parse($conn,$sql);
            if (!$stid){
                echo oci_error($conn)['message'];
                return false;
                break;
            }
            $Excute=oci_execute($stid,OCI_NO_AUTO_COMMIT);
            if (!$Excute){
                echo oci_error($stid)['message'];
                return false;
                break;
            }

        }
    }

    return true;
}

/*
function GetFrmseQ($conn){
   $sql_Serch="SELECT NO_TABFORM FROM  HIS803.NSTBMF  WHERE ID_TABFORM= 'ISSG'";
   $stid=oci_parse($conn,$sql_Serch);
   oci_execute($stid,OCI_NO_AUTO_COMMIT);
   $NO_TABFORM='';
   while (oci_fetch_array($stid)){
       $NO_TABFORM=ociresult($stid,'NO_TABFORM');
   }

   //回壓frmseq
    $PAD_NO_TABFORM  = str_pad($NO_TABFORM,10,0,STR_PAD_LEFT);
    $FORMseq=$NO_TABFORM+1;
    $sql_update="UPDATE  HIS803.NSTBMF SET  NO_TABFORM=:NO_TAB WHERE ID_TABFORM= 'ISSG'";
    $up_stid=oci_parse($conn,$sql_update);

    oci_bind_by_name($up_stid,":NO_TAB",$FORMseq);
    oci_execute($up_stid,OCI_NO_AUTO_COMMIT);

    return 'ISSG'.$PAD_NO_TABFORM;


}*/



function DCORDER($conn,$ID_INPATIENT,$ST_PREB,$ST_PREC){
    $date = date("Ymd");
    $STR = substr($date, 0, 4);
    $STR1 = substr($date, 4, 7);
    $y = $STR - 1911;
    $DATE=(string)$y.(string)$STR1;


    $order_sql="SELECT  JID_KEY, DA_DIACODE, DA_EGNAME, 1 DCSORT,  DT_QTY_PERTIME QTY_PERTIME, DT_USENO USENO 
                    FROM NSCLSI, TOPDIA, INADET, INAHDR 
                    WHERE CID_CLASS='ISOA' AND IS_ACTIVE = 'Y'
                    AND NM_ITEM = DA_ATCCODE
                    AND HD_TYPE = 'S' AND HD_CANCD = 'N'
                    AND HD_INPSEQ = DT_INPSEQ AND HD_DIVNO = DT_DIVNO
                    AND HD_LOOKDT = DT_LOOKDT AND HD_SEQ = DT_SEQ
                    AND DT_CANCD = 'N' AND DT_OPDORDER NOT IN ('1','D', 'Z')
                    AND DT_DIACODE= DA_DIACODE 
                    AND HD_INPSEQ = '$ID_INPATIENT'
                    AND HD_LOOKDT = '$DATE'
                    UNION ALL
                    SELECT  JID_KEY, DA_DIACODE, DA_EGNAME, 2 DCSORT,  LO_QTY_PERTIME QTY_PERTIME, LO_USENO USENO  
                    FROM NSCLSI, TOPDIA, INALOR 
                    WHERE CID_CLASS='ISOA' AND IS_ACTIVE = 'Y'
                    AND NM_ITEM = DA_ATCCODE AND LO_DIACODE = DA_DIACODE
                    AND LO_INPSEQ = '$ID_INPATIENT'
                    AND LO_BEGDATE<= '$DATE'
                    AND (LO_DCDATE = ' ' OR LO_DCDATE >= '$DATE')
                    ORDER BY DCSORT";

    $ORDER_stid=oci_parse($conn,$order_sql);
    oci_execute($ORDER_stid);
    $obj=[];
    $JID_KEY='';

    while (oci_fetch_array($ORDER_stid)){
        $JID_KEY=oci_result($ORDER_stid,'JID_KEY');
        $DIA=oci_result($ORDER_stid,'DA_DIACODE');
        $STM=oci_result($ORDER_stid,'DA_EGNAME');
        $DCSORT=oci_result($ORDER_stid,'DCSORT');
        $QTY=oci_result($ORDER_stid,'QTY_PERTIME');
        $USENO=oci_result($ORDER_stid,'USENO');

        $obj[]=array("JID_KEY"=>"$JID_KEY","DIA"=>"$DIA","STM"=>"$STM","DCSORT"=>"$DCSORT","QTY"=>"$QTY","USENO"=>"$USENO");

    }

    $obj_json=(array)json_encode($obj,JSON_UNESCAPED_UNICODE);
    $jsonArrAY=[];
    $newST_PREB="";
    if($JID_KEY){
        $newST_PREB=str_replace('{"str_replace"}',$obj_json[0],$ST_PREB);

    }else{
        $newST_PREB=str_replace('{"str_replace"},','',$ST_PREB);

    }
    $jsonArrAY[]=array("ISULING"=>stripslashes($newST_PREB),"FUSEQ"=>stripslashes($ST_PREC));


    return json_encode($jsonArrAY,JSON_UNESCAPED_UNICODE);
}
function UP_TP_DATE($conn,$sDt,$sTm,$sTraID){

    $UPTMSQL="UPDATE HIS803.NISWSTP SET TM_EXCUTE=:TM,DT_EXCUTE=:DT  WHERE ID_TRANSACTION=:id_TRAN";
    $upstid=oci_parse($conn,$UPTMSQL);
    oci_bind_by_name($upstid,":TM",$sTm);
    oci_bind_by_name($upstid,":DT",$sDt);
    oci_bind_by_name($upstid,":id_TRAN",$sTraID);
    $Execute=oci_execute($upstid,OCI_NO_AUTO_COMMIT);
    if (!$Execute){
        echo oci_error($upstid)['message'];
        return false;
    }
    return true;
}
function UPDATE_Data($sFm,$transkey,$page,$sUser,$sCidFlag){
    $Del_url="http://".$_SERVER['HTTP_HOST']."/webservice/NISPWSDELILSG.php?str=".AESEnCode("sFm=".$sFm."&sTraID=".$transkey."&sPg=".$page."&sCidFlag".$sCidFlag."&sUr=".$sUser);
    file_get_contents($Del_url);
}

function ISSG_Ser($conn,$sFSq,$idPt,$INPt,$TransKey,$Dt,$sTm,$ID_BED,$DM_PR,$sUr,$JID_NSRANK,$FORMSEQANCE_WT){

    $sql1="SELECT IG.dt_excute, IG.tm_excute, 
                CASE WHEN IG.st_measure < '9999' THEN IG.st_measure ELSE ' ' END STVAL, 
                CASE WHEN IG.st_measure <'9999' THEN ' ' ELSE IG.st_measure END SPRESS,  
                IG.jid_time IDTM, IG.cid_meal IDGP, IG.mm_tprs MMVAL
                 FROM HIS803.NSISSG IG
                WHERE IG.id_patient =:idPt  AND  IG.id_inpatient =:INPt
                AND IG.formseqance = :sFSq
                AND IG.DM_CANCD= ' '";

    $stid=oci_parse($conn,$sql1);
    oci_bind_by_name($stid,":idPt",$idPt);
    oci_bind_by_name($stid,":INPt",$INPt);
    oci_bind_by_name($stid,":sFSq",$sFSq);
    oci_execute($stid);

    $JSONarray=[];
    $DT_EXCUTE='';
    $TM_EXCUTE='';

    while($row=oci_fetch_array($stid))
    {
        $DT_EXCUTE=$row['DT_EXCUTE'];
        $TM_EXCUTE=$row['TM_EXCUTE'];
        $Stval=$row['STVAL'];
        $SPRESS=$row['SPRESS'];
        $JID_TIME=$row['IDTM'];
        $CID_MEAL=$row['IDGP'];
        $MM_TPRS=$row['MMVAL'];

        $JSONarray[]=array("IDGP"=>$CID_MEAL,"IDTM"=>$JID_TIME,"MMVAL"=>$MM_TPRS,"SFRMSEQ"=>$sFSq,"SPRESS"=>$SPRESS,"STVAL"=>$Stval,"idFrm"=>"ISSG");
    }

    $json_STDA=json_encode($JSONarray,JSON_UNESCAPED_UNICODE);

  $sql2="SELECT WI.ST_DATAB,WI.ST_DATAC, WI.ST_PREA, WI.ST_PREB FROM HIS803.NISWSIT WI
                WHERE WI.ID_TABFORM = 'ILSGA'";
    $stid2=oci_parse($conn,$sql2);
    oci_execute($stid2);

    $ST_DATAB='';
    $ST_DATAC='';
    $ST_PREA='';
    $ST_PREB='';

    while ($row=oci_fetch_array($stid2)){
        $ST_DATAB=$row[0]->load();
        $ST_DATAC=$row[1]->load();
        $ST_PREA=$row[2]->load();
        $ST_PREB=$row[3]->load();

    }

    $TPsql="INSERT INTO HIS803.NISWSTP(ID_TABFORM,ID_TRANSACTION,ID_PATIENT,ID_INPATIENT,DT_EXCUTE,
            TM_EXCUTE,ST_DATAA,ST_DATAB,ST_DATAC,ST_PREA,ST_PREB,FORMSEQANCE,ID_BED,DM_PROCESS,UR_PROCESS,JID_NSRANK,FORMSEQANCE_WT)
             VALUES ('ILSGA','$TransKey','$idPt','$INPt','$Dt',
             '$sTm','$json_STDA','$ST_DATAB','$ST_DATAC','$ST_PREA',EMPTY_CLOB(),'$sFSq','$ID_BED','$DM_PR','$sUr','$JID_NSRANK','$FORMSEQANCE_WT')
             RETURNING  ST_PREB INTO :ST_PREB";



    $stid3=oci_parse($conn,$TPsql);
    $clob=oci_new_descriptor($conn,OCI_D_LOB);
    oci_bind_by_name($stid3,":ST_PREB",$clob,-1,OCI_B_CLOB);
    $r=oci_execute($stid3,OCI_NO_AUTO_COMMIT);
    if(!$r){
        oci_rollback($conn);
        $e=oci_error($stid3);
        echo $e['message'];
        return false;
    }else{
        $clob->save($ST_PREB);
        oci_commit($conn);
        oci_free_statement($stid);
        oci_close($conn);
    }

    $responce=array("ID_PATIENT"=>$idPt,"ID_INPATIENT"=>$INPt,"ID_TRANSACTION"=>$TransKey,
                    "DT_EXCUTE"=>$DT_EXCUTE,"TM_EXCUTE"=>substr($TM_EXCUTE,'0',4),"DATA"=>$JSONarray);

    return json_encode($responce,JSON_UNESCAPED_UNICODE);
}
function ISLN_Ser($conn,$idPt,$INPt,$sDT,$sTM,$sUr,$TransKey,$sFSq,$ID_BED,$DM_PR,$JID_NSRANK,$FORMSEQANCE_WT){
    $sql = "SELECT IL.dt_excute, IL.tm_excute, IL.jid_time IDTM, substr(IL.id_region, 1, 1) IDGP,
            IL.id_order OID, IL.nm_order STM, IL.db_dose DBDOSE, IL.st_dose SDOSE, IL.st_useno USEF,
            ' ' LSTPT
            FROM HIS803.NSISLN IL
            WHERE IL.id_patient ='$idPt'  AND  IL.id_inpatient ='$INPt'
            AND IL.dt_excute = '$sDT' AND  IL.tm_excute = '$sTM'
            AND IL.ur_process = '$sUr'
            AND IL.dm_cancd=' '
            AND IL.id_order <> ' '
            ";


    $stid = oci_parse($conn, $sql);
    oci_execute($stid);

    $sql2 = "select ST_DATAA,ST_DATAB,ST_DATAC,WI.ST_PREA, WI.ST_PREB from HIS803.NISWSIT WI
                WHERE WI.ID_TABFORM = 'ILSGA'";
    $stid2 = oci_parse($conn,$sql2);
    oci_execute($stid2);

    $ST_DATAA = '';
    $ST_DATAB = '';
    $ST_DATAC='';
    $ST_PREA = '';
    $ST_PREB = '';

    while ($row = oci_fetch_array($stid2)) {
        $ST_DATAA = $row[0]->load();
        $ST_DATAB = $row[1]->load();
        $ST_DATAC = $row[2]->load();
        $ST_PREA = $row[3]->load();
        $ST_PREB = $row[4]->load();
    }

    $DATAB_default=array_map(function ($value){
        $value->{'FORBID'}="";
        return $value;
    },json_decode($ST_DATAB));



    $DT='';
    $TM='';
    $ID_REGION="";
    for ($i=0;$i<count(oci_fetch_array($stid));$i++){
        $DT = oci_result($stid, "DT_EXCUTE");
        $TM = oci_result($stid, "TM_EXCUTE");
        $JID_TIME = oci_result($stid, "IDTM");
        $ID_REGION = oci_result($stid, "IDGP");
        $ID_ORDER = oci_result($stid, "OID");
        $NM_ORDER = oci_result($stid, "STM");
        $DB_DOSE = oci_result($stid, "DB_DOSE");
        $ST_DOSE = oci_result($stid, "SDOSE");
        $ST_USENO = oci_result($stid, "USEF");
        $LSTPT = oci_result($stid, "LSTPT");

        $DATAB_default[$i]->{'SFRMDTSEQ'}=$sFSq;
        $DATAB_default[$i]->{'IDTM'}=$JID_TIME;


        $DATAB_default[$i]->{'ID'}=$ID_ORDER;
        $DATAB_default[$i]->{'STM'}=$NM_ORDER;
        $DATAB_default[$i]->{'SDOSE'}=$ST_DOSE;
        $DATAB_default[$i]->{'USEF'}=$ST_USENO;

    }

    $json_STDB=array_map(function ($value) use ($ID_REGION) {
        $value->{'IDGP'}=$ID_REGION;
        return $value;
    },$DATAB_default);



    $TPsql = "INSERT INTO HIS803.NISWSTP(ID_TABFORM,ID_TRANSACTION,ID_PATIENT,ID_INPATIENT,DT_EXCUTE,
            TM_EXCUTE,ST_DATAA,ST_DATAB,ST_DATAC,ST_PREA,ST_PREB,FORMSEQANCE,ID_BED,DM_PROCESS,UR_PROCESS,JID_NSRANK,FORMSEQANCE_WT)
             VALUES ('ILSGA','$TransKey','$idPt','$INPt','$DT',
             '$TM','$ST_DATAA','$json_STDB','$ST_DATAC','$ST_PREA',EMPTY_CLOB(),'$sFSq','$ID_BED','$DM_PR','$sUr','$JID_NSRANK','$FORMSEQANCE_WT')
             RETURNING  ST_PREB INTO :ST_PREB";


    $stid3 = oci_parse($conn, $TPsql);
    $clob=oci_new_descriptor($conn,OCI_D_LOB);
    oci_bind_by_name($stid3,":ST_PREB",$clob,-1,OCI_B_CLOB);
    $r = oci_execute($stid3,OCI_NO_AUTO_COMMIT);

    if (!$r) {
        oci_rollback($conn);
        $e=oci_error($stid3);
        echo $e['message'];
        return false;
    } else {
        $clob->save($ST_PREB);
        oci_commit($conn);
        oci_free_statement($stid);
        oci_close($conn);
    }


    $responce=array("ID_PATIENT"=>$idPt,"ID_INPATIENT"=>$INPt,"ID_TRANSACTION"=>$TransKey,"DT_EXCUTE"=>$DT,"TM_EXCUTE"=>substr($TM,'0',4),"DATA"=>$json_STDB);

    return json_encode($responce,JSON_UNESCAPED_UNICODE);
}
function ISLN_Forbid($conn,$idPt,$INPt,$sDT,$sTM,$sUr,$TransKey,$sFSq,$ID_BED,$DM_PR,$JID_NSRANK,$FORMSEQANCE_WT){
    $sql = "SELECT DISTINCT IL.dt_excute,IL.tm_excute, substr(IL.id_region, 1, 1) region,jid_forbid NO_MMVAL
            ,JID_TIME as IDTM
            FROM HIS803.NSISLN IL
            WHERE IL.id_patient ='$idPt'  AND  IL.id_inpatient ='$INPt'
            AND IL.dt_excute = '$sDT' AND  IL.tm_excute = '$sTM'
            AND IL.ur_process = '$sUr'
            AND IL.dm_cancd=' '
            AND IL.id_order = ' '";

    $stid = oci_parse($conn, $sql);
    oci_execute($stid);

    $sql2 = "select ST_DATAA,ST_DATAB,ST_DATAC,WI.ST_PREA, WI.ST_PREB from HIS803.NISWSIT WI
                WHERE WI.ID_TABFORM = 'ILSGA'";
    $stid2 = oci_parse($conn,$sql2);
    oci_execute($stid2);

    $ST_DATAA = '';
    $ST_DATAB = '';
    $ST_DATAC='';
    $ST_PREA = '';
    $ST_PREB = '';
    while ($row = oci_fetch_array($stid2)) {
        $ST_DATAA = $row[0]->load();
        $ST_DATAB = $row[1]->load();
        $ST_DATAC = $row[2]->load();
        $ST_PREA = $row[3]->load();
        $ST_PREB = $row[4]->load();
    }



    $DATAC_Default=array_map(function ($value){
        $value->{'NO_MMVAL'}="";
        return $value;
    },json_decode($ST_DATAC));

    $DT_EXCUTE='';
    $TM_EXCUTE='';

    while ($row = oci_fetch_array($stid)) {
        $DT_EXCUTE = oci_result($stid, "DT_EXCUTE");
        $TM_EXCUTE = oci_result($stid, "TM_EXCUTE");
        $REGION = oci_result($stid, "REGION");
        $NO_MMAL = oci_result($stid, "NO_MMVAL");


        $NO_MMAL=$NO_MMAL=' '?$NO_MMAL:'';
        array_push($DATAC_Default[0]->{'REGION'},$REGION);
        $DATAC_Default[0]->{'NO_MMVAL'}=$NO_MMAL;

    }




    $json_STDC=json_encode($DATAC_Default,JSON_UNESCAPED_UNICODE);

    $TPsql = "INSERT INTO HIS803.NISWSTP(ID_TABFORM,ID_TRANSACTION,ID_PATIENT,ID_INPATIENT,DT_EXCUTE,
            TM_EXCUTE,ST_DATAA,ST_DATAB,ST_DATAC,ST_PREA,ST_PREB,FORMSEQANCE,ID_BED,DM_PROCESS,UR_PROCESS,JID_NSRANK,FORMSEQANCE_WT)
             VALUES ('ILSGA','$TransKey','$idPt','$INPt','$DT_EXCUTE',
             '$TM_EXCUTE','$ST_DATAA','$ST_DATAB','$json_STDC','$ST_PREA',EMPTY_CLOB(),'$sFSq','$ID_BED','$DM_PR','$sUr','$JID_NSRANK','$FORMSEQANCE_WT')
              RETURNING  ST_PREB INTO :ST_PREB";

    $stid3 = oci_parse($conn, $TPsql);
    $clob=oci_new_descriptor($conn,OCI_D_LOB);
    oci_bind_by_name($stid3,":ST_PREB",$clob,-1,OCI_B_CLOB);
    $r = oci_execute($stid3,OCI_NO_AUTO_COMMIT);
    if (!$r) {
        oci_rollback($conn);
        $e=oci_error($stid3);
        echo $e['message'];
        return false;
    } else {
        $clob->save($ST_PREB);
        oci_commit($conn);
        oci_free_statement($stid);
        oci_close($conn);
    }

    $responce=array("ID_PATIENT"=>$idPt,"ID_INPATIENT"=>$INPt,"ID_TRANSACTION"=>$TransKey,"SFRMDTSEQ"=>$sFSq
                    ,"DT_EXCUTE"=>$DT_EXCUTE,"TM_EXCUTE"=>substr($TM_EXCUTE,'0',4),
                    "DATA"=>$DATAC_Default);

    return json_encode($responce,JSON_UNESCAPED_UNICODE);

}

function tt_PosILSGCancel($conn,$idpt,$idinpt,$sDt,$sTm,$sPg,$formseq,$sUr){
    $DateTime = date("YmdHis");
    $STR = substr($DateTime, 0, 4);
    $STR1 = substr($DateTime, -10, 10);
    $str = $STR - 1911;
    $NowDT = $str . $STR1;

    $sql="";
    $Bind_Nm="";
    switch ($sPg){
        case "A":
            $sql="UPDATE HIS803.NSISSG IG SET
           IG.dm_cancd = :dm_cancd,
          IG.ur_cancd = :ur_cancd
         WHERE IG.id_patient = :id_patient
         AND IG.id_inpatient =:id_inpatient
         AND IG.formseqance =:formseqance  AND IG.DM_CANCD=' '
            ";
            $Bind_Nm=array(":dm_cancd"=>$NowDT,":ur_cancd"=>$sUr,":id_patient"=>$idpt,
                ":id_inpatient"=>$idinpt,":formseqance"=>$formseq);
            break;
        case "B":

            $sql="UPDATE HIS803.NSISLN IL SET  
                     IL.dm_cancd =:dm_cancd,
                     IL.ur_cancd = :ur_cancd
                   WHERE IL.id_patient =:id_patient
                     AND IL.id_inpatient =:id_inpatient
                     AND IL.dt_excute = :dt_excute
                     AND IL.tm_excute = :tm_excute
                     AND IL.ur_process = :ur_process
                     AND IL.dm_cancd=' '
                     AND IL.id_order <> ' '";


            $Bind_Nm=array(":dm_cancd"=>$NowDT,":ur_cancd"=>$sUr,":id_patient"=>$idpt,
                ":id_inpatient"=>$idinpt,":dt_excute"=>$sDt,":tm_excute"=>$sTm,
                ":ur_process"=>$sUr);

            break;
        case "C":
            $sql="UPDATE HIS803.NSISLN IL SET
                  IL.dm_cancd =:dm_cancd,
                   IL.ur_cancd = :ur_cancd
                   WHERE IL.id_patient =:id_patient
                   AND IL.id_inpatient =:id_inpatient
                   AND IL.dt_excute = :dt_excute
                   AND IL.tm_excute = :tm_excute
                   AND IL.ur_process = :ur_process
                   AND IL.dm_cancd=' '
                 AND IL.id_order = ' '";

            $Bind_Nm=array(":dm_cancd"=>$NowDT,":ur_cancd"=>$sUr,":id_patient"=>$idpt,
                ":id_inpatient"=>$idinpt,":dt_excute"=>$sDt,":tm_excute"=>$sTm,
                ":ur_process"=>$sUr);

            break;
    }

    $stid=oci_parse($conn,$sql);
    foreach ($Bind_Nm as $key=>$item) {
        oci_bind_by_name($stid,$key,$Bind_Nm[$key]);
    }
    $r=oci_execute($stid,OCI_NO_AUTO_COMMIT);
    if (!$r){
        echo oci_error($stid)['message'];
    }

}
function InsertIndex($conn,$Page,$sDt,$sTm,$ID_PATIENT,$ID_INPATIENT,$UR_PROCESS,$sUr,$pwd){
    //小index儲存程序 1090401 add
    $G_IS_USE_NXL=GetXmlType($conn,"ISLN")==''?'N':GetXmlType($conn,"ISLN");

    $sExeDTMofISSG=$Page=="A"?$sDt.$sTm:"";
    $sExeDTMofISLN=$Page=="A"?"":$sDt.$sTm;

    $AHISLink="ISLN@".$ID_INPATIENT."-".trim($sExeDTMofISSG)."-".trim($sExeDTMofISLN);
    $sHops=GetHospital($conn);
    $idHospital=explode("/",$sHops)[0]; //醫院代碼

    /*      $sHopsNo=explode("/",$sHops)[1];
           $sHopsNo=(trim($sHopsNo)!="")?" ".$sHopsNo:"";*/


    $sCidFlag="I";
    if($G_IS_USE_NXL=="*" || $G_IS_USE_NXL=="A"){
        $sKeyEmr106Prev=GetEMR106PrevSeq($conn,"ISLN",$AHISLink);
        $sKeyEmr106New=ProcessEMR106($conn, $idHospital, "ISLN", $sCidFlag,$sKeyEmr106Prev,$AHISLink,$ID_INPATIENT,$ID_INPATIENT,"ISLN"."C",$sUr);
        $AKeyEmr106New=$sKeyEmr106New;

        if($sCidFlag=="U"){
            CallEmrXmlExe($UR_PROCESS,$sUr,"yc_his_ser ",$pwd,"ISLN",$sCidFlag,"@","",$ID_PATIENT,$ID_INPATIENT,$sDt,$sTm,$AKeyEmr106New);

        }else{
            CallEmrXmlExe($UR_PROCESS,$sUr,"yc_his_ser ",$pwd,"ISLN",$sCidFlag,"@","",$ID_PATIENT,$ID_INPATIENT,$sDt,$sTm,$AKeyEmr106New);
        }
    }
}
