<?php
namespace app\custom\controller;
use think\Db;

class Setting extends Action{
    
    /**
     * 获取预约设置信息
     */
    public function getSettingInfo(){
        $info = db('subscribe_service_setting') -> field('id,button_name') -> where(['appid' => $this->data['appid']]) -> select();
        $return['code'] = 10000;
        $return['msg_test'] = '查询成功';
        $return['data'] = $info;
        return json($return);
    }
}