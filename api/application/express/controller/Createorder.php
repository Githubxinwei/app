<?php
namespace app\express\controller;
use think\Controller;

/* echo "在线下单";

//构造在线下单提交信息
$eorder = [];
$eorder["ShipperCode"] = "SF";
$eorder["OrderCode"] = "PM201605078947";
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


//调用在线下单
$jsonParam = json_encode($eorder, JSON_UNESCAPED_UNICODE);
echo "在线下单接口提交内容：<br/>".$jsonParam;
$jsonResult = submitOOrder($jsonParam);

//解析在线下单返回结果
$result = json_decode($jsonResult, true);
echo "<br/><br/>返回码:".$result["ResultCode"];
if($result["ResultCode"] == "100") {
    echo "<br/>是否成功:".$result["Success"];
}
else {
    echo "<br/>在线下单失败";
} */
//-------------------------------------------------------------

class Createorder extends Controller{
	

	/**
	 * Json方式 提交在线下单  预约取件
	 */
	function submitOOrder($requestData){
		$datas = array(
			'EBusinessID' => '1310235',
			'RequestType' => '1001',
			'RequestData' => urlencode($requestData) ,
			'DataType' => '2',
		);
		$datas['DataSign'] = encrypt($requestData, '22c789e1-81ab-4a49-a9b6-5fa6ed8bd77d');
		$result=sendPost(ReqURL, $datas);	
		
		//根据公司业务处理返回的信息......
		
		return $result;
	}
} 

