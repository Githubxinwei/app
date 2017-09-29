<?php
namespace app\custom\controller;

/*基础信息获取 */
class Base extends Action{
	function app_type(){
		return json(get_app('all'));
	}
	//上传图片接口，图片以用户手机号为名，存储在文件夹内
	function pic_upload(){
		if(isset($_POST['image'])){
			$base64 = $_POST['image'];
		}else{
			$base64 = file_get_contents('test_gif.txt');
		}
		$img_type = strstr($base64,';',true);
		$img_type = explode('/',$img_type);
		if(!in_array($img_type[1], ['jpg','png','gif'])){
			$result['code'] = '10001';$result['msg_test'] = '格式不支持，必须是jpg | png | gif';
		}
		if(strstr($base64,',')){
			$base64 = substr(strstr($base64,','),1);
		}
		$tmp  = base64_decode($base64);//解码
		//写文件
		$path = 'Uploads/'.$this->custom->username.'/'.date("Ymd").'/';
		if(!is_dir($path)){
			//mkdir($path);
			mkdir(iconv("UTF-8", "GBK", $path),0777,true);
		}
		$name = time().rand(1000,9999);
		file_put_contents($path.$name.".".$img_type[1], $tmp);
		$pic_url = '/'.$path.$name.".".$img_type[1];
		$result['code'] = '10000';
		$result['msg_test'] = 'ok';
		$result['data'] = $pic_url;
		echo json_encode($result);
	}
	//遍历目录输出目录及其下的所有文件 利用函数的递归解决
	private function my_scandir($dir){  
		$files=array();  
		if(is_dir($dir)){  
			if($handle=opendir($dir)){  
				while(($file=readdir($handle))!==false){  
					if($file!='.' && $file!=".."){  
						if(is_dir($dir."/".$file)){
							$files[$file]=$this->my_scandir($dir."/".$file);  
						}else{  
							$files[]=$dir."/".$file;  //获取文件的完全路径
							$filesnames[]=$file;      //获取文件的文件名称
						}  
					}  
				}  
			}  
		}  
		closedir($handle);  
		$arr = [];$i=0;
		foreach($files as $v){
			if(is_array($v)){
				foreach($v as $k){
					$arr[$i] = $k;
					$i++;
				}
			}else{
				$arr[$i] = $v;
				$i++;
			}
		}
		//dump($arr);exit;
		return $arr; 
		//return $filesnames; 
	}
	function pic_list(){
		$arr =array();
		//获取到指定文件夹内的照片数量
		$img = array('gif','png','jpg');//所有图片的后缀名
		$dir = 'Uploads/'.$this->custom->username;
		$pic = $this->my_scandir($dir);
		foreach($pic as $k=>$p)
		{
			$new[$k]['time'] =  filemtime($p);
			$new[$k]['pic'] = $p;
		}
		rsort($new);
		$result['code'] = 10000;
		$result['msg_test'] = 'ok';
		$result['data'] = $new;
		echo json_encode($result);
	}
}

 ?>