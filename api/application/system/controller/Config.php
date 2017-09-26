<?php 
namespace app\system\controller;

/*****超级管理员后台参数设置*****/
class Config{

    public function __construct()
    {
        $data = session('admin');
        if($data == null){
            $return['code'] = 99999;
            $return['msg'] = '请登录';
            $return['msg_test'] = '请登录';
            halt($return);
        }
    }

    /**
	 *添加或者更新平台设置
	 */
	public function setSystemOauth(){
	    $data = input('post.','','htmlspecialchars');
	    if (!$data){
	        $return['code'] = 10001;
	        $return['msg'] = '无参数';
	        $return['msg_test'] = '请传入参数';
	        return json($return);
        }
        //判断是否有id
        if(isset($data['id'])){
	        $info = db('SystemOauth') -> find($data['id'] * 1);
	        if(!$info){
                $return['code'] = 10003;
                $return['msg'] = '更新信息不存在';
                $return['msg_test'] = '更新信息不存在';
                return json($return);
            }
        }
        $arr = array($data);
        $res = model('SystemOauth') -> allowField(true) -> saveAll($arr);
	    if($res){
            $return['code'] = 10000;
            $return['msg'] = '成功';
            $return['msg_test'] = '修改或添加成功';
            return json($return);
        }else{
            $return['code'] = 10002;
            $return['msg'] = '网络错误';
            $return['msg_test'] = '网络错误';
            return json($return);
        }
    }

    /**
     *获取平台设置
     */
    public function getSystemOauth(){
        $id = input('id','','htmlspecialchars');
        if(!$id){
            $return['code'] = 10002;
            $return['msg'] = 'id不存在';
            $return['msg_test'] = '缺少参数';
            return json($return);
        }
        $data = db('SystemOauth') -> where("id = :id",['id' => $id*1]) -> find();
        if(!$data){
            $return['code'] = 10001;
            $return['msg'] = '查询信息不存在';
            $return['msg_test'] = '查询信息不存在';
            return json($return);
        }else{
            $return['code'] = 10000;
            $return['data'] = $data;
            $return['msg'] = '成功';
            $return['msg_test'] = '成功';
            return json($return);
        }
    }

    /**
     *添加或者更新系统站点设置信息表
     */
    public function setSystemSite(){
        $data = input('','','htmlspecialchars');
        if (!$data){
            $return['code'] = 10001;
            $return['msg'] = '无参数';
            $return['msg_test'] = '请传入参数';
            return json($return);
        }
        //判断是否有id
        if(isset($data['id'])){
            $info = db('SystemSite') -> find($data['id'] * 1);
            if(!$info){
                $return['code'] = 10003;
                $return['msg'] = '更新信息不存在';
                $return['msg_test'] = '更新信息不存在';
                return json($return);
            }
        }
        $arr = array($data);
        $res = model('SystemSite') -> allowField(true) -> saveAll($arr);
        if($res){
            $return['code'] = 10000;
            $return['msg'] = '成功';
            $return['msg_test'] = '修改或添加成功';
            return json($return);
        }else{
            $return['code'] = 10002;
            $return['msg'] = '网络错误';
            $return['msg_test'] = '网络错误';
            return json($return);
        }
    }

    /**
     *获取系统站点设置信息表数据
     */
    public function getSystemSite(){
        $id = input('id','','htmlspecialchars');
        if(!$id){
            $return['code'] = 10002;
            $return['msg'] = 'id不存在';
            $return['msg_test'] = '缺少参数';
            return json($return);
        }
        $data = db('SystemSite') -> where("id = :id",['id' => $id*1]) -> find();
        if(!$data){
            $return['code'] = 10001;
            $return['msg'] = '查询信息不存在';
            $return['msg_test'] = '查询信息不存在';
            return json($return);
        }else{
            $return['code'] = 10000;
            $return['data'] = $data;
            $return['msg'] = '成功';
            $return['msg_test'] = '成功';
            return json($return);
        }
    }

    /**
     *添加或者支付参数设置
     */
    public function setSystemPay(){
        $data = input('','','htmlspecialchars');
        if (!$data){
            $return['code'] = 10001;
            $return['msg'] = '无参数';
            $return['msg_test'] = '请传入参数';
            return json($return);
        }
        //判断是否有id
        if(isset($data['id'])){
            $info = db('SystemPay') -> find($data['id'] * 1);
            if(!$info){
                $return['code'] = 10003;
                $return['msg'] = '更新信息不存在';
                $return['msg_test'] = '更新信息不存在';
                return json($return);
            }
        }
        $arr = array($data);
        $res = model('SystemPay') -> allowField(true) -> saveAll($arr);
        if($res){
            $return['code'] = 10000;
            $return['msg'] = '成功';
            $return['msg_test'] = '修改或添加成功';
            return json($return);
        }else{
            $return['code'] = 10002;
            $return['msg'] = '网络错误';
            $return['msg_test'] = '网络错误';
            return json($return);
        }
    }

    /**
     *获取系统支付参数设置
     */
    public function getSystemPay(){
        $id = input('id','','htmlspecialchars');
        if(!$id){
            $return['code'] = 10002;
            $return['msg'] = 'id不存在';
            $return['msg_test'] = '缺少参数';
            return json($return);
        }
        $data = db('SystemPay') -> where("id = :id",['id' => $id*1]) -> find();
        if(!$data){
            $return['code'] = 10001;
            $return['msg'] = '查询信息不存在';
            $return['msg_test'] = '查询信息不存在';
            return json($return);
        }else{
            $return['code'] = 10000;
            $return['data'] = $data;
            $return['msg'] = '成功';
            $return['msg_test'] = '成功';
            return json($return);
        }
    }


    /**
     *添加或者系统内发送通知的参数
     */
    public function setSystemSms(){
        $data = input('','','htmlspecialchars');
        if (!$data){
            $return['code'] = 10001;
            $return['msg'] = '无参数';
            $return['msg_test'] = '请传入参数';
            return json($return);
        }
        //判断是否有id
        if(isset($data['id'])){
            $info = db('SystemSms') -> find($data['id'] * 1);
            if(!$info){
                $return['code'] = 10003;
                $return['msg'] = '更新信息不存在';
                $return['msg_test'] = '更新信息不存在';
                return json($return);
            }
        }
        $arr = array($data);
        $res = model('SystemSms') -> allowField(true) -> saveAll($arr);
        if($res){
            $return['code'] = 10000;
            $return['msg'] = '成功';
            $return['msg_test'] = '修改或添加成功';
            return json($return);
        }else{
            $return['code'] = 10002;
            $return['msg'] = '网络错误';
            $return['msg_test'] = '网络错误';
            return json($return);
        }
    }

    /**
     *获取系统内发送通知的参数
     */
    public function getSystemSms(){
        $id = input('id','','htmlspecialchars');
        if(!$id){
            $return['code'] = 10002;
            $return['msg'] = 'id不存在';
            $return['msg_test'] = '缺少参数';
            return json($return);
        }
        $data = db('SystemSms') -> where("id = :id",['id' => $id*1]) -> find();
        if(!$data){
            $return['code'] = 10001;
            $return['msg'] = '查询信息不存在';
            $return['msg_test'] = '查询信息不存在';
            return json($return);
        }else{
            $return['code'] = 10000;
            $return['data'] = $data;
            $return['msg'] = '成功';
            $return['msg_test'] = '成功';
            return json($return);
        }
    }


}
 ?>