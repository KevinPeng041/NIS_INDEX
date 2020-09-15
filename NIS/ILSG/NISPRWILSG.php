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


$sIdUser_value=explode('=',$sIdUser_STR);
$passwd_value=explode('=',$passwd_STR);
$user_value=explode('=',$user_STR);


$sIdUser=trim($sIdUser_value[1]);/*帳號*/
$passwd=trim($passwd_value[1]);/*密碼*/
$sUr=trim($user_value[1]);/*使用者*/


$Account=strtoupper(str_pad($sIdUser,7,"0",STR_PAD_LEFT));
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
    <script>
       /* var ckw=setInterval(function () {
            try {
                if(!window.opener) {
                    alert("此帳號以被登出,請重新登入開啟");
                    window.close();
                }

            }catch (e) {
                $("#wrapper").show();
                alert(e);
                clearInterval(ckw);
                window.close();
                return false;
            }

        },500);*/

        /*ajax Transitions img*/
        function isload(t) {
            if(window.document.readyState=="complete")
            {
                $("#loading").hide();
                $("#wrapper").hide();
                if(t){
                    window.clearInterval(t);
                }
            }
        }
        t=window.setInterval(function(){
            isload(t);
        },700);


        /*reset ui*/
        function Reset(num) {
            switch (num) {
                case 1:
                    $("#ISSG").attr("disabled", true);
                    $("#ISLN").attr("disabled", true);
                     $("#Inhibit").attr("disabled", true);

                    break;
                case 2:
                    $("#ISSG").attr("disabled", false);
                    $("#ISLN").attr("disabled", false);
                    $("#Inhibit").attr("disabled", false);
                    break;
            }


            $("#BSData").hide();
            $("#isuling").hide();
            $("#Imgisuling").hide();
            $("#NO_isuling").hide();
            $("#Del").attr("disabled", true);
            $("#DELMENU").attr("disabled", true);
            $('#SubmitBtn').prop('disabled',false);
            $("#Part").prop("disabled", true);
            $("input[type=text]:not(#clickTime,#sUser)").val("");
            $('#Textarea').val("");
            $("#SERCH_Click").val("1");
            /*時間格式化*/
            $("input[name='sRdoDateTime']").attr("disabled", false);
            $("input[name='sRdoDateTime']").prop('checked',false);
            $('#timer').attr('readonly',false);
            $('#timetxt').attr('readonly',false);
            /*胰島素清空*/
            $("#Part").prop('disabled',true);
            $("#Serch").prop('disabled',false);
            /*血糖清空*/
            $("input[type=radio]").prop('checked',false);
            $("input[type=radio]").prop('disabled',false);
            $("input[type=checkbox]").prop('checked',false);
            $("input[type=checkbox]").prop('disabled',false);
            $("#ISSG").css({ 'background-color' : '', 'opacity' : '' ,'color':'white'});
            $("#ISLN").css({ 'background-color' : '', 'opacity' : '' ,'color':'white'});
            $("#Inhibit").css({ 'background-color' : '', 'opacity' : '' ,'color':'white'});
            $("#Part").css({ 'background-color' : '', 'opacity' : '' ,'color':'white'});

        }

        function  clearvalue(VAL){
            switch (VAL) {
                case 1:
                    $('#Isu_A').val("");
                    break;
                case 2:
                    $('#Isu_B').val("");
                    break;
                case 3:
                    $('#Isu_C').val("");
                    break;
            }
            $('#fUSEF_'+VAL).val("");
            $('#dose'+VAL).val("");
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
            <button type="reset" class="btn btn-primary btn-md"  onclick="Reset(1)">清除</button>
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
                <input type="text" name="sIDTM" id="IDTM" value="" style="display: none" placeholder="IDTM">
            </div>
            <div id="ISTM"></div>
        </div>

        <div class="Features">
            <button type="button" class="btn btn-primary " name="click"  id="ISSG" value="A">血糖</button>
            <button type="button" class="btn btn-primary " name="click"  id="ISLN" value="B">胰島素</button>
            <button type="button" class="btn btn-primary "  name="click" id="Inhibit" value="C" >禁打</button>
            <button type="button" class="btn btn-primary " name="click" id="Part" value="D">部位</button>
        </div>
        <script>
            /*責任床位ws*/
            function bedcallback(data)
            {
                var str=AESDeCode(data);
                var datastr=JSON.parse(JSON.stringify(str).replace(/\u0000/g, '').replace(/\\u0000/g, ""));
                var dataObj=JSON.parse(datastr);
                console.log(dataObj);

                if(dataObj){
                    $("#ISSG").prop("disabled", false);
                    $("#ISLN").prop("disabled", false);
                    $("#Inhibit").prop("disabled", false);
                    $("#Part").prop("disabled", true);
                    $("#SubmitBtn").prop('disabled',false);


                    $("#DataTxt").val(dataObj[0].DataTxt);
                    $("#DA_idpt").val(dataObj[0].IDPT);
                    $("#DA_idinpt").val(dataObj[0].IDINPT);
                    $("#DA_sBed").val(dataObj[0].SBED);
                    $("#clickTime").val(0);
                    $("#PageVal").val('A');
                    $("#BSData").css({"display": "none"});
                    $("#isuling").css({"display": "none"});
                    $("#Imgisuling").css({"display": "none"});
                    $("#NO_isuling").css({"display": "none"});


                    $("#Del").prop('disabled',true);
                    $("#ISSG").prop('disabled',false);
                    $("#ISLN").prop('disabled',false);
                    $("#Inhibit").prop('disabled',false);
                    $("#DELMENU").prop('disabled',true);

                    $("#FORMSEQANCE").val('');
                    $("#DT_EXE").val('');
                    $("#TM_EXE").val('');
                    $("#sSave").val('');

                    $("#STDATA_FORMWT").val('');
                    $("#STDATA_JID_NSRANK").val('');
                    $("#STDATB_idFrm").val('');
                    $("#transKEY").val('');
                    $("#SER_TM").val('');
                    $("#SER_DT").val('');

                    /*時間格式化*/
                    var sRdoD= document.getElementsByName('sRdoDateTime');
                    for(var i=0;i<sRdoD.length;i++){
                        document.getElementsByName('sRdoDateTime')[i].disabled=false;
                       document.getElementsByName('sRdoDateTime')[i].checked=false;
                    }

                   document.getElementById('timer').readOnly=false;
                   document.getElementById('timetxt').readOnly=false;
                   document.getElementById('timer').value='';
                   document.getElementById('timetxt').value='';
                    /*胰島素清空*/
                   document.getElementById('Part').disabled=true;
                    var Part=document.getElementsByName('part');
                    for(var i=0;i<Part.length;i++){
                      document.getElementsByName('part')[i].disabled=false;
                      document.getElementsByName('part')[i].checked=false;
                    }
                    document.getElementById('LastPart').value='';
                   document.getElementById('dose1').value='';
                   document.getElementById('dose2').value='';
                   document.getElementById('dose3').value='';
                    document.getElementById('fUSEF_1').value='';
                   document.getElementById('fUSEF_2').value='';
                    document.getElementById('fUSEF_3').value='';
                   document.getElementById('tt1').value='';
                   document.getElementById('tt2').value='';
                   document.getElementById('tt3').value='';
                  document.getElementById('tt4').value='';
                    document.getElementById('tt5').value='';
                   document.getElementById('tt6').value='';
                    document.getElementById('Isu_A').value='';
                   document.getElementById('Isu_B').value='';
                   document.getElementById('Isu_C').value='';
                    /*血糖清空*/
                    var sPress=document.getElementsByName("sPressure");
                    for(var i=0;i<sPress.length;i++){
                       document.getElementsByName('sPressure')[i].checked = false; //radio button disabled with name
                    }
                   document.getElementsByName("IDGP").checked = false;
                   document.getElementById("ITNO_btn").disabled = false;
                   document.getElementById('STVALval').value='';
                   document.getElementById('Textarea').value='';
                    /*禁打*/
                    var re_NO_MMAL=['1','2','3','4'];
                    $.each(re_NO_MMAL,function (index) {
                        var NO_MMAL=document.getElementById('ISLF0000000'+re_NO_MMAL[index]);
                        if(NO_MMAL){
                            document.getElementById('ISLF0000000'+re_NO_MMAL[index]).disabled=false;
                            document.getElementById('ISLF0000000'+re_NO_MMAL[index]).checked=false;
                        }
                    });
                    $("#ISSG").css({ 'background-color' : '', 'opacity' : '' ,'color':'white'});
                    $("#ISLN").css({ 'background-color' : '', 'opacity' : '' ,'color':'white'});
                    $("#Inhibit").css({ 'background-color' : '', 'opacity' : '' ,'color':'white'});
                    $("#Part").css({ 'background-color' : '', 'opacity' : '','color':'white' });


                }


            }

            var btn=document.getElementById("sbed");
            var x;
            btn.onclick=function () {

                switch (checkBEDwindow()) {
                    case "false":
                        errorModal("責任床位視窗已開啟");
                        return false;
                        break;
                    case "true":
                        x=window.open("/webservice/NISPWSLKCBD.php?str="+AESEnCode("sFm=ILSG&sIdUser=<?php echo $Account?>"),"責任床位(血)",'width=750px,height=650px,scrollbars=yes,resizable=no');
                        break;
                }
                x.bedcallback=bedcallback;
            }

            function checkBEDwindow() {
                if(!x){
                    console.log("not open");
                    return "true";
                }else {
                    if(x.closed){
                        console.log("window close");
                        return "true";
                    }else {
                        console.log("window not close");
                        return "false";
                    }
                }
            }
            var dt = new Date();
            var end='';
            $("#DELMENU").attr("disabled", true);
            $("#ISSG").prop("disabled", true);
            $("#ISLN").prop("disabled", true);
            $("#Inhibit").prop("disabled", true);
            $("#Part").prop("disabled", true);



            $(document).ready(function () {
                $(document).on("keydown", "form", function(event) {
                    return event.key != "Enter";
                });
            $(document).on('change','input[name=sRdoDateTime]',function () {
                var TimeNow=new Date();
                var yyyy=TimeNow.toLocaleDateString().slice(0,4);
                var MM=(TimeNow.getMonth()+1<10?'0':'')+(TimeNow.getMonth()+1);
                var dd=(TimeNow.getDate()<10?'0':'')+TimeNow.getDate();
                var  h=(TimeNow.getHours()<10?'0':'')+TimeNow.getHours();
                var  m=(TimeNow.getMinutes()<10?'0':'')+TimeNow.getMinutes();
             /*   var  s=(TimeNow.getSeconds()<10?'0':'')+TimeNow.getSeconds();*/

                var Timetxt=($(this).val()).split("");
               var timer=Timetxt.filter(function (value, index, array) { return  value!==":"});
                var timerVal=$(this).attr('id')=="ISTM00000005"?h+m:timer.join("");
                $("#IDTM").val($(this).attr('id'));
                $("#timer").val(yyyy-1911+MM+dd);
                $("#timetxt").val(timerVal);

            });




                $(window).on('beforeunload', reloadmsg);
                function  reloadmsg() {
                    return '確認要重新整理嗎?';
                }
                $('#Part').click(function () {
                    $('#Serch').prop('disabled',true);
                });

                $('#STVALval').bind("input propertychange",function(){
                    if($(this).val()){
                        $("input[name='sPressure']").prop('checked',false);
                        $("#sPress").val("");
                    }
                });

                $("input[name='sPressure']").change(function () {
                    $("#sPress").val($(this).val());
                    $("#STVALval").val("");
                });

                var transKey='';     /*交易續號*/
                var sSave='';
                var FORMSEQANCE_WT='';
                var JID_NSRANK='';
                var sDt='';
                var sTm='';
                var  ISSG_jsonStr='';
                var  ISSN_jsonStr1='';
                var  ISSN_jsonStr2='';
                var  ISSN_jsonStr3='';
                /*病人評估初始紀錄ws*/
                var LSTPT='';
                var ST_DATAC='';
                var FORBIDArrary='';
                function ajaxdata(){
                    var ajaxdata_ip='/webservice/NISPWSTRAINI.php';
                    $.ajax({
                        url:ajaxdata_ip+'?str='+AESEnCode('sFm='+'ILSGA'+'&idPt='+$('#DA_idpt').val()+'&INPt='+$('#DA_idinpt').val()+'&sUr=<?php echo $Account?>'),
                        type:"POST",
                        dataType:"text",
                        success:function (data) {
                            var json=JSON.parse(AESDeCode(data));
                            transKey=json.sTraID;
                            sSave=json.sSave;
                            FORMSEQANCE_WT=json.FORMSEQANCE_WT;
                            JID_NSRANK=json.JID_NSRANK;
                            console.log(json);
                            var ST_DATAA=(JSON.parse(json.ST_DATAA))[0];
                            var ST_DATAB=(JSON.parse(json.ST_DATAB))[0];
                            $("#STDATB_idFrm").val(ST_DATAB.idFrm);
                            $("input[name='forbid[]']").prop('checked',false);
                            $("input[name='forbid[]']").prop('disabled',false);
                            FORBIDArrary=ST_DATAB.FORBID;
                            if(FORBIDArrary){
                               ST_DATAC=(JSON.parse(json.ST_DATAC))[0];
                                var  fu_data=JSON.parse(json.ST_PREC);
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
                            $("#fu2").val("");
                            $("#fu1").val("");
                            /*施打頻率UI*/
                            for(var i=0;i<fu_data.length;i++){
                                var str1='';
                                var str2='';
                                var p='';
                                var j='';
                                if(i%2!=0){
                                    p=i;
                                    str1=fu_data[p].FUQUEN;
                                    $("#fu1").append(
                                        "<tr>"+
                                        "<td>"+
                                        "<input type='button' onclick='fuval(this.value)'  style='background-color:#007bff;border: 0;color: white;width: 130px ;font-size: 30px;height: 50px;border-radius: 4px; ' value='"+str1+"'>"+
                                        "</td>"+
                                        "</tr>"
                                    );
                                }else {
                                    j=i;
                                    str2=fu_data[j].FUQUEN;
                                    $("#fu2").append(
                                        "<tr>"+
                                        "<td>"+
                                        "<input type='button' onclick='fuval(this.value)'  style='background-color:#007bff;border: 0;color: white;width: 130px ;font-size: 30px;height: 50px;border-radius: 4px; ' value='"+str2+"'>"+
                                        "</td>"+
                                        "</tr>"
                                    );
                                }
                            }
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
                        $.each(arr,function (index) {
                            $("#NOisuling_RE").append(
                                "<label id='bb"+index+"' style='font-size: 4.5vmin'>"+"<input type='radio' name='NO_MMVAL' id='"+Object.keys(arr[index])+"' value='"+Object.keys(arr[index])+"' style='width: 6vmin;height: 6vmin' >"+Object.values(arr[index])+"</label>"
                            );
                        });
                    }
                }

                /*各頁面控制*/
                $("button[name=click]").click(function(){

                    ISSN_jsonStr1=[{
                        'idFrm':$('#STDATB_idFrm').val(),
                        'SFRMDTSEQ':'',
                        'ITNO':$("input[name='ITNO']:checked").val(),
                        'IDTM':$("#IDTM").val(),
                        'IDGP':$("input[name=part]:checked").val(),
                        'FORBID':'',
                        'ID':$("#tt1").val(),
                        'STM':$("#Isu_A").val(),
                        'DBDOSE':'-1',
                        'SDOSE':$("#dose1").val(),
                        'USEF':$("#fUSEF_1").val(),
                        'LSTPT':$('#LastPart').val()
                    }];
                    ISSN_jsonStr2=[{
                        'idFrm':$('#STDATB_idFrm').val(),
                        'SFRMDTSEQ':'',
                        'ITNO':$("input[name='ITNO']:checked").val(),
                        'IDTM':$("#IDTM").val(),
                        'IDGP':$("input[name=part]:checked").val(),
                        'FORBID':'',
                        'ID':$("#tt1").val(),
                        'STM':$("#Isu_A").val(),
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
                        'ID':$("#tt3").val(),
                        'STM':$("#Isu_B").val(),
                        'DBDOSE':'-1',
                        'SDOSE':$("#dose2").val(),
                        'USEF':$("#fUSEF_2").val(),
                        'LSTPT':$('#LastPart').val()
                    }];
                    ISSN_jsonStr3=[{
                        'idFrm':$('#STDATB_idFrm').val(),
                        'SFRMDTSEQ':'',
                        'ITNO':$("input[name='ITNO']:checked").val(),
                        'IDTM':$("#IDTM").val(),
                        'IDGP':$("input[name=part]:checked").val(),
                        'FORBID':'',
                        'ID':$("#tt1").val(),
                        'STM':$("#Isu_A").val(),
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
                        'ID':$("#tt3").val(),
                        'STM':$("#Isu_B").val(),
                        'DBDOSE':'-1',
                        'SDOSE':$("#dose2").val(),
                        'USEF':$("#fUSEF_2").val(),
                        'LSTPT':$('#LastPart').val()
                    },{
                        'idFrm':$('#STDATB_idFrm').val(),
                        'SFRMDTSEQ':'',
                        'ITNO':$("input[name='ITNO']:checked").val(),
                        'IDTM':$("#IDTM").val(),
                        'IDGP':$("input[name=part]:checked").val(),
                        'FORBID':'',
                        'ID':$("#tt5").val(),
                        'STM':$("#Isu_C").val(),
                        'DBDOSE':'-1',
                        'SDOSE':$("#dose3").val(),
                        'USEF':$("#fUSEF_3").val(),
                        'LSTPT':$('#LastPart').val()
                    }];

                    ISSG_jsonStr=[{
                        'IDTM':$("#IDTM").val(),
                        'IDGP':$("input[name=IDGP]:checked").val(),
                        'STVAL':$("#STVALval").val(),
                        'SPRESS':$("input[name='sPressure']:checked").val(),
                        'MMVAL':$('#Textarea').val().match(/&/)!=null?$('#Textarea').val().replace(/&/g,'＆'):$('#Textarea').val()
                    }];


                    var val=$(this).val();
                    var btnid=this.id;
                    $("#PageVal").val($(this).val());
                    switch (val) {
                        case 'A':
                            $('#Serch').prop('disabled',false);
                            if($("#clickTime").val()=='0'){
                                ajaxdata();
                                LoadInsertPage('DATAA');
                                $("#clickTime").val(1);
                            }
                            /*if val=1 執行快存WSST*/
                            if($("#clickTime").val()=='1'){
                                if($("#Isu_A").val()!='' && $("#Isu_B").val()=='' && $("#Isu_C").val()==''){
                                    UPDATEDATA('B',JSON.stringify(ISSN_jsonStr1));
                                    UPDATEDATA('C',JSON.stringify(Forbidjson()));

                                }else if($("#Isu_A").val()!='' &&　$("#Isu_B").val()!=''&& $("#Isu_C").val()==''){
                                    UPDATEDATA('B',JSON.stringify(ISSN_jsonStr2));
                                    UPDATEDATA('C',JSON.stringify(Forbidjson()));

                                }else if($("#Isu_A").val()!='' &&　$("#Isu_B").val()!=''　&&　$("#Isu_C").val()!=''){
                                    UPDATEDATA('B',JSON.stringify(ISSN_jsonStr3));
                                    UPDATEDATA('C',JSON.stringify(Forbidjson()));

                                }

                            }

                            $("#BSData").show();
                            $("#isuling").hide();
                            $("#Imgisuling").hide();
                            $("#NO_isuling").hide();
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
                                ajaxdata();
                                $('#clickTime').val(1);

                            }
                            /*if val=1 執行快存WSST*/
                            if($("#clickTime").val()=='1'){
                                UPDATEDATA('A',JSON.stringify(ISSG_jsonStr));
                                UPDATEDATA('C',JSON.stringify(Forbidjson()));

                            }

                            $("#isuling").show();
                            $("#BSData").hide();
                            $("#Imgisuling").hide();
                            $("#NO_isuling").hide();

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
                                ajaxdata();
                                    $('#clickTime').val(1);
                            }
                            /*if val=1 執行快存WSST*/
                            if($("#clickTime").val()=='1'){
                                if($("#Isu_A").val()!='' && $("#Isu_B").val()=='' && $("#Isu_C").val()==''){
                                    UPDATEDATA('B',JSON.stringify(ISSN_jsonStr1));
                                }else if($("#Isu_A").val()!='' &&　$("#Isu_B").val()!=''&& $("#Isu_C").val()==''){
                                    UPDATEDATA('B',JSON.stringify(ISSN_jsonStr2));
                                }else if($("#Isu_A").val()!='' &&　$("#Isu_B").val()!=''　&&　$("#Isu_C").val()!=''){
                                    UPDATEDATA('B',JSON.stringify(ISSN_jsonStr3));
                                }
                                UPDATEDATA('A',JSON.stringify(ISSG_jsonStr));
                            }
                            $("#NO_isuling").show();
                            $("#isuling").hide();
                            $("#BSData").hide();
                            $("#Imgisuling").hide();

                            $("#ISSG").css({ 'background-color' : '', 'opacity' : '' ,'color':'white'});
                            $("#ISLN").css({ 'background-color' : '', 'opacity' : '' ,'color':'white'});
                            $("#Part").css({ 'background-color' : '', 'opacity' : '' ,'color':'white'});
                            break;
                        case 'D':
                            if($("#clickTime").val()=='0'){
                                ajaxdata();
                                $('#clickTime').val(1);
                            }
                            NISPWSCILREG();
                            $("#Imgisuling").show();
                            $("#isuling").hide();
                            $("#BSData").hide();
                            $("#NO_isuling").hide();
                            $('#DELMENU').prop('disabled',true);

                            $("#ISSG").css({ 'background-color' : '', 'opacity' : '' ,'color':'white'});
                            $("#ISLN").css({ 'background-color' : '', 'opacity' : '' ,'color':'white'});
                            $("#Inhibit").css({ 'background-color' : '', 'opacity' : '' ,'color':'white'});
                            break;
                        default:
                    }
                    $("#"+btnid).css({ 'background-color' : '#EEEE00', 'opacity' : '' ,'color':'black'});
                    $("#"+btnid).css({ 'background-color' : '#EEEE00', 'opacity' : '' ,'color':'black'});

                    $('button[name=ISLNch]').click(function () {
                        /*call ws for胰島素藥品*/
                        var val=$(this).val();
                        switch (val) {
                            case '1':
                                if($("#ckt").val()==0){
                                    LoadInsertPage('PREB',);
                                }
                                $('#ckt').val(1);
                                break;
                            case '2':
                                if($("#ckt").val()==0){
                                    LoadInsertPage('PREB');
                                }
                                $('#ckt').val(2);
                                break;
                            case '3':
                                if($("#ckt").val()==0){
                                    LoadInsertPage('PREB');
                                }
                                $('#ckt').val(3);
                                break;
                        }
                    });
                });
                function Forbidjson(){
                    var checkboxval=$("input[name='forbid[]']:checked").map(function() { return $(this).val(); }).get();
                    for(var i=0;i<FORBIDArrary.length;i++){
                        /*移除禁打預設值*/
                        delete checkboxval[i];
                    }
                    checkboxval=checkboxval.filter(function (e) {
                        /*去除陣列移除後的空值*/
                        return e;
                    });
                    var newobj=new Object();
                    newobj.REGION=checkboxval;
                    newobj.NO_MMVAL= $("input[name=NO_MMVAL]:checked").val();
                    var data=[];
                    data.push(newobj);
                    return data;
                }
                /*存檔ws*/
                $('#form1').submit(function () {
                    $(window).off('beforeunload', reloadmsg);
                    var timeRadioButton=$("input[name=sRdoDateTime]:checked").val();
                    var Dateinput=$("#timer").val();
                    var TIMER=$("#timetxt").val();
                    var CID_MEAL=$("input[name=IDGP]:checked").val();
                    var trsKey=$('#transKEY').val();
                    var submitAjax_ip='/webservice/NISPWSSAVEILSG.php';
                    sDt=($('#timer').val()).toString();
                    sTm=$('#timetxt').val()+"00";

                    ISSN_jsonStr1=[{
                        'idFrm':$('#STDATB_idFrm').val(),
                        'SFRMDTSEQ':'',
                        'ITNO':$("input[name='ITNO']:checked").val(),
                        'IDTM':$("#IDTM").val(),
                        'IDGP':$("input[name=part]:checked").val(),
                        'FORBID':'',
                        'ID':$("#tt1").val(),
                        'STM':$("#Isu_A").val(),
                        'DBDOSE':'-1',
                        'SDOSE':$("#dose1").val(),
                        'USEF':$("#fUSEF_1").val(),
                        'LSTPT':$('#LastPart').val()
                    }];
                    ISSN_jsonStr2=[{
                        'idFrm':$('#STDATB_idFrm').val(),
                        'SFRMDTSEQ':'',
                        'ITNO':$("input[name='ITNO']:checked").val(),
                        'IDTM':$("#IDTM").val(),
                        'IDGP':$("input[name=part]:checked").val(),
                        'FORBID':'',
                        'ID':$("#tt1").val(),
                        'STM':$("#Isu_A").val(),
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
                        'ID':$("#tt3").val(),
                        'STM':$("#Isu_B").val(),
                        'DBDOSE':'-1',
                        'SDOSE':$("#dose2").val(),
                        'USEF':$("#fUSEF_2").val(),
                        'LSTPT':$('#LastPart').val()
                    }];
                    ISSN_jsonStr3=[{
                        'idFrm':$('#STDATB_idFrm').val(),
                        'SFRMDTSEQ':'',
                        'ITNO':$("input[name='ITNO']:checked").val(),
                        'IDTM':$("#IDTM").val(),
                        'IDGP':$("input[name=part]:checked").val(),
                        'FORBID':'',
                        'ID':$("#tt1").val(),
                        'STM':$("#Isu_A").val(),
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
                        'ID':$("#tt3").val(),
                        'STM':$("#Isu_B").val(),
                        'DBDOSE':'-1',
                        'SDOSE':$("#dose2").val(),
                        'USEF':$("#fUSEF_2").val(),
                        'LSTPT':$('#LastPart').val()
                    },{
                        'idFrm':$('#STDATB_idFrm').val(),
                        'SFRMDTSEQ':'',
                        'ITNO':$("input[name='ITNO']:checked").val(),
                        'IDTM':$("#IDTM").val(),
                        'IDGP':$("input[name=part]:checked").val(),
                        'FORBID':'',
                        'ID':$("#tt5").val(),
                        'STM':$("#Isu_C").val(),
                        'DBDOSE':'-1',
                        'SDOSE':$("#dose3").val(),
                        'USEF':$("#fUSEF_3").val(),
                        'LSTPT':$('#LastPart').val()
                    }];
                    ISSG_jsonStr = [{
                        'IDTM': $("#IDTM").val(),
                        'IDGP': $("input[name=IDGP]:checked").val(),
                        'STVAL': $("#STVALval").val(),
                        'SPRESS': $("input[name='sPressure']:checked").val(),
                        'MMVAL': $('#Textarea').val().match(/&/)!=null?$('#Textarea').val().replace(/&/g,'＆'):$('#Textarea').val()
                    }];
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
                    if($('#PageVal').val()=='A'){
                        if(timeRadioButton != '臨時'){
                            if(CID_MEAL =='' || CID_MEAL == null || CID_MEAL =='undefined'){
                                errorModal("飯前飯後未選擇");
                                return false;
                            }
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

                    }
                    if($('#PageVal').val()=='B'){
                        if($("input[name=part]:checked").val()=='' || $("input[name=part]:checked").val()==null || $("input[name=part]:checked").val()=='undefined'){
                            errorModal("尚未選擇施打部位");
                            return false;
                        }
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
                        if($("#Isu_A").val()!='' && $("#dose1").val()=='') {

                            errorModal("第一筆施打劑量不得為空");
                            $("#ERRORVAL").val(4);
                            focustext(4);
                            return false;
                        }
                        if($("#Isu_A").val()!='' && $("#fUSEF_1").val()==''){

                            errorModal("第一筆頻率不得為空");
                            $("#ERRORVAL").val(5);
                            focustext(5);
                            return false;
                        }
                        if($("#Isu_A").val()!='' && $("#Isu_B").val()!='' && $("#dose2").val()=='') {

                            errorModal("第二筆施打劑量不得為空");
                            $("#ERRORVAL").val(6);
                            focustext(6);
                            return false;
                        }
                        if($("#Isu_A").val()!='' && $("#Isu_B").val()!='' && $("#fUSEF_2").val()=='') {

                            errorModal("第二筆頻率不得為空");
                            $("#ERRORVAL").val(7);
                            focustext(7);
                            return false;
                        }
                        if($("#Isu_A").val()!='' && $("#Isu_B").val()!='' && $("#Isu_C").val()!='' && $("#dose3").val()=='') {
                            errorModal("第三筆施打劑量不得為空");
                            $("#ERRORVAL").val(8);
                            focustext(8);
                            return false;
                        }
                        if($("#Isu_A").val()!='' && $("#Isu_B").val()!='' && $("#Isu_C").val()!='' && $("#fUSEF_3").val()=='') {
                            errorModal("第三筆頻率不得為空");
                            $("#ERRORVAL").val(9);
                            focustext(9);
                            return false;
                        }
                        if ($("#dose1").val() != '') {
                            if (ValidateNumber($("#dose1").val()) == 'error') {
                                errorModal("第一筆劑量請輸入數字");
                                $("#ERRORVAL").val(4);
                                focustext(4);
                                return false;
                            }
                        }
                        if ($("#dose2").val() != '') {
                            if (ValidateNumber($("#dose2").val()) == 'error') {
                                errorModal("第二筆劑量請輸入數字");
                                $("#ERRORVAL").val(6);
                                focustext(6);
                                return false;
                            }
                        }
                        if ($("#dose3").val() != '') {
                            if (ValidateNumber($("#dose3").val()) == 'error') {
                                errorModal("第三筆劑量請輸入數字");
                                $("#ERRORVAL").val(8);
                                focustext(8);
                                return false;
                            }
                        }

                    }

                    var json='';
                    var spg='';
                    if($("#STVALval").val()!='' || $("#sPress").val()!=''){
                        json=ISSG_jsonStr;
                        spg='A';
                        if($("#Isu_A").val()!='' && $("#Isu_B").val()=='' && $("#Isu_C").val()==''){
                            json=ISSN_jsonStr1;
                            spg='B';
                        }
                        if($("#Isu_A").val()!='' &&　$("#Isu_B").val()!=''&& $("#Isu_C").val()==''){
                            json=ISSN_jsonStr2;
                            spg='B';
                        }
                        if($("#Isu_A").val()!='' &&　$("#Isu_B").val()!=''　&&　$("#Isu_C").val()!=''){
                            json=ISSN_jsonStr3;
                            spg='B';
                        }
                    }else if($("#Isu_A").val()!='' && $("#Isu_B").val()=='' && $("#Isu_C").val()==''){
                        json=ISSN_jsonStr1;
                        spg='B';
                    } else  if($("#Isu_A").val()!='' &&　$("#Isu_B").val()!=''&& $("#Isu_C").val()==''){
                        json=ISSN_jsonStr2;
                        spg='B';
                    }else  if($("#Isu_A").val()!='' &&　$("#Isu_B").val()!=''　&&　$("#Isu_C").val()!=''){
                        json=ISSN_jsonStr3;
                        spg='B';
                    }
                    if($("#PageVal").val()=='C'){
                        json=Forbidjson();
                        spg='C';

                    }
                    console.log("http://localhost"+submitAjax_ip+'?str='+AESEnCode('sFm='+'ILSGA'+'&sTraID='+trsKey+'&sPg='+$("#PageVal").val()+'&sDt='+sDt+'&sTm='+sTm+'&PASSWD=<?php echo $passwd?>'+'&USER=<?php echo $sUr?>'));
                    console.log(json);
                    $("#loading").show();
                    $("#wrapper").show();
                    $.ajax({
                        url:submitAjax_ip+'?str='+AESEnCode('sFm='+'ILSGA'+'&sTraID='+trsKey+'&sPg='+$("#PageVal").val()+'&sDt='+sDt+'&sTm='+sTm+'&PASSWD=<?php echo $passwd?>'+'&USER=<?php echo $sUr?>'),
                        type:'POST',
                        beforeSend: UPDATEDATA(spg, JSON.stringify(json)),
                        dataType:'text',
                        success:function (json) {
                            try {
                               var data= JSON.parse(AESDeCode(json));
                               console.log(data);
                               $("#loading").hide();
                               $("#wrapper").hide();
                               if(data.response=='success'){
                                   alert("儲存成功");
                                   window.location.reload(true);
                               }else {
                                   errorModal("儲存失敗重新檢查格式:"+data.message);
                               }
                           }catch (e) {
                               console.log(e);
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
                });

                /*部位序號(圖)*/
                function NISPWSCILREG() {
                    var part_json=[
                        {"FORBID":[
                                {"region":"A"},{"region":"B"},{"region":"C"},{"region":"D"},{"region":"E"},
                                {"region":"F"},{"region":"G"},{"region":"H"}
                            ]
                        }
                    ];
                    var td_json={
                        "Region": [
                            {
                                "A":
                                    [{
                                        "TD":"A1"
                                    },{
                                        "TD":"A2"
                                    },{
                                        "TD":"A3"
                                    },{
                                        "TD":"A4"
                                    },{
                                        "TD":"A5"
                                    },{
                                        "TD":"A6"
                                    },{
                                        "TD":"A7"
                                    },{
                                        "TD":"A8"
                                    }]
                            },
                            {
                                "B":
                                    [{
                                        "TD":"B1"
                                    },{
                                        "TD":"B2"
                                    },{
                                        "TD":"B3"
                                    },{
                                        "TD":"B4"
                                    },{
                                        "TD":"B5"
                                    },{
                                        "TD":"B6"
                                    },{
                                        "TD":"B7"
                                    },{
                                        "TD":"B8"
                                    }]
                            },
                            {
                                "C":
                                    [{
                                        "TD":"C1"
                                    },{
                                        "TD":"C2"
                                    },{
                                        "TD":"C3"
                                    },{
                                        "TD":"C4"
                                    },{
                                        "TD":"C5"
                                    },{
                                        "TD":"C6"
                                    },{
                                        "TD":"C7"
                                    },{
                                        "TD":"C8"
                                    }]
                            },{
                                "D":
                                    [{
                                        "TD":"D1"
                                    },{
                                        "TD":"D2"
                                    },{
                                        "TD":"D3"
                                    },{
                                        "TD":"D4"
                                    },{
                                        "TD":"D5"
                                    },{
                                        "TD":"D6"
                                    },{
                                        "TD":"D7"
                                    },{
                                        "TD":"D8"
                                    }]
                            },{
                                "E":
                                    [{
                                        "TD":"E1"
                                    },{
                                        "TD":"E2"
                                    },{
                                        "TD":"E3"
                                    },{
                                        "TD":"E4"
                                    },{
                                        "TD":"E5"
                                    },{
                                        "TD":"E6"
                                    },{
                                        "TD":"E7"
                                    },{
                                        "TD":"E8"
                                    }]
                            },{
                                "F":
                                    [{
                                        "TD":"F1"
                                    },{
                                        "TD":"F2"
                                    },{
                                        "TD":"F3"
                                    },{
                                        "TD":"F4"
                                    },{
                                        "TD":"F5"
                                    },{
                                        "TD":"F6"
                                    },{
                                        "TD":"F7"
                                    },{
                                        "TD":"F8"
                                    }]
                            },{
                                "G":
                                    [{
                                        "TD":"G1"
                                    },{
                                        "TD":"G2"
                                    },{
                                        "TD":"G3"
                                    },{
                                        "TD":"G4"
                                    },{
                                        "TD":"G5"
                                    },{
                                        "TD":"G6"
                                    },{
                                        "TD":"G7"
                                    },{
                                        "TD":"G8"
                                    }]
                            },{
                                "H":
                                    [{
                                        "TD":"H1"
                                    },{
                                        "TD":"H2"
                                    },{
                                        "TD":"H3"
                                    },{
                                        "TD":"H4"
                                    },{
                                        "TD":"H5"
                                    },{
                                        "TD":"H6"
                                    },{
                                        "TD":"H7"
                                    },{
                                        "TD":"H8"
                                    }]
                            }
                        ]
                    };

                    var td_obj=JSON.parse(JSON.stringify(td_json));
                    var objpart=JSON.parse(JSON.stringify(part_json));
                    $.each(objpart, function (index) {
                        for(var i=0;i<8;i++){
                            var region=objpart[index].FORBID[i].region;
                            document.getElementById(region).style.backgroundColor='white';
                            document.getElementById(region).style.color='black';
                        }
                    });
                    $('.ImgTable').css({"background-color":"","color":""});

                    $.each(FORBIDArrary,function (index) {
                        document.getElementById(FORBIDArrary[index].REGION).style.backgroundColor='red';
                        document.getElementById(FORBIDArrary[index].REGION).style.color='white';
                    });

                    if(LSTPT){
                        document.getElementById(LSTPT).style.backgroundColor='blue';
                        document.getElementById(LSTPT).style.color='white';
                    }

                    $.ajax({
                        url:"/webservice/NISPWSCILREG.php?str="+AESEnCode("sFm=ILSGA&sTraID="+$("#transKEY").val()+"&sRgn="+$("input[name=part]:checked").val()),
                        type:'POST',
                        dataType:'text',
                        success:function (json) {
                            var data=JSON.parse(AESDeCode(json));
                            var IDGP_num=$("input[name=part]:checked").val();
                            document.getElementById(IDGP_num+data).style.backgroundColor="green";
                            document.getElementById(IDGP_num+data).style.color="white";
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
                    var LoadInsertPage_ip="/webservice/NISPWSGETPRE.php";
                    $.ajax({
                        url:LoadInsertPage_ip+"?str="+AESEnCode("sFm=ILSGA&sTraID="+transKey+"&sPg="+num),
                        type:'POST',
                        dataType:"text",
                        success:function (json){
                            if(json){
                                var data=AESDeCode(json);
                                console.log(data);
                                if(num=='DATAA'){
                                    console.log(data);
                                }
                               if(num=='PREB'){
                                   var ISULING_OBJ=JSON.parse((JSON.parse(data))[0].ISULING);
                                    var ID='';
                                    var STM='';
                                    var JID_KEY='';
                                    var DCSORT='';
                                   var QTY='';
                                   var USEF='';
                                    for(var i=0;i<ISULING_OBJ.length;i++){
                                        JID_KEY = ISULING_OBJ[i].JID_KEY;
                                        ID = ISULING_OBJ[i].DIA;
                                        STM =(ISULING_OBJ[i].STM).replace('§0§','');
                                        DCSORT = ISULING_OBJ[i].DCSORT;
                                        QTY = ISULING_OBJ[i].QTY;
                                        USEF = ISULING_OBJ[i].USENO;
                                        var QTY_tt=QTY!=''?"劑量:":"";
                                        var USEF_tt=USEF!=''?"頻率:":"";
                                        $("#MedLi").append("<li id='MEDli"+i+"' " +
                                            "style='list-style-type: none;font-size:3vmin '>"+
                                            "<input type='button' value='選擇' id='Medbtn"+i+"'" +
                                            " onclick='MEDbtnID("+i+")' " +
                                            "style='width: 60px;margin-left: -25px;margin-right: 5px;font-size: 2.7vmin;'  class=\'btn btn-primary\'\"close\"\  data-dismiss=\"modal\" aria-hidden=\"true\" >"+STM+
                                            "<li style='padding-left: 42px;font-size:2.5vmin '>"+QTY_tt+QTY+USEF_tt+USEF+"</li>"+
                                            "<input type='text' value='"+ID+"' style='display: none' id='sID"+i+"'>"+
                                            "<input type='text' value='"+QTY+"' style='display: none' id='QTY"+i+"'>"+
                                            "<input type='text' value='"+USEF+"' style='display: none' id='sUSEF"+i+"'>"+
                                            "</li>");
                                    }

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
                /*切換更新資料ws*/
                function UPDATEDATA(spg,Json) {
                    var  trsKey=$('#transKEY').val();
                    var UPDATEDATA_ip="/webservice/NISPWSSETDATA.php";
                    console.log(UPDATEDATA_ip+'?str='+AESEnCode('sFm='+'ILSGA'+'&sTraID='+trsKey+'&sPg='+spg+'&sData='+Json));
                    $.ajax({
                        url:UPDATEDATA_ip+'?str='+AESEnCode('sFm='+'ILSGA'+'&sTraID='+trsKey+'&sPg='+spg+'&sData='+Json),
                        type:'POST',
                        dataType:"text",
                        success:function (data) {
                            var json=JSON.parse(AESDeCode(data));
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
                /*查詢ws*/

                var Serch_btn=document.getElementById('Serch');
                function Serchcallback(AESdata){
                   var page=$("#PageVal").val();
                    var str=AESDeCode(AESdata);
                    var datastr=JSON.parse(JSON.stringify(str).replace(/\u0000/g, '').replace(/\\u0000/g, ""));
                    var data=JSON.parse(datastr);
                    console.log(AESdata);
                    console.log(data);
                 if(data){
                        document.getElementById("DELMENU").disabled= false;
                        document.getElementById("Del").disabled= false;
                        document.getElementById("timer").readonly= true;
                        document.getElementById("timetxt").readonly= true;
                        document.getElementById('STVALval').value='';
                        document.getElementById('sPress').value='';
                        $("input[name=sRdoDateTime]").prop("disabled",true);

                        var STVALval=document.getElementById('STVALval');
                        STVALval.onpropertychange=function(){
                            var newReg=new RegExp(/^[0-9]*$/);
                            var regExp = new RegExp(/^[a-zA-Z]+$/);
                            if( STVALval.value.length>0 && STVALval.value.match(newReg)){
                                document.getElementById('P_LO').checked= false;
                                document.getElementById('P_HI').checked= false;
                                document.getElementById('P_NONE').checked= false;
                                document.getElementById('P_CE').checked= false;
                            }
                            else if( STVALval.value.length>0  && STVALval.value.match(regExp)){
                                switch (STVALval.value)
                                {
                                    case 'LO':
                                        document.getElementById('P_LO').checked= true;
                                        document.getElementById('STVALval').value='';
                                        break;
                                    case 'HI':
                                        document.getElementById('P_HI').checked= true;
                                        document.getElementById('STVALval').value='';
                                        break;
                                    case 'NONE':
                                        document.getElementById('P_NONE').checked= true;
                                        document.getElementById('STVALval').value='';
                                        break;
                                    case 'CE':
                                        document.getElementById('P_CE').checked= true;
                                        document.getElementById('STVALval').value='';
                                        break;
                                }
                            }
                        };

                    switch (page) {
                            case 'A':
                                document.getElementById('BSData').style.display = "block";
                                document.getElementById('ISLN').disabled= true;
                                document.getElementById('Inhibit').disabled= true;

                                var idPt=data.idPt;
                                var DT_EXCUTE=data.DT_EXCUTE;
                                var TM_EXCUTE=data.TM_EXCUTE;
                                var JID_TIME=data.JID_TIME;
                                var MM_TPRS=data.MM_TPRS;
                                var SPRESS=data.SPRESS;
                                var ST_MEASURE=data.STVALval;
                                var CID_MEAL=data.CID_MEAL;
                                var transKEY=data.transKEY;
                                var FORMSEQANCE=data.FORMSEQANCE;
                                var SER_DT=data.SER_DT;
                                var SER_TM=data.SER_TM;

                                if(idPt!=$("#DA_idpt").val()){
                                    errorModal("病人資訊已異動,請先重新操作一次");
                                    return false;
                                }

                                document.getElementById('FORMSEQANCE').value=FORMSEQANCE;
                                document.getElementById('SER_DT').value=SER_DT;
                                document.getElementById('SER_TM').value=SER_TM;
                                timeback(JID_TIME);
                                document.getElementById("timer").value=DT_EXCUTE.substr(0,3)+DT_EXCUTE.substr(3,2)+DT_EXCUTE.substr(5,2);
                                document.getElementById("timetxt").value=TM_EXCUTE.substr(0,2)+TM_EXCUTE.substr(2,2);
                                document.getElementById("Textarea").value=MM_TPRS.match(/＆/)!=null?MM_TPRS.replace(/＆/g,'&'):MM_TPRS;
                                document.getElementById("transKEY").value=transKEY;
                                var regExp = new RegExp(/^[a-zA-Z]+$/);
                                if(SPRESS.match(regExp)){
                                    document.getElementById("sPress").value=SPRESS;
                                    switch (SPRESS) {
                                        case 'LO':
                                            document.getElementById("P_LO").checked= true;
                                            break;
                                        case 'HI':
                                            document.getElementById("P_HI").checked= true;
                                            break;
                                        case 'NONE':
                                            document.getElementById("P_NONE").checked= true;
                                            break;
                                        case 'CE':
                                            document.getElementById("P_CE").checked= true;
                                            break;
                                    }
                                }
                                if(ST_MEASURE.trim()){
                                    document.getElementById("STVALval").value=ST_MEASURE;
                                }

                                if (CID_MEAL=='A'){
                                    document.getElementById('Eating1').checked=true;

                                }else if(CID_MEAL=='B')
                                {
                                    document.getElementById('Eating2').checked=true;
                                }
                                else{
                                    document.getElementById('Eating1').checked=false;
                                    document.getElementById('Eating2').checked=false;
                                }
                                break;
                            case 'B':
                               document.getElementById('isuling').style.display = "block";
                               document.getElementById('ISSG').disabled=true;
                               document.getElementById('Inhibit').disabled=true;
                                $("#SERCH_Click").val("2");

                                var json=data;
                                // var json=data.ISLN;
                                console.log(json);
                                var DT='';
                                var TM='';
                                console.log(data);
                                $.each(json,function (index) {
                                    DT=json[index].DT_EXCUTE;
                                    TM=json[index].TM_EXCUTE;
                                    var idPt=json[index].idPt;
                                    var JID_TIME=json[index].JID_TIME;
                                    var ID_REGION=json[index].ID_REGION; //部位A1
                                    var ID_ORDER=json[index].ID_ORDER;   //藥名id
                                    var NM_ORDER=json[index].NM_ORDER;
                                    var DB_DOSE=json[index].DB_DOSE; //-1
                                    var ST_DOSE=json[index].ST_DOSE; //劑量
                                    var ST_USENO=json[index].ST_USENO; //頻率
                                    var LSTPT=json[index].LSTPT; //頻率
                                    $("#DT_EXE").val(DT);
                                    $("#TM_EXE").val(TM);
                                   document.getElementById('LastPart').value=LSTPT;
                                   document.getElementById('transKEY').value=json[index].sTraID;
                                    $("#ISLNLi").append('<li style="display:none;">'+
                                        '<input type="text"  name="DT_E" id=DT_E'+index+' value='+DT+'>'+
                                        '<input type="text"  name="TM_E" id=TM_E'+index+' value='+TM+'>'+
                                        '<input type="text"  name="JID_T" id=JID_T'+index+' value='+JID_TIME+'>'+
                                        '<input type="text"  name="ID_R" id=ID_R'+index+' value='+ID_REGION+'>'+
                                        '<input type="text"  name="ID_O" id=ID_O'+index+' value='+ID_ORDER+'>'+
                                        '<input type="text"  name="NM_O" id=NM_O'+index+' value='+NM_ORDER+'>'+
                                        '<input type="text"  name="ST_D" id=ST_D'+index+' value='+ST_DOSE+'>'+
                                        '<input type="text"  name="ST_U" id=ST_U'+index+' value='+ST_USENO+'>'+
                                        '<input type="text"  name="LSTP" id=LSTP'+index+' value='+LSTPT+'>'+
                                        '</li>');

                                    ISLN_Dateback(idPt,index);
                                });
                                break;
                            case 'C':
                                $("#SERCH_Click").val("2");
                                $("#Part").prop('disabled',true);

                                var json=data.Forbid;
                                var idPt=json.idPt;
                                var DT=json.DT_EXCUTE;
                                var TM=json.TM_EXCUTE;
                                var forbid_REGION=json.REGION;
                                var NO_MMAL=json.NO_MMAL;
                                var sTraID=json.sTraID;
                                var FORMSEQANCE=data.FORMSEQANCE;
                                var SER_DT=data.SER_DT;
                                var SER_TM=data.SER_TM;

                                $('#FORMSEQANCE').val(FORMSEQANCE);
                                $('#SER_DT').val(SER_DT);
                                $('#SER_TM').val(SER_TM);
                                $("input[name='forbid[]']").prop('checked',false);
                                $.each(forbid_REGION,function (index) {
                                    console.log(forbid_REGION[index]);
                                    document.getElementById('No_'+forbid_REGION[index]).checked=true;
                                });

                                Forbid_Dateback(idPt,DT,TM,NO_MMAL,sTraID);

                            break;
                        }
                    }

                }
                var y;
               Serch_btn.onclick=function ()
                {
                    console.log("http://localhost"+"/webservice/NISPWSLKQRY.php?str="+
                        "sFm=ILSGA&PageVal="+$("#PageVal").val()+"&DA_idpt="+
                        $('#DA_idpt').val()+"&DA_idinpt="+$('#DA_idinpt').val()+
                        "&sUser="+$('#sUser').val()+"&NM_PATIENT="+$('#DataTxt').val()
                    );



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
                }

                function checkSerchwindow() {
                    if(!y){
                        console.log("not open");
                        return "true";
                    }else {
                        if(y.closed){
                            console.log("window close");
                            return "true";
                        }else {
                            console.log("window not close");
                            return "false";
                        }
                    }
                }

                function ISLN_Dateback(idPt,NUM) {
                    /*DT_EXCUTE+TM_EXCUTE+JID_TIME+ID_REGION+ID_ORDER+NM_ORDER+DT_TAKEDRUG+TM_TAKEDRUG+ST_DOSE+ST_USENO*/
                    if(idPt!=$("#DA_idpt").val()){
                        errorModal("病人資訊已異動,請先重新操作一次");
                        return false;
                    }
                    var DT_EXCUTE=document.getElementById("DT_E"+ NUM).value;
                    var TM_EXCUTE=document.getElementById("TM_E"+ NUM).value;
                    var JID_TIME = document.getElementById("JID_T"+ NUM).value;
                    var ID_REGION= document.getElementById("ID_R0").value;


                   document.getElementById("Isu_A").value=document.getElementById("NM_O"+"0").value;
                   document.getElementById("dose1").value=document.getElementById("ST_D"+ '0').value;
                   document.getElementById("tt1").value=document.getElementById("ID_O0").value;
                   document.getElementById("fUSEF_1").value=document.getElementById("ST_U"+ '0').value;

                    if(document.getElementById("NM_O"+"1")){
                       document.getElementById("Isu_B").value=document.getElementById("NM_O"+"1").value;
                       document.getElementById("dose2").value=document.getElementById("ST_D"+ '1').value;
                       document.getElementById("tt3").value=document.getElementById("ID_O"+"1").value;
                       document.getElementById("fUSEF_2").value=document.getElementById("ST_U"+ '1').value;

                    }
                    if(document.getElementById("NM_O"+"2")){
                       document.getElementById("Isu_C").value=document.getElementById("NM_O"+"2").value;
                       document.getElementById("dose3").value=document.getElementById("ST_D"+ '2').value;
                       document.getElementById("tt5").value=document.getElementById("ID_O"+"2").value;
                       document.getElementById("fUSEF_3").value=document.getElementById("ST_U"+ '2').value;
                    }
                    var spart= document.getElementsByName('part');
                    for(var i=0;i<spart.length;i++){
                        document.getElementsByName('part')[i].disabled=true;
                    }
                    var part =ID_REGION.substr(0,1);
                    switch (part) {
                        case 'A':
                            document.getElementById("PartA").checked= true;
                            break;
                        case 'B':
                            document.getElementById("PartB").checked= true;
                            break;
                        case 'C':
                           document.getElementById("PartC").checked= true;
                            break;
                        case 'D':
                            document.getElementById("PartD").checked= true;
                            break;
                        case 'E':
                            document.getElementById("PartE").checked= true;
                            break;
                        case 'F':
                            document.getElementById("PartF").checked= true;
                            break;
                        case 'G':
                            document.getElementById("PartG").checked= true;
                            break;
                        case 'H':
                            document.getElementById("PartH").checked= true;
                            break;
                    }
                    timeback(JID_TIME);
                    var y=DT_EXCUTE.substr(0,3);
                    var m=DT_EXCUTE.substr(3,2);
                    var d=DT_EXCUTE.substr(5,2);
                    var H=TM_EXCUTE.substr(0,2);
                    var M=TM_EXCUTE.substr(2,2);

                   document.getElementById("timer").value=y+m+d;
                   document.getElementById("timetxt").value=H+M;
                }
                function Forbid_Dateback(idPt,DT,TM,NO_MMAL,sTraID) {
                    if(idPt!=$("#DA_idpt").val()){
                        errorModal("病人資訊已異動,請先重新操作一次");
                        return false;
                    }
                    document.getElementById('NO_isuling').style.display = "block";
                    document.getElementById('ISSG').disabled=true;
                    document.getElementById('ISLN').disabled=true;
                    document.getElementById('Part').disabled=true;
                    document.getElementById('SubmitBtn').disabled=true;
                    document.getElementById('DT_EXE').value=DT;
                    document.getElementById('TM_EXE').value=TM;
                    document.getElementById("timer").value=DT;
                    document.getElementById("timetxt").value=TM;
                    document.getElementById('transKEY').value=sTraID;



                    document.getElementById(NO_MMAL).checked=true;
                    var re_NO_MMAL=['1','2','3','4'];
                    $.each(re_NO_MMAL,function (index) {
                       document.getElementById('ISLF0000000'+re_NO_MMAL[index]).disabled=true;
                    });
                    var region=['A','B','C','D','E','F','G','H'];
                    $.each(region,function (index) {
                        document.getElementById('No_'+region[index]).disabled=true;
                    });

                }




                $("#Del").click(function() {
                    delte(1);
                });
                /*作廢ws*/
                function delte(num) {
                    var del_ip='/webservice/NISPWSDELILSG.php';
                    console.log("http://localhost"+del_ip+"?str="+AESEnCode("sFm="+'ILSGA'+"&sTraID="+$('#transKEY').val()+"&sPg="+$("#PageVal").val()+"&sCidFlag=D"+"&sUr="+$("#sUser").val()));
                    $.ajax({
                        url:del_ip+"?str="+AESEnCode("sFm="+'ILSGA'+"&sTraID="+$('#transKEY').val()+"&sPg="+$("#PageVal").val()+"&sCidFlag=D"+"&sUr="+$("#sUser").val()),
                        type:'POST',
                        dataType:'text',
                        success:function (json) {
                            var data=JSON.parse(AESDeCode(json));
                            switch (num) {
                                case 1:
                                    if(data.message=='false'){
                                        errorModal('作廢失敗');
                                        return false;
                                    }else {
                                     $('#DELModal').modal('hide');
                                        document.getElementById('clickTime').value=0;
                                        Reset(2);
                                    }

                                    break;
                                case 2:
                                    if(data.message=='false'){
                                        console.log('修改失敗');
                                    }else {
                                        console.log('修改成功');
                                    }
                                    break;
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

            });

            function BEDbtnID(num) {
                document.getElementById("PageVal").value='A';/*預設頁面*/
                $("#ISSG").prop("disabled", false);
                $("#ISLN").prop("disabled", false);
                $("#Inhibit").prop("disabled", false);
                $("#Part").prop("disabled", true);

                var txt = $("#BEDli" + num).text();
                var idpt = document.getElementById("idpt"+num).value;
                var idinpt = document.getElementById("idINPt"+num).value;
                var SBED=document.getElementById("SBED"+num).value;

                document.getElementById("DataTxt").value = txt;
                document.getElementById("DA_idpt").value = idpt;
                document.getElementById("DA_idinpt").value = idinpt;
                document.getElementById("DA_sBed").value=SBED;
                document.getElementById('clickTime').value=0;
                Reset(2);
            }
            /*藥品選擇貼上的位置*/
            function MEDbtnID(NUM) {
                var txt = $("#MEDli" + NUM).text();
                var IDtxt=document.getElementById("sID"+NUM).value;
                var USERtxt=document.getElementById("sUSEF"+NUM).value;
                var ckt=document.getElementById('ckt').value;
                var QTY=document.getElementById("QTY"+NUM).value;
                switch (ckt) {
                    case '1':
                        document.getElementById("tt1").value = IDtxt;
                        document.getElementById("tt2").value = USERtxt;
                        document.getElementById("Isu_A").value = txt;
                        document.getElementById("fUSEF_1").value = USERtxt;
                        document.getElementById("dose1").value = QTY;
                        setTimeout("$(\"#dose1\").focus();",500);


                        break;
                    case '2':
                        document.getElementById("tt3").value = IDtxt;
                        document.getElementById("tt4").value = USERtxt;
                        document.getElementById("Isu_B").value = txt;
                        document.getElementById("fUSEF_2").value = USERtxt;
                        document.getElementById("dose2").value = QTY;
                        setTimeout("$(\"#dose2\").focus();",500);
                        break;
                    case '3':
                        document.getElementById("tt5").value = IDtxt;
                        document.getElementById("tt6").value = USERtxt;
                        document.getElementById("Isu_C").value = txt;
                        document.getElementById("fUSEF_3").value = USERtxt;
                        document.getElementById("dose3").value = QTY;
                        setTimeout("$(\"#dose3\").focus();",500);
                        break;
                    default:
                }
            }
            function errorModal(str) {
                $('#Errormodal').modal('show');
                document.getElementById('ErrorText').innerText=str;
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
                        $('#dose1').focus();
                        break;
                    case '5':
                        $('#fUSEF_1').focus();
                        break;
                    case '6':
                        $('#dose2').focus();
                        break;
                    case '7':
                        $('#fUSEF_2').focus();
                        break;
                    case '8':
                        $('#dose3').focus();
                        break;
                    case '9':
                        $('#fUSEF_3').focus();
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
                var reg = new RegExp(/^\d+(\.\d{0,2})?$/);
                if (!number.match(reg)) {
                    return 'error';
                }
            }
            function NISPWSFMINI_Timer() {
                console.log("http://localhost/webservice/NISPWSFMINI.php?str="+AESEnCode("sFm=ILSGA&sPg=A"));
                $.ajax({
                    url:"/webservice/NISPWSFMINI.php?str="+AESEnCode("sFm=ILSGA&sPg=A"),
                    type:"POST",
                    dataType:"text",
                    success:function(data){
                        var json=JSON.parse(AESDeCode(data));
                        console.log(json);
                        var ST_PREAAry=json[0];
                        var obj=(JSON.parse(ST_PREAAry.ST_PREA))[0];
                        $("#ISTM").append(
                            "<label style='font-size: 4.5vmin'>"+"<input type='radio' name='sRdoDateTime' id='ISTM00000001' value='"+obj.ISTM00000001+"' style='width: 6vmin;height: 6vmin' >"+obj.ISTM00000001+"</label>"+
                            "<label style='font-size: 4.5vmin'>"+"<input type='radio' name='sRdoDateTime' id='ISTM00000002' value='"+obj.ISTM00000002+"' style='width: 6vmin;height: 6vmin' >"+obj.ISTM00000002+"</label>"+
                            "<label style='font-size: 4.5vmin'>"+"<input type='radio' name='sRdoDateTime' id='ISTM00000003' value='"+obj.ISTM00000003+"' style='width: 6vmin;height: 6vmin' >"+obj.ISTM00000003+"</label>"+
                            "<label style='font-size: 4.5vmin'>"+"<input type='radio' name='sRdoDateTime' id='ISTM00000004' value='"+obj.ISTM00000004+"' style='width: 6vmin;height: 6vmin' >"+obj.ISTM00000004+"</label>"+
                            "<label style='font-size: 4.5vmin'>"+"<input type='radio' name='sRdoDateTime' id='ISTM00000005' value='"+obj.ISTM00000005+"' style='width: 6vmin;height: 6vmin' >"+obj.ISTM00000005+"</label>"
                        );
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
            NISPWSFMINI_Timer();
            function AESEnCode(text){
                var key = CryptoJS.enc.Latin1.parse('1234567890654321'); //為了避免補位，直接用16位的金鑰
                var iv = CryptoJS.enc.Latin1.parse('1234567890123456'); //16位初始向量
                var encrypted = CryptoJS.AES.encrypt(JSON.stringify(text), key, {
                    iv: iv,
                    mode:CryptoJS.mode.CBC,
                    padding:CryptoJS.pad.ZeroPadding
                });
                return encrypted.toString();
            }
            function AESDeCode(text){
                var encrypted=text.toString();//先轉utf8字串
                var key = CryptoJS.enc.Latin1.parse('1234567890654321'); //為了避免補位，直接用16位的金鑰
                var iv = CryptoJS.enc.Latin1.parse('1234567890123456'); //16位初始向量
                var decrypted = CryptoJS.AES.decrypt(encrypted,key,{
                    iv: iv,
                    mode: CryptoJS.mode.CBC,
                    padding:CryptoJS.pad.Pkcs7
                });
                decrypted=CryptoJS.enc.Utf8.stringify(decrypted);
                return decrypted;
            }
        </script>
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
                        <label>劑量:<input type="text" id="dose1"  name="sTdose1"  style="width: 70px;margin-right: 3px" autocomplete="off"></label><label>頻率:<input
                                type="text" style="width: 80px;" id="fUSEF_1" autocomplete="off" >
                            <input type="button" value="清除此欄" style="color: white;border:0;background-color: #6c757d;border-radius:3px;" onclick="clearvalue(1)"></label>
                    </div>

                    <input type="text" name="sID1" value="" id="tt1" style="display: none">
                    <input type="text" name="sUSEF1" value="" id="tt2" style="display: none">
                    <input type="text" value="" id="ckt" style="display: none">
                    <input type="text" value="" id="fut" style="display: none">
                    <input value="" id="funum" type="text" style="display: none">
                </div>

                <div id="ISU2">
                    <button type="button" name="ISLNch" value="2" class="btn btn-primary btn-md" style="font-size: 3.5vmin" data-toggle="modal"
                            data-target="#isuModal" data-whatever="isubtn2">選擇
                    </button>
                    <label>胰島素:</label><input type="text" value="" name="ISLN_B" id="Isu_B" style="border: 1px white;font-size: 4vmin;width:70vmin" autocomplete="off">
                    <div style="margin-top: 5px">
                        <label>劑量:<input type="text" id="dose2" name="sTdose2" style="width: 70px;margin-right: 3px" autocomplete="off"></label><label>頻率:<input
                                type="text" style="width: 80px;" id="fUSEF_2" autocomplete="off">
                            <input type="button" value="清除此欄" style="color: white;border:0;background-color: #6c757d;border-radius:3px;" onclick="clearvalue(2)"></label>
                    </div>
                    <input type="text" name="sID2" value="" id="tt3" style="display: none">
                    <input type="text" name="sUSEF2" value="" id="tt4" style="display: none">
                </div>
                <div id="ISU3" >
                    <button type="button" name="ISLNch" value="3" class="btn btn-primary btn-md" style="font-size: 3.5vmin" data-toggle="modal"
                            data-target="#isuModal" data-whatever="isubtn3">選擇
                    </button>
                    <label>胰島素:</label><input type="text" value="" name="ISLN_C" id="Isu_C" style="border: 1px white;font-size: 4vmin;width:70vmin" autocomplete="off">
                    <div style="margin-top: 5px">
                        <label>劑量:<input type="text" id="dose3" name="sTdose3" style="width: 70px;margin-right: 3px" autocomplete="off"></label><label>頻率:<input
                                type="text" style="width: 80px;" id="fUSEF_3" autocomplete="off">
                            <input type="button" value="清除此欄" style="color: white;border:0;background-color: #6c757d;border-radius:3px;" onclick="clearvalue(3)"></label>
                    </div>
                    <input type="text" name="sID3" value="" id="tt5" style="display: none">
                    <input type="text" name="sUSEF3" value="" id="tt6" style="display: none">
                </div>
            </div>
        </div>
        <!--禁打-->
        <div id="NO_isuling" style="font-size: 4vmin;">
            <div style="background-color:#FF0000;">
                <label style="color: white">禁打部位</label>
            </div>
            <div style="background-color: #FFFBCC;border-radius:3px;padding-top: 5px">
                <label><input type="checkbox" id="No_A" name="forbid[]" value="A" style="width: 4.5vmin;height: 4.5vmin" >A.左臂</label>
                <label><input type="checkbox" id="No_B" name="forbid[]" value="B" style="width: 4.5vmin;height: 4.5vmin">B.左腹</label>
                <label><input type="checkbox" id="No_C" name="forbid[]" value="C" style="width: 4.5vmin;height: 4.5vmin">C.左臀</label>
                <label><input type="checkbox" id="No_D" name="forbid[]" value="D" style="width: 4.5vmin;height: 4.5vmin">D.左腿</label><br>
                <label><input type="checkbox" id="No_E" name="forbid[]" value="E" style="width: 4.5vmin;height: 4.5vmin">E.右腿</label>
                <label><input type="checkbox" id="No_F" name="forbid[]" value="F" style="width: 4.5vmin;height: 4.5vmin">F.右臀</label>
                <label><input type="checkbox" id="No_G" name="forbid[]" value="G" style="width: 4.5vmin;height: 4.5vmin">G.右腹</label>
                <label><input type="checkbox" id="No_H" name="forbid[]" value="H" style="width: 4.5vmin;height: 4.5vmin">H.右臂</label>
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
            <table id="A" border="1" style="z-index: 2;position: static;margin-top: -445px;margin-left: 35px" class="ImgTable">                　
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
            <table id="B" border="1" style="z-index: 2;position: relative;margin-left: 110px;margin-top: -27px" class="ImgTable">
                　
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
            <table id="C" border="1" style="z-index: 2;position: relative;margin-left: 74px;margin-top: -22px" class="ImgTable">
                　
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
            <table id="D" border="1" style="z-index: 2;position: relative;margin-left: 88px;margin-top: -22px" class="ImgTable">
                　
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
            <table id="H" border="1" style="z-index: 2;position: static;margin-top: -450px;margin-left: 265px" class="ImgTable">
                　
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
            <table id="G" border="1" style="z-index: 2;position: relative;margin-left: 190px;margin-top: -28px" class="ImgTable">
                　
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
            <table id="F" border="1" style="z-index: 2;position: relative;margin-left: 230px;margin-top: -22px" class="ImgTable">
                　
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
            <table id="E" border="1" style="z-index: 2;position: relative;margin-left: 215px;margin-top: -22px" class="ImgTable">
                　
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
</div>


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
    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
        <div class="modal-content" style="height: 30%;">
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
                <button type="button" id="Del" class="btn btn-primary">作廢</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
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
                <label style="font-size: 20px;margin-top: 2px;">自訂:<input value="" type="text" style="width: 100px" id="Fuval"></label><input value="確定" class="btn btn-primary" type="button" style="margin-left: 5px;border-radius: 4px;margin-top: -9px;" onclick="PersonFuval()">
                <table>
                    <tr>
                        <td id="fu1"></td>
                        <td id="fu2"></td>
                    </tr>
                </table>
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
                <button type="button" id="ErorFocus" class="btn btn-secondary" data-dismiss="modal" onclick='focustext($("#ERRORVAL").val())'>關閉</button>
            </div>
        </div>
    </div>
</div>
<div class="container">
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
<script>
    function timeback(IDTM) {
        document.getElementById("IDTM").value= IDTM;
        if(IDTM=="ISTM00000001")
        {
            document.getElementById("ISTM00000001").checked= true;
            document.getElementById("Eating1").disabled = false;
            document.getElementById("Eating2").disabled = false;
        }else if(IDTM=="ISTM00000002")
        {
            document.getElementById("ISTM00000002").checked= true;
            document.getElementById("Eating1").disabled = false;
            document.getElementById("Eating2").disabled = false;
        }else if(IDTM=="ISTM00000003")
        {
            document.getElementById("ISTM00000003").checked= true;
            document.getElementById("Eating1").disabled = false;
            document.getElementById("Eating2").disabled = false;
        }else if(IDTM=="ISTM00000004")
        {
            document.getElementById("ISTM00000004").checked= true;
            document.getElementById("Eating1").disabled = false;
            document.getElementById("Eating2").disabled = false;
        }else if(IDTM=="ISTM00000005"){
            document.getElementById("Eating1").checked = false;
            document.getElementById("Eating2").checked = false;
            document.getElementById("ISTM00000005").checked= true;
            document.getElementById("Eating1").disabled = true;
            document.getElementById("Eating2").disabled = true;

        }
    }
    function ISSG_Dateback(DT_EXCUTE,TM_EXCUTE,ST_MEASURE,SPRESS,JID_TIME,CID_MEAL,MM_TPRS) {
        var y=DT_EXCUTE.substr(0,3);
        var m=DT_EXCUTE.substr(3,2);
        var d=DT_EXCUTE.substr(5,2);

        var H=TM_EXCUTE.substr(0,2);
        var M=TM_EXCUTE.substr(2,2);
        timeback(JID_TIME);

        document.getElementById("timer").value=y+m+d;
        document.getElementById("timetxt").value=H+M;
        document.getElementById("Textarea").value=MM_TPRS;
        var regExp = new RegExp(/^[a-zA-Z]+$/);
        if(SPRESS.match(regExp)){
            document.getElementById("sPress").value=SPRESS;
            switch (SPRESS) {
                case 'LO':
                    $("#P_LO").prop('checked',true);
                    break;
                case 'HI':
                    $("#P_HI").prop('checked',true);
                    break;
                case 'NONE':
                    $("#P_NONE").prop('checked',true);
                    break;
                case 'CE':
                    $("#P_CE").prop('checked',true);
                    break;
            }
        }
        if(ST_MEASURE.trim()){
            document.getElementById("STVALval").value=ST_MEASURE;
        }

        if (CID_MEAL=='A'){
            document.getElementById('Eating1').checked=true;

        }else if(CID_MEAL=='B')
        {
            document.getElementById('Eating2').checked=true;
        }
        else{
            document.getElementById('Eating1').checked=false;
            document.getElementById('Eating2').checked=false;
        }
    }
    function ISLN_Dateback(NUM) {
        /*DT_EXCUTE+TM_EXCUTE+JID_TIME+ID_REGION+ID_ORDER+NM_ORDER+DT_TAKEDRUG+TM_TAKEDRUG+ST_DOSE+ST_USENO*/

        var DT_EXCUTE=document.getElementById("DT_E"+ NUM).value;
        var TM_EXCUTE=document.getElementById("TM_E"+ NUM).value;
        var JID_TIME = document.getElementById("JID_T"+ NUM).value;
        var ID_REGION= document.getElementById("ID_R0").value;


        document.getElementById("Isu_A").value=document.getElementById("NM_O"+"0").value;
        document.getElementById("dose1").value=document.getElementById("ST_D"+ '0').value;
        document.getElementById("tt1").value=document.getElementById("ID_O0").value;
        document.getElementById("fUSEF_1").value=document.getElementById("ST_U"+ '0').value;


        if(document.getElementById("NM_O"+"1")){
            document.getElementById("Isu_B").value=document.getElementById("NM_O"+"1").value;
            document.getElementById("dose2").value=document.getElementById("ST_D"+ '1').value;
            document.getElementById("tt3").value=document.getElementById("ID_O"+"1").value;
            document.getElementById("fUSEF_2").value=document.getElementById("ST_U"+ '1').value;

        }
        if(document.getElementById("NM_O"+"2")){
            document.getElementById("Isu_C").value=document.getElementById("NM_O"+"2").value;
            document.getElementById("dose3").value=document.getElementById("ST_D"+ '2').value;
            document.getElementById("tt5").value=document.getElementById("ID_O"+"2").value;
            document.getElementById("fUSEF_3").value=document.getElementById("ST_U"+ '2').value;
        }

        $("input[name=part]").prop('disabled',true);
        var part =ID_REGION.substr(0,1);
        switch (part) {
            case 'A':
                document.getElementById("PartA").checked= true;
                break;
            case 'B':
                document.getElementById("PartB").checked= true;
                break;
            case 'C':
                document.getElementById("PartC").checked= true;
                break;
            case 'D':
                document.getElementById("PartD").checked= true;
                break;
            case 'E':
                document.getElementById("PartE").checked= true;
                break;
            case 'F':
                document.getElementById("PartF").checked= true;
                break;
            case 'G':
                document.getElementById("PartG").checked= true;
                break;
            case 'H':
                document.getElementById("PartH").checked= true;
                break;
        }
        timeback(JID_TIME);
        var y=DT_EXCUTE.substr(0,3);
        var m=DT_EXCUTE.substr(3,2);
        var d=DT_EXCUTE.substr(5,2);
        var H=TM_EXCUTE.substr(0,2);
        var M=TM_EXCUTE.substr(2,2);

        document.getElementById("timer").value=y+m+d;
        document.getElementById("timetxt").value=H+M;
    }
    $('#fUSEF_1').click(function(){
        fumadol();
        $('#funum').val(1);
        $('#fut').val(1);

    });
    $('#fUSEF_2').click(function(){
        fumadol();
        $('#funum').val(2);
        $('#fut').val(2);
    });
    $('#fUSEF_3').click(function(){
        fumadol();
        $('#funum').val(3);
        $('#fut').val(3);
    });
    function fumadol() {
        $('#FuModal').modal('show');
        document.getElementById('Fuval').value='';
    }
    function fuval(val){
        var num=$('#funum').val();
        switch (num) {
            case '1':
                document.getElementById('fUSEF_1').value=val;
                break;
            case '2':
                document.getElementById('fUSEF_2').value=val;
                break;
            case '3':
                document.getElementById('fUSEF_3').value=val;
                break;
        }
        $('#FuModal').modal('hide');
    }
    function PersonFuval() {
        var val= document.getElementById('Fuval').value;
        if($('#fut').val()==1){
            document.getElementById('fUSEF_1').value='';
            document.getElementById('fUSEF_1').value=val;
        }
        else  if($('#fut').val()==2){
            document.getElementById('fUSEF_2').value='';
            document.getElementById('fUSEF_2').value=val;
        }
        else if($('#fut').val()==3){
            document.getElementById('fUSEF_3').value='';
            document.getElementById('fUSEF_3').value=val;
        }
        $('#FuModal').modal('hide');
    }
</script>
</body>
</html>


