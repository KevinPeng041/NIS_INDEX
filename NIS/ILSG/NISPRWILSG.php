<?php
header("Content-Type:text/html; charset=utf-8");
date_default_timezone_set('Asia/Taipei');
set_time_limit(0);
include '../../NISPWSIFSCR.php';
$str=$_GET['str'];
$replaceSpace=str_replace(' ','+',$str);//空白先替換+
parse_str(AESDeCode($replaceSpace),$output);

$Account=$output['sIdUser'];/*帳號*/
$passwd=$output['passwd'];/*密碼*/
$sUr=$output['user'];/*使用者*/
$From=$output['From'];/*L:登入介面,U:URL操作*/

$HOST_IP=$_SERVER['HTTP_HOST'];
$Account="00FUZZY";

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>血糖胰島素</title>
    <script type="text/javascript" src="../../jquery-3.4.1.js"></script>
    <link rel="stylesheet" href="../../bootstrap-4.3.1-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../css/NIS/ILSG.css">
    <script src="../../bootstrap-4.3.1-dist/js/bootstrap.min.js" type="text/javascript"></script>
    <script src="../../crypto-js.js"></script>
    <script src="../../AESCrypto.js"></script>
    <script src="../../NISCOMMAPI.js"></script>
    <script>
        $(document).ready(function () {
            (function () {
                NISPWSFMINI_Timer('ILSGA','A');
                $("button[name=PageBtn],button[name=FucBtn]:not(#sbed)").prop('disabled',true);
            })();
            let x;
            let y;

            let PageJson=new Map();
            $(document).on('change','input[name=sRdoDateTime]',function () {
                let TimeNow=new Date();
                let yyyy=TimeNow.toLocaleDateString().slice(0,4);
                let MM=(TimeNow.getMonth()+1<10?'0':'')+(TimeNow.getMonth()+1);
                let dd=(TimeNow.getDate()<10?'0':'')+TimeNow.getDate();
                let  h=(TimeNow.getHours()<10?'0':'')+TimeNow.getHours();
                let  m=(TimeNow.getMinutes()<10?'0':'')+TimeNow.getMinutes();
                let Timetxt=($(this).val()).split("");

                let timer=Timetxt.filter(function (value) { return  value!==":"});
                let timerVal=$(this).attr('id')==="ISTM00000005"?h+m:timer.join("");
                let time_ID=$(this).attr('id');
                $("#IDTM").val(time_ID);
                $("#timer").val(yyyy-1911+MM+dd);
                $("#timetxt").val(timerVal);

                let Page=$("#PageVal").val();
                if (Page==="A"|| Page==="B"  ||  Page==="C"){

                  if (PageJson.has(Page)) {

                    let Page_obj=PageJson.get(Page);
                     $.each(Page_obj,function (index,val) {
                          val.IDTM=time_ID;
                      });

                  }
               }


            });
            $(document).on("click","button",function (e) {
                e.preventDefault();//prevent enter to submit
                let BtnID=$(this).attr("id");
                let sTraID=$("#sTraID").val();
                let Page=$("#PageVal").val();
                let ClassName=$(this).attr('class');
                let sDt=$("#timer").val(),sTM=$("#timetxt").val();
                let IdPt=$('#DA_idpt').val(),IdInPt=$('#DA_idinpt').val();
                const Page_Arr=['A','B','C','D'];
                if (ClassName==="FUQEN btn btn-primary btn-lg"){
                    let B_JSON=PageJson.get('B');
                    let F_index= $("#F_INDEX").val();
                    let Frequency=$(this).val();
                    $("#fUSEF_"+F_index).val(Frequency);
                    $('#FuModal').modal('hide');

                    B_JSON[F_index].USEF=Frequency;
                }else if (ClassName==="MED btn btn-primary btn-lg"){
                   let B_JSON=PageJson.get('B');
                   let Btn_NUM=$("#B_INDEX").val();
                   let Meditem_Num=$(this).val();

                   let MedNM=$("#MED_NM_"+Meditem_Num).text();
                   let MedID=$("#DIA"+Meditem_Num).val();

                   $('#Isu_'+Btn_NUM).val(MedNM);

                   B_JSON[Btn_NUM].STM=MedNM;
                   B_JSON[Btn_NUM].ID=MedID;
                   B_JSON[Btn_NUM].IDTM=$("#IDTM").val();
                }

                switch (BtnID) {
                    case "sbed":
                        switch (checkBEDwindow()) {
                            case "false":
                                alert("責任床位視窗已開啟");
                                break;
                            case "true":
                                x=window.open("/webservice/NISPWSLKCBD.php?str="+AESEnCode("sFm=ILSGA&sIdUser=<?php echo $Account?>"),"責任床位(血)",'width=850px,height=650px,scrollbars=yes,resizable=no');
                                break;
                        }
                        x.bedcallback=bedcallback;
                        break;
                    case "Serch":

                        if(($("#DataTxt").val()).trim()=='')
                        {
                            alert("請選擇須查詢的病人");
                            return false;
                        }
                        switch (checkSerchwindow()) {
                            case "false":
                                alert("查詢視窗已開啟");
                                return false;
                                break;
                            case "true":
                                y=window.open("/webservice/NISPWSLKQRY.php?str="+
                                    AESEnCode("sFm=ILSGA&PageVal="+Page+"&DA_idpt="+
                                        IdPt+"&DA_idinpt="+IdInPt+
                                        "&sUser="+"<?php echo $Account?>"+"&NM_PATIENT="+$('#DataTxt').val())
                                    ,"ILSGA",'width=750px,height=650px,scrollbars=yes,resizable=no');

                                break;
                        }

                        y.Serchcallback=Serchcallback;
                        break;
                    case "Del":
                        DELILSG(sTraID,Page,'<?php echo $Account?>');
                        break;
                    case "ReSet":


                        //頁面按鈕顏色重製
                        $("button[name=PageBtn]").css({'background-color' : '', 'opacity' : '','color':'white' });


                        $("input[type=text]:not(#B_INDEX,#sUser)").val("");
                        $("#Text_A").val("");
                        //頁面隱藏
                        Page_Arr.forEach((value)=>$("#P_"+value).hide());

                        //Radio,Checkbox 重製
                        $("input[type=radio],input[type=checkbox]").prop({'disabled':false,"checked":false});
                        $("button[name=FucBtn]:not(#sbed),button[name=PageBtn]").prop({'disabled':true});
                        PageJson.forEach(function (value, key) {
                            if (key==="A"){
                                $.each(value,function (index,val) {
                                    val.IDGP="";
                                    val.IDTM="";
                                    val.MMVAL="";
                                    val.SFRMSEQ="";
                                    val.SPRESS="";
                                    val.STVAL="";
                                });
                            }
                           else if (key==="B"){
                                $.each(value,function (index,val) {
                                    val.ID="";
                                    val.IDTM="";
                                    val.ITNO="";
                                    val.SDOSE="";
                                    val.SFRMDTSEQ="";
                                    val.STM="";
                                    val.USEF="";
                                });

                            }else if (key==="C"){
                                $.each(value,function (index,val) {
                                    val.REGION.length=0;
                                    val.NO_MMVAL="";
                                });
                            }
                        });

                        break;
                    case "FuConfirm":
                        let UserConfirm_Val=$("#Fuval").val();
                        let F_INDEX=$("#F_INDEX").val();
                        let B_JSON=PageJson.get('B');

                        $("#fUSEF_"+F_INDEX).val(UserConfirm_Val);
                        B_JSON[F_INDEX].USEF=UserConfirm_Val;
                        break;
                    case "SubmitBtn":

                        let json_str=JSON.stringify(PageJson.get(Page));
                        let passwd='<?php echo $passwd?>';
                        let freq=$("#FORMSEQANCE").val();

                        let error_arr=Page_Arr.filter(value => value!=="D")
                            .map(value => CheckQty(value))
                            .filter(value => value!=="");

                        if(error_arr.length>0){
                            alert(error_arr.join(','));
                            return  false;
                        }

                        $("#wrapper").show();
                        DB_WSST(Page,sTraID,json_str,sDt,sTM,passwd,freq,'<?php echo $Account?>','true');
                        break;
                    default:
                        break;
                }
            });

            $("button[name='PageBtn']").click(function () {
                let Page=$(this).val();
                let sTraID=$("#sTraID").val();
                const Page_Arr=['A','B','C','D'];


                $.each(Page_Arr,function (index,val) {
                    $("#P_"+val).hide();
                    $("#PBTN_"+val).css({ 'background-color' : '', 'opacity' : '' ,'color':''});
                });

                if (!PageJson.has(Page)){
                    //取一次頁面JSON
                     GetPageJson(Page,sTraID);
                }



                let WSST_arr=Page_Arr.filter(value => value!==Page);
                WSST_arr.forEach(function (value) {
                    if(PageJson.has(value) && value !=="D"){
                        DB_WSST(value,sTraID,JSON.stringify(PageJson.get(value)),'','','','','','false');
                    }
                });


                if (Page==="B"){
                    $("#PBTN_C,#PBTN_D").prop('disabled',false);
                }else  if (Page==="D")
               {
                    let B_Part=$("input[name=part]:checked").val();
                    let Forbid_part=PageJson.get('CP');
                    let Last_Part=PageJson.get('L_PT');
                    NISPWSCILREG(sTraID,B_Part,Forbid_part,Last_Part);
                }

                $("#Part").prop('disabled',false);
                $("#P_"+Page).show();
                $("#PageVal").val(Page);
                $(this).css({ 'background-color' : '#EEEE00', 'opacity' : '' ,'color':'black'});
            });


            /*************************Page A Event *********************************************/
            $("input[name=IDGP]").on('change',function () {
                let A_JSON=PageJson.get('A')[0];
                A_JSON.IDGP=$(this).val();
            });

            $('#STVALval').on("change",function(){
                let A_JSON=PageJson.get('A')[0];
                $("input[name='sPressure']").prop('checked',false);
                A_JSON.STVAL=$(this).val();
                if ($(this).val()!==""){
                    A_JSON.SPRESS="";
                }

                A_JSON.IDTM=$("#IDTM").val();
            });

            $("input[name='sPressure']").change(function () {
                let A_JSON=PageJson.get('A')[0];

                $("#STVALval").val("");
                A_JSON.SPRESS=$(this).val();
                A_JSON.STVAL="";
                A_JSON.IDTM=$("#IDTM").val();
            });

            $("#Text_A").on("change",function () {
                let A_JSON=PageJson.get('A')[0];
                A_JSON.MMVAL=$(this).val().match(/&/) != null ? $(this).val().replace(/&/g, '＆') : $(this).val();
            });


            /*************************Page B Event********************************************/

            $("input[name=part]").on('change',function (e) {
                let A_JSON=PageJson.get('B');

                $.each(A_JSON,function (index,val) {
                    val.IDGP=$(e.target).val();
                });
            });

            $('button[name=ISLNch]').click(function () {
                /*胰島素藥品*/
                let index=$(this).val();
                $("#B_INDEX").val(index);
            });

            $("input[name=QTY]").on('change',function () {
                let index=$(this).attr('id').substr(-1,1);
                let QTY=$(this).val();
                PageJson.get('B')[index].SDOSE=QTY;
                PageJson.get('B')[index].IDTM=$("#IDTM").val();
            });

            $("button[name=ClearInput]").click(function () {
               let index=$(this).attr('id').substr(-1,1);
               let JSON_B=PageJson.get('B')[index];
                $("#Isu_"+index).val("");
                $("#QTY_"+index).val("");
                $("#fUSEF_"+index).val("");

                JSON_B.STM="";     //藥名
                JSON_B.ID="";      //藥名ID
                JSON_B.SDOSE="";   //劑量
                JSON_B.USEF="";    //頻率
            });

            $(".FuQuenCy").on("focus",function () {
                let TxtID=$(this).attr("id");
                let index=TxtID.split("");
                $('#FuModal').modal('show');
                $("#Fuval").val("");
                $("#F_INDEX").val(index[6]);

            });
            /*************************Page C Event********************************************/

            $("input[type=checkbox]").on("change",function () {

                let REGION_Arr=PageJson.get('C')[0].REGION;
                let REGION_Part=$(this).val();
                if ($(this).prop('checked')){

                   if (REGION_Arr.indexOf(REGION_Part)<0)
                   {
                       REGION_Arr.push(REGION_Part);
                   }

                }else {
                    REGION_Arr.forEach(function (value, index,array) {
                       if (value===REGION_Part){
                           array.splice(index,1);
                       }
                    });
                }
            });

            $(document).on('change','input[name=NO_MMVAL]',function () {
                PageJson.get('C')[0].NO_MMVAL=$(this).val();
                PageJson.get('C')[0].IDTM=$("#IDTM").val();
            });




            /***************************Function***************************************************/

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
            function bedcallback(data){
                let str=AESDeCode(data);
                let datastr=JSON.parse(JSON.stringify(str).replace(/\u0000/g, '').replace(/\\u0000/g, ""));
                let dataObj=JSON.parse(datastr);
               if(dataObj){

                   const Page_Arr=['A','B','C','D'];

                    //頁面隱藏
                   Page_Arr.forEach((value)=>$("#P_"+value).hide());

                   //頁面按鈕顏色重製
                   $("button[name=PageBtn]").css({'background-color' : '', 'opacity' : '','color':'white' });

                   //Radio,Checkbox 重製
                   $("input[type=radio],input[type=checkbox]").prop({'disabled':false,"checked":false});
                   $("input[type=text]:not(#B_INDEX,#F_INDEX,#sUser)").val("");
                   $("#Text_A").val("");
                   $("button[name=FucBtn]").prop('disabled',false);

                    $("#DataTxt").val(dataObj[0].DataTxt);
                    $("#DA_idpt").val(dataObj[0].IDPT);
                    $("#DA_idinpt").val(dataObj[0].IDINPT);
                    $("#DA_sBed").val(dataObj[0].SBED);

                    DeFaultINI(dataObj[0].IDPT,dataObj[0].IDINPT);
                    PageJson.clear();
                    $("button[name=PageBtn]:not(#PBTN_C,#PBTN_D)").prop('disabled',false);
                    $("#timer").prop("readOnly",false);
                    $("#timetxt").prop("readOnly",false);
                    $("#PageVal").val('A'); //頁面預設第一頁
                }
            }
            function Serchcallback(AESdata){
                let page=$("#PageVal").val();
                let Json_obj=JSON.parse(AESDeCode(AESdata));
                $("#timer,#timetxt").prop('readonly',true);
                $("#input[name=sRdoDateTime]").prop('disabled',true);

                let sTraID=Json_obj.ID_TRANSACTION;
                let IdPt=Json_obj.ID_PATIENT;
                let IdInPt=Json_obj.ID_INPATIENT;
                let sDt=Json_obj.DT_EXCUTE;
                let sTm=Json_obj.TM_EXCUTE;
                let Data=Json_obj.DATA;

                if(IdPt!=$("#DA_idpt").val())
                {
                   alert("病人資訊已異動,請先重新操作一次");
                    return false;
                }


                $("#DA_idpt").val(IdPt);
                $("#DA_idinpt").val(IdInPt);
                $("#sTraID").val(sTraID);
                $("#timer").val(sDt);
                $("#timetxt").val(sTm);

                switch (page) {
                    case "A":
                        //IDGP: "A"
                        //IDTM: "ISTM00000005"
                        //MMVAL: " "
                        //SFRMSEQ: "ISSG0900009592"
                        //SPRESS: "LO"
                        //STVAL: " "
                        //idFrm: "ISSG"


                        $("#PBTN_B,#PBTN_C,#PBTN_D").prop('disabled',true);
                        $.each(Data,function (index,val) {
                            let Befor_or_After=val.IDGP;
                            let TimeRadioBtn_ID=val.IDTM;
                            let A_SPRESS=(val.SPRESS).trim();
                            let A_Qty=(val.STVAL).trim();
                            let A_MMVAL=val.MMVAL;
                            let FORMSEQANCE=val.SFRMSEQ;

                            $("#IDGP_"+Befor_or_After).prop('checked',true);
                            $("#"+TimeRadioBtn_ID).prop('checked',true);
                            $("#IDTM").val(TimeRadioBtn_ID);
                            $("#FORMSEQANCE").val(FORMSEQANCE);
                            $("#Text_A").val(A_MMVAL);

                            if (A_SPRESS!==""){
                                $("#P_"+A_SPRESS).prop('checked',true);
                            }else {
                                $("#STVALval").val(A_Qty);
                            }


                        });
                        break;
                    case "B":
                    //DBDOSE: "-1"
                    //FORBID: ""
                    //ID: "D5/NS"
                    //IDGP: "C"
                    //IDTM: "ISTM00000005"
                    //ITNO: "1"
                    //LSTPT: ""
                    //SDOSE: "10"
                    //SFRMDTSEQ: "1100318100100"
                    //STM: "04Dextrose+NaCl0.33%500ml/bag"
                    //USEF: "TID"
                    //idFrm: "ISLN"



                    $("#PBTN_A,#PBTN_C").prop('disabled',true);
                    $("input[name=part]").prop({"checked":false,"disabled":true});
                    let Has_Qty_obj=Data.filter((value)=>(value.SDOSE).trim()!=="");

                    $.each(Has_Qty_obj,function (index,val) {
                                let MED_NM=val.STM;
                                let Frequency=val.USEF;
                                let B_Qty=val.SDOSE;
                                let FORMSEQANCE=val.SFRMSEQ;
                                let TimeRadioBtn_ID=val.IDTM;
                                let Part_IDGP=val.IDGP;

                             $("#"+TimeRadioBtn_ID).prop('checked',true);
                             $("#IDTM").val(TimeRadioBtn_ID);
                             $("#FORMSEQANCE").val(FORMSEQANCE);
                             $("#Isu_"+index).val(MED_NM);
                             $("#QTY_"+index).val(B_Qty);
                             $("#fUSEF_"+index).val(Frequency);
                             $("#Part"+Part_IDGP).prop('checked',true);
                             $("#LastPart").val('');
                            });

                        
                    break;
                    case "C":
                    //DATA: Array(1)
                    //0: {REGION: Array(2), NO_MMVAL: "ISLF00000002"}
                    //DT_EXCUTE: "1100318"
                    //ID_INPATIENT: "970000884"
                    //ID_PATIENT: "00055664"
                    //ID_TRANSACTION: "20210318142501658ILSGA01295365"
                    //SFRMDTSEQ: "1100318115100"
                    //TM_EXCUTE: "1151"



                        $("#PBTN_B,#PBTN_A").prop('disabled',true);
                        $("input[type=checkbox]").prop({'checked':false,"disabled":true});
                        $.each(Data[0].REGION,function (index,val) {
                            $("#No_"+val).prop({"checked":true});
                        });

                       $("#"+Data[0].NO_MMVAL).prop('checked',true);
                        $("#"+Json_obj.IDTM).prop('checked',true);
                        $("#FORMSEQANCE").val(Json_obj.SFRMDTSEQ);
                        break;
                }

                PageJson.clear();
                PageJson.set(page,Data);
                $("#P_"+page).show();
                $("input[name=sRdoDateTime]").prop('disabled',true);
                $("#DELMENU").prop('disabled',false);
            }
            function checkSerchwindow() {
                if(!y){
                    return "true";
                }else {
                    if(y.closed){
                        return "true";
                    }else {
                        return "false";
                    }
                }
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
            function DeFaultINI(idpt,Inidpt){
                $.ajax({
                    url:'/webservice/NISPWSTRAINI.php'+'?str='+AESEnCode('sFm='+'ILSGA'+'&idPt='+idpt+'&INPt='+Inidpt+'&sUr=<?php echo $Account?>'),
                    type:"POST",
                    dataType:"text",
                    success:function (data) {
                        let json=JSON.parse(AESDeCode(data));
                        let sTraID=json.sTraID;
                        let Save=json.sSave;
                        let FORMSEQANCE_WT=json.FORMSEQANCE_WT;
                        let JID_NSRANK=json.JID_NSRANK;

                        $("#sTraID").val(sTraID);
                        $("#sSave").val(Save);
                        $("#STDATA_JID_NSRANK").val(JID_NSRANK);
                        $("#STDATA_FORMWT").val(FORMSEQANCE_WT);
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
            function GetPageJson(Page,sTraID) {
                if (Page==="D"){
                    return false;
                }
                $.ajax({
                    url:"/webservice/NISPWSGETPRE.php?str="+AESEnCode("sFm=ILSGA&sTraID="+sTraID+"&sPg="+Page),
                    type:'POST',
                    async: false,
                    dataType:"text",
                    success:function (data){


                        let JSON_Data= JSON.parse(AESDeCode(data));
                        let Json_obj=JSON.parse(JSON_Data.ST_DATA);


                        if (!PageJson.has(Page)){
                            PageJson.set(Page,Json_obj);//各頁面Json
                        }


                        //page UI Append
                         if (Page==="B"){


                            let FUQEN_str=JSON_Data.ORDER;
                            let Frequen_str=JSON.parse(FUQEN_str)[0].FUSEQ;
                            let Frequen_arr=JSON.parse(Frequen_str);            //施打胰島素頻率


                            let ISUL_str=JSON_Data.ORDER;
                            let ISULING_str=JSON.parse(ISUL_str)[0].ISULING;
                            let ISULING_arr=JSON.parse(ISULING_str);             //施打胰島素藥物

                            let ForBid_Arr=Json_obj[0].FORBID;                 //禁打部位:Array

                            if (!PageJson.has('BF')){
                                /*施打頻率UI*/
                                $("#fu1").children().remove();
                                $("#fu2").children().remove();

                                $.each(Frequen_arr,function (index,val) {
                                    if (index%2 !== 0){
                                        $("#fu1").append(
                                            `
                                              <button   class="FUQEN btn btn-primary btn-lg" value='${val.FUQUEN}' style="width:inherit;margin-top: 3px">${val.FUQUEN}</button>
                                             `
                                        );
                                    }else {
                                        $("#fu2").append(
                                            `
                                            <button    class="FUQEN btn btn-primary btn-lg" value='${val.FUQUEN}' style="width:inherit ;margin-top: 3px">${val.FUQUEN}</button>
                                           `
                                        );
                                    }
                                });


                                PageJson.set('BF',Frequen_arr);
                            }
                            if (!PageJson.has('BI')){
                                /*藥物UI*/
                                $.each(ISULING_arr,function (index,val) {
                                    let JID_KEY = val.JID_KEY;
                                    let DIA = val.DIA;
                                    let STM =(val.STM).replace('§0§','');
                                    let  DCSORT = val.DCSORT;
                                    let QTY = val.QTY;
                                    let USEF = val.USENO;
                                    let QTY_tt=QTY!=''?"劑量:":"";
                                    let USEF_tt=USEF!=''?"頻率:":"";

                                    $("#MedLi").append(
                                        `
                                    <li id='${"MEDli"+index}' style="list-style-type: none;font-size:3vmin">
                                    <button type='button' class="MED btn btn-primary btn-lg" style="margin-left: -10px;margin-top: 5px" value="${index}"  data-dismiss="modal"  aria-hidden="true" >
                                        ${"選擇"}
                                    </button>
                                    <label id='${"MED_NM_"+index}'> ${STM}</label>
                                    <li style='padding-left: 42px;font-size:2.5vmin'>${QTY_tt+QTY+USEF_tt+USEF}</li>
                                    <div style="display: none;">
                                        <input type='text' value='${DIA}'  id='${"DIA"+index}' >
                                        <input type='text' value='${QTY}'  id='${"QTY"+index}' >
                                        <input type='text' value='${USEF}' id='${"sUSEF"+index}' >

                                    </div>

                                    </li>
                                    `
                                    );
                                });


                                PageJson.set('BI',ISULING_arr);
                            }

                            $("#LastPart").val(Json_obj[0].LSTPT);
                            $("#Part"+ Json_obj[0].IDGP).prop('checked',true);


                            $.each(ForBid_Arr,function (index,val) {
                                $("#Part"+val.REGION).prop('disabled',true); //PAGE B
                                $("#No_"+val.REGION).prop( {'disabled':true,'checked':true}); //PAGE C
                            });
                            PageJson.set('L_PT',Json_obj[0].LSTPT);
                            PageJson.set('CP',ForBid_Arr);//禁打部位
                        }
                         else if (Page==="C"){
                          Append_RadioBtn_forbid(Json_obj[0].NO_MMVAL);
                          PageJson.get('C')[0].NO_MMVAL="";
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
                 return true;
            }
            function DB_WSST(Page,sTraID,json,sDt=null,sTm=null,Passed=null,Freq=null,sUr,InSertDB){

                $.ajax('/webservice/NISPWSSETDATA.php?str='+AESEnCode(
                    'sFm=ILSGA&sTraID='+sTraID+'&sPg='+Page+'&sData='+json+
                    '&sDt='+sDt+'&sTm='+sTm+'&Fseq='+Freq+'&PASSWD='+Passed+
                    '&USER='+sUr+'&Indb='+InSertDB)
                )
                    .done(function (data) {
                        let json=JSON.parse(AESDeCode(data));

                        if(InSertDB==="true" && json.result==="true"){
                            alert('存檔成功');
                            window.location.replace(window.location.href);
                        }
                        if(InSertDB==="true" && json.result!=="true"){
                            alert("儲存失敗,錯誤訊息:"+json.message);
                            $("#wrapper").hide();
                        }
                    }).fail(function (XMLHttpResponse,textStatus,errorThrown) {
                    console.log(
                        "1 返回失敗,XMLHttpResponse.readyState:"+XMLHttpResponse.readyState+XMLHttpResponse.responseText+
                        "2 返回失敗,XMLHttpResponse.status:"+XMLHttpResponse.status+
                        "3 返回失敗,textStatus:"+textStatus+
                        "4 返回失敗,errorThrown:"+errorThrown
                    );
                });

            }
            function DELILSG(sTraID,Page,sUr) {
                $.ajax({
                    url:"/webservice/NISPWSDELILSG.php?str="+AESEnCode("sFm="+'ILSGA'+"&sTraID="+sTraID+"&sPg="+Page+"&sCidFlag=D"+"&sUr="+sUr),
                    type:'POST',
                    dataType:'text',
                    success:function (json) {
                        let data=JSON.parse(AESDeCode(json));
                        if(data.result==='false'){
                            alert('作廢失敗');
                            console.log(data.message);
                            return false;
                        }else {
                            $('#DELModal').modal('hide');
                            DeFaultINI($("#DA_idpt").val(),$("#DA_idinpt").val());

                            $("button[name=PageBtn]").css({'background-color' : '', 'opacity' : '','color':'white' });
                            $("input[type=radio]").prop({'checked':false,'disabled':false});
                            $("input[type=checkbox]").prop({'checked':false,'disabled':false});
                            $('#timer,#timetxt').prop('readonly',false);

                            $("#DELMENU,#PBTN_D").prop("disabled", true);
                            $('#SubmitBtn,#PBTN_A,#PBTN_B,#PBTN_C').prop('disabled',false);
                            $("input[type=text]:not(#sTraID,#sUser,#DataTxt,#DA_idpt,#DA_idinpt,#DA_sBed)").val("");
                            $("#PageVal").val("A");
                            $('#Text_A').val("");
                            $(".Page").hide();

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
            }
            function Append_RadioBtn_forbid(arr){
                if($("#NOisuling_RE").children().length==0){
                    $.each(arr,function (index,item) {
                        $("#NOisuling_RE").append(
                            `
                            <label  style='font-size: 4.5vmin'><input type='radio' name='NO_MMVAL' id='${item.F_ID}' value='${item.F_ID}' style='width: 6vmin;height: 6vmin' >
                                 ${item.name}
                            </label>
                            `
                        );
                    });
                }
            }
            function NISPWSCILREG(sTraID,Part,FORBID_Arr,LAST_PART) {
                $("td").css({'backgroundColor':'white','color':'black'});

                $.each(FORBID_Arr,function (index,val) {
                    for (let i=1;i<=8;i++) {
                        $("#"+val.REGION+i).css({'backgroundColor':'red','color':'white'});
                    }
                });

                if(LAST_PART){
                    $("#"+LAST_PART).css({'backgroundColor':'blue','color':'white'});
                }

                $.ajax({
                    url:"/webservice/NISPWSCILREG.php?str="+AESEnCode("sFm=ILSGA&sTraID="+sTraID+"&sRgn="+Part),
                    type:'POST',
                    dataType:'text',
                    success:function (json) {
                        let data=JSON.parse(AESDeCode(json));
                        let IDGP_num=$("input[name=part]:checked").val();

                        $("#"+IDGP_num+data).css({'backgroundColor':'green','color':'white'});
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

            function CheckQty(Page) {
               let str="";
               let arr=PageJson.get(Page);

                if (Page==="A"){

                    $.each(arr,function (index,val) {

                       let A_QTY=(val.STVAL).trim()?(val.STVAL).trim():val.SPRESS;
                       let A_IDGP=val.IDGP;

                       if (A_QTY==="" && A_IDGP!==""){
                           str="請檢查血糖值";
                       }
                       if (A_QTY!=="" && A_IDGP===""){
                           str="請選擇飯前後";
                       }
                    });
                }
                else if(Page==="B"){
                    $.each(arr,function (index,val) {
                        let Med_Nm=(val.STM).trim();
                        let Med_Qty=(val.SDOSE).trim();
                        let Med_FQty=(val.USEF).trim();

                        if(Med_Nm!=="" && (Med_Qty==="" || Med_FQty==="")){
                            str="請檢查劑量或頻率";
                        }

                    });

                }
                else {
                    $.each(arr,function (index,val) {
                        let Part=val.REGION;
                        let MMVAL=val.NO_MMVAL;

                        if(Part.length>0 && MMVAL===""){
                            str="請選擇禁打原因";
                        }
                        if(Part.length==0 && MMVAL!==""){
                            str="請選擇禁打部位";
                        }
                    });
                }
                return str;
            }
        });

    </script>

</head>
<body>

<!--<div id="wrapper"></div>
<div id="loading" >請稍後<img class="loadimg" src="../../dotloading.gif"></div>-->
<div class="Parametertable">
    <input id="B_INDEX" value="0"  type="text"  placeholder="B_INDEX"> <!--藥名切換-->
    <input id="F_INDEX" value="0"  type="text"  placeholder="F_INDEX"> <!--頻率切換-->
    <input id="DA_idpt" value="" type="text" name="DA_idpt"   placeholder="DA_idpt"> <!--病歷號-->
    <input id="DA_idinpt" value="" type="text" name="DA_idinpt"  placeholder="DA_idinpt"><!--住院號-->
    <input id="DA_sBed" value="" type="text" name="DA_sBed" placeholder="DA_sBed"><!--床號-->
    <input id="FORMSEQANCE" type="text" value="" placeholder="FORMSEQANCE">
    <input id="DT_EXE" type="text" value="" placeholder="DT_EXE">
    <input id="TM_EXE" type="text" value="" placeholder="TM_EXE">
    <input id="PageVal" type="text" value="" placeholder="PageVal">
    <input id="sSave" value="" type="text" placeholder="sSave">      <!--存檔權限-->
    <input id="sUser" type="text" value="<?php echo $Account?>" placeholder="sUser">
    <input id="sPress" type="text" value="" placeholder="sPress">
    <input id="STDATA_FORMWT" type="text" value="" placeholder="STDATA_FORMWT">
    <input id="STDATA_JID_NSRANK" type="text" value="" placeholder="STDATA_JID_NSRANK">
    <input id="STDATB_idFrm" type="text" value="" placeholder="STDATB_idFrm">
    <input id="sTraID" value="" type="text" placeholder="sTraID"> <!--交易序號-->
    <input id="SER_DT" value="" type="text" placeholder="SER_DT">
    <input id="SER_TM" value="" type="text" placeholder="SER_TM">
    <input id="ERRORVAL" value="" type="text" placeholder="ERRORVAL">
    <input id="SERCH_Click" value="1" type="text" placeholder="SERCH_Click">
    <input type="text" name="sIDTM" id="IDTM" value=""  placeholder="IDTM">
</div>
<div class="container">
    <h1>血糖胰島素注射</h1>
    <form id="form1" >
    <span style="margin-left:0 px">
        <button type="button" class="btn btn-secondary btn-md" name="FucBtn" disabled style="display: none">回主畫面</button>
        <button type="button"  class="btn btn-warning btn-md" name="FucBtn" style="margin-left: 1px"   id="sbed" >責任床位</button>
        <span style="margin-left: 1px"><b>使用者:<?php echo $sUr?></b></span>
    </span>

        <span class="float-left">
            <button type="button" id="SubmitBtn" class="btn btn-primary btn-md" name="FucBtn" >儲存</button>
            <button type="button" id="Serch" class="btn btn-primary btn-md" name="FucBtn">查詢</button>
            <button type="button" id="DELMENU" class="btn btn-primary btn-md" name="FucBtn"  data-toggle="modal" data-target="#DELModal">作廢</button>
            <button type="button" id="ReSet" class="btn btn-primary btn-md"  name="FucBtn" >清除</button>


            <button type="button" class="btn btn-secondary btn-md" disabled style="margin-right: 3px ;display: none">預設</button>
        </span>

        <table class="table" style="font-size:3.5vmin">
            <thead>
            <thead>
            </thead>
        </table>
        <input id="DataTxt" value="" class="form-control" type="text" readonly="readonly">

        <div class="Otimer" >
            <div class="pageTime">
                <label style="font-size: 4vmin">評估時間:</label>
                <input  type="text" id="timer" value="" name="sDate" placeholder="YYYMMDD" maxlength="7" autocomplete="off">
                <input type="text" id="timetxt" value="" name="sTime" placeholder="HHMM" maxlength="4" autocomplete="off">

            </div>
            <div id="ISTM"></div>
        </div>

        <div class="Features">
            <button type="button" class="btn btn-primary " name="PageBtn"  id="PBTN_A" value="A">血糖</button>
            <button type="button" class="btn btn-primary " name="PageBtn"  id="PBTN_B" value="B">胰島素</button>
            <button type="button" class="btn btn-primary "  name="PageBtn" id="PBTN_C" value="C" >禁打</button>
            <button type="button" class="btn btn-primary " name="PageBtn"  id="PBTN_D" value="D">部位</button>
        </div>

        <!--血糖-->
        <div id="P_A" class="Page" style="font-size: 3.5vmin">
            <div id="Eating">
                <div style="background-color: brown;color:white;padding-top: 5px;padding-left: 5px;border-radius:3px;">
                    <label style="margin-right: 5px;font-size: 5vmin"> <input type="radio" value="A" id="IDGP_A" name="IDGP"
                                                                              style="width: 6vmin;height: 6vmin"
                        >飯前</label>
                    <label style="margin-right: 5px;font-size: 5vmin"> <input type="radio" value="B" id="IDGP_B" name="IDGP"
                                                                              style="width: 6vmin;height: 6vmin"
                        >飯後</label>
                </div>
                <div style="background-color: #FFFBCC;padding-top: 10px">

                    <div style="margin-top: 5px;font-size: 4vmin">
                        <label>血糖:<input type="text" style="width: 80px" name="STVAL" id="STVALval" autocomplete="off"></label>
                        <label> <input type="radio" value="LO" id="P_LO" name="sPressure" style="width:5.5vmin;height: 5.5vmin"
                            >LO</label>
                        <label> <input type="radio" value="HI" id="P_HI" name="sPressure" style="width: 5.5vmin;height: 5.5vmin"
                            >HI</label>
                        <label> <input type="radio" value="NONE" id="P_NONE"  name="sPressure" style="width:5.5vmin;height: 5.5vmin"
                            >NONE</label>
                        <label> <input type="radio" value="CE" id="P_CE" name="sPressure" style="width: 5.5vmin;height: 5.5vmin"
                            >CE</label>
                    </div>
                    <div class="form-group shadow-textarea">
                        <textarea class="form-control z-depth-1"  id="Text_A" name="MMVAL" rows="3"
                                  placeholder="備註" autocomplete="off"></textarea>
                    </div>
                </div>
            </div>
        </div>
        <!--胰島素-->
        <div id="P_B" class="Page">
            <div style="background-color: brown;color:white;font-size: 4vmin;border-radius:3px;">
                <label style="display: none;"> <input type="radio" value="1" id="ITNO_btn" name="ITNO"  checked>施打</label>

                <label >上次施打位置:<input type="text"  value="" id="LastPart" readonly="readonly"></label>
            </div>

            <div style="background-color: #edfbff;font-size: 3.5vmin">
                <div style="background-color: #FFFBCC;font-size: 4vmin">
                    <label>注射部位:</label><br>
                    <label><input type="radio" id="PartA" name="part" value="A" style="width: 5vmin;height: 5vmin">A.左臂</label>
                    <label><input type="radio" id="PartB" name="part" value="B" style="width: 5vmin;height: 5vmin">B.左腹</label>
                    <label><input type="radio" id="PartC" name="part" value="C" style="width: 5vmin;height: 5vmin">C.左臀</label>
                    <label><input type="radio" id="PartD" name="part" value="D" style="width: 5vmin;height: 5vmin">D.左腿</label><br>
                    <label><input type="radio" id="PartE" name="part" value="E" style="width: 5vmin;height: 5vmin">E.右腿</label>
                    <label><input type="radio" id="PartF" name="part" value="F" style="width: 5vmin;height: 5vmin">F.右臀</label>
                    <label><input type="radio" id="PartG" name="part" value="G" style="width: 5vmin;height: 5vmin">G.右腹</label>
                    <label><input type="radio" id="PartH" name="part" value="H" style="width: 5vmin;height: 5vmin">H.右臂</label>
                </div>

                <div>

                    <button type="button" name="ISLNch" value="0" class="btn btn-primary btn-md" style="font-size: 3.5vmin" data-toggle="modal"
                            data-target="#isuModal" data-whatever="isubtn1">選擇
                    </button>
                    <label>胰島素:<input type="text" value="" name="MED_inp" id="Isu_0" style="border: 1px white;font-size: 4vmin;width:70vmin" autocomplete="off" readonly="readonly"></label>
                    <div style="margin-top: 5px">
                        <label>劑量:<input type="text" id="QTY_0" name="QTY"  style="width: 70px;margin-right: 3px" autocomplete="off"></label>
                        <label>頻率:<input type="text" style="width: 80px;" id="fUSEF_0" class="FuQuenCy" autocomplete="off" >
                         <button id="CI_0" name="ClearInput" style="color: white;border:0;background-color: #6c757d;border-radius:3px;">清除此欄</button>
                        </label>
                    </div>
                </div>

                <div>
                    <button type="button" name="ISLNch" value="1" class="btn btn-primary btn-md" style="font-size: 3.5vmin" data-toggle="modal"
                            data-target="#isuModal" data-whatever="isubtn2">選擇
                    </button>
                    <label>胰島素:<input type="text" value="" name="MED_inp" id="Isu_1" style="border: 1px white;font-size: 4vmin;width:70vmin" autocomplete="off" readonly="readonly"></label>
                    <div style="margin-top: 5px">
                        <label>劑量:<input type="text" id="QTY_1" name="QTY"  style="width: 70px;margin-right: 3px" autocomplete="off"></label><label>頻率:<input
                                type="text" style="width: 80px;" id="fUSEF_1" class="FuQuenCy" autocomplete="off">
                            <button id="CI_1" name="ClearInput"  type="button" style="color: white;border:0;background-color: #6c757d;border-radius:3px;">清除此欄</button>
                        </label>

                    </div>
                </div>
                <div>
                    <button type="button" name="ISLNch" value="2" class="btn btn-primary btn-md" style="font-size: 3.5vmin" data-toggle="modal"
                            data-target="#isuModal" data-whatever="isubtn3">選擇
                    </button>
                    <label>胰島素:<input type="text" value="" name="MED_inp" id="Isu_2" style="border: 1px white;font-size: 4vmin;width:70vmin" autocomplete="off" readonly="readonly"></label>
                    <div style="margin-top: 5px">
                        <label>劑量:<input type="text" id="QTY_2" name="QTY"  style="width: 70px;margin-right: 3px" autocomplete="off"></label><label>頻率:<input
                                type="text" style="width: 80px;" id="fUSEF_2" class="FuQuenCy" autocomplete="off">
                            <button id="CI_2" name="ClearInput"   type="button"  style="color: white;border:0;background-color: #6c757d;border-radius:3px;">清除此欄</button>
                        </label>
                    </div>
                </div>
            </div>
        </div>
        <!--禁打-->
        <div id="P_C" class="Page" style="font-size: 4vmin;">
            <div style="background-color:#FF0000;">
                <label style="color: white">禁打部位</label>
            </div>
            <div style="background-color: #FFFBCC;border-radius:3px;padding-top: 5px">
                <label><input type="checkbox" id="No_A" name="forbid[]" value="A" style="width: 4.5vmin;height: 4.5vmin" >A.左臂</label>
                <label><input type="checkbox" id="No_B" name="forbid[]" value="B" style="width: 4.5vmin;height: 4.5vmin">B.左腹</label>
                <label><input type="checkbox" id="No_C" name="forbid[]" value="C" style="width: 4.5vmin;height: 4.5vmin">C.左臀</label>
                <label><input type="checkbox" id="No_D" name="forbid[]" value="D" style="width: 4.5vmin;height: 4.5vmin">D.左腿</label><br>
                <label><input type="checkbox" id="No_E" name="forbid[]" value="E" style="width: 4.5vmin;height: 4.5vmin">E.右腿</label>
                <label><input type="checkbox" id="No_F" name="forbid[]" value="F" style="width: 4.5vmin;height: 4.5vmin">F.右臀</label>
                <label><input type="checkbox" id="No_G" name="forbid[]" value="G" style="width: 4.5vmin;height: 4.5vmin">G.右腹</label>
                <label><input type="checkbox" id="No_H" name="forbid[]" value="H" style="width: 4.5vmin;height: 4.5vmin">H.右臂</label>
            </div>
            <div style="background-color:#FF0000;">
                <label style="color: white">禁打原因</label>
            </div>
            <div id="NOisuling_RE" style="background-color: #FFFBCC;border-radius:3px;padding-top: 5px">

            </div>
        </div>
        <!--部位圖-->
        <div id="P_D" class="Page">
            <img src="ISLN800.bmp" style="z-index: -1;">
            <table id="A" border="1">                　
                <tr>                    　
                    <td id="A1">A1</td>                    　
                    <td id="A2">A2</td>                    　
                </tr>
                　
                <tr>
                    　
                    <td id="A3">A3</td>                    　
                    <td id="A4">A4</td>
                    　
                </tr>
                <tr>
                    <td id="A5">A5</td>
                    <td id="A6">A6</td>
                </tr>
                <tr>
                    　
                    <td id="A7">A7</td>                    　
                    <td id="A8">A8</td>
                </tr>
            </table>
            <table id="B" border="1" >
                　
                <tr>
                    　
                    <td id="B1">B1</td>                    　
                    <td id="B2">B2</td>
                    　
                </tr>
                　
                <tr>
                    　
                    <td id="B3">B3</td>                    　
                    <td id="B4">B4</td>
                    　
                </tr>
                <tr>
                    <td id="B5">B5</td>
                    <td id="B6">B6</td>
                </tr>
                <tr>
                    　
                    <td id="B7">B7</td>                    　
                    <td id="B8">B8</td>
                </tr>
            </table>
            <table id="C" border="1" >
                　
                <tr>
                    　
                    <td id="C1">C1</td>                    　
                    <td id="C2">C2</td>
                    　
                </tr>
                　
                <tr>
                    　
                    <td id="C3">C3</td>
                    　
                    <td id="C4">C4</td>
                    　
                </tr>
                <tr>
                    <td id="C5">C5</td>
                    <td id="C6">C6</td>
                </tr>
                <tr>
                    　
                    <td id="C7">C7</td>
                    　
                    <td id="C8">C8</td>
                </tr>
            </table>
            <table id="D" border="1">
                　
                <tr>
                    　
                    <td id="D1">D1</td>
                    　
                    <td id="D2">D2</td>
                    　
                </tr>
                　
                <tr>
                    　
                    <td id="D3">D3</td>
                    　
                    <td id="D4">D4</td>
                    　
                </tr>
                <tr>
                    <td id="D5">D5</td>
                    <td id="D6">D6</td>
                </tr>
                <tr>
                    　
                    <td id="D7">D7</td>
                    　
                    <td id="D8">D8</td>
                </tr>
            </table>
            <table id="H" border="1"  >
                　
                <tr>
                    　
                    <td id="H2">H2</td>
                    　
                    <td id="H1">H1</td>
                    　
                </tr>
                　
                <tr>
                    　
                    <td id="H4">H4</td>
                    　
                    <td id="H3">H3</td>
                    　
                </tr>
                <tr>
                    <td id="H6">H6</td>
                    <td id="H5">H5</td>
                </tr>
                <tr>
                    　
                    <td id="H8">H8</td>
                    　
                    <td id="H7">H7</td>
                </tr>
            </table>
            <table id="G" border="1"  >
                　
                <tr>
                    　
                    <td id="G2">G2</td>
                    　
                    <td id="G1">G1</td>
                    　
                </tr>
                　
                <tr>
                    　
                    <td  id="G4">G4</td>
                    　
                    <td  id="G3">G3</td>
                    　
                </tr>
                <tr>
                    <td  id="G6">G6</td>
                    <td  id="G5">G5</td>
                </tr>
                <tr>
                    　
                    <td  id="G8">G8</td>
                    　
                    <td  id="G7">G7</td>
                </tr>
            </table>
            <table id="F" border="1" >
                　
                <tr>
                    　
                    <td id="F2">F2</td>
                    　
                    <td id="F1">F1</td>
                    　
                </tr>
                　
                <tr>
                    　
                    <td id="F4">F4</td>
                    　
                    <td id="F3">F3</td>
                    　
                </tr>
                <tr>
                    <td id="F6">F6</td>
                    <td id="F5">F5</td>
                </tr>
                <tr>
                    　
                    <td id="F8">F8</td>
                    　
                    <td id="F7">F7</td>
                </tr>
            </table>
            <table id="E" border="1"  >
                　
                <tr>
                    　
                    <td id="E2">E2</td>
                    　
                    <td id="E1">E1</td>
                    　
                </tr>
                　
                <tr>
                    　
                    <td id="E4">E4</td>                    　
                    <td id="E3">E3</td>
                    　
                </tr>
                <tr>
                    <td id="E6">E6</td>
                    <td id="E5">E5</td>
                </tr>
                <tr>
                    <td id="E8">E8</td>                    　
                    <td id="E7">E7</td>
                </tr>
            </table>
        </div>
    </form>



    <div class="modal fade" id="isuModal" tabindex="-1" role="dialog" aria-labelledby="isuModalLabel" aria-hidden="true"
         style="80vim" data-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="isuModalLabel">胰島素藥品選擇</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <ul id="MedLi">
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">上一頁</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="DELModal" tabindex="-1" role="dialog" aria-labelledby="DELModalCenterTitle" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog  modal-sm modal-dialog-centered" role="document">
            <div class="modal-content" >
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
                    <button type="button" id="Del" class="btn btn-primary" style="height: 6vh;font-size: 20px">作廢</button>
                    <button type="button" class="btn btn-secondary " data-dismiss="modal" style="height: 6vh;font-size: 20px">取消</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="FuModal" tabindex="-1" role="dialog" aria-labelledby="FuModalCenterTitle" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-md" role="document">
            <div class="modal-content" >
                <div class="modal-header">
                    <h5 class="modal-title" id="FuModalCenterTitle">頻率選擇</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body" style="overflow-x: hidden;">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-12" >
                                <div class="input-group mb-3">
                                    <span class="input-group-text">自訂</span>
                                    <input type="text"  id="Fuval" class="form-control" placeholder="" >
                                    <div class="input-group-append">
                                        <button  id="FuConfirm" class="btn btn-outline-primary" data-dismiss="modal" type="button">確定</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6" id="fu1"></div>
                            <div class="col-6" id="fu2"></div>
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