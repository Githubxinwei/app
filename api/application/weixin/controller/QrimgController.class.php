<?php
namespace Weixin\Controller;
use Think\Controller;

class QrimgController extends Controller{
	function index($user_id,$nickname,$erweima_img,$token){

		//载入字体zt.ttf 
		$fnt = "Public/qrcode/msyh.ttf"; 
		$head_img="Public/qrcode/head_pic/".$token."/".$user_id.".jpg";
		//相关参数
		$info = M('qrset')->where("token = '$token' ")->find();
		if(!$info){
			$info = array('pic_url'=>'/public/qrcode/qrcode_bg.jpg','font_size'=>'18','font_x'=>'300','font_y'=>'50','head_size'=>'150','head_x'=>'50','head_y'=>'50','qr_size'=>'250','qr_x'=>'200','qr_y'=>'600');
		}
		$head_height=$head_width=$info['head_size'];
		$erweima_height=$erweima_width=$info['qr_size'];
		//$dst_path=$info['pic_url'];
		$dst_path = 'http://'.$_SERVER['HTTP_HOST'].$info['pic_url'];
		$str=$nickname;
		$font_size=$info['font_size'];
		$fnt_x=$info['font_x'];
		$fnt_y=$info['font_y'];
		//头像缩小
		$src1=$this->img_suo($head_img,$head_width,$head_height);
		//二维码缩小
		$src=$this->img_suo($erweima_img,$erweima_width,$erweima_height);
		//创建图片的实例
		$dst = imagecreatefromstring(file_get_contents($dst_path));
		//$src = imagecreatefromstring(file_get_contents($src_path));
		//获取水印图片的宽高
		//将水印图片复制到目标图片上，最后个参数50是设置透明度，这里实现半透明效果
		imagecopymerge($dst, $src1, $info['head_x'], $info['head_y'], 0, 0, $head_width, $head_height, 100);
		imagecopymerge($dst, $src, $info['qr_x'], $info['qr_y'], 0, 0, $erweima_width, $erweima_height, 100);
		//如果水印图片本身带透明色，则使用imagecopy方法
		//imagecopy($dst, $src, 10, 10, 0, 0, $src_w, $src_h);
		//创建颜色，用于文字字体的白和阴影的黑 
		$white=imagecolorallocate($dst,255,255,255); 
		$black=imagecolorallocate($dst,50,50,50);
		imagettftext($dst,$font_size, 0, $fnt_x+1, $fnt_y+1, $black, $fnt, $str); 
		imagettftext($dst,$font_size, 0, $fnt_x, $fnt_y, $white, $fnt, $str); 
		if(!is_dir('Public/qrcode/qr_path/'.$token)){
			mkdir('Public/qrcode/qr_path/'.$token);
		}
		ImageJPEG($dst,'Public/qrcode/qr_path/'.$token.'/'.$user_id.'.jpg'); // 保存图片,但不显示 
		//销毁对象 
		ImageDestroy($dst);
		return 'Public/qrcode/qr_path/'.$token.'/'.$user_id.'.jpg';
	}
		
	function img_suo($img='head.jpg',$new_width=100,$new_height=100){
		list($width, $height) = getimagesize($img);
		$image_p = imagecreatetruecolor($new_width, $new_height);
		$image = imagecreatefromjpeg($img);
		imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

		return $image_p;
	}
}


