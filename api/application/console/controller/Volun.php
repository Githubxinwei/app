<?php
/**
 * Created by PhpStorm.
 * User: 李佳飞
 * Date: 2017/11/18 0018
 * Time: 10:31
 * 任务计划自动执行，两小时没有支付的订单库存返还
 */
namespace app\console\controller;
use think\Controller;

class Volun extends Controller{

    /**
     * 执行方法
     */
    public function setOrderStock(){
        
        $info = db('goods_order')
            -> field('id,user_id,carts,user_money')
            -> where(['state' => 0,'create_time' => ['lt',time() - 7200],'is_expire' => 0])
            -> select();
        if(!$info){return;}
        $orderInfo = array();
        $goodList = array();
        $userMoney = array();
        foreach ($info as $k => $v){
            //判断当前订单是否用余额支付
            if($v['user_money'] != 0){
                $userMoney[] = array(
                  'id' => $v['user_id'],
                  'money' => $v['user_money']
                );
            }
            $orderInfo[] = array(
                'id' => $v['id'],
                'is_expire' => 1
            );
            //通过carts,找到这个订单的商品的库存
            $cartList = db('goods_cart') -> field('good_id,num,spec_key') -> where(['id' => ['exp',"in ({$v['carts']})"]]) -> select();
            if(!$cartList){continue;}
            foreach ($cartList as $key => $val){
                //通过good_id 把商品库存增加
                $goodInfo = db('goods') -> field('stock,spec') -> where(['id' => $val['good_id']]) -> find();
                if($goodInfo){
                    //存在，判断是属性库存还是本身的库存
                    if($val['spec_key'] === null){
                        //是商品本身的库存
                        if($goodInfo['stock'] != -1){
                            $goodList[] = array(
                                'id' => $val['good_id'],
                                'stock' => $goodInfo['stock'] + $val['num'],
                            );
                        }
                    }else{
                        //表示这个是在属性库存中
                        $spec = json_decode($goodInfo['spec'],true);
                        if($spec[$val['spec_key']]['lastNum'] != '∞'){
                            $spec[$val['spec_key']]['lastNum'] = $spec[$val['spec_key']]['lastNum'] + $val['num'];
                            $spec = json_encode($spec);
                            $goodList[] = array(
                                'id' => $val['good_id'],
                                'spec' => $spec,
                            );
                        }

                    }
                }
            }
        }
        model('goods') -> saveAll($goodList);
        model('goods_order') -> saveAll($orderInfo);
        model('user') -> saveAll($userMoney);

    }

}
