<?php



function modifyElement($dom,$Tag,$obj){
    foreach ($obj as $Tag_Name=>$value){
        $Child_Node=$Tag->getElementsByTagName($Tag_Name)->item(0);
        $Child_Node->nodeValue = '';
        $Child_Node->appendChild($dom->createCDATASection($value));
    }
}

function addElement($dom,$ParentNode,$obj){
    foreach ($obj as $key=>$value){
        $Node=$dom->createElement($key);
        $ParentNode->appendChild($Node);
        $text=$dom->createCDATASection($value);
        $Node->appendChild($text);
        unset($value);
    }

}

function GetAge($BirthDate){
    /**
     * @param  $birthday 出生時間 uninx時間戳
     * @param  $time 當前時間
     **/
    //重新組合初始字串取時間戳

    $Y=(int)substr($BirthDate,0,3)+1911;
    $BirthDate=(string)$Y.'-'.substr($BirthDate,3,2).'-'.substr($BirthDate,5,2);
    $uriqi=strtotime($BirthDate);


    //格式化出生年月日
    $byear=date('Y',$uriqi);
    $bmonth=date('m',$uriqi);
    $bday=date('d',$uriqi);

    //格式化當下年月日
    $tyear=date('Y');
    $tmonth=date('m');
    $tday=date('d');


    $Age=$tyear-$byear;
    if ($bmonth>$tmonth || $bmonth==$tmonth && $bday>$tday){
        $Age--;
    }
    return $Age;

}

function GetPrintDT($DateTime){
    $tyear=(int)substr($DateTime,0,4)-1911;

    return $tyear.substr($DateTime,4,strlen($DateTime));
}

function GetBloodSerumReact($DB,$BMK_INDENTNO){
    $SQL="Select RPM_PARAM from RepPam  
          Where RPM_PROGNO='BLDC211'  
          Order By RPM_TEMCODE";

    $result=$DB->Select($SQL);

    $arr_RPM_PARAM=array();
    while ($row=$DB->FetchArray($result)){
        $arr_RPM_PARAM[]=$row;
    }
     $obj=json_decode(json_encode($arr_RPM_PARAM));
     $arr_BLA_STATE=[];
     $BLA_CULLOPName='';

    foreach ($obj as $value){
        $arr_RPM_PARAM=$value->RPM_PARAM;

        $SQL="select BLA_STATE,
                (Select em_empname from treemp where em_empno = BLA_CULLOPID) as BLA_CULLOPName
                from tbolab, tbolim
                where bla_diacode = blm_diacode and BLA_BLMTYPE = BLM_BLMTYPE
                and BLA_CANCD<>'Y' and BLA_BLMTYPE='1'  and BLA_LMSEQ ='1'  AND BLA_DIACODE='$arr_RPM_PARAM'
                and bla_indentno = '$BMK_INDENTNO'";

         $BLA_STATE_result=$DB->Select($SQL);
         $BLA_STATE_obj=$DB->FetchArray($BLA_STATE_result);

         $obj=json_decode(json_encode($BLA_STATE_obj));


        if (count($obj)>0){
            $BLA_CULLOPName=$obj->BLA_CULLOPNAME;
             array_push($arr_BLA_STATE,$obj->BLA_STATE);
         }
    }
    foreach ([4,7,8,14] as $value){
        array_splice($arr_BLA_STATE,$value,0,'');
    }

    $ReactData=[];
    $AntiData=[];
    foreach($arr_BLA_STATE as $index=>$value){
        if ($index<=8){
            array_push($ReactData,$value);
        }else{
            array_push($AntiData,$value);
        }
    }
     $React_Node=['AntiA'=>'','AntiB'=>'','AntiAB'=>'','AntiD'=>'','BloodType1'=>'','Acell'=>'','Bcell'=>'','Compare'=>'','BloodType2'=>''];
     $AntiNode=['AntiFilter'=>'','AntiS1'=>'','AntiS2'=>'','AntiS3'=>'','AntiAuto'=>'','AntiEval'=>'','BloodEval'=>''];


    $R_index=0;
    foreach ($React_Node as $item=>$value){
        $React_Node[$item]=$ReactData[$R_index];
        $R_index++;
    }

     $A_index=0;
     foreach ($AntiNode as $item=>$value){
         $AntiNode[$item]=$AntiData[$A_index];
         $A_index++;
     }
    return (object)array('ReactData'=>$React_Node,'AntiData'=>$AntiNode,'TestDocName'=>$BLA_CULLOPName);
}

