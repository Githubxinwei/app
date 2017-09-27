<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/9/1 0001
 * Time: 16:16  个人中心的上面两个 理财专区和股权专区的控制器
 */
namespace Home\Controller;
use Think\Controller;

class MoneyController extends Controller{


    // /*用户来，判断session存不存在，*/
    function __construct(){
        parent::__construct();
        $this->user_id = session('xigua_user_id');
        $res = $this->is_weixin();
        if ($res == 1){
            //$info = M('zhuce')->where("user_id = '$this->user_id'")->find();
            if (!$this->user_id){
                redirect(U('/Login/User/register',array('id'=>$_GET['id'],'agent_id'=>$_GET['agent_id'])));
            }else{
                $user_info=M('users')->where("user_id = '$this->user_id'")->find();
                if(!$user_info){
                    $redirect_uri='http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
                    redirect('http://'.$_SERVER['HTTP_HOST'].U('/wxapi/oauth/index/')."?surl=".$redirect_uri);exit;
                }
            }

        }else {
            if(!$this->user_id){
                $now_url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
                //dump($_GET);
                //dump($now_url);
                $this -> assign('now_url',$now_url);
                $this->display('Login_error');exit;
            }else{
                $this -> user_id = session('xigua_user_id');
            }
        }

    }

    //判断打开方式
    function is_weixin(){
        if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
            return 1;
        }
        return 2;
    }


    public function manage(){
        $this -> display();
    }

    /**
     * type 1 已购买  还没有过期的 2 已领取 3 已过期
     */
    public function managebb(){
        $page = $_GET['p'];
        $page = htmlspecialchars($page,ENT_QUOTES);
        $page = $page * 1;
        $start = ($page -1) * 10;
        $type = I('post.type');
        if($type == 1){
            $data = M('user_manage')
                -> field('*')
                -> where("user_id = {$this->user_id} and state = 1 and type = 0")
                -> limit($start,10)
                -> order('last_time asc')
                -> select();
            foreach ($data as $k => $v){
                $time = $v['last_time'] + 3600 * $v['time'];
                $cha = $time - time();
                if($cha > 0){
                    $yourhour = (int)(($cha%(3600*24))/(3600));
                    $yourmin = (int)($cha%(3600)/60);
                    $data[$k]['atime'] = $yourhour . '小时' . $yourmin . '分';
                }else{
                    $data[$k]['atime'] = '可领取';
                }
            }
            $this -> assign('data',$data);
            $this->display('Money_manage1');
        }else if($type == 2){
            $data = M('manage_log')
                -> alias("a")
                -> field("a.*,b.name")
                -> join("left join __USER_MANAGE__ as b on a.manage_id = b.id")
                -> where("a.user_id = {$this->user_id}")
                -> limit($start,10)
                -> order('a.create_time desc')
                -> select();
            $this -> assign('data',$data);
            $this->display('Money_manage2');
        }else if($type == 3){
            $data = M('user_manage')
                -> alias('a')
                -> field('a.*,sum(b.money) as zong')
                -> join("left join __MANAGE_LOG__ as b on a.id = b.manage_id")
                -> where("a.user_id = {$this->user_id} and a.state = 1 and a.type = 1")
                -> group('a.id')
                -> limit($start,10)
                -> order('last_time desc')
                -> select();
//            foreach ($data as $k => $v){
//                //计算总共得了多少钱
//                $money = $v['day'] * 24 / $v['time'] * $v['fee'];
//                $data[$k]['zong'] = $money;
//            }
            $this -> assign('data',$data);
            $this->display('Money_manage3');
        }

    }

    /**
     * 领取
     */
    public function lingqu(){
        $id = I("post.id");
        $id = htmlspecialchars($id,ENT_QUOTES);
        $id = $id * 1;
        if(!$id){$arr['code'] = 0;$arr['info'] = "缺少数据,请稍后重试";$this->ajaxReturn($arr);return;}
        $data = M('user_manage') -> find($id);
        if(!$data['state']){
            $arr['code'] = 0;$arr['info'] = "数据错误";$this->ajaxReturn($arr);return;
        }
        if($data['type'] == 1){
            $arr['code'] = 0;$arr['info'] = "数据错误";$this->ajaxReturn($arr);return;
        }
        if($data['user_id'] != $this->user_id){
            $arr['code'] = 0;$arr['info'] = "数据错误";$this->ajaxReturn($arr);return;
        }
        //判断时间是否到了
        if((time() - $data['last_time'])/3600 < $data['time']){
            $arr['code'] = 0;$arr['info'] = "领取时间还未到";$this->ajaxReturn($arr);return;
        }
        //判断是否出局  条件  设定的领取次数和已领取的次数一样的话，出局
        if($data['cishu'] >= $data['day']){
            M('user_manage') -> where("id = {$id}") -> setField('type',1);
            $arr['code'] = 0;$arr['info'] = "对不起,当前理财产品已出局";$this->ajaxReturn($arr);return;
        }
        //领取时间到了，领取返利
        $res = array(
            'user_id' => $data['user_id'],
            'money' => $data['fee'],
            'create_time' => time(),
            'manage_id' => $id
        );
        $re =  M('manage_log') -> add($res);
        if($re){
            M('user_manage') -> where("id = {$id}") -> setField('last_time',time());
            M('user_manage') -> where("id = {$id}") -> setInc('cishu',1);
            if($data['cishu'] + 1 >= $data['day']){
                M('user_manage') -> where("id = {$id}") -> setField('type',1);
            }
            $a = M('user') -> where("user_id = {$data['user_id']}") -> setInc('money',$data['fee']);
            $arr['code'] = 1;$arr['info'] = "领取成功";$this->ajaxReturn($arr);return;
        }

    }


    public function guquan(){
        $this -> display();
    }

    //请求数据
    public function guquanbb(){
        $page = $_GET['p'];
        $page = htmlspecialchars($page,ENT_QUOTES);
        $page = $page * 1;
        $start = ($page -1) * 10;
        $type = I('post.type');
        if($type == 1){
            //获取所有的股权
            $data = M('user_guquan')
                -> where("user_id = {$this->user_id} and state = 1 and flag = 0")
                -> limit($start,10)
                -> select();
            //日息
            $info = F('guquan','',DATA_ROOT);
            $this -> assign('rixi',$info['rixi']);

            //判断是否可以领取
            foreach ($data as $k => $v){
                foreach ($data as $k => $v){
                    $time = $v['last_time'] + 3600 * 24;
                    $cha = $time - time();
                    if($cha > 0){
                        $yourhour = (int)(($cha%(3600*24))/(3600));
                        $yourmin = (int)($cha%(3600)/60);
                        $data[$k]['atime'] = $yourhour . '小时' . $yourmin . '分';
                    }else{
                        $data[$k]['atime'] = '可领取';
                    }
                }
            }
            $this -> assign('data',$data);
            $this -> display('Money_guquan1');
        }else if($type == 2){
            $data = M('guquan_log')
                -> where("user_id = {$this->user_id}")
                -> limit($start,10)
                -> order('create_time desc')
                -> select();
            $this -> assign('data',$data);
            $this->display('Money_guquan2');
        }
    }


    function guquanLq(){
//        if($this->user_id != 11441){
//            $arr['code'] = 0;$arr['info'] = "测试中";$this->ajaxReturn($arr);return;
//        }
        $id = I("post.id");
        $id = htmlspecialchars($id,ENT_QUOTES);
        $id = $id * 1;
        if(!$id){$arr['code'] = 0;$arr['info'] = "缺少数据,请稍后重试";$this->ajaxReturn($arr);return;}
        $data = M('user_guquan') -> find($id);
        if(!$data['state']){
            $arr['code'] = 0;$arr['info'] = "数据错误";$this->ajaxReturn($arr);return;
        }
        if($data['flag'] != 0){
            $arr['code'] = 0;$arr['info'] = "数据错误";$this->ajaxReturn($arr);return;
        }
        if($data['user_id'] != $this->user_id){
            $arr['code'] = 0;$arr['info'] = "数据错误";$this->ajaxReturn($arr);return;
        }

        $switch = F('guquanlingqu');
        if(!$switch){
            $switch['switch'] = 0;
        }
        if($switch['switch'] == 1){
            //判断是否有公派信息
            $is_c = M('gongpai_user') ->  where(['user_id' => $data['user_id']]) -> select();
            if(!$is_c){
                $arr['code'] = 0;$arr['info'] = "公派没有点位，无法领取";$this->ajaxReturn($arr);return;

            }
        }

        //判断时间是否到了
        $info = F('guquan');
        if(!$info['rixi']){
            file_put_contents('error.txt','日息不存在' . date('Y-m-d H:i:s',time() . $info['rixi']),FILE_APPEND);
            $arr['code'] = 0;$arr['info'] = "数据错误";$this->ajaxReturn($arr);return;
        }
        //判断时间是否可以领取
        //判断时间是否到了
        if((time() - $data['last_time'])/3600 < 24){
            $arr['code'] = 0;$arr['info'] = "领取时间还未到";$this->ajaxReturn($arr);return;
        }
        //file_put_contents('D:/text3.txt',$v['type']);
        //利用用户的本金加上送的钱
        $money = $data['money'] * 2;
        $money = $money * $info['rixi'] / 100;
        $arr = array(
            'user_id' => $data['user_id'],
            'money' => $data['money'],
            'fee' => $money,
            'bili' => $info['rixi'] / 100,
            'create_time' => time(),
            'guquan_id' => $data['id']
        );
        // file_put_contents('D:/text2.txt',$v['type']);
        $res =   M('guquan_log') -> add($arr);

        if($res){
            //成功，把领取记录保存起来

            //添加成功，修改最后领取的时间
            $is_true = M('user_guquan') -> where(['id' => $data['id']]) -> setField('last_time',time());
            if($is_true){
                $count = M('guquan_log') -> where(['guquan_id' => $data['id']]) -> count();
                M('user_guquan') -> where(['id' => $data['id']]) -> setField('cishu',$count);
                if($count >= 50){
                    M('user_guquan') -> where(['id' => $data['id']]) -> setField('state',0);
                    M('user_guquan') -> where(['id' => $data['id']]) -> setField('flag',3);
                }
                //如果余额支付，则没有分红
                //  file_put_contents('D:/text1.txt',$v['type']);
                if($data['type'] == 1){
                }else{
                    //判断后台是否开启
                    flog($money,$data['user_id'],2);

                }
                $res = M('user') -> where("user_id = {$data['user_id']}") -> setInc('money',$money);
                M('user') -> where(['user_id' => $data['user_id']]) -> setField('fee',0);
                $arr['code'] = 1;$arr['info'] = "领取成功";$this->ajaxReturn($arr);return;

            }


        }
    }

    function tuihui(){
        $user_id = $this -> user_id;
        if(!$user_id){
            echo -2;exit;
        }
        //获取支付宝信息
        $alipay_nunber = I('post.alipay_number');
        $name = I('post.name');
        if(!$alipay_nunber || !$name){
            echo -4;exit;
        }

        //查询当前人的所有的生效的
        $data = M('user_guquan') -> where("state = 1 and flag = 0 and user_id = {$user_id}") -> select();
        if($data){
            $res = M('user_guquan') -> where("state = 1 and flag = 0 and user_id = {$user_id}") -> setField('flag',2);
            if($res){
                //把支付宝信息保存在alipay
                $re = M('alipay') -> where("user_id = {$this->user_id}") -> select();
                if($re){
                    $info = array(
                        'alipay_number' => $alipay_nunber,
                        'name' => $name,
                    );
                    M('alipay') -> where("user_id = {$this->user_id}") -> save($info);
                }else{
                    $info = array(
                        'alipay_number' => $alipay_nunber,
                        'name' => $name,
                        'user_id' => $user_id,
                    );
                    M('alipay') -> add($info);
                }
                echo 0;exit;
            }
        }
        echo -2;

    }

}