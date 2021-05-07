<?php
$sUr="00FUZZY";
$sfm="BSOR";

if ($sfm=="BSOR"){
    $shape="circle";
    $Title_NM="壓瘡評估作業";
}
if ($sfm=="TPUP"){
    $shape="triangle";
    $Title_NM="管路評估作業";

}
if ($sfm=="CUTS"){
    $shape="square";
    $Title_NM="傷口評估作業";

}



?>
<!DOCTYPEhtml>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>壓瘡評估作業</title>
    <script type="text/javascript" src="../../jquery-3.4.1.js"></script>
    <link rel="stylesheet" href="../../bootstrap-4.3.1-dist/css/bootstrap.min.css">
    <script src="../../bootstrap-4.3.1-dist/js/bootstrap.min.js" type="text/javascript"></script>
    <script src="../../crypto-js.js"></script>
    <script src="../../AESCrypto.js"></script>
    <script src="../../NISCOMMAPI.js"></script>
    <script src="../../JavaScript/jquery-ui.js"></script>
    <script>
       $(document).ready(function () {
           (function () {
               NISPWSFMINI_Timer('ILSGA','A');
               $(".EDIT").hide();
               $(".Main,#MM_B").hide();
           })();

           let imageLoaded = function() {
               let Canvas=$("#CanvasPad")[0];
               let ctx=Canvas.getContext('2d');
               let img=$("img")[0];

               ctx.drawImage(img,0,0,Canvas.width,Canvas.height);
           };

           $("img").each(function () {
              let tmpImg=new Image();
               tmpImg.onload=imageLoaded;
               tmpImg.src=$(this).attr('src');
           });
           //Img=>x,y座標
           //DATA=>Table 欄位值
           //TD_Child=>Table欄位元件
           //TR_CLASSNM=>Table標題欄位名稱
            let Data_obj=new Map();

            let x;//Bed addeventlistener
            const creatTable={
               Default:(obj)=>{
                let Tb_NM_obj=obj.T_NM; // 標題
                let T_ID=[]; // 標題id
                let T_CNM=obj.T_CNM; // td children
                let MM_TEXT_obj=obj.MM_TEXT; //壓傷備註
                let D_edit=obj.D_EDIT; //身高體重
                let Is_Change=obj.IS_CHANGE; //壓瘡=>壓傷


                   $(".area-table").children('table').remove();

                   $(".area-table").append(
                       `
                        <table class="table" id="Data_Table" ">

                        </table>
                        `
                   );


                   $.each(Tb_NM_obj,function (index,val) {
                       //新增左標題
                       let classNm=val.ID_TABITEM===""?'tb'+index:val.ID_TABITEM;


                       $("#Data_Table").append(
                           `
                                       <tr class="${classNm}">
                                             <th>${val.ST_LEFT}</th>
                                       </tr>
                                                 `
                       );

                       T_ID.push(classNm);
                   });

                   if (!Data_obj.has('TD_Child')){
                       Data_obj.set('TD_Child',T_CNM);
                   }
                   if (!Data_obj.has('TR_CLASSNM')){
                       Data_obj.set('TR_CLASSNM',T_ID);
                   }
                   if (!Data_obj.has('NEWDATA')){
                       Data_obj.set('NEWDATA',obj.ST_DATAB);
                   }

                   creatTable.inEdit(D_edit);
                   if (Is_Change==="Y"){
                       $("#T_Btn~label:first,h1").text(function () {
                           let str=$(this)[0].innerText;
                           let reg=/壓瘡/g;
                           $(this)[0].innerText= str.replace(reg,'壓傷');
                       });

                   }
                   if (MM_TEXT_obj!==null){
                       creatTable.inMMText(MM_TEXT_obj);
                    }

               },
               inTableTd:(Page,data)=>{
                let T_CD=Data_obj.get("TD_Child");
                let T_ID=Data_obj.get("TR_CLASSNM");

                if (Page==="A"){

                    if ( !Data_obj.has('IMG') ){
                        Data_obj.set('IMG',data);
                    }

                    $.each(data,function (index,val) {
                        let LEFT=parseInt(val.LEFT);
                        let TOP=parseInt(val.TOP);
                        let Width=parseInt(val.W_TH);
                        let Height=parseInt(val.H_TH);
                        AddShape(val.NUM,'<?php echo $shape?>',LEFT+5,TOP-10,Width,Height);
                    });

                }
                if (Page==="B"){

                   if (Data_obj.has('DATA')){
                       let Data_json=Data_obj.get('DATA');
                       Data_json.push(data);

                   }else {
                       Data_obj.set('DATA',data);
                   }

                   //5/10 補重複去除
                    data= Data_obj.get('DATA')
                        .flat(Infinity)
                        .sort((a,b)=>{return a.NO_NUM-b.NO_NUM});

                    console.log(data);
                   //ele   id=className+No.Number
                    $.each(data,function (ItemNo,item) {
                        let No_Number=item.NO_NUM;//編號
                        $.each(T_CD,function (index,val) {
                            let title_ID=T_ID[index];
                            if (val.length>0 )
                            {
                                //select
                                if ($("#"+title_ID+"_"+ItemNo+"_"+No_Number).length ===0 &&  No_Number!==""){
                                    $("."+title_ID).append(
                                        `
                                        <td>
                                            <select id="${title_ID+"_"+ItemNo+"_"+No_Number}" class='table-edit'></select>
                                        </td>
                                    `
                                    );
                                    $.each(val,function (num,item) {

                                        $("#"+title_ID+"_"+ItemNo+"_"+No_Number).append(
                                            `<option value="${item.ID_TABITEM}">${item.ST_LEFT}</option>`
                                        );
                                    });
                                }

                            }
                            else
                            {
                                //input
                                if ($("#"+title_ID+"_"+ItemNo+"_"+No_Number).length ===0 &&  No_Number!==""){
                                    $("."+title_ID).append(
                                        `
                                          <td>
                                               <input  type="text" class='table-edit' id="${title_ID+"_"+ItemNo+"_"+No_Number}">
                                          </td>
                                    `
                                    );
                                }


                            }

                        });

                        let i=0;
                          for (let key in item){
                            let ele=$("#"+T_ID[i]+"_"+ItemNo+"_"+No_Number);
                              if (ele.is('input')){
                                  //<input>
                                  ele.val(item[key]);
                              }else {
                                  if (item[key]!==""){
                                      //<SELECT>
                                      $("#"+T_ID[i]+"_"+ItemNo+"_"+No_Number+" option[value="+item[key]+"]").prop('selected',true);
                                  }

                              }
                            i++;
                          }


                      });



                }
               },
               inMMText:(data)=>{

                    let En_CharCODE=66;
                    let div_nm={"66":"壓瘡等級說明","65":"護理措施"};
                   for (let key in data){
                       $(".MM_TEXT").append(
                        `
                        <div id="${'MM_'+String.fromCharCode(En_CharCODE)}">
                            <label><b>${div_nm[En_CharCODE]}</b></label>
                        </div>
                        `
                       );


                       $.each(data[key],function (index,val) {
                           $("#MM_"+String.fromCharCode(En_CharCODE)).append(
                               `
                              <p>${val}</p>

                         `
                           );
                       });

                       En_CharCODE--;

                   }

                },
               inEdit:(data)=>{

                  let txtArea_Nm=data.EDIT_AREA;
                  let txtInput=data.EDIT;


                  for (let key in txtArea_Nm) {
                      $(".txtArea").append(
                          `
                           <label for="textArea">${txtArea_Nm['TB_NM']+":"}</label>
                           <textarea id="textArea" class="form-control form-control-lg " readonly></textarea>
                          `
                      )
                  }
                  $.each(txtInput,function (index,val) {
                      $(".txtInput").append(
                          `
                            <div class="input-group-prepend">
                                <span class="input-group-text">${val.TB_NM}</span>
                            </div>
                            <input type="text" class="form-control " readonly>
                          `
                      );
                  });



               }
           };
            var drag_value={
                   containment:'.drop-area',
                   scroll:false,
                   stack:'.draggable',
                   start:function (e) {

                   },
                   drag:function (e) {

                   },
                   stop:function (event, ui) {
                       let Num=$(this).attr('id').split("").filter((value)=>{return !isNaN(parseInt(value));}).join("");
                       let TOP=(Math.round(ui.position.top)).toString();
                       let LEFT=(Math.round(ui.position.left)).toString();
                       let DataObj=Data_obj.get('IMG');
                       let ItemIndex ="";
                       $.each(DataObj,function (index,val) {
                            if (val.NUM===Num){
                                ItemIndex=index;
                            }
                        });

                       Data_obj.get('IMG')[ItemIndex].TOP=TOP;
                       Data_obj.get('IMG')[ItemIndex].LEFT=LEFT;

                       console.log('PX'+LEFT,'PY'+TOP);
                       GetPIXELRegion(LEFT,TOP);
                       /*console.log(Data_obj.get('IMG')[ItemIndex]);*/
                   }
               };

           $(".drop-area").on("click",function (e) {
               //e.clientX , e.clientY
                console.log(e.offsetX , e.offsetY);
               let is_Add=$("#AddSign").val() === "0";
               if (is_Add){
                  let shape='<?php echo $shape?>';
                  let MaxNum=Math.max(...$("."+shape).map(function () {let str= $(this).attr('id'); return  str.substr(1,str.length);}))+1;
                  let copyObj=JSON.parse(JSON.stringify(Data_obj.get('NEWDATA')));
                  copyObj.NO_NUM=MaxNum.toString();
                  creatTable.inTableTd('B',[copyObj]);

                  AddShape(MaxNum,shape,e.offsetX+10,e.offsetY,'','');

                  $("#AddSign").val("");
               }
           });
           $(document).on('click','button',function (e) {
              let id=$(this).attr('id');
              let sTraID=$("#sTraID").val();
              switch (id) {
                  case "sbed":
                      switch (checkBEDwindow()) {
                          case "false":
                              errorModal("責任床位視窗已開啟");
                              break;
                          case "true":
                              try {
                                  x=window.open("/webservice/NISPRWCBED.php?str="+AESEnCode("sFm=ILSGA&sIdUser=<?php echo $sUr?>"),"責任床位",'width=850px,height=650px,scrollbars=yes,resizable=no');

                              }catch (e) {
                                  console.log(e);
                              }
                              break;
                      }
                      x.bedcallback=bedcallback;
                      break;
                  case "A":
                      GetPageJson('A',sTraID);
                      $(".area-table,#MM_B").hide();
                      $(".Main,#MM_A").show(500);
                      break;
                  case "B":
                      GetPageJson('B',sTraID);
                      $(".Main,#MM_A").hide(500);
                      $(".area-table,#MM_B").show();

                      break;
                  default:
                      break;
              }
           });

           $(".sign").on('click',function () {
               $("#AddSign").val($(this).val());

               //!*2.放大 3.縮小
               changeThisSize($(this).val());
           });
           $(document).on("click mousedown",".draggable",function (e) {

               let is_Add=$("#AddSign").val() === "0";
               $(".draggable").css({'background-color' : '' ,'color':'',"border":"","opacity":1});

               $("#div_nm").val($(this).attr('id'));

               //選取後focus
               if ($(this).attr('class').indexOf("triangle ")!==-1){
                   //三角形
                   $(this).css({ "border-color": "transparent transparent yellow transparent"});
               }else {
                   //不是三角形
                   $(this).css({
                       "background-color": "yellow",
                       "opacity":"0.5"
                   });
               }

               if (!is_Add){
                   //  $(this).remove();
                   $('.sign').css({'background-color' : '' ,'color':'',"border":""});
               }
           });


           //動態填值
           $(document).on("change",".table-edit",function () {
              let this_Str=$(this).attr('id').split("_");

            /*
              let No_Num=this_Str[2];

              let ThisData=Data_obj.get('DATA')[Index];*/
              let TD_class=$(this).parent().parent().attr('class');

               let rowIndex=$(this).parent().index()-1;
               let Index=Data_obj.get('TR_CLASSNM').map((value,index)=>{
                   if (TD_class===value){
                       return index;
                   }

               }).join("");
               let ThisData=Data_obj.get('DATA')[rowIndex];

               let i=0;
               for (let key in ThisData){
                   if (parseInt(Index)===i)
                   {
                       ThisData[key]=$(this).val();

                   }
                   i++;
               }


               console.log(ThisData);

          /*     let index= thisID.substr(-1,thisID.length-Data_obj.get('TR_CLASSNM').map((value)=>{
                   if (thisID.substr(0,value.length)===value){
                       return value
                   }
               }).join("").length);


               let thisClass=$(this).parent().parent().attr('class');
               let Class_index=Data_obj.get('TR_CLASSNM').indexOf(thisClass);
               let i=0;
               for (let key in ThisData){
                   if (Class_index===i)
                   {
                       ThisData[key]=$(this).val();
                   }
                    i++;
                }
               console.log(Class_index,ThisData);*/

           });
           $(document).on('change','input[name=sRdoDateTime]',function () {

               let TimeNow=new Date();
               let yyyy=TimeNow.toLocaleDateString().slice(0,4);
               let MM=(TimeNow.getMonth()+1<10?'0':'')+(TimeNow.getMonth()+1);
               let dd=(TimeNow.getDate()<10?'0':'')+TimeNow.getDate();
               let  h=(TimeNow.getHours()<10?'0':'')+TimeNow.getHours();
               let  m=(TimeNow.getMinutes()<10?'0':'')+TimeNow.getMinutes();
               let Timeit=($(this).val()).split("");

               let timer=Timeit.filter(function (value) { return  value!==":"});
               let timerVal=$(this).attr('id')==="ISTM00000005"?h+m:timer.join("");
               let time_ID=$(this).attr('id');
               $("#sDate").val(yyyy-1911+MM+dd);
               $("#sTime").val(timerVal);
           });


           function AddShape(txt,shape,X,Y,W,H) {
               let text_ele="";
               let shape_Nm="";

               if (shape==="triangle"){
                   text_ele='<div>'+txt+'</div>';
                   shape_Nm="t";
               }
               if (shape==="square"){
                   text_ele="<div>"+txt+"</div>";
                   shape_Nm="s";
               }
               if (shape==="circle"){
                   text_ele="<p>"+txt+"</p>";
                   shape_Nm="c";
               }

               if ($('#'+shape_Nm+txt).length >　0){
                   return ;
               }

               $("#CanvasPad").before(
                   `
                      <div class="${'draggable '+shape}" id="${shape_Nm+txt}" >
                           ${text_ele}
                      </div>
                   `
               );
               $("#"+shape_Nm+txt).css({
                  "left" :X+"px",
                  "top" :Y+"px",
                  "width" :W+"px",
                  "height" :H+"px"
               });


               $(".draggable").each(function () {
                   $(this).draggable(drag_value);
               });
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
           function changeThisSize(num) {

               if (parseInt(num)<2){
                   return false;
               }
               let id=$("#div_nm").val();


               let n=num==="2"?10:-10;
               let shape=id.substring(0, 1) ;

               let ele=$("#"+id);
               let W_H="";

               if (shape==="t")
            {

                  let border_width=ele.css('border-width')
                      .split(" ")
                      .map(value => parseInt(value)+n+'px');
                  W_H={
                      "border-width":border_width.join(" ")
                  }
            }
            else
            {

                  W_H={
                      "height":ele.innerHeight()+n,
                      "width": ele.innerWidth()+n,
                      "background-color":""
                  };
              }

               $("#"+id).css(W_H);
           }
           function bedcallback(data){
               let str=AESDeCode(data);
               let dataObj=JSON.parse(str)[0];
               let idPt=dataObj.IDPT;
               let INPt=dataObj.IDINPT;
               let sBed=dataObj.SBED;
               let P_NM=dataObj.DataTxt;


               console.log(dataObj);

               $("#DA_idpt").val(idPt);
               $("#DA_idinpt").val(INPt);
               $("#DA_sBed").val(sBed);
               $("#DataTxt").val(P_NM);
               GetINIJson('<?php echo 'BSOR'.$sfm?>',idPt,INPt);


                $(".Page").next('div').hide();
                $(".area-table").hide();






           }
           function GetINIJson(sfm,idPt,INPt){
               $.ajax("/webservice/NISPWSTRAINI.php?str="+AESEnCode('sFm='+sfm+'&idPt='+idPt+'&INPt='+INPt+"&sUr="+'<?php echo $sUr?>'))
                   .done(function(data) {
                        let obj= JSON.parse(AESDeCode(data));
                       creatTable.Default(obj);

                       $("#sTraID").val(obj.sTraID);
                       $("#sSave").val(obj.sSave);
                   })
                   .fail(function(XMLHttpResponse,textStatus,errorThrown) {
                       console.log(
                           "1 返回失敗,XMLHttpResponse.readyState:"+XMLHttpResponse.readyState+XMLHttpResponse.responseText+
                           "2 返回失敗,XMLHttpResponse.status:"+XMLHttpResponse.status+
                           "3 返回失敗,textStatus:"+textStatus+
                           "4 返回失敗,errorThrown:"+errorThrown
                       );
                   });
           }
           function GetPageJson(Page,sTraID) {
               $.ajax({
                   url:"/webservice/NISPWSGETPRE.php?str="+AESEnCode("sFm=BSOR&sTraID="+sTraID+"&sPg="+Page),
                   dataType:"text",
                   success:function (data){
                         let obj= JSON.parse(AESDeCode(data));
                        console.log(obj);
                        creatTable.inTableTd(Page,obj);

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
           function NISPWSFMINI_Timer(sFm,Page) {
               $.ajax({
                   url:"/webservice/NISPWSFMINI.php?str="+AESEnCode("sFm="+sFm+"&sPg="+Page),
                   type:"POST",
                   dataType:"text",
                   success:function(data){
                       let obj=JSON.parse(AESDeCode(data));
                       let arr=JSON.parse(obj.ST_PREA);

                       $.each(arr,function (index,item) {
                           $("#ISTM").append(
                               `
                                <label style='font-size: 4.5vmin'>
                                    <input type='radio' name='sRdoDateTime' id='${item.T_ID}' value='${item.name}' style='width: 6vmin;height: 6vmin' >${item.name}
                                </label>
                                `
                           )
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
           function GetPIXELRegion(X,Y){
               $.ajax("/webservice/NISBSORPIXEL.php?str="+AESEnCode('&PIXEL_X='+X+'&PIXEL_Y='+Y))
                   .done(function(data) {
                       let obj= JSON.parse(AESDeCode(data));
                        console.log(obj);
                   })
                   .fail(function(XMLHttpResponse,textStatus,errorThrown) {
                       console.log(
                           "1 返回失敗,XMLHttpResponse.readyState:"+XMLHttpResponse.readyState+XMLHttpResponse.responseText+
                           "2 返回失敗,XMLHttpResponse.status:"+XMLHttpResponse.status+
                           "3 返回失敗,textStatus:"+textStatus+
                           "4 返回失敗,errorThrown:"+errorThrown
                       );
                   });
           }
        });

    </script>
</head>
<style>
  /*  .Parametertable*/
      .drop-area{
          width: 395px;
          height: 425px;

      }

      .area-table{
          display: none;
          border: 1px solid #dee2e6;
          overflow-x:auto;
      }

    table {
        border: 1px solid #dee2e6;
          table-layout: auto;
          width: 100%;
      }

      tr>th{
          position: sticky;
          left: 0;
          min-width: 200px;
          z-index:1;
          background-color: white;
      }

      td,th,tr{
          border: 1px solid #dee2e6;
      }


    .container .title button{
        color: white;
        font-size: 4vmin;
        margin-top: 5px;
        margin-bottom: 5px;
    }
    #DataTxt{
        font-size: 4.5vmin;
        background-color: #FFFBCC;
        border-radius:3px;
        margin-top: 5px;
        color: black;
    }
    .container .Otimer{
        margin:5px 0 5px 15px;
        padding:5px 10px 0 0;
        font-size: 4vmin;
        background-color: #baeeff;
        border-radius:3px;
    }


    .Page~div{
        margin-top: 10px;
      }



/*************繪圖*****************/
  #CanvasPad{
      width: 395px;
      height: 425px;
      z-index: 1;
      border: 1px grey solid;
  }


  .circle{
      border-radius: 50%;
      height: 15px;
      width: 15px;
      user-select: none;
      display: flex;
      align-items: center;
      justify-content: center;
      position: absolute;
      border: 3px solid red;
      z-index: 0;
  }
  .circle>p{
      padding-top: 13px;
      z-index: 1;

  }


  .triangle{
      height: 0px;
      width: 0px;
      border-color: transparent transparent #FF9797 transparent;
      border-style: solid solid solid solid;
      border-width:  0 5px 10px 5px;
      position: absolute;
      display: flex;
  }

  .square{
      height: 15px;
      width: 15px;
      border: 2px solid red;
      position: absolute;
      display: flex;
  }


  .triangle>div {
      margin-left: -6px;
      margin-top: 2px;
      font-size: 10px;
  }

  .square>div{
      text-align: center;

  }


/*時間*/
  .DateTime::placeholder{
      font-size: 3.5vmin;
  }
  .DateTime{
      font-size: 3.5vmin;
  }
  #ISTM>label{
      padding:10px 0 0 0 ;
  }


  /*備註*/
.MM_TEXT>div{
   border: none;
    background-color: #FFFBCC;
}
.MM_TEXT>div:nth-child(1){
    display: none;
}
  .MM_TEXT>div>P{

      font-size: 2vmin;
  }

  textarea{
      overflow-y: scroll;
      resize: none
  }
  .form-control[readonly]{
      background-color: white;
  }

   .Main>div>div{
       margin: 15px 0 0 0;
   }
    .CanvasRow{
        margin: 0 15px 0 0;
    }
</style>
<body>
<div class="container">
    <div class="Parametertable">
        <input id="DA_idpt"     value=""  type="text"  placeholder="DA_idpt">
        <input id="DA_idinpt"   value=""  type="text"  placeholder="DA_idinpt">
        <input id="DA_sBed"     value=""  type="text"  placeholder="DA_sBed">
        <input id="sSave"       value=""  type="text"  placeholder="sSave">
        <input id="sTraID"      value=""  type="text"  placeholder="sTraID">
        <input id="div_nm"      value=""  type="text"  placeholder="div_nm">
        <input id="AddSign"     value=""  type="text"  placeholder="AddSign">
        <input id="sfm"         value="<?php echo $sfm?>" type="text">
        <img src="../../img/BedSore.jpg" style="display: none">
    </div>

    <div class="row">
        <div class="col-12">
            <h1><?php echo $Title_NM?></h1>
        </div>

        <div class="col-12">

            <span class="title">
                <button type="button" id="SubmitBtn" class="btn btn-primary btn-md" >儲存</button>
                <button type="button" id="SerchBtn" class="btn btn-primary btn-md" >查詢</button>
                <button type="button" id="DELBtn" class="btn btn-primary btn-md"  data-toggle="modal" data-target="#DELModal">作廢</button>
                <button type="button" id="ReSetBtn" class="btn btn-primary btn-md"  >清除</button>
                <button type="button"  class="btn btn-warning btn-md"  id="sbed" >責任床位</button>
            </span>

            <button type="button" class="btn btn-secondary btn-md" disabled style="display: none">回主畫面</button>

            <span style="margin-left: 1px">
                <b>使用者:<?php echo $sUr?></b>
            </span>

        </div>

        <div class="col-12">
            <input id="DataTxt" value="" class="form-control" type="text" disabled>
        </div>


        <div class="Otimer  col-lg-11">
            <div class="row">
                <div class="input-group col-12">
                    <label for="sDate" >評估時間:</label>
                    <input type="text" id="sDate" value="" class="DateTime form-control form-control-lg"  placeholder="YYYMMDD" maxlength="7" autocomplete="off">
                    <input type="text" id="sTime" value="" class="DateTime form-control form-control-lg" placeholder="HHMM" maxlength="4" autocomplete="off">
                </div>


                <div class="input-group col-12" id="ISTM">

                </div>


                </div>

        </div>

        <div class="Page col-12">
            <button type="button" id="A" class="btn btn-primary btn-lg" >部位圖</button>
            <button type="button" id="B" class="btn btn-primary btn-lg">評估資料</button>
        </div>


        <div class="Main col-12">
            <div class="row">
                <div class="CanvasRow col-lg-5 col-md-12 col-sm-12">
                    <div class="drop-area">
                        <canvas id="CanvasPad" height="425" width="395" ></canvas>
                    </div>
                </div>

                <div class="SignRow col-lg-7 col-md-12 col-sm-12">
                    <div class="col-12">
                        <button  class="sign btn btn-outline-primary" value="0">新增</button>
                        <button  class="sign btn btn-outline-primary" value="1">修改</button>
                        <button  class="sign btn btn-outline-primary" value="2">+</button>
                        <button  class="sign btn btn-outline-primary" value="3">-</button>
                    </div>

                    <div class="col-12">
                         <div class="EDIT row">
                              <div class="txtArea col-12">

                              </div>

                              <div class="txtInput col-12 input-group">

                              </div>

                              <div class="MM_TEXT col-12">

                              </div>
                        </div>
                    </div>


                </div>

            </div>

        </div>

        <div class="B area-table">

        </div>

    </div>

</div>


</body>
</html>
