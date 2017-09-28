<?php
namespace app\custom\controller;

/*客户个人信息相关操作*/
class Own extends Action{
	//返回拥有的小程序列表

	/**
	 * 获取小程序的列表
	 */
	public function getUserAppList(){
		//查询当前用户是否存在
		$info = db('app') -> where("custom_id",$this->custom->id) -> select();
		$arr['code'] = 10000;$arr['msg'] = '获取成功';$arr['msg_test'] = '获取成功';$arr['data'] = $info;
		return json($arr);
		
	}

	/**
	 * 用户创建小程序
	 */
	public function createUserApp(){
		$data = input("post.",'','htmlspecialchars');
		$data['custom_id'] = $this -> custom -> id;
		if(!$data){
			$return['code'] = 10001;
			$return['msg'] = '参数不存在';
			$return['msg_test'] = '参数不存在';
			return json($return);
		}
		if(!isset($data['type']) || !isset($data['custom_id'])){
			$return['code'] = 10002;
			$return['msg'] = '参数值缺失';
			$return['msg_test'] = '参数值缺失';
			return json($return);
		}
		if(!get_app($data['type'])){
			$return['code'] = 10003;
			$return['msg'] = '小程序不存在';
			$return['msg_test'] = '小程序不存在';
			return json($return);
		}

		//判断当前用户是否可以在创建小程序
		$maxappnum = db('custom') -> getFieldByid($data['custom_id'],'max_app_num');
		$app_num = db('custom') -> getFieldByid($data['custom_id'],'app_num');
		if($app_num >= $maxappnum){
			$return['code'] = 10006;
			$return['msg'] = '当前用户小程序数据已满';
			$return['msg_test'] = '当前用户小程序数据已满';
			return json($return);
		}
		//获取当前用户随机的字符串
		$flag = true;
		while($flag){
			$appid = $this -> getNumber();
			$res = db('app') -> where("appid = :appid",['appid' => $appid]) -> select();
			if(!$res){
				$flag = false;
				$data['appid'] = $appid;
			}
		}
		$app_info = get_app($data['type']);
		$data['name'] = $app_info['name'];
		$data['pic'] = $app_info['pic'];
		$data['create_time'] = time();
		$data['try_time'] = time() + 60 * 60;
		$data['use_time'] = time() + 60 * 60;
		$id = db('app') -> insertGetid($data);
		if($id){
			//更新用户的数据表
			db('custom') -> where("id",$data['custom_id']) -> setInc('app_num',1);
			$return['code'] = 10000;
			$return['data'] = ['id' => $id,'appid' => $data['appid'],'try_time' => $data['try_time']];
			$return['msg'] = '添加成功';
			$return['msg_test'] = '添加成功';
			return json($return);
		}else{
			$return['code'] = 10007;
			$return['msg'] = '添加失败';
			$return['msg_test'] = '添加失败';
			return json($return);
		}
	}

	private function getNumber(){
		return mt_rand(10000000,99999999);
	}

<<<<<<< HEAD
    /**
     * 小程序购买
     */
    public function buyUserApp(){
        $this -> data['custom_id'] = $this -> custom -> id;
        if(!isset($this -> data['appid'])){
            $return['code'] = 10004;
            $return['msg_test'] = '缺少app的唯一标识,获取列表的时候传递过去的appid';
            return json($return);
        }
        if(!preg_match("/^\d{8}$/",$this -> data['appid'])){
            $return['code'] = 10005;
            $return['msg_test'] = 'appid是一个8位数';
            return json($return);
        }
        //判断当前用户的小程序是否有这个appid
        $is_true = db('app') -> where(['custom_id' => $this -> data['custom_id'],'appid' => $this -> data['appid']]) -> select();
        if(!$is_true){
            $return['code'] = 10006;
            $return['msg_test'] = '这个客户没有创建这个小程序';
            return json($return);
        }
        $app_type = $is_true[0]['type'];
        $app_info = get_app($app_type);
        if(!$app_info){
            $return['code'] = 10007;
            $return['msg_test'] = '小程序类型不存在';
            return json($return);
        }
        $fee = $app_info['fee'];
        if(!$fee || $fee <= 0){
            $return['code'] = 10008;
            $return['msg'] = '小程序价格错误';
            return json($return);
        }
        //判断用户是否有钱
        $userMoney = db('custom') -> getFieldByid($this -> data['custom_id'],'wallet');
        if($fee > $userMoney){
            $return['code'] = 10009;
            $return['msg'] = '用户余额不足';
            return json($return);
        }
=======
	/**
	 * 小程序购买
	 */
	public function buyUserApp(){
		$data = input("post.",'','htmlspecialchars');
		$data['custom_id'] = $this -> custom -> id;
		if(!$data){
			$return['code'] = 10001;
			$return['msg'] = '参数不存在';
			$return['msg_test'] = '参数不存在';
			return json($return);
		}
		if(!isset($data['custom_id'])){
			$return['code'] = 10002;
			$return['msg'] = '用户id不存在';
			$return['msg_test'] = '用户id不存在';
			return json($return);
		}

		if(!isset($data['appid'])){
			$return['code'] = 10004;
			$return['msg'] = 'appid不存在';
			$return['msg_test'] = '缺少app的唯一标识,获取列表的时候传递过去的appid';
			return json($return);
		}
		if(!preg_match("/^\d{8}$/",$data['appid'])){
			$return['code'] = 10005;
			$return['msg'] = 'appid格式错误';
			$return['msg_test'] = 'appid是一个8位数';
			return json($return);
		}
		//判断当前用户的小程序是否有这个appid
		$is_true = db('app') -> where(['custom_id' => $data['custom_id'],'appid' => $data['appid']]) -> select();
		if(!$is_true){
			$return['code'] = 10006;
			$return['msg'] = '当前用户没有此小程序';
			$return['msg_test'] = '这个客户没有创建这个小程序';
			return json($return);
		}
		$app_type = $is_true[0]['type'];
		$app_info = get_app($app_type);
		if(!$app_info){
			$return['code'] = 10007;
			$return['msg'] = '当前小程序类型不存在';
			$return['msg_test'] = '小程序类型不存在';
			return json($return);
		}
		$fee = $app_info['fee'];
		if(!$fee || $fee <= 0){
			$return['code'] = 10008;
			$return['msg'] = '小程序价格错误';
			$return['msg_test'] = '小程序价格错误';
			return json($return);
		}
		//判断用户是否有钱
		$userMoney = db('custom') -> getFieldByid($data['custom_id'],'wallet');
		if($fee > $userMoney){
			$return['code'] = 10009;
			$return['msg'] = '用户余额不足';
			$return['msg_test'] = '用户余额不足';
			return json($return);
		}
>>>>>>> 7e777ec4c5ea51ddac21b4c32d5b794bfae75533

		$res = db('custom') -> where(['id' => $data['custom_id']]) -> setDec('wallet',$fee);
		if($res){
			$info['update_time'] = time();

			db('app') -> where(['custom_id' => $data['custom_id'],'appid' => $data['appid']]) -> update($info);
			//判断当前购买的时间是否在过期
			$use_time = db('app') -> where(['custom_id' => $data['custom_id'],'appid' => $data['appid']]) -> value('use_time');
			$year_time = strtotime('1 year');
			if($use_time > time()){
				//还没过，应该在原来的基础上添加
				db('app') -> where(['custom_id' => $data['custom_id'],'appid' => $data['appid']*1]) -> setInc('use_time',$year_time - time());
			}else{
				//过期了，在现在的时间上添加
				db('app') -> where(['custom_id' => $data['custom_id'],'appid' => $data['appid']]) -> setField('use_time',$year_time);
			}

			db('app') -> where(['custom_id' => $data['custom_id'],'appid' => $data['appid']]) -> setInc('fee',$fee);

<<<<<<< HEAD
            db('custom') -> where(['id' => $this -> data['custom_id']]) -> setInc('expense',$fee);
            $use_time = db('app') -> where(['custom_id' => $this -> data['custom_id'],'appid' => $this -> data['appid']]) -> value('use_time');
            $return['code'] = 10000;
            $return['data'] = ['use_time' => $use_time];
            $return['msg'] = '购买成功';
            return json($return);
        }else{
            $return['code'] = 10010;
            $return['msg'] = '更新数据失败';
            return json($return);
        }
=======
			db('custom') -> where(['id' => $data['custom_id']]) -> setInc('expense',$fee);
			$use_time = db('app') -> where(['custom_id' => $data['custom_id'],'appid' => $data['appid']]) -> value('use_time');
			$return['code'] = 10000;
			$return['data'] = ['use_time' => $use_time];
			$return['msg'] = '购买成功';
			$return['msg_test'] = '成功';
			return json($return);
		}else{
			$return['code'] = 10010;
			$return['msg'] = '更新数据失败';
			$return['msg_test'] = '更新数据失败';
			return json($return);
		}
>>>>>>> 7e777ec4c5ea51ddac21b4c32d5b794bfae75533

	}


<<<<<<< HEAD
    /**
     * 删除小程序
     */
    public function delUserApp(){
        if(!isset($this -> data['appid'])){
            $return['code'] = 10001;
            $return['msg_test'] = '缺少app的唯一标识,获取列表的时候传递过去的appid';
            return json($return);
        }
        if(!preg_match("/^\d{8}$/",$this -> data['appid'])){
            $return['code'] = 10002;
            $return['msg_test'] = 'appid是一个8位数';
            return json($return);
        }
        $res = db('app') -> where(['appid' => $this -> data['appid'],'custom_id' => $this->custom->id]) -> setField('is_del',1);
        if($res){
            $return['code'] = 10000;
            $return['msg'] = '删除成功';
            $return['msg_test'] = '删除成功';
            return json($return);
        }else{
            $return['code'] = 10003;
            $return['msg'] = '删除失败';
            $return['msg_test'] = 'appid可能传递错了或者这个小程序不是当前用户的';
            return json($return);
        }
=======
	/**
	 * 删除小程序
	 */
	public function delUserApp(){
		$data = input('post.','','htmlspecialchars');
		if(!isset($data['appid'])){
			$return['code'] = 10001;
			$return['msg'] = '参数值缺失';
			$return['msg_test'] = '缺少app的唯一标识,获取列表的时候传递过去的appid';
			return json($return);
		}
		if(!preg_match("/^\d{8}$/",$data['appid'])){
			$return['code'] = 10002;
			$return['msg'] = 'appid格式错误';
			$return['msg_test'] = 'appid是一个8位数';
			return json($return);
		}
		$res = db('app') -> where(['appid' => $data['appid'],'custom_id' => $this->custom->id]) -> setField('is_del',1);
		if($res){
			$return['code'] = 10000;
			$return['msg'] = '删除成功';
			$return['msg_test'] = '删除成功';
			return json($return);
		}else{
			$return['code'] = 10003;
			$return['msg'] = '删除失败';
			$return['msg_test'] = 'appid可能传递错了或者这个小程序不是当前用户的';
			return json($return);
		}
>>>>>>> 7e777ec4c5ea51ddac21b4c32d5b794bfae75533

	}


<<<<<<< HEAD
    /**
     * 获取小程序的基本信息
     * appid
     */
    public function getAppInfo(){
        if(!isset($this -> data['appid'])){
            $return['code'] = 10001;
            $return['msg'] = '参数值缺失';
            $return['msg_test'] = '缺少app的唯一标识,获取列表的时候传递过去的appid';
            return json($return);
        }
        if(!preg_match("/^\d{8}$/",$this -> data['appid'])){
            $return['code'] = 10002;
            $return['msg'] = 'appid格式错误';
            $return['msg_test'] = 'appid是一个8位数';
            return json($return);
        }
        //判断当前用户的小程序是否有这个appid
        $info = db('app') -> field('name,pic,desc,tel,site_url,address,is_publish') ->  where(['custom_id' => $this->custom -> id,'appid' => $this -> data['appid']]) -> select();
        if($info){
            $return['code'] = 10000;
            $return['data'] = $info;
            $return['msg'] = '';
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
            $return['code'] = 10001;
            $return['msg_test'] = '缺少appid或name或tel';
            return json($return);
        }
        if(!preg_match("/^\d{8}$/",$this -> data['appid'])){
            $return['code'] = 10002;
            $return['msg'] = 'appid格式错误';
            $return['msg_test'] = 'appid是一个8位数';
            return json($return);
        }

        $res = model('app') -> where(['appid' => $this -> data['appid'],'custom_id' => $this->custom->id]) -> allowField(true) -> update($this -> data);
        if($res){
            $return['code'] = 10000;
            $return['msg'] = '修改成功';
            $return['msg_test'] = '修改成功';
            return json($return);
        }else{
            $return['code'] = 10005;
            $return['msg'] = '修改失败';
            $return['msg_test'] = '参数可能传递错了,或者这个小程序不是这个用户的';
            return json($return);
        }
=======
	/**
	 * 获取小程序的基本信息
	 * appid
	 */
	public function getAppInfo(){
		$data = input('post.','','htmlspecialchars');
		if(!isset($data['appid'])){
			$return['code'] = 10001;
			$return['msg'] = '参数值缺失';
			$return['msg_test'] = '缺少app的唯一标识,获取列表的时候传递过去的appid';
			return json($return);
		}
		if(!preg_match("/^\d{8}$/",$data['appid'])){
			$return['code'] = 10002;
			$return['msg'] = 'appid格式错误';
			$return['msg_test'] = 'appid是一个8位数';
			return json($return);
		}
		//判断当前用户的小程序是否有这个appid
		$info = db('app') -> field('name,pic,desc,tel,site_url,address') ->  where(['custom_id' => $this->custom -> id,'appid' => $data['appid']]) -> select();
		if($info){
			$return['code'] = 10000;
			$return['data'] = $info;
			$return['msg'] = '';
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
		$data = input('post.','','htmlspecialchars');
		if(!isset($data['appid'])){
			$return['code'] = 10001;
			$return['msg'] = '参数值缺失';
			$return['msg_test'] = '缺少app的唯一标识,获取列表的时候传递过去的appid';
			return json($return);
		}
		if(!preg_match("/^\d{8}$/",$data['appid'])){
			$return['code'] = 10002;
			$return['msg'] = 'appid格式错误';
			$return['msg_test'] = 'appid是一个8位数';
			return json($return);
		}
		if(!isset($data['name'])){
			$return['code'] = 10003;
			$return['msg'] = '请填写小程序名字';
			$return['msg_test'] = '请填写小程序名字';
			return json($return);
		}
		if(!isset($data['tel'])){
			$return['code'] = 10004;
			$return['msg'] = '请填写正确的客服电话';
			$return['msg_test'] = '客服电话没填';
			return json($return);
		}
		$res = db('app') -> where(['appid' => $data['appid'],'custom_id' => $this->custom->id]) -> update($data);
		if($res){
			$return['code'] = 10000;
			$return['msg'] = '修改成功';
			$return['msg_test'] = '修改成功';
			return json($return);
		}else{
			$return['code'] = 10005;
			$return['msg'] = '修改失败';
			$return['msg_test'] = '参数可能传递错了,或者这个小程序不是这个用户的';
			return json($return);
		}
>>>>>>> 7e777ec4c5ea51ddac21b4c32d5b794bfae75533

	}





}

 ?>