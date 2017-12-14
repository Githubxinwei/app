<?php
namespace app\custom\controller;

use think\db;

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
		$info = db('custom') -> where('username',$username) -> find();
		if(!$info){
			$arr['code'] = 10002;$arr['msg'] = '账号或密码错误';$arr['msg_test'] = '账号错误';
			return json($arr);
		}
		if($info['password'] == xgmd5($password)){
		    if($info['is_forbidden'] == 1){
                $arr['code'] = 10003;$arr['msg'] = '当前账户已禁用';$arr['msg_test'] = '当前账户已禁用';
                return json($arr);
            }
			$data['last_time'] = time();
			$data['ip'] = $_SERVER["REMOTE_ADDR"];
			$res = db('custom') -> where('username',$username) -> update($data);
			if($res){
                $session_key = session('custom',$info);
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
		//判断验证码是否正确
        $this -> verifyMsgCode($data['username'],$data['code']);
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

		if($data['is_agency_user'] == 1 ){
            $data['is_agency'] = 1;
        }
		$res = model('custom') -> allowField(true) -> save($data);
		if($res){
			$arr['code'] = 10000;$arr['msg'] = '注册成功';$arr['msg_test'] = '注册成功';
			return json($arr);
		}else{
			$arr['code'] = 10004;$arr['msg'] = '网络错误';$arr['msg_test'] = '添加数据失败';
			return json($arr);
		}

	}

    /**
     * 发送手机验证码
     *
     */
    public function sendMsg(){
        $this -> data = input("post.",'','htmlspecialchars');
        if(!isset($this->data['tel'])){
            $return['code'] = 10001;
            $return['msg_test'] = '传入手机号';
            return json($return);
        }
        if(!preg_match("/^1[3|4|5|7|8][0-9]{9}$/",$this -> data['tel'])){
            $return['code'] = 10002;
            $return['msg_test'] = '格式不正确';
            return json($return);
        }
        $code = mt_rand(100000,999999);
        $param = "code:{$code}";
        $code = sendMsgInfo($this->data['tel'],$param);
        if($code == 0000){
            $return['code'] = 10000;
            $return['msg'] = '发送成功';
            file_cache($this->data['tel'] . '.php',$code,120);
            return json($return);
        }else{
            $return['code'] = 10003;
            $return['msg_test'] = $code;
            return json($return);
        }
    }

    private function verifyMsgCode($tel,$code1){
        $code = file_cache($tel . '.php');
        if(!$code){
            $return['code'] = 10003;
            $return['msg_test'] = '验证码失效,请重新获取';
            return json($return);
        }
        $msg = $code1;
        if($code == $msg){
            $return['code'] = 10000;
            $return['msg_test'] = 'ok';
            return json($return);
        }else{
            $return['code'] = 10004;
            $return['msg'] = '验证码不正确';
            return json($return);
        }
    }


	public  function  forget(){

        $data = input("post.",'','htmlspecialchars');
        $data['password'] = xgmd5($data['password']);

        if(!isset($data['username']) || !isset($data['password'])){
            $arr['code'] = 10001;$arr['msg'] = '请传递账户或密码';$arr['msg_test'] = '请传递账户或密码';
            return json($arr);
        }
        if(!preg_match("/^1[34578]{1}\d{9}$/",$data['username'])){
            $arr['code'] = 10005;$arr['msg'] = '手机号格式不正确';$arr['msg_test'] = '手机号格式不正确';
            return json($arr);
        }
        //判断验证码是否正确
        $this -> verifyMsgCode($data['username'],$data['code']);
        //判断是否手机号是否存在
        $is_register = db('custom') -> where("username",$data['username']) -> find();
        if(!$is_register){
            $arr['code'] = 10003;$arr['msg'] = '手机号不存在';$arr['msg_test'] = '手机号不存在';
            return json($arr);
        }

        $res = db("custom")->where(['id' => $is_register['id'],'username' => $data['username']])->setField('password',$data['password']);
        if($res){
            $arr['code'] = 10000;$arr['msg'] = '密码修改成功';$arr['msg_test'] = '修改成功';
            return json($arr);
        }else{
            $arr['code'] = 10004;$arr['msg'] = '密码修改失败';$arr['msg_test'] = '修改失败';
            return json($arr);
        }


    }

}

 ?>