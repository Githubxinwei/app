<?php
namespace app\express\controller;
use think\Controller;

class Sms extends Controller{
	/**
	 * Json方式 短信发送
	 */
	function getSMSSendRecordByJson(){
		$requestData="{'Detail':[{'ReceiverName':'张三','ReceiverMobile':'15814460948'}],'MemberID':'123456','CallBack':''}";
		
		$datas = array(
			'EBusinessID' => '1310235',
			'RequestType' => '8101',
			'RequestData' => urlencode($requestData) ,
			'DataType' => '2',
		);
		$datas['DataSign'] = encrypt($requestData, '22c789e1-81ab-4a49-a9b6-5fa6ed8bd77d');
		$result=sendPost(ReqURL, $datas);	
		
		//根据公司业务处理返回的信息......
		
		return $result;
	}
	 
	/**
	 * Json方式 短信模板
	 */
	function getSMSTemplateByJson(){
		$requestData= "{'Detail':[{'TemplateType':'6','MessageTemplate':'短信模板','IsOpen':0}],'MemberID':'123456'}";
		$datas = array(
			'EBusinessID' => '1310235',
			'RequestType' => '8102',
			'RequestData' => urlencode($requestData) ,
			'DataType' => '2',
		);
		$datas['DataSign'] = encrypt($requestData, '22c789e1-81ab-4a49-a9b6-5fa6ed8bd77d');
		$result=sendPost(ReqURL, $datas);	
		
		//根据公司业务处理返回的信息......
		
		return $result;
	}

	/**
	 * Json方式 短信黑名单
	 */
	function getSMSBlackByJson(){
		$requestData= "{'Detail':[{'Mobile':'15814460948','IsBlack':0}],'MemberID':'123456'}";
		$datas = array(
			'EBusinessID' => '1310235',
			'RequestType' => '8103',
			'RequestData' => urlencode($requestData) ,
			'DataType' => '2',
		);
		$datas['DataSign'] = encrypt($requestData, '22c789e1-81ab-4a49-a9b6-5fa6ed8bd77d');
		$result=sendPost(ReqURL, $datas);	
		
		//根据公司业务处理返回的信息......
		
		return $result;
	}
} 
