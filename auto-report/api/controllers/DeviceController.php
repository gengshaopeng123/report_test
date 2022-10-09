<?php

namespace api\controllers;
header('Access-Control-Allow-Origin:*');
header("Content-type:text/html;charset=utf-8");
require_once "Export.php";
use SebastianBergmann\Exporter\Exporter;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

class DeviceController extends BaseController
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
        $this->Redis = Yii::$app->redis;
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

    public function actionCeshi()
    {
        $postData = Yii::$app->request->post();
        if (isset($postData['type']) && $postData['type'] == 1) {
            //文件形式
            $postdata =  Yii::$app->request->post('fileData');
            $strrevdata = base64_decode($postdata,true);
            $file = 'C:\phpStudy\PHPTutorial\WWW' . '\\' . mt_rand(1, 1000) . '.xlsx';          //生成的excel文件
            file_put_contents($file,$strrevdata);
//            $receiveFile = 'receive.txt';
//            $ret = $this->receiveStreamFile($receiveFile);
//            var_dump($ret);exit;
//            echo json_encode(array('success' => (bool)$ret));
        } else {
            //输入内容
            require(__DIR__ . '/../../vendor/phpoffice/phpexcel/Classes/PHPExcel.php');
            $dir = 'C:\phpStudy\PHPTutorial\WWW';  //定义所在路径
            $postdata = Yii::$app->request->post('fileData');
            if ($postdata != NULL) {
                $objPHPExcel = new \PHPExcel();                     //实例化一个PHPExcel()对象
                $objSheet = $objPHPExcel->getActiveSheet();         //选取当前的sheet对象
                $objSheet->setTitle('helen');                //对当前sheet对象命名
                $objSheet->fromArray($postdata);                    //利用fromArray()直接一次性填充数据
                $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');   //设定写入excel的类型
                $file = $dir . '\\' . mt_rand(1, 1000) . '.xlsx';   //生成的excel文件
                $objWriter->save($file);                            //保存文件
            } else {
                return $this->api_result('202', '提交数据有问题', '');
            }
        }
        $storagepath = "C:\\phpStudy\\PHPTutorial\\WWW\\".mt_rand(1, 1000)."output_file.xlsx";      //生成的基因文件
        $path = "C:\\phpStudy\\PHPTutorial\\WWW\\search_seq_and_mark_mutation.py " . $file . " > ".$storagepath;
        $str = @exec("c:/python27/python $path 2>&1", $arr, $ret);
        $returnData = [
            'filepath' => 'http://192.168.1.252:8080/jiyin/output_file.xls',
            'filename' => 'output_file.xls'
        ];
        if ($ret === 0) {
            return $this->api_result('200', '文件已生成',$returnData);
        } else {
            return $this->api_result('201', '脚本处理数据发生错误', '');
        }
    }


    public  function actionExportdata(){
        //测试数据
        $headerList= ['列名1','列名2','列名3'];
        $data = [
          ['值1','值2','值3'],
          ['值11','值22','值33'],
          ['值111','值222','值333']
        ];
        $fileName = "测试导出文件名";
//        $tmp = ['备份字段1','备份值1','','备份字段2','备份值2'];

        $export = new \exportcsv();
        $result = $export->exportToCsv($headerList,$data,$fileName);
    }

	/**
     * @method   设备预约时间接口
     * @author : gsp
     * @Date   : 2018-04-25
     */
    public function actionSelectdevice()
    {
        $PostData = $this->PostData;
        $Redis = $this->Redis;
        $did = isset($PostData['did'])?$PostData['did']:'';
       	if(!empty($did)){
            if($Redis->exists('selectdevice-'.$did)){
                $JsonData = $Redis->get('selectdevice-'.$did);
				print_r($JsonData);exit;
			}else{
            	$Device = Device::find()->joinWith(['yySetup'])->where(['shop_device.id'=>$did,'status'=>1])->asArray()->all();
				$JsonData = $this->api_result('200','成功返回',$Device);
               	$Redis->set('selectdevice-'.$did,$JsonData);
            }
            return $JsonData;
        }else{
            return $this->api_result('204','缺少参数','');
        }   
    }
    /**
     * @method   设备预约时间选择判断接口
     * @author : gsp
     * @Date   : 2018-04-25
     */
    public function actionSelectyytradecount()
    {
        $PostData = $this->PostData;
        $pid = isset($PostData['pid'])?$PostData['pid']:'';
        $did = isset($PostData['did'])?$PostData['did']:'';
        $yytime = isset($PostData['yytime'])?$PostData['yytime']:'';
        if(!empty($pid) && !empty($yytime)){
            $isStock = YyProduct::find()->alias('yyp')->select('dev.is_stock,yyp.id,yyp.device_id')->joinWith('device as dev')->where(['yyp.id'=>$pid])->asArray()->one();
            $ProArr = YyProduct::find()->select('id')->where(['device_id'=>$did])->asArray()->all();
            $Arr = array();
			foreach($ProArr as $v){
				array_push($Arr,$v['id']);
			}
			$yyTrade = YyTrade::find()->select('id')->where(['yy_time'=>$yytime])->andWhere(['in','product_id',$Arr])->andWhere(['<>','status',7])->asArray()->count();
			if($yyTrade < $isStock['is_stock']){
                return $this->api_result('200','可预约','');
            }else{
                return $this->api_result('201','不可预约','');
            }
        }else{
            return $this->api_result('204','缺少参数','');
        }   
    }
}
