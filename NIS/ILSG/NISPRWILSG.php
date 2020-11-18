<?php
header("Content-Type:text/html; charset=utf-8");
date_default_timezone_set('Asia/Taipei');
set_time_limit(0);
include '../../NISPWSIFSCR.php';

$str=$_GET['str'];
$replaceSpace=str_replace(' ','+',$str);//空白先替換+
$EXPLODE_data=explode('&',AESDeCode($replaceSpace));

$sIdUser_STR=$EXPLODE_data[0];
$passwd_STR=$EXPLODE_data[1];
$user_STR=$EXPLODE_data[2];
$From_STR=$EXPLODE_data[3];

$sIdUser_value=explode('=',$sIdUser_STR);
$passwd_value=explode('=',$passwd_STR);
$user_value=explode('=',$user_STR);
$From_value=explode('=',$From_STR);

$Account=strtoupper(str_pad(trim($sIdUser_value[1]),7,"0",STR_PAD_LEFT));/*帳號*/
$passwd=trim($passwd_value[1]);/*密碼*/
$sUr=trim($user_value[1]);/*使用者*/
$From=trim($From_value[1]);/*L:登入介面,U:URL操作*/
$HOST_IP=$_SERVER['HTTP_HOST'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>血糖胰島素</title>
    <script type="text/javascript" src="../../jquery-3.4.1.js"></script>
    <link rel="stylesheet" href="../../bootstrap-4.3.1-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../css/NIS/ILSG.css">
    <script src="../../bootstrap-4.3.1-dist/js/bootstrap.min.js" type="text/javascript"></script>
    <script src="../../crypto-js.js"></script>
    <script src="../../AESCrypto.js"></script>
    <script src="../../NISCOMMAPI.js"></script>
    <script>
        $(document).ready(function () {
            (function () {
                //url帳號密碼驗證
                let From='<?php echo $From?>';
                if (From==="U"){
                    let FromObj=JSON.parse(AESDeCode(UrlCheck('<?php echo $Account?>','<?php echo $passwd?>')));
                    if(FromObj.reponse==="false"){
                        alert("帳號密碼錯誤,請關閉視窗重新確認");
                        return;
                    }
                }
                else {
                    let ckw=setInterval(()=>{ try {
                        if(!window.opener) {
                            alert("此帳號以被登出,請關閉視窗重新登入開啟");
                            window.close();
                        }
                    }catch (e) {
                        $("#wrapper").show();
                        alert(e);
                        window.close();
                        clearInterval(ckw);
                        return false;
                    }
                    },500);
                }


                $("#loading").hide();
                $("#wrapper").hide();
                $("#DELMENU").attr("disabled", true);
                $("#ISSG").prop("disabled", true);
                $("#ISLN").prop("disabled", true);
                $("#Inhibit").prop("disabled", true);
                $("#Part").prop("disabled", true);
                NISPWSFMINI_Timer();
            })();

            let transKey='';
            let sSave='';
            let FORMSEQANCE_WT='';
            let JID_NSRANK='';
            let sDt='';
            let sTm='';
            let  ISSG_jsonStr='';
            let LSTPT='';
            let FORBIDArrary='';
            let x;
            let y;
            $(window).on('beforeunload', reloadmsg);

            $(document).on("keydown","input",function (e) {
                if(e.keyCode===13){
                    e.preventDefault();//prevent enter to submit
                    return false;
                }
            });

            $(document).on('change','input[name=sRdoDateTime]',function () {
                let TimeNow=new Date();
                let yyyy=TimeNow.toLocaleDateString().slice(0,4);
                let MM=(TimeNow.getMonth()+1<10?'0':'')+(TimeNow.getMonth()+1);
                let dd=(TimeNow.getDate()<10?'0':'')+TimeNow.getDate();
                let  h=(TimeNow.getHours()<10?'0':'')+TimeNow.getHours();
                let  m=(TimeNow.getMinutes()<10?'0':'')+TimeNow.getMinutes();
                let Timetxt=($(this).val()).split("");

                let timer=Timetxt.filter(function (value, index, array) { return  value!==":"});
                let timerVal=$(this).attr('id')==="ISTM00000005"?h+m:timer.join("");

                $("#IDTM").val($(this).attr('id'));
                $("#timer").val(yyyy-1911+MM+dd);
                $("#timetxt").val(timerVal);

            });

            $('#STVALval').on("keydown",function(){
                if($(this).val()){
                    $("input[name='sPressure']").prop('checked',false);
                    $("#sPress").val("");
                }
            });

            $("input[name='sPressure']").change(function () {
                $("#sPress").val($(this).val());
                $("#STVALval").val("");
            });

            $('button[name=ISLNch]').click(function () {
                /*call ws for胰島素藥品*/
                let val=$(this).val();
                LoadInsertPage('PREB',);
                $('#ckt').val(val);
            });

            /*各頁面控制*/
            $("button[name=click]").click(function(){
                let ISLN_jsonStrtt=new Map();
                let data = [];
                let val=$(this).val();
                let btnid=$(this).attr('id');
                $("#PageVal").val(val);

                ISLN_jsonStrtt.set('ISLN0',{
                    'idFrm':$('#STDATB_idFrm').val(),
                    'SFRMDTSEQ':'',
                    'ITNO':$("input[name='ITNO']:checked").val(),
                    'IDTM':$("#IDTM").val(),
                    'IDGP':$("input[name=part]:checked").val(),
                    'FORBID':'',
                    'ID':$("#sID0").val(),
                    'STM':$("#Isu_A").val(),
                    'DBDOSE':'-1',
                    'SDOSE':$("#dose0").val(),
                    'USEF':$("#fUSEF_0").val(),
                    'LSTPT':$('#LastPart').val()
                });
                ISLN_jsonStrtt.set('ISLN1',{
                    'idFrm':$('#STDATB_idFrm').val(),
                    'SFRMDTSEQ':'',
                    'ITNO':$("input[name='ITNO']:checked").val(),
                    'IDTM':$("#IDTM").val(),
                    'IDGP':$("input[name=part]:checked").val(),
                    'FORBID':'',
                    'ID':$("#sID1").val(),
                    'STM':$("#Isu_B").val(),
                    'DBDOSE':'-1',
                    'SDOSE':$("#dose1").val(),
                    'USEF':$("#fUSEF_1").val(),
                    'LSTPT':$('#LastPart').val()
                });
                ISLN_jsonStrtt.set('ISLN2',{
                    'idFrm':$('#STDATB_idFrm').val(),
                    'SFRMDTSEQ':'',
                    'ITNO':$("input[name='ITNO']:checked").val(),
                    'IDTM':$("#IDTM").val(),
                    'IDGP':$("input[name=part]:checked").val(),
                    'FORBID':'',
                    'ID':$("#sID2").val(),
                    'STM':$("#Isu_C").val(),
                    'DBDOSE':'-1',
                    'SDOSE':$("#dose2").val(),
                    'USEF':$("#fUSEF_2").val(),
                    'LSTPT':$('#LastPart').val()
                });
                ISLN_jsonStrtt.forEach((v, i)=> {
                    data.push({
                        "item": i,
                        "obj": v
                    });
                });

                    ISSG_jsonStr=[{
                    'IDTM':$("#IDTM").val(),
                    'IDGP':$("input[name=IDGP]:checked").val(),
                    'STVAL':$("#STVALval").val(),
                    'SPRESS':$("input[name='sPressure']:checked").val(),
                    'MMVAL':$('#Textarea').val().match(/&/)!=null?$('#Textarea').val().replace(/&/g,'＆'):$('#Textarea').val()
                }];



                switch (val) {
                    case 'A':
                        $('#Serch').prop('disabled',false);
                        if($("#clickTime").val()=='0'){
                            DEaultINI();
                            LoadInsertPage('DATAA');
                            $("#clickTime").val(1);
                        }
                        /*if val=1 執行快存WSST*/
                        if($("#clickTime").val()=='1'){
                            UPDATEDATA('B',JSON.stringify(Get_ISLNjson(data)));
                            UPDATEDATA('C',JSON.stringify(Get_Forbidjson()));
                        }

                        $("#BSData").show();
                        $("#isuling").hide();
                        $("#Imgisuling").hide();
                        $("#NO_isuling").hide();
                        $("#SubmitBtn").prop("disabled",false);

                        $("#ISLN").css({ 'background-color' : '', 'opacity' : '' ,'color':'white'});
                        $("#Inhibit").css({ 'background-color' : '', 'opacity' : '' ,'color':'white'});
                        $("#Part").css({ 'background-color' : '', 'opacity' : '' ,'color':'white'});
                        break;
                    case 'B':
                        if ($("#SERCH_Click").val()=='1'){
                            $('#Serch').prop('disabled',false);
                            $("#Part").prop('disabled',false);
                            $("#Inhibit").prop('disabled',false);
                        }else {
                            $('#DELMENU').prop('disabled',false);
                        }

                        if($("#clickTime").val()=='0'){
                            DEaultINI();
                            $('#clickTime').val(1);

                        }
                        /*if val=1 執行快存WSST*/
                        if($("#clickTime").val()=='1'){
                            UPDATEDATA('A',JSON.stringify(ISSG_jsonStr));
                            UPDATEDATA('C',JSON.stringify(Get_Forbidjson()));

                        }

                        $("#isuling").show();
                        $("#BSData").hide();
                        $("#Imgisuling").hide();
                        $("#NO_isuling").hide();
                        $("#SubmitBtn").prop("disabled",false);

                        $("#ISSG").css({ 'background-color' : '', 'opacity' : '' ,'color':'white'});
                        $("#Inhibit").css({ 'background-color' : '', 'opacity' : '' ,'color':'white'});
                        $("#Part").css({ 'background-color' : '', 'opacity' : '' ,'color':'white'});


                        break;
                    case 'C':
                        if ($("#SERCH_Click").val()=='1'){
                            $("#Part").prop('disabled',false);
                            $('#Serch').prop('disabled',false);
                        }
                        if($("#clickTime").val()=='0'){
                            DEaultINI();
                            $('#clickTime').val(1);
                        }
                        /*if val=1 執行快存WSST*/
                        if($("#clickTime").val()=='1'){
                            UPDATEDATA('A',JSON.stringify(ISSG_jsonStr));
                            UPDATEDATA('B',JSON.stringify(Get_ISLNjson(data)));

                        }
                        $("#NO_isuling").show();
                        $("#isuling").hide();
                        $("#BSData").hide();
                        $("#Imgisuling").hide();
                        $("#SubmitBtn").prop("disabled",false);

                        $("#ISSG").css({ 'background-color' : '', 'opacity' : '' ,'color':'white'});
                        $("#ISLN").css({ 'background-color' : '', 'opacity' : '' ,'color':'white'});
                        $("#Part").css({ 'background-color' : '', 'opacity' : '' ,'color':'white'});
                        break;
                    case 'D':

                        if($("#clickTime").val()=='0'){
                            DEaultINI();
                            $('#clickTime').val(1);
                        }
                        let Part=$("input[name=part]:checked").val();
                        let TraKey=$("#transKEY").val();

                        NISPWSCILREG(TraKey,Part);
                        $("#SubmitBtn").prop("disabled",true);
                        $("#Imgisuling").show();
                        $("#isuling").hide();
                        $("#BSData").hide();
                        $("#NO_isuling").hide();
                        $('#DELMENU').prop('disabled',true);
                        $('#Serch').prop('disabled',true);
                        $("#ISSG").css({ 'background-color' : '', 'opacity' : '' ,'color':'white'});
                        $("#ISLN").css({ 'background-color' : '', 'opacity' : '' ,'color':'white'});
                        $("#Inhibit").css({ 'background-color' : '', 'opacity' : '' ,'color':'white'});
                        break;
                    default:
                        break;
                }
                $("#"+btnid).css({ 'background-color' : '#EEEE00', 'opacity' : '' ,'color':'black'});
                $("#"+btnid).css({ 'background-color' : '#EEEE00', 'opacity' : '' ,'color':'black'});

            });

            $(document).on("click","button",function (e) {
                e.preventDefault();//prevent enter to submit
                let BtnID=$(this).attr("id");
                switch (BtnID) {
                    case "sbed":
                        switch (checkBEDwindow()) {
                            case "false":
                                errorModal("責任床位視窗已開啟");
                                return false;
                                break;
                            case "true":
                                x=window.open("/webservice/NISPWSLKCBD.php?str="+AESEnCode("sFm=ILSGA&sIdUser=<?php echo $Account?>"),"責任床位(血)",'width=850px,height=650px,scrollbars=yes,resizable=no');
                                break;
                        }
                        x.bedcallback=bedcallback;
                        break;
                    case "Serch":

                        if(($("#DataTxt").val()).trim()=='')
                        {
                            errorModal("請選擇須查詢的病人");
                            return false;
                        }
                        switch (checkSerchwindow()) {
                            case "false":
                                errorModal("查詢視窗已開啟");
                                return false;
                                break;
                            case "true":
                                y=window.open("/webservice/NISPWSLKQRY.php?str="+
                                    AESEnCode("sFm=ILSGA&PageVal="+$("#PageVal").val()+"&DA_idpt="+
                                        $('#DA_idpt').val()+"&DA_idinpt="+$('#DA_idinpt').val()+
                                        "&sUser="+$('#sUser').val()+"&NM_PATIENT="+$('#DataTxt').val())
                                    ,"查詢",'width=750px,height=650px,scrollbars=yes,resizable=no');
                                break;
                        }

                        y.Serchcallback=Serchcallback;
                        break;
                    case "Del":
                        DELILSG();
                        break;
                    case "ReSet":
                        Reset(1);
                        break;
                    case "FuClear1":
                        clearvalue(0);
                        break;
                    case "FuClear2":
                        clearvalue(1);
                        break;
                    case "FuClear3":
                        clearvalue(2);
                        break;
                    case "FuConfirm":
                        PersonFuval();
                        break;
                    case "ErorFocus":
                        focustext($("#ERRORVAL").val());
                        break;
                    case "SubmitBtn":
                        $(window).off('beforeunload', reloadmsg);
                        let timeRadioButton=$("input[name=sRdoDateTime]:checked").val();
                        let CID_MEAL=$("input[name=IDGP]:checked").val();
                        let Dateinput=$("#timer").val();
                        let TIMER=$("#timetxt").val();
                        let trsKey=$('#transKEY').val();
                        let Page=$('#PageVal').val();
                        let json='';
                        sDt=($('#timer').val()).toString();
                        sTm=$('#timetxt').val()+"00";

                        let  ISSN_jsonStr3=[{
                            'idFrm':$('#STDATB_idFrm').val(),
                            'SFRMDTSEQ':'',
                            'ITNO':$("input[name='ITNO']:checked").val(),
                            'IDTM':$("#IDTM").val(),
                            'IDGP':$("input[name=part]:checked").val(),
                            'FORBID':'',
                            'ID':$("#sID0").val(),
                            'STM':$("#Isu_A").val(),
                            'DBDOSE':'-1',
                            'SDOSE':$("#dose0").val(),
                            'USEF':$("#fUSEF_0").val(),
                            'LSTPT':$('#LastPart').val()
                        },{
                            'idFrm':$('#STDATB_idFrm').val(),
                            'SFRMDTSEQ':'',
                            'ITNO':$("input[name='ITNO']:checked").val(),
                            'IDTM':$("#IDTM").val(),
                            'IDGP':$("input[name=part]:checked").val(),
                            'FORBID':'',
                            'ID':$("#sID1").val(),
                            'STM':$("#Isu_B").val(),
                            'DBDOSE':'-1',
                            'SDOSE':$("#dose1").val(),
                            'USEF':$("#fUSEF_1").val(),
                            'LSTPT':$('#LastPart').val()
                        },{
                            'idFrm':$('#STDATB_idFrm').val(),
                            'SFRMDTSEQ':'',
                            'ITNO':$("input[name='ITNO']:checked").val(),
                            'IDTM':$("#IDTM").val(),
                            'IDGP':$("input[name=part]:checked").val(),
                            'FORBID':'',
                            'ID':$("#sID2").val(),
                            'STM':$("#Isu_C").val(),
                            'DBDOSE':'-1',
                            'SDOSE':$("#dose2").val(),
                            'USEF':$("#fUSEF_2").val(),
                            'LSTPT':$('#LastPart').val()
                        }];


                        ISSG_jsonStr = [{
                            'IDTM': $("#IDTM").val(),
                            'IDGP': $("input[name=IDGP]:checked").val(),
                            'STVAL': $("#STVALval").val(),
                            'SPRESS': $("input[name='sPressure']:checked").val(),
                            'MMVAL': $('#Textarea').val().match(/&/)!=null?$('#Textarea').val().replace(/&/g,'＆'):$('#Textarea').val()
                        }];

                        if ($("#sSave").val()==='N'){
                            errorModal("此病人權限無法存檔");
                            return false;
                        }


                        if($('#DataTxt').val()=='' ||$('#DataTxt').val()==null　){

                            errorModal("請先選擇責任床位");
                            return false;
                        }
                        //時間防呆
                        if(Dateinput.trim() ==null ||Dateinput.trim() =='' || TIMER.trim() ==null || TIMER.trim() =='' ||timeRadioButton==''||timeRadioButton==null){
                            errorModal("日期時間不得為空");
                            return false;
                        }
                        if((Dateinput.trim()).length <7){
                            errorModal("請輸入正確長度的日期格式");
                            return false;
                        }
                        if((TIMER.trim()).length <4){
                            errorModal("請輸入正確長度的時間格式");
                            return false;
                        }
                        //頁面防呆
                        switch (Page) {
                            case "A":
                                if(timeRadioButton != '臨時'){
                                    if(CID_MEAL =='' || CID_MEAL == null || CID_MEAL =='undefined'){
                                        errorModal("飯前飯後未選擇");
                                        return false;
                                    }
                                }
                                if ($("input[name=IDGP]").is(":checked")===false){
                                    errorModal("請選擇飯前或飯後施打");
                                    return false;
                                }
                                if($("#STVALval").val()=='' && $("#sPress").val()=='' )
                                {
                                    errorModal("請檢查血糖值");
                                    $("#ERRORVAL").val(3);
                                    focustext(3);
                                    return false;
                                }
                                if($("#STVALval").val()>500){
                                    errorModal("血糖值異常請重新檢查");
                                    $("#ERRORVAL").val(3);
                                    focustext(3);
                                    return false;
                                }
                                json=ISSG_jsonStr;

                                break;
                            case "B":
                                if($("#Isu_A").val()=='') {
                                    errorModal("第一筆藥名不得為空");
                                    return false;
                                }
                                if($("#Isu_A").val()=='' && $("#Isu_B").val()!='') {
                                    errorModal("請先選擇第一筆");
                                    return false;
                                }
                                if($("#Isu_A").val()!='' && $("#Isu_B").val()=='' &&  $("#Isu_C").val()!='' ) {

                                    errorModal("請先選擇第二筆");
                                    return false;
                                }
                                if($("#Isu_A").val()!='' && $("#dose0").val()=='') {

                                    errorModal("第一筆施打劑量不得為空");
                                    $("#ERRORVAL").val(4);
                                    focustext(4);
                                    return false;
                                }
                                if($("#Isu_A").val()!='' && $("#fUSEF_0").val()==''){

                                    errorModal("第一筆頻率不得為空");
                                    $("#ERRORVAL").val(5);
                                    focustext(5);
                                    return false;
                                }
                                if($("#Isu_A").val()!='' && $("#Isu_B").val()!='' && $("#dose1").val()=='') {

                                    errorModal("第二筆施打劑量不得為空");
                                    $("#ERRORVAL").val(6);
                                    focustext(6);
                                    return false;
                                }
                                if($("#Isu_A").val()!='' && $("#Isu_B").val()!='' && $("#fUSEF_1").val()=='') {

                                    errorModal("第二筆頻率不得為空");
                                    $("#ERRORVAL").val(7);
                                    focustext(7);
                                    return false;
                                }
                                if($("#Isu_A").val()!='' && $("#Isu_B").val()!='' && $("#Isu_C").val()!='' && $("#dose2").val()=='') {
                                    errorModal("第三筆施打劑量不得為空");
                                    $("#ERRORVAL").val(8);
                                    focustext(8);
                                    return false;
                                }
                                if($("#Isu_A").val()!='' && $("#Isu_B").val()!='' && $("#Isu_C").val()!='' && $("#fUSEF_2").val()=='') {
                                    errorModal("第三筆頻率不得為空");
                                    $("#ERRORVAL").val(9);
                                    focustext(9);
                                    return false;
                                }
                                if ($("#dose0").val() != '') {
                                    if (ValidateNumber($("#dose0").val()) === false) {
                                        errorModal("第一筆劑量請輸入數字");
                                        $("#ERRORVAL").val(4);
                                        focustext(4);
                                        return false;
                                    }
                                }
                                if ($("#dose1").val() != '') {
                                    if (ValidateNumber($("#dose1").val()) === false) {
                                        errorModal("第二筆劑量請輸入數字");
                                        $("#ERRORVAL").val(6);
                                        focustext(6);
                                        return false;
                                    }
                                }
                                if ($("#dose2").val() != '') {
                                    if (ValidateNumber($("#dose2").val()) === false) {
                                        errorModal("第三筆劑量請輸入數字");
                                        $("#ERRORVAL").val(8);
                                        focustext(8);
                                        return false;
                                    }
                                }
                                json=JSON.stringify(Get_ISLNjson(ISSN_jsonStr3));
                                break;
                            case "C":
                                if( $("input[name='forbid']").is(":checked")===true && $("input[name=NO_MMVAL]").is(":checked")===false){
                                    errorModal("請選擇禁打原因");
                                    return false;
                                }
                                if( $("input[name='forbid']").is(":checked")===false && $("input[name=NO_MMVAL]").is(":checked")===true){
                                    errorModal("請選擇禁打部位");
                                    return false;
                                }
                                if( $("input[name='forbid']").is(":checked")===false && $("input[name=NO_MMVAL]").is(":checked")===false){
                                    errorModal("請選擇禁打部位和原因");
                                    return false;
                                }
                                json=Get_Forbidjson();
                                break;
                        }
/*
                        console.log(json);
                        console.log('http://localhost/webservice/NISPWSSAVEILSG.php?str='+AESEnCode('sFm='+'ILSGA'+'&sTraID='+trsKey+'&sPg='+Page+'&sDt='+sDt+'&sTm='+sTm+'&PASSWD=<?php echo $passwd?>'+'&USER=<?php echo $sUr?>'));

*/
                        $("#loading").show();
                        $("#wrapper").show();
                        $.ajax({
                            url:'/webservice/NISPWSSAVEILSG.php?str='+AESEnCode('sFm='+'ILSGA'+'&sTraID='+trsKey+'&sPg='+Page+'&sDt='+sDt+'&sTm='+sTm+'&PASSWD=<?php echo $passwd?>'+'&USER=<?php echo $sUr?>'),
                            type:'POST',
                            beforeSend: UPDATEDATA(Page, JSON.stringify(json)),
                            dataType:'text',
                            success:function (json) {
                                let data= JSON.parse(AESDeCode(json));
                                $("#loading").hide();
                                $("#wrapper").hide();
                                if(data.response=='success'){
                                    alert("儲存成功");
                                    window.location.reload(true);
                                }else {
                                    errorModal("儲存失敗重新檢查格式:"+data.message);
                                }
                            },error:function (XMLHttpResponse,textStatus,errorThrown) {
                                $("#loading").hide();
                                $("#wrapper").hide();
                                errorModal(
                                    "1 返回失敗,XMLHttpResponse.readyState:"+XMLHttpResponse.readyState+XMLHttpResponse.responseText+
                                    "2 返回失敗,XMLHttpResponse.status:"+XMLHttpResponse.status+
                                    "3 返回失敗,textStatus:"+textStatus+
                                    "4 返回失敗,errorThrown:"+errorThrown
                                );
                            }
                        });
                        return false;
                        break;
                    default:
                        break;
                }
            });

            $(".FuQuenCy").on("focus",function () {
                let TxtID=$(this).attr("id");
                let index=TxtID.split("");
                fumadol();
                $('#funum').val(index[6]);
                $('#fut').val(index[6]);
            });

            function Get_ISLNjson(arr){
                let length=0;
                let re=[];
                if($("#Isu_A").val()!='' && $("#Isu_B").val()=='' && $("#Isu_C").val()==''){
                    length=0;
                }else if($("#Isu_A").val()!='' &&　$("#Isu_B").val()!=''&& $("#Isu_C").val()==''){
                    length=1;

                }else if($("#Isu_A").val()!='' &&　$("#Isu_B").val()!=''　&&　$("#Isu_C").val()!=''){
                    length=2;
                }

                for (let i=0;i<=length;i++){
                    if (arr[i].obj===undefined){
                        re.push(arr[i]);
                    }else {
                        re.push(arr[i].obj);
                    }
                }
                return re;
            }

            function checkSerchwindow() {
                if(!y){
                    return "true";
                }else {
                    if(y.closed){
                        return "true";
                    }else {
                        return "false";
                    }
                }
            }
            function checkBEDwindow() {
                if(!x){

                    return "true";
                }else {
                    if(x.closed){

                        return "true";
                    }else {

                        return "false";
                    }
                }
            }
            function DEaultINI(){
                let ajaxdata_ip='/webservice/NISPWSTRAINI.php';
                //console.log("http://localhost"+ajaxdata_ip+'?str='+AESEnCode('sFm='+'ILSGA'+'&idPt='+$('#DA_idpt').val()+'&INPt='+$('#DA_idinpt').val()+'&sUr=<?php echo $Account?>'));
                $.ajax({
                    url:ajaxdata_ip+'?str='+AESEnCode('sFm='+'ILSGA'+'&idPt='+$('#DA_idpt').val()+'&INPt='+$('#DA_idinpt').val()+'&sUr=<?php echo $Account?>'),
                    type:"POST",
                    dataType:"text",
                    success:function (data) {
                        let json=JSON.parse(AESDeCode(data));
                        transKey=json.sTraID;
                        sSave=json.sSave;
                        FORMSEQANCE_WT=json.FORMSEQANCE_WT;
                        JID_NSRANK=json.JID_NSRANK;
                       // console.log(json);
                        let ST_DATAA=(JSON.parse(json.ST_DATAA))[0];
                        let ST_DATAB=(JSON.parse(json.ST_DATAB))[0];
                        let ST_DATAC=(JSON.parse(json.ST_DATAC))[0];
                        let  fu_data=JSON.parse(json.ST_PREC);
                        $("#STDATB_idFrm").val(ST_DATAB.idFrm);
                        $("input[name='forbid']").prop('checked',false);
                        $("input[name='forbid']").prop('disabled',false);
                        FORBIDArrary=ST_DATAB.FORBID;

                        if(FORBIDArrary){
                            $.each(FORBIDArrary,function (index) {
                                $('#Part'+FORBIDArrary[index].REGION).prop('disabled',true);
                                $('#No_'+FORBIDArrary[index].REGION).prop('disabled',true);
                                $('#No_'+FORBIDArrary[index].REGION).prop('checked',true);
                                (ST_DATAC.FORBID).push(FORBIDArrary[index].REGION);
                            });
                        }
                        /*禁打原因*/
                        Radioforbid(ST_DATAC.NO_MMVAL);

                        /*上次施打部位*/
                        LSTPT=ST_DATAB.LSTPT;
                        if(LSTPT){
                            $("#LastPart").val(LSTPT);
                        }
                        if(ST_DATAB.IDGP){
                            $('#Part'+ST_DATAB.IDGP).prop('checked',true);
                        }

                        /*施打頻率UI*/
                        $("#fu1").children().remove();
                        $("#fu2").children().remove();
                        $.each(fu_data,function (index) {
                            let str1='';
                            let str2='';

                            if (index%2 !== 0){
                                str1=fu_data[index].FUQUEN;
                                $("#fu1").append(
                                    `
                                   <input type='button' onclick='fuval("${str1}")'  class="btn btn-primary btn-lg" value='${str1}' style="width:inherit ;">
                                  `
                                );
                            }else {
                                str2=fu_data[index].FUQUEN;
                                $("#fu2").append(
                                    `
                                   <input type='button' onclick='fuval("${str2}")'  class="btn btn-primary btn-lg" value='${str2}' style="width:inherit ;">

                                `
                                );
                            }
                        });

                        $("#transKEY").val(transKey);
                        $("#sSave").val(sSave);
                        $("#STDATA_JID_NSRANK").val(JID_NSRANK);
                        $("#STDATA_FORMWT").val(FORMSEQANCE_WT);
                    },error:function (XMLHttpResponse,textStatus,errorThrown) {
                        errorModal(
                            "1 返回失敗,XMLHttpResponse.readyState:"+XMLHttpResponse.readyState+XMLHttpResponse.responseText+
                            "2 返回失敗,XMLHttpResponse.status:"+XMLHttpResponse.status+
                            "3 返回失敗,textStatus:"+textStatus+
                            "4 返回失敗,errorThrown:"+errorThrown
                        );
                    }
                });
            }

            function Radioforbid(arr){
                if($("#NOisuling_RE").children().length==0){
                    $.each(arr,function (index,item) {
                        $("#NOisuling_RE").append(
                            `
                            <label  style='font-size: 4.5vmin'><input type='radio' name='NO_MMVAL' id='${item.F_ID}' value='${item.F_ID}' style='width: 6vmin;height: 6vmin' >${item.name}</label>
                            `
                        );
                    });
                }
            }


            function Forbid_Dateback(idPt,DT,TM,NO_MMAL,sTraID) {
                if(idPt!==$("#DA_idpt").val()){
                    errorModal("病人資訊已異動,請先重新操作一次");
                    return false;
                }
                $("#NO_isuling").show();
                $("#ISSG").prop('disabled',true);
                $("#ISLN").prop('disabled',true);
                $("#Part").prop('disabled',true);
                $("#SubmitBtn").prop('disabled',true);

                $("#DT_EXE").val(DT);
                $("#timer").val(DT);
                $("#TM_EXE").val(TM);
                $("#timetxt").val(TM);
                $("#transKEY").val(sTraID);
                $("#"+NO_MMAL).prop('checked',true);
                $("input[name=sRdoDateTime]").prop('disabled',true);
                $("input[type=checkbox]").prop('disabled',true);

            }
            function Get_Forbidjson(){
                let checkboxval=$("input[name='forbid']:checked").map(function() { return $(this).val(); }).get();
                for(let i=0;i<FORBIDArrary.length;i++){
                    /*移除禁打預設值*/
                    delete checkboxval[i];
                }
                checkboxval=checkboxval.filter(function (e) {
                    /*去除陣列移除後的空值*/
                    return e;
                });
                let newobj={};
                newobj.REGION=checkboxval.length===0?[]:checkboxval;
                newobj.NO_MMVAL= $("input[name=NO_MMVAL]:checked").val()==="undefined"?"":$("input[name=NO_MMVAL]:checked").val();
                let data=[];
                data.push(newobj);
                return data;
            }
            /*部位序號(圖)*/
            function NISPWSCILREG(TransKEY,Part) {
                $("td").css({'backgroundColor':'white','color':'black'});
                $.each(FORBIDArrary,function (index,val) {
                   for (let i=1;i<=8;i++) {
                       $("#"+val.REGION+i).css({'backgroundColor':'red','color':'white'});
                   }
                });

                if(LSTPT){
                    $("#"+LSTPT).css({'backgroundColor':'blue','color':'white'});
                }

                //console.log("http://localhost"+"/webservice/NISPWSCILREG.php?str="+AESEnCode("sFm=ILSGA&sTraID="+TransKEY+"&sRgn="+Part));
                $.ajax({
                    url:"/webservice/NISPWSCILREG.php?str="+AESEnCode("sFm=ILSGA&sTraID="+TransKEY+"&sRgn="+Part),
                    type:'POST',
                    dataType:'text',
                    success:function (json) {
                        let data=JSON.parse(AESDeCode(json));
                        let IDGP_num=$("input[name=part]:checked").val();

                        $("#"+IDGP_num+data).css({'backgroundColor':'green','color':'white'});
                    },error:function (XMLHttpResponse,textStatus,errorThrown) {
                        errorModal(
                            "1 返回失敗,XMLHttpResponse.readyState:"+XMLHttpResponse.readyState+XMLHttpResponse.responseText+
                            "2 返回失敗,XMLHttpResponse.status:"+XMLHttpResponse.status+
                            "3 返回失敗,textStatus:"+textStatus+
                            "4 返回失敗,errorThrown:"+errorThrown
                        );
                    }

                });

            }
            /*讀取輸入作業頁面資料ws(藥名)*/
            function LoadInsertPage(num) {
                let LoadInsertPage_ip="/webservice/NISPWSGETPRE.php";
                //console.log("http://localhost"+LoadInsertPage_ip+"?str="+AESEnCode("sFm=ILSGA&sTraID="+transKey+"&sPg="+num));
                $.ajax({
                    url:LoadInsertPage_ip+"?str="+AESEnCode("sFm=ILSGA&sTraID="+transKey+"&sPg="+num),
                    type:'POST',
                    dataType:"text",
                    success:function (json){
                        if(json){
                            let data=AESDeCode(json);
                            if(num=='DATAA'){
                               // console.log(data);
                            }
                            if(num=='PREB'){
                                let ISULING_OBJ=JSON.parse((JSON.parse(data))[0].ISULING);
                                $.each(ISULING_OBJ,function (index,value) {
                                    let JID_KEY = ISULING_OBJ[index].JID_KEY;
                                    let DIA = ISULING_OBJ[index].DIA;
                                    let STM =(ISULING_OBJ[index].STM).replace('§0§','');
                                    let  DCSORT = ISULING_OBJ[index].DCSORT;
                                    let QTY = ISULING_OBJ[index].QTY;
                                    let USEF = ISULING_OBJ[index].USENO;
                                    let QTY_tt=QTY!=''?"劑量:":"";
                                    let USEF_tt=USEF!=''?"頻率:":"";

                                    $("#MedLi").append(
                                        `
                                    <li id='${"MEDli"+index}' style="list-style-type: none;font-size:3vmin">
                                    <input type='button' value='選擇' id='${"Medbtn"+index}' onclick='MEDbtnID("${index}")'
                                       class="btn btn-primary"  data-dismiss="modal"  aria-hidden="true" style='width: 60px;margin-left: -25px;margin-right: 5px;font-size: 2.7vmin;' >
                                           ${STM}
                                    <li style='padding-left: 42px;font-size:2.5vmin'>${QTY_tt+QTY+USEF_tt+USEF}</li>
                                        <input type='text' value='${DIA}'  id='${"DIA"+index}'   style="display: none">
                                        <input type='text' value='${QTY}'  id='${"QTY"+index}'   style="display: none">
                                        <input type='text' value='${USEF}' id='${"sUSEF"+index}' style="display: none">
                                    </li>
                                    `
                                    );
                                });
                            }
                        }


                    },error:function (XMLHttpResponse,textStatus,errorThrown) {
                        errorModal(
                            "1 返回失敗,XMLHttpResponse.readyState:"+XMLHttpResponse.readyState+XMLHttpResponse.responseText+
                            "2 返回失敗,XMLHttpResponse.status:"+XMLHttpResponse.status+
                            "3 返回失敗,textStatus:"+textStatus+
                            "4 返回失敗,errorThrown:"+errorThrown
                        );
                    }
                });

            }
            function UPDATEDATA(spg,Json) {
                let  trsKey=$('#transKEY').val();
                let UPDATEDATA_ip="/webservice/NISPWSSETDATA.php";
                //console.log(Json);
                //console.log(UPDATEDATA_ip+'?str='+AESEnCode('sFm='+'ILSGA'+'&sTraID='+trsKey+'&sPg='+spg+'&sData='+Json));
                $.ajax({
                    url:UPDATEDATA_ip+'?str='+AESEnCode('sFm='+'ILSGA'+'&sTraID='+trsKey+'&sPg='+spg+'&sData='+Json),
                    type:'POST',
                    dataType:"text",
                    success:function (data) {
                        let json=JSON.parse(AESDeCode(data));
                        console.log(json.message);
                    },error:function (XMLHttpResponse,textStatus,errorThrown) {
                        errorModal(
                            "1 返回失敗,XMLHttpResponse.readyState:"+XMLHttpResponse.readyState+XMLHttpResponse.responseText+
                            "2 返回失敗,XMLHttpResponse.status:"+XMLHttpResponse.status+
                            "3 返回失敗,textStatus:"+textStatus+
                            "4 返回失敗,errorThrown:"+errorThrown
                        );
                    }
                });
            }
            function DELILSG() {
                let del_ip='/webservice/NISPWSDELILSG.php';
               // console.log("http://localhost"+del_ip+"?str="+AESEnCode("sFm="+'ILSGA'+"&sTraID="+$('#transKEY').val()+"&sPg="+$("#PageVal").val()+"&sCidFlag=D"+"&sUr="+$("#sUser").val()));
                $.ajax({
                    url:del_ip+"?str="+AESEnCode("sFm="+'ILSGA'+"&sTraID="+$('#transKEY').val()+"&sPg="+$("#PageVal").val()+"&sCidFlag=D"+"&sUr="+$("#sUser").val()),
                    type:'POST',
                    dataType:'text',
                    success:function (json) {
                        let data=JSON.parse(AESDeCode(json));
                        if(data.result=='false'){
                            errorModal('作廢失敗');
                            console.log(data.message);
                            return false;
                        }else {
                            $('#DELModal').modal('hide');
                            Reset(2);
                        }
                    },error:function (XMLHttpResponse,textStatus,errorThrown) {
                        errorModal(
                            "1 返回失敗,XMLHttpResponse.readyState:"+XMLHttpResponse.readyState+XMLHttpResponse.responseText+
                            "2 返回失敗,XMLHttpResponse.status:"+XMLHttpResponse.status+
                            "3 返回失敗,textStatus:"+textStatus+
                            "4 返回失敗,errorThrown:"+errorThrown
                        );
                    }
                });
            }
            function errorModal(str) {
                $('#Errormodal').modal('show');
                $("#ErrorText").text(str)
            }
            function focustext(modal) {
                switch (modal) {
                    case '1':
                        $('#timer').focus();
                        break;
                    case '2':
                        $('#timetxt').focus();
                        break;
                    case '3':
                        $('#STVALval').focus();
                        break;
                    case '4':
                        $('#dose0').focus();
                        break;
                    case '5':
                        $('#fUSEF_0').focus();
                        break;
                    case '6':
                        $('#dose1').focus();
                        break;
                    case '7':
                        $('#fUSEF_1').focus();
                        break;
                    case '8':
                        $('#dose2').focus();
                        break;
                    case '9':
                        $('#fUSEF_2').focus();
                        break;
                    case '10':
                        $('#DateStart').focus();
                        break;
                    case '11':
                        $('#DateEnd').focus();
                        break;
                }
            }
            function ValidateNumber(number) {
                let reg = new RegExp(/^\d+(\.\d{0,2})?$/);
                if (!number.match(reg)) {
                    return false;
                }
            }
            function NISPWSFMINI_Timer() {
                /*console.log("http://localhost/webservice/NISPWSFMINI.php?str="+AESEnCode("sFm=ILSGA&sPg=A"));*/
                $.ajax({
                    url:"/webservice/NISPWSFMINI.php?str="+AESEnCode("sFm=ILSGA&sPg=A"),
                    type:"POST",
                    dataType:"text",
                    success:function(data){
                        let obj=JSON.parse(AESDeCode(data));
                        let arr=JSON.parse(obj.ST_PREA);

                        $.each(arr,function (index,item) {
                            $("#ISTM").append(
                                `
                                <label style='font-size: 4.5vmin'><input type='radio' name='sRdoDateTime' id='${item.T_ID}' value='${item.name}' style='width: 6vmin;height: 6vmin' >${item.name}</label>
                                `
                            )
                        });
                    },error:function (XMLHttpResponse,textStatus,errorThrown) {
                        errorModal(
                            "1 返回失敗,XMLHttpResponse.readyState:"+XMLHttpResponse.readyState+XMLHttpResponse.responseText+
                            "2 返回失敗,XMLHttpResponse.status:"+XMLHttpResponse.status+
                            "3 返回失敗,textStatus:"+textStatus+
                            "4 返回失敗,errorThrown:"+errorThrown
                        );
                    }
                });
            }
            function Reset(num) {
                switch (num) {
                    case 1:
                        //畫面初始
                        $("#ISSG").prop("disabled", true);
                        $("#ISLN").prop("disabled", true);
                        $("#Inhibit").prop("disabled", true);
                        $("input[type=text]:not(#clickTime,#sUser)").val("");
                        break;
                    case 2:
                        //作廢後保留
                        $("#ISSG").prop("disabled", false);
                        $("#ISLN").prop("disabled", false);
                        $("#Inhibit").prop("disabled", false);
                        $("input[type=text]:not(#clickTime,#sUser,#DataTxt,#DA_idpt,#DA_idinpt,#DA_sBed)").val("");
                        $('#clickTime').val(0);
                        break;
                }
                $("#BSData").hide();
                $("#isuling").hide();
                $("#Imgisuling").hide();
                $("#NO_isuling").hide();

                $("#Del").prop("disabled", true);
                $("#DELMENU").prop("disabled", true);
                $('#SubmitBtn').prop('disabled',false);
                $("#Part").prop("disabled", true);

                $("#PageVal").val("A");
                $('#Textarea').val("");
                $("#SERCH_Click").val("1");
                $('#timer').prop('readonly',false);
                $('#timetxt').prop('readonly',false);
                $("#Part").prop('disabled',true);
                $("#Serch").prop('disabled',false);

                $("input[type=checkbox]:not(#ITNO_btn)").prop('checked',false);
                $("input[type=checkbox]").prop('disabled',false);
                $("input[type=radio]").prop('disabled',false);
                $("input[type=radio]").prop('checked',false);

                $("#ISSG").css({ 'background-color' : '', 'opacity' : '' ,'color':'white'});
                $("#ISLN").css({ 'background-color' : '', 'opacity' : '' ,'color':'white'});
                $("#Inhibit").css({ 'background-color' : '', 'opacity' : '' ,'color':'white'});
                $("#Part").css({ 'background-color' : '', 'opacity' : '' ,'color':'white'});

            }
            function clearvalue(VAL){
                switch (VAL) {
                    case 0:
                        $('#Isu_A').val("");
                        break;
                    case 1:
                        $('#Isu_B').val("");
                        break;
                    case 2:
                        $('#Isu_C').val("");
                        break;
                }
                $('#fUSEF_'+VAL).val("");
                $('#dose'+VAL).val("");
                $("#sID"+VAL).val("");
                $("#sUSEF"+VAL).val("");

            }
            function Serchcallback(AESdata){
                let page=$("#PageVal").val();
                let str=AESDeCode(AESdata);
                let datastr=JSON.parse(JSON.stringify(str).replace(/\u0000/g, '').replace(/\\u0000/g, ""));
                let data=JSON.parse(datastr);
                if(data){
                    $("#DELMENU").prop('disabled',false);
                    $("#Del").prop('disabled',false);
                    $("#timer").prop('readonly',true);
                    $("#timetxt").prop('readonly',true);
                    $("input[name=sRdoDateTime]").prop("disabled",true);
                    $("#STVALval").val("");
                    $("#sPress").val("");
                    $("#STVALval").bind('input propertychange',function () {
                        let newReg=new RegExp(/^[0-9]*$/);
                        let val=$(this).val();

                        if( val.length>0 && val.match(newReg)){
                            $("input[name=sPressure]").prop('checked',false);
                        }
                        else if($("#P_"+val).length>0){
                            $("#P_"+val).prop('checked',true);
                            $(this).val("");
                        }
                        else {
                            return false;
                        }
                    });

                    switch (page) {
                        case 'A':
                            //console.log(data);
                            $('#BSData').show();
                            $('#ISLN').prop('disabled',true);
                            $('#Inhibit').prop('disabled',true);

                            $.each(data,function (index,val) {
                                let idPt=val.idPt;
                                let DT_EXCUTE=val.DT_EXCUTE;
                                let TM_EXCUTE=val.TM_EXCUTE;
                                let JID_TIME=val.JID_TIME;
                                let MM_TPRS=val.MM_TPRS;
                                let SPRESS=val.SPRESS;
                                let ST_MEASURE=val.ST_MEASURE;
                                let CID_MEAL=val.CID_MEAL;
                                let transKEY=val.sTraID;
                                let FORMSEQANCE=val.FORMSEQANCE;
                                let SER_DT=val.SER_DT;
                                let SER_TM=val.SER_TM;
                                let regExp = new RegExp(/^[a-zA-Z]+$/);

                                if(idPt!=$("#DA_idpt").val())
                                {
                                    errorModal("病人資訊已異動,請先重新操作一次");
                                    return false;
                                }

                                TimeSet(JID_TIME,DT_EXCUTE,TM_EXCUTE);

                                $('#FORMSEQANCE').val(FORMSEQANCE);
                                $('#SER_DT').val(SER_DT);
                                $('#SER_TM').val(SER_TM);
                                $("#Textarea").val(MM_TPRS.match(/＆/)!=null?MM_TPRS.replace(/＆/g,'&'):MM_TPRS);
                                $("#transKEY").val(transKEY);
                                $("#STVALval").val(ST_MEASURE);

                                if(SPRESS.match(regExp)){
                                    $("#sPress").val(SPRESS);
                                    $("#P_"+SPRESS).prop('checked',true);
                                }

                                if (CID_MEAL=='A'){
                                    $("#Eating1").prop('checked',true);

                                }
                                else if(CID_MEAL=='B')
                                {
                                    $("#Eating2").prop('checked',true);
                                }
                                else{
                                    $('input[name=IDGP]').prop('checked',false);
                                }
                            });


                            break;
                        case 'B':
                            let txt_index='';
                            /*  console.log(data);*/
                            $.each(data,function (index) {
                                if(index==0){
                                    txt_index='A';
                                }
                                if(index==1){
                                    txt_index='B';
                                }
                                if (index==2){
                                    txt_index='C';
                                }
                                let idPt=data[index].idPt;
                                let JID_TIME=data[index].JID_TIME;
                                let ID_REGION=data[index].ID_REGION; //部位A1
                                let ID_ORDER=data[index].ID_ORDER;   //藥名id
                                let NM_ORDER=data[index].NM_ORDER;
                                /* let DB_DOSE=data[index].DB_DOSE; //-1*/
                                let ST_DOSE=data[index].ST_DOSE; //劑量
                                let ST_USENO=data[index].ST_USENO; //頻率
                                let LSTPT=data[index].LSTPT; //頻率
                                let TM_EXCUTE=data[index].TM_EXCUTE;
                                let DT_EXCUTE=data[index].DT_EXCUTE;
                                if(idPt!=$("#DA_idpt").val()){
                                    errorModal("病人資訊已異動,請先重新操作一次");
                                    return false;
                                }


                                $('#isuling').show();
                                $('#ISSG').prop('disabled',true);
                                $('#Inhibit').prop('disabled',true);
                                $("#SERCH_Click").val("2");
                                $("#DT_EXE").val(DT_EXCUTE);
                                $("#TM_EXE").val(TM_EXCUTE+"00");
                                $("#LastPart").val(LSTPT);
                                $("#transKEY").val(data[index].sTraID);
                                $("#Isu_"+txt_index).val(NM_ORDER);
                                $("#dose"+index).val(ST_DOSE);
                                $("#fUSEF_"+index).val(ST_USENO);
                                $("#sID"+index).val(data[index].ID_ORDER);
                                $("#sUSEF"+index).val(data[index].ST_USENO);
                                $("#ISLNLi").children().remove();
                                $("#ISLNLi").append(
                                    `
                                    <li style="display: none">
                                     <input type="text"   id='${"DT_E"+index}' value='${DT_EXCUTE}'>
                                     <input type="text"   id='${"TM_E"+index}' value='${TM_EXCUTE}'>
                                     <input type="text"   id='${"JID_T"+index}' value='${JID_TIME}'>
                                     <input type="text"   id='${"ID_R"+index}' value='${ID_REGION}'>
                                     <input type="text"   id='${"ID_O"+index}' value='${ID_ORDER}'>
                                     <input type="text"   id='${"ST_D"+index}' value='${ST_DOSE}'>
                                     <input type="text"   id='${"NM_O"+index}' value='${NM_ORDER}'>
                                     <input type="text"   id='${"ST_U"+index}' value='${ST_USENO}'>
                                     <input type="text"   id='${"LSTPT"+index}' value='${LSTPT}'>
                                    </li>
                                `
                                );
                                $("#Part"+ID_REGION).prop('checked',true);
                                TimeSet(JID_TIME,DT_EXCUTE,TM_EXCUTE);


                            });
                            $('input[name=part]').prop('disabled',true);
                            break;
                        case 'C':
                   /*       console.log(data);*/
                            let C_idPt=data.idPt;
                            let C_DT=data.DT_EXCUTE;
                            let C_TM=data.TM_EXCUTE;
                            let C_F_REGION=data.REGION;
                            let C_NO_MMAL=data.NO_MMAL;
                            let C_sTraID=data.sTraID;
                            let C_FORMSEQANCE=data.FORMSEQANCE;
                            let C_JID_TIME=data.JID_TIME;

                            if(C_idPt!=$("#DA_idpt").val()){
                                errorModal("病人資訊已異動,請先重新操作一次");
                                return false;
                            }

                            $("#SERCH_Click").val("2");
                            $("#Part").prop('disabled',true);
                            $('#FORMSEQANCE').val(C_FORMSEQANCE);
                            $("input[name='forbid']").prop('checked',false);
                            $.each(C_F_REGION,function (index,val) {
                                $('#No_'+val).prop('checked',true);
                            });

                            TimeSet(C_JID_TIME,C_DT,C_TM);
                            Forbid_Dateback(C_idPt,C_DT,C_TM,C_NO_MMAL,C_sTraID);
                            break;
                    }
                }

            }
            function bedcallback(data){
                let str=AESDeCode(data);
                let datastr=JSON.parse(JSON.stringify(str).replace(/\u0000/g, '').replace(/\\u0000/g, ""));
                let dataObj=JSON.parse(datastr);
                if(dataObj){

                    const Disabled_T=['Part','Del','DELMENU','Part'];
                    const CheackName_F=['sRdoDateTime','part','sPressure','IDGP'];
                    const Disabled_F=['ISSG','ISLN','Inhibit','SubmitBtn'];
                    const HandBtn=['ISSG','ISLN','Inhibit','Part','BSData','isuling','Imgisuling','NO_isuling'];

                    const Value_Clear=['FORMSEQANCE','DT_EXE','TM_EXE','sSave','STDATA_FORMWT','STDATA_JID_NSRANK','STDATB_idFrm','transKEY','SER_TM','SER_DT',
                        'timer','timetxt','LastPart','dose0','dose1','dose2','fUSEF_0','fUSEF_1','fUSEF_2','sID0','sUSEF0','sID1','sUSEF1','sUSEF2','sID2',
                        'Isu_A','Isu_B','Isu_C','STVALval','Textarea'];

                    $.each(CheackName_F,function (index,value) {
                        $("input[name="+value+"]").prop("checked",false);
                        $("input[name="+value+"]").prop("disabled",false);
                    });

                    $.each(Disabled_F,function (index,value) {
                        $("#"+value).prop("disabled",false);
                    });

                    $.each(Disabled_T,function (index,value) {
                        $("#"+value).prop("disabled",true);

                    });

                    $.each(Value_Clear,function (index,value) {
                        $("#"+value).val("");

                    });

                    $.each(HandBtn,function (index,value) {
                        //CSS
                        if (index>3){
                            $("#"+value).css({"display": "none"});
                        }else {
                            $("#"+value).css({'background-color' : '', 'opacity' : '','color':'white' });
                        }

                    });

                    $("#DataTxt").val(dataObj[0].DataTxt);
                    $("#DA_idpt").val(dataObj[0].IDPT);
                    $("#DA_idinpt").val(dataObj[0].IDINPT);
                    $("#DA_sBed").val(dataObj[0].SBED);
                    $("#clickTime").val(0);
                    $("#PageVal").val('A');

                    $("#timer").prop("readOnly",false);
                    $("#timetxt").prop("readOnly",false);
                }
            }
            function TimeSet(JID_TIME,DT,TM) {
                $("#timetxt").val(TM.substr(0,2)+TM.substr(2,2));
                $("#timer").val(DT);
                $("#IDTM").val(JID_TIME);
                $("input[name=IDGP]").prop('checked',false);
                $("#"+JID_TIME).prop('checked',true);

                if(JID_TIME=="ISTM00000005"){
                    $("input[name=IDGP]").prop('disabled',true);
                }else {
                    $("input[name=IDGP]").prop('disabled',false);
                }
            }
            function fumadol() {
                $('#FuModal').modal('show');
                $("#Fuval").val("");
            }
            function PersonFuval() {
                let Fval=$("#Fuval").val();
                let index=$('#fut').val();
                $("#fUSEF_"+index).val("");
                $("#fUSEF_"+index).val(Fval);
                $('#FuModal').modal('hide');
            }
            function  reloadmsg() {
                return '確認要重新整理嗎?';
            }
        });
        function MEDbtnID(NUM) {
            let txt = ($("#MEDli" + NUM).text()).trim();
            let IDtxt=($("#DIA"+NUM).val()).trim();
            let USERtxt=($("#sUSEF"+NUM).val()).trim();
            let ckt=($("#ckt").val()).trim();
            let QTY=($("#QTY"+NUM).val()).trim();

            switch (ckt) {
                case '1':

                    $("#sID0").val(IDtxt);
                    $("#sUSEF0").val(USERtxt);
                    $("#Isu_A").val(txt);
                    $("#fUSEF_0").val(USERtxt);
                    $("#dose0").val(QTY);

                    setTimeout("$(\"#dose0\").focus();",500);
                    break;
                case '2':

                    $("#sID1").val(IDtxt);
                    $("#sUSEF1").val(USERtxt);
                    $("#Isu_B").val(txt);
                    $("#fUSEF_1").val(USERtxt);
                    $("#dose1").val(QTY);


                    setTimeout("$(\"#dose1\").focus();",500);
                    break;
                case '3':
                    $("#sID2").val(IDtxt);
                    $("#sUSEF2").val(USERtxt);
                    $("#Isu_C").val(txt);
                    $("#fUSEF_2").val(USERtxt);
                    $("#dose2").val(QTY);


                    setTimeout("$(\"#dose2\").focus();",500);
                    break;
                default:
                    break;
            }
        }
        function fuval(val){
            let num=$('#funum').val();
            $("#fUSEF_"+num).val(val);
            $('#FuModal').modal('hide');
        }
    </script>

</head>
<body>

<div id="wrapper"></div>
<div id="loading" >請稍後<img class="loadimg" src="../../dotloading.gif"></div>
<div class="container">
    <h1>血糖胰島素注射</h1>
    <form id="form1" >
    <span style="margin-left:0 px">
        <button type="button" class="btn btn-secondary btn-md" disabled style="display: none">回主畫面</button>
        <button type="button"  class="btn btn-warning btn-md" style="margin-left: 1px"   id="sbed" >責任床位</button><span style="margin-left: 1px"><b>使用者:<?php echo $sUr?></b></span>
    </span>

        <span class="float-left">
            <button type="submit" id="SubmitBtn" class="btn btn-primary btn-md" >儲存</button>
            <button type="button" id="Serch" class="btn btn-primary btn-md" >查詢</button>
            <button type="button" id="DELMENU" class="btn btn-primary btn-md"  data-toggle="modal" data-target="#DELModal">作廢</button>
            <button type="button" id="ReSet" class="btn btn-primary btn-md"  >清除</button>
            <button type="button" class="btn btn-secondary btn-md" disabled style="margin-right: 3px ;display: none">預設</button>
        </span>

        <table class="table" style="font-size:3.5vmin">
            <thead>
            <thead>
            </thead>
        </table>
        <input id="DataTxt"  value="" type="text" readonly="readonly" style="border:1px white;font-size: 4vmin;width:100vmin;background-color: #FFFBCC;border-radius:3px;">
        <div class="Parametertable">
            <input id="clickTime" value="0"  type="text"  placeholder="clickTime"> <!--頁面載入 0 or 1-->
            <input id="DA_idpt" value="" type="text" name="DA_idpt"   placeholder="DA_idpt"> <!--病歷號-->
            <input id="DA_idinpt" value="" type="text" name="DA_idinpt"  placeholder="DA_idinpt"><!--住院號-->
            <input id="DA_sBed" value="" type="text" name="DA_sBed" placeholder="DA_sBed"><!--床號-->
            <input id="FORMSEQANCE" type="text" value="" placeholder="FORMSEQANCE">
            <input id="DT_EXE" type="text" value="" placeholder="DT_EXE">
            <input id="TM_EXE" type="text" value="" placeholder="TM_EXE">
            <input id="PageVal" type="text" value="" placeholder="PageVal">
            <input id="sSave" value="" type="text" placeholder="sSave">      <!--存檔權限-->
            <input id="sUser" type="text" value="<?php echo $Account?>" placeholder="sUser">
            <input id="sPress" type="text" value="" placeholder="sPress">
            <input id="STDATA_FORMWT" type="text" value="" placeholder="STDATA_FORMWT">
            <input id="STDATA_JID_NSRANK" type="text" value="" placeholder="STDATA_JID_NSRANK">
            <input id="STDATB_idFrm" type="text" value="" placeholder="STDATB_idFrm">
            <input id="transKEY" value="" type="text" placeholder="transKEY"> <!--交易序號-->
            <input id="SER_DT" value="" type="text" placeholder="SER_DT">
            <input id="SER_TM" value="" type="text" placeholder="SER_TM">
            <input id="ERRORVAL" value="" type="text" placeholder="ERRORVAL">
            <input id="SERCH_Click" value="1" type="text" placeholder="SERCH_Click">
        </div>
        <div class="Otimer" >
            <div class="pageTime">
                <label style="font-size: 4vmin">評估時間:</label>
                <input  type="text" id="timer" value="" name="sDate" placeholder="YYYMMDD" maxlength="7" autocomplete="off">
                <input type="text" id="timetxt" value="" name="sTime" placeholder="HHMM" maxlength="4" autocomplete="off">
                <input type="text" name="sIDTM" id="IDTM" value=""  placeholder="IDTM" style="display:none;">
            </div>
            <div id="ISTM"></div>
        </div>

        <div class="Features">
            <button type="button" class="btn btn-primary " name="click"  id="ISSG" value="A">血糖</button>
            <button type="button" class="btn btn-primary " name="click"  id="ISLN" value="B">胰島素</button>
            <button type="button" class="btn btn-primary "  name="click" id="Inhibit" value="C" >禁打</button>
            <button type="button" class="btn btn-primary " name="click" id="Part" value="D">部位</button>
        </div>

        <!--血糖-->
        <div id="BSData" style="font-size: 3.5vmin">
            <div id="Eating">
                <div style="background-color: brown;color:white;padding-top: 5px;padding-left: 5px;border-radius:3px;">
                    <label style="margin-right: 5px;font-size: 5vmin"> <input type="radio" value="A" id="Eating1" name="IDGP"
                                                                              style="width: 6vmin;height: 6vmin"
                        >飯前</label>
                    <label style="margin-right: 5px;font-size: 5vmin"> <input type="radio" value="B" id="Eating2" name="IDGP"
                                                                              style="width: 6vmin;height: 6vmin"
                        >飯後</label>
                </div>
                <div style="background-color: #FFFBCC;padding-top: 10px">

                    <div style="margin-top: 5px;font-size: 4vmin">
                        <label>血糖:<input type="text" style="width: 80px" name="STVAL" id="STVALval" autocomplete="off"></label>
                        <label> <input type="radio" value="LO" id="P_LO" name="sPressure" style="width:5.5vmin;height: 5.5vmin"
                            >LO</label>
                        <label> <input type="radio" value="HI" id="P_HI" name="sPressure" style="width: 5.5vmin;height: 5.5vmin"
                            >HI</label>
                        <label> <input type="radio" value="NONE" id="P_NONE"  name="sPressure" style="width:5.5vmin;height: 5.5vmin"
                            >NONE</label>
                        <label> <input type="radio" value="CE" id="P_CE" name="sPressure" style="width: 5.5vmin;height: 5.5vmin"
                            >CE</label>
                    </div>
                    <div class="form-group shadow-textarea">
                        <textarea class="form-control z-depth-1"  id="Textarea" name="MMVAL" rows="3"
                                  placeholder="備註" autocomplete="off"></textarea>
                    </div>
                </div>
            </div>
        </div>
        <!--胰島素-->
        <div id="isuling">
            <div style="background-color: brown;color:white;font-size: 4vmin;border-radius:3px;">
                <label style="display: none;"> <input type="radio" value="1" id="ITNO_btn" name="ITNO"  checked>施打</label>

                <label >上次施打位置:<input type="text"  value="" id="LastPart" readonly="readonly"></label>
            </div>

            <div style="background-color: #edfbff;font-size: 3.5vmin">
                <div style="background-color: #FFFBCC;font-size: 4vmin">
                    <label>注射部位:</label><br>
                    <label><input type="radio" id="PartA" name="part" value="A" style="width: 5vmin;height: 5vmin">A.左臂</label>
                    <label><input type="radio" id="PartB" name="part" value="B" style="width: 5vmin;height: 5vmin">B.左腹</label>
                    <label><input type="radio" id="PartC" name="part" value="C" style="width: 5vmin;height: 5vmin">C.左臀</label>
                    <label><input type="radio" id="PartD" name="part" value="D" style="width: 5vmin;height: 5vmin">D.左腿</label><br>
                    <label><input type="radio" id="PartE" name="part" value="E" style="width: 5vmin;height: 5vmin">E.右腿</label>
                    <label><input type="radio" id="PartF" name="part" value="F" style="width: 5vmin;height: 5vmin">F.右臀</label>
                    <label><input type="radio" id="PartG" name="part" value="G" style="width: 5vmin;height: 5vmin">G.右腹</label>
                    <label><input type="radio" id="PartH" name="part" value="H" style="width: 5vmin;height: 5vmin">H.右臂</label>
                </div>

                <div>

                    <button type="button" name="ISLNch" value="1" class="btn btn-primary btn-md" style="font-size: 3.5vmin" data-toggle="modal"
                            data-target="#isuModal" data-whatever="isubtn1">選擇
                    </button>
                    <label>胰島素:</label><input type="text" value="" name="ISLN_A" id="Isu_A" style="border: 1px white;font-size: 4vmin;width:70vmin" autocomplete="off">
                    <div style="margin-top: 5px">
                        <label>劑量:<input type="text" id="dose0"  style="width: 70px;margin-right: 3px" autocomplete="off"></label><label>頻率:<input
                                    type="text" style="width: 80px;" id="fUSEF_0" class="FuQuenCy" autocomplete="off" >
                            <button  id="FuClear1" style="color: white;border:0;background-color: #6c757d;border-radius:3px;">清除此欄</button></label>
                    </div>
                    <div style="display: none">
                        <input type="text" value="" id="sID0" ><!-- 藥id-->
                        <input type="text" value="" id="sUSEF0">
                        <input type="text" value="" id="ckt" >
                        <input type="text" value="" id="fut">
                        <input type="text" value="" id="funum">
                    </div>

                </div>

                <div id="ISU2">
                    <button type="button" name="ISLNch" value="2" class="btn btn-primary btn-md" style="font-size: 3.5vmin" data-toggle="modal"
                            data-target="#isuModal" data-whatever="isubtn2">選擇
                    </button>
                    <label>胰島素:</label><input type="text" value="" name="ISLN_B" id="Isu_B" style="border: 1px white;font-size: 4vmin;width:70vmin" autocomplete="off">
                    <div style="margin-top: 5px">
                        <label>劑量:<input type="text" id="dose1"  style="width: 70px;margin-right: 3px" autocomplete="off"></label><label>頻率:<input
                                    type="text" style="width: 80px;" id="fUSEF_1" class="FuQuenCy" autocomplete="off">
                            <button type="button"  id="FuClear2" style="color: white;border:0;background-color: #6c757d;border-radius:3px;">清除此欄</button>
                        </label>

                    </div>
                    <input type="text" value="" id="sID1" style="display: none">
                    <input type="text" value="" id="sUSEF1" style="display: none">
                </div>
                <div id="ISU3" >
                    <button type="button" name="ISLNch" value="3" class="btn btn-primary btn-md" style="font-size: 3.5vmin" data-toggle="modal"
                            data-target="#isuModal" data-whatever="isubtn3">選擇
                    </button>
                    <label>胰島素:</label><input type="text" value="" name="ISLN_C" id="Isu_C" style="border: 1px white;font-size: 4vmin;width:70vmin" autocomplete="off">
                    <div style="margin-top: 5px">
                        <label>劑量:<input type="text" id="dose2"  style="width: 70px;margin-right: 3px" autocomplete="off"></label><label>頻率:<input
                                    type="text" style="width: 80px;" id="fUSEF_2" class="FuQuenCy" autocomplete="off">
                            <button  type="button"   id="FuClear3" style="color: white;border:0;background-color: #6c757d;border-radius:3px;">清除此欄</button>
                        </label>
                    </div>
                    <input type="text"  value="" id="sID2" style="display: none">
                    <input type="text"  value="" id="sUSEF2" style="display: none">
                </div>
            </div>
        </div>
        <!--禁打-->
        <div id="NO_isuling" style="font-size: 4vmin;">
            <div style="background-color:#FF0000;">
                <label style="color: white">禁打部位</label>
            </div>
            <div style="background-color: #FFFBCC;border-radius:3px;padding-top: 5px">
                <label><input type="checkbox" id="No_A" name="forbid" value="A" style="width: 4.5vmin;height: 4.5vmin" >A.左臂</label>
                <label><input type="checkbox" id="No_B" name="forbid" value="B" style="width: 4.5vmin;height: 4.5vmin">B.左腹</label>
                <label><input type="checkbox" id="No_C" name="forbid" value="C" style="width: 4.5vmin;height: 4.5vmin">C.左臀</label>
                <label><input type="checkbox" id="No_D" name="forbid" value="D" style="width: 4.5vmin;height: 4.5vmin">D.左腿</label><br>
                <label><input type="checkbox" id="No_E" name="forbid" value="E" style="width: 4.5vmin;height: 4.5vmin">E.右腿</label>
                <label><input type="checkbox" id="No_F" name="forbid" value="F" style="width: 4.5vmin;height: 4.5vmin">F.右臀</label>
                <label><input type="checkbox" id="No_G" name="forbid" value="G" style="width: 4.5vmin;height: 4.5vmin">G.右腹</label>
                <label><input type="checkbox" id="No_H" name="forbid" value="H" style="width: 4.5vmin;height: 4.5vmin">H.右臂</label>
            </div>
            <div style="background-color:#FF0000;">
                <label style="color: white">禁打原因</label>
            </div>
            <div id="NOisuling_RE" style="background-color: #FFFBCC;border-radius:3px;padding-top: 5px">

            </div>
        </div>
        <!--部位圖-->
        <div id="Imgisuling">
            <img src="ISLN800.bmp" style="z-index: -1;">
            <table id="A" border="1">                　
                <tr>                    　
                    <td id="A1">A1</td>                    　
                    <td id="A2">A2</td>                    　
                </tr>
                　
                <tr>
                    　
                    <td id="A3">A3</td>                    　
                    <td id="A4">A4</td>
                    　
                </tr>
                <tr>
                    <td id="A5">A5</td>
                    <td id="A6">A6</td>
                </tr>
                <tr>
                    　
                    <td id="A7">A7</td>                    　
                    <td id="A8">A8</td>
                </tr>
            </table>
            <table id="B" border="1" >
                　
                <tr>
                    　
                    <td id="B1">B1</td>                    　
                    <td id="B2">B2</td>
                    　
                </tr>
                　
                <tr>
                    　
                    <td id="B3">B3</td>                    　
                    <td id="B4">B4</td>
                    　
                </tr>
                <tr>
                    <td id="B5">B5</td>
                    <td id="B6">B6</td>
                </tr>
                <tr>
                    　
                    <td id="B7">B7</td>                    　
                    <td id="B8">B8</td>
                </tr>
            </table>
            <table id="C" border="1" >
                　
                <tr>
                    　
                    <td id="C1">C1</td>                    　
                    <td id="C2">C2</td>
                    　
                </tr>
                　
                <tr>
                    　
                    <td id="C3">C3</td>
                    　
                    <td id="C4">C4</td>
                    　
                </tr>
                <tr>
                    <td id="C5">C5</td>
                    <td id="C6">C6</td>
                </tr>
                <tr>
                    　
                    <td id="C7">C7</td>
                    　
                    <td id="C8">C8</td>
                </tr>
            </table>
            <table id="D" border="1">
                　
                <tr>
                    　
                    <td id="D1">D1</td>
                    　
                    <td id="D2">D2</td>
                    　
                </tr>
                　
                <tr>
                    　
                    <td id="D3">D3</td>
                    　
                    <td id="D4">D4</td>
                    　
                </tr>
                <tr>
                    <td id="D5">D5</td>
                    <td id="D6">D6</td>
                </tr>
                <tr>
                    　
                    <td id="D7">D7</td>
                    　
                    <td id="D8">D8</td>
                </tr>
            </table>
            <table id="H" border="1"  >
                　
                <tr>
                    　
                    <td id="H2">H2</td>
                    　
                    <td id="H1">H1</td>
                    　
                </tr>
                　
                <tr>
                    　
                    <td id="H4">H4</td>
                    　
                    <td id="H3">H3</td>
                    　
                </tr>
                <tr>
                    <td id="H6">H6</td>
                    <td id="H5">H5</td>
                </tr>
                <tr>
                    　
                    <td id="H8">H8</td>
                    　
                    <td id="H7">H7</td>
                </tr>
            </table>
            <table id="G" border="1"  >
                　
                <tr>
                    　
                    <td id="G2">G2</td>
                    　
                    <td id="G1">G1</td>
                    　
                </tr>
                　
                <tr>
                    　
                    <td  id="G4">G4</td>
                    　
                    <td  id="G3">G3</td>
                    　
                </tr>
                <tr>
                    <td  id="G6">G6</td>
                    <td  id="G5">G5</td>
                </tr>
                <tr>
                    　
                    <td  id="G8">G8</td>
                    　
                    <td  id="G7">G7</td>
                </tr>
            </table>
            <table id="F" border="1" >
                　
                <tr>
                    　
                    <td id="F2">F2</td>
                    　
                    <td id="F1">F1</td>
                    　
                </tr>
                　
                <tr>
                    　
                    <td id="F4">F4</td>
                    　
                    <td id="F3">F3</td>
                    　
                </tr>
                <tr>
                    <td id="F6">F6</td>
                    <td id="F5">F5</td>
                </tr>
                <tr>
                    　
                    <td id="F8">F8</td>
                    　
                    <td id="F7">F7</td>
                </tr>
            </table>
            <table id="E" border="1"  >
                　
                <tr>
                    　
                    <td id="E2">E2</td>
                    　
                    <td id="E1">E1</td>
                    　
                </tr>
                　
                <tr>
                    　
                    <td id="E4">E4</td>                    　
                    <td id="E3">E3</td>
                    　
                </tr>
                <tr>
                    <td id="E6">E6</td>
                    <td id="E5">E5</td>
                </tr>
                <tr>
                    <td id="E8">E8</td>                    　
                    <td id="E7">E7</td>
                </tr>
            </table>
        </div>
    </form>



    <div class="modal fade" id="isuModal" tabindex="-1" role="dialog" aria-labelledby="isuModalLabel" aria-hidden="true"
         style="80vim" data-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="isuModalLabel">胰島素藥品選擇</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <ul id="MedLi">
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">上一頁</button>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade bd-example-modal-sm" id="success" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog modal-sm">
            <div class="modal-content" style="height: 8%;width: 35%">
                <p style="font-size: 3vmin">儲存成功</p>
            </div>
        </div>
    </div>
    <div class="modal fade bd-example-modal-sm" id="error" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog modal-sm">
            <div class="modal-content" style="height: 8%;width: 35%">
                <p style="font-size: 3vmin">儲存失敗</p>
            </div>
        </div>
    </div>


    <div class="modal fade" id="DELModal" tabindex="-1" role="dialog" aria-labelledby="DELModalCenterTitle" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog  modal-sm modal-dialog-centered" role="document">
            <div class="modal-content" >
                <div class="modal-header">
                    <h5 class="modal-title" id="DELModalCenterTitle">作廢提醒</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="overflow-y: hidden;overflow-x: hidden;">
                    <p>請確認是否要作廢此清單!!!</p>
                </div>
                <div class="modal-footer">
                    <button type="button" id="Del" class="btn btn-primary" style="height: 6vh;font-size: 20px">作廢</button>
                    <button type="button" class="btn btn-secondary " data-dismiss="modal" style="height: 6vh;font-size: 20px">取消</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="FuModal" tabindex="-1" role="dialog" aria-labelledby="FuModalCenterTitle" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-md" role="document">
            <div class="modal-content" >
                <div class="modal-header">
                    <h5 class="modal-title" id="FuModalCenterTitle">頻率選擇</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body" style="overflow-x: hidden;">
                    <label style="font-size: 20px;margin-top: 2px;">自訂:<input value="" type="text" style="width: 100px" id="Fuval"></label>
                    <button type="button" id="FuConfirm" class="btn btn-primary"  style="margin-left: 5px;border-radius: 4px;margin-top: -9px;">確定</button>
                    <div class="row">
                        <div class="col-6" id="fu1"></div>
                        <div class="col-6" id="fu2"></div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="Errormodal" tabindex="-1" aria-labelledby="ErrormodalCenterTitle" role="dialog" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-md" role="document">
            <div class="modal-content" style="height:30%;width: 90%;">
                <div class="modal-header">
                    <h5 class="modal-title" id="ErrormodalCenterTitle">錯誤提示</h5>
                </div>
                <div class="modal-body" style="overflow-y: auto">
                    <p id="ErrorText" style="font-size: 2.5vmin;word-wrap: break-word"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" id="ErorFocus" class="btn btn-secondary" data-dismiss="modal" >關閉</button>
                </div>
            </div>
        </div>
    </div>
    <h1 id="titleName"></h1>
    <div>
        <table id="ser_tb" ></table>
        <ul id="SerchLi" >
            <li></li>
        </ul>
        <div>
            <ul id="ISLNLi">
                <li></li>
            </ul>
        </div>
    </div>
</div>
</body>
</html>