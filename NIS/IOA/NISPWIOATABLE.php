<?php
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
            GetPrintJson("","");

        })();


        let Time=new Map();
        $(".tb3_tr").children().css({'width': '93px','height': '30px'});

        function GetPrintJson(IdPt,InIdPt) {
             console.log("http://localhost"+"/webservice/NISPWIOAPRINT.php?str="+AESEnCode('sFm=IOA&idPt='+IdPt+'&INPt='+InIdPt+"&sUr="+'00FUZZY'));
            $.ajax("/webservice/NISPWIOAPRINT.php?str="+AESEnCode('sFm=IOA&idPt='+IdPt+'&INPt='+InIdPt+"&sUr="+'00FUZZY'))
                .done(function(data) {
                    let obj=JSON.parse(AESDeCode(data));

                    Time.set('Time',obj.TmSTtoE);
                    delete obj.TmSTtoE;
                    delete obj.SB;
                    Table1Append(obj);



                    Table2Append(obj);

                    Table3Append(obj.IA); // oc

                    Table4Append(obj);


                  /*  window.print();
                    window.close();*/
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
        function Table1Append(obj){
            let count=[];

            for(let index in obj ){
                count.push(obj[index].length);

            }

            let  leng=Math.max(...count);
            for (let i=0 ;i<leng;i++){
                $("#tb1").append(
                    `
                           <tr class="tb1_tr">
                                <td id="${'tb1_IA_DT'+i}"></td>
                                <td id="${'tb1_IA_NI'+i}"></td>
                                <td id="${'tb1_IA_NP'+i}"></td>
                                <td id="${'tb1_IA_TS'+i}"></td>
                                <td id="${'tb1_IA_TE'+i}"></td>
                                <td id="${'tb1_IA_QT'+i}"></td>

                                <td id="${'tb1_IB_DT'+i}"></td>
                                <td id="${'tb1_IB_NP'+i}"></td>
                                <td id="${'tb1_IB_QT'+i}"></td>

                                <td id="${'tb1_O_DT'+i}" class="O_DT"></td>
                                <td id="${'tb1_OA_QT'+i}"></td>
                                <td id="${'tb1_OB_QT'+i}"></td>
                                <td id="${'tb1_OC_QT'+i}"></td>
                                <td id="${'tb1_OD_QT'+i}"></td>
                                <td id="${'tb1_OE_QT'+i}"></td>
                                <td id="${'tb1_OF_QT'+i}"></td>
                            </tr>

                           `)

            }

            //TB1
            let o_index=0;
            for(let index in obj ){
              $.each(obj[index],function (i,value) {
                let DT_ele=$("#"+"tb1_"+index+"_DT"+i);
                let NI_ele=$("#"+"tb1_"+index+"_NI"+i);
                let NP_ele=$("#"+"tb1_"+index+"_NP"+i);
                let QT_ele=$("#"+"tb1_"+index+"_QT"+i);

                let DT=value.DT;
                let TM=value.TM;
                let ITEM=value.NM_ITEM;
                let PHARMACY=value.NM_PHARMACY;
                let QT=value.QUANTITY;
                let LOSS=value.ST_LOSS;

                if (QT===null){
                    QT=LOSS+"LOSS";
                }

                if (index === 'OA' || index === 'OB' ||index === 'OC'||index === 'OD'||index === 'OE'||index === 'OF' && DT !==""){
                    $("#tb1_O_DT"+o_index).text(DT);
                    o_index++;
                }

                  DT_ele.text((DT).substring(3,7)+"\n"+(TM).substring(0,4));
                  NI_ele.text(ITEM);
                  NP_ele.text(PHARMACY);
                  QT_ele.text(QT);
              });
            }


        }
        function Table2Append(obj){
            let QT_Sum=[[],[],[]];//總量
            let nmMap={};

            let AllTime={'Start':'24小時','End':'','IO':'S'};
            Time.get('Time').push(AllTime);

            $.each(Time.get('Time'),function (index,val) {
                let Ts=val.Start;
                let Te=val.End;
                let IO_id=val.IO;
                if (index<3){
                    Ts=(val.Start).substring(0,2)+"-";
                    Te=(val.End).substring(0,2);
                }

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

            });


            for (let index in obj){

                let arr=obj[index];

                let DSum_QTY=0;

                let NSum_QTY=0;

                let MSum_QTY=0;

                $.each(arr,function (i,val) {
                    let QTY=val.QUANTITY;
                    let CID_EXC=val.CID_EXCUTE;//早D,晚N,夜M
                    let NM_Suer=val.NM_USER;


                    if (QTY === 'NaN'  || QTY === null){
                        QTY=0;
                    }

                    if (CID_EXC==="D"){
                        DSum_QTY+=parseInt(QTY);
                        nmMap.D=NM_Suer;
                    }else  if(CID_EXC==="N"){
                        NSum_QTY+=parseInt(QTY);
                        nmMap.N=NM_Suer;
                    }else if (CID_EXC==="M"){
                        MSum_QTY+=parseInt(QTY);
                        nmMap.M=NM_Suer;
                    }

                });


                QT_Sum[0].push(DSum_QTY);
                QT_Sum[1].push(NSum_QTY);
                QT_Sum[2].push(MSum_QTY);
            }

            //預設值 包含0
            for (let i=0;i<16;i++){
                let num=i+1;
                let S_sum=QT_Sum[0][i]+QT_Sum[1][i]+QT_Sum[2][i];
                $('.tb2'+'D').find('td:eq('+num+')').text((QT_Sum[0][i]).toString());
                $('.tb2'+'N').find('td:eq('+num+')').text((QT_Sum[1][i]).toString());
                $('.tb2'+'M').find('td:eq('+num+')').text((QT_Sum[2][i]).toString());
                $('.tb2'+'S').find('td:eq('+num+')').text(S_sum.toString());
            }


            let Icount=0;
            let Ocount=0;
            let TCcount=0;

            for (let i=0;i<3;i++){
                let PgName="D";
                if (i===1){
                    PgName="N"
                }else if(i===2){
                    PgName="M";
                }

                Icount+=ArrayReduce(QT_Sum[i],0,6);
                Ocount+=ArrayReduce(QT_Sum[i],5,12);
                $('.tb2'+PgName).find('td:eq(6)').text(ArrayReduce(QT_Sum[i],0,6));//I 總量

                $('.tb2'+PgName).find('td:eq(14)').text(ArrayReduce(QT_Sum[i],5,12));//O 總量


                $('.tb2'+PgName).find('td:eq(15)').text(ArrayReduce(QT_Sum[i],5,12)-ArrayReduce(QT_Sum[i],0,6));//輸出入量(D,N,M)


                $('.tb2'+PgName).find('td:eq(16)').text(nmMap[PgName]);//評估人員


                TCcount+= parseInt($('.tb2'+PgName).find('td:eq(15)').text());//輸出入量(S)
            }

            $('.tb2'+'S').find('td:eq(6)').text(Icount);
            $('.tb2'+'S').find('td:eq(14)').text(Ocount);
            $('.tb2'+'S').find('td:eq(15)').text(TCcount);

            //td:0轉空白
            $("td").each(function () {
              if ($(this).text()==="0"){
                  $(this).text(" ");
              }
            });
        }
        function Table3Append(arr){

            $.each(Time.get('Time'),function (index,val) {
                let Ts=val.Start;
                let Te=val.End;
                let IO_id=val.IO;
                if (index<3){
                    Ts=(val.Start).substring(0,2)+"-";
                    Te=(val.End).substring(0,2);
                }

                  $("#tb3").append
                  (
                      `
                           <tr class='${IO_id}'>
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
                  )
            });

            $.each(arr,function (index,val) {

              let Title_tr=$(".tb3_tr");
              let num=index+1;
              let IO=val.CID_EXCUTE;
              Title_tr.find('td:eq('+index+')').text(val.NM_PHARMACY);

              if (IO==="D"){
                  $(".D").find('td:eq('+num+')').text(val.QUANTITY);
              }else if(IO==="N"){
                  $(".N").find('td:eq('+num+')').text(val.QUANTITY);
              }else  if (IO==="M"){
                  $(".M").find('td:eq('+num+')').text(val.QUANTITY);
              }

              let D_val=isNaN(parseInt($(".D").find('td:eq('+num+')').text()))?0:parseInt($(".D").find('td:eq('+num+')').text());
              let N_val =isNaN(parseInt($(".N").find('td:eq('+num+')').text()))?0:parseInt($(".N").find('td:eq('+num+')').text());
              let M_val =isNaN(parseInt($(".M").find('td:eq('+num+')').text()))?0:parseInt($(".M").find('td:eq('+num+')').text()) ;

              $(".S").find('td:eq('+num+')').text(D_val+N_val+M_val);

          });



        }
        function Table4Append(obj) {
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

               /* tb_H+=$('.tb4').innerHeight();


                if (tb_H>580.4){

                }*/





                /*let td_width=$(".tb4").find('td:eq(0)').innerWidth();*/


              /*  $(".tb4").find('td:eq(0)').text(DateTime+' '+str.substring(1,str.length));*/

            }

        }
        /**
         * @return {number}
         */
        function ArrayReduce(arr,start,end) {
            let count=0;

            for (let i=start;i<end;i++){

                count+=arr[i];
            }
            return count;
        }
        function InsertInMap(A_Map,DateTime,obj) {
            console.log(obj);
            for (let [key ,value] of A_Map.entries()){

                if (key===DateTime){
                    value.push(obj);
                }
            }
        }


    });
</script>

<style>
    .container{
        margin-top: 5%;
        page-break-after: always;
    }
    table{
        width: 100%;
    }

    table,
    td ,th{
        border: 1px solid #333;
        text-align: center;
    }
    table  tr td{
        height: 40px;
        min-width: 55px;

    }
    h1,h2{
        text-align: center;
    }

    .T_Date{
        width: 83px;
    }


    .tb4 td:nth-child(1){
        width: 15%;
        padding-bottom: 23px;
        border-right: white solid 1px;
    }
    .tb4 td{
        border-bottom: white solid 1px;
    }

    .tb4_b tr:last-child{

        border-bottom: black solid 2px;
    }
</style>
<body>
<div class="container">

        <div class="row">
            <div class="col-4">

            </div>
            <div class="col-4">
                <h1 >悅晟醫院</h1>
                <h2 >加強醫護出入量紀錄</h2>
            </div>
            <div class="col-4" style="border: black solid 1px">
                123
            </div>
        </div>


    <table>
        <thead>
            <tr>
                <th colspan="6">靜脈量輸入量(公撮)</th>
                <th colspan="3">腸胃道輸入量(公撮)</th>
                <th colspan="8">排出量(公撮)</th>
            </tr>

        </thead>
        <tbody id="tb1">
            <tr>
                <td rowspan="2">日期時間</td>
                <td rowspan="2">溶液</td>
                <td rowspan="2">加入藥物</td>
                <td colspan="2">時間</td>
                <td rowspan="2">給予數量</td>

                <td rowspan="2">日期時間</td>
                <td rowspan="2">方式種類</td>
                <td rowspan="2">數量</td>

                <td rowspan="2">日期時間</td>
                <td rowspan="2">排尿</td>
                <td rowspan="2">排便</td>
                <td rowspan="2">引流量</td>
                <td rowspan="2">嘔吐</td>
                <td rowspan="2">洗腎脫水</td>
                <td rowspan="2">腹膜透析</td>
            </tr>

            <tr>
                <td>起</td>
                <td>迄</td>
            </tr>


        </tbody>
    </table>

    <table >
        <tbody class="tb2">
        <tr>
            <th  rowspan="2" colspan="1">三班及全日</th>
            <th colspan="6">輸入量(公撮)</th>
            <th colspan="8">排出量(公撮)</th>
            <th rowspan="2">輸出入量</th>
            <th rowspan="2">評估人員</th>
        </tr>

        <tr>
            <td>靜脈</td>
            <td>腸胃</td>
            <td>輸血</td>
            <td>TPN/PP</td>
            <td>其他</td>
            <td>總量</td>

            <td>排尿</td>
            <td>排便</td>
            <td>引流量</td>
            <td>嘔吐</td>
            <td>其他</td>
            <td>洗腎脫水</td>
            <td>腹膜透析</td>
            <td>總量</td>
        </tr>




        </tbody>
    </table>

    <table>
        <tbody id="tb3">
        <tr>
            <th class="T_Date" rowspan="2" >三班及全日</th>
            <th colspan="8">引流量</th>
        </tr>
        <tr class="tb3_tr">
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>

        </tbody>
    </table>

    <table class="tb4">
        <tbody class="tb4_b">
            <tr >
                <th colspan="2">詳細內容</th>
            </tr>

        </tbody>
    </table>
    <p>排尿方式: F:F A:自排 B:膀胱造廔 C:洗腎</p>
</div>

</body>
</html>
