<?php 
/**
 * api 错误信息基础类
 * @author huangping
 * @tutorial
 * 支持多国语言,关键词language 如 $_GET['language']=zh
 * zh-中文(默认) en-英文
 */
namespace Api;
class Msg{
	/**
	 * 【参数】-支持的语言
	 */
	static protected $_language = array('zh','en');
	/**
	 * 【参数】-默认语言
	 */
	static protected $_language_default = 'zh';
	
	/**
	 * 根据语言动态返回错误提示
	 * @param string $key
	 * @return string
	 */
	static public function get($key=''){
		$language = isset($_GET['language']) ? $_GET['language'] : self::$_language_default;
		if(!in_array($language, self::$_language )) return '';
		$_fun = 'msgdata_'.$language;
		$msg = self::$$_fun;
		return isset($msg[$key]) ? $msg[$key] : '';
	}
	
	
	/**
	 * 自定义错误提示信息(中文)
	 * @var array $k=>$v
	 * @example
	 * 4开头(客户端错误) 5开头(服务端错误) 9开头(api token错误信息)  以'_'开头的表示必填写字段
	 */
	static public $msgdata_zh = array(
			400 => '用户不存在',
			500 => '服务器错误',
			'_phone' 		=> '请输入电话号码',
	);
	
	
	/**
	 * 自定义错误提示信息(英文)
	 * @var array $k=>$v
	 * @example
	 * 4开头(客户端错误) 5开头(服务端错误) 9开头(api token错误信息)  以'_'开头的表示必填写字段
	 */
	static public $msgdata_en = array(
			400 => 'user does not exist',
			
			500 => 'Server error',
			
			901 => 'Token parsing error',
			902 => 'Token error',
			903 => 'Token user does not match',
			904 => 'The token signature is incorrect',
			905 => 'Token is invalid. Please log in again',
			906 => 'Token is invalid. Your account is logged on elsewhere. Please log in again',

			'_phone' => 'Please enter your phone number',
	);

	
	
	
}