<?php
date_default_timezone_set('Asia/Taipei');
function GetCBLDIniJson($conn,$Idpt,$sTraID,$sSave,$date,$sUr,$JID_NSRANK,$FORMSEQANCE_WT){

    //取病人輸血紀錄初始清單
    $sql = "SELECT ST_DATAA, ST_DATAB, ST_DATAC FROM HIS803.NISWSIT WHERE ID_TABFORM = 'CBLD'";
    $stid=oci_parse($conn,$sql);
    oci_execute($stid);
    $ST_DATAA = '';
    $ST_DATAB = '';
    $ST_DATAC = '';

    while (oci_fetch_array($stid)){
        $ST_DATAA=oci_result($stid,"ST_DATAA")->read(2000);
        $ST_DATAB=oci_result($stid,"ST_DATAB")->read(2000);
        $ST_DATAC=oci_result($stid,"ST_DATAC")->read(2000);
    }


    $Serch_STDATAA="SELECT  BSK_ALLOWDATE As DT_EXE,
        BSK_ALLOWTIME As TM_EXE,  COUNT(*) as Num, BSK_INDENTNO As A_Indno, BSK_TRANSRECNO As A_trano 
         FROM TBOSTK, TREMED
        WHERE BSK_CANCD = 'N' AND BSK_OUT = 'Y' AND BSK_ALLOWDATE <> ' '  AND BSK_INDENTNO <> ' '
         AND BSK_MEDNO = MH_MEDNO AND BSK_ALLOWDATE BETWEEN '1090409' AND '1091118'
        AND   bsk_medno  ='$Idpt'
          GROUP BY  BSK_ALLOWDATE, BSK_ALLOWTIME, BSK_INDENTNO, BSK_TRANSRECNO
          ORDER BY  BSK_ALLOWDATE || BSK_ALLOWTIME DESC ";

    $stidA=oci_parse($conn,$Serch_STDATAA);
    oci_execute($stidA);

    $INDENTNO='';
    $TRANSRECNO='';
    $arr2=[];
    $arr_DATAA=[];
    while (oci_fetch_array($stidA)){
        $DT_EXECUTE=oci_result($stidA,'DT_EXE');
        $TM_EXECUTE=oci_result($stidA,'TM_EXE');
        $NUM=oci_result($stidA,'NUM');
        $INDENTNO=oci_result($stidA,'A_INDNO');
        $TRANSRECNO=oci_result($stidA,'A_TRANO');
        $arr_DATAA[]=array("DT_EXE"=>$DT_EXECUTE,"TM_EXE"=>$TM_EXECUTE,"NUM"=>$NUM,"A_INDNO"=>$INDENTNO,"A_TRANO"=>$TRANSRECNO);
    }


   $DATAA= json_encode($arr_DATAA,JSON_UNESCAPED_UNICODE);

    $Insert_sql="INSERT INTO HIS803.NISWSTP(
                    ID_TABFORM,ID_TRANSACTION,ID_PATIENT,ID_INPATIENT,
                    DT_EXCUTE,TM_EXCUTE,ST_DATAA,ST_DATAB,ST_DATAC,ST_DATAD,ST_PREA,ST_PREB,
                    ST_PREC,ID_BED,DM_PROCESS,UR_PROCESS,JID_NSRANK,FORMSEQANCE_WT)
             VALUES ('CBLD','$sTraID','$Idpt',' ',
             ' ',' ','$DATAA','$ST_DATAB','$ST_DATAC',' ',' ',' ',
             ' ',' ','$date','$sUr','$JID_NSRANK','$FORMSEQANCE_WT')";


    $stid5=oci_parse($conn,$Insert_sql);
    $r=oci_execute($stid5,OCI_NO_AUTO_COMMIT);
    if(!$r){
        oci_rollback($conn);
        $e=oci_error($stid5);
       return $e['message'];
    }else{
        oci_commit($conn);
        $arr2[]=array('INDENTNO'=>$INDENTNO,'TRANSRECNO'=>$TRANSRECNO,'sTraID'=>$sTraID,'sSave'=>$sSave);

        return json_encode($arr2,JSON_UNESCAPED_UNICODE);
    }
}
function GetCBLDPageJson($conn,$sTraID,$sPg){
    //取病患輸血紀錄清單
    $sql="select ST_DATAA,ST_DATAB,ST_DATAC from HIS803.NISWSTP WHERE ID_TABFORM = 'CBLD' AND ID_TRANSACTION = '$sTraID'";
    $stid=oci_parse($conn,$sql);
    oci_execute($stid);
    while (oci_fetch_array($stid)){
        $ST_DATAA=oci_result($stid,"ST_DATAA")->read(2000);
        $ST_DATAB=oci_result($stid,"ST_DATAB")->read(2000);
        $ST_DATAC=oci_result($stid,"ST_DATAC")->read(2000);

    }

    $obj=json_decode($ST_DATAA)[0];
    $DT_EXE=$obj->DT_EXE;
    $TM_EXE=$obj->TM_EXE;
    $INNO=$obj->A_INDNO;
    $NUM=$obj->NUM;
    $A_TRANO=$obj->A_TRANO;

    switch ($sPg){
        case "A":
            $arr[]=array('ST_DATAA'=>$ST_DATAA);
            break;
        case "B":

            $Serch_STDATAB="SELECT bsk_bageno As B_ID, bkd_easyname As B_Num, bsk_blood As B_Tp,
                            ' ' As B_UR, ' ' As B_CUR,
                            BCK_DATMSEQ AS B_dtseq, bck_bldkind As B_Bkd,  bck_indentno B_Indno
                            FROM tbokid, TBOBCK, tbostk
                            WHERE    bsk_outdate <>' '
                                AND bsk_cancd='N'
                                AND  bsk_indentno = '$INNO'
                                AND BSK_ALLOWDATE = '$DT_EXE' AND BSK_ALLOWTIME = '$TM_EXE'
                                AND bsk_bageno = bck_bageno AND bsk_recno = bck_recno
                                AND  bsk_indentno = bck_indentno  AND bsk_medno = bck_medno
                                AND bkd_bldkind =  bsk_bldkind
                                 AND BCK_CANDATE=' ' AND BCK_CANTIME=' '
                            ORDER BY  SubStr(bsk_bageno, 4, 7)";

            $stidB=oci_parse($conn,$Serch_STDATAB);
            oci_execute($stidB);
            $arrB=[];
            while ($row=oci_fetch_array($stidB)){
                $B_ID=$row['B_ID'];
                $B_Num=$row['B_NUM'];
                $B_Tp=$row['B_TP'];
                $B_UR=$row['B_UR'];
                $B_CUR=$row['B_CUR'];
                $B_DTSEQ=$row['B_DTSEQ'];
                $B_BKD=$row['B_BKD'];
                $INDENTNO=$row['B_INDNO'];
                $arrB[]=array('B_ID'=>$B_ID,'B_NUM'=>$B_Num,'B_TP'=>$B_Tp,'B_UR'=>$B_UR,
                    'B_CUR'=>$B_CUR,'B_DTSEQ'=>$B_DTSEQ,'B_BKD'=>$B_BKD,'B_INDNO'=>$INDENTNO);
            }

            $new_ST_DATAB=json_encode($arrB,JSON_UNESCAPED_UNICODE);
            $arr[]=array('ST_DATAB'=>$new_ST_DATAB);
            break;
        case  "C":
            $Serch_STDATAC="SELECT bsk_bageno As C_ID, bkd_easyname As C_Num, bsk_blood As C_Tp, 
                                ' ' As C_UR, ' ' As C_CUR, 
                                BCK_DATMSEQ AS C_dtseq, bck_bldkind As C_Bkd,  bck_indentno C_Indno 
                                FROM tbokid, TBOBCK, tbostk
                                WHERE    bsk_outdate <>' '
                                AND bsk_cancd='N'
                                AND  bsk_indentno ='$INNO'
                                AND BSK_ALLOWDATE = '$DT_EXE' AND BSK_ALLOWTIME = '$TM_EXE'
                                AND bsk_bageno = bck_bageno AND bsk_recno = bck_recno
                                AND  bsk_indentno = bck_indentno  AND bsk_medno = bck_medno  
                                AND bkd_bldkind =  bsk_bldkind
                                 AND BCK_CANDATE=' ' AND BCK_CANTIME=' '
                                ORDER BY  SubStr(bsk_bageno, 4, 7)";
            $stidC=oci_parse($conn,$Serch_STDATAC);
            oci_execute($stidC);
            $arrC=[];
            while ($row=oci_fetch_array($stidC)){
                $C_ID=$row['C_ID'];
                $C_NUM=$row['C_NUM'];
                $C_TP=$row['C_TP'];
                $C_UR=$row['C_UR'];
                $C_CUR=$row['C_CUR'];
                $C_DTSEQ=$row['C_DTSEQ'];
                $C_BKD=$row['C_BKD'];
                $C_INDNO=$row['C_INDNO'];
                $arrC[]=array('C_ID'=>$C_ID,'C_NUM'=>$C_NUM,'C_TP'=>$C_TP,'C_UR'=>$C_UR,
                    'C_CUR'=>$C_CUR,'C_DTSEQ'=>$C_DTSEQ,'C_BKD'=>$C_BKD,'C_INDNO'=>$C_INDNO);
            }
            $new_ST_DATAC=json_encode($arrC,JSON_UNESCAPED_UNICODE);
            $arr[]=array('ST_DATAC'=>$new_ST_DATAC);
            break;
    }
    return json_encode($arr,JSON_UNESCAPED_UNICODE);
}
function PosCBLDSave($conn,$sTraID,$sPg,$sDt,$sTm){
    //領輸血核對資料儲存

    $Ssql="SELECT ST_DATAA,ST_DATAB,ST_DATAC,ID_PATIENT,DT_EXCUTE,TM_EXCUTE,UR_PROCESS from HIS803.NISWSTP
        WHERE ID_TABFORM = 'CBLD'  AND ID_TRANSACTION = '$sTraID'";

    $stid=oci_parse($conn,$Ssql);
    oci_execute($stid);
    $ID_PATIENT='';
    $DT_EXCUTE='';
    $TM_EXCUTE='';
    $ST_DATAA='';
    $ST_DATAB='';
    $ST_DATAC='';
    while (oci_fetch_array($stid)){
        $ST_DATAA=oci_result($stid,"ST_DATAA")->read(2000);
        $ST_DATAB=oci_result($stid,"ST_DATAB")->read(2000);
        $ST_DATAC=oci_result($stid,"ST_DATAC")->read(2000);
        $ID_PATIENT=oci_result($stid,"ID_PATIENT");
        $DT_EXCUTE=oci_result($stid,"DT_EXCUTE");
        $TM_EXCUTE=oci_result($stid,"TM_EXCUTE");
        $UR_PROCESS=oci_result($stid,"UR_PROCESS");
    }
    $response='';
    $UPDATESQL="UPDATE TBOBCK SET ";
    if(trim($DT_EXCUTE)=="" && trim($TM_EXCUTE)==""){
        switch ($sPg){
            case 'A':
                $A=json_decode($ST_DATAA);
                break;
            case 'B':
                if($ST_DATAB){

                    $B=json_decode($ST_DATAB);
                    if(GetCBLDCheck($ST_DATAB)=="false"){
                        return   $response=json_encode(array("response" => "false","message" =>"發血存檔錯誤訊息:血袋尚未勾選"),JSON_UNESCAPED_UNICODE);
                    }
                    for ($i=0;$i<count($B);$i++){
                        $B_ID=$B[$i]->{"B_ID"};
                        $B_NUM=$B[$i]->{"B_NUM"};
                        $B_TP=$B[$i]->{"B_TP"};
                        $B_UR=$B[$i]->{"B_UR"};
                        $B_CUR=$B[$i]->{"B_CUR"};
                        $B_DTSEQ=$B[$i]->{"B_DTSEQ"};
                        $B_BKD=$B[$i]->{"B_BKD"};
                        $B_INDNO=$B[$i]->{"B_INDNO"};
                        if(!empty($B_ID) && $B_ID !=""){
                            $str=" BCK_GETDATE="."'$sDt'".
                                " ,BCK_GETTIME="."'$sTm'".
                                " ,BCK_GETOPID="."'$B_UR'".
                                " ,BCK_GETCKOPID="."'$B_CUR'".
                                " ,BCK_GETFROM="."'WEB'".
                                " where BCK_DATMSEQ="."'$B_DTSEQ'".
                                " and bck_indentno ="."'$B_INDNO'".
                                " and bck_bageno ="."'$B_ID'".
                                " and bck_medno="."'$ID_PATIENT'";

                            $Bsql=$UPDATESQL.$str;

                            $Bstid=oci_parse($conn,$Bsql);
                            if (!$Bstid){
                                $e=oci_error($conn);
                                return $response=json_encode(array("response" => "false","message" =>"領血存檔錯誤訊息:".$e['message']),JSON_UNESCAPED_UNICODE);
                            }
                            $Bex=oci_execute($Bstid,OCI_NO_AUTO_COMMIT);
                            if(!$Bex)
                            {
                                oci_rollback($conn);
                                $e=oci_error($Bstid);
                                return $response=json_encode(array("response" => "false","message" =>"領血存檔錯誤訊息:".$e['message']),JSON_UNESCAPED_UNICODE);
                            }
                            else{
                                $r=oci_commit($conn);
                                if(!$r){
                                    $e=oci_error($conn);
                                    return $response=json_encode(array("response" => "false","message" =>"領血存檔錯誤訊息:".$e['message']),JSON_UNESCAPED_UNICODE);

                                }
                                $response=json_encode(array("response" => "success","message" =>"this is the success message"),JSON_UNESCAPED_UNICODE);

                            }
                        }
                    }
                }
                break;
            case 'C':
                if($ST_DATAC){
                    $C=json_decode($ST_DATAC);
                    if(GetCBLDCheck($ST_DATAC)=="false"){
                        return   $response=json_encode(array("response" => "false","message" =>"發血存檔錯誤訊息:血袋尚未勾選"),JSON_UNESCAPED_UNICODE);
                    }
                    for ($i=0;$i<count($C);$i++){
                        $C_ID=$C[$i]->{"C_ID"};
                        $C_NUM=$C[$i]->{"C_NUM"};
                        $C_TP=$C[$i]->{"C_TP"};
                        $C_UR=$C[$i]->{"C_UR"};
                        $C_CUR=$C[$i]->{"C_CUR"};
                        $C_DTSEQ=$C[$i]->{"C_DTSEQ"};
                        $C_BKD=$C[$i]->{"C_BKD"};
                        $C_INDNO=$C[$i]->{"C_INDNO"};
                        if(!empty($C_ID) && $C_ID !=""){


                            $str=" BCK_TRADATE="."'$sDt'".
                                " ,BCK_TRATIME="."'$sTm'".
                                " ,BCK_TRAOPID ="."'$C_UR'".
                                " ,BCK_TRACKOPID="."'$C_CUR'".
                                " ,BCK_TRAFROM="."'WEB'".
                                " where BCK_DATMSEQ="."'$C_DTSEQ'".
                                " and bck_indentno ="."'$C_INDNO'".
                                " and bck_bageno ="."'$C_ID'".
                                " and bck_medno="."'$ID_PATIENT'";



                            $Csql=$UPDATESQL.$str;
                            $Cstid=oci_parse($conn,$Csql);
                            if (!$Cstid){
                                $e=oci_error($conn);
                                return $response=json_encode(array("response" => "false","message" =>"輸血存檔錯誤訊息:".$e['message']),JSON_UNESCAPED_UNICODE);
                            }
                            $Cex=oci_execute($Cstid,OCI_NO_AUTO_COMMIT);
                            if(!$Cex)
                            {
                                oci_rollback($conn);
                                $e=oci_error($Cstid);
                                return  $response=json_encode(array("response" => "false","message" =>"輸血存檔錯誤訊息:".$e['message']),JSON_UNESCAPED_UNICODE);

                            }
                            else{
                                $r=oci_commit($conn);
                                if(!$r){
                                    $e=oci_error($conn);
                                    return  $response=json_encode(array("response" => "false","message" =>"輸血存檔錯誤訊息:".$e['message']),JSON_UNESCAPED_UNICODE);
                                }
                                $response=json_encode(array("response" => "success","message" =>"this is the success message"),JSON_UNESCAPED_UNICODE);
                            }
                        }
                    }
                }
                break;
        }

    }
    return   $response;
}

function GetCBLDJson($conn,$IDPT,$INPt,$sUr,$sDt,$sTm,$sPg,$sDFL){
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
    if($sPg=='B'){
        $SQL="SELECT BCK_DATMSEQ,Usr.NM_USER AS BCK_OPIDNM ,BCK.BCK_BAGENO,BCK.BCK_RECNO,BCK.BCK_BLDKIND,BCK.BCK_INDENTNO,BCK.BCK_GETDATE,
                 BCK.BCK_GETTIME, BCK.BCK_GETOPID, BCK.BCK_GETCKOPID,BCK.BCK_GETFROM
                 FROM TBOBCK BCK INNER JOIN NSUSER Usr ON Usr.ID_USER=BCK.BCK_GETCKOPID WHERE 
                  BCK.BCK_MEDNO='$IDPT'
                  AND  BCK.BCK_GETDATE ='$sDt' AND  BCK.BCK_GETTIME='$sTm'
                  AND  BCK.BCK_CANDATE=' ' AND  BCK.BCK_CANTIME=' '
                  AND  BCK.BCK_GETOPID <>' 'AND  BCK.BCK_GETCKOPID <>' ' 
                 ORDER BY  BCK.BCK_GETDATE DESC";
        $stid=oci_parse($conn,$SQL);
        oci_execute($stid);
        while (oci_fetch_array($stid)){
            $BCK_DATMSEQ=oci_result($stid,'BCK_DATMSEQ');
            $BCK_OPIDNM=oci_result($stid,'BCK_OPIDNM');
            $BCK_BAGENO=oci_result($stid,'BCK_BAGENO');
            $BCK_RECNO=oci_result($stid,'BCK_RECNO');
            $BCK_BLDKIND=oci_result($stid,'BCK_BLDKIND');
            $BCK_INDENTNO=oci_result($stid,'BCK_INDENTNO');
            $BCK_GETDATE=oci_result($stid,'BCK_GETDATE');
            $BCK_GETTIME=oci_result($stid,'BCK_GETTIME');
            $BCK_GETOPID=oci_result($stid,'BCK_GETOPID');
            $BCK_GETCKOPID=oci_result($stid,'BCK_GETCKOPID');
            $BCK_GETFROM=oci_result($stid,'BCK_GETFROM');
            $Arr[]=array('BCK_DATMSEQ'=>$BCK_DATMSEQ,'BCK_BAGENO'=>$BCK_BAGENO,'BCK_RECNO'=>$BCK_RECNO,'BCK_BLDKIND'=>$BCK_BLDKIND,'BCK_INDENTNO'=>$BCK_INDENTNO,
                'BCK_GETDATE'=>$BCK_GETDATE,'BCK_GETTIME'=>$BCK_GETTIME,'BCK_GETOPID'=>$BCK_GETOPID,'BCK_GETCKOPID'=>$BCK_GETCKOPID,'BCK_GETFROM'=>$BCK_GETFROM,'BCK_OPIDNM'=>$BCK_OPIDNM);
        }
    }

    if($sPg=='C'){
        $SQL="SELECT  BCK_DATMSEQ,Usr.NM_USER AS BCK_OPIDNM ,BCK.BCK_BAGENO,BCK.BCK_RECNO,BCK.BCK_BLDKIND,
                BCK.BCK_INDENTNO,BCK.BCK_TRADATE,BCK.BCK_TRATIME,BCK.BCK_TRAOPID, 
                BCK.BCK_GETCKOPID,BCK.BCK_TRACKOPID,BCK.BCK_TRAFROM FROM 
                TBOBCK BCK INNER JOIN NSUSER Usr ON Usr.ID_USER=BCK.BCK_TRACKOPID
                WHERE BCK_MEDNO='$IDPT' AND BCK_TRADATE ='$sDt' AND BCK_TRATIME='$sTm'
                AND BCK_CANDATE=' ' AND BCK_CANTIME=' ' AND BCK_TRAOPID <>' ' AND BCK_TRACKOPID <>' ' 
                ORDER BY BCK_TRADATE DESC";

        $stid=oci_parse($conn,$SQL);
        oci_execute($stid);
        while (oci_fetch_array($stid)){
            $BCK_DATMSEQ=oci_result($stid,'BCK_DATMSEQ');
            $BCK_OPIDNM=oci_result($stid,'BCK_OPIDNM');
            $BCK_BAGENO=oci_result($stid,'BCK_BAGENO');
            $BCK_RECNO=oci_result($stid,'BCK_RECNO');
            $BCK_BLDKIND=oci_result($stid,'BCK_BLDKIND');
            $BCK_INDENTNO=oci_result($stid,'BCK_INDENTNO');
            $BCK_TRADATE=oci_result($stid,'BCK_TRADATE');
            $BCK_TRATIME=oci_result($stid,'BCK_TRATIME');
            $BCK_TRAOPID=oci_result($stid,'BCK_TRAOPID');
            $BCK_TRACKOPID=oci_result($stid,'BCK_TRACKOPID');
            $BCK_TRAFROM=oci_result($stid,'BCK_TRAFROM');

            $Arr[]=array('BCK_DATMSEQ'=>$BCK_DATMSEQ,'BCK_BAGENO'=>$BCK_BAGENO,'BCK_RECNO'=>$BCK_RECNO,'BCK_TRADATE'=>$BCK_TRADATE,'BCK_BLDKIND'=>$BCK_BLDKIND,'BCK_INDENTNO'=>$BCK_INDENTNO,
                'BCK_TRATIME'=>$BCK_TRATIME,'BCK_TRAOPID'=>$BCK_TRAOPID,'BCK_TRACKOPID'=>$BCK_TRACKOPID,'BCK_TRAFROM'=>$BCK_TRAFROM,'BCK_OPIDNM'=>$BCK_OPIDNM);
        }

    }


    $JSON=json_encode($Arr,JSON_UNESCAPED_UNICODE);
    $sql="SELECT WI.ST_DATAA,WI.ST_DATAB,WI.ST_DATAC FROM HIS803.NISWSIT WI
            WHERE WI.ID_TABFORM = 'CBLD'";
    $stid1=oci_parse($conn,$sql);
    oci_execute($stid1);
    $ST_DATAA='';
    $ST_DATAB='';
    $ST_DATAC='';
    while ($row=oci_fetch_array($stid1)){
        $ST_DATAA=$row[0]->load();
        $ST_DATAB=$row[1]->load();
        $ST_DATAC=$row[2]->load();
    }
    $CallBackJson='';
    switch ($sPg){
        case 'B':
            $ST_DATAB=$JSON;
            $CallBackJson=str_replace('}',',"sTraID":"'.$TransKey.'","sSave":"'.$ID_COMFIRM.'"}', $ST_DATAB);
            break;
        case 'C':
            $ST_DATAC=$JSON;
            $CallBackJson=str_replace('}',',"sTraID":"'.$TransKey.'","sSave":"'.$ID_COMFIRM.'"}', $ST_DATAC);
            break;
    }

    $TPsql = "INSERT INTO HIS803.NISWSTP(ID_TABFORM,ID_TRANSACTION,ID_PATIENT,ID_INPATIENT,DT_EXCUTE,
            TM_EXCUTE,ST_DATAA,ST_DATAB,ST_DATAC,FORMSEQANCE,ID_BED,DM_PROCESS,UR_PROCESS,JID_NSRANK,FORMSEQANCE_WT)
             VALUES ('CBLD','$TransKey','$IDPT','$INPt','$sDt',
             '$sTm','$ST_DATAA','$ST_DATAB','$ST_DATAC',' ','$ID_BED','$DM_PR','$sUr','$JID_NSRANK','$FORMSEQANCE_WT')";
    $TP_stid=oci_parse($conn,$TPsql);
    $TP_r=oci_execute($TP_stid,OCI_NO_AUTO_COMMIT);
    if(!$TP_r){
        oci_rollback($conn);
        $e=oci_error($TP_stid);
        $json=json_encode(array("message"=>$e['message']),JSON_UNESCAPED_UNICODE);

        return $json;
    }else{
        $comm=oci_commit($conn);
       if(!$comm){
           $e=oci_error($conn);
           $json=json_encode(array("message"=>$e['message']),JSON_UNESCAPED_UNICODE);

           return $json;
       }
    }
    return $CallBackJson;
}
function PosCBLDCancel($conn,$sTraID,$sPg){
    //作廢領輸血核對評估紀錄(要先insert=>再update)
    $DateTime = date("YmdHis");
    $STR = substr($DateTime, 0, 4);
    $STR1 = substr($DateTime, -10, 10);
    $str = $STR - 1911;
    $dm_cand = $str . $STR1;
    $CANDATE=substr($dm_cand,0,7);
    $CANTIME=substr($dm_cand,7,4);


    $Ssql="SELECT ID_INPATIENT,ID_PATIENT,DT_EXCUTE,TM_EXCUTE,ST_DATAB,ST_DATAC from HIS803.NISWSTP
        WHERE ID_TABFORM = 'CBLD'  AND ID_TRANSACTION = '$sTraID'";
    $stid=oci_parse($conn,$Ssql);
    oci_execute($stid);
    $ID_INPATIENT='';
    $ID_PATIENT='';
    $DT_EXCUTE='';
    $TM_EXCUTE='';
    $ST_DATAB='';
    $ST_DATAC='';
    while (oci_fetch_array($stid)){
        $ID_INPATIENT=oci_result($stid,'ID_INPATIENT');
        $ID_PATIENT=oci_result($stid,'ID_PATIENT');
        $DT_EXCUTE=oci_result($stid,'DT_EXCUTE');
        $TM_EXCUTE=oci_result($stid,'TM_EXCUTE');
        $ST_DATAB=oci_result($stid,'ST_DATAB')->read(2000);
        $ST_DATAC=oci_result($stid,'ST_DATAC')->read(2000);
    }


    $DATAB=json_decode($ST_DATAB);
    $DATAC=json_decode($ST_DATAC);
    $response='';




    switch ($sPg){
        case 'B':
            for($i=0;$i<count($DATAB);$i++) {
                $BCK_BAGENO = $DATAB[$i]->{'BCK_BAGENO'};
                $BCK_RECNO = $DATAB[$i]->{'BCK_RECNO'};
                $BCK_BLDKIND = $DATAB[$i]->{'BCK_BLDKIND'};
                $BCK_INDENTNO = $DATAB[$i]->{'BCK_INDENTNO'};
                $BCK_GETDATE = $DATAB[$i]->{'BCK_GETDATE'};
                $BCK_GETTIME = $DATAB[$i]->{'BCK_GETTIME'};
                $BCK_GETCKOPID = $DATAB[$i]->{'BCK_GETCKOPID'};
                $BCK_GETFROM = $DATAB[$i]->{'BCK_GETFROM'};

                $SQL = "SELECT BCK_DATMSEQ,BCK_TRADATE,BCK_TRATIME,BCK_TRAOPID,BCK_TRACKOPID,BCK_TRAFROM 
                        FROM TBOBCK 
                        WHERE  BCK_GETDATE=:BCK_GETDATE AND BCK_BAGENO=:BCK_BAGENO
                         AND BCK_GETTIME=:BCK_GETTIME AND BCK_CANDATE=' 'AND BCK_CANTIME=' ' ";

                $DTSEQ_stid=oci_parse($conn,$SQL);
                oci_bind_by_name($DTSEQ_stid,":BCK_GETDATE",$BCK_GETDATE);
                oci_bind_by_name($DTSEQ_stid,":BCK_BAGENO",$BCK_BAGENO);
                oci_bind_by_name($DTSEQ_stid,":BCK_GETTIME",$BCK_GETTIME);

                oci_execute($DTSEQ_stid);
                $BCK_DATMSEQ='';
                $BCK_TRADATE='';
                $BCK_TRATIME='';
                $BCK_TRAOPID='';
                $BCK_TRACKOPID='';
                $BCK_TRAFROM='';
                while (oci_fetch_array($DTSEQ_stid)){
                    $BCK_DATMSEQ=oci_result($DTSEQ_stid,'BCK_DATMSEQ');
                    $BCK_TRADATE=oci_result($DTSEQ_stid,'BCK_TRADATE');
                    $BCK_TRATIME=oci_result($DTSEQ_stid,'BCK_TRATIME');
                    $BCK_TRAOPID=oci_result($DTSEQ_stid,'BCK_TRAOPID');
                    $BCK_TRACKOPID=oci_result($DTSEQ_stid,'BCK_TRACKOPID');
                    $BCK_TRAFROM=oci_result($DTSEQ_stid,'BCK_TRAFROM');

                }

                $INSERSQL="INSERT INTO TBOBCK
                    (BCK_DATMSEQ,BCK_BAGENO,BCK_RECNO,BCK_BLDKIND,BCK_INDENTNO,BCK_MEDNO,
                    BCK_GETDATE,BCK_GETTIME,BCK_GETOPID,BCK_GETCKOPID,BCK_GETFROM,
                    BCK_TRADATE,BCK_TRATIME,BCK_TRAOPID,BCK_TRACKOPID,BCK_TRAFROM,
                    BCK_CANCD,
                    BCK_CANDATE,BCK_CANTIME,BCK_CANOPID,BCK_CANCKOPID,BCK_CANFROM)
                    VALUES 
                    (NIS_DATETIMESEQ,'$BCK_BAGENO','$BCK_RECNO','$BCK_BLDKIND','$BCK_INDENTNO','$ID_PATIENT',
                    ' ',' ',' ',' ',' ',
                    '$BCK_TRADATE','$BCK_TRATIME','$BCK_TRAOPID','$BCK_TRACKOPID','$BCK_TRAFROM',
                    'N',
                    ' ',' ',' ',' ',' ')";
                $stid1=oci_parse($conn,$INSERSQL);
                $r=oci_execute($stid1,OCI_NO_AUTO_COMMIT);
                if(!$r){
                    $e=oci_error($stid1);
                    $response=json_encode(array("response" => "false","message" =>"錯誤訊息:".$e['message']),JSON_UNESCAPED_UNICODE);
                    return $response;
                }else{
                    oci_commit($conn);
                }

                $UPSQL="UPDATE TBOBCK  SET
                 BCK_CANDATE=:CANDATE,
                 BCK_CANTIME=:CANTIME,
                 BCK_CANCKOPID=:BCK_GETCKOPID,
                 BCK_GETFROM=:BCK_GETFROM
                WHERE BCK_BAGENO=:BCK_BAGENO AND BCK_DATMSEQ=:BCK_DATMSEQ
                AND BCK_GETDATE=:BCK_GETDATE AND BCK_GETTIME=:BCK_GETTIME
                AND BCK_CANDATE=' 'AND BCK_CANTIME=' '";

                $stid2=oci_parse($conn,$UPSQL);
                oci_bind_by_name($stid2,":CANDATE",$CANDATE);
                oci_bind_by_name($stid2,":CANTIME",$CANTIME);
                oci_bind_by_name($stid2,":BCK_GETCKOPID",$BCK_GETCKOPID);
                oci_bind_by_name($stid2,":BCK_GETFROM",$BCK_GETFROM);
                oci_bind_by_name($stid2,":BCK_BAGENO",$BCK_BAGENO);
                oci_bind_by_name($stid2,":BCK_DATMSEQ",$BCK_DATMSEQ);
                oci_bind_by_name($stid2,":BCK_GETDATE",$BCK_GETDATE);
                oci_bind_by_name($stid2,":BCK_GETTIME",$BCK_GETTIME);

                $r=oci_execute($stid2,OCI_NO_AUTO_COMMIT);
                if(!$r){
                    ocirollback($conn);
                    $e=oci_error($conn);
                    $response=json_encode(array("response" => "false","message" =>"錯誤訊息:".$e['message']),JSON_UNESCAPED_UNICODE);

                }else{
                    oci_commit($conn);
                    $response=json_encode(array("response" => "success"),JSON_UNESCAPED_UNICODE);

                }

            }
            break;
        case 'C':
            for($i=0;$i<count($DATAC);$i++){
                $BCK_BAGENO = $DATAC[$i]->{'BCK_BAGENO'};
                $BCK_RECNO = $DATAC[$i]->{'BCK_RECNO'};
                $BCK_BLDKIND = $DATAC[$i]->{'BCK_BLDKIND'};
                $BCK_INDENTNO = $DATAC[$i]->{'BCK_INDENTNO'};
                $BCK_TRADATE = $DATAC[$i]->{'BCK_TRADATE'};
                $BCK_TRATIME = $DATAC[$i]->{'BCK_TRATIME'};
                $BCK_TRACKOPID = $DATAC[$i]->{'BCK_TRACKOPID'};
                $BCK_TRAFROM = $DATAC[$i]->{'BCK_TRAFROM'};


                $SQL = "SELECT BCK_DATMSEQ,BCK_GETDATE,BCK_GETTIME,BCK_GETOPID,BCK_GETCKOPID,BCK_GETFROM  
                        FROM TBOBCK WHERE 
                        BCK_TRADATE=:BCK_TRADATE AND BCK_BAGENO=:BCK_BAGENO AND
                        BCK_TRATIME=:BCK_TRATIME AND BCK_CANDATE=' 'AND BCK_CANTIME=' ' ";

                $DTSEQ_stid=oci_parse($conn,$SQL);
                oci_bind_by_name($DTSEQ_stid,":BCK_TRADATE",$BCK_TRADATE);
                oci_bind_by_name($DTSEQ_stid,":BCK_BAGENO",$BCK_BAGENO);
                oci_bind_by_name($DTSEQ_stid,":BCK_TRATIME",$BCK_TRATIME);

                oci_execute($DTSEQ_stid);
                $BCK_DATMSEQ='';
                $BCK_GETDATE='';
                $BCK_GETTIME='';
                $BCK_GETOPID='';
                $BCK_GETCKOPID='';
                $BCK_GETFROM='';
                while (oci_fetch_array($DTSEQ_stid)){
                    $BCK_DATMSEQ=oci_result($DTSEQ_stid,'BCK_DATMSEQ');
                    $BCK_GETDATE=oci_result($DTSEQ_stid,'BCK_GETDATE');
                    $BCK_GETTIME=oci_result($DTSEQ_stid,'BCK_GETTIME');
                    $BCK_GETOPID=oci_result($DTSEQ_stid,'BCK_GETOPID');
                    $BCK_GETCKOPID=oci_result($DTSEQ_stid,'BCK_GETCKOPID');
                    $BCK_GETFROM=oci_result($DTSEQ_stid,'BCK_GETFROM');
                }

                $INSERSQL="INSERT INTO TBOBCK
                    (BCK_DATMSEQ,BCK_BAGENO,BCK_RECNO,BCK_BLDKIND,BCK_INDENTNO,BCK_MEDNO,
                    BCK_GETDATE,BCK_GETTIME,BCK_GETOPID,BCK_GETCKOPID,BCK_GETFROM,
                    BCK_TRADATE,BCK_TRATIME,BCK_TRAOPID,BCK_TRACKOPID,BCK_TRAFROM,
                    BCK_CANCD,
                    BCK_CANDATE,BCK_CANTIME,BCK_CANOPID,BCK_CANCKOPID,BCK_CANFROM)
                    VALUES 
                    (NIS_DATETIMESEQ,'$BCK_BAGENO','$BCK_RECNO','$BCK_BLDKIND','$BCK_INDENTNO','$ID_PATIENT',
                    '$BCK_GETDATE','$BCK_GETTIME','$BCK_GETOPID','$BCK_GETCKOPID','$BCK_GETFROM',
                    ' ',' ',' ',' ',' ',
                    'N',
                    ' ',' ',' ',' ',' ')";

                $stid1=oci_parse($conn,$INSERSQL);

                $r=oci_execute($stid1);
                if(!$r){
                    $e=oci_error($stid1);
                    $response=json_encode(array("response" => "false","message" =>"錯誤訊息:".$e['message']),JSON_UNESCAPED_UNICODE);
                    return $response;
                }


                $UPSQL=" UPDATE TBOBCK  SET
                 BCK_CANDATE=:BCK_CANDATE,
                 BCK_CANTIME=:BCK_CANTIME,
                 BCK_CANCKOPID=:BCK_CANCKOPID,
                 BCK_TRAFROM=:BCK_TRAFROM
                WHERE BCK_BAGENO=:BCK_BAGENO AND BCK_DATMSEQ=:BCK_DATMSEQ
                AND BCK_TRADATE=:BCK_TRADATE AND BCK_TRATIME=:BCK_TRATIME
                AND BCK_CANDATE=' 'AND BCK_CANTIME=' '";

                $stid2=oci_parse($conn,$UPSQL);
                oci_bind_by_name($stid2,":BCK_CANDATE",$CANDATE);
                oci_bind_by_name($stid2,":BCK_CANTIME",$CANTIME);
                oci_bind_by_name($stid2,":BCK_CANCKOPID",$BCK_TRACKOPID);
                oci_bind_by_name($stid2,":BCK_TRAFROM",$BCK_TRAFROM);
                oci_bind_by_name($stid2,":BCK_BAGENO",$BCK_BAGENO);
                oci_bind_by_name($stid2,":BCK_DATMSEQ",$BCK_DATMSEQ);
                oci_bind_by_name($stid2,":BCK_TRADATE",$BCK_TRADATE);
                oci_bind_by_name($stid2,":BCK_TRATIME",$BCK_TRATIME);


                $r=oci_execute($stid2,OCI_NO_AUTO_COMMIT);
                if(!$r){
                    ocirollback($conn);
                    $e=oci_error($stid2);
                    $response=json_encode(array("response" => "false","message" =>"錯誤訊息:".$e['message']),JSON_UNESCAPED_UNICODE);

                }else{
                    oci_commit($conn);
                    $response=json_encode(array("response" => "success"),JSON_UNESCAPED_UNICODE);

                }
            }
            break;
    }


    return $response;
}
function GetCBLDCheck($json){
    $JsonB=json_decode($json);
    $response="true";
    if(count($JsonB)==0){
        $response="false";
    }
    return $response;
}