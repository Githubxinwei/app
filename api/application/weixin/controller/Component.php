<?php
namespace app\weixin\Controller;
use think\Controller;
class Component extends Controller{
	/*************
	**微信第三方平台接口封装类
	**西瓜科技
	*************/
	function _initialize(){
		$this->appId =  "wx56778283760f2432";
		$this->appsecret = '796a552216a5f2fe56f0370bc65d1274';
		$this->com_token = "xiguaweixin2015";
		$this->encodingAesKey = "KdBBx07QKXHoZbpwOU4XICxMVZyMHjchpJYGWz927ef";
	}
	function auth_jump(){
		if(!isset($_GET['xigua_code']) ){echo '参数无效！';exit;}
		$pre_auth_code = $this -> get_auth_code();
		// dump($_GET['xigua_code']);exit;
		$url = 'https://mp.weixin.qq.com/cgi-bin/componentloginpage?component_appid=wx56778283760f2432&pre_auth_code='.$pre_auth_code.'&redirect_uri=http://'.$_SERVER['HTTP_HOST'].'/Weixin/Component/authorization_info?xigua_code='.$_GET['xigua_code'];
		header('location:'.$url);
		// $this->assign('url',$url);
		// $this->display();
	}
	/*加密消息*/
	function encryptMsg($text){
		include_once "WxMsgCrypt/wxBizMsgCrypt.php";
		// 第三方发送消息给公众平台
		$pc = new \WXBizMsgCrypt($this->com_token, $this->encodingAesKey, $this->appId);
		$encryptMsg = '';
		$nonce = "xxxxxx";
		$errCode = $pc->encryptMsg($text, time(), $nonce, $encryptMsg);
		return $encryptMsg;
	}
	/*解密消息*/
	function decryptMsg($arr,$postStr){
		include_once "WxMsgCrypt/wxBizMsgCrypt.php";
		// 第三方发送消息给公众平台
		$pc = new \WXBizMsgCrypt($this->com_token, $this->encodingAesKey, $this->appId);
		$msg = '';
		$errCode = $pc->decryptMsg($arr['msg_signature'], $arr['timestamp'], $arr['nonce'], $postStr, $msg);
		return $msg;
	}
	/*事件响应处理*/
	function openoauth(){
		$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
		$msg = $this->decryptMsg($_GET,$postStr);
		$info = simplexml_load_string($msg,'SimpleXMLElement', LIBXML_NOCDATA);
		switch(trim($info->InfoType)){
			case 'unauthorized'://取消授权
			// F('unauthorized',$msg,DATA_ROOT);
				$appid = trim($info->AuthorizerAppid);
				
				$res = M('auth_info') -> where("appid = '$appid' ") ->setField('is_authorized',0);F('appid',$appid.$res,DATA_ROOT);
			break;
			case 'updateauthorized'://更新授权
				$appid = trim($info->AuthorizerAppid);
				M('auth_info') -> where("appid = '$appid' ") ->setField('is_authorized',1);
			break;
			case 'authorized'://授权成功
				$appid = trim($info->AuthorizerAppid);
				M('auth_info') -> where("appid = '$appid' ") ->setField('is_authorized',1);
			break;
			case 'component_verify_ticket'://每10分钟推送
			F('component_verify_ticket',trim($info->ComponentVerifyTicket),DATA_ROOT);/*缓存component_verify_ticket*/
			break;
			default:
			break;
		}
		echo 'success';
	}
	function get_com_access_token(){
		$component_access_token = $this -> component_access_token();
		echo $component_access_token;
	}
	// 第三方平台方获取预授权码（pre_auth_code）
	function  component_access_token(){
		include_once "WxMsgCrypt/wxBizMsgCrypt.php";
		// 第三方发送消息给公众平台
		if(!file_cache('component_access_token','','')){
			$url = 'https://api.weixin.qq.com/cgi-bin/component/api_component_token';
			$verify_ticket = file_cache('component_verify_ticket','','');
			// $verify_ticket = simplexml_load_string($verify_ticket);
			// $verify = $verify_ticket->ComponentVerifyTicket;
			$data = '{
				"component_appid":"'.$this->appId.'" ,
				"component_appsecret": "'.$this->appsecret.'", 
				"component_verify_ticket": "'.$verify_ticket.'" 
			}';
			$res = $this->http_request($url,$data);
			$ress = json_decode($res,true);
			if(!isset($ress['component_access_token'])){
				dump($res);exit;	
			}
			$ress = json_decode($res,true);
			$component_access_token = $ress['component_access_token'];
			file_cache('component_access_token',$component_access_token,7000);//缓存令牌，两小时过期
		}else{
			$component_access_token = file_cache('component_access_token','','');
		}
		return $component_access_token;
	}
	// 获取预授权码pre_auth_code
	function get_auth_code(){
		$component_access_token = $this -> component_access_token();
		$url = 'https://api.weixin.qq.com/cgi-bin/component/api_create_preauthcode?component_access_token='.$component_access_token;
		$data = '{
			"component_appid":"'.$this->appId.'" 
		}';
		$res = $this->http_request($url,$data);
		$ress = json_decode($res,true);
		return $ress['pre_auth_code'];
	}
	/*使用授权码换取公众号的调用凭证和授权信息*/
	function get_auth_public($auth_code){
		$component_access_token = $this -> component_access_token();
		$url = 'https://api.weixin.qq.com/cgi-bin/component/api_query_auth?component_access_token='.$component_access_token;
		$data = '{
			"component_appid":"'.$this->appId.'" ,
			"authorization_code": "'.$auth_code.'"
		}';
		$res = $this->http_request($url,$data);
		$ress = json_decode($res,true);
		$array = array(
			'access_token'=>$ress['authorization_info']['authorizer_access_token'],
			'appid'=>$ress['authorization_info']['authorizer_appid'],
		);
		return $array;
	}
	/*调用凭证两小时过期刷新凭证*/
	function get_new_access_token($appid,$refresh_token){
		$component_access_token = $this -> component_access_token();
		$url = 'https://api.weixin.qq.com/cgi-bin/component/api_authorizer_token?component_access_token='.$component_access_token;
		$data = '{
			"component_appid":"'.$this->appId.'",
			"authorizer_appid":"'.$appid.'",
			"authorizer_refresh_token":"'.$refresh_token.'",
		}';
		$res = $this->http_request($url,$data);
		$ress = json_decode($res,true);
		$auth_info = array(
			'access_token'=>$ress['authorizer_access_token'],
			'refresh_token'=>$ress['authorizer_refresh_token'],
			'upd_time'=>time(),
		);
		M('auth_info') -> where("appid = '$appid' ") -> save($auth_info);
		//dump($ress);exit;
	}
	function authorization_info(){
		if(!$_GET['auth_code']){
			$arr['type'] = 0;
			$arr['msg'] = '授权信息抓取失败，请返回重试';
			$this->assign('info',$arr);
			$this->display();
			exit;
		}
		//dump($_GET['xigua_code']);exit;
		$component_access_token = $this -> component_access_token();
		$url = 'https://api.weixin.qq.com/cgi-bin/component/api_query_auth?component_access_token='.$component_access_token;
		$data = '{
			"component_appid":"'.$this->appId.'" ,
			"authorization_code": "'.$_GET['auth_code'].'"
		}';
		$res = $this->http_request($url,$data);
		$ress = json_decode($res,true);dump($ress);exit;
		if($ress['errcode']){
			$arr['type'] = 0;
			$arr['msg'] = '授权失败，请检查平台是否全网发布';
			$this->assign('info',$arr);
			$this->display();
			exit;
		}//授权失败时，停止写入数据库
		$func_list = $ress['authorization_info']['func_info'];
		$func_info = '';
		foreach($func_list as $val){
			if($func_info == ''){
				$func_info = $val['funcscope_category']['id'];
			}else{
				$func_info = $func_info.','.$val['funcscope_category']['id'];
			}
		}
		$auth_info = array(
			'appid'=>$ress['authorization_info']['authorizer_appid'],
			'access_token'=>$ress['authorization_info']['authorizer_access_token'],
			'refresh_token'=>$ress['authorization_info']['authorizer_refresh_token'],
			'func_info'=>$func_info,
			'upd_time'=>time(),
		);
		// 判断是否已存入数据库
		$token_info = json_decode($_GET['xigua_code'],true);
		$info = M('auth_info') -> where("token = '$token_info[token]' ") -> find();
		if($info == null){
			/*在其他号绑定过也无法使用*/
			$old_info = M('auth_info') -> where(" appid = '$auth_info[appid]' ")->find();
			if($old_info){
				$arr['type'] = 0;
				$arr['msg'] = '该微信公众号已在其他账户完成绑定，无法绑定到当前账户！';
				$this->assign('info',$arr);
				$this->display();
				exit;
			}
			$auth_info['token'] = $token_info['token'];
			$id = M('auth_info') -> add($auth_info);
		}else{
			if( $auth_info['appid'] != $info['appid']){
				$arr['type'] = 0;
				$arr['msg'] = '授权公众账号与原绑定号不一致！请确认！原公众号名称为「'.$info['nick_name'].'」';
				$this->assign('info',$arr);
				$this->display();
				exit;
			}
			$id = $info['id'];
			M('auth_info') -> where(" id = '$id' ") -> save($auth_info);
		}
		$base_info = $this->get_auth_base_info($auth_info['appid']);
		M('auth_info') -> where(" id = '$id' ") -> save($base_info);
		$arr['type'] = 1;
		$this->assign('info',$arr);
		$this->display();
		// redirect($token_info['url']);
	}
	function get_auth_base_info($authorizer_appid){
		// $authorizer_appid = 'wx8b3d2b12a1eb513f';
		$component_access_token = $this -> component_access_token();
		$url = 'https://api.weixin.qq.com/cgi-bin/component/api_get_authorizer_info?component_access_token='.$component_access_token;
		$data = '{
			"component_appid":"'.$this->appId.'" ,
			"authorizer_appid": "'.$authorizer_appid.'" 
		}';
		$res = $this->http_request($url,$data);
		$ress = json_decode($res,true);
		$return_Data = array(
			'nick_name'=>$ress['authorizer_info']['nick_name'],
			'head_img'=>$ress['authorizer_info']['head_img'],
			'service_type_info'=>$ress['authorizer_info']['service_type_info']['id'],
			'verify_type_info'=>$ress['authorizer_info']['verify_type_info']['id'],
			'user_name'=>$ress['authorizer_info']['user_name'],
			'alias'=>$ress['authorizer_info']['alias'],
			'qrcode_url'=>$ress['authorizer_info']['qrcode_url'],
			'principal_name'=>$ress['authorizer_info']['principal_name'],
			'signature'=>$ress['authorizer_info']['signature'],
		);
		return $return_Data;
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
	
	function curl_grab_page($url,$data,$proxy='',$proxystatus='',$ref_url='') {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
		curl_setopt($ch, CURLOPT_TIMEOUT, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		if ($proxystatus == 'true') 
		{
			curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, TRUE);
			curl_setopt($ch, CURLOPT_PROXY, $proxy);
		}
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_URL, $url);
		if(!empty($ref_url))
		{
			curl_setopt($ch, CURLOPT_HEADER, TRUE);
			curl_setopt($ch, CURLOPT_REFERER, $ref_url);
		}
		curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		ob_start();
		return curl_exec ($ch);
		ob_end_clean();
		curl_close ($ch);
		unset($ch);
	}
}