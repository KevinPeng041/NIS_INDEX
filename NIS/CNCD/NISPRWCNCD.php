<?php
include '../../NISPWSIFSCR.php';
$str=$_GET['str'];
$replaceSpace=str_replace(' ','+',$str);
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
$OPID=strtoupper(str_pad($sIdUser,7,"0",STR_PAD_LEFT));
/*$OPID="00FUZZY";*/
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>檢驗採檢辨識作業</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script type="text/javascript" src="../../jquery-3.4.1.js"></script>
    <link rel="stylesheet" href="../../bootstrap-4.3.1-dist/css/bootstrap.min.css">
    <script src="../../bootstrap-4.3.1-dist/js/bootstrap.min.js" type="text/javascript"></script>
    <script src="../../crypto-js.js"></script>
    <script src="../../AESCrypto.js"></script>
    <script src="../../NISCOMMAPI.js"></script>
    <link rel="stylesheet" href="../../css/NIS/CNCD.css">
    <script src="JS/NISJSCNCD.js"></script>
    <script>
        let sfm='<?php echo $sfm?>';
        if(sfm==""){
            let ckw=setInterval(function () {
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
        $(document).ready(function () {
            let x;
            let y;
            (function () {
                $(".Parametertable").children().prop('readonly',true);
                $("#loading").hide();
                $("#wrapper").hide();
            })();

            let err=[];
            let obj={
                IDPT:{},
                BAR_CODE:{},
                NUM:{}
            };
            let ErrIndex=0;
            let FocusIndex="";
            let InputIdArr=['DataTxt','DateVal','TimeVal','IdPt','NumId'];


            $(document).on('focus', 'input[type=text]', function() {
                let Index=InputIdArr.indexOf($(this).attr('id'));
                FocusIndex=Index+1;
                return false;
            });
            $(document).on('click', 'button', function() {
                let BtnID=$(this).attr('id');
                switch (BtnID) {
                    case "sbed":
                        switch (checkBEDwindow()) {
                            case "false":
                                alert("責任床位視窗已開啟");
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
                        break;
                    case "SerchBtn":
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
                        break;
                    case "ReStart":
                        err.length=0;
                        ErrIndex=0;
                        $("input[type=text]:not(.Parametertable)").prop('disabled',false);
                        $("button:not(#ReStart,#sbed)").prop('disabled',true);
                        $("#Error_btn").css({"background-color":"#6c757d"});
                        $("input[type=text]:not(#NURSOPID)").val("");
                        $("#DATAList").children().remove();
                        break;
                    case "Del":
                        let del_ip='/webservice/NISPWSDELILSG.php';
                        console.log("http://localhost"+del_ip+"?str="+AESEnCode("sFm="+'CNCD'+"&sTraID="+$('#sTraID').val()+"&sPg="+""+"&sCidFlag=D"+"&sUr=<?php echo $OPID?>"));
                        $.ajax({
                            url:del_ip+"?str="+AESEnCode("sFm="+'CNCD'+"&sTraID="+$('#sTraID').val()+"&sPg="+""+"&sCidFlag=D"+"&sUr=<?php echo $OPID?>"),
                            type:'POST',
                            dataType:'text',
                            success:function (json) {
                                let data=JSON.parse(AESDeCode(json));
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
                        break;
                    case "BedChange":
                        CallPatientData("<?php echo $OPID?>",$("#IdPt").val());
                        break;
                    case "Error_btn":
                        errorModal("",false);
                        break;
                }
            });
            $(document).on("keydown","input",function (e) {
                let focusID=$(this).attr('id');

                if(e.keyCode===13){
                    e.preventDefault();//prevent enter to submit
                    if($("input[type=text]:not(.noneEle)").is(":focus")===true)
                    {
                        if(FocusIndex>4){
                            if (focusID==="NumId")
                            {

                                CheckUIisset($("#IdPt").val(),$("#NumId").val());
                            }

                            $("#"+InputIdArr[FocusIndex-1]).focus();

                        }else {
                            if (focusID==="IdPt" && $("#DataTxt").val()===""){
                                CallPatientData("<?PHP echo $OPID?>",$("#IdPt").val());
                            }else if(focusID==="IdPt" && $("#DataTxt").val()!==""){
                                errorModal("是否要異動病人資料",true);
                            }


                            $("#"+InputIdArr[FocusIndex]).focus();
                        }


                    }
                    return false;
                }
            });

            $("#NumId").on('paste',function (e) {
                //prevent paste action
                e.preventDefault();
                let PasteTxt=e.originalEvent.clipboardData.getData('text');
                if( PasteTxt.lastIndexOf("#")>-1){
                    let Arr=PasteTxt.split("@");
                    let IdPt=Arr.shift();
                    Arr.pop();
                    /*if(IdPt!==$("#IdPt").val() && $("#IdPt").val()!==""){
                        alert("與此病人病歷號不符");
                        return false;
                    }*/

                    for (let index in Arr)
                    {
                        CheckUIisset(IdPt,Arr[index]);
                    }
                }
            });
            $("#form1").submit(function () {
                //$(window).off('beforeunload', reloadmsg);
                $("#loading").show();
                $("#wrapper").show();
                let json=GetCheckVal();
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
                        let str=AESDeCode(data);
                        let dataObj=JSON.parse(str);
                        let result = dataObj.response;
                        let message =dataObj.message;
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
            function CheckUIisset(IdPt,NumidStr){
                try {
                    let CheckBoxId=$("#"+IdPt+"\\@"+NumidStr);
                    if(CheckBoxId.length>0){
                        let top=(CheckBoxId.offset()).top-400;
                        CheckBoxId.prop('checked',true);
                        $("#scrollList").scrollTop(top);
                    }else {
                        ErrIndex++;
                        obj.IDPT=IdPt;
                        obj.BAR_CODE=NumidStr;
                        obj.NUM=ErrIndex;
                        let copy=Object.assign({},obj);//複製錯誤血袋
                        err.push(copy);
                        let errfilter=err.filter(function (element, index, arr) {
                            return arr.indexOf(element)===index;
                        });
                        errUI(errfilter);
                    }
                    $("#NumId").val("");
                }catch (e) {
                    console.log(e);
                    return false;
                }
            }
            function bedcallback(AESobj) {
                let str=AESDeCode(AESobj);
                let dataObj=JSON.parse(str);
                errRestar(err);
                $("#DataTxt").val(dataObj[0].DataTxt);
                $("#DA_IdPt").val(dataObj[0].IDPT);
                $("#DA_InPt").val(dataObj[0].IDINPT);
                $("#SBED").val(dataObj[0].SBED);
                DefaultData(dataObj[0].IDPT,dataObj[0].IDINPT,"<?php echo $OPID?>");
                TimerDefault();
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
            function CallPatientData(sUr,IDPT) {
                /*  console.log("http://localhost"+"/webservice/NISCNCDCALLBED.php?str="+AESEnCode("DA_idpt="+IDPT+"&sUr="+sUr));*/
                $.ajax({
                    url:"/webservice/NISCNCDCALLBED.php?str="+AESEnCode("DA_idpt="+IDPT+"&sUr="+sUr),
                    type:"POST",
                    dataType: 'text',
                    success:function (data) {
                        let NewBedJson=JSON.parse(AESDeCode(data));
                        if (NewBedJson.length<1){
                            alert("查無此病人");
                            $("#IdPt").focus();
                            return false;
                        }
                        $("#DataTxt").val(NewBedJson[0].DataTxt);
                        $("#DA_IdPt").val(NewBedJson[0].IDPT);
                        $("#DA_InPt").val(NewBedJson[0].IDINPT);
                        $("#SBED").val(NewBedJson[0].SBED);

                        DefaultData(NewBedJson[0].IDPT,NewBedJson[0].IDINPT,"<?php echo $OPID?>");
                        TimerDefault();
                        errRestar(err);
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
            function errRestar(err) {
                err.length=0;
                ErrIndex=0;
                $(".Num_input:not('#IdPt')").val("");
                $("#Error_btn").css({"background-color":"#6c757d"});
                $("#Error_btn").prop('disabled',true);
            }

        });
    </script>

</head>

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
    <form id="form1" >
        <div class="ListBtn">
            <button type="submit" id="SubmitBtn" class="btn btn-primary btn-md" disabled>儲存</button>
            <button type="button" id="SerchBtn" class="btn btn-primary btn-md" disabled>查詢</button>
            <button type="button" id="DELMENU" class="btn btn-primary btn-md"  data-toggle="modal" data-target="#DELModal" disabled>作廢</button>
            <button type="button" id="ReStart" class="btn btn-primary btn-md" >清除</button>
            <button type="button"  class="btn btn-warning btn-md" style="margin-left: 1px"   id="sbed" >責任床位</button>
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

        <div class="DataTable">
            <div>
                <input id="IdPt" class="Num_input" type="text" placeholder="輸入病歷號" maxlength="8" autocomplete="off">
                <input id="NumId" class="Num_input"  type="text" placeholder="輸入採血編號" autocomplete="off">
                <button id="Error_btn" type="button" class="btn btn-secondary btn-md Num_input"  style="margin-bottom: 15px;" disabled>錯誤查詢</button>
            </div>
            <div id="scrollList" data-spy="scroll" data-target="#navbar-example" data-offset="0" class="List" style="overflow:auto;">
                <table class="table" style="table-layout: fixed;text-align: center">
                    <tbody style=" font-size: 3.0vmin;" id="DATAList" >

                    </tbody>
                </table>
            </div>
        </div>
    </form>
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
                <button type="button" id="Del" class="btn btn-primary btn-md">作廢</button>
                <button type="button" class="btn btn-secondary btn-md" data-dismiss="modal">取消</button>
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
                <button type="button" id="BedChange" class="btn btn-primary" data-dismiss="modal" disabled>確定</button>
                <button type="button" id="ErorFocus" class="btn btn-secondary" data-dismiss="modal">關閉</button>
            </div>
        </div>
    </div>
</div>
</body>