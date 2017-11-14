<?php
/**
 * Created by PhpStorm.
 * User: 宋妍妍
 * Date: 2017/10/27 0027
 * Time: 09:16
 * 预约小程序的前台接口
 */

namespace app\custom\controller;
use think\Controller;
use think\db;

class Appsub  extends Xiguakeji
{

    public function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub
        $this -> data = input('post.','','htmlspecialchars');

        if(!isset($this -> data['appid'])){
            $return['code'] = 10002;
            $return['msg'] = 'appid不存在';
            $return['msg_test'] = 'appid不存在';
            return json($return);
        }
        if(!preg_match("/^\d{8}$/",$this -> data['appid'])){
            $return['code'] = 10003;
            $return['msg'] = 'appid是一个8位数';
            $return['msg_test'] = 'appid是一个8位数';
            return json($return);
        }

    }

    /*获取bannar信息  appid*/
    function get_bannar(){

        $where['appid'] = $this->data['appid'];
        $info = db('loop_img') -> where($where) -> find();
        $return['code'] = 10000;
        if($info){
            $info['content'] = json_decode($info['content'],true);
            $return['data'] = $info['content'];
        }else{
            $return['data'] = '';
        }
        return json($return);
    }

    /*获取预约列表 appid*/
    function lists(){

        $keyword = isset($this->data['keyword']) ? $this->data['keyword'] : '';
        if($keyword){$where['service_name'] = array('like','%'.$keyword.'%');}
        if(isset($this->data['cate_id'])) $where[]=['exp',"FIND_IN_SET(".$this->data['cate_id'].",cate_id)"];
        $where['appid'] = $this->data['appid'];
        if(isset($this->data['page'])){$page = $this->data['page'];}else{$page = 1;}
        if(isset($this->data['limit_num'])){$limit_num = $this->data['limit_num'];}else{$limit_num = 20;}
        $info = db('subscribe_service')
            -> field('id,service_name,service_pic,service_price')
            -> where($where)->page($page)->limit($limit_num)
            -> order('id desc')
            -> select();
        $return['code'] = 10000;
        $return['data'] = $info;
        return json($return);

    }

   /*预约获取分类 appid*/
    function  sub_cate(){

        $where['appid'] = $this->data['appid'];
       $info = db('subscribe_cate')-> field('id,name') -> where($where) -> order('code desc') -> select();
       foreach($info as $k=>$v){
           $goods = db("subscribe_service")->field('service_pic')->where("cate_id",$v['id'])->order("id desc")->find();
           $info[$k]['cata_pic'] = $goods['service_pic'];
       }


       $return['code'] = 10000;$return['data'] = $info;
       return json($return);


   }

   /*服务人员列表  appid  service_id*/
    function  get_user(){

        $info = db('subscribe_service_user')
            -> where(['appid'=>$this->data['appid']])
            -> order('id desc')
            -> select();

        $arr = [];
        foreach($info as $k=>$v){
            $v['service_id'] = explode(',',$v['service_id']);      
            if(in_array($this->data['service_id'], $v['service_id'])){

	       array_push($arr,$v);
            }
        }
        $return['code'] = 10000;
        $return['data'] = $arr;
        return json($return);
    }

    /*单个服务项目信息 appid  id*/
    function get_subscribe(){
        if(!isset($this->data['id'])){
            $return['code'] = 10002;$return['msg_test'] = '商品不存在';
            return json($return);
        }

        $info = db('subscribe_service') -> where('id',$this->data['id'])->find();
        if($info['appid'] != $this-> data['appid']){
            $return['code'] = 10004;
            $return['msg'] = '预约服务不属于该商户';
            $return['msg_test'] = '预约服务不属于该商户';
            return json($return);
        }
        if($info['service_particulars']){
            $info['service_particulars'] = json_decode($info['service_particulars'],true);
        }
        /*基本设置*/
        $setting = db('subscribe_service_setting')
            ->field("id,is_show_address,is_show_tel,button_name,appid")
            -> where('appid',$this->data['appid'])
            ->find();
        unset($info['appid']);
        $return['code'] = 10000;
        $return['setting'] = $setting;
        $return['data'] = $info;
        return json($return);
    }

    /*生成预约订单*/
    /*
      appid  custom_id  subscribe_id  user_id  subscribe_day
      subscribe_time   username  tell  remark
   */
    function  create_order(){

        if(!isset($this -> data['username']) || !isset($this -> data['tel']) || !isset($this -> data['subscribe_time'])){
            $return['code'] = 10001;
            $return['msg'] = '信息不完善';
            $return['msg_test'] = '信息不完善';
            return json($return);
        }
        if(!isset($this->data['subscribe_user_id'])){
            $this->data['subscribe_user_id'] ='';
        }
        $data = $this->data;
        $data["user_id"] = $this->user['id'];
        $data["create_time"] = time();
        $data["order_sn"] = date("YmdHis").rand(100,999);
        $goods = db('subscribe_service')
            ->field("id,service_name,service_price")
            ->where("id",$data['subscribe_id'])
            ->find();
        $data['subscribe_name'] = $goods['service_name'];
        $data['price'] = $goods['service_price'];
        $res = model('subscribe_order')->allowField(true)->save($data);
        if($res){
            $return['code'] = 10000;
            $return['msg'] = '预约成功';
            $return['msg_test'] = '预约成功';
            return json($return);
        }else{
            $return['code'] = 10004;
            $return['msg'] = '预约失败';
            $return['msg_test'] = '预约失败';
            return json($return);
        }
    }

   /*获取订单列表   appid  state  custom_id */
    function order_list(){
        if( !isset($this->data['state']) || !in_array($this->data['state'], [0,1,2,3]) ){
            $return['code'] = 10001;
            $return['msg'] = '缺少订单类型state';
            $return['msg_test'] = '缺少订单类型state';
            return json($return);
        }

        $page = isset($this->data['page']) ? $this->data['page'] : 1 ;
        $limit_num = isset($this->data['limit_num']) ? $this->data['limit_num'] : 10 ;
        $where['user_id'] = $this->user['id'];
        $where['state'] = $this->data['state'];
        $where['appid'] = $this->data['appid'];
        $info = db('subscribe_order')
            ->field('id,price,subscribe_name,subscribe_id,service_user_id,subscribe_time,username,tel,remark,create_time,state')
            -> where($where)
            -> page($page)
            -> limit($limit_num)
            -> order('id desc')
            -> select();
        foreach($info as $k=>$v){
            $sub_id = db('subscribe_service') ->field("service_pic")->where("id",$v['subscribe_id']) -> find();
            $info[$k]['service_pic']= $sub_id['service_pic'];
           if($v['service_user_id'] != 0){
               $user_id = db('subscribe_service_user') ->field("name")->where("id",$v['service_user_id']) -> find();
               $info[$k]['user_name']= $user_id['name'];
           }

        }
        $return['code'] = 10000;
        $return['data'] = $info;
        return json($return);

    }

    /*获取订单详情信息 appid  id  custom_id */
    function order_detail(){

        $where['appid'] = $this->data['appid'];
        $where['id'] = $this->data['id'];
        $where['user_id'] = $this->user['id'];
        $info = db('subscribe_order') -> where($where) -> find();
        $sub_id = db('subscribe_service') ->field("service_pic")->where("id",$info['subscribe_id']) -> find();
        $info['service_pic']= $sub_id['service_pic'];
        if($info['service_user_id'] != 0) {
            $user_id = db('subscribe_service_user') ->field("name")->where("id",$info['service_user_id']) -> find();
            $info['user_name']= $user_id['name'];
        }

        if($info){
            $return['code'] = 10000;
            $return['data'] = $info;
            return json($return);
        }else{
            $return['code'] = 10004;
            $return['msg'] = '获取订单失败';
            $return['msg_test'] = '获取订单失败';
            return json($return);
        }

    }

    /*预约取消   id:预约订单ID   appid    */
    function order_close(){


        
    }

    /*获取小程序的基本信息*/
    function get_message(){
        $where['appid'] = $this->data['appid'];
        $info = db('app')
            -> field('name,pic,desc,tel,site_url,address,is_publish,custom_id,start_time,over_time,business')
            -> where($where)
            -> find();

        if($info){
            $return['code'] = 10000;
            $return['data'] = $info;
            return json($return);
        }else{
            $return['code'] = 10004;
            $return['msg'] = '基本信息获取失败';
            $return['msg_test'] = '基本信息获取失败';
            return json($return);
        }
    }

    /*获取预约程序的服务时间*/
    function get_time(){

        $where['appid'] = $this->data['appid'];
        $info = db('app')
            -> field('start_time,over_time,business')
            -> where($where)
            -> find();

        if($info){
            $return['code'] = 10000;
            $return['data'] = $info;
            return json($return);
        }else{
            $return['code'] = 10004;
            $return['msg'] = '基本信息获取失败';
            $return['msg_test'] = '基本信息获取失败';
            return json($return);
        }
    }



}
