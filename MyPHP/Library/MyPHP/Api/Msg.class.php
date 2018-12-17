<?php 
/**
 * api 错误信息基础类  demo
 * @author huangping
 *
 */
namespace MyPHP\Api;
class Msg{
	
	/**
	 * 返回错误提示
	 * @param string $key
	 * @return string
	 */
	static public function get($key=''){
		$msg = self::$msgdata;
		return isset($msg[$key]) ? $msg[$key] : '';
	}
	
	
	/**
	 * 自定义错误提示信息
	 * @var array $k=>$v
	 * @example
	 * 4开头(客户端错误) 5开头(服务端错误) 9开头(api token错误信息)  以'_'开头的表示必填写字段
	 */
	static public $msgdata = array(
			100 => '需要必填参数XX',
			'_default_city' => '该城市尚未开通服务,敬请期待',
	);

	
	
	
}