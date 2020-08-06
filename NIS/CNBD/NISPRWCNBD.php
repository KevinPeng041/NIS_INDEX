<?php
$OPID="00FUZZY"
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>NISPRWCNBD</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script type="text/javascript" src="../../jquery-3.4.1.js"></script>
    <link rel="stylesheet" href="../../bootstrap-4.3.1-dist/css/bootstrap.min.css">
    <!--<link rel="stylesheet" href="../../css/NIS/CBLD.css">-->
    <script src="../../bootstrap-4.3.1-dist/js/bootstrap.min.js" type="text/javascript"></script>
    <script src="../../crypto-js.js"></script>
    <script src="../../AESCrypto.js"></script>
    <script src="../../NISCOMMAPI.js"></script>
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
            height:300px;overflow:auto;
            position: relative;

        }
        .btn {
            margin-top: 5px;
        }
        div{
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
    </style>
</head>

<body>
<div class="Parametertable">
    <input id="BUT_NEEDUNIT" type="text" value="" placeholder="BUT_NEEDUNIT">
    <input id="BUT_PROCDATE" type="text" value="" placeholder="BUT_PROCDATE">
    <input id="BUT_PROCOPID" type="text" value="" placeholder="BUT_PROCOPID">
    <input id="BUT_PROCTIME" type="text" value="" placeholder="BUT_PROCTIME">
    <input id="sSave" type="text" value="" placeholder="sSave">
    <input id="sTraID" type="text" value="" placeholder="sTraID">
    <input id="NURSOPID" type="text" value="<?php echo $OPID?>" placeholder="NURSOPID">
</div>
<div id="wrapper"></div>
<div id="loading" >請稍後<img class="loadimg" src="../../dotloading.gif"></div>
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
            <div class="Num_input">
                <input id="IdPt" type="text" placeholder="輸入病歷號" maxlength="8">
                <input id="NumId" type="text" placeholder="輸入血袋號碼" maxlength="10" disabled>
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
            <div data-spy="scroll" data-target="#navbar-example" data-offset="0" class="List">
                <table class="table" style="table-layout: fixed;text-align: center">
                    <tbody style=" font-size: 3.5vmin;" id="DATAList">

                    </tbody>
                </table>
            </div>
        </div>
    </form>
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
                    <button type="button" id="Del" class="btn btn-primary btn-md">作廢</button>
                    <button type="button" class="btn btn-secondary btn-md" data-dismiss="modal">取消</button>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
<script>
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

    $(document).ready(function () {

        TimerDefault();
        UIDefault();
        var a=[];
        var ckNum=false;
       $("#IdPt").bind("input propertychange",function () {
           var idpt=$(this).val();
           if(idpt.length==8){
               $("input[type=checkbox]").each(function (i) {
                   var idVal=$(this).attr("id");
                   var CheckboxId=idVal.split("@");
                   if(CheckboxId.indexOf(idpt) > -1 ){
                       console.log(idVal);
                       ckNum=true;
                       if(ckNum==true){
                           $("#NumId").prop("disabled",false);
                           $("#NumId").focus();
                           ckNum=false;
                       }

                       return false;
                   }

               });
           }
        });


        $("#NumId").bind("input propertychange",function () {
            var BSK_BAGENO=$(this).val();
           if(BSK_BAGENO.length==10){
               $("input[type=checkbox]").each(function (i) {
                   var idVal=$(this).attr("id");
                   var CheckboxId=idVal.split("@");
                   if(CheckboxId.indexOf(BSK_BAGENO) > -1 ){
                       var id=$("#IdPt").val();
                       $("#"+id+"\\@"+BSK_BAGENO).prop("checked",true);
                       $("#NumId").prop("disabled",true);
                       $("#IdPt").focus();
                       $("#IdPt").val("");
                       $("#NumId").val("");
                       return false;
                   }

               });
            }
        });
        $(document).on('change', 'input[type=checkbox]', function() {
            var checkbox = $(this);
            console.log(GetCheckVal());
        });

        $("#form1").submit(function () {
            //$(window).off('beforeunload', reloadmsg);
            var json=GetCheckVal();

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
                   var str=AESDeCode(data);
                    var dataObj=JSON.parse(str);
                    console.log(dataObj);
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
                    return false;
                }
            });
            return false;

        });

        function bedcallback(data)
        {
            var str=AESDeCode(data);
            var dataObj=JSON.parse(str);
            console.log(dataObj);
            var  BUT_BUTNAME=dataObj.BUT_BUTNAME;
            var BUT_PROCDATE=dataObj.BUT_PROCDATE;
            var BUT_NEEDUNIT=dataObj.BUT_NEEDUNIT;
            var BUT_PROCOPID=dataObj.BUT_PROCOPID;
            var  BUT_PROCTIME=dataObj.BUT_PROCTIME;
            var   sSave=dataObj.sSave;
            var   sTraID=dataObj.sTraID;
            if($("#DATAList").children()){
                $("#DATAList").children().remove();
            }
            if($("#DateVal").val()==""||$("#TimeVal").val()==""){
                TimerDefault();
            }

            $("#DataTxt").val(BUT_BUTNAME);
            $("#BUT_PROCDATE").val(BUT_PROCDATE);
            $("#BUT_NEEDUNIT").val(BUT_NEEDUNIT);
            $("#BUT_PROCOPID").val(BUT_PROCOPID);
            $("#BUT_PROCTIME").val(BUT_PROCTIME);
            $("#sSave").val(sSave);
            $("#sTraID").val(sTraID);
            InsertWSST(sTraID,'A',data);
            tABLELIST(BUT_NEEDUNIT);
            $("button[type=submit]").prop("disabled",false);
            $("#SerchBtn").prop("disabled",false);
            $("#IdPt").prop("disabled",false);
        }
        var x;
        $("#sbed").click(function () {
            switch (checkBEDwindow()) {
                case "false":
                    errorModal("領血單位視窗已開啟");
                    break;
                case "true":
                    try {
                        x=window.open("/webservice/NISPRWCBED.php?str="+AESEnCode("sFm=CNBD&sIdUser=00FUZZY"),"領血單位",'width=850px,height=650px,scrollbars=yes,resizable=no');

                    }catch (e) {
                        console.log(e);
                    }
                    break;
            }
            x.bedcallback=bedcallback;
        });



        function Serchcallback(AESobj){
            var str1=AESDeCode(AESobj);
            var objArr=JSON.parse(str1);
            console.log(str1);
            $("#DATAList").children().remove();
            var  BSK_NURSDATE='';
            var  BSK_NURSTIME='';
            var  sTraID='';

            $.each(objArr,function (index,val) {
                var   BSK_BAGENO=val.BSK_BAGENO;
                var  BSK_MEDNO=val.BSK_MEDNO;
                var  BKD_EGCODE=val.BKD_EGCODE;
                var  BSK_NEEDUNIT=val.BSK_NEEDUNIT;
                var MH_NAME=val.MH_NAME;
                var  BSK_BARSIGN=val.BSK_BARSIGN;
                var  BSK_BLDKIND=val.BSK_BLDKIND;
                var BSK_INDENTNO=val.BSK_INDENTNO;
                var BSK_NURSOPID=val.BSK_NURSOPID;
                var  BSK_TRANSRECNO=val.BSK_TRANSRECNO;
                sTraID=val.sTraID;
                BSK_NURSDATE=val.BSK_NURSDATE;
                BSK_NURSTIME=val.BSK_NURSTIME;

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
                );

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

        var y;
        $("#SerchBtn").click(function () {
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

                    y=window.open("/webservice/NISPWSLKQRY.php?str="+
                        AESEnCode("sFm=CNBD&PageVal="+$('#BUT_NEEDUNIT').val()+"&DA_idpt="+
                            ""+"&DA_idinpt="+""+
                            "&sUser="+"<?php echo  $OPID?>"+"&NM_PATIENT="+$('#DataTxt').val())
                        ,"查詢",'width=750px,height=650px,scrollbars=yes,resizable=no');
                    break;
            }

            y.Serchcallback=Serchcallback;
        });


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

        $("#Del").click(function() {
            var del_ip='/webservice/NISPWSDELILSG.php';
            console.log('http://localhost'+del_ip+"?str="+AESEnCode("sFm="+'CNBD'+"&sTraID="+$('#sTraID').val()+"&sPg="+""+"&sCidFlag=D"+"&sUr=<?php echo $OPID?>"));
            $.ajax({
                url:del_ip+"?str="+AESEnCode("sFm="+'CNBD'+"&sTraID="+$('#sTraID').val()+"&sPg="+""+"&sCidFlag=D"+"&sUr=<?php echo $OPID?>"),
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
        });

        $("#restBtn").click(function () {
            restUI();
        });


        function tABLELIST(BUT_NEEDUNIT) {
/*
            console.log("http://localhost"+'/webservice/NISPWSGETPRE.PHP?str='+AESEnCode('sFm='+"CNBD"+'&sTraID='+''+'&sPg='+BUT_NEEDUNIT));
*/
            $.ajax({
                url:'/webservice/NISPWSGETPRE.PHP?str='+AESEnCode('sFm='+"CNBD"+'&sTraID='+''+'&sPg='+BUT_NEEDUNIT),
                type:"POST",
                dataType:"text",
                success:function (data) {
                   /* console.log(data);*/
                    var sTraID=$("#sTraID").val();
                    InsertWSST(sTraID,'B',data);
                    var arr=JSON.parse(data);
                    if(arr.length==0){
                        $("#DATAList").append(
                            "<tr class='list-item'>"+
                            "<td>"+"查無資料"+"</td>"+
                            "</tr>"

                        );
                        return false;
                    }

                    $.each(arr,function (index,val) {
                        /*checkbod id:病歷號+血袋號碼 value:領血請領單位+病歷號+血袋號碼*/
                        var   BSK_BAGENO=val.BSK_BAGENO;
                        var  BSK_MEDNO=val.BSK_MEDNO;
                        var  MH_NAME=val.MH_NAME;
                        var  BKD_EGCODE=val.BKD_EGCODE;
                        $("#DATAList").append(
                            "<tr class='list-item'>"+
                            "<td>"+"<input type='checkbox'  name='BDckbox' class='form-check-input' id='"+BSK_MEDNO+'@'+BSK_BAGENO+"' value='"+BUT_NEEDUNIT+"@"+BSK_MEDNO+"@"+BSK_BAGENO+"'>"+"</td>"+
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
        function GetCheckVal() {
            //取checkbox的值
            var cbxVehicle = new Array();
            var Json=[];
            $("input:checkbox:checked[name=BDckbox]").each(function (i) {
                cbxVehicle[i]=$(this).val();
            });
            /*01349277@0076126888*/
            if(cbxVehicle.length>0){
                $.each(cbxVehicle,function (index) {
                   /* BSK_BAGENO,BSK_BLDKIND,BSK_MEDNO,BSK_NEEDUNIT,BSK_NURSDATE,BSK_NURSTIME,BSK_NURSOPID,BSK_INDENTNO,BSK_TRANSRECNO,BSK_BARSIGN*/
                    var str=cbxVehicle[index];
                    var OBJ=new Object();
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
        function errorModal(str) {
            $('#Errormodal').modal('show');
            document.getElementById('ErrorText').innerText=str;
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
        function UIDefault() {
            $("#DELMENU").prop("disabled",true);
            $("#SerchBtn").prop("disabled",true);
            $("#SubmitBtn").prop("disabled",true);
            $("form input[type=text]").prop("disabled",true);
        }
        function restUI(){
            $("form input[type=text]").val("");
            $("form input[type=text] :not(.Num_input)").prop("disabled",false);
            $("input:not(#NURSOPID)").val("");
            $("button").prop("disabled",false);
            $("#DATAList").children().remove();
            $("#SubmitBtn").prop("disabled",true);
            $("#SerchBtn").prop("disabled",true);
            $("#DELMENU").prop("disabled",true);

        }


    });

</script>
</html>
