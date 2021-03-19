function DefaultData(idPt,INPt,sUr) {
    $("#loading").show();
    $("#wrapper").show();
      console.log("http://localhost"+"/webservice/NISPWSTRAINI.php?str="+AESEnCode('sFm=CNCD&idPt='+idPt+'&INPt='+INPt+"&sUr="+sUr));
    $.ajax({
        url:"/webservice/NISPWSTRAINI.php?str="+AESEnCode('sFm=CNCD&idPt='+idPt+'&INPt='+INPt+"&sUr="+sUr),
        type:"POST",
        dataType: 'text',
        success:function (data) {
            $("#loading").hide();
            $("#wrapper").hide();
            let objArr=JSON.parse(AESDeCode(data));
            let TraObj=JSON.parse(objArr.shift());

            if( $("#DATAList").children().length>0){
                $("#DATAList").children().remove();
            }
            console.log(objArr);

            $.each(objArr,function (index,val){
                let ChecBoxkID=val.MEDNO+'@'+val.BARCODE;
                let BARCODE=val.BARCODE;
                let SPENAME=val.SPENAME;
                let CONNAME=val.CONNAME;
                let EGNAME= (val.EGNAME).replaceAll(',',",<br>");
                let CheckBoxVal=val.LOOKDT+"@"+val.MEDNO+"@"+INPt+"@"+val.DIACODE+"@"+val.MACHINENO+"@"+val.WORKNO+"@"+(val.HISORDKEY).replaceAll('@','#');
                $("#DATAList").append(
                    `
                        <tr class='list-item'>
                             <td><input type='checkbox'  name='BDckbox' class='form-check-input' id='${ChecBoxkID}' value='${CheckBoxVal}'></td>
                             <td  colspan='4' style='text-align:left;font-weight: bold'>採血編號:${BARCODE} 檢體:${SPENAME} 試管:${CONNAME}</td>
                           
                        </tr>
                        <tr>
                            <td style='font-weight: bold'>檢驗項目</td>
                            <td colspan='4' style='text-align:left;'>${EGNAME}</td>
                        </tr>
                                            
                     `
                );
            });

            GroupEle($("#DATAList").children('tr'),2) ;

            $("#sTraID").val(TraObj.sTraID);
            $("#sSave").val(TraObj.sSave);
            $("button:not(#DELMENU,#Error_btn)").prop('disabled',false);
            $("input[type=text]").prop('disabled',false);
        },
        error:function (XMLHttpResponse,textStatus,errorThrown) {
            console.log(
                "1 返回失敗,XMLHttpResponse.readyState:"+XMLHttpResponse.readyState+XMLHttpResponse.responseText+
                "2 返回失敗,XMLHttpResponse.status:"+XMLHttpResponse.status+
                "3 返回失敗,textStatus:"+textStatus+
                "4 返回失敗,errorThrown:"+errorThrown
            );
        }
    });
}
function Serchcallback(AESobj){
    let str1=AESDeCode(AESobj);
    let objArr=JSON.parse(str1);
    let TraObj=JSON.parse(objArr.shift());

    if( $("#DATAList").children().length>0){
        $("#DATAList").children().remove();
    }


    $.each(objArr,function (index,val) {
        let ChecBoxkID=val.MEDNO+'@'+val.BARCODE;
        let CheckBoxVal=val.LOOKDT+"@"+val.MEDNO+"@"+$('#DA_InPt').val()+"@"+val.DIACODE+"@"+val.MACHINENO+"@"+val.WORKNO;
        let BARCODE=val.BARCODE;
        let SPENAME=val.SPENAME;
        let CONNAME=val.CONNAME;
        let EGNAME= (val.EGNAME).replaceAll(',',",<br>");

        $("#DATAList").append(
            `
                 <tr class='list-item'>
                    <td><input type='checkbox'  name='BDckbox' class='form-check-input' id='${ChecBoxkID}' value='${CheckBoxVal}'></td>
                    <td  colspan='4' style='text-align:left;font-weight: bold'>採血編號:${BARCODE} 檢體:${SPENAME} 試管:${CONNAME}</td>
                   
                 </tr>
                 <tr>
                     <td style='font-weight: bold'>檢驗項目</td>
                     <td colspan='4' style='text-align:left;'>${EGNAME}</td>
                 </tr>
             `
        );
    });

    GroupEle($("#DATAList").children('tr'),2) ;

    $("#sTraID").val(TraObj.sTraID);
    $("#sSave").val(TraObj.sSave);
    $("#DateVal").val(objArr[0].EXECDATE);
    $("#TimeVal").val(objArr[0].EXECTIME);
    $('#DELMENU').prop('disabled',false);
    $("input[type=text]").prop("disabled",true);
    $("input[type=checkbox]").prop("checked",true);
    $("input[type=checkbox]").prop("disabled",true);
    $("button[type=submit]").prop("disabled",true);
}


function InsertWSST(Page,sTraID,json,sDt=null,sTm=null,Passed=null,Freq=null,sUr,InSertDB) {
    $.ajax('/webservice/NISPWSSETDATA.php?str='+AESEnCode(
        'sFm=CNCD&sTraID='+sTraID+'&sPg='+Page+'&sData='+json+
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
function TimerDefault() {
    let TimeNow=new Date();
    let yyyy=TimeNow.toLocaleDateString().slice(0,4);
    let MM=(TimeNow.getMonth()+1<10?'0':'')+(TimeNow.getMonth()+1);
    let dd=(TimeNow.getDate()<10?'0':'')+TimeNow.getDate();
    let  h=(TimeNow.getHours()<10?'0':'')+TimeNow.getHours();
    let  m=(TimeNow.getMinutes()<10?'0':'')+TimeNow.getMinutes();
    let  s=(TimeNow.getSeconds()<10?'0':'')+TimeNow.getSeconds();
    $("#DateVal").val(yyyy-1911+MM+dd);
    $("#TimeVal").val(h+m);
}
function GetCheckVal() {
    //取checkbox的值
    let cbxVehicle = new Array();
    let Json=[];
    $("input:checkbox:checked[name=BDckbox]").each(function (i) {
        cbxVehicle[i]=$(this).val();
    });

    if(cbxVehicle.length>0){
        $.each(cbxVehicle,function (index) {
            let str=cbxVehicle[index];
            const OBJ={
                LOOKDT:'',
                MEDNO:'',
                IDINPT:'',
                DIACODE:'',
                MACHINENO:'',
                WORKNO:'',
                ORDERKEY:''
            };
            //LOOKDT@MEDNO@IDINPT@DIACODE@MACHINENO@A@WORKNO
            let DIACODEarr= str.split("@",7)[3].split(",");
            let MACHINENOarr= str.split("@",7)[4].split(",");
            let WORKNOarr= str.split("@",7)[5].split(",");
            let Okeyarr= str.split("@",7)[6].split(",");
            $.each(DIACODEarr,function (index) {
                let DeepCopy={};
                $.extend(true,OBJ,DeepCopy);
                DeepCopy.LOOKDT= str.split("@",6)[0];
                DeepCopy.MEDNO= str.split("@",6)[1];
                DeepCopy.IDINPT= str.split("@",6)[2];
                DeepCopy.DIACODE=DIACODEarr[index];
                DeepCopy.MACHINENO= MACHINENOarr[index];
                DeepCopy.WORKNO=WORKNOarr[index];
                DeepCopy.ORDERKEY=Okeyarr[index];
                Json.push(DeepCopy);
            });
        });
    }
    return Json;
}
function errorModal(str,type) {
    $("#ModalBody").children().remove("#ErrorText");
    if(type===true){
        $("#BedChange").prop('disabled',false);
        $("#ErrorTitle").hide();
        $("#ErrBlood").hide();
    }else {
        $("#BedChange").prop('disabled',true);
        $("#ErrorTitle").show();
        $("#ErrBlood").show();

    }
    $("#ModalBody").append(
        `
          <p id="ErrorText" style="font-size: 2.5vmin;word-wrap: break-word">${str}</p>
        `);
    $('#Errormodal').modal('show');

}
function errUI(Arr){
    //錯誤血袋
    $("#Error_btn").css({"background-color":"#FF0000","border-color":"#FF0000"});
    $("#Error_btn").prop("disabled",false);
    $("#ErrBlood").children().remove();
    $.each(Arr,function (index,val) {
        let NUM=val.NUM;
        let IDPT=val.IDPT;
        let BAR_CODE=val.BAR_CODE;
        console.log(val);
        $("#ErrBlood").append(
            `
               <tr class='list-item'>
                    <td>${NUM}</td>
                    <td>${IDPT}</td>
                    <td>${BAR_CODE}</td>
                </tr>
            `
        );
    });
}
function GroupEle(array,subGroupLength) {
    let index=0;
    let newArray=[];
    let ChecekdColor=['#F2EFE9','#FFFAF4'];
    while (index<array.length){
        newArray.push(array.slice(index,index+=subGroupLength));
    }

    for(let index in newArray){
        if(index%2==0){
            newArray[index].css({'background-color':ChecekdColor[0]});
        }else {
            newArray[index].css({'background-color':ChecekdColor[1]});
        }

    }

}
