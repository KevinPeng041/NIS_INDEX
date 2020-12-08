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
            let BEDwindow,Serchwindow;
            let CreatDefultElement={
                MainElement:() =>{
                    let DefultElement=new Map();
                    DefultElement.set('A',{"len":8,"Sum_Str":"量","Last_Str":"餘"});
                    DefultElement.set('B',{"len":3,"Sum_Str":"量","Last_Str":"LO"});
                    DefultElement.set('C',{"len":3,"Sum_Str":"量","Last_Str":""});
                    DefultElement.set('D',{"len":3,"Sum_Str":"量","Last_Str":"餘"});
                    DefultElement.set('E',{"len":3,"Sum_Str":"量","Last_Str":"LO"});
                    DefultElement.set('F',{"len":3,"Sum_Str":"量","Last_Str":"LO"});
                    DefultElement.set('G',{"len":3,"Sum_Str":"量","Last_Str":""});
                    DefultElement.set('H',{"len":3,"Sum_Str":"In","Last_Str":"Out"});

                    for (let [key, value] of DefultElement.entries()) {
                        for (let i=0;i<value.len;i++){
                            $("#item"+key).append(
                                `
                                    <div id="${'Main_'+key+i}">
                                            <div  class="input-group">
                                                    <input id='${'M_Nam'+key+i}' type="text" class="form-control" >
                                                    <span class="input-group-text" >其他說明截斷字元:</span>
                                                    <input id='${'Dir_s'+key+i}'  type="text" class="Dir_s form-control" value="" readonly>
                                                    <button  class="Obtn btn btn-secondary" type="button">其他</button>
                                            </div>


                                              <div class="input-group mb-3">
                                                     <span class="input-group-text" >${value.Sum_Str+":"}</span>
                                                     <input  id="${'Num'+key+i}" type="text"  class="Num form-control">

                                                    <span  class="input-group-text" >${value.Last_Str+":"}</span>
                                                    <input id="${'Last'+key+i}" type="text" class="form-control">

                                                    <div class="input-group-prepend">
                                                       <!--  <button  class="btn btn-secondary" type="button" onclick="openOmodal('${key+i}')">其他</button>
                                                       <button id="" class="btn btn-info" type="button">查詢</button>-->
                                                    </div>
                                               </div>
                                    </div>
                                        `);


                            if(key==="C" || key==="G"){
                                $("#Last"+key+i).prev().hide();
                                $("#Last"+key+i).hide();
                            }
                        }
                    }

                },
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
                            errorModal(
                                "1 返回失敗,XMLHttpResponse.readyState:"+XMLHttpResponse.readyState+XMLHttpResponse.responseText+
                                "2 返回失敗,XMLHttpResponse.status:"+XMLHttpResponse.status+
                                "3 返回失敗,textStatus:"+textStatus+
                                "4 返回失敗,errorThrown:"+errorThrown
                            );
                        }
                    });
                }
            };
            let ItemAction={
                appendItem:(page,ItemName,Dir_s,Num,Last)=>{
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
                                               <div  class="input-group">
                                                    <input id="${'M_Nam'+page+index}" type="text" class="form-control" value="${ItemName}">
                                                    <span class="input-group-text" >其他說明截斷字元:</span>
                                                    <input id="${'Dir_s'+page+index}" type="text" class="form-control" value="${Dir_s}" readonly>
                                                    <button  class="Obtn btn btn-secondary" type="button">其他</button>
                                               </div>


                                              <div class="input-group mb-3">
                                                     <span class="input-group-text" >${Sum_Str+":"}</span>
                                                     <input  id="${'Num'+page+index}" type="text" class="Num form-control" value="${Num}">

                                                    <span  class="input-group-text" >${Last_Str+":"}</span>
                                                    <input   id="${'Last'+page+index}" type="text" class="Last form-control"  value="${Last}">

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
            let CallOnce=false;
            let PageINI=false;
            let SerchCallBack=false;
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
                CreatDefultElement.TimeRadio();
                $("#loading").hide();
                $("#wrapper").hide();
                $("#PageBtn").children().prop('disabled',true);
                $("#SubmitBtn").prop('disabled',true);
                $("#SerchBtn").prop('disabled',true);
                $("#DELBtn").prop('disabled',true);
            })();

            $(document).on('click','button',function () {
                let btnId=$(this).attr('id');
                let Page=$("#PageVal").val();
                let sTraID=$("#sTraID").val();
                let sTM=$("#sTime").val();
                let sDt=$("#sDate").val();
                let IdPt=$("#DA_idpt").val(),InPt=$("#DA_idinpt").val(),PName=$("#DataTxt").val();
                if ($(this).attr('class')==='Obtn btn btn-secondary'){
                    //other btn
                   let FatherEle=$(this).parent().parent();
                   let index=FatherEle.attr('id').substring(6,FatherEle.attr('id').length);
                    $("#O_"+Page+index).val($("#Dir_s"+Page+index).val());

                    CreatOmodal(Page,AddBtn_IoType.get(Page),AddBtn_Color.get(Page),$("#item"+Page).children().length,"");
                    OpenOmodal(Page,index);

                }

                switch (btnId) {
                    case "sbed":
                        switch (checkBEDwindow()) {
                            case "false":
                                errorModal("責任床位視窗已開啟");
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
                                    ,"查詢",'width=750px,height=650px,scrollbars=yes,resizable=no');
                                break;
                        }

                        Serchwindow.Serchcallback=Serchcallback;
                        break;
                    case "AddItemBtn":

                        ItemAction.appendItem(Page,'','','','');
                        CreatOmodal(Page, AddBtn_IoType.get(Page),AddBtn_Color.get(Page),1,"A");
                        let CLASS=ThisPageJson.get(Page)[0].CID_CLASS;
                        let Cid_io=ThisPageJson.get(Page)[0].CID_IO;
                        let IoType=ThisPageJson.get(Page)[0].IO_TYPE;

                        let emptyObj={
                            DataSeq:"",
                            CID_CLASS: CLASS,
                            CID_IO: Cid_io,
                            COLOR: "",
                            IO_TYPE: IoType,
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
                        ThisPageJson.get(Page).push(emptyObj);
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
                        console.log(sTraID,sDt,sTM);
                        DB_SAVE(Page,sTraID,sDt,sTM,'','<?php echo $Account?>');
                        break;
                    case "DELBtn":
                        DB_DEL(sTraID,'<?php echo $Account?>');
                        break;
                    default:
                        break;
                }
            });
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

                $("#IDTM").val($(this).attr('id'));
                $("#sDate").val(yyyy-1911+MM+dd);
                $("#sTime").val(timerVal);

            });
            $(document).on('change',"input[type=checkbox]",function () {


                let index=$(this).val().substring(0,1);
                let Page=$(this).val().substring(1,2);
                let id=$(this).val().substring(2,14);
                let ck_Class=$(this).attr('class');


                if (ck_Class==="IOCK_"+Page+index){
                    $(".IOCK_"+Page+index).prop('checked',false);
                    $("#"+index+Page+id).prop('checked',true);
                }

                if (ck_Class==="COLORCK_"+Page+index){
                    $(".COLORCK_"+Page+index).prop('checked',false);
                    $("#"+index+Page+id).prop('checked',true);
                }

            });
            $(document).on('change',"input[type=text]",function () {

                let Page=$('#PageVal').val();
                let Id=$(this).attr('id');
                let index=Id.split('')[Id.length-1];
                let TypeID="";
                let CidIo="";
                let obj=ThisPageJson.get(Page);
                if (Id !=="sDate" && Id!=="sTime"){
                    if(Page==="A" || Page==="B" || Page==="C" || Page==="D"){
                        TypeID="I"+Page;
                        CidIo="I";
                    }else {
                        TypeID="O"+Page;
                        CidIo="O";
                    }

                    obj[index].IO_TYPE="IOTP000000"+TypeID;
                    obj[index].CID_IO=CidIo;
                    obj[index].QUNTY=$("#Num"+Page+index).val();
                    obj[index].LOSS=$("#Last"+Page+index).val();
                    obj[index].MM_IO=$("#Dir_s"+Page+index).val();
                    obj[index].M_Nam=$("#M_Nam"+Page+index).val();

                }
                console.log(obj);

            });
            $(document).on('keydown',"input[type=text]",function(){
               let id=$(this).attr('id');

               let Page=$("#PageVal").val();
                 if (id.substring(0,3)==="Num" && Page==="B" || Page==="E"  || Page==="F"){
                     $("#Last"+id.substring(3,id.length)).val("");
                 }
                 if (id.substring(0,4)==="Last" && Page==="B"  || Page==="E"  || Page==="F"){
                     $("#Num"+id.substring(4,id.length)).val("");
                 }
            });

            //page on
            $(".PageBtn").on('click',function () {
                let Page=$(this).attr('id');
                let IdPt=$("#DA_idpt").val(), IdinPt=$("#DA_idinpt").val(),sUr="<?php echo $Account?>",sTraID=$("#sTraID").val();

                if (PageINI===true ){
                    let Cloen_DefaultObj=[...DefaultObj];
                    if(SerchCallBack===false){
                        //搜尋後加入
                        Cloen_DefaultObj[0].JID_MM=[];
                        Cloen_DefaultObj[0].JID_COLOR=[];
                    }

                    GetPageJson(Page,sTraID,Cloen_DefaultObj);
                    for (let e of ThisPageJson.entries()){
                        DB_WSST(e[0],sTraID,JSON.stringify(e[1]));
                    }
                }

                if (CallOnce===false){
                    /*Get INI Json to FistTime*/
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

            function GetINIJson(idPt,INPt,sUr,Page){
                $("#wrapper").show();
                /*  console.log("http://localhost"+"/webservice/NISPWSTRAINI.php?str="+AESEnCode('sFm=IOA&idPt='+idPt+'&INPt='+INPt+"&sUr="+sUr));*/
                $.ajax("/webservice/NISPWSTRAINI.php?str="+AESEnCode('sFm=IOA&idPt='+'00055664'+'&INPt='+'970000884'+"&sUr="+'00FUZZY'))
                    .done(function(data) {
                        $("#wrapper").hide();
                        let obj=JSON.parse(AESDeCode(data));
                        $("#sSave").val(obj.sSave);
                        $("#sTraID").val(obj.sTraID);
                        let Cloen_DefaultObj=[...DefaultObj];
                        Cloen_DefaultObj[0].JID_MM=[];
                        Cloen_DefaultObj[0].JID_COLOR=[];
                        GetPageJson(Page,obj.sTraID,Cloen_DefaultObj);
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



            function GetPageJson(Page,sTraID,DefaultObj) {
/*
                console.log("http://localhost/webservice/NISPWSGETPRE.php?str="+AESEnCode("sFm=IOA&sTraID="+sTraID+"&sPg="+Page));
*/
                $.ajax("/webservice/NISPWSGETPRE.php?str="+AESEnCode("sFm=IOA&sTraID="+sTraID+"&sPg="+Page))
                    .done(function (data) {
                        try{
                            let obj=JSON.parse(AESDeCode(data));
                            console.log(obj);
                            if (obj==null ||obj.length===0){
                                obj=DefaultObj;
                            }

                            CreatOmodal(Page,obj[0].JID_MM,obj[0].JID_COLOR,$("#item"+Page).children().length,"");

                            AddBtn_Color.set(Page,obj[0].JID_COLOR);
                            AddBtn_IoType.set(Page,obj[0].JID_MM);

                            if(ThisPageJson.get(Page)===undefined){
                                $.each(obj,function (index,val) {

                                    $("#M_Nam"+Page+index).val(val.M_Nam);
                                    if(val.JID_KEY!==""){
                                        $("#M_Nam"+Page+index).prop('disabled',true);
                                    }
                                    if(val.IO_TYPE)
                                        delete val.JID_MM;
                                    delete val.JID_COLOR;
                                });
                                ThisPageJson.set(Page,obj);
                            }
                        }
                        catch (e) {
                            console.log(e);
                        }
                    }).fail(function(XMLHttpResponse,textStatus,errorThrown) {
                    console.log(
                        "1 返回失敗,XMLHttpResponse.readyState:"+XMLHttpResponse.readyState+XMLHttpResponse.responseText+
                        "2 返回失敗,XMLHttpResponse.status:"+XMLHttpResponse.status+
                        "3 返回失敗,textStatus:"+textStatus+
                        "4 返回失敗,errorThrown:"+errorThrown
                    );
                });

              /*  $.ajax({
                    url:"/webservice/NISPWSGETPRE.php?str="+AESEnCode("sFm=IOA&sTraID="+sTraID+"&sPg="+Page),
                    async:false,
                    type:'POST',
                    dataType:"text",
                    success:function (data){
                        try{
                            let obj=JSON.parse(AESDeCode(data));
                            console.log(obj);
                            if (obj==null ||obj.length===0){
                                obj=DefaultObj;
                            }

                            CreatOmodal(Page,obj[0].JID_MM,obj[0].JID_COLOR,$("#item"+Page).children().length,"");

                            AddBtn_Color.set(Page,obj[0].JID_COLOR);
                            AddBtn_IoType.set(Page,obj[0].JID_MM);

                            if(ThisPageJson.get(Page)===undefined){
                                $.each(obj,function (index,val) {

                                    $("#M_Nam"+Page+index).val(val.M_Nam);
                                    if(val.JID_KEY!==""){
                                        $("#M_Nam"+Page+index).prop('disabled',true);
                                    }
                                    if(val.IO_TYPE)
                                    delete val.JID_MM;
                                    delete val.JID_COLOR;
                                });
                                ThisPageJson.set(Page,obj);
                            }
                        }
                        catch (e) {
                            console.log(e);
                        }

                    },error:function (XMLHttpResponse,textStatus,errorThrown) {
                        errorModal(
                            "1 返回失敗,XMLHttpResponse.readyState:"+XMLHttpResponse.readyState+XMLHttpResponse.responseText+
                            "2 返回失敗,XMLHttpResponse.status:"+XMLHttpResponse.status+
                            "3 返回失敗,textStatus:"+textStatus+
                            "4 返回失敗,errorThrown:"+errorThrown
                        );
                    }
                });*/

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

/*
                console.log("http://localhost"+'/webservice/NISPWSSETDATA.php?str='+AESEnCode('sFm=IOA&sTraID='+sTraID+'&sPg='+Page+'&sData='+SavaJson));
*/

            $.ajax('/webservice/NISPWSSETDATA.php?str='+AESEnCode('sFm=IOA&sTraID='+sTraID+'&sPg='+Page+'&sData='+SavaJson))
                .done(function (data) {
                    let json=JSON.parse(AESDeCode(data));
                    console.log(json.message);
                }).fail(function (XMLHttpResponse,textStatus,errorThrown) {
                console.log(
                    "1 返回失敗,XMLHttpResponse.readyState:"+XMLHttpResponse.readyState+XMLHttpResponse.responseText+
                    "2 返回失敗,XMLHttpResponse.status:"+XMLHttpResponse.status+
                    "3 返回失敗,textStatus:"+textStatus+
                    "4 返回失敗,errorThrown:"+errorThrown
                );
            });
              /*  $.ajax({
                    'url':'/webservice/NISPWSSETDATA.php?str='+AESEnCode('sFm=IOA&sTraID='+sTraID+'&sPg='+Page+'&sData='+SavaJson),
                    type:"POST",
                    dataType:"text",
                    success:function(data){
                        let json=JSON.parse(AESDeCode(data));
                        console.log(json.message);
                    },
                    error:function (XMLHttpResponse,textStatus,errorThrown) {
                        console.log(
                            "1 返回失敗,XMLHttpResponse.readyState:"+XMLHttpResponse.readyState+XMLHttpResponse.responseText+
                            "2 返回失敗,XMLHttpResponse.status:"+XMLHttpResponse.status+
                            "3 返回失敗,textStatus:"+textStatus+
                            "4 返回失敗,errorThrown:"+errorThrown
                        );
                        return false;
                    }

                });*/
            }
            function DB_SAVE(Page,sTraID,sDt,sTm,Passwd,sUr) {
                let json=JSON.stringify(ThisPageJson.get(Page));

               /* console.log("http://localhost"+'/webservice/NISPWSSAVEILSG.php?str='+AESEnCode('sFm='+'IOA'+'&sTraID='+sTraID+'&sPg='+Page+'&sDt='+sDt+'&sTm='+sTm+'&PASSWD='+Passwd+'&USER='+sUr));*/
                DB_WSST(Page,sTraID,json);
                $.ajax('/webservice/NISPWSSAVEILSG.php?str='+AESEnCode('sFm='+'IOA'+'&sTraID='+sTraID+'&sPg='+Page+'&sDt='+sDt+'&sTm='+sTm+'&PASSWD='+Passwd+'&USER='+sUr))
                    .done(function (data) {
                        let result= JSON.parse(AESDeCode(data));
                        /*$("#loading").hide();
                          $("#wrapper").hide();*/
                        if(result.response==='success'){
                            alert("儲存成功");
                            location.reload();
                            window.location.reload(true);
                        }else {
                            console.log("儲存失敗重新檢查格式:"+result.message);
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
              /*   console.log("http://localhost/webservice/NISPWSDELILSG.php?str="+AESEnCode("sFm="+'IOA'+"&sTraID="+sTraID+"&sPg="+""+"&sCidFlag=D"+"&sUr="+sUr));*/
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

                $(".PItem").children().children();
                CreatDefultElement.MainElement();

                $("#PageBtn").children().prop('disabled',false);
                $(".PageBtn").css({'background-color' : '','opacity' : '' ,'color':''});

                $(".PItem").hide();
                $(".ItemBtn").hide();
                CallOnce=false;
                PageINI=false;
                SerchCallBack=false;

            }
            function Serchcallback(AESobj){
                let obj=JSON.parse(AESDeCode(AESobj));

                let sTraID=obj.splice(-1, 1);

              $("#sTraID").val(sTraID);
                console.log(obj);
                const Pagearr =['A','B','C','D','E','F','G','H'];
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
                $("#DELBtn").prop('disabled',false);
                $("#ISTM").hide();
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
            function CreatOmodal(Page,JMM_arr,JColor_arr,len,mode) {
                for (let i=0;i<len;i++){
                    if(mode==="A"){
                       i=$("#item"+Page).children().length-1;
                    }
                    if ($('#M_'+Page+i).length===0){

                $("#OtherModalbody").append(
                            `
                       <div id="${'M_'+Page+i}" class="M_Omodal row">

                            <div class="col-12">
                                  <input type="text" class="form-control" >
                            </div>

                            <div class="col-12">
                                <div class="form-group">
                                     <label for="${'O_'+Page+i}" style="color: #0f6674">備註:</label>
                                     <textarea class="form-control rounded-0" id="${'O_'+Page+i}" rows="10"></textarea>
                                </div>
                            </div>

                            <div id="${'IOType'+Page + i}" class="col-12" >
                                <div>
                                    <label style="color: #0f6674">方式:</label>

                                </div>
                            </div>

                            <div id="${'Color'+Page + i}" class="col-12">
                                <div>
                                    <label style="color: #0f6674">顏色:</label>

                                </div>
                            </div>


                        </div>
                          `
                        );

                        $.each(JMM_arr,function (index,val) {
                            $("#IOType" + Page + i).append(
                            `
                              <label><input id="${i + Page + val.JID_KEY}" class="${'IOCK_' + Page + i}" type="checkbox" name="${'IOCK_' + Page + i}"  value="${i + Page +val.JID_KEY}">${val.NM_ITEM}</label>
                              `
                            );
                        });

                        $.each(JColor_arr,function (index,val) {
                            $("#Color" + Page + i).append(
                                `
                              <label><input id="${i + Page + val.JID_KEY}"  class="${'COLORCK_' + Page + i}"  type="checkbox"  name="${'COLORCK_' + Page + i}"  value="${i + Page +val.JID_KEY}">${val.NM_ITEM}</label>
                              `
                            );
                        });


                    }



                }

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
   #thirdClass{
        background-color: white;
        margin-bottom: 9px;
       font-weight: bold;
    }
   #thirdClass:hover{
     border-color: #0f6674;
    background-color: #0f6674;
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
       /* display: none;*/
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

    input[type=checkbox]{
        width: 3.5vmin;
        height: 3.5vmin;
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
            <button type="button" id="ReSet" class="btn btn-primary btn-md"  >清除</button>
        </span>
    <!----------------------------------------------------------Patient Name-------------------------------------------------------------------------->
        <div>
            <input id="DataTxt" value="" class="form-control" type="text" readonly="readonly">
        </div>
    <!----------------------------------------------------------Time-------------------------------------------------------------------------->
        <div class="Otimer" >

<!--            <div  class="DateTime input-group">
                <label >評估時間:</label>
                <input id='sDate' type="text" class="form-control" value=""  placeholder="YYYMMDD" maxlength="7" autocomplete="off">
                <input id='sTime'  type="text" class="form-control" value=""  placeholder="HHMM" maxlength="4" autocomplete="off">
                <button id="thirdClass"  class="btn btn-outline-primary " type="button">三班</button>
            </div>-->

      <div class="DateTime">
                <label >評估時間:</label>
                <input type="text" id="sDate" value=""  placeholder="YYYMMDD" maxlength="7" autocomplete="off">
                <input type="text" id="sTime" value=""  placeholder="HHMM" maxlength="4" autocomplete="off">
                <button type="button" id="thirdClass" class="btn btn-outline-primary  btn-lg">三班</button>
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
</div>
</body>
</html>
