<?php
namespace Weixin\Controller;
use Think\Controller;

class WeixinController extends Controller{

	function __construct($token){
		parent::__construct();
		$auth_info = M('auth_info') -> where("token = '$token' ") -> find();
		if($auth_info == null){return;}else{
			$time = $auth_info['upd_time'] + 7200;
			$this->appid = $auth_info['appid'];
			$this->mch_id = $auth_info['mchid'];
			$this->mkey = $auth_info['mkey'];
			if($time < time() ){
				$component = A('Weixin/Component');
				$this->access_token = $component ->get_new_access_token($this->appid,$auth_info['refresh_token']);
			}else{
				$this->access_token = $auth_info['access_token'];
			}
		}		
	}
	function objectToArray($e){
		$e=(array)$e;
		foreach($e as $k=>$v){
			if( gettype($v)=='resource' ) return;
			if( gettype($v)=='object' || gettype($v)=='array' )
			   $e[$k]=(array)$this->objectToArray($v);
		}
		return $e;
	}

	//创建自定义菜单
	public function send_menu($data){
		//$url = "https://api.weixin.qq.com/cgi-bin/menu/addconditional?access_token=".$this->access_token
		$url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=".$this->access_token;
		$res = $this->http_request($url,$data);
		return json_decode($res,true);
	}
	//https请求(支持GET和POST)
	 function http_request($url,$data = null){
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE); 
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
		if(!empty($data)){
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		}
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$output = curl_exec($curl);
		//var_dump(curl_error($curl));
		curl_close($curl);
		return $output;
	}
}


