<?php
namespace app\system\controller;
use think\Session;
/*******超级管理员后台接口*******/
/*******登录，注册接口*******/
class Login{
    /**
     * 登录请求接口
     */


	public function index(){
        header('Access-Control-Allow-Origin:https://weapp.xiguawenhua.com');
		$username = input('post.username','','htmlspecialchars');
		$password = input('post.password','','htmlspecialchars');

		if(!$username || !$password){
		    $arr['code'] = 10001;$arr['msg'] = '账号或密码为空';$arr['msg_test'] = '账号或密码为空';
		    return json($arr);
        }
        $info = db('system') -> where(['username'=>$username,'password'=>xgmd5($password)]) -> find();
		if(!$info){
            $arr['code'] = 10002;$arr['msg'] = '账号错误';$arr['msg_test'] = '账号或密码错误';
            return json($arr);
        }

        if($info['password'] == xgmd5($password)){
		    $data['lastIp'] = $info['nowIp'];
		    $data['nowIp'] = $_SERVER["REMOTE_ADDR"];
		    $data['lastTime'] = $info['nowTime'];
		    $data['nowTime'] = date('Y年m月d日H:i:s',time());
            $res = db('system') -> where('username',$username) -> update($data);
            if($res){
                $data['id'] = $info['id'];
                $data['username'] = $username;
                $data['password'] = $password;
                $data['is_agency_user'] = $info['is_agency_user'];

                session('admin',$data);

                $arr['code'] = 10000;$arr['msg'] = '登录成功' ;

                $arr['admin'] = ['is_agency_user'=>$data['is_agency_user'],['username'=>$data['username']]];
                return json($arr);
            }else{
                $arr['code'] = 10004;$arr['msg'] = '网络错误';$arr['msg_test'] = '网络错误';
                return json($arr);
            }
        }else{
            $arr['code'] = 10003;$arr['msg'] = '密码错误';$arr['msg_test'] = '账号或密码错误';
            return json($arr);
        }
	}
} 
 ?>