<?php
include '../../NISPWSIFSCR.php';
$str=$_GET['str'];
$replaceSpace=str_replace(' ','+',$str);//空白先替換+
$EXPLODE_data=explode('&',AESDeCode($replaceSpace));
preg_match('/(sfm)/',AESDeCode($replaceSpace),$matches);

$sfm_STR='';
$sfm_value='';
$sfm='';


if(count($matches)>0){
    $sfm_STR=$EXPLODE_data[0];
    $sfm_value=explode('=',$sfm_STR);
    $sfm=trim($sfm_value[1]);
}

$n=count($matches)>0?1:0;
$sIdUser_STR=$EXPLODE_data[$n];
$passwd_STR=$EXPLODE_data[$n+1];
$user_STR=$EXPLODE_data[$n+2];


$sIdUser_value=explode('=',$sIdUser_STR);
$passwd_value=explode('=',$passwd_STR);
$user_value=explode('=',$user_STR);


$sIdUser=trim($sIdUser_value[1]);/*帳號*/
$passwd=trim($passwd_value[1]);/*密碼*/
$sUr=trim($user_value[1]);/*使用者*/
$OPID=strtoupper(str_pad($sIdUser,7,"0",STR_PAD_LEFT));
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>NISPRWCNAD</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script type="text/javascript" src="../../jquery-3.4.1.js"></script>
    <link rel="stylesheet" href="../../bootstrap-4.3.1-dist/css/bootstrap.min.css">
    <script src="../../bootstrap-4.3.1-dist/js/bootstrap.min.js" type="text/javascript"></script>
    <script src="../../crypto-js.js"></script>
    <script src="../../AESCrypto.js"></script>
    <script src="../../NISCOMMAPI.js"></script>
    <script>
        var sfm='<?php echo $sfm?>';
        console.log(sfm);
        if(sfm==""){
            var ckw=setInterval(function () {
                try {
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

    </script>
    <style>
        /* In order to place the tracking correctly */
        .container{
            max-width: 1140px;
        }
        .container .ListBtn button{
            color: white;
            font-size: 4.5vmin;
        }
        .container .float-left{
            font-size: 3.7vmin;
        }
        .List{
            height:43vmin;
            overflow:auto;
            position: relative;

        }
        .btn {
            margin-top: 5px;
        }

        body{
            overscroll-behavior-y:contain;
        }

        input{
            border-radius:4px;border:1px solid #DBDBDB;
        }
        input[type=checkbox]{
            width: 4.5vmin;
            height: 4.5vmin;
        }
        th{
            padding-bottom: 5px !important;
        }
        h1{
            margin-top:5px;
        }
        td{
        overflow-wrap: break-word;
        }
        .Otimer{
            margin-top:10px;
            font-size: 4vmin;
            background-color: #baeeff;
            border-radius:3px;
        }
        .Otimer  .pageTime #DateVal{
            width:35vmin;
            text-align: center;
            margin-top: 5px;

        }
        .Otimer .pageTime #TimeVal {
            width: 15vmin;
            margin-left: 5px;
            margin-top: 5px;
            border: 1px white;
            text-align: center;
        }
        .Parametertable input{
            display: none;
            background-color: #00FF00;
        }
        #loading{
            position: absolute;
            z-index: 9999;
            top: 50%;
            left: 50%;
            background-color: #FFFFFF;
            color: #000000;
            font-size: 5vmin;
            width: 45vmin;
            height: 12vmin;
            padding-left:20px;
            padding-top:10px;
            border-radius: 5px;
            margin: -15vmin 0 0 -30vmin;

        }
        #loading .loadimg{
            width: 10vmin;
            height:10vmin;
        }
        #wrapper{
            position: absolute;
            width: 100%;
            height: 100%;
            background-color: black;
            opacity: 0.5;
            z-index: 9998;
        }
        .input-group{
            margin-top: 5px;
        }
        .Num_input{
            margin-top: 5px;
            margin-bottom: 5px;
            height: 40px;
            font-size: 20px;
        }

    </style>
</head>

<body>
<div id="wrapper"></div>
<div id="loading" >請稍後<img class="loadimg" src="../../dotloading.gif"></div>
<div class="Parametertable" >
    <input id="sSave" type="text" value="" placeholder="sSave" >
    <input id="sTraID" type="text" value="" placeholder="sTraID">
    <input id="DA_IdPt" type="text" value="" placeholder="DA_IdPt">
    <input id="NURSOPID" type="text" value="<?php echo $OPID?>" placeholder="NURSOPID">
</div>

<div class="container">
    <h2>發血覆核作業</h2>
    <form id="form1">
         <span class="ListBtn" style="margin-left:0 px">
             <button type="button" class="btn btn-secondary btn-md" disabled style="display: none">回主畫面</button>
             <!-- <button type="button"  class="btn btn-warning btn-md" style="margin-left: 1px"   id="sbed" >責任床位</button><span style="margin-left: 1px">-->
             <button type="button"  class="btn btn-warning btn-md" style="margin-left: 1px"   id="sbed" >輸血紀錄單</button><span style="margin-left: 1px"></span>
        </span>
        <span class="ListBtn float-left">
            <button type="submit" id="SubmitBtn" class="btn btn-primary btn-md" >儲存</button>
            <button type="button" id="SerchBtn" class="btn btn-primary btn-md">查詢</button>
            <button type="button" id="DELMENU" class="btn btn-primary btn-md"  data-toggle="modal" data-target="#DELModal" disabled>作廢</button>
<!--            <button type="reset" class="btn btn-primary btn-md" id="restBtn" style="display: none">清除</button>
-->           <button type="button" id="ReStart" class="btn btn-primary btn-md" >重整</button>
        </span>
      <!--  <div class="input-group">
            <input id="DataTxt"  value="" type="text" readonly="readonly" style="font-size: 4vmin;width:100vmin;">
        </div>-->


        <div class="Otimer" >
            <div class="pageTime">
                <label style="font-size: 4vmin">評估時間:</label>
                <input  type="text" id="DateVal" value=""  placeholder="YYYMMDD" maxlength="7" autocomplete="off">
                <input type="text" id="TimeVal" value="" placeholder="HHMM" maxlength="4" autocomplete="off">
            </div>
        </div>

        <div>
            <div>
                <input id="IdPt" class="Num_input" type="text" placeholder="輸入病歷號" maxlength="8">
                <input id="NumId" class="Num_input"  type="text" placeholder="輸入血袋號碼" >
                <button id="Error_btn" type="button" class="btn btn-secondary btn-md Num_input"  style="margin-bottom: 15px;" disabled>錯誤查詢</button>
            </div>
            <table class="table" style="table-layout: fixed;text-align: center;margin-bottom: 0rem;">
                <thead  class="theadtitle" style=" font-size: 3vmin;">
                <th></th>
                <th>輸血編號</th>
                <th>病歷號</th>
                <th >姓名</th>
                <th>血品</th>
                <th>血袋號碼</th>
                </thead>
            </table>
            <div id="scrollList" data-spy="scroll" data-target="#navbar-example" data-offset="0" class="List" style="overflow:auto;">
                <table class="table" style="table-layout: fixed;text-align: center">
                    <tbody style=" font-size: 3.5vmin;" id="DATAList">

                    </tbody>
                </table>
            </div>
        </div>
    </form>
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
                    <button type="button" id="Del" class="btn btn-primary btn-md">作廢</button>
                    <button type="button" class="btn btn-secondary btn-md" data-dismiss="modal">取消</button>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="Errormodal" tabindex="-1" aria-labelledby="ErrormodalCenterTitle" role="dialog" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ErrormodalCenterTitle">錯誤訊息</h5>
            </div>
            <div class="modal-body"   id="ModalBody" style="overflow-y: auto">
                <table class="table" style="table-layout: fixed;text-align: center;margin-bottom: 0rem;">
                    <thead  class="theadtitle" style=" font-size: 2.5vmin;">
                    <th style=" padding-bottom: 5px !important">序號</th>
                    <th style=" padding-bottom: 5px !important">病歷號</th>
                    <th style="text-align: center ;padding-bottom: 5px !important">血袋號碼</th>
                    </thead>
                    <tbody style=" font-size: 3.5vmin;" id="ErrBlood">

                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" id="ErorFocus" class="btn btn-secondary" data-dismiss="modal">關閉</button>
            </div>
        </div>
    </div>
</div>
</body>
<script>
$(document).ready(function () {
    DefaultData();
    $("#loading").hide();
    $("#wrapper").hide();
    var x;
    var y;

    $("#IdPt").bind("input propertychange",function () {
        console.log();
        if(this.value.length==8)
        {
            $("#NumId").focus();
        }
    });

    var err=[];
    var obj={
        IDPT:{},
        BSK_BAGENO:{},
        NUM:{}
    };
    var ErrIndex=0;
    $("#NumId").bind("input propertychange",function () {
        var BSK_BAGENO=($(this).val()).replace(/\s*/g,"");
        var id=$("#IdPt").val();

        if(BSK_BAGENO.length >=10){
            if(BSK_BAGENO.indexOf("@")>-1){
                var arr= BSK_BAGENO.replace(/,/g,"@").split("@");
                var len=arr.length;
                for (var i=1;i<len;i++)
                {
                    var checkID=$("#"+arr[0]+"\\@"+arr[i]);
                    if(checkID.length>0){
                        checkID.prop("checked",true);
                        checkID.parent().parent().css({'background-color':'#BBFF00'});

                    }else {
                        obj.IDPT=arr[0];
                        obj.BSK_BAGENO=arr[i];
                        obj.NUM=i;
                        var copy=Object.assign({},obj);//淺複製錯誤血袋
                        err.push(copy);
                    }

                }
                if(err.length>0){
                    errUI(err);
                }
            } else{
                ErrIndex++;
                if($("#"+id+"\\@"+BSK_BAGENO).length>0){
                    $("#"+id+"\\@"+BSK_BAGENO).prop("checked",true);
                    $("#"+id+"\\@"+BSK_BAGENO).parent().parent().css({'background-color':'#BBFF00'});
                    var top=($("#"+id+"\\@"+BSK_BAGENO).offset()).top-500;
                    $("#scrollList").scrollTop(top);
                }else {
                    obj.IDPT=id;
                    obj.BSK_BAGENO=BSK_BAGENO;
                    obj.NUM=ErrIndex;
                    var copy=Object.assign({},obj);//淺複製錯誤血袋
                    err.push(copy);
                    console.log(err);
                    var errfilter=err.filter(function (element, index, arr) {
                        return arr.indexOf(element)===index;
                    });
                    errUI(errfilter);
                }
            }
            $("#IdPt").focus();
            $("#IdPt").val("");
            $("#NumId").val("");
        }
    });
    $(document).on('change', 'input[type=checkbox]', function() {
        var checkbox = $(this);
        if (checkbox.is(':checked')==true)
        {
            checkbox.parent().parent().css({'background-color':'#BBFF00'});
        }else
        {
            checkbox.parent().parent().css({'background-color':'#FFFFFF'});
        }
    });
    $(document).on("keydown", "form", function(event) {
        return event.key != "Enter";
    });
    $(".Parametertable").children().prop('readonly',true);

    $("#Error_btn").click(function () {
        err.length=0;
        ErrIndex=0;
        $('#Errormodal').modal('show');
    });

    $("#sbed").click(function () {
        switch (checkBEDwindow()) {
            case "false":
                  errorModal("領血單位視窗已開啟");
                break;
            case "true":
                try {
                    x=window.open("/webservice/NISPRWCBED.php?str="+AESEnCode("sFm=CNAD&sIdUser=<?php echo $OPID?>"),"輸血單位",'width=850px,height=650px,scrollbars=yes,resizable=no');

                }catch (e) {
                    errorModal(e);
                }
                break;
        }
        x.bedcallback=bedcallback;
    });
    $("#form1").submit(function () {
        //$(window).off('beforeunload', reloadmsg);
        var json=GetCheckVal();
        console.log("http://localhost"+'/webservice/NISPWSSAVEILSG.php?str=' + AESEnCode('sFm=' + 'CNAD' +
            '&sTraID=' + $('#sTraID').val() +
            '&sPg=' +"" +
            '&sDt=' + $("#DateVal").val() +
            '&sTm=' + $("#TimeVal").val()+
            '&PASSWD='+""+
            '&USER=<?php echo $OPID?>'));
       $.ajax({
            url: '/webservice/NISPWSSAVEILSG.php?str=' + AESEnCode('sFm=' + 'CNAD' +
                '&sTraID=' + $('#sTraID').val() +
                '&sPg=' +"" +
                '&sDt=' + $("#DateVal").val() +
                '&sTm=' + $("#TimeVal").val()+
                '&PASSWD='+""+
                '&USER=<?php echo $OPID?>')
            ,
            type: 'POST',
            beforeSend: InsertWSST($('#sTraID').val(), 'B', JSON.stringify(json)),
            dataType: 'text',
            success: function (data) {
                 $("#loading").hide();
                  $("#wrapper").hide();
                var str=AESDeCode(data);
                var dataObj=JSON.parse(str);
                var result = dataObj.response;
                var message = dataObj.message;
                if (result == "success") {
                    alert("儲存成功");
                    window.location.reload(true);
                }else {
                     errorModal(message);
                 }
            },error:function (XMLHttpResponse,textStatus,errorThrown) {
                console.log(
                    "1 返回失敗,XMLHttpResponse.readyState:"+XMLHttpResponse.readyState+XMLHttpResponse.responseText+
                    "2 返回失敗,XMLHttpResponse.status:"+XMLHttpResponse.status+
                    "3 返回失敗,textStatus:"+textStatus+
                    "4 返回失敗,errorThrown:"+errorThrown
                );
            }
        });
        return false;

    });
    $("#SerchBtn").click(function () {
        switch (checkSerchwindow()) {
            case "false":
                alert("查詢視窗已開啟");
                break;
            case "true":
                y=window.open("/webservice/NISPWSLKQRY.php?str="+
                    AESEnCode("sFm=CNAD&PageVal="+""+"&DA_idpt="+
                        $('#DA_IdPt').val()+"&DA_idinpt="+""+
                        "&sUser="+"<?php echo $OPID?>"+"&NM_PATIENT="+"")
                    ,"查詢",'width=750px,height=650px,scrollbars=yes,resizable=no');
                break;
        }

        y.Serchcallback=Serchcallback;
    });
    $("#ReStart").click(function () {
        err.length=0;
        DefaultData();
        $('input[type=text]:not("#NURSOPID")').val("");
        $('button[type=submit]').prop('disabled',false);
        $("#Error_btn").css({"background-color":"#6c757d","border-color":"#6c757d"});
        $("#Error_btn").prop("disabled",true);
        $('#DELMENU').prop('disabled',true);

    });
    $("#Del").click(function() {
        var del_ip='/webservice/NISPWSDELILSG.php';
        console.log('http://localhost'+del_ip+"?str="+AESEnCode("sFm="+'CNAD'+"&sTraID="+$('#sTraID').val()+"&sPg="+""+"&sCidFlag=D"+"&sUr=<?php echo $OPID?>"));
        $.ajax({
            url:del_ip+"?str="+AESEnCode("sFm="+'CNAD'+"&sTraID="+$('#sTraID').val()+"&sPg="+""+"&sCidFlag=D"+"&sUr=<?php echo $OPID?>"),
            type:'POST',
            dataType:'text',
            success:function (json) {
                var data=JSON.parse(AESDeCode(json));
                console.log(data);
                if(data.message=='false'){
                    errorModal('作廢失敗');
                    return false;
                }else {
                    $('#DELModal').modal('hide');
                    DefaultData();
                    $("#DELMENU").prop('disabled',true);
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
    });

    function DefaultData() {
        $("#loading").show();
        $("#wrapper").show();
        $("#DATAList").children().remove();
        $.ajax({
            url:"/webservice/NISPWSTRAINI.php?str="+AESEnCode('sFm=CNAD&idPt='+"00055664"+'&INPt='+"970000884"+'&sUr=<?php echo $OPID?>'),
            type:"POST",
            dataType: 'text',
            success:function (data) {
                $("#loading").hide();
                $("#wrapper").hide();
                var ArrJson=JSON.parse(AESDeCode(data));
                $.each(ArrJson,function (index,val) {
                    var   BCK_DATMSEQ=val.BCK_DATMSEQ;
                    var   BSK_BAGENO=val.BSK_BAGENO;
                    var  BSK_MEDNO=val.BSK_MEDNO;
                    var  MH_NAME=val.MH_NAME;
                    var  BKD_EGCODE=val.BKD_EGCODE;
                    var BSK_TRANSRECNO=val.BSK_TRANSRECNO;
                    var sTraID=val.sTraID;
                    var sSave=val.sSave;
                    $("#sTraID").val(sTraID);
                    $("#sSave").val(sSave);
                    $("#DATAList").append
                    (
                        "<tr class='list-item'>"+
                        "<td>"+"<input type='checkbox'  name='BDckbox' class='form-check-input' id='"+BSK_MEDNO+'@'+BSK_BAGENO+"' value='"+BCK_DATMSEQ+"@"+BSK_TRANSRECNO+"@"+BSK_MEDNO+"@"+BSK_BAGENO+"'>"+"</td>"+
                        "<td >"+BSK_TRANSRECNO+"</td>"+
                        "<td>"+BSK_MEDNO+"</td>"+
                        "<td>"+MH_NAME+"</td>"+
                        "<td>"+BKD_EGCODE+"</td>"+
                        "<td>"+BSK_BAGENO+"</td>"+
                        "</tr>"
                    );

                });
                TimerDefault();

            },
            error:function (XMLHttpResponse,textStatus,errorThrown) {
                console.log(
                    "1 返回失敗,XMLHttpResponse.readyState:"+XMLHttpResponse.readyState+XMLHttpResponse.responseText+
                    "2 返回失敗,XMLHttpResponse.status:"+XMLHttpResponse.status+
                    "3 返回失敗,textStatus:"+textStatus+
                    "4 返回失敗,errorThrown:"+errorThrown
                );
            }
        });
    }
    function bedcallback(data) {
        var str=AESDeCode(data);
        var dataObj=JSON.parse(str);
        console.log(dataObj);
        var   BSK_ALLOWDATE=dataObj.BSK_ALLOWDATE;
        var   BSK_ALLOWTIME=dataObj.BSK_ALLOWTIME;
        var   sSave=dataObj.sSave;
        var   sTraID=dataObj.sTraID;
        var   MH_MEDNO=dataObj.MH_MEDNO;
        $("#DATAList").children().remove();
        $("#sSave").val(sSave);
        $("#sTraID").val(sTraID);
        $("#DA_IdPt").val(MH_MEDNO);
        TableList(dataObj.BSK_TRANSRECNO,sTraID);
        $('input[type=submit]').prop('disabled',false);
    }
    function Serchcallback(AESobj){
        var str1=AESDeCode(AESobj);
        var objArr=JSON.parse(str1);
        $("#DATAList").children().remove();
        $.each(objArr,function (index,val) {
            var   BCK_DATMSEQ=val.BCK_DATMSEQ;
            var   BSK_BAGENO=val.BSK_BAGENO;
            var  BSK_MEDNO=val.BSK_MEDNO;
            var  MH_NAME=val.MH_NAME;
            var  BKD_EGCODE=val.BKD_EGCODE;
            var BSK_TRANSRECNO=val.BSK_TRANSRECNO;
            var sTraID=val.sTraID;
            var sSave=val.sSave;
            $("#sTraID").val(sTraID);
            $("#sSave").val(sSave);
            $("#DateVal").val(val.BCK_OUTDATE);
            $("#TimeVal").val(val.BCK_OUTTIME);
            $("#DATAList").append
            (
                "<tr class='list-item'>"+
                "<td>"+"<input type='checkbox'  name='BDckbox' class='form-check-input' id='"+BSK_MEDNO+'@'+BSK_BAGENO+"' value='"+BCK_DATMSEQ+"@"+BSK_TRANSRECNO+"@"+BSK_MEDNO+"@"+BSK_BAGENO+"'>"+"</td>"+
                "<td >"+BSK_TRANSRECNO+"</td>"+
                "<td>"+BSK_MEDNO+"</td>"+
                "<td>"+MH_NAME+"</td>"+
                "<td>"+BKD_EGCODE+"</td>"+
                "<td>"+BSK_BAGENO+"</td>"+
                "</tr>"
            );
        });
        $('input[type=checkbox]').prop('disabled',true);
        $('input[type=checkbox]').prop('checked',true);
        $('#DELMENU').prop('disabled',false);
        $('button[type=submit]').prop('disabled',true);

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
    function TableList(BSK_TRANSRECNO,sTraID) {

        console.log("http://localhost"+'/webservice/NISPWSGETPRE.PHP?str='+AESEnCode('sFm='+"CNAD"+'&sTraID='+sTraID+'&sPg='+BSK_TRANSRECNO));

        $.ajax({
            url:'/webservice/NISPWSGETPRE.PHP?str='+AESEnCode('sFm='+"CNAD"+'&sTraID='+sTraID+'&sPg='+BSK_TRANSRECNO),
            type:"POST",
            dataType:"text",
            success:function (data) {
                var arr=JSON.parse(AESDeCode(data));
               InsertWSST(sTraID,'B',AESDeCode(data));
                console.log(arr);
                if(arr.length==0){
                    $("#DATAList").append(
                        "<tr class='list-item'>"+
                        "<td>"+"查無資料"+"</td>"+
                        "</tr>"
                    );
                    return false;
                }
                $.each(arr,function (index,val) {
                    var   BCK_DATMSEQ=val.BCK_DATMSEQ;
                    var   BSK_BAGENO=val.BSK_BAGENO;
                    var  BSK_MEDNO=val.BSK_MEDNO;
                    var  MH_NAME=val.MH_NAME;
                    var  BKD_EGCODE=val.BKD_EGCODE;
                    var  BSK_TRANSRECNO=val.BSK_TRANSRECNO;
                    $("#DATAList").append(
                        "<tr class='list-item'>"+
                        "<td>"+"<input type='checkbox'  name='BDckbox' class='form-check-input' id='"+BSK_MEDNO+'@'+BSK_BAGENO+"' value='"+BCK_DATMSEQ+"@"+BSK_TRANSRECNO+"@"+BSK_MEDNO+"@"+BSK_BAGENO+"'>"+"</td>"+
                        "<td>"+BSK_TRANSRECNO+"</td>"+
                        "<td>"+BSK_MEDNO+"</td>"+
                        "<td>"+MH_NAME+"</td>"+
                        "<td>"+BKD_EGCODE+"</td>"+
                        "<td>"+BSK_BAGENO+"</td>"+
                        "</tr>"
                    );

                });
            },error:function (XMLHttpResponse,textStatus,errorThrown) {
                console.log(
                    "1 返回失敗,XMLHttpResponse.readyState:"+XMLHttpResponse.readyState+XMLHttpResponse.responseText+
                    "2 返回失敗,XMLHttpResponse.status:"+XMLHttpResponse.status+
                    "3 返回失敗,textStatus:"+textStatus+
                    "4 返回失敗,errorThrown:"+errorThrown
                );
            }
        });
    }
    function InsertWSST(sTraID,page,json) {

        console.log("http://localhost"+'/webservice/NISPWSSETDATA.php?str='+AESEnCode('sFm=CNAD&sTraID='+sTraID+'&sPg='+page+'&sData='+json));

        $.ajax({
            'url':'/webservice/NISPWSSETDATA.php?str='+AESEnCode('sFm=CNAD&sTraID='+sTraID+'&sPg='+page+'&sData='+json),
            type:"POST",
            dataType:"text",
            success:function(data){
                var json=JSON.parse(AESDeCode(data));
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
    function TimerDefault() {
        var TimeNow=new Date();
        var yyyy=TimeNow.toLocaleDateString().slice(0,4);
        var MM=(TimeNow.getMonth()+1<10?'0':'')+(TimeNow.getMonth()+1);
        var dd=(TimeNow.getDate()<10?'0':'')+TimeNow.getDate();
        var  h=(TimeNow.getHours()<10?'0':'')+TimeNow.getHours();
        var  m=(TimeNow.getMinutes()<10?'0':'')+TimeNow.getMinutes();
        var  s=(TimeNow.getSeconds()<10?'0':'')+TimeNow.getSeconds();
        $("#DateVal").val(yyyy-1911+MM+dd);
        $("#TimeVal").val(h+m);
    }
    function GetCheckVal() {
        //取checkbox的值
        var cbxVehicle = new Array();
        var Json=[];
        $("input:checkbox:checked[name=BDckbox]").each(function (i) {
            cbxVehicle[i]=$(this).val();
        });

        if(cbxVehicle.length>0){
            $.each(cbxVehicle,function (index) {
                var str=cbxVehicle[index];
                var OBJ=new Object();
                console.log(str);
                OBJ.BCK_DATMSEQ= str.split("@",4)[0];
                OBJ.BSK_TRANSRECNO= str.split("@",4)[1];
                OBJ.BSK_MEDNO= str.split("@",4)[2];
                OBJ.BSK_BAGENO= str.split("@",4)[3];
                Json.push(OBJ);
            });
        }
        return Json;
    }
    function errorModal(str) {
        $("#ModalBody").append(
            '<p id="ErrorText" style="font-size: 2.5vmin;word-wrap: break-word">'+str+'</p>'
        );
        $('#Errormodal').modal('show');

    }
    function errUI(err){
        $("#Error_btn").css({"background-color":"#FF0000","border-color":"#FF0000"});
        $("#Error_btn").prop("disabled",false);
        $("#ErrBlood").children().remove();
        $.each(err,function (index,val) {
            $("#ErrBlood").append(
                "<tr class='list-item'>"+
                "<td>"+val.NUM+"</td>"+
                "<td>"+val.IDPT+"</td>"+
                "<td>"+val.BSK_BAGENO+"</td>"+
                "<tr>"
            );
        });
    }
});
</script>
