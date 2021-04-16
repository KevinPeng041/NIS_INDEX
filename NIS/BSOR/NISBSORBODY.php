<?php
$sUr="00FUZZY";
?>
<!DOCTYPEhtml>
<html lang="en" xmlns="http://www.w3.org/1999/html" xmlns="http://www.w3.org/1999/html">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>壓瘡評估(部位圖)作業</title>
    <script type="text/javascript" src="../../jquery-3.4.1.js"></script>
    <link rel="stylesheet" href="../../bootstrap-4.3.1-dist/css/bootstrap.min.css">
    <script src="../../bootstrap-4.3.1-dist/js/bootstrap.min.js" type="text/javascript"></script>
    <script src="../../crypto-js.js"></script>
    <script src="../../AESCrypto.js"></script>
    <script src="../../NISCOMMAPI.js"></script>
    <script src="../../JavaScript/jquery-ui.js"></script>
    <script>
      $(document).ready(function () {
          let Canvas=$("#CanvasPad")[0];
          let ctx=Canvas.getContext('2d');
          let img=$("img")[0];

          var drag_value={
              containment:'.drop-area',
              scroll:false,
              stack:'.draggable',
              start:function (e) {
                console.log(e);
              },
              drag:function (e) {

              },
              stop:function (e) {

              }
          };
          var div_Num=0;
          ctx.drawImage(img,0,0,Canvas.width,Canvas.height);
          $(".draggable").draggable(drag_value);



          $(".drop-area").on("click",function (e) {
              let is_Add=$("#AddSign").val() === "0";
              let shape_Nm="";
              if (is_Add){

                  let shape=$("#Sign_Shape").val();
                  let text_ele="";
                  if (shape==="triangle"){
                      text_ele='<div>'+div_Num+'</div>';
                      shape_Nm="t";
                  }
                  if (shape==="square"){
                      text_ele="<div>"+div_Num+"</div>";
                      shape_Nm="s";
                  }
                  if (shape==="circle"){
                      text_ele="<p>"+div_Num+"</p>";
                      shape_Nm="c";
                  }



                  $("#CanvasPad").before(
                      `
                      <div class="${'draggable '+shape}" id="${shape_Nm+div_Num}" style="left: ${e.clientX+'px'};top: ${e.clientY+'px'}">
                           ${text_ele}
                      </div>
                   `
                  );

                  $(".draggable").each(function () {
                      $(this).draggable(drag_value);

                  });
                  $('.sign').css({'background-color' : '' ,'color':'',"border":""});
                  $("input[name='Shape']",parent.document).prop('checked',false);
                  $(".val_input").val("");
                  div_Num++;
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
                  $(".val_input").val("");
              }
          });

          $("button").on('click',function () {
              $("#AddSign").val($(this).val());

              let Nm_type=$(this).val();

              if (Nm_type!=="0" && Nm_type !=="1"){
                  //*2放大 3縮小
                  changeThisSize(Nm_type);
              }


          });



          // 監聽母視窗傳來的訊息
          window.addEventListener('message', function(e) {

              if (e.origin!=="http://localhost"){
                  return;
              }
              $("#Sign_Shape").val(e.data);
              e.source.postMessage(e.data);//回傳值給母視窗

          },false);

          function changeThisSize(num) {
              let id=  $("#div_nm").val();
              let n=num==="2"?1:-1;
              let border_width=$("#"+id).css('border-width')
                  .split(" ")
                  .map(value => parseInt(value)+n+'px');

              $("#"+id).css('border-width',border_width.join(" "));

          }
          function postMsgToParent(msg) {
              window.parent.postMessage(msg,'http://localhost/NIS/BSOR/NISPRWBSOR.php');
          }
      });

    </script>
</head>
<style>
    .drop-area{
        width: 400px;
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
        height: 30px;
        width: 30px;
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
        border-color: transparent transparent #FFDEDE transparent;
        border-style: solid solid solid solid;
        border-width:  0px 5px 10px 5px;
        position: absolute;
        display: flex;
    }

    .square{
        height: 30px;
        width: 30px;
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

    input{
      /*  display: none;*/
    }
</style>

<body>
<div>
    <div class="drop-area">
        <canvas id="CanvasPad" height="800" width="400"></canvas>
    </div>
    <span style="position:absolute;top: 0px;left:400px">
        <button  class="sign btn btn-outline-primary" value="0">新增</button>
        <button  class="sign btn btn-outline-primary" value="1">修改</button>
        <button  class="sign btn btn-outline-primary" value="2">+</button>
        <button  class="sign btn btn-outline-primary" value="3">-</button>
    </span>
</div>

<input class="val_input" type="text" id="AddSign">
<input class="val_input" type="text" id="Sign_Shape">
<input value="" type="text" id="div_nm">


<img src="../../img/BedSore.jpg" style="display: none">
</body>
</html>
