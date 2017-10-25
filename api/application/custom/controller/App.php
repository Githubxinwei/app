<?php
namespace app\custom\controller;
use think\Controller;
class App extends Xiguakeji{
	
	//获取bannar信息
	function get_bannar(){
		$info = model('loop_img') -> where('appid',$this->apps) -> find();
		$return['code'] = 10000;
		if($info){
			//$info['content'] = explode(',',$info['content']);
			
			$info['content'] = json_decode($info['content'],true);
			$return['data'] = $info['content'];
		}else{
			$return['data'] = '';
		}

		return json($return);
	}
	//获取商品分类
	function cate_lists(){
		$info = model('goods_cate')-> field('id,name') -> where('appid',$this->apps) -> order('code desc') -> select();
		$return['code'] = 10000;$return['data'] = $info;
		return json($return);
	}
	//获取商品列表
	function lists(){
		$keyword = isset($this->data['keyword']) ? $this->data['keyword'] : '';
		if($keyword){$where['name'] = array('like','%'.$keyword.'%');}
		if(isset($this->data['cid'])) $where[]=['exp',"FIND_IN_SET(".$this->data['cid'].",cid)"];
		$where['appid'] = $this->apps;
		if(isset($this->data['page'])){$page = $this->data['page'];}else{$page = 1;}
		if(isset($this->data['limit_num'])){$limit_num = $this->data['limit_num'];}else{$limit_num = 20;}
		$info = model('goods') -> field('id,name,pic,price,stock,spec') -> where($where)->page($page) ->limit($limit_num) -> order('code desc')->select();
		foreach($info as $k=>$v){
			if(!$v['pic']){
				$info[$k]['pic'] = '/uploads/18595906710/20170929/15066512347389.gif';
			}
			$spec_list =$v->getData();
			if($spec_list['spec']){
				$spec = json_decode($spec_list['spec'],true);
				$price = [];
				foreach($spec as $kk=>$vv){
					$price[$kk]=$vv['price'];
				}
				$pos=array_search(min($price),$price);
				$info[$k]['price'] = $price[$pos];
			}
			
		}
		$return['code'] = 10000;$return['data'] = $info;
		return json($return);
	}
	//获取单个商品信息
	function get_one(){
		// if($this->data['session_key'] == 'n7sm42gn80h9e6cpill8fh2q37' ){
		// 	session(null);
		// 	$return['code'] = 0;$return['msg_test'] = '用户登录已过期';echo json_encode($return);exit;}
		if(!isset($this->data['id'])){
			$return['code'] = 10002;$return['msg_test'] = '商品不存在';
			return json($return);
		}
		$info = model('goods') -> field('id,name,pic,price,stock,spec,desc,content_show,content,appid') -> where('id',$this->data['id'])->find();
		if($info['appid'] != $this->apps){
			$return['code'] = 10003;$return['msg_test'] = '商品不属于该商户';
			return json($return);
		}
		// foreach($info as $k=>$v){
		// 	$info[$k]['pic'] = '/uploads/18595906710/20170929/15066512347389.gif';
		// }
		if($info['spec']){
			$info['spec'] = json_decode($info['spec'],true);
			$spec = $info['spec'];
			foreach($spec as $k=>$v){
				$spec[$k]=array(
					'name'=>$v['name'],
					'price'=>$v['price'],
					'lastNum'=>$v['lastNum']
				);
			}
			$info['spec'] = $spec;
		}
		$info['pic'] = explode(',',$info['pic']);
		unset($info['appid']);
		$return['code'] = 10000;$return['data'] = $info;
		return json($return);
	}
	//加入购物车
	function add_cart(){
		//传入id，num，spec
		if(!isset($this->data['id'])){
			$return['code'] = 10001;$return['msg_test'] = '缺少商品id';return json($return);
		}
		if(!isset($this->data['num'])){
			$return['code'] = 10001;$return['msg_test'] = '缺少商品数量num';return json($return);
		}
		$goods = model('goods');
		$info = $goods -> where('id',$this->data['id']) -> find();
		if(empty($info) || $info['appid'] != $this->apps ){
			$return['code'] = 10002;$return['msg_test'] = '商品不存在';return json($return);
		}
		$cart_data =$info->getData();
		unset($cart_data['id']);
		$spec_array = json_decode($info['spec'],true);
		if( is_array($spec_array) ){
			if( !isset($this->data['spec']) ){
				$return['code'] = 10002;$return['msg_test'] = '请选择商品属性';return json($return);
			}elseif(  !isset($spec_array[$this->data['spec']]) ){
				$return['code'] = 10002;$return['msg_test'] = '请选择商品属性';return json($return);
			}else{
				//检查库存是否足够
				if($spec_array[$this->data['spec']]['lastNum'] != '∞' && $spec_array[$this->data['spec']]['lastNum'] < $this->data['num'] ){
					$return['code'] = 10002;$return['msg'] = '商品库存不足，剩余'.$spec_array[$this->data['spec']]['lastNum'].'件';return json($return);
				}
				$cart_data['spec_key'] = $this->data['spec'];
				$cart_data['spec_value'] = $spec_array[$this->data['spec']]['name'];
				$cart_data['price'] = $spec_array[$this->data['spec']]['price'];
			}
		}else{
			//检查库存是否足够
			if( $info['stock'] != -1 && $info['stock'] < $this->data['num']  ){
				$return['code'] = 10002;$return['msg'] = '商品库存不足，剩余'.$info['stock'].'件';return json($return);
			}
		}
		
		//查询购物车是否有同款产品，如果有合并数据
		$where['good_id'] = $info['id'];$where['is_cart'] = 1;$where['user_id'] = $this->user['id'];
		if(isset($cart_data['spec_key'])){
			$where['spec_key'] = $cart_data['spec_key'];
		}else{
			$where['spec_key'] = array('exp','is null');
		}
		$old_info = model('goods_cart') -> where($where) -> find();
		if($old_info){
			$res = model('goods_cart') ->allowField(true) -> save(['num'=>['exp','num+'.$this->data['num']]],['id'=>$old_info['id']]);
		}else{
			//生成购物车缓存数据
			$cart_data['user_id'] = $this->user['id'];
			$cart_data['good_id'] = $info['id'];
			$cart_data['num'] = $this->data['num'];
			if($cart_data['pic']){
				$pic_arr = explode(',',$cart_data['pic']);
				$cart_data['pic'] = $pic_arr[0];
			}
			$res = model('goods_cart') ->allowField(true) -> save($cart_data);
		}
		
		 if( !$res ){
		 	$return['code'] = 10003;$return['msg'] = '加入购物车失败，请重新操作';return json($return);
		 }else{
		 	$return['code'] = 10000;$return['data'] = ['num'=>$this->data['num']];return json($return);
		 }	
	}
	//获取购物车商品
	function get_cart(){
		$info = model('goods_cart') -> field('pic,name,spec_value,price,num,id') -> where(['appid'=>$this->apps,'is_cart'=>1,'user_id'=>$this->user['id']]) -> order('id desc') -> select();
		return json($info);
	}
	//移除购物车商品
	function remove_cart(){
		if(!isset($this->data['id'])){
			$return['code'] = 10001;$return['msg_test'] = '缺少商品id';return json($return);
		}
		$res = model('goods_cart') -> where(['appid'=>$this->apps,'id'=>$this->data['id'],'is_cart'=>1,'user_id'=>$this->user['id']]) -> delete();
		if( !$res ){
		 	$return['code'] = 10003;$return['msg'] = '移除失败，请重新操作';return json($return);
		 }else{
		 	$return['code'] = 10000;return json($return);
		 }	
	}
	//购买商品
	function buy(){
		//先检测商家是否已配置微信支付参数
		$auth_info = model('auth_info') -> where('apps',$this->apps) -> find();
		if( !$auth_info['mchid'] || !$auth_info['mchid'] ){
			$return['code'] = 20000;$return['msg'] = '商户未配置支付参数，暂无法购买';return json($return);
		}
		// $arr = ['username'=>3, 'tel'=>2, 'dist'=>3, 'city'=>3, 'province'=>3, 'address'=>3, 'zipcode'=>3, 'remark'=>3 ];
		// $this->data = array_merge($arr,$this->data);
		if( !isset($this->data['username']) || !isset($this->data['tel'])  || !isset($this->data['dist'])  || !isset($this->data['city'])  || !isset($this->data['address'])  || !isset($this->data['zipcode'])  || !isset($this->data['province']) ){
			$return['code'] = 10001;
			$return['msg_test'] = '缺少用户信息,其中内含username,tel,dist,city,province,address,zipcode，及选填的remark';
			return json($return);
		}
		//两种情况，一种是直接购买，另一种是在购物车发起购买
		//核价
		if($this->data['type'] == 'direct'){
			//确认商品，加入购物车
			if(  !isset($this->data['id']) || !isset($this->data['num']) ){
				$return['code'] = 10001;$return['msg_test'] = '缺少商品信息,其中内含id，num，及选填的spec';return json($return);
			}
			$info = model('goods') -> where('id',$this->data['id']) -> find();
			if(empty($info) || $info['appid'] != $this->apps ){
				$return['code'] = 10002;$return['msg_test'] = '商品不存在';return json($return);
			}
			$cart_data =$info->getData();
			$cart_data['is_cart'] = 0;
			unset($cart_data['id']);
			$spec_array = json_decode($info['spec'],true);
			if( is_array($spec_array) ){
				if( !isset($this->data['spec']) ){
					$return['code'] = 10002;$return['msg_test'] = '缺少商品属性';return json($return);
				}elseif(  !isset($spec_array[$this->data['spec']]) ){
					$return['code'] = 10002;$return['msg_test'] = '缺少商品属性';return json($return);
				}else{
					//检查库存是否足够
					if($spec_array[$this->data['spec']]['lastNum'] != '∞' && $spec_array[$this->data['spec']]['lastNum'] < $this->data['num'] ){
						$return['code'] = 10002;$return['msg'] = '商品库存不足，剩余'.$spec_array[$this->data['spec']]['lastNum'].'件';return json($return);
					}
					$cart_data['spec_key'] = $this->data['spec'];
					$cart_data['spec_value'] = $spec_array[$this->data['spec']]['name'];
					$cart_data['price'] = $spec_array[$this->data['spec']]['price'];
				}
			}else{
				//检查库存是否足够
				if( $info['stock'] != -1 && $info['stock'] < $this->data['num']  ){
					$return['code'] = 10002;$return['msg'] = '商品库存不足，剩余'.$info['stock'].'件';return json($return);
				}
			}
			$cart_data['user_id'] = $this->user['id'];
			$cart_data['good_id'] = $info['id'];
			$cart_data['num'] = $this->data['num'];
			if($cart_data['pic']){
				$pic_arr = explode(',',$cart_data['pic']);
				$pic = $cart_data['pic'] = $pic_arr[0];
			}else{
				$pic = '';
			}
			$res = model('goods_cart') ->allowField(true) -> save($cart_data);
			if(!$res){
				$return['code'] = 10003;$return['msg_test'] = '生成数据失败,联系工程师';return json($return);
			}
			$carts['id'] = model('goods_cart') ->id;
			$total_fee = $cart_data['price']*$this->data['num'];
			$name = $info['name'];$num = $this->data['num'];
		}else{
			if(  !isset($this->data['ids']) ){
				$return['code'] = 10001;$return['msg_test'] = '缺少商品信息,其中内含ids';return json($return);
			}
			//从数据库取出商品
			$carts = model('goods_cart') ->field('group_concat(id) id,name,pic,sum(num) num') -> where(['user_id'=>$this->user['id'],'appid'=>$this->apps,'is_cart'=>1,'id'=>['exp','in ('.$this->data['ids'].')']]) -> find();
			if(!$carts['id']){
				$return['code'] = 10004;$return['msg_test'] = '购物车是空的';return json($return);
			}
			$name = $carts['name'];$pic = $carts['pic'];$num = $carts['num'];
			$total_fee = model('goods_cart') -> where(['user_id'=>$this->user['id'],'appid'=>$this->apps,'is_cart'=>1]) -> sum('num*price');
		}
		//  ``, `carts`, ``, ``, ``, ``, ``, ``, ``, ``, ``, ``, `` 
		//创建订单信息
		$time = time();
		$order_sn = date('Y').$time.rand(1000,9999);
		$order_data = [
			'user_id'=>$this->user['id'],
			'appid'=>$this->apps,
			'carts'=>$carts['id'],
			'price'=>$total_fee,
			'create_time'=>$time,
			'order_sn'=>$order_sn,
			'custom_id'=>$auth_info['custom_id'],
			'username'=>$this->data['username'],
			'tel'=>$this->data['tel'],
			'dist'=>$this->data['dist'],
			'city'=>$this->data['city'],
			'province'=>$this->data['province'],
			'address'=>$this->data['address'],
			'zipcode'=>$this->data['zipcode'],
			'remark'=>$this->data['remark'],
			'openid'=>$this->user['openid'],
			'name'=>$name,
			'pic'=>$pic,
			'num'=>$num
		];
		model('goods_cart') -> save(['is_cart'=>0],['id'=>['exp','in ('.$carts['id'].')']]);//将cart表内数据标注为不在购物车
		model('goods_order') ->allowField(true) -> save($order_data);
		$weapp = new \app\weixin\controller\Common($this->apps);
		$order_id = model('goods_order')->id;
		$attach = json_encode(['type'=>1,'id'=>$order_id]);//type值为1时，是电商小程序的支付请求
		$prepay_id = $weapp -> get_prepay_id($this->user['openid'],$total_fee*100,$order_sn,$attach,'西瓜科技-'.$name);
		if(!$prepay_id){
			$return['code'] = 10005;$return['msg'] = '微信小程序参数配置有误';return json($return);
		}
		model('goods_order') -> save(['prepay_id'=>$prepay_id,'prepay_time'=>time()],['id'=>$order_id]);
		$return['code'] = 10000;
		// $return['msg_test'] = 'data内数据即调起支付所需参数，无需进行加密操作，直接使用';
		// $return['data'] = $weapp -> paysign($prepay_id);
		$return['data']  = ['id'=>$order_id];
		$return['msg_test'] = '可以向付款页跳转了';
		return json($return);
	}

	//付款页核对订单，调起支付
	function pay(){
		if(!isset($this->data['id'])){
			$return['code'] = 10001;$return['msg_test'] = '缺少参数id';return json($return);
		}
		$order = model('goods_order') -> where('id',$this->data['id']) -> find();
		if(empty($order) || $order['appid'] != $this->apps  || $order['user_id'] != $this->user['id'] ){
			$return['code'] = 10001;$return['msg_test'] = '订单不存在';return json($return);
		}
		if($order['state'] != 0 ){
			$return['code'] = 10001;$return['msg_test'] = '订单不是待付款状态';return json($return);
		}
		$weapp = new \app\weixin\controller\Common($this->apps);
		//prepay_id是否过期，过期重新生成
		if( time() - $order['prepay_time'] > 7200 ){
			$prepay_id = $weapp -> get_prepay_id($this->user['openid'],$order['price']*100,$order['order_sn'],$order['id'],'西瓜科技-'.$order['name']);
			model('goods_order') -> save(['prepay_id'=>$prepay_id,'prepay_time'=>time()],['id'=>$order['id']]);
		}else{
			$prepay_id = $order['prepay_id'];
		}
		$return['code'] = 10000;
		$return['msg_test'] = 'data内数据即调起支付所需参数，无需进行加密操作，直接使用';
		$return['data'] = $weapp -> paysign($prepay_id);
		return json($return);
	}

	//订单列表，囊括未付款，待发货，已发货，已完成，已退款
	function order_list(){
		if( !isset($this->data['type']) || !in_array($this->data['type'], [0,1,2,3,4]) ){
			$return['code'] = 10001;$return['msg_test'] = '缺少订单类型type';return json($return);
		}
		$page = isset($this->data['page']) ? $this->data['page'] : 1 ;
		$limit_num = isset($this->data['limit_num']) ? $this->data['limit_num'] : 10 ;
		$where['user_id'] = $this->user['id'];
		$where['state'] = $this->data['type'];
		$where['appid'] = $this->apps;
		//->alias('a')->join($join)  -> where($where) 
		$info = model('goods_order')->field('id,name,num,pic,price,order_sn')-> where($where) ->  page($page)->limit($limit_num) -> order('id desc') -> select();
		$return['code'] = 10000;
		$return['data'] = $info;
		return json($return);

	}

	//获取更多信息
	function info(){
		$info['nickName'] = $this->user->nickName;
		$info['avatarUrl'] = $this->user->avatarUrl;
		$res = model('app') -> where('appid',$this->apps) -> find();
		$info['desc'] = $res->desc;
		$info['tel'] = $res->tel;
		$info['site_url'] = $res->site_url;
		$info['address'] = $res->address;
		$return['code'] = 10000;
		$return['data'] = $info;
		return json($return);
	}

    function  order_close(){

        if(!isset($this->data['id'])){
            $return['code'] = 10001;$return['msg_test'] = '缺少参数id';return json($return);
        }
        $order = model('goods_order') -> where('id',$this->data['id']) -> find();
        if(empty($order) || $order['appid'] != $this->apps  || $order['user_id'] != $this->user['id'] ){
            $return['code'] = 10001;$return['msg_test'] = '订单不存在';return json($return);
        }
        if($order['state'] != 0 ){
            $return['code'] = 10001;$return['msg_test'] = '订单不是待付款状态';return json($return);
        }

        $this->data['state'] = 5 ;
        $info  = db('goods_order')->where("id = $this->data['id'] ")->save($this->data['state']);
        if($info){
            $return['code'] = 10000;
            $return['msg_test'] = '订单取消成功';
            return json($return);
        }else{
            $return['code'] = 10003;
            $return['msg_test'] = '操作失败';
            return json($return);
        }

     }

	
}



 ?>