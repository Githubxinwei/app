<?php
namespace app\express\controller;
use think\Controller;

class Exprecommend extends Controller{
	/**
	 * Json方式 智选物流
	 */
	function getExpRecommendByJson(){
		$requestData= "{'MemberID':'123456','WarehouseID':'1','Detail':[{'OrderCode':'12345','IsCOD':0,'Sender':{'ProvinceName':'广东省','CityName':'广州','ExpAreaName':'龙岗区','Subdistrict':'布吉街道','Address':'518000'},'Receiver':{'ProvinceName':'广东','CityName':'梅州','ExpAreaName':'丰顺','Subdistrict':'布吉街道','Address':'518000'},'Goods':[{'ProductName':'包','Volume':'','Weight':'1'}]},{'OrderCode':'12346','IsCOD':0,'Sender':{'ProvinceName':'广东省','CityName':'广州','ExpAreaName':'龙岗区','Subdistrict':'布吉街道','Address':'518000'},'Receiver':{'ProvinceName':'湖南','CityName':'长沙','ExpAreaName':'龙岗区','Subdistrict':'布吉街道','Address':'518000'},'Goods':[{'ProductName':'包','Volume':'','Weight':'1'}]}]}";
		$datas = array(
			'EBusinessID' => '1310235',
			'RequestType' => '2006',
			'RequestData' => urlencode($requestData) ,
			'DataType' => '2',
		);
		$datas['DataSign'] = encrypt($requestData, '22c789e1-81ab-4a49-a9b6-5fa6ed8bd77d');
		$result=sendPost(ReqURL, $datas);	
		
		//根据公司业务处理返回的信息......
		
		return $result;
	}
	 
	/**
	 * Json方式 导入运费模板
	 */
	function importCostTemplateByJson(){
		$requestData= "{'MemberID':'123456','Detail':[{'ShipperCode':'YD','SendProvince':'广东','SendCity':'广州','SendExpArea':'天河','ReceiveProvince':'湖南','ReceiveCity':'长沙','ReceiveExpArea':'龙岗','FirstWeight':'1','FirstFee':'8','AdditionalWeight':'1','AdditionalFee':'10','WeightFormula':''},{'ShipperCode':'YD','SendProvince':'广东','SendCity':'广州','SendExpArea':'天河','ReceiveProvince':'湖南','ReceiveCity':'长沙','ReceiveExpArea':'雨花','FirstWeight':'1','FirstFee':'8','AdditionalWeight':'1','AdditionalFee':'10','WeightFormula':'{{w-0}-0.4}*{{{1000-w}-0.4}+1}*4.700+ {{w-1000}-0.6}*[(w-1000)/1000]*4.700）','ShippingType':'1','IntervalList':[{'StartWeight': 1.0,'EndWeight': 2.0, 'Fee': 3.0}]}]}";
		$datas = array(
			'EBusinessID' => '1310235',
			'RequestType' => '2004',
			'RequestData' => urlencode($requestData) ,
			'DataType' => '2',
		);
		$datas['DataSign'] = encrypt($requestData, '22c789e1-81ab-4a49-a9b6-5fa6ed8bd77d');
		$result=sendPost(ReqURL, $datas);	
		
		//根据公司业务处理返回的信息......
		
		return $result;
	}
}
