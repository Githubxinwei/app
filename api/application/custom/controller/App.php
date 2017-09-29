<?php
namespace app\custom\controller;
use think\Controller;
class App extends Controller{
	function _initialize(){
		parent::_initialize();
		$this->data = input("post.",'','htmlspecialchars');
		 if(!isset($this->data['appid'])){
		     $return['code'] = 10001; $return['msg_test'] = 'post参数appid不能为空';
		     echo json_encode($return);exit;
		}
		// else{
		//     // $auth = model('auth_info') -> where('apps',$this->data['appid'])->find();
		//     // if(!$auth || $this->custom['id'] != $auth['custom_id']){
		//     //     $return['code'] = 10002; $return['msg_test'] = '没有查询到要操作的app的授权信息';
		//     //     echo json_encode($return);exit;
		//     // }
		//     // $this->auth = $auth;
			
		// }
		$this->apps = '81435254';
	}
	//获取商品列表
	function lists(){
		$where['appid'] = $this->apps;
		if(isset($this->data['page'])){$page = $this->data['page'];}else{$page = 1;}
		if(isset($this->data['limit_num'])){$limit_num = $this->data['limit_num'];}else{$limit_num = 20;}
		$info = model('goods') -> field('id,name,pic,price,stock') -> where($where)->page($page) ->limit($limit_num) -> order('code desc')->select();
		$return['code'] = 10000;$return['data'] = $info;
		return json($return);
	}
	//获取单个商品信息
	function get_one(){
		if(!isset($this->data['id'])){
			$return['code'] = 10002;$return['msg_test'] = '商品不存在';
			return json($return);
		}
		$info = model('goods') -> field('id,name,pic,price,stock,desc,content_show,content,appid') -> where('id',$this->data['id'])->find();
		if($info['appid'] != $this->apps){
			$return['code'] = 10003;$return['msg_test'] = '商品不属于该商户';
			return json($return);
		}
		unset($info['appid']);
		$return['code'] = 10000;$return['data'] = $info;
		return json($return);
	}
	
}



 ?>