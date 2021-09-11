<?php
function GetMARSIniJson($DB, $sFm, $Idpt, $INPt, $ID_BED, $sTraID, $sSave, $DateTime, $sUr, $JID_NSRANK, $FORMSEQANCE_WT)
{
    $SQL = "SELECT ST_DATAA FROM NISWSIT WHERE ID_TABFORM='MARS' ";

    $Result = $DB->Select($SQL);

    $ST_DATA = "";
    while ($s_row = $DB->FetchArray($Result)) {
        $ST_DATA = $s_row['ST_DATAA']->load();
    }


    $SQL = "SELECT * FROM (
        SELECT DA_EGNAME NMOD, DA_UNIT UT, ' ' IGUL, 
        USENO||'('||US_USNAME||')'||PATHNO||'('||PA_PANAME||')' USPA,
        DECODE(DA_DRUGKIND, '4','2','5','2','1') SROD, ORD.*, 
        (SELECT COUNT(*) FROM NSMARS WHERE ID_INPATIENT = INQ AND ID_ORDER = IDOD
         AND ID_HISORDKEY = HISKEY AND DT_TAKEDRUG = UDT AND TM_TAKEDRUG = UTM) SCT
        FROM TOPDIA, TOPUSE, TOPPAT,
        (SELECT  ud_inpseq INQ, ud_uddate UDT, ud_udtime UTM, ud_diacode IDOD, 
          ud_qty_pertime DOSE, ud_power PW, ud_useno USENO, lo_pathno PATHNO, 
          ud_loseq LOSEQ, lo_memo MMOD, lo_begdate BDT, lo_begtime BTM, 
          lo_dcdate EDT, lo_dctime ETM, 
          'L'||To_Char(ud_inpseq)||'@'||To_Char(ud_loseq) HISKEY,
          CASE WHEN lo_dcdate = ' ' THEN 'N' ELSE
           CASE WHEN ud_uddate||ud_udtime > lo_dcdate||lo_dctime THEN 'Y'
             ELSE 'N' END END DCTP  
         FROM inalor, inaudd
         WHERE ud_inpseq=lo_inpseq AND ud_loseq=lo_loseq
           AND  ud_uddate = '1100821' AND ud_udtime between '0841' AND '0910' AND ud_inpseq =970000897
           AND ((lo_dcdate = ' ') OR (lo_dcdate>=rocdate(sysdate)))
        
         UNION ALL
         SELECT  ud_inpseq INQ, ud_uddate UDT, ud_udtime UTM, ud_diacode IDOD, 
          ud_qty_pertime DOSE, ud_power PW, ud_useno USENO, lo_pathno PATHNO, 
          ud_loseq LOSEQ, lo_memo MMOD, lo_begdate BDT, lo_begtime BTM, 
          lo_dcdate EDT, lo_dctime ETM, 
          'L'||To_Char(ud_inpseq)||'@'||To_Char(ud_loseq) HISKEY,
          CASE WHEN lo_dcdate = ' ' THEN 'N' ELSE
           CASE WHEN ud_uddate||ud_udtime > lo_dcdate||lo_dctime THEN 'Y'
            ELSE 'N' END END DCTP  
         FROM inalor, inaudo
         WHERE ud_inpseq=lo_inpseq AND ud_loseq=lo_loseq
           AND ud_uddate = '1100821' AND ud_udtime between '0841' AND '0910' AND ud_inpseq =970000897
           AND ((lo_dcdate = ' ') OR (lo_dcdate>=rocdate(sysdate)))
        
         union all
         SELECT dt_inpseq INQ, dt_lookdt UDT, dt_exetime UTM, dt_diacode IDOD, 
          dt_qty_pertime DOSE, dt_power PW, dt_useno USENO, dt_pathno PATHNO,
          dt_loseq LOSE, dt_memo MMOD, dt_lookdt BDT, dt_exetime BTM, diffdate(dt_lookdt, -1) DDT, dt_exetime DTM,
          'S'||To_Char(dt_inpseq)||'@'||To_Char(dt_Lookdt)||'@'||To_Char(dt_seq)||'@'||to_char(dt_no) HISKEY,
          DT_CANCD DCTP
         FROM inahdr, inadet
         WHERE hd_inpseq=dt_inpseq AND hd_divno=dt_divno
         AND hd_lookdt=dt_lookdt AND hd_seq=dt_seq AND hd_type='S'
         AND dt_lookdt>=diffdate('1100820', 5) AND dt_inpseq =970000897 ) ORD
        WHERE DA_DIACODE = IDOD AND USENO = US_USENO(+)
          AND PATHNO = PA_PATHNO(+))
          
        WHERE (SROD = '2' OR SCT = 0)
        ORDER BY SROD, HISKEY";


    $IniResult = $DB->Select($SQL);
    $arr = [];
    while ($row = $DB->FetchArray($IniResult)) {

        $arr[] = (object)$row;

    }

    //長期頁面來源：SROD = '1' and HISKEY的第一碼='L'
    //針劑點滴頁面來源：SROD = '2'
    //臨時頁面來源：SROD = '1' and HISKEY的第一碼='S'
    $ST_DATA = json_decode($ST_DATA);

    $Routine = array_filter($arr, function ($val) {
        return $val->SROD == 1 && substr($val->HISKEY, 0, 1) == "L";
    });
    $Bit = array_filter($arr, function ($val) {
        return $val->SROD == 2;
    });
    $State = array_filter($arr, function ($val) {
        return $val->SROD == 1 && substr($val->HISKEY, 0, 1) == "S";
    });


    $DATAA = json_encode(MapIniObj($Routine, $ST_DATA), JSON_UNESCAPED_UNICODE);
    $DATAB = json_encode(MapIniObj($Bit, $ST_DATA), JSON_UNESCAPED_UNICODE);
    $DATAC = json_encode(MapIniObj($State, $ST_DATA), JSON_UNESCAPED_UNICODE);


    // MARD 延時給藥原因
    // MARR 未給藥原因

    $response = array(
        "sTraID" => $sTraID,
        "sSave" => $sSave,
        "REFUSERSN" => Reason($DB, 'MARR'),
        "DELAYRSN" => Reason($DB, 'MARD')
    );
    $TP_Value = array(
        "ID_TABFORM" => "'$sFm'",
        "ID_TRANSACTION" => "'$sTraID'",
        "ID_PATIENT" => "'$Idpt'",
        "ID_INPATIENT" => "'$INPt'",
        "DT_EXCUTE" => "' '",
        "TM_EXCUTE" => "' '",
        "ST_DATAA" => "'$DATAA'",
        "ST_DATAB" => "'$DATAB'",
        "ST_DATAC" => "'$DATAC'",
        "ID_BED" => "'$ID_BED'",
        "DM_PROCESS" => "'$DateTime'",
        "UR_PROCESS" => "'$sUr'",
        "JID_NSRANK" => "'$JID_NSRANK'",
        "FORMSEQANCE_WT" => "'$FORMSEQANCE_WT'"

    );


    $Insert_result = $DB->Insert('NISWSTP', $TP_Value);

    if ($Insert_result) {
        $DB->Commit();
        $DB->FreeStatement($Insert_result);
    } else {
        $DB->Rollback();
    }


    return json_encode($response, JSON_UNESCAPED_UNICODE);
}

function GetMARSPageJson($DB, $sFm, $sPg, $sTraID)
{

    $Fields = "ST_DATA" . $sPg;

    $SQL = "SELECT " . $Fields . " FROM HIS803.NISWSTP WHERE ID_TRANSACTION=:sTraID AND ID_TABFORM = :ID_TABFORM";

    $bind = array(":sTraID" => $sTraID,
        ":ID_TABFORM" => $sFm
    );

    $Result = $DB->Select($SQL, $bind);

    $ST_DATA = "";
    while ($s_row = $DB->FetchArray($Result)) {
        $ST_DATA = $s_row[$Fields]->load();
    }


    return $ST_DATA;
}

function PosMARSSave($DB, $sTraID, $sFm, $sDt, $sTm, $sUr)
{

    //UPDATE NISWSTP => TM_EXCUTE DT_EXCUTE
    $Filed = array("TM_EXCUTE" => ":TM_EXCUTE", "DT_EXCUTE" => ":DT_EXCUTE", "ID_TRANSACTION" => ":ID_TRANSACTION");

    $Condition = array("ID_TRANSACTION" => "'$sTraID'");

    $bind = array(":TM_EXCUTE" => "$sTm", ":DT_EXCUTE" => "$sDt");

    $DB->Update('NISWSTP', $Filed, $Condition, $bind);


    $SQL = "SELECT 
             ID_INPATIENT,ID_PATIENT, ST_DATAA,ST_DATAB,ST_DATAC,
             ID_BED,JID_NSRANK,FORMSEQANCE_WT
             FROM HIS803.NISWSTP
             WHERE ID_TRANSACTION=:sTraID";

    $NISWSTP_bind = array(
        ":sTraID" => $sTraID,
    );
    $SelectResult = $DB->Select($SQL, $NISWSTP_bind);


    $InPt = "";
    $IdPt = "";
    $ST_DATA = "";
    $ST_DATB = "";
    $ST_DATC = "";
    $ID_BED = "";
    $JID_NSRANK = "";
    $FORMSEQANCE_WT = "";

    while ($s_row = $DB->FetchArray($SelectResult)) {
        $ST_DATA = $s_row['ST_DATAA']->load();
        $ST_DATB = $s_row['ST_DATAB']->load();
        $ST_DATC = $s_row['ST_DATAC']->load();

        $InPt = $s_row['ID_INPATIENT'];
        $IdPt = $s_row['ID_PATIENT'];
        $ID_BED = $s_row['ID_BED'];
        $JID_NSRANK = $s_row['JID_NSRANK'];
        $FORMSEQANCE_WT = $s_row['FORMSEQANCE_WT'];
    }

    $DateTime = date("YmdHis");
    $Y_VID = substr($DateTime, 0, 4);
    $Date = substr($DateTime, -10, 10);
    $Y_ROC = (int)$Y_VID - 1911;
    $System_DT = (string)$Y_ROC . (string)$Date;

    $DATAA = json_decode($ST_DATA);

    $DATAB = json_decode($ST_DATB);

    $DATAC = json_decode($ST_DATC);

    $Info = (object)array(
        "sUr" => $sUr,
        "InPt" => $InPt,
        "IdPt" => $IdPt,
        "ID_BED" => $ID_BED,
        "exDate" => $sDt,
        "exTime" => str_pad($sTm, '6', '0', STR_PAD_RIGHT),
        "ROCDT" => $System_DT,
        "JID_NSRANK" => $JID_NSRANK,
        "FORMSEQANCE_WT" => $FORMSEQANCE_WT,
    );

    $result = (object)array("result" => "", "message" => "");


    $hasSTP_A = array_filter($DATAA, function ($value) {
        return $value->STP != "";
    });
    $hasSTP_B = array_filter($DATAB, function ($value) {
        return $value->STP != "";
    });
    $hasSTP_C = array_filter($DATAC, function ($value) {
        return $value->STP != "";
    });

    if (count($hasSTP_A) > 0) {
        $PageAInsertResult = InsertMars($DB, $hasSTP_A, $Info);

        if (!$PageAInsertResult) {
            $result->result = "false";
            $result->message = $DB->GetErrorMsg();
            $DB->Rollback();
            return json_encode($result, JSON_UNESCAPED_UNICODE);
        }
    }

    if (count($hasSTP_B) > 0) {
        $PageBInsertResult = InsertMars($DB, $hasSTP_B, $Info);
        if (!$PageBInsertResult) {
            $result->result = "false";
            $result->message = $DB->GetErrorMsg();
            $DB->Rollback();
            return json_encode($result, JSON_UNESCAPED_UNICODE);
        }
    }

    if (count($hasSTP_C) > 0) {
        $PageCInsertResult = InsertMars($DB, $hasSTP_C, $Info);
        if (!$PageCInsertResult) {
            $result->result = "false";
            $result->message = $DB->GetErrorMsg();
            $DB->Rollback();
            return json_encode($result, JSON_UNESCAPED_UNICODE);
        }
    }


    $result->result = "true";
    $DB->Commit();
    return json_encode($result, JSON_UNESCAPED_UNICODE);


}

function GetMARSJson($DB, $sFm, $idPt, $INPt, $sUr, $sDt, $sTm, $sPg, $sFSq)
{
    $SQL = "SELECT ST_DATAA FROM NISWSIT WHERE ID_TABFORM='$sFm'";

    $Result = $DB->Select($SQL);

    $ST_DATA = "";
    while ($s_row = $DB->FetchArray($Result)) {
        $ST_DATA = $s_row['ST_DATAA']->load();
    }

    $Info = GetPationData($DB, $idPt, $INPt, $sUr);

    $ID_BED = $Info->ID_BED;
    $ID_TRANSB = $Info->ID_TRANSB;
    $ID_TRANSA = $Info->ID_TRANSA;
    $ID_COMFIRM = $Info->ID_COMFIRM;
    $JID_NSRANK = $Info->JID_NSRANK;
    $FORMSEQANCE_WT = $Info->FORMSEQANCE_WT;


    $TRANSA = str_pad($ID_TRANSA, 8, 0, STR_PAD_LEFT);
    $sTraID = $ID_TRANSB . 'ILSGA' . $TRANSA;


    $SQL = "SELECT * FROM (
        SELECT DA_EGNAME NMOD, DA_UNIT UT, ' ' IGUL, 
        USENO||'('||US_USNAME||')'||PATHNO||'('||PA_PANAME||')' USPA,
        DECODE(DA_DRUGKIND, '4','2','5','2','1') SROD, ORD.*, 
        (SELECT COUNT(*) FROM NSMARS WHERE ID_INPATIENT = INQ AND ID_ORDER = IDOD
         AND ID_HISORDKEY = HISKEY AND DT_TAKEDRUG = UDT AND TM_TAKEDRUG = UTM) SCT
        FROM TOPDIA, TOPUSE, TOPPAT,
        (SELECT  ud_inpseq INQ, ud_uddate UDT, ud_udtime UTM, ud_diacode IDOD, 
          ud_qty_pertime DOSE, ud_power PW, ud_useno USENO, lo_pathno PATHNO, 
          ud_loseq LOSEQ, lo_memo MMOD, lo_begdate BDT, lo_begtime BTM, 
          lo_dcdate EDT, lo_dctime ETM, 
          'L'||To_Char(ud_inpseq)||'@'||To_Char(ud_loseq) HISKEY,
          CASE WHEN lo_dcdate = ' ' THEN 'N' ELSE
           CASE WHEN ud_uddate||ud_udtime > lo_dcdate||lo_dctime THEN 'Y'
             ELSE 'N' END END DCTP  
         FROM inalor, inaudd
         WHERE ud_inpseq=lo_inpseq AND ud_loseq=lo_loseq
           AND  ud_uddate = '$sDt' AND ud_udtime ='$sTm' AND ud_inpseq =970000897
           AND ((lo_dcdate = ' ') OR (lo_dcdate>=rocdate(sysdate)))
        
         UNION ALL
         SELECT  ud_inpseq INQ, ud_uddate UDT, ud_udtime UTM, ud_diacode IDOD, 
          ud_qty_pertime DOSE, ud_power PW, ud_useno USENO, lo_pathno PATHNO, 
          ud_loseq LOSEQ, lo_memo MMOD, lo_begdate BDT, lo_begtime BTM, 
          lo_dcdate EDT, lo_dctime ETM, 
          'L'||To_Char(ud_inpseq)||'@'||To_Char(ud_loseq) HISKEY,
          CASE WHEN lo_dcdate = ' ' THEN 'N' ELSE
           CASE WHEN ud_uddate||ud_udtime > lo_dcdate||lo_dctime THEN 'Y'
            ELSE 'N' END END DCTP  
         FROM inalor, inaudo
         WHERE ud_inpseq=lo_inpseq AND ud_loseq=lo_loseq
           AND ud_uddate = '$sDt' AND ud_udtime ='$sTm' AND ud_inpseq =970000897
           AND ((lo_dcdate = ' ') OR (lo_dcdate>=rocdate(sysdate)))
        
         union all
         SELECT dt_inpseq INQ, dt_lookdt UDT, dt_exetime UTM, dt_diacode IDOD, 
          dt_qty_pertime DOSE, dt_power PW, dt_useno USENO, dt_pathno PATHNO,
          dt_loseq LOSE, dt_memo MMOD, dt_lookdt BDT, dt_exetime BTM, diffdate(dt_lookdt, -1) DDT, dt_exetime DTM,
          'S'||To_Char(dt_inpseq)||'@'||To_Char(dt_Lookdt)||'@'||To_Char(dt_seq)||'@'||to_char(dt_no) HISKEY,
          DT_CANCD DCTP
         FROM inahdr, inadet
         WHERE hd_inpseq=dt_inpseq AND hd_divno=dt_divno
         AND hd_lookdt=dt_lookdt AND hd_seq=dt_seq AND hd_type='S'
         AND dt_lookdt>=diffdate('$sDt', 5) AND dt_inpseq =970000897 ) ORD
        WHERE DA_DIACODE = IDOD AND USENO = US_USENO(+)
          AND PATHNO = PA_PATHNO(+))
        WHERE (SROD = '2' OR SCT = 0)
        ORDER BY SROD, HISKEY";


    $IniResult = $DB->Select($SQL);
    $arr = [];
    while ($row = $DB->FetchArray($IniResult)) {
        $arr[] = (object)$row;
    }

    //長期頁面來源：SROD = '1' and HISKEY的第一碼='L'
    //針劑點滴頁面來源：SROD = '2'
    //臨時頁面來源：SROD = '1' and HISKEY的第一碼='S'
    $ST_DATA = json_decode($ST_DATA);

    $Routine = array_filter($arr, function ($val) {
        return $val->SROD == 1 && substr($val->HISKEY, 0, 1) == "L";
    });
    $Bit = array_filter($arr, function ($val) {
        return $val->SROD == 2;
    });
    $State = array_filter($arr, function ($val) {
        return $val->SROD == 1 && substr($val->HISKEY, 0, 1) == "S";
    });

    $DATAA = json_encode(MapIniObj($Routine, $ST_DATA), JSON_UNESCAPED_UNICODE);
    $DATAB = json_encode(MapIniObj($Bit, $ST_DATA), JSON_UNESCAPED_UNICODE);
    $DATAC = json_encode(MapIniObj($State, $ST_DATA), JSON_UNESCAPED_UNICODE);


    $Time = str_pad($sTm, 6, '0', STR_PAD_RIGHT);
    $DateTime = $sDt . $Time;

    $response = array(
        "sTraID" => $sTraID,
        "sSave" => $ID_COMFIRM,
        "IDPT" => $idPt,
        "INPT" => $INPt,
        "DTEXCUTE" => $sDt,
        "TMEXCUTE" => $sTm
    );

    $TP_Value = array(
        "ID_TABFORM" => "'$sFm'",
        "ID_TRANSACTION" => "'$sTraID'",
        "ID_PATIENT" => "'$idPt'",
        "ID_INPATIENT" => "'$INPt'",
        "DT_EXCUTE" => "'$sDt'",
        "TM_EXCUTE" => "'$Time'",
        "ST_DATAA" => "'$DATAA'",
        "ST_DATAB" => "'$DATAB'",
        "ST_DATAC" => "'$DATAC'",
        "ID_BED" => "'$ID_BED'",
        "DM_PROCESS" => "'$DateTime'",
        "UR_PROCESS" => "'$sUr'",
        "JID_NSRANK" => "'$JID_NSRANK'",
        "FORMSEQANCE_WT" => "'$FORMSEQANCE_WT'"

    );


    $Insert_result = $DB->Insert('NISWSTP', $TP_Value);

    if ($Insert_result) {
        $DB->Commit();
        $DB->FreeStatement($Insert_result);
    } else {
        $DB->Rollback();
    }

    return json_encode($response, JSON_UNESCAPED_UNICODE);
}

function MapIniObj($obj, $IniObj)
{
    $arr = [];
    foreach ($obj as $item) {
        $IniObj->HISKEY = $item->HISKEY;
        $IniObj->IDOD = $item->IDOD;
        $IniObj->NMOD = $item->NMOD;
        $IniObj->DOSE = $item->DOSE;
        $IniObj->UT = $item->UT;
        $IniObj->USPA = $item->USPA;
        $IniObj->MMOD = $item->MMOD;
        $IniObj->IGUL = $item->IGUL;
        $IniObj->BDT = $item->BDT;
        $IniObj->BTM = $item->BTM;
        $IniObj->EDT = $item->EDT;
        $IniObj->ETM = $item->ETM;
        $IniObj->DCTP = $item->DCTP;
        $IniObj->SCT = $item->SCT;
        $IniObj->UDDT = $item->UDT;
        $IniObj->UDTM = $item->UTM;
        array_push($arr, unserialize(serialize($IniObj)));
    }
    return $arr;
}

function PushMany($val, $time)
{
    $i = 0;
    $arr = [];
    while ($i < $time) {
        $tmp = unserialize(serialize($val));
        array_push($arr, $tmp);
        $i++;
    }
    return $arr;
}

function InsertMars($DB, $arr, $info)
{

    $sUr = $info->sUr;
    $InPt = $info->InPt;
    $IdPt = $info->IdPt;
    $ID_BED = $info->ID_BED;
    $JID_NSRANK = $info->JID_NSRANK;
    $FORMSEQANCE_WT = $info->FORMSEQANCE_WT;
    $System_DT = $info->ROCDT;
    $exDate = $info->exDate;
    $exTime = $info->exTime;



    foreach ($arr as $item) {

        $ID_ORDER = $item->IDOD;
        $ID_HISORDKEY = $item->HISKEY;
        $UDT = $item->UDDT;
        $UTM = $item->UDTM;
        $CID_MAR = substr($ID_HISORDKEY, 0, 1) . 'D';

        $IDRR = trim($item->IDRR) == "" ? " " : $item->IDRR;//未給藥原因代碼
        $IDRD = trim($item->IDRD) == "" ? " " : $item->IDRD;//延時給藥原因代碼
        $MMOD = trim($item->MMOD) == "" ? " " : $item->MMOD;//備註

        $InsertTEST_DATA = array(
            "DATESEQANCE" => "NIS_DATETIMESEQ",
            "ID_PATIENT" => "'$IdPt'",
            "ID_INPATIENT" => "'$InPt'",
            "NO_OPDSEQ" => "'0'",
            "CID_MAR" => "'$CID_MAR'",
            "ID_HISORDKEY" => "'$ID_HISORDKEY'",
            "DT_EXCUTE" => "'$exDate'",
            "TM_EXCUTE" => "'$exTime'",
            "ID_ORDER" => "'$ID_ORDER'",
            "JID_REFUSERSN" => "'$IDRR'",
            "JID_DELAYRSN" => "'$IDRD'",
            "DT_TAKEDRUG" => "'$UDT'",
            "TM_TAKEDRUG" => "'$UTM'",
            "MM_MAR" => "'$MMOD'",
            "ID_BED" => "'$ID_BED'",
            "JID_NSRANK" => "'$JID_NSRANK'",
            "FORMSEQANCE_WT" => "'$FORMSEQANCE_WT'",
            "FORMSEQANCE_FM" => "' '",
            "DM_PROCESS" => "'$System_DT'",
            "UR_PROCESS" => "'$sUr'",
            "CID_EXCUTE" => "' '",
            "ID_FROMSYS" => "'RWD'"
        );


        $Insert_result = $DB->Insert('NSMARS', $InsertTEST_DATA);
        if (!$Insert_result) {
            return false;
        }
    }
    return true;
}

function Reason($DB, $CID_CLASS)
{

    $SQL = " SELECT JID_KEY,ID_ITEM,NM_ITEM,ST_TEXT2 FROM NSCLSI  WHERE CID_CLASS='$CID_CLASS' AND IS_ACTIVE='Y'";


    $R_Result = $DB->Select($SQL);
    $REFUSERSN = [];
    while ($R_row = $DB->FetchArray($R_Result)) {
        $REFUSERSN[] = (object)$R_row;
    }
    return $REFUSERSN;
}

function GetPationData($DB, $Idpt, $INPt, $sUr)
{
    $SQL = "SELECT (SELECT his803.nis_datetimeseq FROM DUAL) ID_TRANSB,
            his803.GetWSTPNEXTVAL ID_TRANSA, 
             CR.CA_BEDNO ID_BED, WM.formseqance_wt FORMSEQANCE_WT,
            (SELECT Max(CI.id_item) FROM HIS803.NSUSER UR, HIS803.NSCLSI CI
            WHERE  UR.jid_nsrank <> ' '
            AND UR.jid_nsrank = CI.jid_key AND CI.cid_class='RANK') JID_NSRANK,
            (SELECT PU.is_confirm FROM HIS803.NSPROU PU
            WHERE  PU.id_user  =  WM.id_user AND PU.id_program = 'NISCISLN') ID_COMFIRM   
            FROM HIS803.NSWKBD WD, HIS803.NSWKTM WM, HIS803.INACAR CR
            WHERE  CR.CA_MEDNO = '$Idpt' AND CR.CA_INPSEQ = '$INPt'
            AND  WM.id_user(+) ='$sUr'
            AND  WM.dt_offwork(+) = ' ' AND  WM.dm_cancd(+) =' ' 
            AND  WM.formseqance_wt(+)= WD.formseqance_wt
            AND WD.id_bed(+) = CR.CA_BEDNO 
            AND CR.CA_CHECK = 'Y' AND CR.CA_DIVINSU = 'N'
            AND CR.CA_CLOSE='N'";
    $Result = $DB->SELECT($SQL);
    $arr = [];
    while ($s_row = $DB->FetchArray($Result)) {
        $arr = (object)$s_row;
    }
    return $arr;
}