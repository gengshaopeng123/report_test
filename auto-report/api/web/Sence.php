<?php
	include '../../common/components/WeixinHelper.php';
	define("TOKEN", "kfq3mvldnsmg2rovofl4f033gsufav2v");
    //实例化对象
    $wechatObj = new wechatCallbackapiTest();

    $echoStr = $_GET["echostr"];
    //调用函数
    if (isset($echoStr)) {
        $wechatObj->valid();
    }else{
        $wechatObj->responseMsg();
    };

	class wechatCallbackapiTest
	{
		public function valid()
		{
			$echoStr = $_GET["echostr"];
			if($this->checkSignature()){
				ob_clean();
				echo $echoStr;
				exit;
			}else{
				write_log('认证失败');
				exit;
			}
		}

		public function responseMsg()
		{
			//$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];  
			
			//$postStr = isset($GLOBALS['HTTP_RAW_POST_DATA']) ? $GLOBALS['HTTP_RAW_POST_DATA'] : file_get_contents("php://input");
			
			if(isset($GLOBALS['HTTP_RAW_POST_DATA'])){
				$postStr = $GLOBALS['HTTP_RAW_POST_DATA'];
				#echo "GLOBALS['HTTP_RAW_POST_DATA']";
			}
			else{
				$postStr = file_get_contents('php://input');
				//echo "file_get_contents:".$postStr->FromUserName;
			}
			if (!empty($postStr)){
				libxml_disable_entity_loader(true);
				//禁止xml实体解析，防止xml注入
				$msg = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
				switch ($msg->MsgType) {
					case 'event':
					//推送事件
					if($msg->Event == 'subscribe'){
						//这里是扫描二维码事件
						//用户未关注的情况 
						$openid = $msg->FromUserName;  //这里是用户的openid
						$str = $msg->EventKey;
						//qrscene_123123   用户未关注的情况下KEY值: qrscene_为前缀，后面为二维码的参数值
						$arr = explode('_', $str);
						$mid = end($arr);     //这里是商户id
						$this->Weixinlogin($mid,$openid);
						//header("location:http://merchant.lanqiulm.com/custom/business/businessDetailList.html?mid=$mid");
						ob_clean();
						echo $resultStr;
						//授权登录
				   }else if($msg->Event == 'SCAN'){
						//这里是扫描二维码事件
						//用户关注的情况 
						$openid = $msg->FromUserName; //这里是用户的openid
						$mid = $msg->EventKey;   //这里是商户的id
						//调取授权
						//header("location:http://feather.lanqiulm.com/123.php");
						$this->Weixinlogin($mid,$openid);
						ob_clean();
						echo $resultStr;
					}
					break;
				}

			}

		}
			
		private function checkSignature()
		{
			if (!defined("TOKEN")) {
				throw new Exception('TOKEN is not defined!');
			}
			$signature = $_GET["signature"];
			$timestamp = $_GET["timestamp"];
			$nonce = $_GET["nonce"];
			$token = TOKEN;
			$tmpArr = array($token, $timestamp, $nonce);
			sort($tmpArr, SORT_STRING);
			$tmpStr = implode( $tmpArr );
			$tmpStr = sha1( $tmpStr );
			
			if( $tmpStr == $signature ){
				return true;
			}else{
				return false;
			}
		}

		public function httpGet($url)
		{
			try{
				$curl = curl_init();
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($curl, CURLOPT_TIMEOUT, 500);
				curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
				curl_setopt($curl, CURLOPT_URL, $url);
				$res = curl_exec($curl);
				curl_close($curl);
				if(!$res){
					return false;
				}else{
					return json_decode($res,true);
				}
			}
			catch(\Exception $e)
			{
				return false;
			}

		}
		public function Weixinlogin($mid,$openid)
		{
			$appid = 'wx6f58ce17ef7a4e66';
			$appsecret = '7277c6becc32b916e9b2456fe398864b';
			$wechat_id = $openid;
			$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appid&secret=$appsecret";
            $res = $this->httpGet($url);
			$token = $res['access_token'];
			$url    = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=$token&openid=$wechat_id&lang=zh_CN";
       		$res    = $this->httpGet($url);
        	if(isset($res['errcode']) && ($res['errcode'] == "42001" || $res['errcode'] == "40001"))
        	{
            	//$token  = self::getTokenTwo();
            	//$res    = self::httpGet($url);
        	}
			//$userid = isset(Yii::$app->session['user_id']) ? Yii::$app->session['user_id'] :'';
			$conn = mysqli_connect('127.0.0.1','root','1230.zxCV');
			mysqli_query($conn,'set names utf8');
			mysqli_select_db($conn,'BookingShop');
			$openid= $res['openid'];
			$name = preg_replace_callback('/[\xf0-\xf7].{3}/', function($r) { return '@A' . base64_encode($r[0]);},$res['nickname']);
			$avatar = $res['headimgurl'];
			$source = $mid;
			$type = 2;
			$sql = "select * from shop_user where account='".$openid."'";
			$rst = mysqli_query($conn,$sql);
			$row = mysqli_fetch_assoc($rst);
			if(!$row){
				$sql = "insert into shop_user(account,name,avatar,source,type) values('$openid','$name','$avatar','$source','$type')";
				mysqli_query($conn,$sql);
			}	
		}
	}

?>

