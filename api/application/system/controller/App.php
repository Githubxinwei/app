<?php
/**
 * Created by PhpStorm.
 * User: 宋妍妍
 * Date: 2017/12/14 0014
 * Time: 13:36
 */
namespace app\system\controller;
use think\Controller;

class App extends Controller
{
    /*构造函数*/
    public function _initialize()
    {
        $this -> data = input('post.','','htmlspecialchars');
        $this -> user = session('admin');
        if($this->user == null){
            $return['code'] = 99999;
            $return['msg'] = '请登录';
            $return['msg_test'] = '请登录';
            return json($return);
        }
    }

    /*添加小程序数量升级套餐*/
    public function app(){

        if(!isset($this->data['type_auto'])){
            $return['code'] = 10003;
            $return['msg'] = '身份参数丢失';
            $return['msg_test'] = '身份参数丢失';
            return json($return);
        }

        if(!isset($this->data['type_ssh'])){
            $return['code'] = 10003;
            $return['msg'] = '身份参数丢失';
            $return['msg_test'] = '身份参数丢失';
            return json($return);
        }

        if(!isset($this->data['price']) || !isset($this->data['app_num'])){
            $return['code'] = 10003;
            $return['msg'] = '参数丢失';
            $return['msg_test'] = '参数丢失';
            return json($return);
        }
        $user = $this->user;
        $this->data['user_system'] = $user['id'];

        $res = db('app_num')->insert($this->data);


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

    /*套餐列表*/
    public function get_app_list(){
        if(!isset($this->data['type_ssh'])){
            $return['code'] = 10003;
            $return['msg'] = '参数丢失';
            $return['msg_test'] = '参数丢失';
            return json($return);
        }

        $info = db('app_num')->where(['user_system'=>$this->user['id'],' type_ssh'=>$this->data['type_ssh']])->select();
        $return['code'] = 10000;
        $return['data'] = $info ;
        return json($return);

    }

    /*获取信息*/
    public function get_app(){

        if(!isset($this->data['id'])){
            $return['code'] = 10003;
            $return['msg'] = '参数丢失';
            $return['msg_test'] = '参数丢失';
            return json($return);
        }
        $info = db('app_num')->where(['id'=>$this->data['id']])->find();

        $return['code'] = 10000;
        $return['data'] = $info ;
        return json($return);

    }

    /*修改小程序数量升级套餐*/
    public function update_app(){

        if(!isset($this->data['id'])){
            $return['code'] = 10003;
            $return['msg'] = '参数丢失';
            $return['msg_test'] = '参数丢失';
            return json($return);
        }
        $data = $this->data;
        $res = db('app_num')->where(['id'=>$this->data['id']])->update($data);
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

    /*删除小程序数量升级套餐*/
    public function delete_app(){

        if(!isset($this->data['id'])){
            $return['code'] = 10003;
            $return['msg'] = '参数丢失';
            $return['msg_test'] = '参数丢失';
            return json($return);
        }
        $data = db('app_num')->where(['id'=>$this->data['id']])->delete();
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

}