<?php
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


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>輸血記錄回報作業</title>
    <script type="text/javascript" src="../../jquery-3.4.1.js"></script>
    <link rel="stylesheet" href="../../bootstrap-4.3.1-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../css/NIS/CBLD.css">
    <script src="../../bootstrap-4.3.1-dist/js/bootstrap.min.js" type="text/javascript"></script>
    <script src="../../crypto-js.js"></script>
    <script src="../../AESCrypto.js"></script>
    <script  type="text/javascript" src="../../instascan.min.js"></script>
    <script src="../../NISCOMMAPI.js"></script>
    <script>
        $(document).ready(function () {
            //url帳號密碼驗證
            let From='<?php echo $From?>';
            if (From==="U"){
                let FromObj=JSON.parse(AESDeCode(UrlCheck('<?php echo $Account?>','<?php echo $passwd?>')));
                if(FromObj.reponse==="false"){
                    alert("帳號密碼錯誤,請關閉視窗重新確認");
                    return;
                }
            }else {
                let ckw=setInterval(()=>{ try {
                    if(!window.opener) {
                        alert("此帳號以被登出,請重新登入開啟");
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

            (function () {
                TimerDefault();
                DefaultElement();
                $("#loading").hide();
                $("#wrapper").hide();
            }());

            $(window).on('beforeunload', function () {
                return '確認要重新整理嗎?';
            });



            $('#NumB').bind("input propertychange",function(){
                //領血
                NumBind('B');
            });


            $('#NumC').bind("input propertychange",function(){
                //發血
                NumBind('C');

            });

            let  w;//領血掃描
            let  x;//責任床位
            let  y;//查詢
            let  z;//輸血掃描
            let  ST_DATAB='';
            let  ST_DATAC='';
            let  DATAval='';
            let  arr=[];

            $(document).on("keydown", "form", function(event){
                return event.key != "Enter";
            });
            $(document).on('change', 'input[type=checkbox]', function() {
                let  checkbox = $(this);
                let  page=$("#PageVal").val();
                if (checkbox.is(':checked')==true)
                {
                    switch (page) {
                        case "B":
                            checkbox.parent().parent().css({'background-color':'#BBFF00'});
                            break;
                        case "C":
                            checkbox.parent().parent().css({'background-color':'#7D7DFF'});
                            break
                    }

                }else
                {
                    checkbox.parent().parent().css({'background-color':'#FFFFFF'});
                }
            });
            $(document).on('click','button',function () {
                let BtnID=$(this).attr('id');
                switch (BtnID) {
                    case "sbed":
                        switch (checkBEDwindow()) {
                            case "false":
                                errorModal("責任床位視窗已開啟");
                                return false;
                                break;
                            case "true":
                                x=window.open("/webservice/NISPWSLKCBD.php?str="+AESEnCode("sFm=CBLD&sIdUser=<?php echo $Account?>"),"責任床位(輸)",'width=850px,height=650px,scrollbars=yes,resizable=no');
                                break;
                        }
                        x.bedcallback=bedcallback;
                        break;
                    case "SerchBtn":
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
                                let  sPg=$("#PageVal").val();
                                console.log("http://localhost"+"/webservice/NISPWSLKQRY.php?str="+
                                    AESEnCode("sFm=CBLD&PageVal="+sPg+"&DA_idpt="+
                                        $('#DA_idpt').val()+"&DA_idinpt="+$('#DA_idinpt').val()+
                                        "&sUser="+$('#'+sPg+'_UR').val()+"&NM_PATIENT="+$('#DataTxt').val()));
                                y=window.open("/webservice/NISPWSLKQRY.php?str="+
                                    AESEnCode("sFm=CBLD&PageVal="+sPg+"&DA_idpt="+
                                        $('#DA_idpt').val()+"&DA_idinpt="+$('#DA_idinpt').val()+
                                        "&sUser="+$('#'+sPg+'_UR').val()+"&NM_PATIENT="+$('#DataTxt').val())
                                    ,"查詢",'width=750px,height=650px,scrollbars=yes,resizable=no');
                                break;
                        }

                        y.Serchcallback=Serchcallback;
                        break;
                    case "cleanval":
                        reset();
                        $("#"+$("#PageVal").val()).css({'background-color' : '', 'opacity' : '','color':'white'});
                        break;
                    case "scanB":
                        switch (checkBEDwindow()) {
                            case "false":
                                errorModal("掃描視窗已開啟");
                                return false;
                                break;
                            case "true":
                                w=window.open("BarcodeScanner.php?page=B","領血掃描",'width=500px,height=750px,scrollbars=yes,resizable=no');
                                /* 自動keyin*/
                                break;
                        }
                        w.Scancallback=Scancallback;
                        break;
                    case "scanC":
                        switch (checkBEDwindow()) {
                            case "false":
                                errorModal("掃描視窗已開啟");
                                return false;
                                break;
                            case "true":
                                z=window.open("BarcodeScanner.php?page=C","輸血掃描",'width=500px,height=750px,scrollbars=yes,resizable=no');
                                /* 自動keyin*/
                                break;
                        }
                        z.Scancallback=Scancallback;
                        break;
                    case "B_ALLCHECK":
                        $("input[name=GTckbox]").prop('checked',true);
                        $("input[name=GTckbox]").parent().parent().css({"background-color":'#BBFF00'});
                        break;
                    case "C_ALLCHECK":
                        $("input[name=INckbox]").prop('checked',true);
                        $("input[name=INckbox]").parent().parent().css({"background-color":'#BBFF00'});
                        break;
                    case "A":
                        $("#PageVal").val(BtnID);
                        $("#"+BtnID).css({ 'background-color' : '#EEEE00', 'opacity' : '' ,'color':'black'});
                        $("#"+BtnID).css({ 'background-color' : '#EEEE00', 'opacity' : '' ,'color':'black'});
                        if(($("#DataTxt").val()).trim()==""){
                            errorModal("請先選擇病人");
                            return false;
                        }
                        if($("#PBList").children().length == 0){
                            _GetINIjson();
                        }else {
                            $("#B").prop("disabled" ,false);
                            $("#C").prop("disabled" ,false);
                        }
                        $("#clickTime").val(1);
                        $("#PUTbld").show();
                        $("#SetGETbldVal").hide();
                        $("#SetINbldVal").hide();
                        $("#GETbldUI").hide();
                        $("#INbldUI").hide();


                        $("#B").css({ 'background-color' : '', 'opacity' : '' ,'color':'white'});
                        $("#C").css({ 'background-color' : '', 'opacity' : '' ,'color':'white'});
                        $('#SubmitBtn').prop('disabled',true);
                        $('#DELMENU').prop('disabled',true);
                        $('#SerchBtn').prop('disabled',true);
                        break;
                    case "B":
                        $("#PageVal").val(BtnID);
                        $("#"+BtnID).css({ 'background-color' : '#EEEE00', 'opacity' : '' ,'color':'black'});
                        $("#"+BtnID).css({ 'background-color' : '#EEEE00', 'opacity' : '' ,'color':'black'});
                        $("#PUTbld").hide();
                        $("#SetGETbldVal").show();
                        $("#SetINbldVal").hide();
                        $("#GETbldUI").show();
                        $("#INbldUI").hide();
                        let  CCKVal=GetCheckVal('C');

                        InsertWSST($("#sTraID").val(),'A',JSON.stringify(GETNEWSTDATA()));
                        InsertWSST($("#sTraID").val(),'C',JSON.stringify(CCKVal));
                        LoadPageData('CBLD',$('#sTraID').val(),'B');


                        $("#A").css({ 'background-color' : '', 'opacity' : '' ,'color':'white'});
                        $("#C").css({ 'background-color' : '', 'opacity' : '' ,'color':'white'});
                        $('#SubmitBtn').prop('disabled',false);
                        $('#DELMENU').prop('disabled',false);
                        $('#SerchBtn').prop("disabled" ,false);
                        break;
                    case "C":
                        $("#PageVal").val(BtnID);
                        $("#"+BtnID).css({ 'background-color' : '#EEEE00', 'opacity' : '' ,'color':'black'});
                        $("#"+BtnID).css({ 'background-color' : '#EEEE00', 'opacity' : '' ,'color':'black'});
                        $("#PUTbld").hide();
                        $("#SetGETbldVal").hide();
                        $("#SetINbldVal").show();
                        $("#GETbldUI").hide();
                        $("#INbldUI").show();
                        let  BCKVal=GetCheckVal('B');

                        InsertWSST($("#sTraID").val(),'A',JSON.stringify(GETNEWSTDATA()));
                        InsertWSST($("#sTraID").val(),'B',JSON.stringify(BCKVal));
                        LoadPageData('CBLD',$('#sTraID').val(),'C');


                        $("#B").css({ 'background-color' : '', 'opacity' : '' ,'color':'white'});
                        $("#A").css({ 'background-color' : '', 'opacity' : '' ,'color':'white'});
                        $('#SubmitBtn').prop('disabled',false);
                        $('#DELMENU').prop('disabled',false);
                        $('#SerchBtn').prop("disabled" ,false);
                        break;
                    case "Del":
                        let  del_ip='/webservice/NISPWSDELILSG.php';
                        console.log('http://localhost'+del_ip+"?str="+AESEnCode("sFm="+'CBLD'+"&sTraID="+$('#sTraID').val()+"&sPg="+$("#PageVal").val()+"&sCidFlag=D"+"&sUr="+$("#B_UR").val()));
                        let  sPg=$("#PageVal").val();

                        $.ajax({
                            url:del_ip+"?str="+AESEnCode("sFm="+'CBLD'+"&sTraID="+$('#sTraID').val()+"&sPg="+sPg+"&sCidFlag=D"+"&sUr="+$("#"+sPg+"_CUR").val()),
                            type:'POST',
                            dataType:'text',
                            success:function (json) {
                                let  data=JSON.parse(AESDeCode(json));
                                if(data.message=='false'){
                                    errorModal('作廢失敗');
                                    return false;
                                }else {
                                    $('#DELModal').modal('hide');
                                    $('#clickTime').val(0);
                                    $("#"+$("#PageVal").val()).css({'background-color' : '', 'opacity' : '','color':'white'});
                                    reset();
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
                        break;
                }
            });

            $('#PBList').on('change',CheckBOXChang);/*歷次發血選擇*/
            $("#form1").submit(function () {
                /*$(window).off('beforeunload', reloadmsg);*/
                $(window).off('beforeunload');
                let  json='';
                let  page=$('#PageVal').val();
                switch (page) {
                    case 'B':
                        json=GetCheckVal('B');
                        break;
                    case 'C':
                        json=GetCheckVal('C');
                        break;
                }
                $("#loading").show();
                $("#wrapper").show();
                /*  console.log(json) ;*/
                console.log('http://localhost/webservice/NISPWSSAVEILSG.php?str='+ AESEnCode('sFm=' + 'CBLD' +
                    '&sTraID=' + $('#sTraID').val() +
                    '&sPg=' + $("#PageVal").val() +
                    '&sDt=' + $("#DateVal").val() +
                    '&sTm=' + $("#TimeVal").val()+
                    '&PASSWD='+$("#"+page+"_CPWD").val()+
                    '&USER='+paddingLeft($("#"+page+"_CUR").val().toUpperCase(),7))
                );
                $.ajax({
                    url: '/webservice/NISPWSSAVEILSG.php?str=' + AESEnCode('sFm=' + 'CBLD' +
                        '&sTraID=' + $('#sTraID').val() +
                        '&sPg=' + $("#PageVal").val() +
                        '&sDt=' + $("#DateVal").val() +
                        '&sTm=' + $("#TimeVal").val()+
                        '&PASSWD='+$("#"+page+"_CPWD").val()+
                        '&USER='+paddingLeft($("#"+page+"_CUR").val().toUpperCase(),7))
                    ,
                    type: 'POST',
                    beforeSend: InsertWSST($('#sTraID').val(), $("#PageVal").val(), JSON.stringify(json)),
                    dataType: 'text',
                    success: function (data) {
                        $("#loading").hide();
                        $("#wrapper").hide();
                        let  str=AESDeCode(data);
                        let  dataObj=JSON.parse(str);
                        console.log(dataObj);
                        let  result = dataObj.response;
                        let  message = dataObj.message;
                        if (result == "success") {
                            alert("儲存成功");
                            window.location.reload(true);
                        } else {
                            errorModal(message);
                        }
                    }
                });
                return false;

            });

            function NumBind(page){
                let Cval2= $('#Num'+page).val();
                let  arr3=[],arr4=[];
                let  CheckBoxID='';
                let  color=page=="B"?"#BBFF00":"#7D7DFF";
                let  pagNam=page=="B"?"GT":"IN";
                if(Cval2.length<10)
                {
                    //字數不符
                    return false
                }


                if(Cval2.indexOf(',')>-1){
                    //長字串連接

                    let  arr=Cval2.split(',');
                    let  errNum=[];
                    $.each(arr,function (index,bid) {
                        $("#"+pagNam+bid).parent().parent().css({"background-color":color});
                        $("#"+pagNam+bid).prop("checked",true);
                        if($("#"+pagNam+bid).length > 0){
                            $('#Num'+page).val('');
                        }else {
                            errNum.push(bid);
                        }

                    });
                    if(errNum.length>0){
                        alert("血袋號碼:"+errNum.join()+",請確認血袋是否正確");
                    }

                }else {
                    if(Cval2 !="" && Cval2 !=null){
                        CheckBoxID=pagNam+Cval2;
                        if($("#"+CheckBoxID).length > 0){
                            arr3.push(Cval2);
                            arr4.push(Cval2);
                            let  nowval=arr3.pop();
                            $.each(arr4,function (index,bid) {
                                $("#"+pagNam+bid).parent().parent().css({"background-color":""});
                            });
                            $("#"+pagNam+nowval).parent().parent().css({"background-color":color});
                            $("#"+pagNam+nowval).prop("checked",true);
                            $('#Num'+page).val('');
                            let  top=($("#"+pagNam+nowval).offset()).top-500;
                            $("#scr"+page).scrollTop(top);
                        }else {
                            alert("血袋號碼:"+Cval2+"請確認血袋是否正確");
                        }
                    }
                }

            }
            function reset() {
                let  page=$('#PageVal').val();
                $("#PUTbld").hide();
                $("#SetGETbldVal").hide();
                $("#SetINbldVal").hide();
                $("#GETbldUI").hide();
                $("#INbldUI").hide();
                $("#A").prop('disabled',false);
                $("#B").prop("disabled" ,true);
                $("#C").prop("disabled" ,true);
                $("#PBList").children().remove();
                $("#GTList").children().remove();
                $("#INList").children().remove();
                $('#SerchBtn').prop('disabled',true);
                $('#DELMENU').prop('disabled',true);
                $('#SubmitBtn').prop('disabled',true);
                $("#scan"+page).prop('disabled',false);
                $('#DateVal').prop('readonly',false);
                $('#TimeVal').prop('readonly',false);
                $('#code'+page).prop('readonly',false);
                $("#"+page+"_CUR").prop('readonly',false);
                $("#"+page+"_CPWD").prop('readonly',false);
                $("#"+page+"_ALLCHECK").prop('disabled',false);
                $('input[name="INckbox"]').prop('disabled',false);
                $('input[name="GTckbox"]').prop('disabled',false);
            }
            function bedcallback(data){
                /*責任床位ws*/
                let  str=AESDeCode(data);
                let  dataObj=JSON.parse(str);
                reset();

                try {
                    if(dataObj){
                        $.each(dataObj,function (index) {
                            $("#DataTxt").val(dataObj[index].DataTxt);
                            $("#DA_idpt").val(dataObj[index].IDPT);
                            $("#DA_idinpt").val(dataObj[index].IDINPT);
                            $("#DA_sBed").val(dataObj[index].SBED);
                            $("#clickTime").val(0);
                            $("#PageVal").val('A');
                            TimerDefault();
                        });

                    }
                }catch (e) {
                    alert(e);
                }

            }
            function Scancallback(data) {
                let  json=JSON.parse(data);
                console.log(json);
                let  page=json.PAGE;
                let  BIDArr=json.B_ID;
                if(BIDArr.length >0){
                    switch (page) {
                        case "B":
                            $.each(BIDArr,function (index,bid) {
                                if($("#GT"+bid.trim()).length > 0){
                                    $("#GT"+bid.trim()).prop("checked",true);
                                    $("#GT"+bid.trim()).parent().parent().css({"background-color":'#BBFF00'});
                                }
                            });

                            break;
                        case "C":

                            $.each(BIDArr,function (index,bid) {
                                if($("#IN"+bid.trim()).length > 0){
                                    $("#IN"+bid.trim()).prop("checked",true);
                                    $("#IN"+bid.trim()).parent().parent().css({"background-color":'#7D7DFF'});
                                }
                            });
                            break;
                    }
                }
            }
            function LoadPageData(sfm,sTraID,Page) {
                //表單資料
                if(Page=='A'){
                    if($("#PBList").children().length >0){
                        return false;
                    }
                }
                if(Page=='B'){
                    if($("#GTList").children().length > 0){
                        return false;
                    }
                }
                if(Page=='C'){
                    if($("#INList").children().length > 0){
                        return false;
                    }
                }
                console.log("http://localhost"+'/webservice/NISPWSGETPRE.PHP?str='+AESEnCode('sFm='+sfm+'&sTraID='+sTraID+'&sPg='+Page));
                $.ajax({
                    url:'/webservice/NISPWSGETPRE.PHP?str='+AESEnCode('sFm='+sfm+'&sTraID='+sTraID+'&sPg='+Page),
                    type:"POST",
                    dataType:"text",
                    success:function(data){
                        let  json=JSON.parse(AESDeCode(data))[0];
                        console.log(json);
                        switch (Page) {
                            case "A":

                                let  ST_DATAA=JSON.parse(json.ST_DATAA);
                                console.log(ST_DATAA);
                                if($("#PBList").children().length == 0){
                                    $.each(ST_DATAA,function (index) {
                                        let  DT_EXE=ST_DATAA[index].DT_EXE;
                                        let  TM_EXE=ST_DATAA[index].TM_EXE;
                                        let  Num=ST_DATAA[index].NUM;
                                        let  A_INDNO=ST_DATAA[index].A_INDNO;
                                        let  A_TRANO=ST_DATAA[index].A_TRANO;
                                        let RadioBtnVal=DT_EXE+"@"+TM_EXE+"@"+Num+"@"+A_INDNO+"@"+A_TRANO;
                                        $("#PBList").append(
                                            `
                                        <tr>
                                            <td style='width: 20vmin'><input type='radio' id='ck0' name='PBCK' value='${RadioBtnVal}'></td>
                                            <td>${DT_EXE}</td>
                                            <td>${TM_EXE}</td>
                                            <td>${Num}</td>
                                            <td style='display: none'>${A_INDNO}</td>
                                            <td style='display: none'>${A_TRANO}</td>
                                        </tr>
                                      `
                                        );
                                    });
                                }
                                break;
                            case "B":
                                ST_DATAB=JSON.parse(json.ST_DATAB);
                                console.log(ST_DATAB);
                                if($("#GTList").children().length == 0){
                                    $.each(ST_DATAB,function (index) {
                                        let  B_ID=ST_DATAB[index].B_ID;
                                        let  B_NUM=ST_DATAB[index].B_NUM;
                                        let  B_TP=ST_DATAB[index].B_TP;
                                        let  B_UR=ST_DATAB[index].B_UR;
                                        let  B_CUR=ST_DATAB[index].B_CUR;
                                        let  B_DTSEQ=ST_DATAB[index].B_DTSEQ;
                                        let  B_BKD=ST_DATAB[index].B_BKD;
                                        let  B_INDNO=ST_DATAB[index].B_INDNO;
                                        let CheckID="GT"+B_ID;
                                        let CheckBoxVal=B_ID+"\@"+B_NUM+"\@"+B_TP+"\@"+B_UR+"\@"+B_CUR+"\@"+B_DTSEQ+"\@"+B_BKD+"\@"+B_INDNO;

                                        $("#GTList").append(
                                            `
                                      <tr class='list-item'>
                                        <td><input type='checkbox'  name='GTckbox' class='form-check-input' id='${CheckID}' value='${CheckBoxVal}'></td>
                                        <td>${B_ID}</td>
                                        <td>${B_NUM}</td>
                                        <td>${B_TP}</td>
                                        <td>${$('#B_NAME').val()}<br><label id='${'BCUR'+index}'></label></td>
                                        <td style='display: none'>${B_UR}<br>${B_CUR}</td>
                                        <input  style='display: none' type='text' value=${B_DTSEQ}  id=${"B"+B_DTSEQ}>
                                        <input  style='display: none' type='text' value=${B_BKD}  id=${"B"+B_BKD}>
                                        <input  style='display: none' type='text' value=${B_INDNO}  id=${"B"+B_INDNO}>
                                      </tr>
                                      `
                                        );
                                    });
                                }
                                break;
                            case "C":
                                ST_DATAC=JSON.parse(json.ST_DATAC);
                                console.log(ST_DATAC);
                                if($("#INList").children().length == 0){
                                    $.each(ST_DATAC,function (index) {
                                        let  C_ID=ST_DATAC[index].C_ID;
                                        let  C_NUM=ST_DATAC[index].C_NUM;
                                        let  C_TP=ST_DATAC[index].C_TP;
                                        let  C_UR=ST_DATAC[index].C_UR;
                                        let  C_CUR=ST_DATAC[index].C_CUR;
                                        let  C_DTSEQ=ST_DATAC[index].C_DTSEQ;
                                        let  C_BKD=ST_DATAC[index].C_BKD;
                                        let  C_INDNO=ST_DATAC[index].C_INDNO;
                                        let CheckID="IN"+C_ID;
                                        let CheckBoxVal=C_ID+"@"+C_NUM+"@"+C_TP+"@"+C_UR+"@"+C_CUR+"@"+C_DTSEQ+"@"+C_BKD+"@"+C_INDNO;

                                        $("#INList").append(
                                            `
                                      <tr class='list-item'>
                                        <td><input type='checkbox'  name='GTckbox' class='form-check-input' id='${CheckID}' value='${CheckBoxVal}'></td>
                                        <td>${C_ID}</td>
                                        <td>${C_NUM}</td>
                                        <td>${C_TP}</td>
                                        <td style='display: none'>${C_UR}<br>${C_CUR}</td>
                                        <td>${$('#C_NAME').val()}<br><label id='${'CCUR'+index}'></label></td>

                                        <input  style='display: none' type='text' value='${C_DTSEQ}'  id='${"B"+C_DTSEQ}'>
                                        <input  style='display: none' type='text' value='${C_BKD}'  id='${"B"+C_BKD}'>
                                        <input  style='display: none' type='text' value='${C_INDNO}'  id='${"B"+C_INDNO}'>
                                      </tr>
                                      `
                                        );
                                    });
                                }
                                break;
                        }
                    },error:function (XMLHttpResponse,textStatus,errorThrown) {
                        console.log(
                            "1 返回失敗,XMLHttpResponse.readyState:"+XMLHttpResponse.readyState+XMLHttpResponse.responseText+
                            "2 返回失敗,XMLHttpResponse.status:"+XMLHttpResponse.status+
                            "3 返回失敗,textStatus:"+textStatus+
                            "4 返回失敗,errorThrown:"+errorThrown
                        );
                        return false;
                    }

                });
            }
            function _GetINIjson() {
                //歷次發血表單資料
                $("#wrapper").show();
                console.log("http://localhost"+'/webservice/NISPWSTRAINI.PHP?str='+AESEnCode('sFm=CBLD&idPt='+$("#DA_idpt").val()+'&INPt='+$("#DA_idinpt").val()+'&sUr=<?php echo $Account?>'));
                $.ajax({
                    url:'/webservice/NISPWSTRAINI.PHP?str='+AESEnCode('sFm=CBLD&idPt='+$("#DA_idpt").val()+'&INPt='+$("#DA_idinpt").val()+'&sUr=<?php echo $Account?>'),
                    type:"POST",
                    dataType:"text",
                    success:function(data){
                        let  json=JSON.parse(AESDeCode(data))[0];
                        console.log(json);
                        $('#sTraID').val(json.sTraID);
                        $('#sSave').val(json.sSave);
                        $('#INDENTNO').val(json.INDENTNO);
                        $('#TRANSRECNO').val(json.TRANSRECNO);
                        LoadPageData('CBLD',$('#sTraID').val(),'A');
                        $("#wrapper").hide();
                    },error:function () {
                        console.log("error");
                    }
                });
            }
            function CheckBOXChang(){
                $("input[type=radio]:checked").each(function () {
                    DATAval = $(this).val();
                    arr.push(DATAval);
                    $("#B").prop("disabled" ,false);
                    $("#C").prop("disabled" ,false);
                    $("#GTList").children().remove();
                    $("#INList").children().remove();
                });

            }
            function GETNEWSTDATA() {
                let  arr=DATAval.split("@",5);
                let  newST_DATAA=[{
                    'DT_EXE':parseInt(arr[0]),
                    'TM_EXE':parseInt(arr[1]),
                    'B_NUM':parseInt(arr[2]),
                    'A_INDNO':parseInt(arr[3]),
                    'A_TRANO':arr[4]
                }];

                return newST_DATAA;
            }
            function InsertWSST(sTraID,page,json) {
                /*
                       console.log("http://localhost"+'/webservice/NISPWSSETDATA.php?str='+AESEnCode('sFm=CBLD&sTraID='+sTraID+'&sPg='+page+'&sData='+json));
                */
                $.ajax({
                    'url':'/webservice/NISPWSSETDATA.php?str='+AESEnCode('sFm=CBLD&sTraID='+sTraID+'&sPg='+page+'&sData='+json),
                    type:"POST",
                    dataType:"text",
                    success:function(data){
                        let  json=JSON.parse(AESDeCode(data));
                        console.log(json.message);
                    },
                    error:function (XMLHttpResponse,textStatus,errorThrown) {
                        console.log(
                            "1 返回失敗,XMLHttpResponse.readyState:"+XMLHttpResponse.readyState+XMLHttpResponse.responseText+
                            "2 返回失敗,XMLHttpResponse.status:"+XMLHttpResponse.status+
                            "3 返回失敗,textStatus:"+textStatus+
                            "4 返回失敗,errorThrown:"+errorThrown
                        );
                        return false;
                    }

                });
            }
            function Serchcallback(AESobj){
                let  str1=AESDeCode(AESobj);
                let  page=$("#PageVal").val();
                let  obj=JSON.parse(str1);

                $('#SubmitBtn').prop('disabled',true);
                $('#DateVal').prop('readonly',true);
                $('#TimeVal').prop('readonly',true);
                $('#code'+page).prop('readonly',true);
                $("#"+page+"_CUR").prop('readonly',true);
                $("#"+page+"_CPWD").prop('readonly',true);
                $("#"+page+"_ALLCHECK").prop('disabled',true);
                $("#scan"+page).prop('disabled',true);
                $("#A").prop('disabled',true);
                $("#B").prop('disabled',true);
                $("#C").prop('disabled',true);
                /*$('#NumB').prop('disabled',true);
                $('#NumC').prop('disabled',true);*/
                if(page=="B"){
                    $('input[name="GTckbox"]').prop('disabled',true);
                }
                else if (page=="C"){
                    $('input[name="INckbox"]').prop('disabled',true);

                }


                $.each(obj,function (index) {
                    let  BCK_BAGENO=obj[index].BCK_BAGENO;       //血袋號碼
                    let  BCK_RECNO=obj[index].BCK_RECNO;         //血袋序號
                    let  sSave=obj[index].sSave;
                    let  sTraID=obj[index].sTraID;
                    let  BCK_DATMSEQ=obj[index].BCK_DATMSEQ;
                    $('#DATSEQ').val(BCK_DATMSEQ);
                    $('#sTraID').val(sTraID);
                    $('#sSave').val(sSave);
                    if(page=='B'){
                        let  BCK_GETCKOPID=obj[index].BCK_GETCKOPID; //領血覆核人員(帳號)
                        let  BCK_GETDATE=obj[index].BCK_GETDATE;     //領血日期
                        let  BCK_GETFROM=obj[index].BCK_GETFROM;     //領血來源
                        let  BCK_GETOPID=obj[index].BCK_GETOPID;     //領血核對人員
                        let  BCK_GETTIME=obj[index].BCK_GETTIME;     //領血時間
                        let  BCK_OPIDNM=obj[index].BCK_OPIDNM;       //領血覆核人員(姓名)


                        $('#DateVal').val(BCK_GETDATE);
                        $('#TimeVal').val(BCK_GETTIME);
                        $('#BCUR'+Getindex('B',BCK_BAGENO)).html(BCK_OPIDNM);
                        $("#GT"+BCK_BAGENO).prop('checked',true);
                        $("#GT"+BCK_BAGENO).parent().parent().css({"background-color":"#BBFF00"});

                    }
                    if(page=='C'){
                        let  BCK_TRACKOPID=obj[index].BCK_TRACKOPID; //輸血覆核人員
                        let  BCK_TRADATE=obj[index].BCK_TRADATE;     //輸血日期
                        let  BCK_TRAFROM=obj[index].BCK_TRAFROM;     //輸血來源
                        let  BCK_TRAOPID=obj[index].BCK_TRAOPID;     //輸血核對人員
                        let  BCK_TRATIME=obj[index].BCK_TRATIME;     //輸血時間
                        let  BCK_OPIDNM=obj[index].BCK_OPIDNM;       //輸血覆核人員(姓名)
                        $('#DateVal').val(BCK_TRADATE);
                        $('#TimeVal').val(BCK_TRATIME);
                        $('#CCUR'+Getindex('C',BCK_BAGENO)).html(BCK_OPIDNM);
                        $("#IN"+BCK_BAGENO).prop('checked',true);
                        $("#IN"+BCK_BAGENO).parent().parent().css({"background-color":"#9393FF"});
                    }

                });

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
            function DefaultElement(){
                $("#B").prop("disabled" ,true);
                $("#C").prop("disabled" ,true);
                $('#DELMENU').prop("disabled" ,true);
                $('#SerchBtn').prop("disabled" ,true);
                $('#SubmitBtn').prop("disabled" ,true);
            }
            function Getindex(page,BCK_BAGENO) {
                let  arr=[];
                let  id='';
                if(page=='B'){
                    let  SelectorArr=$("input:checkbox[name=GTckbox]");
                    $.each(SelectorArr,function (index) {
                        arr.push(SelectorArr[index].id);
                    });
                    id='GT'+BCK_BAGENO;
                }
                if(page=='C'){
                    let  SelectorArr=$("input:checkbox[name=INckbox]");
                    $.each(SelectorArr,function (index) {
                        arr.push(SelectorArr[index].id);
                    });
                    id='IN'+BCK_BAGENO;
                }

                return arr.indexOf(id);
            }
            function GetCheckVal(page) {
                //取checkbox的值
                let  cbxVehicle = new Array();
                let  Json=[];
                switch (page) {
                    case "B":
                        $("input:checkbox:checked[name=GTckbox]").each(function (i) {
                            cbxVehicle[i]=this.value;
                        });
                        if(cbxVehicle.length>0){
                            $.each(cbxVehicle,function (index) {

                                let  str=cbxVehicle[index];
                                let  OBJ=new Object();
                                console.log(str);
                                OBJ.B_ID= str.split("@",8)[0];
                                OBJ.B_NUM= str.split("@",8)[1];
                                OBJ.B_TP= str.split("@",8)[2];
                                OBJ.B_UR= $("#B_UR").val();
                                OBJ.B_CUR=paddingLeft($('#B_CUR').val().toUpperCase(),7);
                                OBJ.B_DTSEQ= str.split("@",8)[5];
                                OBJ.B_BKD= str.split("@",8)[6];
                                OBJ.B_INDNO= str.split("@",8)[7];
                                Json.push(OBJ);
                            });
                        }

                        break;
                    case "C":
                        $("input:checkbox:checked[name=INckbox]").each(function (i) {
                            cbxVehicle[i]=this.value;
                        });
                        if(cbxVehicle.length>0){
                            $.each(cbxVehicle,function (index) {
                                let  str=cbxVehicle[index];
                                let  OBJ=new Object();
                                OBJ.C_ID= str.split("@",8)[0];
                                OBJ.C_NUM= str.split("@",8)[1];
                                OBJ.C_TP= str.split("@",8)[2];
                                OBJ.C_UR= $("#C_UR").val();
                                OBJ.C_CUR=paddingLeft($('#C_CUR').val().toUpperCase(),7);
                                OBJ.C_DTSEQ= str.split("@",8)[5];
                                OBJ.C_BKD= str.split("@",8)[6];
                                OBJ.C_INDNO= str.split("@",8)[7];
                                Json.push(OBJ);
                            });
                        }
                        break;
                }
                return Json;
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
            function errorModal(str) {
                $('#Errormodal').modal('show');
                document.getElementById('ErrorText').innerText=str;
            }
            function TimerDefault() {
                let  TimeNow=new Date();
                let  yyyy=TimeNow.toLocaleDateString().slice(0,4);
                let  MM=(TimeNow.getMonth()+1<10?'0':'')+(TimeNow.getMonth()+1);
                let  dd=(TimeNow.getDate()<10?'0':'')+TimeNow.getDate();
                let   h=(TimeNow.getHours()<10?'0':'')+TimeNow.getHours();
                let   m=(TimeNow.getMinutes()<10?'0':'')+TimeNow.getMinutes();
                let   s=(TimeNow.getSeconds()<10?'0':'')+TimeNow.getSeconds();
                $("#DateVal").val(yyyy-1911+MM+dd);
                $("#TimeVal").val(h+m);
            }
            function paddingLeft(str,lenght){
                /*向左補0*/
                if(str.length >= lenght)
                    return str;
                else
                    return paddingLeft("0" +str,lenght);
            }
        });
    </script>
</head>
<body>
<div id="wrapper"></div>
<div id="loading" >請稍後<img class="loadimg" src="../../dotloading.gif"></div>
<div class="container">
    <h1>輸血記錄回報作業</h1>
    <form id="form1">
        <span style="margin-left:0 px">
            <button type="button" class="btn btn-primary btn-md" disabled style="display: none">回主畫面</button>
            <button type="button"  class="btn btn-warning btn-md"  id="sbed" style="margin: 0 0 1px 1px" >責任床位</button><span style="margin-left: 1px"><b>悅晟資訊</b></span>

        </span>

        <span class="float-left">
            <button type="submit" id="SubmitBtn" class="btn btn-primary btn-md" >儲存</button>
            <button type="button" id="SerchBtn" class="btn btn-primary btn-md" >查詢</button>
            <button type="button" id="DELMENU" class="btn btn-primary btn-md"   data-toggle="modal" data-target="#DELModal">作廢</button>
            <button type="reset" id="cleanval" class="btn btn-primary btn-md" >清除</button>
            <button type="button" class="btn btn-primary btn-md" disabled style="margin-right: 3px ;display: none">預設</button>
        </span>

        <table class="table" style="font-size:3.5vmin">
            <thead>
            </thead>
        </table>
        <div class="input-group">
            <input id="DataTxt"  value="" type="text" readonly="readonly" style="background-color: #FFFBCC;;font-size: 4vmin;width:100vmin;">
        </div>
        <!--參數欄位-->
        <div class="Parametertable">
            <input id="DA_idpt" value="" type="text" name="DA_idpt"   placeholder="DA_idpt"> <!--病歷號-->
            <input id="DA_idinpt" value="" type="text" name="DA_idinpt"  placeholder="DA_idinpt"><!--住院號-->
            <input id="DA_sBed" value="" type="text" name="DA_sBed" placeholder="DA_sBed"><!--床號-->
            <input id="PageVal" type="text" value="" placeholder="PageVal">
            <input id="clickTime" type="text" value="" placeholder="clickTime">
            <input id="sTraID" value="" type="text" placeholder="sTraID"> <!--交易序號-->
            <input id="sSave" value="" type="text" placeholder="sSave">      <!--存檔權限-->
            <input id="CCURCheck" value="" type="text" placeholder="CCURCheck">
            <input id="INDENTNO" value="" type="text" placeholder="INDENTNO">
            <input id="TRANSRECNO" value="" type="text" placeholder="TRANSRECNO">
            <input id="DATESEQANCE_FL" value="" type="text" placeholder="DATESEQANCE_FL">
            <input id="FORMSEQANCE" value="" type="text" placeholder="FORMSEQANCE">
            <input id="SCAN" value="" type="text" placeholder="SCAN">
            <input id="DATSEQ" value="" type="text" placeholder="BCK_DATMSEQ">
        </div>

        <div class="Otimer" >
            <div class="pageTime">
                <label style="font-size: 4vmin">評估時間:</label>
                <input  type="text" id="DateVal" value=""  placeholder="YYYMMDD" maxlength="7" autocomplete="off">
                <input type="text" id="TimeVal" value="" placeholder="HHMM" maxlength="4" autocomplete="off">
            </div>
        </div>

        <div class="Features">
            <button type="button" class="btn btn-primary " name="click"  id="A" >歷次發血</button>
            <button type="button" class="btn btn-primary " name="click"  id="B" >領血核對</button>
            <button type="button" class="btn btn-primary " name="click" id="C">輸血核對</button>
        </div>

        <!--歷次發血-->
        <div id="PUTbld" style="display: none" class="container">
                <table class="table  table-striped" style="text-align: center">
                    <thead>
                        <tr>
                            <th style="width: 20vmin"></th>
                            <th>發血日期</th>
                            <th>時間</th>
                            <th>袋數</th>
                        </tr>
                    </thead>
                    <tbody id="PBList" >

                    </tbody>
                </table>
        </div>
        <!--領血核對-->
        <div >
            <!--掃描條碼Bar-->
            <div class="container" id="SetGETbldVal" style="display: none;background-color:#bee5eb;margin-top: 5px; ">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" >
                        <div>
                            <div class="input-group" style="margin-top: 5px;margin-bottom: 2px;">
                                <div class="input-group-prepend">
                                    <span class="input-group-text" id="">血袋</span>
                                </div>
                                    <input type="text" id="NumB" class="form-control" placeholder="輸入條碼">
                                <div class="input-group-append">
                                    <input  style="display: none" type="button" value="掃描" id="scanB" class="btn btn-outline-primary ">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" >
                        <div class="input-group" style="margin-top: 5px;margin-bottom: 2px;">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="">領血核對</span>
                            </div>
                            <input type="text" class="form-control" style="display: none;background-color: #00FF00" placeholder="使用者" id="B_NAME"  value="<?php echo $sUr?>" readonly>
                            <input type="text" class="form-control"   placeholder="帳號" id="B_UR"  value="<?php echo $Account?>" readonly>
                            <input type="password" class="form-control" placeholder="密碼" id="B_PWD"  value="<?php echo $passwd?>" readonly>
                        </div>
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" >
                        <div class="input-group" style="margin-top: 5px;margin-bottom: 2px;">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="">領血覆核</span>
                            </div>
                            <input type="text" class="form-control" placeholder="帳號" value="" id="B_CUR" maxlength="7" autocomplete="off">
                            <input type="password" class="form-control" placeholder="密碼" value="" id="B_CPWD">
                            <input type="text" style="display: none;background-color: #00FF00"  placeholder="覆核人員" value="" id="B_CNAME">
                        </div>
                    </div>
                </div>
            </div>
            <!--內容-->
            <div id="GETbldUI" style="display: none;">
                    <table class="table" style="table-layout: fixed;text-align: center;margin-bottom: 0rem;">
                        <thead  class="theadtitle"  style=" font-size: 3.5vmin;">
                        <th> <button type="button" class="btn btn-success" style="font-size: 3vmin" id="B_ALLCHECK">全選</button></th>
                        <th style=" padding-bottom: 5px !important">血袋號碼</th>
                        <th style=" padding-bottom: 5px !important">血品名稱</th>
                        <th style="text-align: center ;padding-bottom: 5px !important">血型</th>
                        <th >領血核對<br>領血覆核</th>
                        <th style="display: none">輸血核對<br>輸血覆核</th>
                        </thead>
                    </table>

                    <div id="scrB" data-spy="scroll" data-target="#GBUI" data-offset="0" style="height:300px;overflow:auto; position: relative;">
                        <div class="table-responsive" id="GBUI">
                            <table class="table" style="table-layout: fixed;text-align: center">
                                <tbody style=" font-size: 3.3vmin;" id="GTList">

                                </tbody>
                            </table>
                        </div>
                    </div>
            </div>
        </div>

        <!--輸血核對-->
        <div>
            <!--掃描條碼Bar-->
            <div class="container" id="SetINbldVal" style="display: none;background-color:#bee5eb;margin-top: 5px; ">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" >
                        <div>
                            <div class="input-group" style="margin-top: 5px">
                                <div class="input-group-prepend">
                                    <span class="input-group-text" id="">血袋</span>
                                </div>
                                <input type="text" id="NumC" class="form-control" placeholder="輸入條碼">
                                <div class="input-group-append">
                                    <input type="button"  style="display: none" value="掃描" id="scanC" class="btn btn-outline-primary">
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" >
                        <div class="input-group" style="margin-top: 5px;margin-bottom: 2px;">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="">輸血核對</span>
                            </div>
                            <input type="text" class="form-control" placeholder="帳號"  id="C_UR" value="<?php echo $Account?>" readonly>
                            <input type="password" class="form-control" placeholder="密碼" id="C_PWD" value="<?php echo $passwd?>" readonly>
                            <input type="text" class="form-control" style="display: none;background-color: #00FF00" placeholder="使用者"  id="C_NAME" value="<?php echo $sUr?>" readonly>

                        </div>
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" >
                        <div class="input-group" style="margin-top: 5px;margin-bottom: 2px;">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="">輸血覆核</span>
                            </div>
                            <input type="text" class="form-control" placeholder="帳號" value="" id="C_CUR" maxlength="7" autocomplete="off">
                            <input type="password" class="form-control" placeholder="密碼" value="" id="C_CPWD">
                            <input type="text" style="display: none;background-color: #00FF00" placeholder="覆核人員" value="" id="C_CNAME">
                        </div>
                    </div>
                </div>
            </div>
            <!--內容-->
            <div id="INbldUI" style="display: none;">
                <table class="table" style="table-layout: fixed;text-align: center;margin-bottom: 0rem;">
                    <thead  class="theadtitle"  style=" font-size: 3.5vmin;">
                    <th> <button type="button" class="btn btn-success" style="font-size: 3vmin" id="C_ALLCHECK">全選</button></th>
                    <th style=" padding-bottom: 5px !important">血袋號碼</th>
                    <th style=" padding-bottom: 5px !important">血品名稱</th>
                    <th style="text-align: center ;padding-bottom: 5px !important">血型</th>
                    <th >輸血核對<br>輸血覆核</th>
                    <th style="display: none">輸血核對<br>輸血覆核</th>
                    </thead>
                </table>



                <div id="scrC" data-spy="scroll" data-target="#IBUI" data-offset="0" style="height:300px;overflow:auto; position: relative;">
                    <div class="table-responsive" id="IBUI">
                        <table class="table" style="table-layout: fixed;text-align: center">
                            <tbody style=" font-size: 3.3vmin;" id="INList">

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </form>
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
                <button type="button" id="ErorFocus" class="btn btn-secondary" data-dismiss="modal">關閉</button>
            </div>
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
</body>

</html>


