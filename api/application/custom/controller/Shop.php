<?php
/**
 * Created by PhpStorm.
 * User: 李佳飞
 * Date: 2017/9/27 0027
 * Time: 11:40
 * 商品，分类
 */
namespace app\custom\controller;
class Shop extends Action{

    /**
     *获取用户的商品别表
     * appid,page,number,cate_id
     */
    public function getUserGoods(){
        $data = input('post.','','htmlspecialchars');
        if(!isset($data['appid'])){
            $return['code'] = 10001;
            $return['msg'] = '缺少参数值';
            $return['msg_test'] = '当前小程序的appid没有';
            return json($return);
        }
        if(!preg_match("/^\d{8}$/",$data['appid'])){
            $return['code'] = 10002;
            $return['msg'] = 'appid格式错误';
            $return['msg_test'] = 'appid是一个8位数';
            return json($return);
        }
        $custom_id = $this -> custom -> id;
        $is_true = db('app') -> where(['appid' => $data['appid'],'custom_id' => $custom_id]) -> select();
        if(!$is_true){
            $return['code'] = 10003;
            $return['msg'] = '当前用户没有此小程序';
            $return['msg_test'] = '当前用户没有此小程序,也就是appid不对';
            return json($return);
        }
        $page = isset($data['page']) ? $data['page'] : 1;
        $limit = isset($data['number']) ? $data['number'] : 15;
        $where = '';
        if(isset($data['cate_id'])){
            $cate_id = $data['cate_id'] * 1;
            $where = "FIND_IN_SET($cate_id,cid)";
        }
        $number = db('goods') -> where(['custom_id' => $custom_id,'appid' => $data['appid']]) -> count();
        $info = db('goods')
            -> where(['custom_id' => $custom_id,'appid' => $data['appid']])
            -> where($where)
            -> page($page,$limit)
            -> order('id desc')
            -> select();
        if($info){
            $return['code'] = 10000;
            $return['data'] = ['number' => $number,'info' => $info];
            $return['msg'] = '';
            $return['msg_test'] = '成功了';
            return json($return);
        }else{
            $return['code'] = 10004;
            $return['msg'] = '查询失败,请稍后重试';
            $return['msg_test'] = '查询失败';
            return json($return);
        }

    }

    /**
     * 删除用户的商品
     * good_id,appid
     */
    public function delUserGoods(){
        $data = input('post.','','htmlspecialchars');
        if(!isset($data['good_id']) || !isset($data['appid'])){
            $return['code'] = 10001;
            $return['msg'] = '参数值缺失';
            $return['msg_test'] = '缺少商品id或缺少appid';
            return json($return);
        }
        if(!preg_match("/^\d{8}$/",$data['appid'])){
            $return['code'] = 10002;
            $return['msg'] = 'appid格式错误';
            $return['msg_test'] = 'appid是一个8位数';
            return json($return);
        }
        $res = db('goods') -> where(['id' => $data['good_id'] * 1,'appid' => $data['appid'],'custom_id' => $this->custom->id]) -> delete();
        if($res){
            $return['code'] = 10000;
            $return['msg'] = '删除成功';
            $return['msg_test'] = '删除成功';
            return json($return);
        }else{
            $return['code'] = 10003;
            $return['msg'] = '删除数据失败';
            $return['msg_test'] = 'appid错误，或者good_id错误';
            return json($return);
        }
    }

    /**
     * 商品名称
     * appid,name,price,spec,pic
     */
    public function createGoods(){
        $data = input("post.",'','htmlspecialchars');
        if(!$data){
            $return['code'] = 10001;
            $return['msg'] = '请求参数不存在';
            $return['msg_test'] = '请求参数不存在';
            return json($return);
        }
        //商品的名字和价格是必须的
        if(!isset($data['name'])){
            $return['code'] = 10002;
            $return['msg'] = '商品名字不能为空';
            $return['msg_test'] = '商品名字不能为空';
            return json($return);
        }
        if(!isset($data['price']) && !isset($data['spec'])){
            $return['code'] = 10003;
            $return['msg'] = '商品价格不能为空';
            $return['msg_test'] = '商品没有价格或者没有规格';
            return json($return);
        }
        if($data['price'] <= 0){
            $return['code'] = 10004;
            $return['msg'] = '请填写正确的商品价格';
            $return['msg_test'] = '请填写正确的商品价格';
            return json($return);
        }
        if(!isset($data['appid'])){
            $return['code'] = 10005;
            $return['msg'] = '参数值缺失';
            $return['msg_test'] = '缺少appid';
            return json($return);
        }
        if(!preg_match("/^\d{8}$/",$data['appid'])){
            $return['code'] = 10006;
            $return['msg'] = 'appid格式错误';
            $return['msg_test'] = 'appid是一个8位数';
            return json($return);
        }

        $is_true = db('app') -> where(['appid' => $data['appid'],'custom_id' => $this->custom -> id]) -> select();
        if(!$is_true){
            $return['code'] = 10011;
            $return['msg'] = '当前用户没有此小程序';
            $return['msg_test'] = '当前用户没有此小程序,也就是appid不对';
            return json($return);
        }

        //如果上传图片，判断图片是否是十个
        if(isset($data['pic'])){
            $pic_number = count(explode(',',$data['pic']));
            if($pic_number > 10){
                $return['code'] = 10007;
                $return['msg'] = '一个商品最多上传10张图片';
                $return['msg_test'] = '一个商品最多上传10张图片';
                return json($return);
            }
        }
        if(isset($data['desc'])){
            if(mb_strlen($data['desc'],'utf8') > 600){
                $return['code'] = 10008;
                $return['msg'] = '商品的简介最多600字';
                $return['msg_test'] = '商品的简介最多600字';
                return json($return);
            }
        }
        $data['create_time'] = time();
        $data['custom_id'] = $this -> custom -> id;
        if(isset($data['spec'])){
            if(!$this -> isJson($data['spec'])){
                $return['code'] = 10009;
                $return['msg'] = '商品的规格是json格式';
                $return['msg_test'] = '商品的规格是json格式';
                return json($return);
            }
        }
        $good_id = db('goods') -> insertGetId($data);
        if($good_id){
            $return['code'] = 10000;
            $return['data'] = ['good_id' => $good_id];
            $return['msg'] = '添加成功';
            $return['msg_test'] = '添加成功';
            return json($return);
        }else{
            $return['code'] = 10010;
            $return['msg'] = '添加商品失败';
            $return['msg_test'] = '添加商品失败';
            return json($return);
        }
    }

    private function isJson($str){
         return is_null(json_decode($str)) == true ? true : false;
    }

    /**
     * 获取商品，进行修改
     * appid,good_id
     */
    public function getUserGoodById(){
        $data = input('post.','','htmlspecialchars');
        if(!isset($data['appid']) || !isset($data['good_id'])){
            $return['code'] = 10001;
            $return['msg'] = '参数值缺失';
            $return['msg_test'] = '参数值缺失';
            return json($return);
        }
        if(!preg_match("/^\d{8}$/",$data['appid'])){
            $return['code'] = 10002;
            $return['msg'] = 'appid格式错误';
            $return['msg_test'] = 'appid是一个8位数';
            return json($return);
        }
        $info = db('goods') -> where(['appid' => $data['appid'],'id' => $data['good_id']*1,'custom_id' => $this->custom->id]) -> find();
        if($info){
            $return['code'] = 10000;
            $return['data'] = $info;
            $return['msg'] = '';
            $return['msg_test'] = '查询成功';
            return json($return);
        }else{
            $return['code'] = 10003;
            $return['msg'] = '查询数据失败';
            $return['msg_test'] = 'appid不对或good_id不对';
            return json($return);
        }
    }

    /**
     * 修改商品
     */
    public function updateUserGood(){
        $data = input('post.','','htmlspecialchars');
        if(!isset($data['appid']) || !isset($data['good_id'])){
            $return['code'] = 10001;
            $return['msg'] = '参数值缺失';
            $return['msg_test'] = '参数值缺失';
            return json($return);
        }
        if(!preg_match("/^\d{8}$/",$data['appid'])){
            $return['code'] = 10002;
            $return['msg'] = 'appid格式错误';
            $return['msg_test'] = 'appid是一个8位数';
            return json($return);
        }
        //商品的名字和价格是必须的
        if(!isset($data['name'])){
            $return['code'] = 10003;
            $return['msg'] = '商品名字不能为空';
            $return['msg_test'] = '商品名字不能为空';
            return json($return);
        }
        if(!isset($data['price']) && !isset($data['spec'])){
            $return['code'] = 10004;
            $return['msg'] = '商品价格不能为空';
            $return['msg_test'] = '商品没有价格或者没有规格';
            return json($return);
        }

        if($data['price'] <= 0){
            $return['code'] = 10005;
            $return['msg'] = '请填写正确的商品价格';
            $return['msg_test'] = '请填写正确的商品价格';
            return json($return);
        }

        //如果上传图片，判断图片是否是十个
        if(isset($data['pic'])){
            $pic_number = count(explode(',',$data['pic']));
            if($pic_number > 10){
                $return['code'] = 10006;
                $return['msg'] = '一个商品最多上传10张图片';
                $return['msg_test'] = '一个商品最多上传10张图片';
                return json($return);
            }
        }
        if(isset($data['desc'])){
            if(mb_strlen($data['desc'],'utf8') > 600){
                $return['code'] = 10007;
                $return['msg'] = '商品的简介最多600字';
                $return['msg_test'] = '商品的简介最多600字';
                return json($return);
            }
        }
        if(isset($data['spec'])){
            if(!$this -> isJson($data['spec'])){
                $return['code'] = 10008;
                $return['msg'] = '商品的规格是json格式';
                $return['msg_test'] = '商品的规格是json格式';
                return json($return);
            }
        }
        $good_id = $data['good_id'];
        unset($data['good_id']);
        $good_id = db('goods') -> where(['id' => $good_id,'appid' => $data['appid'],'custom_id' => $this->custom->id])  -> update($data);
        if($good_id){
            $return['code'] = 10000;
            $return['msg'] = '修改成功';
            $return['msg_test'] = '修改成功';
            return json($return);
        }else{
            $return['code'] = 10009;
            $return['msg'] = '修改商品失败';
            $return['msg_test'] = '修改商品失败,appid或good_id不正确';
            return json($return);
        }

    }

    /**
     * 获取商品里面的分类信息和每个分类所对应的商品数量
     * appid,page,number
     */
    public function getCateList(){
        $custom_id = $this -> custom -> id;
        $data = input("post.",'','htmlspecialchars');
        if(!isset($data['appid'])){
            $return['code'] = 10001;
            $return['msg'] = '缺少参数值';
            $return['msg_test'] = '当前小程序的appid没有';
            return json($return);
        }
        if(!preg_match("/^\d{8}$/",$data['appid'])){
            $return['code'] = 10002;
            $return['msg'] = 'appid格式错误';
            $return['msg_test'] = 'appid是一个8位数';
            return json($return);
        }
        $is_true = db('app') -> where(['appid' => $data['appid'],'custom_id' => $custom_id]) -> select();
        if(!$is_true){
            $return['code'] = 10003;
            $return['msg'] = '当前用户没有此小程序';
            $return['msg_test'] = '当前用户没有此小程序,也就是appid不对';
            return json($return);
        }
        $page = isset($data['page']) ? $data['page'] : 1;
        $limit = isset($data['number']) ? $data['number'] : 15;
        $number = db('goods_cate') -> where(['appid' => $data['appid'],'custom_id' => $custom_id]) -> count();
        $info = db("goods_cate")
            -> alias('a')
            -> field('a.id,a.code,a.name,count(b.id) as cate_number')
            -> join("__GOODS__ b",'FIND_IN_SET(a.id,b.cid)','LEFT')
            -> where(['a.appid' => $data['appid'],'a.custom_id' => $custom_id])
            -> page($page,$limit)
            -> order('a.code desc')
            -> group('a.id')
            -> select();
        $return['code'] = 10000;
        $return['data'] = ['number' => $number,'info' => $info];
        $return['msg'] = '';
        $return['msg_test'] = '成功了';
        return json($return);

    }

    public function delCateById(){
        $data = input('post.','','htmlspecialchars');
        if(!$data){
            $return['code'] = 10001;
            $return['msg'] = '缺少参数值';
            $return['msg_test'] = '删除数据，要通过分类id删除';
            return json($return);
        }
        $cate_id = $data['cate_id'];
        $is_true = db('goods_cate') -> where(['id' => $cate_id,'custom_id' => $this->custom->id]) -> select();
        if(!$is_true){
            $return['code'] = 10002;
            $return['msg'] = '当前分类不存在';
            $return['msg_test'] = '分类表中没有这个id的分类，也就传递错了';
            return json($return);
        }
        $res = db('goods_cate') -> delete($cate_id);
        if($res){
            $return['code'] = 10000;
            $return['msg'] = '删除成功';
            $return['msg_test'] = '删除成功';
            return json($return);
        }else{
            $return['code'] = 10003;
            $return['msg'] = '删除失败';
            $return['msg_test'] = '操作数据库删除失败';
            return json($return);
        }
    }

    /**
     * 修改分类的名字
     * cate_id,name
     */
    public function updateCateName(){
        $data = input("post.",'','htmlspecialchars');
        if(!$data){
            $return['code'] = 10001;
            $return['msg'] = '无请求参数';
            $return['msg_test'] = '没有传递参数';
            return json($return);
        }
        if(!isset($data['cate_id']) || !isset($data['name'])){
            $return['code'] = 10002;
            $return['msg'] = '参数值缺失';
            $return['msg_test'] = '参数值缺失';
            return json($return);
        }
        $info['id'] = $data['cate_id'];
        $info['name'] = $data['name'];
        $res = model('goods_cate') -> where(['id' => $data['cate_id'],'custom_id' => $this->custom->id]) -> update($info);
        if($res){
            $return['code'] = 10000;
            $return['msg'] = '修改成功';
            $return['msg_test'] = '修改成功';
            return json($return);
        }else{
            $return['code'] = 10003;
            $return['msg'] = '修改失败,请稍后重试';
            $return['msg_test'] = '更新数据库失败';
            return json($return);
        }
    }

    /**
     * 增加分类信息
     * appid name
     */
    public function createCate(){
        $data = input('post.','','htmlspecialchars');
        $custom_id = $this -> custom -> id;
        if(!$data){
            $return['code'] = 10001;
            $return['msg'] = '无请求参数';
            $return['msg_test'] = '没有传递参数';
            return json($return);
        }
        if(!isset($data['appid']) || !isset($data['name'])){
            $return['code'] = 10002;
            $return['msg'] = '缺少参数值';
            $return['msg_test'] = 'appid不存在或者分类名字不存在';
            return json($return);
        }
        if(!preg_match("/^\d{8}$/",$data['appid'])){
            $return['code'] = 10003;
            $return['msg'] = 'appid格式错误';
            $return['msg_test'] = 'appid是一个8位数';
            return json($return);
        }
        $is_true = db('app') -> where(['appid' => $data['appid'],'custom_id' => $custom_id]) -> select();
        if(!$is_true){
            $return['code'] = 10004;
            $return['msg'] = '当前用户没有此小程序';
            $return['msg_test'] = '当前用户没有此小程序,也就是appid不对';
            return json($return);
        }
        $code = db('goods_cate') -> where(['appid' => $data['appid'],'custom_id' => $custom_id]) -> max('code');
        $data['code'] = $code + 1;
        $data['custom_id'] = $custom_id;
        $cate_id = db('goods_cate') -> insertGetId($data);
        if($cate_id){
            $return['code'] = 10000;
            $return['data'] = ['cate_id' => $cate_id];
            $return['msg'] = '添加成功';
            $return['msg_test'] = '添加成功';
            return json($return);
        }else{
            $return['code'] = 10005;
            $return['msg'] = '添加分类信息失败';
            $return['msg_test'] = '添加分类信息失败';
            return json($return);
        }


    }




}