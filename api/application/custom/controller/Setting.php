<?php
namespace app\custom\controller;
use think\Db;

class Setting extends Action{
    
    /**
     * 修改预约设置信息
     */
    public function updateSettingInfo(){
        $res = db('subscribe_service_setting') -> allowField(true) -> where(['appid' => $this->data['appid'],['custom_id' => $this->custom->id]) -> save($this->data);
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