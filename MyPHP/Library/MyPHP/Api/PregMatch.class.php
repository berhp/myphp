<?php 
/**
 * api 正则验证基础类 demo
 * @author huangping
 *
 */
namespace MyPHP\Api;
class PregMatch{

	/**
	 * 正则检查 
	 * @param yes string $string 要检查的字符串 
	 * @param no string $key   正则检查的key名
	 * @return showData()  code为1时,表示正则未通过,并返回错误信息
	 */
	static public function check( $string='', $key='' ){
		$check = self::get($key);  if(!$check) return showData('');
		if( !preg_match( $check[0], $string  ) ){
			return showData('', $check[1], 1);
		}
		return showData('');
	}

	
	/**
	 * 获取正则规则数据
	 */
	static public function get($key=''){
		$pregdata = self::$pregdata;
		return isset($pregdata[$key]) ? $pregdata[$key] : array();
	}
	
	/**
	 * 自定义正则规则
	 * @var array $k=>$v
	 * @example
	 *    'phone' => array('/^[0-9]{11}$/', '请正确输入11位手机号码'),
	 */
	static public $pregdata = array(
			'phone' 		=> array('/^[0-9]{11}$/', '请正确输入11位手机号码'),
	);
	
	

	
}