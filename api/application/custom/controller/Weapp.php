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
		$template_id = $template['template_id'];
		$color_arr = [
			['BarText'=>'black','theme'=>'#ffffff','text'=>'#000','icon'=>'blue','selected'=>'#000'],
			['BarText'=>'black','theme'=>'#1d1d1d','text'=>'#fff','icon'=>'blue','selected'=>'#000'],
			['BarText'=>'black','theme'=>'#3790f4','text'=>'#fff','icon'=>'Deep-blue','selected'=>'#000'],
			['BarText'=>'black','theme'=>'#ff8635','text'=>'#fff','icon'=>'orange','selected'=>'#000'],
			['BarText'=>'black','theme'=>'#2a9a3d','text'=>'#fff','icon'=>'green','selected'=>'#000'],
			['BarText'=>'black','theme'=>'#ed5ec0','text'=>'#fff','icon'=>'pink','selected'=>'#000'],
			['BarText'=>'black','theme'=>'#9f56dd','text'=>'#fff','icon'=>'purple','selected'=>'#000'],
			['BarText'=>'black','theme'=>'#ad855e','text'=>'#fff','icon'=>'soil','selected'=>'#000'],
			['BarText'=>'black','theme'=>'#00c1e4','text'=>'#fff','icon'=>'blue','selected'=>'#000'],
			['BarText'=>'black','theme'=>'#f12c20','text'=>'#fff','icon'=>'orange','selected'=>'#000'],
		];
		$layout_arr = ['grid','table','table_row'];
		$color = $color_arr[9];//主题色
		$layout = $layout_arr[1];echo $layout;//布局
		$search = 1==1 ? 'true' : 'false';//启用搜索框
		$on_service = 1==112 ? 'true' : 'false';//启用客服
		$ext_json = '{
			"extEnable": true,
			"extAppid": "wxee74a03b4c01bebc",
			"window":{
			"navigationBarTitleText": "西瓜科技演示",
			"navigationBarTextStyle":"white",
			"navigationBarBackgroundColor": "'.$color['theme'].'",
			"backgroundTextStyle":"light"
			},
			"ext":{
			 "xgAppId":"23542640",
			"appid":"wxee74a03b4c01bebc",
			 "themeColor":"'.$color['theme'].'",
			 "themeTextColor":"'.$color['text'].'",
			 "layoutType":"'.$layout.'",
			 "showSearching":'.$search.',
			 "useOnlineService":'.$on_service.',
			 "host":"https://weapp.xiguawenhua.com"
			},
			"tabBar": {
				"selectedColor": "'.$color['selected'].'",
				"backgroundColor": "#fff",
				"color":"#555",
				"borderStyle": "black",
				"list": [
					{
						"pagePath": "pages/index/index",
						"iconPath": "./img/images/un-home.png",
						"selectedIconPath": "./img/images/'.$color['icon'].'-home.png",
						"postion": "top",
						"text": "首页"
					},
					{
						"pagePath": "pages/cart/cart",
						"iconPath": "./img/images/un-care.png",
						"selectedIconPath": "./img/images/'.$color['icon'].'-care.png",
						"text": "购物车"
					},
					{
						"pagePath": "pages/order/order",
						"iconPath": "./img/images/un-order.png",
						"selectedIconPath": "./img/images/'.$color['icon'].'-order.png",
						"text": "订单"
					},
					{
						"pagePath": "pages/more/more",
						"iconPath": "./img/images/un-more.png",
						"selectedIconPath": "./img/images/'.$color['icon'].'-more.png",
						"text": "更多"
					}
				]
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