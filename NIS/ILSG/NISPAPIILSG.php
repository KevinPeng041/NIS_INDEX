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
    $Default_DATAD_Val=array("[]",'"IDGP":""','"LSTPT":""');
    $Replace_Default_Val=array(FORBID($conn,$idPt,$INPt),'"IDGP":'.'"'.IDGP_LSTPT($conn,$idPt,$INPt,'IDGP').'"','"LSTPT":'.'"'.IDGP_LSTPT($conn,$idPt,$INPt,'LSTPT').'"');


    $ST_DATAB=str_replace($Default_DATAD_Val,$Replace_Default_Val,$ST_DATAB);//取代後的禁打部位json


    $ST_DATD=str_replace("[]",FORBID($conn,$idPt,$INPt),$ST_DATAD);

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
            print htmlentities($e['message']);
            print "\n<pre>\n";
            print htmlentities($e['sqltext']);
            printf("\n%".($e['offset']+1)."s", "^");
            print  "\n</pre>\n";
        }
        $clob->save($ST_PREB);
        oci_commit($conn);

        $jsonback = array('sTraID' => $sTraID, 'sSave' => $sSave, 'FORMSEQANCE_WT' => $FORMSEQANCE_WT,
            'JID_NSRANK' => $JID_NSRANK, 'ST_DATAA' => $ST_DATAA, 'ST_DATAB' => $ST_DATAB,'ST_DATAC'=>$ST_DATAC,'ST_DATAD'=>$ST_DATD,'ST_PREC'=>$ST_PREC);
        oci_free_statement($INTP_Stid);
        return json_encode($jsonback,JSON_UNESCAPED_UNICODE);
    } else {
        oci_rollback($conn);
    }
}
function GetILSGPageJson($conn,$sTraID,$sPg){
    $date = date("Ymd");
    $STR = substr($date, 0, 4);
    $STR1 = substr($date, 4, 7);
    $y = $STR - 1911;
    $DATE=(string)$y.(string)$STR1;
    $respone='';
    $SQL="select ID_INPATIENT from HIS803.NISWSTP WHERE  ID_TRANSACTION = '$sTraID'";

    $stid=oci_parse($conn,$SQL);
    oci_execute($stid);
    $ID_INPATIENT='';
    while (oci_fetch_array($stid)){
        $ID_INPATIENT=oci_result($stid,"ID_INPATIENT");
    }
    switch ($sPg){
        case 'DATAA':

            $sql="select ST_DATAA from HIS803.NISWSTP WHERE ID_TABFORM ='ILSGA' AND ID_TRANSACTION = '$sTraID'";
            $Stid=oci_parse($conn,$sql);
            oci_execute($Stid);
            $jsonArrAY=[];
            while (oci_fetch_array($Stid)){
                /* [{"idFrm":"ISSG","SFRMSEQ":"","IDTM":"","IDGP":"","STVAL":"","SPRESS":"","MMVAL":""}]*/
                $idFrm=oci_result($Stid,'idFrm');
                $SFRMSEQ=oci_result($Stid,'SFRMSEQ');
                $IDTM=oci_result($Stid,'IDTM');
                $IDGP=oci_result($Stid,'IDGP');
                $STVAL=oci_result($Stid,'STVAL');
                $SPRESS=oci_result($Stid,'SPRESS');
                $MMVAL=oci_result($Stid,'MMVAL');
                $jsonArrAY[]=array('idFrm'=>"ISSG",'SFRMSEQ'=>'','IDTM'=>'',
                    'IDGP'=>'','STVAL'=>'','SPRESS'=>'','MMVAL'=>'');
            }
            $respone=json_encode($jsonArrAY,JSON_UNESCAPED_UNICODE);
            oci_free_statement($Stid);
            oci_close($conn);
            break;
        case 'PREB':
            /*醫師開ORDER*/
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

            $sql="select ST_PREB , ST_PREC from HIS803.NISWSTP WHERE  ID_TABFORM ='ILSGA' AND ID_TRANSACTION = '$sTraID'";
            $Stid2=oci_parse($conn,$sql);
            oci_execute($Stid2);
            $jsonArrAY=[];

            while ($row=oci_fetch_array($Stid2)){
                $ST_PREB= stripslashes($row[0]->load());
                $ST_PREC=$row[1]->load();
                if($JID_KEY){
                    $newST_PREB=str_replace('{"str_replace"}',$obj_json[0],$ST_PREB);
                    $jsonArrAY[]=array("ISULING"=>stripslashes($newST_PREB),"FUSEQ"=>stripslashes($ST_PREC));
                }else{
                    $newST_PREB=str_replace('{"str_replace"},','',$ST_PREB);
                    $jsonArrAY[]=array("ISULING"=>stripslashes($newST_PREB),"FUSEQ"=>stripslashes($ST_PREC));
                }
            }

            $respone=json_encode($jsonArrAY,JSON_UNESCAPED_UNICODE);
            oci_free_statement($Stid2);
            oci_close($conn);
            break;
        default:
            break;
    }

    return $respone;
}
function PosILSGSave($conn,$sTraID,$sFm,$sUr,$sPg,$sDt,$sTm,$pwd,$HOST_IP){
    $testTIME = date("YmdHis");
    $STR = substr($testTIME, 0, 4);
    $STR1 = substr($testTIME, -10, 10);
    $str = $STR - 1911;
    $sProcDateTime = $str . $STR1;

    $Ssql="SELECT ID_INPATIENT,ID_PATIENT,DT_EXCUTE,TM_EXCUTE,FORMSEQANCE,UR_PROCESS from HIS803.NISWSTP
        WHERE ID_TABFORM = '$sFm'  AND ID_TRANSACTION = '$sTraID'";

    $Sstid=oci_parse($conn,$Ssql);
    oci_execute($Sstid);
    $idinpt='';
    $idpt='';
    $formseq='';
    $DT_EXCUTE='';
    $TM_EXCUTE='';
    $UR_PROCESS='';

    while ($row=oci_fetch_array($Sstid)){
        $idinpt=$row['ID_INPATIENT'];
        $idpt=$row['ID_PATIENT'];
        $formseq=$row['FORMSEQANCE'];
        $DT_EXCUTE=$row['DT_EXCUTE'];
        $TM_EXCUTE=$row['TM_EXCUTE'];
        $UR_PROCESS=$row['UR_PROCESS'];
    }


    $sCidFlag="I";
    /*防呆重複寫檔*/
    switch ($sPg){
        case "A":
            if(trim($formseq) !="" && trim($formseq) !=null){
                /*修改功能先刪除前次資料*/
                $sCidFlag="U";
                _deleteData($HOST_IP,$sFm,$sTraID,$sPg,$UR_PROCESS,$sCidFlag);
            }
            if(ISRecordForDateTime($conn,'ISSG',$idpt,$idinpt,$sDt,$sTm)=='true'){
                $response=json_encode(array("response" => "false","message" =>"血糖同一時間禁止重複輸入"),JSON_UNESCAPED_UNICODE);

                return $response;
            }
            break;
        case "B":
            if(trim($DT_EXCUTE) !="" && trim($TM_EXCUTE) !=""){
                /*修改功能先刪除前次資料*/
                $sCidFlag="U";
                _deleteData($HOST_IP,$sFm,$sTraID,$sPg,$UR_PROCESS,$sCidFlag);

            }
            if(ISRecordForDateTime($conn,'ISLN',$idpt,$idinpt,$sDt,$sTm)=='true'){
                $response=json_encode(array("response" => "false","message" =>"胰島素同一時間禁止重複輸入"),JSON_UNESCAPED_UNICODE);
                return $response;
            }
            break;
        case "C":
            if(trim($DT_EXCUTE) !="" && trim($TM_EXCUTE) !="" &&  trim($formseq) !=""){
                /*修改功能先刪除前次資料*/
                $sCidFlag="U";
                _deleteData($HOST_IP,$sFm,$sTraID,$sPg,$UR_PROCESS,$sCidFlag);
            }

            if(ISRecordForDateTime($conn,'ISLNN',$idpt,$idinpt,$sDt,$sTm)=='true') {
                $response = json_encode(array("response" => "false", "message" => "禁打同一時間禁止重複輸入"),JSON_UNESCAPED_UNICODE);
                return $response;
                break;
            }
    }
    /*取frmseq*/
    $sql_Serch="SELECT NO_TABFORM FROM  HIS803.NSTBMF  WHERE ID_TABFORM= 'ISSG'";
    $stid=oci_parse($conn,$sql_Serch);
    oci_execute($stid);
    $NO_TABFORM='';
    while (oci_fetch_array($stid)){
        $NO_TABFORM=ociresult($stid,'NO_TABFORM');
    }

    /*回壓frmseq*/
    $PAD_NO_TABFORM  = str_pad($NO_TABFORM,10,0,STR_PAD_LEFT);
    $FORMseq=$NO_TABFORM+1;
    $sql_update="UPDATE  HIS803.NSTBMF SET  NO_TABFORM='$FORMseq' WHERE ID_TABFORM= 'ISSG'";
    $up_stid=oci_parse($conn,$sql_update);
    oci_execute($up_stid);
    $V_FrmSeq='ISSG'.$PAD_NO_TABFORM;

    $idpt = '';
    $idINPt = '';
    $sBed = '';
    $sNsRank = '';
    $sFrmSeqWk = '';
    $account = '';
    $ST_DATA = '';
    $ST_DATB = '';
    $ST_DATC = '';
    $idFrm = '';
    $SFRMSEQ = '';
    $IDTM = '';
    $IDGP = '';
    $STVAL = '';
    $SPRESS = '';
    $MMVAL = '';

    $UPTMSQL="UPDATE HIS803.NISWSTP SET TM_EXCUTE='$sTm',DT_EXCUTE='$sDt'  WHERE ID_TRANSACTION='$sTraID'";
    $upstid=oci_parse($conn,$UPTMSQL);
    $R=oci_execute($upstid,OCI_NO_AUTO_COMMIT);
    if(!$R){
        oci_rollback($conn);
        $response=json_encode(array("response" => "false","message" => "this is the UPDATE TM_EXCUTE false"),JSON_UNESCAPED_UNICODE);

        return $response;
    }
    else{
        $r=oci_commit($conn);
        if(!$r){
            $response=json_encode(array("response" => "false","message" =>"this is the TM_EXCUTE oci_commit false"),JSON_UNESCAPED_UNICODE);

            return $response;
        }
    }

    $sql_json = "SELECT ST_DATAA, ST_DATAB, ST_DATAC,ID_INPATIENT,ID_PATIENT,ID_BED,JID_NSRANK,FORMSEQANCE_WT,UR_PROCESS  
                 from HIS803.NISWSTP
                 WHERE ID_TABFORM = '$sFm'  AND ID_TRANSACTION = '$sTraID'";

    $Stid = oci_parse($conn, $sql_json);

    oci_execute($Stid);
    while ($row = oci_fetch_array($Stid)) {
        $ST_DATA = $row[0]->read(2000);
        $ST_DATB = $row[1]->read(2000);
        $ST_DATC = $row[2]->read(2000);
        $idINPt=$row[3];
        $idpt=$row[4];
        $sBed=$row[5];
        $sNsRank=$row[6];
        $sFrmSeqWk=$row[7];
        $account=$row[8];
        $str1 = explode('[', $ST_DATA);
        $str2 = explode(']', $str1[1]);
        $JSONobj = json_decode($str2[0]);
        $idFrm = $JSONobj->idFrm;
        $SFRMSEQ = $JSONobj->SFRMSEQ;
        $IDTM = $JSONobj->IDTM;
        $IDGP = $JSONobj->IDGP;
        $STVAL = $JSONobj->STVAL;
        $SPRESS = $JSONobj->SPRESS;
        $MMVAL = $JSONobj->MMVAL;
    }

    $sSTVAL=$STVAL ==''?$SPRESS:$STVAL;

    $P_JID_UNIT = "ISSG00000001";
 /*   $response = json_encode(array("a" => $ST_DATA,"b" => $ST_DATB,"c"=>$ST_DATC),JSON_UNESCAPED_UNICODE);*/

    $exISLNstr1 = explode("]", $ST_DATB);
    $exISLNstr_1 = explode("[", $exISLNstr1[0]);
    $exISLNstr_2 = explode("},", $exISLNstr_1[1]);
    $FORBID_lst = "";
    $idFORBID = "";

    $isuA=substr($exISLNstr_2[0],-1)!='}'?$exISLNstr_2[0].'}':$exISLNstr_2[0];
    $isuB=substr($exISLNstr_2[1],-1)!='}'?$exISLNstr_2[1].'}':$exISLNstr_2[1];
    $isuC=substr($exISLNstr_2[2],-1)!='}'?$exISLNstr_2[2].'}':$exISLNstr_2[2];

    $OBJ1=json_decode($isuA);
    $OBJ1->idFrm;
    $LN_SFRMDTSEQ = $OBJ1->SFRMDTSEQ;
    $LN_ITNO =$OBJ1->ITNO;
    $LN_IDTM= $OBJ1->IDTM;
    $LN_IDGP= $OBJ1->IDGP;
    $LN_FORBID=$OBJ1->FORBID;
    $LN_ID = $OBJ1->ID;
    $LN_STM=$OBJ1->STM;
    $LN_DBDOSE = $OBJ1->DBDOSE;
    $LN_SDOSE=$OBJ1->SDOSE;
    $LN_USEF= $OBJ1->USEF;
    $LN_LSTPT= $OBJ1->LSTPT;

    if ($LN_FORBID != "") {

        $idFORBID = $LN_FORBID;
        $FORBID_lst = ($FORBID_lst == "") ? $LN_IDGP : $FORBID_lst . $LN_IDGP;
    }
    $DBDOSE = $LN_DBDOSE;
    $DBDOSE = -1;


    $OBJ2=json_decode($isuB);
    $OBJ2->idFrm;
    $LN2_SFRMDTSEQ = $OBJ2->SFRMDTSEQ;
    $LN2_ITNO =$OBJ2->ITNO;
    $LN2_IDTM= $OBJ2->IDTM;
    $LN2_IDGP= $OBJ2->IDGP;
    $LN2_FORBID=$OBJ2->FORBID;
    $LN2_ID = $OBJ2->ID;
    $LN2_STM=$OBJ2->STM;
    $LN2_DBDOSE = $OBJ2->DBDOSE;
    $LN2_SDOSE=$OBJ2->SDOSE;
    $LN2_USEF= $OBJ2->USEF;
    $LN2_LSTPT= $OBJ2->LSTPT;

    if ($LN2_FORBID != "") {

        $idFORBID = $LN2_FORBID;
        $FORBID_lst = ($FORBID_lst == "") ? $LN2_IDGP : $FORBID_lst . $LN2_IDGP;
    }
    $DBDOSE2 = $LN2_DBDOSE;
    $DBDOSE2 = -1;

    $OBJ3=json_decode($isuC);
    $OBJ3->idFrm;
    $LN3_SFRMDTSEQ = $OBJ3->SFRMDTSEQ;
    $LN3_ITNO =$OBJ3->ITNO;
    $LN3_IDTM= $OBJ3->IDTM;
    $LN3_IDGP= $OBJ3->IDGP;
    $LN3_FORBID=$OBJ3->FORBID;
    $LN3_ID = $OBJ3->ID;
    $LN3_STM=$OBJ3->STM;
    $LN3_DBDOSE = $OBJ3->DBDOSE;
    $LN3_SDOSE=$OBJ3->SDOSE;
    $LN3_USEF= $OBJ3->USEF;
    $LN3_LSTPT= $OBJ3->LSTPT;

    if ($LN3_FORBID != "") {
        $idFORBID = $LN3_FORBID;
        $FORBID_lst = ($FORBID_lst == "") ? $LN3_IDGP : $FORBID_lst . $LN3_IDGP;
    }
    $DBDOSE3 = $LN3_DBDOSE;
    $DBDOSE3 = -1;
    $P_JID_TOOL = "";
    $P_ID_MESSAGE = " ";
    $P_FORMSEQANCE_FL = " ";

    $LN_FORBID != "" ? $LN_FORBID = true : false;
    $dtTake = $LN_FORBID ? " " : $sDt;
    $tmTake = $LN_FORBID ? " " : $sTm;
    $jidTime = $LN_FORBID ? " " :$LN_IDTM;
    $USEF = $LN_FORBID ? " " : $LN_USEF;
    $ID = $LN_FORBID ? " " : $LN_ID;
    $STM = $LN_FORBID ? " " : $LN_STM;
    $iNo = $LN_FORBID ? -1 : (int)$LN_ITNO;
    $iDOSE = $LN_FORBID ? -1 : $LN_SDOSE;

    $LN2_FORBID != "" ? $LN2_FORBID = true : false;
    $dtTake2 = $LN2_FORBID ? " " : $sDt;
    $tmTake2 = $LN2_FORBID ? " " : $sTm;
    $jidTime2 = $LN2_FORBID ? " " :$LN2_IDTM;
    $USEF2 = $LN2_FORBID ? " " : $LN2_USEF;
    $ID2 = $LN2_FORBID ? " " : $LN2_ID;
    $STM2 = $LN2_FORBID ? " " : $LN2_STM;
    $iNo2 = $LN2_FORBID ? -1 : (int)$LN2_ITNO;
    $iDOSE2 = $LN2_FORBID ? -1 : $LN2_SDOSE;

    $LN3_FORBID != "" ? $LN3_FORBID  = true : false;
    $dtTake3 = $LN3_FORBID ? " " : $sDt;
    $tmTake3 = $LN3_FORBID ? " " : $sTm;
    $jidTime3 = $LN3_FORBID ? " " :$LN3_IDTM;
    $USEF3 = $LN3_FORBID ? " " : $LN3_USEF;
    $ID3 = $LN3_FORBID ? " " : $LN3_ID;
    $STM3 = $LN3_FORBID ? " " : $LN3_STM;
    $iNo3 = $LN3_FORBID ? -1 : (int)$LN3_ITNO;
    $iDOSE3 = $LN3_FORBID ? -1 : $LN3_SDOSE;


    /*禁打(一個部位存8次)*/
    $json_DATC=json_decode($ST_DATC);

    $REGION=$json_DATC[0]->REGION;
    $NO_MMVAL=$json_DATC[0]->NO_MMVAL;
    if($REGION){
        for ($i=0;$i<count($REGION);$i++){
            for ($j=1;$j<=8;$j++){
                $SQL=ISLNinsert($idpt,$idINPt,$sDt,$sTm,$jidTime,$REGION[$i].$j,' ',$STM,$DBDOSE,$iDOSE,$USEF,$NO_MMVAL,$iNo,$sBed,$sNsRank,$sFrmSeqWk,$sProcDateTime,$account);
                $stid_FORBID=oci_parse($conn,$SQL);
                $r=oci_execute($stid_FORBID,OCI_NO_AUTO_COMMIT);
                if(!$r){
                    oci_rollback($conn);
                    $e=oci_error($stid_FORBID);
                    $response=json_encode(array("response" => "false","message" =>"禁打錯誤訊息:".$e['message']),JSON_UNESCAPED_UNICODE);
                    return $response;
                }
            }
        }
        $r=oci_commit($conn);
        $e=oci_error($conn);
        if(!$r){
            $response=json_encode(array("response" => "false","message" =>"禁打錯誤訊息:".$e['message']),JSON_UNESCAPED_UNICODE);
            return $response;
        }else{
            oci_free_statement($stid_FORBID);
        }
    }
    //部位序號
    $URL="http://".$HOST_IP."/webservice/NISPWSCILREG.php?str=".AESEnCode("sFm=ILSGA&sTraID=".$sTraID."&sRgn=".$LN_IDGP);
    $num=file_get_contents($URL);
    $LN_IDGP=$LN_IDGP.(int)AESDeCode($num);


    $sql="INSERT INTO his803.NSISSG(DATESEQANCE,FORMSEQANCE,ID_PATIENT,
                ID_INPATIENT,NO_OPDSEQ,DT_EXCUTE,TM_EXCUTE,ST_MEASURE,JID_UNIT,JID_TIME,
                CID_MEAL,JID_TOOL,MM_TPRS,ID_MESSAGE,
                ID_BED,JID_NSRANK,FORMSEQANCE_WT,
                FORMSEQANCE_FL,DM_PROCESS,UR_PROCESS,DM_CANCD,UR_CANCD)
                VALUES
                (his803.NIS_DATETIMESEQ,'$V_FrmSeq','$idpt','$idINPt',
                0,'$sDt','$sTm','$sSTVAL',Nvl('$P_JID_UNIT',' '),Nvl('$IDTM',' '),
                Nvl('$IDGP',' '),Nvl('$P_JID_TOOL',' '),Nvl('$MMVAL',' '),Nvl('$P_ID_MESSAGE',' '),
                Nvl('$sBed',' '),Nvl('$sNsRank',' '),Nvl('$sFrmSeqWk',' '),
                Nvl('$P_FORMSEQANCE_FL',' '),'$sProcDateTime','$account',' ',' ')";
    $sql1=ISLNinsert($idpt,$idINPt,$sDt,$sTm,$jidTime,$LN_IDGP,$ID,$STM,$DBDOSE,$iDOSE,$USEF,$LN_FORBID,$iNo,$sBed,$sNsRank,$sFrmSeqWk,$sProcDateTime,$account);
    $sql2=ISLNinsert($idpt,$idINPt,$sDt,$sTm,$jidTime2,$LN_IDGP,$ID2,$STM2,$DBDOSE2,$iDOSE2,$USEF2,$LN2_FORBID,$iNo2,$sBed,$sNsRank,$sFrmSeqWk,$sProcDateTime,$account);
    $sql3=ISLNinsert($idpt,$idINPt,$sDt,$sTm,$jidTime3,$LN_IDGP,$ID3,$STM3,$DBDOSE3,$iDOSE3,$USEF3,$LN3_FORBID,$iNo3,$sBed,$sNsRank,$sFrmSeqWk,$sProcDateTime,$account);

    if (!empty($STVAL ||$SPRESS) && empty($LN_STM)) {

        if ($SFRMSEQ == '') {
            $stid_ISSG=oci_parse($conn,$sql);
            $r=oci_execute($stid_ISSG,OCI_NO_AUTO_COMMIT);
            if(!$r){
                oci_rollback($conn);
                $e=oci_error($stid_ISSG);
                $response=json_encode(array("response" => "false","message" =>"血糖存檔錯誤訊息:".$e['message']),JSON_UNESCAPED_UNICODE);
                return $response;
            }else{
                //小index儲存程序 1090401 add
                $G_IS_USE_NXL=GetXmlType($conn,"ISLN")==''?'N':GetXmlType($conn,"ISLN");
                $sExeDTMofISSG=$sDt.$sTm;
                $sExeDTMofISLN="";
                $AHISLink="ISLN@".$idINPt."-".trim($sExeDTMofISSG)."-".trim($sExeDTMofISLN);
                $sHops=GetHospital($conn);
                $idHospital=explode("/",$sHops)[0]; //醫院代碼
                $sHopsNo=explode("/",$sHops)[1];
                $sHopsNo=(trim($sHopsNo)!="")?" ".$sHopsNo:"";

                if($G_IS_USE_NXL=="*" || $G_IS_USE_NXL=="A"){
                    $sKeyEmr106Prev=GetEMR106PrevSeq($conn,"ISLN",$AHISLink);

                    $sKeyEmr106New=ProcessEMR106($conn, $idHospital, "ISLN", $sCidFlag,$sKeyEmr106Prev,$AHISLink,$idINPt,$idINPt,"ISLN"."C",$sIdUser);
                    $AKeyEmr106New=$sKeyEmr106New;
                }
                $r=oci_commit($conn);

                if(!$r){
                    $e=oci_error($conn);
                    $response=json_encode(array("response" => "false","message" =>"血糖存檔錯誤訊息:".$e['message']),JSON_UNESCAPED_UNICODE);

                    return $response;
                }else{
                    //拋轉EMR電子病歷 Exe檔 1090401 add
                    if($G_IS_USE_NXL=="*" || $G_IS_USE_NXL=="A"){
                        if($sCidFlag=="U"){
                            CallEmrXmlExe($account,$sUr,"yc_his_ser ",$pwd,"ISLN",$sCidFlag,"@","",$idpt,$idINPt,$sDt,$sTm,$AKeyEmr106New);

                        }else{
                            CallEmrXmlExe($account,$sUr,"yc_his_ser ",$pwd,"ISLN",$sCidFlag,"@","",$idpt,$idINPt,$sDt,$sTm,$AKeyEmr106New);
                        }
                    }
                    oci_free_statement($stid_ISSG);

                    $response=json_encode(array("response" => "success","message" => "thisis the success message"),JSON_UNESCAPED_UNICODE);
                    return $response;
                }
            }

        }
    }
    else if (!empty($STVAL ||$SPRESS && $LN_STM)){
        if(!empty($LN_STM)){
            $sExeDTMofISSG="";
            $sExeDTMofISLN=$sDt.$sTm;
            $stid_ISSG=oci_parse($conn,$sql);
            $stid_ISLN1=oci_parse($conn,$sql1);

            if($stid_ISSG && $stid_ISLN1){
                $result1=oci_execute($stid_ISSG,OCI_NO_AUTO_COMMIT);
                $result2= oci_execute($stid_ISLN1,OCI_NO_AUTO_COMMIT);
                $sExeDTMofISSG=$sDt.$sTm;
                if($result1 && $result2){
                    if(!empty($LN2_STM)){

                        $stid_ISLN2=oci_parse($conn,$sql2);
                        $r=oci_execute($stid_ISLN2,OCI_NO_AUTO_COMMIT);
                        if(!$r){
                            oci_rollback($conn);
                            $e=oci_error($conn);
                            $response=json_encode(array("response" => "false","message" =>"胰島素2存檔錯誤訊息:".$e['message']),JSON_UNESCAPED_UNICODE);
                            return $response;
                        }

                    }
                    if(!empty($LN3_STM)){
                        $stid_ISLN3=oci_parse($conn,$sql3);
                        $r=oci_execute($stid_ISLN3,OCI_NO_AUTO_COMMIT);
                        if(!$r){
                            oci_rollback($conn);
                            $e=oci_error($conn);
                            $response=json_encode(array("response" => "false","message" =>"胰島素3存檔錯誤訊息:".$e['message']),JSON_UNESCAPED_UNICODE);
                            return $response;
                        }
                    }
                    //小index儲存程序 1090401 add
                    $G_IS_USE_NXL=GetXmlType($conn,"ISLN")==''?'N':GetXmlType($conn,"ISLN");

                    $AHISLink="ISLN@".$idINPt."-".trim($sExeDTMofISSG)."-".trim($sExeDTMofISLN);
                    $sHops=GetHospital($conn);
                    $idHospital=explode("/",$sHops)[0]; //醫院代碼
                    $sHopsNo=explode("/",$sHops)[1];
                    $sHopsNo=(trim($sHopsNo)!="")?" ".$sHopsNo:"";
                    if($G_IS_USE_NXL=="*" || $G_IS_USE_NXL=="A"){
                        $sKeyEmr106Prev=GetEMR106PrevSeq($conn,"ISLN",$AHISLink);
                        $sKeyEmr106New=ProcessEMR106($conn, $idHospital, "ISLN", $sCidFlag,$sKeyEmr106Prev,$AHISLink,$idINPt,$idINPt,"ISLN"."C",$sIdUser);
                        $AKeyEmr106New=$sKeyEmr106New;
                    }
                    $r=oci_commit($conn);
                    if(!$r){
                        oci_rollback($conn);
                        $e=oci_error($conn);
                        $response=json_encode(array("response" => "false","message" =>"血糖胰島素存檔錯誤訊息:".$e['message']),JSON_UNESCAPED_UNICODE);
                        echo   AESEnCode($response);
                        return false;
                    }else{
                        //拋轉EMR電子病歷 Exe檔 1090401 add
                        if($G_IS_USE_NXL=="*" || $G_IS_USE_NXL=="A"){
                            if($sCidFlag=="U"){
                                CallEmrXmlExe($account,$sUr,"yc_his_ser ",$pwd,"ISLN",$sCidFlag,"@","",$idpt,$idINPt,$sDt,$sTm,$AKeyEmr106New);

                            }else{
                                CallEmrXmlExe($account,$sUr,"yc_his_ser ",$pwd,"ISLN",$sCidFlag,"@","",$idpt,$idINPt,$sDt,$sTm,$AKeyEmr106New);
                            }
                        }
                        $response=json_encode(array("response" => "success","message" => "this is the success message"),JSON_UNESCAPED_UNICODE);
                        oci_free_statement($stid_ISSG);
                        oci_free_statement($stid_ISLN1);
                        return $response;
                    }
                }
            }
        }
    }
    else if(!empty($LN_STM) && empty($STVAL||$SPRESS)){
        $stid_ISLN1=oci_parse($conn,$sql1);
        $r=oci_execute($stid_ISLN1,OCI_NO_AUTO_COMMIT);
        if(!$r){
            oci_rollback($conn);
            $e=oci_error($conn);
            $response=json_encode(array("response" => "false","message" =>"胰島素存檔錯誤訊息:".$e['message']),JSON_UNESCAPED_UNICODE);
            return $response;
        }
        if(!empty($LN2_STM)){
            $stid_ISLN2=oci_parse($conn,$sql2);
            $r=oci_execute($stid_ISLN2,OCI_NO_AUTO_COMMIT);
            if(!$r){
                oci_rollback($conn);
                $e=oci_error($conn);
                $response=json_encode(array("response" => "false","message" =>"胰島素存檔錯誤訊息:".$e['message']),JSON_UNESCAPED_UNICODE);
                return $response;
            }
        }
        if(!empty($LN3_STM)){
            $stid_ISLN3=oci_parse($conn,$sql3);
            $r=oci_execute($stid_ISLN3,OCI_NO_AUTO_COMMIT);
            if(!$r){
                oci_rollback($conn);
                $e=oci_error($conn);
                $response=json_encode(array("response" => "false","message" =>"胰島素2存檔錯誤訊息:".$e['message']),JSON_UNESCAPED_UNICODE);
                return $response;
            }

        }
        //小index儲存程序 1090401 add
        $G_IS_USE_NXL=GetXmlType($conn,"ISLN")==''?'N':GetXmlType($conn,"ISLN");

        $sExeDTMofISSG="";
        $sExeDTMofISLN=$sDt.$sTm;
        $AHISLink="ISLN@".$idINPt."-".trim($sExeDTMofISSG)."-".trim($sExeDTMofISLN);
        $sHops=GetHospital($conn);
        $idHospital=explode("/",$sHops)[0]; //醫院代碼
        $sHopsNo=explode("/",$sHops)[1];
        $sHopsNo=(trim($sHopsNo)!="")?" ".$sHopsNo:"";

        if($G_IS_USE_NXL=="*" || $G_IS_USE_NXL=="A"){
            $sKeyEmr106Prev=GetEMR106PrevSeq($conn,"ISLN",$AHISLink);
            $sKeyEmr106New=ProcessEMR106($conn, $idHospital, "ISLN", $sCidFlag,$sKeyEmr106Prev,$AHISLink,$idINPt,$idINPt,"ISLN"."C",$sIdUser);
            $AKeyEmr106New=$sKeyEmr106New;
        }
        $r=oci_commit($conn);
        if(!$r){
            $e=oci_error($conn);
            $response=json_encode(array("response" => "false","message" =>"胰島素存檔錯誤訊息:".$e['message']),JSON_UNESCAPED_UNICODE);
            return $response;
        }else{
            //拋轉EMR電子病歷 Exe檔 1090401 add
            if($G_IS_USE_NXL=="*" || $G_IS_USE_NXL=="A"){
                if($sCidFlag=="U"){
                    CallEmrXmlExe($account,$sUr,"yc_his_ser ",$pwd,"ISLN",$sCidFlag,"@","",$idpt,$idINPt,$sDt,$sTm,$AKeyEmr106New);

                }else{
                    CallEmrXmlExe($account,$sUr,"yc_his_ser ",$pwd,"ISLN",$sCidFlag,"@","",$idpt,$idINPt,$sDt,$sTm,$AKeyEmr106New);
                }
            }
            oci_free_statement($stid_ISLN1);
            oci_free_statement($stid_ISLN2);
            oci_free_statement($stid_ISLN3);
            $response=json_encode(array("response" => "success","message" => "this is the success message"),JSON_UNESCAPED_UNICODE);
            return $response;
        }

    }
}

function GetILSGJson($conn,$IDPT,$INPt,$sUr,$sDt,$sTm,$sPg,$sDFL){

}
function PosILSGCancel($conn,$sTraID,$sPg){

}
function ISLNinsert($idpt,$idINPt,$sDt,$sTm,$jidTime,$LN_IDGP,$ID,$STM,$DBDOSE,$iDOSE,$USEF,$LN_FORBID,$iNo,$sBed,$sNsRank,$sFrmSeqWk,$sProcDateTime,$account){
    $sql="INSERT INTO his803.NSISLN (DATESEQANCE,ID_PATIENT,ID_INPATIENT,NO_OPDSEQ,DT_EXCUTE, TM_EXCUTE,
                 JID_TIME,ID_REGION, ID_ORDER, NM_ORDER,DB_DOSE, ST_DOSE, ST_USENO,
                DT_TAKEDRUG, TM_TAKEDRUG, JID_FORBID, NO_PAGE,ID_BED, JID_NSRANK,
                 FORMSEQANCE_WT,DM_PROCESS, UR_PROCESS, DM_CANCD, UR_CANCD)
                 VALUES 
                 (his803.NIS_DATETIMESEQ,'$idpt',Nvl('$idINPt', ' '),0, '$sDt','$sTm',
                 NVL('$jidTime',' '),'$LN_IDGP',NVL('$ID',' '),NVL('$STM',' '),NVL('$DBDOSE',0),NVL('$iDOSE',0),NVL('$USEF',' '),
                 NVL('$sDt',' '),NVL('$sTm',' '),NVL('$LN_FORBID',' '),'$iNo',NVL('$sBed',' '),NVL('$sNsRank',' '),
                 NVL('$sFrmSeqWk',' '),'$sProcDateTime','$account',' ',' ')";
    return $sql;
}
function _deleteData($ip,$sFm,$transkey,$page,$sUser,$sCidFlag){
    $Del_url="http://".$ip."/webservice/NISPWSDELILSG.php?str=".AESEnCode("sFm=".$sFm."&sTraID=".$transkey."&sPg=".$page."&sCidFlag".$sCidFlag."&sUr=".$sUser);
    file_get_contents($Del_url);
}
