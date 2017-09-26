<?php
namespace app\custom\controller;

/*基础信息获取*/
class Base{
	function app_type(){
		return json(get_app('all'));
	}
	//dengdeng
}

 ?>