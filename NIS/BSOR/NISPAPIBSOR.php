<?php

function GetBSORIniJson($conn,$sFm,$Idpt,$INPt,$ID_BED,$sTraID,$sSave,$date,$sUr,$JID_NSRANK,$FORMSEQANCE_WT){

    $SQL="SELECT ST_DATAA,ST_DATAB,ST_PREA,ST_PREB,ST_PREC,ST_PRED FROM NISWSIT WHERE ID_TABFORM='$sFm'";

    $stid=oci_parse($conn,$SQL);
    oci_execute($stid);
    $arr='';
    $ST_DATAA='';
    $ST_DATAB="";
    while (oci_fetch_array($stid)){
        $ST_DATAA=oci_result($stid,"ST_DATAA")->load();
        $ST_DATAB=oci_result($stid,"ST_DATAB")->load();
        $MM_TEXT=oci_result($stid,"ST_PREA")->load();
        $Tittle_Nm=oci_result($stid,"ST_PREB")->load();
        $Tittle_CNm=oci_result($stid,"ST_PREC")->load();
        $Data_Edit=oci_result($stid,"ST_PRED")->load();
        $arr=array(
            "MM_TEXT"=>json_decode($MM_TEXT),
            "T_NM"=>json_decode($Tittle_Nm),
            "T_CNM"=>GetStationOrder($conn,$Tittle_CNm),
            "D_EDIT"=>json_decode($Data_Edit)
        );
    }





   $isChange=ChangeChr($conn)!="Y"?"N":"Y";
    $arr['IS_CHANGE']=$isChange;
    $arr['sSave']=$sSave;
    $arr['sTraID']=$sTraID;
    $arr['ST_DATAB']=json_decode($ST_DATAB);
    $arr['MAXNUM']= MaxNumber($conn,$sFm,$Idpt,$INPt);

    $DATA=GetNoRegion($conn,$ST_DATAA,$ST_DATAB,$Idpt,$INPt,'B');

   if (!InsertTP($conn,$sFm,$sTraID,$DATA,$Idpt,$INPt,' ',' ',$ID_BED,$date,$sUr,$JID_NSRANK,$FORMSEQANCE_WT)){
        return false;
    }


    if ( $isChange!="Y"){
        return json_encode($arr,JSON_UNESCAPED_UNICODE);
    }

    return  str_replace("壓瘡","壓傷",json_encode($arr,JSON_UNESCAPED_UNICODE));
}
function GetBSORPageJson($conn,$sFm,$sPg,$sTraID){
    $TP_SQL="SELECT ST_DATA".$sPg." FROM HIS803.NISWSTP WHERE ID_TRANSACTION=:sTraID AND ID_TABFORM = '$sFm'";
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

function PosBSORSave($conn,$sTraID,$sFm,$sDt,$sTm,$sUr){
    $DateTime = date("YmdHis");
    $Y_VID = substr($DateTime, 0, 4);
    $Date = substr($DateTime, -10, 10);
    $Y_TW = (int)$Y_VID - 1911;
    $System_DT= (string)$Y_TW .(string)$Date;

    $sTm=str_pad($sTm,6,"0",STR_PAD_RIGHT);

    $UPTMSQL="UPDATE HIS803.NISWSTP
              SET TM_EXCUTE=:TM,
                  DT_EXCUTE=:DT,
              WHERE ID_TRANSACTION=:id_TRAN";
    $upstid=oci_parse($conn,$UPTMSQL);



    oci_bind_by_name($upstid,":TM",$sTm);
    oci_bind_by_name($upstid,":DT",$sDt);
    oci_bind_by_name($upstid,":id_TRAN",$sTraID);
    oci_execute($upstid,OCI_NO_AUTO_COMMIT);
    oci_free_statement($upstid);


    $Ssql=" SELECT ID_INPATIENT,ID_PATIENT, ST_DATAA,ST_DATAB,ST_DATAC,
             ID_BED,JID_NSRANK,FORMSEQANCE_WT
             FROM HIS803.NISWSTP
              WHERE ID_TABFORM = :ID_TABFORM
             AND ID_TRANSACTION=:sTraID
            ";



    $Ssql_stid=oci_parse($conn,$Ssql);
    oci_bind_by_name($Ssql_stid,":sTraID",$sTraID);
    oci_bind_by_name($Ssql_stid,":ID_TABFORM",$sFm);

    oci_execute($Ssql_stid,OCI_NO_AUTO_COMMIT);
    $IdinPt="";
    $IdPt="";
    $ST_DATAA="";
    $ST_DATAB="";
    $ST_DATAC="";
    $ID_BED="";
    $JID_NSRANK="";
    $FORMSEQANCE_WT="";
    while (($row=oci_fetch_array($Ssql_stid,OCI_ASSOC+OCI_RETURN_LOBS)) != false)
    {
        $IdinPt=$row['ID_INPATIENT'];
        $IdPt=$row['ID_PATIENT'];
        $ST_DATAA=$row['ST_DATAA'];//部位圖座標
        $ST_DATAB=$row['ST_DATAB'];//評估資料
        $ST_DATAC=$row['ST_DATAC'];//部位圖座標(原始值)
        $ID_BED=$row['ID_BED'];
        $JID_NSRANK=$row['JID_NSRANK'];
        $FORMSEQANCE_WT=$row['FORMSEQANCE_WT'];
    }
    $result=array("result"=>"true","message"=>"");
    if ($sFm=="BSOR" || $sFm=="CUTS"){
        $DTSEQ_SFMSEQ=[];
        $obj_A=json_decode($ST_DATAA);
        $obj_B=json_decode($ST_DATAB);
        $Diff_Arr=obj_diff($obj_A,json_decode($ST_DATAC));
        foreach ($obj_A as $value){
            $DATESEQANCE=GetDataSEQ($conn);

            if (trim($value->FRMSEQ)==""){
                $FORMSEQANCE_BS=GetFrmSeq($conn,$sFm);
                $value->FRMSEQ=$FORMSEQANCE_BS;
            }
            else{
                $FORMSEQANCE_BS=$value->FRMSEQ;
            }

            $INSERT_TBBS=InsertTBBS($conn,$DATESEQANCE,$FORMSEQANCE_BS,$sDt,$sTm,$ID_BED,$FORMSEQANCE_WT,$JID_NSRANK,$sUr);
            if ($INSERT_TBBS->result=="false"){
                //break and return error msg
                $result['result']="false";
                $result['message']=$INSERT_TBBS->message;
                return $result;
            }

            $DTSEQandFSEQ=array("DTSEQ"=>$DATESEQANCE,"FMSEQ"=>$FORMSEQANCE_BS);
            array_push($DTSEQ_SFMSEQ,json_encode($DTSEQandFSEQ));
        }

        //與預設值不同則需新增
        if (count($Diff_Arr)>0){
            foreach ($Diff_Arr as $value){
                $FORMSEQ_BS=$value->FRMSEQ;//表單編號

                $filter= array_filter($DTSEQ_SFMSEQ,function ($value)use ($FORMSEQ_BS){
                  return json_decode($value)->FMSEQ==$FORMSEQ_BS;
               });

                $DATESEQ=json_decode(join($filter))->DTSEQ;

                $NO_BEDSORE=$value->NUM; //編號
                $IT_LEFT=$value->LEFT;
                $IT_TOP=$value->TOP;
                $IT_WIDTH=$value->W_TH;
                $IT_HEIGTH=$value->H_TH;


                $GetData=GetNumData(json_decode($ST_DATAB),$NO_BEDSORE);
                $ID_STATION=$GetData->SSTAT;//護理站代碼
                $DT_START=$GetData->DT_START;//開始日期
                $NM_ORGAN=$GetData->TB_DATA->NM_ORGAN->VALUE;//部位名稱
                $TID_SOURCE=$GetData->TB_DATA->TID_SOURCE->VALUE;//發生來源
                $DT_END=$GetData->TB_DATA->ED_DATE->VALUE==""?" ":$GetData->TB_DATA->ED_DATE->VALUE;//結案日期
                $TID_ENDSTATE=$GetData->TB_DATA->ED_TYPE->VALUE==""?" ":$GetData->TB_DATA->ED_TYPE->VALUE;//結案狀態


                $UPDATE_BSOR=BSORCancel($conn,$sFm,$IdPt,$IdinPt,$FORMSEQ_BS,$System_DT,$sUr);

                if ($UPDATE_BSOR->result=="false"){
                    // return error msg
                    $result['result']="false";
                    $result['message']=$UPDATE_BSOR->message;
                    return $result;
                }


                $UPDATE_TBBS=TBBSCancel($conn,$FORMSEQ_BS,$System_DT,$ID_BED,$sUr);

                if ($UPDATE_TBBS->result=="false"){
                    // return error msg
                    $result['result']="false";
                    $result['message']=$UPDATE_TBBS->message;
                    return $result;
                }


                $INSERT_BSOR=InsertBSOR($conn,$sFm,$DATESEQ,$FORMSEQ_BS,$IdPt,$IdinPt,$NO_BEDSORE,$DT_START,$DT_END,$TID_SOURCE,$ID_STATION,$NM_ORGAN,$IT_TOP,$IT_LEFT,$IT_WIDTH,$IT_HEIGTH,$TID_ENDSTATE,$System_DT,$ID_BED,$JID_NSRANK,$FORMSEQANCE_WT,$sUr);
                if ($INSERT_BSOR->result=="false"){
                    //break and return error msg
                    $result['result']="false";
                    $result['message']=$INSERT_BSOR->message;
                   return $result;
                }

            }
        }

        $count=0;
        foreach ($DTSEQ_SFMSEQ as $value){
            $DATESEQANCE=json_decode($value)->DTSEQ;
            $FORMSEQANCE_BS=json_decode($value)->FMSEQ;

            $INSERT_TIBS=InsertTIBS($conn,$obj_B[$count]->TB_DATA,$sFm,$DATESEQANCE,$FORMSEQANCE_BS);
            if ($INSERT_TIBS->result=="false"){
                //break and return error msg
                $result['result']="false";
                $result['message']=$INSERT_TIBS->message;
                return $result;
            }
            $count++;
        }

    }

    return json_encode($result,JSON_UNESCAPED_UNICODE);
}
function GetNumData($Arr,$Num){
    $result="";
   foreach ($Arr as $item){
       if ($item->TB_DATA->NO_NUM->VALUE==$Num){
           $result= $item;
           break;
       }
   }

   return $result;
}
function GetBSORJson($conn,$sFm,$idPt,$INPt,$sUr,$sDt,$sTm,$sPg,$sFSq){

    $SQL="SELECT ST_DATAA,ST_DATAB FROM NISWSIT WHERE ID_TABFORM='BSOR'";
    $stid=oci_parse($conn,$SQL);
    oci_execute($stid);
    $Obj_A="";
    $Obj_B="";
    while (oci_fetch_array($stid)){
        $Obj_A=json_decode(oci_result($stid,'ST_DATAA')->load());
        $Obj_B=json_decode(oci_result($stid,'ST_DATAB')->load());
    }

    $DateTime = date("YmdHis");
    $Y_VID = substr($DateTime, 0, 4);
    $Date=substr($DateTime, 8, 2);

    $Y_TW = (int)$Y_VID - 1911;
    $DM_PROCESS= (string)$Y_TW.substr($DateTime, 4, 4).(string)$Date;


    $data=array("A"=>"","B"=>"");
    $PationData=GetPationData($conn,$idPt,$INPt,$sUr);

    $sTraID=$PationData->STRA_ID;
    $BED=$PationData->BED;
    $FORMSEQANCE_WT=$PationData->FORMSEQANCE_WT;
    $JID_NSRANK=$PationData->JID_NSRANK;
    $sTm=str_pad($sTm,6,'0',STR_PAD_RIGHT);

    if ($sFm=="BSOR" || $sFm=="CUTS"){
        //A:{"NUM":"","LEFT":"","TOP":"","W_TH":"","H_TH":"","FRMSEQ":""}
        $SQL="SELECT DISTINCT R.FORMSEQANCE_BS,R.NO_BEDSORE,R.DT_START,R.NM_ORGAN,
                 R.IT_TOP,R.IT_LEFT,R.IT_HEIGTH,R.IT_WIDTH
                 FROM NSBSOR R ,NSTBBS B
                 WHERE 
                 R.ID_PATIENT='$idPt' AND R.ID_INPATIENT='$INPt'
                 AND  B.DT_EXCUTE='$sDt' AND B.TM_EXCUTE='$sTm'
                 AND R.CID_BEDSORE='B'
                 AND R.FORMSEQANCE_BS=B.FORMSEQANCE_BS
                 AND R.DM_CANCD=' '";



        $stid=oci_parse($conn,$SQL);
        oci_execute($stid);
        $PageA_arr=[];
        $PageB_arr=[];

        while (oci_fetch_array($stid)){
            $FreSeq=oci_result($stid,'FORMSEQANCE_BS');
            $NUM=oci_result($stid,'NO_BEDSORE');
            $DT_START=oci_result($stid,'DT_START');
            $REGAION=oci_result($stid,'NM_ORGAN');
            $TOP=oci_result($stid,'IT_TOP');
            $LEFT=oci_result($stid,'IT_LEFT');
            $HEIGTH=oci_result($stid,'IT_HEIGTH');
            $WIDTH=oci_result($stid,'IT_WIDTH');


            $tmpA=unserialize(serialize($Obj_A));
            $tmpA->NUM=$NUM;
            $tmpA->TOP=$TOP;
            $tmpA->LEFT=$LEFT;
            $tmpA->W_TH=$WIDTH;
            $tmpA->H_TH=$HEIGTH;
            $tmpA->FRMSEQ=$FreSeq;


            $T_SQL="SELECT DISTINCT I.DATESEQANCE,I.ID_TABITEM,I.CID_CONTORL, R.ID_STATION,
                 case WHEN NM_USER IS NULL THEN ST_VALUE ELSE NM_USER END AS ST_VALUE
                 FROM NSTIBS I
                 LEFT JOIN NSUSER ON ST_VALUE = ID_USER
                 ,NSBSOR R,NSTBBS B 
                  WHERE 
                  B.DT_EXCUTE='$sDt' AND B.TM_EXCUTE='$sTm'
                 AND R.ID_PATIENT='$idPt' AND R.ID_INPATIENT='$INPt'
                 AND R.CID_BEDSORE ='B'
                 AND I.FORMSEQANCE_BS='$FreSeq'
                 AND B.DATESEQANCE_FL=I.DATESEQANCE_FL
                 AND B.DM_CANCD=' ' AND I.DM_CANCD= ' ' 
                 AND R.DT_END=' ' AND R.DM_CANCD=' '";

            $T_Stid=oci_parse($conn,$T_SQL);
            oci_execute($T_Stid);
            $tmpB=unserialize(serialize($Obj_B));

            $tmpB->FORMSEQ=$FreSeq;
            $tmpB->DT_START=$DT_START;
            while (oci_fetch_array($T_Stid)){
                $CID_CONTORL=oci_result($T_Stid,'CID_CONTORL');
                $ID_STATION=oci_result($T_Stid,'ID_STATION');
                $ST_VALUE=oci_result($T_Stid,'ST_VALUE');
                $ID_TABITEM=oci_result($T_Stid,'ID_TABITEM');


                foreach ($tmpB->TB_DATA as $key=>$item){

                    if ($item->ID==$ID_TABITEM){
                        $item->VALUE=$ST_VALUE;

                    }
                    if ($key=="NO_NUM"){
                        $item->VALUE=$NUM;
                    }
                    if ($key=="NM_ORGAN"){
                        $item->VALUE=$REGAION;
                    }
                }


            }


            array_push($PageA_arr,$tmpA);
            array_push($PageB_arr,$tmpB);

        }


        $data['A']=$PageA_arr;
        $data['B']=$PageB_arr;
    }

    $InsertTP_result=InsertTP($conn,$sFm,$sTraID,$data,$idPt,$INPt,$sDt,$sTm,$BED,$DM_PROCESS,$sUr,$JID_NSRANK,$FORMSEQANCE_WT);



    if (!$InsertTP_result){
        echo 'SELECT INSERT TP FALSE';
        return false;
    }

    $result=array(
        "sTraID"=>$sTraID,
        "IDPT"=>$idPt,
        "INPT"=>$INPt,
        "DTEXCUTE"=>$sDt,
        "TMEXCUTE"=>substr($sTm,0,4)
    );




    return json_encode($result,JSON_UNESCAPED_UNICODE);
}
function PosBSORCancel($conn,$sFm,$sTraID,$sUr){

    $SQL="SELECT ID_PATIENT,ID_INPATIENT,DT_EXCUTE,TM_EXCUTE 
            FROM  HIS803.NISWSTP
            WHERE ID_TABFORM = :ID_TABFORM  
            AND ID_TRANSACTION = :ID_TRANSACTION";
    $stid=oci_parse($conn,$SQL);

    $bind_P=array(
        ':ID_TABFORM'=>$sFm,
        ':ID_TRANSACTION'=>$sTraID
    );

    foreach ($bind_P as $key=>$value){
        oci_bind_by_name($stid,$key,$value);
    }
    oci_execute($stid);
    $ID_PATIENT="";
    $ID_INPATIENT="";
    $DT_EXCUTE="";
    $TM_EXCUTE="";
    while (oci_fetch_array($stid)){
        $ID_PATIENT=oci_result($stid,'ID_PATIENT');
        $ID_INPATIENT=oci_result($stid,'ID_INPATIENT');
        $DT_EXCUTE=oci_result($stid,'DT_EXCUTE');
        $TM_EXCUTE=oci_result($stid,'TM_EXCUTE');
    }


}
/*改變瘡=>傷*/
function ChangeChr($conn){
    //瘡=>傷

    $SQL=" SELECT IS_ACTIVE FROM NSCLSI WHERE CID_CLASS = 'BSOR' and IS_ACTIVE = 'Y' AND ST_TEXT1 = 'RETITLE'";
    $stid=oci_parse($conn,$SQL);
    oci_execute($stid);
    $is_Change="";
    while (oci_fetch_array($stid)){
        $is_Change=oci_result($stid,'IS_ACTIVE');
    }
    return $is_Change;
}
function GetNoRegion($conn,$ST_DATAA,$ST_DATAB,$ID_PATIENT,$ID_INPATIENT,$CID_BEDSORE){

$obj_A=json_decode($ST_DATAA);
$obj_B=json_decode($ST_DATAB);


$arr_A=[];
$arr_B=[];

$SQL="SELECT DISTINCT NSTBBS.FORMSEQANCE_BS,NSBSOR.DT_START,NSBSOR.NO_BEDSORE,TID_SOURCE,NM_ORGAN,IT_TOP,IT_LEFT,IT_WIDTH,IT_HEIGTH
        FROM NSBSOR, NSTBBS 
        WHERE NSTBBS.FORMSEQANCE_BS = NSBSOR.FORMSEQANCE_BS
          AND NSBSOR.ID_PATIENT = :ID_PATIENT AND NSBSOR.ID_INPATIENT = :ID_INPATIENT
          AND NSBSOR.DM_CANCD = ' ' AND NSTBBS.DM_CANCD = ' '
          AND NSBSOR.CID_BEDSORE = :CID_BEDSORE
          AND NSTBBS.DT_EXCUTE||NSTBBS.TM_EXCUTE = 
          (
                  SELECT Max(CONCAT(tb.DT_EXCUTE,tb.TM_EXCUTE)) AS LAST_DTTM  
                  FROM NSTBBS tb, NSBSOR
                  WHERE tb.FORMSEQANCE_BS = NSBSOR.FORMSEQANCE_BS
                  AND NSBSOR.CID_BEDSORE =  :CID_BEDSORE
                  AND NSBSOR.ID_PATIENT = :ID_PATIENT AND NSBSOR.ID_INPATIENT = :ID_INPATIENT
                  AND NSBSOR.DM_CANCD = ' ' AND tb.DM_CANCD = ' '
        )
        ORDER BY NO_BEDSORE ASC
        ";

    $stid=oci_parse($conn,$SQL);

    oci_bind_by_name($stid,':ID_PATIENT',$ID_PATIENT);
    oci_bind_by_name($stid,':ID_INPATIENT',$ID_INPATIENT);
    oci_bind_by_name($stid,':CID_BEDSORE',$CID_BEDSORE);
    oci_execute($stid);

    $count=0;
    while (oci_fetch_array($stid)){

      //序列化後複製
      $tmp_A = unserialize(serialize($obj_A));
      $tmp_B = unserialize(serialize($obj_B));

        $FORMSEQANCE_BS=oci_result($stid,'FORMSEQANCE_BS');
        $NO_BEDSORE=oci_result($stid,'NO_BEDSORE');
        $NM_ORGAN=oci_result($stid,'NM_ORGAN');
        $TID_SOURCE=oci_result($stid,'TID_SOURCE');
        $LEFT=oci_result($stid,'IT_LEFT');
        $TOP=oci_result($stid,'IT_TOP');
        $W_TH=oci_result($stid,'IT_WIDTH');
        $H_TH=oci_result($stid,'IT_HEIGTH');
        $DT_START=oci_result($stid,'DT_START');

        $tmp_A->NUM=$NO_BEDSORE;
        $tmp_A->TOP=$TOP;
        $tmp_A->LEFT=$LEFT;
        $tmp_A->W_TH=$W_TH;
        $tmp_A->H_TH=$H_TH;
        $tmp_A->FRMSEQ=$FORMSEQANCE_BS;



        $tmp_B->TB_DATA->NO_NUM->VALUE=$NO_BEDSORE;
        $tmp_B->TB_DATA->NM_ORGAN->VALUE=$NM_ORGAN;
        $tmp_B->TB_DATA->TID_SOURCE->VALUE=$TID_SOURCE;
        $tmp_B->FORMSEQ=$FORMSEQANCE_BS;
        $tmp_B->DT_START=$DT_START;

        array_push($arr_A,$tmp_A);
        array_push($arr_B,$tmp_B);
      $count++;
    }

    if ($count==0){
        //有效時間內無資料 取預設值push
        array_push($arr_A,json_decode($ST_DATAA));
        array_push($arr_B,json_decode($ST_DATAB));
    }
    $result=array("A"=>$arr_A,"B"=>$arr_B);

    return $result;
}
function InsertTP($conn,$sfm,$sTraID,$data,$Idpt,$INPt,$sDT,$sTm,$ID_BED,$DM_PROCESS,$UR_PROCESS,$NSRANK,$FormSeq_WT){
    $SQL="INSERT INTO HIS803.NISWSTP(
                               ID_TABFORM,ID_TRANSACTION,ID_PATIENT,ID_INPATIENT,DT_EXCUTE,TM_EXCUTE,
                               ST_DATAA,ST_DATAB,ST_DATAC,ST_DATAD,ST_DATAE,ST_DATAF,ST_DATAG,ST_DATAH,
                               ID_BED,DM_PROCESS,UR_PROCESS,JID_NSRANK,FORMSEQANCE_WT) 
                                 VALUES (
                                :ID_TABFORM,:sTraID,:Idpt,:INPt,:DT_EXCUTE,:TM_EXCUTE,
                                EMPTY_CLOB(),EMPTY_CLOB(),EMPTY_CLOB(),' ',' ',' ',' ',' ',
                               :BED,:DM_P,:UR_P,:NSRANK,:FormSeq)
                               RETURNING  ST_DATAA,ST_DATAB,ST_DATAC
                                INTO :ST_DATAA,:ST_DATAB,:ST_DATAC";
    $TP_Stid = oci_parse($conn, $SQL);
    if(!$TP_Stid){
        $e=oci_error($conn);
        return $e['message'];
    }
    $clobA=oci_new_descriptor($conn,OCI_D_LOB);
    $clobB=oci_new_descriptor($conn,OCI_D_LOB);
    $clobC=oci_new_descriptor($conn,OCI_D_LOB);

    oci_bind_by_name($TP_Stid,":ST_DATAA",$clobA,-1,OCI_B_CLOB);
    oci_bind_by_name($TP_Stid,":ST_DATAB",$clobB,-1,OCI_B_CLOB);
    oci_bind_by_name($TP_Stid,":ST_DATAC",$clobC,-1,OCI_B_CLOB);


    oci_bind_by_name($TP_Stid,":ID_TABFORM",$sfm);
    oci_bind_by_name($TP_Stid,":sTraID",$sTraID);
    oci_bind_by_name($TP_Stid,":Idpt",$Idpt);
    oci_bind_by_name($TP_Stid,":INPt",$INPt);
    oci_bind_by_name($TP_Stid,":DT_EXCUTE",$sDT);
    oci_bind_by_name($TP_Stid,":TM_EXCUTE",$sTm);
    oci_bind_by_name($TP_Stid,":BED",$ID_BED);
    oci_bind_by_name($TP_Stid,":DM_P",$DM_PROCESS);
    oci_bind_by_name($TP_Stid,":UR_P",$UR_PROCESS);
    oci_bind_by_name($TP_Stid,":NSRANK",$NSRANK);
    oci_bind_by_name($TP_Stid,":FormSeq",$FormSeq_WT);


    $result = oci_execute($TP_Stid,OCI_NO_AUTO_COMMIT);
    if(!$result){
        $e=oci_error($TP_Stid);
        oci_rollback($conn);
        echo $e['message'];
        return false;
    }


    $clobA->save(json_encode($data['A'],JSON_UNESCAPED_UNICODE));
    $clobB->save(json_encode($data['B'],JSON_UNESCAPED_UNICODE));
    $clobC->save(json_encode($data['A'],JSON_UNESCAPED_UNICODE));
    oci_free_statement($TP_Stid);
    oci_commit($conn);
    return true;
}
function GetStationOrder($conn,$CNM_arr){
    $NEW_arr=json_decode($CNM_arr);

    $SQL=" SELECT ID_STATION, NM_STATION FROM NIS_V_HNST_Q0 ORDER BY ID_STATION";
    $stid=oci_parse($conn,$SQL);
    oci_execute($stid);

    while (oci_fetch_array($stid)){
        array_push( $NEW_arr[2],array("ID_TABITEM"=>oci_result($stid,'ID_STATION'),"ST_LEFT"=>oci_result($stid,'NM_STATION')));

    }

    return $NEW_arr;
}

function InsertBSOR($conn,$sfm,$DATESEQANCE,$FORMSEQANCE_BS,$IdPt,$IdinPt,$NO_BEDSORE,$DT_START,$DT_END,$TID_SOURCE,$ID_STATION,$NM_ORGAN,$IT_TOP,$IT_LEFT,$IT_WIDTH,$IT_HEIGTH,$TID_ENDSTATE,$System_DT,$ID_BED,$JID_NSRANK,$FORMSEQANCE_WT,$sUr){
    $CID_BEDSORE=substr($sfm,0,1);


    $SQL="INSERT INTO NSBSOR
                (DATESEQANCE_FL,FORMSEQANCE_BS,ID_PATIENT,ID_INPATIENT,DT_REGISTER,NO_OPDSEQ,NO_BEDSORE,CID_BEDSORE,DT_START,DT_END,
                TID_SOURCE,ID_STATION,NM_ORGAN,TID_ENDSTATE,IT_TOP,IT_LEFT,IT_WIDTH,IT_HEIGTH,
                ID_BED,JID_NSRANK,FORMSEQANCE_WT,DM_PROCESS,UR_PROCESS,DM_CANCD,UR_CANCD)
                VALUES
                ('$DATESEQANCE','$FORMSEQANCE_BS','$IdPt','$IdinPt',' ','0','$NO_BEDSORE','$CID_BEDSORE','$DT_START','$DT_END',
                '$TID_SOURCE','$ID_STATION','$NM_ORGAN','$TID_ENDSTATE','$IT_TOP','$IT_LEFT','$IT_WIDTH','$IT_HEIGTH',
                '$ID_BED','$JID_NSRANK','$FORMSEQANCE_WT','$System_DT','$sUr',' ',' '
                )";
    $Result=array("result"=>"true","message"=>"");

    $stid=oci_parse($conn,$SQL);
    if (!$stid){
        $Result['result']="false";
        $Result['message']=oci_error($conn)['message'];
    }
    $result=oci_execute($stid,OCI_NO_AUTO_COMMIT);
    if (!$result){
        $Result['result']="false";
        $Result['message']=oci_error($stid)['message'];
    }
    return json_decode(json_encode($Result,JSON_UNESCAPED_UNICODE));
}
function InsertTIBS($conn,$obj,$sfm,$DATESEQANCE,$FORMSEQANCE_BS){

    $Result=array("result"=>"true","message"=>"");

   foreach ($obj as $key=>$item){
        $ID_TABITEM=$item->ID; //評估表單代碼
            if ($ID_TABITEM){

                if ($ID_TABITEM=="BSOR000001" || $ID_TABITEM=="BSOR000036" || $ID_TABITEM=="BSOR000043" || $ID_TABITEM=="BSOR000045"){
                    $CID_TABNAME='NSBSOR';
                }else{
                    $CID_TABNAME='NSTBBS';
                }


                $ELE_STAT=$item->TYPE===""?" ":$item->TYPE;//元件名稱 ED=>input CB=>CHECKBOX
                $ST_VALUE=$item->VALUE===""?" ":$item->VALUE;//欄位值

                $SQL="INSERT INTO NSTIBS
                    (DATESEQANCE,DATESEQANCE_FL,ID_TABITEM,FORMSEQANCE_BS,CID_TABNAME,
                    ID_TABFORM,CID_CONTORL,IS_CHELDED,ST_VALUE,MM_VALUE,DM_CANCD,UR_CANCD)
                    VALUES
                    (NIS_DATETIMESEQ,'$DATESEQANCE','$ID_TABITEM','$FORMSEQANCE_BS','$CID_TABNAME',
                    '$sfm','$ELE_STAT',' ','$ST_VALUE',' ',' ',' ')
                    ";

                $stid=oci_parse($conn,$SQL);
                if (!$stid){
                    $Result['result']="false";
                    $Result['message']=oci_error($conn)['message'];
                    break;
                }
                $execute=oci_execute($stid,OCI_NO_AUTO_COMMIT);
                if (!$execute){
                    $Result['result']="false";
                    $Result['message']=oci_error($stid)['message'];
                    break;
                }

            }
    }
    return json_decode(json_encode($Result,JSON_UNESCAPED_UNICODE));
}
function InsertTBBS($conn,$DATESEQANCE_FL,$FORMSEQANCE_BS,$DT,$TM,$BED,$FORMSEQANCE_WT,$JID_NSRANK,$sUr){
    $DateTime = date("YmdHis");
    $Y_VID = substr($DateTime, 0, 4);
    $Date = substr($DateTime, -10, 10);
    $Y_TW = (int)$Y_VID - 1911;
    $System_DT= (string)$Y_TW .(string)$Date;

    $Result=array("result"=>"true","message"=>"");
    $SQL="INSERT INTO NSTBBS (DATESEQANCE_FL,FORMSEQANCE_BS,DT_EXCUTE,TM_EXCUTE,ID_BED,FORMSEQANCE_WT,
                              DM_PROCESS,UR_PROCESS,DM_CANCD,UR_CANCD,JID_NSRANK)
          VALUES('$DATESEQANCE_FL','$FORMSEQANCE_BS','$DT','$TM','$BED','$FORMSEQANCE_WT',
                 '$System_DT','$sUr',' ',' ','$JID_NSRANK')";

    $stid=oci_parse($conn,$SQL);
    if (!$stid){
        $Result['result']="false";
        $Result['message']=oci_error($conn)['message'];
    }
    $result=oci_execute($stid,OCI_NO_AUTO_COMMIT);
    if (!$result){
        $Result['result']="false";
        $Result['message']=oci_error($stid)['message'];

    }
    return json_decode(json_encode($Result,JSON_UNESCAPED_UNICODE));
}



function BSORCancel($conn,$sfm,$idPt,$idInPt,$Freseq,$Sys_DT,$sUr){

    $CID_BEDSORE=substr($sfm,0,1);
    $SQL="UPDATE NSBSOR
          SET DM_CANCD=:DM_CANCD,
              UR_CANCD=:UR_CANCD,
              UR_PROCESS=:UR_PROCESS
          WHERE 
          ID_PATIENT=:ID_PATIENT
          AND ID_INPATIENT= :ID_INPATIENT
          AND FORMSEQANCE_BS=:FORMSEQANCE_BS
          AND CID_BEDSORE=:CID_BEDSORE 
          AND DM_CANCD=' '";

  $stid=oci_parse($conn,$SQL);

   $bind_P=array(':ID_PATIENT'=>$idPt,
                  ':ID_INPATIENT'=>$idInPt,
                  ':FORMSEQANCE_BS'=>$Freseq,
                  ':CID_BEDSORE'=>$CID_BEDSORE,
                  ':DM_CANCD'=>$Sys_DT,
                  ':UR_CANCD'=>$sUr,
                  ':UR_PROCESS'=>$sUr);

    foreach ($bind_P as $key=>$value){
        oci_bind_by_name($stid,$key,$value);
    }
    $re=array("result"=>"true","message"=>"");
    if (!$stid){
        $re['result']="false";
        $re['message']=oci_error($conn)['message'];
    }
    $Execute=oci_execute($stid,OCI_NO_AUTO_COMMIT);
    if (!$Execute){
        $re['result']="false";
        $re['message']=oci_error($stid)['message'];

    }

    return json_decode(json_encode($re));

}
function TBBSCancel($conn,$FORMSEQANCE_BS,$System_DT,$ID_BED,$sUr){




    $SQL="UPDATE  NSTBBS
      SET DM_CANCD=:DM_CANCD,UR_CANCD=:UR_CANCD
      WHERE FORMSEQANCE_BS=:FORMSEQANCE_BS
      AND ID_BED=:ID_BED AND DM_CANCD=' '";
    $stid=oci_parse($conn,$SQL);
    oci_bind_by_name($stid,':DM_CANCD',$System_DT);
    oci_bind_by_name($stid,':UR_CANCD',$sUr);
    oci_bind_by_name($stid,':FORMSEQANCE_BS',$FORMSEQANCE_BS);
    oci_bind_by_name($stid,':ID_BED',$ID_BED);

    $re=array("result"=>"true","message"=>"");
    if (!$stid){
        $re['result']="false";
        $re['message']=oci_error($conn)['message'];
    }
    $result=oci_execute($stid,OCI_NO_AUTO_COMMIT);
    if (!$result){
        $re['result']="false";
        $re['message']=oci_error($stid)['message'];
    }
   return json_decode(json_encode($re));
}


function GetDataSEQ($conn){
    $SQL="SELECT NIS_DATETIMESEQ AS result FROM DUAL";
    $stid=oci_parse($conn,$SQL);
    oci_execute($stid,OCI_NO_AUTO_COMMIT);
    oci_fetch_all($stid,$output);
    return join( $output['RESULT']);
}
function GetPationData($conn,$Idpt,$INPt,$sUr){
    $SQL="SELECT (SELECT his803.nis_datetimeseq FROM DUAL) ID_TRANSB,
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

    $stid1=oci_parse($conn,$SQL);

    oci_bind_by_name($stid1,':idPt',$Idpt);
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


    $sTraID=$ID_TRANSB.'ILSGA'.str_pad($ID_TRANSA,8,0,STR_PAD_LEFT);
    $result=array("STRA_ID"=>$sTraID,"BED"=>$ID_BED,"FORMSEQANCE_WT"=>$FORMSEQANCE_WT,"JID_NSRANK"=>$JID_NSRANK);
    $json_str=json_encode($result);

    return  json_decode($json_str);
}
function MaxNumber($conn,$sfm,$Idpt,$INPt){
    $sNO="";
    $sTable="";
    $sCanCD="";
    $CID_BS=substr($sfm,0,1);
    if ($sfm=="BSOR" || $sfm=="CUTS"){
        $sTable = 'NSBSOR';
        $sNO = 'NO_BEDSORE';
        $sCanCD = 'UR_CANCD';
    }else if ($sfm=="TPUP"){
        $sTable = 'NSTUPG';
        $sNO = 'NO_PROBLEM';
        $sCanCD = 'UR_ENDING';
    }
    $SQL='SELECT MAX('.$sNO.') NUM FROM  '.$sTable. ' 
              WHERE ID_PATIENT ='."'$Idpt'".
        'AND ID_INPATIENT ='."'$INPt'";

    if ($sfm=="BSOR" || $sfm=="CUTS"){
        $SQL=$SQL.' AND '.$sCanCD.' = '."' '"
            .'AND CID_BEDSORE = '."'$CID_BS'";
    }
    $stid=oci_parse($conn,$SQL);
    oci_execute($stid);
    oci_fetch_all($stid,$output);

    $MaxNo=join($output['NUM'])==""?"0":join($output['NUM']);
    return $MaxNo;
}

function obj_diff($new_obj,$default_obj){
  $Common_Array=array_splice($new_obj,0,count($default_obj));

  $count=0;
  $diff=[];
  foreach ($Common_Array as $value){
      if ($value->NUM == $default_obj[$count]->NUM){
          if ($value->LEFT !==$default_obj[$count]->LEFT || $value->TOP !==$default_obj[$count]->TOP){
              array_push($diff,$value);
          }
      }

      $count++;
  }

    return array_merge($diff,$new_obj);
}