<?php
namespace Weixin\Controller;
use Think\Controller;
/*全网发布时使用*/
class CheckController extends Controller{
	function test(){
		dump(F('test0426','',DATA_ROOT));exit;
		$appid = 'wx8b3d2b12a1eb513f';
		$openid = 'o5NEduHfKL9r5JDwtAv_ZJ5lo6m4';
		$wx = A("Weixin/Wx");
		$wx->__construct($appid);
		$wx -> send_text('你好，客服接口',$openid);
	}
	function send_text($keyword,$openid){
		$code = substr($keyword,16);
		$Component = A('Weixin/Component');
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