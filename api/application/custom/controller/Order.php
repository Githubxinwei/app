<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/9 0009
 * Time: 17:19
 * 后台的关于订单的接口都在这里定义
 */
namespace app\custom\controller;
class Order extends Action{
    public function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub
        $this -> data = input("post.",'','htmlspecialchars');
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
        $custom_id = db('app') -> where("appid",$this->data['appid']) -> value('custom_id');
        if($custom_id != $this->custom->id){
            $return['code'] = 10020;
            $return['msg_test'] = '当前app不是这个用户的';
            echo json_encode($return);exit;
        }
    }

    public function getOrderList(){
        $num = isset($this->data['limit_num']) ? $this->data['limit_num'] : 10;
        $page = isset($this->data['page']) ? $this->data['page'] : 1;
        $state = isset($this->data['state']) ? $this->data['state'] : 1;
        $where = array();
        if(isset($this->data['username'])){
            if($this->data['username']){
                $where['username'] = $this->data['username'];
            }
        }
        if(isset($this->data['order_sn'])){
            if($this->data['order_sn']){
                $where['order_sn'] = $this -> data['order_sn'];
            }
        }
        if(isset($this->data['tel'])){
            if($this->data['tel']){
                $where['tel'] = $this->data['tel'];
            }
        }
        if(isset($this->data['starttime']) && isset($this->data['endtime'])){
            if($this->data['starttime'] && $this->data['endtime']){
                $where['create_time'] = ['between',[$this->data['starttime'],$this->data['endtime']]];
            }
        }
        $number = db('goods_order')
            -> where(['appid' => $this -> data['appid'],'state' => $state])
            -> where($where)
            -> count();
        $data = db('goods_order')
            -> field("id,state,username,prepay_time,price,order_sn")
            -> where(['appid' => $this -> data['appid'],'state' => $state])
            -> where($where)
            -> order('id desc')
            -> page($page,$num)
            -> select();
        $return['code'] = 10000;
        $return['data'] = $data;
        $return['number'] = $number;
        $return['msg_test'] = 'ok';
        return json($return);
    }

    /**
     * 获取订单详细的信息
     */
    public function getOrderById(){
        if(!isset($this->data['order_id'])){
            $return['code'] = 10001;
            $return['msg_test'] = '传递订单id,也就是订单列表返回去的id';
            return json($return);
        }
        $info = db('goods_order')
            -> alias('a')
            -> field("a.id,a.custom_id,a.username,a.tel,a.mail,concat(a.province,a.city,a.dist,a.address) address,a.zipcode,a.state,a.order_sn,b.name,b.pic,b.num,b.price,b.spec_value")
            -> join("__GOODS_CART__ b",'FIND_IN_SET(b.id,a.carts)','LEFT')
            -> where('a.id',$this->data['order_id'])
            -> group('b.id')
            -> select();
        if($info[0]['custom_id'] != $this->custom->id){
            $return['code'] = 10002;
            $return['msg_test'] = '当前订单的不是这个用户的';
            return json($return);
        }
        unset($info['custom_id']);
        $return['code'] = 10000;
        $return['data'] = $info;
        $return['msg_test'] = 'ok';
        return json($return);
    }

    /**
     * 修改状态
     */
    public function updateOrderState(){
        if(!isset($this->data['state']) || !isset($this->data['order_id'])){
            $return['code'] = 10001;
            $return['msg_test'] = '请传递参数';
            return json($return);
        }
        $state = db('goods_order') -> getFieldById($this->data['order_id'],'state');
        if($this->data['state'] == 2){
            if(!isset($this->data['kd_number']) || !isset($this -> data['kd_code'])){
                $return['code'] = 10002;
                $return['msg_test'] = '请传递订单号';
                return json($return);
            }
            if($state == 4){
                $return['code'] = 10003;
                $return['msg_test'] = '状态不可修改';
                return json($return);
            }
            $info['kd_number'] = $this->data['kd_number'];
            $info['kd_code'] = $this->data['kd_code'];
            $info['state'] = 2;
            $res = db('goods_order') -> where(['id' => $this->data['order_id'] * 1]) -> update($info);
            if($res){
                $return['code'] = 10000;
                $return['msg_test'] = 'ok';
                return json($return);
            }else{
                $return['code'] = 10004;
                $return['msg_test'] = '失败';
                return json($return);
            }

        }else if($this->data['state'] == 4){
            if($state == 0 || $state == 4){
                $return['code'] = 10003;
                $return['msg_test'] = '状态不可修改';
                return json($return);
            }
            $res = db('goods_order') -> where(['id' => $this->data['order_id']]) -> setField('state',4);
            if($res){
                $return['code'] = 10000;
                $return['msg_test'] = 'ok';
                return json($return);
            }else{
                $return['code'] = 10004;
                $return['msg_test'] = '失败';
                return json($return);
            }
        }
        $return['code'] = 10005;
        $return['msg_test'] = '网络错误';
        return json($return);

    }


    public function getSubscribeOrderList(){
        $num = isset($this->data['limit_num']) ? $this->data['limit_num'] : 10;
        $page = isset($this->data['page']) ? $this->data['page'] : 1;
        $where = array();
        $where['state'] = isset($this->data['state']) ? $this->data['state'] : 0;
        $where['appid'] = $this->data['appid'];
        if(isset($this->data['username'])){
            if($this->data['username']){
                $where['username'] = $this->data['username'];
            }
        }
        if(isset($this->data['order_sn'])){
            if($this->data['order_sn']){
                $where['order_sn'] = $this -> data['order_sn'];
            }
        }
        if(isset($this->data['tel'])){
            if($this->data['tel']){
                $where['tel'] = $this->data['tel'];
            }
        }
        if(isset($this->data['starttime']) && isset($this->data['endtime'])){
            if($this->data['starttime'] && $this->data['endtime']){
                $where['create_time'] = ['between',[$this->data['starttime'],$this->data['endtime']]];
            }
        }
        $count = db('subscribe_order')
            -> where($where)
            -> count();
        $data = db('subscribe_order')
            -> field("id,subscribe_name,username,create_time,price,state,order_sn")
            -> where($where)
            -> order('id desc')
            -> page($page,$num)
            -> select();
        $return['code'] = 10000;
        $return['data'] = $data;
        $return['number'] = $count;
        $return['msg_test'] = 'ok';
        return json($return);
    }

    /**
     * 获取订单详细的信息
     */
    public function getSubscribeOrderById(){
        if(!isset($this->data['order_id'])){
            $return['code'] = 10001;
            $return['msg_test'] = '传递订单id,也就是订单列表返回去的id';
            return json($return);
        }
        $info = db('subscribe_order')
            -> alias('a')
            -> field("a.id,a.appid,a.price,a.subscribe_name,a.subscribe_time,a.subscribe_time,a.username,a.tel,a.remark,a.state,a.create_time,a.order_sn,a.end_time,b.name,a.order_pic as pic")
            -> join("__SUBSCRIBE_SERVICE_USER__ b",'a.service_user_id = b.id','LEFT')
            -> where('a.id',$this->data['order_id'])
            -> find();
        if($info['appid'] != $this->data['appid']){
            $return['code'] = 10002;
            $return['msg_test'] = '当前订单的不是这个用户的';
            return json($return);
        }
        $return['code'] = 10000;
        $return['data'] = $info;
        $return['msg_test'] = 'ok';
        return json($return);
    }

    /**
     * 修改状态
     * 状态0(预约成功，待处理)1(后台已确定)2 后台已取消 3 已完成
     */
    public function updateSubscribeOrderState(){
        if(!isset($this->data['state']) || !isset($this->data['order_id'])){
            $return['code'] = 10001;
            $return['msg_test'] = '请传递参数';
            return json($return);
        }
        $state = db('subscribe_order') -> getFieldById($this->data['order_id'],'state');
        //管理员想确认订单
        if($this->data['state'] == 1){
            if($state != 0){
                $return['code'] = 10003;
                $return['msg_test'] = '状态不可修改';
                return json($return);
            }
            $info['state'] = 1;
            $res = db('subscribe_order') -> where(['id' => $this->data['order_id'] * 1]) -> update($info);
            if($res){
                $return['code'] = 10000;
                $return['msg_test'] = 'ok';
                return json($return);
            }else{
                $return['code'] = 10004;
                $return['msg_test'] = '失败';
                return json($return);
            }
        //管理员想取消订单
        }else if($this->data['state'] == 2){
            if($state != 0){
                $return['code'] = 10003;
                $return['msg_test'] = '状态不可修改';
                return json($return);
            }
            $res = db('subscribe_order') -> where(['id' => $this->data['order_id']]) -> setField('state',2);
            if($res){
                $return['code'] = 10000;
                $return['msg_test'] = 'ok';
                return json($return);
            }else{
                $return['code'] = 10004;
                $return['msg_test'] = '失败';
                return json($return);
            }
        //当用户去店消费后，管理员可以吧当前订单标记为已完成
        }else if($this->data['state'] == 3){
            if($state != 1){
                $return['code'] = 10003;
                $return['msg_test'] = '状态不可修改';
                return json($return);
            }
            $info['state'] = 3;
            $info['end_time'] = time();
            $res = db('subscribe_order') -> where(['id' => $this->data['order_id']]) -> update($info);
            if($res){
                $return['code'] = 10000;
                $return['msg_test'] = 'ok';
                return json($return);
            }else{
                $return['code'] = 10004;
                $return['msg_test'] = '失败';
                return json($return);
            }
        }
        $return['code'] = 10005;
        $return['msg_test'] = '网络错误';
        return json($return);

    }





































}