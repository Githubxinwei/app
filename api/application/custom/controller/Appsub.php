<?php
/**
 * Created by PhpStorm.
<<<<<<< HEAD
 * User: Administrator
=======
 * User: 宋妍妍
>>>>>>> be93a5e607cf4ce40839d9279fce0f12d3cb56fe
 * Date: 2017/10/27 0027
 * Time: 09:16
 */

namespace app\custom\controller;
use think\Controller;

class Appsub  extends Xiguakeji
{

///*获取bannar信息  appid*/
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


    /*获取预约列表 appid*/
    function lists(){

        $keyword = isset($this->data['keyword']) ? $this->data['keyword'] : '';
        if($keyword){$where['name'] = array('like','%'.$keyword.'%');}
        if(isset($this->data['cate_id'])) $where[]=['exp',"FIND_IN_SET(".$this->data['cate_id'].",cate_id)"];
        $where['appid'] = $this->apps;
        if(isset($this->data['page'])){$page = $this->data['page'];}else{$page = 1;}
        if(isset($this->data['limit_num'])){$limit_num = $this->data['limit_num'];}else{$limit_num = 20;}
        $info = model('subscribe_service')
            -> field('id,service_name,service_pic,service_price')
            -> where($where)->page($page)
            -> limit($limit_num)
            -> order('id desc')
            -> select();
        $return['code'] = 10000;
        $return['data'] = $info;
        return json($return);

    }

   /*预约获取分类 appid*/
   public  function  subcate(){

       $info = db('subscribe_cate')-> field('id,name') -> where('appid',$this->apps) -> order('code desc') -> select();
       $return['code'] = 10000;$return['data'] = $info;
       return json($return);


   }

   /*服务人员列表  appid */
    public  function  getuser(){
        $info = db('subscribe_service_user')-> field('id,name,desc,pic') -> where('appid',$this->apps) -> order('id desc') -> select();
        $return['code'] = 10000;
        $return['data'] = $info;
        return json($return);
    }


    /*单个服务项目信息*/
    //获取单个商品信息
    function get_one(){
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










    


}
