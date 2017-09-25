<?php 
namespace app\index\controller;
//本类封装微信支付，支付宝支付的下单操作
class Notify{
	/*微信支付异步通知处理*/
	function weixin(){
		\Think\loader::import('Wechat.jspay');
		$jsapi = new \jspay();
		// 验证签名之前必须调用get_notify_data方法获取数据
		$data = $jsapi -> get_notify_data();
		if(!$data){return;}
		file_put_contents('wxpay.log',json_encode($data)."\r\n",FILE_APPEND);
		if(!$jsapi->check_sign()){
			file_put_contents('wxpay.log',"\r\nCHECK SIGN FAIL\r\n",FILE_APPEND);
			// 签名验证失败
			die('FAIL');
		}
		if($data['return_code'] != 'SUCCESS' || $data['result_code'] != 'SUCCESS'){
			file_put_contents('wxpay.log',"\r\RETURN CODE FAIL\r\n",FILE_APPEND);
			die('FAIL');
		}
		/**/
		// 支付日志
		// $data['transaction_id'] = 6;
		// $data['out_trade_no'] = '201715049229469985';
		if(model('wxpay_log') -> where(array('transaction_id' => $data['transaction_id'])) -> find()){
			die('SUCCESS');
		}
		// 记录支付日志
		$data['log_time'] = time();
		$wxpay_log = model('wxpay_log');
		file_cache('wxpay_test',$data,'');
		$wxpay_log  -> save($data);
		
		$charge_data = file_cache('wxorder/'.$data['out_trade_no'],'','');//用户支付数据
		if(!$charge_data) {die('FAIL');}
		$charge_data['create_time'] = time();
		$charge_data['remark'] = $wxpay_log->id;
		$charge_data['type'] = 'wxpay';
		model('charge_log') -> save($charge_data);
		$money = $data['total_fee']/100;
		if($money != $charge_data['money']){
			file_put_contents('wxpay_error.log',"\r\n".$data['out_trade_no']."支付金额不对 \r\n",FILE_APPEND);
		}else{
			//增加账户余额，删除缓存文件
			model('user') -> where('id',$charge_data['user_id']) -> setInc('gold',$money);
			file_cache('wxorder/'.$data['out_trade_no'],'del','');
		}
		//$user_info = M('user') -> find($info['user_id']);
		// 这个分成的层数不可预期，为了防止出错后未成功添加支付记录，导致重复执行，所以放在最后面
		//if($type != 3)expense($user_info,$money,$type);
		
		die('SUCCESS');
	}
	function alipay(){
		\Think\loader::import('ali.Alipay');
		$alipay = new \Alipay();
		$res = $alipay->notify();
		if($res['code'] == 10000){
			$data = $res['data'];//这是post过来的数据
			//处理业务逻辑
			$this->alipay_sure($data);
			echo 'success';
		}else{
			echo "fail";
		}
	}
	function alipay_return(){
		//做api的时候，这个就不用使用了，在生成支付url的时候，由前台传入一个支付成功后要跳转的地址即可
		\Think\loader::import('ali.Alipay');
		$alipay = new \Alipay();
		$res = $alipay->return_url();
		if($res['code'] == 10000){
			$data = $res['data'];//这是post过来的数据
			//处理业务逻辑
			$this->alipay_sure($data);
			//跳转到要去的页面，例如百度 header("location:http://www.baidu.com/")
		}else{
			echo "fail";
		}
	}
	private function alipay_sure($data){
		file_cache('alipay_test',$data,'');
		//echo json_encode($data);exit;
		$has_done = model('alipay_log') -> where('out_trade_no' , $data['out_trade_no'] ) -> find();
		if($has_done){
			echo 'success';//已处理过
			return;
		}
		$charge_data = file_cache('alipayorder/'.$data['out_trade_no'],'','');//用户支付数据
		
		if(!$charge_data) {file_put_contents('alipay_error.log',"\r\n".$data['out_trade_no']."本地订单缓存文件不存在 \r\n",FILE_APPEND);die('FAIL');}
		$alipay_log = model('alipay_log');
		$data['create_time'] = time();
		$data['buyer_id'] = $charge_data['user_id'];
		$alipay_log ->allowField(true) -> save($data);
		$charge_data['create_time'] = time();
		$charge_data['remark'] = $alipay_log->id;
		$charge_data['type'] = 'alipay';
		model('charge_log') -> save($charge_data);
		$money = $data['total_fee'];
		if($money != $charge_data['money']){
			file_put_contents('alipay_error.log',"\r\n".$data['out_trade_no']."支付金额不对 \r\n",FILE_APPEND);
		}else{
			//增加账户余额，删除缓存文件
			model('user') -> where('id',$charge_data['user_id']) -> setInc('gold',$money);
			file_cache('alipayorder/'.$data['out_trade_no'],'del','');
		}

	}

	
}




?>
