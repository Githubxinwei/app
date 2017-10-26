<?php
namespace app\custom\controller;

/**********客户登录，注册，找回密码等操作***********/
class First{

	function login(){
		$username = input('post.username','','htmlspecialchars');
		$password = input('post.password','','htmlspecialchars');
		if(!$username || !$password){
			$arr['code'] = 10001;$arr['msg'] = '账号或密码为空';$arr['msg_test'] = '账号或密码为空';
			return json($arr);
		}
		if(!preg_match("/^1[34578]{1}\d{9}$/",$username)){
			$arr['code'] = 10005;$arr['msg'] = '手机号格式不正确';$arr['msg_test'] = '手机号格式不正确';
			return json($arr);
		}
		$info = db('custom') -> where('username',$username) -> select();
		if(!$info){
			$arr['code'] = 10002;$arr['msg'] = '账号或密码错误';$arr['msg_test'] = '账号错误';
			return json($arr);
		}
		if($info[0]['password'] == xgmd5($password)){
			$data['last_time'] = time();
			$data['ip'] = $_SERVER["REMOTE_ADDR"];
			$res = db('custom') -> where('username',$username) -> update($data);
			if($res){
                $session_key = session('custom',$info[0]);
				$arr['code'] = 10000;$arr['msg'] = '登录成功';
				$arr['msg_test'] = '登录成功';
                $arr['data'] = ['session_key'=>$session_key];
				return json($arr);
			}else{
				$arr['code'] = 10004;$arr['msg'] = '网络错误';$arr['msg_test'] = '网络错误';
				return json($arr);
			}
		}else{
			$arr['code'] = 10003;$arr['msg'] = '账号或密码错误';$arr['msg_test'] = '密码错误';
			return json($arr);
		}
	}

	public function register(){
		$data = input("post.",'','htmlspecialchars');
		if(!isset($data['username']) || !isset($data['password'])){
			$arr['code'] = 10001;$arr['msg'] = '请传递账户或密码';$arr['msg_test'] = '请传递账户或密码';
			return json($arr);
		}
		if(!preg_match("/^1[34578]{1}\d{9}$/",$data['username'])){
			$arr['code'] = 10002;$arr['msg'] = '手机号格式不正确';$arr['msg_test'] = '手机号格式不正确';
			return json($arr);
		}
		//判断是否手机号已注册
		$is_register = db('custom') -> where("username",$data['username']) -> select();
		if($is_register){
			$arr['code'] = 10003;$arr['msg'] = '手机号已被注册';$arr['msg_test'] = '手机号已被注册';
			return json($arr);
		}
		//保存信息
		$data['ip'] = $_SERVER["REMOTE_ADDR"];
		$data['register_time'] = time();
		$data['password'] = xgmd5($data['password']);
		//默认的每个用户的初始金额是10000元
		$data['wallet'] = 10000;
		$res = model('custom') -> allowField(true) -> save($data);
		if($res){
			$arr['code'] = 10000;$arr['msg'] = '注册成功';$arr['msg_test'] = '注册成功';
			return json($arr);
		}else{
			$arr['code'] = 10004;$arr['msg'] = '网络错误';$arr['msg_test'] = '添加数据失败';
			return json($arr);
		}

	}


	public  function  forget(){

        $data = input("post.",'','htmlspecialchars');

        if(!isset($data['username']) || !isset($data['password'])){
            $arr['code'] = 10001;$arr['msg'] = '请传递账户或密码';$arr['msg_test'] = '请传递账户或密码';
            return json($arr);
        }
        if(!preg_match("/^1[34578]{1}\d{9}$/",$data['username'])){
            $arr['code'] = 10002;$arr['msg'] = '手机号格式不正确';$arr['msg_test'] = '手机号格式不正确';
            return json($arr);
        }
        //判断是否手机号已注册
        $is_register = db('custom') -> where("username",$data['username']) -> select();
        if(!$is_register){
            $arr['code'] = 10003;$arr['msg'] = '手机号存在';$arr['msg_test'] = '手机号不存在';
            return json($arr);
        }

        $this->data['id'] = $is_register['id'];
        $res = Db("custom")->where()->save();



    }

}

 ?>