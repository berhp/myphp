<?php 
namespace Log;
class get{
	
	/**
	 * 状态报文
	 */
	static function stats(){
		self::_action();
		
		$r = \Log\get::get_defined_constants();
		ksort($r);  //以数组key升序排列.
		p($r);
		
		//p(\Log\get::get_defined_constants());  //按默认排列
		p(\Log\get::get_included_files());
		//p($_REQUEST);
		//p($_SERVER);
		p($GLOBALS);
	}
	
	/**
	 * 获取所有已加载文件
	 * @return array 一维数组,文件路径集
	 * @example <pre><font color=blue>
		Array(
		    [0] => E:\www\www\index.php
		    [1] => E:\www\www\Frame\MyPHP\MyPHP.php
		)
	 */
	static function get_included_files(){
		return get_included_files();
	}

	/**
	 * 获取所有自定义常量 与 值
	 * @param boolean $isUser 	        是否仅返回用户自定义的常量, 默认 true
	 * @return array 多维数组,user为用户自定义,其他的为.dll扩展相关定义的常量
	 * @example <pre>
		Array
		(
		    [APP_FRAME] => MyPHP
		    [APP_FRAME_PATH] => ./Frame/MyPHP/MyPHP.php
		    [APP_DEBUG] => 1
		    [APP_PATH] => ./Application/
		    [IS_GET] => 1
		    [IS_POST] => 
		    [APP_CLASS_FILE_EXT] => .class.php
		    [VVA] => VVASS
		    [VVAB] => Array
		        (
		            [0] => 1
		            [1] => 2
		            [2] => aa
		        )
		)
	 */
	static function get_defined_constants( $isUser=true ){
		$r = get_defined_constants(true);
		if( $isUser ){
			return $r['user'];
		}else{
			return $r;
		}
	}
	

	/**
	 * 获取所有已定义的函数名
	 * @param no boolean $isUser  是否仅获取来自用户定义的
	 * @param no boolean $isInternal  是否仅获取来自PHP源方法,扩展中 定义的函数名
	 * @return array 一维/二维数组
	 * @example
	 *   获取所有,返回二维数组,  \Log\get::get_defined_functions();
	 *   获取用户的,返回一维数组,  \Log\get::get_defined_functions(true);
	 *   获取PHP源系统的,返回一维数组,  \Log\get::get_defined_functions(false,true);
	 */
	static function get_defined_functions( $isUser=false, $isInternal=false ){
		$r = get_defined_functions();
		if( $isUser === true ) return $r['user'];
		if( $isInternal === true ) return $r['internal']; 
		return $r;
	}
		
	/**
	 * 【内用】-计算 当前共耗时 和 内存使用情况
	 * @tutorial
	 *   记录的开始毫秒,与 开始时的内存,在框架 初文件 init.class.php中
	 */
	private static function _action(){
		$GLOBALS['_myRunInfo']['over_time'] = microtime(TRUE);
		$GLOBALS['_myRunInfo']['over_usage'] = memory_get_usage();
		$GLOBALS['_myRunInfo']['offset_time'] = round(( $GLOBALS['_myRunInfo']['over_time'] - $GLOBALS['_myRunInfo']['start_time'] ),4) .'s';  //当前共耗时
		$GLOBALS['_myRunInfo']['offset_usage'] = round(( $GLOBALS['_myRunInfo']['over_usage'] - $GLOBALS['_myRunInfo']['start_usage'] )/1024 , 2) .'kb'; //当前共耗内存
	}
	
	
}