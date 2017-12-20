<?php
namespace app\custom\controller;
use think\Controller;
use think\Exception;

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

		$info = model('goods')
            -> field('id,name,pic,price,stock,spec')
            -> where($where)
            ->page($page)
            ->limit($limit_num)
            -> order('code desc')
            ->select();

		foreach($info as $k=>$v){
			if(!$v['pic']){
				$info[$k]['pic'] = '/uploads/18595906710/20170929/15066512347389.gif';
			}
			$spec_list =$v->getData();
			if(count($spec_list['spec']) > 0){
                $return['code'] = 10000;$return['data'] = '111';
				$spec = json_decode($spec_list['spec'],true);
				$price = [];
				foreach($spec as $kk=>$vv){
				    if(isset($vv['price'])){
                        $price[$kk]=$vv['price'];

                    }			
		    	
			}

                 
                asort($price);
				$pos=reset($price);
				$info[$k]['prices'] = $pos;
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
            $spec = json_decode($info['spec'],true);
			foreach($spec as $k=>$v){
			    if(isset($v['name']) && isset($v['price']) && isset($v['lastNum'])){
                    $spec[$k]=array(
                        'name'=>$v['name'],
                        'price'=>$v['price'],
                        'lastNum'=>$v['lastNum']
                    );
                }
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
        if(count($spec_array) != 0){
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
		$info = model('goods_cart') -> field('pic,name,spec_value,price,num,good_id,id') -> where(['appid'=>$this->apps,'is_cart'=>1,'user_id'=>$this->user['id']]) -> order('id desc') -> select();
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
	//修改购物车商品数量
	function alter_cnum(){
	    if(!isset($this->data['id'])){
	        $return['code'] = 10001;$return['缺少购物车id'];return json($return);
	    }
	    //点击'+'号
	    if($this->data['num_type'] == '+'){
	        $res = db('goods_cart')->where(['appid'=>$this->apps,'id'=>$this->data['id'],'is_cart'=>1,'user_id'=>$this->user['id']])->setInc('num');
	        if($res){
	            $return['code'] = 10000;
	            $return['msg_test'] = 'ok';                                                                                                                             
	            return json($return);
	        }else{
	            $return['code'] = 10002;
	            $return['msg_test'] = '缺少appid或者缺少用户id';
	            return json($return);
	        }
	    }
	    //点击'-'号
	    if($this->data['num_type'] == '-'){
	        $res = db('goods_cart')->where(['appid'=>$this->apps,'id'=>$this->data['id'],'is_cart'=>1,'user_id'=>$this->user['id']])->setDec('num');
	        if($res){
	            $return['code'] = 10000;
	            $return['msg_test'] = 'ok';
	            return json($return);
	        }else{
	            $return['code'] = 10002;
	            $return['msg_test'] = '缺少appid或者缺少用户id';
	            return json($return);
	        }
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

			if(count($spec_array) != 0){
				if( !isset($this->data['spec']) ){
					$return['code'] = 10002;$return['msg_test'] = '缺少商品属性';return json($return);
				}elseif(  !isset($spec_array[$this->data['spec']]) ){
					$return['code'] = 10002;$return['msg_test'] = '缺少商品属性';return json($return);
				}else{
					//检查库存是否足够
					if($spec_array[$this->data['spec']]['lastNum'] != '∞' && $spec_array[$this->data['spec']]['lastNum'] < $this->data['num'] ){
						$return['code'] = 10002;$return['msg'] = '商品库存不足，剩余'.$spec_array[$this->data['spec']]['lastNum'].'件';return json($return);
					}else if($spec_array[$this->data['spec']]['lastNum'] != '∞'){
					    //库存充足，扣除库存
                        $spec_array[$this->data['spec']]['lastNum'] -= $this->data['num'];
                        $specInfo['spec'] = json_encode($spec_array);
                        model('goods') -> save($specInfo,['id' => $this->data['id']]);
                    }
					$cart_data['spec_key'] = $this->data['spec'];
					$cart_data['spec_value'] = $spec_array[$this->data['spec']]['name'];
					$cart_data['price'] = $spec_array[$this->data['spec']]['price'];
				}
			}else{
				//检查库存是否足够
				if( $info['stock'] != -1 && $info['stock'] < $this->data['num']  ){
					$return['code'] = 10002;$return['msg'] = '商品库存不足，剩余'.$info['stock'].'件';return json($return);
				}else if($info['stock'] != -1){
                    //库存充足，扣除库存
                    $stock['stock'] = $info['stock'] - $this->data['num'];
                    model('goods') -> save($stock,['id' => $this->data['id']]);
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
			$carts = model('goods_cart') ->field('group_concat(id) id,name,pic,sum(num) num')
                -> where(['user_id'=>$this->user['id'],'appid'=>$this->apps,'is_cart'=>1,'id'=>['exp','in ('.$this->data['ids'].')']])
                -> find();

			if(!$carts['id']){
				$return['code'] = 10004;$return['msg_test'] = '购物车是空的';return json($return);
			}
			$idList = explode(',',$this -> data['ids']);
			foreach ($idList as $k => $v){
                //获取购物车的详情
                $goodCart = db('goods_cart') -> field('spec_key,num,good_id') -> where(['id' => $v]) -> find();
			    //判断这个商品是否存在，还有库存是否够
                $is_good = db('goods') -> field('id,name,spec,stock') -> where(['id' => $goodCart['good_id']]) -> find();
                if(!$is_good){
                    $return['code'] = 10010;
                    $return['msg'] = $is_good['name'] . '商品已经下架了,请选择其他商品吧';
                    return json($return);
                }
                $spec_array = json_decode($is_good['spec'],true);
                if(count($spec_array) != 0){
                        //检查库存是否足够
                        if($spec_array[$goodCart['spec_key']]['lastNum'] != '∞' && $spec_array[$goodCart['spec_key']]['lastNum'] < $goodCart['num']){
                            $return['code'] = 10002;$return['msg'] = '商品库存不足，剩余'.$spec_array[$goodCart['spec_key']]['lastNum'].'件';return json($return);
                        }else if($spec_array[$goodCart['spec_key']]['lastNum'] != '∞'){
                            //库存充足，扣除库存
                            $spec_array[$goodCart['spec_key']]['lastNum'] -= $goodCart['num'];
                            $specInfo['spec'] = json_encode($spec_array);
                            model('goods') -> save($specInfo,['id' => $goodCart['good_id']]);
                        }
                }else{
                    //检查库存是否足够
                    if( $is_good['stock'] != -1 && $is_good['stock'] < $goodCart['num']  ){
                        $return['code'] = 10002;$return['msg'] = '商品库存不足，剩余'.$is_good['stock'].'件';return json($return);
                    }else if($is_good['stock'] != -1){
                        //库存充足，扣除库存
                        $stock['stock'] = $is_good['stock'] - $goodCart['num'];
                        model('goods') -> save($stock,['id' => $goodCart['good_id']]);
                    }
                }
            }
			$name = $carts['name'];$pic = $carts['pic'];$num = $carts['num'];
			$total_fee = model('goods_cart')
                -> where(['user_id'=>$this->user['id'],'appid'=>$this->apps,'is_cart'=>1,'id'=>['exp','in ('.$this->data['ids'].')']])
                -> sum('num*price');
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
        //再生成订单之前，判断用户是否要使用余额支付，如果支付，判断订单的金额减去用户的余额是否为0，如果为0不生成prepay_id，并把用户余额减去订单金额，如果不为0，说明订单金额多。
        if(!isset($this -> data['is_wallet'])){
            $return['code'] = 10003;
            $return['msg_test'] = '缺少参数';
            return json($return);
        }
        $orderInfo = array();
        $orderInfo['user_money'] = 0;
        if($this -> data['is_wallet'] == 1){
            //用户想要使用余额，判断是否有余额，并判断是订单金额多还是余额金额多
            $userInfo = db('user') -> field('money') -> where(['id' => $this -> user['id']]) -> find();
            if($userInfo['money'] > 0){
                //判断订单金额多还是余额多，两种情况不一样
                if($total_fee > $userInfo['money']){
                    //订单金额多，把订单金额减去用户余额，同时，把用户余额值为0，把订单表中的user_money变为用户余额，表示当前订单使用了这么多金额。
                    $orderInfo['price'] = $total_fee - $userInfo['money'];
                    $total_fee = $total_fee - $userInfo['money'];
                    $orderInfo['user_money'] = $userInfo['money'];
                    db('user') -> where(['id' => $this -> user['id']]) -> setField('money',0);
                }else{
                    //用户的余额大于订单的金额，订单金额值为0，user_money是订单金额，用户余额变为余额-订单金额
                    $orderInfo['price'] = 0;
                    $orderInfo['user_money'] = $total_fee;
                    db('user') -> where(['id' => $this -> user['id']]) -> setDec('money',$total_fee);
                    $total_fee = 0;
                }

            }else{
                //用户没有余额，或余额不正确,不处理，直接生成prepay_id
            }
        }
        if($total_fee == 0){
            //用户余额大于订单金额
            $prepay_id = $this -> user['id'] . time() . mt_rand(1000,9999);
        }else{
            $prepay_id = $weapp -> get_prepay_id($this->user['openid'],$total_fee*100,$order_sn,$attach,'西瓜科技-'.$name);
            if(!$prepay_id){
                $return['code'] = 10005;$return['msg'] = '微信小程序参数配置有误';return json($return);
            }
        }

        $orderInfo['prepay_id'] = $prepay_id;
        $orderInfo['prepay_time'] = time();
		model('goods_order') -> save($orderInfo,['id'=>$order_id]);
		$return['code'] = 10000;
		// $return['msg_test'] = 'data内数据即调起支付所需参数，无需进行加密操作，直接使用';
		// $return['data'] = $weapp -> paysign($prepay_id);
		$return['data']  = ['id'=>$order_id,'total_fee' => $total_fee,'user_money' => $orderInfo['user_money']];
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
		//判断当前订单是否过期，从创建起，两小时之内
        $time = time() - 7200;
        if(strtotime($order['create_time']) < $time){
            $return['code'] = 10011;$return['msg'] = '订单已过期';return json($return);
        }
        if($order['price'] == 0 && $order['user_money'] != 0){
            //订单金额等于0
            $flag = 888888;
        }else{
            $weapp = new \app\weixin\controller\Common($this->apps);
            //prepay_id是否过期，过期重新生成
            if( time() - $order['prepay_time'] > 7200 ){
                //重新生成商户订单号
                $order_sn = date('Y').time().rand(1000,9999);
                $prepay_id = $weapp -> get_prepay_id($this->user['openid'],$order['price']*100,$order_sn,$order['id'],'西瓜科技-'.$order['name']);
                if(!$prepay_id){
                    $return['code'] = 10010;
                    $return['msg_test'] = '生成prepay_id出错';
                }
                model('goods_order') -> save(['prepay_id'=>$prepay_id,'prepay_time'=>time(),'order_sn' => $order_sn],['id'=>$order['id']]);
            }else{
                $prepay_id = $order['prepay_id'];
            }
            $flag = $weapp -> paysign($prepay_id);
        }
		//判断订单的金额是否是0，如果是0
		$return['code'] = 10000;
		$return['msg_test'] = 'data内数据即调起支付所需参数，无需进行加密操作，直接使用';
		$return['data'] = $flag;
		return json($return);
	}

    /**
     * @return \think\response\Json
     * 当用户的订单是0的时候,不需要调用微信的接口，直接修改订单状态
     */
    public function setUserOrderState(){
        $order_id = $this -> data['order_id'];
        if(!isset($order_id) && !$order_id){
            $return['code'] = 10001;
            $return['msg_test'] = '订单id不对';
            return json($return);
        }
        $orderInfo = db('goods_order') -> field('user_id,appid,name,carts,price,user_money,state') -> where(['id' => $order_id]) -> find();
        if(!$orderInfo || $orderInfo['user_id'] != $this -> user['id'] || $orderInfo['price'] != 0 || $orderInfo['user_money'] == 0 || $orderInfo['state'] != 0){
            $return['code'] = 10002;
            $return['msg_test'] = '参数错误';
            return json($return);
        }
        //修改订单的状态，并发送邮件给管理员，这种订单不参与分销
        model('goods_cart') -> save(['is_pay'=>1],['id'=>['exp','in ('.$orderInfo['carts'].')']]);
        model('goods_order') -> save(['state'=>1],['id'=>$order_id]);
        //判断当前小程序是否有分销
        action('custom/Notify/sendMail',[$orderInfo['appid'],$orderInfo['name']]);
        $return['code'] = 10000;
        $return['msg_test'] = '支付成功';
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
		$where1 = array();
		if($where['state'] == 0){
		    //显示未付款的。时间为两个小时之内的
            $where1['create_time'] = ['egt',time() - 7200];
        }
		$where['appid'] = $this->apps;
		//->alias('a')->join($join)  -> where($where) 
		$info = model('goods_order')
            ->field('id,name,num,pic,price,user_money,order_sn,username,tel,dist,city,province,address,carts,zipcode,kd_code,kd_number,create_time')
            -> where($where)
            -> where($where1)

            -> page($page)
            -> limit($limit_num)
            -> order('id desc')
            -> select();

		foreach($info as $k=>$v){
		    $cart_id = $v['carts'];
		    $cartid = explode(',',$cart_id);
		    foreach($cartid as $key =>$value){
                $cart = model('goods_cart')->Field(['id','spec_value'])->where("id",$value)->find();
                $info[$k]['spec_value'] = $cart['spec_value'];
            }

	    $cart = model('goods_cart')->where("id",'in',$v['carts'])->select();


            $info[$k]['goods'] = $cart;
        }

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
		$info['money'] = $this->user->money;;
		$return['code'] = 10000;
		$return['data'] = $info;
		return json($return);
	}

    //订单取消
    function  order_close(){

        if(!isset($this->data['id'])){
            $return['code'] = 10001;$return['msg_test'] = '缺少参数id';return json($return);
        }
        $order = model('goods_order') -> where('id',$this->data['id']) -> find();
        if(empty($order) || $order['appid'] != $this->apps  || $order['user_id'] != $this->user['id'] ){
            $return['code'] = 10001;
            $return['msg_test'] = '订单不存在';
            return json($return);
        }
        if($order['state'] != 0 ){
            $return['code'] = 10001;
            $return['msg_test'] = '订单不是待付款状态';
            return json($return);
        }

        $info  = model('goods_order')->where('id',$this->data['id'])->update(['state'=>'5']);

        if($info){
            $return['code'] = 10000;
            $return['msg'] = '订单取消成功';
            return json($return);
        }else{
            $return['code'] = 10003;
            $return['msg'] = '操作失败';

	    return json($return);
        }

     }
     

    /**
     * 订单取消
     * 订单的state改变状态为4表示订单退款，同时is_return生效，0 后台没操作 1 后台已同意 2 后台不同意
     * 如果后台管理员不同意，要把订单的state改为原来的状态，同时is_return改为2，订单原来的状态保存在is_expire这个字段中，这个字段的作用只有在订单未支付也就是state为0 的时候才起作用 ，可以暂时使用这个字段保存订单状态
     */
    public function orderRefund(){
        $orderInfo = db('goods_order') -> field('id,state') -> where(['id' => $this -> data['order_id']]) -> find();
        if(!$orderInfo || $orderInfo['state'] == 0 || $orderInfo['state'] == 5){
            $return['code'] = 10001;
            $return['msg_test'] = '操作失败';
            return json($return);
        }
        $info['state'] = 4;
        $info['is_return'] = 0;
        $info['is_expire'] = $orderInfo['state'];
        $res = model('goods_order') -> save($info,['id' => $orderInfo['id']]);
        if($res){
            $return['code'] = 10000;
            $return['msg_test'] = '退款申请已发出,管理员审核中';
            return json($return);
        }else{
            $return['code'] = 10002;
            $return['msg_test'] = '退款申请失败';
            return json($return);
        }
    }



    /**
     * 前台点击订单的确定收货的时候，改变订单的state为已完成状态
     */
    public function setOrderState(){
        if(!isset($this -> data['order_id']) || !isset($this->data['state'])){
            $return['code'] = 10001;
            $return['msg_test'] = '参数缺失';
            return json($return);
        }
        if($this -> data['state'] != 3){
            $return['code'] = 10002;
            $return['msg_test'] = '状态不对';
            return json($return);
        }
        //查看订单状态是否是发货状态，只有发货状态的订单才可以已完成
        $res = db('goods_order') -> where(['id' => $this -> data['order_id']]) -> find();
        if($res['state'] != 2 || $res['user_id'] != $this -> user -> id){
            $return['code'] = 10003;
            $return['msg_test'] = '状态不可改变';
            return json($return);
        }
        $res = db('goods_order') -> where(['id' => $this->data['order_id']]) -> setField('state',3);
        if($res){
            $return['code'] = 10000;
            $return['msg_test'] = '修改成功';
            return json($return);
        }else{
            $return['code'] = 10004;
            $return['msg_test'] = '修改失败';
            return json($return);
        }
    }

    /**
     * form_id 发送模板的时候需要使用到这个
     */

    public function saveFormId(){
        if(!isset($this->data['form_id'])){
            $return['code'] = 10001;
            $return['msg_test'] = '错误';
            return json($return);
        }
        $data['user_id'] = $this->user['id'];
        $data['openid'] = $this->user['openid'];
        $data['form_id'] = $this->data['form_id'];
        $data['create_time'] = time();
        $data['appid'] = $this->user['apps'];
        $res = db('form_list') -> insertGetId($data);
        if($res){
            $return['code'] = 10000;
            $return['msg_test'] = 'ok';
            return json($return);
        }else{
            $return['code'] = 10002;
            $return['msg_test'] = '失败失败';
            return json($return);
        }
    }
    
    //生成分销二维码
    public function get_my_qrcode(){
        
        $info = db('user')->field('id') -> where(['apps' =>$this->apps])->find();
        $uid = $info['id'];
        $my_qrcode = action('weixin/common/get_qrcodes',$uid);
        if($my_qrcode){
            $return['code'] = 10000;
            $return['data'] =$my_qrcode;
            return json($return);
        }else{
            $return['code'] = 10001;
            $return['msg_test'] = '网络错误';
            return json($return);
        }
        
    }
    //确定上级
    public function superior(){
        if (!isset($this->data['scene'])) {
            $return['code'] = 10001;
            $return['msg_test'] = '用户id不存在';
            return json($return);
        }
        //判断二维码是否有效
        $id = db('user')->where(['id' => $this->data['scene']])->value('id');
        if ($id) {
            //排除用户自己扫自己
            if ($id != $this->user['id']) {
                //判断用户上级是否已确定
                $p_id = db('user')->where(['id' => $this->user['id']])->value('p_id');
                if (!$p_id) {
                    $res = model('user')->allowField(true)->where(['id' => $this->user['id']])->update(['p_id' => $this->data['scene']]);
                    if ($res) {
                        $return['code'] = 10000;
                        $return['msg_test'] = 'ok';
                        return json($return);
                    } else {
                        $return['code'] = 10002;
                        $return['msg_test'] = '网络错误';
                        return json($return);
                    }
                } else {
                    $return['code'] = 10010;
                    $return['msg_test'] = '用户上级已确定';
                    return json($return);
                }
            }
        } else {
            $return['code'] = 10003;
            $return['msg'] = '此用户不存在,二维码无效';
            $return['msg_test'] = '用户id不存在';
            return json($return);
        }
        
    }
<<<<<<< HEAD

    /**
     * 用户前台提现
     */
    public function userWithdraw(){
        //判断是否开启了提现
        $appid = $this -> data['apps'];
        $rule = db('dist_rule') -> field('is_withdraw,withdraw_type') -> where(['appid' => $appid]) -> find();
        if(!$rule || $rule['is_withdraw'] == 0){
            //后台没有设置或没有开启
            $return['code'] = 10001;
            $return['msg'] = '提现功能没有开启';
            return json($return);
        }else if($rule['is_withdraw'] == 1){
            //后台没有设置或没有开启
            $return['code'] = 10000;
            $return['data'] = array('withdraw_type' => $rule['withdraw_type'],'money' => $this -> user['money']);
            $return['msg_test'] = '提现功能已开启';
            return json($return);
        }
    }

    /**
     * 保存用户的提现记录
     * withdraw_type 1 支付宝 2 银行卡
     * name 名字 tel 手机号 money 提现金额 ali_number 支付宝账号 bank_name 开户行 bank_number 银行卡账号
     */
    public function setUserWithdraw(){
        //判断用户是否开启了提现。
        $appid = $this -> data['apps'];
        $rule = db('dist_rule') -> field('is_withdraw,withdraw_type') -> where(['appid' => $appid]) -> find();
        if(!$rule || $rule['is_withdraw'] == 0){
            //后台没有设置或没有开启
            $return['code'] = 10001;
            $return['msg'] = '提现功能没有开启';
            return json($return);
        }
        if(!$this -> data['name'] || !$this -> data['tel'] || !$this -> data['money'] || !$this -> data['withdraw_type']){
            //后台没有设置或没有开启
            $return['code'] = 10002;
            $return['msg_test'] = '参数值缺失';
            return json($return);
        }

        if($this -> data['withdraw_type'] == 1 && !$this -> data['ali_number']){
            //后台没有设置或没有开启
            $return['code'] = 10002;
            $return['msg_test'] = '参数值缺失';
            return json($return);
        }else if($this -> data['withdraw_type'] == 0 && (!$this -> data['bank_name'] || !$this -> data['bank_number'])){
            //后台没有设置或没有开启
            $return['code'] = 10002;
            $return['msg_test'] = '参数值缺失';
            return json($return);
        }
        //判断金额是否足够
        $money = $this -> data['money'] * 1;
        if(!$money || $money <= 0){
            $return['code'] = 10003;
            $return['msg'] = '请输入正确的金额';
            return json($return);
        }
        //判断是否有余额
        if($money > $this -> user['money']){
            $return['code'] = 10004;
            $return['msg'] = '余额不足';
            return json($return);
        }
        //余额充足
        $model = db();
        $model -> startTrans();
        try{
            $res = db('user') -> where(['id' => $this -> user['id']]) -> setDec('money',$money);
            if($res){
                //保存记录
                $withdraw['money'] = $money;
                $withdraw['name'] = $this -> data['name'];
                $withdraw['appid'] = $this -> user['apps'];
                $withdraw['user_id'] = $this -> user['id'];
                $withdraw['tel'] = $this -> data['tel'];
                $withdraw['create_time'] = time();
                if($this -> data['withdraw_type'] == 1){
                    //支付宝
                    $withdraw['ali_number'] = $this -> data['ali_number'];
                }else if($this -> data['withdraw_type'] == 2){
                    //银行卡
                    $withdraw['bank_name'] = $this -> data['bank_name'];
                    $withdraw['bank_number'] = $this -> data['bank_number'];
                }
                $withdraw['withdraw_type'] = $this -> data['withdraw_type'];
                db('withdraw_record') -> insert($withdraw);
                $model -> commit();
                $return['code'] = 10000;
                $return['msg'] = '提现成功,后台审核中';
                return json($return);
            }
        }catch (Exception $e){
            $model -> rollback();
            $return['code'] = 10005;
            $return['msg'] = '网络错误，请稍后重试';
            return json($return);
        }
    }

    /**
     * 得到用户的当前提现的记录
     */
    public function getUserWithdrawRecord(){
        $page = isset($this->data['page']) ? $this->data['page'] : 1;
        $limit_num = isset($this->data['limit_num']) ? $this->data['limit_num'] : 10;
        $count = db('withdraw_record') -> where(['user_id' => $this -> user['id']]) -> count();
        $info = db('withdraw_record')
            -> field('money,withdraw_type,is_audit,create_time')
            -> where(['user_id' => $this -> user['id']])
            -> page($page,$limit_num)
            -> select();
        $return['code'] = 10000;
        $return['msg_test'] = 'ok';
        $return['data'] = $info;
        $return['number'] = $count;
        return json($return);
    }

=======
>>>>>>> f3c10d5320de806fe333c38aaa4b99cf1c479c03
    

}



 ?>