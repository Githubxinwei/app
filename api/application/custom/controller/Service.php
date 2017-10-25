<?php
namespace app\custom\controller;
use think\Db;

class Service extends Action{
    
    public function _initialize()
	{
		parent::_initialize(); // TODO: Change the autogenerated stub
		$this -> data = input('get.','','htmlspecialchars');
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
	
	//添加服务项目
	public function createServiceItems(){
	    if(!isset($this -> data['service_name']) || (!isset($this -> data['service_price'])) || !isset($this -> data['appid'])){
	        $return['code'] = 10001;
	        $return['msg_test'] = '请求参数不存在';
	        return json($return);
	    }
	    //服务名称和价格是必须的
	    if(isset($this -> data['service_price'])){
	        if($this -> data['service_price'] && $this -> data['service_price'] <= 0){
	            $return['code'] = 10004;
	            $return['msg'] = '请填写正确的商品价格';
	            return json($return);
	        }
	    }
	    if(!preg_match("/^\d{8}$/",$this -> data['appid'])){
	        $return['code'] = 10006;
	        $return['msg_test'] = 'appid是一个8位数';
	        return json($return);
	    }
	
	    $is_true = db('app') -> where(['appid' => $this -> data['appid']]) -> find();
	    if(!$is_true){
	        $return['code'] = 10011;
	        $return['msg_test'] = '当前用户没有此小程序,也就是appid不对';
	        return json($return);
	    }
	    if($is_true['custom_id'] != $this -> custom->id){
	        $return['code'] = 10011;
	        $return['msg_test'] = '当前小程序不是这个用户的';
	        return json($return);
	    }
	    //如果上传图片，判断图片是否是十个
	    if(isset($this -> data['service_pic'])){
	        $pic_number = count(explode(',',$this -> data['service_pic']));
	        if($pic_number > 10){
	            $return['code'] = 10007;
	            $return['msg'] = '一个商品最多上传10张图片';
	            return json($return);
	        }
	    }
	    if(isset($this -> data['service_desc'])){
	        if(mb_strlen($this -> data['service_desc'],'utf8') > 600){
	            $return['code'] = 10008;
	            $return['msg'] = '商品的简介最多600字';
	            return json($return);
	        }
	    }
	    $this -> data['create_time'] = time();
	    $this -> data['custom_id'] = $this -> custom -> id;
	    /* if(isset($this -> data['spec'])){
	        $this -> data['spec'] = $_POST['spec'];
	        $info = json_decode($this -> data['spec'],true);
	        if(is_null($info)){
	            $return['code'] = 10009;
	            $return['msg_test'] = '数据格式不正确';
	            return json($return);
	        }
	    } */
	    $res= model('subscribe_service') ->allowField(true)-> save($this -> data);
	    if($res){
	        $return['code'] = 10000;
	        $return['data'] = ['subscribe_service_id' => model('subscribe_service')->id];
	        $return['msg'] = '添加成功';
	        return json($return);
	    }else{
	        $return['code'] = 10010;
	        $return['msg'] = '添加失败';
	        return json($return);
	    }
	}
	
	//获取服务项目列表
	public function getServiceItems(){
	    if(!isset($this -> data['appid'])){
	        $return['code'] = 10001;
	        $return['msg_test'] = '当前小程序的appid没有';
	        return json($return);
	    }
	    if(!preg_match("/^\d{8}$/",$this -> data['appid'])){
	        $return['code'] = 10002;
	        $return['msg_test'] = 'appid是一个8位数';
	        return json($return);
	    }
	    $custom_id = $this -> custom -> id;
	    $is_true = db('app') -> where(['appid' => $this -> data['appid']]) -> find();
	    if(!$is_true){
	        $return['code'] = 10003;
	        $return['msg_test'] = '当前用户没有此小程序,也就是appid不对';
	        return json($return);
	    }
	    if($is_true['custom_id'] != $this->custom->id){
	        $return['code'] = 10005;
	        $return['msg_test'] = '当前小程序不是这个用户的';
	        return json($return);
	    }
	    $page = isset($this -> data['page']) ? $this -> data['page'] : 1;
	    $limit = isset($this -> data['number']) ? $this -> data['number'] : 15;
	    $where = '';
	    if(isset($this -> data['cate_id'])){
	        $cate_id = $this -> data['cate_id'] * 1;
	        $where = "FIND_IN_SET($cate_id,cid)";
	    }
	    $number = db('subscribe_service') -> where(['appid' => $this -> data['appid']]) -> count();
	    $info = db('subscribe_service')
	    -> where(['appid' => $this -> data['appid']])
	    -> where($where)
	    -> page($page,$limit)
	    -> order('id desc')
	    -> select();
	    if($info){
	        $return['code'] = 10000;
	        $return['data'] = ['number' => $number,'info' => $info];
	        $return['msg_test'] = '成功了';
	        return json($return);
	    }else{
	        $return['code'] = 10004;
	        $return['msg'] = '查询失败,请稍后重试';
	        return json($return);
	    }
	
	}

    /**
     * 创建预约小程序的服务人员
     */
	public function createServiceUser(){
        if(!isset($this -> data['name']) || !isset($this->data['desc']) || !isset($this->data['pic']) || !isset($this->data['service_id'])){
            $return['code'] = 10001;
            $return['msg_test'] = '参数值缺失';
            return json($return);
        }
        $info['name'] = $this->data['name'];
        $info['pic'] = $this->data['pic'];
        $info['desc'] = $this->data['desc'];
        $info['service_id'] = $this->data['service_id'] * 1;
        $info['appid'] = $this->data['appid'] * 1;
        $info['custom_id'] = $this->custom->id;
        $res = db('subscribe_service_user') -> insert($info);
        if($res){
            $return['code'] = 10000;
            $return['msg'] = '添加成功';
            return json($return);
        }else{
            $return['code'] = 10002;
            $return['msg'] = '添加失败';
            return json($return);
        }

    }

    /**
     * 删除预约人员
     */
    public function delServiceUser(){
        if(!isset($this->data['service_user_id'])){
            $return['code'] = 10001;
            $return['msg_test'] = '参数值缺失';
            return json($return);
        }
        $res = db('subscribe_service_user') -> where(['id' => $this->data['service_user_id'],'custom_id' => $this->custom->id]) -> delete();
        if($res){
            $return['code'] = 10000;
            $return['msg'] = '删除成功';
            return json($return);
        }else{
            $return['code'] = 10002;
            $return['msg'] = '删除失败';
            return json($return);
        }
    }

    /**
     * 修改预约人员
     */
    public function updateServiceUser(){
        if(!isset($this->data['service_user_id'])){
            $return['code'] = 10001;
            $return['msg_test'] = '参数值缺失';
            return json($return);
        }
        $res = model('subscribe_service_user') -> allowField(['name','pic','desc']) -> where(['id' => $this->data['service_user_id']]) -> save($this->data);
        if($res){
            $return['code'] = 10000;
            $return['msg'] = '修改成功';
            return json($return);
        }else{
            $return['code'] = 10002;
            $return['msg'] = '修改失败';
            return json($return);
        }
    }

    /**
     * 获取预约人员的列表
     */
    public function getServiceUser(){
        $page = isset($this -> data['page']) ? $this -> data['page'] : 1;
        $limit = isset($this -> data['number']) ? $this -> data['number'] : 15;
        $count = db('subscribe_service_user') -> where(['appid' => $this->data['appid']]) -> count();
        $info = db('subscribe_service_user')
            -> alias('a')
            -> field('a.name,a.pic,a.desc,b.service_name')
            -> join("__SUBSCRIBE_SERVICE__ as b",'a.service_id = b.id','LEFT')
            -> where("appid = :appid",['appid' => $this->data['appid']])
            -> page($page,$limit)
            -> select();
        $return['code'] = 10000;
        $return['msg_test'] = '查询成功';
        $return['data'] = ['number' => $count,'info' => $info];
        return json($return);
    }

    /**
     * 获取当前预约小程序的预约项目
     */
    public function getServiceInfo(){
        $info = db('subscribe_service') -> field('id,service_name') -> where(['appid' => $this->data['appid']]) -> select();
        $return['code'] = 10000;
        $return['msg_test'] = '查询成功';
        $return['data'] = $info;
        return json($return);

    }



}