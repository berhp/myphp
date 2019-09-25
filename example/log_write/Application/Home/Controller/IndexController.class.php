<?php 
namespace Home\Controller;
use MyPHP\Controller;
use Home\Model\UserModel;
class IndexController extends Controller{
	protected $db_user;	
	function _init(){
		$this->db_user = new UserModel();
	}
	
	public function index(){ 
		$this->show('<p>欢迎使用myphp:'.MyPHP_VERSION.'</p>');
	}
	
	public function login(){
		if( IS_GET ){
			$this->display('login');
		}else{
			$code = $_POST['code'];
			if( $_SESSION['code'] == $code ){
				echo 'yes';
			}else{
				echo 'no';
			}
		}
	}

	
	/**
	 * 生成验证码图片
	 */
	public function code(){
		$gd = new \MyPHP\GD();
		return $gd->create_code();
	}
	 
	/**
	 * 检测验证码是否正确
	 */
	public function check_code(){
		if( IS_GET){
			$this->display('login');
		}else{
			$code = $_POST['code'];
			if( $_SESSION['code'] == $code ){
				echo 'yes';
			}else{
				echo 'no';
			}
		}
	}
	
	public function phpinfo(){
		phpinfo();
	}
	
	
}