<?php
namespace app\index\controller;

class User extends Action
{
	//返回用户身份信息
	public function index(){
		
    	//dump($this->user->nickname);
		$return['code'] = 10000;
		$return['data'] = $this->user;
		return json($return);
	}
	//返回土地信息
	public function land(){
		$land = model('Land') ->field('id,user_id,status,plant_id,last_plant_time,level') -> where('user_id',$this->user->id) ->order('id desc')->select();
		foreach ($land as $key => $value) {
			$land[$key]['name'] = $this->_lands[$value['level']]['name'];//土地级别名字
			$land[$key]['pic'] = $this->_lands[$value['level']]['pic'];//土地缩略图片
			if($value['plant_id']){
				
				$plant_info = model('user_plant') -> field('pic1,pic2,pic3,last_pickup,plant_time,pick_time') -> where('id',$value['plant_id'] ) ->find();
				if(empty($plant_info)){continue;}
				//植物的不同成长期图片不一样
				$land[$key]['plant_pic'] = array(
					0=>$plant_info->pic1,1=>$plant_info->pic2,2=>$plant_info->pic3
				);
				//植物成熟状态
				if( $plant_info->last_pickup == 0){
					//未采摘过
					$time = time() - $plant_info->plant_time;
				}else{
					$time = time() - $plant_info->last_pickup;
				}
				if($time >= 3600*$plant_info->pick_time ){
					//已经成熟
					$land[$key]['plant_state'] = 2;
				}elseif($time >= 1800*$plant_info->pick_time){
					//半成熟
					$land[$key]['plant_state'] = 1;
				}else{
					//未成熟
					$land[$key]['plant_state'] = 0;
				}
				$land[$key]['half_time'] = 1800*$plant_info->pick_time;
				$land[$key]['ok_time'] = 3600*$plant_info->pick_time - $time;
				
				//植物成熟时间戳
			}
			
		}
		//dump($land);
		//dump($this->_lands);
		$return['code'] = 10000;
		$return['data'] = $land;
		return json($return);
	}
	//购买土地
	public function land_buy(){
		//dump($this->_lands);exit;
		//判断账户余额是否足够
		if(!isset($_POST['type'])){
			$return['code'] = 10001;
			$return['msg'] = '缺参';
			return json($return);
		}elseif($_POST['type'] == 'update'){
			//升级土地
			$return['code'] = 10002;
			$return['msg'] = '暂时不支持升级';
			return json($return);
		}else{
			$price = $this->_lands[0]['price'];
		}
		if($this->user['gold'] < $price){
			$return['code'] = 10001;
			$return['msg'] = '账户余额不足';
			return json($return);
		}
		$data =[
			'user_id'=>$this->user->id,
			'sn'=>time().rand(100,999),
			'status'=>1,
			'create_time'=>time(),
			'payway'=>'gold',
			'paid'=>'10',
			'level'=>0,
			'pay_time'=>time()
		];
		$Land =  model('Land');
		$Land -> save($data);
		model('user') -> where('id',$this->user->id ) -> update([ 'lands'=>array('exp','lands+1'),'gold'=>array('exp','gold-'.$price) ]);//减少用户账户余额，增加土地总数量
		flog($this->user->id,1,$price,'开垦土地，花掉了',$Land->id);//记录财务日志
		$return['code'] = 10000;
		$return['msg'] = '开垦成功，或者是升级成功';
		return json($return);
	}
	//返回商店所有植物信息
	public function plant_list(){
		$plant = model('plant') -> field('id,name,pic4,price,life_cycle,harvest_value,body,stock,land_level')  -> order('id asc') -> select();
		$return['code'] = 10000;
		$return['data'] = $plant;
		return json($return);
	}
	//返回仓库植物信息
	public function plant(){
		$plants = model('User_plant') -> field('id,name,pic4,price,life_cycle,harvest_value,land_level,count(id) num') -> group('plant_id')->  where("user_id",$this->user->id) ->where("status",1) -> order('id desc') ->select();
		$return['code'] = 10000;
		$return['data'] = $plants;
		return json($return);
	}
	//购买植物
	public function plant_buy(){
		//查询植物信息
		if( isset($_POST['plant_id']) && isset($_POST['num']) ){
			$plant_id = $_POST['plant_id'];
			$num = $_POST['num'];
		}else{
			$return['code'] = 10001;
			$return['msg'] = '缺参';
			return json($return);
		}
		$plant_info = model('Plant') -> where('id',$plant_id)->find();
		if(empty($plant_info)){return json(['code'=>10001]);}
		//植物库存是否足够
		if($plant_info->stock  <  $num){
			$return['code'] = 10002;
			$return['msg'] = '库存不足';
			return json($return);
		}
		$price = $num * $plant_info->price;
		//判断账户余额是否足够
		if($this->user['gold'] > $price ){
			$return['code'] = 10003;
			$return['msg'] = '余额不足';
			return json($return);//余额不足
		}
		model('user')->where('id' , $this->user->id ) -> setDec('gold', $price);//减少用户账户余额
		flog($this->user->id,2,$price,'购买'.$plant_info->name.'植物'.$num.'颗种子',$plant_id);//记录财务日志
		$data = [
			'user_id'=>$this->user->id,
			'plant_id'=>$plant_info->id,
			'name'=>$plant_info->name,
			'pic1'=>$plant_info->pic1,
			'pic2'=>$plant_info->pic2,
			'pic3'=>$plant_info->pic3,
			'pic4'=>$plant_info->pic4,
			'price'=>$plant_info->price,
			'life_cycle'=>$plant_info->life_cycle,
			'harvest_value'=>$plant_info->harvest_value,
			'land_level'=>$plant_info->land_level,
			'pick_time'=>$plant_info->pick_time,
			'create_time'=>time(),
			'status'=>1
		];
		for($i=0;$i<$num;$i++){
			$datas[$i] = $data;
		}
		$res = model('user_plant') ->allowField(true) -> saveAll($datas);//增加我的植物记录
		$return['code'] = 10000;
		$return['data'] = $res;
		return json($return);
	}
	/*种植植物*/
	function to_plant(){
		//植物id，和土地id，要去查看植物的土地要求级别和土地是否匹配
		if( isset($_POST['plant_id']) && isset($_POST['land_id']) ){
			$plant_id = $_POST['plant_id'];
			$land_id = $_POST['land_id'];
		}else{
			// $plant_id = 3279;
			// $land_id = 4411;
			$return['code'] = 10001;
			$return['msg'] = '缺参';
			return json($return);
		}
		$plant_info = model('user_plant') -> where('id',$plant_id) -> find();
		if(empty($plant_info) || $plant_info->status != 1 || $plant_info->user_id != $this->user->id ){
			$return['code'] = 10002;
			$return['msg'] = '仓库植物不存在';
			return json($return);
		}
		$land_info = model('land') -> where('id',$land_id ) -> find();
		if(empty($land_info) || $land_info->status != 1 || $land_info->user_id != $this->user->id ){
			$return['code'] = 10003;
			$return['msg'] = '土地不能够使用';
			return json($return);
		}
		model('land') -> where('id',$land_id ) -> update(['plant_id'=>$plant_id,'status'=>2,'last_plant_time'=>time()]);//更改土地种植情况
		model('user_plant') -> where('id',$plant_id ) -> update(['status'=>2,'plant_time'=>time(),'land_id'=>$land_id]);
		flog($this->user->id,66,0,'种植了'.$plant_info->name,$plant_id);//记录财务日志
		$return['code'] = 10000;
		return json($return);
	}
	/*铲除植物*/
	function uproot(){
		//铲除植物，让植物枯萎，并且标记为铲除，把土地空出来
		if( isset($_POST['plant_id']) && isset($_POST['land_id']) ){
			$plant_id = $_POST['plant_id'];
			$land_id = $_POST['land_id'];
		}else{
			$plant_id = 3277;
			$land_id = 4411;
			// $return['code'] = 10001;
			// $return['msg'] = '缺参';
			// return json($return);
		}
		/*判断植物和土地是否是绑定关系*/
		$land_info = model('land') -> where( 'id',$land_id ) -> find();
		if(empty($land_info) || $land_info['plant_id'] != $plant_id ){
			$return['code'] = 10002;
			$return['msg'] = '土地不能够被操作';
			return json($return);
		}
		$plant_info = model('user_plant') -> where('id',$plant_id) -> find();
		if(empty($plant_info) || $plant_info->status != 2 || $plant_info->user_id != $this->user->id ){
			$return['code'] = 10003;
			$return['msg'] = '仓库植物不存在';
			return json($return);
		}
		model('land') -> where( 'id',$land_id ) -> update(['plant_id'=>0,'status'=>1]);
		model('user_plant') ->where('id',$plant_id) ->update(['status'=>3,'chan'=>1]);
		flog($this->user->id,67,0,'铲除了'.$plant_info->name,$plant_id);//记录财务日志
		$return['code'] = 10000;
		return json($return);
	}
	/*采摘植物*/
	function pick(){
		//根据plant_id找到user_plant表内数据，判断是否成熟
		if( isset($_POST['plant_id']) ){
			$plant_id = $_POST['plant_id'];
		}else{
			//$plant_id = 3279;
			$return['code'] = 10001;
			$return['msg'] = '缺参';
			return json($return);
		}
		$plant_info = model('user_plant') -> where('id',$plant_id) -> find();
		if(empty($plant_info) || $plant_info->status != 2 || $plant_info->user_id != $this->user->id ){
			$return['code'] = 10001;
			$return['msg'] = '植物不能操作';
			return json($return);
		}
		//植物成熟状态
		if( $plant_info->last_pickup == 0){
			//未采摘过
			$time = time() - $plant_info->plant_time;
		}else{
			$time = time() - $plant_info->last_pickup;
		}
		if($time < 3600*$plant_info->pick_time ){
			$return['code'] = 10002;
			$return['msg'] = '新一轮未成熟';
			return json($return);
		}
		//已成熟，记录本次采摘，改变last_pickup,改变pickup_times,当pickup_times 和 life_cycle 相等时采摘达标，植物枯萎，土地解放
		if( $plant_info->life_cycle - $plant_info->pickup_times == 1){
			//植物枯萎，土地解放
			model('land') -> where( 'id',$plant_info->land_id ) -> update(['plant_id'=>0,'status'=>1]);
			$data = [ 'last_pickup'=>time(),'pickup_times'=>array('exp','pickup_times+1'),'status'=>3 ];
			$return['data'] = 1;
		}else{
			$data = [ 'last_pickup'=>time(),'pickup_times'=>array('exp','pickup_times+1') ];
			$return['data'] = 0;
		}
		model('user_plant') -> where('id',$plant_id) -> update($data);
		//增加账户金额
		model('user') -> where('id',$this->user->id) -> setInc('gold',$plant_info->harvest_value);
		flog($this->user->id,4,$plant_info->harvest_value,'采摘'.$plant_info->name.'果实',$plant_id);//记录财务日志
		$return['code'] = 10000;
		return json($return);
	}

}
