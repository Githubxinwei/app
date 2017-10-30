<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
// 记录财务日志
function flog($user_id,$action,$money,$type,$remark){
	if(empty($remark)){$remark = null;}
	model('finance_log') -> save(array(
		'user_id' => $user_id,
		'type' => $type,
		'money' => $money,
		'action' => $action,
		'create_time' => time(),
		'remark' => $remark
	));
}
//缓存文件方法，默认有效时间7200秒
function file_cache($name,$value,$time){
	if(empty($name)){return __FUNCTION__.'()方法传参$name文件名不能为空';}
	$name = dirname(__file__).'/file/'.$name.'.php';
	if(empty($value)){
		if(!file_exists($name)){return false;}
		$res = json_decode(file_get_contents($name),true);
		if($res['last_time'] < time()){
			unlink($name);
			return false;//文件已过期
		}
		return $res['value'];
	}elseif($value == 'del'){
		unlink($name);return true;
	}
	$path_arr = explode('/', $name);
	$path = explode($path_arr[count($path_arr)-1],$name);
	if(!file_exists($path[0])){
		mkdir($path[0]);chmod($path[0],0777);
	}
	if(empty($time)){$time = 9999999999;}
	$puts['value'] = $value;$puts['last_time'] = time()+$time;
	$res = file_put_contents($name,  json_encode($puts));
	$return = ($res) ?  true : false;
	return $return;
}
// 取得微信支付参数
function get_wxpay_parameters($openid,$out_trade_no,$money,$notify_url){

	\Think\loader::import('Wechat.jspay');
	$jsapi = new \jspay();
	$param = $GLOBALS['_CFG']['mp'];
	$param['key'] = $GLOBALS['_CFG']['mp']['key'];
	$param['openid'] = $openid;
	$param['body'] = empty($remark) ? '在线支付' : $remark;
	$param['out_trade_no'] = $out_trade_no;
	$param['total_fee'] = $money * 100;
	$param['notify_url'] = $notify_url;
	// $ext = null;
	// if(is_array($ext)){
	// 	$param = array_merge($param, $ext);
	// }
	$jsapi -> set_param($param,null);
	$uo = $jsapi -> unifiedOrder('JSAPI');
	// 发生错误则提示
	if($uo['code'] != 10000){
		return $uo;
	}
	$jsapi_params = $jsapi -> get_jsApi_parameters();
	if($jsapi_params){

	}
	return $jsapi_params;
}
//返回小程序模板信息
function get_app($type){
	$arr = [
		1=>['code'=>1,'name'=>'电商小程序','pic'=>'Uploads/18595906710/20171007/15073391915109.jpeg','fee'=>100,'template_id'=>17],
		2=>['code'=>2,'name'=>'预约小程序','pic'=>'Uploads/18595906710/20171007/15073391915109.jpeg','fee'=>50,'template_id'=>1]
	];
	if($type == 'all'){
		return $arr;
	}else{
		return isset($arr[$type]) ? $arr[$type] : false;
	}
}
//返回小程序的主题配置
function get_theme($key){
	$color_arr = [
		['BarText'=>'black','theme'=>'#ffffff','text'=>'#000','icon'=>'blue','selected'=>'#000'],
		['BarText'=>'black','theme'=>'#1d1d1d','text'=>'#fff','icon'=>'blue','selected'=>'#000'],
		['BarText'=>'black','theme'=>'#3790f4','text'=>'#fff','icon'=>'Deep-blue','selected'=>'#000'],
		['BarText'=>'black','theme'=>'#ff8635','text'=>'#fff','icon'=>'orange','selected'=>'#000'],
		['BarText'=>'black','theme'=>'#2a9a3d','text'=>'#fff','icon'=>'green','selected'=>'#000'],
		['BarText'=>'black','theme'=>'#ed5ec0','text'=>'#fff','icon'=>'pink','selected'=>'#000'],
		['BarText'=>'black','theme'=>'#9f56dd','text'=>'#fff','icon'=>'purple','selected'=>'#000'],
		['BarText'=>'black','theme'=>'#ad855e','text'=>'#fff','icon'=>'soil','selected'=>'#000'],
		['BarText'=>'black','theme'=>'#00c1e4','text'=>'#fff','icon'=>'blue','selected'=>'#000'],
		['BarText'=>'black','theme'=>'#f12c20','text'=>'#fff','icon'=>'orange','selected'=>'#000'],
	];
	if(isset($color_arr[$key])){
		return $color_arr[$key];
	}else{
		return '未配置相关主题';
	}
	
}
//加密方法
function xgmd5($pwd){
	$res = 'xiguakeji.com'.$pwd;
	return md5($res);
}
//生成长度16的随机字符串
function createNonceStr($length = 16) {
	$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
	$str = "";
	for ($i = 0; $i < $length; $i++) {
		$str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
	}
	return "z".$str;
}
function msg_everify($tel,$code){
	$key =  '4c9ff96cc33a783b21329b8c20e8ee7c';//您申请的APPKEY
	$tpl_id = '43730';//您申请的短信模板ID，根据实际情况修改
	$tpl_value = '#code#='.$code;//您设置的模板变量，根据实际情况修改
	$sendUrl = 'http://v.juhe.cn/sms/send'; //短信接口的URL
	$smsConf = array(
		'key'   => $key,
		'mobile'    => $tel, //接受短信的用户手机号码
		'tpl_id'    => $tpl_id,
		'tpl_value' => $tpl_value,
	);
	$content = http_request($sendUrl,$smsConf,1); //请求发送短信
	if($content){
		$result = json_decode($content,true);
		$error_code = $result['error_code'];
		if($error_code == 0){
			return true;//状态为0，说明短信发送成功
		}else{
			return false;//状态非0，说明失败
		}
	}else{
		return false;//返回内容异常，以下可根据业务逻辑自行修改
	}
}
//生成参数二维码
function put_qrcode($value,$name,$qr_path,$logo='',$state=false){
	if(empty($value)){throw new \think\Exception('缺少vaule值', 100006);}
	if(empty($name)){$name = time().rand(1000,9999);}
	if(!file_exists('./static/qrcode/')){
		mkdir('./static/qrcode/');chmod('./static/qrcode/',0777);
	}
	if(empty($qr_path)){$qr_path = './static/qrcode/'.date("Ymd").'/';}else{$qr_path = './static/'.$qr_path;}
	if(!file_exists($qr_path)){
		mkdir($qr_path);chmod($qr_path,0777);
	}
	$QR = $qr_path.$name.'_linshi.png';
	$last = $qr_path.$name.'.png';
	if(!file_exists($last) || $state){
		\Think\Loader::import('phpqrcode.phpqrcode');
		$errorCorrectionLevel = "L";
		$matrixPointSize = "6";
		if(!empty($logo)){
			\QRcode::png($value, $QR, $errorCorrectionLevel, $matrixPointSize,1,true);
			$QR = imagecreatefromstring(file_get_contents($QR));
			$logo = imagecreatefromstring(file_get_contents($logo));
			$QR_width = imagesx($QR);
			$QR_height = imagesy($QR);
			$logo_width = imagesx($logo);
			$logo_height = imagesy($logo);
			$logo_qr_width = $QR_width / 5;
			$scale = $logo_width / $logo_qr_width;
			$logo_qr_height = $logo_height / $scale;
			$from_width = ($QR_width - $logo_qr_width) / 2;
			imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width, $logo_qr_height, $logo_width, $logo_height);
			imagepng($QR,$last);unlink($qr_path.$name.'_linshi.png');
		}else{
			\QRcode::png($value, $last, $errorCorrectionLevel, $matrixPointSize,1,true);
		}
	}
	return $last;
}

/**
 * @param $appid id
 * @param $appsecret
 * @param $t_id 模板id
 * @param $tel 要向哪个手机发送短信
 * @param $time 有效期 默认是120秒
 * @param $params code:888888
 * @param $code 验证码
 * @return int 结果码
 */
function sendMsg($appid,$appsecret,$t_id,$tel,$params,$code,$time = 120){
	if(!isset($appid) || !isset($appsecret) || !isset($t_id) || !isset($tel) || !isset($params)||!isset($code)){
		return -1;
	}
	$url = "http://www.xiguakeji.cc/sms/send";//接口請求地址
	session(array('name'=>'xigua_verify','expire'=>$time));
	session('xigua_verify',$code);
	$data1 = [
		'appid'=>$appid,
		'appsecret'=>$appsecret,
		't_id'=>$t_id,
		'mobile'=>$tel,
		'params'=>$params,
	];
	$result = http_request($url,$data1);
	$result = json_decode($result,true);
	return $result['return_code'];
}

/**发送邮件方法
 *@param $to：接收者 $title：标题 $content：邮件内容
 *@return bool true:发送成功 false:发送失败
 */
function sendMail($to,$title,$content,$type='qq'){
	//引入PHPMailer的核心文件 使用require_once包含避免出现PHPMailer类重复定义的警告
	import("PHPMailer.PHPMailer",EXTEND_PATH,'.class.php');
	import("PHPMailer.SMTP",EXTEND_PATH,'.class.php');
	//实例化PHPMailer核心类
	$mail = new \PHPMailer();
	//使用smtp鉴权方式发送邮件
	$mail->isSMTP();
	//smtp需要鉴权 这个必须是true
	if($type == 'qq'){
		$mail->Host = 'smtp.qq.com';
		$mail->Username ='741350149@qq.com';
		//smtp登录的密码 使用生成的授权码
		$mail->Password = 'ewmwpkaofligbdji';
		//设置发件人邮箱地址 这里填入上述提到的“发件人邮箱”
		$mail->From = '741350149@qq.com';
	}else{
		$mail->Host = 'smtp.163.com';
		$mail->Username ='18317774594@163.com';
		//smtp登录的密码 使用生成的授权码（就刚才叫你保存的最新的授权码）
		$mail->Password = 'lijiafei7511';
		//设置发件人邮箱地址 这里填入上述提到的“发件人邮箱”
		$mail->From = '18317774594@163.com';
	}
	$mail->SMTPAuth=true;
	//设置使用ssl加密方式登录鉴权
	$mail->SMTPSecure = 'ssl';
	//设置ssl连接smtp服务器的远程服务器端口号，以前的默认是25，但是现在新的好像已经不可用了 可选465或587
	$mail->Port = 465;
	//设置发送的邮件的编码 可选GB2312 我喜欢utf-8 据说utf8在某些客户端收信下会乱码
	$mail->CharSet = 'UTF-8';
	//设置发件人姓名（昵称） 任意内容，显示在收件人邮件的发件人邮箱地址前的发件人姓名
	$mail->FromName = '小程序订单通知';
	$mail->isHTML(true);
	$mail->addAddress($to,'小程序');
	$mail->Subject = $title;
	$mail->Body = $content;
	$status = $mail->send();
//        return $status;
	//简单的判断与提示信息
	if($status) {
		return true;
	}else{
		return false;
	}
}

/**
 * @param $appid 八位唯一标志
 * @param $msg 信息
 * @param $type 1审核成功，2失败
 * 代码审核成功或失败是发送给人员的电话和邮件
 */
function sendAuditMsg($appid,$msg,$type){
    $info = db('app') -> field('name,notifytel,notifyemail') -> where(['appid' => $appid]) -> find();
    if(!$info){
        return;
    }
    return;
    if($type == 1){
        $msg = "恭喜你,你的小程序[{$info['name']}]审核成功";
    }else if($type == 2){
        $msg = "很遗憾,你的小程序[{$info['name']}]审核失败,失败原因:{$msg}";
    }
    $url = "http://www.xiguakeji.cc/sms/send";//接口請求地址
    $data1 = [
        'appid'=>'18317774594',
        'appsecret'=>'zaefNsQrp2GJ9F3Y',
        't_id'=>'TP1709201',
        'mobile'=>'18317774594',
        'params'=>"code:12345",
    ];

    $result = http_request($url,$data1);
    $result = json_decode($result,true);
    dump($result);dump($data1);
//    sendMail($info['notifyemail'],'小程序审核结果',$msg,'163');
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