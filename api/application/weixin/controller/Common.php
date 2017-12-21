<?php 
namespace app\weixin\controller;
use think\Controller;
class common extends Controller{
	// public $apps='23542640';
	// public $access_token ;
	public function __construct($apps = 0){
		parent::__construct();
		if($apps != 0){
            $this->apps = $apps;
            $auth_info = model('auth_info') -> where("apps = '$this->apps' ") -> find();
            if(!$auth_info){
                $return['code'] = 10001;
                $return['msg'] = '您还没有绑定小程序，还不能进行相关操作';
                echo json_encode($return);die();
            }
            $time = $auth_info['upd_time'] + 7200;
            $this->appid = $auth_info['appid'];
            $this->mch_id = $auth_info['mchid'];
            $this->mkey = $auth_info['mkey'];
            if($time < time() ){
                $component = controller('Weixin/Component');
                $this->access_token = $component ->  get_new_access_token($this->appid,$auth_info['refresh_token'],$apps);
            }else{
                $this->access_token = $auth_info['access_token'];
            }
        }

	}
	//获取模板消息列表
	function template_list($offset){
		$url = 'https://api.weixin.qq.com/cgi-bin/wxopen/template/library/list?access_token='.$this->access_token;
		$data = '{
			"offset":'.$offset.',
			"count":20
		}';
		$res = http_request($url,$data);
		return json_decode($res,true);
	}

    function user_app_qr($user_id,$page){
        $url = 'https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token='.$this->access_token;
        $data = [
            "scene" => $user_id,
			"page" => $page
        ];
        $data = json_encode($data);
        $res = http_request($url,$data);
        return json_decode($res,true);
    }

	//获取模板消息的关键词库
	function template_get($id){
		$url = 'https://api.weixin.qq.com/cgi-bin/wxopen/template/library/get?access_token='.$this->access_token;
		$data = '{"id":"'.$id.'"}';
		$res = http_request($url,$data);
		halt($res);
		return(json_decode($res,true));
	}
	//组合模板并添加至帐号下的个人模板库
	function template_add($id,$keyword_id_list){
		$url = 'https://api.weixin.qq.com/cgi-bin/wxopen/template/add?access_token='.$this->access_token;
		$data = '{ 
			"id":"'.$id.'", 
			"keyword_id_list":['.$keyword_id_list.'] 
		}';
		$res = http_request($url,$data);
		return(json_decode($res,true));
	}
	//获取现有的模板消息列表
	function template_user($offset){
		$url = 'https://api.weixin.qq.com/cgi-bin/wxopen/template/list?access_token='.$this->access_token;
		$data = '{
			"offset":'.$offset.',
			"count":20
		}';
		$res = http_request($url,$data);
		return(json_decode($res,true));
	}
	//删除模板消息
	function template_del($template_id){
		$url = 'https://api.weixin.qq.com/cgi-bin/wxopen/template/del?access_token='.$this->access_token;
		$data = '{
			"template_id":"'.$template_id.'"
		}';
		$res = http_request($url,$data);
		return(json_decode($res,true));
	}
	//发送模板消息
	function template_send($openid,$template_id,$form_id,$data){
		$url = 'https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token='.$this->access_token;
		$data = '{
			"touser": "'.$openid.'",  
			"template_id": "'.$template_id.'",         
			"form_id": "'.$form_id.'",         
			"data": {
			  "keyword1": {
			      "value": "'.$data['keyword1'].'", 
			      "color": "#173177"
			  }, 
			  "keyword2": {
			      "value": "'.$data['keyword2'].'", 
			      "color": "#173177"
			  },
			  "keyword3": {
			      "value": "'.$data['keyword3'].'", 
			      "color": "#173177"
			  }, 
			  "keyword4": {
			      "value": "'.$data['keyword4'].'", 
			      "color": "#173177"
			  },
			  "keyword5": {
			      "value": "'.$data['keyword5'].'", 
			      "color": "#173177"
			  },
			  "keyword6": {
			      "value": "'.$data['keyword6'].'", 
			      "color": "#173177"
			  },
			  "keyword7": {
			      "value": "'.$data['keyword7'].'", 
			      "color": "#173177"
			  }
			},
			"emphasis_keyword": "keyword1.DATA" 
		}';
		$res = http_request($url,$data);
		$res = json_decode($res,true);
        //发送成功。form_id 已经失效，删除掉
        db('form_list') -> where("form_id",$form_id) -> delete();
		return($res);
	}
	//微信支付加密字符串
	public function paysign($prepay_id){
		//timeStamp, nonceStr, package, signType
		$timeStamp = time();
		$nonceStr = createNonceStr();
		$string = "appId=".$this->appid."&nonceStr=".$nonceStr."&package=prepay_id=".$prepay_id."&signType=MD5&timeStamp=".$timeStamp;
		$res = md5($string."&key=".$this->mkey);
		$arr = array(
			"timeStamp" => $timeStamp,
			//"appid" => $this->appid,
			"nonceStr" => $nonceStr,
			"prepay_id" => $prepay_id,
			"paySign" => strtoupper($res),
		);
		return $arr;
	}
	//获取预支付交易会话标识，有效期两个小时
	function get_prepay_id($openid,$total_fee,$out_trade_no,$attach,$good_name){
		$notify_url = 'https://'.$_SERVER['HTTP_HOST'].url('custom/notify/index');
		$nonce_str = createNonceStr();
		$ip = $_SERVER['SERVER_ADDR'];
		$sign = $this->signjiami($openid,$nonce_str,$total_fee,$out_trade_no,$notify_url,$attach,$good_name,$ip);
		$url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
		$data = "<xml>
		   <appid>".$this->appid."</appid>
		   <attach>".$attach."</attach>
		   <body>".$good_name."</body>
		   <mch_id>".$this->mch_id."</mch_id>
		   <nonce_str>".$nonce_str."</nonce_str>
		   <notify_url>".$notify_url."</notify_url>
		   <openid>".$openid."</openid>
		   <out_trade_no>".$out_trade_no."</out_trade_no>
		   <sign>".$sign."</sign>
		   <spbill_create_ip>".$ip."</spbill_create_ip>
		   <total_fee>".$total_fee."</total_fee>
		   <trade_type>JSAPI</trade_type>
		</xml>";
		$result = http_request($url,$data);
		$postObj = simplexml_load_string($result, 'SimpleXMLElement', LIBXML_NOCDATA);
         $prepay_id = trim($postObj->prepay_id);
		return $prepay_id;
	}


	/*加密*/
	private function signjiami($openid,$nonce_str,$total_fee,$out_trade_no,$notify_url,$attach,$good_name,$ip){
		$string1 = "appid=".$this->appid."&attach=".$attach."&body=".$good_name."&mch_id=".$this->mch_id."&nonce_str=".$nonce_str."&notify_url=".$notify_url."&openid=".$openid."&out_trade_no=".$out_trade_no."&spbill_create_ip=".$ip."&total_fee=".$total_fee."&trade_type=JSAPI";
		$result = md5($string1."&key=".$this->mkey);
		return strtoupper($result);
	}

    //微信扫码支付的调用
    function get_qr_prepay_id($total_fee,$out_trade_no,$attach,$good_name,$payInfo,$notify_url){
        $nonce_str = createNonceStr();
        $ip = $_SERVER['SERVER_ADDR'];
        $sign = $this->signjiami1($nonce_str,$total_fee,$out_trade_no,$notify_url,$attach,$good_name,$ip,$payInfo);
        $url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
        $data = "<xml>
		   <appid>".$payInfo['appid']."</appid>
		   <attach>".$attach."</attach>
		   <body>".$good_name."</body>
		   <mch_id>".$payInfo['mch_id']."</mch_id>
		   <nonce_str>".$nonce_str."</nonce_str>
		   <notify_url>".$notify_url."</notify_url>
		   <out_trade_no>".$out_trade_no."</out_trade_no>
		   <sign>".$sign."</sign>
		   <spbill_create_ip>".$ip."</spbill_create_ip>
		   <total_fee>".$total_fee."</total_fee>
		   <trade_type>NATIVE</trade_type>
		</xml>";
        $result = http_request($url,$data);
        $postObj = simplexml_load_string($result, 'SimpleXMLElement', LIBXML_NOCDATA);
        $code_url = trim($postObj->code_url);
        return $code_url;
    }

    private function signjiami1($nonce_str,$total_fee,$out_trade_no,$notify_url,$pay_id,$good_name,$ip,$payInfo){
        //$key = "qazwsxedcrfvtgbyhnujmikolpqazwsx";
        $string1 = "appid=".$payInfo['appid']."&attach=".$pay_id."&body=".$good_name."&mch_id=".$payInfo['mch_id']."&nonce_str=".$nonce_str."&notify_url=".$notify_url."&out_trade_no=".$out_trade_no."&spbill_create_ip=".$ip."&total_fee=".$total_fee."&trade_type=NATIVE";
        $result = md5($string1."&key=".$payInfo['mch_key']);
        return strtoupper($result);
    }



    /*退款接口*/
    function pay_refund($order_sn,$refund_no,$fee){

        $nonce_str = createNonceStr();
        $sign = $this->pay_refund_sign($nonce_str,$fee,$order_sn,$refund_no);
        $url = "https://api.mch.weixin.qq.com/secapi/pay/refund";
        $data = "<xml>
          <appid>".$this->appid."</appid>
          <mch_id>".$this->mch_id."</mch_id>
          <nonce_str>".$nonce_str."</nonce_str>
         <op_user_id>".$this->mch_id."</op_user_id>
           <out_refund_no>".$refund_no."</out_refund_no>
           <out_trade_no>".$order_sn."</out_trade_no>
           <refund_fee>".$fee."</refund_fee>
           <total_fee>".$fee."</total_fee>
          <sign>".$sign."</sign>
       </xml>";
        $result = $this->curl_post_ssl($url,$data);
        $postObj = simplexml_load_string($result, 'SimpleXMLElement', LIBXML_NOCDATA);
        $result_code = trim($postObj->result_code);
        return $result_code;
    }


    public function pay_refund_sign($nonce_str,$total_fee,$out_trade_no,$refund_no){
        //$key = "qazwsxedcrfvtgbyhnujmikolpqazwsx";
        $string1 = "appid=".$this->appid."&mch_id=".$this->mch_id."&nonce_str=".$nonce_str."&op_user_id=".$this->mch_id."&out_refund_no=".$refund_no."&out_trade_no=".$out_trade_no."&refund_fee=".$total_fee."&total_fee=".$total_fee;

        $result = md5($string1."&key=".$this->mkey);
        return strtoupper($result);
    }


    function curl_post_ssl($url, $vars,$token = '', $second=30,$aHeader=array())
    {
        $ch = curl_init();
        //超时时间
        curl_setopt($ch,CURLOPT_TIMEOUT,$second);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
        //这里设置代理，如果有的话
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
        curl_setopt($ch,CURLOPT_SSLCERT,dirname(__FILE__).DIRECTORY_SEPARATOR.'zhengshu'.DIRECTORY_SEPARATOR.'apiclient_cert.pem');
        curl_setopt($ch,CURLOPT_SSLKEY,dirname(__FILE__).DIRECTORY_SEPARATOR.'zhengshu'.DIRECTORY_SEPARATOR.'apiclient_key.pem');
        curl_setopt($ch,CURLOPT_CAINFO,dirname(__FILE__).DIRECTORY_SEPARATOR.'zhengshu'.DIRECTORY_SEPARATOR.'rootca.pem');
        if( count($aHeader) >= 1 ){
            curl_setopt($ch, CURLOPT_HTTPHEADER, $aHeader);
        }

        curl_setopt($ch,CURLOPT_POST, 1);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$vars);
        $data = curl_exec($ch);
        if($data){
            curl_close($ch);
            return $data;
        }
        else {
            $error = curl_errno($ch);
            curl_close($ch);
            return false;
        }
    }
    /*退款接口 end*/

	//设置服务器域名
	function domain(){
		$url = 'https://api.weixin.qq.com/wxa/modify_domain?access_token='.$this->access_token;
		//$data = '{"action":"get"}';
		$data = '{"action":"set","requestdomain":["https://weapp.xiguawenhua.com"]}';
		$res = http_request($url,$data);
		return(json_decode($res,true));
		
	}
	//设置体验者
	function bind_tester($wechatid){
		$url = 'https://api.weixin.qq.com/wxa/bind_tester?access_token='.$this->access_token;
		$data = '{
			"wechatid":"'.$wechatid.'"
		}';
		$res = http_request($url,$data);return(json_decode($res,true));
	}
	//解绑体验者
	function unbind_tester($wechatid){
		$url = 'https://api.weixin.qq.com/wxa/unbind_tester?access_token='.$this->access_token;
		$data = '{
			"wechatid":"'.$wechatid.'"
		}';
		$res = http_request($url,$data);return(json_decode($res,true));
	}
	//提交代码包
	function commit($template_id,$ext_json){
		$url = 'https://api.weixin.qq.com/wxa/commit?access_token='.$this->access_token;
		$data = array(
			"template_id"=>$template_id,
			"ext_json"=>$ext_json, 
			"user_version"=>"V1.0",
			"user_desc"=>"test"
		);
		$data = json_encode($data,JSON_UNESCAPED_UNICODE);
		$res = http_request($url,$data);return(json_decode($res,true));
	}
	//获取分类信息
	function get_category(){
		$url = 'https://api.weixin.qq.com/wxa/get_category?access_token='.$this->access_token;
		$res = http_request($url);
		$result = json_decode($res,true);
		return $result['category_list'];
	}
	//获取配置页
	function get_page(){
		$url = 'https://api.weixin.qq.com/wxa/get_page?access_token='.$this->access_token;
		$res = http_request($url);
		$result = json_decode($res,true);
		return $result['page_list'];
	}
	//提交代码包到审核
	function submit_audit(){
		$url = 'https://api.weixin.qq.com/wxa/submit_audit?access_token='.$this->access_token;
		$cate = $this->get_category();//dump($cate);
		$page = $this->get_page();//dump($page);
		foreach($cate as $k=>$v){
			$item_list[$k] = $v;
			$item_list[$k]['address'] = $page[$k];
			$item_list[$k]['tag'] = '学习 生活';
			$item_list[$k]['title'] = '首页';
		}
        //$item_list = json_encode($item_list);
		$data = ['item_list'=>$item_list];
		$data = json_encode( $data, JSON_UNESCAPED_UNICODE );
		//dump($data);
		$res = http_request($url,$data);return(json_decode($res,true));
	}
	//查询审核版本状态
	function get_auditstatus(){
		$url = 'https://api.weixin.qq.com/wxa/get_auditstatus?access_token='.$this->access_token;
		$data = '{
		"auditid":513821887
		}';
		$res = http_request($url,$data);dump($res);
	}
	//查询最新一次提交审核的状态
	function get_latest_auditstatus(){
		$url = 'https://api.weixin.qq.com/wxa/get_latest_auditstatus?access_token='.$this->access_token;
		$res = http_request($url);return(json_decode($res,true));
	}
	//发布，当小程序异步通知通过审核时调用此接口
	function release(){
		$url = 'https://api.weixin.qq.com/wxa/release?access_token='.$this->access_token;
		$data = '{}';
		$res = http_request($url,$data);return(json_decode($res,true));
	}
	//修改小程序线上代码的可见状态
	function change_visitstatus(){
		$url = 'https://api.weixin.qq.com/wxa/change_visitstatus?access_token='.$this->access_token;
		$data = '{
			 "action":"open"
		}';
		$res = http_request($url,$data);dump($res);
	}
	//获取体验二维码
	function get_qrcode(){
		$url = 'https://api.weixin.qq.com/wxa/get_qrcode?access_token='.$this->access_token;
		$res = http_request($url);
		$name = STATIC_APTH.'qrcode/'.$this->appid.'.jpg';
		file_put_contents($name,$res);
		return '/static/qrcode/'.$this->appid.'.jpg';
	} 
	//生成分销二维码
    public function get_qrcodes($uid) {

        $data = array();
        $data['scene'] = $uid;
        $data['page'] = "pages/index/index";
        $data = json_encode($data);
        $url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=" . $this->access_token;
        $da = http_request($url,$data);

        $name = STATIC_APTH.'code/'.$uid.'.jpg';
        file_put_contents($name,$da);
        return '/static/code/'.$uid.'.jpg';

   	 }

}



 ?>