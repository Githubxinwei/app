<?php 
namespace app\index\controller;
use think\Controller;
class Action extends Controller{
	function _initialize(){
		//判断用户session存不存在
		//dump(session('id'));die();
		session('id',1);
		if(!session('id')){
			if( $this->is_weixin()){
				echo 'weixin';
			}else{
				echo 'wap';
			}
			echo '该登陆了';die();
		}else{
			$this->user = model('user')->where('id',session('id'))->find();
			session('id',$this->user->id);
		}
		$this->_load_config();
	}
	function is_weixin(){
		if( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
        			return true;
    		}  
       		 return false;
	}

	// 加载配置
	protected function _load_config(){
		$_CFG = file_cache('sys_config','','');
		if(empty($_CFG)){
			$config = model('config') -> select();
			if(!is_array($config)){
				die('请先在后台设置好各参数');
			}
			foreach($config as $v){
				$_CFG[$v['name']] = unserialize($v['value']);
			}
			
			unset($config);			
			file_cache('sys_config',$_CFG,'');
		}
		// 循环将配置写道成员变量
		foreach($_CFG as $k => $v){
			$key  = '_'.$k;
			$this -> $key = $v;
		}
		$GLOBALS['_CFG'] = $_CFG;		// 保存到全局变量
	}
	
}

 ?>