<?php
/**
 * Created by PhpStorm.
 * User: 宋妍妍
 * Date: 2017/11/14 0014
 * Time: 09:31
 * 酒店小程序后台接口
 */

namespace app\custom\controller;
use think\Db;

class Hotel extends Action{

    /*构造函数*/
    public function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub
        $this -> data = input('post.','','htmlspecialchars');
        if(!isset($this -> data['appid'])){
            $return['code'] = 10002;
            $return['msg'] = 'appid不存在或者小名字不存在';
            $return['msg_test'] = 'appid不存在或者小名字不存在';
            return json($return);
        }
        if(!preg_match("/^\d{8}$/",$this -> data['appid'])){
            $return['code'] = 10003;
            $return['msg'] = 'appid是一个8位数';
            $return['msg_test'] = 'appid是一个8位数';
            return json($return);
        }
        $is_true = db('app') -> where(['appid' => $this -> data['appid']]) -> find();
        if(!$is_true){
            $return['code'] = 10004;
            $return['msg'] = '当前用户没有此小程序,appid不对';
            $return['msg_test'] = '当前用户没有此小程序,appid不对';
            return json($return);
        }
        if($is_true['custom_id'] != $this->custom->id){
            $return['code'] = 10005;
            $return['msg'] = '当前小程序不是这个用户的';
            $return['msg_test'] = '当前小程序不是这个用户的';
            return json($return);
        }
	unset($this -> data['session_key']);
    }

    /*添加门店*/
    public  function  add_stores(){

    
        if(!isset($this -> data['stores_name']) || !isset($this -> data['stores_tell']) || !isset($this -> data['stores_address'])){
            $return['code'] = 10007;
            $return['msg'] = '参数丢失';
            $return['msg_test'] = '参数丢失';
            return json($return);
        }
        if(trim($this -> data['stores_name']) == '' || trim($this -> data['stores_tell']) =='' || trim($this -> data['stores_address'] == '')){
            $return['code'] = 10007;
            $return['msg'] = '参数不能为空';
            $return['msg_test'] = '参数不能为空';
            return json($return);
        }
        $data = $this->data;
        $data['custom_id'] = $this -> custom -> id;
        $where['appid'] = $this->data['appid'];

        /*查询小程序门店套餐信息*/
        $info = db('stores_setmeal')->field('surplus_number')->where($where)->find();

        if($info['surplus_number'] > 0){
            unset($data['session_key']);
            db('stores')->insert($data);
            $res =  db('stores_setmeal')->where($where)->setDec("surplus_number",1); //门店剩余数量减去
            if($res){
                $return['code'] = 10000;
                $return['msg'] = '添加门店成功';
                $return['msg_test'] = '添加门店成功';
                return json($return);
            }else{
                $return['code'] = 10001;
                $return['msg'] = '网络错误';
                $return['msg_test'] = '网络错误';
                return json($return);
            }
        }else{
            $return['code'] = 10006;
            $return['msg'] = '门店数量已达上限';
            $return['msg_test'] = '门店数量已达上限';
            return json($return);
        }



    }

    /*门店列表获取 appid*/
    public  function  list_stores()
    {
        $where['appid'] = $this->data['appid'];
        $keyword = isset($this->data['keyword']) ? $this->data['keyword'] : '';
        if($keyword){$where['stores_name'] = array('like','%'.$keyword.'%');}
        if(isset($this->data['page'])){$page = $this->data['page'];}else{$page = 1;}
        if(isset($this->data['limit'])){$limit = $this->data['limit'];}else{$limit = 20;}
        $info = db('stores')->where($where)->page($page)->limit($limit)->select();

        /*查询小程序门店套餐信息*/
        $num =  db('stores_setmeal')->where(['appid' => $where['appid']])->find(); //门店套餐信息
        if(!$num){
            $data_set['appid'] = $this->data['appid'];
            db('stores_setmeal')->insert($data_set);
            $num =  db('stores_setmeal')->where(['appid' => $where['appid']])->find(); //门店套餐信息
        }


         /*试用期结束后  其他门店不可使用*/
        if($num && $num['set_meal'] != '' && time() > $num['probation_end'] && $num['use_end_time'] ='' && $num['is_message'] == 1){
            db('stores')->where($where)->update(['state'=>'1']);
            $use = db('stores')->field('id')->where($where)->order("id desc")->find();
            $state['state'] = 1 ;
            db('stores')->where(['appid'=>$where['appid'],'id'=>$use['id']])->update($state);
            $meat['surplus_number'] = 0 ;
            $meat['number'] = 1 ;

            /*推送支付账单
             $meat['is_message'] = 2 ;
            */

            db('stores_setmeal')->where($where)->update($meat);
        }

            $return['code'] = 10000;
            $return['data'] = $info;
            $return['number'] = $num;
            return json($return);

    }

    /*获取门店信息*/
    public  function  get_stores_detail(){
        $where['appid'] = $this->data['appid'];
        $where['id'] = $this->data['id'];
        $res = db('stores')->where($where)->find();
        if($res){
            $return['code'] = 10000;
            $return['data'] = $res;
            return json($return);
        }else{
            $return['code'] = 10001;
            $return['msg'] = '网络错误';
            $return['msg_test'] = '网络错误';
            return json($return);
        }

    }

    /*门店修改 appid*/
    public function  update_stores(){
        if(!isset($this -> data['stores_name']) || !isset($this -> data['stores_tell']) || !isset($this -> data['stores_address'])){
            $return['code'] = 10007;
            $return['msg_test'] = '参数丢失';
            return json($return);
        }
        $where['appid'] = $this->data['appid'];
        $where['id'] = $this->data['id'];
        $data = $this->data();
        $res = db('stores')->where($where)->update($data);
        if($res){
            $return['code'] = 10000;
            $return['msg'] = '修改成功';
            $return['msg_test'] = '修改成功';
            return json($return);
        }else{
            $return['code'] = 10001;
            $return['msg'] = '网络错误';
            $return['msg_test'] = '网络错误';
            return json($return);
        }

    }

    /*门店删除 appid*/
    public  function  delete_stores()
    {

        $where['appid'] = $this->data['appid'];

        if(isset($this->data['id'])) $where[]=['exp',"FIND_IN_SET(".$this->data['id'].",stores_id)"];


        $info = db('stores')->field("state")->where(['appid'=>$this->data['appid'],'id'=>$this->data['id']])->find();

        if($info['state'] == 1 ){
            db('stores_setmeal')->where(['appid'=>$this->data['appid']])->setInc('surplus_number',1);
        }

        $res = db('stores')->where(['appid'=>$this->data['appid'],'id'=>$this->data['id']])->delete(); //删除门店

        db('rooms')->where(['appid'=>$this->data['appid'],'stores_id'=>$this->data['id']])->delete();  //删除门店下的房间


        if($res){
            $return['code'] = 10000;
            $return['msg'] = '删除成功';
            $return['data'] = $info;
            return json($return);
        }else{
            $return['code'] = 10001;
            $return['msg'] = '网络错误';
            $return['msg_test'] = '网络错误';
            return json($return);
        }
    }

    /*升级门店 appid set_meal set_meal_type*/
    public function stores_up(){

        if(!isset($this -> data['set_meal']) || !isset($this -> data['number'])){
            $return['code'] = 10007;

            $return['msg_test'] = '套餐类型必须选择';
            return json($return);
        }
        $where['appid'] = $this->data['appid'];
        $data = $this->data;
            $num =db('stores')->where(['appid'=> $where['appid'],'state' => 1])->count(); //计算已有门店
            $number = $this->data['number'];
        $data['number'] = $number ;
        $data['surplus_number'] = $number - $num  ;
        $data['probation_start'] = time();
        $data['probation_end'] = strtotime("+1 months");
        $data['is_message'] = 1 ;
        $res = db('stores_setmeal')->where($where)->update($data);
        if($res){
            $return['code'] = 10000;
            $return['msg'] = '升级成功';

            return json($return);
        }else{
            $return['code'] = 10001;
            $return['msg'] = '网络错误';
            $return['msg_test'] = '网络错误';
            return json($return);
        }
      }

    /*添加房间 appid  房间信息字段*/
    public function  rooms_add(){

          if(!isset($this -> data['room_type'])     ||  !isset($this -> data['number']) || !isset($this -> data['price']) || !isset($this -> data['man']) || !isset($this -> data['area']) || !isset($this -> data['bed_type']) || !isset($this -> data['facilities']) || !isset($this -> data['photo']) || !isset($this -> data['stores_id'])){
              $return['code'] = 10007;
              $return['msg'] = '房间参数必须完整';
              $return['msg_test'] = '房间参数必须完整';
              return json($return);
          }
        //如果上传图片，判断图片是否是十个
        if(isset($this -> data['photo'])){
            $pic_number = count(explode(',',$this -> data['photo']));
            if($pic_number > 10){
                $return['code'] = 10008;
                $return['msg'] = '一个商品最多上传10张图片';
                $return['msg_test'] = '一个商品最多上传10张图片';
                return json($return);
            }
        }


         $where['appid'] = $this->data['appid'];
         $data = $this->data;
         $data['facilities'] = $_POST['facilities'];
         $data['detail'] = $_POST['detail'];
         $data['number_in'] = $data['number'];
         $stores_id = $data['stores_id'];
         $stores_id = explode(',',$stores_id);

              if(isset($this -> data['stores_type']) && $this->data['stores_type'] == 2){
                  $stores = db('stores')->field("id")->where($where)->select();
                  $id = [];
                  foreach($stores as $k=>$v){
                      array_push($id,$v['id']);
                  }
                  $stores_id = $id;
              }
         $data['custom_id'] = $this -> custom -> id;

              $arr = [];
              foreach($stores_id as $k =>$v){
                  $array = $data;
                  $array['stores_id']  = $v ;
                  array_push($arr,$array);
              }

          $info = db('rooms')->insertAll($arr);

          if($info){
              $return['code'] = 10000;
              $return['msg'] = '添加成功';
              $return['msg_test'] = '添加成功';
              return json($return);
          }else{
              $return['code'] = 10000;
              $return['msg'] = '网络错误';
              $return['msg_test'] = '网络错误';
              return json($return);
          }

      }

    /*房间列表 appid*/
    public function rooms_list(){

        $where['appid'] = $this->data['appid'];

        if(isset($this->data['stores_id'])) $where[]=['exp',"FIND_IN_SET(".$this->data['stores_id'].",stores_id)"];
        if(isset($this->data['page'])){$page = $this->data['page'];}else{$page = 1;}
        if(isset($this->data['limit'])){$limit = $this->data['limit'];}else{$limit = 20;}


        $info = model('rooms') -> where($where)->page($page)->limit($limit)->select();


          $return['code'] = 10000;
          $return['data'] = $info;
          return json($return);

      }

    /*房间修改*/
    public function  rooms_update(){

        if(!isset($this -> data['room_type'])     ||  !isset($this -> data['number']) || !isset($this -> data['price']) || !isset($this -> data['man']) || !isset($this -> data['area']) || !isset($this -> data['bed_type']) || !isset($this -> data['facilities']) || !isset($this -> data['photo']) || !isset($this -> data['stores_id'])){
            $return['code'] = 10007;
            $return['msg'] = '房间参数必须完整';
            $return['msg_test'] = '房间参数必须完整';
            return json($return);
        }
        //如果上传图片，判断图片是否是十个
        if(isset($this -> data['photo'])){
            $pic_number = count(explode(',',$this -> data['photo']));
            if($pic_number > 10){
                $return['code'] = 10008;
                $return['msg'] = '一个商品最多上传10张图片';
                $return['msg_test'] = '一个商品最多上传10张图片';
                return json($return);
            }
        }

        $where['appid'] = $this->data['appid'];
        $where['id'] = $this->data['id'];
        $data = $this->data;
        $data['facilities'] = $_POST['facilities'];
        $data['detail'] = $_POST['detail'];
               /*更新房间剩余数量*/
        $num = db("rooms")->field('number,number_in')->where($where)->find();
        $num = $num['number'] - $num['number_in'];
        $data['number_in']   =   $data['number'] - $num;

        unset($data['stores_id']);

        $info = db('rooms')->where($where)->update($data);

        if($info){
            $return['code'] = 10000;
            $return['msg'] = '修改成功';
            $return['msg_test'] = '修改成功';
            return json($return);
        }else{
            $return['code'] = 10001;
            $return['msg'] = '网络错误';
            $return['msg_test'] = '网络错误';
            return json($return);
        }

    }

    /*房间详细信息获取*/
    public  function get_rooms(){
        $where['appid'] = $this->data['appid'];
        $where['custom_id'] = $this -> custom -> id;
        $where['id'] = $this->data['id'];
        $info =  db("rooms")->where($where)->find();
        if($info){
            $return['code'] = 10000;
            $return['msg'] = '修改成功';
            $return['data'] = $info;
            return json($return);
        }else{
            $return['code'] = 10001;
            $return['msg'] = '网络错误';
            $return['msg_test'] = '网络错误';
            return json($return);
        }
    }

    /*房间删除*/
    public function rooms_delete(){

        $where['appid'] = $this->data['appid'];
        $where['id'] = $this->data['id'];
        $where['stores_id'] = $this->data['stores_id'];

       /* $dalete = db('rooms')->where($where)->find();
        $de = explode(',',$dalete['stores_id']);
        $id = [];
        foreach($de as $k=>$v){
            if($v != $this->data['stores_id']){
                array_push($id,$v);
            }
        }
        if(empty($id)){
            $info = db('rooms')->where($where)->delete();
        }else{
            $data['stores_id'] = implode(',',$id);
            $info = db('rooms')->where($where)->update($data);
        }*/
        $info = db('rooms')->where($where)->delete();
        if($info){
            $return['code'] = 10000;
            $return['msg'] = '删除成功';
            $return['data'] = $info;
            return json($return);
        }else{
            $return['code'] = 10001;
            $return['msg'] = '网络错误';
            $return['msg_test'] = '网络错误';
            return json($return);
        }

    }

    /*品牌信息添加修改*/
    public  function  brands(){
        if(!isset($this -> data['name']) || !isset($this -> data['site_url']) || !isset($this -> data['pic']) || !isset($this -> data['start_time']) || !isset($this -> data['over_time'])){
            $return['code'] = 10007;
            $return['msg'] = '品牌参数必须完整';
            $return['msg_test'] = '品牌参数必须完整';
            return json($return);
        }
        $data = $this->data;
        $data['custom_id'] = $this -> custom -> id;
        $where['appid'] = $this->data['appid'];
        $where['custom_id'] = $this -> custom -> id;
        if(isset($this -> data['id'])){ $where['id'] = $this->data['id'];}

        $info =  db("app")->where($where)->find();
        if(!$info){
           $res =  db("app")->insert($data);
            if($res){
                $return['code'] = 10000;
                $return['msg'] = '添加成功';
                $return['msg_test'] = '添加成功';
                return json($return);
            }else{
                $return['code'] = 10001;
                $return['msg'] = '网络错误';
                $return['msg_test'] = '网络错误';
                return json($return);
            }
        }else{
            db("app")->where($where)->update($data);
            $res =  db("app")->insert($data);
            if($res){
                $return['code'] = 10000;
                $return['msg'] = '修改成功';
                $return['msg_test'] = '修改成功';
                return json($return);
            }else{
                $return['code'] = 10001;
                $return['msg'] = '网络错误';
                $return['msg_test'] = '网络错误';
                return json($return);
            }
        }

    }

    /*品牌信息获取*/
    public  function get_brands(){
        $where['appid'] = $this->data['appid'];
        $where['custom_id'] = $this -> custom -> id;
        $info =  db("app")->field("id,name,pic,desc,site_url,start_time,over_time")->where($where)->find();
        if($info){
            $return['code'] = 10000;
            $return['msg'] = '修改成功';
            $return['data'] = $info;
            return json($return);
        }else{
            $return['code'] = 10001;
            $return['msg'] = '网络错误';
            $return['msg_test'] = '网络错误';
            return json($return);
        }
    }

    /*房型获取*/
    public  function get_room_type(){
        $where['appid'] = $this->data['appid'];
        $where['custom_id'] = $this -> custom -> id;
        if(!isset($this->data['stores_id'])){
            $return['code'] = 10007;
            $return['msg'] = '请选择门店';
            $return['msg_test'] = '请选择门店';
            return json($return);
        }
        if(isset($this->data['stores_id'])) $where[]=['exp',"FIND_IN_SET(".$this->data['stores_id'].",stores_id)"];

        $rooms =  db('rooms')->distinct(true)->field('room_type')->where($where)->select();

        $room = [];
        foreach($rooms as $k=>$v){
            array_push($room,$v['room_type']);
        }

        if($room || empty($info)){
            $return['code'] = 10000;
            $return['data'] = $room;
            return json($return);
        }else{
            $return['code'] = 10001;
            $return['msg'] = '网络错误';
            $return['msg_test'] = '网络错误';
            return json($return);
        }




    }

    /*价格调整添加*/
    public  function rules_add(){
        if(!isset($this -> data['name'])  ||  !isset($this -> data['start_time']) || !isset($this -> data['over_time']) || !isset($this -> data['rules']) || !isset($this -> data['rules_detail']) || !isset($this -> data['rules_range'])){
            $return['code'] = 10008;
            $return['msg'] = '参数必须完整';
            $return['msg_test'] = '参数必须完整';
            return json($return);
        }
        if(!isset($this->data['stores_id']) || $this->data['stores_id'] == ''){
            $return['code'] = 10007;
            $return['msg'] = '请选择门店';
            $return['msg_test'] = '请选择门店';
            return json($return);
        }
        $data = $this->data;
        $data['custom_id'] = $this -> custom -> id;
        $res = db('rooms_rules')->insert($data);
        if($res){
            $return['code'] = 10000;
            $return['msg'] = '添加成功';
            $return['msg_test'] = '添加成功';
            return json($return);
        }else{
            $return['code'] = 10001;
            $return['msg'] = '网络错误';
            $return['msg_test'] = '网络错误';
            return json($return);
        }


    }

    /*价格调整列表*/
    public function rules_list(){


        $where['appid'] = $this->data['appid'];
        $where['custom_id'] = $this -> custom -> id;

        if(isset($this->data['stores_id'])) $where[]=['exp',"FIND_IN_SET(".$this->data['stores_id'].",stores_id)"];
        if(isset($this->data['page'])){$page = $this->data['page'];}else{$page = 1;}
        if(isset($this->data['limit'])){$limit = $this->data['limit'];}else{$limit = 20;}
        $info  = db('rooms_rules')->where($where)->page($page,$limit)->select();
        $return['code'] = 10000;
        $return['data'] = $info;
        return json($return);



    }

    /*价格调整详细信息获取*/
    public  function  rules_get(){
        $where['appid'] = $this->data['appid'];
        $where['custom_id'] = $this -> custom -> id;
        $where['id'] = $this->data['id'];
        $info = db('rooms_rules')->where($where)->find();
        if($info){
            $return['code'] = 10000;
            $return['data'] = $info ;
            return json($return);
        }else{
            $return['code'] = 10001;
            $return['msg'] = '网络错误';
            $return['msg_test'] = '网络错误';
            return json($return);
        }
    }

    /*价格调整删除*/
    public  function  rules_delete(){
        $where['appid'] = $this->data['appid'];
        $where['custom_id'] = $this -> custom -> id;
        $where['id'] = $this->data['id'];
        $dalete = db('rooms_rules')->where($where)->find();
        $de = explode(',',$dalete['stores_id']);
        $id = [];
        foreach($de as $k=>$v){
            if($v != $this->data['stores_id']){
                array_push($id,$v);
            }
        }
        if(empty($id)){
            $res = db('rooms_rules')->where($where)->delete();
        }else{
            $data['stores_id'] = implode(',',$id);
            $res = db('rooms_rules')->where($where)->update($data);
        }
        if($res){
            $return['code'] = 10000;
            $return['msg'] = '删除成功';
            $return['msg_test'] = '删除成功';
            return json($return);
        }else{
            $return['code'] = 10001;
            $return['msg'] = '网络错误';
            $return['msg_test'] = '网络错误';
            return json($return);
        }
    }

    /*酒店风格*/
    public  function style_hotel(){

        if(!isset($this -> data['slogan'])  ||  !isset($this -> data['pic'])){
            $return['code'] = 10008;
            $return['msg'] = '标语或者封面图必须填写';
            $return['msg_test'] = '标语或者封面图必须填写';
            return json($return);
        }
        $data = $this->data;

        $data['custom_id'] = $this -> custom -> id;

        $where['appid'] = $this->data['appid'];
        $where['custom_id'] = $this -> custom -> id;
        $info = db('stores_style')->where($where)->find();

        if(!$info){
            $res = db('stores_style')->insert($data);
            if($res){
                $return['code'] = 10000;
                $return['msg'] = '添加成功';
                $return['msg_test'] = '添加成功';
                return json($return);
            }else{
                $return['code'] = 10001;
                $return['msg'] = '网络错误';
                $return['msg_test'] = '网络错误';
                return json($return);
            }
        }else{
            $res = db('stores_style')->where($where)->update($data);
            if($res){
                $return['code'] = 10000;
                $return['msg'] = '修改成功';
                $return['msg_test'] = '修改成功';
                return json($return);
            }else{
                $return['code'] = 10001;
                $return['msg'] = '网络错误';
                $return['msg_test'] = '网络错误';
                return json($return);
            }
        }
    }

    /*酒店风格内容获取*/
    public function style_detail(){

        $where['appid'] = $this->data['appid'];
        $where['custom_id'] = $this -> custom -> id;
        $info = db('stores_style')->where($where)->find();

        if($info){
            $return['code'] = 10000;
            $return['data'] = $info ;
            return json($return);
        }else{
            $return['code'] = 10001;
            $return['msg'] = '获取失败';
            $return['msg_test'] = '获取失败';
            return json($return);
        }

    }

    /*申请退款接口*/
    public  function refunds(){
        $weapp = new \app\weixin\controller\Common($this->data['appid']);
        $weapp -> __construct('WRJZVRMU');
        $refund_no = time().rand(1000,9999);
        $where['appid'] = $this->data['appid'];
        $where['id'] = $this->data['id'];
        $order_info = db('rooms_order')->where($where)->find();
        $res = $weapp->pay_refund($order_info['order_sn'],$refund_no,$order_info['total_price']*100);
        $over = db('rooms_order')->where($where)->update(['state'=>2,'is_refunds'=>2]);
        if($res && $over){
            $return['code'] = 10000;
            $return['msg_test'] = 'ok';
            return json($return);
        }else{
            $return['code'] = 10004;
            $return['msg_test'] = '失败';
            return json($return);
        }


    }

    /*风格封面图片调取*/
    public function get_pic(){

        $path = $_SERVER['SERVER_ADDR'].'/Uploads/banner';
        $result = scanFile($path);
        if($result){
            $return['code'] = 10000;
            $return['data'] = $result ;
            return json($return);
        }else{
            $return['code'] = 10001;
            $return['msg'] = '获取失败';
            $return['msg_test'] = '获取失败';
            return json($return);
        }
    }

    /*获取订单列表*/
    public function get_order_list(){

        $limit = isset($this->data['limit']) ? $this->data['limit'] : 10;
        $page = isset($this->data['page']) ? $this->data['page'] : 1;

        $where['state'] = isset($this->data['state']) ? $this->data['state'] : 0;   //状态

        if(isset($this->data['stores_id'])){
            if($this->data['stores_id']){
                $where['stores_id'] = $this->data['stores_id']; //门店
            }
        }

        $where['appid'] = $this->data['appid'];
        if(isset($this->data['username'])){
            if($this->data['username']){
                $where['username'] = $this->data['username'];  //入住人
            }
        }
        if(isset($this->data['order_sn'])){
            if($this->data['order_sn']){
                $where['order_sn'] = $this -> data['order_sn']; //订单id
            }
        }
        if(isset($this->data['user_tel'])){
            if($this->data['user_tel']){
                $where['user_tel'] = $this->data['user_tel'];  //联系方式
            }
        }
        if(isset($this->data['start_time']) && isset($this->data['end_time'])){
            if($this->data['start_time'] && $this->data['end_time']){
                $where['create_time'] = ['between',[$this->data['start_time'],$this->data['end_time']]];  //起止时间
            }
        }

        $data = db('rooms_order')
            ->field('id,state,username,order_sn,prepay_time,total_price,is_refunds')
            -> where($where)
            -> page($page,$limit)
            -> select();
        $number = db('rooms_order')
            ->field('id,state,username,order_sn,prepay_time,total_price,is_refunds')
            -> where($where)
            -> count();


        $return['code'] = 10000;
        $return['data'] = $data;
        $return['number'] = $number;
        $return['msg_test'] = 'ok';
        return json($return);
    }

    /*获取订单详细信息*/
    public  function get_order_detail(){
        $where['appid'] = $this->data['appid'];
        $where['id'] = $this->data['id'];

        $info = model('rooms_order')->where($where)->find();
        $rooms = db('rooms')->field('id,photo')->where(['id' => $info['rooms_id']])->find();
        $pic = explode(',',$rooms['photo']);
        $info['room_photo'] = $pic[0];
        $stores = db('stores')->field('stores_name')->where(['id' => $info['stores_id']])->find();
        $info['stores_name'] = $stores['stores_name'];

        if($info){
            $return['code'] = 10000;
            $return['data'] = $info;
            return json($return);
        }else{
            $return['code'] = 10001;
            $return['msg'] = '订单错误';
            $return['msg_test'] = '订单错误';
            return json($return);
        }

    }

    /*修改订单状态   入住结束确认接口*/
    public function update_order_state(){
        if(!isset($this->data['id'])){
            $return['code'] = 10001;
            $return['msg_test'] = '请传递参数';
            return json($return);
        }
        /*订单结束*/
        $state = db('rooms_order')->field('state')->where(['id'=>$this->data['id']])->find();
        if($state['state'] == 0){
            $return['code'] = 10003;
            $return['msg_test'] = '订单未支付,不能修改状态';
            return json($return);
        }
        if($state['state'] == 2){
            $return['code'] = 10003;
            $return['msg_test'] = '订单已退款,不能修改状态';
            return json($return);
        }
        if($state['state'] == 4){
            $return['code'] = 10003;
            $return['msg_test'] = '订单退款中,不能修改状态';
            return json($return);
        }

        $res = db('rooms_order') -> where(['id' => $this->data['id']]) -> update(['state'=>$this->data['state']]); //入住

        if($this->data['state'] == 5){
            $rooms = db('rooms_order') ->field('rooms_id')-> where(['id' => $this->data['id']])->find();
            db('rooms')-> where(['id' => $rooms['rooms_id']])->setInc('number_in' , 1);        //入住结束房间剩余数量加上
        }


        if($res){
            $return['code'] = 10000;
            $return['msg_test'] = 'ok';
            return json($return);
        }else{
            $return['code'] = 10004;
            $return['msg_test'] = '失败';
            return json($return);
        }
    }

    /*预览使用
     * 门店列表 以及 所对应的房间*/
    public  function get_stores_rooms_detail(){

        $where['appid'] = $this->data['appid'];
        $where['custom_id'] = $this -> custom -> id;
        if(isset($this->data['page'])){$page = $this->data['page'];}else{$page = 1;}
        if(isset($this->data['limit'])){$limit = $this->data['limit'];}else{$limit = 20;}

        $info = db("stores")->where($where)->page($page,$limit)->select();

        foreach($info as $k=>$v){
            $rooms = db('rooms')->where(['appid'=> $this->data['appid'], 'stores_id' => $v['id']])->page($page,$limit)->select();
            $info[$k]['rooms'] = $rooms;
        }

        $return['code'] = 10000;
        $return['data'] = $info;
        return json($return);

    }




}