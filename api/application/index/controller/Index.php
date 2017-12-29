<?php
namespace app\index\controller;
//测试支付
use think\Controller;
use think\View;

class Index extends Controller{
    public function index(){
        //于春凤的商城23542640 酒店89618248
        $weapp = new \app\weixin\controller\Common(23542640);
        $res = $weapp -> get_latest_auditstatus();
        dump($res);
//        $res = $weapp->get_qrcode();
//        $res = $weapp->get_qrcodes();
//        db('app') -> where('appid',89618248) ->update(['is_publish'=>4]);//标注已发布
    }

    public function index1(){

    }




}
