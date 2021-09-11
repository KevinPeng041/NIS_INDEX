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

        let BedWindow, SerchWindow, ScanCodeWindow;
        let Info = {
            Page: '',
            sTraID: '',
            IdPt: '',
            InPt: '',
            sBed: '',
            ssTAT: '',
            REFUSERSN: '',
            AllStp: '',
            ALLDELY: ''

        };
        let PageInfo = new Map();

        $(document).on('click', 'button', function () {
            let btnID = $(this).attr('id');
            let btnCls = $(this).attr('class').split(" ")[0];
            let IdPt = Info.IdPt;
            let InPt = Info.InPt;
            let Page = Info.Page;
            switch (btnID) {
                case "sbed":
                    if (!checkBEDwindow()) {
                        alert("視窗已開啟");
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
                        alert("視窗已開啟");
                        return;
                    } else {
                        let PName = $("#DataTxt").val();
                        let BEGDATE = $("#sDate").val();
                        let BEGTIME = $("#sTime").val();
                        if (IdPt.trim() === "" || InPt.trim() === "" || PName.trim() === "") {
                            alert('請先選擇病人');
                            return false;
                        }

                        SerchWindow = window.open("/webservice/NISPWSLKQRY.php?str=" +
                            AESEnCode("sFm=" + "<?php echo $sfm?>" + "&PageVal=" + "" + "&DA_idpt=" + IdPt + "&DA_idinpt=" + InPt
                                + "&sUser=" + "<?php echo $sUr?>" + "&NM_PATIENT=" + PName + "&BEGDATE=" + BEGDATE + "&BEGTIME=" + BEGTIME)
                            , "查詢", 'width=750px,height=650px,scrollbars=yes,resizable=no');
                    }
                    SerchWindow.Serchcallback = Serchcallback;
                    break;
                case "ScanCode":
                    if (!checkScanCodeWindow()) {
                        alert("視窗已開啟");
                        return;
                    } else {
                        ScanCodeWindow = window.open("NISSCANMARS.php"
                            , "條碼", 'width=750px,height=650px,scrollbars=yes,resizable=no');
                    }
                    ScanCodeWindow.ScanCodeBack = ScanCodeBack;
                    break;
            }

            if (btnCls === "ShowBigPic") {
                let hiskey = $(this).val();
                let imgUrl = PageInfo.get(Page).filter((item) => item.HISKEY === hiskey)[0].IGUL;
                let element = checkImgUrl(imgUrl) === true ? `<img src="${imgUrl}"  class="img-fluid">` : `<p class="px-25">無圖示</p>`;

                // 放大藥品圖片
                $(".ImgShowModalBody").children().remove();
                $(".ImgShowModalBody").append(
                    `
                        ${element}
                    `
                );
                $("#ImgShow").modal('show');

            } else if (btnCls === "MoreBtn") {
                // 更多資訊
                let hiskey = $(this).val();
                let NMOD = PageInfo.get(Page).filter((item) => item.HISKEY === hiskey)[0].NMOD;

                $(".MoreInfo:eq(0)").text(NMOD);
                $("#ImgMore").modal('show');

            }

        });

        $(document).on('click', 'input[type=checkbox]', function () {

            let thisInfo = $(this).attr('id').split("&");
            let thisClass = $(this).attr('class').split(" ")[0].split("").map((val) => {
                if (val === "@") {
                    return "\\" + val;
                }
                return val;
            }).join("");

            let Page = thisInfo[0];
            let hiskey = thisInfo[1];
            let ItemIndex = thisInfo[3];
            let filterObj = PageInfo.get(Page).filter((item) => item.HISKEY === hiskey)[0];
            let Stp = "";

            if ($(this).is(":checked")) {
                //checked
                Stp = $(this).val();
                $("." + thisClass).prop('checked', false);
                $(this).prop('checked', true);

            } else {
                //取消 checked
                filterObj.IDRR = "";
                filterObj.NMRR = "";
                $("#" + Page + '\\&' + ItemIndex + ' option:eq(0)').prop('selected', 'selected');
            }
            filterObj.STP = Stp;
        });

        $(document).on('change', 'select', function () {

            let ThisId = $(this).attr('id');
            let RRInfo = $(this).val().split("@");
            let idRR = RRInfo[0];
            let nmRR = RRInfo[1];

            if (ThisId === "ModalState") {
                Info.AllStp = RRInfo;
            } else if (ThisId === "ModalDelay") {
                Info.ALLDELY = RRInfo;
            } else {
                let OptionInfo = ThisId.split("&");
                let Page = OptionInfo[0];
                let index = OptionInfo[1];
                let thisItemObj = PageInfo.get(Page)[index];

                thisItemObj.IDRR = idRR;
                thisItemObj.NMRR = nmRR;
                console.log(PageInfo.get(Page));
            }
        });

        $(".Page").click(function () {
            let Page = $(this).val();
            let TransKey = Info.sTraID;
            // let ReFusersn = Info.REFUSERSN;

            Info.Page = Page;

            $(".table-container").scrollTop(0);
            $('.Page').each(function () {
                $(this).css({'background-color': '', 'color': '', "border": ""});
            });
            $(this).css({'background-color': '#F9F900', 'color': '#000000', "border": ""});

            // if ($(".page" + Page).length === 0) {
            //
            //     let PageJson = JSON.parse(GetPageJson(Page, TransKey));
            //     if (!PageInfo.has(Page)) {
            //         PageInfo.set(Page, PageJson);
            //     }
            //     CreatElement(Page,PageJson);
            //
            // }

            ['A', 'B', 'C'].filter((val) => val !== Page).forEach((value) => {
                if (PageInfo.has(value)) {
                    DB_WSST(Page, TransKey, JSON.stringify(PageInfo.get(value)), '', '', '', '', '', 'false');
                }
            });

            $(".table_main").children().hide();
            $(".page" + Page).show();
        });

        $("#SubmitBtn").click(function () {

            let sDt = $("#sDate").val();
            let sTm = $("#sTime").val();
            let LocalPage = Info.Page;


            const AllPage = ['A', 'B', 'C'];
            let index = 0;
            while (index < 3) {
                let Page = AllPage[index];
                if (PageInfo.has(Page)) {
                    let PageObj = PageInfo.get(Page);


                    /*state=>給藥時間超過30分鐘,填延遲給藥原因*/
                    if (Page === "C") {
                        let hsaDelay = PageObj.filter((val) => val.STP === "Y" && val.IDRD === "")
                            .filter(function (val) {
                                let date = val.BDT;
                                let Time = val.BTM;
                                return getDateDiff(getDateStr(sDt, sTm), getDateStr(date, Time), 'min') > 30;
                            });

                        if (hsaDelay.length > 0) {
                            $("#mDELAY").modal('show');
                            return false;
                        }

                    }

                    /*已勾選未給藥,但沒選擇未給藥原因*/
                    let hasNullReason = PageObj.filter((val) => val.IDRR === "" && val.STP === "N");
                    if (hasNullReason.length > 0) {
                        $("#mSTATE").modal('show');
                        return false;
                    }
                }

                index++;
            }
            console.log(LocalPage);
            DB_WSST(LocalPage, Info.sTraID, JSON.stringify(PageInfo.get(LocalPage)), sDt, sTm, '', '', "<?php echo $sUr?>", 'true');
        });

        //所有未給藥原因確認
        $("#ck_STATE").click(function () {
            let idRR = Info.AllStp[0];
            let nmRR = Info.AllStp[1];
            let TransKey = Info.sTraID;
            let localPage = Info.Page;

            let sDt = $("#sDate").val();
            let sTm = $("#sTime").val();

            $(".Page").each(function () {
                let page = $(this).val();
                if (PageInfo.has(page)) {
                    PageInfo.get(page)
                        .filter((val) => val.IDRR === "" && val.STP === "N")
                        .forEach((val) => {
                            val.IDRR = idRR;
                            val.NMRR = nmRR;
                        });

                    DB_WSST(page, TransKey, JSON.stringify(PageInfo.get(page)), '', '', '', '', '', 'false');
                }
            });

            //DB_WSST(localPage, TransKey, JSON.stringify(PageInfo.get(localPage)), sDt, sTm, '', '', "<?php echo $sUr?>", 'true');
            $("#mSTATE").modal('hide');
        });

        //延時給藥原因確認
        $("#ck_Delay").click(function () {
            let idRD = Info.ALLDELY[0];
            let nmRD = Info.ALLDELY[1];
            let TransKey = Info.sTraID;
            let PageObj = PageInfo.get('C');
            let sDt = $("#sDate").val();
            let sTm = $("#sTime").val();
            let hsaDelay = PageObj
                .filter((val) => val.STP === "Y")
                .filter(function (val) {
                    let date = val.BDT;
                    let Time = val.BTM;
                    //延遲給藥=>給藥時間-開始時間>30min
                    return getDateDiff(getDateStr(sDt, sTm), getDateStr(date, Time), 'min') > 30;
                });

            hsaDelay.forEach((val) => {
                val.IDRD = idRD;
                val.NMRD = nmRD;
            });

            DB_WSST('C', TransKey, JSON.stringify(PageObj), '', '', '', '', '', 'false');
            $("#mDELAY").modal('hide');
        });

        function CreatElement(Page, arr) {
            let ReFusersn = Info.REFUSERSN;

            // $("#"+Page+"_Num").text('0');
            if ($(".page" + Page).length > 0) {
                $(".page" + Page).remove('tbody')
            }
            $("#" + Page + "_Num").text(arr.length);//頁簽數量

            $.each(arr, function (index, val) {
                let HISKEY = val.HISKEY;
                let NMOD = val.NMOD;//藥名
                let ImgUrl = val.IGUL;//圖檔路徑
                let DOSE = val.DOSE;//每次劑量
                let MMOD = val.MMOD;//備註
                let USPA = val.USPA;//院內給藥頻率(用法)
                let UT = val.UT;//單位
                let SCT = val.SCT;//累積給藥次數

                let Note = DOSE + '/' + UT + '/' + USPA;
                let element = checkImgUrl(ImgUrl) === true ? `<img src="${ImgUrl}"  class="img-fluid">` : `<p class="px-20">無圖示</p>`;
                let MMODele = Page === "B" ? `<span class="badge px-15 badge-danger">給藥次數:${SCT}</span>  備註:${MMOD}` : `備註:${MMOD}`;
                let ckBtnID = Page + "&" + HISKEY + "&";
                let ckBtnCLS = Page + index + HISKEY;
                let selectID = Page + '&' + index;

                NMOD = NMOD.length > 58 ? NMOD.substr(0, 55) + '...' : NMOD;//字數超過55之後隱藏


                $(".table_main").append(
                    `
                         <tbody class="page${Page}" >
                                <tr>
                                    <td class="img-td" rowspan="3" colspan="2">
                                      ${element}
                                    </td>
                                 </tr>

                                 <tr>
                                     <td style="max-width: 530px" class="px-25">${NMOD}</td>
                                     <td colspan="2" class="col-2">
                                         <div class="form-check form-check-inline">
                                              <input class="${ckBtnCLS + ' form-check-input'}" id="${ckBtnID + "0" + "&" + index}" type="checkbox"  value="Y">
                                              <label class="form-check-label" for="${ckBtnID + "0" + "&" + index}"><b>給藥</b></label>
                                         </div>
                                     </td>
                                 </tr>

                                 <tr>
                                     <td class="px-20">${Note}</td>
                                     <td colspan="2" class="col-2" style="border-bottom: 1px white solid">
                                          <div class="form-check form-check-inline">
                                                 <input class="${ckBtnCLS + ' form-check-input'}" id="${ckBtnID + "1" + "&" + index}" type="checkbox"  value="N">
                                                 <label class="form-check-label" for="${ckBtnID + "1" + "&" + index}"><b>未給藥</b></label>
                                          </div>
                                      </td>
                                 </tr>

                                 <tr>
                                     <td><button class="ShowBigPic btn btn-md btn-block" value="${HISKEY}"><img class="img-More" src="../../img/PLUS.png"></button></td>
                                     <td><button class="MoreBtn btn btn-md btn-block" value="${HISKEY}"><img class="img-More" src="../../img/more2.png"></button></td>
                                     <td class="px-25" colspan="2">${MMODele}</td>
                                     <td>
                                         <select class="form-control" id="${selectID}">
                                              <option></option>
                                          </select>
                                     </td>
                                  </tr>


                            </tbody>
                        `
                );

                //append 未給藥原因
                $.each(ReFusersn, function (num, item) {
                    let optionVal = item.JID_KEY + '@' + item.NM_ITEM;
                    $("#" + Page + '\\&' + index).append(
                        `
                            <option value="${optionVal}">${item.NM_ITEM}</option>
                             `
                    );
                });


            });

        }

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


            for (let key in Info) {
                Info[key] = '';
            }

            Info.IdPt = IdPt;
            Info.InPt = InPt;
            Info.sBed = sBed;
            Info.ssTAT = ssTAT;

            PageInfo.clear();
            ['A', 'B', 'C'].forEach((Page) => $(".page" + Page).remove());
            $(".Page ").css({'background': '', 'color': ''});
            GetINIJson(IdPt, InPt);
            console.log(Info);

        }

        function Serchcallback(AESobj) {
            const obj = JSON.parse(AESDeCode(AESobj));
            const sTraID = obj.sTraID;
            const sTime = obj.TMEXCUTE;
            const sDate = obj.DTEXCUTE;
            const IDPT = obj.IDPT;
            const INPT = obj.INPT;

            if (Info.IdPt !== IDPT || Info.InPt !== INPT) {
                alert('病人資料有異動請重新選擇病人');
                return;
            }
            PageInfo.clear();
            Info.sTraID = sTraID;
            SetDefaultPage('A', sTraID);//預設顯示頁面為A
            $("#sDate").val(sDate);
            $("#sTime").val(sTime);
        }

        function ScanCodeBack(data) {
            let Obj = JSON.parse(AESDeCode(data));

            //條碼格式：病歷號|UD日期|UD時間|院內碼1|醫令序1|院內碼2|醫令序2…^^@#

            let MapObj = Obj.map((value) => {
                return value.replace(/\^\^@#/gi, '')
                    .split('|')
                    .filter((value, index) => index > 2 && index % 2 === 0);
            });

            $("input[value='Y']").each(function () {
                let HisKey = $(this).attr('id').split("&")[1];
                let CheckBox = $(this);
                MapObj.forEach((arr) => {
                    if (arr.indexOf(HisKey) > -1) {
                        CheckBox.prop('checked', true);
                    } else {
                        console.log('無相對應的HisKeyCode:'+HisKey);
                    }
                });

            });
        }

        function GetINIJson(IdPt, InPt) {
            $("#wrapper").show();
            $.ajax("/webservice/NISPWSTRAINI.php?str=" + AESEnCode('sFm=' + '<?php echo $sfm?>' + '&idPt=' + IdPt + '&INPt=' + InPt + "&sUr=" + '<?php echo $sUr?>'))
                .done(function (data) {
                    $("#wrapper").hide();

                    let obj = JSON.parse(AESDeCode(data));
                    let sTraID = obj.sTraID;
                    Info.sTraID = sTraID;
                    Info.REFUSERSN = obj.REFUSERSN;
                    SetDefaultPage('A', sTraID);//預設顯示頁面為A

                    if ($("#ModalState").children().length === 1) {
                        $.each(obj.REFUSERSN, function (index, val) {
                            let optionVal = val.JID_KEY + '@' + val.NM_ITEM;
                            $("#ModalState").append(
                                `
                                 <option value="${optionVal}">${val.NM_ITEM}</option>
                                `
                            )
                        });
                    }

                    if ($("#ModalDelay").children().length === 1) {
                        $.each(obj.DELAYRSN, function (index, val) {
                            let optionVal = val.JID_KEY + '@' + val.NM_ITEM;
                            $("#ModalDelay").append(
                                `
                                 <option value="${optionVal}">${val.NM_ITEM}</option>
                                `
                            )
                        });
                    }

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

        function SetDefaultPage(D_Page, sTraID) {
            let itmIndex = "";
            if (D_Page === "A") {
                itmIndex = 0;
            } else if (D_Page === "B") {
                itmIndex = 1;
            } else if (D_Page === "C") {
                itmIndex = 2;
            }
            $(".Page ").css({"background-color": "", "color": ""});

            ["A", "B", "C"].forEach((Page) => {
                let PageJson = JSON.parse(GetPageJson(Page, sTraID));
                PageInfo.set(Page, PageJson);
                CreatElement(Page, PageJson);
                $(".Page:eq(" + itmIndex + ")").css({"background-color": "yellow", "color": "black"});
                $(".page" + Page).hide();
            });

            Info.Page = D_Page;
            $(".page" + D_Page).show();
        }

        function GetPageJson(Page, sTraID) {
            let response = "";
            $.ajax({
                url: "/webservice/NISPWSGETPRE.php?str=" + AESEnCode("sFm=" + '<?php echo $sfm?>' + "&sTraID=" + sTraID + "&sPg=" + Page),
                dataType: "text",
                async: false,
                success: function (data) {

                    response = AESDeCode(data);
                }, error: function (XMLHttpResponse, textStatus, errorThrown) {
                    $("#wrapper").hide();
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
                    'sFm=' + '<?php echo $sfm?>' + '&sTraID=' + sTraID + '&sPg=' + Page + '&sData=' + encodeURI(json) +
                    '&sDt=' + sDt + '&sTm=' + sTm + '&Fseq=' + Freq + '&PASSWD=' + Passed +
                    '&USER=' + sUr + '&Indb=' + InSertDB)

            }).done(function (data) {

                let json = JSON.parse(AESDeCode(data));

                if (InSertDB === "true") {
                    console.log(json);
                    if (json.result === "true") {
                        alert('存檔成功');
                        window.location.replace(window.location.href);
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
            if (!SerchWindow) {
                return true;
            } else {
                return !!SerchWindow.closed;
            }
        }

        function checkScanCodeWindow() {
            if (!ScanCodeWindow) {
                return true;
            } else {
                return !!ScanCodeWindow.closed;
            }
        }

        function checkImgUrl(url) {
            let response = "";
            if (url.trim() === '') {
                return false;
            }
            $.ajax({
                url: url,
                async: false,
                success: function () {
                    response = true;
                }, error: function () {
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

        function getDateStr(RocDate, Time) {
            let Y = parseInt(RocDate.substr(0, 3)) + 1911;
            let M = parseInt(RocDate.substr(3, 2));
            let D = parseInt(RocDate.substr(5, 2));

            let hour = parseInt(Time.substr(0, 2));
            let min = parseInt(Time.substr(2, 2));

            return Y.toString() + '/' + M.toString() + '/' + D.toString() + ' ' + hour.toString() + ':' + min.toString() + ':' + '00';
        }

        function getDateDiff(sTime, eTime, diffType) {
            let startTime = new Date(sTime);
            let endTime = new Date(eTime);
            let timeType = 1;

            switch (diffType) {
                case "sec":
                    timeType = 1000;
                    break;
                case "min":
                    timeType = 1000 * 60;
                    break;
                case "hour":
                    timeType = 1000 * 3600;
                    break;
                case "day":
                    timeType = 1000 * 3600 * 24;
                    break;
                default:
                    break;
            }

            return Math.round((startTime.getTime() - endTime.getTime()) / timeType);

        }
    });
</script

>
<style>
    tbody tr td {
        border: 1px solid transparent;
    }

    .table-container {
        height: 55vmin;
        overflow-y: scroll;
        overflow-x: hidden;
        border: 2px solid #dee2e6;
    }

    .table-bordered {
        border: 1px solid transparent;
    }

    .table_main > tbody {
        /*藥物區隔線*/
        border-bottom: 20px solid #dee2e6;
    }

    input[type=checkbox] {
        width: 35px;
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

    .ShowBigPic {
        background-color: #ff6670;
    }

    .MoreBtn {
        background-color: #77a88d;
    }

    .MoreBtn > img, .ShowBigPic > img {
        height: 20px;
        width: 20px;
        margin-top: -5px;
    }


    /*時間*/
    .DateTime, .Otimer > label, .DateTime::placeholder {
        font-size: 3vmin;
    }

    .DateTime {
        min-width: 120px;
    }

    .Otimer {
        border-radius: 5px;
    }

    .vim-4 {
        font-size: 4vmin;
    }

    .px-15 {
        font-size: 15px;
    }

    .px-20 {
        font-size: 20px;
    }

    .px-25 {
        font-size: 25px;
    }

    .ImgShowModalBody > img {
        cursor: default;
        width: 100%
    }

    .Hide {
        display: none;
    }

    #wrapper {
        position: absolute;
        height: 100%;
        background-color: black;
        opacity: 0.5;
        z-index: 9998;
    }

</style>
<body>
<div id="wrapper" class="Hide container-fluid"></div>
<div class="container">
    <div class="row">
        <div class="col-12">
            <h1>藥物治療作業</h1>
        </div>
        <div class="col-12">
            <span class="title pb-3">
                <button type="button" id="SubmitBtn" class="btn btn-primary btn-lg mt-1">儲存</button>
                <button type="button" id="SearchBtn" class="btn btn-primary btn-lg mt-1">查詢</button>
                <button type="button" id="ScanCode" class="btn btn-primary btn-lg mt-1">條碼</button>
                <button type="button" id="DELBtn" class="Hide btn btn-primary btn-lg mt-1">作廢</button>
                <button type="button" id="ReSetBtn" class="Hide btn btn-primary btn-lg mt-1">清除</button>
                <button type="button" id="sbed" class="btn btn-warning btn-lg mt-1">責任床位</button>
            </span>
            <span class="ml-1">
               <b>使用者:<?php echo $sUr ?></b>
            </span>
        </div>
        <div class="col-12 mt-3">
            <input id="DataTxt" value="" class="form-control vim-4" type="text" disabled>
        </div>
        <div class="Otimer col-12 mt-2 input-group">
            <label for="sDate">給藥時間:</label>
            <input type="text" id="sDate" value="" class="DateTime form-control col-2 form-control-lg"
                   placeholder="YYYMMDD" maxlength="7">
            <input type="text" id="sTime" value="" class="DateTime form-control col-2 form-control-lg"
                   placeholder="HHMM" maxlength="4">
        </div>
        <div class="col-12 mt-2">
            <button class="Page btn btn-primary btn-lg" value="A">長期 <span id="A_Num" class="badge badge-light">0</span>
            </button>
            <button class="Page btn btn-primary btn-lg" value="B">注射 <span id="B_Num" class="badge badge-light">0</span>
            </button>
            <button class="Page btn btn-primary btn-lg" value="C">臨時 <span id="C_Num" class="badge badge-light">0</span>
            </button>
        </div>
        <div class="table-responsive table-container mt-2">
            <table class="table table_main table-bordered">

            </table>
        </div>
    </div>

    <!-------------------------------------   modal  ---------------------------------->
    <div class="modal fade" id="ImgShow" tabindex="-1" role="dialog" aria-labelledby="ImgShow" aria-hidden="true"
         data-backdrop="static">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="ImgShowModalBody modal-body">

                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="ImgMore" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
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

    <div class="modal fade" id="mSTATE" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">有尚未選擇的未給藥原因(請選擇未給藥原因)</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-8">
                            <select class="form-control" id="ModalState">
                                <option></option>
                            </select>
                        </div>
                        <div class="col-4">
                            <button id="ck_STATE" class="btn btn-primary form-control">確認</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="mDELAY" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">延遲給藥原因</h5>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-8">
                            <select class="form-control" id="ModalDelay">
                                <option></option>
                            </select>
                        </div>
                        <div class="col-4">
                            <button id="ck_Delay" class="btn btn-primary form-control">確認</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
</body>

</html>
