<?php
namespace app\custom\controller;
use think\Controller;
class Action extends Controller{

	function _initialize(){
		$this->custom = model('custom') -> where('id',2) -> find();
		//$data = session('custom');
		if(!$this->custom){
            $return['code'] = 0;
            $return['msg'] = '登录超时，请重新登录';
            $return['msg_test'] = '请登录';
            echo json_encode($return);exit;
        }
	}
}




 ?>