<?php

function GetBSORIniJson($conn,$sFm,$Idpt,$INPt,$ID_BED,$sTraID,$sSave,$date,$sUr,$JID_NSRANK,$FORMSEQANCE_WT){

    $SQL="SELECT ST_DATAA,ST_DATAB,ST_PREA,ST_PREB,ST_PREC,ST_PRED FROM NISWSIT WHERE ID_TABFORM=:ID_TABFORM";

    $stid=oci_parse($conn,$SQL);
    oci_bind_by_name($stid,':ID_TABFORM',$sFm);
    oci_execute($stid);

    $ST_DATAA='';
    $ST_DATAB="";
    $MM_TEXT="";
    $Tittle_Nm="";
    $Tittle_CNm="";
    $Data_Edit="";

    while (oci_fetch_array($stid)){
        $ST_DATAA=oci_result($stid,"ST_DATAA")->load();
        $ST_DATAB=oci_result($stid,"ST_DATAB")->load();
        $MM_TEXT=oci_result($stid,"ST_PREA")->load();
        $Tittle_Nm=oci_result($stid,"ST_PREB")->load();
        $Tittle_CNm=oci_result($stid,"ST_PREC")->load();
        $Data_Edit=oci_result($stid,"ST_PRED")->load();
    }

    $response=array(
        "MM_TEXT"=>json_decode($MM_TEXT),
        "T_NM"=>json_decode($Tittle_Nm),
        "T_CNM"=>GetStationOrder($conn,$sFm,$Tittle_CNm),
        "D_EDIT"=>json_decode($Data_Edit)
    );



    $response['sSave']=$sSave;
    $response['sTraID']=$sTraID;
    $response['ST_DATAB']=json_decode($ST_DATAB);
    $response['MAXNUM']= MaxNumber($conn,$sFm,$Idpt,$INPt);

    if ($sFm=="TUPT"){

        $GetNoRegion_Param=array(
            ":ID_PATIENT"=>$Idpt,
            ":ID_INPATIENT"=>$INPt
        );

    }else{

        $GetNoRegion_Param=array(
            ":ID_PATIENT"=>$Idpt,
            ":ID_INPATIENT"=>$INPt,
            ":CID_BEDSORE"=>substr($sFm,0,1)
                );

    }


    $DATA=GetNoRegion($conn,$sFm,$ST_DATAA,$ST_DATAB,$GetNoRegion_Param);

    if (!InsertTP($conn,$sFm,$sTraID,$DATA,$Idpt,$INPt,' ',' ',$ID_BED,$date,$sUr,$JID_NSRANK,$FORMSEQANCE_WT))
    {
        return false;
    }

    return  json_encode($response,JSON_UNESCAPED_UNICODE);
}

function GetBSORPageJson($conn,$sFm,$sPg,$sTraID){
   // $TP_SQL="SELECT ST_DATA".$sPg." FROM HIS803.NISWSTP WHERE ID_TRANSACTION=:sTraID AND ID_TABFORM = '$sFm'";
    $SQL="SELECT ST_DATAA,ST_DATAB FROM HIS803.NISWSTP WHERE ID_TRANSACTION=:sTraID AND ID_TABFORM = :ID_TABFORM";
    $stid=oci_parse($conn,$SQL);
    if (!$stid){
        $e=oci_error($conn);
        return $e['message'];
    }

    oci_bind_by_name($stid,":sTraID",$sTraID);
    oci_bind_by_name($stid,":ID_TABFORM",$sFm);
    $r=oci_execute($stid);
    if (!$r){
        $e=oci_error($stid);
        return $e['message'];
    }
    $DATA=json_decode(json_encode(array("DATA_A"=>"","DATA_B"=>"")));
    while (oci_fetch_array($stid)){
        $DATA_A=oci_result($stid,"ST_DATAA")->load();
        $DATA_B=oci_result($stid,"ST_DATAB")->load();

        $DATA->DATA_A=json_decode($DATA_A);
        $DATA->DATA_B=json_decode($DATA_B);
    }


    return json_encode($DATA);
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


    $Ssql=" SELECT ID_INPATIENT,ID_PATIENT, ST_DATAA,ST_DATAB,ST_DATAC,ST_DATAD,
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
    $ST_DATAD="";
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
        $ST_DATAD=$row['ST_DATAD'];//評估資料(原始值)
        $ID_BED=$row['ID_BED'];
        $JID_NSRANK=$row['JID_NSRANK'];
        $FORMSEQANCE_WT=$row['FORMSEQANCE_WT'];
    }

    $result=array("result"=>"","message"=>"");


        $DTSEQ_SFMSEQ=[];

        $obj_A=json_decode($ST_DATAA);
        $obj_B=array_filter(json_decode($ST_DATAB),function ($val){return $val->TB_DATA->NO_NUM->VALUE!="";});
        $obj_C=json_decode($ST_DATAC); //座標預設值
        $obj_D=json_decode($ST_DATAD); //資料預設值

        foreach ($obj_A as $key=>$value){
            $NUM=$value->NUM;
            if (trim($NUM)!=""){
                $DATESEQANCE=GetDataSEQ($conn);

                if (trim($value->FORMSEQ)==""){
                    if ($sFm=="BSOR" || $sFm=="CUTS"){
                        $FORMSEQANCE_BS=GetFrmSeq($conn,$sFm);
                    }else{
                         $FORMSEQANCE_BS=GetFrmSeq($conn,'TUPG');
                    }


                    $value->FORMSEQ=$FORMSEQANCE_BS;
                    $obj_B[$key]->FORMSEQ=$FORMSEQANCE_BS;
                }
                else{
                    $FORMSEQANCE_BS=$value->FORMSEQ;
                }
                $DTSEQandFSEQ=array("DTSEQ"=>$DATESEQANCE,"FMSEQ"=>$FORMSEQANCE_BS,"NUM"=>$NUM);
                array_push($DTSEQ_SFMSEQ,json_encode($DTSEQandFSEQ));
            }
        }

        $PIXCEL_DiffArr=obj_diff('A',$obj_A,$obj_C);//比對座標
        $TB_DATA=obj_diff('B',$obj_B,$obj_D);//比對TB_DATA

        $Cancel_PIXCEL_Data=$PIXCEL_DiffArr->OLD_DATA;//作廢座標
        $Insert_PIXCEL_Data=$PIXCEL_DiffArr->NEW_DATA;//新增座標

        $Cancel_TB_DATA=$TB_DATA->OLD_DATA;//作廢TB_DATA
        $Insert_TB_DATA=$TB_DATA->NEW_DATA;//新增TB_DATA


      // 防呆無異 return 無須存檔
      if (count($Cancel_PIXCEL_Data)==0    && count($Insert_PIXCEL_Data)==0 &&
        count($Cancel_TB_DATA)==0 && count($Insert_TB_DATA)==0)
    {
        $result['result']="false";
        $result['message']='資料無任何異動';
        return json_encode($result,JSON_UNESCAPED_UNICODE);
    }



        //與座標預設值不同則需新增
      if(count($Insert_PIXCEL_Data)>0){

            //已有紀錄先作廢
            if (count($Cancel_PIXCEL_Data)>0){
                foreach ($Cancel_PIXCEL_Data as $value){
                    $FORMSEQ_BS=$value->FORMSEQ;
                 if ($sFm=="BSOR" || $sFm=="CUTS"){
                        $arr_UP_BSOR=array(
                            ":ID_PATIENT"=>$IdPt,
                            ":ID_INPATIENT"=>$IdinPt,
                            ":FORMSEQANCE_BS"=>$FORMSEQ_BS,
                            ":CID_BEDSORE"=>substr($sFm,0,1),
                            ":DM_CANCD"=>$System_DT,
                            ":UR_CANCD"=>$sUr,
                            ":UR_PROCESS"=>$sUr
                        );
                        $arr_UP_TBBS=array(
                            ':DM_CANCD'=>$System_DT,
                            ':UR_CANCD'=>$sUr,
                            ':FORMSEQANCE_BS'=>$FORMSEQ_BS,
                            ':ID_BED'=>$ID_BED
                        );

                        $UPDATE_BSOR=DB_Cancel($conn,'BSOR',$arr_UP_BSOR);
                        if ($UPDATE_BSOR->result=="false"){
                           $result['result']="false";
                           $result['message']=$UPDATE_BSOR->message;
                           return json_encode($result,JSON_UNESCAPED_UNICODE);
                        }
                        $UPDATE_TBBS=DB_Cancel($conn,'TBBS',$arr_UP_TBBS);
                        if ($UPDATE_TBBS->result=="false"){
                            $result['result']="false";
                            $result['message']=$UPDATE_TBBS->message;
                            return json_encode($result,JSON_UNESCAPED_UNICODE);
                        }
                    }
                }
            }


             //存檔座標
             foreach ($Insert_PIXCEL_Data as $value){
                 $FORMSEQ_BS=$value->FORMSEQ;//表單編號
                 $NO_BEDSORE=$value->NUM; //編號
                 $GetThisDTSEQ= array_filter($DTSEQ_SFMSEQ,function ($value)use ($FORMSEQ_BS,$NO_BEDSORE){
                     return json_decode($value)->FMSEQ==$FORMSEQ_BS && json_decode($value)->NUM==$NO_BEDSORE;
                 });

                 $DATESEQ=json_decode(join($GetThisDTSEQ))->DTSEQ;


                 $IT_LEFT=$value->LEFT;
                 $IT_TOP=$value->TOP;
                 $IT_WIDTH=$value->W_TH;
                 $IT_HEIGTH=$value->H_TH;

                 $GetData=array_filter($obj_B,function ($val)use($NO_BEDSORE){return $val->TB_DATA->NO_NUM->VALUE==$NO_BEDSORE;});
                 sort($GetData);

                 $ID_STATION=$GetData[0]->SSTAT;//護理站代碼
                 $NM_ORGAN=$GetData[0]->TB_DATA->NM_ORGAN->VALUE;//部位名稱

                 $DT_START=$GetData[0]->DT_START;//開始日期
                 $TID_SOURCE=$GetData[0]->TB_DATA->TID_SOURCE->VALUE;//發生來源

                 if ($sFm=="BSOR" || $sFm=="CUTS"){
                           $arr_IN_BSOR=array(
                         ":DATESEQANCE_FL"=>$DATESEQ
                         ,":FORMSEQANCE_BS"=>$FORMSEQ_BS
                         ,":ID_PATIENT"=>$IdPt
                         ,":ID_INPATIENT"=>$IdinPt
                         ,":DT_REGISTER"=>" "
                         ,":NO_OPDSEQ"=>"0"
                         ,":NO_BEDSORE"=>$NO_BEDSORE
                         ,":CID_BEDSORE"=>substr($sFm,0,1)
                         ,":DT_START"=>$DT_START
                         ,":DT_END"=>" "
                         ,":TID_SOURCE"=>$TID_SOURCE
                         ,":ID_STATION"=>$ID_STATION
                         ,":NM_ORGAN"=>$NM_ORGAN
                         ,":TID_ENDSTATE"=>" "
                         ,":IT_TOP"=>$IT_TOP
                         ,":IT_LEFT"=>$IT_LEFT
                         ,":IT_WIDTH"=>$IT_WIDTH
                         ,":IT_HEIGTH"=>$IT_HEIGTH
                         ,":ID_BED"=>$ID_BED
                         ,":JID_NSRANK"=>$JID_NSRANK
                         ,":FORMSEQANCE_WT"=>$FORMSEQANCE_WT
                         ,":DM_PROCESS"=>$System_DT
                         ,":UR_PROCESS"=>$sUr
                         ,":DM_CANCD"=>" "
                         ,":UR_CANCD"=>" "
                     );

                           $INSERT_BSOR=DB_INSERT($conn,'BSOR',$arr_IN_BSOR);

                           if ($INSERT_BSOR->result=="false"){
                               $result['result']="false";
                               $result['message']=$INSERT_BSOR->message;
                               return json_encode($result,JSON_UNESCAPED_UNICODE);
                           }
                 }
                 else{
                     //insert  NSTUPG
                     $ID_REGION=$value->ID_REGION;

                     $arr_IN_TUPG=array(
                         ":DATESEQANCE"=>$DATESEQ,
                         ":FORMSEQANCE"=>$FORMSEQ_BS,
                         ":ID_PATIENT"=>$IdPt,
                         ":ID_INPATIENT"=>$IdinPt,
                         ":NO_OPDSEQ"=>'0',
                         ":DT_REGISTER"=>' ',
                         ":NO_PROBLEM"=>$NO_BEDSORE,
                         ":ID_ORGAN"=>$ID_REGION,//部位代碼
                         ":NM_ORGAN"=>$NM_ORGAN,
                         ":IT_TOP"=>$IT_TOP,
                         ":IT_LEFT"=>$IT_LEFT,
                         ":IT_WIDTH"=>$IT_WIDTH,
                         ":IT_HEIGTH"=>$IT_HEIGTH,
                         ":IS_CANCD"=>'N',
                         ":ID_BED"=>$ID_BED,
                         ":JID_NSRANK"=>$JID_NSRANK,
                         ":FORMSEQANCE_WT"=>$FORMSEQANCE_WT,
                         ":DM_PROCESS"=>$System_DT,
                         ":UR_PROCESS"=>$sUr
                     );
                     $INSERT_TUPG=DB_INSERT($conn,'TUPG',$arr_IN_TUPG);
                     if ($INSERT_TUPG->result=="false"){
                         $result['result']="false";
                         $result['message']=$INSERT_TUPG->message;
                         return json_encode($result,JSON_UNESCAPED_UNICODE);
                     }

                 }

             }
        }


        //TB_DATA
       if ($sFm=="BSOR" || $sFm=="CUTS"){


           //是否結案
           $DTEND=array_filter($obj_B,function ($val) use($sDt,$sUr){

                 if (trim($val->TB_DATA->ED_TYPE->VALUE)!=""){
                     $val->TB_DATA->ED_DATE->VALUE=$sDt;
                     $val->TB_DATA->ED_PRO->VALUE=$sUr;
                     return $val;
                 }

             });
           if (count($DTEND)>0){
               //結案狀態 update
               foreach ($DTEND as $value){
                   $ED_TYPE="";
                   $FORMSEQ_BS=$value->FORMSEQ;
                   $CID_BEDSORE=substr($sFm,0,1);

                   foreach ($value->TB_DATA as $key=>$item){
                       if ($item->ID =="BSOR000036" || $item->ID=="BSOR000045"){
                           $ED_TYPE=$item->VALUE;
                       }
                   }
                   $UPDATE_DTEND=UpDateToDTEND($conn,$IdPt,$IdinPt,$CID_BEDSORE,$FORMSEQ_BS,$sDt,$ED_TYPE);
                   if ($UPDATE_DTEND->result=="false"){
                       $result['result']="false";
                       $result['message']=$UPDATE_DTEND->message;
                       return json_encode($result,JSON_UNESCAPED_UNICODE);
                   }
               }
           }



             //TI TB 必新增
             foreach ($DTSEQ_SFMSEQ as $key=>$value){
                 $DATESEQANCE_FL=json_decode($value)->DTSEQ;
                 $FORMSEQANCE_BS=json_decode($value)->FMSEQ;

                 $arr_IN_TBBS=array(
                     ":DATESEQANCE_FL"=>$DATESEQANCE_FL,
                     ":FORMSEQANCE_BS"=>$FORMSEQANCE_BS,
                     ":DT_EXCUTE"=>$sDt,
                     ":TM_EXCUTE"=>$sTm,
                     ":ID_BED"=>$ID_BED,
                     ":FORMSEQANCE_WT"=>$FORMSEQANCE_WT,
                     ":DM_PROCESS"=>$System_DT,
                     ":UR_PROCESS"=>$sUr,
                     ":DM_CANCD"=>" ",
                     ":UR_CANCD"=>" ",
                     ":JID_NSRANK"=>$JID_NSRANK);

                     $INSERT_TBBS= DB_INSERT($conn,'TBBS',$arr_IN_TBBS);

                    if ($INSERT_TBBS->result=="false"){
                         //return error msg
                         $result['result']="false";
                         $result['message']=$INSERT_TBBS->message;
                         return json_encode($result,JSON_UNESCAPED_UNICODE);
                     }

                    //取表單對應的obj
                    $TIBS_Obj=array_filter($obj_B,function ($value) use ($FORMSEQANCE_BS){return $value->FORMSEQ!="" &&  $value->FORMSEQ==$FORMSEQANCE_BS;});

                    $INSERT_TIBS=InsertTIBS($conn,$TIBS_Obj,$sFm,$sUr,$DATESEQANCE_FL,$FORMSEQANCE_BS);

                    if ($INSERT_TIBS->result=="false"){
                        //return error msg
                        $result['result']="false";
                        $result['message']=$INSERT_TIBS->message;
                        return json_encode($result,JSON_UNESCAPED_UNICODE);
                    }

                }
            }
       else{
                if (count($TB_DATA->OLD_DATA)>0){
                    foreach ($TB_DATA->OLD_DATA as $key=>$value) {
                        $arr_UP_TUPT = array(
                            ":DM_ENDING" => $System_DT,
                            ":UR_ENDING" => $sUr,
                            ":DATESEQANCE_FL" => $value->DATESEQ,
                            ":FORMSEQANCE" => $value->FORMSEQ
                        );
                        $arr_UP_TUPI = array(
                            ":DM_ENDING" => $System_DT,
                            ":UR_ENDING" => $sUr,
                            ":DATESEQANCE_FL" => $value->DATESEQ);

                        $UPDATE_TUPT= DB_Cancel($conn, 'TUPT', $arr_UP_TUPT);
                        if ($UPDATE_TUPT->result=="false"){
                            $result['result']="false";
                            $result['message']=$UPDATE_TUPT->message;
                            return json_encode($result,JSON_UNESCAPED_UNICODE);
                        }

                        $UPDATE_TUPI= DB_Cancel($conn, 'TUPI', $arr_UP_TUPI);
                        if ($UPDATE_TUPI->result=="false"){
                            $result['result']="false";
                            $result['message']=$UPDATE_TUPI->message;
                            return json_encode($result,JSON_UNESCAPED_UNICODE);
                        }

                    }
                }


                foreach ($DTSEQ_SFMSEQ as $value){
                    $obj=json_decode($value);
                    $DATESEQANCE_FL=$obj->DTSEQ;
                    //$FORMSEQANCE_BS=$obj->FMSEQ;

                    $Num=$obj->NUM;
                    foreach($TB_DATA->NEW_DATA as $item){
                        if ($item->TB_DATA->NO_NUM->VALUE==$Num){
                            $item->DATESEQ=$DATESEQANCE_FL;
                           // $item->FORMSEQ=$FORMSEQANCE_BS;
                        }
                    }
                }
                foreach($TB_DATA->NEW_DATA as $key=>$value) {
                    $DATESEQANCE_FL=$value->DATESEQ;
                    $FORMSEQANCE_BS=$value->FORMSEQ;

                    $DT_EXECUTE=$value->TB_DATA->sDT_EXE->VALUE;//置入日
                    $DT_ENDING=$value->TB_DATA->sDT_END->VALUE;//預計拔管日
                    $ST_DEPTH=$value->TB_DATA->DEPTH->VALUE;//深度
                    $ID_TUBE=$value->TB_DATA->ID_TUBE->VALUE;//管路ID
                    $ST_TUBE=$value->TB_DATA->sST_TUBE->VALUE; //型號
                    $IT_TERMDAYS=$value->TB_DATA->IT_TERMDAYS->VALUE; //預計換管日期
                    $CD_STATUS=$value->TB_DATA->CD_STATUS->VALUE;//入院帶入
                    $NM_TUBE=$value->TB_DATA->sNM_TUBE->VALUE;//管路名稱
                    $MB_RAND=$value->TB_DATA->MB_RAND->VALUE;//入院帶入

                    $arr_IN_TUPI=array(
                        ":DATESEQANCE"=>GetDataSEQ($conn),
                        ":DATESEQANCE_FL"=>$DATESEQANCE_FL,
                        ":DT_EXECUTE"=>$DT_EXECUTE,
                        ":TM_EXECUTE"=>$sTm,//當下時間
                        ":DT_ENDING"=>trim($DT_ENDING)==""?" ":trim($DT_ENDING),
                        ":TM_ENDING"=>trim($DT_ENDING)==""?" ":$sTm,//當下時間
                        ":IS_FIRST"=>"Y",
                        ":IS_DRAWOUT"=>"N",
                        ":IS_CANCD"=>"N",
                        ":ID_BED"=>$ID_BED,
                        ":JID_NSRANK"=>$JID_NSRANK,
                        ":FORMSEQANCE_WT"=>$FORMSEQANCE_WT,
                        ":DM_PROCESS"=>$System_DT,
                        ":UR_PROCESS"=>$sUr,
                        ":DM_ENDING"=>" ",
                        ":UR_ENDING"=>" ",
                        ":ST_DEPTH"=>trim($ST_DEPTH)==""?" ":$ST_DEPTH
                    );
                    $arr_IN_TUPT=array(
                        ":DATESEQANCE_FL"=>$DATESEQANCE_FL,
                        ":FORMSEQANCE"=>$FORMSEQANCE_BS,
                        ":DT_EXECUTE"=>trim($DT_EXECUTE),
                        ":TM_EXECUTE"=>$sTm,
                        ":ID_TUBE"=>$ID_TUBE,
                        ":ST_TUBE"=>trim($ST_TUBE)==""?" ":$ST_TUBE,
                        ":IT_TERMDAYS"=>$IT_TERMDAYS,
                        ":DT_ENDING"=>" ",
                        ":TM_ENDING"=>" ",
                        ":IS_CANCD"=>"N",
                        ":ID_BED"=>$ID_BED,
                        ":JID_NSRANK"=>$JID_NSRANK,
                        ":FORMSEQANCE_WT"=>$FORMSEQANCE_WT,
                        ":DM_PROCESS"=>$System_DT,
                        ":UR_PROCESS"=>$sUr,
                        ":DM_ENDING"=>" ",
                        ":UR_ENDING"=>" ",
                        ":CD_STATUS"=>$CD_STATUS,
                        ":NM_TUBE"=>$NM_TUBE,
                        ":NM_BRAND"=>trim($MB_RAND)==""?" ":$MB_RAND
                    );


                    $INSERT_TUPI=DB_INSERT($conn,'TUPI',$arr_IN_TUPI);
                    if ($INSERT_TUPI->result=="false"){
                        $result['result']="false";
                        $result['message']=$INSERT_TUPI->message;
                        return json_encode($result,JSON_UNESCAPED_UNICODE);
                    }
                    $INSERT_TUPT=DB_INSERT($conn,'TUPT',$arr_IN_TUPT);
                    if ($INSERT_TUPT->result=="false")
                    {
                        $result['result']="false";
                        $result['message']=$INSERT_TUPT->message;
                        return json_encode($result,JSON_UNESCAPED_UNICODE);
                    }


                }
            }


      return json_encode($result,JSON_UNESCAPED_UNICODE);
   }

   function GetBSORJson($conn,$sFm,$idPt,$INPt,$sUr,$sDt,$sTm,$sPg,$sFSq){

       $SQL="SELECT ST_DATAA,ST_DATAB,ST_DATAC FROM NISWSIT WHERE ID_TABFORM='$sFm'";
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
       $CID_BEDSORE=substr($sFm,0,1);
       $sTm=str_pad($sTm,6,'0',STR_PAD_RIGHT);

       if ($sFm=="BSOR" || $sFm=="CUTS"){
           //A:{"NUM":"","LEFT":"","TOP":"","W_TH":"","H_TH":"","FORMSEQ":"","NM_ORGAN":""}
           $SQL="SELECT DISTINCT R.FORMSEQANCE_BS,R.NO_BEDSORE,R.DT_START,R.NM_ORGAN,
                    R.IT_TOP,R.IT_LEFT,R.IT_HEIGTH,R.IT_WIDTH
                    FROM NSBSOR R ,NSTBBS B
                    WHERE 
                    R.ID_PATIENT='$idPt' AND R.ID_INPATIENT='$INPt'
                    AND  B.DT_EXCUTE='$sDt' AND B.TM_EXCUTE='$sTm'
                    AND R.CID_BEDSORE='$CID_BEDSORE'  
                    AND R.FORMSEQANCE_BS=B.FORMSEQANCE_BS
                    AND R.DM_CANCD=' ' AND B.DM_CANCD=' '";
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
               $tmpA->FORMSEQ=$FreSeq;
               $tmpA->NM_ORGAN=$REGAION;

               $T_SQL="SELECT DISTINCT I.DATESEQANCE,B.DATESEQANCE_FL,I.ID_TABITEM,I.CID_CONTORL, R.ID_STATION,
                    case WHEN NM_USER IS NULL THEN ST_VALUE ELSE NM_USER END AS ST_VALUE
                    FROM NSTIBS I
                    LEFT JOIN NSUSER ON ST_VALUE = ID_USER
                    ,NSBSOR R,NSTBBS B 
                     WHERE 
                     B.DT_EXCUTE='$sDt' AND B.TM_EXCUTE='$sTm'
                    AND R.ID_PATIENT='$idPt' AND R.ID_INPATIENT='$INPt'
                    AND R.CID_BEDSORE ='$CID_BEDSORE'
                    AND I.FORMSEQANCE_BS='$FreSeq'
                    AND B.DATESEQANCE_FL=I.DATESEQANCE_FL
                    AND B.DM_CANCD=' ' AND I.DM_CANCD= ' ' 
                    AND R.DM_CANCD=' '";

               $T_Stid=oci_parse($conn,$T_SQL);
               oci_execute($T_Stid);
               $tmpB=unserialize(serialize($Obj_B));

               $DATESEQANCE_FL="";
               $ID_STATION="";
               $tmpB->FORMSEQ=$FreSeq;
               $tmpB->DT_START=$DT_START;
               while (oci_fetch_array($T_Stid)){
                   $CID_CONTORL=oci_result($T_Stid,'CID_CONTORL');
                   $ID_STATION=oci_result($T_Stid,'ID_STATION');
                   $ST_VALUE=oci_result($T_Stid,'ST_VALUE');
                   $DATESEQANCE_FL=oci_result($T_Stid,'DATESEQANCE_FL');
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
               $tmpB->SSTAT=$ID_STATION;
               $tmpB->DATESEQ=$DATESEQANCE_FL;
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

   function PosBSORCancel($conn,$sFm,$sPg,$Num,$sTraID,$sUr){

       $DateTime = date("YmdHis");
       $Y_VID = substr($DateTime, 0, 4);
       $Date = substr($DateTime, -10, 10);
       $Y_TW = (int)$Y_VID - 1911;
       $System_DT= (string)$Y_TW .(string)$Date;

       $SQL="SELECT ID_PATIENT,ID_INPATIENT,ST_DATAA,ST_DATAB,DT_EXCUTE,TM_EXCUTE
               FROM  HIS803.NISWSTP
               WHERE ID_TABFORM = :ID_TABFORM  
               AND ID_TRANSACTION = :ID_TRANSACTION";


       $stid=oci_parse($conn,$SQL);

       oci_bind_by_name($stid,':ID_TABFORM',$sFm);
       oci_bind_by_name($stid,':ID_TRANSACTION',$sTraID);

       oci_execute($stid,OCI_NO_AUTO_COMMIT);


       $ID_PATIENT="";
       $ID_INPATIENT="";
       $DT_EXCUTE="";
       $TM_EXCUTE="";
       $ST_DATA="";
       $CID_BEDSORE=substr($sFm,0,1);
       while (oci_fetch_array($stid)){
           $ID_PATIENT=oci_result($stid,'ID_PATIENT');
           $ID_INPATIENT=oci_result($stid,'ID_INPATIENT');
           $DT_EXCUTE=oci_result($stid,'DT_EXCUTE');
           $TM_EXCUTE=oci_result($stid,'TM_EXCUTE');
           $ST_DATA=oci_result($stid,'ST_DATAB')->load();
       }

      $obj=json_decode($ST_DATA);
      $respon=json_decode(json_encode(array("result"=>"","message"=>"")));
       if ($sFm=="BSOR" || $sFm=="CUTS")
       {
           if ($sPg=="A"){
               $map_Num=array_filter($obj,function ($value)use ($Num){
                   if ($value->TB_DATA->NO_NUM->VALUE==$Num){
                       return $value->FORMSEQ;
                   }
                   return [];
               });
              sort($map_Num);
               $FORMSEQANCE_BS=$map_Num[0]->FORMSEQ;
               $DATESEQANCE_FL=$map_Num[0]->DATESEQ;

               $bid_BSOR=array(
                   ":DM_CANCD"=>$System_DT,
                   ":UR_CANCD"=>$sUr,
                   ":ID_PATIENT"=>$ID_PATIENT,
                   ":ID_INPATIENT"=>$ID_INPATIENT,
                   ":NO_BEDSORE"=>$Num,
                   ":CID_BEDSORE"=>$CID_BEDSORE,
                   ":UR_PROCESS"=>$sUr,
                   ":FORMSEQANCE_BS"=>$FORMSEQANCE_BS
               );

               $bid_TBBS=array(
                   ":DM_CANCD"=>$System_DT,
                   ":UR_CANCD"=>$sUr,
                   ":DATESEQANCE_FL"=>$DATESEQANCE_FL,
                   ":DT_EXCUTE"=>$DT_EXCUTE,
                   ":TM_EXCUTE"=>$TM_EXCUTE,
                   ":UR_PROCESS"=>$sUr
               );

               $bid_TIBS=array(
                   ":DM_CANCD"=>$System_DT,
                   ":UR_CANCD"=>$sUr,
                   ":DATESEQANCE_FL"=>$DATESEQANCE_FL,
                   ":FORMSEQANCE_BS"=>$FORMSEQANCE_BS
               );

               $BSOR_re=QueryToCancel($conn,'BSOR',$bid_BSOR);
               if($BSOR_re->result=="false"){
                   $respon->message=$BSOR_re->message;
                   return json_encode($respon);
               }

               $TBBS_re=QueryToCancel($conn,'TBBS',$bid_TBBS);
               if($TBBS_re->result=="false"){
                   $respon->message=$TBBS_re->message;
                   return json_encode($respon);
               }

               $TIBS_re=QueryToCancel($conn,'TIBS',$bid_TIBS);
               if($TIBS_re->result=="false"){
                   $respon->message=$TIBS_re->message;
                   return json_encode($respon);
               }

       }
           else{
           foreach ($obj as $value)
           {
               $DATESEQANCE_FL=$value->DATESEQ;
               $FORMSEQANCE_BS=$value->FORMSEQ;

               $bid_TBBS=array(
                   ":DM_CANCD"=>$System_DT,
                   ":UR_CANCD"=>$sUr,
                   ":DATESEQANCE_FL"=>$DATESEQANCE_FL,
                   ":DT_EXCUTE"=>$DT_EXCUTE,
                   ":TM_EXCUTE"=>$TM_EXCUTE,
                   ":UR_PROCESS"=>$sUr
               );

               $bid_TIBS=array(
                   ":DM_CANCD"=>$System_DT,
                   ":UR_CANCD"=>$sUr,
                   ":DATESEQANCE_FL"=>$DATESEQANCE_FL,
                   ":FORMSEQANCE_BS"=>$FORMSEQANCE_BS
               );


               $TBBS_re=QueryToCancel($conn,'TBBS',$bid_TBBS);
               if($TBBS_re->result=="false"){
                   $respon->message=$TBBS_re->message;
                   return json_encode($respon);
               }

               $TIBS_re=QueryToCancel($conn,'TIBS',$bid_TIBS);
               if($TIBS_re->result=="false"){
                   $respon->message=$TIBS_re->message;
                   return json_encode($respon);
               }
           }
       }
       }
       else{
           $map_Num=array_filter($obj,function ($value)use ($Num){
               return $value->TB_DATA->NO_NUM->VALUE==$Num;
           })[0];
           $DATESEQ_FL=$map_Num->DATESEQ;
           $FORMSEQ=$map_Num->FORMSEQ;
          // $tB_DATA=$map_Num->TB_DATA;


           $TUPG_DATA=array(":DM_ENDING"=>$System_DT,":UR_ENDING"=>$sUr,":FORMSEQANCE"=>$FORMSEQ,
                         ":ID_PATIENT"=>$ID_PATIENT,":ID_INPATIENT"=>$ID_INPATIENT,":NO_PROBLEM"=>$Num);

           $TUPT_DATA=array(":DM_ENDING"=>$System_DT,":UR_ENDING"=>$sUr,":FORMSEQANCE"=>$FORMSEQ,
                           ":DATESEQANCE_FL"=>$DATESEQ_FL);

           $TUPI_DATA=array(":DM_ENDING"=>$System_DT,":UR_ENDING"=>$sUr,":DATESEQANCE_FL"=>$DATESEQ_FL);


           $TUPG_result=DB_Cancel($conn,'TUPG',$TUPG_DATA);
           if ($TUPG_result->result=="false"){
               $respon->result="false";
               $respon->message=$TUPG_result->message;
               oci_rollback($conn);
               return json_encode($respon,JSON_UNESCAPED_UNICODE);
           }

           $TUPT_result=DB_Cancel($conn,'TUPT',$TUPT_DATA);
           if ($TUPT_result->result=="false"){
               $respon->result="false";
               $respon->message=$TUPT_result->message;
               oci_rollback($conn);
               return json_encode($respon,JSON_UNESCAPED_UNICODE);
           }

           $TUPI_result=DB_Cancel($conn,'TUPI',$TUPI_DATA);
           if ($TUPI_result->result=="false"){
               $respon->result="false";
               $respon->message=$TUPI_result->message;
               oci_rollback($conn);
               return json_encode($respon,JSON_UNESCAPED_UNICODE);
           }
       }

       $respon->result="true";
       oci_commit($conn);
       return json_encode($respon,JSON_UNESCAPED_UNICODE);
   }

   function UpDateToDTEND($conn,$IdPt,$IdInPt,$CID_BEDSORE,$FORMSEQ_BS,$sDt,$ED_TYPE){
       $SQL=" UPDATE  NSBSOR  
               SET   DT_END=:DT_END , TID_ENDSTATE=:TID_ENDSTATE
               WHERE ID_PATIENT= :ID_PATIENT
               AND ID_INPATIENT=:ID_INPATIENT
               AND FORMSEQANCE_BS=:FORMSEQANCE_BS
               AND CID_BEDSORE=:CID_BEDSORE
               AND DT_END=' '   
               AND DM_CANCD=' '      ";
       $response=json_decode(json_encode(array("result"=>"true","message"=>"")));

       $data=array(":DT_END"=>$sDt,
           ":TID_ENDSTATE"=>$ED_TYPE,
           ":ID_PATIENT"=>$IdPt,
           ":ID_INPATIENT"=>$IdInPt,
           ":FORMSEQANCE_BS"=>$FORMSEQ_BS,
           ":CID_BEDSORE"=>$CID_BEDSORE);
       $stid=oci_parse($conn,$SQL);

       if (!$stid){
           $response->result="false";
           $response->message=oci_error($conn)['message'];
           return $response;
       }

       foreach ($data as $key=>$value){
           oci_bind_by_name($stid,$key,$data[$key]);
       }
       $excute=oci_execute($stid);
       if (!$excute){
           $response->result="false";
           $response->message=oci_error($stid)['message'];
           return $response;
       }
       return $response;
   }
   /*取該編號對應之值*/
function GetNumData($Arr,$Num){
    $result="";
    foreach ($Arr as $item){
        if ($item->TB_DATA->NO_NUM->VALUE==$Num){
            $result= $item;
            break;
        }
    }
    print_r($result);
    return $result;
}

/*改變瘡=>傷*/
function ChangeChr($conn){


    $SQL=" SELECT IS_ACTIVE FROM NSCLSI WHERE CID_CLASS = 'BSOR' and IS_ACTIVE = 'Y' AND ST_TEXT1 = 'RETITLE'";
    $stid=oci_parse($conn,$SQL);
    oci_execute($stid);
    $is_Change="";
    while (oci_fetch_array($stid)){
        $is_Change=oci_result($stid,'IS_ACTIVE');
    }
    return $is_Change;
}

/*有效時間取得的部位*/
function GetNoRegion($conn,$sFm,$ST_DATAA,$ST_DATAB,$Parameter){
    $obj_A=json_decode($ST_DATAA);
    $obj_B=json_decode($ST_DATAB);

    $arr_A=[];
    $arr_B=[];

    if ($sFm=="TUPT"){

        $SQL="SELECT DISTINCT PT.DATESEQANCE_FL,PG.FORMSEQANCE,PG.NO_PROBLEM,PG.ID_ORGAN,
                PG.NM_ORGAN,PI.DT_EXECUTE,PI.DT_ENDING,
                PG.IT_TOP,PG.IT_LEFT,PG.IT_WIDTH,PG.IT_HEIGTH,PT.IT_TERMDAYS,
                PT.ST_TUBE,PT.NM_BRAND,PI.ST_DEPTH,
                PT.ID_TUBE,PT.NM_TUBE,PT.CD_STATUS
                FROM  NSTUPG PG, NSTUPT PT,NSTUPI PI
                WHERE PG.FORMSEQANCE=PT.FORMSEQANCE
                AND PT.DATESEQANCE_FL=PI.DATESEQANCE_FL
                AND PG.ID_PATIENT=:ID_PATIENT
                AND PG.ID_INPATIENT=:ID_INPATIENT
                AND PI.IS_DRAWOUT ='N' 
                AND PG.IS_CANCD='N' AND PT.IS_CANCD='N' AND PI.IS_CANCD='N' 
                AND PG.DM_ENDING=' ' AND PT.DM_ENDING=' ' AND PI.DM_ENDING=' '
               ORDER BY PG.NO_PROBLEM DESC
            ";

    }
    else{

        $SQL="SELECT DISTINCT NSTBBS.FORMSEQANCE_BS,NSBSOR.DT_START,NSBSOR.NO_BEDSORE,TID_SOURCE,NM_ORGAN,IT_TOP,IT_LEFT,IT_WIDTH,IT_HEIGTH
            FROM NSBSOR, NSTBBS 
            WHERE NSTBBS.FORMSEQANCE_BS = NSBSOR.FORMSEQANCE_BS
              AND NSBSOR.ID_PATIENT = :ID_PATIENT AND NSBSOR.ID_INPATIENT = :ID_INPATIENT
              AND NSBSOR.DM_CANCD = ' ' AND NSTBBS.DM_CANCD = ' '
              AND NSBSOR.CID_BEDSORE = :CID_BEDSORE
              AND DT_END=' '
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
    }

    $stid=oci_parse($conn,$SQL);
    foreach ($Parameter as $key=>$value){
        oci_bind_by_name($stid,$key,$Parameter[$key]);
    }

    oci_execute($stid);

        $count=0;
        while (oci_fetch_array($stid)){
            //序列化後複製
            $tmp_A = unserialize(serialize($obj_A));
            $tmp_B = unserialize(serialize($obj_B));

            $LEFT=oci_result($stid,'IT_LEFT');
            $TOP=oci_result($stid,'IT_TOP');
            $W_TH=oci_result($stid,'IT_WIDTH');
            $H_TH=oci_result($stid,'IT_HEIGTH');

            $tmp_A->TOP=$TOP;
            $tmp_A->LEFT=$LEFT;
            $tmp_A->W_TH=$W_TH;
            $tmp_A->H_TH=$H_TH;

            if ($sFm=="TUPT"){

                $DATESEQANCE_FL=oci_result($stid,'DATESEQANCE_FL');
                $FORMSEQANCE=oci_result($stid,'FORMSEQANCE');//表單編號
                $NO_PROBLEM=oci_result($stid,'NO_PROBLEM');//部位編號
                $NM_ORGAN=oci_result($stid,'NM_ORGAN');//部位名稱
                $ID_ORGAN=oci_result($stid,'ID_ORGAN');//部位代碼
                $NM_TUBE=oci_result($stid,'NM_TUBE');//管路名稱
                $ID_TUBE=oci_result($stid,'ID_TUBE');//管路ID
                $IT_TERMDAYS=oci_result($stid,'IT_TERMDAYS');//預計拔管天數
                $ST_TUBE=oci_result($stid,'ST_TUBE');//型號
                $NM_BRAND=oci_result($stid,'NM_BRAND');//廠商
                $ST_DEPTH=oci_result($stid,'ST_DEPTH');//深度
                $CD_STATUS=oci_result($stid,'CD_STATUS');//入院帶入
                $DT_EXECUTE=oci_result($stid,'DT_EXECUTE');//置入日期
                $DT_ENDING=oci_result($stid,'DT_ENDING');//換管日期

                $tmp_A->NUM=$NO_PROBLEM;
                $tmp_A->FORMSEQ=$FORMSEQANCE;
                $tmp_A->NM_ORGAN=$NM_ORGAN;
                $tmp_A->ID_REGION=$ID_ORGAN;


                $tmp_B->DATESEQ=$DATESEQANCE_FL;
                $tmp_B->FORMSEQ=$FORMSEQANCE;
                $tmp_B->TB_DATA->NO_NUM->VALUE=$NO_PROBLEM;
                $tmp_B->TB_DATA->NM_ORGAN->VALUE=$NM_ORGAN;
                $tmp_B->TB_DATA->sNM_TUBE->VALUE=$NM_TUBE;
                $tmp_B->TB_DATA->ID_TUBE->VALUE=$ID_TUBE;
                $tmp_B->TB_DATA->sST_TUBE->VALUE=$ST_TUBE;
                $tmp_B->TB_DATA->DEPTH->VALUE=$ST_DEPTH;
                $tmp_B->TB_DATA->sDT_EXE->VALUE=trim($DT_EXECUTE);
                $tmp_B->TB_DATA->sDT_END->VALUE=trim($DT_ENDING);
                $tmp_B->TB_DATA->IT_TERMDAYS->VALUE=$IT_TERMDAYS;
                $tmp_B->TB_DATA->CD_STATUS->VALUE=$CD_STATUS;
                $tmp_B->TB_DATA->MB_RAND->VALUE=$NM_BRAND;



            }
            else{
                $FORMSEQANCE_BS=oci_result($stid,'FORMSEQANCE_BS');//表單編號
                $NO_BEDSORE=oci_result($stid,'NO_BEDSORE');//部位編號
                $NM_ORGAN=oci_result($stid,'NM_ORGAN');//部位名稱
                $TID_SOURCE=oci_result($stid,'TID_SOURCE');//發生來源
                $DT_START=oci_result($stid,'DT_START');//開始日

                $tmp_A->NUM=$NO_BEDSORE;
                $tmp_A->FORMSEQ=$FORMSEQANCE_BS;
                $tmp_A->NM_ORGAN=$NM_ORGAN;


                $tmp_B->TB_DATA->NO_NUM->VALUE=$NO_BEDSORE;
                $tmp_B->TB_DATA->NM_ORGAN->VALUE=$NM_ORGAN;
                $tmp_B->TB_DATA->TID_SOURCE->VALUE=$TID_SOURCE;
                $tmp_B->FORMSEQ=$FORMSEQANCE_BS;
                $tmp_B->DT_START=$DT_START;
            }

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
                                EMPTY_CLOB(),EMPTY_CLOB(),EMPTY_CLOB(),EMPTY_CLOB(),' ',' ',' ',' ',
                               :BED,:DM_P,:UR_P,:NSRANK,:FormSeq)
                               RETURNING  ST_DATAA,ST_DATAB,ST_DATAC,ST_DATAD
                                INTO :ST_DATAA,:ST_DATAB,:ST_DATAC,:ST_DATAD";
    $TP_Stid = oci_parse($conn, $SQL);
    if(!$TP_Stid){
        $e=oci_error($conn);
        return $e['message'];
    }
    $clobA=oci_new_descriptor($conn,OCI_D_LOB);
    $clobB=oci_new_descriptor($conn,OCI_D_LOB);
    $clobC=oci_new_descriptor($conn,OCI_D_LOB);
    $clobD=oci_new_descriptor($conn,OCI_D_LOB);

    oci_bind_by_name($TP_Stid,":ST_DATAA",$clobA,-1,OCI_B_CLOB);
    oci_bind_by_name($TP_Stid,":ST_DATAB",$clobB,-1,OCI_B_CLOB);
    oci_bind_by_name($TP_Stid,":ST_DATAC",$clobC,-1,OCI_B_CLOB);
    oci_bind_by_name($TP_Stid,":ST_DATAD",$clobD,-1,OCI_B_CLOB);

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
    $clobC->save(json_encode($data['A'],JSON_UNESCAPED_UNICODE));
    $clobB->save(json_encode($data['B'],JSON_UNESCAPED_UNICODE));
    $clobD->save(json_encode($data['B'],JSON_UNESCAPED_UNICODE));


    oci_free_statement($TP_Stid);
    oci_commit($conn);
    return true;
}
function UpdateTP($conn,$Flag,$Data){

    if ($Flag=='Y'){
        $SQL="UPDATE NISWSTP 
          SET 
              ST_DATAA=:ST_DATAA,
              ST_DATAB=:ST_DATAB,
              ST_DATAC=:ST_DATAC,
              ST_DATAD=:ST_DATAD
          WHERE ID_TABFORM=:ID_TABFORM 
          AND  ID_TRANSACTION=:ID_TRANSACTION";
    }
    else{
        $SQL="UPDATE NISWSTP 
          SET ST_DATAB=:ST_DATAB,
              ST_DATAD=:ST_DATAD
          WHERE ID_TABFORM=:ID_TABFORM 
          AND  ID_TRANSACTION=:ID_TRANSACTION";
    }


    $stid=oci_parse($conn,$SQL);

    $Result=json_decode(json_encode(array("result"=>"true","message"=>""),JSON_UNESCAPED_UNICODE));

    foreach ($Data as $key=>$value){
        oci_bind_by_name($stid,$key,$Data[$key]);
    }
    if (!$stid){
        $Result->result="false";
        $Result->message=oci_error($conn)['message'];
        return $Result;
    }
    $execute=oci_execute($stid,OCI_NO_AUTO_COMMIT);
    if (!$execute){
        $Result->result="false";
        $Result->message=oci_error($stid)['message'];
        return $Result;
    }
    return $Result;
}
/*TUPT=>管路名稱 other=> 發生來源*/
function GetStationOrder($conn,$sFm,$CNM_arr){
     if ($sFm=="TUPT"){
         $SQL="SELECT * FROM (
                    SELECT ID_TUBE, NM_TUBE, IT_TERMDAYS,IS_IO
                    FROM NSTUMI
                  WHERE (DM_ENDING = ' ' OR DM_ENDING > NIS_DM_PROCESS)
                  AND IS_CANCD = 'N'
                  AND ID_TUBE <> 'XXX'
                  ORDER BY Upper(NM_TUBE), SR_TUBE)
                UNION ALL
                  SELECT ID_TUBE, NM_TUBE, IT_TERMDAYS ,IS_IO
                    FROM NSTUMI
                  WHERE (DM_ENDING = ' ' OR DM_ENDING > NIS_DM_PROCESS)
                  AND IS_CANCD = 'N'
                  AND ID_TUBE = 'XXX'" ;
     }else{
         $SQL=" SELECT ID_STATION, NM_STATION FROM NIS_V_HNST_Q0 ORDER BY ID_STATION";
     }

    $stid=oci_parse($conn,$SQL);
    oci_execute($stid);

    $NEW_arr=json_decode($CNM_arr);
    while (oci_fetch_array($stid)){
        if ($sFm=="TUPT"){
            $ID_TUBE=oci_result($stid,'ID_TUBE');
            $NM_TUBE=oci_result($stid,'NM_TUBE');
            $IT_TERMDAYS=oci_result($stid,'IT_TERMDAYS');
            $IS_IO=oci_result($stid,'IS_IO');

            array_push( $NEW_arr[2],array("ID_TABITEM"=>$ID_TUBE,"ST_LEFT"=>$NM_TUBE,"IT_TERMDAYS"=>$IT_TERMDAYS,"IS_IO"=>$IS_IO));
        }
        else{
            $ID_STATION=oci_result($stid,'ID_STATION');
            $NM_STATION=oci_result($stid,'NM_STATION');


            array_push( $NEW_arr[2],array("ID_TABITEM"=>$ID_STATION,"ST_LEFT"=>$NM_STATION));
        }


    }

    return $NEW_arr;
}

/*BSOR TUPG存檔*/
function DB_INSERT($conn,$Qry_Nm,$dataArr){
    $SQL="";
    if ($Qry_Nm=="BSOR"){
        $SQL="INSERT INTO NSBSOR
                (DATESEQANCE_FL,FORMSEQANCE_BS,ID_PATIENT,ID_INPATIENT,DT_REGISTER,NO_OPDSEQ,NO_BEDSORE,CID_BEDSORE,DT_START,DT_END,
                TID_SOURCE,ID_STATION,NM_ORGAN,TID_ENDSTATE,IT_TOP,IT_LEFT,IT_WIDTH,IT_HEIGTH,
                ID_BED,JID_NSRANK,FORMSEQANCE_WT,DM_PROCESS,UR_PROCESS,DM_CANCD,UR_CANCD)
                VALUES
                (:DATESEQANCE_FL,:FORMSEQANCE_BS,:ID_PATIENT,:ID_INPATIENT,:DT_REGISTER,:NO_OPDSEQ,:NO_BEDSORE,:CID_BEDSORE,:DT_START,:DT_END,
                :TID_SOURCE,:ID_STATION,:NM_ORGAN,:TID_ENDSTATE,:IT_TOP,:IT_LEFT,:IT_WIDTH,:IT_HEIGTH,
                :ID_BED,:JID_NSRANK,:FORMSEQANCE_WT,:DM_PROCESS,:UR_PROCESS,:DM_CANCD,:UR_CANCD
                )";

    }
    else if ($Qry_Nm=="TBBS"){

        $SQL="INSERT INTO NSTBBS (DATESEQANCE_FL,FORMSEQANCE_BS,DT_EXCUTE,TM_EXCUTE,ID_BED,FORMSEQANCE_WT,
                                 DM_PROCESS,UR_PROCESS,DM_CANCD,UR_CANCD,JID_NSRANK)
                  VALUES(:DATESEQANCE_FL,:FORMSEQANCE_BS,:DT_EXCUTE,:TM_EXCUTE,:ID_BED,:FORMSEQANCE_WT,
                                :DM_PROCESS,:UR_PROCESS,:DM_CANCD,:UR_CANCD,:JID_NSRANK)";

    }
    else if ($Qry_Nm=="TUPG"){
        $SQL="INSERT INTO NSTUPG(
                                DATESEQANCE,FORMSEQANCE,ID_PATIENT,ID_INPATIENT,NO_OPDSEQ,DT_REGISTER,NO_PROBLEM,ID_ORGAN,NM_ORGAN,
                                IT_TOP,IT_LEFT,IT_WIDTH,IT_HEIGTH,IS_CANCD,ID_BED,JID_NSRANK,
                                FORMSEQANCE_WT,DM_PROCESS,UR_PROCESS,DM_ENDING,UR_ENDING)
                VALUES(:DATESEQANCE,:FORMSEQANCE,:ID_PATIENT,:ID_INPATIENT,:NO_OPDSEQ,:DT_REGISTER,:NO_PROBLEM,:ID_ORGAN,:NM_ORGAN,
                       :IT_TOP,:IT_LEFT,:IT_WIDTH,:IT_HEIGTH,:IS_CANCD,:ID_BED,:JID_NSRANK,
                       :FORMSEQANCE_WT,:DM_PROCESS,:UR_PROCESS,' ',' ')";
    }
    else if ($Qry_Nm=="TUPI"){
        $SQL="INSERT INTO NSTUPI
            (DATESEQANCE,DATESEQANCE_FL,DT_EXECUTE,TM_EXECUTE,DT_ENDING,TM_ENDING,IS_FIRST,IS_DRAWOUT,IS_CANCD,
            ID_BED,JID_NSRANK,FORMSEQANCE_WT,DM_PROCESS,UR_PROCESS,DM_ENDING,UR_ENDING,ST_DEPTH)
            VALUES
            (:DATESEQANCE,:DATESEQANCE_FL,:DT_EXECUTE,:TM_EXECUTE,:DT_ENDING,:TM_ENDING,:IS_FIRST,:IS_DRAWOUT,:IS_CANCD,
            :ID_BED,:JID_NSRANK,:FORMSEQANCE_WT,:DM_PROCESS,:UR_PROCESS,:DM_ENDING,:UR_ENDING,:ST_DEPTH)";

    }
    else if ($Qry_Nm=="TUPT"){
        $SQL="INSERT INTO NSTUPT
                (DATESEQANCE_FL,FORMSEQANCE,DT_EXECUTE,TM_EXECUTE,ID_TUBE,ST_TUBE,IT_TERMDAYS,
                    DT_ENDING,TM_ENDING,IS_CANCD,ID_BED,JID_NSRANK,FORMSEQANCE_WT,
                    DM_PROCESS,UR_PROCESS,DM_ENDING,UR_ENDING,CD_STATUS,NM_TUBE,NM_BRAND)
               VALUES (:DATESEQANCE_FL,:FORMSEQANCE,:DT_EXECUTE,:TM_EXECUTE,:ID_TUBE,:ST_TUBE,:IT_TERMDAYS,
                    :DT_ENDING,:TM_ENDING,:IS_CANCD,:ID_BED,:JID_NSRANK,:FORMSEQANCE_WT,
                    :DM_PROCESS,:UR_PROCESS,:DM_ENDING,:UR_ENDING,:CD_STATUS,:NM_TUBE,:NM_BRAND)";

    }

    $Result=json_decode(json_encode(array("result"=>"true","message"=>""),JSON_UNESCAPED_UNICODE));

    $stid=oci_parse($conn,$SQL);

    foreach ($dataArr as $key=>$value){
        oci_bind_by_name($stid,$key,$dataArr[$key]);
    }
    if (!$stid){
        $Result->result="false";
        $Result->message=oci_error($conn)['message'];
        return $Result;
    }



    $result=oci_execute($stid,OCI_NO_AUTO_COMMIT);
    if (!$result){

        $Result->result="false";
        $Result->message=oci_error($stid)['message'];
        return $Result;
    }
    return $Result;
}
/*做廢*/

function QueryToCancel($conn,$tbName,$bind_arr){
    $response=json_decode(json_encode(array("result"=>"","message"=>""),JSON_UNESCAPED_UNICODE));
    $SQL="  UPDATE NS".$tbName." SET
            DM_CANCD=".":DM_CANCD,".
          " UR_CANCD=".":UR_CANCD".
          " WHERE ";

    if ($tbName=="BSOR"){

        $SQL.=" ID_PATIENT=:ID_PATIENT
                AND ID_INPATIENT=:ID_INPATIENT
                AND CID_BEDSORE=:CID_BEDSORE
                AND UR_PROCESS=:UR_PROCESS
                AND NO_BEDSORE=:NO_BEDSORE
                AND FORMSEQANCE_BS=:FORMSEQANCE_BS
                AND DM_CANCD=' '";
    }
    if ($tbName=="TBBS"){
        $SQL.=" DATESEQANCE_FL=:DATESEQANCE_FL
                AND DT_EXCUTE=:DT_EXCUTE
                AND TM_EXCUTE=:TM_EXCUTE
                AND UR_PROCESS=:UR_PROCESS
                AND DM_CANCD=' '";

    }
    if ($tbName=="TIBS"){
        $SQL.=" DATESEQANCE_FL=:DATESEQANCE_FL
                AND FORMSEQANCE_BS=:FORMSEQANCE_BS
                AND DM_CANCD=' '";
    }


    $stid=oci_parse($conn,$SQL);
    if (!$stid){
        $response->result="false";
        $response->message=oci_error($conn)['message'];
        oci_rollback($conn);
        return $response;
    }
    foreach ($bind_arr as $key=>$value){
        oci_bind_by_name($stid,$key,$bind_arr[$key]);
    }
    $excute=oci_execute($stid,OCI_NO_AUTO_COMMIT);
    if (!$excute){
        $response->result="false";
        $response->message=oci_error($conn)['message'];
        oci_rollback($conn);
        return $response;
    }
    $response->result="true";
    return $response;
}

function DB_Cancel($conn,$Qry_Nm,$dataArr){
    $SQL="";
    $Result=json_decode(json_encode(array("result"=>"true","message"=>""),JSON_UNESCAPED_UNICODE));
    if ($Qry_Nm=="BSOR"){

        $SQL="
          UPDATE NSBSOR
          SET DM_CANCD=:DM_CANCD,
              UR_CANCD=:UR_CANCD
          WHERE 
          ID_PATIENT=:ID_PATIENT
          AND ID_INPATIENT= :ID_INPATIENT
          AND FORMSEQANCE_BS=:FORMSEQANCE_BS
          AND CID_BEDSORE=:CID_BEDSORE 
          AND DM_CANCD=' '";
    }
    else if ($Qry_Nm=="TBBS"){
        $SQL="
              UPDATE  NSTBBS
              SET DM_CANCD=:DM_CANCD,
                  UR_CANCD=:UR_CANCD
              WHERE FORMSEQANCE_BS=:FORMSEQANCE_BS
              AND ID_BED=:ID_BED
              AND DM_CANCD=' '
              ";
    }

    else if ($Qry_Nm=="TUPG"){
        $SQL="UPDATE NSTUPG 
              SET IS_CANCD='Y', 
                  DM_ENDING=:DM_ENDING,
                  UR_ENDING=:UR_ENDING   
              WHERE FORMSEQANCE=:FORMSEQANCE
              AND ID_PATIENT=:ID_PATIENT 
              AND ID_INPATIENT=:ID_INPATIENT 
              AND NO_PROBLEM=:NO_PROBLEM 
              AND IS_CANCD='N'
                ";

    }
    else if ($Qry_Nm=="TUPT"){
        $SQL="UPDATE NSTUPT 
              SET IS_CANCD='Y',
                  DM_ENDING=:DM_ENDING,
                  UR_ENDING=:UR_ENDING 
              WHERE DATESEQANCE_FL=:DATESEQANCE_FL
              AND FORMSEQANCE=:FORMSEQANCE
              AND IS_CANCD='N'";

    }
    else if ($Qry_Nm=="TUPI"){
        $SQL="UPDATE NSTUPI 
              SET IS_CANCD='Y',
                  DM_ENDING=:DM_ENDING,
                  UR_ENDING=:UR_ENDING 
               WHERE DATESEQANCE_FL=:DATESEQANCE_FL 
               AND IS_DRAWOUT='N'
               AND IS_CANCD='N'";

    }

    $stid=oci_parse($conn,$SQL);
    foreach ($dataArr as $key=>$value){
        oci_bind_by_name($stid,$key,$dataArr[$key]);
    }
    if (!$stid){
        $Result->result="false";
        $Result->message=oci_error($conn)['message'];
        return $Result;
    }
    $result=oci_execute($stid,OCI_NO_AUTO_COMMIT);
    if (!$result){
        $Result->result="false";
        $Result->message=oci_error($stid)['message'];
        return $Result;
    }
    return $Result;
}

/*TIBS存檔*/
function InsertTIBS($conn,$obj,$sfm,$sUr,$DATESEQANCE_FL,$FORMSEQANCE_BS){
    $Result= json_decode(json_encode(array("result"=>"true","message"=>""),JSON_UNESCAPED_UNICODE));

    foreach($obj as $value){
       foreach ($value->TB_DATA as $item){
           $ID_TABITEM=$item->ID; //評估表單代碼
           $DATESEQANCE=GetDataSEQ($conn);

           if ($ID_TABITEM){
               if ($ID_TABITEM=="BSOR000001" || $ID_TABITEM=="BSOR000036" || $ID_TABITEM=="BSOR000043" || $ID_TABITEM=="BSOR000045"){
                   $CID_TABNAME='NSBSOR';
               }else{
                   $CID_TABNAME='NSTBBS';
               }

               $ELE_STAT=$item->TYPE===""?" ":$item->TYPE;//元件名稱 ED=>input CB=>CHECKBOX
               $ST_VALUE=$item->VALUE===""?" ":$item->VALUE;//欄位值

               if ($ID_TABITEM=="BSOR000035"){
                   $ST_VALUE=$sUr; //評估人員
               }

               $SQL="INSERT INTO NSTIBS
                    (DATESEQANCE,DATESEQANCE_FL,ID_TABITEM,FORMSEQANCE_BS,CID_TABNAME,
                    ID_TABFORM,CID_CONTORL,IS_CHELDED,ST_VALUE,MM_VALUE,DM_CANCD,UR_CANCD)
                    VALUES
                    ('$DATESEQANCE','$DATESEQANCE_FL','$ID_TABITEM','$FORMSEQANCE_BS','$CID_TABNAME',
                    '$sfm','$ELE_STAT',' ','$ST_VALUE',' ',' ',' ')
                    ";

               $stid=oci_parse($conn,$SQL);
               if (!$stid){
                   $Result->result="false";
                   $Result->message=oci_error($conn)['message'];
                  return $Result;
               }
               $execute=oci_execute($stid,OCI_NO_AUTO_COMMIT);
               if (!$execute){
                   $Result->result="false";
                   $Result->message=oci_error($stid)['message'];
                   return $Result;
               }
           }
       }
    }


    return $Result;
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
/*取最大編號*/
function MaxNumber($conn,$sfm,$Idpt,$INPt){
    $sNO="";
    $sTable="";
    $sCanCD="";
    $CID_BS=substr($sfm,0,1);
    if ($sfm=="BSOR" || $sfm=="CUTS"){
        $sTable = 'NSBSOR';
        $sNO = 'NO_BEDSORE';
        $sCanCD = 'UR_CANCD';
    }else if ($sfm=="TUPT"){
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
/*取有變化的obj*/
function obj_diff($Page,$new_obj,$default_obj){
    $oldOBJ=json_decode(json_encode($default_obj)); //舊資料
    $newOBJ=json_decode(json_encode($new_obj));//新資料
    $result=array("OLD_DATA"=>[],"NEW_DATA"=>[]);
    if ($Page=="A"){
        $oldOBJ=array_filter($oldOBJ,function ($value){return $value->NUM!="";});
        $newOBJ=array_filter($new_obj,function ($value){return $value->NUM!="";});

        if (count($oldOBJ)==0){
            sort($newOBJ);
            $result['NEW_DATA']=$newOBJ;
        }else{
            $diff=[];
            $newArr=[];

            foreach ($newOBJ as $value){
                // $value->NUM
                $new_obj_Num=$value->NUM;

                $oldItem=array_filter($oldOBJ,function ($val)use($new_obj_Num){
                    return $val->NUM==$new_obj_Num;
                });

                if (count($oldItem)>0){

                    sort($oldItem);
                    $old_OBJ=$oldItem[0];
                    if ($old_OBJ->NM_ORGAN != $value->NM_ORGAN ||
                        $old_OBJ->LEFT != $value->LEFT ||
                        $old_OBJ->TOP != $value->TOP ||
                        $old_OBJ->W_TH != $value->W_TH ||
                        $old_OBJ->H_TH != $value->H_TH){


                        array_push($diff,$old_OBJ);
                        array_push($newArr,$value);

                    }else{
                        array_push($newArr,$value);
                    }


                }else{
                    //新增
                    array_push($newArr,$value);
                }

           /*     foreach ($oldOBJ as $item){
                    if ($new_obj_Num == $item->NUM){
                        if ($value->NM_ORGAN != $item->NM_ORGAN ||
                            $value->LEFT != $item->LEFT ||
                            $value->TOP != $item->TOP ||
                            $value->W_TH != $item->W_TH ||
                            $value->H_TH != $item->H_TH
                          ){
                            //名稱,座標位置,大小被修改
                            array_push($diff,$item);
                            array_push($newArr,$value);

                           }else{
                            array_push($newArr,$value);
                        }
                    }
                }*/
            }

            $result['OLD_DATA']=$diff;
            $result['NEW_DATA']=$newArr;
        }

    }
    else if ($Page=="B"){
        $oldOBJ=array_filter($oldOBJ,function ($value){return $value->TB_DATA->NO_NUM->VALUE!="";});
        $newOBJ=array_filter($new_obj,function ($value){return $value->TB_DATA->NO_NUM->VALUE!="";});

        if (count($oldOBJ)==0){
            sort($newOBJ);
            $result['NEW_DATA']=$newOBJ;
        }
        else{
               //$newOBJ
              //  $oldOBJ
            $newOBJ=array_map(function ($value){return $value->TB_DATA;},$newOBJ);
            $oldOBJ=array_map(function ($value){return $value->TB_DATA;},$oldOBJ);
            $diff=[];
            $newArr=[];





            foreach ($newOBJ as $value){
                $new_Num=$value->NO_NUM->VALUE;
                $filter_oldObj=array_filter($oldOBJ,function ($val) use ($new_Num){return $val->NO_NUM->VALUE==$new_Num;});

                if (count($filter_oldObj)>0){
                    foreach ($filter_oldObj[0] as $key=>$item){
                        if ($item->VALUE !=$value->$key->VALUE){
                            array_push($diff,$filter_oldObj[0]);
                            array_push($newArr,$value);
                            break;
                        }

                    }

                }else{
                    //新編號=>新增
                    array_push($newArr,$value);
                }

            }
            // 依照編號回壓dtseq forseq
            //$diff   --old TB_DATA
            //$newArr --new TB_DATA
            $OLD_DATA=[];
            $NEW_DATA=[];
          if (count($diff)>0){
             $OLD_DATA=array_map(function ($value) use ($diff){
                    foreach ($diff as $item){
                        if ($item->NO_NUM->VALUE==$value->TB_DATA->NO_NUM->VALUE){
                            $value->TB_DATA=$item;
                        }
                    }
                    return $value;
                },$default_obj);
          }

          if (count($newArr)>0){
              $NEW_DATA=array_map(function ($value) use ($newArr){
                  foreach ($newArr as $item){
                      if ($item->NO_NUM->VALUE==$value->TB_DATA->NO_NUM->VALUE){
                          $value->TB_DATA=$item;
                      }
                  }
                  return $value;
              },$new_obj);
          }

         $result['OLD_DATA']=$OLD_DATA;
         $result['NEW_DATA']=$NEW_DATA;
        }
    }
 return json_decode(json_encode($result,JSON_UNESCAPED_UNICODE));


}
