/*先引用crypto-js.js*/
function AESEnCode(text){
    var key = CryptoJS.enc.Latin1.parse('1234567890654321'); //為了避免補位，直接用16位的金鑰
    var iv = CryptoJS.enc.Latin1.parse('1234567890123456'); //16位初始向量
    var encrypted = CryptoJS.AES.encrypt(text, key, {
        iv: iv,
        mode:CryptoJS.mode.CBC,
        padding:CryptoJS.pad.ZeroPadding
    });
    return encrypted.toString();
}
function AESDeCode(text){
    var encrypted=text.toString();//先轉utf8字串
    var key = CryptoJS.enc.Latin1.parse('1234567890654321'); //為了避免補位，直接用16位的金鑰
    var iv = CryptoJS.enc.Latin1.parse('1234567890123456'); //16位初始向量
    var decrypted = CryptoJS.AES.decrypt(encrypted,key,{
        iv: iv,
        mode: CryptoJS.mode.CBC,
        padding:CryptoJS.pad.Pkcs7
    });
    decrypted=CryptoJS.enc.Utf8.stringify(decrypted);

    var s=decrypted.split("");
    var a='';
    for (var i=s.length;i>0;i--){
        if( s[0]=="[" && s[i]=="]"  ){
            a=i;
            break;
        }else if (s[0]=="{"  && s[i]=="}" )
        {
            a=i;
            break;
        }
    }

    return decrypted.slice(0,a+1);
}
