function DefaultData(idPt,INPt,sUr) {
    $("#loading").show();
    $("#wrapper").show();
    /*  console.log("http://localhost"+"/webservice/NISPWSTRAINI.php?str="+AESEnCode('sFm=CNCD&idPt='+idPt+'&INPt='+INPt+"&sUr="+sUr));*/
    $.ajax({
        url:"/webservice/NISPWSTRAINI.php?str="+AESEnCode('sFm=CNCD&idPt='+idPt+'&INPt='+INPt+"&sUr="+sUr),
        type:"POST",
        dataType: 'text',
        success:function (data) {
            $("#loading").hide();
            $("#wrapper").hide();
            var objArr=JSON.parse(AESDeCode(data));
            var TraObj=JSON.parse(objArr.shift());
            if( $("#DATAList").children().length>0){
                $("#DATAList").children().remove();
            }
            console.log(objArr);
            $.each(objArr,function (index,val){
                $("#DATAList").append(
                    "<tr class='list-item'>"+
                    "<td>"+
                    "<input type='checkbox'  name='BDckbox' class='form-check-input' id='"+val.MEDNO+'@'+val.BARCODE+"' value='"+val.LOOKDT+"@"+val.MEDNO+"@"+$('#DA_InPt').val()+"@"+val.DIACODE+"@"+val.MACHINENO+"@"+val.WORKNO+"'>"+
                    "</td>"+
                    "<td  colspan='4' style='text-align:left;font-weight: bold'>"+"採血編號:"+" "+val.BARCODE+" 檢體:"+val.SPENAME+" 試管:"+val.CONNAME+"</td>"+
                    "</tr>"+
                    "<tr>"+
                    "<td style='font-weight: bold'>"+"檢驗項目:"+"</td>"+
                    "<td colspan='4' style='text-align:left;'>"+(val.EGNAME).replaceAll(',',",<br>")+"</td>"+
                    "</tr>"
                );
            });

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
    var str1=AESDeCode(AESobj);
    var objArr=JSON.parse(str1);
    var TraObj=JSON.parse(objArr.shift());

    if( $("#DATAList").children().length>0){
        $("#DATAList").children().remove();
    }

    $.each(objArr,function (index,val) {

        $("#DATAList").append(
            "<tr class='list-item'>"+
            "<td>"+
            "<input type='checkbox'  name='BDckbox' class='form-check-input' id='"+val.MEDNO+'@'+val.BARCODE+"' value='"+val.LOOKDT+"@"+val.MEDNO+"@"+$('#DA_InPt').val()+"@"+val.DIACODE+"@"+val.MACHINENO+"@"+val.WORKNO+"'>"+
            "</td>"+
            "<td  colspan='4' style='text-align:left;font-weight: bold'>"+"採血編號:"+" "+val.BARCODE+" 檢體:"+val.SPENAME+" 試管:"+val.CONNAME+"</td>"+
            "</tr>"+
            "<tr>"+
            "<td style='font-weight: bold''>"+"檢驗項目:"+"</td>"+
            "<td colspan='4' style='text-align:left;'>"+(val.EGNAME).replaceAll(',',",<br>")+"</td>"+
            "</tr>"
        );
    });


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
function InsertWSST(sTraID,page,json) {
    console.log("http://localhost"+'/webservice/NISPWSSETDATA.php?str='+AESEnCode('sFm=CNCD&sTraID='+sTraID+'&sPg='+page+'&sData='+json));
    $.ajax({
        'url':'/webservice/NISPWSSETDATA.php?str='+AESEnCode('sFm=CNCD&sTraID='+sTraID+'&sPg='+page+'&sData='+json),
        type:"POST",
        dataType:"text",
        success:function(data){
            var json=JSON.parse(AESDeCode(data));
            /*
                                console.log(json.message);
            */
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
    });
}
function TimerDefault() {
    var TimeNow=new Date();
    var yyyy=TimeNow.toLocaleDateString().slice(0,4);
    var MM=(TimeNow.getMonth()+1<10?'0':'')+(TimeNow.getMonth()+1);
    var dd=(TimeNow.getDate()<10?'0':'')+TimeNow.getDate();
    var  h=(TimeNow.getHours()<10?'0':'')+TimeNow.getHours();
    var  m=(TimeNow.getMinutes()<10?'0':'')+TimeNow.getMinutes();
    var  s=(TimeNow.getSeconds()<10?'0':'')+TimeNow.getSeconds();
    $("#DateVal").val(yyyy-1911+MM+dd);
    $("#TimeVal").val(h+m);
}
function GetCheckVal() {
    //取checkbox的值
    var cbxVehicle = new Array();
    var Json=[];
    $("input:checkbox:checked[name=BDckbox]").each(function (i) {
        cbxVehicle[i]=$(this).val();
    });

    if(cbxVehicle.length>0){
        $.each(cbxVehicle,function (index) {
            var str=cbxVehicle[index];
            var OBJ={
                LOOKDT:'',
                MEDNO:'',
                IDINPT:'',
                DIACODE:'',
                MACHINENO:'',
                WORKNO:''
            };
            //LOOKDT@MEDNO@IDINPT@DIACODE@MACHINENO@A@WORKNO
            var DIACODEarr= str.split("@",6)[3].split(",");
            var MACHINENOarr= str.split("@",6)[4].split(",");
            var WORKNOarr= str.split("@",6)[5].split(",");
            $.each(DIACODEarr,function (index) {
                var DeepCopy={};
                $.extend(true,OBJ,DeepCopy);
                DeepCopy.LOOKDT= str.split("@",6)[0];
                DeepCopy.MEDNO= str.split("@",6)[1];
                DeepCopy.IDINPT= str.split("@",6)[2];
                DeepCopy.DIACODE=DIACODEarr[index];
                DeepCopy.MACHINENO= MACHINENOarr[index];
                DeepCopy.WORKNO=WORKNOarr[index];
                Json.push(DeepCopy);
            });
        });
    }
    return Json;
}
function errorModal(str,AddEle) {
    if(AddEle){
        $("#BedChange").remove();
        $("#ErorFocus").before(
            '<button type="button" id="BedChange" class="btn btn-primary" data-dismiss="modal">'+'確定'+'</button>'
        )
    }
    $("#ModalBody").children().remove("#ErrorText");
    $("#ErrorTitle").hide();
    $("#ErrBlood").hide();
    $("#ModalBody").append(
        '<p id="ErrorText" style="font-size: 2.5vmin;word-wrap: break-word">'+str+'</p>'
    );
    $('#Errormodal').modal('show');

}
function errUI(Arr){
    $("#Error_btn").css({"background-color":"#FF0000","border-color":"#FF0000"});
    $("#Error_btn").prop("disabled",false);
    $("#ErrBlood").children().remove();
    $.each(Arr,function (index,val) {
        $("#ErrBlood").append(
            "<tr class='list-item'>"+
            "<td>"+val.NUM+"</td>"+
            "<td>"+val.IDPT+"</td>"+
            "<td>"+val.BAR_CODE+"</td>"+
            "<tr>"
        );
    });
}
function CheckUIisset(IdPt,NumidStr){
    if($("#"+IdPt+"\\@"+NumidStr).length>0){
        var top=($("#"+IdPt+"\\@"+NumidStr).offset()).top-400;
        $("#"+IdPt+"\\@"+NumidStr).prop('checked',true);
        $("#"+IdPt+"\\@"+NumidStr).parent().parent().css({'background-color':ChecekdColor[CheckedTime]});
        $("#"+IdPt+"\\@"+NumidStr).parent().parent().next('tr').css({'background-color':ChecekdColor[CheckedTime]});
        $("#scrollList").scrollTop(top);
        CheckedTime++;
    }else {
        ErrIndex++;
        obj.IDPT=IdPt;
        obj.BAR_CODE=NumidStr;
        obj.NUM=ErrIndex;
        var copy=Object.assign({},obj);//淺複製錯誤血袋
        err.push(copy);
        var errfilter=err.filter(function (element, index, arr) {
            return arr.indexOf(element)===index;
        });
        errUI(errfilter);
    }
    $("#NumId").val("");
}