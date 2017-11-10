<?php
namespace app\express\controller;
use think\Controller;

class Subscribe extends Controller{
	/**
	 * Json方式  物流信息订阅    查询类:物流跟踪
	 */
	function orderTracesSubByJson(){
		$requestData="{'OrderCode': 'SF201608081055208281',".
				   "'ShipperCode':'SF',".
				   "'LogisticCode':'3100707578976',".
				   "'PayType':1,".
				   "'ExpType':1,".
				   "'IsNotice':0,".
				   "'Cost':1.0,".
				   "'OtherCost':1.0,".
				   "'Sender':".
				   "{".
				   "'Company':'LV','Name':'Taylor','Mobile':'15018442396','ProvinceName':'上海','CityName':'上海','ExpAreaName':'青浦区','Address':'明珠路73号'},".
				   "'Receiver':".
				   "{".
				   "'Company':'GCCUI','Name':'Yann','Mobile':'15018442396','ProvinceName':'北京','CityName':'北京','ExpAreaName':'朝阳区','Address':'三里屯街道雅秀大厦'},".
				   "'Commodity':".
				   "[{".
				   "'GoodsName':'鞋子','Goodsquantity':1,'GoodsWeight':1.0}],".
				   "'Weight':1.0,".
				   "'Quantity':1,".
				   "'Volume':0.0,".
				   "'Remark':'小心轻放'}";
		
		
		$datas = array(
			'EBusinessID' => '1310235',
			'RequestType' => '1008',
			'RequestData' => urlencode($requestData) ,
			'DataType' => '2',
		);
		$datas['DataSign'] = encrypt($requestData, '22c789e1-81ab-4a49-a9b6-5fa6ed8bd77d');
		$url = 'http://api.kdniao.cc/api/dist';
		$result=sendPost($url, $datas);	
		
		return $result;
	}
}

