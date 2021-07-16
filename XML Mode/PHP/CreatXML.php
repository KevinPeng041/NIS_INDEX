<?php
date_default_timezone_set('Asia/Taipei');

require_once '../../config.php';
require_once '../../DB_ACTION/DatabaseAccessClass.php';
require_once 'XMLCOMFUNCTION.php';
$DateTime=date("YmdHis");

$DB=new DatabaseAccessObject($host,$user,$passwd);
$SQL="SELECT bmk_indentno,BMK_APNO,bmk_medno,mh_name,mh_idno,MH_BIRTH,MH_BIRTHDATE,
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
And  bmk_icd9cm=id_icd9cm(+) And BMK_INDENTNO= '11006303001'";

$Select_result=$DB->Select($SQL);
$obj=(object)array();


foreach ($Select_result as $key=>$value){
    foreach ($value as $item){
        $obj->$key=$item;
    }
}
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
$PrinterDT=GetPrintDT(date("Y/m/d - H:i"));

$dom=new DOMDocument();
$dom->encoding='utf-8';
$dom->xmlVersion='1.0';
$dom->formatOutput=true;
$xslt=$dom->createProcessingInstruction('xml-stylesheet','type="text/xsl" href="../XSLT/usetest01.xsl"');
$dom->appendChild($xslt);
//$xml_file_name=$DateTime;
$xml_file_name='../XML/20210706111129.xml';

$EMR=$dom->createElement('EMR');
$dom->appendChild($EMR);


$DocumentInfo=$dom->createElement('DocumentInfo');
$EMR->appendChild($DocumentInfo);

$HospitalName=$dom->createElement('HospitalName');
$DocumentInfo->appendChild($HospitalName);
$text=$dom->createCDATASection('悅晟資訊');
$HospitalName->appendChild($text);

$HospitalID=$dom->createElement('SheetName');
$DocumentInfo->appendChild($HospitalID);
$text=$dom->createCDATASection('檢驗報告單');
$HospitalID->appendChild($text);

$FormSquence=$dom->createElement('FormSquence');
$DocumentInfo->appendChild($FormSquence);
$text=$dom->createCDATASection($BMK_INDENTNO);
$FormSquence->appendChild($text);

$PrinterDate=$dom->createElement('PrinterDate');
$DocumentInfo->appendChild($PrinterDate);
$text=$dom->createCDATASection($PrinterDT);
$PrinterDate->appendChild($text);

//Patient
$Patient=$dom->createElement('Patient');
$EMR->appendChild($Patient);

$TableRow1=$dom->createElement('TableRow1');
$Patient->appendChild($TableRow1);


$name=$dom->createElement('name');
$TableRow1->appendChild($name);
$text=$dom->createCDATASection($MH_NAME);
$name->appendChild($text);

$part=$dom->createElement('part');
$TableRow1->appendChild($part);
$text=$dom->createCDATASection($SE_SENAME);
$part->appendChild($text);

$IdType=$dom->createElement('IdType');
$TableRow1->appendChild($IdType);
$text=$dom->createCDATASection($BE_EASYNAME);
$IdType->appendChild($text);

$OrderDate=$dom->createElement('OrderDate');
$TableRow1->appendChild($OrderDate);
$BMK_MAKEDATE=substr($BMK_MAKEDATE,0,3).'/'.substr($BMK_MAKEDATE,3,2).'/'.substr($BMK_MAKEDATE,5,2);
$text=$dom->createCDATASection($BMK_MAKEDATE);
$OrderDate->appendChild($text);

$TableRow2=$dom->createElement('TableRow2');
$Patient->appendChild($TableRow2);

$bed=$dom->createElement('bed');
$TableRow2->appendChild($bed);
$text=$dom->createCDATASection($BMK_BEDNO);
$bed->appendChild($text);

$gender=$dom->createElement('gender');
$TableRow2->appendChild($gender);
$text=$dom->createCDATASection($MH_SEX);
$gender->appendChild($text);

$Diagnosis=$dom->createElement('Diagnosis');
$TableRow2->appendChild($Diagnosis);
$text=$dom->createCDATASection('一般');
$Diagnosis->appendChild($text);

$ScBloodDateTime=$dom->createElement('ScBloodDateTime');
$TableRow2->appendChild($ScBloodDateTime);
$DateTime=$BMK_MAKEDATE.' '.substr($BMK_MAKETIME,0,2).':'.substr($BMK_MAKETIME,2,2);
$text=$dom->createCDATASection($DateTime);
$ScBloodDateTime->appendChild($text);


$TableRow3=$dom->createElement('TableRow3');
$Patient->appendChild($TableRow3);

$chartNo=$dom->createElement('chartNo');
$TableRow3->appendChild($chartNo);
$text=$dom->createCDATASection($BMK_MEDNO);
$chartNo->appendChild($text);

$age=$dom->createElement('age');
$TableRow3->appendChild($age);
$text=$dom->createCDATASection($Age);
$age->appendChild($text);

$ClinicalDiagnosis=$dom->createElement('ClinicalDiagnosis');
$TableRow3->appendChild($ClinicalDiagnosis);
$text=$dom->createCDATASection($BMK_ICD9CM);
$ClinicalDiagnosis->appendChild($text);

$state=$dom->createElement('state');
$TableRow3->appendChild($state);
$text=$dom->createCDATASection($ID_CHNAME);
$state->appendChild($text);

$TableRow4=$dom->createElement('TableRow4');
$Patient->appendChild($TableRow4);

$PatientID=$dom->createElement('PatientID');
$TableRow4->appendChild($PatientID);
$text=$dom->createCDATASection($MH_IDNO);
$PatientID->appendChild($text);

$DoctorName=$dom->createElement('DoctorName');
$TableRow4->appendChild($DoctorName);
$text=$dom->createCDATASection($EM_EMPNAME);
$DoctorName->appendChild($text);

$BloodCauses=$dom->createElement('BloodCauses');
$TableRow4->appendChild($BloodCauses);
$text=$dom->createCDATASection($BMK_REASON);
$BloodCauses->appendChild($text);

$BloodType=$dom->createElement('BloodType');
$TableRow4->appendChild($BloodType);
$text=$dom->createCDATASection($BMK_BLOOD);
$BloodType->appendChild($text);

$TableRow5=$dom->createElement('TableRow5');
$Patient->appendChild($TableRow5);

$BloodType=$dom->createElement('BloodType');

$TableRow5->appendChild($BloodType);
$text=$dom->createCDATASection($BMK_BLOOD);
$BloodType->appendChild($text);

$SampleNo=$dom->createElement('SampleNo');
$TableRow5->appendChild($SampleNo);
$text=$dom->createCDATASection($BMK_INDENTNO);
$SampleNo->appendChild($text);

$BloodData=$dom->createElement('BloodData');
$EMR->appendChild($BloodData);

//BloodData
$SQL="SELECT BMK_BLDKIND,
        (SELECT BKD_BLDNAME FROM TBOKID WHERE BMK_BLDKIND=BKD_BLDKIND) AS  EASY_NAME,
        BMK_QTY FROM TBOMAK
        WHERE
        BMK_MEDNO ='01164093'
        AND BMK_MAKEDATE='1100630'
        AND BMK_MAKETIME='1034' AND BMK_CANDATE=' '
        ";

$Select_result=$DB->Select($SQL);

$BMK_BLDKIND=$Select_result['BMK_BLDKIND'];
$EASY_NAME=$Select_result['EASY_NAME'];
$BMK_QTY=$Select_result['BMK_QTY'];

foreach ($BMK_BLDKIND as $key=>$value){

    $Data=$dom->createElement('Data');
    print_r($Data);
    $BloodData->appendChild($Data);

    $BloodNo=$dom->createElement('BloodNo');
    $Data->appendChild($BloodNo);
    $text=$dom->createCDATASection($value);
    $BloodNo->appendChild($text);

    $BloodName=$dom->createElement('BloodName');
    $Data->appendChild($BloodName);
    $text=$dom->createCDATASection($EASY_NAME[$key]);
    $BloodName->appendChild($text);

    $BloodQty=$dom->createElement('BloodQty');
    $Data->appendChild($BloodQty);
    $text=$dom->createCDATASection($BMK_QTY[$key]);
    $BloodQty->appendChild($text);

}

$ReactData=$dom->createElement('ReactData');
$AntiData=$dom->createElement('AntiData');
$EMR->appendChild($AntiData);
$EMR->appendChild($ReactData);

$R_Data=$dom->createElement('Data');
$A_Data=$dom->createElement('Data');
$ReactData->appendChild($R_Data);
$AntiData->appendChild($A_Data);

$Patient_Blood_DATA=GetBloodSerumReact($DB,$BMK_INDENTNO);
$TestDocName=$Patient_Blood_DATA->TestDocName;//醫檢師

//ReactData
$ReactNode=['AntiA','AntiB','AntiAB','AntiD','BloodType1','Acell','Bcell','Compare','BloodType2'];
$ReactTypeData=$Patient_Blood_DATA->ReactData;//血球,血清反應
foreach ($ReactNode as $key=>$value){
    $node=$dom->createElement($value);
    $R_Data->appendChild($node);
    $text=$dom->createCDATASection($ReactTypeData[$key]);
    $node->appendChild($text);
}


//AntiData
$AntiNode=['AntiFilter','AntiS1','AntiS2','AntiS3','AntiAuto','AntiEval','BloodEval'];
$AntiData=$Patient_Blood_DATA->AntiData;//抗體篩檢
foreach ($AntiNode as $key=>$value){
    $node=$dom->createElement($value);
    $A_Data->appendChild($node);
    $text=$dom->createCDATASection($AntiData[$key]);
    $node->appendChild($text);
}

/* OrderType
 * Y.非常緊急用血 E.緊急備血 A.立即備血 N.一般備血
 * */

$Order_obj=(object)array('N'=>'□','A'=>'□','E'=>'□','Y'=>'□','DC_NM'=>$TestDocName);
$OrderType=$dom->createElement('OrderType');
$EMR->appendChild($OrderType);

foreach ($Order_obj as $key=>$value)
{
    if ($key==$BMK_EMG_CD){
       $value='■';
    }

    if ($key=="N")
    {
        $NodeName='Normal';
    }else if ($key=="A"){
        $NodeName='Immediate';
    }else if ($key=="E"){
        $NodeName='Emergency';
    }
    else if ($key=="Y"){
        $NodeName='VeryEmergency';
    }
    else {
        $NodeName='TestDocName';
    }
    $OrderNode=$dom->createElement($NodeName);
    $OrderType->appendChild($OrderNode);
    $text=$dom->createCDATASection($value);
    $OrderNode->appendChild($text);
}

$dom->save($xml_file_name);//存檔xml
echo '<a href= "'.$xml_file_name.'">' . $xml_file_name . '</a> has been successfully created';
