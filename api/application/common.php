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
        1=>['code'=>1,'name'=>'电商小程序','pic'=>'http://www.xiguakeji.cc/images/logo-2.png','fee'=>100],
        2=>['code'=>2,'name'=>'预约小程序','pic'=>'http://www.xiguakeji.cc/images/logo-2.png','fee'=>50]
    ];
    if($type == 'all'){
        return $arr;
    }else{
        return isset($arr[$type]) ? $arr[$type] : false;
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

//获取8位的整形的字符串
function getNumber(){
    return mt_rand(10000000,99999999);
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