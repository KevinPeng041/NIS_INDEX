<?php
date_default_timezone_set('Asia/Taipei');
function AESEnCode($text){
    define('AES_128_CBC', 'aes-128-cbc');
    $key='1234567890654321';
    $iv='1234567890123456';
    $encrypted=openssl_encrypt($text,'aes-128-cbc',$key,0,$iv);
    $encryptstr=str_replace(' ','+',$encrypted);
    return $encryptstr;
}
function AESDeCode($text){
    $key = '1234567890654321';
    $iv = "1234567890123456";
    $decrypted = openssl_decrypt($text, 'aes-128-cbc', $key, OPENSSL_ZERO_PADDING , $iv);
    $Arr=str_split($decrypted);
    $decryptStr='';
    foreach ($Arr as $item){
        $assii =ord($item);
        if ($assii <= 32){
            return $decryptStr = str_replace(chr($assii),"",$decrypted); //删除此ASCII字符
        }
    }
    if($decryptStr==''){
        return $decrypted;
    }
}
function is_json($string) {
 json_decode($string);
 return (json_last_error() == JSON_ERROR_NONE);
}
function GetXmlType($conn,$IdForm){
    /// 使用的電子病歷版本 (悅晟、商之器...)
    /// <returns>N.不轉電子病歷格式  Y.悅晟版電子病歷格式  A.商之器電子病歷格式 (以前傑格也是)  *.悅晟版+商之器版
    $sUseNxl="N";
    $sql="Select IS_USENXL from HIS803.NSTBMF WHERE ID_TABFORM ='$IdForm'";
    $stid=oci_parse($conn,$sql);
    oci_execute($stid);
    while (oci_fetch_array($stid)){
        $sUseNxl=oci_result($stid,"IS_USENXL");
    }
    return $sUseNxl;
}
function GetEMR106PrevSeq($conn,$AFormType,$AHisSeq){
    #region Emr 商之器版 小Index
    $sSeq="";
    $sTab=GetEMr106TabName($AFormType);
    $sql="SELECT ID_DTTMSEQ, ID_PATIENT, ST_SEQNO FROM HIS803.YC_EMR_".$sTab.
         " WHERE ID_HISLINK =".$AHisSeq.
         " AND CD_DATA_FLAG = 'I'".
         " AND DM_CANCEL = ' '";
    if(strpos($AHisSeq,"ISLN@")>-1){
        $sql=" SELECT ID_DTTMSEQ, ID_PATIENT, ST_SEQNO FROM HIS803.YC_EMR_".$sTab.
             " WHERE ST_SEQNO = '".explode("-",str_replace("ISLN@","",$AHisSeq))[0]."' "
            ."  AND ID_HISLINK like '".str_replace("ISLN@","",$AHisSeq)."' "
            ."  AND CD_DATA_FLAG = 'I'  AND DM_CANCEL = ' '";
    }
    $stid=oci_parse($conn,$sql);
    oci_execute($stid);
    while(oci_fetch_array($stid)){
        if(strpos($AHisSeq,"ISLN@")>-1){
            $AHisSeq=oci_result($stid,"ID_HISLINK");
        }
        $sSeq=oci_result($stid,"ID_TTDMSEQ");
    }
    return $sSeq;
}
function GetEMr106TabName($AFormType){
    $TXmlForm=new TXmlForm();
    if ($AFormType == $TXmlForm->C_ID_TABFORM_SEPT)
    {
        $sTab = $TXmlForm->C_ID_TABFORM_SEPT;
    }
    else if ($AFormType == $TXmlForm->C_ID_TABFORM_TPRS)
    {
        $sTab = $TXmlForm->C_ID_TABFORM_TPRS;
    }
    else if ($AFormType == $TXmlForm->C_ID_TABFORM_IOQA)
    {
        $sTab = $TXmlForm->C_ID_TABFORM_IOQA;
    }
    else if ($AFormType == $TXmlForm->C_ID_TABFORM_BSOR)
    {
        $sTab = $TXmlForm->C_ID_TABFORM_BSOR;
    }
    else if ($AFormType == $TXmlForm->C_ID_TABFORM_LCHL)
    {
        $sTab = $TXmlForm->C_ID_TABFORM_LCHL;
    }
    else if ($AFormType == $TXmlForm->C_ID_TABFORM_MARS)
    {
        $sTab = $TXmlForm->C_ID_TABFORM_MARS;
    }
    else if ($AFormType == $TXmlForm->C_ID_TABFORM_TBHL)
    {
        $sTab = $TXmlForm->C_ID_TABFORM_TBHL;
    }
    else
    {
        $sTab = "NISX";
    }
    return $sTab;
}
function ProcessEMR106($conn,$idHospital,$AFormType,$ADataType,$APreviousSeq,$AHislink,$AMedno,$AInpSeq,$AFormID,$ASignHisAcc){
    $sTab=GetEMr106TabName($AFormType);
    try{
        if($ADataType !="I"){
            UpdateEMR($conn,$sTab,$ADataType,$APreviousSeq);
        }
        $sDttmSeq=InsertEMR($conn,$idHospital,$sTab,$AFormType,$ADataType,$AHislink, $AMedno, $AInpSeq, $AFormID, $ASignHisAcc);
        $result=$sDttmSeq;
    }catch (Exception $e){
        $result="";
    }
    return $result;
}

/*更新EMR*/
function UpdateEMR($conn,$ATab,$ADataType,$APreviousSeq){
    $sProcDateTime=DateNow();//13碼(7+6)1090331 110201
    $sCD_DATA_FLAG="";
    if($ADataType=="U"){
        $sCD_DATA_FLAG="R";
    }else if($ADataType=="D"){
        $sCD_DATA_FLAG="C";
    }
    $sql="UPDATE HIS803.YC_EMR_".$ATab
        ." SET CD_DATA_FLAG = '".$sCD_DATA_FLAG."', DM_CANCEL = '".$sProcDateTime."' "
        ." WHERE ID_DTTMSEQ = '".$APreviousSeq."' ";
    $stid=oci_parse($conn,$sql);
    $r=oci_execute($stid,OCI_NO_AUTO_COMMIT);
    if(!$r){
        oci_rollback($conn);

    }
    oci_commit($conn);
}

/*存檔EMR*/
function InsertEMR($conn,$idHospital,$ATab,$AFormType,$ADataType,$AHislink,$AMedno,$AInpSeq,$AFormID,$ASignHisAcc){
    $sKey="";$sInpDate=""; $sDivNo=""; $sSignIdno="";
    $sql="SELECT CA_INPDATE, CA_DIVNO FROM HIS803.INACAR".
         " WHERE CA_INPSEQ  = '".$AInpSeq."' "
        ." AND CA_DIVINSU = 'N' AND CA_CHECK = 'Y' ";
    $stid=oci_parse($conn,$sql);
    oci_execute($stid);
    while (oci_fetch_array($stid)){
        $sInpDate=oci_result($stid,"CA_INPDATE");
        $sDivNo=oci_result($stid,"CA_DIVNO");
    }
    if($sInpDate==""){
        $sInpDate=" ";
    }
    if($sDivNo==""){
        $sDivNo=" ";
    }
    $sql2="SELECT To_Char(SYSDATE, 'YYYYMMDD')||Lpad(HIS803.YC_EMR_".$ATab."SEQ.NEXTVAL, 6, '0') AS DTTMSEQ FROM DUAL ";
    $stid2=oci_parse($conn,$sql2);
    oci_execute($stid2);
    while (oci_fetch_array($stid2)){
        $sKey=$idHospital.$AFormType.oci_result($stid2,"DTTMSEQ");
    }
    $sql3= "SELECT EM_IDNO FROM HIS803.TREEMP "." WHERE EM_EMPNO = '".$ASignHisAcc."' ";
    $stid3=oci_parse($conn,$sql3);
    oci_execute($stid3);
    while (oci_fetch_array($stid3)){
        $sSignIdno=oci_result($stid3,"EM_IDNO");
    }
    if(trim($sSignIdno)==""){
        $sSignIdno=" ";
    }
    $sCD_DATA_FLAG="";
    if($ADataType=="I" || $ADataType=="U"){
        $sCD_DATA_FLAG="I";
    }else if ($ADataType=="D"){
        $sCD_DATA_FLAG="D";
    }
    $sql4="INSERT INTO HIS803.YC_EMR_".$ATab
        ." ( "
        ."ID_DTTMSEQ, ID_FORMSEQ, ID_HISLINK, ID_PATIENT, ST_SEQNO,"
        ."DT_REGISTER, CD_HISRECNO, CD_DATA_FLAG, DM_CANCEL, SS_PROCESS,"
        ."ID_FORM, UR_EXECUTE, UR_IDNO"
        .") VALUES ("
        ." '".$sKey."', ' ', '" .$AHislink."', '".$AMedno."', '".$AInpSeq."', "
        ." '".$sInpDate."', '" .$sDivNo."', '".$sCD_DATA_FLAG."', ' ', SYSDATE, "
        ." '".$AFormID. "', '".$ASignHisAcc. "', '".$sSignIdno. "' "
        ." ) ";


    $stid4=oci_parse($conn,$sql4);
    oci_execute($stid4);
    return $sKey;
}

/*醫院代碼*/
function GetHospital($conn){

    $sRet="";
    $sql="SELECT * FROM his803.NSCLSI WHERE JID_KEY = 'SYSS00000001'";
    $stid=oci_parse($conn,$sql);
    oci_execute($stid);
    while (oci_fetch_array($stid)){
        $sHopID=oci_result($stid,"ST_TEXT1");
        $sRet=oci_result($stid,"ST_TEXT2");
        switch ($sRet){
            case "0502080015": //高雄
                if(trim($sHopID)!="悅晟醫院")
                    $sRet .="/802";
                else
                    $sRet .="/";
                break;
            case "0532090029": //桃園
                $sRet .="/804";
                break;
            case "0517050010":  //中清
                $sRet .= "/816";
                break;
            case "0502030015": // 左營
                $sRet .= "/806";
                break;
            case "2502090013": // 國軍高雄門診中心
                $sRet .= "/802S";
                break;
            case "0512040014": // 新竹
                $sRet .= "/813";
                break;
            case "0501010019": // 松山
                $sRet .= "/807";
                break;
            case "0545040515": // 花蓮
                $sRet .= "/805";
                break;
            case "0501160014": // 北投
                $sRet .= "/818";
                break;
            case "0542020011": // 岡山
                $sRet .= "/814";
                break;
            case "0543010019": // 屏東
                $sRet .= "/815";
                break;
            case "0544010031": // 澎湖
                $sRet .= "/811";
                break;
        }
    }
    return $sRet;
}

/*電子病歷拋轉執行EXE*/
function CallEmrXmlExe($account,$sUr,$sTnsName,$sPassword,$sFromWk,$sDataFlag,$sFrmDtSeq,$sFrmSeq,$sIdPatient,$sInPatient,$exeDate,$exeTime,$sKeyEmr106New){

    $sToEMR="ToEMR";
   if( $sFrmSeq=="" ||  $sFrmSeq==null){
       $sFrmSeq=str_pad(trim( $sFrmSeq),1,"@",STR_PAD_LEFT);
   }
    $tArguments=" ".$account." ".$sUr." ".$sTnsName." ".$sToEMR." ".$sPassword." ".
                $sFromWk." ".$sDataFlag." ".$sFrmDtSeq." ".$sFrmSeq." ".$sIdPatient." ".$sInPatient." ".
                 $exeDate." ".substr($exeTime,0,4)." ".str_pad($sKeyEmr106New,1,"@",STR_PAD_LEFT );

    //exec("C:/xampp/htdocs/NISCTEMR/NISCTEMR.exe".$tArguments);
}

/*系統當下時間*/
function DateNow(){
    $DateTime_NOW = date("YmdHis");
    $STR = substr($DateTime_NOW, 0, 4);
    $STR1 = substr($DateTime_NOW, -10, 10);
    $str = $STR - 1911;
    $sProcDateTime = $str . $STR1;
    return $sProcDateTime;
}

function IsNullOrEmpty($input){
    return (!isset($input) || trim($input)==='');
}
class TXmlForm{

    public   $C_ID_TABFORM_TBDC = "TBDC";
    /// <summary>
    /// 轉出護理摘要
    /// </summary>
    public  $C_ID_TABFORM_TBTO = "TBTO";
    /// <summary>
    /// 入院護理評估作業
    /// </summary>
    public  $C_ID_TABFORM_TBIN = "TBIN";
    /// <summary>
    /// 護理診斷計畫單
    /// </summary>
    public   $C_ID_TABFORM_NDPT = "NDPT";
    /// <summary>
    /// 壓瘡危險因子評估作業
    /// </summary>
    public   $C_ID_TABFORM_TBBE = "TBBE";
    /// <summary>
    /// 防範跌倒評估紀錄作業
    /// </summary>
    public   $C_ID_TABFORM_TBFD = "TBFD";
    /// <summary>
    /// 身體約束病人評估及照護紀錄表
    /// </summary>
    public   $C_ID_TABFORM_TBHL = "TBHL";
    /// <summary>
    /// 復健評估作業
    /// </summary>
    public   $C_ID_TABFORM_TBAL = "TBAL";
    /// <summary>
    /// 護理紀錄作業
    /// </summary>
    public   $C_ID_TABFORM_SEPT = "SEPT";
    /// <summary>
    /// 護理交班
    /// </summary>
    public   $C_ID_TABFORM_HDPI = "HAND";
    /// <summary>
    /// 血壓呼吸脈搏
    /// </summary>
    public   $C_ID_TABFORM_TPRS = "TPRS";
    /// <summary>
    /// 護理給藥作業
    /// </summary>
    public   $C_ID_TABFORM_MARS = "MARS";
    /// <summary>
    /// 輸出入量
    /// </summary>
    public   $C_ID_TABFORM_IOQT = "IOQT";
    /// <summary>
    /// 約束同意書
    /// </summary>
    public   $C_ID_TABFORM_LCHL = "LCHL";
    /// <summary>
    /// 神經學檢查
    /// </summary>
    public   $C_ID_TABFORM_NRLG = "NRLG";
    /// <summary>
    /// 護理照會
    /// </summary>
    public   $C_ID_TABFORM_TBMR = "TBMR";
    /// <summary>
    /// 護理照會回覆
    /// </summary>
    public   $C_ID_TABFORM_TBME = "TBME";
    /// <summary>
    /// 加強醫護輸入量紀錄
    /// </summary>
    public   $C_ID_TABFORM_IOQA = "IOQA";
    /// <summary>
    /// 護理指導紀錄
    /// </summary>
    public   $C_ID_TABFORM_TCPT = "TCPT";
    /// <summary>
    /// 手術前護理紀錄
    /// </summary>
    public   $C_ID_TABFORM_TBOB = "TBOB";
    /// <summary>
    /// 手術後護理紀錄
    /// </summary>
    public   $C_ID_TABFORM_TBOA = "TBOA";
    /// <summary>
    /// 血糖及胰島素注射治療紀錄表
    /// </summary>
    public   $C_ID_TABFORM_ISLN = "ISLN";
    /// <summary>
    /// 壓瘡評沽紀錄
    /// </summary>
    public   $C_ID_TABFORM_BSOR = "BSOR";
}

