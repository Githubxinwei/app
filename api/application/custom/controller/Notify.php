<?php 
namespace app\custom\controller;

//本类封装微信支付de异步通知操作
use think\Exception;

class Notify{
	/*微信支付异步通知处理*/
	function index(){

		$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];file_put_contents('notify.txt',$postStr);
        $data = $this->xml2arr($postStr);
		$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
		$sign_info = $this->sign($postObj);
		if(!$sign_info){
			file_put_contents('wxpay.log',PHP_EOL.json_encode($data)." 签名验证失败".PHP_EOL,FILE_APPEND);
			return;//签名失败
		}

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
            case '3':
			$this->hotel_pay($data,$pay_type);
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
		//判断当前小程序是否有分销
		$this -> distribution($order,1);
		$this -> sendMail($order['appid'],$order['name']);
	}

	/*酒店小程序预约购买付款*/
	private function hotel_pay($data,$pay_type){
        $order_id = $pay_type['id'];//订单表的ID，在生成prepay_id时追加在attach中
        $order = db('rooms_order') -> where(['id'=> $order_id]) -> find();
        if(empty($order) || $order['order_sn'] != $data['out_trade_no'] ){
            //订单数据不符，记录后不处理
            file_put_contents('wxpay.log',PHP_EOL.json_encode($data)." 订单不存在或订单号不一致，已拦截未处理".PHP_EOL,FILE_APPEND);
            return;
        }
        if($order['state'] != 0 ){
            return;//订单不是待处理状态，已确认收款
        }
        //更改订单状态，下发模板消息，下发商户通知
        db('rooms_order') ->where(['id'=>$order_id])-> update(['state'=>1]);
        $where['id'] = $order['rooms_id'];
        db('rooms') ->where($where)->setDec('number_in',1);
        $this -> distribution($order,2);
        $this -> sendMail($order['appid'],$order['room_type']);
	}

    /**
	 * 通过appid获取这个小程序是谁的，同时发送邮件通知有新订单，尽快处理
	*/
    public function sendMail($appid,$name){
		$mail = db('app') -> field('custom_id,name,notifytel,notifyemail') -> where(['appid' => $appid]) -> find();
		if(!$mail){return;}
		sendMail($mail['notifyemail'],'你有新的订单,请尽快处理','你有新的订单,请尽快处理【'.$name.'】等...','163');
        $param = "msg:{$mail['name']}";
		sendMsgInfo($mail['notifytel'],$param,1,1,$mail['custom_id']);
	}

    /**
     * @param $orderInfo 订单信息
     * @param $type 1 goods_order表的信息 2 rooms_order表的信息
     */
	private function distribution($orderInfo,$type){
		if(!$orderInfo || !$type){
			return 0;
		}
		//得到是谁下的单，并判断这个人是否有上级
		$userInfo = db('user') -> field('p_id') -> where(['id' => $orderInfo['user_id']]) -> find();
		if(!$userInfo || !$userInfo['p_id']){return 0;}
		//得到这个小程序的appid在xg_dist_rule分销规则表中是否设置分销，并判断是否打开分销
		$distRule = db('dist_rule') -> where(['appid' => $orderInfo['appid']]) -> find();
		if(!$distRule || $distRule['switch'] != 1){
			return 0;
		}
		//判断是什么分销也就是distRule中的type为0全部分销  1 部分商品参与分销
		$scale = explode(',',$distRule['scale']);
		$goodList = explode(',',$distRule['good_list']);
        $price = 0;
		if($distRule['type'] == 1){
			//部分分销，判断当前订单购买的商品是否是分销产品，如果是，进行分销
            switch ($type){
                case 1:
                    $carts = explode(',',$orderInfo['carts']);
                    foreach ($carts as $k => $v){
                    	$cartInfo = db('good_cart') -> field('good_id,num,price') -> where(['id' => $v]) -> find();
                    	if($cartInfo){
                    		if(in_array($cartInfo['good_id'],$goodList)){
                    			$price += $orderInfo['num'] * $orderInfo['price'];
							}
						}
					}
                    break;
                case 2:
                	//rooms_order里面的rooms_id就是，判断这个id是否在列表中
					if(!in_array($orderInfo['rooms_id'],$goodList)){
                		return 0;
					}
                    break;
                default:
                    return 0;
                    break;
            }
		}

		switch ($type){
			case 1:
				break;
			case 2:
				$price = $orderInfo['total_price'];
				break;
			default:
				return 0;
				break;
		}
		if($price == 0){return 0;}
		define('ORDER_ID',$orderInfo['id']);
		define('ORDER_TYPE',$type);
        $this -> dist($orderInfo['user_id'],$scale,$distRule['level'],$price);
		return 1;
	}

	private function dist($user_id,$scaleArr,$level,$price,$i = 0){
		if(!$user_id || !$scaleArr || !$level || !$price){return 0;}
		if($i >= $level){
			//分销到达最高的级别
			return 1;
		}
		$scale = $scaleArr[$i];
		if(!$scale){return 0;}
		//判断是否有上级
		$p_id = db('user') -> getFieldById($user_id,'p_id');
		if(!$p_id){return 0;}
		//分销金额
		$money = $price * $scale;
		$model = db();
		$model -> startTrans();
		try{
            db('user') -> where(['id' => $p_id]) -> setInc('money',$money);
            //保存记录到记录表中
			$distRecord['user_id'] = $p_id;
			$distRecord['xj_userid'] = $user_id;
			$distRecord['order_id'] = ORDER_ID;
			$distRecord['money'] = $money;
			$distRecord['level'] = $i+1;
			$distRecord['create_time'] = time();
			$distRecord['type'] = ORDER_TYPE;
			db('dist_record') -> insert($distRecord);
			$model -> commit();
			$i++;
			$this -> dist($p_id,$scaleArr,$level,$price,$i);
		}catch (Exception $e){
			$model -> rollback();
			return 0;
		}




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
