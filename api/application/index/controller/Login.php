<?php 
namespace app\index\controller;
/* *
 * 类名：Client
 * 功能：处理用户登录，注册，验证码发送
 * 版本：2.0
 * 日期：2017年9月6日17:41:04
 * 西瓜科技版权所有，于春风 西瓜科技网址 http://www.xiguakeji.cc，Tel：0371-55086535
 */

class Login{
	/*用户登录，使用手机号和密码登录*/
	function index(){
		
		if(!isset($_GET['login_name']) || !isset($_GET['pwd']) ){
			return json(['errcode'=>10001]);
		}
		$user = model('User');
		$info = $user->get_user_info($_GET['login_name']);
		if(!isset($info->login_pass) || $info->login_pass != xgmd5($_GET['pwd'])){
			return json(['errcode'=>10002]);
		}else{
			//生成浏览器缓存，记录用户登录信息
			session('user',$info->id);
			return  json(['errcode'=>10000]);
		}
		
	}
	
	/*用户注册,手机号，验证码，昵称，头像，密码是否一致，上级关系*/
	function reg(){
		$_POST = array(
			'login_name'=>'18538739007',
			'pwd1'=>'18538739007',
			'pwd2'=>'18538739007',
			'parent'=>'1'
		);
		if( !isset($_POST['login_name']) || !isset($_POST['code']) || !isset($_POST['pwd1']) || !isset($_POST['pwd2']) || !isset($_POST['parent']) ){
			return json(['errcode'=>10001]);
		}
		if( !$this->check_code() ){
			return json(['errcode'=>10002]);
		}

		//$_POST['']
		return json(['errcode'=>10000]);
	}
	/*发送验证码*/
	function send_code(){
		$act =  !empty($_REQUEST['act']) ? $_REQUEST['act'] : 'reg';//act 等于 set_pwd，默认等于reg
		if(!isset($_GET['login_name']) ){
			return json(['errcode'=>10001]);
		}
		/*检测手机号是否注册过*/
		$user = model('User');
		$info = $user ->get_user_info($_GET['login_name']);
		if(isset($info->id) && $act == 'reg'){return json(['errcode'=>10002]);}//手机注册过
		if( session('code_time')+ 120 > time() ){
			$left_time =120 -( time() - session('code_time') );
			return json(['errcode'=>$left_time]);
		}
		$code = rand(1000,9999);
		$res = msg_everify('18538739007',$code);//发送验证码
		$errcode = $res ? 10000 : 10003;
		if($errcode == 10000){
			session('code',$code);
			session('code_time',time());
		}	
		return json(['errcode'=>$errcode]);
	}
	// 验证验证码
	private function check_code(){
		return true;
		$mobile = !empty($_REQUEST['mobile']) ? $_REQUEST['mobile'] : $_REQUEST['login_name'];
		if(!empty($_POST['code']) && $_POST['code'] == session('code') && $mobile == session('mobile')){
			return true;
		}
		else return false;
	}
}

 ?>