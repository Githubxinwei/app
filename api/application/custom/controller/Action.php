<?php
namespace app\custom\controller;
use think\Controller;
class Action extends Controller{

	function _initialize(){
//		$this->custom = model('custom') -> where('id',2) -> find();
		$this -> data = input("post.",'','htmlspecialchars');
		if(!isset($this->data['session_key'])){
			$return['code'] = 0;
			$return['msg'] = '登录超时，请重新登录';
			$return['msg_test'] = '请登录';
			echo json_encode($return);exit;
		}
		
		session_id($this->data['session_key']);
		$this->custom = session('custom');
		if(!$this->custom){
			$return['code'] = 0;
			$return['msg'] = '登录超时，请重新登录';
			$return['msg_test'] = '请登录';
			echo json_encode($return);exit;
		}
<<<<<<< HEAD
		$this->custom = model('custom') -> where('id',$this->custom['id']) -> find();
		
=======
		$this->custom = model('custom') -> find($this->custom->id);
>>>>>>> 71ac59a9a36575e3703e27abef210ac58644f47d
	}
}




 ?>