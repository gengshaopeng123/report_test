<?php

namespace api\controllers;
header('Access-Control-Allow-Origin:*');
header("Content-type:text/html;charset=utf-8");

use common\models\Admin;
use common\models\ExpendDetail;
use common\models\Report;
use common\models\ReportClassify;
use common\models\User;
use Yii;
use yii\db\Exception;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

class ReportController extends BaseController
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
     * @method   报告列表接口
     * @author : gengshaopeng
     * @Date   : 2019-11-18
     */
    public function actionReportlist(){
        $data = $this->Checklogin();
        if($data == 201 || $data == 202){
            return $this->api_result(205,'登录信息失效');
        }
        $page = Yii::$app->request->get('page',1);
        $status = Yii::$app->request->get('status',0);
        $uname = Yii::$app->request->get('username');
        $limit = 20;
        $start = ($page-1)*$limit;
        $arr = array();
        if($status == 3){
            $where = [
                'or',
                'status=3',
                'status=4'
            ];
        }else{
            $where = ['status'=>$status];
        }
        $report = Report::find()->alias('r')->select('r.*,po.name')->joinWith('reportClassify as po')->where($where);
        if($uname){
            $admin = Admin::find()->select('id,username')->where(['username'=>$uname])->asArray()->one();
            $uid = isset($admin['id']) ? $admin['id'] :'';
            $report = $report->andWhere(['uid'=>$uid]);
        }
        $count = $report->count();
        $reportList = $report->offset($start)->limit($limit)->asArray()->orderBy('id desc')->all();
        foreach ($reportList as $k=>$v){
            $reportClassify  = ReportClassify::find()->select('name')->where(['id'=>$v['type_id']])->asArray()->one();
            $reportList[$k]['type_name'] = $reportClassify['name'];             //类型名称
        }
        $arr['count'] = intval($count);
        $arr['reportList'] = $reportList;
        foreach ($arr['reportList'] as $k=>&$v){
            $arr['reportList'][$k]['xuhao'] = $k+1;
            //if($v['sex'] == 0){
            //    $arr['reportList'][$k]['sex']='男';
            //}else{
            //    $arr['reportList'][$k]['sex']='女';
            //}
            if($v['status'] == 0){
                $arr['reportList'][$k]['status'] = '待审核';
            }else if($v['status'] == 1){
                $arr['reportList'][$k]['status'] = '审核通过';
            }else if($v['status'] == 2){
                $arr['reportList'][$k]['status'] = '审核未通过';
            }else if($v['status'] == 3){
                $arr['reportList'][$k]['status'] = '正在生成中';
            }else{
                $arr['reportList'][$k]['status'] = '生成失败';
            }
        }
        if($reportList){
            return $this->api_result(200,'success',$arr);
        }else{
            return $this->api_result(201,'无数据');
        }
    }

    /**
     * @method   报告doc地址
     * @author : gengshaopeng
     * @Date   : 2019-12-20
     */
    public function actionReportpath(){
        $data = $this->Checklogin();
        if($data == 201 || $data == 202){
            return $this->api_result(205,'登录信息失效');
        }
        $id = Yii::$app->request->get('id');//报告id
        if($id){
            $reportData = Report::find()->select('download_path,preview_path')->where(['id'=>$id])->asArray()->one();
            if($reportData){
                $docData = $reportData['download_path'];
                return $this->api_result(200,'success',$docData);
            }
        }else{
            return $this->api_result(201,'缺少参数');
        }
    }

    /**
     * @method   报告pdf地址
     * @author : gengshaopeng
     * @Date   : 2019-12-23
     */
    public function actionReportpdfpath(){
        $data = $this->Checklogin();
        if($data == 201 || $data == 202){
            return $this->api_result(205,'登录信息失效');
        }
        $id = Yii::$app->request->get('id');//报告id
        if($id){
            $reportData = Report::find()->select('download_path,preview_path')->where(['id'=>$id])->asArray()->one();
            if($reportData){
                $pdfData = $reportData['preview_path'];
                $pdf_data = file_get_contents($pdfData);
                var_dump($pdf_data);
                return $this->api_result(200,'success',$pdf_data);
            }
        }else{
            return $this->api_result(201,'缺少参数');
        }
    }



    /**
     * @method   报告审核接口
     * @author : gengshaopeng
     * @Date   : 2019-11-28
*/
    public function actionExamine(){
        $data = $this->Checklogin();
        if($data == 201 || $data == 202){
            return $this->api_result(205,'登录信息失效');
        }
        $id = Yii::$app->request->post('id');
        $status = Yii::$app->request->post('status');
        $result = Report::updateAll(['status'=>$status],['id'=>$id]);
        if($result){
            return $this->api_result(200,'success');
        }else{
            return $this->api_result(201,'failed');
        }
    }


    /**
     * @method   报告首页统计接口
     * @author : gengshaopeng
     * @Date   : 2019-12-2
     */
    public function actionNum(){
        $data = $this->Checklogin();
        if($data == 201 || $data == 202){
            return $this->api_result(205,'登录信息失效');
        }
        $countArr = array();
        $username = Yii::$app->request->get('username');
        if($username){
            $admin = Admin::find()->select('id,username')->where(['username'=>$username])->asArray()->one();
            $uid = isset($admin['id']) ? $admin['id'] :'';
            $report = Report::find()->where(['uid'=>$uid])->asArray()->count();
            $countArr['report_count'] = intval($report);
            $countArr['template_count'] = 66;
            $countArr['module_count'] = 58;
            return $this->api_result(200,'success',$countArr);
        }else{
            return $this->api_result(201,'缺少参数');
        }
    }
}
