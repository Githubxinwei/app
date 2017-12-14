<?php
namespace app\custom\controller;

/*客户个人信息相关操作*/
use think\Exception;

class Own extends Action{
	//返回拥有的小程序列表
	public function _initialize()
	{
		parent::_initialize(); // TODO: Change the autogenerated stub
		$this -> data = input("post.",'','htmlspecialchars');
	}

	/**
	 * 获取小程序的列表
	 */
	public function getUserAppList(){
		//查询当前用户是否存在
		$info = db('app') -> where("custom_id",$this->custom->id) -> where('is_del','0') -> select();
		$arr['code'] = 10000;$arr['msg'] = '获取成功';$arr['msg_test'] = '获取成功';$arr['data'] = $info;
		return json($arr);

	}

	/**
	 * 用户创建小程序
	 */
	public function createUserApp(){

		$this -> data['custom_id'] = $this -> custom -> id;
		if(!isset($this -> data['type']) || !isset($this -> data['custom_id'])){
			$return['code'] = 10002;
			$return['msg'] = '参数值缺失';
			return json($return);
		}
		if(!get_app($this -> data['type'])){
			$return['code'] = 10003;
			$return['msg'] = '小程序不存在';
			return json($return);
		}

		//判断当前用户是否可以在创建小程序
//       $app_num = db('app')->where(['custom_id'=> $this->data['custom_id'],'type'=>$this -> data['type']])->count();
        $setting = db('app_setting')->field('id,app_num')->where(['id'=>$this->data['setting_id']])->find();
//		$maxappnum = db('custom') -> getFieldByid($this -> data['custom_id'],'max_app_num');
//		$app_num = db('custom') -> getFieldByid($this -> data['custom_id'],'app_num');
/*		if($app_num >= $maxappnum['app_num']){
			$return['code'] = 10006;
			$return['msg'] = '当前用户此类型的小程序已上限';
			return json($return);
		}*/

		//获取当前用户随机的字符串
		$flag = true;
		while($flag){
			$appid = $this -> getNumber();
			$res = db('app') -> where("appid = :appid",['appid' => $appid]) -> select();
			if(!$res){
				$flag = false;
				$this -> data['appid'] = $appid;
			}
		}
		$app_info = get_app($this -> data['type']);
		$this -> data['name'] = $app_info['name'];
        $this -> data['pic'] = $app_info['pic'];
		$this -> data['setting_id'] = $setting['id'];
		$this -> data['create_time'] = time();
		$this -> data['try_time'] = time() + 86400;//一天的使用期
		$this -> data['use_time'] = time() + 86400;
		$id = model('app') -> allowField(true) -> save($this -> data);
		if($id){
			//更新用户的数据表
			db('custom') -> where("id",$this -> data['custom_id']) -> setInc('app_num',1);
			$return['code'] = 10000;
			$return['data'] = ['id' => $id,'appid' => $this -> data['appid'],'try_time' => $this -> data['try_time']];
			$return['msg'] = '添加成功';
			return json($return);
		}else{
			$return['code'] = 10007;
			$return['msg'] = '添加失败';
			return json($return);
		}
	}

	private function getNumber(){
		return mt_rand(10000000,99999999);
	}

	/**
	 * 小程序购买 账户余额购买
	 */
	public function buyUserApp(){
		$this -> data['custom_id'] = $this -> custom -> id;
		if(!isset($this -> data['appid'])){
			$return['code'] = 10004;
			$return['msg'] = '缺少app的唯一标识,获取列表的时候传递过去的appid';
			return json($return);
		}
		if(!preg_match("/^\d{8}$/",$this -> data['appid'])){
			$return['code'] = 10005;
			$return['msg'] = 'appid是一个8位数';
			return json($return);
		}
		//判断当前用户的小程序是否有这个appid
		$is_true = db('app') -> where(['appid' => $this -> data['appid']]) -> find();
		if(!$is_true){
			$return['code'] = 10006;
			$return['msg'] = '这个客户没有创建这个小程序';
			return json($return);
		}
		if($is_true['custom_id'] != $this -> custom->id){
			$return['code'] = 10011;
			$return['msg'] = '这个客户没有创建这个小程序';
			return json($return);
		}
		$app_type = $is_true['type'];
		$app_info = get_app($app_type);
		if(!$app_info){
			$return['code'] = 10007;
			$return['msg'] = '小程序类型不存在';
			return json($return);
		}


        $user_id = $this -> custom ->id; //用户id
        $user = db('custom')->field("is_agency_user,is_belong")->where(['id'=>$user_id])->find(); //用户信息
        /*代理商的情况*/
        if($user['is_agency_user'] == 1 ){
            $where['type_auto'] = 1 ;
            $where['type_ssh'] = 1 ;
            $where['user_system'] = 1 ;
        }
        /*普通用户是超级管理员下的情况*/
        if($user['is_belong'] == 0 ){
            $where['type_auto'] = 1 ;
            $where['type_ssh'] = 2 ;
            $where['user_system'] = 1 ;
        }
        /*普通用户是代理商的情况下*/
        if($user['is_belong'] == 1 ){
            $where['type_auto'] = 2 ;
            $where['type_ssh'] = 2 ;
            $where['user_system'] = $user['id_agency'];
        }
        $where['type'] = $app_type;
        $setting = db('app_setting')->where($where)->find();
        $data['name'] = $is_true['name'];
        $data['price'] = $setting['price'];
        $data['zk'] = '';
        $data['all_money'] = $data['price'] - $data['zk'];
        $fee =  $data['all_money'] ;
		

		if(!$fee || $fee <= 0){
			$return['code'] = 10008;
			$return['msg'] = '小程序价格错误';
			return json($return);
		}
		//判断用户是否有钱
		$userMoney = db('custom') -> field('wallet')->where(['id'=> $this -> data['custom_id']])->find();

        if($fee > $userMoney['wallet']){
			$return['code'] = 10009;
			$return['msg'] = '用户余额不足';
			return json($return);
		}
        $model = db();
		$model -> startTrans();
		try{
            $res = db('custom') -> where(['id' => $this -> data['custom_id']]) -> setDec('wallet',$fee);
            if($res){
                $buyData['custom_id'] = $this->custom->id;
                $buyData['appid'] = $this->data['appid'];
                $buyData['money'] = $fee;
                $buyData['order_sn'] = $this->custom->id . time() . mt_rand(1,9999);;
                $buyData['create_time'] = time();
                $buyData['pay_time'] = time();
                $buyData['type'] = 0;
                $buyData['state'] = 1;
                $buyData['year_num'] = $setting['year_num'];
                db('buy_app_log') -> insertGetId($buyData);
                $info['update_time'] = time();
                //判断当前购买的时间是否在过期
                $app_info = db('app') -> field("use_time,fee") ->  where(['appid' => $this -> data['appid']]) -> find();
                $year_time = strtotime($setting['year_num'].'year');
                if($app_info['use_time'] > time()){
                    //还没过，应该在原来的基础上添加
                    $info['use_time'] = $app_info['use_time'] + $year_time - time();
                }else{
                    //过期了，在现在的时间上添加
                    $info['use_time'] = $year_time;
                }
                $info['fee'] = $app_info['fee'] + $fee;
                db('app') -> where(['appid' => $this -> data['appid']]) -> update($info);
                db('custom') -> where(['id' => $this -> data['custom_id']]) -> setInc('expense',$fee);
                $return['code'] = 10000;
                $return['data'] = ['use_time' => $info['use_time']];
                $return['msg'] = '购买成功';
            }else{
                $return['code'] = 10010;
                $return['msg'] = '更新数据失败';
            }
            $model -> commit();
            return json($return);
        }catch (Exception $e){
		    $model -> rollback();
        }


	}


	/*
     小程序数量升级购买 账户余额购买
	 * */
    public function buyUserAppNum(){

        $this -> data['custom_id'] = $this -> custom -> id;

        $id = $this->data['id'];
        $info = db('app_num')->where(['id'=>$id])->find();
        $fee = $info['price'];

        if(!$fee || $fee <= 0){
            $return['code'] = 10008;
            $return['msg'] = '小程序价格错误';
            return json($return);
        }
        //判断用户是否有钱
        $userMoney = db('custom') -> field('wallet')->where(['id'=> $this -> data['custom_id']])->find();

        if($fee > $userMoney['wallet']){
            $return['code'] = 10009;
            $return['msg'] = '用户余额不足';
            return json($return);
        }
        $model = db();
        $model -> startTrans();
        try{
            $res = db('custom') -> where(['id' => $this -> data['custom_id']]) -> setDec('wallet',$fee);
            if($res){
                $buyData['custom_id'] = $this->custom->id;
                $buyData['money'] = $fee;
                $buyData['order_sn'] = $this->custom->id . time() . mt_rand(1,9999);
                $buyData['create_time'] = time();
                $buyData['app_num'] = $info['app_num'];
                $buyData['type'] = 0;
                $buyData['state'] = 1;
                db('buy_app_num_log') -> insertGetId($buyData);
                db('custom') -> where(['id' => $this->data['custom_id']]) -> setInc('max_app_num',$buyData['app_num']);
                $return['code'] = 10000;
                $return['msg'] = '购买成功';
            }else{
                $return['code'] = 10010;
                $return['msg'] = '更新数据失败';
            }
            $model -> commit();
            return json($return);
        }catch (Exception $e){
            $model -> rollback();
        }


    }


	/**
	 * 删除小程序
	 */
	public function delUserApp(){
		if(!isset($this -> data['appid'])){
			$return['code'] = 10001;
			$return['msg'] = '缺少app的唯一标识,获取列表的时候传递过去的appid';
			return json($return);
		}
		if(!preg_match("/^\d{8}$/",$this -> data['appid'])){
			$return['code'] = 10002;
			$return['msg'] = 'appid是一个8位数';
			return json($return);
		}
		$res = db('app') -> where(['appid' => $this -> data['appid']]) -> setField('is_del',1);
		if($res){
			$return['code'] = 10000;
			$return['msg'] = '删除成功';
			return json($return);
		}else{
			$return['code'] = 10003;
			$return['msg'] = '删除失败';
			$return['msg_test'] = 'appid可能传递错了或者这个小程序不是当前用户的';
			return json($return);
		}

	}


	/**
	 * 获取小程序的基本信息
	 * appid
	 */
	public function getAppInfo(){
		if(!isset($this -> data['appid'])){
			$return['code'] = 10001;
			$return['msg'] = '缺少app的唯一标识,获取列表的时候传递过去的appid';
			return json($return);
		}
		if(!preg_match("/^\d{8}$/",$this -> data['appid'])){
			$return['code'] = 10002;
			$return['msg'] = 'appid是一个8位数';
			return json($return);
		}


		//判断当前用户的小程序是否有这个appid
		$info = db('app') -> field('name,type,setting_id,pic,desc,tel,site_url,address,is_publish,custom_id,start_time,over_time,business,is_forbidden') ->  where(['appid' => $this -> data['appid']]) -> find();
		if($info['custom_id'] != $this->custom->id){
			$return['code'] = 10004;
			$return['msg'] = '这个app不是这个用户的';
			return json($return);
		}
        if(!isset($this -> data['appid'])) {
            if ($this->data['author'] == 1) {
                //判断当前用户是否可以在创建小程序
                $app_num = db('auto_info')->where(['custom_id' => $this->data['custom_id'], 'type' => $info['type']])->count();
                $max_num = db('custom')->field('max_app_num')->where(['id' => $this->data['custom_id']])->find();
                if ($app_num >= $max_num['app_num']) {
                    $return['code'] = 10006;
                    $return['msg'] = '当前用户此类型的小程序已上限';
                    return json($return);
                }
            }
        }


        if($info){
			$return['code'] = 10000;
			$return['data'] = $info;
			$return['msg'] = '成功';
			$return['msg_test'] = '成功';
			return json($return);
		}else{
			$return['code'] = 10003;
			$return['msg'] = '查询数据失败';
			$return['msg_test'] = '参数可能传递错了,或者这个小程序不是这个用户的';
			return json($return);
		}
	}

	/**
	 * 设置小程序的姓名，标志，简介等信息
	 * appid
	 */
	public function setAppInfo(){
		if(!isset($this -> data['appid']) || !isset($this -> data['name']) || !isset($this -> data['tel'])){
			$return['code'] = 10001;$return['msg'] = '缺少appid或name或tel';
			return json($return);
		}
		if(!preg_match("/^\d{8}$/",$this -> data['appid'])){
			$return['code'] = 10002;$return['msg'] = 'appid是一个8位数';
			return json($return);
		}
        // 预约
		model('app') ->allowField(['name','pic','desc','tel','site_url','address','start_time','over_time','business']) -> save($this -> data,['appid' => $this -> data['appid']]);
		$return['code'] = 10000;
		return json($return);
	}


	/**
	 * 微信支付的商户号和商户key
	 * appid,mchid,mkey
	 */
	public function setUserWxKey(){
		if(!isset($this->data['appid']) || !isset($this->data['mchid']) || !isset($this->data['mkey'])){
			$return['code'] = 10001;
			$return['msg'] = '参数值缺失';
			return json($return);
		}
		if(!preg_match("/^.{10}$/",$this -> data['mchid']) || !preg_match("/^\d{8}$/",$this -> data['appid'])){
			$return['code'] = 10002;
			$return['msg'] = '商户号或者appid格式不正确';
			return json($return);
		}
		$info = db('auth_info') -> field('custom_id,mchid,mkey') -> where(['apps' => $this -> data['appid']]) -> find();
		if(!$info){
			$return['code'] = 10003;
			$return['msg'] = '该小程序的授权信息不存在';
			return json($return);
		}
		if($info['custom_id'] != $this->custom->id){
			$return['code'] = 10006;
			$return['msg'] = '这个授权信息不是这个用户的';
			return json($return);
		}
		if($info['mchid']|| $info['mkey']){
			$return['code'] = 10004;
			$return['msg'] = '商户号或秘钥已存在';
			return json($return);
		}
		$mData['mchid'] = $this->data['mchid'];
		$mData['mkey'] = $this->data['mkey'];
		$res = db('auth_info')  -> where(['apps' => $this -> data['appid']]) -> update($mData);
		if($res){
			$return['code'] = 10000;
			$return['msg'] = '添加成功';
			return json($return);
		}else{
			$return['code'] = 10005;
			$return['msg'] = 'appid或许不对';
			return json($return);
		}
	}

	/**
	 * 获取商户号和商户Key
	 */
	public function getWxKey(){
		if(!isset($this->data['appid'])){
			$return['code'] = 10001;
			$return['msg'] = '参数值缺失';
			return json($return);
		}
		if(!preg_match("/^\d{8}$/",$this -> data['appid'])){
			$return['code'] = 10002;
			$return['msg'] = 'appid格式不正确';
			return json($return);
		}
		$info = db('auth_info') -> field('custom_id,mchid,mkey') -> where(['apps' => $this->data['appid']]) -> find();
		if(!$info){
			$return['code'] = 10003;
			$return['msg'] = '授权信息不存在,也就是没设置';
			return json($return);
		}
		if($info['custom_id'] != $this->custom->id){
			$return['code'] = 10004;
			$return['msg'] = '授权信息不是这个人的';
			return json($return);
		}
		$return['code'] = 10000;
		unset($info['custom_id']);
		$return['data'] = $info;
		$return['msg'] = 'ok';
		return json($return);
		
	}

	/**
	 * 解除小程序的商户号和商户key
	 * appid
	 */
	public function clearWxKey(){
		if(!isset($this->data['appid'])){
			$return['code'] = 10001;
			$return['msg'] = '参数值缺失';
			return json($return);
		}
		if(!preg_match("/^\d{8}$/",$this -> data['appid'])){
			$return['code'] = 10002;
			$return['msg'] = 'appid格式不正确';
			return json($return);
		}
		//是否设置了授权
		$info = db('auth_info') -> field('custom_id,mchid,mkey') -> where(['apps' => $this->data['appid']]) -> find();
		if(!$info){
			$return['code'] = 10003;
			$return['msg'] = '该小程序的授权信息不存在';
			return json($return);
		}
		if($info['custom_id'] != $this->custom->id){
			$return['code'] = 10005;
			$return['msg'] = '不是这个用户的';
			return json($return);
		}
		if(!$info['mchid']||!$info['mkey']){
			$return['code'] = 10004;
			$return['msg'] = '商户号或秘钥不存在';
			return json($return);
		}
		$m_data['mchid'] = '';
		$m_data['mkey'] = '';
		$res = db('auth_info') -> where(['apps' => $this->data['appid']]) -> update($m_data);
		if($res){
			$return['code'] = 10000;
			$return['msg'] = 'OK';
			return json($return);
		}else{
			$return['code'] = 10006;
			$return['msg'] = '失败了';
			return json($return);
		}
	}

	/**
	 * 添加通知提醒
	 * appid
	 */
	public function getNotifyInfo(){
		if(!isset($this->data['appid'])){
			$return['code'] = 10001;
			$return['msg'] = '参数值缺失';
			return json($return);
		}
		if(!preg_match("/^\d{8}$/",$this -> data['appid'])){
			$return['code'] = 10002;
			$return['msg'] = 'appid格式不正确';
			return json($return);
		}
		$info = db('app') -> field("notifytel,notifyemail,custom_id") -> where('appid',$this->data['appid']) -> find();
		if(!$info){
			$return['code'] = 10003;
			$return['msg'] = 'appid不正确';
			return json($return);
		}else{
			if($info['custom_id'] != $this->custom->id){
				$return['code'] = 10004;
				$return['msg'] = '当前的小程序不是这个用户的';
				return json($return);
			}
			unset($info['custom_id']);
			$return['code'] = 10000;
			$return['data'] = $info;
			$return['msg'] = 'ok';
			return json($return);
		}

	}

	/**
	 * 设置或保存通知信息
	 * appid,notifytel notifyemail
	 */
	public function setNotifyInfo(){
		if(!isset($this->data['appid'])){
			$return['code'] = 10001;
			$return['msg'] = '参数值缺失';
			return json($return);
		}
		if(!preg_match("/^\d{8}$/",$this -> data['appid'])){
			$return['code'] = 10002;
			$return['msg'] = 'appid格式不正确';
			return json($return);
		}
		if(!isset($this->data['notifytel']) || !isset($this->data['notifyemail'])){
			$return['code'] = 10003;
			$return['msg'] = '请传入参数';
			return json($return);
		}
		//判断规则
		if(!preg_match("/^1[3|4|5|7|8][0-9]{9}$/",$this -> data['notifytel']) || !preg_match("/^[\w\.-]+@[a-zA-Z\d]+(\.[a-zA-Z]+)?\.[a-zA-Z]{1,3}$/",$this -> data['notifyemail'])){
			$return['code'] = 10004;
			$return['msg'] = '格式不正确';
			return json($return);
		}
		$info['notifytel'] = $this->data['notifytel'];
		$info['notifyemail'] = $this->data['notifyemail'];
		$res = model('app') -> where("appid = :appid and custom_id = :custom_id",['appid' => $this->data['appid'],'custom_id' => $this->custom->id]) -> update($info);
		if($res){
			$return['code'] = 10000;
			$return['msg'] = 'ok';
			return json($return);
		}else{
			$return['code'] = 10005;
			$return['msg'] = '添加失败';
			return json($return);
		}
	}
	/**
	 * 发送手机验证码
	 *
	 */
	public function sendMsg(){
		if(!isset($this->data['tel'])){
			$return['code'] = 10001;
			$return['msg'] = '传入手机号';
			return json($return);
		}
		if(!preg_match("/^1[3|4|5|7|8][0-9]{9}$/",$this -> data['tel'])){
			$return['code'] = 10002;
			$return['msg'] = '格式不正确';
			return json($return);
		}
		$code1 = mt_rand(100000,999999);
		$param = "code:{$code1}";
		$code = sendMsgInfo($this->data['tel'],$param,1,0,$this -> custom -> id);
		if($code == 0000){
			$return['code'] = 10000;
			$return['msg'] = '发送成功';
            file_cache($this->data['tel'] . '.php',$code1,120);
			return json($return);
		}else{
			$return['code'] = $code;
			$return['msg'] = $code;
			return json($return);
		}
	}

	/**
	 * 验证手机号是否正确
	 * code
	 */
	public function verifyMsgCode(){
		if(!isset($this->data['code']) || !isset($this->data['tel'])){
			$return['code'] = 10001;
			$return['msg'] = '缺少参数code和tel';
			return json($return);
		}
		if(!preg_match("/^[0-9]{6}$/",$this -> data['code'])){
			$return['code'] = 10002;
			$return['msg'] = 'code位6位数字';
			return json($return);
		}
                $code = file_cache($this->data['tel'] . '.php');
		if(!$code){
			$return['code'] = 10003;
			$return['msg'] = '验证码失效,请重新获取';
			return json($return);
		}
		$msg = $this->data['code'];
		if($code == $msg){
			$return['code'] = 10000;
			$return['msg'] = 'ok';
			return json($return);
		}else{
			$return['code'] = 10004;
			$return['msg'] = '验证码不正确';
			return json($return);
		}
	}

	public function getUserList(){
		if(!isset($this -> data['appid'])){
			$return['code'] = 10001;
			$return['msg'] = '缺少app的唯一标识,获取列表的时候传递过去的appid';
			return json($return);
		}
		if(!preg_match("/^\d{8}$/",$this -> data['appid'])){
			$return['code'] = 10002;
			$return['msg'] = 'appid是一个8位数';
			return json($return);
		}
		$app_id = db('app') -> field('id,custom_id') -> where(['appid' => $this->data['appid']]) -> find();
		if($app_id['custom_id'] != $this->custom->id){
			$return['code'] = 10003;
			$return['msg'] = '当前的小程序不是这个用户的';
			return json($return);
		}
		$num = isset($this->data['limit_num']) ? $this->data['limit_num'] : 10;
		$page = isset($this->data['page']) ? $this->data['page'] : 1;
		$where = array();
		if(isset($this->data['nickname'])){
			if($this->data['nickname']){
				$where['nickName'] = $this->data['nickname'];
			}
		}
        $number = db('user')
            -> where(['apps' => $this -> data['appid']])
            -> where($where)
            -> count();
		$info = db('user')
			-> field("avatarUrl,nickName,gender,country,province,create_time")
			-> where(['apps' => $this -> data['appid']])
			-> where($where)
			-> page($page,$num)
			-> select();
		$return['code'] = 10000;
		$return['data'] = $info;
		$return['number'] = $number;
		$return['msg'] = 'ok';
		return json($return);
	}

	public function getCustomInfo(){
	    $info = db('custom')
            -> field("id,nickname,username,wallet,expense,app_num,register_time,is_agency_user")
            -> where("id",$this->custom->id)
            -> find();
        $return['code'] = 10000;
        $return['data'] = $info;
        $return['msg'] = 'ok';
        return json($return);
    }

    /**
     * 获取小程序的价格  无用代码
     */
    public function getAppMoney(){
        if(!isset($this -> data['appid'])){
            $return['code'] = 10001;
            $return['msg'] = '缺少app的唯一标识,获取列表的时候传递过去的appid';
            return json($return);
        }
        if(!preg_match("/^\d{8}$/",$this -> data['appid'])){
            $return['code'] = 10002;
            $return['msg'] = 'appid是一个8位数';
            return json($return);
        }
        $type = db("app") -> getFieldByAppid($this->data['appid'],'type');
        $type = get_app($type);
        $return['code'] = 10000;
        $return['data'] = $type['fee'];
        $return['msg'] = 'ok';
        return json($return);
    }


    /*小程序下线*/
    public function  update_app_state(){

        if(!isset($this -> data['appid'])){
            $return['code'] = 10001;
            $return['msg'] = '缺少app的唯一标识';
            return json($return);
        }
        if(!preg_match("/^\d{8}$/",$this -> data['appid'])){
            $return['code'] = 10002;
            $return['msg'] = 'appid是一个8位数';
            return json($return);
        }

        if(!isset($this -> data['is_publish'])){
            $return['code'] = 10003;
            $return['msg'] = '参数丢失';
            return json($return);
        }

        //判断当前用户的小程序是否有这个appid
        $info = db('app')  ->  where(['appid' => $this -> data['appid']]) -> find();
        if($info['custom_id'] != $this->custom->id){
            $return['code'] = 10004;
            $return['msg'] = '这个app不是这个用户的';
            return json($return);
        }
        if($info['is_publish'] != 4 ){
            $return['code'] = 10004;
            $return['msg'] = '小程序未上线,不能进行此操作';
            return json($return);
        }else{

            $res = db('app')
                ->  where(['appid' => $this -> data['appid']])
                -> update(['is_publish' => $this->data['is_publish']]);
            if($res){
                $return['code'] = 10000;
                $return['msg'] = '成功下线';
                return json($return);
            }else{
                $return['code'] = 10001;
                $return['msg'] = '失败';
                return json($return);
            }
        }


    }


}


 ?>