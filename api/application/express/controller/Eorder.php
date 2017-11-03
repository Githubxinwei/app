<?php
namespace app\express\controller;
use think\Controller;

//构造电子面单提交信息
/* $eorder = [];
$eorder["ShipperCode"] = "SF";
$eorder["OrderCode"] = "012657700387";
$eorder["PayType"] = 1;
$eorder["ExpType"] = 1;

$sender = [];
$sender["Name"] = "李先生";
$sender["Mobile"] = "18888888888";
$sender["ProvinceName"] = "李先生";
$sender["CityName"] = "深圳市";
$sender["ExpAreaName"] = "福田区";
$sender["Address"] = "赛格广场5401AB";

$receiver = [];
$receiver["Name"] = "李先生";
$receiver["Mobile"] = "18888888888";
$receiver["ProvinceName"] = "李先生";
$receiver["CityName"] = "深圳市";
$receiver["ExpAreaName"] = "福田区";
$receiver["Address"] = "赛格广场5401AB";

$commodityOne = [];
$commodityOne["GoodsName"] = "其他";
$commodity = [];
$commodity[] = $commodityOne;

$eorder["Sender"] = $sender;
$eorder["Receiver"] = $receiver;
$eorder["Commodity"] = $commodity;


//调用电子面单
$jsonParam = json_encode($eorder, JSON_UNESCAPED_UNICODE);

//$jsonParam = JSON($eorder);//兼容php5.2（含）以下

echo "电子面单接口提交内容：<br/>".$jsonParam;
$jsonResult = submitEOrder($jsonParam);
echo "<br/><br/>电子面单提交结果:<br/>".$jsonResult;

//解析电子面单返回结果
$result = json_decode($jsonResult, true);
echo "<br/><br/>返回码:".$result["ResultCode"];
if($result["ResultCode"] == "100") {
    echo "<br/>是否成功:".$result["Success"];
}
else {
    echo "<br/>电子面单下单失败";
} */
//-------------------------------------------------------------

class Eorder extends Controller{
    
    
	/**
	 * Json方式 调用电子面单接口
	 */
	function submitEOrder($requestData){
		$datas = array(
			'EBusinessID' => '1310235',
			'RequestType' => '1007',
			'RequestData' => urlencode($requestData) ,
			'DataType' => '2',
		);
		$datas['DataSign'] = encrypt($requestData, '22c789e1-81ab-4a49-a9b6-5fa6ed8bd77d');
		$result=sendPost(ReqURL, $datas);	
		
		//根据公司业务处理返回的信息......
		
		return $result;
	}

	/************************************************************** 
	 * 
	 *  使用特定function对数组中所有元素做处理 
	 *  @param  string  &$array     要处理的字符串 
	 *  @param  string  $function   要执行的函数 
	 *  @return boolean $apply_to_keys_also     是否也应用到key上 
	 *  @access public 
	 * 
	 *************************************************************/  
	function arrayRecursive(&$array, $function, $apply_to_keys_also = false)  
	{  
		static $recursive_counter = 0;  
		if (++$recursive_counter > 1000) {  
			die('possible deep recursion attack');  
		}  
		foreach ($array as $key => $value) {  
			if (is_array($value)) {  
				arrayRecursive($array[$key], $function, $apply_to_keys_also);  
			} else {  
				$array[$key] = $function($value);  
			}  
	   
			if ($apply_to_keys_also && is_string($key)) {  
				$new_key = $function($key);  
				if ($new_key != $key) {  
					$array[$new_key] = $array[$key];  
					unset($array[$key]);  
				}  
			}  
		}  
		$recursive_counter--;  
	}  


	/************************************************************** 
	 * 
	 *  将数组转换为JSON字符串（兼容中文） 
	 *  @param  array   $array      要转换的数组 
	 *  @return string      转换得到的json字符串 
	 *  @access public 
	 * 
	 *************************************************************/  
	function JSON($array) {  
		arrayRecursive($array, 'urlencode', true);  
		$json = json_encode($array);  
		return urldecode($json);  
	} 
	
}
 
