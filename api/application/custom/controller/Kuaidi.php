<?php
/**
 * 使用快递鸟api进行查询
 */
namespace app\custom\controller;
use think\Controller;

class Kuaidi extends Xiguakeji{

    public function __construct()
    {
        parent::__construct();
        $this -> EBusinessID = '1310235';
        $this -> AppKey = '22c789e1-81ab-4a49-a9b6-5fa6ed8bd77d';
        $this -> ReqURL = 'http://api.kdniao.cc/Ebusiness/EbusinessOrderHandle.aspx';
    }

    /**
     * @param $kd_code 快递公司编码
     * @param $kd_number 快递单号
     */
    public function getMessage(){
    	if(!isset($this->data['kd_code'])){
            $return['code'] = 10001;
            $return['msg_test'] = '缺少快递公司编码kd_code';
            return json($return);
        }else{
            $kd_code = $this->data['kd_code'];
        }
    	if(!isset($this->data['kd_number'])){
            $return['code'] = 10002;
            $return['msg_test'] = '缺少快递单号kd_number';
            return json($return);
        }else{
            $kd_number = $this->data['kd_number'];
        }
        $requestData= "{'OrderCode':'','ShipperCode':'".$kd_code."','LogisticCode':'".$kd_number."'}";
        $datas = array(
            'EBusinessID' => $this -> EBusinessID,
            'RequestType' => '1002',
            'RequestData' => urlencode($requestData) ,
            'DataType' => '2',
        );
        $datas['DataSign'] = $this -> encrypt($requestData, $this -> AppKey);
        $result = $this -> sendPost($this -> ReqURL, $datas);
	if($result){
            return $result;
        }else{
            $return['code'] = 10003;
            $return['msg_test'] = '快递鸟接口账户已过期';
            return json($return);
        }
    }

    /**
     *  post提交数据
     * @param  string $url 请求Url
     * @param  array $datas 提交的数据
     * @return url响应返回的html
     */
    private function sendPost($url, $datas) {
        $temps = array();
        foreach ($datas as $key => $value) {
            $temps[] = sprintf('%s=%s', $key, $value);
        }
        $post_data = implode('&', $temps);
        $url_info = parse_url($url);
        if(empty($url_info['port']))
        {
            $url_info['port']=80;
        }
        $httpheader = "POST " . $url_info['path'] . " HTTP/1.0\r\n";
        $httpheader.= "Host:" . $url_info['host'] . "\r\n";
        $httpheader.= "Content-Type:application/x-www-form-urlencoded\r\n";
        $httpheader.= "Content-Length:" . strlen($post_data) . "\r\n";
        $httpheader.= "Connection:close\r\n\r\n";
        $httpheader.= $post_data;
        $fd = fsockopen($url_info['host'], $url_info['port']);
        fwrite($fd, $httpheader);
        $gets = "";
        $headerFlag = true;
        while (!feof($fd)) {
            if (($header = @fgets($fd)) && ($header == "\r\n" || $header == "\n")) {
                break;
            }
        }
        while (!feof($fd)) {
            $gets.= fread($fd, 128);
        }
        fclose($fd);

        return $gets;
    }


    /*
     * 进行加密
     */
    private function encrypt($data, $appkey) {
        return urlencode(base64_encode(md5($data.$appkey)));
    }
    public function dataToArray($data){
        $res = array();
        $res[0]['ddh'] = $data['nu'];
        $res[0]['kd'] = $data['company'];
        foreach ($data['data'] as $k => $v){
            $data['data'][$k]['info'] = $v['context'];
            unset($data['data'][$k]['context']);
        }
        $res[1] = $data['data'];
        return $res;
    }

    function _url($Date){
        $ch = curl_init();
        $timeout = 5;
        curl_setopt ($ch, CURLOPT_URL, "$Date");
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)");
        curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $contents = curl_exec($ch);
        curl_close($ch);
        return $contents;
    }


    //转换成数组
    function objectToArray($e){
        $e=(array)$e;
        foreach($e as $k=>$v){
            if( gettype($v)=='resource' ) return;
            if( gettype($v)=='object' || gettype($v)=='array' )
                $e[$k]=(array)$this -> objectToArray($v);
        }
        return $e;
    }

}
