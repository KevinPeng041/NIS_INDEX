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



        const T_Date=['08-16','16-00','00-08','24小時'];


        $(".tb3_tr").children().css({'width': '93px','height': '30px'});
        $.each(T_Date,function (index,val) {

            $(".tb2").append(
                `
                <tr>
                        <td>${val}</td>
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



            $("#tb3").append(`

             <tr>
                <td>${val}</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
             </tr>

            `)
        });
        function GetPrintJson(IdPt,InIdPt) {
             console.log("http://localhost"+"/webservice/NISPWIOAPRINT.php?str="+AESEnCode('sFm=IOA&idPt='+IdPt+'&INPt='+InIdPt+"&sUr="+'00FUZZY'));
            $.ajax("/webservice/NISPWIOAPRINT.php?str="+AESEnCode('sFm=IOA&idPt='+IdPt+'&INPt='+InIdPt+"&sUr="+'00FUZZY'))
                .done(function(data) {
                    let obj=JSON.parse(AESDeCode(data));

                    console.log(obj);
                    Table1Append(obj);

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
        function Table1Append(obj) {
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

    });
</script>
<style>
    table{
        width: 1000px;
    }

    table,
    td ,th{
        border: 1px solid #333;
        text-align: center;
    }

    table  tr td{
        height: 40px;
        width:55px;
      /*  min-width: 55px;
        min-height: 40px;*/
    }

    .T_Date{
        width: 83px;
    }
    h1,h2{
        text-align: center;
    }

</style>
<body>
<div class="container">
    <div class="title">
        <h1 >悅晟醫院</h1>
        <h2 >加強醫護出入量紀錄</h2>
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
            <td>輸血</td>
            <td>腸胃</td>
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

    <table>
        <tbody>
            <tr>
                <th>詳細內容</th>
            </tr>
            <tr>
                <td>
                    106/07/11 11:36 管灌食物 4、管灌食物 4、TPN / PPN 2、TPN / PPN 2、其他攝入量 3、其他攝入量 3、開水 1、開水 1、
                    尿量 5(Loss 6) 自排 鮮紅色、尿量 5(Loss 6) 自排 鮮紅色、排便量 3、排便量 3、NG tube(鼻) 45、
                    NG tube(鼻) 45、嘔吐 5-33、嘔吐 5-33、洗腎脫水量 7-22、洗腎脫水量 7-22、腹膜透析量 6-44、
                    腹膜透析量 6-44
                </td>
            </tr>
        </tbody>
    </table>
    <p>排尿方式: F:F A:自排 B:膀胱造廔 C:洗腎</p>
</div>

</body>
</html>
