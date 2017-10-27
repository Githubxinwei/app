<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/27 0027
 * Time: 09:16
 */

namespace app\custom\controller;
use think\Controller;

class Appsub  extends Xiguakeji
{

//获取bannar信息
    function get_bannar(){

        $info = db('loop_img') -> where('appid',$this->apps) -> find();
        $return['code'] = 10000;
        if($info){
            $info['content'] = json_decode($info['content'],true);
            $return['data'] = $info['content'];
        }else{
            $return['data'] = '';
        }
        return json($return);
    }

    //获取预约列表
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


}