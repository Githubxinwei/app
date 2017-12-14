<?php
namespace app\test\controller;

class Index {
	function index(){
		dump(get_app(1));
		model('system') -> find();
	}
	function wx(){
		$apps = '23542640';$appid = 'wxee74a03b4c01bebc';
		$weapp = new \app\weixin\controller\Common($apps);
		$weapp ->domain();//设置request服务器域名
		$template = get_app(1);
		$template_id = $template['template_id'];//得到小程序模板ID
		$color = get_theme(3);//主题色
		$layout_arr = ['grid','table','table_row'];
		$layout = $layout_arr[1];//布局
		$search = 1==1 ? 'true' : 'false';//启用搜索框
		$on_service = 1==1 ? 'true' : 'false';//启用客服
		$ext_json = '{
			"extEnable": true,
			"extAppid": "'.$appid.'",
			"window":{
			"navigationBarTitleText": "西瓜科技演示",
			"navigationBarTextStyle":"white",
			"navigationBarBackgroundColor": "'.$color['theme'].'",
			"backgroundTextStyle":"light"
			"backgroundColor": "'.$color['theme'].'"
			},
			"ext":{
			 "xgAppId":"'.$apps.'",
			"appid":"'.$appid.'",
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
		$res = $weapp -> commit($template_id,$ext_json);dump($res);file_cache('ext_json1',$ext_json,'');
		
	}
	function put_user(){
		//$weapp = controller("weixin/Commonsad");
		$weapp = new \app\weixin\controller\Common('23542640');
		$this->data = input("post.",'','htmlspecialchars');
		if(!$this->data){$this->data = $_GET;}//仅测试使用，待删
		if(!isset($this->data['page'])){
			$return['code']=10002;$return['msg_test']='忘记了参数page';return json($return);
		}
		$page = ($this->data['page']-1)*20;
		$res = $weapp -> template_list($page);
		if($res['errcode'] != 0){
			$return['code']=10002;$return['msg']=$result['errmsg'];return json($return);
		}
		$return['code'] = 10000;$return['total_count'] = $res['total_count'];$return['page'] = $this->data['page'];$return['data'] = $res['list'];
		return json($return);
		// foreach($res as $k=>$v){
		// 	$res[$k]['keywords'] = $weapp -> template_get($v['id']);
		// }
		dump($res);exit;
		$res = $weapp -> get_latest_auditstatus();dump($res);exit;
		$res = $weapp -> submit_audit();dump($res);exit;
		$res = $weapp -> release();dump($res);
		
	}
	function get_qrcode(){
		if (ob_get_level() == 0) ob_start();
		 ob_implicit_flush(true);
		 ob_clean();
		 header("Content-type: text/plain");
		 echo("success");
		 ob_flush();
		 flush();
		 ob_end_flush();
		 die();
		$result = '3344';
		$component = controller('Weixin/Component');
			$result = $component -> encryptMsg($result);dump($result);
		$apps = '23542640';
		$apps = '56617092';
		$weapp = new \app\weixin\controller\Common($apps);
		$weapp->bind_tester('mashuaiwei');
		$img = $weapp ->get_qrcode();//$res = $weapp->bind_tester('songlindexiaowo');dump($res);
		echo '<img src="'.$img.'">';
	}
	

}



 ?>