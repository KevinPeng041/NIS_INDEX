<?php
$sUr="00FUZZY";
$sfm="BSOR";

if ($sfm=="BSOR"){
    $shape="circle";
    $Title_NM="壓瘡評估作業";
}
if ($sfm=="TPUP"){
    $shape="triangle";
    $Title_NM="管路評估作業";

}
if ($sfm=="CUTS"){
    $shape="square";
    $Title_NM="傷口評估作業";

}



?>
<!DOCTYPEhtml>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>壓瘡評估作業</title>
    <script type="text/javascript" src="../../jquery-3.4.1.js"></script>
    <link rel="stylesheet" href="../../bootstrap-4.3.1-dist/css/bootstrap.min.css">
    <script src="../../bootstrap-4.3.1-dist/js/bootstrap.min.js" type="text/javascript"></script>
    <script src="../../crypto-js.js"></script>
    <script src="../../AESCrypto.js"></script>
    <script src="../../NISCOMMAPI.js"></script>
    <script src="../../JavaScript/jquery-ui.js"></script>
    <script>
       $(document).ready(function () {
           (function () {
               NISPWSFMINI_Timer('ILSGA','A');
               $(".EDIT").hide();
               $(".Main,#MM_B").hide();
           })();

           let imageLoaded = function() {
               let Canvas=$("#CanvasPad")[0];
               let ctx=Canvas.getContext('2d');
               let img=$("img")[0];

               ctx.drawImage(img,0,0,Canvas.width,Canvas.height);
           };

           $("img").each(function () {
              let tmpImg=new Image();
               tmpImg.onload=imageLoaded;
               tmpImg.src=$(this).attr('src');
           });


           //Img=>x,y座標
           //DATA=>Table 欄位值
           //TD_Child=>Table欄位元件
           //TR_CLASSNM=>Table標題欄位名稱
            let Data_obj=new Map();


           var BEDwindow,Serchwindow;

           const creatTable={
               Default:(obj)=>{
                let Tb_NM_obj=obj.T_NM; // 標題
                let T_ID=[]; // 標題id
                let T_CNM=obj.T_CNM; // td children
                let MM_TEXT_obj=obj.MM_TEXT; //壓傷備註
                let D_edit=obj.D_EDIT; //身高體重
                let Is_Change=obj.IS_CHANGE; //壓瘡=>壓傷


                   $(".area-table").children('table').remove();

                   $(".area-table").append(
                       `
                        <table class="table" id="Data_Table" ">

                        </table>
                        `
                   );

                   console.log(obj);

                   $.each(Tb_NM_obj,function (index,val) {
                       //新增左標題
                       let classNm=val.ID_TABITEM===""?'tb'+index:val.ID_TABITEM;


                       $("#Data_Table").append(
                           `
                                       <tr class="${classNm}">
                                             <th>${val.ST_LEFT}</th>
                                       </tr>
                                                 `
                       );

                       T_ID.push(classNm);
                   });

                   if (!Data_obj.has('TD_Child')){
                       Data_obj.set('TD_Child',T_CNM);
                   }
                   if (!Data_obj.has('TR_CLASSNM')){
                       Data_obj.set('TR_CLASSNM',T_ID);
                   }
                   if (!Data_obj.has('NEWDATA')){
                       Data_obj.set('NEWDATA',obj.ST_DATAB);
                   }
                   Data_obj.set('MAXNUM',obj.MAXNUM);


                   creatTable.inEdit(D_edit);
                   if (Is_Change==="Y"){
                       $("#T_Btn~label:first,h1").text(function () {
                           let str=$(this)[0].innerText;
                           let reg=/壓瘡/g;
                           $(this)[0].innerText= str.replace(reg,'壓傷');
                       });

                   }
                   if (MM_TEXT_obj!==null){
                       creatTable.inMMText(MM_TEXT_obj);
                    }

               },
               inTableTd:(Page,data)=>{
               if (Page==="A"){

                   if (!Data_obj.has("O_DATA")){
                       //存原始資料

                       let copy=JSON.parse(JSON.stringify(data));
                       Data_obj.set("O_DATA",copy);
                   }

                    if (!Data_obj.has('IMG') ){
                        Data_obj.set('IMG',data);

                    }

                    $.each(data,function (index,val) {
                        let LEFT=parseInt(val.LEFT);
                        let TOP=parseInt(val.TOP);
                        let Width=parseInt(val.W_TH);
                        let Height=parseInt(val.H_TH);
                        AddShape(val.NUM,'<?php echo $shape?>',LEFT+5,TOP-10,Width,Height);
                    });

                }
               if (Page==="B"){
                    let T_CD=Data_obj.get("TD_Child");
                    let T_ID=Data_obj.get("TR_CLASSNM");
                    let Data_json=Data_obj.get('DATA');
                    let sSTAT=  $("#sSTAT").val() ;//護理級值
                    data.forEach((value)=>{value.SSTAT=sSTAT});

                   if (Data_obj.has('DATA')){
                       //判斷是否新增過
                       Data_json.push(data);
                   }
                   else {
                       Data_obj.set('DATA',data);
                   }


                   data= Data_obj.get('DATA')
                        .flat(Infinity);

                   Data_obj.set('DATA',data);

                   $.each(data,function (ItemNo,item) {
                      let DATA=item.TB_DATA;
                      let FORMSEQ=item.FORMSEQ;         //表單編號
                      let No_Number= DATA.NO_NUM.VALUE; //編號

                      let count_element=0;
                       for (let key in DATA){
                           let ELE_Type=DATA[key].TYPE;
                           let ELE_Val=DATA[key].VALUE;
                           let title_ID=T_ID[count_element];


                           if ($("#"+title_ID+"_"+ItemNo+"_"+No_Number).length===0){

                               if (ELE_Type==="CB"){
                                   $("."+title_ID).append(
                                       `
                                        <td>
                                            <select id="${title_ID+"_"+ItemNo+"_"+No_Number}" class='table-edit'></select>
                                        </td>
                                    `
                                   );

                                   $.each(T_CD[count_element],function (index,val) {
                                       $("#"+title_ID+"_"+ItemNo+"_"+No_Number).append(
                                           `<option value="${val.ID_TABITEM}" >${val.ST_LEFT}</option>`
                                       );

                                       if (ELE_Val.trim()!==""){
                                           $("#"+title_ID+"_"+ItemNo+"_"+No_Number+" option[value="+ELE_Val+"]").attr('selected',true);
                                       }

                                   })


                               }

                               if (ELE_Type==="ET"){
                                   $("."+title_ID).append(
                                       `
                                          <td>
                                               <input  type="text" class='table-edit' id="${title_ID+"_"+ItemNo+"_"+No_Number}" value="${ELE_Val}">
                                          </td>
                                        `
                                   );

                               }


                           }
                           count_element++;
                       }
                      });

                }
               },
               inMMText:(data)=>{

                    let En_CharCODE=66;
                    let div_nm={"66":"壓瘡等級說明","65":"護理措施"};
                   for (let key in data){
                       $(".MM_TEXT").append(
                        `
                        <div id="${'MM_'+String.fromCharCode(En_CharCODE)}">
                            <label><b>${div_nm[En_CharCODE]}</b></label>
                        </div>
                        `
                       );


                       $.each(data[key],function (index,val) {
                           $("#MM_"+String.fromCharCode(En_CharCODE)).append(
                               `
                              <p>${val}</p>

                         `
                           );
                       });

                       En_CharCODE--;

                   }

                },
               inEdit:(data)=>{

                  let txtArea_Nm=data.EDIT_AREA;
                  let txtInput=data.EDIT;


                  for (let key in txtArea_Nm) {
                      $(".txtArea").append(
                          `
                           <label for="textArea">${txtArea_Nm['TB_NM']+":"}</label>
                           <textarea id="textArea" class="form-control form-control-lg " readonly></textarea>
                          `
                      )
                  }
                  $.each(txtInput,function (index,val) {
                      $(".txtInput").append(
                          `
                            <div class="input-group-prepend">
                                <span class="input-group-text">${val.TB_NM}</span>
                            </div>
                            <input type="text" class="form-control " readonly>
                          `
                      );
                  });



               }
           };
           var drag_value={
                   containment:'.drop-area',
                   scroll:false,
                   stack:'.draggable',
                   start:function (e) {

                   },
                   drag:function (e) {

                   },
                   stop:function (event, ui) {
                       let Num=$(this).attr('id').split("").filter((value)=>{return !isNaN(parseInt(value));}).join("");
                       let TOP=(Math.round(ui.position.top)).toString();
                       let LEFT=(Math.round(ui.position.left)).toString();
                       let DataObj=Data_obj.get('IMG');
                       let ItemIndex ="";
                       $.each(DataObj,function (index,val) {
                            if (val.NUM===Num){
                                ItemIndex=index;
                            }
                        });

                       const Width=$(this).css("width",function (index,value) {
                           return parseInt(value.split("px")[0])
                       })[0].offsetWidth;

                       const Height=$(this).css("height",function (index,value) {
                           return parseInt(value.split("px")[0])
                       })[0].offsetHeight;


                       let middelTop=Math.floor(ui.position.top+Height/2);
                       let middelLeft=Math.floor(ui.position.left+Width/2);



                       Data_obj.get('IMG')[ItemIndex].TOP=TOP;
                       Data_obj.get('IMG')[ItemIndex].LEFT=LEFT;

                       GetPIXELRegion(Num,middelLeft,middelTop);

                   }
               };
           var Get_AJson,Get_BJson=false;

           $(".drop-area").on("click",function (e) {
               //e.clientX , e.clientY
               let is_Add=$("#AddSign").val() === "0";
               if (is_Add){

                  let shape='<?php echo $shape?>';

                  //取Canvas上圖形最大號 若為空,num=0


                  let num= Data_obj.get('MAXNUM')==="0"?0:parseInt(Data_obj.get('MAXNUM'));

                  let MaxNum=num+1;
                  Data_obj.set('MAXNUM',MaxNum.toString());//回壓最大直


                  let copyObj=JSON.parse(JSON.stringify(Data_obj.get('NEWDATA')));
                  copyObj.TB_DATA.NO_NUM.VALUE=MaxNum.toString();
                  creatTable.inTableTd('B',[copyObj]);

                  AddShape(MaxNum,shape,e.offsetX+10,e.offsetY,15,15);
                  GetPIXELRegion(MaxNum,e.offsetX+7,e.offsetY+7);
                  $("#AddSign").val("");
               }
           });
           $(document).on('click','button',function (e) {
              let id=$(this).attr('id');
              let sTraID=$("#sTraID").val();
              switch (id) {
                  case "sbed":
                      if (!checkBEDwindow()){
                          alert("責任床位視窗已開啟");
                          break;
                      }else {
                          try {
                              BEDwindow=window.open("/webservice/NISPRWCBED.php?str="+AESEnCode("sFm=ILSGA&sIdUser=<?php echo $sUr?>"),
                                                    "責任床位",'width=850px,height=650px,scrollbars=yes,resizable=no');

                          }catch (e) {
                              console.log(e);
                          }
                      }

                      BEDwindow.bedcallback=bedcallback;
                      break;
                  case "SubmitBtn":
                      const sDt=$("#sDate").val();
                      const sTm=$("#sTime").val();
                      const Freq=$("#FORMSEQANCE").val();
                      const Page=$("#Page").val();
                      const sUr="<?php echo $sUr?>";
                      let Json_obj=Page==="A"?cmp(Data_obj.get('O_DATA'),Data_obj.get('IMG')): Data_obj.get('DATA');

                       DB_WSST(Page,sTraID,JSON.stringify(Json_obj),sDt,sTm,'',Freq,sUr,'true');
                      break;
                  case "SerchBtn":
                      if (!checkSerchwindow()){
                          alert("查詢視窗已開啟");
                          break;
                      }else {
                          const IdPt=$("#DA_idpt").val();
                          const InPt=$("#DA_idinpt").val();
                          const PName=$("#DataTxt").val();
                          Serchwindow=window.open("/webservice/NISPWSLKQRY.php?str="+
                              AESEnCode("sFm="+"<?php echo $sfm?>"+"&PageVal="+""+"&DA_idpt="+IdPt+"&DA_idinpt="+InPt
                                  +"&sUser="+"<?php echo $sUr?>"+"&NM_PATIENT="+PName)
                              ,"<?php echo $sfm?>",'width=750px,height=650px,scrollbars=yes,resizable=no');
                      }
                      Serchwindow.Serchcallback=Serchcallback;
                      break;
                  default:
                      break;
              }
           });
           $(".sign").on('click',function () {
               $("#AddSign").val($(this).val());
               changeThisSize($(this).val());
           });
           $(".Page>button").on('click',function () {
               const Page=$(this).attr('id');
               let sTraID=$("#sTraID").val();
               let obj="";
               let TransPage=Page==="A"?"B":"A";
               if (Page==="A"){
                    if (!Get_AJson){
                        GetPageJson('A',sTraID);
                        Get_AJson=true;
                    }

                   obj=Data_obj.get('DATA');

                   $(".area-table,#MM_B").hide();
                   $(".Main,#MM_A").show(500);
               }
               else {
                   if (!Get_BJson){
                       GetPageJson('B',sTraID);
                       Get_BJson=true;
                   }
                   let old_obj=Data_obj.get('O_DATA');
                   let new_obj=Data_obj.get('IMG');
                   obj=cmp(old_obj,new_obj);

                   $(".area-table,#MM_B").show();
                   $(".Main,#MM_A").hide(500);
               }
               $("#Page").val(Page);

               if(Get_AJson && Get_BJson){
                   DB_WSST(TransPage,sTraID,JSON.stringify(obj),'','','','','','false');
               }
           });
           $(document).on("click mousedown",".draggable",function (e) {
               let is_Add=$("#AddSign").val() === "0";
               let ThisDiv_id=$(this).attr('id');
               let Num=ThisDiv_id.substr(1,ThisDiv_id.length);

               $("#div_nm").val(ThisDiv_id);

               $(".table-edit").each(function(index,value){
                   $(value).attr('id')
                       .split("_")
                       .forEach((val,index,arr)=>{
                           let ele=arr.join("_");
                           if(arr[2]===Num)
                           {

                               if (val==="tb0"){
                                   let Num=$("#"+ele).val();
                                   $("#NO_NUM").val(Num);
                               }
                               if (val==="tb1"){
                                   let Region=$("#"+ele).val();
                                   $("#NO_REG").val(Region);

                               }

                           }


                       });
               });






               if (!is_Add){
                   $('.sign').css({'background-color' : '' ,'color':'',"border":""});
               }

           });

           //動態填值
           $(document).on("change",".table-edit",function () {

              let TD_class=$(this).parent().parent().attr('class');

               let rowIndex=$(this).parent().index()-1;
               let Index=Data_obj.get('TR_CLASSNM').map((value,index)=>{
                   if (TD_class===value){
                       return index;
                   }

               }).join("");
               let ThisData=Data_obj.get('DATA')[rowIndex].TB_DATA;


               let i=0;
               for (let key in ThisData){
                   if (parseInt(Index)===i)
                   {
                       ThisData[key].VALUE=$(this).val();

                   }
                   i++;
               }
           });
           $(document).on('change','input[name=sRdoDateTime]',function () {

               let TimeNow=new Date();
               let yyyy=TimeNow.toLocaleDateString().slice(0,4);
               let MM=(TimeNow.getMonth()+1<10?'0':'')+(TimeNow.getMonth()+1);
               let dd=(TimeNow.getDate()<10?'0':'')+TimeNow.getDate();
               let  h=(TimeNow.getHours()<10?'0':'')+TimeNow.getHours();
               let  m=(TimeNow.getMinutes()<10?'0':'')+TimeNow.getMinutes();
               let Timeit=($(this).val()).split("");

               let timer=Timeit.filter(function (value) { return  value!==":"});
               let timerVal=$(this).attr('id')==="ISTM00000005"?h+m:timer.join("");
               let time_ID=$(this).attr('id');
               $("#sDate").val(yyyy-1911+MM+dd);
               $("#sTime").val(timerVal);
           });

           function AddShape(txt,shape,X,Y,W,H) {
               let text_ele="";
               let shape_Nm="";

               if (txt==="")return;
               if (shape==="triangle"){
                   text_ele='<div>'+txt+'</div>';
                   shape_Nm="t";
               }
               if (shape==="square"){
                   text_ele="<div>"+txt+"</div>";
                   shape_Nm="s";
               }
               if (shape==="circle"){
                   text_ele="<p>"+txt+"</p>";
                   shape_Nm="c";
               }

               if ($('#'+shape_Nm+txt).length >　0){
                   return ;
               }

               $("#CanvasPad").before(
                   `
                      <div class="${'draggable '+shape}" id="${shape_Nm+txt}" >
                           ${text_ele}
                      </div>
                   `
               );
               $("#"+shape_Nm+txt).css({
                  "left" :X+"px",
                  "top" :Y+"px",
                  "width" :W+"px",
                  "height" :H+"px"
               });

             const isAdd= Data_obj.get('IMG').filter((value,index,arr)=>{
                   return value.NUM===txt;
               });

                if (isAdd.length===0){
                    let newObj=JSON.parse(JSON.stringify(Data_obj.get('IMG')[0]));

                    newObj.NUM=txt.toString();
                    newObj.LEFT=X.toString();
                    newObj.TOP=Y.toString();
                    newObj.W_TH=W.toString();
                    newObj.H_TH=H.toString();
                    newObj.DT_START="";
                    Data_obj.get('IMG').push(newObj);
                }
               

               $(".draggable").each(function () {
                   $(this).draggable(drag_value);
               });
           }
           function checkBEDwindow() {

               if(!BEDwindow){
                   return true;
               }else {
                   return !!BEDwindow.closed;
               }
           }
           function checkSerchwindow() {
               if(!Serchwindow){
                   return true;
               }else {
                   return !!Serchwindow.closed;
               }
           }
           function changeThisSize(num) {

               if (parseInt(num)<2){
                   return false;
               }
               let id=$("#div_nm").val();


               let n=num==="2"?10:-10;
               let shape=id.substring(0, 1) ;

               let ele=$("#"+id);
               let W_H="";

               if (shape==="t")
            {

                  let border_width=ele.css('border-width')
                      .split(" ")
                      .map(value => parseInt(value)+n+'px');
                  W_H={
                      "border-width":border_width.join(" ")
                  }
            }
            else
            {

                  W_H={
                      "height":ele.innerHeight()+n,
                      "width": ele.innerWidth()+n,
                      "background-color":""
                  };
              }

               $("#"+id).css(W_H);
           }
           function bedcallback(data){
               let str=AESDeCode(data);
               let dataObj=JSON.parse(str)[0];
               let idPt=dataObj.IDPT;
               let INPt=dataObj.IDINPT;
               let sBed=dataObj.SBED;
               let P_NM=dataObj.DataTxt;
               let ssTAT=dataObj.sSTAT;

               Data_obj.clear();

               $("#DA_idpt").val(idPt);
               $("#DA_idinpt").val(INPt);
               $("#DA_sBed").val(sBed);
               $("#DataTxt").val(P_NM);
               $("#sSTAT").val(ssTAT) ;

               GetINIJson('<?php echo 'BSOR'.$sfm?>',idPt,INPt);

                $(".Page").next('div').hide();
                $(".area-table").hide();
                $(".draggable").remove();

               Get_AJson=false; //GetPageJson false
               Get_BJson=false;


           }
           function Serchcallback(AESobj){
              const  obj=JSON.parse(AESDeCode(AESobj));
              console.log(obj);
              const sTraID=obj.sTraID;
              const sTime=obj.TMEXCUTE;
              const sDate=obj.DTEXCUTE;
              const IDPT=obj.IDPT;
              const INPT=obj.INPT;
               $("#sTime").val(sTime);
               $("#sDate").val(sDate);
               $("#sTraID").val(sTraID);




               if ($(".table-edit").parent().length>0){
                   $(".table-edit").parent().remove();
                   Data_obj.get('DATA').length=0;
               }
               $(".draggable").remove();


               $(".EDIT").hide();
               $(".Main,.B,#MM_B").hide();
               Get_AJson=false; //GetPageJson false
               Get_BJson=false;
           }
           function GetINIJson(sfm,idPt,INPt){
               $.ajax("/webservice/NISPWSTRAINI.php?str="+AESEnCode('sFm='+sfm+'&idPt='+idPt+'&INPt='+INPt+"&sUr="+'<?php echo $sUr?>'))
                   .done(function(data) {
                        let obj= JSON.parse(AESDeCode(data));
                       creatTable.Default(obj);
                       $("#sTraID").val(obj.sTraID);
                       $("#sSave").val(obj.sSave);
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
               $.ajax({
                   url:"/webservice/NISPWSGETPRE.php?str="+AESEnCode("sFm=BSOR&sTraID="+sTraID+"&sPg="+Page),
                   dataType:"text",
                   async:false,
                   success:function (data){
                         let obj= JSON.parse(AESDeCode(data));

                         creatTable.inTableTd(Page,obj);

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
           function GetPIXELRegion(Num,X,Y){
               $.ajax({
                   url:"/webservice/NISBSORPIXEL.php?str="+AESEnCode('&PIXEL_X='+X+'&PIXEL_Y='+Y)
               }) .done(function(data) {
                   let obj= JSON.parse(AESDeCode(data));
                   const Region=obj.NM_REGION===""?"不正確":obj.NM_REGION;
                   $(".table-edit").each(function(index,value){
                       $(value).attr('id')
                           .split("_")
                           .forEach((val,index,arr)=>{
                               if (val==="tb1" && arr[2]===Num.toString()){
                                   let ele=arr.join("_");
                                   $("#"+ele).val(Region);
                                   $("#NO_NUM").val(Num);
                                   $("#NO_REG").val(Region);
                               }
                           });
                   });
                    //新增部位名稱
                   $.each( Data_obj.get('DATA'),function (index,obj) {
                      if (obj.TB_DATA.NO_NUM.VALUE===Num.toString()){
                           obj.TB_DATA.NM_ORGAN.VALUE=Region;
                       }
                   });
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
               $.ajax('/webservice/NISPWSSETDATA.php?str='+AESEnCode(
                   'sFm='+"<?php echo $sfm?>"+'&sTraID='+sTraID+'&sPg='+Page+'&sData='+json+
                   '&sDt='+sDt+'&sTm='+sTm+'&Fseq='+Freq+'&PASSWD='+Passed+
                   '&USER='+sUr+'&Indb='+InSertDB)
               )
                   .done(function (data) {
                       let json=JSON.parse(AESDeCode(data));

                       console.log(Page,json);

                      if(InSertDB==="true" && json.result==="true"){
                           alert('存檔成功');
                           window.location.replace(window.location.href);
                       }
                       if(InSertDB==="true" && json.result!=="true"){
                           alert("儲存失敗,錯誤訊息:"+json.message);
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
           function cmp(x,y) {

               //x=>old_obj
               //y=>new_obj
               if (typeof (x)=="undefined" || typeof (y)=="undefined"){
                   return false;
               }


               let CompareLength=x.length; // 原始長度

               let copy=JSON.parse(JSON.stringify(y));
               let compareArr=copy.splice(0,CompareLength);

               return x.map((val, index) => {
                   let diff;
                   for (const p in val) {
                       if (val[p] !== compareArr[index][p]) {
                           diff = compareArr[index];
                       }
                   }
                   return diff ;

               }).filter(res=>{
                   if ( typeof res=="object"){
                       return res;
                   }

               }).concat(copy);
           }

        });

    </script>
</head>
<style>
  /*  .Parametertable*/
      .drop-area{
          width: 395px;
          height: 425px;

      }

      .area-table{
          display: none;
          border: 1px solid #dee2e6;
          overflow-x:auto;
      }

    table {
        border: 1px solid #dee2e6;
          table-layout: auto;
          width: 100%;
      }

      tr>th{
          position: sticky;
          left: 0;
          min-width: 200px;
          z-index:1;
          background-color: white;
      }

      td,th,tr{
          border: 1px solid #dee2e6;
      }


    .container .title button{
        color: white;
        font-size: 4vmin;
        margin-top: 5px;
        margin-bottom: 5px;
    }
    #DataTxt{
        font-size: 4.5vmin;
        background-color: #FFFBCC;
        border-radius:3px;
        margin-top: 5px;
        color: black;
    }
    .container .Otimer{
        margin:5px 0 5px 15px;
        padding:5px 10px 0 0;
        font-size: 4vmin;
        background-color: #baeeff;
        border-radius:3px;
    }


    .Page~div{
        margin-top: 10px;
      }



/*************繪圖*****************/
  #CanvasPad{
      width: 395px;
      height: 425px;
      z-index: 1;
      border: 1px grey solid;
  }


  .circle{
      border-radius: 50%;
      height: 15px;
      width: 15px;
      user-select: none;
      display: flex;
      align-items: center;
      justify-content: center;
      position: absolute;
      border: 3px solid red;
      z-index: 0;
  }
  .circle>p{
      padding-top: 13px;
      z-index: 1;

  }


  .triangle{
      height: 0px;
      width: 0px;
      border-color: transparent transparent #FF9797 transparent;
      border-style: solid solid solid solid;
      border-width:  0 5px 10px 5px;
      position: absolute;
      display: flex;
  }

  .square{
      height: 15px;
      width: 15px;
      border: 2px solid red;
      position: absolute;
      display: flex;
  }


  .triangle>div {
      margin-left: -6px;
      margin-top: 2px;
      font-size: 10px;
  }

  .square>div{
      text-align: center;

  }


/*時間*/
  .DateTime::placeholder{
      font-size: 3.5vmin;
  }
  .DateTime{
      font-size: 3.5vmin;
  }
  #ISTM>label{
      padding:10px 0 0 0 ;
  }


  /*備註*/
.MM_TEXT>div{
   border: none;
    background-color: #FFFBCC;
}
.MM_TEXT>div:nth-child(1){
    display: none;
}
  .MM_TEXT>div>P{

      font-size: 2vmin;
  }

  textarea{
      overflow-y: scroll;
      resize: none
  }
  .form-control[readonly]{
      background-color: white;
  }

   .Main>div>div{
       margin: 15px 0 0 0;
   }
    .CanvasRow{
        margin: 0 15px 0 0;
    }




</style>
<body>
<div class="container">
    <div class="Parametertable">
        <input id="DA_idpt"     value=""  type="text"  placeholder="病歷號">
        <input id="DA_idinpt"   value=""  type="text"  placeholder="住院號">
        <input id="DA_sBed"     value=""  type="text"  placeholder="床位">
        <input id="FORMSEQANCE"     value=""  type="text"  placeholder="表單編號">
        <input id="sSTAT"     value=""  type="text"  placeholder="護理站代碼">
        <input id="sSave"       value=""  type="text"  placeholder="存檔權限">
        <input id="sTraID"      value=""  type="text"  placeholder="交易序號">
        <input id="div_nm"      value=""  type="text"  placeholder="所選圖形">
        <input id="AddSign"     value=""  type="text"  placeholder="是否新增">
        <input id="Page"     value=""  type="text"  placeholder="頁面">
        <input id="sfm"         value="<?php echo $sfm?>" type="text">
        <img src="../../img/BedSore.jpg" style="display: none">
    </div>

    <div class="row">
        <div class="col-12">
            <h1><?php echo $Title_NM?></h1>
        </div>

        <div class="col-12">

            <span class="title">
                <button type="button" id="SubmitBtn" class="btn btn-primary btn-md" >儲存</button>
                <button type="button" id="SerchBtn" class="btn btn-primary btn-md" >查詢</button>
                <button type="button" id="DELBtn" class="btn btn-primary btn-md"  data-toggle="modal" data-target="#DELModal">作廢</button>
                <button type="button" id="ReSetBtn" class="btn btn-primary btn-md"  >清除</button>
                <button type="button"  class="btn btn-warning btn-md"  id="sbed" >責任床位</button>
            </span>

            <button type="button" class="btn btn-secondary btn-md" disabled style="display: none">回主畫面</button>

            <span style="margin-left: 1px">
                <b>使用者:<?php echo $sUr?></b>
            </span>

        </div>

        <div class="col-12">
            <input id="DataTxt" value="" class="form-control" type="text" disabled>
        </div>


        <div class="Otimer  col-lg-11">
            <div class="row">
                <div class="input-group col-12">
                    <label for="sDate" >評估時間:</label>
                    <input type="text" id="sDate" value="" class="DateTime form-control form-control-lg"  placeholder="YYYMMDD" maxlength="7" autocomplete="off">
                    <input type="text" id="sTime" value="" class="DateTime form-control form-control-lg" placeholder="HHMM" maxlength="4" autocomplete="off">
                </div>


                <div class="input-group col-12" id="ISTM">

                </div>


                </div>

        </div>

        <div class="Page col-12">
            <button type="button" id="B" class="btn btn-primary btn-lg">評估資料</button>
            <button type="button" id="A" class="btn btn-primary btn-lg" >部位圖</button>
        </div>


        <div class="Main col-12">
            <div class="row">
                <div class="CanvasRow col-lg-5 col-md-12 col-sm-12">

                    <div class="drop-area">

                        <canvas id="CanvasPad" height="425" width="395" ></canvas>


                    </div>
                </div>

                <div class="SignRow col-lg-7 col-md-12 col-sm-12">
                    <div class="col-12">
                        <div class="input-group input-group-sm mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="inputGroup-sizing-sm">編號</span>
                            </div>
                            <input type="text" id="NO_NUM" class="form-control" aria-label="Small" aria-describedby="inputGroup-sizing-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="inputGroup-sizing-sm">部位</span>
                            </div>
                            <input type="text" id="NO_REG" class="form-control" aria-label="Small" aria-describedby="inputGroup-sizing-sm">
                        </div>

                        <button  class="sign btn btn-outline-primary" value="0">新增</button>
                        <button  class="sign btn btn-outline-primary" value="1">修改</button>
                        <button  class="sign btn btn-outline-primary" value="2">+</button>
                        <button  class="sign btn btn-outline-primary" value="3">-</button>
                    </div>

                    <div class="col-12">
                         <div class="EDIT row">
                              <div class="txtArea col-12">

                              </div>

                              <div class="txtInput col-12 input-group">

                              </div>

                              <div class="MM_TEXT col-12">

                              </div>
                        </div>
                    </div>


                </div>

            </div>

        </div>

        <div class="B area-table">

        </div>

    </div>

</div>


</body>
</html>
