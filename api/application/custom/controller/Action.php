<?php
namespace app\custom\controller;
use think\Controller;
class Action extends Controller{

	function _initialize(){
		$data = session('custom');
		if(!$data){
            $return['code'] = 0;
            $return['msg'] = '请登录';
            $return['msg_test'] = '请登录';
            halt($return);
        }
        $this -> data = $data;
	}
}




 ?>