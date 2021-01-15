<?php
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
            let CreatDefaultElement={
                TimeRadio:() =>{
                    /*console.log("http://localhost/webservice/NISPWSFMINI.php?str="+AESEnCode("sFm=ILSGA&sPg=A"));*/
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
                                <label style='font-size: 4.5vmin'><input type='radio' name='sRdoDateTime' id='${item.T_ID}' value='${item.name}' style='width: 6vmin;height: 6vmin' >${item.name}</label>
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
                                                    <input id='${'M_Nam'+Page+index}' value="${val.M_Nam}" type="text" class="form-control" >
                                             </div>

                                           <div class="input-group">
                                                     <span class="input-group-text" >${Qty_Nm+":"}</span>
                                                     <input  id="${'Num'+Page+index}" type="text" value="${val.QUNTY}"  class="Num form-control">

                                                    <span  class="input-group-text" >${Last_Nm+":"}</span>
                                                    <input id="${'Last'+Page+index}" type="text"  value="${val.LOSS}" class="form-control">

                                               </div>


                                             <div class="input-group mb-3">
                                                    <span class="input-group-text" >其他說明截斷字元:</span>
                                                    <input id='${'Dir_s'+Page+index}'  type="text" class="Dir_s form-control" value="" readonly>
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
                    CreatOmodal(Page,arr);
            }
            };
            let ItemAction={
                appendItem:(page)=>{
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
                            Sum_Str="IN";
                            Last_Str="Out";
                            break;
                    }

                    $("#item"+page).append(
                        `
                           <div id="${'Main_'+page+index}">

                                               <div class="input-group">
                                                 <input id="${'M_Nam'+page+index}" type="text" class="form-control">
                                                  <div class="input-group-append">
                                                    <button  class="btn btn-outline-primary" type="button">選擇</button>
                                                  </div>
                                                </div>

                                               <div class="input-group ">
                                                     <span class="input-group-text" >${Sum_Str+":"}</span>
                                                     <input  id="${'Num'+page+index}" type="text" class="Num form-control" >

                                                    <span  class="input-group-text" >${Last_Str+":"}</span>
                                                    <input   id="${'Last'+page+index}" type="text" class="Last form-control">

                                                </div>

                                               <div  class="input-group mb-3">
                                                    <span class="input-group-text" >其他說明截斷字元:</span>
                                                    <input id="${'Dir_s'+page+index}" type="text" class="form-control"  readonly>
                                                    <button  class="Obtn btn btn-secondary" type="button">其他</button>
                                               </div>

                             </div>
                                        `
                    );
                    if(page==="C" || page==="G"){
                        $("#Last"+page+index).prev().hide();
                        $("#Last"+page+index).hide();
                    }
                },
                removeItem:(page)=>{
                    if($("#item"+page).children().length===1){
                        return false;
                    }else {
                        $("#item"+page).children().last().remove();
                    }
                }
            };
            var CallOnce=false;
            var PageINI=false;
            var SerchCallBack=false;
            let AddBtn_Color=new Map();
            let AddBtn_IoType=new Map();
            let ThisPageJson=new Map();
            let DefaultObj=[{
                DataSeq:"",
                CID_CLASS: "",
                CID_IO: "",
                COLOR: "",
                IO_TYPE: "",
                IOWAY: "",
                JID_KEY: "",
                LOSS: "",
                MM_IO: "",
                M_Nam: "",
                QUNTY: "",
                IS_SUM:""
            }];


            (function () {
                CreatDefaultElement.TimeRadio();
                $("#loading").hide();
                $("#wrapper").hide();
                $("#PageBtn").children().prop('disabled',true);
                $("#SubmitBtn").prop('disabled',true);
                $("#SerchBtn").prop('disabled',true);
                $("#DELBtn").prop('disabled',true);
            })();

            /****************************************Click Event***************************************************/
            $(document).on('click','button',function () {
                let btnId=$(this).attr('id');
                let Page=$("#PageVal").val();
                let sTraID=$("#sTraID").val();
                let sTM=$("#sTime").val();
                let sDt=$("#sDate").val();
                let IdPt=$("#DA_idpt").val(),InPt=$("#DA_idinpt").val(),PName=$("#DataTxt").val();



                //other btn
                if ($(this).attr('class')==='Obtn btn btn-secondary'){

                    let FatherEle=$(this).parent().parent();
                    let index=FatherEle.attr('id').substring(6,FatherEle.attr('id').length);
                    $("#O_"+Page+index).val($("#Dir_s"+Page+index).val());

                  /*  CreatOmodal(Page);*/
                    OpenOmodal(Page,index);

                }




                switch (btnId) {
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

                      ItemAction.appendItem(Page);

   /*                       CreatOmodal(Page, AddBtn_IoType.get(Page),AddBtn_Color.get(Page),1,"A");
                        let CLASS=ThisPageJson.get(Page)[0].CID_CLASS;
                        let Cid_io=ThisPageJson.get(Page)[0].CID_IO;
                        let IoType=ThisPageJson.get(Page)[0].IO_TYPE;

                        let emptyObj={
                            DataSeq:"",
                            CID_CLASS: CLASS,
                            CID_IO: Cid_io,
                            COLOR: "",
                            IO_TYPE: "",
                            IOWAY: "",
                            JID_MM:"",
                            JID_COLOR:"",
                            JID_KEY: "",
                            LOSS: "",
                            MM_IO: "",
                            M_Nam: "",
                            QUNTY: "",
                            IS_SUM:""
                        };

                        ThisPageJson.get(Page).push(emptyObj);*/
                        break;
                    case "O_ConfirmBtn":
                        let index= $("#OMindex").val();
                        let MM=$("#O_"+Page+index).val();
                        let obj=ThisPageJson.get(Page);


                        obj[index].MM_IO=MM;
                        let val='';
                        if($("input[name="+'IOCK_'+Page+index+"]:checked").val()){
                            val=$("input[name="+'IOCK_'+Page+index+"]:checked").val();
                            obj[index].IOWAY= val.substring(index.length+1,val.length);
                        }
                        if ($("input[name="+'COLORCK_'+Page+index+"]:checked").val()){

                            val= $("input[name="+'COLORCK_'+Page+index+"]:checked").val();
                            obj[index].COLOR= val.substring(index.length+1,val.length);
                        }

                        $("#Dir_s"+Page+index).val(MM);
                        $("#OtherModal").modal('hide');
                        break;
                    case "SubmitBtn":
                        DB_SAVE(Page,sTraID,sDt,sTM,'','<?php echo $Account?>');
                        break;
                    case "DELBtn":
                        DB_DEL(sTraID,'<?php echo $Account?>');
                        break;
                    case "ReSetBtn":
                        $(".PageBtn").css({'background-color' : '','opacity' : '' ,'color':''});
                        $("input[type=text]:not(.Parametertable>input)").val("");
                        $("input[type=radio]").prop("checked",false);
                        $(".PItem").hide();
                        $(".ItemBtn").hide();

                        break;
                    case "ThirdClassBtn":
                        window.open("../IOA_C/NISPWIOA_C.php?str="+AESEnCode("DA_idpt="+IdPt+"&DA_idinpt="+InPt+"&sUser="+"<?php echo $Account?>"+"&nM_P="+$('#DataTxt').val()),
                            "三班時間",'width=850px,height=650px,scrollbars=yes,resizable=no');
                        break;
                    default:
                        break;
                }
            });
            $(".PageBtn").on('click',function () {
                let Page=$(this).attr('id');
                let IdPt=$("#DA_idpt").val(), IdinPt=$("#DA_idinpt").val(),sUr="<?php echo $Account?>",sTraID=$("#sTraID").val();
                console.log(PageINI);

                if (PageINI===true ){

                    /*if(SerchCallBack===false){
                        //搜尋後加入
                    }*/

                    GetPageJson(Page,sTraID);

                    //存第一次選擇的頁面Json
                    for (let e of ThisPageJson.entries()){
                        DB_WSST(e[0],sTraID,JSON.stringify(e[1]));
                    }

                }


                /*Get INI Json to FistTime*/
                if (CallOnce===false){

                    $("#SubmitBtn").prop('disabled',false);
                    $("#SerchBtn").prop('disabled',false);
                    GetINIJson(IdPt,IdinPt,sUr,Page);
                    PageINI=true;
                }

                $(".PageBtn").css({'background-color' : '','opacity' : '' ,'color':''});
                $(this).css({'background-color' : '#EEEE00', 'opacity' : '' ,'color':'black'});
                $("#PageVal").val(Page);
                $(".PItem").hide();
                $("#item"+Page).show();
                $(".ItemBtn").show();
                CallOnce=true;
            });
            /******************************************************************************************************/

            /***************************************Text Change Event***************************************************/
            $(document).on('change',"input[type=text]",function () {

                let Page=$('#PageVal').val();
                let Id=$(this).attr('id');
                let index=Id.split('')[Id.length-1];

                let CidIo="";
                let obj=ThisPageJson.get(Page);

                if (Id !=="sDate" && Id!=="sTime"){
                    if(Page==="A" || Page==="B" || Page==="C" || Page==="D"){
                        CidIo="I";
                    }else if (Page==="H"){
                        CidIo="R";
                    }
                    else {
                        CidIo="O";
                    }
                    obj[index].CID_IO=CidIo;
                    obj[index].QUNTY=$("#Num"+Page+index).val();
                    obj[index].LOSS=$("#Last"+Page+index).val();
                    obj[index].MM_IO=$("#Dir_s"+Page+index).val();
                    obj[index].M_Nam=$("#M_Nam"+Page+index).val();
                    console.log(obj);
                }

            });
            /***********************************************************************************************************/


            /***************************************RadioBtn Change Event***************************************************/
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

                    if (ck_Class==="COLORCK_"+Page+index){
                        $(".COLORCK_"+Page+index).prop('checked',false);
                        $("#"+index+Page+id).prop('checked',true);
                    }
                }



            });
            /****************************************************************************************************************/




            function GetINIJson(idPt,INPt,sUr,Page){
                $("#wrapper").show();
                /*  console.log("http://localhost"+"/webservice/NISPWSTRAINI.php?str="+AESEnCode('sFm=IOA&idPt='+idPt+'&INPt='+INPt+"&sUr="+sUr));*/
                $.ajax("/webservice/NISPWSTRAINI.php?str="+AESEnCode('sFm=IOA&idPt='+idPt+'&INPt='+INPt+"&sUr="+sUr))
                    .done(function(data) {
                        $("#wrapper").hide();
                        let obj=JSON.parse(AESDeCode(data));

                        $("#sSave").val(obj.sSave);
                        $("#sTraID").val(obj.sTraID);
                        $("#SRANK").val(obj.JID_NSRANK);
                        $("#FSEQ_WT").val(obj.FORMSEQANCE_WT);

                        GetPageJson(Page,obj.sTraID);

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
                        console.log(obj);
                        /*if ($('#item'+Page).children().length>0){
                            return;
                        }*/
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
            function DB_WSST(Page,sTraID,json){

                let obj=JSON.parse(json);

                $.each(obj,function (index,val) {
                    if ((val.M_Nam).indexOf('&')>0){
                        val.M_Nam= encodeURI(val.M_Nam.split("").map(function (value) {
                            return  value.match(/&/)!==null?value.replace(/&/g,'＆'):value;
                        }).join(""));
                    }
                });

                let SavaJson=JSON.stringify(obj);
                $.ajax('/webservice/NISPWSSETDATA.php?str='+AESEnCode('sFm=IOA&sTraID='+sTraID+'&sPg='+Page+'&sData='+SavaJson))
                    .done(function (data) {
/*
                        let json=JSON.parse(AESDeCode(data));
*/
                    }).fail(function (XMLHttpResponse,textStatus,errorThrown) {
                    console.log(
                        "1 返回失敗,XMLHttpResponse.readyState:"+XMLHttpResponse.readyState+XMLHttpResponse.responseText+
                        "2 返回失敗,XMLHttpResponse.status:"+XMLHttpResponse.status+
                        "3 返回失敗,textStatus:"+textStatus+
                        "4 返回失敗,errorThrown:"+errorThrown
                    );
                });

            }
            function DB_SAVE(Page,sTraID,sDt,sTm,Passwd,sUr) {
                let json=JSON.stringify(ThisPageJson.get(Page));
                DB_WSST(Page,sTraID,json);
                $.ajax('/webservice/NISPWSSAVEILSG.php?str='+AESEnCode('sFm='+'IOA'+'&sTraID='+sTraID+'&sPg='+Page+'&sDt='+sDt+'&sTm='+sTm+'&PASSWD='+Passwd+'&USER='+sUr))
                    .done(function (data) {
                        let result= JSON.parse(AESDeCode(data));
                          $("#loading").hide();
                          $("#wrapper").hide();
                        if(result.response==='success'){
                            alert("儲存成功");
                            location.reload();
                        }else {
                            alert("儲存失敗重新檢查格式:"+result.message);
                        }
                    }).
                fail(function (XMLHttpResponse,textStatus,errorThrown) {
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
                            location.reload();
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
                    AddBtn_Color.clear();
                    AddBtn_IoType.clear();
                }

                $(".PItem").children().children().remove();

                $("#PageBtn").children().prop('disabled',false);
                $(".PageBtn").css({'background-color' : '','opacity' : '' ,'color':''});
                $("input[type=radio]").prop("checked",false);

                $(".PItem").hide();
                $(".ItemBtn").hide();
                CallOnce=false;
                PageINI=false;
                SerchCallBack=false;

            }

            function Serchcallback(AESobj){

             const PageArr =['A','B','C','D','E','F','G','H'];
             let obj=JSON.parse(AESDeCode(AESobj));


             /*   if (IdPt!==$("#DA_idpt").val() || InIdPt!==$("#DA_idinpt").val()){
                    alert("病人資訊以異動,請重新操作");
                    return ;
                }

             */

             /***初始化****/
             ThisPageJson.clear();
             $.each(PageArr,function (index,page) {
                 $("#item"+page).children().remove();
             });



             for(let index in obj){

                 if (PageArr.indexOf(index)>-1){
                     let arr=obj[index];
                     CreatDefaultElement.MainItem(arr,index);

                 }
             }


                $("#sDate").val(obj.DT_EXCUTE);
                $("#sTime").val(obj.TM_EXCUTE.substring(0,4));
                $("#sTraID").val(obj.sTraID);


            /*           let PatientID=obj.splice(0, 3);
                        let sTraID=PatientID[0];
                        let IdPt=PatientID[1];
                        let InIdPt=PatientID[2];


                        ThisPageJson.clear();
                        $.each(Pagearr,function (index,page) {
                              $("#item"+page).children().remove();
                          });


                        $.each(obj,function (index) {
                            let page="";
                            let PageArr=obj[index];
                            switch (index) {
                                case 0:
                                    page='A';
                                    break;
                                case 1:
                                    page='B';
                                    break;
                                case 2:
                                    page='C';
                                    break;
                                case 3:
                                    page='D';
                                    break;
                                case 4:
                                    page='E';
                                    break;
                                case 5:
                                    page='F';
                                    break;
                                case 6:
                                    page='G';
                                    break;
                                case 7:
                                    page='H';
                                    break;
                                default:
                                    break;
                            }

                           $.each(PageArr,function (i) {
                               let Obj= JSON.parse(PageArr[i]);
                               let Qty=Obj.QUNTY==='-1'?"":Obj.QUNTY;
                               ItemAction.appendItem(page,Obj.M_Nam,Obj.MM_IO,Qty,Obj.LOSS);


                               $("#sDate").val(Obj.DT);
                               $("#sTime").val(Obj.TM.substring(0,4));
                               $("#M_Nam"+page+i).prop('readonly',true);
                               $("#"+i+page+Obj.IOWAY).prop('checked',true);
                               $("#"+i+page+Obj.COLOR).prop('checked',true);

                           }) ;

                            if (PageArr.length===0){
                                ThisPageJson.set(page,[...DefaultObj]);
                            }else {
                                ThisPageJson.set(page, JSON.parse("[" + obj[index].join() + "]"));
                            }

                        });

                        SerchCallBack=true;
                        $("#sTraID").val(sTraID);
                        $("#DELBtn").prop('disabled',false);
                        $("#ISTM").hide();*/

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
            function CreatOmodal(Page,arr) {

               $.each(arr,function (index,val) {

                   if ($('#M_'+Page+index).length===0){
                       $("#OtherModalbody").append(
                           `
                               <div id="${'M_'+Page+index}" class="M_Omodal row">

                                    <div class="col-12">
                                          <input type="text" class="form-control" value="${val.M_Nam}" readonly>
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
                   }


                   if ((val.JID_MM).length>0){
                       $.each(val.JID_MM,function (i,value) {
                           $("#IOType" + Page + index).append(
                               `
                          <label style="font-size: 1.5rem;">
                               <input id="${index + Page + value.JID_KEY}" class="${'IOCK_' + Page + index}" type="radio" name="${'IOCK_' + Page + index}"  value="${index + Page +value.JID_KEY}">
                               ${value.NM_ITEM}
                          </label>
                        `
                           )

                       });

                   }

                   if ((val.JID_COLOR).length>0)
                   {
                       $.each(val.JID_COLOR,function (i,value) {
                           $("#Color" + Page + index).append(
                               `
                              <label style="font-size: 1.5rem;">
                                  <input id="${index + Page + value.JID_KEY}"  class="${'COLORCK_' + Page + index}"  type="radio"  name="${'COLORCK_' + Page + index}"  value="${index + Page +value.JID_KEY}">
                                   ${value.NM_ITEM}
                             </label>
                              `
                           );
                       })

                   }


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
        /*display: none;*/
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
        width: 100%;
        height: 100%;
        background-color: black;
        opacity: 0.5;
        z-index: 9998;
    }
    input[type=radio]{
        width: 1.5rem;
        height: 1.5rem;
    }
</style>
<body>
<div id="wrapper"></div>
<div id="loading" >請稍後<img class="loadimg" src="../../dotloading.gif"></div>
<div class="container">
         <h1>輸出入量作業</h1>
    <!----------------------------------------------------------Parametertable displaynone-------------------------------------------------------------------------->
    <div class="Parametertable">
        <input id="PageVal"     value=""  type="text"  placeholder="PageVal">       <!--標籤-->
        <input id="DA_idpt"     value=""  type="text"  placeholder="DA_idpt">       <!--病歷號-->
        <input id="DA_idinpt"   value=""  type="text"  placeholder="DA_idinpt">     <!--住院號-->
        <input id="DA_sBed"     value=""  type="text"  placeholder="DA_sBed">       <!--床號-->
        <input id="sSave"       value=""  type="text"  placeholder="sSave">         <!--存檔權限-->
        <input id="sTraID"      value=""  type="text"  placeholder="sTraID">        <!--交易序號-->
        <input id="SRANK"       value=""  type="text"  placeholder="JID_NSRANK" >
        <input id="FSEQ_WT"       value=""  type="text"  placeholder="FORMSEQANCE_WT">

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
            <button type="button" id="DELBtn" class="btn btn-primary btn-md"  data-toggle="modal" data-target="#DELModal">作廢</button>
            <button type="button" id="ReSetBtn" class="btn btn-primary btn-md"  >清除</button>
        </span>
    <!----------------------------------------------------------Patient Name-------------------------------------------------------------------------->
        <div>
            <input id="DataTxt" value="" class="form-control" type="text" readonly="readonly">
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
                        <button id="O_ConfirmBtn" type="button" class="btn btn-primary">確認</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">放棄回上一頁</button>
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
    <!----------------------------------------------------------AddItem Modal-------------------------------------------------------------------------->
</div>
</body>
</html>
