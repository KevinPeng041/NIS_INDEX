<?php
include '../../NISPWSIFSCR.php';
$str=$_GET['str'];
$replaceSpace=str_replace(' ','+',$str);
$EXPLODE_data=explode('&',AESDeCode($replaceSpace));

$IdPt_STR=$EXPLODE_data[0];
$IdInPt_STR=$EXPLODE_data[1];
$Dt_STR=$EXPLODE_data[2];
$sUr_STR=$EXPLODE_data[3];
$nM_STR=$EXPLODE_data[4];

$IdPt_value=explode('=',$IdPt_STR);
$IdInPt_value=explode('=',$IdInPt_STR);
$Dt_value=explode('=',$Dt_STR);
$sUr_value=explode('=',$sUr_STR);
$nM_value=explode('=',$nM_STR);

$IdPt=$IdPt_value[1];
$IdInPt=$IdInPt_value[1];
$Dt=$Dt_value[1];
$sUr=$sUr_value[1];
$nM_P=$nM_value[1];


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
            GetINIJson("<?php echo $IdPt?>","<?php echo $IdInPt?>","<?php echo $Dt?>");
        })();

        let Time=new Map();
        $(".tb3_tr").children().css({'width': '93px','height': '30px'});

        $(document).on('click','button',function () {
            let Page=$(this).val();
            const arr=['I','O','IO_Sum','OC'];
            if ($(this).attr('class')==="btn btn-primary btn-lg"){
                arr.forEach(index=>$('.'+index).hide());
                $("."+Page).show();
            }

        });
        $(document).on('change','select',function () {
            let val=$(this).val();
            const arr=['D','N','M','S'];

            if (val==='0'){
                $("#SubmitBtn").prop('disabled',true);
                return false;
            }


            $("#SubmitBtn").prop('disabled',false);
            for (let index of arr){
                $(".tb1"+index).remove();
                $(".tb2"+index).remove();
                $(".IO_Sum_tb"+index).remove();

            }

            $(".tb4_b tr:not(.tb4_title)").remove(); //詳細內容*/
            $("#tb3 > tr:gt(1)").remove(); //OC

            GetPrintJson("<?php echo $IdPt?>","<?php echo $IdInPt?>",val);
        });

        $("#SubmitBtn").click(function () {
            DB_SAVE('<?php echo $IdPt?>','<?php echo $IdInPt?>',$('select').val(),'<?php echo $sUr?>');
        });

        function isEmpty(obj) {
            let count=0;
            for (let index in obj){
                if (obj[index].length===0){
                    count++;
                }
            }
            if (count>=16){
                return false;
            }
                return true;
        }
        function GetPrintJson(IdPt,InIdPt,sDt) {
            $.ajax("/webservice/NISPWIOAPRINT.php?str="+AESEnCode('idPt='+IdPt+'&INPt='+InIdPt+"&sDt="+sDt))
                .done(function(data) {
                    let obj=JSON.parse(AESDeCode(data));
                    let AllTime={'Start':'24小時','End':'','IO':'S'};
                    let isConfirm=obj.Comfirm;

                    Time.set('Time',obj.TmSTtoE);
                    delete obj.TmSTtoE;
                    delete obj.SB;
                    delete obj.Comfirm;


                    if (!isEmpty(obj)){
                        $("#SubmitBtn").prop('disabled',true);
                        $("#DELBtn").prop('disabled',true);
                        alert('查無資料');
                        return false;
                    }

                    TableAppend(obj,AllTime);

                    //引流
                    OCAppend(obj.OC);

                    //詳細內容
                    DeTailAppend(obj);

                    //td:0轉空白
                    $("td").each(function () {
                        if ($(this).text()==="0" || $(this).text()==="NaN")
                        {
                            $(this).text(" ");
                        }
                    });

                    if (isConfirm==="N"){
                        $("#DELBtn").prop('disabled',true);
                    }else {
                        $("#SubmitBtn").prop('disabled',true);
                        $("#DELBtn").prop('disabled',false);
                    }


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
        function TableAppend(obj,AllTime){
            let QT_Sum=[[],[],[]];//總量
            let nmObj={};

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
                        <td></td>
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

            for (let index in obj){

                let arr=obj[index];
                let DSum_QTY=0;

                let NSum_QTY=0;

                let MSum_QTY=0;

                $.each(arr,function (i,val) {
                    let QTY=isNaN(parseInt(val.QUANTITY))?val.ST_LOSS+"LOSS":val.QUANTITY;
                    let CID_EXC=val.CID_EXCUTE;//早D,晚N,夜M
                    let NM_Suer=val.NM_USER;

                    if (QTY === 'NaN'  || QTY === null){
                        QTY=0;
                    }

                    if (CID_EXC==="D"){
                        DSum_QTY+=parseInt(QTY);
                        nmObj.D=NM_Suer;
                    }else  if(CID_EXC==="N"){
                        NSum_QTY+=parseInt(QTY);
                        nmObj.N=NM_Suer;
                    }else if (CID_EXC==="M"){
                        MSum_QTY+=parseInt(QTY);
                        nmObj.M=NM_Suer;
                    }
                });

                QT_Sum[0].push(DSum_QTY);
                QT_Sum[1].push(NSum_QTY);
                QT_Sum[2].push(MSum_QTY);
            }
           QT_Sum.forEach((value, index) =>InsertTdValue(value,index));
            InsertSumTdValue(QT_Sum,nmObj,obj);

        }
        function OCAppend(arr){
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

            let A_Map = new Map();


            for (let index in obj) {
                let arr = obj[index];

                if (arr.length > 0) {
                    arr.forEach(element => A_Map.set(element.DT + element.TM, []));
                }

            }//set DateTime Default[]
            for (let index in obj) {
                let arr = obj[index];

                if (arr.length > 0) {

                    $.each(arr, function (i, val) {
                        InsertInMap(A_Map, val.DT + val.TM, val)
                    });
                }

            }


            for (let [key, value] of A_Map){
                let str='';
                let dt=key.substring(0,11);
                let DateTime=dt.substring(0,3)+'/'+dt.substring(3,5)+'/'+dt.substring(5,7)+
                        ' '+dt.substring(7,9)+':'+dt.substring(9,11);
                $.each(value,function (i,val) {
                    let FieldByName=val.ID_ITEM;

                    if (FieldByName==="IB" || FieldByName==="OC"){


                        str+=(val.NM_PHARMACY).trim()!==null?'、'+val.NM_PHARMACY:'、'+val.NM_ITEM+' '+val.QUANTITY;

                    }else if(val.CID_IO==="I" || val.CID_IO==="O"){

                        if (FieldByName==="IA" || FieldByName==="IC" || FieldByName==="OG" ){
                            str+='、'+val.NM_ITEM+' '+val.NM_PHARMACY+val.QUANTITY;
                        }
                        else {
                            str+=(val.NM_ITEM).trim()!==null?val.NM_ITEM:val.NM_PHARMACY+' '+val.QUANTITY;
                        }

                    }

                    if ((val.ST_LOSS).trim()!==''){
                        str+=('LOSS'+(val.ST_LOSS).trim());
                    }
                    if ((val.NM_IOWAY).trim()!==''){
                        str+=' '+(val.NM_IOWAY);
                    }
                    if ((val.NM_COLOR).trim()!==''){
                        str+=' '+(val.NM_COLOR);
                    }
                    if ((val.MM_IO).trim()!==''){
                        str+='-'+(val.MM_IO);
                    }
                    if (value.length===i){
                        str+='<br>';
                    }
                });


                $(".tb4_b").append(
                    `
                    <tr>
                        <td >${DateTime}</td>
                        <td style="text-align: left">${str.substring(1,str.length)}</td>
                    </tr>
                    `

                );
            }

        }
        function InsertInMap(A_Map,DateTime,obj) {

            for (let [key ,value] of A_Map.entries()){

                if (key===DateTime){
                    value.push(obj);
                }
            }
        }
        function InsertTdValue(arr,index) {
            let I=arr.slice(0,5);
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
        function InsertSumTdValue(arr,nmObj,obj) {
            //輸入
            for (let i=1;i<=5;i++){
                let D=parseInt($('.tb1D').find('td:eq('+i+')').text());
                let N=parseInt($('.tb1N').find('td:eq('+i+')').text());
                let M=parseInt($('.tb1M').find('td:eq('+i+')').text());

                $(".tb1S").find('td:eq('+i+')').text((D+N+M).toString());
            }
            //輸出
            for (let i=1;i<=7;i++){
                let D=parseInt($('.tb2D').find('td:eq('+i+')').text());
                let N=parseInt($('.tb2N').find('td:eq('+i+')').text());
                let M=parseInt($('.tb2M').find('td:eq('+i+')').text());

                $(".tb2S").find('td:eq('+i+')').text((D+N+M).toString());
            }

            //總量
            for (let i=1;i<=3;i++){
                let D=parseInt($('.IO_Sum_tbD').find('td:eq('+i+')').text());
                let N=parseInt($('.IO_Sum_tbN').find('td:eq('+i+')').text());
                let M=parseInt($('.IO_Sum_tbM').find('td:eq('+i+')').text());


                $(".IO_Sum_tbS").find('td:eq('+i+')').text(D+N+M);
            }


            for (let index in nmObj){
                $('.tb1'+index).find('td:eq(6)').text(nmObj[index]);
                $('.tb2'+index).find('td:eq(8)').text(nmObj[index]);
                $('.IO_Sum_tb'+index).find('td:eq(4)').text(nmObj[index]);
            }


            //有LOSS值
            let count=0;
            for (let index in obj){
                $.each(obj[index],function (i,val) {
                    if (val.ST_LOSS){

                        if (index==='IB'){
                           let IB_str=$('.tb1'+val.CID_EXCUTE).find('td:eq('+(count+1)+')').text();
                            IB_str=IB_str+'LOSS';
                            $('.tb1'+val.CID_EXCUTE).find('td:eq('+(count+1)+')').text(IB_str);
                            $('.tb1S').find('td:eq('+(2)+')').text(IB_str);
                        }
                        if(index==='OB'){
                            let OB_str=$('.tb2'+val.CID_EXCUTE).find('td:eq('+(2)+')').text();
                            OB_str=OB_str+'LOSS';
                            $('.tb2'+val.CID_EXCUTE).find('td:eq('+(2)+')').text(OB_str);
                            $('.tb2S').find('td:eq('+(2)+')').text(OB_str);
                        }

                    }
                });
                count++;
            }


        }
        function DB_SAVE(IdPt,IdInPt,Dt,sUr) {
            let ck_val=$("input[type=radio]:checked").val();
            $.ajax('/webservice/NISPWSSAVEILSG.php?str='+AESEnCode('sFm='+'IOA_C'+'&IdPt='+IdPt+'&IdInPt='+IdInPt+'&sDt='+Dt+'&USER='+sUr+'&CID_EXE='+ck_val+'&USER='+sUr))
                .done(function (data) {
                    let result= JSON.parse(AESDeCode(data));
                    $("#loading").hide();
                    $("#wrapper").hide();
                    if(result.response==='success'){
                        alert("儲存成功");
                       window.close();
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
        function GetINIJson(IdPt,InIdPt,C_DT){

            $.ajax("/webservice/NISPWSTRAINI.php?str="+AESEnCode('sFm=IOA_C&idPt='+IdPt+'&INPt='+InIdPt+"&sUr="+''))
                .done(function(data) {

                    let obj=JSON.parse(AESDeCode(data));
                    let DT=obj.pop();

                    $.each(obj,function (index,val)
                    {

                           $(".T_Class").append(`
                               <label>
                                    <input type="radio" value="${val.CID_NM}" name="ClassDt" disabled>${val.NM_ITEM}
                                </label>
                                `);
                                if (val.CID_NM===C_DT){
                                    $(".T_Class").find('input:eq('+index+')').prop('checked',true);
                                }
                    });

                    $.each(DT,function (index,val) {
                        $("#DT_SELECT").append(
                            `
                             <option value="${val}">${val}</option>
                              `
                        )
                    });



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
    label{
        font-size: 3.6vmin;
    }
    div{
        margin-top: 10px;
    }
</style>
<body>
<div class="container">

    <h1 >加強醫護出入量紀錄</h1>
    <input class="form-control form-control-lg" type="text" value="<?php echo  $nM_P?>" disabled>
    <div class="T_Class">

    </div>
    <div>
        <select id="DT_SELECT" class="form-control form-control-lg">
            <option value="0">請選擇</option>
        </select>
    </div>
    <div>
        <button class="btn btn-info btn-lg" id="SubmitBtn" disabled>儲存</button>
        <button class="btn btn-info btn-lg" id="DELBtn" disabled>作廢</button>
    </div>
    <div>
        <button class="btn btn-primary btn-lg" value="I">輸入量</button>
        <button class="btn btn-primary btn-lg" value="O">排出量</button>
        <button class="btn btn-primary btn-lg" value="IO_Sum">總量</button>
        <button class="btn btn-primary btn-lg" value="OC">引流</button>
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
                <th  colspan="5">輸入量(公撮)</th>
                <th rowspan="2">評估人員</th>
            </tr>

            <tr>
                <td >靜脈</td>
                <td >腸胃</td>
                <td >輸血</td>
                <td >TPN/PP</td>
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
                <th rowspan="2">評估人員</th>
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
