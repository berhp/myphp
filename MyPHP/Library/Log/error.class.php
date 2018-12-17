<?php 
/**
 * 错误信息收集
 * @author huangping
 * @tutorial APP_BUG == true 才会记录运行
 */
namespace Log;
class error{
	
	/**
	 * 自定义错误处理
	 * @link http://php.net/manual/zh/errorfunc.constants.php
	 * type值:
			1		E_ERROR	运行时致命的错误。不能修复的错误。终止执行脚本。
			2		E_WARNING	运行时非致命的错误。不终止执行脚本。
			4		E_PARSE	编译时语法解析错误。解析错误仅仅由分析器产生。
			8		E_NOTICE	运行时通知。表示脚本遇到可能会表现为错误的情况，但是在可以正常运行的脚本里面也可能会有类似的通知。
			16		E_CORE_ERROR	在 PHP 初始化启动过程中发生的致命错误。该错误类似 E_ERROR，但是是由 PHP 引擎核心产生的。
			32		E_CORE_WARNING	PHP 初始化启动过程中发生的警告 (非致命错误) 。类似 E_WARNING，但是是由 PHP 引擎核心产生的。
			64		E_COMPILE_ERROR	致命编译时错误。类似 E_ERROR, 但是是由 Zend 脚本引擎产生的。
			128		E_COMPILE_WARNING	编译时警告 (非致命错误)。类似 E_WARNING，但是是由 Zend 脚本引擎产生的。
			256		E_USER_ERROR	用户产生的错误信息。类似 E_ERROR, 但是是由用户自己在代码中使用PHP函数 trigger_error()来产生的。
			512		E_USER_WARNING	用户产生的警告信息。类似 E_WARNING, 但是是由用户自己在代码中使用 PHP 函数 trigger_error() 来产生的。
			1024	E_USER_NOTICE	用户产生的通知信息。类似 E_NOTICE, 但是是由用户自己在代码中使用 PHP 函数 trigger_error() 来产生的。
			2048	E_STRICT	启用 PHP 对代码的修改建议，以确保代码具有最佳的互操作性和向前兼容性。
			4096	E_RECOVERABLE_ERROR	可被捕捉的致命错误。它表示发生了一个可能非常危险的错误，但是还没有导致 PHP 引擎处于不稳定的状态。 如果该错误没有被用户自定义句柄捕获 (参见 set_error_handler())，将成为一个 E_ERROR 从而脚本会终止运行。
			8192	E_DEPRECATED	运行时通知。启用后将会对在未来版本中可能无法正常工作的代码给出警告。
			16384	E_USER_DEPRECATED	用户产生的警告信息。类似 E_DEPRECATED, 但是是由用户自己在代码中使用 PHP 函数 trigger_error() 来产生的。
			32767	E_ALL	E_STRICT 除非的所有错误和警告信息。
	 */
	static public function action_error(){
		$_error = error_get_last(); 						//获取错误信息
		if($_error){
			if( APP_DEBUG === true ){
				defined('MyPHP_DIR')?p($_error):var_dump($_error);

				$msg = '[error_file]:'.$_error['file'].' 第'.$_error['line']."行\n";
				$msg .= '[error_type]:'.$_error['type']."\n";
				$msg .= '[error_message]:'.$_error['message'];
				$log = new \Log\write();
				$r = $log->write($msg);
				exit;
			}else{  // 运营模式,仅记录致命错误
				if( in_array( $_error['type'], array(2,512) ) ){  //非致命错误,继续运行: E_WARNING, E_USER_WARNING

				}else{
					if( defined('MyPHP_DIR') ){  //只有在框架内才写log记录
						$msg = '[error_file]:'.$_error['file'].' 第'.$_error['line']."行\n";
						$msg .= '[error_type]:'.$_error['type']."\n";
						$msg .= '[error_message]:'.$_error['message'];
						$log = new \Log\write();
						$r = $log->write($msg);
					}
					exit;
				}
			}
		}
	}


}