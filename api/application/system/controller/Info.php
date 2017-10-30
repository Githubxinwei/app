<?php
/**
 * Created by PhpStorm.
 * User: 李佳飞
 * Date: 2017/10/27 0027
 * Time: 16:20
 * 超管后台的用户列表和小程序列表
 */
namespace app\system\controller;
use think\Controller;

class Info extends Controller{

    public function __construct()
    {
        parent::__construct();
        $this -> data = input('get','','htmlspecialchars');
        $this -> user = session('admin');
        if($this->user == null){
            $return['code'] = 99999;
            $return['msg'] = '请登录';
            $return['msg_test'] = '请登录';
            json_encode($return);
        }
    }


    /**
     * 获取代理商的列表  就是custom表里面的用户
     */
    public function getAgentList(){
        $page = isset($this -> data['page']) ? $this->data['page'] : 1;
        $limit = isset($this->data['limit_num']) ? $this->data['limit_num'] : 10;
        //如果有筛选条件，那么加where 名称  手机号 时间戳
        $where = array();
        if(isset($this->data['name'])){
            $where['nickname'] = $this->data['name'];
        }
        if(isset($this->data['username'])){
            $where['username'] = $this->data['username'];
        }
        if(isset($this->data['start_time']) && isset($this->data['end_time'])){
            $where['register_time'] = ['between',[$this->data['start_time'],$this->data['end_time']]];
        }
        $count = db('custom') -> where($where) -> count();
        $info = db('custom')
            -> field('id,nickname,username,wallet,expense,app_num,max_app_num,register_time')
            -> where($where)
            -> page($page,$limit)
            -> order('register_time desc')
            -> select();
        $return['code'] = 10000;
        $return['msg_test'] = '查询成功';
        $return['data'] = ['number' => $count,'info' => $info];
        return json($return);
    }

    /**
     * 通过代理商的id获取这个人的详细信息
     */
    public function getAgentById(){
        if(!isset($this->data['agent_id'])){
            $return['code'] = 10001;
            $return['msg_test'] = '参数缺失';
            return json($return);
        }
        $info = db('custom') -> field('id,nickname,username,wallet,expense,ip,app_num,max_app_num,register_time') ->  find($this->data['agent_id']*1);
        $return['code'] = 10000;
        $return['msg_test'] = '查询成功';
        $return['data'] = $info;
        return json($return);
    }

    /**
     * 通过代理商id删除用户
     */
    public function delAgentById(){
        if(!isset($this->data['agent_id'])){
            $return['code'] = 10001;
            $return['msg_test'] = '参数缺失';
            return json($return);
        }
        $res = db('custom') -> delete($this->data['agent_id']);
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
     * 获取小程序的用户列表
     */
    public function getUserList(){
        $page = isset($this -> data['page']) ? $this->data['page'] : 1;
        $limit = isset($this->data['limit_num']) ? $this->data['limit_num'] : 10;
        //如果有筛选条件，那么加where 名称  手机号 时间戳
        $where = array();
        if(isset($this->data['name'])){
            $where['nickName'] = $this->data['name'];
        }
        if(isset($this->data['username'])){
            $where['apps'] = $this->data['username'];
        }
        if(isset($this->data['start_time']) && isset($this->data['end_time'])){
            $where['create_time'] = ['between',[$this->data['start_time'],$this->data['end_time']]];
        }
        $count = db('user') -> where($where) -> count();
        $info = db('user')
            -> field('id,nickName,gender,avatarUrl,city,province,country,create_time')
            -> where($where)
            -> page($page,$limit)
            -> order('create_time desc')
            -> select();
        $return['code'] = 10000;
        $return['msg_test'] = '查询成功';
        $return['data'] = ['number' => $count,'info' => $info];
        return json($return);
    }

    /**
     * 通过小程序用户id获取详细信息
     */
    public function getUserById(){
        if(!isset($this->data['user_id'])){
            $return['code'] = 10001;
            $return['msg_test'] = '参数缺失';
            return json($return);
        }
        $info = db('user')  -> field('id,nickName,gender,avatarUrl,city,province,country,create_time') ->  find($this->data['user_id']*1);
        $return['code'] = 10000;
        $return['msg_test'] = '查询成功';
        $return['data'] = $info;
        return json($return);
    }

    /**
     * 通过小程序的用户id删除用户
     */
    public function delUserById(){
        if(!isset($this->data['user_id'])){
            $return['code'] = 10001;
            $return['msg_test'] = '参数缺失';
            return json($return);
        }
        $res = db('user') -> delete($this->data['user_id']);
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
     * 获取小程序列表
     */
    public function getUserAppList(){
        $page = isset($this -> data['page']) ? $this->data['page'] : 1;
        $limit = isset($this->data['limit_num']) ? $this->data['limit_num'] : 10;
        //如果有筛选条件，那么加where 名称  手机号 时间戳
        $where = array();
        //没有删除的小程序
        $where['a.is_del'] = 0;
        if(isset($this->data['name'])){
            $where['a.name'] = $this->data['name'];
        }
        if(isset($this->data['type'])){
            $where['a.type'] = $this->data['type'];
        }
        if(isset($this->data['nickname'])){
            $where['b.nickname'] = $this->data['nickname'];
        }
        $count = db('app')
            -> alias('a')
            -> join("__CUSTOM__ b",'a.custom_id = b.id','LEFT')
            -> where($where)
            -> count();
        $info = db('app')
            -> alias('a')
            -> field('a.id,a.name,a.pic,a.type,a.create_time,a.use_time,a.is_publish,b.name as username')
            -> join("__CUSTOM__ b",'a.custom_id = b.id','LEFT')
            -> where($where)
            -> page($page,$limit)
            -> order('a.create_time desc')
            -> select();
        foreach ($info as $k => $v){
            $appType = get_app($v['type']);
            $info[$k]['type'] = isset($appType['name']) ? isset($appType['name']) : '无';
        }
        $return['code'] = 10000;
        $return['msg_test'] = '查询成功';
        $return['data'] = ['number' => $count,'info' => $info];
        return json($return);
    }

    public function getUserAppById(){
        if(!$this->data['app_id']){
            $return['code'] = 10001;
            $return['msg_test'] = '参数缺失';
            return json($return);
        }
        $info = db('app')
            -> alias('a')
            -> field("a.id,a.name,a.pic,a.type,a.create_time,a.use_time,a.fee,a.desc,a.tel,a.site_url,a.address,a.is_publish,a.notifytel,a.notifyemail,a.start_time,a.over_time,a.business,a.is_del,b.name as username")
            -> join("__CUSTOM__ b",'a.custom_id = b.id',"LEFT")
            -> where(['a.id' => $this->data['app_id']*1])
            -> find();
        if($info['is_del'] == 1){
            $return['code'] = 10002;
            $return['msg_test'] = '当前小程序已删除';
            return json($return);
        }
        $appType = get_app($info['type']);
        $info['type'] = isset($appType['name']) ? isset($appType['name']) : '无';
        $return['code'] = 10000;
        $return['msg_test'] = 'ok';
        $return['data'] = $info;
        return json($return);
    }

    /**
     * 通过小程序的用户id删除用户
     */
    public function delUserAppById(){
        if(!isset($this->data['app_id'])){
            $return['code'] = 10001;
            $return['msg_test'] = '参数缺失';
            return json($return);
        }
        $res = db('app') -> delete($this->data['app_id']);
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


    public function getAllAppType(){
        $data = get_app('all');
        $return['code'] = 10000;
        $return['msg_test'] = 'ok';
        $return['data'] = $data;
        return json($return);
    }



















}