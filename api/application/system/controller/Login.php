<?php
namespace app\system\controller;

/*******超级管理员后台接口*******/
/*******登录，注册接口*******/
class Login{
    /**
     * 登录请求接口
     */
	public function index(){
		$username = input('get.username','','htmlspecialchars');
		$password = input('get.password','','htmlspecialchars');

		if(!$username || !$password){
		    $arr['code'] = 10001;$arr['msg'] = '账号或密码为空';$arr['msg_test'] = '账号或密码为空';
		    return json($arr);
        }
        $info = db('system') -> where('username',$username) -> select();
		if(!$info){
            $arr['code'] = 10002;$arr['msg'] = '账号错误';$arr['msg_test'] = '账号或密码错误';
            return json($arr);
        }
        if($info[0]['password'] == xgmd5($password)){
		    $data['lastIp '] = $info[0]['nowIp'];
		    $data['nowIp'] = $_SERVER["REMOTE_ADDR"];
		    $data['lastTime'] = $info[0]['nowTime '];
		    $data['nowTime'] = strtotime('Y年m月d日H:i:s',time());
            $res = db('system') -> where('username',$username) -> update($data);
            if($res){
                $arr['code'] = 10000;$arr['msg'] = '登录成功';$arr['msg_test'] = '登录成功';
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