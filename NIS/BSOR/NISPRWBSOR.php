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
            const creatTable=
                {
                  Default:()=>{
                      $(".body_iframe").after(
                          `
                          <div class="area-table">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th scope="col">編號</th>
                                            <th scope="col">部位</th>
                                            <th scope="col">來源</th>
                                            <th scope="col">大小(長*寬)</th>

                                            <th scope="col">深度</th>
                                            <th scope="col">等級</th>
                                            <th scope="col">異味</th>
                                            <th scope="col">物量</th>

                                            <th scope="col">顏色</th>
                                            <th scope="col">換藥</th>
                                            <th scope="col">處置</th>
                                            <th scope="col">備註</th>

                                            <th scope="col">評估人員</th>
                                            <th scope="col">結案狀態</th>
                                            <th scope="col">結案日期</th>

                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <th>1</th>
                                            <td>鼻</td>
                                            <td><select></select></td>
                                            <td></td>

                                            <td></td>
                                            <td><select></select></td>
                                            <td><select></select></td>
                                            <td><select></select></td>

                                            <td><select></select></td>
                                            <td><select></select></td>
                                            <td><select></select></td>
                                            <td></td>

                                            <td></td>
                                            <td><select></select></td>
                                            <td></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                           `
                      )
                  }
                };

           (function () {
               creatTable.Default();

           })();


           $(document).on('click','button',function (e) {
              let id=$(this).attr('id');

              switch (id) {
                  case "A":
                      $(".body_iframe").show();
                       $(".area-table").hide();
                      break;
                  case "B":

                      $(".body_iframe").hide();
                      $(".area-table").show();
                      break;
                  default:
                      break;
              }
           });

          $("input[type=radio]").on('change',function () {
              postMsgToiframe($(this).val());
          });
            window.addEventListener("message",function (e) {
                console.log(e);
            });

            function postMsgToiframe(msg) {
                window.frames["body_iframe"].postMessage(msg,"http://localhost/NIS/BSOR/NISBSORBODY.php");
            }
        });

    </script>
</head>
<style>
    .area-table,.Parametertable{
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
    iframe{
        width: 800px;
        height: 400px;
    }

</style>
<body>
<div class="container">
    <div class="Parametertable">
        <input value="" id="sign_value">
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


        <div class="col-12">
            <div class="row">
                <div class="Shape col-4">
                    <input id="T_Btn"  class="radio-lg" type="radio" name="Shape" value="triangle" >
                    <label for="T_Btn" class="label-lg">壓瘡</label>

                    <input id="S_Btn"   class="radio-lg" type="radio" name="Shape" value="square" >
                    <label for="S_Btn"  class="label-lg">管路</label>

                    <input id="C_Btn"   class="radio-lg" type="radio" name="Shape" value="circle" >
                    <label for="C_Btn"  class="label-lg">傷口</label>
                </div>
                <div class="col-8">
                    <button type="button" id="A" class="btn btn-primary" >部位圖</button>
                    <button type="button" id="B" class="btn btn-primary">評估資料</button>
                </div>
            </div>

        </div>
    </div>

    <div class="body_iframe">
        <iframe name="body_iframe" src="NISBSORBODY.php" scrolling="no" ></iframe> <!--frameBorder="0"-->
    </div>

</div>


</body>
</html>
