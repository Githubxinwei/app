<?php
namespace app\custom\controller;

/*客户个人信息相关操作*/
class Own extends Action{
	//返回拥有的小程序列表
	function app(){
		echo $this->custom_id;
	}
	//等等
}

 ?>