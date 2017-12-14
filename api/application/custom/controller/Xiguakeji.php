<?php
namespace app\custom\controller;
use think\Controller;

/******************处理小程序端请求的合法性验证*************************
		公司网址：http://www.xiguakeji.cc
********************2017年10月8日 郑州西瓜科技***********************/

class Xiguakeji extends Controller{

	function _initialize(){
		session_start();
		$session_key = session_id();
		$this->data = input("post.",'','htmlspecialchars');
		if(!$this->data){$this->data = $_GET;}//仅测试使用，待删
		if(!session('user')){$return['code'] = 0;$return['msg_test'] = '用户登录已过期';echo json_encode($return);exit;}
		if(!$session_key ){
			$return['code'] = 0;$return['msg_test'] = '用户登录已过期';echo json_encode($return);exit;
		}elseif(!isset($this->data['session_key'])){
			$return['code'] = 10001;$return['msg_test'] = 'session_key不能为空';echo json_encode($return);exit;
		}elseif($this->data['session_key'] != $session_key ){
			$return['code'] = 0;$return['msg_test'] = '用户登录已过期，值有誤';echo json_encode($return);exit;
		}

		if(!isset($this->data['apps'])){
		     $return['code'] = 10001; $return['msg_test'] = 'post参数apps不能为空';echo json_encode($return);exit;
		}
		$this->apps = $this->data['apps'];//当前要操作的小程序的8位识别号
		$this->user = session('user');//当前发起请求的用户，数组形式，是user表返回信息，内含有id字段
        //判断当前小程序是否禁用
        $is_forbidden = db('app') -> field('is_forbidden,is_publish') -> where(['appid' => $this->apps]) -> find();
        if($is_forbidden['is_forbidden'] == 1){
            $return['code'] = 11111;$return['msg_test'] = '当前小程序已被禁用';echo json_encode($return);exit;
        }
//        if($is_forbidden['is_publish'] != 4){
//            $return['code'] = 11111;$return['msg_test'] = '当前小程序未上线';echo json_encode($return);exit;
//        }


		$this->user = model('user') -> where('id',$this->user['id']) -> find();
		if(!$this->user || $this->user->apps != $this->apps){
			$return['code'] = 0;$return['msg_test'] = '用户登录已过期,查无此人';echo json_encode($return);exit;
		}else if($this->user -> is_forbidden == 1){
            $return['code'] = 11110;$return['msg_test'] = '当前用户已被禁用';echo json_encode($return);exit;
        }
	}
}