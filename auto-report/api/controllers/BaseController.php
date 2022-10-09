<?php
namespace api\Controllers;
header('Access-Control-Allow-Origin:*');

use common\models\ReportCreate;
use Yii;
use yii\web\Controller;
use common\models\Admin;
use yii\web\UploadedFile;
use common\components\pay\JsApiPay;

class BaseController extends Controller
{
	public $cache;
	public $redis; 
	/**
     * @method   初始化方法 
     * @author : gengshaopeng
     * @Date   :2019-11-15
     *
     */
    public function init()
    {
        $this->enableCsrfValidation = false;
		$this->cache = Yii::$app->cache;
		$this->redis = Yii::$app->redis;
    }

    /**
     * @method   验证登录
     * @author : gengshaopeng
     * @Date   :2019-11-15
     */
    public function Checklogin()
    {
        $session = Yii::$app->session;
        if (!isset($session['username']) || empty($session['username']))
        {
            return 201;
        }

        if(isset($session['expiretime'])) {
            if($session['expiretime'] < time()) {
                unset($session['expiretime']);
                unset($session['username']);
                unset($session['password']);
                return 202;
            } else {
                $session['expiretime'] = time() + 3600*2; // 刷新时间戳  2小时过期
            }
        }
    }

	/**
     * @method : 请求验证
     * @author : gengshaopeng
     * @Date   : 2019-11-15
     */
    public function respond(){
		$PostData  = Yii::$app->request->post();
		$timeStamp = isset($PostData['timeStamp']) ? $PostData['timeStamp'] : '' ;	
		$randomStr = isset($PostData['randomStr']) ? $PostData['randomStr'] : '' ;	
		$signature = isset($PostData['signature']) ? $PostData['signature'] : '' ;	
		//$timeStamp = '1514198675207';
		//$randomStr = '937323';
		//$signature = '69DA2D852A17F85499966C0C090C8D9A';		
		if($timeStamp == '' || $randomStr == '' || $signature == ''){
			return $this->redirect('/error/error/1');		
		}	
		//验证身份
		$str = $this -> arithmetic($timeStamp,$randomStr);
		//file_put_contents('555.php',$str.'--'.$timeStamp.'--'.$randomStr.'--'.$signature.'--'.$str);
		if($str != $signature){
			return $this->redirect('/error/error/2');			
		}
	}

	/**
     * @method : 验证方法
     * @author : gengshaopeng
     * @Data   : 2019-11-15
     */
    public function arithmetic($timeStamp,$randomStr){
        $arr[] = $timeStamp;
        $arr[] = $randomStr;
        $arr[] = Yii::$app->params['api_yan'];
        //按照首字母大小写顺序排序
        sort($arr,SORT_STRING);
		//拼接成字符串
        $str = implode($arr);
		//进行加密
        $signature = sha1($str);
		$signature = md5($signature);
        //转换成大写
        $signature = strtoupper($signature);
        return $signature;
    }

	/**
     * @method   返回信息
     * @author : gengshaopeng
     * @Date   : 2019-11-15
     */
    public function api_result($code,$msg,$data=''){
		$result = array('code' => $code , 'msg' => $msg, 'data'=> $data);
		return json_encode($result);
	}

    /**
     * @method   清除缓存
     * @author : gengshaopeng
     * @Date   : 2019-11-15
     */
    public function del_redis($arr){
        $redis = Yii::$app->redis;
        foreach ($arr as $key => $value) {
            $station = $redis->keys($value);
            foreach($station as $v){
                $redis->del($v);
            }
        } 
    }

    /**
     * @method   文件上传
     * @author : gengshaopeng
     * @Date   : 2019-12-2
     */

    public function uploadFile($unique,$type,$relation,$direction,$site,$relationNote){
        if (!empty($_FILES)) {
            // 限制文件大小
            $file_size = $_FILES["file"]["size"];
            // 限制2M大小
            if ($file_size > 1024 * 1024 * 2) {
                echo '文件大小超过限制';
                exit;
            }
            // 限制文件上传类型
            $file_type = $_FILES["file"]["type"];
            $file_type_arr = ['image/jpg', 'image/jpeg', 'image/png', 'image/pjpeg', 'image/gif','application/vnd.ms-excel','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
            if (!in_array($file_type, $file_type_arr)) {
                echo '上传文件类型错误';
                exit;
            }

            // 文件上传到服务器临时文件夹之后的文件名
            $tem_name = $_FILES['file']['tmp_name'];

            // 取得文件后缀名
            $file_fix = explode('.', $_FILES['file']['name'])[1] ? explode('.', $_FILES['file']['name'])[1] : 'png';

            // 文件重命名，这里自动生成一个不重复的名字，方便使用
            $name = md5(uniqid(md5(microtime(true)), true)) . '.' . $file_fix;

            // 要存放文件的目录定义，这里按日期分开存储
            $file_dir = dirname(__FILE__) . '/upload/' . date('Ymd') . '/';

            // 检测要存放文件的目录是否存在，不存在则创建
            if (!is_dir($file_dir)) {
                mkdir($file_dir, 0755, true);
            }

            // 移动文件到指定目录下
            @ move_uploaded_file($tem_name, $file_dir . $name);
            echo '上传成功';
            //入库表
            $reportModel = new ReportCreate();
            $reportModel->url = $file_dir . $name;
            $reportModel->unique = $unique;
            $reportModel->type = $type;
            $reportModel->relation = $relation;
            $reportModel->direction = $direction;
            $reportModel->site = $site;
            $reportModel->relation_note = $relationNote;
            $reportModel->save();
            exit;
        } else {
            echo '文件上传失败';
            exit;
        }
    }


    /**
     * @method   生成文件名字
     * @author : gengshaopeng
     * @Date   : 2019-12-20
     */
    public function createFile($uname,$type){
        $file_name = md5(uniqid(md5(microtime(true)), true)).$type; //文件名
        $output_dir = 'C:/auto_report/'.$uname.'/'.date('Ymd');          //输出路径
        if (!is_dir($output_dir)) {
            mkdir($output_dir, 0755, true);
        }
        $output_path = $output_dir.'/'.$file_name;
        return $output_path;
    }
}
