<?php 
namespace app\index\model;

use think\Model;

class User extends Model
{
	/*更新用户信息*/
	function add_user($user_info){
		$user = User::get(1);
        		$user->nickname = 'thinkphp';
        		$user->allowField(true)->save($user_info);
        		return $user->id;
	}
	/*查询用户信息，用于登录*/
	function get_user_info($username){
		$user = User::get(['login_name'=>$username]);
		return $user;
	}
}
 ?>