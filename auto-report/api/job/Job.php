<?php
    namespace api\job;
    header("Content-type:text/html;charset=utf-8");
	use Yii;
	use yii\db\Exception;
	use yii\web\Controller;
    use common\models\Report;
    use yii\base\BaseObject;
    class Job extends BaseObject implements \yii\queue\JobInterface
    {
        public $path;
        public $json;
        public $unique;
		public $projectId;
        public $uname;
		public $outputPath;
		public $isQuence;
		public $fileName;
		public $callbackUrl;
		public $mode;
		

        public function execute($queue)
        {
            var_dump('start');
            var_dump("python $this->path $this->json 2>&1");
            $str = @exec("python $this->path $this->json 2>&1", $arr1, $ret); //调取生成word
			$path = "C:/wnmp/www/auto-report/api/web/log.txt";
            $fp = fopen($path,"a");//打开文件资源通道 不存在则自动创建
            // var_dump('-----------------');
            var_dump(end($arr1));
            file_put_contents('C:/logs/'.time().'doc返回原始数据.php',$arr1);
			$strr = json_encode($arr1);
            $data = $strr;
            fwrite($fp,date("Y-m-d H:i:s").var_export($data ,true)."\r\n");//写入文件
            fclose($fp);//关闭资源通道
            $a = end($arr1);
            // file_put_contents('C:/logs/'.time().'111111111111111doc返回原始数据.php',$a);
            $result = json_decode(end($arr1),true);
            var_dump('++++++++++++++doc');
            var_dump($result);//doc返回
            // file_put_contents('C:/logs/'.time().'doc返回处理后数据.php',end($arr1));
            // file_put_contents('C:/logs/'.time().'output_pdf_path.php',end($arr1), true);
            if($result['code'] == 0 && $result != NULL){
   
            	// file_put_contents('C:/logs/'.time().'返回钟工json数据.php',$result);
            	// var_dump('################report');
            	// var_dump($result['report']);
            	$mode = isset($result['mode']) ? $result['mode'] :'';    //mode
            	if(in_array($this->mode,[355,356])){
            		$reportJson = json_decode(str_replace('/','',json_encode($result['report'])));
            	}else{
            		$reportJson = isset($result['report']) ? $result['report'] :'';    //返回的报告数据参数
            	}
    //         	$results = print_r($reportJson, true); 
				// file_put_contents('C:/logs/'.time().'doc返回reportjson.php', print_r($reportJson, true));
            	// file_put_contents('C:/logs/'.time().'doc返回reportjson.php',json_encode($reportJson));
            	// file_put_contents('C:/logs/'.time().'doc返回mode.php',json_encode($this->mode));
            	
            	$name = isset($result['name']) ? $result['name'] :'';    //检测者name
				$outputPath = isset($this->outputPath)?$this->outputPath:'';
                $download_doc = str_replace("C:/wnmp/www/auto-report/api/web","http://121.36.2.49:8090",$outputPath);            //下载doc路径  ip加端口号
                $output_pdf_path = str_replace('docx','pdf',$result['output_path']);  //$result['output_path']是绝对路径
                
                //完成word转pdf
                $pdf = [
                    'input_path'=>$result['output_path'],   //要转换的doc文件绝对路径
                    'output_path'=>$output_pdf_path         //pdf绝对路径
                ];
                $a = $result['output_path'];
                $pdf_json = json_encode($pdf);
                $json = str_replace("\\/", "/", $pdf_json);
                $json = str_replace("\\", "/", $json);
                $pdf_json = str_replace("\"", "\\\"", $json);
                $pdf_path = 'C:\work\auto_reporter\reporter\yn-cmd\docx_2_pdf.py';
//                $str = @exec("python $pdf_path $pdf_json 2>&1", $arr2, $ret2);
                var_dump("python $pdf_path $a $output_pdf_path 2>&1");
                $str = @exec("python $pdf_path $a $output_pdf_path 2>&1", $arr2, $ret2); //调取转pdf
                $pdf_result = json_decode(end($arr2),true);
                var_dump('-----------------pdf');
                // var_dump("###".end($arr2)."@@@");  //pdf返回
                var_dump($pdf_result);  //pdf返回
                // file_put_contents('C:/logs/'.time().'pdfword.php',json_encode($a));
                // file_put_contents('C:/logs/'.time().'pdfpdf.php',json_encode($output_pdf_path));
                // file_put_contents('C:/logs/'.time().'pdf返回.php',json_encode($pdf_result));
                if($pdf_result['code'] == 0){
                    $preview_path = str_replace('docx','pdf',$download_doc);    //预览pdf路径  ip加端口号
                        //转pdf成功  修改数据库报告状态
                    $result = Report::updateAll(['status'=>0,'download_path'=>$download_doc,'preview_path'=>$preview_path],['unique'=>$this->unique]);
                    if($result){
						if($this->isQuence == "1"){
							//请求测序管理系统 接口 更新任务状态为生成报告
							$url = $this->callbackUrl;
							if(in_array($this->mode,[183,185,187,188,189,231,60,179,180,228,224,225,221,227,223,226,51,229,233,234,251,133,249,250,262,314,263,264,265,266,267,268,269,320,321,232,247,411,418])){
								$backFile = $preview_path;
							}else{
								$backFile = $download_doc;
							}
							$data = [
								'uname'=>$this->uname,
								'projectId'=>$this->projectId,
								'download_doc'=>$backFile,
								'status'=>2,
								'reportJson'=>$reportJson,
								'mode' => $mode,
								'name' => $name
							];
							// file_put_contents('C:/logs/'.time().'路由.php',$url );
							// file_put_contents('C:/logs/'.time().'向测序管理系统发送.php',json_encode($data));
							$response = $this->curlPost($url,$data);
							$returnData = json_decode($response,true);
							// file_put_contents('C:/logs/'.time().'测序管理系统返回.php',$response);
							var_dump($returnData);
							if($returnData['code']==0 && $returnData!=NULL){
								var_dump('update state success');
							}else{
								var_dump($returnData['msg']);
							}
						}
                        var_dump('success');
                    }else{
                        var_dump('error');
                    }
                    //var_dump('end');
                }else{
                	var_dump('create pdf failed11111');
					Report::updateAll(['status'=>4],['unique'=>$this->unique]);
                    var_dump('create pdf failed');
                }
            }else{
            	file_put_contents('C:/logs/'.time().'失败数据.php',$result);
                Report::updateAll(['status'=>4],['unique'=>$this->unique]);
				if($this->isQuence == "1"){
					$url = $this->callbackUrl;
					$data = [
						'uname'=>$this->uname,
						'projectId'=>$this->projectId,
						'download_doc'=>"",
						'status'=>3
					];
					var_dump($data);
					$response = $this->curlPost($url,$data);
					$returnData = json_decode($response,true);
					var_dump($returnData);
					if($returnData['code']==0){
						var_dump('create doc failed');
					}else{
						var_dump($returnData['msg']);
					}
				}
            }
        }
		
		public function curlPost($url,$data=array()){
			$url = str_replace(' ','+',$url);
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "$url");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch,CURLOPT_TIMEOUT,3);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
			$output = curl_exec($ch);
			$errorCode = curl_errno($ch);
			curl_close($ch);
			if(0 !== $errorCode) {
				return false;
			}
			return $output;
		}
		
    }