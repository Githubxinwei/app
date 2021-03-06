<?php
/**
 * Created by PhpStorm.
 * User: 宋妍妍
 * Date: 2017/12/2 0002
 * Time: 11:08
 */
namespace app\system\controller;
use think\Controller;
use think\Session;

class Agency  extends Controller
{

     /*构造函数*/
     public function _initialize()
    {
        $this -> data = input('post.','','htmlspecialchars');
        $this -> user = session('admin');
        if($this->user == null){
            $return['code'] = 99999;
            $return['msg'] = '请登录';
            return json($return);
        }
    }

     /*获取小程序类型*/
     public function get_app(){

         if(!isset($this->data['type_auto'])){
             $return['code'] = 10003;
             $return['msg_test'] = '身份参数丢失';
             return json($return);
         }
         if(!isset($this->data['type_ssh'])){
             $return['code'] = 10003;
             $return['msg_test'] = '参数丢失';
             return json($return);
         }


         /*查询已经添加过得小程序套餐 进行验证*/
         $setting = db('app_setting')->field("type")->where(['type_auto'=>$this->data['type_auto'],'type_ssh'=>$this->data['type_ssh']])->select();
         $datas = [];
         foreach($setting as $k=>$v){
             array_push($datas,$v['type']);
         }

         /*小程序类型*/
         $app=  db('app')->distinct(true)->field('type')->select();
         $data = [];
         foreach($app as $k=>$v){
             array_push($data,$v['type']);
         }

         $data = array_diff($data,$datas);

         $return['data'] = $setting;
         $return['code'] = 10000;
         $return['data'] = $data;
         return json($return);

     }

     /*添加小程序套餐设置*/
     public function app_setting(){

         if(!isset($this->data['type_auto'])){
             $return['code'] = 10003;
             $return['msg_test'] = '身份参数丢失';
             return json($return);
         }
         if(!isset($this->data['type']) || !isset($this->data['year_num'])){
             $return['code'] = 10003;
             $return['msg_test'] = '参数丢失';
             return json($return);
         }
         if(!isset($this->data['type'])){
             $return['code'] = 10003;
             $return['msg_test'] = '小程序类型丢失';
             return json($return);
         }

         $user = $this->user;
         $this->data['user_system'] = $user['id'];
             $info = db('app_setting')
             ->where(['type_auto'=>$this->data['type_auto'],'type_ssh'=>$this->data['type_ssh'],'type'=>$this->data['type'],'user_system'=>$user['id']])
             ->find();

        if(!$info){
            $count = db('app_setting')->where(['type_auto'=>$this->data['type_auto'],'type_ssh'=>$this->data['type_ssh'],'user_system'=>$user['id']])->count();
            if($count >= 3 ){
                $return['code'] = 10004;
                $return['msg'] = '套餐数量已满';
                return json($return);
            }
            $res = db('app_setting')->insert($this->data);
        }
         if($res){
             $return['code'] = 10000;
             $return['msg'] = '成功';
             return json($return);
         }else{
             $return['code'] = 10001;
             $return['msg'] = '失败';
             return json($return);
         }
     }

     /*修改小程序套餐设置*/
    public function  update_setting(){
        if(!isset($this->data['id'])){
            $return['code'] = 10003;
            $return['msg_test'] = '参数丢失';
            return json($return);
        }
        $data = $this->data;
        $res = db('app_setting')->where(['id'=>$this->data['id']])->update($data);
        if($res){
            $return['code'] = 10000;
            $return['msg'] = '成功';
            return json($return);
        }else{
            $return['code'] = 10001;
            $return['msg'] = '失败';
            return json($return);
        }
    }

    /*获取套餐列表*/
    public  function  get_package(){

        if(!isset($this->data['type_auto'])){
            $return['code'] = 10003;
            $return['msg_test'] = '身份参数丢失';
            return json($return);
        }
        if(!isset($this->data['type_ssh'])){
            $return['code'] = 10003;
            $return['msg_test'] = '参数丢失';
            return json($return);
        }
        $user= $this->user;
        $data = db('app_setting')->where(['type_auto'=>$this->data['type_auto'],'type_ssh'=>$this->data['type_ssh'],'user_system'=>$user['id']])->select();
        $return['code'] = 10000;
        $return['data'] = $data;
        return json($return);

    }

    /*获取套餐详情*/
    public function get_package_detail(){

        if(!isset($this->data['id'])){
            $return['code'] = 10003;
            $return['msg_test'] = '参数丢失';
            return json($return);
        }
        $data = db('app_setting')->where(['id'=>$this->data['id']])->find();
        $return['code'] = 10000;
        $return['data'] = $data;
        return json($return);

    }

    /*删除套餐*/
    public function delete_detail(){

        if(!isset($this->data['id'])){
            $return['code'] = 10003;
            $return['msg_test'] = '参数丢失';
            return json($return);
        }
        $data = db('app_setting')->where(['id'=>$this->data['id']])->delete();
        if($data){
            $return['code'] = 10000;
            $return['msg'] = "删除成功";
            return json($return);
        }else{
            $return['code'] = 10001;
            $return['msg'] = "删除失败";
            return json($return);
        }

    }

    /*普通用户申请成为代理商*/
    public function  normal_agent(){

        $data = $this->data;
        $data['is_agency_user'] = 1;
        $data['is_agency'] = 1;

        $id_cart = checkIdCard($data['id_cart']);
        if(!$id_cart){
            $return['code'] = 10003;
            $return['msg'] = '身份证号不正确';
            return json($return);
        }

        $info  = db('custom')->where(['id'=>$this->data['id']])->find();
        if($info['is_agency_user'] == 1 && $info['is_agency'] == 2 ){
            $return['code'] = 10003;
            $return['msg'] = "您已经是代理商";
            return json($return);
        }
        if($info['is_agency'] == 1 && $info['is_agency'] == 1){
            $return['code'] = 10003;
            $return['msg'] = "请耐心等待审核";
            return json($return);
        }

        unset($data['session_key']);
        unset($data['appid']);
        $res = db('custom')->where(['id'=>$this->data['id']])->update($data);
        if($res){
            $return['code'] = 10000;
            $return['msg'] = "已提交，请等待审核";
            return json($return);
        }else{
            $return['code'] = 10001;
            $return['msg'] = "提交失败";
            return json($return);
        }

    }

    /*代理商列表*/
    public  function get_agent_list(){

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
        if(isset($this->data['start_time']) && isset($this->data['end_time']) && trim($this->data['start_time']) != '' && trim($this->data['end_time']) !=''){
            $where['register_time'] = ['between',[$this->data['start_time'],$this->data['end_time']]];
        }

        $where['is_agency_user'] = 1;
        $where = array_filter($where);
        $data = db("custom")
            ->where($where)
            ->page($page,$limit)
            ->select();
        $num = db("custom")->where(['is_agency_user'=>1])->count();

        $return['code'] = 10000;
        $return['data'] = $data;
        $return['number'] = $num;
        return json($return);

    }

    /*更新代理商审核状态*/
    public  function  update_agent(){

        if(!isset($this->data['id'])){
            $return['code'] = 10003;
            $return['msg_test'] = '用户参数丢失';
            return json($return);
        }
        if(!isset($this->data['is_agency'])){
            $return['code'] = 10003;
            $return['msg_test'] = '状态参数丢失';
            return json($return);
        }
        $arr = db('custom')->where(['id'=> $this->data['id']])->find();
        $id_cart = checkIdCard($arr['id_cart']);
        if(!$id_cart){
            $return['code'] = 10003;
            $return['msg'] = '身份证号不正确';
            return json($return);
        }

        /*审核通过  添加管理账户*/
        if($this->data['is_agency'] == 2){
            $list['username'] = $arr['username'];
            $list['is_agency_user'] = 1 ;
            $list['password'] = $arr['password'];
            $list['nowIp'] = $_SERVER["REMOTE_ADDR"];
            $list['nowTime'] = date('Y年m月d日H:i:s',time());
            $list['custom_id'] = $this->data['id'];
            $add =  db('system')->where(['username'=>$list['username']])->find();
            $num = db("custom")->where(['is_agency_user'=>1,'is_agency'=>2])->find();
            db("custom")->where(['id'=>$list['custom_id']])->update(['angency_number'=>$num]); //修改代理商可以添加的普通用户数量

           if($add){
               $return['code'] = 10003;
               $return['msg'] = '该手机号已存在';
               return json($return);
           }else{
               db('system') ->insert($list);
           }

        }elseif($this->data['is_agency'] == 3){
            /*审核失败  重新定义为普通用户  */
            $data['is_agency_user'] = 0;
        }

        $data['is_agency'] = $this->data['is_agency'];

        $res = db('custom')->where(['id'=> $this->data['id']])->update($data);

        if($res){
            $return['code'] = 10000;
            $return['msg'] = "成功";
            return json($return);
        }else{
            $return['code'] = 10001;
            $return['msg'] = "失败";
            return json($return);
        }

    }

    /*代理商用户数量更新*/
    public  function save_user(){

        $user = $this -> user;
            if($user['is_agency_user'] != 0) {
            $return['code'] = 10003;
            $return['msg_test'] = '账号类型不能设置普通用户';
            return json($return);
        }

        if(!isset($this->data['angency_number'])){
            $return['code'] = 10003;
            $return['msg'] = '用户数量必须填写';
            return json($return);
        }

        $res = db('custom')->where(['is_agency_user'=>1,'is_agency'=>2])->update(['angency_number'=>$this->data['angency_number']]);

        if($res){
            $return['code'] = 10000;
            $return['msg'] = "更新成功";
            return json($return);
        }else{
            $return['code'] = 10001;
            $return['msg'] = "更新失败";
            return json($return);
        }


    }

    /*普通用户可添加数量*/
    public  function number_user(){

        $data = db('custom')->field('angency_number')->where(['is_agency_user'=>1])->find();
        $return['code'] = 10000;
        $return['data'] = $data;
        return json($return);

    }

    /*代理商禁用*/
    public  function  save_agent_state(){
        /*is_forbidden*/
        if(!isset($this->data['id'])){
            $return['code'] = 10003;
            $return['msg_test'] = '用户参数丢失';
            return json($return);
        }
        if(!isset($this->data['is_forbidden'])){
            $return['code'] = 10003;
            $return['msg_test'] = '状态参数丢失';
            return json($return);
        }
        $res = db('custom')->where(['id'=>$this->data['id']])->update(['is_forbidden'=>$this->data['is_forbidden']]);
        db('custom')->where(['id_agency'=>$this->data['id']])->update(['is_forbidden'=>$this->data['is_forbidden']]);
        if($res){
            $return['code'] = 10000;
            $return['msg'] = "成功";
            return json($return);
        }else{
            $return['code'] = 10001;
            $return['msg'] = "失败";
            return json($return);
        }

    }

    /*代理商删除*/
    public  function   delete_agent(){
        if(!isset($this->data['id'])){
            $return['code'] = 10003;
            $return['msg_test'] = '用户参数丢失';
            return json($return);
        }
        $res = db('custom')->where(['id'=>$this->data['id']])->delete();
        db('custom')->where(['id_agency'=>$this->data['id']])->delete();
        if($res){
            $return['code'] = 10000;
            $return['msg'] = "成功";
            return json($return);
        }else{
            $return['code'] = 10001;
            $return['msg'] = "失败";
            return json($return);
        }

    }

    /*代理商详情*/
    public  function  detail_agent(){
        if(!isset($this->data['id'])){
            $return['code'] = 10003;
            $return['msg_test'] = '用户参数丢失';
            return json($return);
        }
        $data = db('custom')->where(['id'=>$this->data['id']])->find();
        $user = db('custom')->where(['id_agency'=>$this->data['id']])->select();
        $return['code'] = 10000;
        $return['data'] = $data;
        $return['user'] = $user;
        return json($return);

    }

    /*添加普通用户*/
    public  function  add_user(){
        $user = $this -> user;
        if($user['is_agency_user'] != 1) {
            $return['code'] = 10003;
            $return['msg_test'] = '账号类型不能设置普通用户';
            return json($return);
        }
        /*当前用户的custom_id*/
        $arr = db('system')->field('id,custom_id')->where(['id'=>$user['id']])->find();

        $data = [
            'username'=> $this->data['username'],
            'password'=> xgmd5($this->data['password']) ,
            'register_time'=> time() ,
            'is_agency'=> 0,
            'agency_realname'=> $this->data['agency_realname'],
            'id_cart'=> $this->data['id_cart'],
            'is_belong'=>1 ,
            'id_agency'=> $arr['id'],
            'nickname' =>$this->data['nickname']
        ];
        if(!isset($data['username']) || !isset($data['password'])){
            $arr['code'] = 10001;$arr['msg'] = '请传递账户或密码';$arr['msg_test'] = '请传递账户或密码';
            return json($arr);
        }
        if(!preg_match("/^1[34578]{1}\d{9}$/",$data['username'])){
            $arr['code'] = 10002;$arr['msg'] = '手机号格式不正确';$arr['msg_test'] = '手机号格式不正确';
            return json($arr);
        }
        /*身份证号验证*/
        $id_cart = checkIdCard($data['id_cart']);
        if(!$id_cart){
            $return['code'] = 10003;
            $return['msg'] = '身份证号不正确';
            return json($return);
        }
        /*判断手机号是否存在*/
        $have = db('custom')->where(['username'=>$data['username']])->find();
        if($have){
            $return['code'] = 10003;
            $return['msg'] = '手机号已存在';
            return json($return);
        }

        /*判断代理商当前是否还可以哪添加普通用户*/
        $count = db('custom')->where(['id_agency'=>$data['id_agency']])->count();/*查询岗前代理商已添加的普通用户*/
        $num = db('custom')->field('angency_number')->where(['id'=>$arr['custom_id'],'is_agency_user'=>1,'is_agency'=>2])->find();//可以添加的数量
        if($count>=$num['angency_number']){
            $return['code'] = 10003;
            $return['msg'] = '普通用户已上限';
            return json($return);
        }

        $res = db('custom')->insert($data);
        if($res){
            //发送短信通知
            $param = "password:{$this->data['password']}";
            sendMsgInfo($data['username'],$param,1,2,$user['id']);
            $return['code'] = 10000;
            $return['msg'] = "添加成功";
            return json($return);
        }else{
            $return['code'] = 10001;
            $return['msg'] = "添加失败";
            return json($return);
        }



    }




}