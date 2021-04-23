<?php
$sUr="00FUZZY";
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
               let Canvas=$("#CanvasPad")[0];
               let ctx=Canvas.getContext('2d');
               let img=$("img")[0];
               ctx.drawImage(img,0,0,Canvas.width,Canvas.height);
               GetINIJson('','');
           })();


            let i=0;//紀錄td class
            let CNM=new Map();
            let x;//Bed addeventlistener
            const creatTable={
               Default:(obj)=>{

                let Tb_NM_obj=obj.Tittle_Nm;
                let Tb_CNM_obj=obj.Tittle_CNm;
                let MM_TEXT_obj=obj.MM_TEXT;


                for (let  key in Tb_NM_obj){

                    let TB_title_Nm=Tb_NM_obj[key].TB_NM;

                    $(".area-table").append(
                        `
                                <table class="table" id="${key+'_tb'}">



                                </table>

                            `
                    );

                    $.each(TB_title_Nm,function (index,val) {
                        //新增左標題
                        $("#"+key+'_tb').append(
                            `
                                         <tr class="${'C_NM'+key+index}">
                                                <th>${val.ST_LEFT}</th>
                                         </tr>
                                        `
                        );

                    });

                    if (!CNM.has(key)){
                        CNM.set(key,Tb_CNM_obj[key]);
                    }

                    creatTable.inTableTd(key,Tb_CNM_obj[key]);
                }
                creatTable.inMMText(MM_TEXT_obj);
               },
               inTableTd:(key,data)=>{
                   /* key =>
                      BSOR 壓瘡(傷)
                      TUPT 管路
                      CUTS 傷口
                   */

                   $.each(data,function (index,val) {
                       if (val.length>0){
                           $('.C_NM'+key+index).append(`<td><select class="${key+i}" id="${key+i+'sel_'+index}"></select><td>`);

                           $.each(val,function (num,item) {
                               $("#"+key+i+'sel_'+index).append(
                                   `<option>${item.ST_LEFT}</option>`
                               )
                           });


                       }else {
                           $('.C_NM'+key+index).append(`<td><input  class="${key+i}" id="${key+i+'edit_'+index}" type="text"><td>`);
                       }

                   });

                   i++;
                   CNM.set('NUM',i);
               },
               inMMText:(data)=>{
                   console.log(data);
                   $.each(data.MM_TEXT,function (index,val) {
                       $(".MM_TEXT").append(
                           `
                            <p>${val}</p>
                           `
                       )
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
                        console.log(ui.position.left,ui.position.top);
                   }
               };

           $(".draggable").draggable(drag_value);
           $(".drop-area").on("click",function (e) {

               //e.clientX , e.clientY


               let is_Add=$("#AddSign").val() === "0";
               if (is_Add){
                   let shape=$("input[type=radio]:checked").val();

                   AddShape(0,shape,e.offsetX+10,e.offsetY);

                   $(".draggable").each(function () {
                       $(this).draggable(drag_value);
                   });
                   $("#AddSign").val("");
               }
           });
           $(document).on('click','button',function (e) {
              let id=$(this).attr('id');

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
                       $(".drop-area").parent().show(500);
                       $(".area-table").hide();
                      break;
                  case "B":
                   if (!$("input[type=radio]").is(':checked')){
                       alert("請選擇評估方式");
                       return;
                   }
                      $(".drop-area").parent().hide(500);
                      $(".area-table").show();
                      break;
                  default:
                      break;
              }
           });
           $("input[type=radio]").on('change',function () {

              let TagNm= $(this).val();
              let t_key="";
              if (TagNm==="triangle" ){
                  t_key="BSOR";
              }else if (TagNm==="square" ) {
                  t_key="TUPT";
              }else{
                  t_key="CUTS";
              }

              $(".table").hide();
              $("#"+t_key+'_tb').show(300);
          });
           $(".sign").on('click',function () {
               $("#AddSign").val($(this).val());
               let Nm_type=$(this).val();
               if (Nm_type!=="0" && Nm_type !=="1"){
                   //*2.放大 3.縮小
                   changeThisSize(Nm_type);
               }


           });
           $(document).on("click",".draggable",function () {
               let is_Add=$("#AddSign").val() === "0";
               $(".draggable").css({'background-color' : '' ,'color':'',"border":""});

               $("#div_nm").val($(this).attr('id'));

               //選取後focus
               if ($(this).attr('class').indexOf("triangle ")!==-1){
                   //三角形
                   $(this).css({ "border-color": "transparent transparent yellow transparent"});
               }else {
                   //不是三角形
                   $(this).css({ "background-color": "yellow"});
               }

               if (!is_Add){
                   //  $(this).remove();
                   $('.sign').css({'background-color' : '' ,'color':'',"border":""});
               }
           });



           function AddShape(txt,shape,X,Y) {
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


               $("#CanvasPad").before(
                   `
                      <div class="${'draggable '+shape}" id="${shape_Nm+txt}" style="left: ${X+'px'};top: ${Y+'px'}">
                           ${text_ele}
                      </div>
                   `
               );

               console.log($("#"+shape_Nm+txt));


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

           function changeThisSize(num) {
               let id=$("#div_nm").val();
               let n=num==="2"?1:-1;
               let shape=id.substring(0, 1) ;
               let ele=$("#"+id);
               let W_H="";

              if (shape==="t"){
                  let border_width=ele.css('border-width')
                      .split(" ")
                      .map(value => parseInt(value)+n+'px');
                  W_H={
                      "border-width":border_width.join(" ")
                  }
              }else {
                  W_H={
                      "height": ele.innerHeight()+n,
                     "width": ele.innerWidth()+n
                  }

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
               $("#DA_InPt").val(INPt);
               $("#DA_sBed").val(sBed);
               $("#DataTxt").val(P_NM);

           }
           function GetINIJson(idPt,INPt){
               $.ajax("/webservice/NISPWSTRAINI.php?str="+AESEnCode('sFm=BSOR&idPt='+idPt+'&INPt='+INPt+"&sUr="+'<?php echo $sUr?>'))
                   .done(function(data) {
                        let obj= JSON.parse(AESDeCode(data));
                        creatTable.Default(obj);
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
          width: 400px;
          height: 400px;

      }
    .area-table{
        display: none;
    }
    .container .title button{
        color: white;
        font-size: 4vmin;
        margin-top: 5px;
        margin-bottom: 5px;
    }
    #DataTxt{
        font-size: 3vmin;
        background-color: #FFFBCC;
        border-radius:3px;
        margin-top: 5px;
        color: black;
    }
    .container .Otimer{
        margin-top:5px;
        font-size: 4vmin;
    }


    .Shape{
        padding-left: 15px;
    }
    .Shape>input[type=radio]{
        width:30px ;
        height:30px;
    }

    .Shape>label {
        font-size: 25px;
    }
    .Page~div{
        margin-top: 10px;
      }
    iframe{
        width: 800px;
        height: 400px;
    }

  #CanvasPad{
      width: 400px;
      height: 400px;
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
      background-color: lightgreen;
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
      background-color: cyan;
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
  .MM_TEXT{
      margin:5px 0 0 0;
     border: mediumslateblue 2px dashed;
  }
.MM_TEXT>p{
    font-size: 2.2vmin;
    margin-bottom: -5px;
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
        <img src="../../img/BedSore.jpg" style="display: none">
    </div>

    <div class="row">
        <div class="col-12">
            <h1>壓瘡評估作業</h1>
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

        <div class="Otimer col-12" >
            <div class="DateTime">
                <div class="row">
                    <div class="col-12">
                        <label >評估時間:</label>
                        <input type="text" id="sDate" value=""  placeholder="YYYMMDD" maxlength="7" autocomplete="off">
                        <input type="text" id="sTime" value=""  placeholder="HHMM" maxlength="4" autocomplete="off">
                    </div>
                    <div class="col-12">
                        <div id="ISTM"></div>
                    </div>
                </div>
            </div>
        </div>


        <div class="Page col-12">
            <button type="button" id="A" class="btn btn-primary" >部位圖</button>
            <button type="button" id="B" class="btn btn-primary">評估資料</button>
        </div>


        <div class="col-12">
            <div class="row">
                <div class="col-lg-5 col-md-12 col-sm-12">
                    <div class="drop-area">
                        <canvas id="CanvasPad" height="425" width="395" style="border: grey 1px solid"></canvas>
                    </div>
                </div>
                <div class="col-lg-7 col-md-12 col-sm-12">
                    <div class="Shape col-12">
                        <input id="T_Btn"  class="radio-lg" type="radio" name="Shape" value="triangle" >
                        <label for="T_Btn" class="label-lg">壓瘡</label>

                        <input id="S_Btn"   class="radio-lg" type="radio" name="Shape" value="square" >
                        <label for="S_Btn"  class="label-lg">管路</label>

                        <input id="C_Btn"   class="radio-lg" type="radio" name="Shape" value="circle" >
                        <label for="C_Btn"  class="label-lg">傷口</label>
                    </div>

                    <div class="col-12">
                        <button  class="sign btn btn-outline-primary" value="0">新增</button>
                        <button  class="sign btn btn-outline-primary" value="1">修改</button>
                        <button  class="sign btn btn-outline-primary" value="2">+</button>
                        <button  class="sign btn btn-outline-primary" value="3">-</button>
                    </div>
                    <div class="MM_TEXT col-12">

                    </div>
                </div>

            </div>

        </div>

        <div class="area-table">

        </div>

    </div>

</div>


</body>
</html>
