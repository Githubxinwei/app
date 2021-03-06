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
function file_cache($name,$value = '',$time = '7200'){
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
		1=>['code'=>1,'name'=>'电商小程序','pic'=>'Uploads/18595906710/20171007/15073391915109.jpeg','fee'=>0.02,'template_id'=>50],
		2=>['code'=>2,'name'=>'预约小程序','pic'=>'Uploads/18595906710/20171007/15073391915109.jpeg','fee'=>0.01,'template_id'=>35],
        3=>['code'=>3,'name'=>'酒店小程序','pic'=>'Uploads/18595906710/20171007/15073391915109.jpeg','fee'=>0.01,'template_id'=>44]

    ];
	if($type == 'all'){
		return $arr;
	}else{
		return isset($arr[$type]) ? $arr[$type] : false;
	}
}

/**
 * 轮播图设置的时候选择，当前图片设置的路径。
 */
function get_app_page($type){
    $arr = [
        1=>array(
            '/order/order' => '订单页',
            '/more/more' => '更多页',
            '/cart/cart' => '购物车页',
            '/inputview/inputview' => '搜索页',
            '/classify/classify' => '分类页',
        ),
        2=>array(
            '/orderlist/orderlist' => '订单列表页',
            '/goodsdetail/goodsdetail' => '服务详情页',
            '/cate/cate' => '分类页',
        ),

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
		['BarText'=>'black','theme'=>'#ffffff','text'=>'#000','icon'=>'black','selected'=>'#000'],
		['BarText'=>'black','theme'=>'#1d1d1d','text'=>'#fff','icon'=>'black','selected'=>'#000'],
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
 * @param $tel
 * @param $params
 * @param int $flag 1 表示要扣管理员的钱
 * @param int $type 用哪个模板
 * @param int $custom_id 当前管理员的id
 * @return int|void
 * type 0 发送验证码 1 前台发送短信给管理员有新订单 2 管理员创建完普通用户给普通用户发送短信 3 后台发货发送短信通知终端用户(区分使用哪个模板id) 4 申请代理商成功或失败发送短信通知
 */
function sendMsgInfo($tel,$params,$flag = 0,$type = 0,$custom_id = 0){
    if(!isset($tel) || !isset($params)){
        return -1;
    }
    //获取超级管理员填写的账号信息
    $smsInfo = db('SystemSms') -> find();
    if(!$smsInfo){
        sendMail('741350149@qq.com','没有短信信息','没有短信信息');
        file_cache('smserror','没有短信信息');return -1;
    }
    if($smsInfo['is_mobile'] == 0){
        //后台没有开启短信功能
        sendMail('741350149@qq.com','后台没有开启短信功能','后台没有开启短信功能');
        file_cache('smserror','后台没有开启短信功能');return -2;
    }
    if(!$smsInfo['appid'] || !$smsInfo['appsecret']){
        //后台没有开启短信功能
        sendMail('741350149@qq.com','后台没有账号信息功能','后台没有账号信息功能');
        file_cache('smserror','后台没有账号信息功能');return -3;
    }
    if($flag == 1){
        //判断管理员是否有钱
        if(!$custom_id){
            return -4;
        }
        if(!$smsInfo['sms_money']){
            sendMail('741350149@qq.com','没有设置短信价格','没有设置短信价格',163);
            return -5;
        }
        $money = db('custom') -> where(['id' => $custom_id]) -> value('wallet');
        if($money - $smsInfo['sms_money'] < 0){
            $notifymail = db('app') -> field('notifymail') -> where(['custom_id' => $custom_id]) -> find();
            if($notifymail){
                sendMail($notifymail['notifymail'],'你的账户余额不足,无法发送短信','你的账户余额不足,无法发送短信');
            }
            return -6;
        }
        $res = db('custom') -> where(['id' => $custom_id]) -> setDec('wallet',$smsInfo['sms_money']);
    }else{
        $res = true;
    }
    $t_id = '';
    switch ($type){
        case 0:
            $t_id = 'TP1709201';
            break;
        default:
            return -7;
    }
    if($res){
        if($flag == 1){
            //记录账户的流水
            $smsRecord['custom_id'] = $custom_id;
            $smsRecord['money'] = $smsInfo['sms_money'];
            $smsRecord['type'] = $type;
            $smsRecord['create_time'] = time();
            db('sms_record') -> insert($smsRecord);
        }
        $url = "http://www.xiguakeji.cc/sms/send";//接口請求地址
        $data1 = [
            'appid'=>$smsInfo['appid'],
            'appsecret'=>$smsInfo['appsecret'],
            't_id'=>$t_id,
            'mobile'=>$tel,
            'params'=>$params,
        ];
        $result = http_request($url,$data1);
        $result = json_decode($result,true);
        $return_code =  $result['return_code'];
        if($return_code == 5007){
            //短信账户余额不足
            sendMail($smsInfo['mail_user'],'短信账户平台没钱了','短信账户平台没钱了');
        }
        return $return_code;
    }
}

/**发送邮件方法
 *@param $to：接收者 $title：标题 $content：邮件内容
 *@return bool true:发送成功 false:发送失败
 */
function sendMail($to,$title,$content,$type='163'){
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
    if(!$info || !$info['notifytel'] || !$info['notifyemail']){
        file_cache('notifytel.php',$appid . json_encode($info) . '.....',FILE_APPEND);
        return;
    }
    if($type == 1){
        //sendMsgInfo($info['notifytel'],)
    }else if($type == 2){
        //$msg = "很遗憾,你的小程序[{$info['name']}]审核失败,失败原因:{$msg}";
    }
    sendMail($info['notifyemail'],'小程序审核结果',$msg,'163');
}

/**
 * @param $app唯一八位数字的值
 * @return int|string
 */
function getAppExtJson($app){
    if(!isset($app)){return 0;}
    //判断是否在auto_info表中有绑定
    $appid = db("auth_info") -> where(['apps' => $app]) -> value('appid');
    if(!$appid){
        return 0;
    }
    $info = db('app') -> field('type,theme,layout,search,on_service') -> where(['appid' => $app]) -> find();
    $apps = $app;//1
    $color = get_theme($info['theme']);//主题色//1
    $layout_arr = ['grid','table','table_row'];
    $layout = $layout_arr[$info['layout']];//布局1
    $search = 1==$info['search'] ? 'true' : 'false';//启用搜索框1
    $on_service = 1==$info['on_service'] ? 'true' : 'false';//启用客服1
    switch($info['type']){
        case 1:
            $ext_json = '{
	"extEnable": true,
	"extAppid": "'.$appid.'",
	"window":{
	"navigationBarTitleText": "西瓜科技演示",
	"navigationBarTextStyle":"white",
	"navigationBarBackgroundColor": "'.$color['theme'].'",
	"backgroundTextStyle":"light",
	"backgroundColor": "'.$color['theme'].'"
	},
	"ext":{
	 "xgAppId":"'.$apps.'",
	"appid":"'.$appid.'",
	 "themeColor":"'.$color['theme'].'",
	 "themeTextColor":"'.$color['text'].'",
	 "layoutType":"'.$layout.'",
	 "showSearching":'.$search.',
	 "useOnlineService":'.$on_service.',
	 "host":"https://weapp.xiguawenhua.com"
	},
	"tabBar": {
		"selectedColor": "'.$color['selected'].'",
		"backgroundColor": "#fff",
		"color":"#555",
		"borderStyle": "black",
		"list": [
			{
				"pagePath": "pages/index/index",
				"iconPath": "./img/images/un-home.png",
				"selectedIconPath": "./img/images/'.$color['icon'].'-home.png",
				"postion": "top",
				"text": "首页"
			},
			{
				"pagePath": "pages/cart/cart",
				"iconPath": "./img/images/un-care.png",
				"selectedIconPath": "./img/images/'.$color['icon'].'-care.png",
				"text": "购物车"
			},
			{
				"pagePath": "pages/order/order",
				"iconPath": "./img/images/un-order.png",
				"selectedIconPath": "./img/images/'.$color['icon'].'-order.png",
				"text": "订单"
			},
			{
				"pagePath": "pages/more/more",
				"iconPath": "./img/images/un-more.png",
				"selectedIconPath": "./img/images/'.$color['icon'].'-more.png",
				"text": "更多"
			}
		]
	}
}';
            break;
        case 2:
            //待定
            $ext_json = '{
                "extEnable": true,
                "extAppid": "'.$appid.'",
                "window":{
                    "navigationBarTitleText": "西瓜科技演示",
                    "navigationBarTextStyle":"white",
                    "navigationBarBackgroundColor": "'.$color['theme'].'",
                    "backgroundTextStyle":"light"
                },
                "ext":{
                    "xgAppId":"'.$apps.'",
                    "appid":"'.$appid.'",
                    "themeColor":"'.$color['theme'].'",
                    "themeTextColor":"'.$color['text'].'",
                    "layoutType":"'.$layout.'",
                    "host":"https://weapp.xiguawenhua.com/"
                },
                "tabBar": {
                    "selectedColor": "'.$color['selected'].'",
                    "backgroundColor": "#fff",
                    "color":"#555",
                    "borderStyle": "black",
                    "list": [
                        {
                            "pagePath": "pages/index/index",
                            "text": "预定",
                            "iconPath": "images/icon_book.png",
                            "selectedIconPath": "images/icon_book_selected.png"
                            },
                            {
                            "pagePath": "pages/orderlist/orderlist",
                            "text": "订单",
                            "iconPath": "images/icon_order.png",
                            "selectedIconPath": "images/icon_order_selected.png"
                            },
                            {
                            "pagePath": "pages/mine/mine",
                            "text": "我的",
                            "iconPath": "images/icon_member.png",
                            "selectedIconPath": "images/icon_member_selected.png"
                        }
                    ]
                }
            }';
            break;
        case 3:
            //待定
            $ext_json = '{
                "extEnable": true,
                "extAppid": "'.$appid.'",
                "window": {
                    "navigationBarTitleText": "酒店预订",
                    "navigationBarTextStyle": "white",
                    "navigationBarBackgroundColor": "'.$color['theme'].'",
                    "backgroundTextStyle": "light",
                    "backgroundColor": "#272938"
                },
                "ext": {
                    "xgAppId":"'.$apps.'",
                    "appid":"'.$appid.'",
                    "themeColor":"'.$color['theme'].'",
                    "themeTextColor":"'.$color['text'].'",
                    "host": "https://weapp.xiguawenhua.com"
                },
                "tabBar": {
                        "selectedColor": "'.$color['selected'].'",
                        "backgroundColor": "#fff",
                        "color": "#555",
                        "borderStyle": "black",
                        "list": [
                            {
                            "pagePath": "pages/index/index",
                            "iconPath": "./img/images/un-home.png",
                            "selectedIconPath": "./img/images/'.$color['icon'].'-home.png",
                            "postion": "top",
                            "text": "预订"
                            },
                            {
                            "pagePath": "pages/order/order",
                            "iconPath": "./img/images/un-order.png",
                            "selectedIconPath": "./img/images/'.$color['icon'].'-order.png",
                            "text": "订单"
                            },
                            {
                            "pagePath": "pages/more/more",
                            "iconPath": "./img/images/un-more.png",
                            "selectedIconPath": "./img/images/'.$color['icon'].'-more.png",
                            "text": "我的"
                            }
                        ]
                    }
                }';
            break;
        default:
            $ext_json = 0;
    }
    return $ext_json;

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



/*
 * $path 路径
 * 获取文件夹下所有文件以及子文件下所有的文件*/
function scanFile($path) {
    global $result;
    $files = scandir($path);
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            if (is_dir($path . '/' . $file)) {
                scanFile($path . '/' . $file);
            } else {
                $result[] = basename($file);
            }
        }
    }
    return $result;
}


/*身份证号验证*/
function checkIdCard($idcard){
    // 只能是18位
    if(strlen($idcard)!=18){
        return false;
    }
    // 取出本体码
    $idcard_base = substr($idcard, 0, 17);
    // 取出校验码
    $verify_code = substr($idcard, 17, 1);
    // 加权因子
    $factor = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
    // 校验码对应值
    $verify_code_list = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
    // 根据前17位计算校验码
    $total = 0;
    for($i=0; $i<17; $i++){
        $total += substr($idcard_base, $i, 1)*$factor[$i];
    }
    // 取模
    $mod = $total % 11;
    // 比较校验码
    if($verify_code == $verify_code_list[$mod]){
        return true;
    }else{
        return false;
    }

}
