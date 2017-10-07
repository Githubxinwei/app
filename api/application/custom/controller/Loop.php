<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/9/30 0030
 * Time: 09:13
 * 关于轮播图的接口都在这里定义
 */
namespace app\custom\controller;
class Loop extends Action{
    public function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub
        $this -> data = input("post.",'','htmlspecialchars');
        if(!isset($this->data['appid'])){
            $return['code'] = 10001;
            $return['msg_test'] = '参数值缺失';
            echo json_encode($return);exit;
        }
        if(!preg_match("/^\d{8}$/",$this -> data['appid'])){
            $return['code'] = 10002;
            $return['msg_test'] = 'appid格式不正确';
            echo json_encode($return);exit;
        }
    }

    /**
     * 获取轮播图的信息
     * appid
     */
    public function getLoopImgList(){
        $info = db('loop_img') -> where(['appid' => $this->data['appid']]) -> find();
        if(!$info){
            $return['code'] = 10003;
            $return['msg_test'] = 'appid不正确或者当前用户没有此小程序,或或者没有创建轮播图';
            return json($return);
        }else{
            if($info['custom_id'] != $this->custom->id){
                $return['code'] = 10004;
                $return['msg_test'] = '当前的小程序不是这个用户的';
                return json($return);
            }
            $return['code'] = 10000;
            $return['data'] = $info;
            $return['msg_test'] = 'ok';
            return json($return);
        }
    }

    /**
     * 获取所有的商品的id,name
     * appid
     */
    public function getAllShopInfo(){
        $info = db('goods') -> where(['appid' => $this->data['appid']]) -> column('name','id');
        $return['code'] = 10000;
        $return['data'] = $info;
        $return['msg_test'] = 'ok';
        return json($return);
    }

    /**
     * 获取所有的分类的信息
     */
    public function getAllCateInfo(){
        $info = db('goods_cate') -> where(['appid' => $this->data['appid']]) -> column('name','id');
        $return['code'] = 10000;
        $return['data'] = $info;
        $return['msg_test'] = 'ok';
        return json($return);
    }

    /**
     * 获取所有的页面的信息
     */
    public function getAllPageInfo(){
        $data = array(
            'Pages/order/order' => '订单',
            'Pages/User/index' => '我的中心',
        );
        $return['code'] = 10000;
        $return['data'] = $data;
        $return['msg_test'] = 'ok';
        return json($return);
    }

    /**
     * 删除数据
     * appid,loop_id
     */
    public function delLoopInfo(){
        if(!isset($this->data['loop_id'])){
            $return['code'] = 10002;
            $return['msg_test'] = '缺少参数值';
            return json($return);
        }
        $res = db('loop_img') -> where(['id' => $this->data['loop_id'],'custom_id' => $this->custom->id]) -> delete();
        if($res){
            $return['code'] = 10000;
            $return['msg_test'] = 'ok';
            return json($return);
        }else{
            $return['code'] = 10003;
            $return['msg_test'] = 'appid不对或者loop_id不存在';
            return json($return);
        }
    }

    /**
     * 设置轮播图的，接受前台传递过来的参数
     * appid,info
     */
    public function setLoopImg(){
        if(!isset($this -> data['info'])){
            $return['code'] = 10002;
            $return['msg_test'] = '参数值缺失';
            return json($return);
        }
        $info = $_GET['info'];

        $info = json_decode($info,true);
        if(is_null($info)){
            $return['code'] = 10003;
            $return['msg_test'] = '数据格式不正确';
            return json($return);
        }
        $res = db('loop_img') -> field('id') -> where(['appid' => $this->data['appid']]) -> find();
        if($res){
            $re = db('loop_img') -> where(['id' => $res['id']]) -> setField('content',$_GET['info']);
        }else{
            $loopInfo['content'] = $_GET['info'];
            $loopInfo['appid'] = $this->data['appid'];
            $loopInfo['custom_id'] = $this->custom->id;
            $re = db('loop_img') -> insertGetId($loopInfo);
        }
        if($re){
            $return['code'] = 10000;
            $return['msg_test'] = '成功';
            return json($return);
        }else{
            $return['code'] = 10004;
            $return['msg_test'] = '失败';
            return json($return);
        }

    }


}