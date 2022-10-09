<?php
	
ob_start();
var_dump($_GET);
$result = ob_get_clean();
	$mid = $_GET['mid'];
	file_put_contents('midcan.txt',$result);
	file_put_contents('mmid.txt',$result);
	$appid  = 'wx6f58ce17ef7a4e66';
	$secret = '7277c6becc32b916e9b2456fe398864b';
	$code   = $_GET["code"];
	file_put_contents('code.txt',$code);
	$get_token_url='https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$appid.'&secret='.$secret.'&code='.$code.'&grant_type=authorization_code';
	try
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $get_token_url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		$res = curl_exec($ch);
		curl_close($ch);
		$json_obj = json_decode($res, true);
		if (!empty($res))
		{
			$json_obj = json_decode($res, true);
			if (!empty($json_obj)&&isset($json_obj['access_token'])&&isset($json_obj['openid']))
			{
				$wechat_id = $json_obj['openid'];
				$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appid&secret=$secret";
				$weixin = new WeixinHelper($appid,$secret);
				$userWxInfo = $weixin::getUserInfo($wechat_id);
				//$userid = isset(Yii::$app->session['user_id']) ? Yii::$app->session['user_id'] :'';
				$userModel =User::find()->select('id,account,avatar,name')->where(['account'=>$wechat_id])->one();
				if (!empty($userModel))
				{
					if(empty($userModel->name)){
						$up = User::updateAll([
							'avatar' => $userWxInfo['headimgurl'],
							'name' =>preg_replace_callback('/[\xf0-\xf7].{3}/', function($r) { return '@A' . base64_encode($r[0]);},$userWxInfo['nickname'])
						],['account'=>$wechat_id]);
					}
				}
				else
				{
					file_put_contents('kong.txt','是不是');
					$model = new User();
					$model['avatar'] = $userWxInfo['headimgurl'];
					$model['account'] = $userWxInfo['openid'];
					$model['name'] = preg_replace_callback('/[\xf0-\xf7].{3}/', function($r) { return '@A' . base64_encode($r[0]);},$userWxInfo['nickname']);
					if(!empty($mid)){
						$model['source'] = $mid;
					}
					if($model->save()){
						header("location:http://merchant.lanqiulm.com/custom/business/businessDetailList.html?mid=$mid");
						return ;
					}
				}
				header("location:http://merchant.lanqiulm.com/custom/business/businessDetailList.html?mid=$mid");
				return ;
			}
		}
		else
		{
			Yii::warning($res);
		}
	}
	catch (\Exception $e)
	{
		Yii::warning($e->getMessage());
	}
	Yii::warning('登录失败');
	exit;
       


	
?>
