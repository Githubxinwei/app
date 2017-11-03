<?php
namespace app\express\controller;
use think\Controller;

class Evaluate extends Controller{
	/**
	 * Json方式 物流评价投诉
	 */
	function getExpEvaluateByJson()
	{
		$requestData="{'MemberID': ''," +
					  "'EvaluateType':1," +
					  "'ExpressNode':1," +
					  "'OrderCode':''," +
					  "'LogisticCode':'12345678'," +
					  "'Target':" +
					  "{" +
					  "'ExpCode':'YTO','OutletCode':'0453','CourierCode':'200115887318'}," +
					  "'Score':" +
					  "{" +
					  "'ExpValue':5,'OutletValue':4.5,'CourierValue':4}," +
					  "'EvaluationTag':'速度快，服务好'," +
					  "'Content':''}";
		
		$datas = array(
			'EBusinessID' => '1310235',
			'RequestType' => '1011',
			'RequestData' => urlencode($requestData) ,
			'DataType' => '2',
		);
		$datas['DataSign'] = encrypt($requestData,'22c789e1-81ab-4a49-a9b6-5fa6ed8bd77d');
		$result=sendPost(ReqURL, $datas);	
		
		//根据公司业务处理返回的信息......
		
		return $result;
	}

	/**
	 * Json方式 物流平均分获取
	 */
	function getExpAverageByJson()
	{
		$requestData='{"MemberID":"123456","LogisticsType":1,"ExpCode":"","OutletCode":""}';
		
		$datas = array(
			'EBusinessID' => '1310235',
			'RequestType' => '1012',
			'RequestData' => urlencode($requestData) ,
			'DataType' => '2',
		);
		$datas['DataSign'] = encrypt($requestData, '22c789e1-81ab-4a49-a9b6-5fa6ed8bd77d');
		$result=sendPost(ReqURL, $datas);	
		
		//根据公司业务处理返回的信息......
		
		return $result;
	}
}

 
