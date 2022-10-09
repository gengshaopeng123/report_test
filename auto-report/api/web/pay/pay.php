<?php 
$payinfo = $_GET['return'];
$order_no = $_GET['order_code'];
$url_order_list = 'http://'.$_SERVER['SERVER_NAME'].'/shop/order';
$url_order_index = 'http://'.$_SERVER['SERVER_NAME'].'/shop/order';
 
?>
<html>
	<title>力士通达</title>
   	<meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
   	<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
    <script type="text/javascript">

        window.onload = function () {
            if (typeof WeixinJSBridge == "undefined") {
                if (document.addEventListener) {
                    document.addEventListener('WeixinJSBridgeReady', jsApiCall, false);
                } else if (document.attachEvent) {
                    document.attachEvent('WeixinJSBridgeReady', jsApiCall);
                    document.attachEvent('onWeixinJSBridgeReady', jsApiCall);
                }
            } else {
                jsApiCall();
            }

        };
        function jsApiCall() {
			WeixinJSBridge.invoke(
			'getBrandWCPayRequest',
			<?php echo $payinfo; ?>,
			function(res){
				WeixinJSBridge.log(res.err_msg);
				if(res.err_msg == "get_brand_wcpay_request:ok"){
						 window.location.href = "<?php echo $url_order_list; ?>"
				}else if(res.err_msg == "get_brand_wcpay_request:cancel"){
						 window.location.href = "<?php echo $url_order_index; ?>"
				}else if(res.err_msg == "get_brand_wcpay_request:fail"){
						 window.location.href = "<?php echo $url_order_index; ?>"			
				}
			}
		);
     }
    </script>
</html>
