<?php
namespace app\weixin\controller;
use think\Controller;
class Check extends Controller{
	function send_text($keyword,$openid){
		$code = substr($keyword,16);
		$Component = controller('Weixin/Component');
		$info = $Component -> get_auth_public($code);
		$url = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token='.$info['access_token'];
		$data = '{
		    "touser":"'.$openid.'",
		    "msgtype":"text",
		    "text":
		    {
		         "content":"'.$code.'_from_api"
		    }
		}';
		$res = $this->http_request($url,$data);
	}
	
}


