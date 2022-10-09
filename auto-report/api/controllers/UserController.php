<?php
namespace api\controllers;
header('Access-Control-Allow-Origin:*');

use Yii;
use common\models\User;
use common\models\UserSearch;
use common\models\ZyTrade;
use common\models\ShTrade;
use common\models\ZyProduct;
use common\models\ShProduct;
use common\models\ZyImg;
use common\models\ShImg;
use common\models\GoodsCollection;
use common\models\Share;
use common\models\Coupon;
use common\models\YyTrade;
use common\models\YyProduct;
use common\models\ZyFollow;
use common\models\YyFollow;
use common\models\ShFollow;
use common\models\YyImg;
use common\models\Address;
use common\models\CouponDetail;
use common\models\ZyDetails;
use common\models\ZyTradeSearch;
use common\models\CouponSend;
use common\components\jssdk\jssdk;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * 用户端(移动)
 */
class UserController extends BaseController
{

	public $enableCsrfValidation = false;
    public $PostData;
    public $userId;
    /**
     * @method   初始化方法 
     * @author : kongerlong
     * @Date   : 2018-04-18
     */
    public function init()
    {
		//验证请求
        $this->respond();
        $this->PostData = Yii::$app->request->post();
        // $userId = Yii::$app->session['user_id'];
	}
    
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST','GET'],
                ],
            ],
        ];
    }


	/**
     * @method   我的会员中心接口
     * @author : gsp
     * @Date   : 2018-05-03
     */
    public function actionSelectuser()
    {
        $PostData = $this->PostData;
        $uid = isset($PostData['uid'])?$PostData['uid']:'';
        $fid = isset($PostData['fid'])?$PostData['fid']:'';
       	if(!empty($uid)){
            $user = User::find()->where(['id'=>$uid,'status'=>1])->asArray()->one();
            $user['name'] = preg_replace_callback('/@A(.{6}==)/', function($r) {return base64_decode($r[1]);},$user['name']);
			if(empty($user['source'])){
				//不属扫描于商户二维码进来的才建立分享关系
				if(!empty($fid) && $uid != $fid){
					$Share = Share::find()->where(['uid'=>$uid])->asArray()->one();
					if(!empty($Share)){
						if($Share['status'] == 1){
							//未获得优惠券去更新fid
							if($Share['fid'] != $fid){
								$share_id = $Share['id'];
								$res = Share::updateAll(['fid'=>$fid,'utime'=>time()],"id=$share_id");
							}else{
								return $this->api_result('200','成功返回',$user);
							}
						}else{
							return $this->api_result('200','成功返回',$user);
						}
					}else{
						$coupon = Coupon::findOne(1);
						$model = new Share();
						$model->uid = $uid;
						$model->fid = $fid;
						if(!empty($coupon)){
							$model->m_rule = $coupon->m_rule;
							$model->j_rule = $coupon->j_rule;
						}
						if($model->save()){
							return $this->api_result('200','成功返回',$user);
						}else{
							return $this->api_result('201','返回失败','');
						}
					}
				}
			}	
			return $this->api_result('200','成功返回',$user);
		}else{
            return $this->api_result('204','缺少参数','');
        }   
    }

	/**
     * @method   我的会员中心-产品订单详情
     * @author : kongerlong
     * @Date   : 2018-05-31
     */
    public function actionSelectorderdetails()
    {
        $PostData = $this->PostData;
        $uid = isset($PostData['uid'])?$PostData['uid']:'';
        $oid = isset($PostData['oid'])?$PostData['oid']:'';
        if(!empty($uid) && !empty($oid)){
            $orders = ZyTrade::find()->alias('zyd')
                ->joinWith(['zyDetails as zyt'])
                ->where(['zyd.uid'=>$uid,'zyd.id'=>$oid])
                ->asArray()
                ->one();
            if(!empty($orders)){
				if(!empty($orders['utime'])){
					$orders['utime'] = date('Y-m-d H:i:s',$orders['utime']);
				}
				if(!empty($orders['ftime'])){
					$orders['ftime'] = date('Y-m-d H:i:s',$orders['ftime']);
				}
				if(!empty($orders['htime'])){
					$orders['htime'] = date('Y-m-d H:i:s',$orders['htime']);
				}
				foreach($orders['zyDetails'] as $y=>$v){
					$Zypro = ZyProduct::find()->select('id,product')->where(['id'=>$v['goods_id']])->asArray()->one();
					$ZyImg = ZyImg::find()->select('url')->where(['mid'=>$Zypro['id']])->asArray()->one();
					$orders['zyDetails'][$y]['product'] = $Zypro['product'];
					$orders['zyDetails'][$y]['url'] = 'http://admin.lanqiulm.com'.$ZyImg['url'];
				}
				return $this->api_result('200','成功返回',$orders);
			}else{
				return $this->api_result('255','数据错误','');
			}
    	}else{
            return $this->api_result('204','缺少参数','');
        }   
    }

	/**
     * @method   我的会员中心-商家服务订单详情
     * @author : kongerlong
     * @Date   : 2018-06-05
     */
    public function actionSelectshdetails()
    {
        $PostData = $this->PostData;
        $uid = isset($PostData['uid'])?$PostData['uid']:'';
        $oid = isset($PostData['oid'])?$PostData['oid']:'';
        if(!empty($uid) && !empty($oid)){
            $orders = ShTrade::find()->alias('zyd')
                ->joinWith(['shDetails as zyt'])
                ->joinWith(['shOrders'])
                ->where(['zyd.uid'=>$uid,'zyd.id'=>$oid])
                ->asArray()
                ->one();
            if(!empty($orders)){
				if(!empty($orders['utime'])){
					$orders['utime'] = date('Y-m-d H:i:s',$orders['utime']);
				}
				if(!empty($orders['ttime'])){
					$orders['ttime'] = date('Y-m-d H:i:s',$orders['ttime']);
				}
				if(!empty($orders['ztime'])){
					$orders['ztime'] = date('Y-m-d H:i:s',$orders['ztime']);
				}
				foreach($orders['shDetails'] as $y=>$v){
					$Shpro = ShProduct::find()->select('id,product')->where(['id'=>$v['goods_id']])->asArray()->one();
					$ShImg = ShImg::find()->select('url')->where(['mid'=>$Shpro['id']])->asArray()->one();
					$orders['shDetails'][$y]['product'] = $Shpro['product'];
					$orders['shDetails'][$y]['url'] = 'http://feather.lanqiulm.com'.$ShImg['url'];
				}
				return $this->api_result('200','成功返回',$orders);
			}else{
				return $this->api_result('255','数据错误','');
			}
    	}else{
            return $this->api_result('204','缺少参数','');
        }   
    }
	
	 /**
     * @method   我的会员中心-产品订单退货列表接口
     * @author : kongerlong
     * @Date   : 2018-05-31
     */
    public function actionSelectordertui()
    {
        $PostData = $this->PostData;
        $uid = isset($PostData['uid'])?$PostData['uid']:'';
        if(!empty($uid)){
            $orders = ZyTrade::find()->alias('zyd')
                ->joinWith(['zyDetails as zyt'])
                ->where(['zyd.uid'=>$uid])
				->andWhere(['in','zyd.status',[5,6,7]])    
                ->orderBy('ctime desc')
				->asArray()
                ->all();
            if(!empty($orders)){
				foreach($orders as $k=>$pro){
					foreach($pro['zyDetails'] as $y=>$v){
						if($v['t_status'] != 0){
							$Zypro = ZyProduct::find()->select('id,product')->where(['id'=>$v['goods_id']])->asArray()->one();
							$ZyImg = ZyImg::find()->select('url')->where(['mid'=>$Zypro['id']])->asArray()->one();
							$orders[$k]['zyDetails'][$y]['product'] = $Zypro['product'];
							$orders[$k]['zyDetails'][$y]['url'] = 'http://admin.lanqiulm.com'.$ZyImg['url'];
						}else{
							unset($orders[$k]['zyDetails'][$y]);
						}
					}
				}
			}
			return $this->api_result('200','成功返回',$orders);
    	}else{
            return $this->api_result('204','缺少参数','');
        }   
    }
	
    /**
     * @method   我的会员中心-产品订单接口
     * @author : gsp
     * @Date   : 2018-05-03
     */
    public function actionSelectorder()
    {
        $PostData = $this->PostData;
        $uid = isset($PostData['uid'])?$PostData['uid']:'';
        $status = isset($PostData['status'])?$PostData['status']:'';
        if(!empty($uid) && !empty($status)){
            $orders = ZyTrade::find()->alias('zyd')
                ->joinWith(['zyDetails as zyt'])
                ->where(['zyd.uid'=>$uid,'zyd.status'=>$status])    
                ->orderBy('zyd.id desc')
				->asArray()
                ->all();
            if(!empty($orders)){
				foreach($orders as $k=>$pro){
					foreach($pro['zyDetails'] as $y=>$v){
						$Zypro = ZyProduct::find()->select('id,product')->where(['id'=>$v['goods_id']])->asArray()->one();
						$ZyImg = ZyImg::find()->select('url')->where(['mid'=>$Zypro['id']])->asArray()->one();
						$orders[$k]['zyDetails'][$y]['product'] = $Zypro['product'];
						$orders[$k]['zyDetails'][$y]['url'] = 'http://admin.lanqiulm.com'.$ZyImg['url'];
					}
				}
			}
			return $this->api_result('200','成功返回',$orders);
        }elseif(!empty($uid) && empty($status)){
        	$orders = ZyTrade::find()->alias('zyd')
                ->joinWith(['zyDetails as zyt'])
                ->where(['zyd.uid'=>$uid])
				->orderBy('id desc') 
                ->asArray()
                ->all();
            if(!empty($orders)){
				foreach($orders as $k=>$pro){
					foreach($pro['zyDetails'] as $y=>$v){
						$Zypro = ZyProduct::find()->select('id,product')->where(['id'=>$v['goods_id']])->asArray()->one();
						$ZyImg = ZyImg::find()->select('url')->where(['mid'=>$Zypro['id']])->asArray()->one();
						$orders[$k]['zyDetails'][$y]['product'] = $Zypro['product'];
						$orders[$k]['zyDetails'][$y]['url'] = 'http://admin.lanqiulm.com'.$ZyImg['url'];
					}
				}
			}
			return $this->api_result('200','成功返回',$orders);
        }else{
            return $this->api_result('204','缺少参数','');
        }   
    }

	/**
     * @method   我的会员中心-商家服务订单接口
     * @author : kongerlong
     * @Date   : 2018-06-05
     */
    public function actionSelectshtrade()
    {
        $PostData = $this->PostData;
        $uid = isset($PostData['uid'])?$PostData['uid']:'';
        $status = isset($PostData['status'])?$PostData['status']:'';
        $type = isset($PostData['type'])?$PostData['type']:'';
        if(!empty($uid) && !empty($status)){
            if(empty($type)){
				$orders = ShTrade::find()->alias('zyd')->joinWith(['shDetails as zyt'])->where(['zyd.uid'=>$uid,'zyd.status'=>$status])->asArray()->orderBy('id desc')->all();
			}else{
				$orders = ShTrade::find()->alias('zyd')->joinWith(['shDetails as zyt'])->where(['zyd.uid'=>$uid])->asArray()->orderBy('id desc')->all();
			}
            if(!empty($orders)){
				foreach($orders as $k=>$pro){
					if(!empty($type)){
						$count =count($pro['shDetails']);
						$i = 0;
						foreach($pro['shDetails'] as $y=>$v){
							if($v['status'] != 1){
								$Shpro = ShProduct::find()->select('id,product')->where(['id'=>$v['goods_id']])->asArray()->one();
								$ShImg = ShImg::find()->select('url')->where(['mid'=>$Shpro['id']])->asArray()->one();
								$orders[$k]['shDetails'][$y]['product'] = $Shpro['product'];
								$orders[$k]['shDetails'][$y]['url'] = 'http://feather.lanqiulm.com'.$ShImg['url'];
							}else{
								$i++;
							}
						}
						if($i == $count){
							unset($orders[$k]);
						}
					}else{
						foreach($pro['shDetails'] as $y=>$v){
							$Shpro = ShProduct::find()->select('id,product')->where(['id'=>$v['goods_id']])->asArray()->one();
							$ShImg = ShImg::find()->select('url')->where(['mid'=>$Shpro['id']])->asArray()->one();
							$orders[$k]['shDetails'][$y]['product'] = $Shpro['product'];
							$orders[$k]['shDetails'][$y]['url'] = 'http://feather.lanqiulm.com'.$ShImg['url'];
						}
					}
				}
			}
			return $this->api_result('200','成功返回',$orders);
        }elseif(!empty($uid) && empty($status)){
        	$orders = ShTrade::find()->alias('zyd')
                ->joinWith(['shDetails as zyt'])
                ->where(['zyd.uid'=>$uid])    
                ->asArray()
				->orderBy('id desc')
                ->all();
            if(!empty($orders)){
				foreach($orders as $k=>$pro){
					foreach($pro['shDetails'] as $y=>$v){
						$Shpro = ShProduct::find()->select('id,product')->where(['id'=>$v['goods_id']])->asArray()->one();
						$ShImg = ShImg::find()->select('url')->where(['mid'=>$Shpro['id']])->asArray()->one();
						$orders[$k]['shDetails'][$y]['product'] = $Shpro['product'];
						$orders[$k]['shDetails'][$y]['url'] = 'http://feather.lanqiulm.com'.$ShImg['url'];
					}
				}
			}
			return $this->api_result('200','成功返回',$orders);
        }else{
            return $this->api_result('204','缺少参数','');
        }   
    }



    /**
     * @method   我的会员中心-预约服务订单接口
     * @author : gsp
     * @Date   : 2018-05-07
     */
    public function actionSelectyytrade()
    {
        $PostData = $this->PostData;
        $uid = isset($PostData['uid'])?$PostData['uid']:'';
        $status = isset($PostData['status'])?$PostData['status']:'';
        if(!empty($uid) && !empty($status)){
            if($status == 4){
				$wh = array(4,5,6);
				$yytrade = YyTrade::find()->alias('yyt')
                    ->joinWith(['yyProduct as yyp'])
                    ->joinWith(['yyImg as yyi'])
                    ->select('yyt.*,yyp.product,yyi.url')
                    ->where(['yyt.uid'=>$uid])
                    ->andWhere(['in','yyt.status',$wh])
					->orderBy('yyt.ctime desc')
                    ->asArray() 
                    ->all();
			}else if($status == 3){
				$wh = array(3,8);
				$yytrade = YyTrade::find()->alias('yyt')
                    ->joinWith(['yyProduct as yyp'])
                    ->joinWith(['yyImg as yyi'])
                    ->select('yyt.*,yyp.product,yyi.url')
                    ->where(['yyt.uid'=>$uid])
                    ->andWhere(['in','yyt.status',$wh])
					->orderBy('yyt.ctime desc')
                    ->asArray() 
                    ->all();
			}else{
				$yytrade = YyTrade::find()->alias('yyt')
                	->joinWith(['yyProduct as yyp'])
                	->joinWith(['yyImg as yyi'])
                	->select('yyt.*,yyp.product,yyi.url')
                	->where(['yyt.uid'=>$uid,'yyt.status'=>$status])    
                	->orderBy('yyt.ctime desc')
					->asArray()
                	->all();
			}
            return $this->api_result('200','成功返回',$yytrade);
        }else if(!empty($uid) && empty($status)){
			$yytrade = YyTrade::find()->alias('yyt')
                ->joinWith(['yyProduct as yyp'])
                ->joinWith(['yyImg as yyi'])
                ->select('yyt.*,yyp.product,yyi.url')
                ->where(['yyt.uid'=>$uid])    
                ->orderBy('yyt.ctime desc')
                ->asArray()
                ->all();
            return $this->api_result('200','成功返回',$yytrade);
		}else{
            return $this->api_result('204','缺少参数','');
        }   
    }

    /**
     * @method   我的会员中心-预约服务订单详情接口
     * @author : gsp
     * @Date   : 2018-05-07
     */
    public function actionSelectyytradedetail()
    {
        $PostData = $this->PostData;
        $uid = isset($PostData['uid'])?$PostData['uid']:'';
        $oid = isset($PostData['oid'])?$PostData['oid']:'';
        if(!empty($uid) && !empty($oid)){
            $yytradedetail = YyTrade::find()->alias('yyt')
                ->joinWith(['yyProduct as yyp'])
                ->joinWith(['yyImg as yyi'])
                ->joinWith(['ygOrders'])
                // ->select('zyd.*,shop_zy_details.*,shop_zy_product.*')
                ->where(['yyt.uid'=>$uid,'yyt.id'=>$oid])    
                ->asArray()
                ->one();
			if(empty($yytradedetail)){
           		return $this->api_result('255','数据错误','');
			}else{
				$yytradedetail['yyImg'][0]['url'] = 'http://admin.lanqiulm.com'.$yytradedetail['yyImg'][0]['url'];
				if($yytradedetail['utime']){
					$yytradedetail['utime'] = date('Y-m-d H:i:s',$yytradedetail['utime']);
				}
				if($yytradedetail['ztime']){
					$yytradedetail['ztime'] = date('Y-m-d H:i:s',$yytradedetail['ztime']);
				}
				return $this->api_result('200','成功返回',$yytradedetail);
        	}
		}else{
            return $this->api_result('204','缺少参数','');
        }   
    }


    /**
     * @method   我的会员中心-我的优惠券接口
     * @author : gsp
     * @Date   : 2018-05-07
     */
    public function actionSelectcouponsend()
    {
        $PostData = $this->PostData;
        $uid = isset($PostData['uid'])?$PostData['uid']:'';
        $status = isset($PostData['status'])?$PostData['status']:'';
        $page = isset($PostData['page'])?$PostData['page']:'1';
        if(!empty($uid) && !empty($status) && !empty($page)){
            $start = ($page-1)*5;
            $coupons = CouponDetail::find()
				->where(['uid'=>$uid,'status'=>$status])
                ->offset($start)->limit(5)    
                ->asArray() 
                ->all();
				foreach($coupons as $key=>$value){
					if($value['type'] == 1){
						$CouponSend = CouponSend::find()->where(['id'=>$value['coupon_id']])->asArray()->one();
					}else{
						$CouponSend = Coupon::find()->where(['id'=>$value['coupon_id']])->asArray()->one();
					}
					$coupons[$key]['m_rule'] = $CouponSend['m_rule'];
					$coupons[$key]['j_rule'] = $CouponSend['j_rule'];
					$coupons[$key]['validity'] = $CouponSend['validity'];
				}
            return $this->api_result('200','成功返回',$coupons);
        }else{
            return $this->api_result('204','缺少参数','');
        }   
    }

    /**
     * @method   我的会员中心-我的优惠券统计个数接口
     * @author : gsp
     * @Date   : 2018-05-07
     */
    public function actionSelectcouponsendcount()
    {
        $PostData = $this->PostData;
        $uid = isset($PostData['uid'])?$PostData['uid']:'';
        $status = isset($PostData['status'])?$PostData['status']:'';
        if(!empty($uid) && !empty($status)){
            $coupons = CouponDetail::find()
                ->where(['uid'=>$uid,'status'=>$status])   
                ->count();
            return $this->api_result('200','成功返回',$coupons);
        }else{
            return $this->api_result('204','缺少参数','');
        }   
    }

    /**
     * @method   我的会员中心-我的分享接口
     * @author : gsp
     * @Date   : 2018-05-04
     */
    public function actionSelectcoupon()
    {
        $PostData = $this->PostData;
        $uid = isset($PostData['uid'])?$PostData['uid']:'';
        if(!empty($uid)){
            $shareData = Share::find()->where(['fid'=>$uid,'status'=>2])->asArray()->all();
			return $this->api_result('200','成功返回',$shareData);
        }else{
            return $this->api_result('204','缺少参数','');
        }   
    }

    /**
     * @method   我的会员中心-我的关注接口
     * @author : gsp
     * @Date   : 2018-05-06
     */
    public function actionSelectcollection()
    {
        $PostData = $this->PostData;
        $uid = isset($PostData['uid'])?$PostData['uid']:'';
        $page = isset($PostData['page'])?$PostData['page']:'';
        $type = isset($PostData['type'])?$PostData['type']:'';
        $start = ($page-1)*5;
        if(!empty($uid) && !empty($page) && !empty($type)){
            if($type==1){            //美容产品
                $goodscollection = ZyFollow::find()->alias('goodsco')   
                    ->joinWith('zyProduct')
                    ->joinWith('zyImg')
                    // ->select('goodsco.*')
                    ->where(['goodsco.uid'=>$uid,'goodsco.status'=>1])
                    ->offset($start)
                    ->limit(5)    
                    ->asArray()
                    ->all();
            }elseif($type==2){               //美容服务
                $goodscollection = ShFollow::find()->alias('goodsco')  
                    ->joinWith('shProduct')
                    ->joinWith('shImg')
                    // ->select('coupd.ctime,coup.j_rule')
                    ->where(['goodsco.uid'=>$uid,'goodsco.status'=>1])
                    ->offset($start)
                    ->limit(5)    
                    ->asArray()
                    ->all();
            }else{                   //预约服务
                $goodscollection = YyFollow::find()->alias('goodsco')  
                    ->joinWith('yyProduct')
                    ->joinWith('yyImg')
                    // ->select('coupd.ctime,coup.j_rule')
                    ->where(['goodsco.uid'=>$uid,'goodsco.status'=>1])
                    ->offset($start)
                    ->limit(5)    
                    ->asArray()
                    ->all();
            }
            return $this->api_result('200','成功返回',$goodscollection);
        }else{
            return $this->api_result('204','缺少参数','');
        }   
    }

     /**
     * @method   我的会员中心-我的收货地址接口
     * @author : gsp
     * @Date   : 2018-05-06
     */
    public function actionSelectaddress()
    {
        $PostData = $this->PostData;
        $uid = isset($PostData['uid'])?$PostData['uid']:'';
        $page = isset($PostData['page'])?$PostData['page']:'1';
        if(!empty($uid) && !empty($page)){
            $start = ($page-1)*5;
            $address = Address::find()->where(['user_id'=>$uid])->offset($start)->limit(5)->asArray()->all();   //模型未生成、关联
               // print_r($address);exit;
            return $this->api_result('200','成功返回',$address);
        }else{
            return $this->api_result('204','缺少参数','');
        }   
    }

	/**
     * @method   用户登录验证
     * @author : kongerlong
     * @Date   : 2018-07-05
     */
    public function actionYzuser()
    {
        $PostData = $this->PostData;
        $uid = isset($PostData['uid'])?$PostData['uid']:'';
        $account = isset($PostData['account'])?$PostData['account']:'';
        if(!empty($uid) && !empty($account)){
            $UserArr = User::find()->where(['id'=>$uid,'account'=>$account])->asArray()->one();   //模型未生成、关联
            if($UserArr['status'] == 1){
				return $this->api_result('200','用户正常','');
			}else{
				return $this->api_result('201','用户冻结','');
        	}
		}else{
            return $this->api_result('204','缺少参数','');
        }   
    }
	
	/**
     * @method   分享
     * @author : kongerlong
     * @Date   : 2018-07-05
     */
	public function actionShare()
	{
		$PostData = $this->PostData;
        $url = isset($PostData['url'])?$PostData['url']:'';
		$jssdk = new JSSDK("wx6f58ce17ef7a4e66","7277c6becc32b916e9b2456fe398864b");//填写开发者中心你的开发者ID
		$signPackage = $jssdk->getSignPackage($url);
		if(!empty($signPackage)){
			return $this->api_result('200','返回成功',$signPackage);
		}else{
			return $this->api_result('201','数据错误','');
		}
	}	
}
