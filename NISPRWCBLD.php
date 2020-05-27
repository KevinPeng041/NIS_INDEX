<?php
include '../NISPWSIFSCR.php';
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
    <title>輸血記錄回報作業</title>
    <script type="text/javascript" src="../jquery-3.4.1.js"></script>
    <link rel="stylesheet" href="http://localhost/bootstrap-4.3.1-dist/css/bootstrap.min.css">
    <script src="../bootstrap-4.3.1-dist/js/bootstrap.min.js" type="text/javascript"></script>
    <script src="../crypto-js.js"></script>
    <script src="../AESCrypto.js"></script>
    <script  type="text/javascript" src="../instascan.min.js"></script>
    <script src="../NISCOMMAPI.js"></script>
    <script>

        var ckw=setInterval(function () {
            try {
                window.opener.document.getElementById("CKWindow").value="123";
            }catch (e) {
                $("#wrapper").show();
                alert("此帳號以被登出,請重新登入開啟");
                window.close();
                clearInterval(ckw);
                return false;
            }

        },500);

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

    </script>
    <style>

        .Parametertable input{
         /*display: none;*/
           background-color: #00FF00;
        }
        h1 {
            text-align: left;
            font-size: 6vmin;
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
        .container{
            max-width: 1140px;
        }
        .container button{
            color: white;
            font-size: 4.5vmin;
        }
        .container .float-left{
            font-size: 3.7vmin;
        }

        .container .Features{
            margin-top: 5px;
            font-size: 3.5vmin;
        }
        td{
            word-wrap: break-word;
        }

       .table td, .table th {
           padding: 0 !important
       }
        input{
            border-radius:4px;border:1px solid #DBDBDB;
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
      .Otimer  .pageTime #TimeVal {
            width: 15vmin;
            margin-left: 5px;
            margin-top: 5px;
            border: 1px white;
             text-align: center;
        }
        #B_CUR{
            text-transform:uppercase;
        }
        #C_CUR{
            text-transform:uppercase;
        }
        #PBList{
            font-size: 30px;
        }
        #PBList  input[type=radio] {
            width: 30px;
            height: 30px;
            margin-top: 5px!important;
        }
        #GTList input[type=checkbox]{
            width: 4.5vmin;
            height: 4.5vmin;
        }
        #INList  input[type=checkbox]{
            width: 4.5vmin;
            height: 4.5vmin;
        }
    </style>
</head>
<body>
<div id="wrapper"></div>
<div id="loading" >請稍後<img class="loadimg" src="../dotloading.gif"></div>
<div class="container">
    <h1>輸血記錄回報作業</h1>
    <form id="form1">
        <span style="margin-left:0 px">
            <button type="button" class="btn btn-primary btn-md" disabled style="display: none">回主畫面</button>
            <button type="button"  class="btn btn-warning btn-md"  id="sbed" style="margin: 0 0 1px 1px" >責任床位</button><span style="margin-left: 1px"><b>悅晟資訊</b></span>

        </span>

        <span class="float-left">
            <button type="submit" id="SubmitBtn" class="btn btn-primary btn-md" >儲存</button>
            <button type="button" id="Serch" class="btn btn-primary btn-md" >查詢</button>
            <button type="button" id="DELMENU" class="btn btn-primary btn-md"  data-toggle="modal" data-target="#DELModal">作廢</button>
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


            <input id="SCAN" value="" type="text" placeholder="SCAN">
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
                    <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12" >
                        <div>
                            <div class="input-group" style="margin-top: 5px;margin-bottom: 2px;">
                                <div class="input-group-prepend">
                                    <span class="input-group-text" id="">血袋</span>
                                </div>
                                <input type="text" id="code1" class="form-control" placeholder="輸入條碼" maxlength="10">
                                <div class="input-group-append">
                                    <input type="button" value="掃描" id="scan1" class="btn btn-outline-primary ">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12" >
                        <div class="input-group" style="margin-top: 5px;margin-bottom: 2px;">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="">領血核對</span>
                            </div>
                            <input type="text" class="form-control" style="display: none;background-color: #00FF00" placeholder="使用者" id="B_NAME"  value="<?php echo $sUr?>" readonly>
                            <input type="text" class="form-control"   placeholder="帳號" id="B_UR"  value="<?php echo $Account?>" readonly>
                            <input type="password" class="form-control" placeholder="密碼" id="B_PWD"  value="<?php echo $passwd?>" readonly>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12" >
                        <div class="input-group" style="margin-top: 5px;margin-bottom: 2px;">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="">領血覆核</span>
                            </div>
                            <input type="text" class="form-control" placeholder="帳號" value="" id="B_CUR" maxlength="7">
                            <input type="password" class="form-control" placeholder="密碼" value="" id="B_CPWD">
                            <input type="text" style="display: none;background-color: #00FF00"  placeholder="覆核人員" value="" id="B_CNAME">
                        </div>
                    </div>
                </div>
            </div>
            <!--內容-->
            <div id="GETbldUI" data-spy="scroll" data-target="#GBUI" data-offset="0" style="height:300px;overflow:auto; position: relative;display: none;margin-top: 5px;">
                     <div class="table-responsive" id="GBUI">
                         <table class="table" style="table-layout: fixed;text-align: center">
                                <thead  class="theadtitle"  style=" font-size: 3.5vmin;">
                                <th> <button type="button" class="btn btn-success" style="font-size: 3vmin" id="B_ALLCHECK">全選</button></th>
                                <th style=" padding-bottom: 5px !important">血袋號碼</th>
                                <th style=" padding-bottom: 5px !important">血品名稱</th>
                                <th style="text-align: center ;padding-bottom: 5px !important">血型</th>
                                <th >領血核對<br>領血覆核</th>
                                <th style="display: none">輸血核對<br>輸血覆核</th>
                                </thead>
                                <tbody style=" font-size: 3.5vmin;" id="GTList">

                                </tbody>
                        </table>
                     </div>
            </div>
        </div>

        <!--輸血核對-->
        <div>
            <!--掃描條碼Bar-->
            <div class="container" id="SetINbldVal" style="display: none;background-color:#bee5eb;margin-top: 5px; ">
                <div class="row">
                    <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12" >
                        <div>
                            <div class="input-group" style="margin-top: 5px">
                                <div class="input-group-prepend">
                                    <span class="input-group-text" id="">血袋</span>
                                </div>
                                <input type="text" id="code2" class="form-control" placeholder="輸入條碼" maxlength="10">
                                <div class="input-group-append">
                                    <input type="button" value="掃描" id="scan2" class="btn btn-outline-primary">
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12" >
                        <div class="input-group" style="margin-top: 5px;margin-bottom: 2px;">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="">輸血核對</span>
                            </div>
                            <input type="text" class="form-control" placeholder="帳號"  id="C_UR" value="<?php echo $Account?>" readonly>
                            <input type="password" class="form-control" placeholder="密碼" id="C_PWD" value="<?php echo $passwd?>" readonly>
                            <input type="text" class="form-control" style="display: none;background-color: #00FF00" placeholder="使用者"  id="C_NAME" value="<?php echo $sUr?>" readonly>

                        </div>
                    </div>
                    <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12" >
                        <div class="input-group" style="margin-top: 5px;margin-bottom: 2px;">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="">輸血覆核</span>
                            </div>
                            <input type="text" class="form-control" placeholder="帳號" value="" id="C_CUR" maxlength="7">
                            <input type="password" class="form-control" placeholder="密碼" value="" id="C_CPWD">
                            <input type="text" style="display: none;background-color: #00FF00" placeholder="覆核人員" value="" id="C_CNAME">
                        </div>
                    </div>
                </div>
            </div>
            <!--內容-->
            <div id="INbldUI" data-spy="scroll" data-target="#IBUI" data-offset="0" style="height:300px;overflow:auto; position: relative;display: none;margin-top: 5px;">
                    <div class="table-responsive" id="IBUI">
                        <table class="table" style="table-layout: fixed;text-align: center">
                            <thead  class="theadtitle" style=" font-size: 3.5vmin;">
                            <th> <button type="button" class="btn btn-success" style="font-size: 3vmin" id="C_ALLCHECK">全選</button></th>
                            <th style=" padding-bottom: 5px !important">血袋號碼</th>
                            <th style=" padding-bottom: 5px !important">血品名稱</th>
                            <th style="text-align: center ;padding-bottom: 5px !important">血型</th>
                            <th style="display: none">領血核對<br>領血覆核</th>
                            <th >輸血核對<br>輸血覆核</th>
                            </thead>
                            <tbody style=" font-size: 3.5vmin;" id="INList">

                            </tbody>
                        </table>
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
</body>
<script>
  $(document).ready(function () {
      $("#B").prop("disabled" ,true);
      $("#C").prop("disabled" ,true);
      /*條碼監測(手動keyin)*/
      var arr1=[];var arr2=[];
      $('#code1').bind("input propertychange",function(){
          var Bval1= $('#code1').val();
          console.log(Bval1);
          if(Bval1 !="" && Bval1 !=null){
              if($("#GT"+Bval1).length > 0){
                  arr1.push(Bval1);
                  arr2.push(Bval1);
                  var nowval=arr1.pop();
                  console.log(nowval);
                  console.log(arr2);
                  $.each(arr2,function (index) {
                      $("#GT"+arr2[index]).parent().parent().css({"background-color":""});
                  });
                  $("#GT"+nowval).parent().parent().css({"background-color":"#BBFF00"});
                  $("#GT"+nowval).prop("checked",true);
                  var top=($("#GT"+nowval).offset()).top-500;
                  $("#GETbldUI").scrollTop(top);
              }
          }
      });
      var arr3=[];var arr4=[];
      $('#code2').bind("input propertychange",function(){
          var Cval2= $('#code2').val();
          if(Cval2 !="" && Cval2 !=null){
              if($("#IN"+Cval2).length > 0){
                  arr3.push(Cval2);
                  arr4.push(Cval2);
                  var nowval=arr3.pop();
                  console.log(nowval);
                  console.log(arr4);
                  $.each(arr4,function (index) {
                      $("#IN"+arr4[index]).parent().parent().css({"background-color":""});
                  });
                  $("#IN"+nowval).parent().parent().css({"background-color":"#BBFF00"});
                  $("#IN"+nowval).prop("checked",true);
                  var top=($("#IN"+nowval).offset()).top-500;
                  $("#INbldUI").scrollTop(top);
              }
          }

      });

      function reset() {
          $("#PUTbld").hide();
          $("#SetGETbldVal").hide();
          $("#SetINbldVal").hide();
          $("#GETbldUI").hide();
          $("#INbldUI").hide();
          $("#B").prop("disabled" ,true);
          $("#C").prop("disabled" ,true);
          $("#PBList").children().remove();
          $("#GTList").children().remove();
          $("#INList").children().remove();
      }
      $("#cleanval").click(function () {
          reset();
          $("#"+$("#PageVal").val()).css({'background-color' : '', 'opacity' : '','color':'white'});
      });
      /*責任床位ws*/
      function bedcallback(data)
      {
          var str=AESDeCode(data);
          var datastr=JSON.parse(JSON.stringify(str).replace(/\u0000/g, '').replace(/\\u0000/g, ""));
          var dataObj=JSON.parse(datastr);

          reset();
          if(dataObj){
              $("#DataTxt").val(dataObj.DataTxt);
              $("#DA_idpt").val(dataObj.IDPT);
              $("#DA_idinpt").val(dataObj.IDINPT);
              $("#DA_sBed").val(dataObj.SBED);
              $("#clickTime").val(0);
              $("#PageVal").val('A');
              TimerRdo();
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
                  x=window.open("/webservice/NISPWSLKCBD.php?str="+AESEnCode("sIdUser=00FUZZY"),"責任床位",'width=850px,height=650px,scrollbars=yes,resizable=no');
                  break;
          }
          x.bedcallback=bedcallback;
      };
      function Scancallback(data) {

          var json=JSON.parse(data);
          var page=json.PAGE;
          var BIDArr=json.B_ID;
          if(BIDArr.length >0){
              switch (page) {
                  case "B":
                      $.each(BIDArr,function (index) {
                          if($("#GT"+BIDArr[index]).length > 0){
                              $("#GT"+BIDArr[index]).parent().parent().css({"background-color":"#BBFF00"});
                              $("#GT"+BIDArr[index]).prop("checked",true);
                          }
                      });

                      break;
                  case "C":
                      $.each(BIDArr,function (index) {
                          if($("#IN"+BIDArr[index]).length > 0){
                              $("#IN"+BIDArr[index]).parent().parent().css({"background-color":"#BBFF00"});
                              $("#IN"+BIDArr[index]).prop("checked",true);
                          }
                      });
                      break;
              }
          }
      }
      /*掃描資料(自動keyin)*/
      var scan=document.getElementById("scan1");
      var y;
       scan.onclick=function(){
          var ip='localhost';
          switch (checkBEDwindow()) {
              case "false":
                  errorModal("掃描視窗已開啟");
                  return false;
                  break;
              case "true":
                  y=window.open("/test/CamScanQrcode.php?page=B","領血掃描",'width=350px,height=450px,scrollbars=yes,resizable=no');
                 /* 自動keyin*/
                 break;
          }
          y.Scancallback=Scancallback;
      };

      var scan2=document.getElementById("scan2");
      var z;
      scan2.onclick=function(){
          switch (checkBEDwindow()) {
              case "false":
                  errorModal("掃描視窗已開啟");
                  return false;
                  break;
              case "true":
                  z=window.open("/test/CamScanQrcode.php?page=C","領血掃描",'width=350px,height=450px,scrollbars=yes,resizable=no');
                  /* 自動keyin*/
                  break;
          }
          z.Scancallback=Scancallback;
      };



      function _GetINIjson() {
          $("#wrapper").show();
          console.log("http://localhost"+'/webservice/NISPWSTRAINI.PHP?str='+AESEnCode('sFm=CBLD&idPt='+$("#DA_idpt").val()+'&INPt='+$("#DA_idinpt").val()+'&sUr=<?php echo $Account?>'));
          $.ajax({
              'url':'/webservice/NISPWSTRAINI.PHP?str='+AESEnCode('sFm=CBLD&idPt='+$("#DA_idpt").val()+'&INPt='+$("#DA_idinpt").val()+'&sUr=<?php echo $Account?>'),
              type:"POST",
              dataType:"text",
              success:function(data){
                  var json=JSON.parse(AESDeCode(data))[0];
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
      var ST_DATAB='';
      var ST_DATAC='';
      function LoadPageData(sfm,sTraID,Page) {
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
          $.ajax({
              'url':'/webservice/NISPWSGETPRE.PHP?str='+AESEnCode('sFm='+sfm+'&sTraID='+sTraID+'&sPg='+Page),
              type:"POST",
              dataType:"text",
              success:function(data){
                  var json=JSON.parse(AESDeCode(data))[0];
                  switch (Page) {
                      case "A":
                          var ST_DATAA=JSON.parse(json.ST_DATAA);
                          if($("#PBList").children().length == 0){
                              $.each(ST_DATAA,function (index) {
                                  var DT_EXE=ST_DATAA[index].DT_EXE;
                                  var TM_EXE=ST_DATAA[index].TM_EXE;
                                  var Num=ST_DATAA[index].NUM;
                                  var A_INDNO=ST_DATAA[index].A_INDNO;
                                  var A_TRANO=ST_DATAA[index].A_TRANO;

                                  $("#PBList").append(
                                      "<tr>"+
                                      "<td style='width: 20vmin'><input type='radio' id='ck0' name='PBCK' value='"+DT_EXE+"@"+TM_EXE+"@"+Num+"@"+A_INDNO+"@"+A_TRANO+"'>"+"</td>"+
                                      "<td>"+DT_EXE+"</td>"+
                                      "<td>"+TM_EXE+"</td>"+
                                      "<td>"+Num+"</td>"+
                                      "<td style='display: none'>"+A_INDNO+"</td>"+
                                      "<td style='display: none'>"+A_TRANO+"</td>"+
                                      "</tr>"
                                  );
                              });
                          }
                          break;
                      case "B":
                           ST_DATAB=JSON.parse(json.ST_DATAB);
                          console.log(ST_DATAB);
                          if($("#GTList").children().length == 0){
                              $.each(ST_DATAB,function (index) {
                                  var B_ID=ST_DATAB[index].B_ID;
                                  var B_NUM=ST_DATAB[index].B_NUM;
                                  var B_TP=ST_DATAB[index].B_TP;
                                  var B_UR=ST_DATAB[index].B_UR;
                                  var B_CUR=ST_DATAB[index].B_CUR;
                                  var B_DTSEQ=ST_DATAB[index].B_DTSEQ;
                                  var B_BKD=ST_DATAB[index].B_BKD;
                                  var B_INDNO=ST_DATAB[index].B_INDNO;

                                  $("#GTList").append(
                                      "<tr>"+
                                      "<td>"+"<input type='checkbox' name='GTckbox' class='form-check-input' id='GT"+B_ID+"' value='"+B_ID+"@"+B_NUM+"@"+B_TP+"@"+B_UR+"@"+B_CUR+"@"+B_DTSEQ+"@"+B_BKD+"@"+B_INDNO+"'>"+"</td>"+
                                      "<td>"+B_ID+"</td>"+
                                      "<td>"+B_NUM+"</td>"+
                                      "<td>"+B_TP+"</td>"+
                                      "<td>"+$('#B_NAME').val()+"<br>"+"<label id='BCUR"+index+"'>"+"</label>"+"</td>"+
                                      "<td style='display: none'>"+B_UR+"<br>"+B_CUR+"</td>"+
                                      "<input type='text' value='"+B_DTSEQ+"' style='display: none' id='B"+B_DTSEQ+"' >"+
                                      "<input type='text' value='"+B_BKD+"' style='display: none' id='B"+B_BKD+"' >"+
                                      "<input type='text' value='"+B_INDNO+"' style='display: none' id='B"+B_INDNO+"' >"+
                                      "</tr>"
                                  );
                              });
                          }
                          break;
                      case "C":
                          ST_DATAC=JSON.parse(json.ST_DATAC);
                          console.log(ST_DATAC);
                          if($("#INList").children().length == 0){
                              $.each(ST_DATAC,function (index) {
                                  var C_ID=ST_DATAC[index].C_ID;
                                  var C_NUM=ST_DATAC[index].C_NUM;
                                  var C_TP=ST_DATAC[index].C_TP;
                                  var C_UR=ST_DATAC[index].C_UR;
                                  var C_CUR=ST_DATAC[index].C_CUR;
                                  var C_DTSEQ=ST_DATAC[index].C_DTSEQ;
                                  var C_BKD=ST_DATAC[index].C_BKD;
                                  var C_INDNO=ST_DATAC[index].C_INDNO;

                                  $("#INList").append(
                                      "<tr>"+
                                      "<td>"+"<input type='checkbox' name='INckbox' class='form-check-input' id='IN"+C_ID+"' value='"+C_ID+"@"+C_NUM+"@"+C_TP+"@"+C_UR+"@"+C_CUR+"@"+C_DTSEQ+"@"+C_BKD+"@"+C_INDNO+"'>"+"</td>"+
                                      "<td>"+C_ID+"</td>"+
                                      "<td>"+C_NUM+"</td>"+
                                      "<td>"+C_TP+"</td>"+
                                      "<td style='display: none'>"+C_UR+"<br>"+C_CUR+"</td>"+
                                      "<td>"+$('#C_NAME').val()+"<br>"+"<label id='CCUR"+index+"'>"+"</label>"+"</td>"+
                                      "<input type='text' value='"+C_DTSEQ+"' style='display: none' id='C"+C_DTSEQ+"' >"+
                                      "<input type='text' value='"+C_BKD+"' style='display: none' id='C"+C_BKD+"' >"+
                                      "<input type='text' value='"+C_INDNO+"' style='display: none' id='C"+C_INDNO+"' >"+
                                      "</tr>"
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

  /* 及時檢核
     /!*領血覆核帳號*!/
      $('#B_CUR').blur(function () {
          $.ajax({
              url:'/webservice/NISPWSCKUSR.php?str='+AESEnCode('sIdUser='+paddingLeft((this.value).toUpperCase(),7)),
              type:'GET',
              dataType:'text',
              success:function (data) {
                  var re=AESDeCode(data);
                  if(re=='error'){
                      errorModal('帳號錯誤');
                      $('#B_CUR').focus();
                      return false;
                  }
                  var BCUR=re.split("=",2)[1];
                  $("#B_CNAME").val(BCUR);
                  $.each(ST_DATAB,function (index) {
                      $("#BCUR"+index).html(BCUR);
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
      });
      /!*領血覆核密碼*!/
      $('#B_CPWD').blur(function () {
          if($('#B_CUR').val()==""){
              errorModal("請先輸入覆核帳號");
              $('#B_CUR').focus();
              return false;
          }
          if($("#B_CPWD").val()==''){
              errorModal("密碼不得為空");
              $("#B_CPWD").focus();
              return false;
          }
          $.ajax({
              url:'/webservice/NISPWSCKPWD.php?str='+AESEnCode('sIdUser='+paddingLeft($('#B_CUR').val().toUpperCase(),7)+'&sPassword='+$('#B_CPWD').val()),
              type:'GET',
              dataType:'text',
              success:function (data) {
                  var result=AESDeCode(data);
                  if(result != 'true'){
                      errorModal("密碼錯誤");
                      $("#B_CPWD").focus();
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
      });

      /!*輸血覆核帳號*!/
      $('#C_CUR').blur(function () {
          $.ajax({
              url:'/webservice/NISPWSCKUSR.php?str='+AESEnCode('sIdUser='+paddingLeft((this.value).toUpperCase(),7)),
              type:'GET',
              dataType:'text',
              success:function (data) {
                  var re=AESDeCode(data);
                  if(re=='error'){
                      errorModal('帳號錯誤');
                      $('#C_CUR').focus();
                      return false;
                  }
                  console.log(ST_DATAC);
                  var CCUR=re.split("=",2)[1];
                  $("#C_CNAME").val(CCUR);
                  $.each(ST_DATAC,function (index) {
                      $("#CCUR"+index).html(CCUR);
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
      });
      /!*領血覆核密碼*!/
      $('#C_CPWD').blur(function () {
          if($('#C_CUR').val()==""){
              errorModal("請先輸入覆核帳號");
              $('#C_CUR').focus();
              return false;
          }
          if($("#C_CPWD").val()==''){
              errorModal("密碼不得為空");
              $("#C_CPWD").focus();
              return false;
          }
          $.ajax({
              url:'/webservice/NISPWSCKPWD.php?str='+AESEnCode('sIdUser='+paddingLeft($('#C_CUR').val().toUpperCase(),7)+'&sPassword='+$('#C_CPWD').val()),
              type:'GET',
              dataType:'text',
              success:function (data) {
                  var result=AESDeCode(data);
                  if(result != 'true'){
                      errorModal("密碼錯誤");
                      $("#C_CPWD").focus();
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
      });*/
      var  DATAval='';
      var arr=[];
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
         var arr=DATAval.split("@",5);
           var newST_DATAA=[{
               'DT_EXE':parseInt(arr[0]),
               'TM_EXE':parseInt(arr[1]),
               'B_NUM':parseInt(arr[2]),
               'A_INDNO':parseInt(arr[3]),
               'A_TRANO':arr[4]
           }];

          return newST_DATAA;
       }

      function InsertWSST(sTraID,page,json) {
          console.log("http://localhost"+'/webservice/NISPWSSETDATA.php?str='+AESEnCode('sFm=CBLD&sTraID='+sTraID+'&sPg='+page+'&sData='+json));
          $.ajax({
              'url':'/webservice/NISPWSSETDATA.php?str='+AESEnCode('sFm=CBLD&sTraID='+sTraID+'&sPg='+page+'&sData='+json),
              type:"POST",
              dataType:"text",
              success:function(data){
                  console.log(AESDeCode(data));
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
     /*------------------------------------全選------------------------------------*/

     $("#B_ALLCHECK").click(function () {
            $.each(ST_DATAB,function (index) {
                $("#GT"+ST_DATAB[index].B_ID).prop('checked',true);
            });
      });
      $("#C_ALLCHECK").click(function () {
          $.each(ST_DATAC,function (index) {
              $("#IN"+ST_DATAC[index].C_ID).prop('checked',true);
          });
      });
      /*---------------------------------------------------------------------------*/
      var DATABarr=[];
      var JsonB='';
      $('#GTList').on("change","input[type='checkbox']",function(){
          var val=this.value;
          if(val){
              DATABarr.push(val);
              var DATAB=DATABarr.filter(function (el, i, arr) {
                  /*刪除重複值*/
                  return arr.indexOf(el)===i;
              });
              $.each(DATAB,function (index) {
                  if(DATAB[index]){
                      var B_ID=DATAB[index].split("@",1)[0];
                      /*是否有勾選*/
                      if(!$("#GT"+B_ID).is(":checked")){
                          var i=DATAB.indexOf(DATAB[index]);
                          if(i>-1){
                              DATAB.splice(i,1);
                              DATAB.filter(function (e) {
                                  return e;
                              });
                          }
                      }
                  }
                  /*重整新的json*/
                  if(DATAB.length > 0){
                      var Json_DATAB=[];
                      $.each(DATAB,function (index) {
                          var str=DATAB[index];
                          var OBJ=new Object();
                          OBJ.B_ID= str.split("@",8)[0];
                          OBJ.B_NUM= str.split("@",8)[1];
                          OBJ.B_TP= str.split("@",8)[2];
                          OBJ.B_UR= $("#B_UR").val();
                          OBJ.B_CUR=paddingLeft($('#B_CUR').val().toUpperCase(),7);
                          OBJ.B_DTSEQ= str.split("@",8)[5];
                          OBJ.B_BKD= str.split("@",8)[6];
                          OBJ.B_INDNO= str.split("@",8)[7];
                          Json_DATAB.push(OBJ);
                      });
                      JsonB=Json_DATAB;

                  }
              });

          }

      });
      var DATACarr=[];
      var JsonC='';
      $('#INList').on("change","input[type='checkbox']",function(){
          var val=this.value;
          if(val){
              DATACarr.push(val);
              var DATAC=DATACarr.filter(function (el, i, arr) {
                  /*刪除重複值*/
                  return arr.indexOf(el)===i;
              });
              $.each(DATAC,function (index) {
                  if(DATAC[index]){
                      var C_ID=DATAC[index].split("@",1)[0];
                      /*是否有勾選*/
                      if(!$("#IN"+C_ID).is(":checked")){
                          var i=DATAC.indexOf(DATAC[index]);
                          if(i>-1){
                              DATAC.splice(i,1);
                              DATAC.filter(function (e) {
                                  return e;
                              });
                          }
                      }
                  }
                  /*重整新的json*/
                  if(DATAC.length > 0){
                      var Json_DATAC=[];
                      $.each(DATAC,function (index) {
                          var str=DATAC[index];
                          var OBJ=new Object();
                          OBJ.C_ID= str.split("@",8)[0];
                          OBJ.C_NUM= str.split("@",8)[1];
                          OBJ.C_TP= str.split("@",8)[2];
                          OBJ.C_UR= $("#C_UR").val();
                          OBJ.C_CUR=paddingLeft($('#C_CUR').val().toUpperCase(),7);
                          OBJ.C_DTSEQ= str.split("@",8)[5];
                          OBJ.C_BKD= str.split("@",8)[6];
                          OBJ.C_INDNO= str.split("@",8)[7];
                          Json_DATAC.push(OBJ);
                      });
                      JsonC=Json_DATAC;
                  }
              });
          }

      });

      $('button[name=click]').click(function (){
          var page=this.id;
          $("#PageVal").val(page);
          switch (page) {
              case "A":
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
                  break;
              case "B":
                  $("#PUTbld").hide();
                  $("#SetGETbldVal").show();
                  $("#SetINbldVal").hide();
                  $("#GETbldUI").show();
                  $("#INbldUI").hide();

                  InsertWSST($("#sTraID").val(),'A',JSON.stringify(GETNEWSTDATA()));

                  if(JsonC.length > 0){
                      InsertWSST($("#sTraID").val(),'C',JSON.stringify(GetCheckVal('C')));
                  }
                  LoadPageData('CBLD',$('#sTraID').val(),'B');


                  $("#A").css({ 'background-color' : '', 'opacity' : '' ,'color':'white'});
                  $("#C").css({ 'background-color' : '', 'opacity' : '' ,'color':'white'});
                  break;
              case "C":
                  $("#PUTbld").hide();
                  $("#SetGETbldVal").hide();
                  $("#SetINbldVal").show();
                  $("#GETbldUI").hide();
                  $("#INbldUI").show();
                  InsertWSST($("#sTraID").val(),'A',JSON.stringify(GETNEWSTDATA()));

                  if(JsonB.length >0){
                      InsertWSST($("#sTraID").val(),'B',JSON.stringify(GetCheckVal('B')));
                  }
                  LoadPageData('CBLD',$('#sTraID').val(),'C');



                  $("#B").css({ 'background-color' : '', 'opacity' : '' ,'color':'white'});
                  $("#A").css({ 'background-color' : '', 'opacity' : '' ,'color':'white'});
                  break;
          }
          $("#"+page).css({ 'background-color' : '#EEEE00', 'opacity' : '' ,'color':'black'});
          $("#"+page).css({ 'background-color' : '#EEEE00', 'opacity' : '' ,'color':'black'});
      });

      $('#PBList').on('change',CheckBOXChang);/*歷次發血選擇*/

      $("#form1").submit(function () {

            var json='';
            var page=$('#PageVal').val();

            console.log(checkAcount(page,'00FUZZY'));
            switch (page) {
                case 'B':
                    json=GetCheckVal('B');
                    break;
                case 'C':
                    json=GetCheckVal('C');
                    break;
            }
         /*   $("#loading").show();
            $("#wrapper").show();*/


            return false;
        });
      function GetCheckVal(page) {
          var cbxVehicle = new Array();
          var Json=[];
          switch (page) {
              case "B":
                  $("input:checkbox:checked[name=GTckbox]").each(function (i) {
                      cbxVehicle[i]=this.value;
                  });
                  if(cbxVehicle.length>0){
                      $.each(cbxVehicle,function (index) {
                          var str=cbxVehicle[index];
                          var OBJ=new Object();
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
                          var str=cbxVehicle[index];
                          var OBJ=new Object();
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
      function checkAcount(page,Acount) {
          var a=[];
          $.ajax({
              url:'/webservice/NISPWSCKUSR.php?str='+AESEnCode('sIdUser='+paddingLeft((Acount).toUpperCase(),7)),
              type:'GET',
              dataType:'text',
              success:function (data) {
                  var re=AESDeCode(data);
                 a.push(re);
              },error:function (XMLHttpResponse,textStatus,errorThrown) {
                  console.log(
                      "1 返回失敗,XMLHttpResponse.readyState:"+XMLHttpResponse.readyState+XMLHttpResponse.responseText+
                      "2 返回失敗,XMLHttpResponse.status:"+XMLHttpResponse.status+
                      "3 返回失敗,textStatus:"+textStatus+
                      "4 返回失敗,errorThrown:"+errorThrown
                  );
              }
          });
            return a;
      }

      function errorModal(str) {
          $('#Errormodal').modal('show');
          document.getElementById('ErrorText').innerText=str;
      }
      function TimerRdo() {
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
      TimerRdo();
  });



</script>
</html>


