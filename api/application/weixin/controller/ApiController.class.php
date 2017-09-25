<?php
namespace Weixin\Controller;
use Think\Controller;
class ApiController extends Controller{
	function index(){
		$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
		$component = A('Weixin/Component');
		$msg = $component -> decryptMsg($_GET,$postStr);
		if (!empty($msg)){
		            $postObj = simplexml_load_string($msg, 'SimpleXMLElement', LIBXML_NOCDATA);
		            $RX_TYPE = trim($postObj->MsgType);
		            /*全网发布1事件请求回复*/
		            if(trim($postObj->ToUserName) == 'gh_3c884a361561' && $RX_TYPE == 'event' ){
		            		$content = 'LOCATIONfrom_callback';
				$result = $this->transmitText($postObj, $content);
				$component = A('Weixin/Component');
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
				$component = A('Weixin/Component');
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
            				$auth_info = M('auth_info') ->field("token") -> where("appid = '$_GET[appid]' ")->find();
            				if(!$auth_info){echo '';exit;}
				/*两种情况，一种是直接关注，另外是扫码关注*/
				/*先判断有无用户数据*/
				$user_info = M('user_child') ->field("id")  -> where("openid = '$openid' ") -> find();
				if(!$user_info){
					/*获取用户信息*/
					$weixin = A('Weixin/Wx');
					$weixin -> __construct($auth_info['token']);
					$userinfo = $weixin -> get_user_info($openid);
					$new_Data = array(
						'openid'=>$openid,
						'token'=>$auth_info['token'],
						'time'=>time(),
						'is_subscribe'=>1,
						'nickname'=>$userinfo['nickname'],
						'headimgurl'=>$userinfo['headimgurl']
					);
					/*判断新用户是哪一种关注来源*/
					if(trim($object->EventKey)){
						/*锁定关系*/
						$new_Data['pid'] = str_replace("qrscene_","",$object->EventKey);
						$template = A('Pay/Template');
						$template -> __construct($auth_info['token']);
						$pid_info = M('user_child') ->field("openid")->where("token = '$auth_info[token]' and user_id = '$new_Data[pid]' ") -> find();
						if ($pid_info['openid']) {
							$desc = array(
								'top'=>'',
								'keyword1'=>'新增粉丝通知',
								'keyword2'=>'恭喜您，您的朋友'.$new_Data['nickname'].'在'.date("Y-m-d H:i:s",$new_Data['time']).'成为您的粉丝',
								'remark'=>''
							);
							$template ->task_notice($pid_info['openid'],$desc);
						}
						//$content = '扫码关注成功';
					}
					M('user_child') -> add($new_Data);/*写入新用户数据*/
				}else{
					M('user_child') -> where("id = 2 ") -> save(array('is_subscribe'=>1));
					//$content = '老用户关注';
				}
				$subscribe_info = M('subscribe') -> where("token = '$auth_info[token]' ") -> find();
				$content = $subscribe_info['content'];
				if(!$content){$content = '关注回复未配置';}
                		break;
            			case "unsubscribe"://取消关注事件
            			M('user_child') -> where("openid = '$openid' ") -> save(array('is_subscribe'=>0,'nosub_time'=>time()));/*记录取消关注行为*/
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
            			case "MASSSENDJOBFINISH":
                //$content = "消息ID：".$object->MsgID."，结果：".$object->Status."，粉丝数：".$object->TotalCount."，过滤：".$object->FilterCount."，发送成功：".$object->SentCount."，发送失败：".$object->ErrorCount;
                		break;
            			default:
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
			$Check = A('Weixin/Check');
			$Check->send_text($keyword,trim($object->FromUserName));
			echo '';exit;
		}elseif($keyword == 'TESTCOMPONENT_MSG_TYPE_TEXT'){
			//全网发布检测2
			$content = 'TESTCOMPONENT_MSG_TYPE_TEXT_callback';
			$result = $this->transmitText($object, $content);
			$component = A('Weixin/Component');
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
			   // file_put_contents('a.txt',$token);
				$text = M('text');
				$text_all=$text->where("token = '$token' and keyword like '%$keyword%' ")->select();
				$textinfo = array();$i=0;
				foreach($text_all as $item){
				   // file_put_contents("a.txt",$item['content']);
					$keyword_arr = explode(',',$item['keyword']);
					foreach($keyword_arr as $vv){
						if($item['keyword_type'] == 1 && strstr($keyword,$vv)){
							$textinfo[$i] = $item;$i++;
						}
						if($item['keyword_type'] == 0 && $keyword == $vv){
							$textinfo[$i] = $item;$i++;
						}
					}
				}
				
				
				if($textinfo){
					//如果有创建，从结果中取随机值
					$temp = array_rand($textinfo,1);  
					$content = $textinfo[$temp]['content'];
					//增加调用次数
					$id = $textinfo[$temp]['id'];
					$res=$text->where(" id = '$id' ")->setInc('click',1); 
				}else{
					$custominfo=M('custom')->where("token = '$token' ") -> select();
					$custominfo = $custominfo[0];
					//如果开启了自定义回复
					if($custominfo['switch'] == 1){
						if($custominfo['keyword']){
							$where=array(
								'keyword'=>$custominfo['keyword']
							  );
							 $newsinfo=M('news')->where($where)->order("code asc")->select();
							if($newsinfo){
								//如果有，赋值
								$content = $newsinfo;
								foreach($content as $key =>$value){
									$id = $value['id'];
									$content[$key]['url']="http://".$_SERVER['SERVER_NAME'].U('/home/wap/index')."?id=".$id;
									$content[$key]['pic_url']="http://".$_SERVER['SERVER_NAME'].$value['pic_url'];
								}
							}else{
                                $content = $custominfo['content'];
							}
						}else{
                            $content = $custominfo['content'];
						}
					}

				}
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