<?php

namespace api\controllers;
header('Access-Control-Allow-Origin:*');
header("Content-type:text/html;charset=utf-8");

use common\models\ExpendDetail;
use common\models\ReportClassify;
use common\models\User;
use Yii;
use yii\db\Exception;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

class ReportclassifyController extends BaseController
{
    public $enableCsrfValidation = false;
    public $PostData;
    public $Redis;

    /**
     * @method   初始化方法
     * @author : gsp
     * @Date   : 2019-11-18
     */
    public function init()
    {
        $this->PostData = Yii::$app->request->post();
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
     * @method   分类接口
     * @author : gengshaopeng
     * @Date   : 2019-11-18
     */
    public function actionAllclass(){
//        $data = $this->Checklogin();
//        if($data == 201 || $data == 202){
//            return $this->api_result(205,'登录信息失效');
//        }
        $data = ReportClassify::find()->where(['pid'=>0])->asArray()->all();
        if($data){
            $ar = array();
            foreach ($data as $k=>&$v){
                $secondClass = ReportClassify::find()->where(['pid'=>$v['id']])->asArray()->all();
                $data[$k]['second'] = $secondClass;
            }
            return $this->api_result(200,'success',$data);
        }else{
            return $this->api_result(201,'failed');
        }
    }

    /**
     * @method   二级分类接口
     * @author : gengshaopeng
     * @Date   : 2019-11-18
     */
    public function actionSecondclass(){
       $data = $this->Checklogin();
       if($data == 201 || $data == 202){
           return $this->api_result(205,'登录信息失效');
       }
        $pid = Yii::$app->request->get('pid',1);
        $data = ReportClassify::find()->where(['pid'=>$pid])->asArray()->all();
        if($data){
            $class = array_column($data,'name');
            if($class){
                return $this->api_result(200,'success',$class);
            }
        }else{
            return $this->api_result(201,'无二级分类');
        }
    }

    /**
     * @method   杨男肠道分类接口
     * @author : gengshaopeng
     * @Date   : 2020-06-16
     */
    public function actionChangallclass(){
       $data = $this->Checklogin();
       if($data == 201 || $data == 202){
           return $this->api_result(205,'登录信息失效');
       }
        $data = ReportClassify::find()->where(['pid'=>0])->andWhere(['id'=>10014])->asArray()->all();
        if($data){
            $ar = array();
            foreach ($data as $k=>&$v){
                $secondClass = ReportClassify::find()->where(['pid'=>$v['id']])->asArray()->all();
                $data[$k]['second'] = $secondClass;
            }
            return $this->api_result(200,'success',$data);
        }else{
            return $this->api_result(201,'failed');
        }
    }

}
