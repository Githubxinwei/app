<?php
namespace app\weixin\controller;

class Api{
	function index(){
		if(!isset($GLOBALS["HTTP_RAW_POST_DATA"])){return;}
		$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
		$component = controller('Weixin/Component');
		$msg = $component -> decryptMsg($_GET,$postStr);
		$name = rand(10000,99999);file_cache($name.'get',$_GET,'');file_cache($name.'postObj',$postObj,'');
		if (!empty($msg)){
			$postObj = simplexml_load_string($msg, 'SimpleXMLElement', LIBXML_NOCDATA);
			$name = rand(10000,99999);file_cache($name.'get',$_GET,'');file_cache($name.'postObj',$postObj,'');
			$RX_TYPE = trim($postObj->MsgType);
			/*全网发布1事件请求回复*/
			if($RX_TYPE == 'event'){
				$name = rand(10000,99999);file_cache($name.'get',$_GET,'');file_cache($name.'postObj',$postObj,'');
			}
			if( in_array(trim($postObj->ToUserName),['gh_3c884a361561','gh_8dad206e9538'])  && $RX_TYPE == 'event' ){
					$content = $postObj->Event.'from_callback';
					$result = $this->transmitText($postObj, $content);
					$component = controller('Weixin/Component');
					$result = $component -> encryptMsg($result);//消息加密返回
					echo $result;exit;
			}
			//消息类型分离
			switch ($RX_TYPE){
				case "event":
					$result = $this->receiveEvent($postObj);
					break;
				case "text":
					$result = $this->receiveText($postObj);
					break;
				case "image":
					//$result = $this->receiveImage($postObj);
					break;
				case "location":
					//$result = $this->receiveLocation($postObj);
					break;
				case "voice":
					//$result = $this->receiveVoice($postObj);
					break;
				case "video":
					//$result = $this->receiveVideo($postObj);
					break;
				case "link":
					//$result = $this->receiveLink($postObj);
					break;
				default:
					//$result = "unknown msg type: ".$RX_TYPE;
					break;
			}
				   // $this->logger("T ".$result);
			if($result){
				$component = controller('Weixin/Component');
				$result = $component -> encryptMsg($result);//消息加密返回
				echo $result;
			}else{
				echo '';
			}
		}else {
					echo "";
					exit;
		}
	}
	//接收事件消息
		private function receiveEvent($object){
		$content = "";
		$openid = trim($object->FromUserName);
				switch ($object->Event){
						case "subscribe": //关注事件
							/*判断是哪个商户*/
							
						break;
						case "unsubscribe"://取消关注事件
						
						break;
						case "SCAN": //扫描事件$object->EventKey
						//$content = '';
						break;
						case "CLICK": //菜单点击事件$object->EventKey
			 
						break;
						case "LOCATION": //地理位置上传事件纬度 ".$object->Latitude.";经度 ".$object->Longitude;
						break;
						case "VIEW"://"跳转链接 ".$object->EventKey;
						break;
						case "weapp_audit_success"://小程序通过审核的消息通知
							file_cache('88888888888object',$object,'');
							file_cache('88888888888get',$_GET,'');
							//如果提交审核通过，执行发布版本，并通知所有人
							$auth_info = model('auth_info') -> where('appid',$_GET['appid'])->find();
							$weapp = new \app\weixin\controller\Common($auth_info['apps']);
							$weapp->release();
							model('app') -> where('appid',$auth_info['apps']) ->update(['is_publish'=>4]);//标注已发布
							//通知到短信，邮箱，app内有notifytel,notifyemail
							
						break;
						case "weapp_audit_fail":
							file_cache('9object',$object,'');
							file_cache('9get',$_GET,'');
							//通知所有人审核失败，更改状态到已绑定
							file_cache('9Reason',$object->Reason,'');
							$auth_info = model('auth_info') -> where('appid',$_GET['appid'])->find();
							model('app') -> where('appid',$auth_info['apps']) ->update(['is_publish'=>2]);//标注已上传代码
							//通知到短信，邮箱，app内有notifytel,notifyemail
						break;
						case "MASSSENDJOBFINISH":
				//$content = "消息ID：".$object->MsgID."，结果：".$object->Status."，粉丝数：".$object->TotalCount."，过滤：".$object->FilterCount."，发送成功：".$object->SentCount."，发送失败：".$object->ErrorCount;
						break;
						default:
						file_cache('up',$info,'');
			  //  $content = "receive a new event: ".$object->Event;
						break;
				}
				if(is_array($content)){
					 if (isset($content[0])){
							$result = $this->transmitNews($object, $content);
						}else if (isset($content['MusicUrl'])){
							$result = $this->transmitMusic($object, $content);
						}
				}else{
			if($content == ''){
				echo '';exit;
			}else{
				$result = $this->transmitText($object, $content);
			}
			
				}
				return $result;
		}
	//接收文本消息
	private function receiveText($object){
		if($object->Content){
			$keyword = trim($object->Content);
		}else{
			$keyword = trim($object->EventKey);
		}
		if (strstr($keyword, "QUERY_AUTH_CODE:")){
			//全网发布检测3
			$Check = controller('Weixin/Check');
			$Check->send_text($keyword,trim($object->FromUserName));
			echo '';exit;
		}elseif($keyword == 'TESTCOMPONENT_MSG_TYPE_TEXT'){
			//全网发布检测2
			$content = 'TESTCOMPONENT_MSG_TYPE_TEXT_callback';
			$result = $this->transmitText($object, $content);
			$component = controller('Weixin/Component');
			$result = $component -> encryptMsg($result);//消息加密返回
			echo $result;exit;
			
		}else{
			$auth_info = M('auth_info') ->field("token") -> where("appid = '$_GET[appid]' ")->find();
			$token = $auth_info['token'];
			$content = "";
			$news = M('news');
			$news_all = $news->field("id,pic_url,keyword,keyword_type,title,desc") -> where("keyword != '' and token = '$token'") ->order("code asc") -> select();
			$newsinfo = array();$i=0;
			foreach($news_all as $item){
				$keyword_arr = explode(',',$item['keyword']);
				foreach($keyword_arr as $vv){
					if($item['keyword_type'] == 1 && strstr($keyword,$vv)){
						$newsinfo[$i] = $item;$i++;
					}
					if($item['keyword_type'] == 0 && $keyword == $vv){
						$newsinfo[$i] = $item;$i++;
					}
				}
			}
			//如果有图文内容
			if($newsinfo != null){
				$content = $newsinfo;
				foreach($content as $key =>$value){
					$id = $value['id'];
					$content[$key]['url']="http://".$_SERVER['SERVER_NAME'].U('/home/wap/index')."?id=".$id;
					$content[$key]['pic_url']="http://".$_SERVER['SERVER_NAME'].$value['pic_url'];
					$res=$news->where(" id = '$id' ")->setInc('click',1);
				}
				//file_put_contents('b.txt',$content);
			}else{
			   
				
			}
			
		}
			//$content = '接口调试中，一切正常——西瓜科技';
		if(is_array($content)){
			$result = $this->transmitNews($object, $content);	
		}elseif(empty($content)){
			echo "";exit;
		}else{
			$result = $this->transmitText($object, $content);
		}
		return $result;
	}
	//回复文本消息
	private function transmitText($object, $content){
			$xmlTpl = "<xml>
		<ToUserName><![CDATA[%s]]></ToUserName>
		<FromUserName><![CDATA[%s]]></FromUserName>
		<CreateTime>%s</CreateTime>
		<MsgType><![CDATA[text]]></MsgType>
		<Content><![CDATA[%s]]></Content>
		</xml>";
		$result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time(), $content);
		return $result;
	}

	//回复图文消息
	private function transmitNews($object, $newsArray)
	{
		if(!is_array($newsArray)){
			return;
		}
		$itemTpl = "    <item>
		<Title><![CDATA[%s]]></Title>
		<Description><![CDATA[%s]]></Description>
		<PicUrl><![CDATA[%s]]></PicUrl>
		<Url><![CDATA[%s]]></Url>
	</item>
";
		$item_str = "";
		foreach ($newsArray as $item){
			$item_str .= sprintf($itemTpl, $item['title'], $item['desc'], $item['pic_url'], $item['url']);
		}
		$xmlTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[news]]></MsgType>
<ArticleCount>%s</ArticleCount>
<Articles>
$item_str</Articles>
</xml>";

		$result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time(), count($newsArray));
		return $result;
	}

}