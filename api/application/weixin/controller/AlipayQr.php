<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/27 0027
 * Time: 09:12
 */
namespace app\weixin\controller;

use think\Controller;

class AlipayQr extends Controller{
    public function getAlipayQr($data){
        if(!isset($data) || !$data){
            return -1;
        }
        import('f2fpay.model.builder.AlipayTradePrecreateContentBuilder',EXTEND_PATH,'.class.php');
        import('f2fpay.service.AlipayTradeService',EXTEND_PATH,'.class.php');
        import('f2fpay.aop.request.AlipayTradePrecreateRequest',EXTEND_PATH,'.class.php');
        import('f2fpay.aop.AopClient',EXTEND_PATH,'.class.php');
        import('f2fpay.aop.SignData',EXTEND_PATH,'.class.php');
        import('f2fpay.model.result.AlipayF2FPrecreateResult',EXTEND_PATH,'.php');
        $config = array (
            //支付宝公钥
            'alipay_public_key' => "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAqOkIXHphgimE8S82vCLtF1xctwM7UuUOK4tiqaErjIvyQ95c3DHZihoOw+HMQ2gHKsRS0gavbzpusHMMgdRnY5BhilbddoEkVSMKy8/pYWTowtmoZT6YozD+FdN3aEQAkb4UTc35zYD85JqHqpSDX8RK4rZ0vMoVLB5Cignf46gEil9J5a0vFr3uXGd8y1UnVznC8C4QlNaATbwKDxg8EpPI88KpnFCxGyT828PtUgTIbpRIuJQ0Taaxznkdlip+OOmn3TpSAyTr+J+SconOiBc+IlcJTcCpKC9BOjXuHUowCKaU8K8Pai6rOU6KUnuUOhoMUfjPyl8l+Wwh2yTG7QIDAQAB",

            //商户私钥
            'merchant_private_key' => "MIIEpAIBAAKCAQEA993hm2zBOEdXdyGEty+FLxTHSpTR5cm9Pf7QLB7QlL+m3mLVieSudR5NplnKUXEttoTn+F9ZTDe1BaXJnUr6+S6DzLpaDpyEHC5OBIuBbD58G9NURFxStgP8FK8BHl3v738YwDBGXixRa2yGCD3MFybuf4X/K2fHxsYu8F5fgpXzDMRzHNK2jtwiD+buqG9H4mhOWckHPb5S2EKqrbWUqGtDfCFC0yN996g4h2O+ou6GVGk43LJTatIaBYQG7E0KZeHXCwQ86pugAMBHGrN8g6dplIOcNu9xJVxme3sHI5TFD3UVl9Z4an2mybOEWZFZiyPOQDAi1AIAwMQLxR1usQIDAQABAoIBAQCuHbI+sR3l/8+EQwpseDgxg4IfdP0hUx7ZrubjJ15UL3Dz5S+l5vtaEEhxo7+IiF2ZSjF9etVKwhMqfXRsRuCYLEGvjfR3MSFofmqVrL3koNwj28blIomDjLcGfIznnQtQDNMBJqg3vSAQuzJIFckJNnLxJ9rAze07R7pvZYvoOTWIwxq0PGTT1+QMLk8ZgaBRnAR/3Fhvay1B/ghPv8HAILlABrv8BJ5/1bUGq9bkp8UWj3ctRXJc2/42rSUQhqjjJtOsxBJpKlbU2AFgU3JkGCRLqglPsh0pq6Gh0G3qO0CDyLb9caXbPfrvJMCVnYRGwioBj5bVgy4e6LQIt/I1AoGBAP/3NcIaYv+8vGhIteFZIcV60M7OCD0XLu5VMO4iN5FMVZszAEgqT7PdEQuaR0MMJne3W9hYMzfmF4O2SFpDmNuEaReSDvBmUOVKl+t+lClXYblPDuQYogBQO0LA2lVSxs8TJDeGGLxcxgpFxe9ihcFE0Sr6aHrcIsWILJ/Lew4vAoGBAPfmZKZNnfqr83Ofkyt2Dp+4NxRyV2JTyEIzhW6BsZ6fNKl3RwxFkI03fRL6SRDQe0xJLpku2h3edH5Zn8fJQJ4QNhkKqvTRIyGcs5NPW3XaL+Y+tudAsutow0cv+cvAuj0DRtN1YW4b9vsMHMhzLV8TShYv2FM1+AA83PWOhfkfAoGAHLrmsc68ZfANRbdDkvOqMrxCS7QcgJ7liaLORyxYCFsFENJ8qZz2LT4W97JtZT0r5CwUhwf/V7rf0MzY+ii0M499LEQcoSca1WG2A5zFjI5eTapuBXQuWtKmlCuJViJgZkXDvueyRxIyuFx0hxYL5VGQGL7ak0+6J2nNeHIicckCgYEAmn4YHdhjaxR8fYNmiYBirsF0eiakNOA3/qHzNyJWmp9nh3GRcqFr68Y4CXq3zGXRYYJ+KvMa9eBsQ04BmNXgkmFSBZszXa10sn7hHx4mxrS6g0h0XnxgxPseMCBDEetDZcDEBAa0OJXu/xfWXEoDbaws0NTygTEyJJvJrLMs5UsCgYBP40QyHao/OQwgXjWKtRP4y1gVzR2jnP/lbDQuQYOKTtI+ndDRxX4o77zm1ijQdIKyGCA1V+oXxbtlMqwq+tRH1h1wpMAKdnDlXvdcKcZE/5V0tygQMhPK00VQNrFjUdzfDirq7uOisJaPPmH01otmGLfJr5ZbO9lbqxIzVkduTg==",

            //编码格式
            'charset' => "UTF-8",

            //支付宝网关
            'gatewayUrl' => "https://openapi.alipaydev.com/gateway.do",

            //应用ID
            'app_id' => "2016080200146959",

            //异步通知地址,只有扫码支付预下单可用
            'notify_url' => $data['notify_url'],

            //最大查询重试次数
            'MaxQueryRetry' => "10",

            //查询间隔
            'QueryDuration' => "3"
        );
        $outTradeNo = time();
        // (必填) 订单标题，粗略描述用户的支付目的。如“xxx品牌xxx门店当面付扫码消费”
        $subject = $data['title'];

        // (必填) 订单总金额，单位为元，不能超过1亿元
        // 如果同时传入了【打折金额】,【不可打折金额】,【订单总金额】三者,则必须满足如下条件:【订单总金额】=【打折金额】+【不可打折金额】
        $totalAmount = $data['money'];



        // 订单描述，可以对交易或商品进行一个详细地描述，比如填写"购买商品2件共15.00元"
        $body = "购买商品2件共15.00元";

        // (可选) 商户门店编号，通过门店号和商家后台可以配置精准到门店的折扣信息，详询支付宝技术支持
        // $storeId = 123456;

        // 支付宝的店铺编号
        //$alipayStoreId= "test_alipay_store_id";

        // 支付超时，线下扫码交易定义为5分钟
        $timeExpress = "10m";

        // 商品明细列表，需填写购买商品详细信息，
        $goodsDetailList = array();
        //把自定义的参数是post  把appid或key是通过url链接，发过去时要签名，返回来的二维码地址也要签名验证，异步通知签名验证


        // 创建请求builder，设置请求参数
        $qrPayRequestBuilder = new \AlipayTradePrecreateContentBuilder();
        $qrPayRequestBuilder->setOutTradeNo($outTradeNo);
        $qrPayRequestBuilder->setTotalAmount($totalAmount);
        $qrPayRequestBuilder->setTimeExpress($timeExpress);
        $qrPayRequestBuilder->setSubject($subject);
        $qrPayRequestBuilder->setBody($body);
        $qrPayRequestBuilder->setGoodsDetailList($goodsDetailList);
//        $qrPayRequestBuilder->setAlipayStoreId($alipayStoreId);
        // 调用qrPay方法获取当面付应答
        $qrPay = new \AlipayTradeService($config);
        $qrPayResult = $qrPay->qrPay($qrPayRequestBuilder);
// 调用qrPay方法获取当面付应答
        //dump($qrPayResult);

        //	根据状态值进行业务处理
        switch ($qrPayResult->getTradeStatus()) {
            case "SUCCESS":
                $response = $qrPayResult->getResponse();
                return $response->qr_code;
                break;
            case "FAILED":
                return -2;
                break;
            case "UNKNOWN":
                return -3;
                break;
            default:
                return -4;
                break;
        }
    }
}