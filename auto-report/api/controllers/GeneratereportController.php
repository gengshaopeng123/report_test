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

class GeneratereportController extends BaseController
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
     * @method   生成报告
     * @author : gengshaopeng
     * @Date   : 2019-11-28
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
        $type = isset($postdata['type']) ? $postdata['type'] :'';                           //文件类型区分 1:样本信息 2：注释结果 3:一代图片
        $relation = isset($postdata['qsgx']) ? $postdata['qsgx'] :'';                       //亲属关系
        $direction = isset($postdata['yzfx']) ? $postdata['yzfx'] :'';                      //验证方向
        $heterozygosity = isset($postdata['heterozygosity']) ? $postdata['heterozygosity'] :'';  //杂合性
        $variation_source = isset($postdata['variation_source']) ? $postdata['variation_source'] :'';    //变异来源
        $site = isset($postdata['wd']) ? $postdata['wd'] :'';                               //位点
        $relationNote = isset($postdata['gxbz']) ? $postdata['gxbz'] :'';                   //关系备注

        $company_name = isset($postdata['com_name']) ? $postdata['com_name'] :'';           //公司名称
        $header_footer = isset($postdata['ymyj']) ? $postdata['ymyj'] :'';                  //页眉页脚

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
                // 限制2M大小
                // if ($file_size > 1024 * 1024 * 100) {
                //     echo '文件大小超过限制';
                //     exit;
                // }
                // 限制文件上传类型
                $file_type = $_FILES["file"]["type"];
                $file_type_arr = [
					'image/jpg', 'image/jpeg', 'image/png', 'image/pjpeg', 'image/gif','application/vnd.ms-excel','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet','application/octet-stream',
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
                $reportModel->relation = $relation;
                $reportModel->direction = $direction;
                $reportModel->site = $site;
                $reportModel->relation_note = $relationNote;
                $reportModel->company_name = $company_name;
                $reportModel->header_footer = $header_footer;
                $reportModel->heterozygosity = $heterozygosity;
                $reportModel->variation_source = $variation_source;
                if($reportModel->save()){
                    echo 'success';
                }else{
					return $this->api_result(501,'database error');
                    var_dump($reportModel->getErrors());
                }
                exit;
            } else {
                echo '文件上传失败';
                exit;
            }
            //如果是
        }else{
            ini_set("memory_limit", "13312M");
            //这里文件都上传完成
            $ybxx = ReportCreate::find()->select('url')->where(['type'=>1,'unique'=>$unique])->asArray()->one();
            $ybxx_url = isset($ybxx['url']) ? $ybxx['url'] : '';
            $zsjg = ReportCreate::find()->select('url')->where(['type'=>2,'unique'=>$unique])->asArray()->one();
            $zsjg_url = isset($zsjg['url'])?$zsjg['url']:'';

            $ybxx_file_name = $ybxx_url;                                 //样本信息
            $obj_PHPExcel = \PHPExcel_IOFactory::load($ybxx_file_name);  //加载文件内容
            $excel_array=$obj_PHPExcel->getsheet(0)->toArray();     //转换为数组格式
            $arr  = reset($excel_array); //获取字段名(标题)
            unset($excel_array[0]);
            $ybxx_data = [];
            for($i = 0;$i < count($excel_array);$i++){
                foreach ($arr as $key => $value){
                    $ybxx_data[$i][$value] = $excel_array[$i+1][$key];//使数组的键值就是数据表的字段名
                }
            }

            $ybxx_sample = array_unique(array_column($ybxx_data,'样本编号')); //样本编号数据
            // var_dump($ybxx_sample);die;
            // file_put_contents('C:/logs/'.time().'样本信息.php',$ybxx_sample);
   //          $zsjg_file_name = $zsjg_url;                                 //注释结果
   //          $obj_PHPExcel = \PHPExcel_IOFactory::load($zsjg_file_name);  //加载文件内容
   //          $res = [];
   //          $sheets = $obj_PHPExcel->getSheetNames();
   //          foreach($sheets as $v){//循环获取到的工作表名称
   //              $res[] = $v;
   //          }
   //          $sheet_arr = array_flip($res);
   //          if($report_type_id == 10001 || $report_type_id == 10002 || $report_type_id == 10015){
   //              if(!isset($sheet_arr['judge-variant'])){
   //                  return $this->api_result(201,'注释结果中sheet名有问题，请重新提交');
   //              }
   //              $sheet_num = isset($sheet_arr['judge-variant'])?$sheet_arr['judge-variant']:'';
   //              $key = 'judge-variant';
   //          }else if($report_type_id == 10003 || $report_type_id == 10005 || $report_type_id == 10011 || $report_type_id == 10023){
   //              if(!isset($sheet_arr['variant'])){
   //                  return $this->api_result(201,'注释结果中sheet名有问题，请重新提交');
   //              }
   //              $sheet_num = isset($sheet_arr['variant'])?$sheet_arr['variant']:'';
   //              $key = 'variant';
   //          }else if($report_type_id == 10004){
   //              $sheet_num = 0;
   //          }else if($report_type_id == 10012){
			// 	if(isset($sheet_arr['judge-TL-variant'])){
			// 		$sheet_num = isset($sheet_arr['judge-TL-variant'])?$sheet_arr['judge-TL-variant']:'';
   //                  $key = 'judge-TL-variant';
			// 	}
			// 	if(isset($sheet_arr['judge-BL-variant'])){
			// 		$sheet_num = isset($sheet_arr['judge-BL-variant'])?$sheet_arr['judge-BL-variant']:'';
   //                  $key = 'judge-BL-variant';
			// 	}
			// }else if($report_type_id == 10013){
   //              if(!isset($sheet_arr['judge-variant'])){
   //                  return $this->api_result(201,'注释结果中sheet名有问题，请重新提交');
   //              }
			// 	if(isset($sheet_arr['judge-variant'])){
			// 		$sheet_num = isset($sheet_arr['judge-variant'])?$sheet_arr['judge-variant']:'';
   //                  $key = 'judge-variant';
			// 	}
			// }else{
   //              if(!isset($sheet_arr['judge-variant'])){
   //                  return $this->api_result(201,'注释结果中sheet名有问题，请重新提交');
   //              }
   //              $sheet_num = isset($sheet_arr['judge-variant'])?$sheet_arr['judge-variant']:'';
   //              $key = 'judge-variant';
   //          }
            // $currentSheet = $obj_PHPExcel->getSheetByName($key);// 通过页名称取得当前页
            // $row_num = $currentSheet->getHighestRow();// 当前页行数、
            // for ($i = 2; $i <= $row_num; $i++) 
            // { 
            //     $cell_values = array(); 
            //     foreach ($val as $cell_val) 
            //     {
            //         $address = $cell_val . $i;// 单元格坐标 
            //     }
            //     // 读取单元格内容 
            //     $excel_array[] = $currentSheet->getCell($address)->getFormattedValue(); 
                
            // } 
            // $sheet_count = $obj_PHPExcel->getSheetCount(); 
            // $currentSheet = $objPHPExcel->getSheet($s);// 当前页 
            // $row_num = $currentSheet->getHighestRow();// 当前页行数 
            // $col_max = $currentSheet->getHighestColumn(); // 当前页最大列号 
            // for($i = 2; $i <= $row_num; $i++) 
            // { 
            //     $cell_values = array(); 
            //     foreach ($val as $cell_val) 
            //     { 
            //         $address = $cell_val . $i;// 单元格坐标 

            //         // 读取单元格内容 
            //         $cell_values[] = $currentSheet->getCell($address)->getFormattedValue(); 
            //     } 
            // }
            $zsjg_sample = isset($ybxx_sample[0]) ? [$ybxx_sample[0]] : [];   //由于大文件 所以不读取文件内容，直接拿第一个样本作为信息
            if(!$zsjg_sample){
                return $this->api_result(201,'提交数据有问题，请重新提交');
            }
            // if($report_type_id == 10001 && $report_template_id ==5){
            //     $zsjg_sample = isset($ybxx_sample[0]) ? [$ybxx_sample[0]] : [];
            // }else{
            //     $excel_array = $obj_PHPExcel->getsheet($sheet_num)->toArray();     //转换为数组格式 (variant)注释结果
            //     $arr  = reset($excel_array); //获取字段名(标题)
            //     unset($excel_array[0]);

            //     if($report_type_id == 10004 && $report_template_id ==29){
            //         $title = '样本编号';
            //     }else if($report_type_id == 10004 && $report_template_id ==30){
            //         $title = 'Sample Description';
            //     }else{
            //         $title = 'SAMPLE';
            //     }

            //     // file_put_contents('C:/logs/'.time().'excel_array.php',json_encode($arr));
            //     $zsjg_data = [];
            //     for($i = 0;$i < count($excel_array);$i++){
            //         foreach ($arr as $key => $value){
            //             if($value==$title){
            //                 $zsjg_data[$i][$value] = $excel_array[$i+1][$key];//使数组的键值就是数据表的字段名
            //             }
            //         }
            //     }
            //     $zsjg_sample = array_unique(array_column($zsjg_data,$title));
            // }
            
			// file_put_contents('C:/logs/'.time().'zsjg_data.php',json_encode($zsjg_data));
            
			// file_put_contents('C:/logs/'.time().'注释结果2.php',json_encode($zsjg_sample));
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
//                //如果是药物基因组 拆分数据到不同的excel
//                if($report_type_id == 10004){
//                    $count = count($reportData);
//                    for ($i=1;$i<=$count; $i++) {
//
//                        file_put_contents();
//                    }
//                }
                // file_put_contents('C:/logs/'.time().'reportData.php',json_encode($reportData));
                //ruku报告数据
				$sampleId = '';
                foreach ($reportData as $rek=>$rev){
					$sampleId = $rev['样本编号'];
                    $reportMode = new Report();
                    $reportMode->sample_name = strval($rev['样本编号']);
                    $reportMode->type_id = $report_type_id;
                    $reportMode->template_id = $report_template_id;
					if($report_type_id == 10004 && $report_template_id == 29){
                        $reportMode->user_name = $rev['检测人'];
                    }else{
                        $reportMode->user_name = $rev['姓名'];
                    }
                    $reportMode->sex = $rev['性别'];
					if($report_type_id == 10004 && $report_template_id == 29){
                        $bir = $rev['出生日期'];
                        $year = substr($bir,0,4);
                        $now_year = date('Y',time());
                        $age = $now_year - $year;
                        $reportMode->age = strval($age);
                    }else{
						$age = strval(isset($rev['年龄'])?$rev['年龄']:'');
                        $reportMode->age = $age;
                    }
                    $reportMode->uid = $uid;
                    $reportMode->unique = $unique;
                    $reportMode->status = 3;
                    if($reportMode->save()){
                        continue;
                    }else{
						return $this->api_result(202,'提交数据异常');
                        var_dump($reportMode->getErrors());
                        break;
                    }
                }
            }else{
                return $this->api_result(201,'提交样本信息和注释结果有问题');
            }



            //无type参数的为数据   入库report_parameters表
            $parametersModel = new ReportParameters();
            $parametersModel->report_type_id = $report_type_id;
            $parametersModel->report_template_id = $report_template_id;
            $parametersModel->unique = $unique;
            if($parametersModel->save()){

            }else{
                var_dump($parametersModel->getErrors());
            }
            //最后请求标识 整合这些数据
            if($report_type_id && $report_template_id){
				$modeType = ReportClassify::find()->select('name')->where(['id'=>$report_template_id])->asArray()->one();
				$sampleTypeName = isset($modeType['name']) ? $modeType['name'] :'';
				$file_name = $sampleId.$sampleTypeName.time().'.docx';
				$file_name = str_replace('/','-',$file_name);
                $json = self::requestPy($unique,$uname,$report_type_id,$report_template_id,$file_name);
				$output_path = $json['output_path'];
				$json = $json['json'];
                $json = str_replace("\\/", "/", $json);
                $json = str_replace("\\", "/", $json);
                $json = str_replace("\"", "\\\"", $json);
                // file_put_contents('C:/logs/'.time().'提交数据3.php',$json);
                // var_dump($json);die;
//                $path = 'C:/work/auto_reporter/reporter/guohaichi-php/tumour/tumour_97.py';  //由python（海池）提供
                if($report_type_id == 10003 && $report_template_id == 35){
                    $path = 'C:/work/auto_reporter/reporter/guohaichi-php/tumour/tumour_13.py';  //由python（海池）提供
                }else if($report_type_id == 10003 && $report_template_id == 36){
                    $path = 'C:/work/auto_reporter/reporter/guohaichi-php/tumour/tumour_25.py';  //由python（海池）提供
                }else if($report_type_id == 10003 && $report_template_id == 37){
                    $path = 'C:/work/auto_reporter/reporter/guohaichi-php/tumour/tumour_65.py';  //由python（海池）提供
                }else if($report_type_id == 10003 && $report_template_id == 38){
                    $path = 'C:/work/auto_reporter/reporter/guohaichi-php/tumour/tumour_97.py';  //由python（海池）提供
                }else if($report_type_id == 10004 && $report_template_id == 15){
                    $path = 'C:/work/auto_reporter/reporter/guohaichi-php/drug/drug_asipilin.py';  //由python（海池）提供
                }else if($report_type_id == 10004 && $report_template_id == 16){
                    $path = 'C:/work/auto_reporter/reporter/guohaichi-php/drug/drug_biepiaolingchun.py';  //由python（海池）提供
                }else if($report_type_id == 10004 && $report_template_id == 17){
                    $path = 'C:/work/auto_reporter/reporter/guohaichi-php/drug/drug_erjiashuanggua.py';  //由python（海池）提供
                }else if($report_type_id == 10004 && $report_template_id == 18){
                    $path = 'C:/work/auto_reporter/reporter/guohaichi-php/drug/drug_fulikangzuo.py';  //由python（海池）提供
                }else if($report_type_id == 10004 && $report_template_id == 19){
                    $path = 'C:/work/auto_reporter/reporter/guohaichi-php/drug/drug_huafalin.py';  //由python（海池）提供
                }else if($report_type_id == 10004 && $report_template_id == 20){
                    $path = 'C:/work/auto_reporter/reporter/guohaichi-php/drug/drug_huangniaolei.py';  //由python（海池）提供
                }else if($report_type_id == 10004 && $report_template_id == 21){
                    $path = 'C:/work/auto_reporter/reporter/guohaichi-php/drug/drug_liuzuopiaoling.py';  //由python（海池）提供
                }else if($report_type_id == 10004 && $report_template_id == 22){
                    $path = 'C:/work/auto_reporter/reporter/guohaichi-php/drug/drug_lvbigelei.py';  //由python（海池）提供
                }else if($report_type_id == 10004 && $report_template_id == 23){
                    $path = 'C:/work/auto_reporter/reporter/guohaichi-php/drug/drug_meifensuanzhi.py';  //由python（海池）提供
                }else if($report_type_id == 10004 && $report_template_id == 24){
                    $path = 'C:/work/auto_reporter/reporter/guohaichi-php/drug/drug_takemosi.py';  //由python（海池）提供
                }else if($report_type_id == 10004 && $report_template_id == 25){
                    $path = 'C:/work/auto_reporter/reporter/guohaichi-php/drug/drug_tangpizhijisu.py';  //由python（海池）提供
                }else if($report_type_id == 10004 && $report_template_id == 26){
                    $path = 'C:/work/auto_reporter/reporter/guohaichi-php/drug/drug_tating.py';  //由python（海池）提供
                }else if($report_type_id == 10004 && $report_template_id == 27){
                    $path = 'C:/work/auto_reporter/reporter/guohaichi-php/drug/drug_xiaosuanganyou.py';  //由python（海池）提供
                }else if($report_type_id == 10004 && $report_template_id == 28){
                    $path = 'C:/work/auto_reporter/reporter/guohaichi-php/drug/drug_yesuan.py';  //由python（海池）提供
                }else if($report_type_id == 10004 && $report_template_id == 29){
                    $path = 'C:/work/auto_reporter/reporter/guohaichi-php/drug/child_drug.py';  //由python（海池）提供
                }else if($report_type_id == 10004 && $report_template_id == 30){
                    $path = 'C:/work/auto_reporter/reporter/guohaichi-php/drug/chronic_disease.py';  //由python（海池）提供
                }else if($report_template_id == 155 || $report_template_id == 156 || $report_template_id == 157 || $report_template_id == 158 || $report_template_id == 159 || $report_template_id == 160){
					$path = 'C:/work/auto_reporter/reporter/jianqi-cmd/report/report.py';
				}else if($report_type_id == 10001 && $report_template_id == 39){
					$path = 'C:/work/auto_reporter/reporter/Coronary-heart-disease/guanxinbing_report.py';  //由python（瑞泽）提供
				}else if($report_type_id == 10001 && $report_template_id == 56){
                    $path = 'C:/work/auto_reporter/reporter/jianqi-cmd/yimai_CAD/report.py';  //由python（瑞泽）提供
                }else if($report_type_id == 10001 && $report_template_id == 190){
                    $path = 'C:/work/auto_reporter/reporter/yn-cmd/XiJingHospitalReport/bin/XiJingHospitalAutoReport.py';  //由python（杨男）提供
                }else if($report_type_id == 10002 && $report_template_id == 57){
                    $path = 'C:/work/auto_reporter/reporter/jianqi-cmd/tianzhong/report_long.py'; 
                }
                else if($report_type_id == 10002 && $report_template_id == 58){
                    $path = 'C:/work/auto_reporter/reporter/jianqi-cmd/tianzhong/report_short.py'; 
                }
                else if($report_type_id == 10001 && $report_template_id == 392){
                     $path = 'C:/work/auto_reporter/reporter/jianqi-cmd/10000people_FH/zzqy_wes_report.py';
                }
                else if($report_type_id == 10001 || $report_type_id == 10002){
                    $path = 'C:/work/auto_reporter/reporter/yn-cmd/geneticDiseDetection/Auto_repoter_v1.0.py';  //由python（杨男）提供
                }else if($report_type_id == 10005){
					$path = 'C:/work/auto_reporter/reporter/血液肿瘤/Auto_Report_Blood_cancer.py';
				}else if($report_type_id == 10011){
					$path = 'C:/work/auto_reporter/reporter/guohaichi-php/blood/blood_reporter.py';
				}else if($report_type_id == 10012){
					$path = 'C:/work/auto_reporter/reporter/ruize-cmd/GO108/go108_report.py';
				}else if($report_type_id == 10013 && in_array($report_template_id, [501])){
                    $path = "c:/work/auto_reporter/reporter/ruize-cmd/sxrm/panel/sxrm_panel_report.py";
                }
                else if($report_type_id == 10013){
                    $path = 'C:/work/auto_reporter/reporter/ruize-cmd/sxrm/panel/sxrm_panel_report.py';
				}else if($report_type_id == 10015){
					$path = "C:/work/auto_reporter/reporter/ruize-cmd/sxrm/WES/sxrm_wes_report.py";
				}else if($report_type_id == 10018 && in_array($report_template_id,[502])){
                    $path = 'C:/work/auto_reporter/reporter/ruize-cmd/zhengzhou/panel/zzqy_panel_report.py';
                }
                else if($report_type_id == 10018){
                    $path = "c:/work/auto_reporter/reporter/ruize-cmd/zhengzhou/panel/zzqy_panel_report.py";
                }else if($report_type_id == 10019){
                    $path = "c:/work/auto_reporter/reporter/ruize-cmd/zhengzhou/wes/zzqy_wes_report.py";
                }else if($report_type_id == 10020 && in_array($report_template_id,[210])){
                    $path = "c:/work/auto_reporter/reporter/ruize-cmd/huaxi/cad/guanxinbing_report.py";
                }else if($report_type_id == 10020 && in_array($report_template_id,[206,207,208,209])){
                    $path = "c:/work/auto_reporter/reporter/jianqi-cmd/huaxi/report.py";
                }else if($report_type_id == 10023 && in_array($report_template_id,[270,271,272,273,274,275,276,277,278,279])){
                    $path = "c:/work/auto_reporter/reporter/血液肿瘤/V3/Auto_Report_Blood_cancer.py";
                }else if($report_type_id == 10029 && in_array($report_template_id,[59])){
                    $path = "c:/work/auto_reporter/reporter/jianqi-cmd/shanxirenmin/report.py";
                }else if($report_type_id == 10030 && in_array($report_template_id,[332,333,334,335,336,337,338,339,340,341,342,343,344,345,346,347,348,349,350,351,352,353])){
                    $path = "c:/work/auto_reporter/reporter/bin/report.py";
                }
                // var_dump($path);die;
//                使用队列异步处理调取脚本
                  //var_dump($json);exit;
//                $str = @exec("python $path $json 2>&1", $arr1, $ret);
//                $result = json_decode(end($arr1),true);
                Yii::$app->queue->push(new Job(["path"=>$path,"json" => $json,'unique'=>$unique,'uname'=>$uname,'outputPath'=>$output_path]));
                return $this->api_result(200,'正在生成中');
            }else{
                return $this->api_result(201,'缺少参数');
            }
        }
    }


    /**
     * @method   整合数据请求 python
     * @author : gengshaopeng
     * @Date   : 2019-12-03
     */
    static function requestPy($unique,$uname,$report_type_id,$report_template_id,$file_name){
        $reportYangben = ReportCreate::find()->where(['unique'=>$unique,'type'=>1])->asArray()->one();
        $reportZhushi = ReportCreate::find()->where(['unique'=>$unique,'type'=>2])->asArray()->one();
        $reportYidai = ReportCreate::find()->select('relation,direction,site,relation_note,variation_source,heterozygosity,url')->where(['unique'=>$unique,'type'=>3])->asArray()->all();
        $reportLogo = ReportCreate::find()->where(['unique'=>$unique,'type'=>4])->asArray()->one();
        $reportParameters = ReportParameters::find()->where(['unique'=>$unique])->asArray()->one();
        $arrdata = array();
        $sample_path = isset($reportYangben['url']) ? $reportYangben['url'] : '';
        $input_path = isset($reportZhushi['url']) ? $reportZhushi['url'] : '';
        $file_name = $file_name;
        $output_dir = 'C:/wnmp/www/auto-report/api/web/output_path/'.$uname.'/'.date('Ymd');     //输出路径
        if (!is_dir($output_dir)) {
            mkdir($output_dir, 0755, true);
        }
        $output_path = $output_dir.'/'.$file_name;
        $output_download_path = str_replace('C:/wnmp/www/auto-report/api/web',Yii::$app->params['serverPathurl'],$output_path); //下载文件路径
        // var_dump($output_download_path);
        $arrdata['input_path'] = !empty($input_path)?$input_path:'';
        $arrdata['output_download_path'] = !empty($output_download_path)?$output_download_path:'';    //ip加端口号的路径用于下载doc
        $arrdata['sample_path'] = !empty($sample_path)?$sample_path :'';
        $arrdata['output_path'] = !empty($output_path)?$output_path:'';
        $arrdata['mode'] = $report_template_id;
        $arrdata['logo'] = !empty($reportLogo['url']) ?$reportLogo['url'] :'';
        $arrdata['yidai'] = !empty($reportYidai) ?$reportYidai :[];
        $arrdata['organization'] = !empty($reportLogo['company_name']) ?$reportLogo['company_name'] :'';
        $json = json_encode($arrdata,JSON_UNESCAPED_SLASHES);              //组装的数据
		return [
			'json'=>$json,
			'output_path'=>$output_path
		];
        //return $json;
    }

    /**
     * @method   测序管理系统生成报告
     * @author : gengshaopeng
     * @Date   : 2019-11-28
     */
     public function actionSequencereport(){
         set_time_limit(0);
//         ini_set('default_socket_timeout', -1);
         $fileArr = [];                                                 //要下载的文件数据
         $postdata =  Yii::$app->request->post();                      //数据
         $str = var_export($postdata,TRUE);
		 // file_put_contents('C:/logs/'.time().'cexuData.php',$str);
		 $transaction = Yii::$app->db->beginTransaction();
		 try{
			 foreach ($postdata as $dastak=>$datav) {
				 $projectId = isset($datav['taskId']) ? $datav['taskId'] : '';
				 $inputserverPathurl = isset($datav['inputPath']) ? $datav['inputPath'] : '';       //注释结果文件url
				 $mode = isset($datav['mode']) ? $datav['mode'] : '';                        //模板文件id
				 $sampleInfo = isset($datav['sampleInfo']) ? $datav['sampleInfo'] : '';         //sampleInfo
                 $sampleInfo['inspectorName'] = $sampleInfo['Inspector'];
                // $str1 = var_export($sampleInfo,TRUE);
                //  file_put_contents('C:/logs/'.time().'cexuData1.php',$str1);

                 $sampleInfo['Send_Sample_Time'] = date('Y-m-d',strtotime($sampleInfo['Send_Sample_Time']));
                 
				 $signInspector = !empty($sampleInfo['Inspector']) ? $sampleInfo['Inspector'] : 'http://139.9.113.124:8090/upload/徐忠海_检验师.png';        //签名图片1 url
				 $signReviewer = !empty($sampleInfo['Reviewer']) ? $sampleInfo['Reviewer'] : 'http://139.9.113.124:8090/upload/刘海迪_PCR.png';           //签名图片2 url
				 if(in_array($mode,[61,62,63,64,65,66,67,68])){
					 $signInspector = 'http://139.9.113.124:8090/upload/刘海迪_PCR.png';
					 $signReviewer = 'http://139.9.113.124:8090/upload/杨杉_PCR+检验师.png';
				 }elseif(in_array($mode,[69,70,71,72,73,74,75,76,77,78,79,80,81,82,83,84,85,86,87,88,89,90,91,92,93,94,95,96,97])){
					 $signInspector = 'http://139.9.113.124:8090/upload/金玉慧_PCR.png';
					 $signReviewer = 'http://139.9.113.124:8090/upload/吴永莉_PCR+检验师.png';
				 }elseif(in_array($mode,[98,99,100,101,102,103,104,105,106,107,108,109,110,111,112,113,114,115,116,117,118,119,120,121,122,123,124,125,126,127])){
					 $signInspector = 'http://139.9.113.124:8090/upload/刘海迪_PCR.png';
					 $signReviewer = 'http://139.9.113.124:8090/upload/吴永莉_PCR+检验师.png';
				 }elseif(in_array($mode,[128,129,130,131,132,133,134,135,136,137,138,139,140,141,142])){
					 $signInspector = 'http://139.9.113.124:8090/upload/kongbai.png';
					 $signReviewer = 'http://139.9.113.124:8090/upload/kongbai.png';
				 }elseif(in_array($mode,[214])){
                     $signInspector = 'http://139.9.113.124:8090/upload/杨杉_PCR+检验师.png';
                     $signReviewer = 'http://139.9.113.124:8090/upload/侯然_检验师.png';
                 }elseif(in_array($mode,[215])){
                     $signInspector = 'http://139.9.113.124:8090/upload/杨杉_PCR+检验师.png';
                     $signReviewer = 'http://139.9.113.124:8090/upload/侯然_检验师.png';
                 }elseif(in_array($mode,[176,177,178,179,180,181,182,183,184,185,186,187,188,189,216,217,218,219,220,221,222,223,224,225,226,227,228,229,230,231,232,233,234,235,236,237,238,239,240,241,242,243,244,245,246,247,248,249,250,251,252,253,262,316,317,318,319,320,321,51,322])){
                     if($sampleInfo['Inspector']=='侯然'){
                         $signInspector = 'http://139.9.113.124:8090/upload/侯然.png';
                     }elseif($sampleInfo['Inspector']=='梁李君'){
                         $signInspector = 'http://139.9.113.124:8090/upload/梁李君.png';
                     }elseif($sampleInfo['Inspector']=='梁子萍'){
                         $signInspector = 'http://139.9.113.124:8090/upload/梁子萍.png';
                     }elseif($sampleInfo['Inspector']=='刘超'){
                         $signInspector = 'http://139.9.113.124:8090/upload/刘超.png';
                     }elseif($sampleInfo['Inspector']=='沈玉林'){
                         $signInspector = 'http://139.9.113.124:8090/upload/沈玉林.png';
                     }elseif($sampleInfo['Inspector']=='薛丽文'){
                         $signInspector = 'http://139.9.113.124:8090/upload/薛丽文.png';
                     }elseif($sampleInfo['Inspector']=='岳新峰'){
                         $signInspector = 'http://139.9.113.124:8090/upload/岳新峰.png';
                     }elseif($sampleInfo['Inspector']=='赵日霞'){
                         $signInspector = 'http://139.9.113.124:8090/upload/赵日霞.png';
                     }elseif($sampleInfo['Inspector']=='杨杉'){
                         $signInspector = 'http://139.9.113.124:8090/upload/杨杉_PCR+检验师.png';
                     }else{
                        $signInspector = 'http://139.9.113.124:8090/upload/薛丽文.png';
                     }
                 }elseif (in_array($mode,[254,255,256,257,258,259,260,261])) {
                     $signInspector = '';
                     $signReviewer = '';
                 }elseif (in_array($mode,[263,264,265,266,267,268,269,55])) {
                     $signInspector = 'http://139.9.113.124:8090/upload/宁姝威.jpg';
                     $signReviewer = 'http://139.9.113.124:8090/upload/王柯柯.png';
                 }
                 elseif (in_array($mode,[281,282,283,284,285,286,287,288,289,290,291,292,293,294,295,296,297,298,299,300,301,302,303,304,305,306,307,308,309,310,311,312,313,314,315
                 ])) {
                     $signInspector = 'http://139.9.113.124:8090/upload/刘超.png';
                     $signReviewer = '';
                 }else{
                     $signInspector = 'http://139.9.113.124:8090/upload/徐忠海_检验师.png';
                     $signReviewer = 'http://139.9.113.124:8090/upload/刘海迪_PCR.png';
                 }
    
				 $rearRange = isset($datav['Rearrange']) ? $datav['Rearrange'] : '';                      //重排图片属性
				 $username = isset($datav['userName']) ? $datav['userName'] : '';

				 $unique = isset($datav['unique']) ? $datav['unique'] : time();
				 $keywords = isset($datav['keyword']) ? $datav['keyword'] : '';                          //注释结果文件名
				 $isQuence = isset($datav['isQuence']) ? $datav['isQuence'] : '1';                      //是否是测序管理系统请求
				 $callbackUrl = isset($datav['callbackUrl']) ? $datav['callbackUrl'] : ''; 
				 if($keywords){
					 $arr = explode(',',$keywords);
					 $keywords = isset($arr[0]) ? $arr[0] : '';
				 }
				 if (!$unique || !$mode) {
					 return $this->api_result('202', '缺少参数');
				 }
				 //入库report
				 $reportMode = new Report();
				 $reportMode->sample_name = $sampleInfo['Sample_Id'];
				 $reportMode->template_id = $mode;
				 $reportMode->user_name = isset($sampleInfo['Name']) ? $sampleInfo['Name'] : '';
				 $reportMode->sex = isset($sampleInfo['Sex']) ? $sampleInfo['Sex'] :'';
				 $reportMode->age = isset($sampleInfo['Age']) ? $sampleInfo['Age'] :'';
				 $reportMode->unique = $unique;
				 $reportMode->status = 3;
				 if(!$reportMode->save()){
					 var_dump($reportMode->getErrors());
					 return $this->api_result(201,'database error');
					 var_dump($reportMode->getErrors());
				 }

				 //重排
				 if (!empty($rearRange)) {
					 foreach($rearRange as $reark=>$rearv){
						 $arr = explode('_',$reark);
						 if(!empty($rearv)){
							 $save_reviewerpath = $this->createFilename($rearv);                                   //管文件地址 type 6
							 $file = $this->grabFile($rearv, $save_reviewerpath);
							 if ($file) {
								 $fileArr[$unique][] = [
									 'url' => $save_reviewerpath,
									 'type' => 6,
									 'flag' => $arr[0],
									 'pipe_type' => $arr[1]
								 ];
							 }
						 }else{
							 $save_reviewerpath = "";
							 $fileArr[$unique][] = [
								 'url' => $save_reviewerpath,
								 'type' => 6,
								 'flag' => $arr[0],
								 'pipe_type' => $arr[1]
							 ];
						 }
					 }
				 }
				 if (!empty($inputserverPathurl)) {
					 if ($mode == "140") {
						 $save_inputpath = $this->createFilename($inputserverPathurl['ABCB1']);                                   //注释结果文件新的地址 type 2
						 $file = $this->grabFile($inputserverPathurl['ABCB1'], $save_inputpath);
						 if ($file) {
							 $fileArr[$unique][] = [
								 'url' => $save_inputpath,
								 'type' => 2,
								 'flag' => 'ABCB1',
								 'pipe_type' => ''
							 ];
						 }
						 $save_inputpath = $this->createFilename($inputserverPathurl['PAI-1']);                                   //注释结果文件新的地址 type 2
						 $file = $this->grabFile($inputserverPathurl['PAI-1'], $save_inputpath);
						 if ($file) {
							 $fileArr[$unique][] = [
								 'url' => $save_inputpath,
								 'type' => 2,
								 'flag' => 'PAI-1',
								 'pipe_type' => ''
							 ];
						 }
					 } else {
						 $save_inputpath = $this->createFilename($inputserverPathurl);                       //注释结果文件新的地址 type 2
						 $file = $this->grabFile($inputserverPathurl,$save_inputpath);
						 if ($file) {
							 $fileArr[$unique][] = [
								 'url' => $save_inputpath,
								 'type' => 2,
								 'flag' => 'inputPath',
								 'pipe_type' => ''
							 ];
						 }
					 }
				 }
				 if (!empty($signInspector)) {
					 
					 $save_inspectorpath = $this->createFilename($signInspector);                              //签名文件1新的地址 type 5
					 $file = $this->grabFile($signInspector, $save_inspectorpath);
					 if ($file) {
						 $fileArr[$unique][] = [
							 'url' => $save_inspectorpath,
							 'type' => 5,
							 'flag' => 'Inspector',
							 'pipe_type' => ''
						 ];
					 }
				 }
								
				 if (!empty($signReviewer)) {
					 $save_reviewerpath = $this->createFilename($signReviewer);                                   //签名文件2新的地址 type 5
					 $file = $this->grabFile($signReviewer, $save_reviewerpath);
					 if ($file) {
						 $fileArr[$unique][] = [
							 'url' => $save_reviewerpath,
							 'type' => 5,
							 'flag' => 'Reviewer',
							 'pipe_type' => ''
						 ];
					 }
				 }
				 //拿到所有的url保存到本地
				 if (isset($fileArr[$unique]) && !empty($fileArr[$unique])) {
					 foreach ($fileArr[$unique] as $v) {
						 $reportModel = new ReportCreate();
						 $reportModel->url = $v['url'];
						 $reportModel->unique = $unique;
						 $reportModel->type = $v['type'];
						 $reportModel->flag = $v['flag'];
						 $reportModel->pipe_type = $v['pipe_type'];
						 $result = $reportModel->save();
						 if (!$result) {
							 return $this->api_result(405, 'database error');
							 var_dump($reportModel->getErrors());
						 }
					 }
				 }
				 $modeType = ReportClassify::find()->select('name')->where(['id'=>$mode])->asArray()->one();
				 $sampleTypeName = isset($modeType['name']) ? $modeType['name'] :'';
				 $file_name = $sampleInfo['Sample_Id'].$sampleTypeName.time().'.docx';
                 if($mode == "60"){
                    $file_name = $sampleInfo['Name'].$sampleInfo['Sample_Id'].$sampleTypeName.time().'.docx';
                 }
                 // file_put_contents('C:/logs/'.time().'out_put_path.php',$file_name);
                 $file_name = str_replace('/','_',$file_name);
				 $json = self::requestZhan($unique, $username, $mode, $sampleInfo, $rearRange,$keywords,$file_name);
				 $output_path = $json['output_path'];
                 
				 $json = $json['json'];
				 $json = str_replace("\\/", "/", $json);
				 $json = str_replace("\\", "/", $json);
				 $json = str_replace("\"", "\\\"", $json);
                 $request_json = var_export($json,TRUE);
                 file_put_contents('C:/logs/'.time().'request_json.php',$request_json);
				 //print_r($json);
				 if($mode == "140"){
					 $path = 'C:/work/auto_reporter/reporter/guohaichi-php/drug2/drug_tangpizhijisu.py';
				 }else if(in_array($mode,[61,62,63,64,65,66,67,68])){
					 $path = 'C:/work/auto_reporter/reporter/ruize-cmd/rearrange/rearrange_load_result.py';
				 }else if($mode == "128"){
					 $path = 'C:/work/auto_reporter/reporter/guohaichi-php/drug2/drug_lvbigelei.py';
				 }else if($mode == "129"){
					 $path = 'C:/work/auto_reporter/reporter/guohaichi-php/drug2/drug_huafalin.py';
				 }else if($mode == "130"){
					 $path = 'C:/work/auto_reporter/reporter/guohaichi-php/drug2/drug_tating.py';
				 }else if($mode == "131"){
					 $path = 'C:/work/auto_reporter/reporter/guohaichi-php/drug2/drug_yesuan.py';
				 }else if($mode == "132"){
					 $path = 'C:/work/auto_reporter/reporter/guohaichi-php/drug2/drug_xiaosuanganyou.py';
				 }else if($mode == "133"){
					 $path = 'C:/work/auto_reporter/reporter/guohaichi-php/drug2/drug_gao_xue_ya.py';
				 }else if($mode == "134"){
					 $path = 'C:/work/auto_reporter/reporter/guohaichi-php/drug2/drug_asipilin.py';
				 }else if($mode == "135"){
					 $path = 'C:/work/auto_reporter/reporter/guohaichi-php/drug2/drug_huangniaolei.py';
				 }else if($mode == "136"){
					 $path = 'C:/work/auto_reporter/reporter/guohaichi-php/drug2/drug_takemosi.py';
				 }else if($mode == "137"){
					 $path = 'C:/work/auto_reporter/reporter/guohaichi-php/drug2/drug_liuzuopiaoling.py';
				 }else if($mode == "138"){
					 $path = 'C:/work/auto_reporter/reporter/guohaichi-php/drug2/drug_biepiaolingchun.py';
				 }else if($mode == "139"){
					 $path = 'C:/work/auto_reporter/reporter/guohaichi-php/drug2/drug_meifensuanzhi.py';
				 }else if($mode == "141"){
					 $path = 'C:/work/auto_reporter/reporter/guohaichi-php/drug2/drug_fulikangzuo.py';
				 }else if($mode == "142"){
					 $path = 'C:/work/auto_reporter/reporter/guohaichi-php/drug2/drug_erjiashuanggua.py';
				 }else if(in_array($mode,[98,99,100,101,102,103,104,105,106,107,108,109,110,111,112,113,114,115,116,117,118,119,120,121,122,123,124,125,126,127])){
					 $path = "C:/work/auto_reporter/reporter/yn-cmd/geneticDiseDetection/Auto_repoter_v1.0.py";
				 }else if(in_array($mode,[69,70,71,72,73,74,75,76,77,78,79,80,81,82,83,84,85,86,87,88,89,90,91,92,93,94,95,96,97])){
					 $path = "C:/work/auto_reporter/reporter/ruize-cmd/zhanjiang/mutation/mutation.py";
				 }else if(in_array($mode,[176,177,178,179,180,247])){
                     $path = 'C:/work/auto_reporter/reporter/jianqi-cmd/zhipu_zhanjiang/report.py';
                 }else if(in_array($mode,[181,182,183,184,185,186,187,188,189])){
                     $path = 'C:/work/auto_reporter/reporter/jianqi-cmd/liushi_zhanjiang/report.py';
                 }else if(in_array($mode,[214])){
                     $path = 'C:/work/auto_reporter/reporter/ruize-cmd/VMP/VMP_report.py';
                 }else if(in_array($mode,[215])){
                     $path = 'C:/work/auto_reporter/reporter/ruize-cmd/HPV/HPV_report.py';
                 }else if(in_array($mode,[216,217,218,219])){
                     $path = 'C:/work/auto_reporter/reporter/ruize-cmd/zhanjiang/AE/report.py';
                 }else if(in_array($mode,[220])){
                     $path = 'C:/work/auto_reporter/reporter/ruize-cmd/zhanjiang/general/report.py';
                 }else if(in_array($mode,[221,222,223,224,225,226,227,228])){
                     $path = 'C:/work/auto_reporter/reporter/ruize-cmd/zhanjiang/IC/report.py';
                 }else if(in_array($mode,[229,230,231,232])){
                     $path = 'C:/work/auto_reporter/reporter/ruize-cmd/zhanjiang/linjian/report.py';
                 }else if(in_array($mode,[233,234,235,236,237,238,239,240,241,242,243,244,245,246])){
                     $path = 'C:/work/auto_reporter/reporter/ruize-cmd/zhanjiang/PM/report.py';
                 }else if(in_array($mode,[249])){
                     $path = 'C:/work/auto_reporter/reporter/ruize-cmd/zhanjiang/drug_clopidogrel/report.py';
                 }else if(in_array($mode,[250])){
                     $path = 'C:/work/auto_reporter/reporter/ruize-cmd/zhanjiang/Statins/report.py';
                 }else if(in_array($mode,[251])){
                     $path = 'C:/work/auto_reporter/reporter/xinxinliu/huafalin/report.py';
                 }else if(in_array($mode,[252])){
                     $path = 'C:/work/auto_reporter/reporter/yn-cmd/ZhanJiangYeSuanReport/bin/ZhanJiangYeSuanReporter.py';
                 }else if(in_array($mode,[253])){
                     $path = 'C:/work/auto_reporter/reporter/yn-cmd/ZhanJiangXSGYReport/bin/ZhanJiangXSGYReporter.py';
                 }else if(in_array($mode,[259])){
                     $path = 'C:/work/auto_reporter/reporter/ruize-cmd/huaxi/drug_clopidogrel/report.py';
                 }else if(in_array($mode,[260])){
                     $path = 'C:/work/auto_reporter/reporter/ruize-cmd/huaxi/Statins/report.py';
                 }else if(in_array($mode,[261])){
                     $path = 'C:/work/auto_reporter/reporter/xinxinliu/HX_show/huafalin/report.py';
                 }else if(in_array($mode,[254,255,256,257])){
                     $path = 'C:/work/auto_reporter/reporter/jianqi-cmd/huachuang/report.py';
                 }else if(in_array($mode,[258])){
                     $path = 'C:/work/auto_reporter/reporter/ruize-cmd/huaxi/xinxueguanbing/guanxinbing_report.py';
                 }else if(in_array($mode,[262])){
                     $path = 'C:/work/auto_reporter/reporter/wangyuexing/zhanjiang_gaoxueya/report.py';
                 }else if(in_array($mode,[263])){
                     $path = 'C:/work/auto_reporter/reporter/xinxinliu/zheng7/EJSG/report.py';
                 }else if(in_array($mode,[264])){
                     $path = 'C:/work/auto_reporter/reporter/xinxinliu/zheng7/HFL/report.py';
                 }else if(in_array($mode,[265])){
                     $path = 'C:/work/auto_reporter/reporter/xinxinliu/zheng7/LBGL/report.py';
                 }else if(in_array($mode,[266])){
                     $path = 'C:/work/auto_reporter/reporter/xinxinliu/zheng7/TKMS/report.py';
                 }else if(in_array($mode,[267])){
                     $path = 'C:/work/auto_reporter/reporter/xinxinliu/zheng7/XSGY/report.py';
                 }else if(in_array($mode,[268])){
                     $path = 'C:/work/auto_reporter/reporter/xinxinliu/zheng7/YS/report.py';
                 }else if(in_array($mode,[269])){
                     $path = 'C:/work/auto_reporter/reporter/xinxinliu/zheng7/TT/report.py';
                 }else if(in_array($mode,[281,282,283,284,285,286,287,288,289,290,291])){
                     $path = 'C:/work/auto_reporter/reporter/yangpengcheng/Auto_Report_Blood_cancer.py';
                 }else if(in_array($mode,[292,293,294,295,296,297,298,299,300,301,302,303,304,305,306,307,308,309,310,311,312])){
                     $path = 'C:/work/auto_reporter/reporter/xinxinliu/zhanjiang/report.py';
                 }else if(in_array($mode,[313,314,315])){
                     $path = 'C:/work/auto_reporter/reporter/wangyuexing/zhanjiang_PG/report.py';
                 }else if(in_array($mode,[51])){
                     $path = 'C:/work/auto_reporter/reporter/ruize-cmd/zhanjiang/IC/report.py';
                 }else if(in_array($mode,[316,317,318,319])){
                     $path = 'C:/work/auto_reporter/reporter/jianqi-cmd/HereditaryTumor_zhanjiang/report.py';
                 }else if(in_array($mode,[320])){
                     $path = 'C:/work/auto_reporter/reporter/xinxinliu/zheng7/GXY/report.py';
                 }else if(in_array($mode,[321])){
                     $path = 'C:/work/auto_reporter/reporter/xinxinliu/zheng7/ASPL/report.py';
                 }else if(in_array($mode,[322])){
                     $path = 'C:/work/auto_reporter/reporter/ruize-cmd/yijiansuo/xinguan/report.py';
                 }else if(in_array($mode,[52])){
                     $path = 'C:/work/auto_reporter/reporter/jianqi-cmd/fuwai/ReportV1.3.4/report.py';
                 }else if(in_array($mode,[53])){
                     $path = 'C:/work/auto_reporter/reporter/jianqi-cmd/beidarenmin/report.py';
                 }else if(in_array($mode,[54])){
                     $path = 'C:/work/auto_reporter/reporter/jianqi-cmd/fuwai/ReportV1.3.3/report.py';
                 }else if(in_array($mode,[323])){
                     $path = 'C:/work/auto_reporter/reporter/ruize-cmd/changcheng/xinguan/report.py';
                 }else if(in_array($mode,[55])){
                     $path = 'C:/work/auto_reporter/reporter/jianqi-cmd/liushi_zhanjiang/report.py';
                 }else if(in_array($mode,[56])){
                     $path = 'C:/work/auto_reporter/reporter/jianqi-cmd/yimai_CAD/report.py';
                 }else if(in_array($mode,[57])){
                     $path = 'C:/work/auto_reporter/reporter/jianqi-cmd/tianzhong/report_long.py';
                 }else if(in_array($mode,[58])){
                     $path = 'C:/work/auto_reporter/reporter/jianqi-cmd/tianzhong/report_short.py';
                 }else if(in_array($mode,[60])){
                     $path = 'C:/work/auto_reporter/reporter/jianqi-cmd/zhanjiang/xinguan/report.py';
                 }else if(in_array($mode,[324,325,326,327,328])){
                     $path = 'C:/work/auto_reporter/reporter/jianqi-cmd/zhanjiang/fish/report.py';
                 }else if(in_array($mode,[329,330,331,355,356])){
                     $path = 'C:/work/auto_reporter/reporter/bin/report.py';
                 }else if(in_array($mode,[392])){
                     $path = 'C:/work/auto_reporter/reporter/jianqi-cmd/10000people_FH/zzqy_wes_report.py';
                 }else if(in_array($mode,[411])){
                     $path = 'C:/work/auto_reporter/reporter/jianqi-cmd/zhipu_zhanjiang/report.py';
                 }else if(in_array($mode,[418])){
                     $path = 'C:/work/auto_reporter/reporter/ruize-cmd/zhanjiang/linjian/report.py';
                 }else if(in_array($mode,[434])){
                    $path = 'C:/work/auto_reporter/reporter/jianqi-cmd/zhanjiang/cultivate-cell-chrom-analysis/report.py';
                 }else if(in_array($mode,[499])){
                    $path = 'C:/work/auto_reporter/reporter/bin/report.py';
                 }
                 // file_put_contents('C:/logs/'.time().'mode返回原始数据.php',[$mode]);
				 $this->actionJob($path, $json, $unique, $username,$projectId,$output_path,$isQuence,$file_name,$callbackUrl,$mode);
			 }
			 $transaction->commit();
         }catch (\Exception $e) {
			 $transaction->rollBack();
			 return $this->api_result('201','database error');
		 }
		 return $this->api_result('200','生成中');
     }
	 
	 static function requestZhan($unique,$uname,$mode,$sampleInfo,$rearRange,$keywords,$file_name){
        $reportZhushi = ReportCreate::find()->select('id,url,flag')->where(['unique'=>$unique,'type'=>2])->asArray()->all();
        $inspector = ReportCreate::find()->select('id,url')->where(['unique'=>$unique,'type'=>5,'flag'=>'Inspector'])->asArray()->one();
        $reviewer = ReportCreate::find()->select('id,url')->where(['unique'=>$unique,'type'=>5,'flag'=>'Reviewer'])->asArray()->one();
        $sampleInfo['Inspector'] = isset($inspector['url']) ? $inspector['url']:'';
        $sampleInfo['Reviewer'] = isset($reviewer['url']) ? $reviewer['url'] :'';
        if($mode == 140){
            $input_path = array();
            foreach ($reportZhushi as $v) {
                if($v['flag'] == 'ABCB1'){
                    $input_path['ABCB1'] = $v['url'];
                }else{
                    $input_path['PAI-1'] = $v['url'];
                }
            }
        }else{
            $url = array_column($reportZhushi,'url');
            $input_path = isset($url[0]) ?$url[0] :'';
        }
        $file_name = $file_name;
		
        $output_dir = Yii::$app->params['phpPath'].'/output_path/'.$uname.'/'.date('Ymd');
        if (!is_dir($output_dir)) {
            mkdir($output_dir, 0755, true);
        }
        $output_path = $output_dir.'/'.$file_name;
        $output_download_path = str_replace(Yii::$app->params['phpPath'],Yii::$app->params['serverPathurl'],$output_path);
        $images = ReportCreate::find()->select('id,url,flag,pipe_type')->where(['type'=>6,'unique'=>$unique])->asArray()->all();
        $pipeType = array_unique(array_column($images,'pipe_type'));
        $pipeArr = [];
        if(!empty($pipeType)){
            foreach ($pipeType as $v){
                $pipeArr[] = $v;
            }
        }
        $imageArr = [];
        foreach($pipeArr as $k=>$v){
            $imageArr[$k]['Gene_Rearrange'] = $v;
            $imageurl = ReportCreate::find()->select('id,url,flag,pipe_type')->where(['type'=>6,'unique'=>$unique,'pipe_type'=>$v])->asArray()->all();
            foreach ($imageurl as $kk=>$vv){
                $imageArr[$k]['Pic_Type_Rearrange'][$kk] = !empty($vv['url']) ? $vv['url'] : "";
            }
        }
        $arrdata = array();
        $arrdata['Input_Path'] = !empty($input_path) ? $input_path:'';
        $arrdata['Input_Path'] = str_replace(' ','_',$arrdata['Input_Path']);
        $arrdata['Output_Download_Path'] = !empty($output_download_path)?$output_download_path:'';
        $arrdata['Output_Download_Path'] = str_replace(' ','_',$arrdata['Output_Download_Path']);
        $arrdata['Output_Path'] = !empty($output_path)?$output_path:'';
        $arrdata['Output_Path'] = str_replace(' ','_',$arrdata['Output_Path']);
        $arrdata['Mode'] = $mode;
        $arrdata['Sample_Info'] = $sampleInfo;
        $arrdata['Rearrange'] = $imageArr;
        $arrdata['Keyword'] = $keywords;
        $json = json_encode($arrdata,JSON_UNESCAPED_SLASHES);              //组装的数据
		return [
			'json'=>$json,
			'output_path'=>$arrdata['Output_Path']
		];
    }
	
	public function actionJob($path,$json,$unique,$uname,$projectId,$output_path,$isQuence,$file_name,$callbackUrl,$mode){
        Yii::$app->queue->push(new Job(["mode"=>$mode,"path"=>$path,"json" => $json,'unique'=>$unique,'uname'=>$uname,'projectId'=>$projectId,'outputPath'=>$output_path,'isQuence'=>$isQuence,'fileName'=>$file_name,'callbackUrl'=>$callbackUrl]));
    }
	
	//保存远程文件到本地
    function grabFile($url,$save_path){
        $file = $this->curl_file_get_contents($url);
        file_put_contents($save_path,$file);
        return $file;
    }
    //根据url创建文件目录
    function createFilename($inputserverPathurl){
        // 文件重命名，这里自动生成一个不重复的名字，方便使用
        $file_fix = strrchr($inputserverPathurl, '.');
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
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $r = curl_exec($ch);
        if(curl_errno($ch)){  //如果存在错误，输出错误（超时是不会抛出异常的，需要用这个函数设置自己的处理方式）
            echo 'Curl error: ' . curl_error($ch);
        }
        // file_put_contents('error5555.php', 'Curl error: ' . $r);
        return $r;
    }
}
