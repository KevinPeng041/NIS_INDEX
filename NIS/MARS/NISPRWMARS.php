<?php
include '../../NISPWSIFSCR.php';
$str = $_GET['str'];
$replaceSpace = str_replace(' ', '+', $str);//空白先替換+
parse_str(AESDeCode($replaceSpace), $output);

/*$sUr=$output['sIdUser'];
$sfm=$output['sFm'];*/
$sUr = '00FUZZY';
$sfm = 'MARS';
?>
<!DOCTYPEhtml>
<html lang="en">
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<title>藥物治療作業</title>
<script type="text/javascript" src="../../jquery-3.4.1.js"></script>
<link rel="stylesheet" href="../../bootstrap-4.3.1-dist/css/bootstrap.min.css">
<script src="../../bootstrap-4.3.1-dist/js/bootstrap.min.js" type="text/javascript"></script>
<script src="../../crypto-js.js"></script>
<script src="../../AESCrypto.js"></script>
<script src="../../JavaScript/jquery-ui.js"></script>
<script>


    $(document).ready(function () {

        var BedWindow, Serchwindow;
        var Info = {
            Page:'',
            sTraID: '',
            IdPt: '',
            InPt: '',
            sBed: '',
            ssTAT: ''
        };
        var PageInfo = new Map();

        $(document).on('click', 'button', function () {
            let btnID = $(this).attr('id');
            let btnCls = $(this).attr('class').split(" ")[0];
            let IdPt = Info.IdPt;
            let InPt = Info.InPt;

            switch (btnID) {
                case "sbed":
                    if (!checkBEDwindow()) {
                        alert("責任床位視窗已開啟");
                        return;
                    } else {
                        try {
                            BedWindow = window.open("/webservice/NISPRWCBED.php?str=" + AESEnCode("sFm=ILSGA&sIdUser=<?php echo $sUr?>"),
                                "責任床位", 'width=850px,height=650px,scrollbars=yes,resizable=no');

                        } catch (e) {
                            console.log(e);
                        }
                    }

                    BedWindow.bedcallback = bedcallback;
                    break;
                case "SearchBtn":
                    if (!checkSerchwindow()) {
                        alert("查詢視窗已開啟");
                        return;
                    } else {
                        let PName = $("#DataTxt").val();
                        let BEGDATE = $("#sDate").val();
                        let BEGTIME = $("#sTime").val();
                        if (IdPt.trim() === "" || InPt.trim() === "" || PName.trim() === "") {
                            alert('請先選擇病人');
                            return false;
                        }

                        Serchwindow = window.open("/webservice/NISPWSLKQRY.php?str=" +
                            AESEnCode("sFm=" + "<?php echo $sfm?>" + "&PageVal=" + "" + "&DA_idpt=" + IdPt + "&DA_idinpt=" + InPt
                                + "&sUser=" + "<?php echo $sUr?>" + "&NM_PATIENT=" + PName + "&BEGDATE=" + BEGDATE + "&BEGTIME=" + BEGTIME)
                            , "<?php echo $sfm?>", 'width=750px,height=650px,scrollbars=yes,resizable=no');
                    }
                    Serchwindow.Serchcallback = Serchcallback;
                    break;
            }

            if (btnCls === "ShowBigPic") {
                $(".ImgShowModalBody").children().remove();
                let imgStyle = {
                    "cursor": "default",
                    "width": "100%"
                };

                $(this).parent().parent().prev().prev().prev().children().children().clone().css(imgStyle).appendTo(".ImgShowModalBody");
                $("#ImgShow").modal('show');
            }

        });

        $(document).on('click', 'img', function () {
            let imgID = $(this).attr('class').split(" ")[0];
            if (imgID === "img-More") {
                let More_STM = $(this).parent().parent().prev().prev().children().first().text();//完整藥名
                $(".MoreInfo:eq(0)").text(More_STM);
                $("#ImgMore").modal('show');
            }
        });


        $(".Page").click(function () {
            let Page = $(this).val();
            let TransKey = Info.sTraID;
            Info.Page=Page;
            $(".table-container").scrollTop(0);
            $('.Page').each(function () {
                $(this).css({'background-color': '', 'color': '', "border": ""});
            });
            $(this).css({'background-color': '#F9F900', 'color': '#000000', "border": ""});

            if ($(".page" + Page).length === 0) {
                let PageJson = JSON.parse(GetPageJson(Page, TransKey));

                if (!PageInfo.has(Page)) {
                    PageInfo.set(Page, PageJson);
                }

                console.log(PageJson);

                $.each(PageJson, function (index, val) {
                    let STM = val.STM;//藥名
                    let ImgUrl = val.IMGURL;//圖檔路徑
                    let DBDOSE = val.DBDOSE;//劑量
                    let USEF = val.USEF;//頻率
                    let STMM = val.STMM;//備註

                    STM = STM.length > 58 ? STM.substr(0, 55) + '...' : STM;//字數超過58之後隱藏
                    STMM = STMM.trim().length > 0 ? '備註:' + STMM : " ";
                    let HasImg = checkImgUrl(ImgUrl) === true ? `<img src="${ImgUrl}"  class="img-fluid">` : `<p class="Txt-25">無圖示</p>`;

                    // $(".table_main").append(
                    //     `
                    //    <tbody class="page${Page}" >
                    //         <tr>
                    //             <td class="img-td" rowspan="4">
                    //                ${HasImg}
                    //             </td>
                    //         </tr>
                    //
                    //         <tr>
                    //             <td style="max-width: 530px">${STM}  <img class="img-More" src="../../img/more.png"></td>
                    //             <td colspan="2" class="col-2">
                    //                 <div class="form-check form-check-inline">
                    //                     <input class="form-check-input" id="TakeDrug@${Page}@${index}@0" type="radio" name="TakeDrug@${Page}@${index}">
                    //                     <label class="form-check-label" for="TakeDrug@${Page}@${index}@0"><b>給藥</b></label>
                    //                 </div>
                    //             </td>
                    //         </tr>
                    //
                    //         <tr>
                    //             <td>${DBDOSE + '/' + USEF}</td>
                    //             <td colspan="2" class="col-2">
                    //                 <div class="form-check form-check-inline">
                    //                     <input class="form-check-input" id="TakeDrug@${Page}@${index}@1" type="radio" name="TakeDrug@${Page}@${index}">
                    //                     <label class="form-check-label" for="TakeDrug@${Page}@${index}@1"><b>未給藥</b></label>
                    //                 </div>
                    //             </td>
                    //
                    //         </tr>
                    //
                    //         <tr>
                    //             <td colspan="2">${STMM}</td>
                    //             <td>
                    //                 <select class="form-control" >
                    //                     <option>NPO</option>
                    //                     <option>請假</option>
                    //                     <option>檢查</option>
                    //                     <option>OP</option>
                    //                     <option>拒服</option>
                    //                 </select>
                    //             </td>
                    //         </tr>
                    //     </tbody>
                    //     `
                    // );

                    $(".table_main").append(
                        `
                       <tbody class="page${Page}" >
                            <tr>
                                <td class="img-td" rowspan="3" colspan="2">
                                   ${HasImg}
                                </td>
                            </tr>

                            <tr>
                                <td style="max-width: 530px" class="Txt-25">${STM}</td>
                                <td colspan="2" class="col-2">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" id="TakeDrug@${Page}@${index}@0" type="radio" name="TakeDrug@${Page}@${index}">
                                        <label class="form-check-label" for="TakeDrug@${Page}@${index}@0"><b>給藥</b></label>
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td class="Txt-25">${DBDOSE + '/' + USEF}</td>
                                <td colspan="2" class="col-2">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" id="TakeDrug@${Page}@${index}@1" type="radio" name="TakeDrug@${Page}@${index}">
                                        <label class="form-check-label" for="TakeDrug@${Page}@${index}@1"><b>未給藥</b></label>
                                    </div>
                                </td>

                            </tr>

                            <tr>

                                <td><button class="ShowBigPic btn btn-info btn-md btn-block "><b style="font-size: ">+</b></button></td>
                                <td><img class="img-More ml-4" src="../../img/more.png"></td>
                                <td class="Txt-25" colspan="2">${STMM}</td>
                                <td>
                                    <select class="form-control" >
                                        <option>NPO</option>
                                        <option>請假</option>
                                        <option>檢查</option>
                                        <option>OP</option>
                                        <option>拒服</option>
                                    </select>
                                </td>
                            </tr>
                        </tbody>
                        `
                    );
                });
            }

            if ($(".page" + Page).length > 0) {
                $(".table_main").children().hide();
                $(".page" + Page).show();
            }

        });

        $("#SubmitBtn").click(function () {
            // let Json_obj=JSON.stringify(Json_obj)
            let sDt=$("#sDate").val();
            let sTm=$("#sTime").val();
            DB_WSST(Info.Page, Info.sTraID,' ', sDt, sTm, '', '', "<?php echo $sUr?>", 'true')
        });



        function bedcallback(data) {
            let str = AESDeCode(data);
            let dataObj = JSON.parse(str)[0];
            let IdPt = dataObj.IDPT;
            let InPt = dataObj.IDINPT;
            let sBed = dataObj.SBED;
            let P_NM = dataObj.DataTxt;
            let ssTAT = dataObj.sSTAT;


            let Date = new getDateTime();

            let Local_Date = Date.ROC + Date.Month + Date.Day;
            let Local_Time = Date.Hour + Date.Min;

            $("#sDate").val(Local_Date);
            $("#sTime").val(Local_Time);
            $("#DataTxt").val(P_NM);

            Info.sTraID = '';
            Info.IdPt = IdPt;
            Info.InPt = InPt;
            Info.sBed = sBed;
            Info.ssTAT = ssTAT;

            GetINIJson(IdPt, InPt);
        }

        function Serchcallback(AESobj) {
            const obj = JSON.parse(AESDeCode(AESobj));
            const sTraID = obj.sTraID;
            const sTime = obj.TMEXCUTE;
            const sDate = obj.DTEXCUTE;
            const IdPt = obj.IDPT;
            const InPt = obj.INPT;

            if ($("#DA_idpt").val() !== IdPt || $("#DA_idinpt").val() !== InPt) {
                alert('病人資料有異動請重新選擇病人');
                return false;
            }


        }

        function GetINIJson(IdPt, InPt) {

            $.ajax("/webservice/NISPWSTRAINI.php?str=" + AESEnCode('sFm=' + '<?php echo $sfm?>' + '&idPt=' + IdPt + '&INPt=' + InPt + "&sUr=" + '<?php echo $sUr?>'))
                .done(function (data) {
                    let obj = JSON.parse(AESDeCode(data));
                    console.log(obj);
                    Info.sTraID = obj.sTraID;
                })
                .fail(function (XMLHttpResponse, textStatus, errorThrown) {
                    console.log(
                        "1 返回失敗,XMLHttpResponse.readyState:" + XMLHttpResponse.readyState + XMLHttpResponse.responseText +
                        "2 返回失敗,XMLHttpResponse.status:" + XMLHttpResponse.status +
                        "3 返回失敗,textStatus:" + textStatus +
                        "4 返回失敗,errorThrown:" + errorThrown
                    );
                });
        }

        function GetPageJson(Page, sTraID) {
            let response = "";
            $.ajax({
                url: "/webservice/NISPWSGETPRE.php?str=" + AESEnCode("sFm=" + '<?php echo $sfm?>' + "&sTraID=" + sTraID + "&sPg=" + Page),
                dataType: "text",
                async: false,
                success: function (data) {
                    let obj = AESDeCode(data);
                    response = obj;
                }, error: function (XMLHttpResponse, textStatus, errorThrown) {
                    console.log(
                        "1 返回失敗,XMLHttpResponse.readyState:" + XMLHttpResponse.readyState + XMLHttpResponse.responseText +
                        "2 返回失敗,XMLHttpResponse.status:" + XMLHttpResponse.status +
                        "3 返回失敗,textStatus:" + textStatus +
                        "4 返回失敗,errorThrown:" + errorThrown
                    );
                }
            });
            return response;
        }

        function DB_WSST(Page, sTraID, json, sDt = null, sTm = null, Passed = null, Freq = null, sUr, InSertDB) {

            $.ajax({
                type: "POST",
                url: '/webservice/NISPWSSETDATA.php?str=' + AESEnCode(
                    'sFm='+'<?php echo $sfm?>'+'&sTraID=' + sTraID + '&sPg=' + Page + '&sData=' + encodeURI(json) +
                    '&sDt=' + sDt + '&sTm=' + sTm + '&Fseq=' + Freq + '&PASSWD=' + Passed +
                    '&USER=' + sUr + '&Indb=' + InSertDB )

            }).done(function (data) {

                let json = JSON.parse(AESDeCode(data));

                if (InSertDB === "true") {
                    console.log(json);
                    if (json.result === "true") {
                        // alert('存檔成功');
                        console.log('存檔成功');
                        // window.location.replace(window.location.href);
                    } else {
                        console.log("儲存失敗,錯誤訊息:" + json.message);
                        // alert("儲存失敗,錯誤訊息:" + json.message);
                    }
                    // $("#wrapper,#loading").hide();
                }

            }).fail(function (XMLHttpResponse, textStatus, errorThrown) {
                console.log(
                    "1 返回失敗,XMLHttpResponse.readyState:" + XMLHttpResponse.readyState + XMLHttpResponse.responseText +
                    "2 返回失敗,XMLHttpResponse.status:" + XMLHttpResponse.status +
                    "3 返回失敗,textStatus:" + textStatus +
                    "4 返回失敗,errorThrown:" + errorThrown
                );
            });

        }

        function checkBEDwindow() {

            if (!BedWindow) {
                return true;
            } else {
                return !!BedWindow.closed;
            }
        }

        function checkSerchwindow() {
            if (!Serchwindow) {
                return true;
            } else {
                return !!Serchwindow.closed;
            }
        }

        function checkImgUrl(url) {
            let response = "";
            $.ajax({
                url: url,
                async: false,
                success: function () {
                    response = true;
                }, error: function (XMLHttpResponse, textStatus, errorThrown) {
                    response = false;
                }
            });
            return response;
        }

        function getDateTime() {

            let TimeNow = new Date();
            this.Year = TimeNow.toLocaleDateString().slice(0, 4);
            this.Month = (TimeNow.getMonth() + 1 < 10 ? '0' : '') + (TimeNow.getMonth() + 1);
            this.Day = (TimeNow.getDate() < 10 ? '0' : '') + TimeNow.getDate();
            this.Hour = (TimeNow.getHours() < 10 ? '0' : '') + TimeNow.getHours();
            this.Min = (TimeNow.getMinutes() < 10 ? '0' : '') + TimeNow.getMinutes();
            this.ROC = (this.Year - 1911).toString();

        }


    });
</script>
<style>
    tbody tr td {
        border: 1px solid transparent;
    }

    .table-container {
        height: 30em;
        overflow-y: scroll;
        overflow-x: hidden;
        border: 2px solid #dee2e6;
    }

    .table-bordered {
        border: 1px solid transparent;
    }

    .table_main > tbody {
        /*藥物區隔線*/
        border-bottom: 10px solid #dee2e6;
    }

    input[type=radio] {
        width: 60px;
        height: 2.5em;
    }

    .img-td {
        width: 20%;
        height: 40%;
    }

    img:not(.img-More) {
        min-height: 132px;
        min-width: 176px;
    }

    .img-More {
        height: 25px;
        width: 25px;
        cursor: pointer;
    }

    /*時間*/

    .DateTime, .Otimer > label, .DateTime::placeholder {
        font-size: 3vmin;
    }

    .Otimer {
        border-radius: 5px;
    }

    .Txt-3 {
        font-size: 3vmin;
    }

    .Txt-25 {
        font-size: 25px;
    }
</style>
<body>
<div class="container">
    <div class="Parametertable"></div>
    <div class="row">
        <div class="col-12">
            <h1>藥物治療作業</h1>
        </div>
        <div class="col-12">
            <span class="title pb-3">
                <button type="button" id="SubmitBtn" class="btn btn-primary btn-lg mt-1">儲存</button>
                <button type="button" id="SearchBtn" class="btn btn-primary btn-lg mt-1">查詢</button>
                <button type="button" id="" class="btn btn-primary btn-lg mt-1">條碼</button>
                <button type="button" id="DELBtn" class="btn btn-primary btn-lg mt-1">作廢</button>
                <button type="button" id="ReSetBtn" class="btn btn-primary btn-lg mt-1">清除</button>
                <button type="button" id="sbed" class="btn btn-warning btn-lg mt-1">責任床位</button>
            </span>
            <span class="ml-1">
               <b>使用者:<?php echo $sUr ?></b>
            </span>
        </div>

        <div class="col-12 mt-3">
            <input id="DataTxt" value="" class="form-control Txt-3" type="text" disabled>
        </div>


        <div class="Otimer  col-12 mt-2 input-group">
            <label for="sDate">給藥時間:</label>
            <input type="text" id="sDate" value="" class="DateTime form-control col-2 form-control-lg"
                   placeholder="YYYMMDD" maxlength="7">
            <input type="text" id="sTime" value="" class="DateTime form-control col-2 form-control-lg"
                   placeholder="HHMM" maxlength="4">
        </div>

        <div class="col-12 mt-2">
            <button class="Page btn btn-primary btn-lg" value="A">長期</button>
            <button class="Page btn btn-primary btn-lg" value="B">點滴</button>
            <button class="Page btn btn-primary btn-lg" value="C">臨時</button>
        </div>
        <div class="table-responsive table-container mt-2">
            <table class="table table_main table-bordered">

            </table>
        </div>
    </div>


    <div class="modal fade" id="ImgShow" tabindex="-1" role="dialog" aria-labelledby="ImgShow" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="ImgShowModalBody modal-body">

                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="ImgMore" tabindex="-1" role="dialog" aria-labelledby="ImgShow" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">詳細資訊</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-12">
                                <!--藥名-->
                                <p class="MoreInfo"></p>
                                <!--其他資訊-->
                                <p class="MoreInfo"></p>
                            </div>

                            <div class="embed-responsive embed-responsive-21by9">
                                <iframe class="embed-responsive-item" src=""></iframe>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
</body>

</html>
