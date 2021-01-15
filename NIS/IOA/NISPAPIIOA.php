<?php
function GetIOAIniJson($conn,$Idpt,$INPt,$ID_BED,$sTraID,$sSave,$date,$sUr,$JID_NSRANK,$FORMSEQANCE_WT){

        $IT_SQL= "SELECT ST_DATAA,ST_DATAB,ST_DATAC,ST_DATAD,ST_DATAE,ST_DATAF,ST_DATAG, ST_DATAH 
                  FROM HIS803.NISWSIT WHERE ID_TABFORM = 'IOA'";
        $IT_stid=oci_parse($conn,$IT_SQL);
        if(!$IT_stid){

            $e=oci_error($conn);
            return $e['message'];
        }
       oci_execute($IT_stid);

        $ST_DATAA=[];
        $ST_DATAB='';
        $ST_DATAC=[];
        $ST_DATAD=[];
        $ST_DATAE='';
        $ST_DATAF='';
        $ST_DATAG=[];
        $ST_DATAH='';

        while (oci_fetch_array($IT_stid)){
          //  $ST_DATAA=oci_result($IT_stid,"ST_DATAA")->load();
            $ST_DATAB=oci_result($IT_stid,"ST_DATAB")->load();
         //   $ST_DATAC=oci_result($IT_stid,"ST_DATAC")->load();
         //   $ST_DATAD=oci_result($IT_stid,"ST_DATAD")->load();
            $ST_DATAE=oci_result($IT_stid,"ST_DATAE")->load();
            $ST_DATAF=oci_result($IT_stid,"ST_DATAF")->load();
        //    $ST_DATAG=oci_result($IT_stid,"ST_DATAG")->load();
            $ST_DATAH=oci_result($IT_stid,"ST_DATAH")->load();
        }

        oci_free_statement($IT_stid);

        $A_SQL=" SELECT DISTINCT CID_CLASS, JID_KEY, NM_ITEM  FROM NIS_V_HORD_QI
                WHERE ID_INPATIENT = '$INPt' AND DT_BEGIN <= '1090101'
                AND (DT_DC = ' ' Or DT_DC >= '1090101')
                ORDER by NM_ITEM ";


        $C_SQL="SELECT DISTINCT CID_CLASS, JID_KEY, NM_ITEM FROM NIS_V_HORD_QB
                WHERE ID_INPATIENT = '$INPt'
                AND DT_BEGIN <= '0970121' AND (DT_DC = ' ' Or DT_DC >= '0970121')
                ORDER by NM_ITEM";

        $D_SQL="SELECT DISTINCT CID_CLASS, JID_KEY, NM_ITEM FROM NIS_V_HORD_QIC
                WHERE ID_INPATIENT = '$INPt' AND DT_BEGIN <= '1090101' AND (DT_DC = ' ' OR DT_DC >= '1090101')
                AND JID_KEY IN (SELECT ID_ITEM FROM NSCLSI WHERE CID_CLASS = 'TPPN' AND IS_ACTIVE = 'Y')
                ORDER BY NM_ITEM";

        $G_SQL="SELECT DISTINCT CID_CLASS, JID_KEY, NM_TUBE || '(' || NM_ORGAN || ')' AS NM_ITEM, NO_PROBLEM
                FROM NIS_V_TUPG_QO WHERE ID_INPATIENT = '$INPt'
                AND DT_EXECUTE <= '1090701'
                AND (DT_ENDING >= ' ' OR DT_ENDING = ' ')
                AND IS_IO = 'Y'
                ORDER BY NO_PROBLEM";


        $A_stid=oci_parse($conn,$A_SQL);
        $C_stid=oci_parse($conn,$C_SQL);
        $D_stid=oci_parse($conn,$D_SQL);
        $G_stid=oci_parse($conn,$G_SQL);

        oci_execute($A_stid);
        oci_execute($C_stid);
        oci_execute($D_stid);
        oci_execute($G_stid);

        while (oci_fetch_array($A_stid)){
            $NM_ITEM=oci_result($A_stid,"NM_ITEM");
            $JID_KEY=oci_result($A_stid,"JID_KEY");
            $CID_CLASS=oci_result($A_stid,"CID_CLASS");
            $ST_DATAA[]=array("M_Nam"=>$NM_ITEM,"IO_TYPE"=>'IOTP000000IA',"JID_KEY"=>$JID_KEY,"CID_CLASS"=>$CID_CLASS);
        }

        while (oci_fetch_array($C_stid)){
            $NM_ITEM=oci_result($C_stid,"NM_ITEM");
            $JID_KEY=oci_result($C_stid,"JID_KEY");
            $CID_CLASS=oci_result($C_stid,"CID_CLASS");
            $ST_DATAC[]=array("M_Nam"=>$NM_ITEM,"IO_TYPE"=>'IOTP000000IC',"JID_KEY"=>$JID_KEY,"CID_CLASS"=>$CID_CLASS);
        }
        while (oci_fetch_array($D_stid)){
            $NM_ITEM=oci_result($D_stid,"NM_ITEM");
            $JID_KEY=oci_result($D_stid,"JID_KEY");
            $CID_CLASS=oci_result($D_stid,"CID_CLASS");
            $ST_DATAD[]=array("M_Nam"=>$NM_ITEM,"IO_TYPE"=>'IOTP000000ID',"JID_KEY"=>$JID_KEY,"CID_CLASS"=>$CID_CLASS);
        }
        while (oci_fetch_array($G_stid)){
            $NM_ITEM=oci_result($G_stid,"NM_ITEM");
            $JID_KEY=oci_result($G_stid,"JID_KEY");
            $CID_CLASS=oci_result($G_stid,"CID_CLASS");
            $NO_PROBLEM=oci_result($G_stid,"NO_PROBLEM");

            $ST_DATAG[]=array("M_Nam"=>$NM_ITEM,"IO_TYPE"=>'IOTP000000OC',"JID_KEY"=>$JID_KEY,"CID_CLASS"=>$CID_CLASS,"NO_PROBLEM"=>$NO_PROBLEM);
        }


        $JsonA=json_encode($ST_DATAA,JSON_UNESCAPED_UNICODE);
        $JsonC=json_encode($ST_DATAC,JSON_UNESCAPED_UNICODE);
        $JsonD=json_encode($ST_DATAD,JSON_UNESCAPED_UNICODE);
        $JsonG=json_encode($ST_DATAG,JSON_UNESCAPED_UNICODE);



        oci_free_statement($A_stid);
        oci_free_statement($C_stid);
        oci_free_statement($D_stid);
        oci_free_statement($G_stid);

        $TP_SQL="INSERT INTO HIS803.NISWSTP(
                        ID_TABFORM,ID_TRANSACTION,ID_PATIENT,ID_INPATIENT,DT_EXCUTE,TM_EXCUTE,
                        ST_DATAA,ST_DATAB,ST_DATAC,ST_DATAD,ST_DATAE,ST_DATAF,ST_DATAG,ST_DATAH,
                        ID_BED,DM_PROCESS,UR_PROCESS,JID_NSRANK,FORMSEQANCE_WT)
                         VALUES (
                         'IOA',:sTraID,:Idpt,:INPt,' ',' ',
                         EMPTY_CLOB(),EMPTY_CLOB(),EMPTY_CLOB(),EMPTY_CLOB(),EMPTY_CLOB(),EMPTY_CLOB(),EMPTY_CLOB(),EMPTY_CLOB(),
                        :BED,:DM_P,:UR_P,:NSRANK,:FormSeq)
                        RETURNING  ST_DATAA,ST_DATAB,ST_DATAC,ST_DATAD,ST_DATAE,ST_DATAF,ST_DATAG,ST_DATAH
                         INTO :ST_DATAA,:ST_DATAB,:ST_DATAC,:ST_DATAD,:ST_DATAE,:ST_DATAF,:ST_DATAG,:ST_DATAH
                         ";


        $TP_Stid = oci_parse($conn, $TP_SQL);
        if(!$TP_Stid){
            $e=oci_error($conn);
            return $e['message'];
        }


        $clobA=oci_new_descriptor($conn,OCI_D_LOB);
        $clobB=oci_new_descriptor($conn,OCI_D_LOB);
        $clobC=oci_new_descriptor($conn,OCI_D_LOB);
        $clobD=oci_new_descriptor($conn,OCI_D_LOB);
        $clobE=oci_new_descriptor($conn,OCI_D_LOB);
        $clobF=oci_new_descriptor($conn,OCI_D_LOB);
        $clobG=oci_new_descriptor($conn,OCI_D_LOB);
        $clobH=oci_new_descriptor($conn,OCI_D_LOB);




        oci_bind_by_name($TP_Stid,":sTraID",$sTraID);
        oci_bind_by_name($TP_Stid,":Idpt",$Idpt);
        oci_bind_by_name($TP_Stid,":INPt",$INPt);
        oci_bind_by_name($TP_Stid,":BED",$ID_BED);
        oci_bind_by_name($TP_Stid,":DM_P",$date);
        oci_bind_by_name($TP_Stid,":UR_P",$sUr);
        oci_bind_by_name($TP_Stid,":NSRANK",$JID_NSRANK);
        oci_bind_by_name($TP_Stid,":FormSeq",$FORMSEQANCE_WT);

        //I
        oci_bind_by_name($TP_Stid,":ST_DATAA",$clobA,-1,OCI_B_CLOB);
        oci_bind_by_name($TP_Stid,":ST_DATAB",$clobB,-1,OCI_B_CLOB);
        oci_bind_by_name($TP_Stid,":ST_DATAC",$clobC,-1,OCI_B_CLOB);
        oci_bind_by_name($TP_Stid,":ST_DATAD",$clobD,-1,OCI_B_CLOB);

        //O
        oci_bind_by_name($TP_Stid,":ST_DATAE",$clobE,-1,OCI_B_CLOB);
        oci_bind_by_name($TP_Stid,":ST_DATAF",$clobF,-1,OCI_B_CLOB);
        oci_bind_by_name($TP_Stid,":ST_DATAG",$clobG,-1,OCI_B_CLOB);
        oci_bind_by_name($TP_Stid,":ST_DATAH",$clobH,-1,OCI_B_CLOB);


        $result = oci_execute($TP_Stid,OCI_NO_AUTO_COMMIT);
        if(!$result){
            $e=oci_error($TP_Stid);
            return $e['message'];
        }
        $clobA->save($JsonA);
        $clobB->save($ST_DATAB);
        $clobC->save($JsonC);
        $clobD->save($JsonD);
        $clobE->save($ST_DATAE);
        $clobF->save($ST_DATAF);
        $clobG->save($JsonG);
        $clobH->save($ST_DATAH);

        oci_free_statement($TP_Stid);
        oci_commit($conn);
        $JsonBack=array('sTraID' => $sTraID, 'sSave' => $sSave,'FORMSEQANCE_WT'=>$FORMSEQANCE_WT,"JID_NSRANK"=>$JID_NSRANK);
        return json_encode($JsonBack,JSON_UNESCAPED_UNICODE);
}


function GetIOAPageJson($conn,$sPg,$sTraID){
    $TP_SQL="SELECT ST_DATA".$sPg." FROM HIS803.NISWSTP WHERE ID_TRANSACTION=:sTraID AND ID_TABFORM = 'IOA'" ;

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

    $obj_Arr=json_decode($DATA);

    if ($sPg==="A" || $sPg==="C" || $sPg==="D" || $sPg==="G"){
        //Has order
        $result=ObjectMap($conn,$obj_Arr);
    }else{
        //No order.
        $result=ObjectMap($conn,GetDeFaultArray($sPg,$obj_Arr));
    }

    return json_encode($result,JSON_UNESCAPED_UNICODE);


}
function PosIOASave($conn,$sTraID,$sPg,$sDt,$sTm,$sUr){

    $DateTime = date("YmdHis");
    $STR = substr($DateTime, 0, 4);
    $STR1 = substr($DateTime, -10, 10);
    $str = $STR - 1911;
    $NowDT= $str . $STR1;


    $Ssql="SELECT ID_INPATIENT,ID_PATIENT, 
            ST_DATAA,ST_DATAB,ST_DATAC,ST_DATAD,ST_DATAE,ST_DATAF,ST_DATAG,ST_DATAH,
            ID_BED,JID_NSRANK,FORMSEQANCE_WT
           FROM HIS803.NISWSTP
           WHERE ID_TABFORM = 'IOA'  AND ID_TRANSACTION = :id_TRANS";

    $Sstid=oci_parse($conn,$Ssql);
    if (!$Sstid){
        $e=oci_error($conn);
        return $e['message'];
    }

    oci_bind_by_name($Sstid,":id_TRANS",$sTraID);
    oci_execute($Sstid);
    $IdinPt='';
    $IdPt='';
    $ID_BED='';
    $JID_NSRANK='';
    $FormSeq_WT='';


    $DATA=[];

    while ($row=oci_fetch_array($Sstid)){
        $IdinPt=oci_result($Sstid,"ID_INPATIENT");
        $IdPt=oci_result($Sstid,"ID_PATIENT");

        $ST_DATAA=oci_result($Sstid,"ST_DATAA")->load();
        $ST_DATAB=oci_result($Sstid,"ST_DATAB")->load();
        $ST_DATAC=oci_result($Sstid,"ST_DATAC")->load();
        $ST_DATAD=oci_result($Sstid,"ST_DATAD")->load();
        $ST_DATAE=oci_result($Sstid,"ST_DATAE")->load();
        $ST_DATAF=oci_result($Sstid,"ST_DATAF")->load();
        $ST_DATAG=oci_result($Sstid,"ST_DATAG")->load();
        $ST_DATAH=oci_result($Sstid,"ST_DATAH")->load();

        $ID_BED=oci_result($Sstid,"ID_BED");
        $JID_NSRANK=oci_result($Sstid,"JID_NSRANK");
        $FormSeq_WT=oci_result($Sstid,"FORMSEQANCE_WT");

        array_push($DATA, $ST_DATAA,$ST_DATAB,$ST_DATAC,$ST_DATAD,$ST_DATAE,$ST_DATAF,$ST_DATAG,$ST_DATAH);
    }

    $FormSeq_SQL="SELECT NO_TABFORM FROM  HIS803.NSTBMF  WHERE ID_TABFORM= 'IOQT'";
    $Fsq_stid=oci_parse($conn,$FormSeq_SQL);
    if (!$Fsq_stid){
        $e=oci_error($conn);
        return $e['message'];
    }
    oci_execute($Fsq_stid);

    $NO_TABFORM='';

    while (oci_fetch_array($Fsq_stid)){
        $NO_TABFORM=ociresult($Fsq_stid,'NO_TABFORM');
    }

    $PAD_NO_TABFORM  = str_pad($NO_TABFORM,10,0,STR_PAD_LEFT);
    $FormSeq=$NO_TABFORM+1;
    $UpTabForm_sql="UPDATE  HIS803.NSTBMF SET  NO_TABFORM=:NO_TAB WHERE ID_TABFORM= 'IOQT'";
    $Up_Stid=oci_parse($conn,$UpTabForm_sql);
    if (!$Up_Stid){
        $e=oci_error($conn);
        return $e['message'];
    }


    oci_bind_by_name($Up_Stid,":NO_TAB",$FormSeq);
    oci_execute($Up_Stid);
    $FrmSeq='IOQT'.$PAD_NO_TABFORM;

    return InsertDB($conn,$DATA,$FrmSeq,$IdPt,$IdinPt,$sDt,$sTm,$ID_BED,$JID_NSRANK,$FormSeq_WT,$NowDT,$sUr);


}
function GetIOAJson($conn,$idPt,$INPt,$sUr,$sDt,$sTm,$sPg,$sFSq){

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

    oci_bind_by_name($stid1,':idPt',$idPt);
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



    $SQL=" SELECT
            DATESEQANCE,CID_IO,JID_IOTYPE,
            CASE SUBSTRING(JID_IOTYPE,-2,2)
            WHEN 'IA' THEN 'A'
            WHEN 'IB' THEN 'B'
            WHEN 'IC' THEN 'C'
            WHEN 'ID' THEN 'D'
            WHEN 'OA' THEN 'E'
            WHEN 'OB' THEN 'F'
            WHEN 'OC' THEN 'G'
            WHEN 'OD' THEN 'H'
            END AS PAGE,
            DB_QUANTITY,ST_LOSS,JID_COLOR,JID_IOWAY,IS_SUMARY,CID_CLASS,JID_KEY,NM_ITEM,MM_IO
            FROM NSIOQA
             WHERE ID_PATIENT=:idPt AND ID_INPATIENT=:INPt
                AND  DT_EXCUTE =:sDt  AND  TM_EXCUTE =:sTm
                AND UR_PROCESS=:sUr
                AND DM_CANCD=' '";


    $stid=oci_parse($conn,$SQL);

    oci_bind_by_name($stid,':idPt',$idPt);
    oci_bind_by_name($stid,':INPt',$INPt);
    oci_bind_by_name($stid,':sDt',$sDt);
    oci_bind_by_name($stid,':sTm',$sTm);
    oci_bind_by_name($stid,':sUr',$sUr);

    oci_execute($stid);
    $DATAA=[];
    $DATAB=[];
    $DATAC=[];
    $DATAD=[];
    $DATAE=[];
    $DATAF=[];
    $DATAG=[];
    $DATAH=[];


    while (oci_fetch_array($stid)){
        $DTSEQ=oci_result($stid,"DATESEQANCE");
        $CID_IO=oci_result($stid,"CID_IO");
        $IOTYPE=oci_result($stid,"JID_IOTYPE");
        $Qty=oci_result($stid,"DB_QUANTITY");
        $LOSS=oci_result($stid,"ST_LOSS");
        $COLOR=oci_result($stid,"JID_COLOR");
        $IOWAY=oci_result($stid,"JID_IOWAY");
        $SUMARY=oci_result($stid,"IS_SUMARY");
        $CID_CLASS=oci_result($stid,"CID_CLASS");
        $JID_KEY=oci_result($stid,"JID_KEY");
        $NM_ITEM=oci_result($stid,"NM_ITEM");
        $MM_IO=oci_result($stid,"MM_IO");
        $PAGE=oci_result($stid,"PAGE");




        $OBJ=array("DataSeq"=>$DTSEQ,"CID_IO"=>$CID_IO,"CID_CLASS"=>$CID_CLASS,
            "COLOR"=>$COLOR,"IO_TYPE"=>$IOTYPE,"IOWAY"=>$IOWAY,"JID_KEY"=>$JID_KEY,"LOSS"=>$LOSS,
            "MM_IO"=>$MM_IO,"M_Nam"=>$NM_ITEM,"QUNTY"=>$Qty,"JID_COLOR"=>Get_Color($conn,$IOTYPE),
            "JID_MM"=>Get_MM($conn,$IOTYPE),"IS_SUM"=>$SUMARY);


        if ($PAGE==="A"){
            array_push($DATAA,$OBJ);
        }
        if ($PAGE==="B"){
            array_push($DATAB,$OBJ);
        }
        if ($PAGE==="C"){
            array_push($DATAC,$OBJ);
        }
        if ($PAGE==="D"){
            array_push($DATAD,$OBJ);
        }
        if ($PAGE==="E"){
            array_push($DATAE,$OBJ);
        }
        if ($PAGE==="F"){
            array_push($DATAF,$OBJ);
        }
        if ($PAGE==="G"){
            array_push($DATAG,$OBJ);
        }
        if ($PAGE==="H"){
            array_push($DATAH,$OBJ);
        }
    }



    $IN_TP="INSERT INTO HIS803.NISWSTP(
                    ID_TABFORM,ID_TRANSACTION,ID_PATIENT,ID_INPATIENT,DT_EXCUTE,TM_EXCUTE,
                    ST_DATAA,ST_DATAB,ST_DATAC,ST_DATAD,ST_DATAE,ST_DATAF,ST_DATAG,ST_DATAH,
                    ID_BED,DM_PROCESS,UR_PROCESS,JID_NSRANK,FORMSEQANCE_WT)
                     VALUES (
                     'IOA',:sTraID,:Idpt,:INPt,:DT,:TM,
                     EMPTY_CLOB(),EMPTY_CLOB(),EMPTY_CLOB(),EMPTY_CLOB(),EMPTY_CLOB(),EMPTY_CLOB(),EMPTY_CLOB(),EMPTY_CLOB(),
                    :BED,:DM_P,:UR_P,:NSRANK,:FormSeq)
                    RETURNING  ST_DATAA,ST_DATAB,ST_DATAC,ST_DATAD,ST_DATAE,ST_DATAF,ST_DATAG,ST_DATAH
                     INTO :ST_DATAA,:ST_DATAB,:ST_DATAC,:ST_DATAD,:ST_DATAE,:ST_DATAF,:ST_DATAG,:ST_DATAH
                     ";

    $TP_Stid = oci_parse($conn, $IN_TP);
    if(!$TP_Stid){
        $e=oci_error($conn);
        return $e['message'];
    }



    oci_bind_by_name($TP_Stid,":sTraID",$sTraID);
    oci_bind_by_name($TP_Stid,":Idpt",$idPt);
    oci_bind_by_name($TP_Stid,":INPt",$INPt);
    oci_bind_by_name($TP_Stid,":DT",$sDt);
    oci_bind_by_name($TP_Stid,":TM",$sTm);

    oci_bind_by_name($TP_Stid,":BED",$ID_BED);
    oci_bind_by_name($TP_Stid,":DM_P",$DM_PR);
    oci_bind_by_name($TP_Stid,":UR_P",$sUr);
    oci_bind_by_name($TP_Stid,":NSRANK",$JID_NSRANK);
    oci_bind_by_name($TP_Stid,":FormSeq",$FORMSEQANCE_WT);







    $clobA=oci_new_descriptor($conn,OCI_D_LOB);
    $clobB=oci_new_descriptor($conn,OCI_D_LOB);
    $clobC=oci_new_descriptor($conn,OCI_D_LOB);
    $clobD=oci_new_descriptor($conn,OCI_D_LOB);
    $clobE=oci_new_descriptor($conn,OCI_D_LOB);
    $clobF=oci_new_descriptor($conn,OCI_D_LOB);
    $clobG=oci_new_descriptor($conn,OCI_D_LOB);
    $clobH=oci_new_descriptor($conn,OCI_D_LOB);


    //I
    oci_bind_by_name($TP_Stid,":ST_DATAA",$clobA,-1,OCI_B_CLOB);
    oci_bind_by_name($TP_Stid,":ST_DATAB",$clobB,-1,OCI_B_CLOB);
    oci_bind_by_name($TP_Stid,":ST_DATAC",$clobC,-1,OCI_B_CLOB);
    oci_bind_by_name($TP_Stid,":ST_DATAD",$clobD,-1,OCI_B_CLOB);

    //O
    oci_bind_by_name($TP_Stid,":ST_DATAE",$clobE,-1,OCI_B_CLOB);
    oci_bind_by_name($TP_Stid,":ST_DATAF",$clobF,-1,OCI_B_CLOB);
    oci_bind_by_name($TP_Stid,":ST_DATAG",$clobG,-1,OCI_B_CLOB);
    oci_bind_by_name($TP_Stid,":ST_DATAH",$clobH,-1,OCI_B_CLOB);


    $result = oci_execute($TP_Stid,OCI_NO_AUTO_COMMIT);
    if(!$result){
        $e=oci_error($TP_Stid);
        return $e['message'];
    }
    $clobA->save(join("",$DATAA));
    $clobB->save(join("",$DATAB));
    $clobC->save(join("",$DATAC));
    $clobD->save(join("",$DATAD));
    $clobE->save(join("",$DATAE));
    $clobF->save(join("",$DATAF));
    $clobG->save(join("",$DATAG));
    $clobH->save(join("",$DATAH));

    oci_free_statement($TP_Stid);
    oci_commit($conn);


    $RE=array("sTraID"=>$sTraID,"IdPt"=>$idPt,"INPt"=>$INPt,"DT_EXCUTE"=>$sDt,"TM_EXCUTE"=>$sTm,"A"=>$DATAA,"B"=>$DATAB,"C"=>$DATAC,"D"=>$DATAD
                ,"E"=>$DATAE,"F"=>$DATAF,"G"=>$DATAG,"H"=>$DATAH);


    return json_encode($RE,JSON_UNESCAPED_UNICODE);
}
function PosIOACancel($conn,$sFm,$sTraID,$sUr){
    $DateTime = date("YmdHis");
    $STR = substr($DateTime, 0, 4);
    $STR1 = substr($DateTime, -10, 10);
    $str = $STR - 1911;
    $NowDT = $str . $STR1;


    $S_SQL="SELECT ID_PATIENT,ID_INPATIENT,DT_EXCUTE,TM_EXCUTE 
            FROM  HIS803.NISWSTP
            WHERE ID_TABFORM = :ID_TABFORM  AND ID_TRANSACTION = :ID_TRANSACTION";

    $stid=oci_parse($conn,$S_SQL);
    oci_bind_by_name($stid,":ID_TABFORM",$sFm);
    oci_bind_by_name($stid,":ID_TRANSACTION",$sTraID);
    oci_execute($stid);
    $IdPt='';
    $INPt='';
    $DT='';
    $TM='';
    while (oci_fetch_array($stid)){
        $IdPt=oci_result($stid,"ID_PATIENT");
        $INPt=oci_result($stid,"ID_INPATIENT");
        $DT=oci_result($stid,"DT_EXCUTE");
        $TM=oci_result($stid,"TM_EXCUTE");
    }

    $UP_SQL=" UPDATE NSIOQA SET DM_CANCD=:C_DT , UR_CANCD=:sUr
              WHERE ID_PATIENT=:IdPt AND ID_INPATIENT=:INPt 
              AND  DT_EXCUTE=:DT AND TM_EXCUTE=:TM";

    $Ustid=oci_parse($conn,$UP_SQL);
    if (!$Ustid){
        $e=oci_error($conn);
        return $json_reponce=json_encode(array("message"=>$e['message'],"result"=>"false"));
    }

    oci_bind_by_name($Ustid,":C_DT",$NowDT);
    oci_bind_by_name($Ustid,":sUr",$sUr);
    oci_bind_by_name($Ustid,":IdPt",$IdPt);
    oci_bind_by_name($Ustid,":INPt",$INPt);
    oci_bind_by_name($Ustid,":DT",$DT);
    oci_bind_by_name($Ustid,":TM",$TM);

    $UP_re=oci_execute($Ustid,OCI_NO_AUTO_COMMIT);
    if(!$UP_re){
        $e=oci_error($Ustid);
        $json_reponce=json_encode(array("message"=>$e['message'],"result"=>"false"));
        oci_rollback($conn);
        return $json_reponce;
    }
    oci_commit($conn);

    $json_reponce=json_encode(array("message"=>"success","result"=>"true"));
    return $json_reponce;
}
function GetIOACheck($sTraID,$sPg){

}
function InsertDB($conn,$arr,$FrmSeq,$IdPt,$IdinPt,$sDt,$sTm,$ID_BED,$JID_NSRANK,$FormSeq_WT,$NowDT,$UR_PROCESS){


    $sTm=str_pad($sTm,6,"0",STR_PAD_RIGHT);
    $response="";

    for ($i=0;$i<count($arr);$i++){
        $len=strrpos(urldecode($arr[$i]),'}]');
        $Obj=json_decode(substr(urldecode($arr[$i]),0,$len+2));

        if(is_array($Obj)){

            $count = count($Obj);

        } else {

            $count = 0;

        }
        for ($j=0;$j<$count;$j++){
            $M_Nam=$Obj[$j]->{'M_Nam'};
            $JID_KEY=$Obj[$j]->{'JID_KEY'}==""?" ":$Obj[$j]->{'JID_KEY'};
            $CID_CLASS=$Obj[$j]->{'CID_CLASS'}==""?" ":$Obj[$j]->{'CID_CLASS'};
            $Cid_io=$Obj[$j]->{'CID_IO'};
            $IoType=$Obj[$j]->{'IO_TYPE'}==""?" ":$Obj[$j]->{'IO_TYPE'};
            $Quantity=$Obj[$j]->{'QUNTY'}==""?" ":$Obj[$j]->{'QUNTY'};
            $Loss=$Obj[$j]->{'LOSS'}==""?" ":$Obj[$j]->{'LOSS'};
            $Color=$Obj[$j]->{'COLOR'}==""?" ":$Obj[$j]->{'COLOR'};
            $IoWay=$Obj[$j]->{'IOWAY'}==""?" ":$Obj[$j]->{'IOWAY'};
            $MM_IO=$Obj[$j]->{'MM_IO'}==""?" ":$Obj[$j]->{'MM_IO'};
            $Is_Sum=$Obj[$j]->{'IS_SUM'}==""?" ":$Obj[$j]->{'IS_SUM'};
            $DataSeq=$Obj[$j]->{'DataSeq'};

            if (trim($DataSeq)!==""){
                DBDEL($conn,$DataSeq,$IdPt,$IdinPt,$sDt,$sTm,$NowDT,$UR_PROCESS);
            }

            if (trim($Quantity)!=="" || trim($Loss)!==""){

                if (trim($Loss)!=="" && trim($Quantity)===""){
                    $Quantity="-1";
                }
                $In_Sql="INSERT INTO  NSIOQA(DATESEQANCE,FORMSEQANCE,ID_PATIENT,ID_INPATIENT,NO_OPDSEQ,DT_EXCUTE,TM_EXCUTE,
                                            CID_IO,JID_IOTYPE,DB_QUANTITY,ST_LOSS,DB_CALORIE,DB_REMAIN,JID_COLOR,
                                            JID_IOWAY,TM_START,TM_END,IS_SUMARY,CID_CLASS,JID_KEY,NM_ITEM,ST_KEYSYSTEM,
                                            MM_IO,ID_BED,JID_NSRANK,FORMSEQANCE_WT,FORMSEQANCE_FL,DM_PROCESS,
                                            UR_PROCESS,DM_CANCD,UR_CANCD,ID_FROMSYS)
                                 VALUES(NIS_DATETIMESEQ,'$FrmSeq','$IdPt','$IdinPt','0','$sDt','$sTm',
                                            '$Cid_io','$IoType','$Quantity','$Loss','-1','-1','$Color',
                                            '$IoWay',' ',' ','$Is_Sum','$CID_CLASS','$JID_KEY','$M_Nam',' ',
                                            '$MM_IO','$ID_BED','$JID_NSRANK','$FormSeq_WT','$FrmSeq','$NowDT',
                                            '$UR_PROCESS',' ',' ','TEST')";

                $stid=oci_parse($conn,$In_Sql);
                if (!$stid){
                    $e=oci_error($conn);
                    $response=json_encode(array("response" => "false","message" =>$e['message']),JSON_UNESCAPED_UNICODE);
                    return $response;
                }

                $excute=oci_execute($stid,OCI_NO_AUTO_COMMIT);
                if (!$excute){
                    $e=oci_error($stid);
                    $response=json_encode(array("response" => "false","message" =>$e['message']),JSON_UNESCAPED_UNICODE);
                    oci_rollback($conn);
                    return $response;
                }
                $commit=oci_commit($conn);
                if(!$commit){
                    $e=oci_error($conn);
                    $response=json_encode(array("response" => "false","message" =>$e['message']),JSON_UNESCAPED_UNICODE);
                    return $response;
                }else{
                    $response=json_encode(array("response" => "success","message" =>"this is the success message"),JSON_UNESCAPED_UNICODE);
                }
            }

        }
    }
    return  $response;
}
function DBDEL($conn,$DtSeq,$IdPt,$InPt,$DT,$TM,$DM_Cand,$UR_Cand){


    $UP_SQL=" UPDATE NSIOQA SET DM_CANCD=:CAN_DT , UR_CANCD=:CAN_UR
              WHERE DATESEQANCE=:DtSeq AND 
              ID_PATIENT=:IdPt AND ID_INPATIENT=:InPt 
              AND  DT_EXCUTE=:DT AND TM_EXCUTE=:TM";
    $UP_Stid=oci_parse($conn,$UP_SQL);

    oci_bind_by_name($UP_Stid,":CAN_DT",$DM_Cand);
    oci_bind_by_name($UP_Stid,":CAN_UR",$UR_Cand);
    oci_bind_by_name($UP_Stid,":DtSeq",$DtSeq);
    oci_bind_by_name($UP_Stid,":IdPt",$IdPt);
    oci_bind_by_name($UP_Stid,":InPt",$InPt);
    oci_bind_by_name($UP_Stid,":DT",$DT);
    oci_bind_by_name($UP_Stid,":TM",$TM);


    if(!$UP_Stid){
        $e=oci_error($conn);
        return $e['message'];
    }
    $UP_re=oci_execute($UP_Stid);
    if(!$UP_re){
        $e=oci_error($UP_Stid);
        return $e['message'];
    }

    return true;
}

function Get_Color($conn,$Io_Type){
    $Sql="SELECT SI2.JID_KEY,SI2.NM_ITEM
                FROM
                (SELECT * FROM NSCLSI  WHERE  CID_CLASS ='IOTP') SI1,
                (SELECT * FROM NSCLSI  WHERE  CID_CLASS ='IOCL' AND ST_TEXT2=:IoType) SI2
                WHERE SI1.jid_key = SI2.st_text2";

    $Stid=oci_parse($conn,$Sql);

    oci_bind_by_name($Stid,":IoType",$Io_Type);
    oci_execute($Stid);
    $Color=[];
    while (oci_fetch_array($Stid)){
        $Color_JID_KEY=oci_result($Stid,"JID_KEY");
        $Color_ITEM=oci_result($Stid,"NM_ITEM");
        $Color[]=array("JID_KEY"=>$Color_JID_KEY,"NM_ITEM"=>$Color_ITEM);
    }
    return $Color;
}

function Get_MM($conn,$Io_Type){
    $Sql="SELECT SI2.JID_KEY,SI2.NM_ITEM from
              (SELECT * from NSCLSI  WHERE  CID_CLASS ='IOTP') SI1,
              (SELECT * from NSCLSI  WHERE  CID_CLASS ='IOWY' AND ST_TEXT2=:IoType) SI2
              WHERE SI1.jid_key = SI2.st_text2";

    $Stid=oci_parse($conn,$Sql);

    oci_bind_by_name($Stid,":IoType",$Io_Type);
    oci_execute($Stid);
    $MM=[];
    while (oci_fetch_array($Stid)){
        $MM_JID_KEY=oci_result($Stid,"JID_KEY");
        $NM_ITEM=oci_result($Stid,"NM_ITEM");
        $MM[]=array("JID_KEY"=>$MM_JID_KEY,"NM_ITEM"=>$NM_ITEM);
    }
    return $MM;


}

function Is_Sum($conn,$Io_Type){
    $Sql="SELECT ST_TEXT2 FROM NSCLSI  WHERE  CID_CLASS ='IOTP' AND JID_KEY=:IoType";

    $Stid=oci_parse($conn,$Sql);

    oci_bind_by_name($Stid,":IoType",$Io_Type);
    oci_execute($Stid);
    $Is_Sum="";
    while (oci_fetch_array($Stid)){
        $Is_Sum=oci_result($Stid,"ST_TEXT2");
    }
    return $Is_Sum;
}

function GetDeFaultArray($Page,$arr){
    $Nm_arr=[];
    $IoType_arr=[];

    $Result=[];

    if ($Page==="B"){
        array_push($Nm_arr,'開水','牛奶','管灌食物');
        array_push($IoType_arr,'IOTP000000IF','IOTP000000IB','IOTP000000IB');
    }
    if ($Page==="E"){
        array_push($Nm_arr,'尿量','嘔吐','洗腎脫水量','腹膜透析量');
        array_push($IoType_arr,'IOTP000000OA','IOTP000000OD','IOTP000000OE','IOTP000000OF');
    }
    if ($Page==="F"){
        array_push($Nm_arr,'排便量');
        array_push($IoType_arr,'IOTP000000OB');
    }
    if ($Page==="H"){
        array_push($Nm_arr,'Irrigation');
        array_push($IoType_arr,'');
    }
    for ($i=0;$i<count($Nm_arr);$i++){
        $arr[$i]->M_Nam=$Nm_arr[$i];
        $arr[$i]->CID_CLASS='';
        $arr[$i]->JID_KEY='';
        $arr[$i]->IO_TYPE=$IoType_arr[$i];
        array_push($Result,$arr[$i]);
    }

    return $Result;
}

function ObjectMap($conn,$arr){
    $len= count($arr)===0?1:count($arr);

    for ($i=0;$i<$len;$i++){
        if (count($arr)===0){
            $arr[$i]->M_Nam="";
            $arr[$i]->CID_CLASS="";
            $arr[$i]->JID_KEY="";
        }

        $arr[$i]->DataSeq="";
        $arr[$i]->QUNTY="";
        $arr[$i]->LOSS="";
        $arr[$i]->COLOR="";
        $arr[$i]->IOWAY="";
        $arr[$i]->MM_IO="";
        $arr[$i]->CID_IO='';
        $arr[$i]->JID_MM=Get_MM($conn,$arr[$i]->IO_TYPE);
        $arr[$i]->JID_COLOR=Get_Color($conn,$arr[$i]->IO_TYPE);
        $arr[$i]->IS_SUM=Is_Sum($conn,$arr[$i]->IO_TYPE);

    }
    return $arr;
}