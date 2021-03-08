
<!DOCTYPE html>
<html xmlns="http:/www.w3.org/1999/xhtml">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no" />
    <meta name="format-detection" content="telephone=no" />
    <meta http-equiv="x-ua-compatible" content="ie=edge" />
    <title></title>
    <link href="https:/www.mypay888.com/static/css/webBank_new.css" rel="stylesheet" />
    <script type="text/javascript" src="https:/www.mypay888.com/static/pay_static/jquery.min.js"></script>
    <script src="https:/www.mypay888.com/static/js/layer.js"></script>
    <script src="https:/www.mypay888.com/static/js/clipboard.min.js"></script>
    <script src="https:/www.mypay888.com/static/js/jquery.qrcode.min.js"></script>
    <script>
        document.addEventListener("touchstart", function () { }, false);
    </script>

    <style>
        .time-item strong {
            background: #3ec742;
            color: #fff;
            line-height: 25px;
            font-size: 15px;
            font-family: Arial;
            padding: 0 10px;
            margin-right: 10px;
            border-radius: 5px;
            box-shadow: 1px 1px 3px rgba(0, 0, 0, 0.2);
        }
        .langbar{text-align:right;padding-right:10px;margin-bottom:8px;}
        .langbar a{text-decoration:none;}
        .langbar .on{color:#990000;}

        .inforData_box ul li{width:100%;}
        .inforData_tile{padding-right:0;  }
        .bankli{display:none;}
        .notice_box ul{padding-left:8px}
        .nl li{list-style:none;}
        .inforData_tile{word-break:break-all;}
        #qrcode_cav{display:inline-block;}
        .qrcli{display:none;}
        .inforData_box ul li a{padding:3px 8px;}
        .m6{margin:6px;}
        .amount{font-size:28px;}
    </style>
</head>

<body>
<div class="contain_main">
    <div class="notice_box">
        <span langid="LANG_NOTICE">注意</span>
        <ul class="nl">
            <li langid="LANG_NOTICE_1">1. 请按照以下<b style="color:#0065CA">金额</b>汇款</li>
            <li id="changShowInfo" langid="LANG_NOTICE_2">2. 汇款账户必须要跟<b style="color:#0065CA">系统登记账户相同</b>，否则不能完成本交易</li>
            <li langid="LANG_NOTICE_3"></li>
            <li langid="LANG_NOTICE_4"><font color="red">3. 收款帐号会不定期更换，请勿自行转帐，概不负责。</font></li>
        </ul>
        <p class="m6" langid="LANG_NOTICE_6"></p>
    </div>

    <div class="inforData_box">
        <ul>
            <li>
                <span class="inforData_tile"><font langid="LANG_ORDERNO">订单号</font>:</span>
                <span>{{ $data['merchant_order_no'] }}&nbsp;</span>
            </li>


            <li class="bankli">
                <span class="inforData_tile"><font langid="LANG_BANK">银行</font>:</span>
                <span>{{ $data['bank_name'] }}&nbsp;</span>
            </li>
            <li class="bankli">
                <span class="inforData_tile"><font langid="LANG_BRANCH">支行</font>:
                </span>
                <span>{{ $data['branch'] }}&nbsp;
                </span>
            </li>
            <li class="bankli">
                <span class="inforData_tile"><font langid="LANG_ACCOUNT">账号</font>:
                </span>
                <span>{{ $data['account_number'] }}&nbsp;
                </span>
                <a data-clipboard-text="{{ $data['account_number'] }}" class="copyClass" tab="账号" lang="LANGV_ACCOUNT" lang_att="tab"><font langid="LANG_COPY">复制</font>
                </a>
            </li>
{{--            <content class="hint_t" langid="LANG_NOTICE_5">※不支持支付宝/QQ/微信等转银行卡方式转帐。</content>--}}
            <li class="bankli">
                    <span class="inforData_tile"><font langid="LANG_PAYEE">收款人</font>:
                    </span>
                <span>{{ $data['account_name'] }}&nbsp;
                    </span>
                <a data-clipboard-text="{{ $data['account_name'] }}" class="copyClass" tab="收款人" lang="LANGV_PAYEE" lang_att="tab"><font langid="LANG_COPY">复制</font>
                </a>
            </li>
            <li>
                <span class="inforData_tile"><font langid="LANG_AMOUNT">金额</font>:
                </span>
                <span id="amount" class="amount">{{ $data['amount'] }}元&nbsp;
                </span>
                <a data-clipboard-text="{{ $data['amount'] }}" currency class="copyClass" tab="金额" lang="LANGV_AMOUNT" lang_att="tab"><font langid="LANG_COPY">复制</font>
                </a>
            </li>
            <content class="hint_t" langid="LANG_AMOUNTTIP"></content>
            <p class="qrcli" style="display:none">
                <span class="inforData_tile" style="font-weight:bold;display:inline-block;">QRCODE:
                </span>
                <div class="qrcli" id="qrcode_cav" style="display:none;padding:8px;background-color:#fff;border:1px solid #ccc;"></div>
            </p>

            <p id="showbank">
{{--                <a id="bankurl" target="_blank" class="btn btn-primary"--}}
{{--                   style="margin-top: 10px;"><font langid="LANG_CLICKTO">点我进入</font>[交通银行]<font langid="LANG_ONLINE_BANK">网上银行</font></a>--}}
            </p>
        </ul>
        <div class="prompt_t">
            <img id="infoImg" style="display:none" src="https:/www.mypay888.com/static/img/icon_check.svg" />
            <span id="showInfo"></span>
        </div>

        <div class="time-item" style="padding-top: 5px" id="timeouter">
            <span class="red_t"><font langid="LANG_OVERTIME">超时剩余</font>：</span>
            <strong id="hour_show"><s id="h"></s>0<font langid="LANG_HOURS">时</font></strong>
            <strong id="minute_show"><s></s>0<font langid="LANG_MINUTES">分</font></strong>
            <strong id="second_show"><s></s>0<font langid="LANG_SECONDS">秒</font></strong>
        </div>
    </div>


</div>
</body>
<script>


    history.pushState(null, null, document.URL);
    window.addEventListener('popstate', function () {
        history.pushState(null, null, document.URL);
    });


    var qrcode_url=''
    var onlyqr='0';
    $(function(){
        var money='{{ $data["amount"] }}';
        $('#amount').html(money);
        var ShowTradeTimeout='1';
        if(ShowTradeTimeout=='1'){setTimeOuter();$('#timeouter').show();}
        if(qrcode_url){
            if(onlyqr=='1'){
                $('.bankli').hide();
            }else if(onlyqr=='2'){
                $('.bankli').show();return;
            }else{
                $('.bankli').show();
            }
            $('.qrcli').show()
            $('#qrcode_cav').qrcode({
                render:"canvas",
                width:220,
                height:220,
                text:qrcode_url
            });
        }else{
            $('.bankli').show();
        }

    });

    function resetWd(){
        var minw=0;
        var winw=window.innerWidth;
        if(winw>560){winw=560;}
        $('.inforData_tile').each(function(){
            var zw=$(this).width();
            if(minw<zw){minw=zw}
        });
        if(minw>200){minw=200}
        if((minw/winw)>0.4){minw=winw*0.4}
        $('.inforData_tile').css('width',(minw+12)+'px');
        var t1=$('.hint_t').text();
        if($.trim(t1)==''){
            $('.hint_t').css('margin-top','0')
        }else{
            $('.hint_t').css('margin-top','-18px')
        }
    }

    var myTimer;
    function timer(intDiff) {
        myTimer = window.setInterval(function () {
            var day = 0,
                hour = 0,
                minute = 0,
                second = 0;
            if (intDiff > 0) {
                day = Math.floor(intDiff / (60 * 60 * 24));
                hour = Math.floor(intDiff / (60 * 60)) - (day * 24);
                minute = Math.floor(intDiff / 60) - (day * 24 * 60) - (hour * 60);
                second = Math.floor(intDiff) - (day * 24 * 60 * 60) - (hour * 60 * 60) - (minute * 60);
            }
            if (minute <= 9) minute = '0' + minute;
            if (second <= 9) second = '0' + second;
            $('#hour_show').html('<s id="h"></s>' + hour + '时');
            $('#minute_show').html('<s></s>' + minute +  '分');
            $('#second_show').html('<s></s>' + second +  '秒');
            if (hour <= 0 && minute <= 0 && second <= 0) {
                clearInterval(myTimer);
            }
            intDiff--;
        }, 1000);
    }

    function setTimeOuter() {
        var nowTime = new Date()
        var nowTimeUnix = Math.floor(nowTime / 1000)
        var cTime = parseInt('{{ $data['expire_time']  }}')
        var timeout = parseInt('0')
        var time_left = timeout - (nowTimeUnix - cTime)
        timer(time_left);
    }

    var payname = ''
    var remark = ''
    if (remark != '') {
        var datas = remark.split(':')
        if (datas.length == 2) {
            if (datas[0] == 'payname') {
                payname = datas[1]
            }
        }

    }
    if (payname != '') {
        payname = '*' + payname.substring(1, payname.length)
        $("#payname").html(payname)
    }

    var clipboard = new ClipboardJS('.copyClass')

    clipboard.on('success', function (e) {
        $("#infoImg").show();
        $("#showInfo").html(e.trigger.getAttribute('tab') + " " + e.text + " 已复制");
        e.clearSelection();
    })
    clipboard.on('error', function (e) {
        layer.msg(e.trigger.getAttribute('tab') + ' 复制失败');
    });
    resetWd();

    /*
        var qrtimer
        times = '420' / 2

        var returl='&returnurl='+encodeURIComponent('');
        function payTimer() {
            times--
            if (times < 0) {
                clearTimeout(qrtimer);
                location.href = '/pay/payresult?result=error'+returl+'&info='+PAY_LANG['_PAY_FAILED_OVERTIME'];return;
            }
            $.post('/pay/status','tradeno=392b5da2a2b04cb48cf19be90f040791',function(res){
                var code = res.status;
                if($.inArray(code,[-1,2,100])>-1){
                    clearInterval(qrtimer);
                    if(!!res['msg']){
                        location.href = '/pay/payresult?result=error'+returl+'&info='+res.msg;
                    }else{
                        location.href = '/pay/payresult?result=error'+returl+'&info=unknown error code['+code+']';
                    }
                    return;
                }else if(code == 1){
                    clearInterval(qrtimer)
                    location.href = '/pay/payresult?result=success'+returl+'';return;
                }
            },'json')
        }
        qrtimer = setInterval(payTimer, 2000)

        function getQueryVariable(variable) {
            var query = window.location.search.substring(1);
            var vars = query.split("&");
            for (var i = 0; i < vars.length; i++) {
                var pair = vars[i].split("=");
                if (pair[0] == variable) { return pair[1]; }
            }
            return (false);
        }

        function isPC() {
            var userAgentInfo = navigator.userAgent;
            var Agents = ["Android", "iPhone",
                "SymbianOS", "Windows Phone",
                "iPad", "iPod"];
            var flag = true;
            for (var v = 0; v < Agents.length; v++) {
                if (userAgentInfo.indexOf(Agents[v]) > 0) {
                    flag = false;
                    break;
                }
            }
            return flag;
        }

        var changShowInfo = getQueryVariable('ex')
        if (changShowInfo == 'isalipay') {
            $('#changShowInfo').html('โปรดอย่าใช้วิธีการโอนเงินที่ไม่ใช่สุทธิตัวอย่างเช่น QQ ไมโครตัวอักษร□เมฆแฟลชจ่ายฯลฯ')
        }

        $("#showbank").hide()
        $('#timeouter').hide()
        if (changShowInfo == 'showbank') {
            $('#changShowInfo').html('โปรดอย่าใช้วิธีการโอนเงินที่ไม่ใช่สุทธิตัวอย่างเช่น QQ ไมโครตัวอักษร□เมฆแฟลชจ่ายฯลฯ')
            $.getJSON("/static/js/bankurls.json", function (data) {
                $("#showbank").show()
                if (isPC()) {
                    $("#bankurl").attr("href", data['交通银行'][1])
                } else {
                    $("#bankurl").attr("href", data['交通银行'][0])
                }

            });


            $('#timeouter').show()
            setTimeOuter()
        }
        */




</script>

</html>

