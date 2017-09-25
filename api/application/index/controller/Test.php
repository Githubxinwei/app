<?php 
namespace app\index\controller;

class Test{
	function pic_upload(){
		if(isset($_POST['image'])){
			$base64 = $_POST['image'];
		}else{
			$base64 = file_get_contents('test_img.txt');
		}
		if(strstr($base64,',')){
			$base64 = substr(strstr($base64,','),1);
		}
		$tmp  = base64_decode($base64);//解码
		//写文件
		if(!is_dir('Uploads/test/')){
			mkdir('Uploads/test/');
		}
		$name = time().rand(1000,9999);
		file_put_contents('Uploads/test/'.$name.".jpg", $tmp);
		$pic_url = '/Uploads/test/'.$name.".jpg";
		$result['code'] = '10000';
		$result['msg'] = '把base64用参数名image以post形式发送过来，返回路径前先加入http://192.168.1.177/tp5/public/，临时使用';
		$result['data'] = $pic_url;
		echo json_encode($result);
	}
	function pic_list(){
		$arr =array();
		//获取到指定文件夹内的照片数量
		$img = array('gif','png','jpg');//所有图片的后缀名
		
		if(isset($_POST['address'])){
			$dir = $_POST['address'];//文件夹名称
		}else{
			$dir = './Uploads/test/';
		}
		if(isset($_POST['num'])){
			$num = $_POST['num'];
		}else{
			$num = 2;
		}
		$pic = array();
		// foreach($img as $k=>$v)
		// {
			// $pattern = $dir.'*.'.$v;
			// $all = glob($pattern);
			// $pic = array_merge($pic,$all);
		// }
		if (is_dir($dir)){
			if ($dh = opendir($dir)){
				while (($file = readdir($dh))!= false){
					$filePath = $dir.$file;
					$arr1 = explode('.',$file);
					if(in_array('png',$arr1) || in_array('jpg',$arr1) || in_array('gif',$arr1)){
						$all = array($filePath);
						$pic = array_merge($pic,$all);
						
					}
					
				}
				closedir($dh);
			}
		}
		//echo "<pre>";

		foreach($pic as $k=>$p)
		{
			$new[$k]['time'] =  filemtime($p);
			$new[$k]['pic'] =  $p;
		//分行分页显示代码
		}
		if($new){
			rsort($new);
		}
		
		//var_dump($new);
		foreach($new as $k=>$v)
		{
			$pic[$k] =  $v['pic'];
		//分行分页显示代码
		}
		$arr['page_num'] = ceil(count($pic)/$num);
		$arr['pic'] = $pic;
		$result['code'] = 10000;
		$result['msg'] = '现在直接请求就可以得到列表了，暂时使用';
		$result['data'] = $arr;
		echo json_encode($result);
	}
	//付款接口
	function index(){
		//商户订单号，商户网站订单系统中唯一订单号，必填
	    	$out_trade_no ='33232455235';
		   //订单名称，必填
		$subject = 'xiguakeji';
		//付款金额，必填
		$total_amount = '1.0';
		//商品描述，可空
		$body ='334';
	    	//超时时间
	    	$timeout_express="1m";
		\Think\loader::import('aop.request.AlipayTradeWapPayContentBuilder');
		$payRequestBuilder = new \AlipayTradeWapPayContentBuilder();
		$payRequestBuilder->setBody($body);
		$payRequestBuilder->setSubject($subject);
		$payRequestBuilder->setOutTradeNo($out_trade_no);
		$payRequestBuilder->setTotalAmount($total_amount);
		$payRequestBuilder->setTimeExpress($timeout_express);
		dump($payRequestBuilder);
		$biz_content=$payRequestBuilder->getBizContent();
		\Think\loader::import('aop.request.AlipayTradeWapPayRequest');
        		$request = new \AlipayTradeWapPayRequest ();
		$notify_url = 'http://www.baidu.com/';
		$return_url = 'http://www.baidu.com/';
		$request->setNotifyUrl($notify_url);
		$request->setReturnUrl($return_url);
		$request->setBizContent ( $biz_content );
		 \Think\loader::import('aop.AopClient');
        		$aop = new \AopClient ();
        		$aop->signType = 'RSA2';
		$result = $aop->pageExecute ( $request);
		echo $result;
		dump($result);

	}

	//支付宝发红包
	function soupon(){
		 \Think\loader::import('aop.AopClient');
        		$aop = new \AopClient ();
        		$aop->signType = 'RSA2';
        		\Think\loader::import('aop.request.AlipayFundCouponOrderDisburseRequest');
		$request = new \AlipayFundCouponOrderDisburseRequest ();
		 $data = '{
		            "out_order_no":"8077735255938023",
		            "deduct_auth_no":"2014031600002001260000001024",
		            "deduct_out_order_no":"8077735255937028",
		            "out_request_no":"8077735255634078",
		            "amount":"1.00",
		            "payee_logon_id":"529157244@qq.com",
		            "pay_timeout":"1h"
		            "extra_param":"merchantExt:key=value"
		}';
		$request->setBizContent($data);
$result = $aop->execute ( $request); dump($result);

$responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
$resultCode = $result->$responseNode->code;
if(!empty($resultCode)&&$resultCode == 10000){
echo "成功";
} else {
echo "失败";
}
	}
	//转账到支付宝账户
	function accounts(){
		 \Think\loader::import('aop.AopClient');
        		$aop = new \AopClient ();
        $aop->signType = 'RSA2';
        \Think\loader::import('aop.request.AlipayFundTransToaccountTransferRequest');
        $request = new \AlipayFundTransToaccountTransferRequest ();
        $amount = '0.1';
        $out_sn = rand(1000,9999).time();
        $data = '{
            "out_biz_no":"'.$out_sn.'",
            "payee_type":"ALIPAY_LOGONID",
            "payee_account":"529157244@qq.com",
            "amount":"'.$amount.'",
            "payer_show_name":"口袋农场佣金发放",
            "payee_real_name":"于春峰",
            "remark":"转账佣金"
            }';
        $request->setBizContent($data);
$result = $aop->execute ( $request);dump($result);
$responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
$resultCode = $result->$responseNode->code;
if(!empty($resultCode)&&$resultCode == 10000){
echo "成功";
} else {
echo "失败";
}
	}
}




 ?>