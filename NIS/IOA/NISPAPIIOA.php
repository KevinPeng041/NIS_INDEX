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

        $ST_DATAA='';
        $ST_DATAB='';
        $ST_DATAC='';
        $ST_DATAD='';
        $ST_DATAE='';
        $ST_DATAF='';
        $ST_DATAG='';
        $ST_DATAH='';

        while (oci_fetch_array($IT_stid)){
            $ST_DATAA=oci_result($IT_stid,'ST_DATAA')->load();
            $ST_DATAB=oci_result($IT_stid,'ST_DATAB')->load();
            $ST_DATAC=oci_result($IT_stid,'ST_DATAC')->load();
            $ST_DATAD=oci_result($IT_stid,'ST_DATAD')->load();
            $ST_DATAE=oci_result($IT_stid,'ST_DATAE')->load();
            $ST_DATAF=oci_result($IT_stid,'ST_DATAF')->load();
            $ST_DATAG=oci_result($IT_stid,'ST_DATAG')->load();
            $ST_DATAH=oci_result($IT_stid,'ST_DATAH')->load();
        }

    oci_free_statement($IT_stid);

    $DATAA=GetOrderData($conn,'A',$INPt,$date,$ST_DATAA);
    $DATAC=GetOrderData($conn,'C',$INPt,$date,$ST_DATAC);
    $DATAD=GetOrderData($conn,'D',$INPt,$date,$ST_DATAD);
    $DATAG=GetOrderData($conn,'G',$INPt,$date,$ST_DATAG);

    $DATAB=GetOrderData($conn,'B',$date,$INPt,json_decode($ST_DATAB));
    $DATAE=GetOrderData($conn,'E',$date,$INPt,json_decode($ST_DATAE));
    $DATAF=GetOrderData($conn,'F',$date,$INPt,json_decode($ST_DATAF));
    $DATAH=GetOrderData($conn,'H',$date,$INPt,json_decode($ST_DATAH));


     $PAGEA=json_encode(ObjectMap($conn,json_decode($DATAA)->{'DATA'}),JSON_UNESCAPED_UNICODE);
     $PAGEB=json_encode(ObjectMap($conn,json_decode($DATAB)->{'DATA'}),JSON_UNESCAPED_UNICODE);
     $PAGEC=json_encode(ObjectMap($conn,json_decode($DATAC)->{'DATA'}),JSON_UNESCAPED_UNICODE);
     $PAGED=json_encode( ObjectMap($conn,json_decode($DATAD)->{'DATA'}),JSON_UNESCAPED_UNICODE);
     $PAGEE=json_encode( ObjectMap($conn,json_decode($DATAE)->{'DATA'}),JSON_UNESCAPED_UNICODE);
     $PAGEF=json_encode( ObjectMap($conn,json_decode($DATAF)->{'DATA'}),JSON_UNESCAPED_UNICODE);
     $PAGEG=json_encode( ObjectMap($conn,json_decode($DATAG)->{'DATA'}),JSON_UNESCAPED_UNICODE);
     $PAGEH=json_encode( ObjectMap($conn,json_decode($DATAH)->{'DATA'}),JSON_UNESCAPED_UNICODE);


    $TP_SQL="INSERT INTO HIS803.NISWSTP(
                               ID_TABFORM,ID_TRANSACTION,ID_PATIENT,ID_INPATIENT,DT_EXCUTE,TM_EXCUTE,
                               ST_DATAA,ST_DATAB,ST_DATAC,ST_DATAD,ST_DATAE,ST_DATAF,ST_DATAG,ST_DATAH,
                               ID_BED,DM_PROCESS,UR_PROCESS,JID_NSRANK,FORMSEQANCE_WT)
                                VALUES (
                                'IOA',:sTraID,:Idpt,:INPt,' ',' ',
                                EMPTY_CLOB(),EMPTY_CLOB(),EMPTY_CLOB(),EMPTY_CLOB(),EMPTY_CLOB(),EMPTY_CLOB(),EMPTY_CLOB(),EMPTY_CLOB(),
                               :BED,:DM_P,:UR_P,:NSRANK,:FormSeq)
                               RETURNING  ST_DATAA,ST_DATAB,ST_DATAC,ST_DATAD,ST_DATAE,ST_DATAF,ST_DATAG,ST_DATAH
                                INTO :ST_DATAA,:ST_DATAB,:ST_DATAC,:ST_DATAD,:ST_DATAE,:ST_DATAF,:ST_DATAG,:ST_DATAH";

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

       //R
       oci_bind_by_name($TP_Stid,":ST_DATAH",$clobH,-1,OCI_B_CLOB);

       $result = oci_execute($TP_Stid,OCI_NO_AUTO_COMMIT);
       if(!$result){
           $e=oci_error($TP_Stid);
           return $e['message'];
       }


        $clobA->save($PAGEA);
        $clobB->save($PAGEB);
        $clobC->save($PAGEC);
        $clobD->save($PAGED);
        $clobE->save($PAGEE);
        $clobF->save($PAGEF);
        $clobG->save($PAGEG);
        $clobH->save($PAGEH);


        $Orader_json=array(
               "A"=>ObjectMap($conn, json_decode($DATAA)->{'ORDER'}),
               "B"=>ObjectMap($conn, json_decode($DATAB)->{'ORDER'}),
               "C"=>ObjectMap($conn, json_decode($DATAC)->{'ORDER'}),
               "D"=>ObjectMap($conn, json_decode($DATAD)->{'ORDER'}),
               "E"=>ObjectMap($conn, json_decode($DATAE)->{'ORDER'}),
               "F"=>ObjectMap($conn, json_decode($DATAF)->{'ORDER'}),
               "G"=>ObjectMap($conn, json_decode($DATAG)->{'ORDER'})
               );

    oci_free_statement($TP_Stid);
    oci_commit($conn);

    $JsonBack=array('sTraID' => $sTraID, 'sSave' => $sSave,'FORMSEQANCE_WT'=>$FORMSEQANCE_WT,
                "JID_NSRANK"=>$JID_NSRANK,"ORDER"=>$Orader_json,"P_H"=>ObjectMap($conn, json_decode($DATAH)->{'DATA'})
            );
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
    return $DATA;
}
function PosIOASave($conn,$sTraID,$sFm,$sDt,$sTm,$sUr){

    $DateTime = date("YmdHis");
    $STR = substr($DateTime, 0, 4);
    $STR1 = substr($DateTime, -10, 10);
    $str = (int)$STR - 1911;
    $NowDT= (string)$str .(string)$STR1;

    $sTm=str_pad($sTm,6,"0",STR_PAD_RIGHT);


    $UPTMSQL="UPDATE HIS803.NISWSTP SET TM_EXCUTE=:TM,DT_EXCUTE=:DT  WHERE ID_TRANSACTION=:id_TRAN";
    $upstid=oci_parse($conn,$UPTMSQL);
    oci_bind_by_name($upstid,":TM",$sTm);
    oci_bind_by_name($upstid,":DT",$sDt);
    oci_bind_by_name($upstid,":id_TRAN",$sTraID);

    oci_execute($upstid);
    oci_free_statement($upstid);



    $Ssql=" SELECT ID_INPATIENT,ID_PATIENT,
            ST_DATAA,ST_DATAB,ST_DATAC,ST_DATAD,ST_DATAE,ST_DATAF,ST_DATAG,ST_DATAH,
            ID_BED,JID_NSRANK,FORMSEQANCE_WT
            FROM HIS803.NISWSTP 
             WHERE ID_TABFORM = 'IOA'
            AND ID_TRANSACTION='$sTraID'
           ";

    $Ssql_stid=oci_parse($conn,$Ssql);
    oci_execute($Ssql_stid);
    $DATA=[];
    $IdinPt='';
    $IdPt='';
    $ID_BED='';
    $JID_NSRANK='';
    $FormSeq_WT='';
    while (($row=oci_fetch_array($Ssql_stid,OCI_ASSOC+OCI_RETURN_LOBS)) != false){

        $IdinPt=$row['ID_INPATIENT'];
        $IdPt=$row['ID_PATIENT'];

        $ST_DATAA=$row['ST_DATAA'];
        $ST_DATAB=$row['ST_DATAB'];
        $ST_DATAC=$row['ST_DATAC'];
        $ST_DATAD=$row['ST_DATAD'];
        $ST_DATAE=$row['ST_DATAE'];
        $ST_DATAF=$row['ST_DATAF'];
        $ST_DATAG=$row['ST_DATAG'];
        $ST_DATAH=$row['ST_DATAH'];

        $ID_BED=$row['ID_BED'];
        $JID_NSRANK=$row['JID_NSRANK'];
        $FormSeq_WT=$row['FORMSEQANCE_WT'];

        array_push($DATA, $ST_DATAA,$ST_DATAB,$ST_DATAC,$ST_DATAD,$ST_DATAE,$ST_DATAF,$ST_DATAG,$ST_DATAH);
    }
    oci_free_statement($Ssql_stid);


    $StringMapToArr=array_map('Big5toStr',$DATA);
    $FrmSeq=GetFrmSeq($conn,'IOQT');
    if (trim($sFm))
    {
        //1090208 存檔判斷單號是否已存檔過
        DB_UPDATE($conn,trim($sFm),$IdPt,$IdinPt,$NowDT,$sUr);
    }



    return InsertDB($conn,$StringMapToArr,$FrmSeq,$IdPt,$IdinPt,$sDt,$sTm,$ID_BED,$JID_NSRANK,$FormSeq_WT,$NowDT,$sUr);



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

    $SQL="SELECT DISTINCT  QA.DATESEQANCE,QA.FORMSEQANCE,QA.CID_IO,QA.JID_IOTYPE, QA.DB_QUANTITY,
            QA.ST_LOSS,QA.JID_COLOR,QA.JID_IOWAY,QA.IS_SUMARY,
            QA.CID_CLASS,QA.JID_KEY,
           CASE  QA.NM_ITEM
           WHEN ' ' THEN (SELECT NM_ITEM  FROM NSCLSI where jid_key=QA.JID_IOTYPE)
           ELSE  QA.NM_ITEM END NM_ITEM,
            QA.MM_IO,
             CASE DB_UP
             WHEN 1 THEN 'A' WHEN 2 THEN 'B' WHEN 3 THEN 'C' WHEN 4 THEN 'D' WHEN 5 THEN 'E'
             WHEN 6 THEN 'F' WHEN 7 THEN 'G' WHEN 8 THEN 'F' WHEN 9 THEN 'H'
             else to_char( DB_UP)
             END PAGE
             FROM NSIOQA QA,NSCLSI SI
             WHERE QA.ID_PATIENT='$idPt' AND QA.ID_INPATIENT='$INPt'
            AND  QA.DT_EXCUTE ='$sDt'  AND  QA.TM_EXCUTE ='$sTm'
            AND QA.UR_PROCESS='$sUr'
             AND SI.CID_CLASS = 'IOTP' AND IS_ACTIVE = 'Y'
             AND            
              ( ( SI.CID_CLASS='IOTP' AND SI.ST_TEXT1='S' AND SI.ST_TEXT1=QA.CID_IO  AND(QA.JID_IOTYPE=SI.JID_KEY OR QA.JID_IOTYPE=' ') ) 
              OR
              ( SI.CID_CLASS='IOTP' AND SI.ST_TEXT1 <>'S' AND QA.JID_IOTYPE=SI.JID_KEY))
            AND QA.DM_CANCD=' '
         ";



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
    $FrmSeq="";

    while (oci_fetch_array($stid)){
        $DTSEQ=oci_result($stid,"DATESEQANCE");
        $FrmSeq=oci_result($stid,"FORMSEQANCE");
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





        $OBJ=array("M_Nam"=>$NM_ITEM,
            "IO_TYPE"=>$IOTYPE,
            "JID_KEY"=>$JID_KEY,
            "CID_CLASS"=>$CID_CLASS,
            "DATASEQ"=>$DTSEQ,
            "QUNTY"=>$Qty,
            "LOSS"=>$LOSS,
            "COLOR"=>$COLOR,
            "IOWAY"=>$IOWAY,
            "MM_IO"=>$MM_IO,
            "CID_IO"=>$CID_IO,
            "JID_MM"=>Get_MM($conn,$IOTYPE),
            "JID_COLOR"=>Get_Color($conn,$IOTYPE),
            "IS_SUM"=>$SUMARY);


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
            $OBJ['M_Nam']='Irrigation';
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

    $clobA->save(json_encode($DATAA,JSON_UNESCAPED_UNICODE));
    $clobB->save(json_encode($DATAB,JSON_UNESCAPED_UNICODE));
    $clobC->save(json_encode($DATAC,JSON_UNESCAPED_UNICODE));
    $clobD->save(json_encode($DATAD,JSON_UNESCAPED_UNICODE));
    $clobE->save(json_encode($DATAE,JSON_UNESCAPED_UNICODE));
    $clobF->save(json_encode($DATAF,JSON_UNESCAPED_UNICODE));
    $clobG->save(json_encode($DATAG,JSON_UNESCAPED_UNICODE));
    $clobH->save(json_encode($DATAH,JSON_UNESCAPED_UNICODE));

    oci_free_statement($TP_Stid);
    oci_commit($conn);


    $RE=array("sTraID"=>$sTraID,"FORMSEQ"=>$FrmSeq,"IdPt"=>$idPt,"INPt"=>$INPt,"DT_EXCUTE"=>$sDt,"TM_EXCUTE"=>$sTm
                ,"A"=>$DATAA,"B"=>$DATAB,"C"=>$DATAC,"D"=>$DATAD
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
function GetIOACheck($Arr){





}

function InsertDB($conn,$arr,$FrmSeq,$IdPt,$IdinPt,$sDt,$sTm,$ID_BED,$JID_NSRANK,$FormSeq_WT,$NowDT,$UR_PROCESS){

   $Arr_Decode= array_map(function ($value){
       $obj=json_decode($value);

       return  array_filter($obj,function ($Key){
        return $Key->{'QUNTY'}!="" ;
       });


    },$arr);

    $ASCii_Num=65; //ASCii A
    $length=count($Arr_Decode);


    for ($i=0;$i<$length;$i++){
        $ID_FROMSYS="RWD".chr((string)$ASCii_Num);
        $OBJ=$Arr_Decode[$i];
        $OBJ_length=count($OBJ);

        for ($j=0;$j<$OBJ_length;$j++){
            $M_Nam=$OBJ[$j]->{'M_Nam'}==""?" ":$OBJ[$j]->{'M_Nam'};
            $JID_KEY=$OBJ[$j]->{'JID_KEY'}==""?" ":$OBJ[$j]->{'JID_KEY'};
            $CID_CLASS=$OBJ[$j]->{'CID_CLASS'}==""?" ":$OBJ[$j]->{'CID_CLASS'};
            $Cid_io=$OBJ[$j]->{'CID_IO'};
            $IoType=$OBJ[$j]->{'IO_TYPE'}==""?" ":$OBJ[$j]->{'IO_TYPE'};
            $Quantity=$OBJ[$j]->{'QUNTY'}==""?" ":$OBJ[$j]->{'QUNTY'};
            $Loss=$OBJ[$j]->{'LOSS'}==""?" ":$OBJ[$j]->{'LOSS'};
            $Color=$OBJ[$j]->{'COLOR'}==""?" ":$OBJ[$j]->{'COLOR'};
            $IoWay=$OBJ[$j]->{'IOWAY'}==""?" ":$OBJ[$j]->{'IOWAY'};
            $MM_IO=$OBJ[$j]->{'MM_IO'}==""?" ":$OBJ[$j]->{'MM_IO'};
            $Is_Sum=$OBJ[$j]->{'IS_SUM'}==""?" ":$OBJ[$j]->{'IS_SUM'};
            $DataSeq=$OBJ[$j]->{'DATASEQ'};

            if (trim($Loss)!=="" && trim($Quantity)==="")
            {
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
                                                                   '$UR_PROCESS',' ',' ','$ID_FROMSYS')";

            $IN_Stid=oci_parse($conn,$In_Sql);
            if (!$IN_Stid){
                $e=oci_error($conn);
                return json_encode(array("response" => "Error","message" => $e['message']),JSON_UNESCAPED_UNICODE);
                break;
            }
            $Execute= oci_execute($IN_Stid,OCI_NO_AUTO_COMMIT);
            if (!$Execute){
                $e=oci_error($IN_Stid);
                return json_encode(array("response" => "Error","message" => $e['message']),JSON_UNESCAPED_UNICODE);
                break;
            }
        }
        $ASCii_Num++;

    }


    oci_commit($conn);
    $response=json_encode(array("response" => "success","message" =>"this is the success message"),JSON_UNESCAPED_UNICODE);
    return  $response;

}
function DB_UPDATE($conn,$sFm,$IdPt,$InPt,$DM_Cand,$UR_Cand){

    $UP_SQL=" UPDATE NSIOQA
              SET DM_CANCD=:CAN_DT , UR_CANCD=:CAN_UR
              WHERE FORMSEQANCE=:sFm AND 
              ID_PATIENT=:IdPt AND ID_INPATIENT=:InPt 
            ";
    $UP_Stid=oci_parse($conn,$UP_SQL);

    oci_bind_by_name($UP_Stid,":CAN_DT",$DM_Cand);
    oci_bind_by_name($UP_Stid,":CAN_UR",$UR_Cand);
    oci_bind_by_name($UP_Stid,":sFm",$sFm);
    oci_bind_by_name($UP_Stid,":IdPt",$IdPt);
    oci_bind_by_name($UP_Stid,":InPt",$InPt);

    oci_execute($UP_Stid);

}
function GetFrmSeq($conn,$Tag){
    $FormSeq_SQL="SELECT NO_TABFORM FROM  HIS803.NSTBMF  WHERE ID_TABFORM= 'IOQT'";
    $Fsq_stid=oci_parse($conn,$FormSeq_SQL);
    if (!$Fsq_stid){
        $e=oci_error($conn);
        return $e['message'];
    }

    oci_execute($Fsq_stid);
    $NO_TABFORM='';

    while (oci_fetch_array($Fsq_stid)){
        $NO_TABFORM=oci_result($Fsq_stid,'NO_TABFORM');
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
    oci_free_statement($Fsq_stid);
    return $Tag.$PAD_NO_TABFORM;
}

function ObjectMap($conn,$arr){

    $len= count($arr);

    for ($i=0;$i<$len;$i++){
        $arr[$i]->JID_MM=Get_MM($conn,$arr[$i]->IO_TYPE);
        $arr[$i]->JID_COLOR=Get_Color($conn,$arr[$i]->IO_TYPE);
        $arr[$i]->IS_SUM=Is_Sum($conn,$arr[$i]->IO_TYPE);
    }
    return $arr;
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

/*Default*/
function GetOrderData($conn,$Page,$INPt,$sDT,$DefaultArr){
    $SQL_Query="";

    $result=[];
    $IO_TYPE="IOTP000000";
    $ID_TM="";
    switch ($Page){
        case "A":
            $SQL_Query=" SELECT DISTINCT CID_CLASS, JID_KEY, NM_ITEM  FROM NIS_V_HORD_QI
                WHERE ID_INPATIENT = '$INPt' AND DT_BEGIN <= '$sDT'
                AND (DT_DC = ' ' Or DT_DC >= '$sDT')
                ORDER by NM_ITEM ";
            $ID_TM="IA";
            break;
        case "B":

            $SQL_Query=" SELECT DISTINCT  CID_CLASS, JID_KEY, NM_ITEM  FROM NSCLSI
                        WHERE CID_CLASS = 'IOIT'  AND IS_ACTIVE = 'Y' 
                        ORDER BY NM_ITEM";


            $ID_TM='IB';
            break;
        case "C":
            $SQL_Query="SELECT DISTINCT CID_CLASS, JID_KEY, NM_ITEM FROM NIS_V_HORD_QB
                WHERE ID_INPATIENT = '$INPt'
                AND DT_BEGIN <= '$sDT' AND (DT_DC = ' ' Or DT_DC >= '$sDT')
                ORDER by NM_ITEM";
            $ID_TM="IC";
            break;
        case "D":
            $SQL_Query="SELECT DISTINCT CID_CLASS, JID_KEY, NM_ITEM FROM NIS_V_HORD_QIC
                WHERE ID_INPATIENT = '$INPt' AND DT_BEGIN <= '$sDT' AND (DT_DC = ' ' OR DT_DC >= '$sDT')
                AND JID_KEY IN (SELECT ID_ITEM FROM NSCLSI WHERE CID_CLASS = 'TPPN' AND IS_ACTIVE = 'Y')
                ORDER BY NM_ITEM";
            $ID_TM="ID";
            break;
        case "E":
            $SQL_Query="SELECT DISTINCT CID_CLASS, JID_KEY, NM_ITEM  FROM NSCLSI
                        WHERE CID_CLASS = 'IOTP'  AND IS_ACTIVE = 'Y'  AND ST_TEXT1='O'
                        AND (ID_ITEM<>'OB' AND ID_ITEM<>'OC')
                        ORDER BY NM_ITEM";

            $ID_TM='OA';
            break;
        case "F":
            $SQL_Query="SELECT  DISTINCT  CID_CLASS, JID_KEY, NM_ITEM  FROM NSCLSI
                        WHERE CID_CLASS = 'IOTP'  AND IS_ACTIVE = 'Y'  AND ST_TEXT1='O'
                         AND (ID_ITEM='OB' or  ID_ITEM='OC' or ID_ITEM='OG')
                        ORDER BY NM_ITEM";

            $ID_TM='OB';
            break;
        case "G":
            $SQL_Query="SELECT DISTINCT CID_CLASS, JID_KEY, NM_TUBE || '(' || NM_ORGAN || ')' AS NM_ITEM, NO_PROBLEM
                FROM NIS_V_TUPG_QO WHERE ID_INPATIENT = '$INPt'
                AND DT_EXECUTE <= '$sDT'
                AND (DT_ENDING >= ' ' OR DT_ENDING = ' ')
                AND IS_IO = 'Y'
                ORDER BY NO_PROBLEM";
            $ID_TM="OC";
            break;
        default:
            $result=$DefaultArr;
            break;
    }


    if ($Page!=="H"){

            $Stid=oci_parse($conn,$SQL_Query);
            oci_execute($Stid);
            while (oci_fetch_array($Stid)){
                $NM_ITEM=oci_result($Stid,"NM_ITEM");
                $JID_KEY=oci_result($Stid,"JID_KEY");
                $CID_CLASS=oci_result($Stid,"CID_CLASS");
                $result[]=array("M_Nam"=>$NM_ITEM,"IO_TYPE"=>$IO_TYPE.$ID_TM,"JID_KEY"=>$JID_KEY,"CID_CLASS"=>$CID_CLASS
                ,"DATASEQ"=>"","QUNTY"=>"","LOSS"=>"","COLOR"=>"","IOWAY"=>"","MM_IO"=>"","CID_IO"=>"");
            }

            if (count($result)===0){
                $result=json_decode($DefaultArr);
            }

            $DATA=$Page=="B"||$Page=="E"||$Page=="F"?$DefaultArr:$result;

            $Data=array("DATA"=>$DATA,"ORDER"=>$result);
    }else{
            $Data=array("DATA"=>$result,"ORDER"=>[]);
    }

    return json_encode($Data,JSON_UNESCAPED_UNICODE);
}

function Big5toStr($str){
    //找出最後的 }] 計算總長度轉換文字

  return  substr(urldecode($str),0,(int)strripos(urldecode($str),"}]")+2);
}

/*********************************************************************************************************************/
