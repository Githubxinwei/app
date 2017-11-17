<?php
/**
 * Created by PhpStorm.
 * User: 宋妍妍
 * Date: 2017/11/14 0014
 * Time: 16:11
 * 酒店小程序的手机端接口
 */

namespace app\custom\controller;
use think\Controller;

class Rooms extends Xiguakeji
{

    /*构造函数*/
    public function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub
        $this -> data = input('post.','','htmlspecialchars');
        if(!isset($this -> data['appid'])){
            $return['code'] = 10002;
            $return['msg'] = 'appid不存在或者预约名字不存在';
            $return['msg_test'] = 'appid不存在或者预约名字不存在';
            return json($return);
        }
        if(!preg_match("/^\d{8}$/",$this -> data['appid'])){
            $return['code'] = 10003;
            $return['msg'] = 'appid是一个8位数';
            $return['msg_test'] = 'appid是一个8位数';
            return json($return);
        }
    }

    /*品牌信息*/
    public  function brands(){
        $where['appid'] = $this->data['appid'];
        $info = db('app')->field('id,site_url,start_time,over_time,address,tel,desc,name,pic')->where($where)->find();
        if($info){
            $return['code'] = 10000;
            $return['data'] = $info ;
            return json($return);
        }else{
            $return['code'] = 10001;
            $return['msg'] = '获取失败';
            $return['msg_test'] = '获取失败';
            return json($return);
        }
    }

    /*获取门店列表*/
    public function  stores(){

        $page = isset($this -> data['page']) ? $this -> data['page'] : 1;
        $limit = isset($this -> data['number']) ? $this -> data['number'] : 15;
        $stores = db("stores")
            ->where(['appid' => $this -> data['appid'],'state' => 1])
            ->page($page)
            ->limit($limit)
            ->order('id desc')
            ->select();


        foreach($stores as $k=>$v){

            $res = db('rooms')->where(['appid' => $v['appid']])->Min("price");
            $stores[$k]['price'] =$res;
            unset($wheres);

            $wheres[]=['exp',"FIND_IN_SET(".$v['id'].",stores_id)"];
            $wheres['appid'] = $v['appid'];

            $room = db('rooms')->where($wheres)->Sum('number_in');
            $stores[$k]['number'] = $room;
        }

        if($stores){
            $return['code'] = 10000;
            $return['data'] = $stores ;
            return json($return);
        }else{
            $return['code'] = 10004;
            $return['msg'] = "获取失败";
            $return['msg_test'] = '获取失败';
            return json($return);
        }

    }

    /*获取门店详细信息*/
    public function  stores_detail(){

        $where['appid'] = $this -> data['appid'];
        if(isset($this -> data['id'])){
            $where['id'] = $this -> data['id'];
        }

        $stores = db('stores')->where($where)->find();
        if($stores){
            $return['code'] = 10000;
            $return['data'] = $stores ;
            return json($return);
        }else{
            $return['code'] = 10004;
            $return['msg'] = "获取失败";
            $return['msg_test'] = '获取失败';
            return json($return);
        }

    }

    /*获取门店下的房间*/
    public  function  get_rooms(){
        $where['appid'] = $this -> data['appid'];
        if(isset($this->data['stores_id'])) $where[]=['exp',"FIND_IN_SET(".$this->data['stores_id'].",stores_id)"];
        $rooms = db('rooms')->field('id,photo,price,bed_type,room_type,number_in')->where($where)->select();
        foreach($rooms as $k=>$v){
            $pic = explode(',',$v['photo']);
            $rooms[$k]['photo'] = $pic[0];
        }
        if($rooms || empty($rooms)){
            $return['code'] = 10000;
            $return['data'] = $rooms ;
            return json($return);
        }else{
            $return['code'] = 10004;
            $return['msg'] = "获取失败";
            $return['msg_test'] = '获取失败';
            return json($return);
        }

    }

    /*获取房间详细信息*/
    public  function get_rooms_detail(){
        $where['appid'] = $this -> data['appid'];
        $where['id'] = $this -> data['id'];
        $rooms = db('rooms')->where($where)->find();
        $rooms['photo'] = explode(',',$rooms['photo']);
        if($rooms){
            $return['code'] = 10000;
            $return['data'] = $rooms ;
            return json($return);
        }else{
            $return['code'] = 10004;
            $return['msg'] = "获取失败";
            $return['msg_test'] = '获取失败';
            return json($return);
        }

    }


    /*酒店风格内容获取*/
    public function style_detail(){

        $where['appid'] = $this->data['appid'];
        $info = db('stores_style')->where($where)->find();
        if($info){
            $return['code'] = 10000;
            $return['data'] = $info ;
            return json($return);
        }else{
            $return['code'] = 10001;
            $return['msg'] = '获取失败';
            $return['msg_test'] = '获取失败';
            return json($return);
        }

    }


    /*生成酒店预约订单*/
    public  function create_order(){


        if(!isset($this -> data['start_time'])  || !isset($this -> data['end_time'])  || !isset($this -> data['username'])  || !isset($this -> data['user_tel'])){
            $return['code'] = 10007;
            $return['msg'] = '参数丢失';
            $return['msg_test'] = '参数丢失';
            return json($return);
        }
        if(!isset($this -> data['stores_id']) || !isset($this -> data['rooms_id'])){
            $return['code'] = 10008;
            $return['msg'] = '门店id或者房间id丢失';
            $return['msg_test'] = '门店id或者房间id丢失';
            return json($return);
        }


       $time = time();
       $order_sn = date('Y').$time.rand(1000,9999);

       $info = db("rooms")->field("room_type,price,bed_type,number_in")->where(['id' => $this->data['rooms_id']])->find();
        $name =$info['room_type'];
       $total_fee = $this->data['num'] * $info['price'];

       if($info['number_in'] <= 0){
           $return['code'] = 10008;
           $return['msg'] = '房间已满';
           $return['msg_test'] = '房间已满';
           return json($return);
       }

       $address = db("stores")->field('stores_address')->where(['id' => $this->data['stores_id']])->find();
       $data = $this->data;
       $data['user_id'] = $this->user['id'];
       $data['order_sn'] = $order_sn;
       $data['create_time'] = $time;
       $data['openid'] = $this->user['openid'];
       $data['room_type'] = $info['room_type'];
       $data['price'] = $info['price'];
       $data['total_price'] = $total_fee;
       $data['bed_type'] =$info['bed_type'];
       $data['address'] =$address['stores_address'];
       $data['end_time'] =strtotime($this->data['end_time']);
       $data['start_time'] =strtotime($this->data['start_time']);

        unset($data['session_key']);
        unset($data['apps']);
        $id = db('rooms_order')->insertGetId($data);

        $weapp = new \app\weixin\controller\Common($this->data['appid']);
        $order_id = $id;
        $attach = json_encode(['type'=>3,'id'=>$order_id]);//type值为3时，是酒店小程序的支付请求
        $prepay_id = $weapp -> get_prepay_id($this->user['openid'],$total_fee*100,$order_sn,$attach,'西瓜科技-'.$name);
        if(!$prepay_id){
            $return['code'] = 10005;$return['msg'] = '微信小程序参数配置有误';return json($return);
        }
        db('rooms_order') ->where(['id'=>$order_id])->update(['prepay_id'=>$prepay_id,'prepay_time'=>time()]);
        $return['code'] = 10000;
        $return['data']  = ['id'=>$order_id];
        $return['msg_test'] = '可以向付款页跳转了';
        return json($return);

    }

    /*订单详情页*/
    public function get_order_detail(){

        $where['id'] = $this->data['id'];
        $where['appid'] = $this->data['appid'];
        $where['user_id'] = $this->user['id'];
        $info = model('rooms_order')->where($where)->find();
        if($info){
            $return['code'] = 10000;
            $return['data'] = $info;
            return json($return);
        }else{
            $return['code'] = 10001;
            $return['msg'] = '订单错误';
            $return['msg_test'] = '订单错误';
            return json($return);
        }

    }

    //付款页核对订单，调起支付
    function pay(){

        if(!isset($this->data['id'])){
            $return['code'] = 10001;$return['msg_test'] = '缺少参数id';return json($return);
        }
        $order = db('rooms_order') -> where('id',$this->data['id']) -> find();
        if(empty($order) || $order['appid'] != $this->apps  || $order['user_id'] != $this->user['id'] ){
            $return['code'] = 10001;$return['msg_test'] = '订单不存在';return json($return);
        }
        if($order['state'] != 0 ){
            $return['code'] = 10001;$return['msg_test'] = '订单不是待付款状态';return json($return);
        }
        $weapp = new \app\weixin\controller\Common($this->apps);
        //prepay_id是否过期，过期重新生成
        if( time() - $order['prepay_time'] > 7200 ){
            $prepay_id = $weapp ->get_prepay_id($this->user['openid'],$order['total_price']*100,$order['order_sn'],$order['id'],'西瓜科技-'.$order['room_type']);
            db('rooms_order')->where(['id'=>$order['id']])-> update(['prepay_id'=>$prepay_id,'prepay_time'=>time()]);
        }else{
            $prepay_id = $order['prepay_id'];
        }
        $return['code'] = 10000;
        $return['msg_test'] = 'data内数据即调起支付所需参数，无需进行加密操作，直接使用';
        $return['data'] = $weapp -> paysign($prepay_id);
        return json($return);
    }

    //订单列表，未付款，已付款，已确定，退款中,已退款
    public  function order_list(){
        if( !isset($this->data['type']) || !in_array($this->data['type'], [0,1,2]) ){
            $return['code'] = 10001;$return['msg_test'] = '缺少订单类型type';return json($return);
        }
        $page = isset($this->data['page']) ? $this->data['page'] : 1 ;
        $limit_num = isset($this->data['limit_num']) ? $this->data['limit_num'] : 10 ;
        $where['user_id'] = $this->user['id'];
        $where['state'] = $this->data['type'];
        $where['appid'] = $this->apps;
        //->alias('a')->join($join)  -> where($where)
        $info = db('rooms_order')
            -> where($where)
            -> page($page)
            -> limit($limit_num)
            -> order('id desc')
            -> select();

        $return['code'] = 10000;
        $return['msg_test'] = '加载成功';
        $return['data'] = $info;
        return json($return);

    }

    /*订单详情  appid  id*/
    public  function  get_order(){

       $where['appid'] = $this->data['appid'];
       $where['user_id'] = $this->user['id'];
       $where['id'] = $this->data['id'];

       $info = db('rooms_order')->where($where)->find();

       if($info){
           $return['code'] = 10000;
           $return['data'] = $info;
           return json($return);
       }else{
           $return['code'] = 10001;
           $return['msg'] = '网络错误';
           $return['msg_test'] = '网络错误';
           return json($return);
       }



   }

    /*退款申请*/
    public  function  refunds(){

       $where['appid'] = $this->data['appid'];
       $where['id'] = $this->data['id'];
       $where['user_id'] = $this->this->user['id'];
       $data['state'] = 2;
       $data['is_refunds'] = 1;

       $check  = db('rooms_order')->field("id,is_checkin")->where($where)->find();
        if($check['is_check_in'] != 0){
            $return['code'] = 10004;
            $return['msg'] = '对不起，您无法进行此操作';
            $return['msg_test'] = '对不起，您无法进行此操作';
            return json($return);
        }

       $res = db('rooms_order')->where($where)->update($data);
       if($res){
           $return['code'] = 10000;
           $return['msg'] = '申请退款成功，退款将于三个工作日内退还至您的微信钱包';
           $return['msg_test'] = '申请退款成功，退款将于三个工作日内退还至您的微信钱包';
           return json($return);
       }else{
           $return['code'] = 10001;
           $return['msg'] = '网络错误';
           $return['msg_test'] = '网络错误';
           return json($return);
       }





    }


}