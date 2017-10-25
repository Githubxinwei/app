<?php 
namespace app\custom\controller;

//本类封装微信支付de异步通知操作
class Notify{
	/*微信支付异步通知处理*/
	function index(){
		$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];file_put_contents('notify.txt',$postStr);
		$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
		$sign_info = $this->sign($postObj);
		if(!$sign_info){
			file_put_contents('wxpay.log',PHP_EOL.json_encode($data)." 签名验证失败".PHP_EOL,FILE_APPEND);
			return;//签名失败
		}
		$data = $this->xml2arr($postStr);
		if($data['return_code'] != 'SUCCESS' || $data['result_code'] != 'SUCCESS'){
			file_put_contents('wxpay.log',PHP_EOL.json_encode($data)." RETURN CODE FAIL".PHP_EOL,FILE_APPEND);
			die('FAIL');
		}
		$attach = $data['attach'];
		$pay_type = json_decode($attach,true);
		if( !isset($pay_type['type']) ){
			file_put_contents('wxpay.log',PHP_EOL.json_encode($data)." 缺少attach".PHP_EOL,FILE_APPEND);
			die('FAIL');
		}
		switch($pay_type['type']){
			case '1':
			$this->dianshang_pay($data,$pay_type);
			break;
			default:
			file_put_contents('wxpay.log',PHP_EOL.json_encode($data)." 未知attach类型".PHP_EOL,FILE_APPEND);
			break;
		}
		die('SUCCESS');
	}
	
	//电商小程序商品购买付款
	private function dianshang_pay($data,$pay_type){
		//确认订单是否存在，是否是待收款状态
		$order_id = $pay_type['id'];//订单表的ID，在生成prepay_id时追加在attach中
		$order = model('goods_order') -> where('id',$order_id) -> find();
		if(empty($order) || $order['order_sn'] != $data['out_trade_no'] ){
			//订单数据不符，记录后不处理
			file_put_contents('wxpay.log',PHP_EOL.json_encode($data)." 订单不存在或订单号不一致，已拦截未处理".PHP_EOL,FILE_APPEND);
			return;
		}
		if($order['state'] != 0 ){
			return;//订单不是待处理状态，已确认收款
		}
		//更改订单状态，下发模板消息，下发商户通知
		model('goods_cart') -> save(['is_pay'=>1],['id'=>['exp','in ('.$order['carts'].')']]);
		model('goods_order') -> save(['state'=>1],['id'=>$order_id]);



		
	}

	/**
	*	xml转为数组
	*	@param string $xml 原始的xml字符串
	*/
	public function xml2arr($xml){
		$xml = new \SimpleXMLElement($xml);
		if(!is_object($xml)){
			$this->errmsg = "xml数据接收错误";
			return false;
		}
		$arr = array();
		foreach ($xml as $key => $value) {
			$arr[strtolower($key)] = strval($value);
		}
		return $arr;
	}
	/*支付解密*/
	private function sign($postObj){
		$sign = trim($postObj->sign);
		$appid = trim($postObj->appid);
		$attach = trim($postObj->attach);
		$bank_type = trim($postObj->bank_type);
		$cash_fee = trim($postObj->cash_fee);
		$fee_type = trim($postObj->fee_type);
		$is_subscribe = trim($postObj->is_subscribe);
		$mch_id = trim($postObj->mch_id);
		$nonce_str = trim($postObj->nonce_str);
		$openid = trim($postObj->openid);
		$out_trade_no = trim($postObj->out_trade_no);
		$result_code = trim($postObj->result_code);
		$return_code = trim($postObj->return_code);
		$time_end = trim($postObj->time_end);
		$total_fee = trim($postObj->total_fee);
		$trade_type = trim($postObj->trade_type);
		$transaction_id = trim($postObj->transaction_id);
		$str1 = 'appid='.$appid.'&attach='.$attach.'&bank_type='.$bank_type.'&cash_fee='.$cash_fee.'&fee_type='.$fee_type.'&is_subscribe='.$is_subscribe.'&mch_id='.$mch_id.'&nonce_str='.$nonce_str.'&openid='.$openid.'&out_trade_no='.$out_trade_no.'&result_code='.$result_code.'&return_code='.$return_code.'&time_end='.$time_end.'&total_fee='.$total_fee.'&trade_type='.$trade_type.'&transaction_id='.$transaction_id;
		$auth_info = model('auth_info')->field("mkey") -> where('appid',$appid)->find();
		$str2 = $str1.'&key='.$auth_info['mkey'];
		$new_sign = strtoupper(MD5($str2));
		if($new_sign == $sign){return true;}else{return false;}
	}
}




?>
