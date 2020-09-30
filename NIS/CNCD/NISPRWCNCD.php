<?php
/*include '../../NISPWSIFSCR.php';
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


$sIdUser=trim($sIdUser_value[1]);
$passwd=trim($passwd_value[1]);
$sUr=trim($user_value[1]);
$OPID=strtoupper(str_pad($sIdUser,7,"0",STR_PAD_LEFT));*/
$OPID="00FUZZY";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>NISPRWCNCD</title>
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
        /*        if(sfm==""){
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
                }*/

    </script>

</head>
<style>
    .container{
        max-width: 1140px;
    }
    .container .ListBtn button{
        color: white;
        font-size: 4.5vmin;
    }
    .List{
        height:35vh;
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
        /*display: none;*/
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
    .PatientName input{
        border:1px white;
        font-size: 4vmin;
        width:100vmin;
        background-color: #FFFBCC;
        border-radius:3px;
        margin-top: 10px;
    }
</style>

<body>
<div id="wrapper"></div>
<div id="loading" >請稍後<img class="loadimg" src="../../dotloading.gif"></div>
<div class="Parametertable" >
    <input id="sSave" class="noneEle" type="text" value="" placeholder="sSave" >
    <input id="sTraID"  class="noneEle" type="text" value="" placeholder="sTraID">
    <input id="DA_InPt"  class="noneEle" type="text" value="" placeholder="DA_InPt">
    <input id="DA_IdPt"  class="noneEle" type="text" value="" placeholder="DA_IdPt">
    <input id="SBED"  class="noneEle" type="text" value="" placeholder="SBED">
    <input id="NURSOPID"  class="noneEle" type="text" value="<?php echo $OPID?>" placeholder="NURSOPID">
</div>

<div class="container">
    <h2>檢驗採檢辨識作業</h2>
    <form id="form1">
        <div class="ListBtn">
            <button type="submit" id="SubmitBtn" class="btn btn-primary btn-md" disabled>儲存</button>
            <button type="button" id="SerchBtn" class="btn btn-primary btn-md" disabled>查詢</button>
            <button type="button" id="DELMENU" class="btn btn-primary btn-md"  data-toggle="modal" data-target="#DELModal" disabled>作廢</button>
            <button type="button" id="ReStart" class="btn btn-primary btn-md" >清除</button>
            <button type="button"  class="btn btn-warning btn-md" style="margin-left: 1px"   id="sbed" >責任床位</button><span style="margin-left: 1px">
        </div>
        <div class="PatientName">
            <input id="DataTxt"  value="" type="text" readonly="readonly">
        </div>
        <div class="Otimer">
            <div class="pageTime">
                <label style="font-size: 4vmin">評估時間:</label>
                <input  type="text" id="DateVal" value=""  placeholder="YYYMMDD" maxlength="7" autocomplete="off">
                <input type="text" id="TimeVal" value="" placeholder="HHMM" maxlength="4" autocomplete="off">
            </div>
        </div>

        <div>
            <div>
                <input id="IdPt" class="Num_input" type="text" placeholder="輸入病歷號" maxlength="8" autocomplete="off">
                <input id="NumId" class="Num_input"  type="text" placeholder="輸入採血編號" autocomplete="off">
                <button id="Error_btn" type="button" class="btn btn-secondary btn-md Num_input"  style="margin-bottom: 15px;" disabled>錯誤查詢</button>
            </div>
          <!--  <table class="table" style="table-layout: fixed;text-align: center;margin-bottom: 0rem;text-align:center;">
                <thead  class="theadtitle" style=" font-size: 3vmin;">
                    <th></th>
                    <th style='text-align:left;'>採血編號</th>
                    <th>病歷號</th>
                    <th >姓名</th>
                    <th>檢體</th>
                    <th>容器</th>
                </thead>
            </table>-->
            <div id="scrollList" data-spy="scroll" data-target="#navbar-example" data-offset="0" class="List" style="overflow:auto;">
                <table class="table" style="table-layout: fixed;text-align: center">
                    <tbody style=" font-size: 3.0vmin;" id="DATAList" >

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
                    <thead id="ErrorTitle" class="theadtitle" style=" font-size: 2.5vmin;">
                    <th style=" padding-bottom: 5px !important">序號</th>
                    <th style=" padding-bottom: 5px !important">病歷號</th>
                    <th style="text-align: center ;padding-bottom: 5px !important">採血編號</th>
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

        var x;
        var y;
        (function () {
            $(".Parametertable").children().prop('readonly',true);
            $("#loading").hide();
            $("#wrapper").hide();
        })();

        $("#IdPt").bind("input propertychange",function () {
            if(this.value.length==8)
            {
                if(this.value!=$("#DA_IdPt").val()){
                    errorModal("與此病人的病歷號不符,是否重新選擇?","Button");
                    return false;
                }
                $("#NumId").focus();
            }
        });
        /* $("#NumId").bind("input propertychange",function () {
                   CheckUIisset($("#IdPt").val(),$(this).val());
               });*/
        var err=[];
        var obj={
            IDPT:{},
            BAR_CODE:{},
            NUM:{}
        };
        var ErrIndex=0;
        var FocusIndex="";
        var InputIdArr=['DataTxt','DateVal','TimeVal','IdPt','NumId'];

        $(document).on('focus', 'input[type=text]', function() {
            var Index=InputIdArr.indexOf($(this).attr('id'));
            FocusIndex=Index+1;
            return false;

        });
        $(document).on("keydown", "form", function(event) {
             if(event.key=='Enter'){
                 if($("input[type=text]:not(.noneEle)").is(":focus")==true)
                 {
                     if(FocusIndex>4){
                         $("#"+InputIdArr[FocusIndex-1]).focus();
                         CheckUIisset($("#IdPt").val(),$("#NumId").val());
                     }else {
                         $("#"+InputIdArr[FocusIndex]).focus();
                     }
                 }
             }
            $("#form1").on("submit",function () {return false;});
            return event.key != "Enter";
        });
        function CheckUIisset(IdPt,NumidStr){
            if($("#"+IdPt+"\\@"+NumidStr).length>0){
                $("#"+IdPt+"\\@"+NumidStr).prop('checked',true);
                $("#"+IdPt+"\\@"+NumidStr).parent().parent().css({'background-color':'#BBFF00'});

            }else {
                ErrIndex++;
                obj.IDPT=IdPt===""?"病歷號輸入有誤":IdPt;
                obj.BAR_CODE=NumidStr===""?"條碼輸入有誤":NumidStr;
                obj.NUM=ErrIndex;
                var copy=Object.assign({},obj);//淺複製錯誤血袋
                err.push(copy);
                var errfilter=err.filter(function (element, index, arr) {
                    return arr.indexOf(element)===index;
                });
                errUI(errfilter);
            }
            $("#NumId").val("");
            /*var BSK_BAGENO=NumidStr.replace(/\s*!/g,"");
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
                    if($("#"+IdPt+"\\@"+BSK_BAGENO).length>0){
                        $("#"+IdPt+"\\@"+BSK_BAGENO).prop("checked",true);
                        $("#"+IdPt+"\\@"+BSK_BAGENO).parent().parent().css({'background-color':'#BBFF00'});
                        var top=($("#"+IdPt+"\\@"+BSK_BAGENO).offset()).top-500;
                        $("#scrollList").scrollTop(top);
                    }else {
                        obj.IDPT=IdPt;
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
                $("#NumId").focus();
                $("#NumId").val("");
*/
        }

        $(document).on('change', 'input[type=checkbox]', function() {
            if ($(this).prop('checked')==true)
            {
                $(this).parent().parent().css({'background-color':'#BBFF00'});
                $(this).parent().parent().next('tr').css({'background-color':'#BBFF00'});
            }else
            {
                $(this).parent().parent().css({'background-color':'#FFFFFF'});
                $(this).parent().parent().next('tr').css({'background-color':'#FFFFFF'});
            }
        });
        $(document).on('click','#BedChange',function () {
            $.ajax({
                url:"/webservice/NISCNCDRECALLBED.php",
                type:"POST",
                dataType: 'text',
                success:function (data) {
                    var NewBedJson=JSON.parse(data);
                    console.log(NewBedJson);
                    $("#DataTxt").val(NewBedJson[0].DataTxt);
                    $("#DA_IdPt").val(NewBedJson[0].IDPT);
                    $("#DA_InPt").val(NewBedJson[0].IDINPT);
                    $("#SBED").val(NewBedJson[0].SBED);
                    DefaultData(NewBedJson[0].IDPT,NewBedJson[0].IDINPT);
                    TimerDefault();
                    $("#IdPt").val("");
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
        });

        $("#Error_btn").click(function () {
            $("#ErrorTitle").show();
            $("#ErrBlood").show();
            $("#ErrorText").hide();
            $('#Errormodal').modal('show');
        });
        $("#sbed").click(function () {
            switch (checkBEDwindow()) {
                case "false":
                  /*  errorModal("責任床位視窗已開啟");*/
                    break;
                case "true":
                    try {
                        x=window.open("/webservice/NISPRWCBED.php?str="+AESEnCode("sFm=CNCD&sIdUser=<?php echo $OPID?>"),"責任床位",'width=850px,height=650px,scrollbars=yes,resizable=no');

                    }catch (e) {
                        console.log(e);
                    }
                    break;
            }
            x.bedcallback=bedcallback;
        });
        $("#form1").submit(function () {
            //$(window).off('beforeunload', reloadmsg);
          /*  if($("input[type=checkbox]").prop("checked")==false || $("#DATAList").children().length<=0){
                alert("儲存錯誤:尚未選擇任一筆檢體");
                return  false;
            }*/
            $("#loading").show();
            $("#wrapper").show();
            var json=GetCheckVal();
            console.log(json);
          console.log("http://localhost"+'/webservice/NISPWSSAVEILSG.php?str=' + AESEnCode('sFm=' + 'CNCD' +
                '&sTraID=' + $('#sTraID').val() +
                '&sPg=' +"" +
                '&sDt=' + $("#DateVal").val() +
                '&sTm=' + $("#TimeVal").val()+
                '&PASSWD='+""+
                '&USER=<?php echo $OPID?>'));
            $.ajax({
                url: '/webservice/NISPWSSAVEILSG.php?str=' + AESEnCode('sFm=' + 'CNCD' +
                    '&sTraID=' + $('#sTraID').val() +
                    '&sPg=' +"" +
                    '&sDt=' + $("#DateVal").val() +
                    '&sTm=' + $("#TimeVal").val()+
                    '&PASSWD='+""+
                    '&USER=<?php echo $OPID?>')
                ,
                type: 'POST',
                beforeSend: InsertWSST($('#sTraID').val(), 'A', JSON.stringify(json)),
                dataType: 'text',
                success: function (data) {
                    $("#loading").hide();
                    $("#wrapper").hide();
                    var str=AESDeCode(data);
                    console.log(str);
                    var dataObj=JSON.parse(str);
                    var result = dataObj.response;
                    var message = dataObj.message;
                    if (result == "success") {
                        alert("儲存成功");
                        window.location.reload(true);
                    }else {
                        alert(message);
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
                        AESEnCode("sFm=CNCD&PageVal="+""+"&DA_idpt="+
                            $('#DA_IdPt').val()+"&DA_idinpt="+$('#DA_InPt').val()+
                            "&sUser="+"<?php echo $OPID?>"+"&NM_PATIENT="+"")
                        ,"查詢",'width=750px,height=650px,scrollbars=yes,resizable=no');
                    break;
            }

            y.Serchcallback=Serchcallback;
        });
        $("#ReStart").click(function () {
            err.length=0;
            ErrIndex=0;
            $("input[type=text]:not(.Parametertable)").prop('disabled',false);
            $("button:not(#ReStart,#sbed)").prop('disabled',true);
            $("#Error_btn").css({"background-color":"#6c757d"});
            $("input[type=text]:not(#NURSOPID)").val("");
            $("#DATAList").children().remove();
        });
        $("#Del").click(function() {
            var del_ip='/webservice/NISPWSDELILSG.php';
            console.log('http://localhost'+del_ip+"?str="+AESEnCode("sFm="+'CNCD'+"&sTraID="+$('#sTraID').val()+"&sPg="+""+"&sCidFlag=D"+"&sUr=<?php echo $OPID?>"));
            $.ajax({
                url:del_ip+"?str="+AESEnCode("sFm="+'CNCD'+"&sTraID="+$('#sTraID').val()+"&sPg="+""+"&sCidFlag=D"+"&sUr=<?php echo $OPID?>"),
                type:'POST',
                dataType:'text',
                success:function (json) {
                    var data=JSON.parse(AESDeCode(json));
                    if(data.response==="false"){
                        alert('作廢失敗');
                        return false;
                    }
                    $('#DELMENU').prop('disabled',true);
                    $('#DELModal').modal('hide');
                    $("input[type=text]:not(.Parametertable)").prop('disabled',false);
                    $("button:not(#ReStart,#sbed)").prop('disabled',true);
                    $("input[type=text]:not(#NURSOPID)").val("");
                    $("#DATAList").children().remove();
                },error:function (XMLHttpResponse,textStatus,errorThrown) {
                    console.log(
                        "1 返回失敗,XMLHttpResponse.readyState:"+XMLHttpResponse.readyState+XMLHttpResponse.responseText+
                        "2 返回失敗,XMLHttpResponse.status:"+XMLHttpResponse.status+
                        "3 返回失敗,textStatus:"+textStatus+
                        "4 返回失敗,errorThrown:"+errorThrown
                    );
                }
            });
        });
        $("#ErorFocus").click(function () {
            if( $("#BedChange").length>0){
                $("#BedChange").remove();
            }
        });

        function DefaultData(idPt,INPt) {
            $("#loading").show();
            $("#wrapper").show();
            $("#DATAList").children().remove();
/*
            console.log("http://localhost"+"/webservice/NISPWSTRAINI.php?str="+AESEnCode('sFm=CNCD&idPt='+idPt+'&INPt='+INPt+"&sUr=<?php echo $OPID?>"));
*/
            $.ajax({
                url:"/webservice/NISPWSTRAINI.php?str="+AESEnCode('sFm=CNCD&idPt='+idPt+'&INPt='+INPt+"&sUr=<?php echo $OPID?>"),
                type:"POST",
                dataType: 'text',
                success:function (data) {
                    $("#loading").hide();
                    $("#wrapper").hide();
                    var objArr=JSON.parse(AESDeCode(data));
                    var TraObj=JSON.parse(objArr.shift());
                    if( $("#DATAList").children().length>0){
                        $("#DATAList").children().remove();
                    }

                    $.each(objArr,function (index,val){
                        $("#DATAList").append(
                            "<tr class='list-item'>"+
                                "<td>"+
                                "<input type='checkbox'  name='BDckbox' class='form-check-input' id='"+val.MEDNO+'@'+val.BARCODE+"' value='"+val.LOOKDT+"@"+val.MEDNO+"@"+$('#DA_InPt').val()+"@"+val.DIACODE+"@"+val.MACHINENO+"@"+val.WORKNO+"'>"+
                                "</td>"+
                                "<td  colspan='4' style='text-align:left;font-weight: bold'>"+"採血編號:"+" "+val.BARCODE+"</td>"+
                            "</tr>"+
                            "<tr>"+
                                "<td style='font-weight: bold'>"+"檢驗項目:"+"</td>"+
                                "<td colspan='4' style='text-align:left;'>"+(val.EGNAME).replaceAll(',',",<br>")+"</td>"+
                            "</tr>"
                        );
                    });

                    $("#sTraID").val(TraObj.sTraID);
                    $("#sSave").val(TraObj.sSave);
                    $("button:not(#DELMENU,#Error_btn)").prop('disabled',false);
                    $("input[type=text]").prop('disabled',false);
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
            $("#DataTxt").val(dataObj[0].DataTxt);
            $("#DA_IdPt").val(dataObj[0].IDPT);
            $("#DA_InPt").val(dataObj[0].IDINPT);
            $("#SBED").val(dataObj[0].SBED);
            DefaultData(dataObj[0].IDPT,dataObj[0].IDINPT);
            TimerDefault();
        }
        function Serchcallback(AESobj){
            var str1=AESDeCode(AESobj);
            var objArr=JSON.parse(str1);
            var TraObj=JSON.parse(objArr.shift());

            if( $("#DATAList").children().length>0){
                $("#DATAList").children().remove();
            }

               $.each(objArr,function (index,val) {
                   $("#DATAList").append(
                       "<tr class='list-item'>"+
                           "<td>"+
                           "<input type='checkbox'  name='BDckbox' class='form-check-input' id='"+val.MEDNO+'@'+val.BARCODE+"' value='"+val.LOOKDT+"@"+val.MEDNO+"@"+$('#DA_InPt').val()+"@"+val.DIACODE+"@"+val.MACHINENO+"@"+val.WORKNO+"'>"+
                           "</td>"+
                           "<td  colspan='4' style='text-align:left;font-weight: bold''>"+"採血編號:"+" "+val.BARCODE+"</td>"+
                       "</tr>"+
                       "<tr>"+
                           "<td style='font-weight: bold''>"+"檢驗項目:"+"</td>"+
                           "<td colspan='4' style='text-align:left;'>"+(val.EGNAME).replaceAll(',',",<br>")+"</td>"+
                       "</tr>"
                   );
               });
               $("#sTraID").val(TraObj.sTraID);
               $("#sSave").val(TraObj.sSave);
               $("#DateVal").val(objArr[0].EXECDATE);
               $("#TimeVal").val(objArr[0].EXECTIME);
               $('#DELMENU').prop('disabled',false);
               $("input[type=text]").prop("disabled",true);
               $("input[type=checkbox]").prop("checked",true);
               $("input[type=checkbox]").prop("disabled",true);
               $("button[type=submit]").prop("disabled",true);
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
        function InsertWSST(sTraID,page,json) {
            console.log("http://localhost"+'/webservice/NISPWSSETDATA.php?str='+AESEnCode('sFm=CNCD&sTraID='+sTraID+'&sPg='+page+'&sData='+json));
            $.ajax({
                'url':'/webservice/NISPWSSETDATA.php?str='+AESEnCode('sFm=CNCD&sTraID='+sTraID+'&sPg='+page+'&sData='+json),
                type:"POST",
                dataType:"text",
                success:function(data){
                    var json=JSON.parse(AESDeCode(data));
/*
                    console.log(json.message);
*/
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
                    var OBJ={
                        LOOKDT:'',
                        MEDNO:'',
                        IDINPT:'',
                        DIACODE:'',
                        MACHINENO:'',
                        WORKNO:''
                    };
                    //LOOKDT@MEDNO@IDINPT@DIACODE@MACHINENO@A@WORKNO
                    var DIACODEarr= str.split("@",6)[3].split(",");
                    var MACHINENOarr= str.split("@",6)[4].split(",");
                    var WORKNOarr= str.split("@",6)[5].split(",");
                   $.each(DIACODEarr,function (index) {
                       var DeepCopy={};
                       $.extend(true,OBJ,DeepCopy);
                       DeepCopy.LOOKDT= str.split("@",6)[0];
                       DeepCopy.MEDNO= str.split("@",6)[1];
                       DeepCopy.IDINPT= str.split("@",6)[2];
                       DeepCopy.DIACODE=DIACODEarr[index];
                       DeepCopy.MACHINENO= MACHINENOarr[index];
                       DeepCopy.WORKNO=WORKNOarr[index];
                       Json.push(DeepCopy);
                   });
                });
            }
            return Json;
        }
        function errorModal(str,AddEle) {
            if(AddEle){
                $("#BedChange").remove();
                $("#ErorFocus").before(
                    '<button type="button" id="BedChange" class="btn btn-primary" data-dismiss="modal">'+'確定'+'</button>'
                )
            }
            $("#ModalBody").children().remove("#ErrorText");
            $("#ErrorTitle").hide();
            $("#ErrBlood").hide();
            $("#ModalBody").append(
                '<p id="ErrorText" style="font-size: 2.5vmin;word-wrap: break-word">'+str+'</p>'
            );
            $('#Errormodal').modal('show');

        }
        function errUI(Arr){
            $("#Error_btn").css({"background-color":"#FF0000","border-color":"#FF0000"});
            $("#Error_btn").prop("disabled",false);
            $("#ErrBlood").children().remove();
            $.each(Arr,function (index,val) {
                $("#ErrBlood").append(
                    "<tr class='list-item'>"+
                    "<td>"+val.NUM+"</td>"+
                    "<td>"+val.IDPT+"</td>"+
                    "<td>"+val.BAR_CODE+"</td>"+
                    "<tr>"
                );
            });
        }
    });
</script>
