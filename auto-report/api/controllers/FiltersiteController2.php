<?php

namespace api\controllers;
header('Access-Control-Allow-Origin:*');
header("Content-type:text/html;charset=utf-8");

use common\models\ExpendDetail;
use common\models\Report;
use common\models\ReportClassify;
use common\models\ReportCreate;
use common\models\ReportFilter;
use common\models\ReportParameters;
use common\models\User;
use Yii;
use yii\db\Exception;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

class FiltersiteController extends BaseController
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
     * @method   过滤位点接口
     * @author : gengshaopeng
     * @Date   : 2019-12-04
     */
    public function actionSite(){
        $postdata =  Yii::$app->request->post();                                            //数据
        $uname = isset($postdata['username']) ? $postdata['username'] :'';
        $unique = isset($postdata['unique']) ? $postdata['unique'] :'';                     //确定是这一批数据 的唯一表示
        $type = isset($postdata['type']) ? $postdata['type'] :'';                           //文件类型区分 1:样本信息 2：注释结果 3:一代图片
        $relation = isset($postdata['qsgx']) ? $postdata['qsgx'] :'';                       //亲属关系
        $direction = isset($postdata['yzfx']) ? $postdata['yzfx'] :'';                      //验证方向
        $site = isset($postdata['wd']) ? $postdata['wd'] :'';                               //位点
        $relationNote = isset($postdata['gxbz']) ? $postdata['gxbz'] :'';                   //关系备注
        $company_name = isset($postdata['com_name']) ? $postdata['com_name'] :'';           //公司名称
        $header_footer = isset($postdata['ymyj']) ? $postdata['ymyj'] :'';                  //页眉页脚
        $report_type_id = isset($postdata['report_type']) ? $postdata['report_type'] :'';    //报告类型id
        $report_template_id = isset($postdata['report_template']) ? $postdata['report_template'] :'';   //模板文件id

        if(isset($postdata['type']) && !empty($postdata['type'])){
            //有type参数的为文件上传  上传完后入库（report_create）
            if (!empty($_FILES)) {
                $file_name = $_FILES["file"]["name"];
                // 限制文件大小
                $file_size = $_FILES["file"]["size"];
                // 限制2M大小
                if ($file_size > 1024 * 1024 * 50) {
                    echo '文件大小超过限制';
                    exit;
                }
                // 限制文件上传类型
                $file_type = $_FILES["file"]["type"];
                $file_type_arr = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
                if (!in_array($file_type, $file_type_arr)) {
                    echo '上传文件类型错误';
                    exit;
                }

                // 文件上传到服务器临时文件夹之后的文件名
                $tem_name = $_FILES['file']['tmp_name'];

                // 取得文件后缀名
                $arrc = count(explode('.', $_FILES['file']['name']));
                $file_fix = explode('.', $_FILES['file']['name'])[$arrc-1] ? explode('.', $_FILES['file']['name'])[$arrc-1] : 'png';
//                $file_fix = explode('.', $_FILES['file']['name'])[1] ? explode('.', $_FILES['file']['name'])[1] : 'png';

                // 文件重命名，这里自动生成一个不重复的名字，方便使用
                $name = md5(uniqid(md5(microtime(true)), true)) . '.' . $file_fix;

                // 要存放文件的目录定义，这里按日期分开存储
                $file_dir = dirname(__FILE__) . '/upload/' . date('Ymd') . '/';
                $file_dir = str_replace('\\','/',$file_dir);
                // 检测要存放文件的目录是否存在，不存在则创建
                if (!is_dir($file_dir)) {
                    mkdir($file_dir, 0755, true);
                }

                // 移动文件到指定目录下
                @ move_uploaded_file($tem_name, $file_dir . $name);
                echo '上传成功';
                //入库表
                $reportModel = new ReportFilter();
                $reportModel->upload_url = $file_dir . $name;
                $reportModel->unique = $unique;
                if($reportModel->save()){
                    echo 'success';
                }else{
                    var_dump($reportModel->getErrors());
                }
                exit;
            } else {
                echo '文件上传失败';
                exit;
            }
        }else{
            //最后请求标识($report_type_id $report_template_id) 整合这些数据
            if($report_type_id && $report_template_id){
                $json = self::requestPy($unique,$report_template_id,$uname);
                $datat = json_decode($json,true);
                $json = str_replace("\\/", "/", $json);
                $json = str_replace("\\", "/", $json);
                $json = str_replace("\"", "\\\"", $json);
                var_dump($json);
                if($report_type_id == 10004){
                    $path = 'C:/work/auto_reporter/reporter/guohaichi-php/drug/drug_analyses.py';
                }else if($report_type_id == 10001 || $report_type_id == 10002){
                    $path = 'C:/work/auto_reporter/reporter/yn-cmd/bin/data_fiter_v2.0.py';  
                }
                $str = @exec("python $path $json 2>&1", $arr1, $ret);
                var_dump($arr1);
                $result = json_decode(end($arr1),true);
                if($result['code'] == 0){
                    //更新数据库
                    $result_l = ReportFilter::updateAll(['report_type_id'=>$report_type_id,'report_template_id'=>$report_template_id,'download_url'=>$datat['output_download_path']],['unique'=>$unique]);
                    if($result_l){
                        return $this->api_result(200,$result['message'],$result['output_download_path']);
                    }else{
                        return $this->api_result(201,$result['message']);
                    }
                }else{
                    return $this->api_result(202,$result['message']);
                }
            }else{
                return $this->api_result(203,'缺少参数');
            }
        }
    }


    /**
     * @method   整合数据请求 python
     * @author : gengshaopeng
     * @Date   : 2019-12-03
     */
    static function requestPy($unique,$report_template_id,$uname){
        $reportZhushi = ReportFilter::find()->where(['unique'=>$unique])->asArray()->one();
        $arrdata = array();
        $input_path = isset($reportZhushi['upload_url']) ? $reportZhushi['upload_url'] : '';
        $file_name = md5(uniqid(md5(microtime(true)), true)).'.xlsx';
        $output_dir = 'C:/wnmp/www/auto-report/api/web/filter_output_path/'.$uname.'/'.date('Ymd');     //输出路径
        if (!is_dir($output_dir)) {
            mkdir($output_dir, 0755, true);
        }
        $output_path = $output_dir.'/'.$file_name;
        $output_download_path = str_replace('C:/wnmp/www/auto-report/api/web',Yii::$app->params['pathurl'],$output_path); //下载文件路径
//        $output_path = str_replace('C:/phpStudy/PHPTutorial/WWW/auto-report/api/web',Yii::$app->params['pathurl'],$reportZhushi['upload_url']); //下载文件路径
        $arrdata['input_path'] = $input_path;
        $arrdata['output_path'] = $output_path;
        $arrdata['output_download_path'] = !empty($output_download_path)?$output_download_path:'';    //ip加端口号的路径用于下载doc
        $arrdata['mode'] = $report_template_id;
        $json = json_encode($arrdata,JSON_UNESCAPED_SLASHES);              //组装的数据
        return $json;
    }
}
