<?php
namespace Weixin\Controller;
use Think\Controller;
/*************************************
*****封装公众号的接口*****
*****西瓜科技·于春风*****
*****2017年4月19日23:48:36*****
**************************************/
class WxController extends Controller{
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
				$this->access_token = $component ->  get_new_access_token($this->appid,$auth_info['refresh_token']);
			}else{
				$this->access_token = $auth_info['access_token'];
			}
		}
		
	}
	/*获取用户基本信息*/
	public function get_user_info($openid){
		$url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$this->access_token."&openid=".$openid."&lang=zh_CN";
		$res = $this->http_request($url);
		return json_decode($res,true);
	}
	/*客服接口发送文字信息*/
	function send_text($text,$openid){
		$url = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token='.$this->access_token;
		$data = '{
		    "touser":"'.$openid.'",
		    "msgtype":"text",
		    "text":
		    {
		         "content":"'.$text.'"
		    }
		}';
		$res = $this->http_request($url,$data);
	}
	/*客服接口发送图片消息*/
	function send_pic($openid,$media_id){
		$url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=".$this->access_token;
		$data='{
			"touser":"'.$openid.'",
			"msgtype":"image",
			"image":
			{
			  "media_id":"'.$media_id.'"
			}
		}';
		$res_json = $this->curl_grab_page($url, $data);
		$res = json_decode($res_json,true);
		return $res;
	}
	//发送模版消息
	public function send_template($openid,$data){
		$url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=".$this->access_token;
		$res = $this->http_request($url,$data);
		return $res;
	}
	/*生成临时参数二维码*/
	function get_qr($scene_id,$token){
		$url="https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=".$this->access_token;
		$data='{
			"expire_seconds": 2592000, 
			"action_name": "QR_SCENE", 
			"action_info": {
				"scene": {
					"scene_id": '.$scene_id.'
				}
			}
		}';
		$res=$this->http_request($url,$data);
		$result = json_decode($res, true);
		$ticket=$result['ticket'];
		$surl="https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=".$ticket;
		$ress=$this->http_request($surl);
		if(!is_dir('Public/qrcode/qrimg/'.$token)){
			mkdir('Public/qrcode/qrimg/'.$token);
		}
		file_put_contents('Public/qrcode/qrimg/'.$token.'/'.$scene_id.'.jpg',$ress);
		return 'Public/qrcode/qrimg/'.$token.'/'.$scene_id.'.jpg';
	}
	/*上传图片素材*/
	function media_pic($filepath,$type='image'){
		$url = "https://api.weixin.qq.com/cgi-bin/media/upload?access_token=".$this->access_token."&type=".$type;
		$filedata = array("media"  => "@".realpath($filepath));
		$res = $this->http_request($url,$filedata);
		$result = json_decode($res, true);
		return $result['media_id'];
	}
	/*保存用户头像到本地，并返回nickname和openid*/
	function get_head($user_id,$token){
		$userinfo=M('user_child')->field("nickname,headimgurl")->where(" user_id = '$user_id' ")->find();
		$url=$userinfo['headimgurl'];
		$res=$this->http_request($url);
		if(!is_dir('Public/qrcode/head_pic/'.$token)){
			mkdir('Public/qrcode/head_pic/'.$token);
		}
		file_put_contents('Public/qrcode/head_pic/'.$token.'/'.$user_id.'.jpg',$res);
		return $userinfo['nickname'];
	}
	/*创建自定义菜单*/
	public function send_menu($data){
		//$url = "https://api.weixin.qq.com/cgi-bin/menu/addconditional?access_token=".$this->access_token
		$url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=".$this->access_token;
		$res = $this->http_request($url,$data);
		return json_decode($res,true);
	}
	/*JS签名包*/
	public function getSignPackage(){
		$jsapiTicket = $this->getJsApi();
	        	$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
	        	$url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        		$timestamp = time();
        		$nonceStr = $this->createNonceStr();
       		$string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";
        		$signature = sha1($string);
        		$signPackage = array(
			"appId"     => $this->appid,
			"nonceStr"  => $nonceStr,
			"timestamp" => $timestamp,
			"url"       => $url,
			"signature" => $signature,
			"rawString" => $string
		);
        		return $signPackage;
    	}
    	//获得JS API的ticket 普通ticket
    	public function getJsApi(){
		//$ticket="sM4AOVdWfPE4DxkXGEs8VDl5qjwtv5XbtLzgtl30YSlyQYtVSeLi4sUvDPa_cG9y-I9Y1mlyn29FEv2zvZrABw";
		if(S($this->appid.'getJsApi')){
			$ticket = S($this->appid.'getJsApi');
		}else{
			$url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=".$this->access_token."&type=jsapi";
			$res = $this->http_request($url);
			$result = json_decode($res, true);
			$ticket = $result['ticket'];
			S($this->appid.'getJsApi',$ticket,7000);
		}
		return $ticket;
    	}
    	//微信支付加密字符串
	public function paysign($prepay_id){
		//timeStamp, nonceStr, package, signType
		$timeStamp = time();
		$nonceStr = $this->createNonceStr();
		$string = "appId=".$this->appid."&nonceStr=".$nonceStr."&package=prepay_id=".$prepay_id."&signType=MD5&timeStamp=".$timeStamp;
		$res = md5($string."&key=".$this->mkey);
		$arr = array(
			"timeStamp" => $timeStamp,
			"appid" => $this->appid,
			"nonceStr" => $nonceStr,
			"prepay_id" => $prepay_id,
			"paySign" => strtoupper($res),
		);
		return $arr;
	}
    	//获取预支付交易会话标识，有效期两个小时
	function get_prepay_id($openid,$total_fee,$out_trade_no,$notify_url,$pay_id,$good_name){
		$nonce_str = $this->createNonceStr();
		$sign = $this->signjiami($openid,$nonce_str,$total_fee,$out_trade_no,$notify_url,$pay_id,$good_name);
		$url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
		
		$data = "<xml>
		   <appid>".$this->appid."</appid>
		   <attach>".$pay_id."</attach>
		   <body>".$good_name."</body>
		   <mch_id>".$this->mch_id."</mch_id>
		   <nonce_str>".$nonce_str."</nonce_str>
		   <notify_url>".$notify_url."</notify_url>
		   <openid>".$openid."</openid>
		   <out_trade_no>".$out_trade_no."</out_trade_no>
		   <spbill_create_ip>14.23.150.211</spbill_create_ip>
		   <total_fee>".$total_fee."</total_fee>
		   <trade_type>JSAPI</trade_type>
		   <sign>".$sign."</sign>
		</xml>";
		file_put_contents('not.txt',$data);
		$result = $this->http_request($url,$data);
		$postObj = simplexml_load_string($result, 'SimpleXMLElement', LIBXML_NOCDATA);
        		$prepay_id = trim($postObj->prepay_id);
		return $prepay_id;
	}
	//获取扫码支付二维码code_url
	function get_code_url($total_fee,$out_trade_no,$notify_url,$pay_id,$good_name){
		$nonce_str = $this->createNonceStr();
		$sign = $this->signjiami_url($nonce_str,$total_fee,$out_trade_no,$notify_url,$pay_id,$good_name);
		$url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
		
		$data = "<xml>
		   <appid>".$this->appid."</appid>
		   <attach>".$pay_id."</attach>
		   <body>".$good_name."</body>
		   <mch_id>".$this->mch_id."</mch_id>
		   <nonce_str>".$nonce_str."</nonce_str>
		   <notify_url>".$notify_url."</notify_url>
		   <out_trade_no>".$out_trade_no."</out_trade_no>
		   <spbill_create_ip>14.23.150.211</spbill_create_ip>
		   <total_fee>".$total_fee."</total_fee>
		   <trade_type>NATIVE</trade_type>
		   <sign>".$sign."</sign>
		</xml>";
		$result = $this->http_request($url,$data);
		$postObj = simplexml_load_string($result, 'SimpleXMLElement', LIBXML_NOCDATA);
        		$code_url = trim($postObj->code_url);
		return $code_url;
	}
	public function signjiami($openid,$nonce_str,$total_fee,$out_trade_no,$notify_url,$pay_id,$good_name){
		//$key = "qazwsxedcrfvtgbyhnujmikolpqazwsx";
		$string1 = "appid=".$this->appid."&attach=".$pay_id."&body=".$good_name."&mch_id=".$this->mch_id."&nonce_str=".$nonce_str."&notify_url=".$notify_url."&openid=".$openid."&out_trade_no=".$out_trade_no."&spbill_create_ip=14.23.150.211&total_fee=".$total_fee."&trade_type=JSAPI";
		$result = md5($string1."&key=".$this->mkey);
		return strtoupper($result);
	}
	public function signjiami_url($nonce_str,$total_fee,$out_trade_no,$notify_url,$pay_id,$good_name){
		//$key = "qazwsxedcrfvtgbyhnujmikolpqazwsx";
		$string1 = "appid=".$this->appid."&attach=".$pay_id."&body=".$good_name."&mch_id=".$this->mch_id."&nonce_str=".$nonce_str."&notify_url=".$notify_url."&out_trade_no=".$out_trade_no."&spbill_create_ip=14.23.150.211&total_fee=".$total_fee."&trade_type=NATIVE";
		$result = md5($string1."&key=".$this->mkey);
		return strtoupper($result);
	}
	//生成长度16的随机字符串
    	private function createNonceStr($length = 16) {
        		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        		$str = "";
        		for ($i = 0; $i < $length; $i++) {
            			$str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        		}
        		return "z".$str;
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