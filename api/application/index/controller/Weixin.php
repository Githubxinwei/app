<?php
namespace app\index\controller;
//use app\index\model\User;
class Weixin
{
    public function index()
    {
	\Think\loader::import('Wechat.auth');
    	$auth = new \auth();
    	$appid = $auth -> get_appid();
    	return json($appid);
    	$res = $auth -> get_access_token($code);
   	
    }
    public function put_code()
    {
            if(!isset($_POST['code'])){return json('缺少code参数');}
            $code = $_POST['code'];
            \Think\loader::import('Wechat.auth');
            $auth = new \auth();
           (!isset($_POST['scope'])) ? $scope = 'snsapi_userinfo' : $scope = $_POST['scope'];
            if($scope == 'snsapi_userinfo'){
                //第一种获取，如果scope是snsapi_userinfo，可以获取access_token，然后再得到用户详细信息数据
                $auth_info = $auth ->get_access_token($code,'snsapi_userinfo');//
                if(!isset($auth_info['access_token'])){return json($auth_info);}
                $user_info = $auth -> get_user_info($auth_info);
                $user = model('User');
                $id = $user->add_user($user_info);//返回用户的user_id
                return json($id);
            }else{
                 //第二种获取，scope是snsapi_base，返回openid
                //$openid = $auth ->get_access_token($code,'snsapi_base');//获取openid
                return json($user_info);
            }
            
           
    }

    public function add_user(){
        $user_info = ['nickname'=>'122测试','openid'=>1234555];
        $user = model('User');
        $id = $user->add_user($user_info);
        return $id;
    }
}
