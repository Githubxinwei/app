<?php
namespace app\custom\controller;

/****************该类设置小程序相关的接口操作，需要传入共同参数apps********************/
class Weapp extends Action{
	function _initialize(){
		parent::_initialize();
		$this->data = input("post.",'','htmlspecialchars');
		if(!isset($this->data['appid'])){
			 $return['code'] = 10001; $return['msg_test'] = 'post参数appid不能为空';
			 echo json_encode($return);exit;
		}else{

			$auth = model('auth_info') -> where('apps',$this->data['appid'])->find();
			if(!$auth || $this->custom['id'] != $auth['custom_id']){
				$return['code'] = 10002; $return['msg_test'] = '没有查询到要操作的app的授权信息';
				echo json_encode($return);exit;
			}
			$this->auth = $auth;
			$this->weapp = new \app\weixin\controller\Common($this->data['appid']);
		}
	}
	//设置体验者||解绑体验者，需要传入微信号
	function put_user(){
		if(!isset($this->data['wechatid'])){
			$return['code'] = 10003; $return['msg_test'] = '请传入wechatid，绑定者的微信号';
			return json($return);
		}
		if(!isset($this->data['type'])){
			$return['code'] = 10004; $return['msg_test'] = '请传入type类型，值为bind或unbind';
			return json($return);
		}elseif($this->data['type'] == 'bind'){
			$res = $this->weapp -> bind_tester($wechatid);
		}elseif($this->data['type'] == 'unbind'){
			$res = $this->weapp -> unbind_tester($wechatid);
		}else{
			$return['code'] = 10004; $return['msg_test'] = '请传入type类型，值为bind或unbind';
			return json($return);
		}
		if($res['errcode'] == 0){
			$return['code'] = 10000; $return['msg_test'] = $this->data['type'].' ok';
			return json($return);
		}else{
			$return['code'] = 10005; $return['msg'] =$res['errmsg'];
			return json($return);
		}
	}
	//为授权的小程序帐号上传小程序代码
	function commit(){
		$app = model('app') -> where('appid',$this->auth['apps'])->find();
		if(!$app){
			$return['code'] = 10006; $return['msg_test'] = '未查询到小程序账号';
			return json($return);
		}
		$template = get_app($app['type']);
		if(!$template){
			$return['code'] = 10007; $return['msg_test'] = '小程序模板类型不存在';
		}
		$template_id = $template['template_id'];//dump($template_id);exit;
		//$ext = ['extAppid'=>'wxcc7d6937a1dc7290','extEnable'=>true,'ext'=>['name'=>'3333']];
		//  $ext = array(
		// 	'extEnable'=>true,
		// 	'extAppid' => 'wxcc7d6937a1dc7290',
		// 	'ext' => array(
		// 		'attr1' => 'value1'
		// 	)
		// );
		// $ext_json = json_encode($ext,JSON_UNESCAPED_UNICODE);
		$ext_json = '{
		  "extEnable": true,
		  "extAppid": "wxcc7d6937a1dc7290",
		  "window":{
			"backgroundTextStyle":"light",
			"navigationBarBackgroundColor": "#ff0000",
			"navigationBarTitleText": "Demo",
			"navigationBarTextStyle":"black"
		  }
		}';
		$res = $this->weapp ->commit($template_id,$ext_json);//dump($res);
		if($res['errcode'] == 0){
			model('app') -> where('appid',$this->auth['apps'])->update(['is_publish'=>2]);
			$return['code'] = 10000; $return['msg_test'] = 'ok';
			return json($return);
		}else{
			$return['code'] = 10005; $return['msg'] =$res['errcode'].$res['errmsg'];
			return json($return);
		}
	}
	//获取体验二维码
	function qrcode(){
		$res = $this->weapp ->get_qrcode();
		$return['code'] = 10005; $return['data'] ='http://'.$_SERVER['HTTP_HOST'].$res;
		return json($return);
	}
	//代码包提交审核
	function submit_audit(){
		$res = $this->weapp ->submit_audit();
		if($res['errcode'] == 0){
			model('app') -> where('appid',$this->auth['apps'])->update(['is_publish'=>3]);
			$return['code'] = 10000; $return['msg_test'] = 'ok';
			return json($return);
		}else{
			$return['code'] = 10005; $return['msg'] =$res['errcode'].$res['errmsg'];
			return json($return);
		}
	}
	//发布
	function release(){
		$latest_audit = $this->weapp ->get_latest_auditstatus();
		if($latest_audit['errcode'] != 0){
			$return['code'] = 10008; $return['msg'] ='当前没有审核中的小程序';return json($return);
		}elseif( $latest_audit['status'] != 0 ){
			$return['code'] = 10009; $return['msg'] ='当前的小程序在审核中';return json($return);
		}
		$res = $this->weapp ->release();
		if($res['errcode'] == 0){
			model('app') -> where('appid',$this->auth['apps'])->update(['is_publish'=>4]);
			$return['code'] = 10000; $return['msg_test'] = 'ok';
			return json($return);
		}else{
			$return['code'] = $res['errcode']; $return['msg'] =$res['errmsg'];
			return json($return);
		}
	}
}


 ?>