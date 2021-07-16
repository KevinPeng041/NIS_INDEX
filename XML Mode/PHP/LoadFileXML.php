<?php
//ini_set('display_errors','1');
//error_reporting(E_ALL);

date_default_timezone_set('Asia/Taipei');

require_once '../../config.php';
require_once '../../DB_ACTION/DatabaseAccessClass.php';
require_once 'XMLCOMFUNCTION.php';

$DB=new OracleDB();
$DB->Connect($host,$user,$passwd);
$DB->SetFetchMode(OCI_ASSOC);
$DB->SetAutoCommit(false);

$dom=new DOMDocument();
$dom->formatOutput=true;
$dom->preserveWhiteSpace=false;




$SQL="SELECT Bmk_indentno,BMK_APNO,bmk_medno,mh_name,mh_idno,MH_BIRTH,MH_BIRTHDATE,
case mh_sex when '1' then '男' else '女' end  as mh_sex,
BMK_lookdt,
bmk_makedate,bmk_maketime,bmk_emg_cd,bmk_blood, bmk_sectno,
case when bmk_bedno <>' '
then (select ca_bedno from inacar where ca_medno = bmk_medno  and ca_close='N'
      and  ca_check not in ('D','B') and CA_GOVBUDGET = 'N'  and ca_divno = bmk_divno and ca_inpseq = ca_inpseq and rownum = 1)
else ' ' end bmk_bedno,
bmk_belong1,ID_CHNAME, BMK_ICD9CM, BMK_ICDTYPE,
bmk_memo,bmk_doccd,em_empname,be_easyname,SE_SENAME,
BKD_BLDNAME,BMK_BLDKIND,bmk_allowqty,bmk_qty,
(SELECT BRA_BRANAME FROM TBOREA WHERE  BRA_REASON=BMK_REASON ) AS BMK_REASON
,
CASE WHEN BMK_SAMPLE = 'Y' THEN BMK_SAMPLENO ELSE ' ' END BMK_SAMPLENO
From  tbomak,tremed,tbokid,treemp,trebng,tresec,topicdt
Where bmk_cancd='N' And bmk_lab_cd='Y'
And bmk_medno=mh_medno(+)
And BMK_BLDKIND=BKD_BLDKIND  And  bmk_doccd=em_empno
And bmk_belong1=be_belong    And  bmk_sectno=se_sectno
And bmk_icd9cm=id_icd9cm(+)  And  BMK_INDENTNO= '11006303001'";

$Select_result=$DB->Select($SQL);
$obj=$DB->FetchArray($Select_result);
$obj=json_decode(json_encode($obj));

$BMK_INDENTNO=$obj->BMK_INDENTNO;//備血單號
$BMK_MEDNO=$obj->BMK_MEDNO;//病歷號
$MH_NAME=$obj->MH_NAME;//姓名
$MH_IDNO=$obj->MH_IDNO;//身分證
$MH_SEX=$obj->MH_SEX;//性別
$MH_BIRTHDATE=$obj->MH_BIRTHDATE;//出生日期
$Age= GetAge($MH_BIRTHDATE);//年齡
$BMK_LOOKDT=$obj->BMK_LOOKDT;//門診看診日
$BMK_MAKEDATE=$obj->BMK_MAKEDATE;//備血開單日期
$BMK_MAKETIME=$obj->BMK_MAKETIME;//預計用血時間
$BMK_EMG_CD=$obj->BMK_EMG_CD;//備血方式
$ID_CHNAME=$obj->ID_CHNAME;
$BMK_BLOOD=$obj->BMK_BLOOD;//血型
$BMK_BEDNO=$obj->BMK_BEDNO;//床位
$BMK_ICD9CM=$obj->BMK_ICD9CM;//臨床診斷
$EM_EMPNAME=$obj->EM_EMPNAME;//醫生
$BE_EASYNAME=$obj->BE_EASYNAME;//身分
$SE_SENAME=$obj->SE_SENAME;//科別
$BMK_REASON=$obj->BMK_REASON;//輸血原因

$PrinterDT=GetPrintDT(date("Y/m/d - H:i"));//列印時間
$DateTime=date("YmdHis");

$File_path='../XML/MODAL/INIBLOOD.xml';//讀取血褲血清xml模板



$dom->load($File_path);
$EMR=$dom->getElementsByTagName('EMR');

/************************DocumentInfo**************************************/
$DocumentInfo=$dom->getElementsByTagName('DocumentInfo');

$Info_Node=(object)array('HospitalName'=>'悅晟資訊','SheetName'=>'檢驗報告單','FormSquence'=>$BMK_INDENTNO,'PrinterDate'=>$PrinterDT);
$DocumentInfo_Child=$DocumentInfo->item(0);
modifyElement($dom,$DocumentInfo_Child,$Info_Node);

/**************************Patient****************************************/

$Patient=$dom->getElementsByTagName('Patient');
$Patient_Child=$Patient->item(0);

$OrderDate=substr($BMK_MAKEDATE,0,3).'/'.substr($BMK_MAKEDATE,3,2).'/'.substr($BMK_MAKEDATE,5,2);
$ScBloodDateTime=$BMK_MAKEDATE.' '.substr($BMK_MAKETIME,0,2).':'.substr($BMK_MAKETIME,2,2);
$Patient_Node=(object)array(
    'name'=>$MH_NAME,'part'=>$SE_SENAME,'IdType'=>$BE_EASYNAME,'OrderDate'=>$OrderDate,
    'bed'=>$BMK_BEDNO, 'gender'=>$BMK_BEDNO,'Diagnosis'=>'一般','ScBloodDateTime'=>$ScBloodDateTime,
    'chartNo'=>$BMK_MEDNO,'age'=>$Age,'ClinicalDiagnosis'=>$BMK_ICD9CM,'state'=>$ID_CHNAME,
    'PatientID'=>$MH_IDNO,'DoctorName'=>$EM_EMPNAME,'BloodCauses'=>$BMK_REASON,'BloodType'=>$BMK_BLOOD,
    'SampleNo'=>$BMK_INDENTNO
    );


modifyElement($dom,$Patient_Child,$Patient_Node);


/*****************************BloodData*********************************/
$SQL="SELECT BMK_BLDKIND,
        (SELECT BKD_BLDNAME FROM TBOKID WHERE BMK_BLDKIND=BKD_BLDKIND) AS  EASY_NAME,
        BMK_QTY FROM TBOMAK
        WHERE
        BMK_MEDNO ='01164093'
        AND BMK_MAKEDATE='1100630'
        AND BMK_MAKETIME='1034' AND BMK_CANDATE=' '
        ";
$Select_result=$DB->Select($SQL);
$arr_result=array();
while ($row=$DB->FetchArray($Select_result)){
    $arr_result[]=$row;
}

$obj=json_decode(json_encode($arr_result));

$BloodData=$dom->getElementsByTagName('BloodData')->item(0);



foreach ($obj as $item) {
    $Data=$dom->createElement('Data');
    $BloodData->appendChild($Data);


    $BMK_BLDKIND=$item->BMK_BLDKIND;
    $EASY_NAME=$item->EASY_NAME;
    $BMK_QTY=$item->BMK_QTY;

    $arr_Val=array('BMK_BLDKIND'=>$BMK_BLDKIND,'EASY_NAME'=>$EASY_NAME,'BMK_QTY'=>$BMK_QTY);
    addElement($dom,$Data,$arr_Val);
}

/******************AntiData  ReactData********************************/
$Patient_Blood_DATA=GetBloodSerumReact($DB,$BMK_INDENTNO);
$TestDocName=$Patient_Blood_DATA->TestDocName;//醫檢師

foreach ($Patient_Blood_DATA as $tag=>$Node){
    if ($tag !='TestDocName'){
        $NodeName=$dom->getElementsByTagName($tag);
        $child=$NodeName->item(0);
        modifyElement($dom,$child,$Node);
    }
}

/**********************OrderType*************************************/

/* OrderType  Y.非常緊急用血 E.緊急備血 A.立即備血 N.一般備血 */
$OrderType=$dom->getElementsByTagName('OrderType')->item(0);
$Order_obj=(object)array(
                'N_Normal'=>'□',
                'A_Immediate'=>'□',
                'E_Emergency'=>'□',
                'Y_VeryEmergency'=>'□',
                'TestDocName'=>$TestDocName);
foreach ($Order_obj as $key=>&$value){
    if ($BMK_EMG_CD==explode('_',$key)[0]){
        $value='■';
    }
    unset($value);
}
modifyElement($dom,$OrderType,$Order_obj);

$Save_Name='../XML/'.$BMK_MEDNO.$DateTime.'.xml';//病歷號+當下時間


//$XMLdom_str=str_replace('<', '&lt;', $dom->saveXML());//dom轉字串 for web

//$XMLdom_str=$dom->saveXML();//dom轉字串 for db
//$arr=array(
//    "SHEETID"=>"'TW.HOSPITAL.LABORATORYREPORT.1'",
//    "SHEETVER"=>"3",
//    "SHEETNAME"=>"'血液檢驗報告'",
//    "HISDOCPK"=>"'01121366202004089700011713'",
//    "HISXMLCREATEDTIME"=>"SYSDATE",
//    "HISTMPXML"=>"'$XMLdom_str'",
//    "SENSITIVE"=>"0",
//    "CHARTNO"=>"'01121366'",
//    "PATGOVID"=>"'S1*****111'",
//    "PATNAME"=>"'許****'",
//    "SIGNCARDUSERID"=>"'BA00243767'",
//    "SIGNCARDUSERNAME"=>"'HIS維護商'",
//    "ENDEPTID"=>"'23'",
//    "ENDEPTNAME"=>"'胸腔感染科'",
//    "ENTIME"=>"SYSDATE",
//    "ENSEQ"=>"'143011'",
//    "SIGNSTATUS"=>"0",
//    "SIGNERRORCODE"=>"' '",
//    "SIGNEDDOCID"=>"' '",
//    "SIGNEDDOCVER"=>"0",
//    "DELETEMARK"=>"'0'",
//    "SIGNERRORMSG"=>"' '",
//    "SIGNHISID"=>"'00FUZZY'",
//    "XMLVER"=>"'XMLV1.0'",
//    "APVER"=>"'10812040000'",
//    "RECDATE"=>"'1090527'",
//    "RECTIME"=>"'144300'",
//    "HISTMPXML2"=>"' '",
//    "HISTMPXML3"=>"' '",
//    "CKLINKKEY"=>"' '"
//);
//
//$bind = array(":HISTMPXML"=>$XMLdom_str);
//$h=$DB->Insert('TMPEMR',$arr);
//if ($h){
//    $DB->Commit();
//    $DB->FreeStatement($h);
//    echo 'Insert ok';
//}else{
//    $DB->Rollback();
//
//}

$dom->formatOutput = true;
$dom->save($Save_Name);//dom轉檔案
echo 'successfully modify';

