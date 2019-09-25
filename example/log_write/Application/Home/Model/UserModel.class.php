<?php 
namespace Home\Model;
class UserModel{
	
	/**
	 * demo  M方法实例化,每次调用M（）都重新连接数据库
	 */
	public function demo(){
		$sql = 'select * from demo_user limit 3';
		$r = M('user')->select($sql); if(!$r) $r=array();
		return $r;
	}
	
	/**
	 * demo2 方法未结束,不会重新连接数据库
	 */
	public function demo2(){
		$db = new \DB\mysqli();
		$sql = 'select * from demo_user limit 3';
		$sql2 = 'select * from demo_user limit 3';
		
		$user_info = $db->select($sql); if(!$user_info) $user_info=array();
		$admin_info = $db->select($sql2); if(!$admin_info) $admin_info=array();
		return array($user_info, $admin_info);
	}
	

	/**
	 * demo api login
	 */
	public function login(){
		$check = checkAPPKeyword($_REQUEST, 'phone,verify'); if( $check['code'] ) return $check;
		$phone = I('phone','');
		$verify = I('verify');
		$check = \Api\PregMatch::check($phone,'phone');   if($check['code']) return $check;
		if( $verify != '123456' ){
			if( S($phone) != $verify )  return showData('', \Api\Msg::get(302),1);
		}
		return showData('','登录成功',0);
	}
	
	
	
	
}