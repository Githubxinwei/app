<?php
namespace app\index\controller;

class Index extends Action
{
    public function index()
    {
    	dump($this->user->nickname);
	\Think\loader::import('Wechat.auth');
    	$auth = new \auth();
    	$appid = $auth -> get_appid();
    	return $appid;exit;
    	$res = $auth -> get_access_token($code);
    	exit;

    	\Think\loader::import('Wechat.function');
    	$wechat = new \Client();
    	$arr = array(1,2,2,3,4,5666,545,6,6,66);
    	$FILE = $wechat -> getSignPackage();dump($FILE);EXIT;
    	//put_qrcode();
    	
    	//$logo =  './static/qr/head.jpg';
    	//$name = 'user';
    	////自定义logo，自定义名称，自定义路径
    	$logo = null;
    	$name = null;
    	$path = null;//目录默认保存在static下，如果没有传值，系统路径为./static/qrcode/date("Ymd")/
    	$value = '344';
   	$qr = put_qrcode($value,$name,$path,$logo,true);dump($qr);
   	return '<img src="'.$qr.'">';
   	
    }
}
