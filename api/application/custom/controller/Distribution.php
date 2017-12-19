<?php
/**
 * Created by PhpStorm.
 * User: 李佳飞
 * Date: 2017/12/14 0014
 * Time: 16:02
 * 后台管理员为每个小程序设置分销，设置分销的只有小程序有支付的时候，才能启用分销
 */
namespace app\custom\controller;

class Distribution extends Action{
    
    public function _initialize(){
        parent::_initialize(); // TODO: Change the autogenerated stub
        $this -> data = input('post.','','htmlspecialchars');
        if(!isset($this->data['appid'])){
            $return['code'] = 10100;
            $return['msg_test'] = 'appid参数值缺失';
            echo json_encode($return);exit;
        }
        if(!preg_match("/^\d{8}$/",$this -> data['appid'])){
            $return['code'] = 10200;
            $return['msg_test'] = 'appid格式不正确';
            echo json_encode($return);exit;
        }
        $custom_id = db('app') -> where(['appid' => $this->data['appid']]) -> value('custom_id');
        if($custom_id != $this->custom->id){
            $return['code'] = 10300;
            $return['msg_test'] = '当前app不是这个用户的';
            echo json_encode($return);exit;
        }
    }
    //分销详情
    public function getDistInfo() {
        $info = db('dist_rule')
            -> field('appid,switch,level,scale,type,good_list,is_withdraw,withdraw_type')
            ->where(['appid' => $this->data['appid']]) -> find();

        $return['code'] = 10000;
        $return['msg_test'] = '查询成功';
        $return['data'] = $info;
        return json($return);
        
    }
    //分销设置
    public function setDist() {
        if (!isset($this->data['is_withdraw']) || !isset($this->data['type']) || !isset($this->data['scale']) || !isset($this->data['level']) || !isset($this->data['switch'])) {
            $return['code'] = 10001;
            $return['msg_test'] =  '参数缺失';
            return  json($return);
        }
        $dist_data = array();
		$dist_data['appid'] = $this->data['appid'];
		$dist_data['custom_id'] = $this->custom['id'];
		$dist_data['is_withdraw'] = $this->data['is_withdraw'];
		$dist_data['type'] = $this->data['type'];
		$dist_data['scale'] = $this->data['scale'];
		$dist_data['level'] = $this->data['level'];
		$dist_data['switch'] = $this->data['switch'];
		$dist_data['type'] = $this->data['type'];
		$dist_data['good_list'] = $this->data['good_list'];
		$dist_data['withdraw_type'] = $this->data['withdraw_type'];
        $id = db('dist_rule')->where(['appid' => $dist_data['appid']])->value('id');
        if (!$id ) {
            $res = db('dist_rule')->insert($dist_data);
            if($res){
                $return['code'] = 10000;
                $return['msg'] = '保存成功';
                return json($return);
            }else{
                $return['code'] = 10010;
                $return['msg'] = '网络错误,保存失败';
                return json($return);
            }
        } else {
            $res = db('dist_rule')->where(['id' => $id])->update($dist_data);
            if(isset($res)){
                $return['code'] = 10000;
                $return['msg'] = '保存成功';
                return json($return);
            }else{
                $return['code'] = 10010;
                $return['msg'] = '网络错误,保存失败';
                return json($return);
            }
        }
    }

    /*分销记录*/
    public  function dist_list(){

        $limit = isset($this->data['limit']) ? $this->data['limit'] : 10;
        $page = isset($this->data['page']) ? $this->data['page'] : 1;

        if (!isset($this->data['appid'])) {
            $return['code'] = 10002;
            $return['msg_test'] =  '小程序参数丢失';
            return  json($return);
        }
        $info = db('dist_record')
            -> alias('a')
            -> field('a.id,a.order_id,a.user_id,a.xj_userid,a.money,a.level,a.create_time,a.type,b.nickName as user_nickName,c.nickName as xj_nickName')
            -> join("__USER__ b",'a.user_id = b.id','LEFT')
            -> join("__USER__ c",'a.xj_userid = c.id','LEFT')
            -> where(['appid' => $this->data['appid']])
            -> page($page,$limit)
            -> order('a.create_time desc')
            -> select();

        $return['code'] = 10010;
        $return['data'] = $info ;
        return json($return);

    }
}