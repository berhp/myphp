<?php 
/**
 * 基础 Api接口
 * @author huangping
 * @tutorial
 *	1.接口不会受伪静态,静态缓存影响，是独立的
 * @eg
 */ 
namespace MyPHP;
class Api{
		
	/**
	 * 【参数】-是否开启API缓存功能
	 * @author berhp 2018.2.2
	 * @var bloor
	 */	
	public static $_Api_isopen_cache = false;
	
	/**
	 * 【容器】-开启API缓存后的有效期,单位秒,请勿赋值
	 * @author berhp 2018.2.2
	 * @var int
	 */
	public static $_Api_cache_time = 60;
	
	/**
	 * 【容器】-用于临时存储接口数据,便于缓存读取存储用,请勿赋值
	 * @author berhp 2018.2.2
	 */
	private static $_Api_cache_data = '';

	/**
	 * Api缓存功能
	 * @author berhp 201
	 * @param no bloor $_Api_isopen_cache 是否开启缓存:( true-开启 false-不开启 ),默认 false
	 * @param no int $_Api_cache_time  缓存有效期,单位秒,默认0秒 (0秒时 - 会以配置C('Api_cache_time')的缓存时间为准,配置默认为60秒)
	 * @return 输出json
	 * @tutorial <设计>
	 * 		1. API接口中通常只有在查询的接口才会开启缓存;  （增,删,改）地方的接口不建议开启缓存
	 * 		2. 在需要的地方直接调用这个方法
	 * 		3. 根据接口地址和参数,判断是否存在缓存,若存在直接读取缓存数据,输出JOSN,程序终止
	 *      4. 若不存在缓存,会继续执行后续程序,在程序尾部完成前,若开启了缓存,则会存储运行结果的JSON数据
	 * @example
	 *    Api\acdd\v1\Controller\Index\IndexApi.class.php
	 *    
	 *    
		<?php 
		namespace acdd\v1\Controller\Index;
		use acdd\v1\Model\IndexModel;
		class IndexApi extends \Api\BaseApiController{   //这里时继承你定义的继承api控制器, 也可以直接继承 \MyPHP\Api   如 class IndexApi extends \MyPHP\Api{
			public $db;
		
			public function __construct(){
				parent::__construct();
				$this->db = new IndexModel();
			}
			
			// 这是一个API缓存DEMO
			public function index(){
			  	C('Api_cache_param_key_true', 'uid');  			// 【临时更改配置】Api数据缓存 - 扩展缓存key参数名,仅一个（ 如 设置uid,则以业务的用户ID的值为特殊key作为扩展储存key ),如 'uid'
			  	C('Api_cache_param_key_false', 'uid,lat,lng');  // 【临时更改配置】Api数据缓存 - 不需要缓存的参数名,多个用","隔开,如 'lat,lng'
				$this->_cache(true, 1200);   	  //★★★此地方接口,开启了缓存,并且设置的缓存时间为1200秒 (若存在缓存,直接输出缓存数据;不存在缓存才会执行后续程序),若缓存时间设置为0 或不传,会根据配置C('Api_cache_time')为准
				$info = $this->db->getXXData();   //你的业务数据
				return $this->jsonOutput($info);  //输出JSON
			}
			
			// 这是一个DEMO 不开启缓存的
			public function deleteXX(){
			  	$info = $this->db->deleteData();
				return $this->jsonOutput($info);
			}
			
		?>
	 *    
	 *    
	 */
	public function _cache( $_Api_isopen_cache=false, $_Api_cache_time=0 ){
		
		self::$_Api_isopen_cache 	= $_Api_isopen_cache;
		self::$_Api_cache_time 		= $_Api_cache_time ? $_Api_cache_time : C('Api_cache_time');
		
		//判断是否在接口缓存中,若存在直接读取,在输出
		if( self::$_Api_isopen_cache === true ){
		 	$_r = self::_get_cache_keyname();
			$_cacheData = S($_r);
			if( $_cacheData ){
				header('Content-Type:application/json; charset=utf-8');
				if( C('Api_is_Cross') === true ){ header('Access-Control-Allow-Origin:*'); }    //授权支持JS跨域访问请求API接口
				return exit( $_cacheData );  //★直接返回输出且程序终止,注意return不能取消,避免继续执行之后的程序
			}
		}
		
	}
	/**
	 * 【内用】-获取api cache的存储key名
	 * @author berhp 2018.2.5
	 * @return string
	 */
	private function _get_cache_keyname(){
		static $cache_keyname = '';  if( $cache_keyname ) return $cache_keyname;  //避免多次重复赋值key名
		$_param_key = '';
		$_param_value = array();
		if( C('Api_cache_param_key_true') ){
			$_Api_cache_param_key_true = C('Api_cache_param_key_true');
			$_param_key = isset($_REQUEST[$_Api_cache_param_key_true]) ? $_REQUEST[$_Api_cache_param_key_true] : '';	
		}
		if( C('Api_cache_param_key_false') ){
			$_param_value = $_REQUEST;
			$_Api_cache_param_key_false = explode(',', C('Api_cache_param_key_false'));
			foreach ($_Api_cache_param_key_false as $v){ 
				unset($_param_value[$v]);
			}
		}else{
			$_param_value = $_REQUEST;
		}
		$cache_keyname = md5('_api_'.$_param_key.$_SERVER['PHP_SELF'].'_param_value:'.json_encode($_param_value));
		return $cache_keyname;
	}
	
	
	/**
	 * 构造
	 * @author berhp 2018.2.5
	 * @tutorial 优先执行自定义接口方法: self::_init();
	 */
	public function __construct(){
		$this->_init();
	}

	/**
	 * 析构
	 * @author berhp 2018.2.5
	 * @tutorial
	 *   若开启API缓存处理时,会在程序结束之前缓存API接口数据
	 */
	public function __destruct(){
		if( self::$_Api_isopen_cache === true ){
			$_r = self::_get_cache_keyname();
			if( S($_r) ){  //若缓存还在有效期内,不在覆盖新数据.
					
			}else{
				S($_r, self::$_Api_cache_data, self::$_Api_cache_time );
			}
		}
	}
	
	/**
	 * 【接口】-控制器优先执行,这里勿写逻辑,由子类来实现做什么
	 */
	public function _init(){}
	
	

	/**
	 * jsonOutput输出
	 * @author berhp 2017.10.11
	 * @version 1.0
	 * @param yes $data  		数据,或 来自 showdata()组装的数组数据
	 * @param no string $msg	给用户看的消息
	 * @param no int $code		code值
	 * @param no array $page	分页信息
	 * @param string $debugMsg	给研发同事看的消息
	 * @return string $json
	 * @tutorial
				$json['pageinfo'] = array(
						'page' 			=> $showdata['page']['page'],  		//当前第几页,默认1
						'pageSize' 		=> $showdata['page']['pageSize'],  	//每页多少条,默认20
						'count' 		=> $showdata['page']['count'],  	//当前页有多少条数据
						'firstRow' 		=> $showdata['page']['firstRow'],  	//起始行数(数据库中)
						'totalRows' 	=> $showdata['page']['totalRows'],  //总条数(满足条件的)
						'totalPages' 	=> $showdata['page']['totalPages'], //总页数(按每页多少条,动态算出的)
						'isMore' 		=> $showdata['page']['isMore'], 	//是否还有下一页 (0-没有 1-有)
				);
		@example
		{"data":{"Welcome":"api demo","Version":"1.0.0","url":"http:\/\/192.168.0.200\/rwxphp\/api.php\/Home\/v1\/Home\/Index\/index"},"msg":"","debugMsg":"","code":0,"page":""}
	 */
	public function jsonOutput( $data, $msg='', $code=0, $page=array(), $debugMsg='' ){
		header('Content-Type:application/json; charset=utf-8');
		if( C('Api_is_Cross') === true ){ header('Access-Control-Allow-Origin:*'); }    //授权支持JS跨域访问请求API接口
		if( isset($data['data']) && isset($data['msg']) && isset($data['code']) ){  //解析showdata()
			$showdata = $data;
			$json = array(
					'data' 		=> $showdata['data'],
					'msg' 		=> $showdata['msg'],
					'code' 		=> $showdata['code'],
					'debugMsg' 	=> $showdata['debugMsg'],
			);
			if( isset($showdata['page']['page']) ){
				$json['pageinfo'] = array(
						'page' 			=> $showdata['page']['page'],
						'pageSize' 		=> $showdata['page']['pageSize'],
						'count' 		=> $showdata['page']['count'],
						'firstRow' 		=> $showdata['page']['firstRow'],
						'totalRows' 	=> $showdata['page']['totalRows'],
						'totalPages' 	=> $showdata['page']['totalPages'],
						'isMore' 		=> $showdata['page']['isMore'],
				);
			}
		}else{ //直接解析
			$json = array( 'data'=>$data, 'msg'=>$msg, 'code'=>$code, 'debugMsg'=>$debugMsg );
			if ( $page ){
				$json['pageinfo'] = array(
						'page' 			=> $page['page'],
						'pageSize' 		=> $page['pageSize'],
						'count' 		=> $page['count'],
						'firstRow' 		=> $page['firstRow'],
						'totalRows' 	=> $page['totalRows'],
						'totalPages' 	=> $page['totalPages'],
						'isMore' 		=> $page['isMore'],
				);
			}
		}
		
		$_r = ( APP_DEBUG === true ) ? self::unicodeString(json_encode($json)) : json_encode($json);
		self::$_Api_cache_data = $_r;
		exit( $_r );
	}
	
	
	/**
	 * unicode 转中文
	 * @param string $str
	 * @param string $encoding
	 * @return mixed
	 * @throws  【设计】:
	 *  若 APP_DEBUG===true 模式,则会转译 json中的中文字符,方便研发人员直接看见json中的中文是什么意思;实例转前,转后的数据如下:
	 *  【转前】:
	 *  {"data":{"uid":"1194","phone":"15882402263","user_name":"\u6d4b\u8bd5hp","spread_pid":"0","sex":"1","card_number":"","names":"\u9ec4\u5e73","is_safeagent":"0","insurance_type":"0","is_spreadagent":"0","spread_type":"8","user_head":"\/Uploads\/head\/22206280859cdad9850bce.jpeg","uid_name":"\u666e\u901a\u7528\u6237","user_platenumber":"\u5dddA88888","user_platenumber_date":"","w_pay":"4","w_check":"3","w_get":"0"},"msg":"","code":0,"debugMsg":""}
	 *  
	 *  【转后】:
	 *  {"data":{"uid":"1194","phone":"15882402263","user_name":"测试hp","spread_pid":"0","sex":"1","card_number":"","names":"黄平","is_safeagent":"0","insurance_type":"0","is_spreadagent":"0","spread_type":"8","user_head":"\/Uploads\/head\/22206280859cdad9850bce.jpeg","uid_name":"普通用户","user_platenumber":"川A88888","user_platenumber_date":"","w_pay":"4","w_check":"3","w_get":"0"},"msg":"","code":0,"debugMsg":""}
	 * 
	 * 
	 * @tutorial 2018.11.20 berhp 更新写法，创建匿名函数-用于回调,便于支持php7.2.1版本写法( 此高版本,废弃了函数create_function() ), 更新源码如下:
		public function unicodeString($str, $encoding=null) {	
			$_fun = function($match){
				return mb_convert_encoding(pack("H*", $match[1]), "utf-8", "UTF-16BE");
			};
			return preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/u', $_fun, $str);
		} 
		
		说明: 创建匿名函数$fun, 用于回调 -2018.11.20 berhp 新建,支持php7.2.1版本; $match的回调时,动态接收的值为数组: 
		array(2) {
		  [0]=>
		  string(6) "\u6d4b"
		  [1]=>
		  string(4) "6d4b"
		}
	* @tutorial 2018.11.20 berhp ★废弃之前的老MyPHP框架的写法,之前的源码为,此老源码在php7.2.1中会报错:
		public function unicodeString($str, $encoding=null) {
			return preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/u', create_function('$match', 'return mb_convert_encoding(pack("H*", $match[1]), "utf-8", "UTF-16BE");'), $str);
		}
	 * 
	 * 
	 */
	public function unicodeString($str, $encoding=null){
		$_fun = function($match){
			return mb_convert_encoding(pack("H*", $match[1]), "utf-8", "UTF-16BE");
		};
		return preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/u', $_fun, $str);
	}


	/**
	 * Ajax方式返回数据到客户端 (ok)
	 * @access protected
	 * @param mixed $data 要返回的数据
	 * @param String $type AJAX返回数据格式
	 * @param int $json_option 传递给json_encode的option参数, true || false
	 * @return void
	 * @throws
	 *   返回JSON申明,可以为 header('Content-Type:text/json; charset=utf-8');
	 */
	protected function ajaxReturn($data,$type='JSON',$json_option=0) {
		if(empty($type)) $type  =   C('DEFAULT_AJAX_RETURN');
		switch (strtoupper($type)){
			case 'JSON' :
				// 返回JSON数据格式到客户端 包含状态信息
				header('Content-Type:application/json; charset=utf-8');
				exit(json_encode($data,$json_option));
			case 'XML'  :
				// 返回xml格式数据
				header('Content-Type:text/xml; charset=utf-8');
				exit(xml_encode($data));
			case 'EVAL' :
				// 返回可执行的js脚本
				header('Content-Type:text/html; charset=utf-8');
				exit($data);
			default     :
		}
	}
	
		
	/**
	 * 【api token机制】-动态生成 token
	 * @author hp 2017.12.21
	 * @version 1.0
	 * @param yes int $uid 用户ID
	 * @return string
	 * @example
	 * 	$model = new \MyPHP\Api;
	 *  $token = $model->create_token();
	 *  var_dump($token);
	 */
	public function create_token( $uid=0 ){
		$sign = self::create_sign( $uid );
		$token_lifetime = C('token_lifetime') ? C('token_lifetime') : 3600;
		$lifetime = time() + $token_lifetime;
		$token = base64_encode("{$uid}_{$lifetime}_{$sign}");
		$filePath = '';
		if( C('token_is_defineTempPath')===true ){
			$filePath = C('token_TempPath') ? C('token_TempPath') : ''; 
		}
		if( C('token_is_SingleSignOn')===true ) S( 'token_'.C('token_key_prefix').$uid, array('time'=>$lifetime,'token'=>$token, 'client_ip'=>$_SERVER['REMOTE_ADDR'] ), $token_lifetime, $filePath );
		return $token;
	}
	
	/**
	 * 【api token机制】-检验api请求token值
	 * @author hp 2017.12.21
	 * @version 1.0
	 * @param yes int $uid 用户ID
	 * @param yes string $token 用户登录成功后回返的token值
	 * @throws 设计
	 * 		1.在需要的地方,可以先检验api token
	 * 		2.若验证不成功,程序终止,输出json
	 * @example
	 * 	$model = new \MyPHP\Api;
	 *  $model->checkApiToken();
	 *  @tutorial  更新：只要token异常都是返回code为2
	 */
	public function checkApiToken(){
		$token_key_prefix = C('token_key_prefix') ? C('token_key_prefix') : '';
		$uid = I('uid', 0);
		$token = I('token', '');
		$r = base64_decode($token); if(!$r)  self::_action_token_error( C('token_msg_901'), 2 );
		$r = explode('_', $r); if(count($r)<>3)  self::_action_token_error( C('token_msg_902'), 2 );
		$_uid = $r[0];  $_time = $r[1];  $_sign = $r[2];
		if($uid<>$_uid)  self::_action_token_error( C('token_msg_903'), 2 );
		if( $_sign != self::create_sign($uid) )  self::_action_token_error( C('token_msg_904'), 2 );
		if( $_time < time() )  self::_action_token_error( C('token_msg_905'), 2);
		if( C('token_is_SingleSignOn') === true ){
			$filePath = '';
			if( C('token_is_defineTempPath')===true ){
				$filePath = C('token_TempPath') ? C('token_TempPath') : '';
			}
			$_stoken = S('token_'.$token_key_prefix.$uid, null, 0, $filePath);
			$_token = $_stoken['token'];
			if( $_time != $_stoken['time'] ) self::_action_token_error( C('token_msg_906')." IP:{$_stoken['client_ip']}", 2);  //单独登录模式-其他地方登录了
			if( $token != $_token ) self::_action_token_error( C('token_msg_904'), 2); 			 //单独登录模式-签名不正确
		}
	}
	
	/**
	 * 【api token机制】-动态生成签名
	 * @author hp 2017.9.30
	 * @version 1.0
	 * @param yes int $uid 用户ID
	 * @return string
	 */
	private function create_sign( $uid=0 ){
		$token_key_prefix = C('token_key_prefix') ? C('token_key_prefix') : '';
		if( $token_key_prefix ){
			$token_key_prefix = str_replace('_', '', $token_key_prefix);
		}
		$_sign = C('token_sign');
		$key = sha1( '_token_'.md5('_token'.$uid.$_sign ) );
		return $token_key_prefix.md5( $key.$_sign );
	}
	/**
	 * token错误 - 程序终止
	 * @param string $msg  错误提示信息
	 * @param number $code 错误code值
	 * @return json
	 */
	private function _action_token_error( $msg='', $code=1 ){
		exit( $this->jsonOutput( showData('', $msg, $code )) );
	}
	
	
	
	

}