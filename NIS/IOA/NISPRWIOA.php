<?php
include '../../NISPWSIFSCR.php';
$str=$_GET['str'];
$replaceSpace=str_replace(' ','+',$str);//空白先替換+
parse_str(AESDeCode($replaceSpace),$output);

$Account=$output['sIdUser'];/*帳號*/
$passwd=$output['passwd'];/*密碼*/
$sUr=$output['user'];/*使用者*/
$From=$output['From'];/*L:登入介面,U:URL操作*/


$Account="00FUZZY";

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>輸出入量作業</title>
    <script type="text/javascript" src="../../jquery-3.4.1.js"></script>
    <link rel="stylesheet" href="../../bootstrap-4.3.1-dist/css/bootstrap.min.css">
    <script src="../../bootstrap-4.3.1-dist/js/bootstrap.min.js" type="text/javascript"></script>
    <script src="../../crypto-js.js"></script>
    <script src="../../AESCrypto.js"></script>
    <script src="../../NISCOMMAPI.js"></script>
    <script>
        $(document).ready(function () {
            var BEDwindow,Serchwindow;

            let ThisPageJson=new Map();
            let OrderList=new Map();
            let CreatDefaultElement={
                TimeRadio:() =>{
                    $.ajax({
                        url:"/webservice/NISPWSFMINI.php?str="+AESEnCode("sFm=ILSGA&sPg=A"),
                        type:"POST",
                        dataType:"text",
                        success:function(data){
                            let obj=JSON.parse(AESDeCode(data));
                            let arr=JSON.parse(obj.ST_PREA);

                            $.each(arr,function (index,item) {
                                $("#ISTM").append(
                                    `
                                        <label style='font-size: 4.5vmin'><input type='radio' name='sRdoDateTime' id='${item.T_ID}' value='${item.name}' style='width: 6vmin;height: 6vmin' >
                                            ${item.name}
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
                },
                MainItem:(arr,Page)=>{
                    let Qty_Nm='';
                    let Last_Nm='';
                    switch (Page) {
                        case "A":
                            Qty_Nm='量';
                            Last_Nm='餘';
                            break;
                        case "B":
                            Qty_Nm='量';
                            Last_Nm='LO';
                            break;
                        case "C":
                            Qty_Nm='量';
                            Last_Nm='';
                            break;
                        case "D":
                            Qty_Nm='量';
                            Last_Nm='餘';
                            break;
                        case "E":
                            Qty_Nm='量';
                            Last_Nm='LO';
                            break;
                        case "F":
                            Qty_Nm='量';
                            Last_Nm='LO';
                            break;
                        case "G":
                            Qty_Nm='量';
                            Last_Nm='';
                            break;
                        case "H":
                            Qty_Nm='In';
                            Last_Nm='Out';
                            break;
                        default:
                            break;
                    }



                    $.each(arr,function (index,val) {
                       $("#item"+Page).append(
                           `
                         <div id="${'Main_'+Page+index}">
                                            <div  class="input-group">
                                                    <input id='${'M_Nam'+Page+index}' value="${val.M_Nam}" type="text" class="form-control" disabled>
                                             </div>

                                           <div class="input-group">
                                                     <span class="input-group-text" >${Qty_Nm+":"}</span>
                                                     <input  id="${'Num'+Page+index}" type="text" value="${val.QUNTY}"  class="Num form-control" autocomplete="off">

                                                    <span  class="input-group-text" >${Last_Nm+":"}</span>
                                                    <input id="${'Last'+Page+index}" type="text"  value="${val.LOSS}" class="form-control" autocomplete="off">

                                           </div>


                                           <div class="input-group mb-3">
                                                    <span class="input-group-text" >其他說明截斷字元:</span>
                                                    <input id='${'Dir_s'+Page+index}'  type="text" class="Dir_s form-control" value="" disabled>
                                                    <button  class="Obtn btn btn-secondary" type="button">其他</button>
                                           </div>

                          </div>

                           `
                       );

                        if(Page==="C" || Page==="G"){
                            $("#Last"+Page+index).prev().hide();
                            $("#Last"+Page+index).hide();
                        }

                        if(val.JID_KEY!==""){
                          $("#M_Nam"+Page+index).prop('disabled',true);

                        }
                    });

                    CreatOmodal(Page);
                    $("#LastH0").val($("#NumH1").val());
                    $("#Main_H1").hide();
                }
            };
            let ItemAction={
                appendItem:(page,obj)=>{
                    let index=$("#item"+page).children().length;
                    let Last_Str="";
                    let Sum_Str="量";

                    switch (page) {
                        case "A":
                            Last_Str="餘";
                            break;
                        case "B":
                            Last_Str="LO";
                            break;
                        case "C":
                            Last_Str="";
                            break;
                        case "D":
                            Last_Str="餘";
                            break;
                        case "E":
                            Last_Str="LO";
                            break;
                        case "F":
                            Last_Str="LO";
                            break;
                        case "G":
                            Last_Str="";
                            break;
                        case "H":
                            Sum_Str='In';
                            Last_Str='Out';
                            break;
                    }

                    $("#item"+page).append(
                        `
                           <div id="${'Main_'+page+index}">

                                               <div class="input-group">
                                                 <input id="${'M_Nam'+page+index}" value="${obj.M_Nam}" type="text" class="form-control" autocomplete="off" disabled>
                                               </div>

                                               <div class="input-group ">
                                                     <span class="input-group-text" >${Sum_Str+":"}</span>
                                                     <input  id="${'Num'+page+index}" type="text" class="Num form-control" autocomplete="off">

                                                    <span  class="input-group-text" >${Last_Str+":"}</span>
                                                    <input   id="${'Last'+page+index}" type="text" class="Last form-control" autocomplete="off">

                                                </div>

                                               <div  class="input-group mb-3">
                                                    <span class="input-group-text" >其他說明截斷字元:</span>
                                                    <input id="${'Dir_s'+page+index}" type="text" class="form-control"  disabled>
                                                    <button  class="Obtn btn btn-secondary" type="button">其他</button>
                                               </div>

                             </div>
                                        `
                    );
                    if(page==="C" || page==="G"){
                        $("#Last"+page+index).prev().hide();
                        $("#Last"+page+index).hide();
                    }

                    CreatOmodal(page);
                    $("#LastH0").val($("#NumH1").val());
                    $("#Main_H1").hide();
                },
                removeItem:(page)=>{
                    if($("#item"+page).children().length===1){
                        return false;
                    }else {
                        $("#item"+page).children().last().remove();
                    }
                }
            };

            (function () {
                CreatDefaultElement.TimeRadio();
                $("#loading").hide();
                $("#wrapper").hide();
                $("#PageBtn").children().prop('disabled',true);
                $("#SubmitBtn").prop('disabled',true);
                $("#SerchBtn").prop('disabled',true);
                $("#DELBtn").prop('disabled',true);
                $(".Parametertable").children().prop('disabled',true);
            })();

            /****************************************Click Event***************************************************/

            $(document).on('click','button',function () {
                let Btn=$(this).attr('id');
                let Page=$("#PageVal").val();
                let sTraID=$("#sTraID").val();
                let sTM=$("#sTime").val();
                let sDt=$("#sDate").val();
                let IdPt=$("#DA_idpt").val(),InPt=$("#DA_idinpt").val(),PName=$("#DataTxt").val();

                //other btn
                if ($(this).attr('class')==='Obtn btn btn-secondary'){
                    let FatherEle=$(this).parent().parent();
                    let index=FatherEle.attr('id').substring(6,FatherEle.attr('id').length);
                    OpenOmodal(Page,index);

                }

                //Add item btn
                if ($(this).attr('class')==='OrderConfirmBtn btn btn-primary'){
                    let index=parseInt($(this).val());
                    let Order_Obj=OrderList.get(Page)[index];
                    ThisPageJson.get(Page).push(Order_Obj);
                    ItemAction.appendItem(Page,Order_Obj);

                    ThisPageJson.set(Page,ThisPageJson.get(Page));
                    $("#Order_Modal").modal('hide');

                }

                switch (Btn) {
                    case "sbed":
                        switch (checkBEDwindow()) {
                            case "false":
                                alert("責任床位視窗已開啟");
                                break;
                            case "true":
                                BEDwindow=window.open("/webservice/NISPWSLKCBD.php?str="+AESEnCode("sFm=IOA&sIdUser=<?php echo $Account?>"),"責任床位(血)",'width=850px,height=650px,scrollbars=yes,resizable=no');
                                break;
                        }
                        BEDwindow.bedcallback=bedcallback;
                        break;
                    case "SerchBtn":
                        switch (checkSerchwindow()) {
                            case "false":
                                alert("查詢視窗已開啟");
                                break;
                            case "true":
                                Serchwindow=window.open("/webservice/NISPWSLKQRY.php?str="+
                                    AESEnCode("sFm=IOA&PageVal="+""+"&DA_idpt="+IdPt+"&DA_idinpt="+InPt
                                        +"&sUser="+"<?php echo $Account?>"+"&NM_PATIENT="+PName)
                                    ,"IOA",'width=750px,height=650px,scrollbars=yes,resizable=no');
                                break;
                        }

                        Serchwindow.Serchcallback=Serchcallback;
                        break;
                    case "AddItemBtn":
                        let Order_DATA=OrderList.get(Page);

                        $("#Order_Content").children().remove();

                        $.each(Order_DATA,function (index,val) {
                            $("#Order_Content").append(
                                `
                                <li style="font-size: 120%">
                                    <button  value="${index}" style="font-size: 130%" class="OrderConfirmBtn btn btn-primary">選擇</button>
                                    ${val.M_Nam}
                                </li>
                                `
                            );
                        });
                        $("#Order_Modal").modal('show');
                        break;
                    case "O_ConfirmBtn":
                        let index= $("#OMindex").val();
                        let MM=$("#O_"+Page+index).val();
                        let obj=ThisPageJson.get(Page);
                        let val='';


                        if($("input[name="+'IOCK_'+Page+index+"]:checked").val()){
                            val=$("input[name="+'IOCK_'+Page+index+"]:checked").val();
                            obj[index].IOWAY= val.substring(index.length+1,val.length);
                        }else {
                            obj[index].IOWAY=" ";
                        }

                        if ($("input[name="+'COLOR_CK_'+Page+index+"]:checked").val()){
                            val= $("input[name="+'COLOR_CK_'+Page+index+"]:checked").val();
                            obj[index].COLOR= val.substring(index.length+1,val.length);
                        }else {
                            obj[index].COLOR=" ";
                        }

                        obj[index].MM_IO=MM;

                        ThisPageJson.set(Page,obj);
                        $("#Dir_s"+Page+index).val(MM);
                        $("#OtherModal").modal('hide');
                        break;
                    case "O_CancelBtn":
                       let C_index= $("#OMindex").val();


                        $("input[name="+'IOCK_'+Page+C_index+"]").prop('checked',false);
                        $("input[name="+'COLOR_CK_'+Page+C_index+"]").prop('checked',false);
                        $("#O_"+Page+C_index).val("");

                        break;
                    case "SubmitBtn":

                        if (($("#sDate").val()).trim()==="" || ($("#sTime").val()).trim()==="")
                        {
                            alert('請填寫時間日期');
                            return;
                        }

                        let json_str=JSON.stringify(ThisPageJson.get(Page));
                        $("#wrapper").show();

                        DB_WSST(Page,sTraID,json_str,sDt,sTM,'',$("#FSEQ").val(),'<?php echo $Account?>','true');

                        break;
                    case "DELBtn":
                        DB_DEL(sTraID,'<?php echo $Account?>');
                        break;
                    case "ReSetBtn":
                        $(".PageBtn").css({'background-color' : '','opacity' : '' ,'color':''});
                        $("#SerchBtn,#SubmitBtn,#DELBtn,.PageBtn").prop('disabled',true);
                        $("input[type=text]:not(.Parametertable>input)").val("");
                        $("input[type=radio]").prop("checked",false);
                        $("#ISTM").show();
                        $(".ItemBtn,.PItem").hide();
                        $("#FSEQ").val("");
                        break;
                    case "ThirdClassBtn":

                        if ($("#DataTxt").val()===""){
                            alert('請先選擇責任床位');
                            return;
                        }

                        window.open("../IOAC/NISPWIOAC.php?str="+AESEnCode("DA_idpt="+IdPt+"&DA_idinpt="+InPt+
                            "&sUser="+"<?php echo $Account?>"+"&nM_P="+$('#DataTxt').val()),
                            "三班時間",'width=850px,height=650px,scrollbars=yes,resizable=no');
                        break;
                    default:
                        break;
                }
            });

            $(".PageBtn").on('click',function () {
                let Page=$(this).attr('id');
                let sTraID=$("#sTraID").val();
                let S_Confirm=$("#SearchConfirm").val();
                const Page_Arr=['A','B','C','D','E','F','G','H'];
                let WSST_arr=Page_Arr.filter(value => value!==Page);


                if ($("#item"+Page).children().length===0 && S_Confirm==="N"){
                    GetPageJson(Page,sTraID);
                }

                WSST_arr.forEach(function (value) {

                    if (ThisPageJson.has(value)){
                        console.log(ThisPageJson.get(value));
                        DB_WSST(value,sTraID,JSON.stringify(ThisPageJson.get(value)),'','','','','','false')
                    }
                });



                if (Page==="H" )
                {
                    $(".ItemBtn").hide();

                }else {
                    $(".ItemBtn").show();

                }


                $(".PageBtn").css({'background-color' : '','opacity' : '' ,'color':''});
                $(this).css({'background-color' : '#EEEE00', 'opacity' : '' ,'color':'black'});
                $("#PageVal").val(Page);
                $(".PItem").hide();
                $("#item"+Page).show();
                $("#SubmitBtn").prop('disabled',false);
                $(".ItemBtn").show();   //新增Btn
            });

            /***************************************Text Change Event**********************************************/

            $(document).on('change',"input[type=text]",function () {

                let Page=$('#PageVal').val();
                let Id=$(this).attr('id');
                let index=Id.split('')[Id.length-1];
                let Txt=$(this).val();

                let CidIo="";
                let obj=ThisPageJson.get(Page);
                let myReg =  new RegExp("^[1-9][0-9]*([\\.][0-9]{1,2})?$");


               if (myReg.test(Txt)!==true && Txt.trim()!==""){
                    alert('請輸入數字');
                    $(this).val('');
                    $(this).focus();
                    return;
                }

                if (Id !=="sDate" && Id!=="sTime"){
                    if(Page==="A" || Page==="B" || Page==="C" || Page==="D"){
                        CidIo="I";
                    }else if (Page==="H"){
                        CidIo="R";
                    }
                    else if (Page==="F"){
                        CidIo="S";
                    }
                    else {
                        CidIo="O";
                    }

                    obj[index].CID_IO=CidIo;
                    obj[index].QUNTY=$("#Num"+Page+index).val();
                    obj[index].LOSS=$("#Last"+Page+index).val();

                    if (Page==="H"){
                        obj[1].CID_IO=obj[0].CID_IO;
                        obj[1].QUNTY=obj[0].LOSS;
                        obj[0].LOSS="";
                    }

                    obj[index].MM_IO=$("#Dir_s"+Page+index).val();
                    obj[index].M_Nam=$("#M_Nam"+Page+index).val();
                    ThisPageJson.set(Page,obj);
                }

            });

            /***************************************RadioBtn Change Event******************************************/
            $(document).on('change',"input[type=radio]",function () {
                let index=$(this).val().substring(0,1);
                let Page=$(this).val().substring(1,2);
                let id=$(this).val().substring(2,14);
                let ck_Class=$(this).attr('class');
                let ck_Name=$(this).attr('name');


                if (ck_Name==="sRdoDateTime"){
                    let TimeNow=new Date();
                    let yyyy=TimeNow.toLocaleDateString().slice(0,4);
                    let MM=(TimeNow.getMonth()+1<10?'0':'')+(TimeNow.getMonth()+1);
                    let dd=(TimeNow.getDate()<10?'0':'')+TimeNow.getDate();
                    let  h=(TimeNow.getHours()<10?'0':'')+TimeNow.getHours();
                    let  m=(TimeNow.getMinutes()<10?'0':'')+TimeNow.getMinutes();
                    let Timetxt=($(this).val()).split("");
                    let timer=Timetxt.filter(value => value!==":");
                    let timerVal=$(this).attr('id')==="ISTM00000005"?h+m:timer.join("");

                    $("#IDTM").val($(this).attr('id'));
                    $("#sDate").val(yyyy-1911+MM+dd);
                    $("#sTime").val(timerVal);
                }else {
                    if (ck_Class==="IOCK_"+Page+index){
                        $(".IOCK_"+Page+index).prop('checked',false);
                        $("#"+index+Page+id).prop('checked',true);
                    }

                    if (ck_Class==="COLOR_CK_"+Page+index){
                        $(".COLOR_CK_"+Page+index).prop('checked',false);
                        $("#"+index+Page+id).prop('checked',true);
                    }
                }

            });


            function GetINIJson(idPt,INPt,sUr){
                $("#wrapper").show();
                $("#loading").show();
                $.ajax("/webservice/NISPWSTRAINI.php?str="+AESEnCode('sFm=IOA&idPt='+idPt+'&INPt='+INPt+"&sUr="+sUr))
                    .done(function(data) {
                        $("#wrapper").hide();
                        let obj=JSON.parse(AESDeCode(data));
                        $("#sSave").val(obj.sSave);
                        $("#sTraID").val(obj.sTraID);
                        $("#SRANK").val(obj.JID_NSRANK);
                        $("#FSEQ_WT").val(obj.FORMSEQANCE_WT);


                        for (let index in obj.ORDER){
                            OrderList.set(index,obj.ORDER[index]);
                        }


                        ThisPageJson.set('H',obj.P_H);
                        $.each(obj.P_H,function (index,value) {
                            ItemAction.appendItem('H',value);
                        });
                        $("#loading").hide();
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
            $.ajax("/webservice/NISPWSGETPRE.php?str="+AESEnCode("sFm=IOA&sTraID="+sTraID+"&sPg="+Page))
                    .done(function (data) {
                        let obj=JSON.parse(AESDeCode(data));
                        ThisPageJson.set(Page,obj);
                        CreatDefaultElement.MainItem(obj,Page);
                    }).fail(function(XMLHttpResponse,textStatus,errorThrown) {
                    console.log(
                        "1 返回失敗,XMLHttpResponse.readyState:"+XMLHttpResponse.readyState+XMLHttpResponse.responseText+
                        "2 返回失敗,XMLHttpResponse.status:"+XMLHttpResponse.status+
                        "3 返回失敗,textStatus:"+textStatus+
                        "4 返回失敗,errorThrown:"+errorThrown
                    );
                });

            }
            function DB_WSST(Page,sTraID,json,sDt=null,sTm=null,Passed=null,Freq=null,sUr,InSertDB){
                let obj=JSON.parse(json);

                $.each(obj,function (index,val) {
                    if ((val.M_Nam).indexOf('&')>0){
                        val.M_Nam= encodeURI(val.M_Nam.split("").map(function (value) {
                            return  value.match(/&/)!==null?value.replace(/&/g,'＆'):value;
                        }).join(""));
                    }
                });


                let SavaJson=JSON.stringify(obj);


                $.ajax('/webservice/NISPWSSETDATA.php?str='+AESEnCode(
                    'sFm=IOA&sTraID='+sTraID+'&sPg='+Page+'&sData='+SavaJson+
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

                        console.log(Page,json);
                    }).fail(function (XMLHttpResponse,textStatus,errorThrown) {
                    console.log(
                        "1 返回失敗,XMLHttpResponse.readyState:"+XMLHttpResponse.readyState+XMLHttpResponse.responseText+
                        "2 返回失敗,XMLHttpResponse.status:"+XMLHttpResponse.status+
                        "3 返回失敗,textStatus:"+textStatus+
                        "4 返回失敗,errorThrown:"+errorThrown
                    );
                });

            }
            function DB_DEL(sTraID,sUr) {
                $.ajax("/webservice/NISPWSDELILSG.php?str="+AESEnCode("sFm="+'IOA'+"&sTraID="+sTraID+"&sPg="+""+"&sCidFlag=D"+"&sUr="+sUr))
                    .done(function (data) {
                        let re=JSON.parse(AESDeCode(data));
                        if(re.result==="false"){
                            alert('作廢失敗');
                            return false;
                        }else {
                            const Page_Arr=['A','B','C','D','E','F','G','H'];
                            ThisPageJson.clear();
                            Page_Arr.forEach(function (value) {
                                $("#item"+value).children().remove();
                                $("#"+value).css({'background-color':'','color':''})
                            });

                            GetINIJson($("#DA_idpt").val(),$("#DA_idinpt").val(),'<?php echo $Account?>');

                            $("#DELBtn").prop('disabled',true);
                            $(".ItemBtn").hide();
                            $("#ISTM").show();
                            $("#sDate,#sTime").val("");
                            $("#SearchConfirm").val('N');
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
            function bedcallback(data){
                let dataObj=JSON.parse(AESDeCode(data))[0];

                $("#DA_idpt").val(dataObj.IDPT);
                $("#DA_idinpt").val(dataObj.IDINPT);
                $("#DA_sBed").val(dataObj.SBED);
                $("#DataTxt").val(dataObj.DataTxt);


                if (ThisPageJson.size>0){
                    ThisPageJson.clear();
                }

                if (OrderList.size > 0 ){
                    OrderList.clear();
                }

                $(".PItem").children().remove();
                $("#OtherModalbody").children().remove();

                GetINIJson(dataObj.IDPT,dataObj.IDINPT,'<?php echo $Account?>');



                $("#PageBtn").children().prop('disabled',false);
                $(".PageBtn").css({'background-color' : '','opacity' : '' ,'color':''});
                $("input[type=radio]").prop("checked",false);

                $(".PItem").hide();
                $(".ItemBtn").hide();
                $("#SerchBtn").prop('disabled',false);
                $("#SearchConfirm").val('N');
                $("#FSEQ").val("");
                $("#ISTM").show();
            }
            function Serchcallback(AESobj){

             const PageArr =['A','B','C','D','E','F','G','H'];
             let obj=JSON.parse(AESDeCode(AESobj));
             let IdPt=obj.IdPt;
             let InIdPt=obj.INPt;

            if (IdPt!==$("#DA_idpt").val() || InIdPt!==$("#DA_idinpt").val())
             {
                    alert("病人資訊已異動,請重新操作");
                    return ;
             }



             /***初始化****/
             //ThisPageJson.clear();

             $.each(PageArr.filter(value => value!=='H'),function (index,page) {
                 $("#item"+page).children().remove();
             });


             for(let index in obj){
                 if (PageArr.indexOf(index)>-1){
                     let arr=obj[index];

                     /*IPR若有存值,ThisPageJson重新定義*/
                    if (index !=='H' || obj['H'].length>0){
                        ThisPageJson.set(index,arr);
                    }
                     /*IPR若有存值,畫面重新布置*/
                     if (obj['H'].length>0){
                         $("#itemH").children().remove();
                     }
                     CreatDefaultElement.MainItem(arr,index);
                 }

             }




                $("#sDate").val(obj.DT_EXCUTE);
                $("#sTime").val(obj.TM_EXCUTE.substring(0,4));
                $("#sTraID").val(obj.sTraID);
                $("#FSEQ").val(obj.FORMSEQ);
                $("#SearchConfirm").val('Y');
                $("#DELBtn").prop('disabled',false);
                $("#ISTM").hide();
                $("#ItemBtn").show();
            }
            function checkBEDwindow() {
                if(!BEDwindow){
                    return "true";
                }else {
                    if(BEDwindow.closed){
                        return "true";
                    }else {
                        return "false";
                    }
                }
            }
            function checkSerchwindow() {
                if(!Serchwindow){
                    return "true";
                }else {
                    if(Serchwindow.closed){
                        return "true";
                    }else {
                        return "false";
                    }
                }
            }
            function CreatOmodal(Page) {
                let arr=ThisPageJson.get(Page);
                $.each(arr,function (index,val) {
                    let Name=(val.M_Nam).trim()===""?$("#M_Nam"+Page+index).val():val.M_Nam;
                    $('#M_'+Page+index).remove();

                    $("#OtherModalbody").append(
                        `
                                   <div id="${'M_'+Page+index}" class="M_Omodal row">

                                        <div class="col-12">
                                              <input type="text" class="form-control" value="${Name}" disabled>
                                        </div>

                                        <div id="${'IOType'+Page + index}" class="col-12" >
                                            <div>
                                                  <label style="color: #0f6674">方式:</label>
                                            </div>
                                        </div>

                                        <div id="${'Color'+Page + index}" class="col-12">
                                            <div>
                                                <label style="color: #0f6674">顏色:</label>
                                            </div>
                                        </div>


                                        <div class="col-12">
                                            <div class="form-group">
                                                 <label for="${'O_'+Page+index}" style="color: #0f6674">備註:</label>
                                                 <textarea class="form-control rounded-0" id="${'O_'+Page+index}" rows="10"></textarea>
                                            </div>
                                        </div>

                                    </div>
                                `
                    );


                       if ((val.JID_MM).length>0){
                           $.each(val.JID_MM,function (i,value) {

                               $("#IOType" + Page + index).append(
                                   `
                              <label style="font-size: 1.5rem;">
                                   <input type="radio" id="${index + Page + value.JID_KEY}" class="${'IOCK_' + Page + index}"  name="${'IOCK_' + Page + index}"  value="${index + Page +value.JID_KEY}">
                                   ${value.NM_ITEM}
                              </label>
                            `
                               )

                           });

                       }else {
                           //沒方式選項->隱藏
                           $("#IOType" + Page + index).hide();
                       }

                       if ((val.JID_COLOR).length>0){
                           $.each(val.JID_COLOR,function (i,value) {
                               $("#Color" + Page + index).append(
                                   `
                                  <label style="font-size: 1.5rem;">
                                      <input type="radio" id="${index + Page + value.JID_KEY}"  class="${'COLOR_CK_' + Page + index}"    name="${'COLOR_CK_' + Page + index}"  value="${index + Page +value.JID_KEY}">
                                       ${value.NM_ITEM}
                                 </label>
                                  `
                               );
                           })

                       }else {
                           //沒顏色選項->隱藏
                           $("#Color" + Page + index).hide();
                       }

                        $("#"+index+Page+val.IOWAY).prop('checked',true);
                        $("#"+index+Page+val.COLOR).prop('checked',true);
                        $("#O_"+Page+index).val(val.MM_IO);
                        $("#Dir_s"+Page+index).val(val.MM_IO);

                   });
            }
            function OpenOmodal(Page,index) {
                $(".M_Omodal").hide();
                $("#OMindex").val(index);//other modal index
                $("#M_"+Page+index).show();
                $("#OtherModal").modal('show');
            }

        });
    </script>
</head>
<style>

    .container .title button{
        color: white;
        font-size: 4.5vmin;
        margin-top: 5px;
        margin-bottom: 5px;
    }
    .container .Otimer{
        margin-top:5px;
        font-size: 4vmin;
        background-color: #baeeff;
        border-radius:3px;
    }

    #sbed{
        margin-left: 1px;
    }
    #DataTxt{
        font-size: 4vmin;
        background-color: #FFFBCC;
        border-radius:3px;
        margin-top: 5px;
        color: black;
    }
    #sDate{
         width:35vmin;
        text-align: center;
        margin-top: 5px;
        border: 1px white;
    }
    #sTime {
        width: 15vmin;
        margin-left: 5px;
        margin-top: 5px;
        border: 1px white;
    }
    #ISTM{
        margin-top: 5px;
    }
    #ThirdClassBtn{
        margin-bottom: 10px;
        max-width: 75px;
        max-height: 45px;
    }

   .Otimer .DateTime:first-child{
       font-size: 4vmin;
   }
   .PageBtn{
       margin-top: 5px;
       font-size: 3.6vmin;
    }
    #main{
        margin-top: 5px;
    }
    .PItem{
        display: none;
    }
    .ItemBtn{
        display: none;
        margin-top: auto;
        margin-bottom:15px;
    }
    .ItemBtn button{
        font-size: 3vmin;
        border-radius: 50%;
    }

    .Parametertable input{
        display: none;
        background-color: #00FF00;
    }
    .Dir_s{
        text-overflow: ellipsis;
        white-space: nowrap;
        overflow: hidden;
    }
    #DModal_Txt p{
        word-break: break-all;
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
        width: 200%;
        height: 200%;
        background-color: black;
        opacity: 0.5;
        z-index: 9998;
    }
    input[type=radio]{
        width: 1.5rem;
        height: 1.5rem;
    }
    li{
        list-style: none;
        margin-bottom: 5px;
        margin-left: -5px;
    }
</style>
<body>
<div id="wrapper"></div>
<div id="loading" >請稍後<img class="loadimg" src="../../dotloading.gif"></div>
<div class="container">
         <h1>輸出入量作業</h1>
    <!----------------------------------------------------------Parametertable display none-------------------------------------------------------------------------->
    <div class="Parametertable">
        <input id="PageVal"     value=""  type="text"  placeholder="PageVal">       <!--標籤-->
        <input id="DA_idpt"     value=""  type="text"  placeholder="DA_idpt">       <!--病歷號-->
        <input id="DA_idinpt"   value=""  type="text"  placeholder="DA_idinpt">     <!--住院號-->
        <input id="DA_sBed"     value=""  type="text"  placeholder="DA_sBed">       <!--床號-->
        <input id="sSave"       value=""  type="text"  placeholder="sSave">         <!--存檔權限-->
        <input id="sTraID"      value=""  type="text"  placeholder="sTraID">        <!--交易序號-->
        <input id="FSEQ"        value=""  type="text"  placeholder="FSEQ">  <!--I/O單號-->
        <input id="SRANK"       value=""  type="text"  placeholder="JID_NSRANK" >
        <input id="FSEQ_WT"     value=""  type="text"  placeholder="FORMSEQANCE_WT">


        <input id="SearchConfirm"  value="N"  type="text"  placeholder="SearchConfirm">
        <input id="IDTM"        value=""  type="text"  placeholder="IDTM" >         <!--timeID-->
        <input id="OMindex"     value=""  type="text"  placeholder="OMindex" >      <!--OtherModalIndex-->

    </div>
    <!----------------------------------------------------------Function Bar-------------------------------------------------------------------------->
         <span  class="title">
            <button type="button" class="btn btn-secondary btn-md" disabled style="display: none">回主畫面</button>
            <button type="button"  class="btn btn-warning btn-md"  id="sbed" >責任床位</button>
            <span style="margin-left: 1px"><b>使用者:<?php echo $sUr?></b></span>
         </span>

        <span class="title float-left">
            <button type="button" id="SubmitBtn" class="btn btn-primary btn-md" >儲存</button>
            <button type="button" id="SerchBtn" class="btn btn-primary btn-md" >查詢</button>
            <button type="button" id="DELBtn" class="btn btn-primary btn-md"  >作廢</button>
            <button type="button" id="ReSetBtn" class="btn btn-primary btn-md"  >清除</button>
        </span>
    <!----------------------------------------------------------Patient Name-------------------------------------------------------------------------->
        <div>
            <input id="DataTxt" value="" class="form-control" type="text" disabled>
        </div>
    <!----------------------------------------------------------Time-------------------------------------------------------------------------->
        <div class="Otimer" >
            <div class="DateTime">
                <label >評估時間:</label>
                <input type="text" id="sDate" value=""  placeholder="YYYMMDD" maxlength="7" autocomplete="off">
                <input type="text" id="sTime" value=""  placeholder="HHMM" maxlength="4" autocomplete="off">
                <button type="button" id="ThirdClassBtn" class="btn btn-info  btn-lg">三班</button>
            </div>
            <div id="ISTM"></div>
        </div>
    <!----------------------------------------------------------Page Button-------------------------------------------------------------------------->
        <div id="PageBtn">
            <button type="button" id="A" class="PageBtn btn btn-primary btn-lg" >靜脈</button>
            <button type="button" id="B" class="PageBtn btn btn-primary btn-lg" >飲食</button>
            <button type="button" id="C" class="PageBtn btn btn-primary btn-lg" >輸血</button>
            <button type="button" id="D" class="PageBtn btn btn-primary btn-lg" >TPN</button>
            <button type="button" id="E" class="PageBtn btn btn-primary btn-lg" >輸出</button>
            <button type="button" id="F" class="PageBtn btn btn-primary btn-lg" >大便</button>
            <button type="button" id="G" class="PageBtn btn btn-primary btn-lg" >引流</button>
            <button type="button" id="H" class="PageBtn btn btn-primary btn-lg" >IPR</button>
        </div>
    <!----------------------------------------------------------Page Item-------------------------------------------------------------------------->
         <div id="main">
            <!----------------------------------------------------------Item A-------------------------------------------------------------------------->
            <div id="itemA" class="PItem">

            </div>

            <!----------------------------------------------------------Item B-------------------------------------------------------------------------->
            <div id="itemB" class="PItem">

            </div>

            <!----------------------------------------------------------Item C-------------------------------------------------------------------------->
            <div id="itemC" class="PItem">

            </div>

            <!----------------------------------------------------------Item D------------------------------------------------------------------------->
            <div id="itemD" class="PItem">

            </div>

            <!----------------------------------------------------------Item E-------------------------------------------------------------------------->
            <div id="itemE" class="PItem">

            </div>

            <!----------------------------------------------------------Item F-------------------------------------------------------------------------->
            <div id="itemF" class="PItem">

            </div>

            <!----------------------------------------------------------Item G-------------------------------------------------------------------------->
            <div id="itemG" class="PItem">

            </div>

            <!----------------------------------------------------------Item H-------------------------------------------------------------------------->
            <div id="itemH" class="PItem">

            </div>


        </div>

        <div class="ItemBtn">
             <button id="AddItemBtn" class="btn btn-outline-warning"><b>新增</b></button>
        </div>
    <!----------------------------------------------------------OtherModal-------------------------------------------------------------------------->
        <div class="modal fade" id="OtherModal" tabindex="-1" role="dialog"  aria-hidden="true" data-backdrop="static">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="OtherModalTitle">其他評估</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div id="OtherModalbody" class="modal-body">

                    </div>
                    <div class="modal-footer">
                        <button id="O_CancelBtn" type="button" class="btn btn-danger">清除</button>
                        <button id="O_ConfirmBtn" type="button" class="btn btn-primary">確認</button>
                        <button  type="button" class="btn btn-secondary" data-dismiss="modal">放棄回上一頁</button>
                    </div>
                </div>
            </div>
        </div>

    <!----------------------------------------------------------Dir_s Modal-------------------------------------------------------------------------->
        <div class="modal fade" id="Dir_sModal" tabindex="-1" role="dialog"  aria-hidden="true" data-backdrop="static">
            <div class="modal-dialog  modal-dialog-scrollable modal-dialog-centered " role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">其他說明截斷字元</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div id="DModal_Txt"  class="modal-body">

                    </div>
                </div>
            </div>
        </div>

    <!----------------------------------------------------------Third_Class Modal-------------------------------------------------------------------------->
       <div class="modal fade" id="TC_sModal" tabindex="-1" role="dialog"  aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog  modal-dialog-scrollable modal-dialog-centered " role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">三班日期</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div id="TC_Content"  class="modal-body">

                </div>
            </div>
        </div>
    </div>
    <!----------------------------------------------------------Order Modal-------------------------------------------------------------------------------->
      <div class="modal fade" id="Order_Modal" tabindex="-1" role="dialog"  aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog  modal-dialog-scrollable modal-dialog-centered " role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">藥名清單</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <ul id="Order_Content">

                    </ul>
                </div>
            </div>
        </div>
    </div>

</div>
</body>
</html>
