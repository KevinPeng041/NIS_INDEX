<?php
date_default_timezone_set('Asia/Taipei');
function  GetCNADPatient($conn,$TransKey,$ID_COMFIRM,$date,$sUr,$JID_NSRANK,$FORMSEQANCE_WT){
    //取輸血紀錄單 FOR NISPRWCBED
      $SQL="SELECT  BSK_ALLOWDATE, BSK_ALLOWTIME,MH_MEDNO,MH_NAME ,BSK_INDENTNO , BSK_TRANSRECNO
         FROM TBOSTK, TREMED
        WHERE BSK_CANCD = 'N' AND BSK_OUT = 'Y' AND BSK_ALLOWDATE <> ' '  AND BSK_INDENTNO <> ' '
        AND BSK_MEDNO = MH_MEDNO AND BSK_ALLOWDATE BETWEEN '1090411' AND '1090414'
          GROUP BY BSK_ALLOWDATE, BSK_ALLOWTIME,MH_MEDNO,MH_NAME,BSK_INDENTNO, BSK_TRANSRECNO";
    $stid=oci_parse($conn,$SQL);
    oci_execute($stid);
    $arr=[];
    while (oci_fetch_array($stid)){
        $BSK_ALLOWDATE=oci_result($stid,'BSK_ALLOWDATE');
        $BSK_ALLOWTIME=oci_result($stid,'BSK_ALLOWTIME');
        $MH_MEDNO=oci_result($stid,'MH_MEDNO');
        $MH_NAME=oci_result($stid,'MH_NAME');
        $BSK_INDENTNO=oci_result($stid,'BSK_INDENTNO');
        $BSK_TRANSRECNO=oci_result($stid,'BSK_TRANSRECNO');
        $arr[]=array("BSK_ALLOWDATE"=>$BSK_ALLOWDATE,"BSK_ALLOWTIME"=>$BSK_ALLOWTIME,"MH_MEDNO"=>$MH_MEDNO,
            "MH_NAME"=>$MH_NAME, "BSK_INDENTNO"=>$BSK_INDENTNO,"BSK_TRANSRECNO"=>$BSK_TRANSRECNO,'sTraID'=>$TransKey,'sSave'=>$ID_COMFIRM);
    }
    $json=json_encode($arr,JSON_UNESCAPED_UNICODE);
    $Insert_sql="INSERT INTO HIS803.NISWSTP(
                    ID_TABFORM,ID_TRANSACTION,ID_PATIENT,ID_INPATIENT,
                    DT_EXCUTE,TM_EXCUTE,ST_DATAA,ST_DATAB,ST_DATAC,ST_DATAD,ST_PREA,ST_PREB,
                    ST_PREC,ID_BED,DM_PROCESS,UR_PROCESS,JID_NSRANK,FORMSEQANCE_WT)
             VALUES ('CNAD','$TransKey',' ',' ',
             ' ',' ','$json',' ',' ',' ',' ',' ',
             ' ',' ','$date','$sUr','$JID_NSRANK','$FORMSEQANCE_WT')";

    $stid2=oci_parse($conn,$Insert_sql);
    $r=oci_execute($stid2,OCI_NO_AUTO_COMMIT);
    if(!$r){
        oci_rollback($conn);
        $e=oci_error($stid2);
         return $e['message'];
    }else{
        oci_commit($conn);
        return $json;
    }
}
function GetCNADIniJson ($conn,$TransKey,$ID_COMFIRM,$date,$sUr,$JID_NSRANK,$FORMSEQANCE_WT){
//Default TableList 取表單預設 for NISPRWCNAD
    $SQL="SELECT BCK_DATMSEQ,BSK_BAGENO,BSK_MEDNO,MH_NAME,BKD_EGCODE,BSK_TRANSRECNO
            FROM TBOSTK,TBOKID,TREMED,TBOBCK
            WHERE BSK_ALLOWDATE BETWEEN '1081126' AND '1090414'
             AND TBOSTK.BSK_MEDNO=TREMED.MH_MEDNO
             AND BSK_BLDKIND=BKD_BLDKIND
             AND BSK_BAGENO=BCK_BAGENO
            AND  BCK_OUTDATE=' '
             AND  BSK_CANCD='N' AND BCK_TRADATE <> ' '  AND BCK_CANDATE=' '
             group by BCK_DATMSEQ,BSK_BAGENO,BSK_MEDNO,MH_NAME,BKD_EGCODE,BSK_TRANSRECNO";

    $stid=oci_parse($conn,$SQL);
    oci_execute($stid);
    $arr=[];
    while (oci_fetch_array($stid)){
        $BCK_DATMSEQ=oci_result($stid,'BCK_DATMSEQ');
        $BSK_BAGENO=oci_result($stid,'BSK_BAGENO');
        $BSK_MEDNO=oci_result($stid,'BSK_MEDNO');
        $MH_NAME=oci_result($stid,'MH_NAME');
        $BKD_EGCODE=oci_result($stid,'BKD_EGCODE');
        $BSK_TRANSRECNO=oci_result($stid,'BSK_TRANSRECNO');
        $arr[]=array("BCK_DATMSEQ"=>$BCK_DATMSEQ,"BSK_BAGENO"=>$BSK_BAGENO,"BSK_MEDNO"=>$BSK_MEDNO,"MH_NAME"=>$MH_NAME,
            "BKD_EGCODE"=>$BKD_EGCODE,"BSK_TRANSRECNO"=>$BSK_TRANSRECNO,'sTraID'=>$TransKey,'sSave'=>$ID_COMFIRM);
    }
    $json=json_encode($arr,JSON_UNESCAPED_UNICODE);

    $Insert_sql="INSERT INTO HIS803.NISWSTP(
                    ID_TABFORM,ID_TRANSACTION,ID_PATIENT,ID_INPATIENT,
                    DT_EXCUTE,TM_EXCUTE,ST_DATAA,ST_DATAB,ST_DATAC,ST_DATAD,ST_PREA,ST_PREB,
                    ST_PREC,ID_BED,DM_PROCESS,UR_PROCESS,JID_NSRANK,FORMSEQANCE_WT)
             VALUES ('CNAD','$TransKey',' ',' ',
             ' ',' ','$json',' ',' ',' ',' ',' ',
             ' ',' ','$date','$sUr','$JID_NSRANK','$FORMSEQANCE_WT')";

    $stid5=oci_parse($conn,$Insert_sql);
    $r=oci_execute($stid5,OCI_NO_AUTO_COMMIT);
    if(!$r){
        oci_rollback($conn);
        $e=oci_error($stid5);
        return $e['message'];
    }else{
        oci_commit($conn);
        return $json;
    }


}
function GetCNADPageJson($conn,$BSK_TRANSRECNO,$sTraID){
    $SQL="SELECT BCK_DATMSEQ,BSK_BAGENO,BSK_MEDNO,MH_NAME,BKD_EGCODE,BSK_TRANSRECNO
            FROM TBOSTK,TBOKID,TREMED,TBOBCK
            WHERE BSK_ALLOWDATE BETWEEN '1081126' AND '1090414'
            AND BSK_TRANSRECNO ='$BSK_TRANSRECNO'
             AND TBOSTK.BSK_MEDNO=TREMED.MH_MEDNO
             AND BSK_BLDKIND=BKD_BLDKIND
             AND BSK_BAGENO=BCK_BAGENO
            AND  BCK_OUTDATE=' '
             AND  BSK_CANCD='N' AND BCK_TRADATE <> ' '  AND BCK_CANDATE=' '
             group by BCK_DATMSEQ,BSK_BAGENO,BSK_MEDNO,MH_NAME,BKD_EGCODE,BSK_TRANSRECNO";

    $stid=oci_parse($conn,$SQL);
    oci_execute($stid);
    $arr=[];
    while (oci_fetch_array($stid)){
        $BCK_DATMSEQ=oci_result($stid,'BCK_DATMSEQ');
        $BSK_BAGENO=oci_result($stid,'BSK_BAGENO');
        $BSK_MEDNO=oci_result($stid,'BSK_MEDNO');
        $MH_NAME=oci_result($stid,'MH_NAME');
        $BKD_EGCODE=oci_result($stid,'BKD_EGCODE');
        $BSK_TRANSRECNO=oci_result($stid,'BSK_TRANSRECNO');
        $arr[]=array("BCK_DATMSEQ"=>$BCK_DATMSEQ,"BSK_BAGENO"=>$BSK_BAGENO,"BSK_MEDNO"=>$BSK_MEDNO,"MH_NAME"=>$MH_NAME,
                     "BKD_EGCODE"=>$BKD_EGCODE,"BSK_TRANSRECNO"=>$BSK_TRANSRECNO);
    }
    $json=json_encode($arr,JSON_UNESCAPED_UNICODE);
    $Update_sql=" UPDATE HIS803.NISWSTP SET ST_DATAA='$json' where ID_TABFORM='CNAD' AND ID_TRANSACTION='$sTraID'";

    $stid5=oci_parse($conn,$Update_sql);
    $r=oci_execute($stid5,OCI_NO_AUTO_COMMIT);
    if(!$r){
        oci_rollback($conn);
        $e=oci_error($stid5);
        return $e['message'];
    }else{
        oci_commit($conn);
        return $json;
    }
}
function PosCNADSave($conn,$sTraID,$sDt,$sTm,$sUr){
    $Ssql="SELECT ST_DATAB,DT_EXCUTE,TM_EXCUTE from HIS803.NISWSTP
        WHERE ID_TABFORM = 'CNAD'  AND ID_TRANSACTION = '$sTraID'";

    $stid=oci_parse($conn,$Ssql);
    oci_execute($stid);
    $ST_DATAB='';
    $DT_EXCUTE='';
    $TM_EXCUTE='';
    while (oci_fetch_array($stid)){
        $ST_DATAB=oci_result($stid,"ST_DATAB")->read(2000);
        $DT_EXCUTE=oci_result($stid,"DT_EXCUTE");
        $TM_EXCUTE=oci_result($stid,"TM_EXCUTE");
    }
    $response='';
    if(trim($DT_EXCUTE)=="" && trim($TM_EXCUTE)==""){
        if($ST_DATAB){
            $B=json_decode($ST_DATAB);

            if(GetCNADCheck($ST_DATAB)=="false"){
                return    $response=json_encode(array("response" => "false","message" =>"發血存檔錯誤訊息:血袋尚未勾選"),JSON_UNESCAPED_UNICODE);

            }

            for ($i=0;$i<count($B);$i++)
            {
                $BSK_TRANSRECNO=$B[$i]->{"BSK_TRANSRECNO"};
                $BCK_DATMSEQ=$B[$i]->{"BCK_DATMSEQ"};
                $BSK_MEDNO=$B[$i]->{"BSK_MEDNO"};
                $BSK_BAGENO=$B[$i]->{"BSK_BAGENO"};

                $UPDATESQL="UPDATE  TBOBCK SET   BCK_OUTDATE='$sDt',BCK_OUTTIME='$sTm',BCK_OUTOPID='$sUr' 
                            WHERE BCK_DATMSEQ='$BCK_DATMSEQ' AND BCK_MEDNO='$BSK_MEDNO' AND BCK_BAGENO='$BSK_BAGENO'";

                $stid=oci_parse($conn,$UPDATESQL);
                if (!$stid){
                    $e=oci_error($conn);
                    $response=json_encode(array("response" => "false","message" =>"發血存檔錯誤訊息:".$e['message']),JSON_UNESCAPED_UNICODE);
                    return $response;
                }
                $Bex=oci_execute($stid,OCI_NO_AUTO_COMMIT);
                if(!$Bex)
                {
                    oci_rollback($conn);
                    $e=oci_error($stid);
                    $response=json_encode(array("response" => "false","message" =>"發血存檔錯誤訊息:".$e['message']),JSON_UNESCAPED_UNICODE);
                    return $response;
                }
                else{
                    $r=oci_commit($conn);
                    if(!$r){
                        $e=oci_error($conn);
                        $response=json_encode(array("response" => "false","message" =>"發血存檔錯誤訊息:".$e['message']),JSON_UNESCAPED_UNICODE);
                        return $response;
                    }
                    $response=json_encode(array("response" => "success","message" =>"this is the success message"),JSON_UNESCAPED_UNICODE);

                }
            }
        }
    }
    return $response;
}
function GetCNADJson($conn,$IDPT,$INPt,$sUr,$sDt,$sTm,$sPg,$sDFL){
    //產生已儲存之紀錄資料
    $sql="SELECT (SELECT his803.nis_datetimeseq FROM DUAL) ID_TRANSB,
            his803.GetWSTPNEXTVAL ID_TRANSA, 
             CR.CA_BEDNO ID_BED, WM.formseqance_wt FORMSEQANCE_WT,
            (SELECT Max(CI.id_item) FROM HIS803.NSUSER UR, HIS803.NSCLSI CI
            WHERE  UR.jid_nsrank <> ' '
            AND UR.jid_nsrank = CI.jid_key AND CI.cid_class='RANK') JID_NSRANK,
            (SELECT PU.is_confirm FROM HIS803.NSPROU PU
            WHERE  PU.id_user  =  WM.id_user AND PU.id_program = 'NISCISLN') ID_COMFIRM   
            FROM HIS803.NSWKBD WD, HIS803.NSWKTM WM, HIS803.INACAR CR
            WHERE  CR.CA_MEDNO = '00055664' AND CR.CA_INPSEQ = '970000884'
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


    $sSQL="SELECT  BCK_DATMSEQ,BSK_BAGENO,BSK_BLDKIND,BSK_INDENTNO,BCK_OUTDATE,BCK_OUTTIME,
                    BCK_OUTOPID,BSK_MEDNO,MH_NAME,BKD_EGCODE,BSK_TRANSRECNO
            FROM  TBOSTK,TBOKID,TREMED,TBOBCK
            WHERE  TBOSTK.BSK_MEDNO=TREMED.MH_MEDNO
            AND BSK_BLDKIND=BKD_BLDKIND
            AND BSK_BAGENO=BCK_BAGENO
            AND BCK_OUTDATE='$sDt' AND BCK_OUTTIME='$sTm'
            AND BCK_OUTOPID='$sUr'
            AND  BSK_CANCD='N' AND BCK_TRADATE <> ' '  AND BCK_CANDATE=' '
            GROUP BY  BCK_DATMSEQ,BSK_BAGENO,BSK_BLDKIND,BSK_INDENTNO,BCK_OUTDATE,BCK_OUTTIME,
                        BCK_OUTOPID,BSK_MEDNO,MH_NAME,BKD_EGCODE,BSK_TRANSRECNO";

    $stid=oci_parse($conn,$sSQL);
    oci_execute($stid);

    while(oci_fetch_array($stid))
    {
        $BCK_DATMSEQ=oci_result($stid,'BCK_DATMSEQ');
        $BSK_BAGENO=oci_result($stid,'BSK_BAGENO');

         $BSK_BLDKIND=oci_result($stid,'BSK_BLDKIND');
         $BSK_INDENTNO=oci_result($stid,'BSK_INDENTNO');

        $BCK_OUTDATE=oci_result($stid,'BCK_OUTDATE');
        $BCK_OUTTIME=oci_result($stid,'BCK_OUTTIME');
        $BCK_OUTOPID=oci_result($stid,'BCK_OUTOPID');

        $BSK_MEDNO=oci_result($stid,'BSK_MEDNO');
        $MH_NAME=oci_result($stid,'MH_NAME');
        $BKD_EGCODE=oci_result($stid,'BKD_EGCODE');
        $BSK_TRANSRECNO=oci_result($stid,'BSK_TRANSRECNO');


        $Arr[]=array("BCK_DATMSEQ"=>$BCK_DATMSEQ,"BSK_BAGENO"=>$BSK_BAGENO,"BSK_BLDKIND"=>$BSK_BLDKIND,"BSK_INDENTNO"=>$BSK_INDENTNO,
                    "BCK_OUTDATE"=>$BCK_OUTDATE,"BCK_OUTTIME"=>$BCK_OUTTIME,"BCK_OUTOPID"=>$BCK_OUTOPID,"BSK_MEDNO"=>$BSK_MEDNO,
                    "MH_NAME"=>$MH_NAME,"BKD_EGCODE"=>$BKD_EGCODE,"BSK_TRANSRECNO"=>$BSK_TRANSRECNO);
    }

    $JSON=json_encode($Arr,JSON_UNESCAPED_UNICODE);
    $CallBackJson=str_replace('}',',"sTraID":"'.$TransKey.'","sSave":"'.$ID_COMFIRM.'"}', $JSON);

    $sql="SELECT WI.ST_DATAA FROM HIS803.NISWSIT WI
            WHERE WI.ID_TABFORM = 'CNAD'";
    $stid1=oci_parse($conn,$sql);
    oci_execute($stid1);
    $ST_DATAA='';


    while ($row=oci_fetch_array($stid1)){
        $ST_DATAA=$row[0]->load();
    }
    $Insert_sql="INSERT INTO HIS803.NISWSTP(
                    ID_TABFORM,ID_TRANSACTION,ID_PATIENT,ID_INPATIENT,
                    DT_EXCUTE,TM_EXCUTE,ST_DATAA,ST_DATAB,ST_DATAC,ST_DATAD,ST_PREA,ST_PREB,
                    ST_PREC,ID_BED,DM_PROCESS,UR_PROCESS,JID_NSRANK,FORMSEQANCE_WT)
             VALUES ('CNAD','$TransKey',' ',' ',
             '$sDt','$sTm','$ST_DATAA','$CallBackJson',' ',' ',' ',' ',
             ' ',' ','$DM_PR','$sUr','$JID_NSRANK','$FORMSEQANCE_WT')";

    $TP_stid=oci_parse($conn,$Insert_sql);
    $TP_r=oci_execute($TP_stid,OCI_NO_AUTO_COMMIT);
    if(!$TP_r){
        oci_rollback($conn);
        $e=oci_error($TP_stid);
        $json=json_encode(array("message"=>$e['message']));
        echo $json;
        return false;
    }else{
        $comm=oci_commit($conn);
        if(!$comm){
            $e=oci_error($conn);
            $json=json_encode(array("message"=>$e['message']));
            echo $json;
            return false;
        }
    }
    return $CallBackJson;
}
function PosCNADCancel($conn,$sTraID,$sUr){
    //作廢領用血簽收紀錄
    $DateTime = date("YmdHis");
    $STR = substr($DateTime, 0, 4);
    $STR1 = substr($DateTime, -10, 10);
    $str = $STR - 1911;
    $dm_cand = $str . $STR1;
    $CANDATE=substr($dm_cand,0,7);
    $CANTIME=substr($dm_cand,7,4)."00";
    $BTG_CANDATETIME=$CANDATE.$CANTIME;
    $sSQL="SELECT ST_DATAB FROM HIS803.NISWSTP WHERE ID_TABFORM='CNAD'AND ID_TRANSACTION='$sTraID'";

    $sid=oci_parse($conn,$sSQL);
    oci_execute($sid);
    $ST_DATAB='';
    while (oci_fetch_array($sid)){
        $ST_DATAB=oci_result($sid,"ST_DATAB")->load();
    }
    $DATAB=json_decode($ST_DATAB);
    $response='';

    for($i=0;$i<count($DATAB);$i++) {
        $BCK_DATMSEQ = $DATAB[$i]->{'BCK_DATMSEQ'};
        $BSK_BAGENO = $DATAB[$i]->{'BSK_BAGENO'};
        $BSK_BLDKIND = $DATAB[$i]->{'BSK_BLDKIND'};
        $BSK_INDENTNO= $DATAB[$i]->{'BSK_INDENTNO'};
        $BCK_OUTDATE = $DATAB[$i]->{'BCK_OUTDATE'};
        $BCK_OUTTIME = $DATAB[$i]->{'BCK_OUTTIME'};
        $BCK_OUTOPID=$DATAB[$i]->{'BCK_OUTOPID'};
        $BSK_MEDNO = $DATAB[$i]->{'BSK_MEDNO'};
        $MH_NAME = $DATAB[$i]->{'MH_NAME'};
        $BKD_EGCODE = $DATAB[$i]->{'BKD_EGCODE'};
        $BSK_TRANSRECNO = $DATAB[$i]->{'BSK_TRANSRECNO'};

        $UPDATE_SQL="UPDATE TBOBCK  SET 
                    BCK_OUTDATE=' ',
                     BCK_OUTTIME=' ',       
                     BCK_OUTOPID=' '
                      WHERE 
                      BCK_DATMSEQ='$BCK_DATMSEQ' AND BCK_MEDNO='$BSK_MEDNO' 
                      AND BCK_OUTDATE='$BCK_OUTDATE' AND BCK_OUTTIME='$BCK_OUTTIME'";
        $sid1=oci_parse($conn,$UPDATE_SQL);
        $r_execute=oci_execute($sid1);

        if(!$r_execute){
            ocirollback($conn);
            $e=oci_error($sid1);
            $response=json_encode(array("response" => "false","message" =>"作廢錯誤訊息:".$e['message']),JSON_UNESCAPED_UNICODE);
        }else{
            $INSERT_SQL="INSERT INTO TBOBTG
            (BTG_BAGENO,BTG_BLDKIND,BTG_NURSDATE,BTG_NURSTIME,BTG_NURSOPID,BTG_INDENTNO,BTG_TRANSRECNO,BTG_BARSIGN,BTG_CANCODE,BTG_CANDATETIME,BTG_CANOPID)
            VALUES
            ('$BSK_BAGENO','$BSK_BLDKIND','$BCK_OUTDATE','$BCK_OUTTIME','$BCK_OUTOPID','$BSK_INDENTNO','$BSK_TRANSRECNO',' ',' ','$BTG_CANDATETIME','$sUr')";
            $sid2=oci_parse($conn,$INSERT_SQL);

            $r_execute=oci_execute($sid2);
            if(!$r_execute){
                ocirollback($conn);
                $e=oci_error($sid2);

                $response=json_encode(array("response" => "false","message" =>"存檔錯誤訊息:".$e['message']),JSON_UNESCAPED_UNICODE);
            }else{
                $response=json_encode(array("response" => "success"),JSON_UNESCAPED_UNICODE);
            }
        }
    }
    oci_commit($conn);
    return $response;
}
function GetCNADCheck($json){
    $JsonB=json_decode($json);
    $response="true";
    if(count($JsonB)==0){
        $response="false";
    }
    return $response;
}