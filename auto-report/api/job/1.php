<?php
	$json ='{\"input_path\":\"C:/wnmp/www/auto-report/api/controllers/upload/20200811/1e509f5f7a67b55696f26b5c507a0fd3.xlsx\",\"output_download_path\":\"C:/wnmp/www/auto-report/api/www/output_path/gengshao/20200811/MF009/u897f/u4eac/u4e3b/u52a8/u8109/u79d1/u7814/u62a5/u544a1597130418.docx\",\"sample_path\":\"C:/wnmp/www/auto-report/api/controllers/upload/20200811/bc94b12fb76faf4454fa91eb8aa11a98.xlsx\",\"output_path\":\"C:/wnmp/www/auto-report/api/web/output_path/gengshao/20200811/MF009/u897f/u4eac/u4e3b/u52a8/u8109/u79d1/u7814/u62a5/u544a1597130418.docx\",\"mode\":\"190\",\"logo\":\"\",\"yidai\":[],\"organization\":\"\"}';
	$str = @exec("python 'C:/work/auto_reporter/reporter/yn-cmd/XiJingHospitalAutoReport.py' $json 2>&1", $arr1, $ret); //调取生成word
	var_dump($arr1);
?>