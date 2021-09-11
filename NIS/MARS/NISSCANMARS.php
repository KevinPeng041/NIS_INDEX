<!DOCTYPEhtml>
<html lang="en">
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<title>條碼掃描</title>
<script type="text/javascript" src="../../jquery-3.4.1.js"></script>
<link rel="stylesheet" href="../../bootstrap-4.3.1-dist/css/bootstrap.min.css">
<script src="../../bootstrap-4.3.1-dist/js/bootstrap.min.js" type="text/javascript"></script>
<script src="../../crypto-js.js"></script>
<script src="../../AESCrypto.js"></script>
<script src="../../JavaScript/jquery-ui.js"></script>
<script>
    $(document).ready(function () {
        let itemIndex = 0;
        let HisKey_arr = [];
        $("textarea").css({
            "background-color": "white"

        });
        //條碼格：病歷號|UD日期|UD時間|院內碼1|醫令序1|院內碼2|醫令序2…^^


        //@01167070_28|1100910|1700|^OSEFT|L970000523@28|^OKODT|L970000523@29|^OACE|L970000523@30^^@#
        //@01134556O|1100910|1700|1100916|1300|^OWANS|L970000763@31^-,
        //01134556S|1100910|1550|^OWANS|S970000763@1#1100910@18@3^-@#

        $("#CodeInput").on("input propertychange", function () {
            let InputText = $(this).val();
            let textareaText = $("textarea").val();
            if (InputText === "") {
                return;
            }

            textareaText += InputText;
            $("textarea").val(textareaText);
          //  itemIndex++;
            HisKey_arr.push(InputText);
            //$("span").text(itemIndex);
            $(this).val("");
        });

        $("button").click(function () {

            let aa = [
                '@01167070_28|1100910|1700|^OSEFT|L970000897@25|^OKODT|L970000523@29|^OACE|L970000523@30^^@#',
                '@01167070_29|1100910|2100|^OKODT|L970000523@29^^@#',
                '@01167070_5|1100911|0900|^OD50|L970000523@5|^OBUM|L970000523@28|^OKODT|L970000523@29|^OACE|L970000523@30^^@#'
            ];

            //  let response = AESEnCode(JSON.stringify(HisKey_arr));
            let response = AESEnCode(JSON.stringify(aa));
            window.ScanCodeBack(response);
            window.close();
        });
    });
</script>
<style>
    .btn-primary {
        background-color: #337ab7;
        border: #337ab7;
    }

    .btn-primary:hover {
        background-color: #2e6da4;
    }

    .badge {
        background-color: white;
        color: #337ab7;
        left: 5px;
    }
</style>
<body>
<div class="container">
    <diV class="row">
        <div class="col-8  mt-3">
            <h1>掃描條碼</h1>
        </div>
        <div class="col-6 mt-2">
            <input id="CodeInput" class="form-control form-control-lg" type="text" placeholder="點擊開始掃描">
        </div>
        <div class="col-4  mt-2">
            <button type="button" class="btn btn-primary btn-lg">傳送<span class="badge">0</span></button>
        </div>
        <div class="col-6 mt-3">
            <textarea class="form-control" rows="20" disabled></textarea>
        </div>
    </diV>
</div>
</body>
</html>