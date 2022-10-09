<?php

namespace api\controllers;
header('Access-Control-Allow-Origin:*');
use Yii;
use yii\web\Controller;

/**
 * ErrorController implements the CRUD actions for User model.
 */
class ErrorController extends BaseController
{
	public $enableCsrfValidation = false;
    /**
     * @method   api验证失败
     * @author : kongerlong
     * @Date   : 2017-12-14
     *
     */
    public function actionError($id){
		if($id == 1){
			return  $this->api_result(101,'签名缺少参数');
		}else if($id == 2){
			return  $this->api_result(102,'签名验证失败');
		}else{
			return  $this->api_result(220,'未登录');
		}
	}

	public function actionLogin(){
		return  $this->api_result(103,'session失效');
	}
}
