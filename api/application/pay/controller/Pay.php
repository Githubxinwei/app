<?php 
namespace app\index\controller;
//本类封装微信支付，支付宝支付的下单操作
class Pay{
	/*微信支付*/
	function weixin(){
		//充值支付的订单，充值时不要去插入数据，会成为垃圾数据，严谨来讲，可以把订单信息文件形式缓存在本地，当充值失败时可以做为参考，充值成功时从缓存文件中读取用户的详细信息
		//$openid = 'o5NEduHfKL9r5JDwtAv_ZJ5lo6m4';$money = 1;
		$openid = $this->user->openid;
		if( isset($_POST['money']) ){
			$money = $_POST['money']*1;
		}else{
			$money = 1;
			// $return['code'] = 10001;
			// $return['msg'] = '缺参';
			// return json($return);
		}
		$sn = date("Y").time().rand(1000,9999);
		$notify_url =  'http://'.$_SERVER['HTTP_HOST'].'/pay/Notify/weixin';//$GLOBALS['_CFG']['site']['url']
		$pay = get_wxpay_parameters($openid,$sn,$money,$notify_url);//dump($pay);
		if(isset($pay['code'])){
			return json($pay);//调用接口失败，返回失败原因，例如{"msg":"统一下单接口调用失败openid is invalid","code":10001}
		}
		//本地缓存订单的信息
		$data = ['user_id'=>$this->user->id,'money'=>$money];
		file_cache('wxorder/'.$sn,$data,'');
		$return['code'] = 10000;
		$return['data'] = $pay;
		return json($return);
	}

	/*支付宝支付*/
	public function alipay(){
		if( isset($_POST['money']) ){
			$total_fee = $_POST['money']*1;
		}else{
			$total_fee = 0.01;
			// $return['code'] = 10001;
			// $return['msg'] = '缺参';
			// return json($return);
		}
		// if( isset($_POST['return_url'])){
		// 	$return_url =  "http://".$_SERVER['SERVER_NAME'].url('notify/alipay_return');
		// }else{
		// 	$return['code'] = 10001;
		// 	$return['msg'] = '缺少支付成功后跳转地址return_url';
		// 	return json($return);
		// }
		
		
		
		\Think\loader::import('ali.Alipay');
		$alipay = new \Alipay();
		$out_trade_no =date("Y").time().rand(1000,9999);//订单号
		$subject = '充值点券';//订单名称，必填
		//$total_fee =1;//付款金额，必填
		$show_url = "http://".$_SERVER['SERVER_NAME'].'/web/';//收银台页面上，商品展示的超链接，必填
		$notify_url =  "http://".$_SERVER['SERVER_NAME'].url('notify/alipay');
		$return_url =  "http://".$_SERVER['SERVER_NAME'].url('notify/alipay_return');
		$pay_url = $alipay->pay($out_trade_no,$subject,$total_fee,$show_url,$notify_url,$return_url);
		//dump($pay_url);exit;
		$data = ['user_id'=>$this->user->id,'money'=>$total_fee];
		file_cache('alipayorder/'.$out_trade_no,$data,'');//生成用户支付数据 
		 echo 1;header("location:".$pay_url);exit;$return['code'] = 10000;
		$return['data'] = $pay_url;
		//return json($return);

	}
}




?>
