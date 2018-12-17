<?php 
/**
 * 核心类
 * @author berhp
 *
 */
namespace MyPHP;
class init{
	
	/**
	 * 当遇见未加载的类,则执行我们自己定义的方法
	 * @link http://www.php.net/manual/zh/function.spl-autoload-register.php
	 * @param unknown $classname
	 * @tutorial <pre>
	 *   1.优势在于,可以不影响其他 框架中的  __autoload()定义,如  申明注册 spl_autoload_register('MyPHP\init::myload');
	 *   2.php>=5.3
	 *   3.设计-加载顺序： 框架中 》 用户自定义 》都不满足记录错误 
	 *   4. 转译 \ 为 /  支持linux路径
	 */
	static function myload($classname){
		//var_dump($classname);
		$classname = str_replace('\\',"/", $classname);
		$file = MyPHP_DIR . '/Library' .'/' . $classname . APP_CLASS_FILE_EXT;
		if( file_exists( $file ) ){
				require_once ( $file );  // 框架中的
		}else{
				$file = APP_PATH . $classname . APP_CLASS_FILE_EXT; //用户项目自定义
				if( file_exists( $file ) ){
					require_once ( $file );
				}else{
					if( C('is_spl_autoload_registerOther')===true ) return;  //若配置,开启继续队列访问第三方的自动加载类,则跳过这里的错误,默认配置为false
					$log = new \Log\write();
					$log->write('[file]:404 error '.$file);
					include C('template.filePath').C('template.404');
					exit;
				}
		}
	}
	
	/**
	 * 路由解析
	 * @tutorial <pre>
	 * 	MODULE_NAME  		当前模块名  
	 * 	CONTROLLER_NAME  	当前控制器名
	 *  ACTION_NAME  		当前操作名
	 */
	private static function _action_url(){
		$data = array();
		$url = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
		$_EXT_NAME='';  $EXT_NAME_API='';
		if($url){
			$r = explode('/', substr($url, 1) );
			$count = count($r);  	//用于替换最后参数的.html
			$i = 0; $y=0; $_key=''; $_key_i='';
			foreach ( $r as $k=>$v ){
				$i++;
				if( strlen($v) ){
					if($i==$count){
						$v = str_replace( APP_VIEW_FILE_EXT , '', $v);
					}
					if( $k >= APP_ROUTE_BEGIN_NUMBER ){ //模块名解析
						$data[] = $v;
					}else{
						$_EXT_NAME .= '\\'.$v;
					}
					if( $k >= APP_ROUTE_OVER_NUMBER ){ 	//参数解析
						$y++;
						if( !strlen($_key_i) ) $_key_i=$k;						
						if( 1 === $y%2 ){
							$_REQUEST[ $r[$k] ] = isset($r[$k+1]) ? $r[$k+1] : '';
						}
					}
					if( $k==0 ) $EXT_NAME_API=$v;
				}
			}
		}
		
		if( IS_API === true  && !$_EXT_NAME ) $_EXT_NAME = DEFAULT_EXT_NAME;  //\Home\v1
		if( IS_API === true  && !$EXT_NAME_API ) $EXT_NAME_API = DEFAULT_EXT_NAME_API;  //Home
		$data[0] = isset($data[0]) ? $data[0] :  DEFAULT_MODULE; 	  //Home
		$data[1] = isset($data[1]) ? $data[1] :  DEFAULT_CONTROLLER;  //Index
		$data[2] = isset($data[2]) ? $data[2] :  DEFAULT_ACTION; 	  //index
		$count = count($data);
		$data[$count-1] = str_replace( APP_VIEW_FILE_EXT , '', $data[$count-1]); //动态替换如 index.html -> index
		define('MODULE_NAME', 	 $data[0] ); 		//当前模块名  
		define('CONTROLLER_NAME', $data[1] ); 		//当前控制器名
		define('ACTION_NAME', 	$data[2] ); 		//当前方法名
		define('EXT_NAME', 		$_EXT_NAME ); 		//扩展字段,便于路由解析,如  \acdd\v1
		define('EXT_NAME_API', 	$EXT_NAME_API ); 	//扩展,当IS_API===true时,便于加载API中子项目配置
	}

	/**
	 * 实现视图渲染
	 * @tutorial
	 *   1. 检查类控制器文件是否存在,检查类中方法是否存在,不存在显示404错误页
	 *   2. 根据url路由解析,动态加载 控制器 并运行
	 *   $r = new \User\Controller;
	 *   $r->login();
	 */
	private static function _show_display(){
		if( IS_API === true ){
			$_r = EXT_NAME.'\\'.'Controller'.'\\'.MODULE_NAME.'\\'.CONTROLLER_NAME.'Api';
		}else{
			$_r = EXT_NAME.'\\'.MODULE_NAME.'\\'.'Controller'.'\\'.CONTROLLER_NAME.'Controller';
		}
		self::myload($_r);
		$r = new $_r;
		if( is_callable(array($r , ACTION_NAME)) === false ){
			$log = new \Log\write();
			$log->write('[file]:405 error Call to undefined method: '.$_r.'::'.ACTION_NAME.'()');
			include C('template.filePath').C('template.404');
			exit;
		}
		$var_action_name = ACTION_NAME;
		$r->$var_action_name();
	}

	/**
	 * 初始化
	 */
	static function start(){
		date_default_timezone_set(DATE_DEFAULT_TIMEZONE_SET);  //设置时区
		$GLOBALS['_myRunInfo']['start_time'] = microtime(TRUE);
		$GLOBALS['_myRunInfo']['start_usage'] = memory_get_usage();  //开始内存,单位bytes
		
		// 注册:自动加载类
		spl_autoload_register('MyPHP\init::myload');
		
		// 关闭PHP错误报文,根据自定义方法解析处理
		error_reporting(0);
		register_shutdown_function('Log\error::action_error');
		
		// 加载配置文件,公共函数
		include MyPHP_DIR.'/Common/functions.php';
		C(include MyPHP_DIR.'/Config/config.php');
		
		// 加载自定义公共配置文件,自定义公共函数
		include 'Common/Common/functions.php';
		$_r = glob('Common/Config/*.php');  if($_r){ foreach ($_r as $_v){ C(include $_v); } }

		// 检查是否为首次运行
		if( !is_dir( APP_PATH ) ){
			$zip = new \MyPHP\Zip();
			if( IS_API ){
				$zip->unzip(APP_PATH_DEFALUE_CONTENTS_API, APP_PATH );
			}else{
				$zip->unzip(APP_PATH_DEFALUE_CONTENTS, APP_PATH );
			}
		}

		// 优先加载项目公共核心文件 - 便于自定义路由
		$_file[0] = APP_PATH.'Config/config.php';
		$_file[1] = APP_PATH.'Common/functions.php';
		if(is_file($_file[0])) C( include $_file[0] );
		if(is_file($_file[1])) include $_file[1];

		// url路由
		self::_action_url();

		// 加载当前模块核心文件
		$_file[2] = APP_PATH.MODULE_NAME.'/Config/config.php';
		$_file[3] = APP_PATH.MODULE_NAME.'/Common/functions.php';		
		if( IS_API === true ){
			$_file[2] = APP_PATH.EXT_NAME_API.'/Config/config.php';
			$_file[3] = APP_PATH.EXT_NAME_API.'/Common/functions.php';
		}

		if(is_file($_file[2])) C(include $_file[2]);
		if(is_file($_file[3])) include $_file[3];
		
		// 判断是否开启session功能
		if( C('SESSION_OPEN')  === true ) session_start();
		
		// 加载项目自定义格外首运行文件
		defined('APP_FIRST') ? @include APP_FIRST : '' ;
		
		// 若是debug模式,记录每一次访问记录
		if( APP_DEBUG === true ){
			$r = new \Log\write();
			$r->write(' ');
		}
		
		ob_clean(); //清理缓冲区,避免第三方调用前的影响
		
		// 视图渲染
		self::_show_display();
	}

}