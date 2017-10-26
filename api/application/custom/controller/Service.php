<?php
namespace app\custom\controller;
use think\Db;

class Service extends Action{
    
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
	 * 获取服务项目，进行修改
	 * appid,service_id
	 */
	public function getServiceById(){
	    if(!isset($this -> data['service_id'])){
	        $return['code'] = 10001;
	        $return['msg_test'] = '参数值缺失';
	        return json($return);
	    }
	    $info = db('subscribe_service') -> where(['id' => $this -> data['service_id']*1]) -> find();
	    if($info){
	        if($info['custom_id'] != $this->custom->id){
	            $return['code'] = 10004;
	            $return['msg_test'] = '不是这个商户的';
	            return json($return);
	        }
	    }
        $return['code'] = 10000;
        $return['data'] = $info;
        $return['msg'] = '';
        $return['msg_test'] = '查询成功';
        return json($return);
	}
	
	/**
	 * 修改服务项目
	 */
	public function updateServiceItem(){
	   //服务名称不能为空
	   if(!isset($this -> data['service_name'])){
	        $return['code'] = 10001;
	        $return['msg_test'] = '服务名称不能为空';
	        return json($return);
	   }
	    //服务项目的价格不能为空或负值
	    if(isset($this -> data['service_price'])){
	        if($this -> data['service_price'] && $this -> data['service_price'] <= 0){
	            $return['code'] = 10002;
	            $return['msg'] = '请填写正确的价格';
	            return json($return);
	        }
	    }
	    if(!isset($this -> data['service_id']) || !isset($this -> data['service_name']) || (!isset($this -> data['service_price']))){
	        $return['code'] = 10001;
	        $return['msg_test'] = '参数值缺失';
	        return json($return);
	    }
	
	    //服务项目的名字和价格是必须的
        if($this -> data['service_price'] && $this -> data['service_price'] <= 0){
            $return['code'] = 10004;
            $return['msg'] = '请填写正确的服务价格';
            return json($return);
        }
	
	    //如果上传图片，判断图片是否是十个
	    if(isset($this -> data['service_pic'])){
	        $pic_number = count(explode(',',$this -> data['service_pic']));
	        if($pic_number > 10){
	            $return['code'] = 10006;
	            $return['msg'] = '一个商品最多上传10张图片';
	            return json($return);
	        }
	    }
	    if(isset($this -> data['service_desc'])){
	        if(mb_strlen($this -> data['service_desc'],'utf8') > 600){
	            $return['code'] = 10007;
	            $return['msg'] = '商品的简介最多600字';
	            return json($return);
	        }
	    }
	    db('subscribe_service') -> allowField(true) -> save($this -> data,['id' =>$this -> data['service_id'],'custom_id' => $this->custom->id]);
	    $return['code'] = 10000;$return['msg'] = '修改成功'.$this->custom->id;
	    return json($return);
	
	}
	
	/**
	 * 删除用户的服务项目
	 * service_id,appid
	 */
	public function delServiceItems(){
	    if(!isset($this -> data['service_id'])){
	        $return['code'] = 10001;
	        $return['msg_test'] = '缺少服务项目id或缺少appid';
	        return json($return);
	    }
	    $res = db('subscribe_service') -> where(['id' => $this -> data['service_id'] * 1,'custom_id' => $this->custom->id]) -> delete();
	
	    if($res){
	        $return['code'] = 10000;
	        $return['msg'] = '删除成功';
	        $return['msg_test'] = '删除成功';
	        return json($return);
	    }else{

	        $return['code'] = 10001;
	        $return['msg'] = '删除数据失败';
	        $return['msg_test'] = 'appid错误，或者service_id错误';
	        return json($return);
	    }
	}
	
	//添加服务项目
	public function createServiceItems(){

	    if(!isset($this -> data['service_name']) || (!isset($this -> data['service_price']))){
	        $return['code'] = 10001;
	        $return['msg_test'] = '请求参数不存在';
	        return json($return);
	    }
	    //服务名称和价格是必须的

        if(isset($this -> data['service_price'])) {
            if ($this->data['service_price'] && $this->data['service_price'] <= 0) {
                $return['code'] = 10002;
                $return['msg'] = '请填写正确的商品价格';
                return json($return);
            }
        }
	    //如果上传图片，判断图片是否是十个
	    if(isset($this -> data['service_pic'])){
	        $pic_number = count(explode(',',$this -> data['service_pic']));
	        if($pic_number > 10){
	            $return['code'] = 10003;
	            $return['msg'] = '一个商品最多上传10张图片';
	            return json($return);
	        }
	    }
	    if(isset($this -> data['service_desc'])){
	        if(mb_strlen($this -> data['service_desc'],'utf8') > 600){
	            $return['code'] = 10004;
	            $return['msg'] = '商品的简介最多600字';
	            return json($return);
	        }
	    }

	    $this -> data['create_time'] = time();
	    $this -> data['custom_id'] = $this -> custom -> id;
        $data = $this -> data;
        $data = array_splice($data,1);
        $id =  db('subscribe_service')-> insertGetId($data);
//      $arr = db('subscribe_service')->where(['appid' => $this->data['appid'],'custom_id' => $this -> custom->id])->select();
        if($id){
            $return['code'] = 10000;
//          $return['data'] = $arr;
            $return['msg'] = '添加成功';
            $return['msg_test'] = '添加成功';
            return json($return);
        }else{
            $return['code'] = 10005;
            $return['msg'] = '添加失败';
            $return['msg_test'] = '添加失败';
            return json($return);
        }

	}
	
	//获取服务项目列表
	public function getServiceItems(){
	    $page = isset($this -> data['page']) ? $this -> data['page'] : 1;
	    $limit = isset($this -> data['number']) ? $this -> data['number'] : 15;
	    $where = '';
	    if(isset($this -> data['cate_id'])){
	        $cate_id = $this -> data['cate_id'] * 1;
	        $where = "FIND_IN_SET($cate_id,cate_id)";
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
        $info['service_id'] = $this->data['service_id'];
        $info['appid'] = $this->data['appid'] * 1;
        $info['custom_id'] = $this->custom->id;
        $info['service_name'] = $this->data['service_name'];
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
        $res = model('subscribe_service_user') -> allowField(['name','pic','desc','service_id','service_name']) -> where(['id' => $this->data['service_user_id']]) -> save($this->data);
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
            -> field('a.id,a.name,a.pic,a.desc,a.service_id,a.service_name')
            -> where(['a.appid' => $this->data['appid']])
            -> page($page,$limit)
            -> select();
        $return['code'] = 10000;
        $return['msg_test'] = '查询成功';
        $return['data'] = ['number' => $count,'info' => $info];
        return json($return);
    }

    /**
     * 通过id获取人员的信息
     */
    public function getServiceUserById(){
        if(!isset($this->data['service_user_id'])){
            $return['code'] = 10001;
            $return['msg_test'] = '参数值缺失';
            return json($return);
        }
        $info = db('subscribe_service_user') -> field('custom_id,id,name,desc,pic,service_id,service_name') -> find($this->data['service_user_id']);
        if($info){
            if($info['custom_id'] != $this->custom->id){
                $return['code'] = 10002;
                $return['msg_test'] = '数据错误';
                $return['data'] = $info;
                return json($return);
            }
        }
        unset($info['custom_id']);
        $return['code'] = 10000;
        $return['msg_test'] = '查询成功';
        $return['data'] = $info;
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