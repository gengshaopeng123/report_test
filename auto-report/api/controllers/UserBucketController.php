<?php

namespace api\controllers;
header('Access-Control-Allow-Origin:*');
header("Content-type:text/html;charset=utf-8");

use common\models\Auth;
use common\models\AuthRole;
use common\models\User;
use common\models\UserBucket;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

class UserbucketController extends BaseController
{
    public $enableCsrfValidation = false;
    public $PostData;
    public $Redis;

    /**
     * @method   初始化方法
     * @author : gsp
     * @Date   : 2018-04-18
     */
    public function init()
    {
        //验证session
        //$this->isLogin();
        //验证请求
        // $this->respond();
        $this->PostData = Yii::$app->request->post();
//        $this->Redis = Yii::$app->redis;
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
                    'delete' => ['POST', 'GET'],
                ],
            ],
        ];
    }

    /**
     * @method   获取用户与桶名
     * @author : gengshaopeng
     * @Date   : 2019-09-18
     */
    public function actionBucketname()
    {
        $getData = Yii::$app->request->get();
        $user = isset($getData['username'])?$getData['username'] : '';

        $userBucket = UserBucket::find()->where(['user_name'=>$user])->asArray()->one();
        if($userBucket){
            return $this->api_result(200,'成功',$userBucket);
        }else{
            return $this->api_result(201,'没有此用户数据');
        }
    }


    /**
     * @method   获取用户所对应的测序流程
     * @author : gengshaopeng
     * @Date   : 2019-09-18
     */
    public function actionWorkflow(){
        $uid = Yii::$app->request->get('username'); //用户id
//        $uid = 'demo1'; //用户id
        if($uid){
            $user = User::find()->where(['id'=>$uid])->asArray()->one();
            if($user){
                $authRole = AuthRole::find()->select('auth_id')->where(['role_id'=>$user['role_id']])->asArray()->All();
                $authRoles = array_column($authRole,'auth_id'); //测序流程的数组
                $auth = Auth::find()->select('name')->where(['in','id',$authRoles])->asArray()->all();
                $auths = array_column($auth,'name'); //登录用户对应的测序流程数据
                return $this->api_result(200,'success',$auths);
            }else{
                return $this->api_result(202,'用户不存在');
            }
        }else{
            return $this->api_result(201,'缺少参数');
        }
    }
}
