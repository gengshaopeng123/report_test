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
use common\models\ReportClassify;
use common\models\ReportCreate;
use common\models\ReportParameters;
use common\models\User;
use Yii;
use yii\db\Exception;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

class BatchreportController extends BaseController
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
     * @method   自动化报告生成报告
     * @author : gengshaopeng
     * @Date   : 2020-06-15
     */
    public function actionCreatereport(){
        set_time_limit(0);
        $postdata =  Yii::$app->request->post();                                            //数据
        $uname = isset($postdata['username']) ? $postdata['username'] :'';
        $admin  = Admin::find()->select('id')->where(['username'=>$uname])->asArray()->one();
        if($admin){
            $uid = isset($admin['id']) ? $admin['id']:'';
        }
        $unique = isset($postdata['unique']) ? $postdata['unique'] :'';                     //确定是这一批数据 的唯一表示
        $type = isset($postdata['type']) ? $postdata['type'] :'';                           //文件类型区分 1:样本信息 2：注释结果

        $report_type_id = isset($postdata['report_type']) ? $postdata['report_type'] :'';    //报告类型id
        $report_template_id = isset($postdata['report_template']) ? $postdata['report_template'] :'';   //模板文件id
        $last_request = isset($postdata['last_request']) ? $postdata['last_request'] :'';   //确定是否是最后一个请求
        $cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;
        $cacheSettings = array();
        \PHPExcel_Settings::setCacheStorageMethod($cacheMethod,$cacheSettings);
        if(isset($postdata['type']) && !empty($postdata['type'])){
            //有type参数的为文件上传  上传完后入库（report_create）
            if (!empty($_FILES)) {
                $file_name = $_FILES["file"]["name"];
                // 限制文件大小
                $file_size = $_FILES["file"]["size"];
                // 限制10M大小
                if ($file_size > 1024 * 1024 * 50) {
                    echo '文件大小超过限制';
                    exit;
                }
                // 限制文件上传类型
                $file_type = $_FILES["file"]["type"];
                $file_type_arr = ['image/jpg', 'image/jpeg', 'image/png', 'image/pjpeg',
                    'image/gif','application/vnd.ms-excel',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'application/octet-stream',
                    'application/x-zip-compressed',
                ];
                if (!in_array($file_type, $file_type_arr)) {
                    echo '上传文件类型错误';
                    exit;
                }

                // 文件上传到服务器临时文件夹之后的文件名
                $tem_name = $_FILES['file']['tmp_name'];

                // 取得文件后缀名
                $arrc = count(explode('.', $_FILES['file']['name']));
                $file_fix = explode('.', $_FILES['file']['name'])[$arrc-1] ? explode('.', $_FILES['file']['name'])[$arrc-1] : 'png';

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

                //入库表 文件上传
                $reportModel = new ReportCreate();
                $reportModel->url = $file_dir . $name;
                $reportModel->unique = $unique;
                $reportModel->type = $type;
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
            //如果是
        }else{
            //这里文件都上传完成
            $ybxx = ReportCreate::find()->select('url')->where(['type'=>1,'unique'=>$unique])->asArray()->one();
            $ybxx_url = isset($ybxx['url']) ? $ybxx['url'] : '';
            $zsjg = ReportCreate::find()->select('url')->where(['type'=>2,'unique'=>$unique])->asArray()->one();
            $zsjg_url = isset($zsjg['url'])?$zsjg['url']:'';

            $ybxx_file_name = $ybxx_url;                                 //样本信息
            $obj_PHPExcel = \PHPExcel_IOFactory::load($ybxx_file_name);  //加载文件内容
            $res = [];
            $sheets = $obj_PHPExcel->getSheetNames();
            foreach($sheets as $v){//循环获取到的工作表名称
                $res[] = $v;
            }
            $sheet_arr = array_flip($res);
            $sheetNum = isset($sheet_arr['样本信息'])?$sheet_arr['样本信息']:'';
            if(!$sheetNum){
                return $this->api_result('201','样本信息文件有问题');
            }
            $excel_array=$obj_PHPExcel->getsheet($sheetNum)->toArray();     //转换为数组格式
            $arr  = reset($excel_array); //获取字段名(标题)
            unset($excel_array[0]);
            $ybxx_data = [];
            for($i = 0;$i < count($excel_array);$i++){
                foreach ($arr as $key => $value){
                    $ybxx_data[$i][$value] = $excel_array[$i+1][$key];//使数组的键值就是数据表的字段名
                }
            }
            $ybxx_sample = array_unique(array_column($ybxx_data,'样本编号'));
            $ybxxData = [];
            foreach ($ybxx_sample as $v){
                if(!empty($v)){
                    $ybxxData[]=strval($v);
                }
            }
            $signData = ['email'=>'m1@qq.com','password'=>'123'];
            $signUrl = "http://52.80.150.199:8002/api/v1/auth/login";
            $signResponse = $this->curlPost($signUrl,$signData,$header='');
            if(json_decode($signResponse)->code!=0){
                return $this->api_result('201','测序管理系统请求错误');
            }
            $token = json_decode($signResponse)->token;
            $header=array(
                'authorization:bearer'.$token,
            );
            $url = "https://sms.anngene.top/api/v1/web/reportSample/getSampleInfo";  //获取样本信息的接口
            // $url = "http://52.80.150.199:8002/api/v1/web/reportSample/getSampleInfo";  //获取样本信息的接口
            $data = [
                'srcCodes'=>$ybxxData,
            ];
            $response = $this->curlPost($url,$data,$header);
            $returnData = json_decode($response,true);
            if(!isset($returnData['code']) && !isset($returnData['data'])){
                return $this->api_result('201','错误请求，请联系管理人员');
            }
            if($returnData['code']!=0){
                return $this->api_result('201',$returnData['msg']);
            }
            $samples = $returnData['data'];
            // var_dump($samples);die;
            $returnSamples = [];  //测序管理系统返回的样本信息
            if(count($samples) <1){ 
                return $this->api_result('201','未找到符合对应的样本信息，请检查');
            }
            foreach ($samples as $key => $value) {
                $returnSamples[] = strval($value['srcCode']);
            }
            $diffSamples = implode(',',(array_diff($ybxxData,$returnSamples)));
            $modeType = ReportClassify::find()->select('name')->where(['id'=>$report_template_id])->asArray()->one();
            $sampleTypeName = isset($modeType['name']) ? $modeType['name'] :'';
            $sampleDatas = [];
            $fileNameArr = [];
            foreach ($samples as $v){
                $customField = json_decode($v['customField'],true);
                $sampleDatas[]['sampleInfo'] = [
                    $v['srcCode']=>[
                        'sampleId'=>$v['srcCode'],
                        'name'=>$customField['name'],
                        'sex'=>$customField['gender']['value'],
                        'age'=>$customField['birth'],
                        'sampleType'=>$v['sampleType'],
                        'receiveTime'=>date("Y-m-d",strtotime($v['receivedAt'])),
                    ]
                ];
                $fileNameArr[] = $v['srcCode'].$sampleTypeName.time().'.docx';
                $transaction = Yii::$app->db->beginTransaction();
                try{
                    $reportMode = new Report();
                    $reportMode->sample_name = $v['srcCode'];
                    $reportMode->type_id = $report_type_id;
                    $reportMode->template_id = $report_template_id;
                    $reportMode->user_name = $customField['name'];
                    $reportMode->sex = $customField['gender']['value'];
                    $reportMode->age = $customField['birth'];
                    $reportMode->uid = $uid;
                    $reportMode->unique = $unique;
                    $reportMode->status = 3;
                    $reportMode->save();
                    $transaction->commit();
                }catch (\Exception $e) {
                    $transaction->rollBack();
                    return $this->api_result(201,'database error');
                }
            }
            //最后请求标识 整合这些数据
            if($report_type_id && $report_template_id){
                foreach($sampleDatas as $k=>$sampleData){
                    $json = self::requestPy($unique,$uname,$report_template_id,$sampleData,$fileNameArr[$k]);
                    $json = str_replace("\\/", "/", $json);
                    $json = str_replace("\\", "/", $json);
                    $json = str_replace("\"", "\\\"", $json);
                    $output_path = $json['output_path'];
                    $json = strval($json['json']);
                    if($report_template_id == 901){
                        $path = 'C:/work/auto_reporter/reporter/yn-cmd/16sGutReport/bin/qiime2_report_online.py'; 
                    }elseif($report_template_id == 902){
                        $path = 'C:/work/auto_reporter/reporter/Coronary-heart-disease/guanxinbing_report_batch.py';
                    }
                    
                    $this->actionJob($path,$json,$unique,$uname,$output_path);
                }
                return $this->api_result(200,'正在生成中',$diffSamples);
            }else{
                return $this->api_result(201,'缺少参数');
            }
        }
    }
    public function curlPost($url,$data,$header){
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        // POST数据

        curl_setopt($ch, CURLOPT_POST, 1);
        if(!empty($header)){
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_HEADER, 0);//返回response头部信息
        }
        // 把post的变量加上

        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

        $output = curl_exec($ch);

        curl_close($ch);

        return $output;
    }
    public function postData($url, $data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

    /**
     * @method   整合数据请求 python
     * @author : gengshaopeng
     * @Date   : 2019-12-03
     */
    static function requestPy($unique,$uname,$report_template_id,$sampleData,$fileName){
        $reportYangben = ReportCreate::find()->where(['unique'=>$unique,'type'=>1])->asArray()->one();
        $reportZhushi = ReportCreate::find()->where(['unique'=>$unique,'type'=>2])->asArray()->one();
        $arrdata = array();
        $input_path = isset($reportZhushi['url']) ? $reportZhushi['url'] : '';
        $output_dir = 'C:/wnmp/www/auto-report/api/web/output_path/'.$uname.'/'.date('Ymd');      //输出路径
        if (!is_dir($output_dir)) {
            mkdir($output_dir, 0755, true);
        }
        $output_path = $output_dir.'/'.$fileName;
        $output_download_path = str_replace('C:/wnmp/www/auto-report/api/web',Yii::$app->params['serverPathurl'],$output_path); //下载文件路径
        $arrdata['inputPath'] = !empty($input_path)?$input_path:'';
        $arrdata['outputDownloadPath'] = !empty($output_download_path)?$output_download_path:'';    //ip加端口号的路径用于下载doc
        $arrdata['outputPath'] = !empty($output_path)?$output_path:'';
        $arrdata['mode'] = $report_template_id;
        $arrdata['sampleInfo'] = !empty($sampleData['sampleInfo']) ?$sampleData['sampleInfo'] :'';
        $json = json_encode($arrdata,JSON_UNESCAPED_SLASHES);              //组装的数据\
        return [
            'json'=>$json,
            'output_path'=>$output_path
        ];
        // return $json;
    }


    public function actionJob($path,$json,$unique,$uname,$output_path){
        Yii::$app->queue->push(new Job(["path"=>$path,"json" => $json,'unique'=>$unique,'uname'=>$uname,'outputPath'=>$output_path]));
    }


    //保存远程文件到本地
    function grabFile($url,$save_path){
        $file = $this->curl_file_get_contents($url);
        file_put_contents($save_path,$file);
        return $file;
    }
    //根据url创建文件目录
    function createFilename($inputpathUrl){
        // 文件重命名，这里自动生成一个不重复的名字，方便使用
        $file_fix = strrchr($inputpathUrl, '.');
        $expArr = explode("&",$file_fix);
        if(count($expArr)>1){
            $file_fix = $expArr[0];
        }
        $name = md5(uniqid(md5(microtime(true)), true)) . $file_fix;
        // 要存放文件的目录定义，这里按日期分开存储
        $file_dir = dirname(__FILE__) . '/upload/' . date('Ymd') . '/';
        $file_dir = str_replace('\\','/',$file_dir);
        // 检测要存放文件的目录是否存在，不存在则创建
        if (!is_dir($file_dir)) {
            mkdir($file_dir, 0755, true);
        }
        return $file_dir.$name;
    }

    function curl_file_get_contents($durl){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $durl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //将获取的信息以字符串形式返回
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 5);  //指定最多的 HTTP 重定向次数
        curl_setopt($ch,CURLOPT_TIMEOUT,6); //允许 cURL 函数执行的最长秒数
        $r = curl_exec($ch);
        if(curl_errno($ch)){  //如果存在错误，输出错误（超时是不会抛出异常的，需要用这个函数设置自己的处理方式）
            echo 'Curl error: ' . curl_error($ch);
        }
        return $r;
    }
}
