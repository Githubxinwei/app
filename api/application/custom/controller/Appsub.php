<?php
/**
 * Created by PhpStorm.
<<<<<<< HEAD
 * User: Administrator
=======
 * User: 宋妍妍
>>>>>>> be93a5e607cf4ce40839d9279fce0f12d3cb56fe
 * Date: 2017/10/27 0027
 * Time: 09:16
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
    }

///*获取bannar信息  appid*/
    function get_bannar(){
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
        $info = db('loop_img') -> where('appid',$this->apps) -> find();
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
        $keyword = isset($this->data['keyword']) ? $this->data['keyword'] : '';
        if($keyword){$where['service_name'] = array('like','%'.$keyword.'%');}
        if(isset($this->data['cate_id'])) $where[]=['exp',"FIND_IN_SET(".$this->data['cate_id'].",cate_id)"];
        $where['appid'] = $this->apps;
        if(isset($this->data['page'])){$page = $this->data['page'];}else{$page = 1;}
        if(isset($this->data['limit_num'])){$limit_num = $this->data['limit_num'];}else{$limit_num = 20;}
        $info = db('subscribe_service')
            -> field('id,service_name,service_pic,service_price')
            -> where($where)->page($page)
            -> limit($limit_num)
            -> order('id desc')
            -> select();
        $return['code'] = 10000;
        $return['data'] = $info;
        return json($return);

    }

   /*预约获取分类 appid*/
    function  sub_cate(){
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
       $info = db('subscribe_cate')-> field('id,name') -> where('appid',$this->apps) -> order('code desc') -> select();
       $return['code'] = 10000;$return['data'] = $info;
       return json($return);


   }

   /*服务人员列表  appid */
    function  get_user(){
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

        $info = db('subscribe_service_user')-> field('id,name,desc,pic') -> where('appid',$this->apps) -> order('id desc') -> select();
        $return['code'] = 10000;
        $return['data'] = $info;
        return json($return);
    }


    /*单个服务项目信息*/
    function get_subscribe(){

        if(!isset($this->data['id'])){
            $return['code'] = 10002;$return['msg_test'] = '商品不存在';
            return json($return);
        }
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
        $info = db('subscribe_service') -> where('id',$this->data['id'])->find();
        if($info['appid'] != $this->apps){
            $return['code'] = 10003;
            $return['msg'] = '商品不属于该商户';
            $return['msg_test'] = '商品不属于该商户';
            return json($return);
        }
        if($info['service_particulars']){
            $info['service_particulars'] = json_decode($info['service_particulars'],true);
            $spec = $info['service_particulars'];
            foreach($spec as $k=>$v){
                $spec[$k]=array(
                    'service_name'=>$v['service_name'],
                    'service_price'=>$v['service_price'],
                    'lastNum'=>$v['lastNum']
                );
            }
            $info['service_particulars'] = $spec;
        }
        unset($info['appid']);
        $return['code'] = 10000;
        $return['data'] = $info;
        return json($return);
    }


    /*生成预约订单*/
    function  create_order(){

        if(!isset($this -> data['username']) || !isset($this -> data['tell']) || !isset($this -> data['subscribe_time']) || !isset($this -> data['subscribe_day'])){
            $return['code'] = 10001;
            $return['msg'] = '信息不完善';
            $return['msg_test'] = '信息不完善';
            return json($return);
        }
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
        $data = $this->data;
        $data["create_time"] = time();
        $res = db('subscribe_order') ->allowField(true) -> save($data);

        if($res){
            $return['code'] = 10000;
            $return['msg'] = '预约成功';
            $return['msg_test'] = '预约成功';
        }else{
            $return['code'] = 10004;
            $return['msg'] = '预约失败';
            $return['msg_test'] = '预约失败';
        }


    }












    


}
