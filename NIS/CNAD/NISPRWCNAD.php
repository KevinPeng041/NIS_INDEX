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
    <link rel="stylesheet" href="../../css/NIS/CNAD.css">
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
                DefaultData();
                $("#loading").hide();
                $("#wrapper").hide();
            }());

            let x;
            let y;
            let err=[];
            let obj={
                IDPT:{},
                BSK_BAGENO:{},
                NUM:{}
            };
            let ScanTime=0;
            $(".Parametertable").children().prop('readonly',true);

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
                                    x=window.open("/webservice/NISPRWCBED.php?str="+AESEnCode("sFm=CNAD&sIdUser=<?php echo $OPID?>"),"輸血單位",'width=850px,height=650px,scrollbars=yes,resizable=no');

                                }catch (e) {
                                    errorModal(e);
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
                                    AESEnCode("sFm=CNAD&PageVal="+""+"&DA_idpt="+
                                        $('#DA_IdPt').val()+"&DA_idinpt="+""+
                                        "&sUser="+"<?php echo $OPID?>"+"&NM_PATIENT="+"")
                                    ,"查詢",'width=750px,height=650px,scrollbars=yes,resizable=no');
                                break;
                        }

                        y.Serchcallback=Serchcallback;
                        break;
                    case "ReStart":
                        err.length=0;
                        ScanTime=0;
                        DefaultData();
                        $('input[type=text]:not("#NURSOPID")').val("");
                        $('button[type=submit]').prop('disabled',false);
                        $("#Error_btn").css({"background-color":"#6c757d","border-color":"#6c757d"});
                        $("#Error_btn").prop("disabled",true);
                        $('#DELMENU').prop('disabled',true);
                        $('#IdPt').focus();

                        break;
                    case "Del":
                        let del_ip='/webservice/NISPWSDELILSG.php';
                        console.log('http://localhost'+del_ip+"?str="+AESEnCode("sFm="+'CNAD'+"&sTraID="+$('#sTraID').val()+"&sPg="+""+"&sCidFlag=D"+"&sUr=<?php echo $OPID?>"));
                        $.ajax({
                            url:del_ip+"?str="+AESEnCode("sFm="+'CNAD'+"&sTraID="+$('#sTraID').val()+"&sPg="+""+"&sCidFlag=D"+"&sUr=<?php echo $OPID?>"),
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
                                    DefaultData();
                                    $("#DELMENU").prop('disabled',true);
                                    $("#SubmitBtn").prop('disabled',false);
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
                    case "Error_btn":
                        $('#Errormodal').modal('show');
                        break;
                }
            });


            $('input[type=text]').keypress(function(e) {
                //enter sumbit return false
              let  code = e.keyCode ? e.keyCode : e.which;
                if(code === 13) {
                    e.preventDefault();
                }
            });

            $("#IdPt").bind("input propertychange",function () {
                if(this.value.length==8)
                {
                    $("#NumId").focus();
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




            $("#form1").submit(function () {
                //$(window).off('beforeunload', reloadmsg);
                let json=GetCheckVal();
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

                        let str=AESDeCode(data);
                        console.log(str);
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
            function DefaultData() {
                $("#loading").show();
                $("#wrapper").show();
                $("#DATAList").children().remove();
                console.log("http://localhost"+"/webservice/NISPWSTRAINI.php?str="+AESEnCode('sFm=CNAD&idPt='+"00055664"+'&INPt='+"970000884"+'&sUr=00FUZZY'));
                $.ajax({
                    url:"/webservice/NISPWSTRAINI.php?str="+AESEnCode('sFm=CNAD&idPt='+"00055664"+'&INPt='+"970000884"+'&sUr=<?php echo $OPID?>'),
                    type:"POST",
                    dataType: 'text',
                    success:function (data) {
                        $("#loading").hide();
                        $("#wrapper").hide();
                        let ArrJson=JSON.parse(AESDeCode(data));
                        $.each(ArrJson,function (index,val) {
                            let   BCK_DATMSEQ=val.BCK_DATMSEQ;
                            let   BSK_BAGENO=val.BSK_BAGENO;
                            let  BSK_MEDNO=val.BSK_MEDNO;
                            let  MH_NAME=val.MH_NAME;
                            let  BKD_EGCODE=val.BKD_EGCODE;
                            let BSK_TRANSRECNO=val.BSK_TRANSRECNO;
                            let sTraID=val.sTraID;
                            let sSave=val.sSave;
                            let CheckBoxVal=BCK_DATMSEQ+"@"+BSK_TRANSRECNO+"@"+BSK_MEDNO+"@"+BSK_BAGENO;
                            let CheckID=BSK_MEDNO+'@'+BSK_BAGENO;
                            $("#sTraID").val(sTraID);
                            $("#sSave").val(sSave);

                            $("#DATAList").append
                            (
                                `
                                <tr class='list-item'>
                                <td><input type='checkbox'  name='BDckbox' class='form-check-input' id='${CheckID}' value='${CheckBoxVal}'></td>
                                <td>${BSK_TRANSRECNO}</td>
                                <td>${BSK_MEDNO}</td>
                                <td>${MH_NAME}</td>
                                <td>${BKD_EGCODE}</td>
                                <td>${BSK_BAGENO}</td>
                                </tr>
                                `
                            );






                       /*     $("#DATAList").append
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
*/
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
                let str=AESDeCode(data);
                let dataObj=JSON.parse(str);
                console.log(dataObj);
                let   BSK_ALLOWDATE=dataObj.BSK_ALLOWDATE;
                let   BSK_ALLOWTIME=dataObj.BSK_ALLOWTIME;
                let   sSave=dataObj.sSave;
                let   sTraID=dataObj.sTraID;
                let   MH_MEDNO=dataObj.MH_MEDNO;
                $("#DATAList").children().remove();
                $("#sSave").val(sSave);
                $("#sTraID").val(sTraID);
                $("#DA_IdPt").val(MH_MEDNO);
                TableList(dataObj.BSK_TRANSRECNO,sTraID);
                $('input[type=submit]').prop('disabled',false);
            }
            function Serchcallback(AESobj)
            {
                let str1=AESDeCode(AESobj);
                let objArr=JSON.parse(str1);
                $("#DATAList").children().remove();
                $.each(objArr,function (index,val) {
                    let   BCK_DATMSEQ=val.BCK_DATMSEQ;
                    let   BSK_BAGENO=val.BSK_BAGENO;
                    let  BSK_MEDNO=val.BSK_MEDNO;
                    let  MH_NAME=val.MH_NAME;
                    let  BKD_EGCODE=val.BKD_EGCODE;
                    let BSK_TRANSRECNO=val.BSK_TRANSRECNO;
                    let sTraID=val.sTraID;
                    let sSave=val.sSave;
                    let CheckID=BSK_MEDNO+'@'+BSK_BAGENO;
                    let CheckBoxVal=BCK_DATMSEQ+"@"+BSK_TRANSRECNO+"@"+BSK_MEDNO+"@"+BSK_BAGENO

                    $("#sTraID").val(sTraID);
                    $("#sSave").val(sSave);
                    $("#DateVal").val(val.BCK_OUTDATE);
                    $("#TimeVal").val(val.BCK_OUTTIME);
                   $("#DATAList").append
                    (
                        `
                        <tr class='list-item'>
                            <td><input type='checkbox'  name='BDckbox' class='form-check-input' id='${CheckID}' value='${CheckBoxVal}'></td>
                            <td>${BSK_TRANSRECNO}</td>
                            <td>${BSK_MEDNO}</td>
                            <td>${MH_NAME}</td>
                            <td>${BKD_EGCODE}</td>
                            <td>${BSK_BAGENO}</td>
                        </tr>
                        `
                    );

               /*    $("#DATAList").append
                    (
                        "<tr class='list-item'>"+
                        "<td>"+"<input type='checkbox'  name='BDckbox' class='form-check-input' id='"+BSK_MEDNO+'@'+BSK_BAGENO+"' value='"+BCK_DATMSEQ+"@"+BSK_TRANSRECNO+"@"+BSK_MEDNO+"@"+BSK_BAGENO+"'>"+"</td>"+
                        "<td >"+BSK_TRANSRECNO+"</td>"+
                        "<td>"+BSK_MEDNO+"</td>"+
                        "<td>"+MH_NAME+"</td>"+
                        "<td>"+BKD_EGCODE+"</td>"+
                        "<td>"+BSK_BAGENO+"</td>"+
                        "</tr>"
                    );*/
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
                        let arr=JSON.parse(AESDeCode(data));
                        InsertWSST(sTraID,'B',AESDeCode(data));
                        console.log(arr);
                        if(arr.length==0){
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
                            let   BCK_DATMSEQ=val.BCK_DATMSEQ;
                            let   BSK_BAGENO=val.BSK_BAGENO;
                            let  BSK_MEDNO=val.BSK_MEDNO;
                            let  MH_NAME=val.MH_NAME;
                            let  BKD_EGCODE=val.BKD_EGCODE;
                            let  BSK_TRANSRECNO=val.BSK_TRANSRECNO;
                            let  CheckID=BSK_MEDNO+'@'+BSK_BAGENO;
                            let  CheckBoxVal=BCK_DATMSEQ+"@"+BSK_TRANSRECNO+"@"+BSK_MEDNO+"@"+BSK_BAGENO;
                            $("#DATAList").append(
                                `
                                <tr class='list-item'>
                                    <td> <input type='checkbox'  name='BDckbox' class='form-check-input' id='${CheckID}' value='${CheckBoxVal}'></td>
                                    <td>${BSK_TRANSRECNO}</td>
                                    <td>${BSK_MEDNO}</td>
                                    <td>${MH_NAME}</td>
                                    <td>${BKD_EGCODE}</td>
                                    <td>${BSK_BAGENO}</td>
                                </tr>
                                `

                            );

                        /*    $("#DATAList").append(
                                "<tr class='list-item'>"+
                                "<td>"+"<input type='checkbox'  name='BDckbox' class='form-check-input' id='"+BSK_MEDNO+'@'+BSK_BAGENO+"' value='"+BCK_DATMSEQ+"@"+BSK_TRANSRECNO+"@"+BSK_MEDNO+"@"+BSK_BAGENO+"'>"+"</td>"+
                                "<td>"+BSK_TRANSRECNO+"</td>"+
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
            function GetCheckVal() {
                //取checkbox的值
                let cbxVehicle = new Array();
                let Json=[];
                $("input:checkbox:checked[name=BDckbox]").each(function (i) {
                    cbxVehicle[i]=$(this).val();
                });

                if(cbxVehicle.length>0){
                    $.each(cbxVehicle,function (index) {
                        let str=cbxVehicle[index];
                        let OBJ=new Object();
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
                    `
                        <p id="ErrorText" style="font-size: 2.5vmin;word-wrap: break-word">${str}</p>
                    `
                );
                $('#Errormodal').modal('show');
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
<div class="Parametertable" >
    <input id="sSave" type="text" value="" placeholder="sSave" >
    <input id="sTraID" type="text" value="" placeholder="sTraID">
    <input id="DA_IdPt" type="text" value="" placeholder="DA_IdPt">
    <input id="NURSOPID" type="text" value="<?php echo $OPID?>" placeholder="NURSOPID">
</div>

<div class="container">
    <h2>發血覆核作業</h2>
    <form id="form1">
        <div class="ListBtn">
            <button type="submit" id="SubmitBtn" class="btn btn-primary btn-md" >儲存</button>
            <button type="button" id="SerchBtn" class="btn btn-primary btn-md">查詢</button>
            <button type="button" id="DELMENU" class="btn btn-primary btn-md"  data-toggle="modal" data-target="#DELModal" disabled>作廢</button>
            <button type="button" id="ReStart" class="btn btn-primary btn-md" >重整</button>
            <button type="button"  class="btn btn-warning btn-md" style="margin-left: 1px"   id="sbed" >輸血紀錄單</button><span style="margin-left: 1px">
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
                <input id="NumId" class="Num_input"  type="text" placeholder="輸入血袋號碼" autocomplete="off">
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
                    <tbody style=" font-size: 3.0vmin;" id="DATAList">

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
