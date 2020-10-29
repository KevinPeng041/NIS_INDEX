<?php
date_default_timezone_set('Asia/Taipei');
function GetCNCDIniJson($conn,$Idpt,$INPt,$ID_BED,$sTraID,$sSave,$date,$sUr,$JID_NSRANK,$FORMSEQANCE_WT){
    $sql=" SELECT 
            MH_NAME AS NAME,
            MH_MEDNO AS MEDNO,
            DECODE(MH_SEX, '1', '男', '2', '女', '不分') AS SEX,
            CA_BEDNO AS BEDNO,
            TO_CHAR(DT_INPSEQ) AS INPSEQ, 
            DT_LOOKDT AS LOOKDT,
             WMSYS.WM_CONCAT(DT_DIACODE) AS DIACODE,
            ITW_BARCODE AS BARCODE ,
            WMSYS.WM_CONCAT('S' || DT_INPSEQ || '@' || DT_LOOKDT || '@' || DT_SEQ || '@' || DT_NO) AS ID_HISORDKEY ,
            WMSYS.WM_CONCAT((SELECT   CON_CONNAME FROM TOPCON WHERE CON_LCSKIND = SCR_LCSKIND AND CON_CONCODE = SCR_CONCODE))AS CONNAME, 
            WMSYS.WM_CONCAT((SELECT  SPE_MELTHOD FROM TOPSPE WHERE SPE_LCSKIND = SCR_LCSKIND AND SPE_SPECODE = SCR_SPECODE))     AS SPENAME,
            WMSYS.WM_CONCAT(DT_DIACODE  || ':' || DA_EGNAME)  AS EGNAME,
            WMSYS.WM_CONCAT(TO_CHAR(ITW_WORKNO))  AS MACHINENO,
            WMSYS.WM_CONCAT(DT_SENDCD)AS SENDCD, 
           WMSYS.WM_CONCAT('http://192.168.16.77:8005/InExam/pic/' || SUBSTR(DT_SENDCD,1,1) ||  SCR_CONCODE || '.JPG')  AS PICURL, 
           WMSYS.WM_CONCAT('http://192.168.16.77/labinfo/web/LabInfo.asp?DIACODE=' || DT_DIACODE)  AS DETAILURL,
            TO_CHAR(DT_SEQ) AS ORDERSEQ,
           WMSYS.WM_CONCAT(TO_CHAR(DT_NO) )  AS ORDERNO,
             DT_PROCDATE AS ORDERDATE,
             DT_PROCTIME AS ORDERTIME,
           WMSYS.WM_CONCAT(DT_WORKNO) AS WORKNO,
           WMSYS.WM_CONCAT(CASE ED_CLASS WHEN '0B' THEN 1 WHEN '0A' THEN 2  WHEN '0D' THEN 3 WHEN '0J' THEN 4  WHEN '0Y' THEN 5  WHEN '0C' THEN 6 WHEN '0H' THEN 7 WHEN '0L' THEN 8
              WHEN '0M' THEN 9 WHEN '0V' THEN 10 WHEN '0Z' THEN 11 WHEN '0G' THEN 12 WHEN '0I' THEN 13 WHEN '0O' THEN 13 WHEN '0U' THEN 14 WHEN '0F' THEN 15 
              WHEN '0X' THEN 15 WHEN '0S' THEN 16 WHEN '0E' THEN 17 WHEN '0K' THEN 18 WHEN '0P' THEN 19 WHEN '0N' THEN 20 WHEN '0T' THEN 21 WHEN '1A' THEN 22
               WHEN '0Q' THEN 23 WHEN '0R' THEN 24 WHEN '1S' THEN 25 WHEN '2S' THEN 26 END)   AS sort_num 
            FROM TREMED
             INNER JOIN  INACAR ON   MH_MEDNO = CA_MEDNO 
            INNER JOIN  INADET ON  CA_INPSEQ = DT_INPSEQ AND CA_DIVNO = DT_DIVNO 
            INNER JOIN  INATWN ON  ITW_INPSEQ = DT_INPSEQ AND ITW_LOOKDT = DT_LOOKDT AND DT_WORKNO = ITW_WORKNO 
            INNER JOIN  TOPDIA ON   DA_DIACODE = DT_DIACODE 
            INNER JOIN  TOPSCR ON DT_DIACODE = SCR_DIACODE AND DT_SPECODE = SCR_SPECODE 
            LEFT JOIN  SYSESD ON  ED_SENDCD = DT_SENDCD 
            WHERE CA_MEDNO  ='$Idpt' AND CA_DIVINSU = 'N' AND CA_CLOSE = 'N' AND CA_CHECK<>'D' AND DT_CANCD = 'N' AND DT_LOOKDT BETWEEN ROCDATE(SYSDATE-14) AND
             ROCDATE(SYSDATE) AND(DT_SENDCD LIKE '3%' or DT_SENDCD LIKE 'M%')
            AND DT_LABDEGREE = '0' AND(ITW_STATUS = '2')
             AND NOT EXISTS(SELECT * FROM  TOPLBT WHERE  LBT_MEDNO = MH_MEDNO AND DT_INPSEQ = LBT_SEQ AND LBT_DIACODE = DT_DIACODE
              AND DT_LOOKDT = LBT_LOOKDT  AND ITW_WORKNO = LBT_MACHINENO  AND LBT_CANDATE=' ') 
               GROUP BY MH_NAME,MH_MEDNO,MH_SEX,CA_BEDNO,DT_INPSEQ,DT_LOOKDT,ITW_BARCODE,DT_SEQ,DT_PROCDATE,DT_PROCTIME
            ORDER BY BARCODE
           ";

    $stid=oci_parse($conn,$sql);
    oci_execute($stid);
    $arr=[];
    while (oci_fetch_array($stid)){
        $NAME=oci_result($stid,"NAME");//姓名
        $MEDNO=oci_result($stid,"MEDNO");//病歷號
        $SEX=oci_result($stid,"SEX");//性別
        $BEDNO=oci_result($stid,"BEDNO");//床位號
        $INPSEQ=oci_result($stid,"INPSEQ");//住院序號
        $HISORDKEY=oci_result($stid,"ID_HISORDKEY");
        $LOOKDT=oci_result($stid,"LOOKDT");//診療日期
        $EGNAME=oci_result($stid,"EGNAME");//藥品英文名稱(商品名)
        $BARCODE=oci_result($stid,"BARCODE");//採血編號
        $CONNAME=oci_result($stid,"CONNAME");
        $SPENAME=oci_result($stid,"SPENAME");
        $DIACODE=oci_result($stid,"DIACODE");//診療代碼
        $MACHINENO=oci_result($stid,"MACHINENO");//申請序號(檢驗查)
        $SENDCD=oci_result($stid,"SENDCD");//傳送碼
        $ORDERSEQ=oci_result($stid,"ORDERSEQ");//序號
        $ORDERNO=oci_result($stid,"ORDERNO");//資料序號
        $SORTNUM=oci_result($stid,"SORT_NUM");//檢驗類別
        $WORKNO=oci_result($stid,"WORKNO");//申請序號
        $arr[]=array("NAME"=>$NAME,"MEDNO"=>$MEDNO,"SEX"=>$SEX,"BEDNO"=>$BEDNO,"INPSEQ"=>$INPSEQ,"LOOKDT"=>$LOOKDT,"EGNAME"=>$EGNAME,"BARCODE"=>$BARCODE,
            "CONNAME"=>explode(",",$CONNAME)[0],"SPENAME"=>explode(",",$SPENAME)[0],"DIACODE"=>$DIACODE,"MACHINENO"=>$MACHINENO,"SENDCD"=>$SENDCD,
            "ORDERSEQ"=>$ORDERSEQ,"ORDERNO"=>$ORDERNO,"SORTNUM"=>$SORTNUM,"WORKNO"=>$WORKNO,"HISORDKEY"=>$HISORDKEY);
    }
    /*無資料:push交易序號回傳,有資料:unshift加到陣列[0]*/
    count($arr)==0?array_push($arr,'{"sSave":"'.$sSave.'","sTraID":"'.$sTraID.'"}'):array_unshift($arr,'{"sSave":"'.$sSave.'","sTraID":"'.$sTraID.'"}');
    $ST_DATAA=json_encode($arr,JSON_UNESCAPED_UNICODE);

    $stm=oci_parse($conn,"
            INSERT INTO HIS803.NISWSTP(
                    ID_TABFORM,ID_TRANSACTION,ID_PATIENT,ID_INPATIENT,
                    DT_EXCUTE,TM_EXCUTE,ST_DATAA,ST_DATAB,ST_DATAC,ST_DATAD,ST_PREA,ST_PREB,
                    ST_PREC,DM_PROCESS,UR_PROCESS,ID_BED,JID_NSRANK,FORMSEQANCE_WT)
             VALUES ('CNCD','$sTraID','$Idpt','$INPt',
             ' ',' ',EMPTY_CLOB(),' ',' ',' ',' ',' ',
             ' ','$date','$sUr','$ID_BED','$JID_NSRANK','$FORMSEQANCE_WT')
             RETURNING ST_DATAA INTO :ST_DATAA");
    $lob=oci_new_descriptor($conn,OCI_D_LOB);
    oci_bind_by_name($stm,':ST_DATAA',$lob,-1,OCI_B_CLOB);
    if ($stm) {
        $result =  oci_execute($stm,OCI_NO_AUTO_COMMIT);
        if(!$result){
            $e=oci_error($stm);
            print htmlentities($e['message']);
            print "\n<pre>\n";
            print htmlentities($e['sqltext']);
            printf("\n%".($e['offset']+1)."s", "^");
            print  "\n</pre>\n";
            return false;
        }else{
            $lob->save($ST_DATAA);
            oci_commit($conn);
        }

    } else {
        oci_rollback($conn);
    }

    return $ST_DATAA;
}

function PosCNCDSave($conn,$sTraID,$sDt,$sTm,$sUr){

    $DateTime = date("YmdHis");
    $STR = substr($DateTime, 0, 4);
    $STR1 = substr($DateTime, -10, 10);
    $str = $STR - 1911;
    $DateTime_NOW = $str . $STR1;
    $LBT_DATE=substr($DateTime_NOW,0,7);
    $LBT_TIME=substr($DateTime_NOW,7,4);

    $Ssql="SELECT ID_PATIENT,ID_INPATIENT,ST_DATAA,DT_EXCUTE,TM_EXCUTE,ID_BED,JID_NSRANK,FORMSEQANCE_WT from HIS803.NISWSTP
        WHERE ID_TABFORM = 'CNCD'  AND ID_TRANSACTION = '$sTraID'";

    $stid=oci_parse($conn,$Ssql);
    oci_execute($stid);
    $IDPT='';
    $IDINPT='';
    $ST_DATAA='';
    $DT_EXCUTE='';
    $TM_EXCUTE='';
    $ID_BED='';
    $JID_NSRANK='';
    $FORMSEQANCE_WT='';
    while (oci_fetch_array($stid)){
        $IDPT=oci_result($stid,"ID_PATIENT");
        $IDINPT=oci_result($stid,"ID_INPATIENT");
        $ST_DATAA=oci_result($stid,"ST_DATAA")->read(2000);
        $DT_EXCUTE=oci_result($stid,"DT_EXCUTE");
        $TM_EXCUTE=oci_result($stid,"TM_EXCUTE");
        $ID_BED=oci_result($stid,"ID_BED");
        $JID_NSRANK=oci_result($stid,"JID_NSRANK");
        $FORMSEQANCE_WT=oci_result($stid,"FORMSEQANCE_WT");
    }
    $response='';


    if(trim($DT_EXCUTE)=="" && trim($TM_EXCUTE)==""){
        if($ST_DATAA){
            $A=json_decode($ST_DATAA);
            if(GetCNCDCheck($ST_DATAA)=="false"){
                return    $response=json_encode(array("response" => "false","message" =>"存檔錯誤訊息:檢驗項目尚未勾選"),JSON_UNESCAPED_UNICODE);

            }
            for ($i=0;$i<count($A);$i++)
            {
                $LBT_LOOKDT=$A[$i]->{"LOOKDT"};
                $LBT_MEDNO=$A[$i]->{"MEDNO"};
                $LBT_SEQ=$A[$i]->{"IDINPT"};
                $LBT_DIACODE=$A[$i]->{"DIACODE"};
                $LBT_WORKNO=$A[$i]->{"WORKNO"};
                $LBT_MACHINENO=$A[$i]->{"MACHINENO"};
                $OrDerKey=$A[$i]->{"ORDERKEY"};

                $INSERT_SQL="INSERT INTO TOPLBT(LBT_DATETIMESEQ,LBT_LOOKDT,LBT_MEDNO,LBT_SEQ,LBT_DIACODE,LBT_WORKNO,
                                LBT_MACHINENO,LBT_TYPE,LBT_EXECDATE,LBT_EXECTIME,LBT_PROCDATE,LBT_PROCTIME,LBT_PRCOPID,
                                LBT_CANDATE,LBT_CANTIME,LBT_CANOPID) 
                                VALUES(his803.NIS_DATETIMESEQ,'$LBT_LOOKDT','$LBT_MEDNO','$LBT_SEQ','$LBT_DIACODE','$LBT_WORKNO'
                                ,'$LBT_MACHINENO','A','$sDt','$sTm','$LBT_DATE','$LBT_TIME','$sUr'
                                ,' ',' ',' ')";

               if (PosCNCDSaveNSMARS($conn,$IDPT,$IDINPT,$OrDerKey,$sDt,$sTm,$ID_BED,$JID_NSRANK,$FORMSEQANCE_WT,$DateTime_NOW,$sUr)!==true){
                   $response=json_encode(array("response" => "false","message" =>"檢體NSMARS存檔錯誤訊息:"),JSON_UNESCAPED_UNICODE);
                   return $response;
                }

                $stid=oci_parse($conn,$INSERT_SQL);
                if (!$stid){
                    $e=oci_error($conn);
                    $response=json_encode(array("response" => "false","message" =>"檢體存檔錯誤訊息:".$e['message']),JSON_UNESCAPED_UNICODE);
                    return $response;
                }
                $Aex=oci_execute($stid,OCI_NO_AUTO_COMMIT);
                if(!$Aex)
                {
                    oci_rollback($conn);
                    $e=oci_error($stid);
                    $response=json_encode(array("response" => "false","message" =>"檢體存檔錯誤訊息:".$e['message']),JSON_UNESCAPED_UNICODE);
                    return $response;
                }
                else{
                    $r=oci_commit($conn);
                    if(!$r){
                        $e=oci_error($conn);
                        $response=json_encode(array("response" => "false","message" =>"檢體存檔錯誤訊息:".$e['message']),JSON_UNESCAPED_UNICODE);
                        return $response;
                    }
                    $response=json_encode(array("response" => "success","message" =>"this is the success message"),JSON_UNESCAPED_UNICODE);

                }
            }
        }
    }
    return $response;

}
function GetCNCDJson($conn,$IDPT,$INPt,$sUr,$sDt,$sTm,$sPg,$sDFL){
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
    $ID_COMFIRM='';

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

    $Arr=[];
    $sSQL="SELECT  LBT_EXECDATE,LBT_EXECTIME,LBT_PRCOPID ,ITW_BARCODE AS BARCODE ,
         WMSYS.WM_CONCAT('S' || DT_INPSEQ || '@' || DT_LOOKDT || '@' || DT_SEQ || '@' || DT_NO) AS ID_HISORDKEY ,
         WMSYS.WM_CONCAT((SELECT   CON_CONNAME FROM TOPCON WHERE CON_LCSKIND = SCR_LCSKIND AND CON_CONCODE = SCR_CONCODE))  AS CONNAME, 
         WMSYS.WM_CONCAT((SELECT  SPE_MELTHOD FROM TOPSPE WHERE SPE_LCSKIND = SCR_LCSKIND AND SPE_SPECODE = SCR_SPECODE))   AS SPENAME,
         WMSYS.WM_CONCAT(DT_DIACODE  || ':' || DA_EGNAME )  AS EGNAME,
         WMSYS.WM_CONCAT(TO_CHAR(ITW_WORKNO))  AS MACHINENO,
         WMSYS.WM_CONCAT(DT_SENDCD)AS SENDCD, 
         WMSYS.WM_CONCAT('http://192.168.16.77:8005/InExam/pic/' || SUBSTR(DT_SENDCD,1,1) ||  SCR_CONCODE || '.JPG')  AS PICURL, 
         WMSYS.WM_CONCAT('http://192.168.16.77/labinfo/web/LabInfo.asp?DIACODE=' || DT_DIACODE)  AS DETAILURL,
         TO_CHAR(DT_SEQ) AS ORDERSEQ,
         WMSYS.WM_CONCAT(TO_CHAR(DT_NO) )  AS ORDERNO,
         DT_PROCDATE AS ORDERDATE,
         DT_PROCTIME AS ORDERTIME,
         WMSYS.WM_CONCAT(DT_WORKNO) AS WORKNO,
         WMSYS.WM_CONCAT(CASE ED_CLASS WHEN '0B' THEN 1 WHEN '0A' THEN 2  WHEN '0D' THEN 3 WHEN '0J' THEN 4  WHEN '0Y' THEN 5  WHEN '0C' THEN 6 WHEN '0H' THEN 7 WHEN '0L' THEN 8
              WHEN '0M' THEN 9 WHEN '0V' THEN 10 WHEN '0Z' THEN 11 WHEN '0G' THEN 12 WHEN '0I' THEN 13 WHEN '0O' THEN 13 WHEN '0U' THEN 14 WHEN '0F' THEN 15 
              WHEN '0X' THEN 15 WHEN '0S' THEN 16 WHEN '0E' THEN 17 WHEN '0K' THEN 18 WHEN '0P' THEN 19 WHEN '0N' THEN 20 WHEN '0T' THEN 21 WHEN '1A' THEN 22
               WHEN '0Q' THEN 23 WHEN '0R' THEN 24 WHEN '1S' THEN 25 WHEN '2S' THEN 26 END)   as sort_num 
              FROM  TOPLBT
              INNER JOIN TREMED ON MH_MEDNO=LBT_MEDNO
              INNER JOIN INACAR   ON  CA_MEDNO=LBT_MEDNO
              INNER JOIN  INADET ON  CA_INPSEQ = DT_INPSEQ AND CA_DIVNO = DT_DIVNO AND DT_DIACODE=LBT_DIACODE	AND DT_LOOKDT=LBT_LOOKDT
              INNER JOIN  TOPDIA ON   DA_DIACODE = DT_DIACODE 
              INNER JOIN  INATWN ON  ITW_INPSEQ = DT_INPSEQ AND ITW_LOOKDT = DT_LOOKDT AND DT_WORKNO = ITW_WORKNO 
              INNER JOIN  TOPSCR ON DT_DIACODE = SCR_DIACODE AND DT_SPECODE = SCR_SPECODE 
               LEFT JOIN  SYSESD ON  ED_SENDCD = DT_SENDCD 
              WHERE LBT_MEDNO='$IDPT' AND LBT_EXECDATE='$sDt' AND LBT_EXECTIME='$sTm'  AND  LBT_PRCOPID='$sUr' AND LBT_CANDATE=' '
                GROUP BY LBT_EXECDATE,LBT_EXECTIME,LBT_PRCOPID,MH_NAME,MH_MEDNO,MH_SEX,CA_BEDNO,DT_INPSEQ,DT_LOOKDT,ITW_BARCODE,DT_SEQ,DT_PROCDATE,DT_PROCTIME";

    $stid=oci_parse($conn,$sSQL);
    oci_execute($stid);
    while (oci_fetch_array($stid)){
        $LBT_EXECDATE=oci_result($stid,"LBT_EXECDATE");//
        $LBT_EXECTIME=oci_result($stid,"LBT_EXECTIME");//
        $LBT_PRCOPID=oci_result($stid,"LBT_PRCOPID");//
        $NAME=oci_result($stid,"NAME");//姓名
        $MEDNO=oci_result($stid,"MEDNO");//病歷號
        $SEX=oci_result($stid,"SEX");//性別
        $BEDNO=oci_result($stid,"BEDNO");//床位號
        $INPSEQ=oci_result($stid,"INPSEQ");//住院序號
        $LOOKDT=oci_result($stid,"LOOKDT");//診療日期
        $EGNAME=oci_result($stid,"EGNAME");//藥品英文名稱(商品名)
        $BARCODE=oci_result($stid,"BARCODE");//採血編號
        $CONNAME=oci_result($stid,"CONNAME");
        $SPENAME=oci_result($stid,"SPENAME");
        $DIACODE=oci_result($stid,"DIACODE");//診療代碼
        $MACHINENO=oci_result($stid,"MACHINENO");//申請序號(檢驗查)
        $SENDCD=oci_result($stid,"SENDCD");//傳送碼
        $ORDERSEQ=oci_result($stid,"ORDERSEQ");//序號
        $ORDERNO=oci_result($stid,"ORDERNO");//資料序號
        $SORTNUM=oci_result($stid,"SORT_NUM");//檢驗類別
        $WORKNO=oci_result($stid,"WORKNO");//申請序號
        $HISORDKEY=oci_result($stid,"ID_HISORDKEY");
        $Arr[]=array("EXECDATE"=>$LBT_EXECDATE,"EXECTIME"=>$LBT_EXECTIME,"PRCOPID"=>$LBT_PRCOPID,"NAME"=>$NAME,"MEDNO"=>$MEDNO,"SEX"=>$SEX,
            "BEDNO"=>$BEDNO,"INPSEQ"=>$INPSEQ,"LOOKDT"=>$LOOKDT,"EGNAME"=>$EGNAME,"BARCODE"=>$BARCODE,
            "CONNAME"=>explode(",",$CONNAME)[0],"SPENAME"=>explode(",",$SPENAME)[0],
            "DIACODE"=>$DIACODE,"MACHINENO"=>$MACHINENO,"SENDCD"=>$SENDCD,"ORDERSEQ"=>$ORDERSEQ,"
            ORDERNO"=>$ORDERNO,"SORTNUM"=>$SORTNUM,"WORKNO"=>$WORKNO,"HISORDKEY"=>$HISORDKEY);
    }
    array_unshift($Arr,'{"sSave":"'.$ID_COMFIRM.'","sTraID":"'.$TransKey.'"}');
    $ST_DATAA=json_encode($Arr,JSON_UNESCAPED_UNICODE);

    $stm=oci_parse($conn,"
            INSERT INTO HIS803.NISWSTP(
                    ID_TABFORM,ID_TRANSACTION,ID_PATIENT,ID_INPATIENT,
                    DT_EXCUTE,TM_EXCUTE,ST_DATAA,ST_DATAB,ST_DATAC,ST_DATAD,ST_PREA,ST_PREB,
                    ST_PREC,ID_BED,DM_PROCESS,UR_PROCESS,JID_NSRANK,FORMSEQANCE_WT)
             VALUES ('CNCD','$TransKey','$IDPT','$INPt',
             ' ',' ',EMPTY_CLOB(),' ',' ',' ',' ',' ',
             ' ',' ','$DM_PR','$sUr','$JID_NSRANK','$FORMSEQANCE_WT')
             RETURNING ST_DATAA INTO :ST_DATAA");
    $lob=oci_new_descriptor($conn,OCI_D_LOB);
    oci_bind_by_name($stm,':ST_DATAA',$lob,-1,OCI_B_CLOB);

    if ($stm) {
        $result =  oci_execute($stm,OCI_NO_AUTO_COMMIT);
        if(!$result){
            $e=oci_error($stm);
            print htmlentities($e['message']);
            print "\n<pre>\n";
            print htmlentities($e['sqltext']);
            printf("\n%".($e['offset']+1)."s", "^");
            print  "\n</pre>\n";
        }
        $lob->save($ST_DATAA);
        oci_commit($conn);
    } else {
        oci_rollback($conn);
    }
    return $ST_DATAA;
}
function PosCNCDCancel($conn,$sTraID,$sUr){
    $DateTime = date("YmdHis");
    $STR = substr($DateTime, 0, 4);
    $STR1 = substr($DateTime, -10, 10);
    $str = $STR - 1911;
    $dm_cand = $str . $STR1;
    $CANDATE=substr($dm_cand,0,7);
    $CANTIME=substr($dm_cand,7,4);

    $sSQL="SELECT ID_PATIENT,ST_DATAA FROM HIS803.NISWSTP WHERE ID_TABFORM='CNCD'AND ID_TRANSACTION='$sTraID'";

    $sid=oci_parse($conn,$sSQL);
    oci_execute($sid);
    $ST_DATAA='';
    $IDPT='';
    while (oci_fetch_array($sid)){
        $ST_DATAA=oci_result($sid,"ST_DATAA")->load();
        $IDPT=oci_result($sid,"ID_PATIENT");
    }
    $DATAA=json_decode($ST_DATAA);
    array_shift($DATAA);
    for($i=0;$i<count($DATAA);$i++) {
        $EXECDATE=$DATAA[$i]->{'EXECDATE'};
        $EXECTIME=$DATAA[$i]->{'EXECTIME'};
        $PRCOPID=$DATAA[$i]->{'PRCOPID'};


        $UPDATE_SQL="UPDATE TOPLBT  SET 
                      LBT_CANDATE='$CANDATE' ,
                      LBT_CANTIME='$CANTIME',
                      LBT_CANOPID='$sUr'
                      WHERE 
                    LBT_EXECDATE='$EXECDATE' and LBT_EXECTIME='$EXECTIME' AND  LBT_PRCOPID='$PRCOPID'";
        $sid1=oci_parse($conn,$UPDATE_SQL);
        $r_execute=oci_execute($sid1);
        if(!$r_execute){
            ocirollback($conn);
            $e=oci_error($conn);
            $response=json_encode(array("response" => "false","message" =>"作廢錯誤訊息:".$e['message']),JSON_UNESCAPED_UNICODE);
        }else{
           if(PosCNCDCancelNSMARS($conn,$IDPT,$sUr,$CANDATE,$EXECDATE,$EXECTIME,$PRCOPID)!==true){
               ocirollback($conn);
               $response=json_encode(array("response" => "false","message" =>"作廢錯誤訊息:"),JSON_UNESCAPED_UNICODE);
               break;
           }
            $response=json_encode(array("response" => "success"),JSON_UNESCAPED_UNICODE);
        }
    }
    oci_commit($conn);
    return $response;
}
function GetCNCDChangeBed($conn,$sUr,$IDPT){
    $sql="SELECT   ID_INPATIENT, ID_STATION, ID_BED,ID_PATIENT, NM_PATIENT, PAT.ID_NUMBER,
                     CASE PAT.JID_SEX WHEN '1' THEN '男' WHEN '2' THEN '女' ELSE '不分' END AS JID_SEX,
                                            PAT.DT_BIRTHDATE, DT_INPATIENT, DT_OUTPATIENT, 
                                            HIS803.ageymd(HIS803.NIS_DT_PROCESS, '', PAT.DT_BIRTHDATE) AS ST_AGE, 
                                            UR_DOCTORVS, NM_USER AS NM_DOCTORVS
                                    FROM HIS803.NIS_V_HIPT_Q0 PAT, his803.NIS_V_HUSR_Q0
                                    WHERE ID_BED IN (SELECT ID_BED
                                                        FROM HIS803.NIS_V_WKBD_Q0
                                                        WHERE ID_USER = '$sUr')
                                        AND (   DT_OUTPATIENT = ' '
                                         OR (DT_OUTPATIENT <> ' ' AND DT_OUTPATIENT >='1090929') )
                                        AND UR_DOCTORVS = ID_USER AND ID_PATIENT='$IDPT'
                                         ORDER BY ID_BED";


    $stid=oci_parse($conn,$sql);
    oci_execute($stid);
    $Arr=[];
    while (oci_fetch_array($stid)){
        $ID_INPATIENT=oci_result($stid,"ID_INPATIENT");
        $ID_PATIENT=oci_result($stid,"ID_PATIENT");

        $ID_STATION=oci_result($stid,"ID_STATION");
        $ID_BED=oci_result($stid,"ID_BED");
        $NM_PATIENT=oci_result($stid,"NM_PATIENT");
        $ID_NUMBER=oci_result($stid,"ID_NUMBER");
        $JID_SEX=oci_result($stid,"JID_SEX");
        $DT_BIRTHDATE=oci_result($stid,"DT_BIRTHDATE");
        $DT_INPATIENT=oci_result($stid,"DT_INPATIENT");
        $DT_OUTPATIENT=oci_result($stid,"DT_OUTPATIENT");
        $ST_AGE=oci_result($stid,"ST_AGE");
        $NM_DOCTORVS=oci_result($stid,"NM_DOCTORVS");
        $Arr[]=array("DataTxt"=>$ID_BED.":".$NM_PATIENT.":".$ID_NUMBER.":".$JID_SEX,"IDINPT"=>$ID_INPATIENT,"SBED"=>$ID_BED,"IDPT"=>$ID_PATIENT);

    }
    return json_encode($Arr,JSON_UNESCAPED_UNICODE);
}
function GetCNCDCheck($json){
    $JsonB=json_decode($json);
    $response="true";
    if(count($JsonB)==0){
        $response="false";
    }
    return $response;
}
function PosCNCDSaveNSMARS($conn,$IDPT,$IDINPT,$ORDERKEY,$DT_NOW,$TM_NOW,$BED,$JID,$FSEQ,$PROCESS,$sUr){
    $ORKEY=str_replace("#","@",$ORDERKEY);
    $sql="INSERT INTO NSMARS
        (DATESEQANCE,ID_PATIENT,ID_INPATIENT,NO_OPDSEQ,CID_MAR,ID_HISORDKEY,DT_EXCUTE,
        TM_EXCUTE,ID_ORDER,JID_REFUSERSN,JID_DELAYRSN,DT_TAKEDRUG,TM_TAKEDRUG,MM_MAR,ID_BED,JID_NSRANK,
        FORMSEQANCE_WT,FORMSEQANCE_FM,DM_PROCESS,UR_PROCESS,DM_CANCD,UR_CANCD,CID_EXCUTE,ID_FROMSYS)
        VALUES
        (his803.NIS_DATETIMESEQ,'$IDPT','$IDINPT',0,'EM','$ORKEY','$DT_NOW',
        '$TM_NOW',' ',' ',' ','$DT_NOW','$TM_NOW',' ','$BED','$JID',
        '$FSEQ',' ','$PROCESS','$sUr',' ',' ',' ','RWD')";
    $responce=true;
    $stid=oci_parse($conn,$sql);
    if (!$stid){
        $e=oci_error($conn);
        echo $e['message'];
        return false;
    }

    $result=oci_execute($stid,OCI_NO_AUTO_COMMIT);
    if(!$result){
        oci_rollback($conn);
        $result=oci_error($stid);
        echo $result['message'];
        return false;
    }
    oci_commit($conn);
    return $responce;

}
function PosCNCDCancelNSMARS($conn,$IDPT,$sUr,$CAN_DATE,$DT_EXE,$TM_EXE,$PRCOPID)
{
    $Sql="UPDATE NSMARS SET DM_CANCD='$CAN_DATE',UR_CANCD='$sUr' 
            where 
            ID_PATIENT='$IDPT' AND DT_EXCUTE='$DT_EXE' AND
             TM_EXCUTE='$TM_EXE' AND UR_PROCESS='$PRCOPID'
             AND DM_CANCD=' '";

    $stid=oci_parse($conn,$Sql);
    $r=oci_execute($stid,OCI_NO_AUTO_COMMIT);
    if (!$r){
        ocirollback($conn);
        $e=oci_error($conn);
         echo $e['message'];
        return false;
    }else{
        oci_commit($conn);
        return true;
    }
}