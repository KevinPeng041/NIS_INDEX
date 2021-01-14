<?php
include '../../NISPWSIFSCR.php';
$str=$_GET['str'];
$replaceSpace=str_replace(' ','+',$str);
$EXPLODE_data=explode('&',AESDeCode($replaceSpace));

$IdPt_STR=$EXPLODE_data[0];
$IdInPt_STR=$EXPLODE_data[1];

$sUr_STR=$EXPLODE_data[2];
$nM_STR=$EXPLODE_data[3];

$IdPt_value=explode('=',$IdPt_STR);
$IdInPt_value=explode('=',$IdInPt_STR);

$sUr_value=explode('=',$sUr_STR);
$nM_value=explode('=',$nM_STR);

$IdPt=$IdPt_value[1];
$IdInPt=$IdInPt_value[1];

/*$sUr=$sUr_value[1];*/
$nM_P=$nM_value[1];
$sUr='00FUZZY';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>輸出入量(三班)表單</title>
    <script type="text/javascript" src="../../jquery-3.4.1.js"></script>
    <link rel="stylesheet" href="../../bootstrap-4.3.1-dist/css/bootstrap.min.css">
    <script src="../../bootstrap-4.3.1-dist/js/bootstrap.min.js" type="text/javascript"></script>
    <script src="../../crypto-js.js"></script>
    <script src="../../AESCrypto.js"></script>


</head>
<script>
    $(document).ready(function () {

        (function () {
            GetINIJson("<?php echo $IdPt?>","<?php echo $IdInPt?>","<?php echo $sUr?>");
        })();

        var Serchwindow;
        let Save_Obj="";
        $(".tb3_tr").children().css({'width': '93px','height': '30px'});

        $(document).on('change','input[type=radio]',function () {
            Save_Obj={
                CID_EXCUTE:$(this).val(),
                sDT:$("#sDate").val()
            };
            DB_WSST("C",$("#sTraID").val(),JSON.stringify(Save_Obj));
        });
        $(document).on('click','button',function () {
            let Btn_ID=$(this).attr('id');

            let IdPt="<?php echo $IdPt?>",InPt="<?php echo $IdInPt?>",sUser='<?php echo $sUr?>',TransKEY=$("#sTraID").val();
            const arr=['I','O','IO_Sum','OC'];

            switch (Btn_ID) {
                case "SubmitBtn":
                    DB_WSST("C",TransKEY,JSON.stringify(Save_Obj));

                    DB_SAVE("C",TransKEY,$("#sDate").val(),"","",sUser);
                    break;
                case "SearchBtn":
                    if (checkSerchwindow()===true)
                    {
                        Serchwindow = window.open("/webservice/NISPWSLKQRY.php?str=" +
                        AESEnCode("sFm=IOA_C&PageVal=" + "" + "&DA_idpt=" + IdPt + "&DA_idinpt=" + InPt
                            + "&sUser=" + sUser + "&NM_PATIENT=" + '')
                        , "IOAC", 'width=750px,height=650px,scrollbars=yes,resizable=no');
                        Serchwindow.Serchcallback = Serchcallback;
                    }
                    else
                    {
                        alert("查詢視窗已開啟");
                    }
                    break;
                case "DELBtn":
                    DB_DEL("C",TransKEY,sUser);
                    break;
                default:
                    break;
            }

            if ($(this).attr('class')==="Page btn btn-primary btn-lg"){
                let Page=$(this).val();
                $(".itemBtn > button").css({'background-color' : '','opacity' : '' ,'color':''});
                $(this).css({'background-color' : '#EEEE00', 'opacity' : '' ,'color':'black'});
                arr.forEach(index=>$('.'+index).hide());
                $("."+Page).show();
            }
        });

        function DB_WSST(Page,sTraID,json){
            $.ajax('/webservice/NISPWSSETDATA.php?str='+AESEnCode('sFm=IOA_C&sTraID='+sTraID+'&sPg='+Page+'&sData='+json))
                .done(function (data) {
                    let json=JSON.parse(AESDeCode(data));
                    console.log(json);
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

            $.ajax('/webservice/NISPWSSAVEILSG.php?str='+AESEnCode( 'sFm='+'IOA_C'+'&sTraID='+sTraID+'&sPg='+Page+'&sDt='+sDt+'&sTm='+sTm+'&PASSWD='+Passwd+'&USER='+sUr))
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
        function DB_DEL(Page,sTraID,sUr) {
            $.ajax("/webservice/NISPWSDELILSG.php?str="+AESEnCode("sFm="+'IOA_C'+"&sTraID="+sTraID+"&sPg="+Page+"&sCidFlag=D"+"&sUr="+sUr))
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
        function GetINIJson(IdPt,InIdPt,sUr){
            $.ajax("/webservice/NISPWSTRAINI.php?str="+AESEnCode('sFm=IOA_C&idPt='+IdPt+'&INPt='+InIdPt+"&sUr="+sUr))
                .done(function(data) {
                    let obj=JSON.parse(AESDeCode(data));
                    $("#sTraID").val(obj.sTraID);
                    GetPREJson('A',obj.sTraID);//radio Time
                    GetPREJson('B',obj.sTraID);//default Table
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
        function GetPREJson(Page,sTraID) {
            $.ajax("/webservice/NISPWSGETPRE.php?str="+AESEnCode("sFm=IOA_C&sTraID="+sTraID+"&sPg="+Page))
                .done(function (data) {
                    let obj=JSON.parse(AESDeCode(data));

                    //Print RadioButton Time
                    if (Page==="A"){
                        $.each(obj,function (index,val)
                        {


                            $(".T_Class").append(`
                               <label>
                                    <input type="radio" value="${val.CID_S}" name="ClassDt" >${val.NM_ITEM}
                                </label>
                                `);

                            if (index===obj.length-1){

                                $(".T_Class").append(`
                                       <label>
                                                 <input type="radio" value="I" name="ClassDt" >24小時
                                        </label>
                                    `);

                            }

                        });

                    }

                    //Crete Table and Data(ST_DATAB)
                    if (Page==="B"){
                        let AllTime={'Start':'24小時','End':'','IO':'I'};
                        let Time=new Map();
                        let TimeNow=new Date();
                        let yyyy=TimeNow.toLocaleDateString().slice(0,4);
                        let MM=(TimeNow.getMonth()+1<10?'0':'')+(TimeNow.getMonth()+1);
                        let dd=(TimeNow.getDate()<10?'0':'')+TimeNow.getDate();

                        let sDt=yyyy-1911+MM+dd;


                        console.log(obj);
                        Time.set('Time',obj.TmSTtoE);
                        delete obj.TmSTtoE;
                        delete obj.SB;
                        CreatEle(obj,Time,sDt,AllTime);
                    }
                }).fail(function(XMLHttpResponse,textStatus,errorThrown) {
                console.log(
                    "1 返回失敗,XMLHttpResponse.readyState:"+XMLHttpResponse.readyState+XMLHttpResponse.responseText+
                    "2 返回失敗,XMLHttpResponse.status:"+XMLHttpResponse.status+
                    "3 返回失敗,textStatus:"+textStatus+
                    "4 返回失敗,errorThrown:"+errorThrown
                );
            });
        }
        function Serchcallback(AESobj) {
            let obj = JSON.parse(AESDeCode(AESobj));
            let Data_Json=JSON.parse(obj.Data);
            let sTraID=obj.sTraID;
            let AllTime={'Start':'24小時','End':'','IO':'I'};
            let Time=new Map();
            console.log(Data_Json);

            Time.set('Time',Data_Json.TmSTtoE);
            delete Data_Json.TmSTtoE;
            delete Data_Json.SB;

            let keys=[];
            let objs={};
            for (let i in Data_Json){
                keys.push(i);
            }

            keys.sort();

            for (let index of keys){
                objs[index]=Data_Json[index];

            }

            let sDt="";
             for (let index in Data_Json){
                if (Data_Json[index].length>0){
                    sDt=Data_Json[index][0].DT;
                    break;
                }
            }

            CreatEle(objs,Time,sDt,AllTime);
            $("#sTraID").val(sTraID);
            $("input[type=radio]").prop('checked',false);
            $(".IO_Sum").show();
        }
        function CreatEle(obj,Time,sDt,AllTime) {
            const arr=['D','N','M','I'];
            let QT_Sum=[[],[],[]];//總量

            for (let index of arr){
                $(".tb1"+index).remove();
                $(".tb2"+index).remove();
                $(".IO_Sum_tb"+index).remove();

            }
            $(".tb4_b tr:not(.tb4_title)").remove(); //詳細內容*/
            $("#tb3 > tr:gt(1)").remove(); //OC
            $("#sDate").val(sDt);
              if(isEmpty(obj))  {
                  console.log("查無資料");
                  return ;
              }


            Time.get('Time').push(AllTime);
            $.each(Time.get('Time'),function (index,val) {
                let Ts=val.Start;
                let Te=val.End;
                let IO_id=val.IO;
                if (index<3){
                    Ts=(val.Start).substring(0,2)+"-";
                    Te=(val.End).substring(0,2);
                }

                $(".tb1").append(
                    `
                <tr class="${'tb1'+IO_id}">
                        <td>${Ts+Te}</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td style="display: none"></td>
                        <td></td>
                    </tr>
                `
                );

                $(".tb2").append(
                    `
                <tr class="${'tb2'+IO_id}">
                        <td>${Ts+Te}</td>
                        <td></td>

                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                `
                );


                $(".IO_Sum_tb").append(
                    `
                <tr class="${'IO_Sum_tb'+IO_id}">
                        <td>${Ts+Te}</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                `
                );
            });

            ComFirmUserNm(obj.ComUser);
            delete obj.ComUser;


            for (let index in obj){

                let arr=obj[index];
                let DSum_QTY=0;

                let NSum_QTY=0;

                let MSum_QTY=0;

                $.each(arr,function (i,val) {
                    let QTY=isNaN(parseInt(val.QUANTITY))?val.ST_LOSS:val.QUANTITY;
                    let CID_EXC=val.CID_EXCUTE;//早D,晚N,夜M


                    if (QTY === 'NaN'  || QTY === null){
                        QTY=0;
                    }

                    if (CID_EXC==="D"){
                        DSum_QTY+=parseInt(QTY);

                    }else  if(CID_EXC==="N"){
                        NSum_QTY+=parseInt(QTY);

                    }else if (CID_EXC==="M"){
                        MSum_QTY+=parseInt(QTY);

                    }
                });

                QT_Sum[0].push(DSum_QTY);
                QT_Sum[1].push(NSum_QTY);
                QT_Sum[2].push(MSum_QTY);
            }
            QT_Sum.forEach((value, index) =>InsertTdValue(value,index));
            InsertSumTdValue(QT_Sum,obj);

            OCAppend(obj.OC,Time);
            DeTailAppend(obj);

            $("td").each(function () {
                if ($(this).text()==="0" || $(this).text()==="NaN")
                {
                    $(this).text(" ");
                }
            });
        }

        function OCAppend(arr,Time){
            $.each(Time.get('Time'),function (index,val) {
                let Ts=val.Start;
                let Te=val.End;
                if (index<3){
                    Ts=(val.Start).substring(0,2)+"-";
                    Te=(val.End).substring(0,2);
                }
                $(".tb3_tr td:nth-child("+(index+1)+")").text(Ts+Te);
            });
            $.each(arr,function (index) {
                $("#tb3").append(
                    `
                    <tr class="${'OC'+index}">
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    `
                );


            });

            InsertOC_TdValue(arr);
        }
        function DeTailAppend(obj) {
            let DateTimeK=[];

            for (let index in obj) {
                let arr = obj[index];

                if (arr.length > 0) {

                    arr.forEach(element=>DateTimeK.push(element.DT + element.TM));
                }

            }
            const DT_KEY=DateTimeK.filter((element, index, arr)=> arr.indexOf(element)===index);

            $.each(DT_KEY,function (index,val) {
                let dt=val.substring(0,11);
                let DateTime=dt.substring(0,3)+'/'+dt.substring(3,5)+'/'+dt.substring(5,7)+
                    ' '+dt.substring(7,9)+':'+dt.substring(9,11);

                //判斷同時段,同ID_ITEM的量 判斷DT=class

                $(".tb4_b").append(
                    `
                        <tr class="${val}">
                            <td>${DateTime}</td>
                            <td></td>
                        </tr>
                        `
                );

                $("."+val+" td:nth-child(2)").text(GetSameTimeData(obj,val));

            });
        }
        /**
         * @return {string}
         */
        function GetSameTimeData(obj,sDT) {

            let A_Map=new Map();
            let D_Map=new Map();
            A_Map.clear();

            for (let index in obj){
                let arr=obj[index];
                if (arr.length>0){

                    const Filter_Arr=arr.filter(value=>(value.DT+value.TM)===sDT);

                    if(Filter_Arr.length>0){
                        $.each(Filter_Arr,function (index,val) {
                            let DeTail_str="";
                            let NM_ITEM=(val.NM_PHARMACY).trim()===""?val.NM_ITEM:val.NM_PHARMACY;
                            let Qty=val.QUANTITY===null||val.QUANTITY===""?val.ST_LOSS:val.QUANTITY;
                            let Loss=(val.ST_LOSS).trim();
                            let NM_IOWAY=val.NM_IOWAY;
                            let NM_COLOR=val.NM_COLOR;
                            let MM_IO=val.MM_IO;




                            /*****************判斷是否有相同的名稱數值相加*********************************/
                            if (A_Map.get(NM_ITEM)){
                                A_Map.set(NM_ITEM, (parseInt(A_Map.get(NM_ITEM))+parseInt(Qty))*-1);
                            }else {

                                A_Map.set(NM_ITEM,Qty);
                            }
                            /**************************************************************************/
                            if (Loss){
                                DeTail_str+='LOSS'+Loss;
                            }
                            if (NM_IOWAY){
                                DeTail_str+=NM_IOWAY+' ';
                            }
                            if (NM_COLOR){
                                DeTail_str+=NM_COLOR;
                            }
                            if (MM_IO){
                                DeTail_str+=MM_IO;
                            }

                            D_Map.set(NM_ITEM,DeTail_str);


                        });

                    }
                }

            }
            let str=[];

            for (let [key,value] of A_Map){
                str.push(key+' '+value+' '+D_Map.get(key));

            }
            return str.join('、');
        }


        function InsertTdValue(arr,index) {
            let I=arr.slice(0,6);

            let O=arr.slice(6,13);
            let I_Sum=I.reduce((acc,cur)=>acc+cur);
            let O_Sum=O.reduce((acc,cur)=>acc+cur);

            let IO_Tag='';
            if (index===0){
                IO_Tag='D';
                I.forEach((value,N_index)=>$('.tb1D').find('td:eq('+(N_index+1)+')').text(value.toString()));
                O.forEach((value,N_index)=>$('.tb2D').find('td:eq('+(N_index+1)+')').text(value.toString()));
            }else if (index===1){
                IO_Tag='N';
                I.forEach((value,N_index)=>$('.tb1N').find('td:eq('+(N_index+1)+')').text(value.toString()));
                O.forEach((value,N_index)=>$('.tb2N').find('td:eq('+(N_index+1)+')').text(value.toString()));
            }else {
                IO_Tag='M';
                I.forEach((value,N_index)=>$('.tb1M').find('td:eq('+(N_index+1)+')').text(value.toString()));
                O.forEach((value,N_index)=>$('.tb2M').find('td:eq('+(N_index+1)+')').text(value.toString()));
            }

            $(".IO_Sum_tb"+IO_Tag).find('td:eq('+(1)+')').text(I_Sum);
            $(".IO_Sum_tb"+IO_Tag).find('td:eq('+(2)+')').text(O_Sum);
            $(".IO_Sum_tb"+IO_Tag).find('td:eq('+(3)+')').text(O_Sum-I_Sum);

        }
        function InsertOC_TdValue(arr){

            $.each(arr,function (index,val) {
                let IO=val.CID_EXCUTE;


                $('.OC'+index).find('td:eq(0)').text(val.NM_PHARMACY);
                if (IO==="D"){
                    $('.OC'+index).find('td:eq(1)').text(val.QUANTITY);
                }else if (IO==="N"){
                    $('.OC'+index).find('td:eq(2)').text(val.QUANTITY);
                }else if(IO==="M"){
                    $('.OC'+index).find('td:eq(3)').text(val.QUANTITY);
                }
                let D=isNaN(parseInt($('.OC'+index).find('td:eq(1)').text()))?0:parseInt($('.OC'+index).find('td:eq(1)').text());
                let N=isNaN(parseInt($('.OC'+index).find('td:eq(2)').text()))?0:parseInt($('.OC'+index).find('td:eq(2)').text());
                let M=isNaN(parseInt($('.OC'+index).find('td:eq(3)').text()))?0:parseInt($('.OC'+index).find('td:eq(3)').text());


                $('.OC'+index+' td:nth-child(5)').text(D+N+M);
            });





        }
        function InsertSumTdValue(arr,obj) {

            //輸入
            for (let i=1;i<=6;i++){
                let D=parseInt($('.tb1D').find('td:eq('+i+')').text());
                let N=parseInt($('.tb1N').find('td:eq('+i+')').text());
                let M=parseInt($('.tb1M').find('td:eq('+i+')').text());

                $(".tb1I").find('td:eq('+i+')').text((D+N+M).toString());
            }

            //輸出
            for (let i=1;i<=7;i++){
                let D=parseInt($('.tb2D').find('td:eq('+i+')').text());
                let N=parseInt($('.tb2N').find('td:eq('+i+')').text());
                let M=parseInt($('.tb2M').find('td:eq('+i+')').text());

                $(".tb2I").find('td:eq('+i+')').text((D+N+M).toString());
            }

            //總量
            for (let i=1;i<=3;i++){
                let D=parseInt($('.IO_Sum_tbD').find('td:eq('+i+')').text());
                let N=parseInt($('.IO_Sum_tbN').find('td:eq('+i+')').text());
                let M=parseInt($('.IO_Sum_tbM').find('td:eq('+i+')').text());


                $(".IO_Sum_tbI").find('td:eq('+i+')').text(D+N+M);
            }




            //有LOSS值
            let count=0;
            for (let index in obj){
                $.each(obj[index],function (i,val) {
                    if (val.ST_LOSS){

                        if (index==='IB'){
                           let IB_str=$('.tb1'+val.CID_EXCUTE).find('td:eq('+(count+1)+')').text();
                            $('.tb1'+val.CID_EXCUTE).find('td:eq('+(count+1)+')').text(IB_str);
                            $('.tb1S').find('td:eq('+(2)+')').text(IB_str);
                        }
                        if(index==='OB'){
                            let OB_str=$('.tb2'+val.CID_EXCUTE).find('td:eq('+(2)+')').text();

                            $('.tb2'+val.CID_EXCUTE).find('td:eq('+(2)+')').text(OB_str);
                            $('.tb2S').find('td:eq('+(2)+')').text(OB_str);
                        }

                    }
                });
                count++;
            }


        }

        function ComFirmUserNm(str) {
            let obj=JSON.parse(str);
            $.each(obj,function (index,value) {
                $('.IO_Sum_tb'+value.CID_EXCUTE).find('td:eq(4)').text(value.NM_ITEM);
                if (value.CID_EXCUTE==="M"){
                    $('.IO_Sum_tbI').find('td:eq(4)').text(value.NM_ITEM);
                }
            });
        }
        function checkSerchwindow() {
            if(!Serchwindow){
                return true;
            }else {
                return !!Serchwindow.closed;
            }
        }
        function isEmpty(obj) {
            let count=0;
            for (let index in obj){
                if (obj[index].length===0){
                    count++;
                }
            }

            return count >=16;
        }





        function CreatTable() {

        }

    });
</script>

<style>
    .container{

        page-break-after: always;
    }
   table{
        width: 100%;
    }

    table,
    td ,th{
        border: black solid 1px;
        text-align: center;
    }
    h1,h2{
        text-align: center;
    }
    .I,.O,.IO_Sum,.OC{
        margin-top: 10px;
        display: none;
    }

    input[type=radio]{
        width: 1.5rem;
        height: 1.5rem;
    }
    input[type=text]{
       margin-top: 10px;
        font-size: 4vmin;

    }
    .form-control:disabled, .form-control[readonly] {
      background-color: white;
    }
    label{
        font-size: 3.6vmin;
    }
    div{
        margin-top: 10px;
    }
    .Patient_NM :first-child{
        background-color: #FFFBCC;
    }
    .sDate{
        background-color: #baeeff;
        border-radius:3px;
    }

   .T_Class{
        background-color: #baeeff;
        border-radius:3px;
        margin-top: -10px;
       padding-top: 10px;
    }

    .Parametertable input{
         display: none;
        background-color: #00FF00;
    }
    button{
        margin-top: 5px;
        margin-bottom: 5px;
    }
</style>
<body>
<div class="container">
    <div class="Parametertable">
        <input id="sTraID"  value=""  type="text"  placeholder="sTraID">        <!--交易序號-->
    </div>
    <h1 >加強醫護出入量紀錄</h1>

    <div>
        <button class="btn btn-primary btn-lg" id="SubmitBtn" >儲存</button>
        <button class="btn btn-primary btn-lg" id="SearchBtn" >查詢</button>
        <button class="btn btn-primary btn-lg" id="DELBtn" >作廢</button>
    </div>

    <!----------------------------------------------------------Patient Name-------------------------------------------------------------------------->
    <div class="Patient_NM">
        <input class="form-control form-control-lg" type="text" value="<?php echo $nM_P?>" readonly>
    </div>
    <!----------------------------------------------------------Time-------------------------------------------------------------------------->
    <div class="sDate">
        <form class="form-inline">
            <div class="form-group mb-2" >
                <label for="sDate">評估時間:</label>
                <input type="text" id="sDate" class="form-control form-control-md" value="" autocomplete="off" readonly>
            </div>
        </form>
    </div>


    <div class="T_Class">

    </div>

    <div class="itemBtn">
        <button class="Page btn btn-primary btn-lg" value="IO_Sum">總量</button>
        <button class="Page btn btn-primary btn-lg" value="I">輸入量</button>
        <button class="Page btn btn-primary btn-lg" value="O">排出量</button>
        <button class="Page btn btn-primary btn-lg" value="OC">引流</button>
    </div>



    <!--    <table >
            <tbody class="tb2">
            <tr>
                <th  rowspan="2" colspan="1">三班及全日</th>
                <th class="I" colspan="6">輸入量(公撮)</th>
                <th class="O" colspan="8">排出量(公撮)</th>
                <th rowspan="2">輸出入量</th>
                <th rowspan="2">評估人員</th>
            </tr>

            <tr>
                <td class="I">靜脈</td>
                <td class="I">腸胃</td>
                <td class="I">輸血</td>
                <td class="I">TPN/PP</td>
                <td class="I">其他</td>


                <td class="O">排尿</td>
                <td class="O">排便</td>
                <td class="O">引流量</td>
                <td class="O">嘔吐</td>
                <td class="O">其他</td>
                <td class="O">洗腎脫水</td>
                <td class="O">腹膜透析</td>
            </tr>

            </tbody>
        </table>-->

    <div class="I">
        <table >
            <tbody class="tb1">
            <tr>
                <th  rowspan="2" colspan="1">三班及全日</th>
                <th  colspan="6">輸入量(公撮)</th>

            </tr>

            <tr>
                <td >靜脈</td>
                <td >腸胃</td>
                <td >輸血</td>
                <td >TPN/PP</td>
                <td style="display: none">其他</td>
                <td >其他</td>
            </tr>
            </tbody>
        </table>

    </div>
    <div class="O">
        <table >
            <tbody class="tb2">
            <tr>
                <th  rowspan="2" colspan="1">三班及全日</th>
                <th  colspan="7">排出量(公撮)</th>

            </tr>

            <tr>
                <td>排尿</td>
                <td >排便</td>
                <td >引流量</td>
                <td>嘔吐</td>
                <td >其他</td>
                <td >洗腎脫水</td>
                <td>腹膜透析</td>
            </tr>
            </tbody>
        </table>
    </div>
    <div class="IO_Sum">
        <table>
            <tbody class="IO_Sum_tb">
            <tr>
                <th  rowspan="2" colspan="1">三班及全日</th>
                <th  colspan="1">輸入量(公撮)</th>
                <th  colspan="1">排出量(公撮)</th>
                <th rowspan="2">輸出入量</th>
                <th rowspan="2">評估人員</th>
            </tr>

            <tr>
                <td >總量</td>
                <td >總量</td>
            </tr>

            </tbody>
        </table>
        <table class="tb4">
            <tbody class="tb4_b">
            <tr class="tb4_title">
                <th colspan="2">詳細內容</th>
            </tr>

            </tbody>
        </table>
    </div>
    <div class="OC">
        <table style="max-width: 650px">
            <tbody id="tb3">
            <tr>
                <th rowspan="2">引流量</th>
                <th colspan="4">三班及全日</th>
            </tr>

             <tr class="tb3_tr">
                 <td></td>
                 <td></td>
                 <td></td>
                 <td></td>
             </tr>

            </tbody>
        </table>
    </div>

</div>

</body>
</html>
