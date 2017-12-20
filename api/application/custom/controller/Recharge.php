<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/31 0031
 * Time: 17:32
 * 后台用户充值的方法
 */
namespace app\custom\controller;

use think\Controller;

class Recharge extends Action {

    public function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub
        //1
//        $this -> custom = model('custom') -> find(4);
//        $this -> data = input('get.');
//        $this->data['appid'] = '99395291';
        //1
        $this -> pay = db('system_pay') -> find();
    }

    /**
     * 微信充值到余额
     */
    public function wxRecharge(){
        if(!isset($this->data['recharge_money'])){
            $return['code'] = 10001;
            $return['msg_test'] = '参数缺失';
            return json($return);
        }else{
            $money = $this->data['recharge_money'] * 1;
            if($money <= 0){
                $return['code'] = 10002;
                $return['msg_test'] = '参数错误';
                return json($return);
            }
        }
        $data['custom_id'] = $this->custom->id;
        $data['money'] = $money;
        $data['order_sn'] = $this->custom->id . time() . mt_rand(1,9999);
        $data['create_time'] = time();
        $data['type'] = 0;
        $res = file_cache($data['order_sn'],$data);
        if($res){
            $weapp = new \app\weixin\controller\Common();
            $notify_url = 'https://'.$_SERVER['HTTP_HOST'].url('custom/NotifyAdmin/recharge');
            $code_url = $weapp -> get_qr_prepay_id($data['money'] * 100,$data['order_sn'],$data['order_sn'],'shop',$this->pay,$notify_url);
            /*输出核销二维码图片*/
//            import("Erweima",EXTEND_PATH,'.class.php');
//            $value = $code_url;
//            $errorCorrectionLevel = "H";
//            $matrixPointSize = "8";
//            \QRcode::png($value,false, $errorCorrectionLevel, $matrixPointSize,1);
            import('phpqrcode.phpqrcode',EXTEND_PATH,'.php');
            $data =$code_url;
            $level = 'L';
            $size =4;
            $QRcode = new \QRcode();
            ob_start();
            $QRcode->png($data,false,$level,$size,2);
            $imageString = base64_encode(ob_get_contents());
            ob_end_clean();
            $imageString = "data:image/jpg;base64,".$imageString;
            $return['code'] = 10000;
            $return['msg_test'] = 'ok';
            $return['data'] = $imageString;
            return json($return);
        }
    }

    /**
     * 支付宝充值到余额
     */
    public function zfbRecharge(){
    }

    public function zhifubaoNotify(){

    }

    /**
     * 微信购买小程序使用期限
     */
    public function wxBuyApp(){
        if(!isset($this->data['appid'])){
            $return['code'] = 10021;
            $return['msg_test'] = '参数值缺失';
            echo json_encode($return);exit;
        }
        if(!preg_match("/^\d{8}$/",$this -> data['appid'])){
            $return['code'] = 10022;
            $return['msg_test'] = 'appid格式不正确';
            echo json_encode($return);exit;
        }
        $info = db('app') -> field("type,custom_id") -> where("appid",$this->data['appid']) -> find();
        if($info['custom_id'] != $this -> custom -> id){
            $return['code'] = 10002;
            $return['msg_test'] = '小程序和用户不对照';
            return json($return);
        }
/*获取当前小程序的价格*/
        $is_true = db('app')->field('id,name,type,create_time,try_time,custom_id') -> where(['appid' => $this -> data['appid']]) -> find();
        $type = $is_true['type'];
        if(!isset($type)){
            $return['code'] = 10003;
            $return['msg'] = '小程序类型丢失';
            $return['msg_test'] = '小程序类型丢失';
            return json($return);
        }
        $user_id = $this -> custom ->id; //用户id
        $user = db('custom')->field("is_agency_user,is_belong")->where(['id'=>$user_id])->find(); //用户信息
        /*代理商的情况*/
        if($user['is_agency_user'] == 1 ){
            $where['type_auto'] = 1 ;
            $where['type_ssh'] = 1 ;
            $where['user_system'] = 1 ;
        }
        /*普通用户是超级管理员下的情况*/
        if($user['is_belong'] == 0 ){
            $where['type_auto'] = 1 ;
            $where['type_ssh'] = 2 ;
            $where['user_system'] = 1 ;
        }
        /*普通用户是代理商的情况下*/
        if($user['is_belong'] == 1 ){
            $where['type_auto'] = 2 ;
            $where['type_ssh'] = 2 ;
            $where['user_system'] = $user['id_agency'];
        }
        $where['type'] = $type;
        $setting = db('app_setting')->where($where)->find();
        $data['name'] = $is_true['name'];
        $data['price'] = $setting['price'];
        $data['zk'] = '';
        $data['all_money'] = $data['price'] - $data['zk'];
        $app_fee =  $data['all_money'];
/* 获取价格结束*/
//        $app_fee = get_app($info['type']);
//        $app_fee = $app_fee['fee'];
        if($app_fee <= 0){
            $return['code'] = 10003;
            $return['msg_test'] = '价格错误';
            return json($return);
        }
        $data['custom_id'] = $this->custom->id;
        $data['appid'] = $this->data['appid'];
        $data['money'] = $app_fee;
        $data['order_sn'] = $this->custom->id . time() . mt_rand(1,9999);
        $data['create_time'] = time();
        $data['type'] = 1;
        $data['year_num'] = $setting['year_num'];
        $res = file_cache($data['order_sn'],$data);
        if($res){
            $weapp = new \app\weixin\controller\Common();
            $notify_url = 'https://'.$_SERVER['HTTP_HOST'].url('custom/NotifyAdmin/wxBuyApp');
            $code_url = $weapp -> get_qr_prepay_id($data['money'] * 100,$data['order_sn'],$data['order_sn'],$data['name'],$this->pay,$notify_url);
            /*输出核销二维码图片*/
//            import("Erweima",EXTEND_PATH,'.class.php');
//            $value = $code_url;
//            $errorCorrectionLevel = "H";
//            $matrixPointSize = "8";
//            $path = 'wxqr/' . $this->custom->id . ".png";
//            \QRcode::png($value,$path, $errorCorrectionLevel, $matrixPointSize,1);
//            $return['code'] = 10000;
//            $return['msg_test'] = 'ok';
//            $return['data'] = $path;
//            return json($return);
            import('phpqrcode.phpqrcode',EXTEND_PATH,'.php');
            $data =$code_url;
            $level = 'L';
            $size =4;
            $QRcode = new \QRcode();
            ob_start();
            $QRcode->png($data,false,$level,$size,2);
            $imageString = base64_encode(ob_get_contents());
            ob_end_clean();
            $imageString = "data:image/jpg;base64,".$imageString;
            $return['code'] = 10000;
            $return['msg_test'] = 'ok';
            $return['data'] = $imageString;
            return json($return);
        }

    }


    /*微信购买小程序升级数量*/
    public function wxBuyAppNum(){

        if(!isset($this -> data['id'])){
            $return['code'] = 10002;
            $return['msg'] = '参数丢失';
            $return['msg_test'] = '参数丢失';
            return json($return);
        }
        $id = $this->data['id'];
        $info = db('app_num')->where(['id'=>$id])->find();
        $app_fee = $info['price'];
        if($app_fee <= 0){
            $return['code'] = 10003;
            $return['msg_test'] = '价格错误';
            return json($return);
        }
        $data['custom_id'] = $this->custom->id;
        $data['money'] = $app_fee;
        $data['order_sn'] = $this->custom->id . time() . mt_rand(1,9999);
        $data['create_time'] = time();
        $data['type'] = 1;
        $data['app_num'] = $info['app_num'];
        $res = file_cache($data['order_sn'],$data);
        if($res){
            $weapp = new \app\weixin\controller\Common();
            $notify_url = 'https://'.$_SERVER['HTTP_HOST'].url('custom/NotifyAdmin/wxBuyAppNum');
            $code_url = $weapp -> get_qr_prepay_id($data['money'] * 100,$data['order_sn'],$data['order_sn'],'app数量升级'.$data['app_num'],$this->pay,$notify_url);
            /*输出核销二维码图片*/
//            import("Erweima",EXTEND_PATH,'.class.php');
//            $value = $code_url;
//            $errorCorrectionLevel = "H";
//            $matrixPointSize = "8";
//            $path = 'wxqr/' . $this->custom->id . ".png";
//            \QRcode::png($value,$path, $errorCorrectionLevel, $matrixPointSize,1);
//            $return['code'] = 10000;
//            $return['msg_test'] = 'ok';
//            $return['data'] = $path;
//            return json($return);
            import('phpqrcode.phpqrcode',EXTEND_PATH,'.php');
            $data =$code_url;
            $level = 'L';
            $size =4;
            $QRcode = new \QRcode();
            ob_start();
            $QRcode->png($data,false,$level,$size,2);
            $imageString = base64_encode(ob_get_contents());
            ob_end_clean();
            $imageString = "data:image/jpg;base64,".$imageString;
            $return['code'] = 10000;
            $return['msg_test'] = 'ok';
            $return['data'] = $imageString;
            return json($return);
        }

    }




}