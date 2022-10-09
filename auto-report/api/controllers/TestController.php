<?php
namespace api\controllers;
header('Access-Control-Allow-Origin:*');
header("Content-type:text/html;charset=utf-8");
require_once '../../PHPExcel/Classes/PHPExcel.php';
require_once '../../PHPExcel/Classes/PHPExcel/IOFactory.php';
require_once '../../PHPExcel/Classes/PHPExcel/Cell.php';

use api\job\Job;
use common\models\Admin;
use common\models\ExpendDetail;
use common\models\Report;
use common\models\ReportCreate;
use common\models\ReportParameters;
use common\models\User;
use Yii;
use yii\db\Exception;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

class TestController extends BaseController
{
    public $enableCsrfValidation = false;
    public $PostData;
    public $Redis;

    /**
     * @method   初始化方法
     * @author : gsp
     * @Date   : 2019-09-28
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

    public function curlPost($url,$data=array()){
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        // POST数据

        curl_setopt($ch, CURLOPT_POST, 1);

        // 把post的变量加上

        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

        $output = curl_exec($ch);

        curl_close($ch);

        return $output;
    }

    public function actionTest()
    {
        $signData = ['email'=>'m1@qq.com','password'=>'123'];
        $signUrl = "http://52.80.150.199:8002/api/v1/auth/login";
        $signResponse = $this->curlPost($signUrl,$signData);
        if(json_decode($signResponse)->code!=0){
            return $this->api_result('201','测序管理系统请求错误');
        }
        $token = json_decode($signResponse)->token;
        echo $token;exit;
        $json =  '{"input_path":"C:/phpStudy/PHPTutorial/WWW/auto-report/api/controllers/upload/20191224/7f1ea885ffb6365a282a88a1f5ab0a60.xlsx","output_download_path":"http://192.168.1.178:8020/output_path/kunpeng/20191224/30899b5bb5b3ee2a0cca78974a8b244a.docx","sample_path":"C:/phpStudy/PHPTutorial/WWW/auto-report/api/controllers/upload/20191224/bfdc7031c77e383bc57776b21ad1ec7f.xlsx","output_path":"C:/phpStudy/PHPTutorial/WWW/auto-report/api/web/output_path/kunpeng/20191224/30899b5bb5b3ee2a0cca78974a8b244a.docx","mode":"36","logo":"","yidai":"","organization":""}';

        $weizhi = strrpos($json,'organization');
        $json1 = substr($json,1,$weizhi-1);
        $json2 = substr($json,$weizhi);
        $json1 = str_replace("\\/", "/", $json1);
        $json1 = str_replace("\\", "/", $json1);
        $json1 = str_replace("\"", "\\\"", $json1);
        $json2 = str_replace("\"", "\\\"", $json2);
        var_dump($json1.$json2);exit;
        $str = "Hello world. I love Shanghai!";
        print_r(explode(" ",$str));
        echo Yii::$app->params['pathurl'];exit;
//            $file_name = md5(uniqid(md5(microtime(true)), true)).'.doc';
////
////            $output_path = 'C:/auto_report/gengshao/'.date('Ymd') .'/'.$file_name;
////            var_dump($output_path);exit;
//        测试调取python脚本
//        $path = 'C:/phpStudy/PHPTutorial/WWW/auto_reporter/reporter/guohaichi-php/tumour/tumour_97.py';
        $path = 'C:/phpStudy/PHPTutorial/WWW/auto_reporter/reporter/yn-cmd/Auto_repoter_v1.0.py';
        $json = '{\"input_path\":\"C:/phpStudy/PHPTutorial/WWW/auto_reporter/reporter/yn-cmd/sample_01_filter.xls\",\"sample_path\":\"C:/phpStudy/PHPTutorial/WWW/auto_reporter/reporter/yn-cmd/样本信息.xlsx\",\"output_path\":\"C:/Users/nice/Desktop/222.docx\",\"mode\":\"4\"}';
//        file_put_contents('C:/phpStudy/PHPTutorial/WWW/auto_reporter/reporter/guohaichi/tumour/1.bat',"python $path $json");
        $str = @exec("python $path $json 2>&1", $arr1, $ret);
        var_dump($arr1);exit;
        $result = json_decode(end($arr1),true);
        if($result['code'] == 0){
            return $this->api_result(200,$result['message'],$result['output_path']);
        }else{
            return $this->api_result(201,$result['message']);
        }
        $unique = '11224049853488400';
        $uname = 'gengshao';
        $admin  = Admin::find()->select('id')->where(['username'=>$uname])->asArray()->one();
        $uid = $admin['id'];
        $ybxx = ReportCreate::find()->select('url')->where(['type'=>1,'unique'=>$unique])->asArray()->one();
        $ybxx_url = isset($ybxx['url']) ? $ybxx['url'] : '';
        $zsjg = ReportCreate::find()->select('url')->where(['type'=>2,'unique'=>$unique])->asArray()->one();
        $zsjg_url = isset($zsjg['url'])?$zsjg['url']:'';

        $ybxx_file_name = $ybxx_url;                                  //样本信息
        $obj_PHPExcel = \PHPExcel_IOFactory::load($ybxx_file_name);   //加载文件内容
        $excel_array=$obj_PHPExcel->getsheet(0)->toArray();           //转换为数组格式
        $arr  = reset($excel_array); //获取字段名(标题)
        unset($excel_array[0]);
        $ybxx_data = [];
        for($i = 0;$i < count($excel_array);$i++){
            foreach ($arr as $key => $value){
                $ybxx_data[$i][$value] = $excel_array[$i+1][$key];//使数组的键值就是数据表的字段名
            }
        }
        $ybxx_sample = array_unique(array_column($ybxx_data,'样本编号'));

        $zsjg_file_name = $zsjg_url;                                 //注释结果
        $obj_PHPExcel = \PHPExcel_IOFactory::load($zsjg_file_name);  //加载文件内容
        $res = [];
        $sheets = $obj_PHPExcel->getSheetNames();
        foreach($sheets as $v){//循环获取到的工作表名称
            $res[] = $v;
        }
        $sheet_arr = array_flip($res);
        $sheet_num = isset($sheet_arr['variant'])?$sheet_arr['variant']:'';
        $excel_array=$obj_PHPExcel->getsheet($sheet_num)->toArray();     //转换为数组格式 (variant)
        $arr  = reset($excel_array); //获取字段名(标题)
        unset($excel_array[0]);
        $zsjg_data = [];
        for($i = 0;$i < count($excel_array);$i++){
            foreach ($arr as $key => $value){
                $zsjg_data[$i][$value] = $excel_array[$i+1][$key];//使数组的键值就是数据表的字段名
            }
        }
        $zsjg_sample = array_unique(array_column($zsjg_data,'SAMPLE'));
        $jiaoji = array_intersect($zsjg_sample,$ybxx_sample);
        $reportData = array();
        if(!empty($jiaoji)){
            //报告信息就是这个
            foreach ($ybxx_data as $ybk=>$ybv){
                foreach ($jiaoji as $k=>$v){
                    if($v == $ybv['样本编号']){
                        $reportData[] = $ybv;
                    }
                }
            }
            //拿到报告数据
            foreach ($reportData as $rek=>$rev){
                $reportMode = new Report();
                $reportMode->sample_name = $rev['样本编号'];
                $reportMode->type_id = 1;
                $reportMode->template_id = 2;
                $reportMode->user_name = $rev['姓名'];
                $reportMode->sex = $rev['性别'];
                $reportMode->age = $rev['年龄'];
                $reportMode->uid = $uid;
                $reportMode->status = 3;
                if($reportMode->save()){
                    continue;
                }else{
                    var_dump($reportMode->getErrors());
                    break;
                }
            }
            echo 123;exit;
        }else{
            return $this->api_result(201,'提交样本信息和注释结果有问题');
        }











        $file_name = 'C:/phpStudy/PHPTutorial/WWW/实体肿瘤样本信息.xlsx';         //获取上传文件的地址名称
        $obj_PHPExcel = \PHPExcel_IOFactory::load($file_name);  //加载文件内容
        $excel_array=$obj_PHPExcel->getsheet(0)->toArray();   //转换为数组格式
        $arr  = reset($excel_array); //获取字段名(标题)
        foreach ($arr as $k=>$v){
            if(empty($v)){
                unset($arr[$k]);
            }
        }
        unset($excel_array[0]);
        $data = [];
        for($i = 0;$i < count($excel_array);$i++){
            foreach ($arr as $key => $value){
                $data[$i][$value] = $excel_array[$i+1][$key];//使数组的键值就是数据表的字段名
            }
        }
    }

    public function actionToken(){
        $session = Yii::$app->session;
        $sessionUname = $session['username'];
        var_dump($sessionUname);exit;
        $data = $this->Checklogin();
        if($data == 201 || $data == 202){
            return $this->api_result(205,'登录信息失效');
        }
        $session = Yii::$app->session;
        $sessionUname = $session['username'];
        $sessionPass = $session['password'];
        var_dump($sessionUname);exit;
    }

    public function actionLogout()
    {
        //销毁session
        $session = Yii::$app->session;
        $session->remove('username');
        $session->remove('password');
        var_dump($session['username']);exit;
    }

    public function actionQueue()
    {
        Yii::$app->queue->push(new Job(["name"=>"name","age" => "66666"]));
    }

}
