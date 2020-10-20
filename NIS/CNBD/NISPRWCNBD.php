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
    <title>NISPRWCNBD</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script type="text/javascript" src="../../jquery-3.4.1.js"></script>
    <link rel="stylesheet" href="../../bootstrap-4.3.1-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../css/NIS/CNBD.css">
    <script src="../../bootstrap-4.3.1-dist/js/bootstrap.min.js" type="text/javascript"></script>
    <script src="../../crypto-js.js"></script>
    <script src="../../AESCrypto.js"></script>
    <script src="../../NISCOMMAPI.js"></script>
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

            (function () {
                TimerDefault();
                UIDefault();
                $("#loading").hide();
                $("#wrapper").hide();
            }());

            let err=[];
            let obj={
                IDPT:{},
                BSK_BAGENO:{},
                NUM:{}
            };
            let ScanTime=0;
            let x;//Bed addeventlistener
            let y;//Serch addeventlistener




            $("#IdPt").bind("input propertychange",function () {
                if(this.value.length==8)
                {
                    $("#NumId").focus();
                }
            });
            $('input[type=text]').keypress(function(e) {
                //enter sumbit return false
                let  code = e.keyCode ? e.keyCode : e.which;
                if(code === 13) {
                    e.preventDefault();
                }
            });
            $("#NumId").on('change paste keyup',function (event) {
                let  code = event.keyCode ? event.keyCode : event.which;
                let Numid=$(this).val();

                //HandKey and Enter,Scan
                if(code===13 && Numid.length===10){
                    if(CheckHasSpecialStr(Numid)===false){
                        alert("禁止輸入數字以外的值");
                        $(this).val("");
                        return  false;
                    }
                    CheckUI($("#IdPt").val(),Numid);
                }
                //paste
                else if( Numid.lastIndexOf("#")>-1)
                {
                    let Arr=Numid.split("@");
                    let IdPt=Arr.shift();
                    Arr.pop();
                    for (let index in Arr)
                    {
                        CheckUI(IdPt,Arr[index]);
                    }
                }
            });


            $(document).on('change', 'input[type=checkbox]', function() {
                let checkbox = $(this);

                if (checkbox.is(':checked')==true)
                {
                    checkbox.parent().parent().css({'background-color':'#BBFF00'});
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
                                errorModal("領血單位視窗已開啟");
                                break;
                            case "true":
                                try {
                                    x=window.open("/webservice/NISPRWCBED.php?str="+AESEnCode("sFm=CNBD&sIdUser=<?php echo $sIdUser?>"),"領血單位",'width=850px,height=650px,scrollbars=yes,resizable=no');

                                }catch (e) {
                                    console.log(e);
                                }
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
                                break;
                            case "true":
                                console.log($('#DataTxt').val());
                                y=window.open("/webservice/NISPWSLKQRY.php?str="+
                                    AESEnCode("sFm=CNBD&PageVal="+$('#BUT_NEEDUNIT').val()+"&DA_idpt="+
                                        ""+"&DA_idinpt="+""+
                                        "&sUser="+"<?php echo  $OPID?>"+"&NM_PATIENT="+$('#DataTxt').val())
                                    ,"查詢",'width=750px,height=650px,scrollbars=yes,resizable=no');
                                break;
                        }

                        y.Serchcallback=Serchcallback;
                        break;
                    case "Del":
                        let del_ip='/webservice/NISPWSDELILSG.php';
                        console.log('http://localhost'+del_ip+"?str="+AESEnCode("sFm="+'CNBD'+"&sTraID="+$('#sTraID').val()+"&sPg="+""+"&sCidFlag=D"+"&sUr=<?php echo $OPID?>"));
                        $.ajax({
                            url:del_ip+"?str="+AESEnCode("sFm="+'CNBD'+"&sTraID="+$('#sTraID').val()+"&sPg="+""+"&sCidFlag=D"+"&sUr=<?php echo $OPID?>"),
                            type:'POST',
                            dataType:'text',
                            success:function (json) {
                                let data=JSON.parse(AESDeCode(json));
                                console.log(data);
                                if(data.message=='false'){
                                    errorModal('作廢失敗');
                                    return false;
                                }else {
                                    $('#DELModal').modal('hide');
                                    restUI();
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
                    case "restBtn":

                        restUI();
                        break;
                    case "Error_btn":
                        $('#Errormodal').modal('show');
                        break;
                }
            });
            $("#form1").submit(function () {
                //$(window).off('beforeunload', reloadmsg);
                let json=GetCheckVal();

                $("#loading").show();
                $("#wrapper").show();

                console.log('http://localhost/webservice/NISPWSSAVEILSG.php?str='+ AESEnCode('sFm=' + 'CNBD' +
                    '&sTraID=' + $('#sTraID').val() +
                    '&sPg=' + $("#PageVal").val() +
                    '&sDt=' + $("#DateVal").val() +
                    '&sTm=' + $("#TimeVal").val()+
                    '&PASSWD='+""+
                    '&USER='+"<?php echo $OPID?>"));

                $.ajax({
                    url: '/webservice/NISPWSSAVEILSG.php?str=' + AESEnCode('sFm=' + 'CNBD' +
                        '&sTraID=' + $('#sTraID').val() +
                        '&sPg=' + $("#PageVal").val() +
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
                        let str=AESDeCode(data);
                        let dataObj=JSON.parse(str);
                        let result = dataObj.response;
                        let message = dataObj.message;
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
                        return false;
                    }
                });
                return false;

            });
            function CheckUI(IdPt,ScanNum){
                ScanTime++;
                if( $("#"+IdPt+"\\@"+ScanNum).length>0){
                    let top=($("#"+IdPt+"\\@"+ScanNum).offset()).top-400;
                    $("#"+IdPt+"\\@"+ScanNum).prop('checked',true);
                    $("#"+IdPt+"\\@"+ScanNum).parent().parent().css({'background-color':'#BBFF00'});
                    $("#scrollList").scrollTop(top);
                }else {
                    obj.IDPT=IdPt;
                    obj.BSK_BAGENO=ScanNum;
                    obj.NUM=ScanTime;
                    let copy=Object.assign({},obj);//淺複製錯誤血袋
                    err.push(copy);
                }
                if(err.length>0){
                    errUI(err);
                }
                $("#IdPt").focus();
                $("#IdPt").val("");
                $("#NumId").val("");
            }
            function TableList(BUT_NEEDUNIT) {
                /*
                            console.log("http://localhost"+'/webservice/NISPWSGETPRE.PHP?str='+AESEnCode('sFm='+"CNBD"+'&sTraID='+''+'&sPg='+BUT_NEEDUNIT));
                */
                $.ajax({
                    url:'/webservice/NISPWSGETPRE.PHP?str='+AESEnCode('sFm='+"CNBD"+'&sTraID='+''+'&sPg='+BUT_NEEDUNIT),
                    type:"POST",
                    dataType:"text",
                    success:function (data) {
                        /* console.log(data);*/
                        let sTraID=$("#sTraID").val();
                        InsertWSST(sTraID,'B',data);

                        let arr=JSON.parse(AESDeCode(data));
                        if(arr.length==0){
                          /*  $("#DATAList").append(
                                "<tr class='list-item'>"+
                                "<td>"+"查無資料"+"</td>"+
                                "</tr>"

                            );*/
                            $("#DATAList").append(
                                `
                                <tr class='list-item'>
                                     <td>查無資料</td>
                                </tr>
                                `
                            );


                            return false;
                        }

                        $.each(arr,function (index,val) {
                            /*checkbod id:病歷號+血袋號碼 value:領血請領單位+病歷號+血袋號碼*/
                            let   BSK_BAGENO=val.BSK_BAGENO;
                            let  BSK_MEDNO=val.BSK_MEDNO;
                            let  MH_NAME=val.MH_NAME;
                            let  BKD_EGCODE=val.BKD_EGCODE;

                            let CheckID=BSK_MEDNO+'@'+BSK_BAGENO;
                            let CheckBoxVal= BUT_NEEDUNIT+"@"+BSK_MEDNO+"@"+BSK_BAGENO;

                            $("#DATAList").append(
                                `
                                <tr class='list-item'>
                                    <td><input type='checkbox'  name='BDckbox' class='form-check-input' id='${CheckID}' value='${CheckBoxVal}'></td>
                                    <td>${BSK_MEDNO}</td>
                                    <td>${MH_NAME}</td>
                                    <td>${BKD_EGCODE}</td>
                                    <td>${BSK_BAGENO}</td>
                                </tr>
                                `
                            );


                   /*
                            $("#DATAList").append(
                                "<tr class='list-item'>"+
                                "<td>"+"<input type='checkbox'  name='BDckbox' class='form-check-input' id='"+BSK_MEDNO+'@'+BSK_BAGENO+"' value='"+BUT_NEEDUNIT+"@"+BSK_MEDNO+"@"+BSK_BAGENO+"'>"+"</td>"+
                                "<td>"+BSK_MEDNO+"</td>"+
                                "<td>"+MH_NAME+"</td>"+
                                "<td>"+BKD_EGCODE+"</td>"+
                                "<td>"+BSK_BAGENO+"</td>"+
                                "</tr>"
                            );*/

                        });
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
            function InsertWSST(sTraID,page,json) {
                /*
                console.log("http://localhost"+'/webservice/NISPWSSETDATA.php?str='+AESEnCode('sFm=CBLD&sTraID='+sTraID+'&sPg='+page+'&sData='+json));
                 */
                $.ajax({
                    'url':'/webservice/NISPWSSETDATA.php?str='+AESEnCode('sFm=CNBD&sTraID='+sTraID+'&sPg='+page+'&sData='+json),
                    type:"POST",
                    dataType:"text",
                    success:function(data){
                        let json=JSON.parse(AESDeCode(data));
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
                let str1=AESDeCode(AESobj);
                let objArr=JSON.parse(str1);
                $("#DATAList").children().remove();
                let  BSK_NURSDATE='';
                let  BSK_NURSTIME='';
                let  sTraID='';

                $.each(objArr,function (index,val) {
                    let   BSK_BAGENO=val.BSK_BAGENO;
                    let  BSK_MEDNO=val.BSK_MEDNO;
                    let  BKD_EGCODE=val.BKD_EGCODE;
                    let  BSK_NEEDUNIT=val.BSK_NEEDUNIT;
                    let MH_NAME=val.MH_NAME;
                    let  BSK_BARSIGN=val.BSK_BARSIGN;
                    let  BSK_BLDKIND=val.BSK_BLDKIND;
                    let BSK_INDENTNO=val.BSK_INDENTNO;
                    let BSK_NURSOPID=val.BSK_NURSOPID;
                    let  BSK_TRANSRECNO=val.BSK_TRANSRECNO;
                    let CheckBoxVal=BSK_NEEDUNIT+"@"+BSK_MEDNO+"@"+BSK_BAGENO+"@"+BSK_BLDKIND+"@"+BSK_INDENTNO+"@"+BSK_NURSOPID+"@"+BSK_TRANSRECNO+"@"+BSK_BARSIGN
                    let CheckID=BSK_MEDNO+'@'+BSK_BAGENO

                    sTraID=val.sTraID;
                    BSK_NURSDATE=val.BSK_NURSDATE;
                    BSK_NURSTIME=val.BSK_NURSTIME;
/*
                    $("#DATAList").append(
                        "<tr class='list-item'>"+
                        "<td>"+"<input type='checkbox'  name='BDckbox' class='form-check-input' id='"+BSK_MEDNO+'@'+BSK_BAGENO+"' " +
                        "value='"+BSK_NEEDUNIT+"@"+BSK_MEDNO+"@"+BSK_BAGENO+"@"+BSK_BLDKIND+"@"+BSK_INDENTNO+"@"+BSK_NURSOPID+"@"+BSK_TRANSRECNO+"@"+BSK_BARSIGN+"'>"+
                        "</td>"+
                        "<td>"+BSK_MEDNO+"</td>"+
                        "<td>"+MH_NAME+"</td>"+
                        "<td>"+BKD_EGCODE+"</td>"+
                        "<td>"+BSK_BAGENO+"</td>"+
                        "</tr>"
                    );*/
                    $("#DATAList").append(
                        `<tr class='list-item'>
                            <td><input type='checkbox'  name='BDckbox' class='form-check-input' id='${CheckID}'  value='${CheckBoxVal}'></td>
                            <td>${BSK_MEDNO}</td>
                            <td>${MH_NAME}</td>
                            <td>${BKD_EGCODE}</td>
                            <td>${BSK_BAGENO}</td>
                        </tr>
                        `
                    )
                });
                $("#sTraID").val(sTraID);
                $("#DateVal").val(BSK_NURSDATE);
                $("#TimeVal").val(BSK_NURSTIME);
                $("input[type=text]").prop("disabled",true);
                $("input[type=checkbox]").prop("checked",true);
                $("input[type=checkbox]").prop("disabled",true);
                $("button[type=submit]").prop("disabled",true);
                $("#DELMENU").prop("disabled",false);
                $('#DateVal').prop('readonly',true);
                $('#TimeVal').prop('readonly',true);
            }
            function bedcallback(data){
                let str=AESDeCode(data);
                let dataObj=JSON.parse(str);
                console.log(dataObj);
                let  BUT_BUTNAME=dataObj.BUT_BUTNAME;
                let BUT_PROCDATE=dataObj.BUT_PROCDATE;
                let BUT_NEEDUNIT=dataObj.BUT_NEEDUNIT;
                let BUT_PROCOPID=dataObj.BUT_PROCOPID;
                let  BUT_PROCTIME=dataObj.BUT_PROCTIME;
                let   sSave=dataObj.sSave;
                let   sTraID=dataObj.sTraID;
                if($("#DATAList").children()){
                    $("#DATAList").children().remove();
                }
                if($("#DateVal").val()==""||$("#TimeVal").val()==""){
                    TimerDefault();
                }

                $("#DataTxt").val(BUT_BUTNAME.trim());
                $("#BUT_PROCDATE").val(BUT_PROCDATE);
                $("#BUT_NEEDUNIT").val(BUT_NEEDUNIT);
                $("#BUT_PROCOPID").val(BUT_PROCOPID);
                $("#BUT_PROCTIME").val(BUT_PROCTIME);
                $("#sSave").val(sSave);
                $("#sTraID").val(sTraID);
                InsertWSST(sTraID,'A',data);
                TableList(BUT_NEEDUNIT);
                $("button[type=submit]").prop("disabled",false);
                $("#SerchBtn").prop("disabled",false);
                $("#IdPt").prop("disabled",false);
                $("#ErrBlood").children().remove();
                $("#Error_btn").prop("disabled",true);
                $("#Error_btn").css({"background-color":"#6c757d","border-color":"#6c757d"});
            }
            function GetCheckVal() {
                //取checkbox的值
                let cbxVehicle = new Array();
                let Json=[];
                $("input:checkbox:checked[name=BDckbox]").each(function (i) {
                    cbxVehicle[i]=$(this).val();
                });
                /*01349277@0076126888*/
                if(cbxVehicle.length>0){
                    $.each(cbxVehicle,function (index) {
                        /* BSK_BAGENO,BSK_BLDKIND,BSK_MEDNO,BSK_NEEDUNIT,BSK_NURSDATE,BSK_NURSTIME,BSK_NURSOPID,BSK_INDENTNO,BSK_TRANSRECNO,BSK_BARSIGN*/
                        let str=cbxVehicle[index];
                        let OBJ=new Object();
                        OBJ.BUT_NEEDUNIT= str.split("@",8)[0];
                        OBJ.BSK_MEDNO= str.split("@",8)[1];
                        OBJ.BSK_BAGENO= str.split("@",8)[2];
                        Json.push(OBJ);
                    });
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
            function errorModal(str) {
                $("#ModalBody table").hide();//隱藏血袋錯誤訊息

                $("#ModalBody").append(
                    `
                         <p id="ErrorText" style="font-size: 2.5vmin;word-wrap: break-word">${str}</p>
                    `


                );



                $('#Errormodal').modal('show');

            }
            function TimerDefault() {
                let TimeNow=new Date();
                let yyyy=TimeNow.toLocaleDateString().slice(0,4);
                let MM=(TimeNow.getMonth()+1<10?'0':'')+(TimeNow.getMonth()+1);
                let dd=(TimeNow.getDate()<10?'0':'')+TimeNow.getDate();
                let  h=(TimeNow.getHours()<10?'0':'')+TimeNow.getHours();
                let  m=(TimeNow.getMinutes()<10?'0':'')+TimeNow.getMinutes();
                let  s=(TimeNow.getSeconds()<10?'0':'')+TimeNow.getSeconds();
                $("#DateVal").val(yyyy-1911+MM+dd);
                $("#TimeVal").val(h+m);
            }
            function UIDefault() {
                $("#DELMENU").prop("disabled",true);
                $("#SerchBtn").prop("disabled",true);
                $("#SubmitBtn").prop("disabled",true);
            }
            function restUI(){
                ScanTime=0;
                err.length=0;
                $("form input[type=text]").val("");
                $("input:not(#NURSOPID)").val("");
                $("button").prop("disabled",false);
                $("#DATAList").children().remove();
                $("#SubmitBtn").prop("disabled",true);
                $("#SerchBtn").prop("disabled",true);
                $("#DELMENU").prop("disabled",true);
                $("#ErrBlood").children().remove();
                $("#Error_btn").prop("disabled",true);
                $("#Error_btn").css({"background-color":"#6c757d","border-color":"#6c757d"});
            }
            function errUI(err){
                $("#Error_btn").css({"background-color":"#FF0000","border-color":"#FF0000"});
                $("#Error_btn").prop("disabled",false);
                $("#ErrBlood").children().remove();
                $.each(err,function (index,val) {

                    let NUM=val.NUM;
                    let IDPT=val.IDPT;
                    let BSK_BAGENO=val.BSK_BAGENO;
                    $("#ErrBlood").append(
                       `
                       <tr class='list-item'>
                          <td>${NUM}</td>
                          <td>${IDPT}</td>
                          <td>${BSK_BAGENO}</td>
                       </tr>
                       `
                    );


                 /*   $("#ErrBlood").append(
                        "<tr class='list-item'>"+
                        "<td>"+val.NUM+"</td>"+
                        "<td>"+val.IDPT+"</td>"+
                        "<td>"+val.BSK_BAGENO+"</td>"+
                        "<tr>"
                    );*/
                });
            }
            function CheckHasSpecialStr(val){
                let strReg=/^[0-9]*$/;
                return  val.match(strReg)==null?false:true;

            }
        });

    </script>
</head>

<body>
<div id="wrapper"></div>
<div id="loading" >請稍後<img class="loadimg" src="../../dotloading.gif"></div>
<div class="Parametertable">
    <input id="BUT_NEEDUNIT" type="text" value="" placeholder="BUT_NEEDUNIT">
    <input id="BUT_PROCDATE" type="text" value="" placeholder="BUT_PROCDATE">
    <input id="BUT_PROCOPID" type="text" value="" placeholder="BUT_PROCOPID">
    <input id="BUT_PROCTIME" type="text" value="" placeholder="BUT_PROCTIME">
    <input id="sSave" type="text" value="" placeholder="sSave">
    <input id="sTraID" type="text" value="" placeholder="sTraID">
    <input id="NURSOPID" type="text" value="<?php echo $OPID?>" placeholder="NURSOPID">
</div>
<div class="container">
    <h2>領用血簽收單作業</h2>
    <form id="form1">
         <span class="ListBtn" style="margin-left:0 px">
             <button type="button" class="btn btn-secondary btn-md" disabled style="display: none">回主畫面</button>
             <!-- <button type="button"  class="btn btn-warning btn-md" style="margin-left: 1px"   id="sbed" >責任床位</button><span style="margin-left: 1px">-->
             <button type="button"  class="btn btn-warning btn-md" style="margin-left: 1px"   id="sbed" >領血單位</button><span style="margin-left: 1px"></span>
        </span>
        <span class="ListBtn float-left">
            <button type="submit" id="SubmitBtn" class="btn btn-primary btn-md" >儲存</button>
            <button type="button" id="SerchBtn" class="btn btn-primary btn-md" >查詢</button>
            <button type="button" id="DELMENU" class="btn btn-primary btn-md"  data-toggle="modal" data-target="#DELModal">作廢</button>
            <button type="reset" class="btn btn-primary btn-md" id="restBtn">清除</button>
            <button type="button" class="btn btn-secondary btn-md" disabled style="margin-right: 3px ;display: none">預設</button>
        </span>
        <div class="input-group">
            <input id="DataTxt"  value="" type="text" readonly="readonly" style="font-size: 4vmin;width:100vmin;">
        </div>


        <div class="Otimer" >
            <div class="pageTime">
                <label style="font-size: 4vmin">評估時間:</label>
                <input  type="text" id="DateVal" value=""  placeholder="YYYMMDD" maxlength="7" autocomplete="off">
                <input type="text" id="TimeVal" value="" placeholder="HHMM" maxlength="4" autocomplete="off">
            </div>
        </div>

        <div>
            <div>
                <input id="IdPt" class="Num_input" type="text" placeholder="輸入病歷號" maxlength="8" autocomplete="off">
                <input id="NumId" class="Num_input"  type="text" placeholder="輸入血袋號碼" autocomplete="off">
                <button id="Error_btn" type="button" class="btn btn-secondary btn-md Num_input"  style="margin-bottom: 15px;" disabled>錯誤查詢</button>
            </div>
            <table class="table" style="table-layout: fixed;text-align: center;margin-bottom: 0rem;">
                <thead  class="theadtitle" style=" font-size: 3.5vmin;">
                <th style=" padding-bottom: 5px !important"></th>
                <th style=" padding-bottom: 5px !important">病歷號</th>
                <th style=" padding-bottom: 5px !important">姓名</th>
                <th style="text-align: center ;padding-bottom: 5px !important">血品</th>
                <th style="text-align: center ;padding-bottom: 5px !important">血袋號碼</th>
                </thead>
            </table>
            <div id="scrollList" data-spy="scroll" data-target="#navbar-example" data-offset="0" class="List">
                <table class="table" style="table-layout: fixed;text-align: center">
                    <tbody style=" font-size: 3.5vmin;" id="DATAList">

                    </tbody>
                </table>
            </div>
        </div>
    </form>


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
</body>
</html>
