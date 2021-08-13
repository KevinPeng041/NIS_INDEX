<?php
function GetMARSIniJson($DB, $sFm, $Idpt, $INPt, $ID_BED, $sTraID, $sSave, $DateTime, $sUr, $JID_NSRANK, $FORMSEQANCE_WT)
{
    $SQL = "SELECT ST_DATAA FROM NISWSIT WHERE ID_TABFORM='MARS' ";

    $Result = $DB->Select($SQL);

    $ST_DATA = "";
    while ($s_row = $DB->FetchArray($Result)) {
        $ST_DATA = $s_row['ST_DATAA']->load();
    }
    $ST_DATA = json_decode($ST_DATA);


    $A = PushMany($ST_DATA, 5);
//    $B = PushMany($ST_DATA, 10);
//    $C = PushMany($ST_DATA, 15);


    foreach ($A as $index => $value) {
        foreach ($value as $key => $item) {
            switch ($key) {
                case "idFrm"://CID_MAR
                    $item = 'LD';
                    break;
                case "IDGP"://DIACODE
                    $item = 'IREC';
                    break;
                case "STM"://藥名
                    $item = 'B-COMPLEX☆100MG/1ML INJ';
                    break;
                case "DBDOSE"://劑量
                    $item = $index;
                    break;
                case "USEF"://頻率
                    $item = 'QD 每日一次 ,早餐';
                    break;
                case "IMGURL"://圖片路徑
                    $item = 'http://localhost/img/IBC.png';
                    break;
                case "STMM"://備註
                    $item = '測試資料';
                    break;
            }
            $value->$key = $item;
        }

    }
    $A = json_encode($A, JSON_UNESCAPED_UNICODE);

    $response = array(
        "sTraID" => $sTraID,
        "sSave" => $sSave,
    );
    $TP_Value = array(
        "ID_TABFORM" => "'$sFm'",
        "ID_TRANSACTION" => "'$sTraID'",
        "ID_PATIENT" => "'$Idpt'",
        "ID_INPATIENT" => "'$INPt'",
        "DT_EXCUTE" => "' '",
        "TM_EXCUTE" => "' '",
        "ST_DATAA" => "'$A'",
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
    //UPDATE NISWSTP TM_EXCUTE DT_EXCUTE
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

    $ST_DATA = "";
    $ST_DATB = "";
    $ST_DATC = "";

    $InPt = "";
    $IdPt = "";
    $ID_BED = "";
    $JID_NSRANK = "";
    $FORMSEQANCE_WT = "";

    while ($s_row = $DB->FetchArray($SelectResult)) {
//        $ST_DATA = $s_row['ST_DATAA']->load();
//        $ST_DATB = $s_row['ST_DATAB']->load();
//        $ST_DATC = $s_row['ST_DATAC']->load();
        $InPt = $s_row['ID_INPATIENT'];
        $IdPt = $s_row['ID_PATIENT'];
        $ID_BED = $s_row['ID_BED'];
        $JID_NSRANK = $s_row['JID_NSRANK'];
        $FORMSEQANCE_WT = $s_row['FORMSEQANCE_WT'];
    }

    $DateTime = date("YmdHis");
    $Y_VID = substr($DateTime, 0, 4);
    $Date = substr($DateTime, -10, 10);
    $Y_TW = (int)$Y_VID - 1911;
    $System_DT = (string)$Y_TW . (string)$Date;

    $InsertTEST_DATA = array(
        "DATESEQANCE" => "NIS_DATETIMESEQ",
        "ID_PATIENT" => "'$IdPt'",
        "ID_INPATIENT" => "'$InPt'",
        "NO_OPDSEQ" => "'0'",
        "CID_MAR" => "'LD'",
        "ID_HISORDKEY" => "'L970000884@20'",
        "DT_EXCUTE" => "'$sDt'",
        "TM_EXCUTE" => "'$sTm'",
        "ID_ORDER" => "'IUSAV'",
        "JID_REFUSERSN" => "' '",
        "JID_DELAYRSN" => "' '",
        "DT_TAKEDRUG" => "'$sDt'",
        "TM_TAKEDRUG" => "'$sTm'",
        "MM_MAR" => "' '",
        "ID_BED" => "'$ID_BED'",
        "JID_NSRANK" => "'$JID_NSRANK'",
        "FORMSEQANCE_WT" => "'$FORMSEQANCE_WT'",
        "FORMSEQANCE_FM" => "' '",
        "DM_PROCESS" => "'$System_DT'",
        "UR_PROCESS" => "'$sUr'",
        "CID_EXCUTE" => "' '",
        "ID_FROMSYS" => "'NIS'"
    );

    $Insert_result = $DB->Insert('NSMARS', $InsertTEST_DATA);

    $result = (object)array("result" => "", "message" => "");
    if ($Insert_result) {
        $result->result = "true";
        $DB->Commit();
        $DB->FreeStatement($Insert_result);
    } else {
        $result->result = "false";
        $result->message = $DB->GetErrorMsg();
        $DB->Rollback();
    }


    return json_encode($result, JSON_UNESCAPED_UNICODE);

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

