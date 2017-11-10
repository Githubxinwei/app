<?php
namespace app\express\controller;
use think\Controller;

class Search extends Controller{
    /**
     * Json方式 查询订单物流轨迹      查询类:即时查询
     */
    function getOrderTracesByJson(){
        $requestData= "{'OrderCode':'','ShipperCode':'YTO','LogisticCode':'12345678'}";
    
        $datas = array(
            'EBusinessID' => '1310235',
            'RequestType' => '1002',
            'RequestData' => urlencode($requestData) ,
            'DataType' => '2',
        );
        $datas['DataSign'] = encrypt($requestData, '22c789e1-81ab-4a49-a9b6-5fa6ed8bd77d');
        $url = 'http://api.kdniao.cc/Ebusiness/EbusinessOrderHandle.aspx';
        $result=sendPost($url, $datas);
        
        return $result;
    }
    
}



