<?php
namespace app\custom\controller;
use think\Db;

class Setting extends Action{
    
    public function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub
        $this -> data = input('post.','','htmlspecialchars');
        if(!isset($this->data['appid'])){
            $return['code'] = 10100;
            $return['msg_test'] = '参数值缺失';
            echo json_encode($return);exit;
        }
        if(!preg_match("/^\d{8}$/",$this -> data['appid'])){
            $return['code'] = 10200;
            $return['msg_test'] = 'appid格式不正确';
            echo json_encode($return);exit;
        }
        $custom_id = db('app') -> where("appid",$this->data['appid']) -> value('custom_id');
        if($custom_id != $this->custom->id){
            $return['code'] = 10300;
            $return['msg_test'] = '当前app不是这个用户的';
            echo json_encode($return);exit;
        }
    }
    
    /**
     * 修改预约设置信息
     */
    public function updateSettingInfo(){
        $res = model('subscribe_service_setting') -> allowField(true)-> save($this->data,['appid' => $this->data['appid'],'custom_id' => $this->custom->id]);
        if($res){
            $return['code'] = 10000;
            $return['msg'] = '修改成功';
            return json($return);
        }else{
            $return['code'] = 10001;
            $return['msg'] = '修改失败';
            return json($return);
        }
    }
    
    /**
     * 获取预约设置信息
     */
    public function getSettingInfo(){
        $info = db('subscribe_service_setting') -> where(['appid' => $this->data['appid']]) -> select();
        $return['code'] = 10000;
        $return['msg_test'] = '查询成功';
        $return['data'] = $info;
        return json($return);
    }
    
}