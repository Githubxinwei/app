<?php
/**
 * Created by PhpStorm.
 * User: 宋妍妍
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

    /*获取预约列表 appid*/
    function lists(){

        $keyword = isset($this->data['keyword']) ? $this->data['keyword'] : '';
        if($keyword){$where['name'] = array('like','%'.$keyword.'%');}
        if(isset($this->data['cid'])) $where[]=['exp',"FIND_IN_SET(".$this->data['cid'].",cid)"];
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

    /*分类下的服务列表*/





}
