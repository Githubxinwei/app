<?php
namespace app\express\controller;
use think\Controller;

class Orderdistinguish extends Controller{
	/**
	 * Json方式 单号识别
	 */
	function getOrderTracesByJson(){
		$requestData= "{'LogisticCode':'1000745320654'}";
		$datas = array(
			'EBusinessID' => '1310235',
			'RequestType' => '2002',
			'RequestData' => urlencode($requestData) ,
			'DataType' => '2',
		);
		$datas['DataSign'] = encrypt($requestData, '22c789e1-81ab-4a49-a9b6-5fa6ed8bd77d');
		$result=sendPost(ReqURL, $datas);	
		
		//根据公司业务处理返回的信息......
		
		return $result;
	}
}

