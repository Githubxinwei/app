<?php
namespace app\custom\controller;
use think\Controller;
class Action extends Controller{

	function _initialize(){
		//判断用户是否已登录
		$this->custom_id = 1;
	}
}




 ?>