<?php

date_default_timezone_set('Asia/Taipei');
function GetCNBDIniJson($conn,$TransKey,$ID_COMFIRM,$date,$sUr,$JID_NSRANK,$FORMSEQANCE_WT){
    //取護理站初始清單
/*
    $sql = "select ST_DATAA from HIS803.NISWSIT WHERE ID_TABFORM = 'CNBD'";

    $stid=oci_parse($conn,$sql);
    oci_execute($stid);
    $ST_DATAA = '';

    while (oci_fetch_array($stid)){
        $ST_DATAA=oci_result($stid,"ST_DATAA")->read(2000);
    }*/
    //[{"BUT_BUTNAME":"","BUT_NEEDUNIT":"","BUT_PROCDATE":"","BUT_PROCOPID":"","BUT_PROCTIME":"","sTraID":"","sSave":""}]

    $sql="SELECT * FROM TBOUNT";
    $stid=oci_parse($conn,$sql);
    oci_execute($stid);
    $arr=[];
    while(oci_fetch_array($stid)){
        $BUT_NEEDUNIT=oci_result($stid,'BUT_NEEDUNIT');
        $BUT_BUTNAME=oci_result($stid,'BUT_BUTNAME');
        $BUT_PROCDATE=oci_result($stid,'BUT_PROCDATE');
        $BUT_PROCTIME=oci_result($stid,'BUT_PROCTIME');
        $BUT_PROCOPID=oci_result($stid,'BUT_PROCOPID');

        $arr[]=array("BUT_NEEDUNIT"=>$BUT_NEEDUNIT,"BUT_BUTNAME"=>$BUT_BUTNAME,"BUT_PROCDATE"=>$BUT_PROCDATE,
        "BUT_PROCTIME"=>$BUT_PROCTIME,"BUT_PROCOPID"=>$BUT_PROCOPID,'sTraID'=>$TransKey,'sSave'=>$ID_COMFIRM);
    }

    $json=json_encode($arr,JSON_UNESCAPED_UNICODE);

    $Insert_sql="INSERT INTO HIS803.NISWSTP(
                    ID_TABFORM,ID_TRANSACTION,ID_PATIENT,ID_INPATIENT,
                    DT_EXCUTE,TM_EXCUTE,ST_DATAA,ST_DATAB,ST_DATAC,ST_DATAD,ST_PREA,ST_PREB,
                    ST_PREC,ID_BED,DM_PROCESS,UR_PROCESS,JID_NSRANK,FORMSEQANCE_WT)
             VALUES ('CNBD','$TransKey',' ',' ',
             ' ',' ','$json',' ',' ',' ',' ',' ',
             ' ',' ','$date','$sUr','$JID_NSRANK',' ')";
    $stid5=oci_parse($conn,$Insert_sql);
    $r=oci_execute($stid5,OCI_NO_AUTO_COMMIT);
    if(!$r){
        oci_rollback($conn);
        $e=oci_error($stid5);
        print htmlentities($e['message']);
        print "\n<pre>\n";
        print htmlentities($e['sqltext']);
        printf("\n%".($e['offset']+1)."s", "^");
        print  "\n</pre>\n";
    }else{
        oci_commit($conn);
        return $json;
    }


}
function GetCNBDPageJson($conn,$BSK_NEEDUNIT){
    //取護理站輸血血袋紀錄清單
    $Sql="SELECT DISTINCT BSK_BAGENO,BSK_MEDNO,MH_NAME,BKD_EGCODE
            FROM TBOSTK,TBOKID,TREMED
            where  BSK_NEEDUNIT ='$BSK_NEEDUNIT' 
            AND TBOSTK.BSK_MEDNO=TREMED.MH_MEDNO
            AND BSK_BLDKIND=BKD_BLDKIND
            AND  BSK_CANCD='N' 
            AND BSK_CANDATE=' 'AND BSK_CANTIME=' '
            AND BSK_OUTDATE <>' ' AND BSK_NURSDATE=' '";
    $stid=oci_parse($conn,$Sql);
    oci_execute($stid);
    $arr=[];
    while (oci_fetch_array($stid)){
        $BSK_BAGENO=oci_result($stid,'BSK_BAGENO');
        $BSK_MEDNO=oci_result($stid,'BSK_MEDNO');
        $MH_NAME=oci_result($stid,'MH_NAME');
        $BKD_EGCODE=oci_result($stid,'BKD_EGCODE');
        $arr[]=array("BSK_BAGENO"=>$BSK_BAGENO,"BSK_MEDNO"=>$BSK_MEDNO,"MH_NAME"=>$MH_NAME ,"BKD_EGCODE"=>$BKD_EGCODE);
    }
    $json=json_encode($arr,JSON_UNESCAPED_UNICODE);
    return $json;
}
function PosCNBDSave($conn,$sTraID,$sDt,$sTm,$sUr){
    //血袋領用簽收資料儲存
    $Ssql="SELECT ST_DATAB,DT_EXCUTE,TM_EXCUTE from HIS803.NISWSTP
        WHERE ID_TABFORM = 'CNBD'  AND ID_TRANSACTION = '$sTraID'";
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
           for ($i=0;$i<count($B);$i++){
               $BSK_BAGENO=$B[$i]->{"BSK_BAGENO"};
               $BSK_MEDNO=$B[$i]->{"BSK_MEDNO"};
               $BUT_NEEDUNIT=$B[$i]->{"BUT_NEEDUNIT"};
               $UPDATESQL="UPDATE TBOSTK  SET 
                            BSK_NURSDATE='$sDt',BSK_NURSTIME='$sTm',
                            BSK_NURSOPID='$sUr',BSK_BARSIGN='Y' WHERE
                            BSK_MEDNO='$BSK_MEDNO' 
                            AND BSK_NEEDUNIT='$BUT_NEEDUNIT'
                            AND BSK_BAGENO='$BSK_BAGENO'
                            AND  BSK_CANCD='N' 
                            AND BSK_OUTDATE <>' ' AND BSK_NURSDATE=' '";



               $Bstid=oci_parse($conn,$UPDATESQL);
               if (!$Bstid){
                   $e=oci_error($conn);
                   $response=json_encode(array("response" => "false","message" =>"領血存檔錯誤訊息:".$e['message']));
               }
               $Bex=oci_execute($Bstid,OCI_NO_AUTO_COMMIT);
               if(!$Bex)
               {
                   oci_rollback($conn);
                   $e=oci_error($Bstid);
                   $response=json_encode(array("response" => "false","message" =>"領血存檔錯誤訊息:".$e['message']));
               }
               else{
                   $r=oci_commit($conn);
                   if(!$r){
                       $e=oci_error($conn);
                       $response=json_encode(array("response" => "false","message" =>"領血存檔錯誤訊息:".$e['message']));

                   }
                   $response=json_encode(array("response" => "success","message" =>"this is the success message"));

               }

           }
       }
   }
    return   $response;
}
function GetCNBDJson($conn,$IDPT,$INPt,$sUr,$sDt,$sTm,$sPg,$sDFL){
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



    $SQL="SELECT BSK_BAGENO,BSK_BLDKIND,BSK_MEDNO,BKD_EGCODE,BSK_NEEDUNIT,MH_NAME,BSK_NURSDATE,BSK_NURSTIME,BSK_NURSOPID,BSK_INDENTNO,BSK_TRANSRECNO,BSK_BARSIGN
            FROM TBOSTK,TBOKID,TREMED where  BSK_NEEDUNIT ='$sPg' 
            AND TBOSTK.BSK_MEDNO=TREMED.MH_MEDNO
            AND BSK_BLDKIND=BKD_BLDKIND
             AND BSK_MEDNO='$IDPT'
            AND  BSK_BARSIGN='Y' 
           AND BSK_NURSDATE='$sDt'AND  BSK_NURSTIME='$sTm'AND BSK_NURSOPID='$sUr'";

    $stid=oci_parse($conn,$SQL);
    oci_execute($stid);
    while(oci_fetch_array($stid)){
        $BSK_BAGENO=oci_result($stid,'BSK_BAGENO');
        $BSK_BLDKIND=oci_result($stid,'BSK_BLDKIND');
        $BSK_MEDNO=oci_result($stid,'BSK_MEDNO');
        $BKD_EGCODE=oci_result($stid,'BKD_EGCODE');
        $BSK_NEEDUNIT=oci_result($stid,'BSK_NEEDUNIT');
        $BSK_NURSDATE=oci_result($stid,'BSK_NURSDATE');
        $BSK_NURSTIME=oci_result($stid,'BSK_NURSTIME');
        $MH_NAME=oci_result($stid,'MH_NAME');
        $BSK_NURSOPID=oci_result($stid,'BSK_NURSOPID');
        $BSK_INDENTNO=oci_result($stid,'BSK_INDENTNO');
        $BSK_TRANSRECNO=oci_result($stid,'BSK_TRANSRECNO');
        $BSK_BARSIGN=oci_result($stid,'BSK_BARSIGN');


        $Arr[]=array("BSK_BAGENO"=>$BSK_BAGENO,"BSK_MEDNO"=>$BSK_MEDNO,"BSK_BLDKIND"=>$BSK_BLDKIND,"BKD_EGCODE"=>$BKD_EGCODE,"BSK_NEEDUNIT"=>$BSK_NEEDUNIT
                    ,"BSK_NURSDATE"=>$BSK_NURSDATE,"MH_NAME"=>$MH_NAME,"BSK_NURSTIME"=>$BSK_NURSTIME,"BSK_NURSOPID"=>$BSK_NURSOPID,"BSK_INDENTNO"=>$BSK_INDENTNO,"BSK_TRANSRECNO"=>$BSK_TRANSRECNO,
                    "BSK_BARSIGN"=>$BSK_BARSIGN);
    }
    $JSON=json_encode($Arr,JSON_UNESCAPED_UNICODE);
    $CallBackJson=str_replace('}',',"sTraID":"'.$TransKey.'","sSave":"'.$ID_COMFIRM.'"}', $JSON);

    $sql="SELECT WI.ST_DATAA FROM HIS803.NISWSIT WI
            WHERE WI.ID_TABFORM = 'CNBD'";
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
             VALUES ('CNBD','$TransKey',' ',' ',
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
function PosCNBDCancel($conn,$sTraID,$sUr){
   //作廢領用血簽收紀錄
    $DateTime = date("YmdHis");
    $STR = substr($DateTime, 0, 4);
    $STR1 = substr($DateTime, -10, 10);
    $str = $STR - 1911;
    $dm_cand = $str . $STR1;
    $CANDATE=substr($dm_cand,0,7);
    $CANTIME=substr($dm_cand,7,4)."00";
    $BTG_CANDATETIME=$CANDATE.$CANTIME;
    $sSQL="SELECT ST_DATAB FROM HIS803.NISWSTP WHERE ID_TABFORM='CNBD'AND ID_TRANSACTION='$sTraID'";

    $sid=oci_parse($conn,$sSQL);
    oci_execute($sid);
    $ST_DATAB='';
    while (oci_fetch_array($sid)){
        $ST_DATAB=oci_result($sid,"ST_DATAB")->load();
    }
    $DATAB=json_decode($ST_DATAB);
    $response='';
    for($i=0;$i<count($DATAB);$i++) {
        //[{"BSK_BAGENO":"0072019559","BSK_MEDNO":"01168420","BSK_BLDKIND":"3042182","BKD_EGCODE":"FFP",
        //"BSK_NEEDUNIT":"ER","BSK_NURSDATE":"1090804","BSK_NURSTIME":"1425","BSK_NURSOPID":"00FUZZY",
        //"BSK_INDENTNO":"09810132109","BSK_TRANSRECNO":"T10111160004","BSK_BARSIGN":"Y","sTraID":"20200806101325878ILSGA00597410","sSave":"Y"}]
        $BSK_BAGENO = $DATAB[$i]->{'BSK_BAGENO'};
        $BSK_MEDNO = $DATAB[$i]->{'BSK_MEDNO'};
        $BSK_BLDKIND =$DATAB[$i]->{'BSK_BLDKIND'};
        $BSK_NEEDUNIT = $DATAB[$i]->{'BSK_NEEDUNIT'};
        $BSK_NURSDATE = $DATAB[$i]->{'BSK_NURSDATE'};
        $BSK_NURSTIME = $DATAB[$i]->{'BSK_NURSTIME'};
        $BSK_NURSOPID=$DATAB[$i]->{'BSK_NURSOPID'};
        $BSK_INDENTNO=$DATAB[$i]->{'BSK_INDENTNO'};
        $BSK_TRANSRECNO=$DATAB[$i]->{'BSK_TRANSRECNO'};
        $BSK_BARSIGN=$DATAB[$i]->{'BSK_BARSIGN'};

        $UPDATE_SQL="UPDATE TBOSTK  SET 
                     BSK_NURSDATE=' ',BSK_NURSTIME=' ',
                     BSK_NURSOPID=' ',BSK_BARSIGN='N'
                    WHERE
                    BSK_MEDNO='$BSK_MEDNO' 
                    AND BSK_NEEDUNIT='$BSK_NEEDUNIT'
                    AND BSK_BAGENO='$BSK_BAGENO'
                    AND  BSK_CANCD='N' 
                    AND BSK_OUTDATE <>' '";
        $sid1=oci_parse($conn,$UPDATE_SQL);
        $r_execute=oci_execute($sid1);

        if(!$r_execute){
            ocirollback($conn);
            $e=oci_error($conn);
            $response=json_encode(array("response" => "false","message" =>"作廢錯誤訊息:".$e['message']),JSON_UNESCAPED_UNICODE);
        }else{
            $INSERT_SQL="INSERT INTO TBOBTG
            (BTG_BAGENO,BTG_BLDKIND,BTG_NURSDATE,BTG_NURSTIME,BTG_NURSOPID,BTG_INDENTNO,BTG_TRANSRECNO,BTG_BARSIGN,BTG_CANCODE,BTG_CANDATETIME,BTG_CANOPID)
            VALUES
            ('$BSK_BAGENO','$BSK_BLDKIND','$BSK_NURSDATE','$BSK_NURSTIME','$BSK_NURSOPID','$BSK_INDENTNO','$BSK_TRANSRECNO','$BSK_BARSIGN',' ','$BTG_CANDATETIME','$sUr')";
            $sid2=oci_parse($conn,$INSERT_SQL);
            $r_execute=oci_execute($sid2);
            if(!$r_execute){
                ocirollback($conn);
                $e=oci_error($conn);
          $response=json_encode(array("response" => "false","message" =>"存檔錯誤訊息:".$e['message']),JSON_UNESCAPED_UNICODE);
            }else{
                $response=json_encode(array("response" => "success"),JSON_UNESCAPED_UNICODE);
            }
        }
    }
    oci_commit($conn);
    return $response;
}
