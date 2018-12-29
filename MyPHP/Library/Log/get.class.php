<?php 
namespace Log;
class get{
	
	/**
	 * 状态报文
	 * @author berhp 2018.12.28
	 * @example 
		//运行状态打印
		\Log\get::stats();
	 */
	static function stats(){
		p(">>> 当前内存使用情况:". '\Log\get::get_memory_usage()');
		self::_action();
		p($GLOBALS['_myRunInfo']);
		
		
		p(">>> 当前动态加载的文件列表:");
		p(\Log\get::get_included_files());
		
		
		p(">>> 当前动态加载的自定义所有常量:");
		$r = \Log\get::get_defined_constants();  //按默认排列
		ksort($r);  							 //以数组key升序排列. @link: http://www.php.net/manual/zh/array.sorting.php
		p($r);
		

		p(">>> 当前动态加载的自定义所有函数列表:");
		$r = \Log\get::get_defined_functions(true);
		array_multisort($r); 					//以数组value升序排列
		p($r);
		
		
		p(">>> 更多信息:");
		p($GLOBALS);
		//p($_REQUEST);
		//p($_SERVER);

		
		p(">>> phpinfo信息:");
		phpinfo();
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
	 * @example
		Array
		(
		    [start_time] => 1546061287.2202     //框架内,init.class.php中最早记录时的时间戳
		    [start_usage] => 340152  			//框架内,最早记录时的内存使用情况
		    [over_time] => 1546061350.9328      //此次内调时,时间戳
		    [over_usage] => 4390392 			//此次内调时,总内存使用情况
		    [offset_time] => 63.7126s 			//此次与最早的运行时间对比,
		    [offset_usage] => 3955.31kb			//此次与最早的内存使用对比
		    [last_offset_time] => 37.5611s  	//此次与上一次内调时的运行时间对比
		    [last_offset_usage] => 1722.08kb 	//此次与上一次内调时的内存使用对比
		)
	 */
	private static function _action(){
		if(isset($GLOBALS['_myRunInfo']['over_time'])){
			$GLOBALS['_myRunInfo']['last_offset_time'] = round( ( microtime(TRUE) - (double)$GLOBALS['_myRunInfo']['over_time'] ), 4).'s';
		}
		if(isset($GLOBALS['_myRunInfo']['over_usage'])){
			$GLOBALS['_myRunInfo']['last_offset_usage'] = round( ( memory_get_usage() - (double)$GLOBALS['_myRunInfo']['over_usage'] )/1024 , 2).'kb';
		}
		$GLOBALS['_myRunInfo']['over_time'] = microtime(TRUE);
		$GLOBALS['_myRunInfo']['over_usage'] = memory_get_usage();
		$GLOBALS['_myRunInfo']['offset_time'] = round(( $GLOBALS['_myRunInfo']['over_time'] - $GLOBALS['_myRunInfo']['start_time'] ),4) .'s';  //当前共耗时
		$GLOBALS['_myRunInfo']['offset_usage'] = round(( $GLOBALS['_myRunInfo']['over_usage'] - $GLOBALS['_myRunInfo']['start_usage'] )/1024 , 2) .'kb'; //当前共耗内存
	}
	
	
	/**
	 * 【外用】-获取运行至当前内存使用情况
	 * @example
		\Log\get::get_memory_usage();  //打印当前的
		$r=array();
		for($i=0;  $i<=100000;  $i++){
			$r[] = $i;
		}
		\Log\get::get_memory_usage();  //打印最后的 
		
		@tutorial 对比结果如: for()循环10万,共耗时为 0.038-0.013=0.025s,共耗内存为13831.3-305.66=13525.64kb 即13525.64/1024=13.2086mb
		Array
		(
		    [start_time] => 1545980559.2417
		    [start_usage] => 340152
		    [over_time] => 1545980559.2547
		    [over_usage] => 653144
		    [offset_time] => 0.013s
		    [offset_usage] => 305.66kb
		)
		Array
		(
		    [start_time] => 1545980559.2417
		    [start_usage] => 340152
		    [over_time] => 1545980559.2797
		    [over_usage] => 14503400
		    [offset_time] => 0.038s
		    [offset_usage] => 13831.3kb
		)
	 */
	public static function get_memory_usage(){
		self::_action();
		p($GLOBALS['_myRunInfo']);
	} 
	
	
	
	
	
}