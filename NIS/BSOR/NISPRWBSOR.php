<?php
include '../../NISPWSIFSCR.php';
$str = $_GET['str'];
$replaceSpace = str_replace(' ', '+', $str);//空白先替換+
parse_str(AESDeCode($replaceSpace), $output);

/*$sUr=$output['sIdUser'];
$sfm=$output['sFm'];*/
$sUr = '00FUZZY';
$sfm = 'BSOR';
if ($sfm == "BSOR") {
    $shape = "circle";
    $Title_NM = "壓瘡評估作業";
}

if ($sfm == "CUTS") {
    $shape = "square";
    $Title_NM = "傷口評估作業";

}
if ($sfm == "TUPT") {
    $shape = "triangle";
    $Title_NM = "管路評估作業";
}


?>
<!DOCTYPEhtml>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php echo $Title_NM ?></title>
    <script type="text/javascript" src="../../jquery-3.4.1.js"></script>
    <link rel="stylesheet" href="../../bootstrap-4.3.1-dist/css/bootstrap.min.css">
    <script src="../../bootstrap-4.3.1-dist/js/bootstrap.min.js" type="text/javascript"></script>
    <script src="../../crypto-js.js"></script>
    <script src="../../AESCrypto.js"></script>
    <script src="../../JavaScript/jquery-ui.js"></script>
    <script>
        $(document).ready(function () {
            (function () {
                NISPWSFMINI_Timer('ILSGA', 'A');
                $("input[type=text]").attr('autocomplete', 'off');
                $(".Main,.MMDIV").hide();
                $("#SubmitBtn").prop('disabled', true);
                $("#SubmitBtn").prop('disabled', true);

                if ("<?php echo $sfm?>" !== "TUPT") {
                    $(" .TPUT_div").hide();
                } else {
                    $(" .Otimer,#DELBtn,#ReSetBtn").hide();
                }
                $("#wrapper,#loading").hide();
            })();

            let imageLoaded = function () {
                let Canvas = $("#CanvasPad")[0];
                let ctx = Canvas.getContext('2d');
                // let img=$("img:eq(0)")[0];
                let img = $("img:eq(1)")[0];
                ctx.drawImage(img, 0, 0, Canvas.width, Canvas.height);
            };

            $("img").each(function () {
                let tmpImg = new Image();
                tmpImg.onload = imageLoaded;
                tmpImg.src = $(this).attr('src');
            });


            //Img=>x,y座標
            //DATA=>Table 欄位值
            //TD_Child=>Table欄位元件
            //TR_CLASSNM=>Table標題欄位名稱
            let Data_obj = new Map();


            var BEDwindow, Serchwindow;
            var CancelNum = [];//被[刪除]的編號
            const creatTable = {
                Default: (obj) => {
                    let Tb_NM_obj = obj.T_NM; // 標題
                    let T_ID = []; // 標題id
                    let T_CNM = obj.T_CNM; // td children
                    let MM_TEXT_obj = obj.MM_TEXT; //壓傷備註
                    let D_edit = obj.D_EDIT; //身高體重
                    // let Is_Change=obj.IS_CHANGE; //壓瘡=>壓傷


                    $(".area-table").children('table').remove();

                    $(".area-table").append(
                        `
                        <table class="table" id="Data_Table" ">

                        </table>
                        `
                    );

                    $.each(Tb_NM_obj, function (index, val) {
                        //新增左標題
                        let classNm = val.ID_TABITEM === "" ? 'tb' + index : val.ID_TABITEM;

                        $("#Data_Table").append(
                            `
                            <tr class="${classNm}">
                                 <th>${val.ST_LEFT}</th>
                            </tr>
                                                 `
                        );

                        if ("<?php echo $sfm?>" === "TUPT" && index >= 9) {
                            $("." + classNm).hide();
                        }

                        T_ID.push(classNm);
                    });

                    if (!Data_obj.has('TD_Child')) {
                        Data_obj.set('TD_Child', T_CNM);
                    }
                    if (!Data_obj.has('TR_CLASSNM')) {
                        Data_obj.set('TR_CLASSNM', T_ID);
                    }
                    if (!Data_obj.has('NEWDATA')) {
                        Data_obj.set('NEWDATA', obj.ST_DATAB);
                    }
                    if (D_edit !== null) {
                        creatTable.inEdit(D_edit);
                    }
                    if (MM_TEXT_obj !== null) {
                        creatTable.inMMText(MM_TEXT_obj);
                    }
                    Data_obj.set('MAXNUM', obj.MAXNUM);


                },
                inTableTd: (sfm, Page, data) => {

                    if (Page === "A") {
                        $.each(data, function (index, val) {
                            let LEFT = parseInt(val.LEFT);
                            let TOP = parseInt(val.TOP);
                            let Width = parseInt(val.W_TH);
                            let Height = parseInt(val.H_TH);

                            LEFT = LEFT + Math.floor(Width / 2);
                            TOP = TOP - Math.floor(Height / 2);

                            if (sfm === "BSOR" || sfm === "CUTS") {
                                if (Width !== 15 && Height !== 15) {
                                    LEFT = LEFT + Math.floor(Width / 4);
                                    TOP = TOP - Math.floor(Height / 4);
                                }
                            }

                            AddShape(val.NUM, '<?php echo $shape?>', LEFT, TOP, Width, Height);

                        });

                    }
                    if (Page === "B") {

                        let T_CD = Data_obj.get("TD_Child");
                        let T_ID = Data_obj.get("TR_CLASSNM");


                        // data.forEach((value)=>{value.SSTAT=$("#sSTAT").val()});

                        $.each(data, function (ItemNo, item) {

                            let count_element = 0;
                            let DATA = item.TB_DATA;
                            let No_Number = DATA.NO_NUM.VALUE; //編號

                            if (sfm === "BSOR" || sfm === "CUTS") {
                                item.SSTAT = $("#sSTAT").val(); //護理級值
                            } else {
                                //管路一覽.
                                if ($("#sNM_Tab").children().length === 0) {
                                    $.each(T_CD[2], function (index, val) {
                                        if ((val.ST_LEFT) !== "") {
                                            $("#sNM_Tab").append(
                                                `
                                                  <div class="col-4"><button class="sNM_Ck form-control btn btn-primary btn-lg" value="${val.ST_LEFT + "_" + val.ID_TABITEM + "_" + val.IT_TERMDAYS + "_" + val.IS_IO}">選擇</button></div>
                                                  <div class="col-8 h6">${val.ST_LEFT}</div>
                                                 `
                                            );
                                        }
                                    });
                                }
                            }


                            for (let key in DATA) {
                                let ELE_Type = DATA[key].TYPE;
                                let ELE_Val = DATA[key].VALUE;
                                let title_ID = T_ID[count_element];

                                if ($("#" + title_ID + "_" + ItemNo + "_" + No_Number).length === 0) {

                                    if (ELE_Type === "CB") {
                                        $("." + title_ID).append(
                                            `
                                            <td class="${No_Number}">
                                                <select id="${title_ID + "_" + ItemNo + "_" + No_Number}" class='table-edit'></select>
                                            </td>
                                        `
                                        );

                                        $.each(T_CD[count_element], function (index, val) {
                                            $("#" + title_ID + "_" + ItemNo + "_" + No_Number).append(
                                                `<option value="${val.ID_TABITEM}" >${val.ST_LEFT}</option>`
                                            );

                                            if (ELE_Val.trim() !== "") {
                                                $("#" + title_ID + "_" + ItemNo + "_" + No_Number + " option[value=" + ELE_Val + "]").attr('selected', true);
                                            }

                                        })


                                    }

                                    if (ELE_Type === "ET") {

                                        if (title_ID === "sNMTUBE") {
                                            //管路名稱
                                            $("." + title_ID).append(
                                                `
                                              <td class="${No_Number}">
                                                   <input  type="text" class='table-edit' id="${title_ID + "_" + ItemNo + "_" + No_Number}" value="${ELE_Val}">
                                                   <button value="${ItemNo + "_" + No_Number}" class="sNM_Open form-control-sm btn btn-success">選擇</button>
                                              </td>
                                            `
                                            );

                                        } else {
                                            //發生來源
                                            $("." + title_ID).append(
                                                `
                                              <td class="${No_Number}">
                                                   <input  type="text" class='table-edit' id="${title_ID + "_" + ItemNo + "_" + No_Number}" value="${ELE_Val}">
                                              </td>
                                            `
                                            );
                                        }


                                        if (title_ID === "tb0" || title_ID === "BSOR000043" || title_ID === "BSOR000044") {
                                            $("#" + title_ID + "_" + ItemNo + "_" + No_Number).prop('disabled', true);
                                        }

                                    }
                                    if (No_Number.trim() === "") {
                                        $("#" + title_ID + "_" + ItemNo + "_" + No_Number).parent().hide();
                                    }

                                }
                                count_element++;
                            }

                        });
                    }
                },
                inMMText: (data) => {
                    let En_CharCODE = 65;
                    let MM_title = {"65": "壓瘡等級說明", "66": "護理措施"};

                    for (let key in data) {
                        if ($("#" + 'MM_' + String.fromCharCode(En_CharCODE)).length === 0) {
                            $(".MM_" + String.fromCharCode(En_CharCODE)).append(
                                `
                        <div id="${'MM_' + String.fromCharCode(En_CharCODE)}">
                            <label><b>${MM_title[En_CharCODE]}</b></label>
                        </div>
                        `
                            );


                            $.each(data[key], function (index, val) {
                                $("#MM_" + String.fromCharCode(En_CharCODE)).append(
                                    `
                              <p>${val}</p>

                             `
                                );
                            });

                            En_CharCODE++;
                        }
                    }

                },
                inEdit: (data) => {

                    let txtArea_Nm = data.EDIT_AREA;
                    let txtInput = data.EDIT;


                    for (let key in txtArea_Nm) {
                        if ($("#textArea").length === 0) {
                            $(".txtArea").append(
                                `
                           <label for="textArea">${txtArea_Nm['TB_NM'] + ":"}</label>
                           <textarea id="textArea" class="form-control form-control-lg " readonly></textarea>
                          `
                            )
                        }
                    }


                    if ($(".txtInput").children().length === 0) {
                        $.each(txtInput, function (index, val) {
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


                }
            };

            var drag_value = {
                containment: '.drop-area',
                scroll: false,
                stack: '.draggable',
                start: function (e) {

                },
                drag: function (e) {

                },
                stop: function (event, ui) {
                    let Num = $(this).attr('id').split("").filter((value) => {
                        return !isNaN(parseInt(value));
                    }).join("");


                    let DataObj = Data_obj.get('IMG');
                    let ItemIndex = "";
                    $.each(DataObj, function (index, val) {
                        if (val.NUM === Num) {
                            ItemIndex = index;
                        }
                    });

                    const Width = $(this).css("width", function (index, value) {
                        return parseInt(value.split("px")[0])
                    })[0].offsetWidth;

                    const Height = $(this).css("height", function (index, value) {
                        return parseInt(value.split("px")[0])
                    })[0].offsetHeight;

                    let TOP = (Math.round(ui.position.top)).toString();
                    let LEFT = (Math.round(ui.position.left)).toString();

                    let middelTop = Math.floor(ui.position.top + Height / 2);
                    let middelLeft = Math.floor(ui.position.left + Width / 2);
                    let Region = GetPIXELRegion("<?php echo $sfm?>", middelLeft, middelTop);
                    Data_obj.get('IMG')[ItemIndex].TOP = TOP;
                    Data_obj.get('IMG')[ItemIndex].LEFT = LEFT;

                    if ("<?php echo $sfm?>" === "TUPT") {
                        Data_obj.get('IMG')[ItemIndex].NM_ORGAN = Region.NM_REG;
                        Data_obj.get('IMG')[ItemIndex].ID_REGION = Region.ID_REG;
                    }

                    Data_obj.get('DATA')[ItemIndex].TB_DATA.NM_ORGAN.VALUE = Region.NM_REG;
                    PasteRegion(Num, Region.NM_REG);
                    $("#NO_REG").val(Region.NM_REG);

                }
            };
            var Get_AJson, Get_BJson = false;
            const getDateTime = function () {
                let TimeNow = new Date();
                let Y = TimeNow.toLocaleDateString().slice(0, 4);
                let M = (TimeNow.getMonth() + 1 < 10 ? '0' : '') + (TimeNow.getMonth() + 1);
                let D = (TimeNow.getDate() < 10 ? '0' : '') + TimeNow.getDate();
                let h = (TimeNow.getHours() < 10 ? '0' : '') + TimeNow.getHours();
                let m = (TimeNow.getMinutes() < 10 ? '0' : '') + TimeNow.getMinutes();
                let obj = {};
                obj.Year = (Y - 1911).toString();
                obj.Month = M;
                obj.Day = D;
                obj.Hour = h;
                obj.Min = m;
                return obj;

            };
            $(".drop-area").on("click", function (e) {
                let is_Add = $("#AddSign").val() === "0";
                if (is_Add) {
                    let shape = '<?php echo $shape?>';
                    //取Canvas上圖形最大號 若為空,num=0

                    let num = Data_obj.get('MAXNUM') === "0" ? 0 : parseInt(Data_obj.get('MAXNUM'));
                    let MaxNum = num + 1; //最大號
                    let add_Num = "";  // 新增的編號
                    let copyObj = JSON.parse(JSON.stringify(Data_obj.get('NEWDATA')));

                    /***********取被刪除編號重新排序，已被刪除的最小號開始新增***************************/
                    if (CancelNum.length > 0) {
                        CancelNum.sort((x, y) => x - y);
                        add_Num = CancelNum[0];
                        CancelNum.splice(0, 1);
                    } else {
                        add_Num = MaxNum;
                        Data_obj.set('MAXNUM', MaxNum.toString());//回壓最大直
                    }
                    /******************************end**************************************************/
                    copyObj.TB_DATA.NO_NUM.VALUE = add_Num.toString();

                    if ("<?php echo $sfm?>" === "BOSR" || "<?php echo $sfm?>" === "CUTS") {
                        let newTime = new Date();
                        let DT_Y = (newTime.toLocaleDateString().slice(0, 4) - 1911).toString();
                        let DT_M = (newTime.getMonth() + 1 < 10 ? '0' : '') + (newTime.getMonth() + 1);
                        let DT_D = (newTime.getDate() < 10 ? '0' : '') + newTime.getDate();

                        copyObj.DT_START = DT_Y + DT_M + DT_D;
                    }


                    if (Data_obj.has('DATA')) {
                        //判斷是否新增過
                        Data_obj.get('DATA').push(copyObj);
                    } else {
                        Data_obj.set('DATA', [copyObj]);
                    }

                    creatTable.inTableTd("<?php echo $sfm?>", 'B', [copyObj]);

                    AddShape(add_Num, shape, e.offsetX + 10, e.offsetY, 15, 15);
                    let Region = GetPIXELRegion("<?php echo $sfm?>", e.offsetX + 7, e.offsetY + 7);
                    PasteRegion(add_Num, Region.NM_REG);


                    // 迭代新增部位名稱
                    for (let [key, value] of Data_obj) {
                        if (key === "IMG") {
                            value.filter((val) => {
                                return val.NUM === add_Num.toString()
                            })
                                .forEach((val) => {
                                    if ("<?php echo $sfm?>" === "TUPT") {
                                        val.ID_REGION = Region.ID_REG;
                                    }
                                    val.NM_ORGAN = Region.NM_REG;
                                });
                        }

                        if (key === "DATA") {
                            value.filter((val) => {
                                return val.TB_DATA.NO_NUM.VALUE === add_Num.toString()
                            })
                                .forEach((val) => val.TB_DATA.NM_ORGAN.VALUE = Region.NM_REG);
                        }
                    }

                    $("#AddSign").val("");
                }
            });
            $(document).on('click', 'button', function (e) {
                let btn_id = $(this).attr('id'); //sNM_Open
                let btn_class = $(this).attr('class').split(" ")[0];
                let sTraID = $("#sTraID").val();
                const Page = $("#Page").val();
                const IdPt = $("#DA_idpt").val();
                const InPt = $("#DA_idinpt").val();
                const PName = $("#DataTxt").val();
                const sDt = $("#sDate").val();
                const sTm = $("#sTime").val();
                const Num = $("#NO_NUM").val();


                switch (btn_id) {
                    case "sbed":
                        if (!checkBEDwindow()) {
                            alert("責任床位視窗已開啟");
                            break;
                        } else {
                            try {
                                BEDwindow = window.open("/webservice/NISPRWCBED.php?str=" + AESEnCode("sFm=ILSGA&sIdUser=<?php echo $sUr?>"),
                                    "責任床位", 'width=850px,height=650px,scrollbars=yes,resizable=no');

                            } catch (e) {
                                console.log(e);
                            }
                        }

                        BEDwindow.bedcallback = bedcallback;
                        break;
                    case "SubmitBtn":
                        let Json_obj = Page === "A" ? Data_obj.get('IMG') : Data_obj.get('DATA');

                        let B_obj = Data_obj.get('DATA');
                        let sfm = '<?php echo $sfm?>';

                        const error_msg = B_obj.map((val) => {
                            let msg = [];
                            for (let key in val.TB_DATA) {
                                let Num = (val.TB_DATA.NO_NUM.VALUE).trim();
                                let Value = (val.TB_DATA[key].VALUE).toString();
                                let TD_id = (val.TB_DATA[key].ID);
                                let sfm_Nm = ("<?php echo $Title_NM?>").substr(0, 2);

                                if (Num !== "") {
                                    if (sfm === "BSOR" || sfm === "CUTS") {
                                        if (Value.trim() === "") {
                                            if (TD_id === "BSOR000001") {
                                                //BSOR000001 發生來源
                                                msg.push('編號:' + Num + '提醒:發生來源禁止空值');
                                            }
                                            if (TD_id === "BSOR000009" || TD_id === "BSOR000051") {
                                                //BSOR000051 傷口等級
                                                //BSOR000009 壓瘡等級
                                                msg.push('編號:' + Num + '提醒:' + sfm_Nm + '等級禁止空值');

                                            }

                                        }

                                    } else if (sfm === "TUPT") {
                                        if (Value.trim() === "") {
                                            //sNMTUBE 管路名稱
                                            if (TD_id === "sNMTUBE") {
                                                msg.push('編號:' + Num + '提醒:管路名稱禁止空值');
                                            }
                                        }
                                    }
                                }

                            }
                            return msg;
                        })
                            .reduce((previousValue, currentValue, currentIndex, array) => {
                                return previousValue.concat(currentValue);
                            }, []);

                        $("#wrapper,#loading").show();

                        setTimeout(function () {
                            if (error_msg.length > 0) {
                                alert(error_msg.join('\n'));
                                $("#wrapper,#loading").hide();
                                return false;
                            }

                            DB_WSST(Page, sTraID, JSON.stringify(Json_obj), sDt, sTm, '', '', "<?php echo $sUr?>", 'true');
                        }, 100);
                        break;
                    case "DELBtn":
                        let DelConfirm_Str = "";

                        if (Page === "A") {
                            if (Num.trim() === "") {
                                alert('請選擇要作廢的編號');
                                return false;
                            }
                            DelConfirm_Str = "確定要作廢編號[" + Num + "]的所有資料嗎?(所有此編號的紀錄將一併刪除)";
                        } else {
                            DelConfirm_Str = "是否確定要作廢[" + sDt + " " + sTm + "]的資料嗎?";
                        }


                        $(".modal-body>p").empty();
                        $(".modal-body>p").text(DelConfirm_Str);
                        $("#DelModal").modal('show');
                        break;
                    case "SearchBtn":
                        if (!checkSerchwindow()) {
                            alert("查詢視窗已開啟");
                            break;
                        } else {

                            if (IdPt.trim() === "" || InPt.trim() === "" || PName.trim() === "") {
                                alert('請先選擇病人');
                                return false;
                            }

                            Serchwindow = window.open("/webservice/NISPWSLKQRY.php?str=" +
                                AESEnCode("sFm=" + "BSOR" + "&PageVal=" + "" + "&DA_idpt=" + IdPt + "&DA_idinpt=" + InPt
                                    + "&sUser=" + "<?php echo $sUr?>" + "&NM_PATIENT=" + PName + "&TsFm=" + "<?php echo $sfm?>")
                                , "<?php echo $sfm?>", 'width=750px,height=650px,scrollbars=yes,resizable=no');
                        }
                        Serchwindow.Serchcallback = Serchcallback;
                        break;
                    case "DelConfirm_Btn":

                        let Update_result = DB_DEL(sTraID, Page, '<?php echo $sUr?>');
                        if (Update_result.result === "false") {
                            alert('作廢失敗:' + Update_result.message);
                            console.log('作廢失敗:' + Update_result.message);
                        } else {
                            $(".draggable").remove();
                            $(".DateTime").val("");
                            $("input[type=radio]").prop('disabled', false);
                            $("input[type=radio]").prop('checked', false);
                            $("#DELBtn").prop('disabled', true);
                            $(".Main,.B,.MMDIV").hide();
                            $("#DrawOutModal").modal('hide');
                            GetINIJson("<?php echo $sfm?>", IdPt, InPt);
                            console.log(Data_obj);

                        }
                        $("#DelModal").modal('hide');
                        break;
                    case "CancelBtn":
                        RemoveShape(Num);
                        break;
                    case "ReSetBtn":
                        $("input[type=text]:not(#sfm)").val("");
                        $("input[type=radio]").prop('checked', false);
                        $(".Main,.B,.MMDIV").hide();
                        break;
                    default:
                        break;
                }


                if (btn_class === "sNM_Open") {
                    $("#sNMTUBEModal").modal('show');
                    $("#OpenSNM_Modal").val($(this).val());
                } else if (btn_class === "sNM_Ck") {
                    let strVal = ($(this).val()).split("_");
                    let strOpenSNM_Val = ($("#OpenSNM_Modal").val()).split("_");

                    let NM_TUBE = strVal[0];//管路名稱
                    let ID_TUBE = strVal[1];//管路id
                    let IT_TERMDAYS = parseInt(strVal[2]);//管路預估換管日
                    let IS_IO = strVal[3];//入院帶入

                    let Index = strOpenSNM_Val[0];//編號Index
                    let Num = strOpenSNM_Val[1];//編號
                    // let FormSeq=Data_obj.get('IMG').filter((val)=>{return val.NUM===Num})[0].FORMSEQ;
                    let sDTEND = "";

                    const FilterNumData = Data_obj.get('DATA').filter((val) => {
                        return val.TB_DATA.NO_NUM.VALUE === Num
                    })[0];


                    // if (FormSeq.trim()===""){
                    //
                    // }
                    // else {
                    //     FilterNumData.TB_DATA.ID_TUBE.VALUE=ID_TUBE;
                    //     FilterNumData.TB_DATA.sNM_TUBE.VALUE=NM_TUBE;
                    // }
                    let tDate = new Date();
                    let strDate = tDate.toLocaleDateString().slice(0, 4) - 1911 +
                        (tDate.getMonth() + 1 < 10 ? '0' : '') +
                        (tDate.getMonth() + 1) + (tDate.getDate() < 10 ? '0' : '') + tDate.getDate();

                    ['sDTEND_', 'sDTEXE_', 'sCDSTATUS_'].forEach((value => $("#" + value + Index + "_" + Num).val("")));

                    if (IT_TERMDAYS !== 0) {
                        sDTEND = addDate(strDate, IT_TERMDAYS);
                    }
                    $("#sDTEXE_" + Index + "_" + Num).val(strDate);
                    $("#sDTEND_" + Index + "_" + Num).val(sDTEND);
                    $("#sCDSTATUS_" + Index + "_" + Num).val(IS_IO);

                    for (let key in FilterNumData.TB_DATA) {
                        if (key === "ID_TUBE") {
                            FilterNumData.TB_DATA[key].VALUE = ID_TUBE;
                        }
                        if (key === "sNM_TUBE") {
                            FilterNumData.TB_DATA[key].VALUE = NM_TUBE;
                        }
                        if (key === "sDT_EXE") {
                            FilterNumData.TB_DATA[key].VALUE = strDate;
                        }
                        if (key === "sDT_END") {
                            FilterNumData.TB_DATA[key].VALUE = sDTEND;
                        }
                        if (key === "CD_STATUS") {
                            FilterNumData.TB_DATA[key].VALUE = IS_IO;
                        }

                        if (key === "IT_TERMDAYS") {
                            FilterNumData.TB_DATA[key].VALUE = IT_TERMDAYS;
                        }


                    }

                    if (ID_TUBE === "XXX") {
                        NM_TUBE = "";
                    }

                    Data_obj.get('IMG').filter((val) => {
                        return val.NUM === Num
                    })[0].sNM_TUBE = NM_TUBE;

                    DB_WSST('A', sTraID, JSON.stringify(Data_obj.get('IMG')), '', '', '', '', '', 'false');
                    $("#sNMTUBE_" + Index + "_" + Num).val(NM_TUBE);
                    $("#sNMTUBEModal").modal('hide');
                } else if (btn_class === "DrawOut_Confirm_btn") {
                    let Num_Obj = Data_obj.get('DATA').filter((value) => {
                        return value.TB_DATA.NO_NUM.VALUE === Num
                    })[0];
                    let DrawOut_Flag = "";
                    if (btn_id === 'DrawOut_Del') {
                        let Del_result = DB_DEL(sTraID, Page, '<?php echo $sUr?>');
                        if (Del_result.result === "false") {
                            alert('作廢失敗:' + Del_result.message);
                            console.log('作廢失敗:' + Del_result.message);
                        } else {
                            $(".draggable").remove();
                            $("#DrawOutModal").modal('hide');
                            GetINIJson("<?php echo $sfm?>", IdPt, InPt);
                            $(".Main,.B").hide();
                        }
                    } else {
                        if (btn_id === "DrawOut_N") {
                            //換管
                            DrawOut_Flag = "N";
                            Num_Obj.TB_DATA.sDT_EXE.VALUE = $("#sDt").val();
                            Num_Obj.TB_DATA.sDT_END.VALUE = $("#eDt").val();


                        } else if (btn_id === "DrawOut_Y") {
                            //拔管
                            DrawOut_Flag = "Y";
                            Num_Obj.TB_DATA.TM_EXE.VALUE = $("#sDt").val();
                            Num_Obj.TB_DATA.TM_END.VALUE = $("#eDt").val();

                        }

                        let Change_result = ChangeTUPTCOM(DrawOut_Flag, sTraID, JSON.stringify(Num_Obj));
                        if (Change_result.result !== "true") {
                            alert(Change_result.message);
                            console.log(Change_result.message);
                        } else {
                            if (DrawOut_Flag === "Y") {
                                $("." + Num).remove();
                            }
                            GetINIJson("<?php echo $sfm?>", IdPt, InPt);
                            $("#NO_NUM,#NO_REG,.DateTime").val("");
                            $("#DrawOutModal").modal('hide');
                            $(".Main,.B").hide();

                        }
                    }

                }
            });

            //修改圖形
            $(".sign").on('click', function () {
                //0=>add
                //1=>remove
                //2=>Tobig
                //3=>Tosmall
                const Sign_val = $(this).val();
                if (Sign_val === "1") {
                    let Num = $("#NO_NUM").val();

                    if (Num.trim() === "") {
                        alert("請選擇要刪除的編號");
                        return;
                    }

                    //沒有表單單號能刪除
                    if (Data_obj.get('IMG').filter((val) => {
                        return val.FORMSEQ !== "" && val.NUM === Num
                    }).length > 0) {
                        alert("提醒:僅能刪除新增的[編號]");
                        return;
                    }

                    $(".modal-body>p").empty();
                    $(".modal-body>p").text('確定要刪除編號:[' + $("#NO_NUM").val() + ']嗎?');
                    $("#CancelModal").modal('show');
                }
                $("#AddSign").val($(this).val());
                changeThisSize($(this).val());
            });

            $(".Page>button").on('click', function () {

                if ($("#DataTxt").val() === "") {
                    alert('請先選擇病人');
                    return;
                }

                const Page = $(this).attr('id');
                let sTraID = $("#sTraID").val();
                let obj = "";
                let TransPage = Page === "A" ? "B" : "A";
                if (Page === "A") {
                    if (!Get_AJson) {
                        GetPageJson('A', sTraID);
                        Get_AJson = true;
                    }
                    obj = Data_obj.get('DATA');
                    $(".area-table").hide();
                    $(".Main").show(500);
                } else {
                    if (!Get_BJson) {
                        GetPageJson('B', sTraID);
                        Get_BJson = true;
                    }
                    $("button[id='A']").prop('disabled', false);
                    obj = Data_obj.get('IMG');
                    $(".area-table").show();
                    $(".Main").hide(500);
                }

                $(".MMDIV").hide();
                $(".MM_" + Page).show();
                $("#Page").val(Page);
                $("#SubmitBtn").prop('disabled', false);

                if (Get_AJson && Get_BJson) {
                    DB_WSST(TransPage, sTraID, JSON.stringify(obj), '', '', '', '', '', 'false');
                }
            });

            //換(拔)管
            $(".DrawOut_btn").on('click', function () {
                let Index = $(this).index().toString();
                let msgTitle = "";
                let NUM = $("#NO_NUM").val();

                if (NUM.trim() === "") {
                    alert('請先點擊選擇編號');
                    return;
                }

                let Num_Obj = Data_obj.get('DATA').filter((value) => {
                    return value.TB_DATA.NO_NUM.VALUE === NUM
                })[0];
                let NM_TUBE = Num_Obj.TB_DATA.sNM_TUBE.VALUE;
                let ThisID_NM = "";
                $("#DrawOutModal>div>.modal-content>.modal-body>.container-fluid").children().remove();
                switch (Index) {
                    case "0":
                        //換管

                        let DT_EXE = Num_Obj.TB_DATA.sDT_EXE.VALUE,
                            DT_END = Num_Obj.TB_DATA.sDT_END.VALUE,
                            TERMDAYS = Num_Obj.TB_DATA.IT_TERMDAYS.VALUE,
                            sDt = DT_END !== "" ? DT_END : DT_EXE,//置管日
                            eDt = DT_END !== "" ? addDate(DT_END, parseInt(TERMDAYS)) : "";//換管日

                        msgTitle = "換管提醒 ";

                        $("#DrawOutModal>div>.modal-content>.modal-body>.container-fluid").append(
                            `
                            <div class="input-group input-group-sm mb-3">
                                <div class="input-group-prepend">
                                    <span class="input-group-text" id="inputGroup-sizing-sm">置入日:</span>
                                </div>
                                <input type="text" id="sDt" class="form-control" aria-label="Small" aria-describedby="inputGroup-sizing-sm" value="${sDt}" maxlength="7">
                                <div class="input-group-prepend">
                                    <span class="input-group-text" id="inputGroup-sizing-sm">換管日:</span>
                                </div>
                                <input type="text" id="eDt"  class="form-control" aria-label="Small" aria-describedby="inputGroup-sizing-sm" value="${eDt}" maxlength="7">

                            </div>
                           `
                        );
                        ThisID_NM = "DrawOut_N";

                        break;
                    case "1":
                        //拔管
                        msgTitle = "拔管管提醒 ";
                        let Dt = getDateTime();

                        $("#DrawOutModal>div>.modal-content>.modal-body>.container-fluid").append(
                            `
                           <div class="input-group input-group-sm mb-3">
                                <div class="input-group-prepend">
                                    <span class="input-group-text" id="inputGroup-sizing-sm">拔管日:</span>
                                </div>
                                <input type="text" id="sDt" class="form-control" aria-label="Small" aria-describedby="inputGroup-sizing-sm" value="${Dt.Year + Dt.Month + Dt.Day}"  maxlength="7">
                                <div class="input-group-prepend">
                                    <span class="input-group-text" id="inputGroup-sizing-sm">時間:</span>
                                </div>
                                <input type="text" id="eDt"  class="form-control" aria-label="Small" aria-describedby="inputGroup-sizing-sm"  value="${Dt.Hour + Dt.Min}" maxlength="4">

                            </div>

                           `
                        );

                        ThisID_NM = "DrawOut_Y";


                        break;
                    case "2":
                        //換管作廢
                        msgTitle = "作廢提醒 ";

                        $("#DrawOutModal>div>.modal-content>.modal-body>.container-fluid").append(
                            `
                           <p>確定要作廢嗎?</p>
                           `
                        );
                        ThisID_NM = "DrawOut_Del";

                        break;
                    default:
                        break;
                }
                $(".DrawOut_Confirm_btn").attr('id', ThisID_NM);
                msgTitle += "編號:[" + NUM + "]" + "換管名稱:[" + NM_TUBE + "]";
                $("#DrawOutModal>div>.modal-content>.modal-header>h4").text(msgTitle);
                $("#DrawOutModal").modal('show');
            });

            $(document).on("click mousedown", ".draggable", function (e) {
                let is_Add = $("#AddSign").val() === "0";
                let ThisDiv_id = $(this).attr('id');
                let Num = $(this).children().text();

                let Region = Data_obj.get('IMG')
                    .filter((value) => {
                        return value.NUM === Num
                    });

                $("#div_nm").val(ThisDiv_id);
                $("#NO_NUM").val(Num);
                $("#NO_REG").val(Region[0].NM_ORGAN);
                if (!is_Add) {
                    $('.sign').css({'background-color': '', 'color': '', "border": ""});
                }

            });

            //動態填值
            $(document).on("change", ".table-edit", function () {

                let arrThis = $(this).attr('id').split("_");

                let TD_class = arrThis[0];
                // let Index= arrThis[1];
                let Num = arrThis[2];
                let ThisData = Data_obj.get('DATA').filter((val) => {
                    return val.TB_DATA.NO_NUM.VALUE === Num;
                })[0].TB_DATA;

                let Val = $(this).val();

                for (let key in ThisData) {

                    if (TD_class === ThisData[key].ID && ThisData[key].ID !== "") {

                        ThisData[key].VALUE = $(this).val();

                    } else if (ThisData[key].ID === "") {
                        if (TD_class === "tb0") {
                            ThisData.NO_NUM.VALUE = Val;
                        }
                        if (TD_class === "tb1") {
                            ThisData.NM_ORGAN.VALUE = Val;
                        }
                    }

                }
            });
            $(document).on('change', 'input[name=sRdoDateTime]', function () {

                let TimeNow = getDateTime();
                let Timeit = ($(this).val()).split("");

                let timer = Timeit.filter(function (value) {
                    return value !== ":"
                });
                let timerVal = $(this).attr('id') === "ISTM00000005" ? TimeNow.Hour + TimeNow.Min : timer.join("");
                $("#sDate").val(TimeNow.Year + TimeNow.Month + TimeNow.Day);
                $("#sTime").val(timerVal);
            });

            function AddShape(Number, shape, X, Y, W, H) {
                let text_ele = "";
                let shape_Nm = "";

                if (Number === "") return;
                if (shape === "triangle") {
                    text_ele = '<div>' + Number + '</div>';
                    shape_Nm = "t";
                }
                if (shape === "square") {
                    text_ele = "<div>" + Number + "</div>";
                    shape_Nm = "s";
                }
                if (shape === "circle") {
                    text_ele = "<p>" + Number + "</p>";
                    shape_Nm = "c";
                }

                if ($('#' + shape_Nm + Number).length > 0) {
                    return;
                }

                $("#CanvasPad").before(
                    `
                      <div class="${'draggable ' + shape + ' ' + Number}" id="${shape_Nm + Number}" >
                           ${text_ele}
                      </div>
                   `
                );

                $("#" + shape_Nm + Number).css({
                    "left": X + "px",
                    "top": Y + "px",
                    "width": W + "px",
                    "height": H + "px"
                });


                const isAdd = Data_obj.get('IMG').filter((value, index, arr) => {
                    return value.NUM === Number;
                });

                if (isAdd.length === 0) {
                    let newObj = JSON.parse(JSON.stringify(Data_obj.get('IMG')[0]));
                    //新增標記
                    newObj.NUM = Number.toString();
                    newObj.LEFT = X.toString();
                    newObj.TOP = Y.toString();
                    newObj.W_TH = W.toString();
                    newObj.H_TH = H.toString();
                    newObj.FORMSEQ = "";
                    newObj.DATESEQ = "";

                    Data_obj.get('IMG').push(newObj);
                }

                $(".draggable").each(function () {
                    $(this).draggable(drag_value);
                });
            }

            function RemoveShape(Num) {

                let Index = "";
                $.each(Data_obj.get('IMG'), function (index, val) {
                    if (val.NUM === Num) {
                        Index = index;
                    }
                });

                Data_obj.get('IMG').splice(Index, 1);
                Data_obj.get('DATA').splice(Index, 1);
                $("." + Num).remove();
                $("#NO_REG,#NO_NUM").val("");

                CancelNum.push(parseInt(Num));
                $("#CancelModal").modal('hide');
            }

            function changeThisSize(num) {
                if (parseInt(num) < 2) {
                    return;
                }
                let sfm = '<?php echo $sfm?>';
                let id = $("#div_nm").val();
                let N = num === "2" ? 1 : -1;

                let ele = $("#" + id);
                let h = ele.outerHeight(true) + N;
                let w = ele.outerWidth(true) + N;

                if (id.trim() === "") {
                    alert('請選擇要修改的編號');
                    return;
                }

                if (h <= 15 || w <= 15) {
                    return;
                }


                if (sfm === "TPUP") {
                    let border_width = ele.css('border-width')
                        .split(" ")
                        .map(value => parseInt(value) + N + 'px');


                    $("#" + id).css({
                        "border-width": border_width.join(" ")
                    });

                } else {

                    $("#" + id).css({"height": h, "width": w});

                    Data_obj.get('IMG')
                        .filter((val) => {
                            return val.NUM === id.substring(1, id.length)
                        })
                        .forEach((val) => {
                            val.W_TH = h.toString();
                            val.H_TH = w.toString();
                        });
                }
            }

            function bedcallback(data) {
                let str = AESDeCode(data);
                let dataObj = JSON.parse(str)[0];
                let idPt = dataObj.IDPT;
                let INPt = dataObj.IDINPT;
                let sBed = dataObj.SBED;
                let P_NM = dataObj.DataTxt;
                let ssTAT = dataObj.sSTAT;
                $("#DA_idpt").val(idPt);
                $("#DA_idinpt").val(INPt);
                $("#DA_sBed").val(sBed);
                $("#DataTxt").val(P_NM);
                $("#sSTAT").val(ssTAT);

                GetINIJson('<?php echo $sfm?>', idPt, INPt);

                $(".Page").next('div').hide();
                $(".B,.MMDIV").hide();
                $(".draggable").remove();
                $(".DateTime").prop('readonly', false);
                $(".DateTime ").val("");
                $("#SubmitBtn").prop('disabled', true);
                $("input[type=radio]").prop('checked', false);
                $("#ISTM>label").children('input').prop('disabled', false);
                CancelNum.length = 0;
            }

            function Serchcallback(AESobj) {
                const obj = JSON.parse(AESDeCode(AESobj));
                const sTraID = obj.sTraID;
                const sTime = obj.TMEXCUTE;
                const sDate = obj.DTEXCUTE;
                const IDPT = obj.IDPT;
                const INPT = obj.INPT;

                if ($("#DA_idpt").val() !== IDPT || $("#DA_idinpt").val() !== INPT) {
                    alert('病人資料有異動請重新選擇病人');
                    return;
                }


                $("input[name=sRdoDateTime]").each(function () {
                    if ($(this).val().split(":").join("") === sTime) {
                        $(this).prop('checked', true);
                    } else {
                        $("#ISTM00000005").prop('checked', true);
                        $("#ISTM00000005").prop('checked', true);
                    }
                });
                $("#sTime").val(sTime);
                $("#sDate").val(sDate);
                $("#sTraID").val(sTraID);

                if ($(".table-edit").parent().length > 0) {
                    $(".table-edit").parent().remove();
                }
                //座標移除
                $(".draggable").remove();
                Data_obj.delete('IMG');
                Data_obj.delete('DATA');


                $(".Main,.B,.MMDIV").hide();//pageA,pageB,pageMM
                $(".DateTime").prop('readonly', true);
                $("#DELBtn").prop('disabled', false);
                $("#ISTM > label").children('input').prop('disabled', true);

                Get_AJson = false; //GetPageJson false
                Get_BJson = false;
                CancelNum.length = 0;

            }

            function GetINIJson(sfm, idPt, INPt) {
                $.ajax("/webservice/NISPWSTRAINI.php?str=" + AESEnCode('sFm=BSOR' + '&idPt=' + idPt + '&INPt=' + INPt + "&sUr=" + '<?php echo $sUr?>' + "&TsFm=" + '<?php echo $sfm?>'))
                    .done(function (data) {
                        let obj = JSON.parse(AESDeCode(data));
                        Data_obj.clear();
                        creatTable.Default(obj);
                        $("#NO_NUM,#NO_REG").val("");
                        $("#sTraID").val(obj.sTraID);
                        $("#sSave").val(obj.sSave);
                        Get_AJson = false;
                        Get_BJson = false;
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
                $.ajax({
                    url: "/webservice/NISPWSGETPRE.php?str=" + AESEnCode("sFm=BSOR&sTraID=" + sTraID + "&sPg=" + Page + "&TsFm=" + '<?php echo $sfm?>'),
                    dataType: "text",
                    async: false,
                    success: function (data) {

                        let obj = JSON.parse(AESDeCode(data));
                        let Data_A = obj.DATA_A;
                        let Data_B = obj.DATA_B;

                        let CreatDataTd = Page === "A" ? Data_A : Data_B;

                        if (!Data_obj.has('IMG')) {
                            Data_obj.set('IMG', Data_A);
                        }

                        if (!Data_obj.has('DATA')) {
                            Data_obj.set("DATA", Data_B);
                        }

                        creatTable.inTableTd("<?php echo $sfm?>", Page, CreatDataTd);
                        console.log(Data_A);
                        console.log(Data_B);
                    }, error: function (XMLHttpResponse, textStatus, errorThrown) {
                        console.log(
                            "1 返回失敗,XMLHttpResponse.readyState:" + XMLHttpResponse.readyState + XMLHttpResponse.responseText +
                            "2 返回失敗,XMLHttpResponse.status:" + XMLHttpResponse.status +
                            "3 返回失敗,textStatus:" + textStatus +
                            "4 返回失敗,errorThrown:" + errorThrown
                        );
                    }
                });
            }

            function DB_WSST(Page, sTraID, json, sDt = null, sTm = null, Passed = null, Freq = null, sUr, InSertDB) {

                $.ajax({
                    type: "POST",
                    url: '/webservice/NISPWSSETDATA.php?str=' + AESEnCode(
                        'sFm=BSOR&sTraID=' + sTraID + '&sPg=' + Page + '&sData=' + encodeURI(json) +
                        '&sDt=' + sDt + '&sTm=' + sTm + '&Fseq=' + Freq + '&PASSWD=' + Passed +
                        '&USER=' + sUr + '&Indb=' + InSertDB + "&TsFm=" + '<?php echo $sfm?>')

                }).done(function (data) {

                    let json = JSON.parse(AESDeCode(data));
                    if (InSertDB === "true") {
                        if (json.result === "true") {
                            alert('存檔成功');
                            window.location.replace(window.location.href);
                        } else {
                            alert("儲存失敗,錯誤訊息:" + json.message);
                        }
                        $("#wrapper,#loading").hide();
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

            function DB_DEL(sTraID, Page, sUr) {
                let result = "";
                const DelNum = $("#NO_NUM").val();
                $.ajax({
                    url: "/webservice/NISPWSDELILSG.php?str=" + AESEnCode("sFm=" + 'BSOR' + "&sTraID=" + sTraID + "&sPg=" + Page + "&sCidFlag=" + DelNum + "&sUr=" + sUr + "&TsFm=" + '<?php echo $sfm?>'),
                    async: false
                })
                    .done(function (data) {
                        let response = JSON.parse(AESDeCode(data));
                        result = response;
                    }).fail(function (XMLHttpResponse, textStatus, errorThrown) {
                    console.log(
                        "1 返回失敗,XMLHttpResponse.readyState:" + XMLHttpResponse.readyState + XMLHttpResponse.responseText +
                        "2 返回失敗,XMLHttpResponse.status:" + XMLHttpResponse.status +
                        "3 返回失敗,textStatus:" + textStatus +
                        "4 返回失敗,errorThrown:" + errorThrown
                    );
                });
                return result;
            }

            function ChangeTUPTCOM(CidFlag, sTraID, strJson) {
                let result = "";
                $.ajax({
                    url: "/webservice/NISBSORCOM.php?str=" + AESEnCode("sFm=" + 'TUPT' + "&DRAWOUT=" + CidFlag + "&sTraID=" + sTraID + '&sData=' + encodeURI(strJson) + "&sUr=" + '<?php echo $sUr?>'),
                    async: false
                })
                    .done(function (data) {
                        let response = JSON.parse(AESDeCode(data));
                        result = response;
                        console.log(response);
                    }).fail(function (XMLHttpResponse, textStatus, errorThrown) {
                    console.log(
                        "1 返回失敗,XMLHttpResponse.readyState:" + XMLHttpResponse.readyState + XMLHttpResponse.responseText +
                        "2 返回失敗,XMLHttpResponse.status:" + XMLHttpResponse.status +
                        "3 返回失敗,textStatus:" + textStatus +
                        "4 返回失敗,errorThrown:" + errorThrown
                    );
                });
                return result;
            }

            function NISPWSFMINI_Timer(sFm, Page) {
                $.ajax({
                    url: "/webservice/NISPWSFMINI.php?str=" + AESEnCode("sFm=" + sFm + "&sPg=" + Page),
                    type: "POST",
                    dataType: "text",
                    success: function (data) {
                        let obj = JSON.parse(AESDeCode(data));
                        let arr = JSON.parse(obj.ST_PREA);

                        $.each(arr, function (index, item) {
                            $("#ISTM").append(
                                `
                                <label style='font-size: 4.5vmin'>
                                    <input type='radio' name='sRdoDateTime' id='${item.T_ID}' value='${item.name}' style='width: 6vmin;height: 6vmin' >${item.name}
                                </label>
                                `
                            )
                        });
                    }, error: function (XMLHttpResponse, textStatus, errorThrown) {
                        console.log(
                            "1 返回失敗,XMLHttpResponse.readyState:" + XMLHttpResponse.readyState + XMLHttpResponse.responseText +
                            "2 返回失敗,XMLHttpResponse.status:" + XMLHttpResponse.status +
                            "3 返回失敗,textStatus:" + textStatus +
                            "4 返回失敗,errorThrown:" + errorThrown
                        );
                    }
                });
            }

            /**
             * @return {string}
             */
            function GetPIXELRegion(sFm, X, Y) {
                let response = "";
                $.ajax({
                    url: "/webservice/NISBSORPIXEL.php?str=" + AESEnCode('&PIXEL_X=' + X + '&PIXEL_Y=' + Y),
                    async: false
                }).done(function (data) {
                    let obj = JSON.parse(AESDeCode(data));
                    if (obj.NM_REG === "") {
                        obj.NM_REG = "不正確";
                    }
                    response = obj;

                }).fail(function (XMLHttpResponse, textStatus, errorThrown) {
                    console.log(
                        "1 返回失敗,XMLHttpResponse.readyState:" + XMLHttpResponse.readyState + XMLHttpResponse.responseText +
                        "2 返回失敗,XMLHttpResponse.status:" + XMLHttpResponse.status +
                        "3 返回失敗,textStatus:" + textStatus +
                        "4 返回失敗,errorThrown:" + errorThrown
                    );
                });

                return response;
            }

            function PasteRegion(Num, Region) {
                $(".table-edit").each(function (index, ele) {
                    let nm_val = $(ele).attr('id').split("_");
                    let classNm = nm_val[0];
                    // let classIndex=nm_val[1];
                    let classNum = nm_val[2];
                    if (classNm === "tb1" || classNm === "sNM") {
                        if (classNum === Num.toString()) {
                            $(ele).val(Region);
                        }
                    }

                });
            }

            function addDate(day, AddDay) {

                const Dates = (parseInt(day.substring(0, 3)) + 1911).toString() + "-" + day.substring(3, 5) + "-" + day.substring(5, 7);

                let dt = new Date(Dates);

                dt.setDate(dt.getDate() + AddDay);
                let month = dt.getMonth() + 1;
                let days = dt.getDate();
                if (month < 10) {
                    month = "0" + month;
                }

                if (days < 10) {
                    days = "0" + days;
                }

                return (dt.getFullYear() - 1911) + month.toString() + days.toString();

            }

            function checkBEDwindow() {

                if (!BEDwindow) {
                    return true;
                } else {
                    return !!BEDwindow.closed;
                }
            }

            function checkSerchwindow() {
                if (!Serchwindow) {
                    return true;
                } else {
                    return !!Serchwindow.closed;
                }
            }
        });

    </script>
</head>
<style>
    body {
        height: 100%;
    }

    .Parametertable {
        display: none;
    }

    .drop-area {
        width: 395px;
        height: 425px;

    }

    .area-table {
        display: none;
        border: 1px solid #dee2e6;
        overflow-x: auto;
    }

    table {
        border: 1px solid #dee2e6;
        table-layout: auto;
        width: 100%;
    }

    tr > th {
        position: sticky;
        left: 0;
        min-width: 200px;
        z-index: 1;
        background-color: white;
    }

    td, th, tr {
        border: 1px solid #dee2e6;
    }

    .container .title button {
        color: white;
        font-size: 4vmin;
        margin-top: 5px;
        margin-bottom: 5px;
    }

    #DataTxt {
        font-size: 4.5vmin;
        background-color: #FFFBCC;
        border-radius: 3px;
        margin-top: 5px;
        color: black;
    }

    .container .Otimer {
        margin: 5px 0 5px 15px;
        padding: 5px 10px 0 0;
        font-size: 4vmin;
        background-color: #baeeff;
        border-radius: 3px;
    }


    .Page, .Page ~ div {
        margin-top: 10px;
    }


    /*************繪圖*****************/
    #CanvasPad {
        width: 395px;
        height: 425px;
        border: 3px grey solid;
    }


    .circle {
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

    .circle > p {
        padding-top: 13px;
        z-index: 1;

    }


    .triangle {
        height: 0px;
        width: 0px;
        border-color: transparent transparent #AAAAFF transparent;
        border-style: solid solid solid solid;
        border-width: 0 8px 15px 8px;
        position: absolute;
        display: flex;
    }

    .square {
        height: 15px;
        width: 15px;
        border: 2px solid red;
        position: absolute;
        display: flex;
        background-color: greenyellow;
        opacity: 0.8;
        z-index: 1;
    }


    .triangle > div {
        margin-left: -4px;
        font-size: 10px;
    }

    .square > div {
        margin-top: -5px;
        text-align: center;
        font-weight: bold;
    }


    /*時間*/
    .DateTime::placeholder {
        font-size: 3.5vmin;
    }

    .DateTime {
        font-size: 3.5vmin;
    }

    #ISTM > label {
        padding: 10px 0 0 0;
    }


    /*備註*/
    .MMDIV > div {
        border: none;
        background-color: #FFFBCC;
    }

    .MMDIV > div > P {
        font-size: 2vmin;
    }

    textarea {
        overflow-y: scroll;
        resize: none
    }

    .form-control[readonly] {
        background-color: white;
    }

    .CanvasRow {
        margin: 0 15px 0 0;
    }

    .SignRow {
        padding: 0;
    }

    #sNM_Tab > div, .sNM_Open {
        margin: 5px 0 0 0;
    }

    .col-8 {
        padding-top: 10px;
    }

    #wrapper {
        position: absolute;
        height: 100%;
        background-color: black;
        opacity: 0.5;
        z-index: 9998;
    }

    #loading {
        position: absolute;
        z-index: 9999;
        top: 50%;
        left: 50%;
        background-color: #FFFFFF;
        color: #000000;
        font-size: 5vmin;
        width: 45vmin;
        height: 12vmin;
        padding-left: 20px;
        padding-top: 10px;
        border-radius: 5px;
        margin: -15vmin 0 0 -30vmin;

    }

    #loading .loadimg {
        width: 10vmin;
        height: 10vmin;
    }

</style>
<body>
<div id="wrapper" class="container-fluid"></div>
<div id="loading">請稍後<img class="loadimg" src="../../dotloading.gif"></div>

<div class="container">
    <div class="Parametertable">
        <input id="DA_idpt" value="" type="text" placeholder="病歷號">
        <input id="DA_idinpt" value="" type="text" placeholder="住院號">
        <input id="DA_sBed" value="" type="text" placeholder="床位">
        <input id="sSTAT" value="" type="text" placeholder="護理站代碼">
        <input id="sSave" value="" type="text" placeholder="存檔權限">
        <input id="sTraID" value="" type="text" placeholder="交易序號">
        <input id="div_nm" value="" type="text" placeholder="所選圖形">
        <input id="AddSign" value="" type="text" placeholder="是否新增">
        <input id="Page" value="" type="text" placeholder="頁面">
        <input id="OpenSNM_Modal" value="" type="text">
        <input id="sfm" value="<?php echo $sfm ?>" type="text">
        <!-- <input id="CancelNum"   value="" type="text"   placeholder="刪除的編號">-->
        <img src="../../img/BedSore.jpg" style="display: none">
    </div>

    <div class="row">
        <div class="col-12">
            <h1><?php echo $Title_NM ?></h1>
        </div>

        <div class="col-12">

            <span class="title">
                <button type="button" id="SubmitBtn" class="btn btn-primary btn-md">儲存</button>
                <button type="button" id="SearchBtn" class="btn btn-primary btn-md">查詢</button>
                <button type="button" id="DELBtn" class="btn btn-primary btn-md">作廢</button>
                <button type="button" id="ReSetBtn" class="btn btn-primary btn-md">清除</button>
                <button type="button" id="sbed" class="btn btn-warning btn-md">責任床位</button>
            </span>

            <button type="button" class="btn btn-secondary btn-md" disabled style="display: none">回主畫面</button>

            <span style="margin-left: 1px">
                <b>使用者:<?php echo $sUr ?></b>
            </span>

        </div>

        <div class="col-12">
            <input id="DataTxt" value="" class="form-control" type="text" disabled>
        </div>


        <div class="Otimer  col-lg-11">
            <div class="row">
                <div class="input-group col-12">
                    <label for="sDate">評估時間:</label>
                    <input type="text" id="sDate" value="" class="DateTime form-control form-control-lg"
                           placeholder="YYYMMDD" maxlength="7">
                    <input type="text" id="sTime" value="" class="DateTime form-control form-control-lg"
                           placeholder="HHMM" maxlength="4">
                </div>


                <div class="input-group col-12" id="ISTM">

                </div>


            </div>

        </div>


        <div class="Page col-12">
            <button type="button" id="B" class="btn btn-primary btn-lg">評估資料</button>
            <button type="button" id="A" class="btn btn-primary btn-lg">部位圖</button>
        </div>


        <div class="Main col-12">
            <div class="row">
                <div class="CanvasRow col-lg-6 col-md-12 col-sm-12">
                    <div class="drop-area">
                        <canvas id="CanvasPad" width="395" height="425"></canvas>
                    </div>
                </div>

                <div class="SignRow col-lg-5  col-md-12 col-sm-12 ">

                    <div class="col-12">
                        <div class="input-group input-group-sm mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="inputGroup-sizing-sm">選取編號</span>
                            </div>
                            <input type="text" id="NO_NUM" class="form-control" aria-label="Small"
                                   aria-describedby="inputGroup-sizing-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="inputGroup-sizing-sm">部位</span>
                            </div>
                            <input type="text" id="NO_REG" class="form-control" aria-label="Small"
                                   aria-describedby="inputGroup-sizing-sm">
                        </div>
                        <button class="sign btn btn-outline-info" value="0">新增</button>
                        <button class="sign btn btn-outline-info" value="1">刪除</button>
                        <button class="sign btn btn-outline-info" value="2">+</button>
                        <button class="sign btn btn-outline-info" value="3">-</button>
                    </div>
                    <div class="TPUT_div col-12 mt-1">
                        <button class="DrawOut_btn btn btn-primary">換管</button>
                        <button class="DrawOut_btn btn btn-primary">拔管</button>
                        <button class="DrawOut_btn btn btn-primary">作廢</button>
                    </div>
                    <div class="col-12">
                        <div class="EDIT row">
                            <div class="txtArea col-12">

                            </div>

                            <div class="txtInput col-12 input-group">

                            </div>

                            <div class="MM_A col-12 MMDIV">

                            </div>
                        </div>
                    </div>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>


        <div class="B area-table">

        </div>
        <div class="MM_B col-12 MMDIV">
        </div>


    </div>
    <!-- Modal -->
    <div class="modal fade" id="DelModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>作廢提醒</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p></p>
                </div>
                <div class="modal-footer">
                    <button type="button" id="DelConfirm_Btn" class="btn btn-primary">確定</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="CancelModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>刪除提醒</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p></p>
                </div>
                <div class="modal-footer">
                    <button type="button" id="CancelBtn" class="btn btn-primary">確定</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="DrawOutModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="container-fluid">

                    </div>
                </div>
                <div class="modal-footer">
                    <button id="" type="button" class="DrawOut_Confirm_btn btn btn-primary">確定</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="sNMTUBEModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4>管路一覽</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="container-fluid">
                        <div id="sNM_Tab" class="row">

                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
                </div>
            </div>
        </div>
    </div>

</div>


</body>
</html>
