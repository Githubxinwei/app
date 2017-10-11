<?php
namespace app\custom\controller;

/*客户个人信息相关操作*/
class Own extends Action{
	//返回拥有的小程序列表
    public function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub
        $this -> data = input("post.",'','htmlspecialchars');
    }

    /**
     * 获取小程序的列表
     */
	public function getUserAppList(){
        //查询当前用户是否存在
        $info = db('app') -> where("custom_id",$this->custom->id) -> where('is_del','0') -> select();
		$arr['code'] = 10000;$arr['msg'] = '获取成功';$arr['msg_test'] = '获取成功';$arr['data'] = $info;
		return json($arr);

	}

    /**
     * 用户创建小程序
     */
	public function createUserApp(){
        $this -> data['custom_id'] = $this -> custom -> id;
        if(!isset($this -> data['type']) || !isset($this -> data['custom_id'])){
            $return['code'] = 10002;
            $return['msg_test'] = '参数值缺失';
            return json($return);
        }
        if(!get_app($this -> data['type'])){
            $return['code'] = 10003;
            $return['msg_test'] = '小程序不存在';
            return json($return);
        }

        //判断当前用户是否可以在创建小程序
        $maxappnum = db('custom') -> getFieldByid($this -> data['custom_id'],'max_app_num');
        $app_num = db('custom') -> getFieldByid($this -> data['custom_id'],'app_num');
        if($app_num >= $maxappnum){
            $return['code'] = 10006;
            $return['msg_test'] = '当前用户小程序数据已满';
            return json($return);
        }
        //获取当前用户随机的字符串
        $flag = true;
        while($flag){
            $appid = $this -> getNumber();
            $res = db('app') -> where("appid = :appid",['appid' => $appid]) -> select();
            if(!$res){
                $flag = false;
                $this -> data['appid'] = $appid;
            }
        }
        $app_info = get_app($this -> data['type']);
        $this -> data['name'] = $app_info['name'];
        $this -> data['pic'] = $app_info['pic'];
        $this -> data['create_time'] = time();
        $this -> data['try_time'] = time() + 60 * 60;
        $this -> data['use_time'] = time() + 60 * 60;
        $id = model('app') -> allowField(true) -> save($this -> data);
        if($id){
            //更新用户的数据表
            db('custom') -> where("id",$this -> data['custom_id']) -> setInc('app_num',1);
            $return['code'] = 10000;
            $return['data'] = ['id' => $id,'appid' => $this -> data['appid'],'try_time' => $this -> data['try_time']];
            $return['msg'] = '添加成功';
            return json($return);
        }else{
            $return['code'] = 10007;
            $return['msg'] = '添加失败';
            return json($return);
        }
    }

    private function getNumber(){
        return mt_rand(10000000,99999999);
    }

    /**
     * 小程序购买
     */
    public function buyUserApp(){
        $this -> data['custom_id'] = $this -> custom -> id;
        if(!isset($this -> data['appid'])){
            $return['code'] = 10004;
            $return['msg_test'] = '缺少app的唯一标识,获取列表的时候传递过去的appid';
            return json($return);
        }
        if(!preg_match("/^\d{8}$/",$this -> data['appid'])){
            $return['code'] = 10005;
            $return['msg_test'] = 'appid是一个8位数';
            return json($return);
        }
        //判断当前用户的小程序是否有这个appid
        $is_true = db('app') -> where(['appid' => $this -> data['appid']]) -> find();
        if(!$is_true){
            $return['code'] = 10006;
            $return['msg_test'] = '这个客户没有创建这个小程序';
            return json($return);
        }
        if($is_true['custom_id'] != $this -> custom->id){
            $return['code'] = 10011;
            $return['msg_test'] = '这个客户没有创建这个小程序';
            return json($return);
        }
        $app_type = $is_true[0]['type'];
        $app_info = get_app($app_type);
        if(!$app_info){
            $return['code'] = 10007;
            $return['msg_test'] = '小程序类型不存在';
            return json($return);
        }
        $fee = $app_info['fee'];
        if(!$fee || $fee <= 0){
            $return['code'] = 10008;
            $return['msg'] = '小程序价格错误';
            return json($return);
        }
        //判断用户是否有钱
        $userMoney = db('custom') -> getFieldByid($this -> data['custom_id'],'wallet');
        if($fee > $userMoney){
            $return['code'] = 10009;
            $return['msg'] = '用户余额不足';
            return json($return);
        }

        $res = db('custom') -> where(['id' => $this -> data['custom_id']]) -> setDec('wallet',$fee);
        if($res){
            $info['update_time'] = time();

            db('app') -> where(['appid' => $this -> data['appid']]) -> update($info);
            //判断当前购买的时间是否在过期
            $use_time = db('app') -> where(['appid' => $this -> data['appid']]) -> value('use_time');
            $year_time = strtotime('1 year');
            if($use_time > time()){
                //还没过，应该在原来的基础上添加
                db('app') -> where(['appid' => $this -> data['appid']*1]) -> setInc('use_time',$year_time - time());
            }else{
                //过期了，在现在的时间上添加
                db('app') -> where(['appid' => $this -> data['appid']]) -> setField('use_time',$year_time);
            }

            db('app') -> where(['appid' => $this -> data['appid']]) -> setInc('fee',$fee);

            db('custom') -> where(['id' => $this -> data['custom_id']]) -> setInc('expense',$fee);
            $use_time = db('app') -> where(['appid' => $this -> data['appid']]) -> value('use_time');
            $return['code'] = 10000;
            $return['data'] = ['use_time' => $use_time];
            $return['msg'] = '购买成功';
            return json($return);
        }else{
            $return['code'] = 10010;
            $return['msg'] = '更新数据失败';
            return json($return);
        }

    }


    /**
     * 删除小程序
     */
    public function delUserApp(){
        if(!isset($this -> data['appid'])){
            $return['code'] = 10001;
            $return['msg_test'] = '缺少app的唯一标识,获取列表的时候传递过去的appid';
            return json($return);
        }
        if(!preg_match("/^\d{8}$/",$this -> data['appid'])){
            $return['code'] = 10002;
            $return['msg_test'] = 'appid是一个8位数';
            return json($return);
        }
        $res = db('app') -> where(['appid' => $this -> data['appid']]) -> setField('is_del',1);
        if($res){
            $return['code'] = 10000;
            $return['msg'] = '删除成功';
            $return['msg_test'] = '删除成功';
            return json($return);
        }else{
            $return['code'] = 10003;
            $return['msg'] = '删除失败';
            $return['msg_test'] = 'appid可能传递错了或者这个小程序不是当前用户的';
            return json($return);
        }

    }


    /**
     * 获取小程序的基本信息
     * appid
     */
    public function getAppInfo(){
        if(!isset($this -> data['appid'])){
            $return['code'] = 10001;
            $return['msg_test'] = '缺少app的唯一标识,获取列表的时候传递过去的appid';
            return json($return);
        }
        if(!preg_match("/^\d{8}$/",$this -> data['appid'])){
            $return['code'] = 10002;
            $return['msg_test'] = 'appid是一个8位数';
            return json($return);
        }
        //判断当前用户的小程序是否有这个appid
        $info = db('app') -> field('name,pic,desc,tel,site_url,address,is_publish,custom_id') ->  where(['appid' => $this -> data['appid']]) -> find();
        if($info['custom_id'] != $this->custom->id){
            $return['code'] = 10004;
            $return['msg_test'] = '这个app不是这个用户的';
            return json($return);
        }
        if($info){
            $return['code'] = 10000;
            $return['data'] = $info;
            $return['msg'] = '';
            $return['msg_test'] = '成功';
            return json($return);
        }else{
            $return['code'] = 10003;
            $return['msg'] = '查询数据失败';
            $return['msg_test'] = '参数可能传递错了,或者这个小程序不是这个用户的';
            return json($return);
        }
    }


    /**
     * 设置小程序的姓名，标志，简介等信息
     * appid
     */
    public function setAppInfo(){
        if(!isset($this -> data['appid']) || !isset($this -> data['name']) || !isset($this -> data['tel'])){
            $return['code'] = 10001;
            $return['msg_test'] = '缺少appid或name或tel';
            return json($return);
        }
        if(!preg_match("/^\d{8}$/",$this -> data['appid'])){
            $return['code'] = 10002;
            $return['msg_test'] = 'appid是一个8位数';
            return json($return);
        }

        $res = model('app') -> where(['appid' => $this -> data['appid']]) -> update($this -> data);
        if($res){
            $return['code'] = 10000;
            $return['msg'] = '修改成功';
            $return['msg_test'] = '修改成功';
            return json($return);
        }else{
            $return['code'] = 10005;
            $return['msg'] = '修改失败';
            $return['msg_test'] = '参数可能传递错了,或者这个小程序不是这个用户的';
            return json($return);
        }

    }


    /**
     * 微信支付的商户号和商户key
     * appid,mchid,mkey
     */
    public function setUserWxKey(){
        if(!isset($this->data['appid']) || !isset($this->data['mchid']) || !isset($this->data['mkey'])){
            $return['code'] = 10001;
            $return['msg_test'] = '参数值缺失';
            return json($return);
        }
        if(!preg_match("/^.{10}$/",$this -> data['mchid']) || !preg_match("/^\d{8}$/",$this -> data['appid'])){
            $return['code'] = 10002;
            $return['msg_test'] = '商户号或者appid格式不正确';
            return json($return);
        }
        $info = db('auth_info') -> field('custom_id,mchid,mkey') -> where(['apps' => $this -> data['appid']]) -> find();
        if(!$info){
            $return['code'] = 10003;
            $return['msg_test'] = '该小程序的授权信息不存在';
            return json($return);
        }
        if($info['custom_id'] != $this->custom->id){
            $return['code'] = 10006;
            $return['msg_test'] = '这个授权信息不是这个用户的';
            return json($return);
        }
        if($info['mchid']|| $info['mkey']){
            $return['code'] = 10004;
            $return['msg_test'] = '商户号或秘钥已存在';
            return json($return);
        }
        $mData['mchid'] = $this->data['mchid'];
        $mData['mkey'] = $this->data['mkey'];
        $res = db('auth_info')  -> where(['apps' => $this -> data['appid']]) -> update($mData);
        if($res){
            $return['code'] = 10000;
            $return['msg_test'] = '添加成功';
            return json($return);
        }else{
            $return['code'] = 10005;
            $return['msg_test'] = 'appid或许不对';
            return json($return);
        }
    }

    /**
     * 获取商户号和商户Key
     */
    public function getWxKey(){
        if(!isset($this->data['appid'])){
            $return['code'] = 10001;
            $return['msg_test'] = '参数值缺失';
            return json($return);
        }
        if(!preg_match("/^\d{8}$/",$this -> data['appid'])){
            $return['code'] = 10002;
            $return['msg_test'] = 'appid格式不正确';
            return json($return);
        }
        $info = db('auth_info') -> field('custom_id,mchid,mkey') -> where(['apps' => $this->data['appid']]) -> find();
        if(!$info){
            $return['code'] = 10003;
            $return['msg_test'] = '授权信息不存在,也就是没设置';
            return json($return);
        }
        if($info['custom_id'] != $this->custom->id){
            $return['code'] = 10004;
            $return['msg_test'] = '授权信息不是这个人的';
            return json($return);
        }
        if(!$info['mchid'] || !$info['mkey']){
            $return['code'] = 10005;
            $return['msg_test'] = '商户信息不存在';
            return json($return);
        }
        $return['code'] = 10000;
        unset($info['custom_id']);
        $return['data'] = $info;
        $return['msg_test'] = 'ok';
        return json($return);
        
    }

    /**
     * 解除小程序的商户号和商户key
     * appid
     */
    public function clearWxKey(){
        if(!isset($this->data['appid'])){
            $return['code'] = 10001;
            $return['msg_test'] = '参数值缺失';
            return json($return);
        }
        if(!preg_match("/^\d{8}$/",$this -> data['appid'])){
            $return['code'] = 10002;
            $return['msg_test'] = 'appid格式不正确';
            return json($return);
        }
        //是否设置了授权
        $info = db('auth_info') -> field('custom_id,mchid,mkey') -> where(['apps' => $this->data['appid']]) -> find();
        if(!$info){
            $return['code'] = 10003;
            $return['msg_test'] = '该小程序的授权信息不存在';
            return json($return);
        }
        if($info['custom_id'] != $this->custom->id){
            $return['code'] = 10005;
            $return['msg_test'] = '不是这个用户的';
            return json($return);
        }
        if(!$info['mchid']||!$info['mkey']){
            $return['code'] = 10004;
            $return['msg_test'] = '商户号或秘钥不存在';
            return json($return);
        }
        $m_data['mchid'] = '';
        $m_data['mkey'] = '';
        $res = db('auth_info') -> where(['apps' => $this->data['appid']]) -> update($m_data);
        if($res){
            $return['code'] = 10000;
            $return['msg_test'] = 'OK';
            return json($return);
        }else{
            $return['code'] = 10006;
            $return['msg_test'] = '失败了';
            return json($return);
        }
    }

    /**
     * 添加通知提醒
     * appid
     */
    public function getNotifyInfo(){
        if(!isset($this->data['appid'])){
            $return['code'] = 10001;
            $return['msg_test'] = '参数值缺失';
            return json($return);
        }
        if(!preg_match("/^\d{8}$/",$this -> data['appid'])){
            $return['code'] = 10002;
            $return['msg_test'] = 'appid格式不正确';
            return json($return);
        }
        $info = db('app') -> field("notifytel,notifyemail,custom_id") -> where('appid',$this->data['appid']) -> find();
        if(!$info){
            $return['code'] = 10003;
            $return['msg_test'] = 'appid不正确';
            return json($return);
        }else{
            if($info['custom_id'] != $this->custom->id){
                $return['code'] = 10004;
                $return['msg_test'] = '当前的小程序不是这个用户的';
                return json($return);
            }
            unset($info['custom_id']);
            $return['code'] = 10000;
            $return['data'] = $info;
            $return['msg_test'] = 'ok';
            return json($return);
        }

    }

    /**
     * 设置或保存通知信息
     * appid,notifytel notifyemail
     */
    public function setNotifyInfo(){
        if(!isset($this->data['appid'])){
            $return['code'] = 10001;
            $return['msg_test'] = '参数值缺失';
            return json($return);
        }
        if(!preg_match("/^\d{8}$/",$this -> data['appid'])){
            $return['code'] = 10002;
            $return['msg_test'] = 'appid格式不正确';
            return json($return);
        }
        if(!isset($this->data['notifytel']) || !isset($this->data['notifyemail'])){
            $return['code'] = 10003;
            $return['msg_test'] = '请传入参数';
            return json($return);
        }
        //判断规则
        if(!preg_match("/^1[3|4|5|7|8][0-9]{9}$/",$this -> data['notifytel']) || !preg_match("/^[\w\.-]+@[a-zA-Z\d]+(\.[a-zA-Z]+)?\.[a-zA-Z]{1,3}$/",$this -> data['notifyemail'])){
            $return['code'] = 10004;
            $return['msg_test'] = '格式不正确';
            return json($return);
        }
        $info['notifytel'] = $this->data['notifytel'];
        $info['notifyemail'] = $this->data['notifyemail'];
        $res = model('app') -> where("appid = :appid and custom_id = :custom_id",['appid' => $this->data['appid'],'custom_id' => $this->custom->id]) -> update($info);
        if($res){
            $return['code'] = 10000;
            $return['msg_test'] = 'ok';
            return json($return);
        }else{
            $return['code'] = 10005;
            $return['msg_test'] = '添加失败';
            return json($return);
        }
    }


    /**
     * 发送手机验证码
     *
     */
    public function sendMsg(){
        if(!isset($this->data['tel'])){
            $return['code'] = 10001;
            $return['msg_test'] = '传入手机号';
            return json($return);
        }
        if(!preg_match("/^1[3|4|5|7|8][0-9]{9}$/",$this -> data['tel'])){
            $return['code'] = 10002;
            $return['msg_test'] = '格式不正确';
            return json($return);
        }
        $code = mt_rand(100000,999999);
        $param = "code:{$code}";
        $code = sendMsg('18317774594','zaefNsQrp2GJ9F3Y','TP1709201',$this->data['tel'],$param,$code);
        if($code == 0000){
            $return['code'] = 10000;
            $return['msg'] = '发送成功';
            return json($return);
        }else{
            $return['code'] = 10003;
            $return['msg_test'] = $code;
            return json($return);
        }
    }


    /**
     * 验证手机号是否正确
     * code
     */
    public function verifyMsgCode(){
        if(!isset($this->data['code'])){
            $return['code'] = 10001;
            $return['msg_test'] = '缺少参数';
            return json($return);
        }
        if(!preg_match("/^[0-9]{6}$/",$this -> data['code'])){
            $return['code'] = 10002;
            $return['msg_test'] = 'code位6位数字';
            return json($return);
        }
        $code = session('xigua_verify');
        $code = 88888;//测试
        if(!$code){
            $return['code'] = 10003;
            $return['msg_test'] = '验证码失效,请重新获取';
            return json($return);
        }
        $msg = $this->data['code'];
        if($code == $msg){
            $return['code'] = 10000;
            $return['msg_test'] = 'ok';
            return json($return);
        }else{
            $return['code'] = 10004;
            $return['msg'] = '验证码不正确';
            return json($return);
        }
    }

    public function getUserList(){
        if(!isset($this -> data['appid'])){
            $return['code'] = 10001;
            $return['msg_test'] = '缺少app的唯一标识,获取列表的时候传递过去的appid';
            return json($return);
        }
        if(!preg_match("/^\d{8}$/",$this -> data['appid'])){
            $return['code'] = 10002;
            $return['msg_test'] = 'appid是一个8位数';
            return json($return);
        }
        $app_id = db('app') -> field('id,custom_id') -> where(['appid' => $this->data['appid']]) -> find();
        if($app_id['custom_id'] != $this->custom->id){
            $return['code'] = 10003;
            $return['msg_test'] = '当前的小程序不是这个用户的';
            return json($return);
        }
        $num = isset($this->data['limit_num']) ? $this->data['limit_num'] : 10;
        $page = isset($this->data['page']) ? $this->data['page'] : 1;
        $where = array();
        if(isset($this->data['nickname'])){
            if($this->data['nickname']){
                $where['nickName'] = $this->data['nickname'];
            }
        }
        $info = db('user')
            -> field("avatarUrl,nickName,gender,country,province,create_time")
            -> where("FIND_IN_SET({$app_id['id']},apps)")
            -> where($where)
            -> page($page,$num)
            -> select();
        $return['code'] = 10000;
        $return['data'] = $info;
        $return['msg_test'] = 'ok';
        return json($return);
    }


    public function getAppQr(){
        if(!isset($this -> data['appid'])){
            $return['code'] = 10001;
            $return['msg_test'] = '缺少app的唯一标识,获取列表的时候传递过去的appid';
            return json($return);
        }
        if(!preg_match("/^\d{8}$/",$this -> data['appid'])){
            $return['code'] = 10002;
            $return['msg_test'] = 'appid是一个8位数';
            return json($return);
        }
        //判断是否绑定
        $res = db('auth_info') -> where(['apps' => $this->data['appid']]) -> select();
        if($res){
            $weapp = new \app\weixin\controller\Common($this->data['appid']);
            $img = $weapp ->get_qrcode();
            $return['code'] = 10000;
            $return['data'] = $img;
            $return['msg_test'] = 'ok';
            return json($return);
        }else{
            $return['code'] = 10003;
            $return['msg_test'] = '未绑定';
            return json($return);
        }

    }




}


 ?>