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
    $DateTime = date("YmdHis");
    $STR = substr($DateTime, 0, 4);
    $STR1 = substr($DateTime, -10, 10);
    $str = $STR - 1911;
    $NowDT= $str . $STR1;

    $Ssql="SELECT ID_INPATIENT,ID_PATIENT,DT_EXCUTE,TM_EXCUTE,FORMSEQANCE,UR_PROCESS FROM HIS803.NISWSTP
                WHERE ID_TABFORM = :id_TAB  AND ID_TRANSACTION = :id_TRANS";

    $Sstid=oci_parse($conn,$Ssql);

    oci_bind_by_name($Sstid,":id_TAB",$sFm);
    oci_bind_by_name($Sstid,":id_TRANS",$sTraID);

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
            }
            break;
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
    $sql_update="UPDATE  HIS803.NSTBMF SET  NO_TABFORM=:NO_TAB WHERE ID_TABFORM= 'ISSG'";
    $up_stid=oci_parse($conn,$sql_update);

    oci_bind_by_name($up_stid,":NO_TAB",$FORMseq);
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

    $UPTMSQL="UPDATE HIS803.NISWSTP SET TM_EXCUTE=:TM,DT_EXCUTE=:DT  WHERE ID_TRANSACTION=:id_TRAN";
    $upstid=oci_parse($conn,$UPTMSQL);
    oci_bind_by_name($upstid,":TM",$sTm);
    oci_bind_by_name($upstid,":DT",$sDt);
    oci_bind_by_name($upstid,":id_TRAN",$sTraID);
    $R=oci_execute($upstid,OCI_NO_AUTO_COMMIT);
    if(!$R){
        oci_rollback($conn);
        $e=oci_error($upstid);
        $response=json_encode(array("response" => "false","message" => $e['message']),JSON_UNESCAPED_UNICODE);
        return $response;
    }
    else{
        $r=oci_commit($conn);
        if(!$r){
            $e=oci_error($conn);
            $response=json_encode(array("response" => "false","message" =>$e['message']),JSON_UNESCAPED_UNICODE);
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
/*    $response = json_encode(array("a" => $ST_DATA,"b" => $ST_DATB,"c"=>$ST_DATC),JSON_UNESCAPED_UNICODE);*/

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
    $idFORBID = "";
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
    $idFORBID = "";
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
    $idFORBID = "";
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
                $SQL=ISLNinsert($idpt,$idINPt,$sDt,$sTm,$jidTime,$REGION[$i].$j,' ',$STM,$DBDOSE,$iDOSE,$USEF,$NO_MMVAL,$iNo,$sBed,$sNsRank,$sFrmSeqWk,$NowDT,$account);
                $stid_FORBID=oci_parse($conn,$SQL);
                if(!$stid_FORBID){
                    $e=oci_error($conn);
                    $response=json_encode(array("response" => "false","message" =>"禁打錯誤訊息:".$e['message']),JSON_UNESCAPED_UNICODE);
                    echo AESEnCode($response);
                    return false;
                }

                $r=oci_execute($stid_FORBID,OCI_NO_AUTO_COMMIT);
                if(!$r){
                    $e=oci_error($stid_FORBID);
                    $response=json_encode(array("response" => "false","message" =>"禁打錯誤訊息:".$e['message']),JSON_UNESCAPED_UNICODE);
                    oci_rollback($conn);
                    echo AESEnCode($response);
                    return false;
                }
            }
        }
        $r=oci_commit($conn);
        $e=oci_error($conn);
        if(!$r){
            $response=json_encode(array("response" => "false","message" =>"禁打錯誤訊息:".$e['message']),JSON_UNESCAPED_UNICODE);
            echo AESEnCode($response);
            return false;
        }/*   else{

         $response=json_encode(array("response" => "success","message" => "thisis the success message"),JSON_UNESCAPED_UNICODE);
        }*/

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
                Nvl('$P_FORMSEQANCE_FL',' '),'$NowDT','$account',' ',' ')";

    $sql1=ISLNinsert($idpt,$idINPt,$sDt,$sTm,$jidTime,$LN_IDGP,$ID,$STM,$DBDOSE,$iDOSE,$USEF,$LN_FORBID,$iNo,$sBed,$sNsRank,$sFrmSeqWk,$NowDT,$account);
    $sql2=ISLNinsert($idpt,$idINPt,$sDt,$sTm,$jidTime2,$LN_IDGP,$ID2,$STM2,$DBDOSE2,$iDOSE2,$USEF2,$LN2_FORBID,$iNo2,$sBed,$sNsRank,$sFrmSeqWk,$NowDT,$account);
    $sql3=ISLNinsert($idpt,$idINPt,$sDt,$sTm,$jidTime3,$LN_IDGP,$ID3,$STM3,$DBDOSE3,$iDOSE3,$USEF3,$LN3_FORBID,$iNo3,$sBed,$sNsRank,$sFrmSeqWk,$NowDT,$account);

    if (!empty($STVAL ||$SPRESS) && empty($LN_STM)) {

        $AKeyEmr106New='';
        if ($SFRMSEQ == '') {
            $stid_ISSG=oci_parse($conn,$sql);
            $r=oci_execute($stid_ISSG,OCI_NO_AUTO_COMMIT);
            if(!$r){
                oci_rollback($conn);
                $e=oci_error($stid_ISSG);
                $response=json_encode(array("response" => "false","message" =>"血糖存檔錯誤訊息:".$e['message']),JSON_UNESCAPED_UNICODE);
                echo   AESEnCode($response);
                return false;
            }
            else{
                //小index儲存程序 1090401 add
                $G_IS_USE_NXL=GetXmlType($conn,"ISLN")==''?'N':GetXmlType($conn,"ISLN");
                $sKeyEmr106New="";
                $sExeDTMofISSG=$sDt.$sTm;
                $sExeDTMofISLN="";
                $AHISLink="ISLN@".$idINPt."-".trim($sExeDTMofISSG)."-".trim($sExeDTMofISLN);
                $sHops=GetHospital($conn);
                $idHospital=explode("/",$sHops)[0]; //醫院代碼
                $sHopsNo=explode("/",$sHops)[1];
                $sHopsNo=(trim($sHopsNo)!="")?" ".$sHopsNo:"";

                if($G_IS_USE_NXL=="*" || $G_IS_USE_NXL=="A"){
                    $sKeyEmr106Prev=GetEMR106PrevSeq($conn,"ISLN",$AHISLink);
                    $sKeyEmr106New=ProcessEMR106($conn, $idHospital, "ISLN", $sCidFlag,$sKeyEmr106Prev,$AHISLink,$idINPt,$idINPt,"ISLN"."C",$sUr);
                    $AKeyEmr106New=$sKeyEmr106New;
                }
                $r=oci_commit($conn);

                if(!$r){
                    $e=oci_error($conn);
                    $response=json_encode(array("response" => "false","message" =>"血糖存檔錯誤訊息:".$e['message']),JSON_UNESCAPED_UNICODE);
                    echo  AESEnCode($response);
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
                        oci_free_statement($stid_ISSG);
                        $response=json_encode(array("response" => "success","message" => "thisis the success message"),JSON_UNESCAPED_UNICODE);
                        echo   AESEnCode($response);
                }
            }

        }

    }
    else if (!empty($STVAL ||$SPRESS && $LN_STM)){
        if(!empty($LN_STM)){
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
                            $e=oci_error($stid_ISLN2);
                            $response=json_encode(array("response" => "false","message" =>"胰島素2存檔錯誤訊息:".$e['message']),JSON_UNESCAPED_UNICODE);
                            echo   AESEnCode($response);
                            return false;
                        }

                    }
                    if(!empty($LN3_STM)){
                        $stid_ISLN3=oci_parse($conn,$sql3);
                        $r=oci_execute($stid_ISLN3,OCI_NO_AUTO_COMMIT);
                        if(!$r){
                            oci_rollback($conn);
                            $e=oci_error($stid_ISLN3);
                            $response=json_encode(array("response" => "false","message" =>"胰島素3存檔錯誤訊息:".$e['message']),JSON_UNESCAPED_UNICODE);
                            echo   AESEnCode($response);
                            return false;
                        }
                    }
                    //小index儲存程序 1090401 add
                    $G_IS_USE_NXL=GetXmlType($conn,"ISLN")==''?'N':GetXmlType($conn,"ISLN");
                    $sKeyEmr106New="";
                    $AHISLink="ISLN@".$idINPt."-".trim($sExeDTMofISSG)."-".trim($sExeDTMofISLN);
                    $sHops=GetHospital($conn);
                    $idHospital=explode("/",$sHops)[0]; //醫院代碼
                    $sHopsNo=explode("/",$sHops)[1];
                    $sHopsNo=(trim($sHopsNo)!="")?" ".$sHopsNo:"";
                    if($G_IS_USE_NXL=="*" || $G_IS_USE_NXL=="A"){
                        $sKeyEmr106Prev=GetEMR106PrevSeq($conn,"ISLN",$AHISLink);
                        $sKeyEmr106New=ProcessEMR106($conn, $idHospital, "ISLN", $sCidFlag,$sKeyEmr106Prev,$AHISLink,$idINPt,$idINPt,"ISLN"."C",$sUr);
                        $AKeyEmr106New=$sKeyEmr106New;
                    }
                    $r=oci_commit($conn);
                    if(!$r){
                        oci_rollback($conn);
                        $e=oci_error($stid_ISSG);
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

                        oci_free_statement($stid_ISSG);
                        oci_free_statement($stid_ISLN1);
                        return  $response=json_encode(array("response" => "success","message" => "this is the success message"),JSON_UNESCAPED_UNICODE);


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
            $e=oci_error($stid_ISLN1);
            $response=json_encode(array("response" => "false","message" =>"胰島素存檔錯誤訊息:".$e['message']),JSON_UNESCAPED_UNICODE);
            echo  AESEnCode($response);
            return false;
        }
        if(!empty($LN2_STM)){
            $stid_ISLN2=oci_parse($conn,$sql2);
            $r=oci_execute($stid_ISLN2,OCI_NO_AUTO_COMMIT);
            if(!$r){
                oci_rollback($conn);
                $e=oci_error($stid_ISLN2);
                $response=json_encode(array("response" => "false","message" =>"胰島素存檔錯誤訊息:".$e['message']),JSON_UNESCAPED_UNICODE);
                echo   AESEnCode($response);
                return false;
            }
        }
        if(!empty($LN3_STM)){
            $stid_ISLN3=oci_parse($conn,$sql3);
            $r=oci_execute($stid_ISLN3,OCI_NO_AUTO_COMMIT);
            if(!$r){
                oci_rollback($conn);
                $e=oci_error($stid_ISLN3);
                $response=json_encode(array("response" => "false","message" =>"胰島素2存檔錯誤訊息:".$e['message']),JSON_UNESCAPED_UNICODE);
                echo  AESEnCode($response);
                return false;
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
            $sKeyEmr106New=ProcessEMR106($conn, $idHospital, "ISLN", $sCidFlag,$sKeyEmr106Prev,$AHISLink,$idINPt,$idINPt,"ISLN"."C",$sUr);
            $AKeyEmr106New=$sKeyEmr106New;
        }
        $r=oci_commit($conn);
        if(!$r){
            $e=oci_error($conn);
            $response=json_encode(array("response" => "false","message" =>"胰島素存檔錯誤訊息:".$e['message']),JSON_UNESCAPED_UNICODE);
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
            oci_free_statement($stid_ISLN1);
            return   $response=json_encode(array("response" => "success","message" => "this is the success message"),JSON_UNESCAPED_UNICODE);

        }

    }

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
    $Dt=substr($sDt,-4).substr($sTm,0,2);
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

    oci_execute($Sstid);
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
           /* $sql="UPDATE HIS803.NSISSG IG SET
                  IG.dm_cancd = :dm_cancd,
                  IG.ur_cancd = :ur_cancd               
                WHERE IG.id_patient =:id_patient
                  AND IG.id_inpatient =:id_inpatient
                  AND IG.formseqance =:formseqance  AND IG.DM_CANCD=' '";*/

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
                oci_commit($conn);
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
                oci_commit($conn);
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
            /*$sql3="UPDATE HIS803.NSISLN IL SET
                   IL.dm_cancd ='$NowDT',
                    IL.ur_cancd = '$sUr'
                    WHERE IL.id_patient ='$idpt'
                    AND IL.id_inpatient ='$idinpt'
                    AND IL.dt_excute = '$DT_EXCUTE'
                    AND IL.tm_excute = '$TM_EXCUTE'
                    AND IL.ur_process = '$sUr'
                    AND IL.dm_cancd=' '
                  AND IL.id_order = ' '";*/

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
                oci_commit($conn);
                $json_reponce=json_encode(array("message"=>"success","result"=>"true"));
            }
            break;
    }

    oci_free_statement($Sstid);
    return $json_reponce;
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
    $json_STDATA=[];

    while($row=oci_fetch_array($stid))
    {
        $Date=$row['DT_EXCUTE'];
        $TIME=$row['TM_EXCUTE'];
        $Stval=$row['STVAL'];
        $SPRESS=$row['SPRESS'];
        $JID_TIME=$row['IDTM'];
        $CID_MEAL=$row['IDGP'];
        $MM_TPRS=$row['MMVAL'];

        $JSONarray[]=array("idPt"=>$idPt,"DT_EXCUTE"=>$Date,"TM_EXCUTE"=>$TIME,"ST_MEASURE"=>$Stval,"SPRESS"=>$SPRESS,"JID_TIME"=>$JID_TIME,"CID_MEAL"=>$CID_MEAL,"MM_TPRS"=>$MM_TPRS,"sTraID"=>$TransKey);
        $standard= "/^([A-Za-z]+)$/";
        preg_match($standard,$Stval,$matches,PREG_UNMATCHED_AS_NULL);

        $stvl=$matches[0]==''?$Stval:'';
        $spress=$matches[0]!=''?$matches[0]:'';
        $json_STDATA[]=array("IDTM"=>$JID_TIME,"STVAL"=>$stvl,"SPRESS"=>$spress,"MMVAL"=>$MM_TPRS);
    }
    $json_STDA=json_encode($json_STDATA,JSON_UNESCAPED_UNICODE);

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
    $responce='';
    if(!$r){
        oci_rollback($conn);
        $e=oci_error($stid3);
        $responce=json_encode(array("message"=>$e['message']));
        echo AESEnCode($json);
    }else{
        $clob->save($ST_PREB);
        oci_commit($conn);
        $responce=json_encode($JSONarray,JSON_UNESCAPED_UNICODE);
        oci_free_statement($stid);
        oci_close($conn);

    }
    return $responce;
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
    $jsonArrary = [];
    $ID_REGION='';
    $ID_ORDER='';
    $NM_ORDER='';
    $ST_DOSE='';
    $ST_USENO='';
    $JID_TIME='';
    $DT='';
    $TM='';

    while ($row = oci_fetch_array($stid)) {
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
        $jsonArrary[] = array("idPt"=>$idPt,"DT_EXCUTE" => $DT, "TM_EXCUTE" => $TM, "JID_TIME" => $JID_TIME, "ID_REGION" => $ID_REGION, "ID_ORDER" => $ID_ORDER,
            "NM_ORDER" => $NM_ORDER, "DB_DOSE" => $DB_DOSE,"ST_DOSE" => $ST_DOSE, "ST_USENO" => $ST_USENO,"LSTPT"=>$LSTPT,"sTraID" => $TransKey);
    }

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
    /*[{"idFrm":"","SFRMDTSEQ":"","ITNO":"","IDTM":"","IDGP":"","FORBID":[],"ID":"","STM":"","DBDOSE":"","SDOSE":"","USEF":"","LSTPT":""}]*/
    $DATAB_1=str_replace('"IDGP":""','"IDGP":'.'"'.$ID_REGION.'"',$ST_DATAB);
    $DATAB_2=str_replace('"ID":""','"ID":'.'"'.$ID_ORDER.'"',$DATAB_1);
    $DATAB_3=str_replace('"STM":""','"STM":'.'"'.$NM_ORDER.'"',$DATAB_2);
    $DATAB_4=str_replace('"SDOSE":""','"SDOSE":'.'"'.$ST_DOSE.'"',$DATAB_3);
    $DATAB_5=str_replace('"USEF":""','"USEF":'.'"'.$ST_USENO.'"',$DATAB_4);
    $DATAB_6=str_replace('"IDTM":""','"IDTM":'.'"'.$JID_TIME.'"',$DATAB_5);

    /* echo $DATAB_6."<br>";*/
    $TPsql = "INSERT INTO HIS803.NISWSTP(ID_TABFORM,ID_TRANSACTION,ID_PATIENT,ID_INPATIENT,DT_EXCUTE,
            TM_EXCUTE,ST_DATAA,ST_DATAB,ST_DATAC,ST_PREA,ST_PREB,FORMSEQANCE,ID_BED,DM_PROCESS,UR_PROCESS,JID_NSRANK,FORMSEQANCE_WT)
             VALUES ('ILSGA','$TransKey','$idPt','$INPt','$DT',
             '$TM','$ST_DATAA','$DATAB_6','$ST_DATAC','$ST_PREA',EMPTY_CLOB(),'$sFSq','$ID_BED','$DM_PR','$sUr','$JID_NSRANK','$FORMSEQANCE_WT')
             RETURNING  ST_PREB INTO :ST_PREB";

    $stid3 = oci_parse($conn, $TPsql);
    $clob=oci_new_descriptor($conn,OCI_D_LOB);
    oci_bind_by_name($stid3,":ST_PREB",$clob,-1,OCI_B_CLOB);
    $r = oci_execute($stid3,OCI_NO_AUTO_COMMIT);
    $responce='';
    if (!$r) {
        oci_rollback($conn);
        $e=oci_error($stid3);
        $responce = json_encode(array("message" =>  $e['message']));

    } else {
        $clob->save($ST_PREB);
        oci_commit($conn);
        $responce = json_encode($jsonArrary, JSON_UNESCAPED_UNICODE);
        oci_free_statement($stid);
        oci_close($conn);
    }
    return $responce;
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
    $jsonArrary = [];
    $DT_EXCUTE='';
    $TM_EXCUTE='';
    $NO_MMAL='';
    $regionARR=[];
    while ($row = oci_fetch_array($stid)) {
        $DT_EXCUTE = oci_result($stid, "DT_EXCUTE");
        $TM_EXCUTE = oci_result($stid, "TM_EXCUTE");
        $REGION = oci_result($stid, "REGION");
        $NO_MMAL = oci_result($stid, "NO_MMVAL");
        $JID_TIME = oci_result($stid, "IDTM");
        $NO_MMAL=$NO_MMAL=' '?$NO_MMAL:'';

        array_push($regionARR,$REGION);
        $jsonArrary[] = array("idPt"=>$idPt,"DT_EXCUTE" => $DT_EXCUTE, "TM_EXCUTE" => $TM_EXCUTE,"JID_TIME"=>$JID_TIME,"REGION" => $regionARR,
                            "NO_MMAL" =>$NO_MMAL,"sTraID" => $TransKey,"FORMSEQANCE"=>$FORMSEQANCE_WT);
    }


    $leng=count($jsonArrary);
    /* {"DT_EXCUTE":"1090219","TM_EXCUTE":"160600","REGION":["B","C"],"NO_MMAL":""}*/
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
    /*[{"REGION":["B","C"],"NO_MMVAL":"ISLF00000001"}]*/
    $DATAC_1=str_replace('"FORBID":[]','"FORBID":'.'"['.implode(",",$regionARR).']"',$ST_DATAC);
    $DATAC_2=str_replace('"NO_MMVAL":[{"ISLF00000001":"傷口"},{"ISLF00000002":"病患拒打"},{"ISLF00000003":"截肢"},{"ISLF00000004":"禁作治療"}]','"NO_MMVAL":'.'"'.$NO_MMAL.'"',$DATAC_1);
    $TPsql = "INSERT INTO HIS803.NISWSTP(ID_TABFORM,ID_TRANSACTION,ID_PATIENT,ID_INPATIENT,DT_EXCUTE,
            TM_EXCUTE,ST_DATAA,ST_DATAB,ST_DATAC,ST_PREA,ST_PREB,FORMSEQANCE,ID_BED,DM_PROCESS,UR_PROCESS,JID_NSRANK,FORMSEQANCE_WT)
             VALUES ('ILSGA','$TransKey','$idPt','$INPt','$DT_EXCUTE',
             '$TM_EXCUTE','$ST_DATAA','$ST_DATAB','$DATAC_2','$ST_PREA',EMPTY_CLOB(),'$sFSq','$ID_BED','$DM_PR','$sUr','$JID_NSRANK','$FORMSEQANCE_WT')
              RETURNING  ST_PREB INTO :ST_PREB";

    $stid3 = oci_parse($conn, $TPsql);
    $clob=oci_new_descriptor($conn,OCI_D_LOB);
    oci_bind_by_name($stid3,":ST_PREB",$clob,-1,OCI_B_CLOB);
    $r = oci_execute($stid3,OCI_NO_AUTO_COMMIT);
    $responce='';
    if (!$r) {
        oci_rollback($conn);
        $e=oci_error($stid3);
        $responce = json_encode(array("message" => $e['message']));

    } else {
        $clob->save($ST_PREB);
        oci_commit($conn);
        $responce = json_encode(($jsonArrary[$leng-1]),JSON_UNESCAPED_UNICODE);
        oci_free_statement($stid);
        oci_close($conn);
    }
    return $responce;
}