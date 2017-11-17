<?php 
namespace app\custom\controller;

class User{
	function index(){
		
		$this->data = input("post.",'','htmlspecialchars');
		if(!$this->data){$this->data = $_GET;}
		if(!isset($this->data['apps'])){
			$return['code'] = 10001;$return['msg_test'] = 'apps不能为空';return json($return);
		}
		if(!isset($this->data['appid'])){
			$return['code'] = 10001;$return['msg_test'] = 'appid不能为空';return json($return);
		}
		if(!isset($this->data['code'])){
			$return['code'] = 10001;$return['msg_test'] = 'code不能为空';return json($return);
		}
		

		$common = new \app\weixin\controller\Component();

		$info = $common->get_session($this->data['apps'],$this->data['appid'],$this->data['code']);
		if(!isset($info['session_key'])){
			$return['code'] = 10003;$return['msg_test'] = $info['errmsg'];return json($return);
		}else{
			//判断用户是否存在
            $where['apps'] = $this->data['apps'];
            $where['openid'] = $info['openid'];
			$user_info =  db('user') -> where($where) -> find();
			if(!$user_info){
				model('user') ->isUpdate(false)-> save(['openid'=>$info['openid'],'create_time'=>time(),'apps'=>$this->data['apps']]);
				$user_info['id'] = model('user')->id;
				$user_info['openid'] = $info['openid'];
				$user_info['apps'] = $this->data['apps'];
				// $return['code'] = 20000;
			}
			$return['code'] = 10000;
			$session_key = session('user',$user_info);
			$return['data'] = ['session_key'=>$session_key];
			return json($return);
		}
	}
	
	function info(){
		//session_id($this->data['session_key']);
		session_start();
		$session_key = session_id();
		$this->data = input("post.",'','htmlspecialchars');
		if(!$session_key ){
			$return['code'] = 0;$return['msg_test'] = '用户登录已过期';return json($return);
		}elseif(!isset($this->data['session_key'])){
			$return['code'] = 10001;$return['msg_test'] = 'session_key不能为空';return json($return);
		}elseif($this->data['session_key'] != $session_key ){
			$return['code'] = 0;$return['msg_test'] = '用户登录已过期，值有誤';return json($return);
		}
		$user_info = session('user');
		// $this->user = model('user') -> where('id',$this->user['id']) -> find();
		// if(!$this->user || $this->user->apps != $this->apps){
		// 	$return['code'] = 0;$return['msg_test'] = '用户登录已过期,查无此人';echo json_encode($return);exit;
		// }
		$t_data = [
			'nickName'=>$this->data['nickName'],
			'gender'=>$this->data['gender'],
			'avatarUrl'=>$this->data['avatarUrl'],
			'city'=>$this->data['city'],
			'province'=>$this->data['province'],
			'country'=>$this->data['country']
		];
		$apps = explode(',',$user_info['apps']);
		if($user_info['apps'] == 0){
			$t_data['apps'] = $this->data['apps'];
		}
		file_cache('t_data',$t_data,'');
		$res = model('user') -> allowField(true) -> save($t_data,['id'=>$user_info['id']]);
		$return['code'] = 10000;return json($return);
		 // if( !$res ){
		 // 	$return['data'] = $user_info;
		 // 	$return['code'] = 10003;$return['msg'] = '保存用户信息失败，请重新操作'.$user_info['id'];return json($return);
		 // }else{
		 // 	$return['code'] = 10000;return json($return);
		 // }
	}
	
	function test(){
		$user_info = db('user') -> where('id',1) -> find();
		$key = session('user',$user_info);dump($key);exit;
		$res = model('app')->field('id app_id,use_time,is_del')->where('appid',56617092)->find();return $res;
		// session('user',null);exit;
		//$key = session('user',['id'=>2]);dump($key);
		//session_start();
		//dump(session_id('user'));exit;
		// $t_data = file_cache('t_data','','');
		// $res = model('user') -> allowField(true) -> save($t_data,['id'=>5]);
	}
	function test2(){
		session_id('f3in43a4aeuc4srmgo25q1eba0');session_start();
		$user_info = session('user');dump($user_info);
		$apps = explode(',','');
		if(!in_array($user_info['app_id'],$apps)){
			$t_data['apps'] = ['exp','apps+,'.$user_info['app_id']];dump($t_data);exit;
		}

		model('user') ->  allowField(true) -> save($t_data,['id'=>$user_info['id']]);
		// $user_info = db('user') -> where('id',$user_info['id']) -> find();
		// $app_info = db('app')->field('id app_id,use_time,is_del')->where('appid',56617092)->find();
		// $user_info = array_merge($user_info,$app_info);
		// dump($user_info);
		if(session('user')){
			dump(session('user'));exit;
		}else{echo '不存在';}
		//session(null);
		dump(session(''));echo '已清除';exit;
		$carts = model('goods_cart') ->field('group_concat(id) id,name,pic,sum(num)') -> where(['user_id'=>3,'appid'=>23542640,'is_cart'=>0]) -> find();
		return json($carts);
	}
	function test1(){
		$str = '{"appid":"wxee74a03b4c01bebc","attach":"{\"type\":1,\"id\":\"67\"}","bank_type":"CFT","cash_fee":"1","fee_type":"CNY","is_subscribe":"N","mch_id":"1254101501","nonce_str":"zFRWelTR4fRHd1PX6","openid":"om2Tu0KgLuRkzGTxx-omCypOea2s","out_trade_no":"201715075308535497","result_code":"SUCCESS","return_code":"SUCCESS","sign":"CF8BE458DBCD6CD8531DBC1AB749EAC8","time_end":"20171009143349","total_fee":"1","trade_type":"JSAPI","transaction_id":"4200000029201710097031061799"}';
		$data = json_decode($str,true);
		$data = $this->arr2xml($data);dump($data);
		$sign_info = $this->sign($data);dump($sign_info);
		exit;
		if($data['return_code'] != 'SUCCESS' || $data['result_code'] != 'SUCCESS'){
			echo 2;
			die('FAIL');
		}
		
		$attach = $data['attach'];
		$pay_type = json_decode($attach,true);
		if( !isset($pay_type['type']) ){
			file_put_contents('wxpay.log',"\r\n".json_encode($data)." 缺少attach\r\n",FILE_APPEND);
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
			file_put_contents('wxpay.log',"\r\n".json_encode($data)." 未知attach类型\r\n",FILE_APPEND);
			break;
		}
		die('SUCCESS');
	}
	
	
	
	//电商小程序商品购买付款
	private function dianshang_pay($data,$pay_type){
		dump($pay_type);
		//确认订单是否存在，是否是待收款状态
		$order_id = $pay_type['id'];//订单表的ID，在生成prepay_id时追加在attach中
		$order = model('goods_order') -> where('id',$order_id) -> find();
		if(empty($order) || $order['order_sn'] != $data['out_trade_no'] ){
			//订单数据不符，记录后不处理
			file_put_contents('wxpay.log',"\r\n".json_encode($data)." 订单不存在或订单号不一致，已拦截未处理\r\n",FILE_APPEND);
			return;
		}
		if($order['state'] != 0 ){
			return;//订单不是待处理状态，已确认收款
		}
		//更改订单状态，下发模板消息，下发商户通知
		model('goods_cart') -> save(['is_pay'=>1],['id'=>['exp','in ('.$order['carts'].')']]);
		model('goods_order') -> save(['state'=>1],['id'=>$order_id]);

	}

    /*酒店小程序商品购买付款*/
    private function hotel_pay($data,$pay_type){
        $order_id = $pay_type['id'];//订单表的ID，在生成prepay_id时追加在attach中
        $order = model('rooms_order') -> where('id',$order_id) -> find();
        if(empty($order) || $order['order_sn'] != $data['out_trade_no'] ){
            //订单数据不符，记录后不处理
            file_put_contents('wxpay.log',PHP_EOL.json_encode($data)." 订单不存在或订单号不一致，已拦截未处理".PHP_EOL,FILE_APPEND);
            return;
        }
        if($order['state'] != 0 ){
            return;//订单不是待处理状态，已确认收款
        }
        //更改订单状态，下发模板消息，下发商户通知
        model('rooms_order') -> save(['state'=>1],['id'=>$order_id]);
    }


}


 ?>