<?php
/**
 * Created by PhpStorm.
 * User: 李佳飞
 * Date: 2017/11/1 0001
 * Time: 13:45
 * 平台后台管理员的充值和购买小程序的异步通知
 */
namespace app\custom\controller;
class NotifyAdmin{
    /**
     * 后台的用户购买小程序或充值
     */
    public function recharge(){
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
        $sign_info = $this->signRecharge($postObj);
        if(!$sign_info){
            file_put_contents('wxpay.log',PHP_EOL.json_encode($postStr)." 签名验证失败".PHP_EOL,FILE_APPEND);
            return;//签名失败
        }
        $data = $this->xml2arr($postStr);

        $attach = $data['attach'];//这个是文件的名字
        $value = file_cache($attach);
        if(!$value){
            file_put_contents('wxpay.log',PHP_EOL.$attach." 文件里面为空".PHP_EOL,FILE_APPEND);
        }
        $is_true = db('recharge') -> field('id') ->  where(['order_sn' => $value['order_sn']]) -> find();
        if($is_true){
            return;//订单不是待处理状态，已确认收款
        }
        $rechargeData['custom_id'] = $value['custom_id'];
        $rechargeData['money'] = $data['total_fee']/100;
        $rechargeData['order_sn'] = $value['order_sn'];
        $rechargeData['create_time'] = $value['create_time'];
        $rechargeData['pay_time'] = time();
        $rechargeData['type'] = $value['type'];
        $rechargeData['state'] = 1;
        $model = db();
        $model -> startTrans();
        try{
            $model -> table("xg_recharge") -> insertGetId($rechargeData);
            $model -> table('xg_custom') -> where(['id' => $rechargeData['custom_id']]) -> setInc('wallet',$data['total_fee']/100);
            $model -> commit();
            file_cache($attach,'del');
            die('SUCCESS');
        }catch (\think\Exception $e){
            $model -> rollback();
            file_put_contents('wxpay.log',PHP_EOL.$attach." 保存数据库时出错".PHP_EOL,FILE_APPEND);
            die('FAIL');
        }

    }

    /**
     * 后台的用户购买小程序或充值
     */
    public function wxBuyApp(){
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
        $sign_info = $this->signRecharge($postObj);
        if(!$sign_info){
            file_put_contents('wxpay.log',PHP_EOL.json_encode($postStr)." 签名验证失败".PHP_EOL,FILE_APPEND);
            return;//签名失败
        }
        $data = $this->xml2arr($postStr);

        $attach = $data['attach'];//这个是文件的名字
        $value = file_cache($attach);
        if(!$value){
            file_put_contents('wxpay.log',PHP_EOL.$attach." 文件里面为空".PHP_EOL,FILE_APPEND);
        }
        $is_true = db('buy_app_log') -> field('id') ->  where(['order_sn' => $value['order_sn']]) -> find();
        if($is_true){
            return;//订单不是待处理状态，已确认收款
        }


        $buyData['custom_id'] = $value['custom_id'];
        $buyData['appid'] = $value['appid'];
        $buyData['money'] = $data['total_fee']/100;
        $buyData['order_sn'] = $value['order_sn'];
        $buyData['create_time'] = $value['create_time'];
        $buyData['pay_time'] = time();
        $buyData['type'] = $value['type'];
        $buyData['state'] = 1;
        $buyData['year_num'] = $value['year_num'];
        $model = db();
        $model -> startTrans();
        try{
            $res = $model -> table("xg_buy_app_log") -> insertGetId($buyData);
            if($res){
                $info['update_time'] = time();
                //判断当前购买的时间是否在过期
                $app_info = db('app') -> field("use_time,fee") ->  where(['appid' => $value['appid']]) -> find();
                $year_time = strtotime($value['year_num'].'year');
                if($app_info['use_time'] > time()){
                    //还没过，应该在原来的基础上添加
                    $info['use_time'] = $app_info['use_time'] + $year_time - time();
                }else{
                    //过期了，在现在的时间上添加
                    $info['use_time'] = $year_time;
                }
                $info['fee'] = $app_info['fee'] + $data['total_fee']/100;
                db('app') -> where(['appid' => $value['appid']]) -> update($info);
                db('custom') -> where(['id' => $value['custom_id']]) -> setInc('expense',$data['total_fee']/100);
                $model -> commit();
                file_cache($attach,'del');
                die('SUCCESS');
            }else{

            }
        }catch (\think\Exception $e){
            $model -> rollback();
            file_put_contents('wxpay.log',PHP_EOL.$attach." 保存数据库时出错".PHP_EOL,FILE_APPEND);
            die('FAIL');
        }

    }


    //    后台用户升级app数量
    public function wxBuyAppNum(){
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
        $sign_info = $this->signRecharge($postObj);
        if(!$sign_info){
            file_put_contents('numpay.log',PHP_EOL.json_encode($postStr)." 签名验证失败".PHP_EOL,FILE_APPEND);
            return;//签名失败
        }
        $data = $this->xml2arr($postStr);

        $attach = $data['attach'];//这个是文件的名字
        $value = file_cache($attach);
        if(!$value){
            file_put_contents('numpay.log',PHP_EOL.$attach." 文件里面为空".PHP_EOL,FILE_APPEND);
        }
        $is_true = db('buy_app_num_log') -> field('id') ->  where(['order_sn' => $value['order_sn']]) -> find();
        if($is_true){
            return;//订单不是待处理状态，已确认收款
        }
        $buyData['custom_id'] = $value['custom_id'];
        $buyData['money'] = $data['total_fee']/100;
        $buyData['order_sn'] = $value['order_sn'];
        $buyData['create_time'] = $value['create_time'];
        $buyData['app_num'] = $value['app_num'];
        $buyData['pay_time'] = time();
        $buyData['state'] = 1;
        $model = db();
        $model -> startTrans();
        try{
            $res = $model -> table("xg_buy_app_num_log") -> insertGetId($buyData);
            if($res){
                db('custom') -> where(['id' => $value['custom_id']]) -> setInc('max_app_num',$data['app_num']);
                $model -> commit();
                file_cache($attach,'del');
                die('SUCCESS');
            }else{

            }
        }catch (\think\Exception $e){
            $model -> rollback();
            file_put_contents('numpay.log',PHP_EOL.$attach." 保存数据库时出错".PHP_EOL,FILE_APPEND);
            die('FAIL');
        }

    }

    /**
     *	xml转为数组
     *	@param string $xml 原始的xml字符串
     */
    public function xml2arr($xml){
        $xml = new \SimpleXMLElement($xml);
        if(!is_object($xml)){
            $this->errmsg = "xml数据接收错误";
            return false;
        }
        $arr = array();
        foreach ($xml as $key => $value) {
            $arr[strtolower($key)] = strval($value);
        }
        return $arr;
    }

    private function signRecharge($postObj){
        $sign = trim($postObj->sign);
        $appid = trim($postObj->appid);
        $attach = trim($postObj->attach);
        $bank_type = trim($postObj->bank_type);
        $cash_fee = trim($postObj->cash_fee);
        $fee_type = trim($postObj->fee_type);
        $is_subscribe = trim($postObj->is_subscribe);
        $mch_id = trim($postObj->mch_id);
        $nonce_str = trim($postObj->nonce_str);
        $openid = trim($postObj->openid);
        $out_trade_no = trim($postObj->out_trade_no);
        $result_code = trim($postObj->result_code);
        $return_code = trim($postObj->return_code);
        $time_end = trim($postObj->time_end);
        $total_fee = trim($postObj->total_fee);
        $trade_type = trim($postObj->trade_type);
        $transaction_id = trim($postObj->transaction_id);
        $str1 = 'appid='.$appid.'&attach='.$attach.'&bank_type='.$bank_type.'&cash_fee='.$cash_fee.'&fee_type='.$fee_type.'&is_subscribe='.$is_subscribe.'&mch_id='.$mch_id.'&nonce_str='.$nonce_str.'&openid='.$openid.'&out_trade_no='.$out_trade_no.'&result_code='.$result_code.'&return_code='.$return_code.'&time_end='.$time_end.'&total_fee='.$total_fee.'&trade_type='.$trade_type.'&transaction_id='.$transaction_id;
        $auth_info = db('system_pay')->field("mch_key") -> where('appid',$appid)->find();
        $str2 = $str1.'&key='.$auth_info['mch_key'];
        $new_sign = strtoupper(MD5($str2));
        if($new_sign == $sign){return true;}else{return false;}
    }


}