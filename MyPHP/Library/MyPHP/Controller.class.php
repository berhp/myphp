<?php 
/**
 * 基础 controller
 * @author huangping
 * @tutorial
 *   优先级：
 *      纯静态缓存功能(若开启的话)  > $this->_init() > $this->display()
 *   备注：
 *      若控制器,不继承基础类,可以跳过此优先级规则
 */ 
namespace MyPHP;
class Controller{
   // 容器,视图传值
    protected $_r=array();

	public function __construct(){
		if( APP_HEMLCACHE===true ) self::_action_tmpl_html_string();
		$this->_init();
	}
	/**
	 * 【接口】-控制器优先执行,这里勿写逻辑,由子类来实现做什么
	 */
	public function _init(){}
	
	/**
	 * 加载视图 (ok)
	 * @param no string $file
	 * @tutorial <pre>
	 *   设计:
	 *   1.根据当前的模块来动态获得当前模块下对应方法的视图文件  
	 *   2.若传参,可获取当前模块下的指定视图文件,注意不用带文件后缀
	 *   3.暂时不提供跨模块的视图文件加载
	 * @tutorial
	 *   伪静态缓存: 默认项目Runtime/Cache目录下,文件夹不存在,会自动创建
	 */
	public function display($file=''){
		$display_open_dirLevel = C('display_open_dirLevel');
		$_fg = ($display_open_dirLevel===true) ? '/' : '_';
		if( !$file ){
			$_file = APP_PATH.MODULE_NAME.'/View/'.CONTROLLER_NAME.$_fg.ACTION_NAME.APP_VIEW_FILE_EXT;
		}else{
			$_file = APP_PATH.MODULE_NAME.'/View/'.$file.APP_VIEW_FILE_EXT;
		}

		// 方式一 直接引用加载渲染（更偏向于源生写法）
		//return include $_file;

		// 方式二 伪静态缓存加载渲染（会自动处理视图中的自定义常量,自定标签等等）
		self::_action_tmpl_cache_string($_file);



	}
	
	
	/**
	 * 自定义变量传值到视图 (ok)
	 */
	public function assign($key,$value){
	   $this->_r[$key] = $value;
	}
	
	/**
	 * 原样输出
	 * @param string $str
	 * @link http://php.net/manual/en/language.types.string.php#language.types.string.syntax.heredoc
	 * @tutorial
		（<<<）标记符了，这是在PHP的模板代码中可以见到的。后面的关键字为自定义,需成双出现
		<<<EOT
		EOT;
		中间的文档直接输出，一个比较好理解的说法是“一个多行的echo ”。
		优点是输出大段HTML方便，不需要转义，而且可以引用变量.
	 */
	public function show($str=''){
		$_str = <<<EOD
$str
EOD;
		echo ($_str);
	}

	/**
	 * 错误信息提示  (ok)
	 * @tutorial 弹出错误信息，若设置了$url会跳转, $time是多少秒后跳转
	 * 		//$str = "<script type='text/javascript'>alert('$msg');window.location.href='$url'</script>"; exit($str);
	 * @param string $msg			提现信息
	 * @param string $jumpUrl		跳转URL地址,如 U('Home/Index/index') 或 http://xx.xx.xx/
	 * @param string $waitSecond   等待时间,单位秒
	 * @param bool|false $isAjax   是否为ajax返回,默认 false
	 * @totrial
	//模板设置
		'template' => array(
		'filePath' => APP_TEMPLATE_PATH, //模板所在目录，默认为 MYPHP框架下的 Template中
		'404' 		=> '404.tpl', 		//404错误文件, 常量 APP_TEMPLATE_PATH 指定的目录下,如 404.tpl'
		'error' 	=> 'error.tpl', 	//错误提示模版页
		'success' 	=> 'success.tpl',  //成功提示模版页
		'isUse' 	=> false, 			//是否启动模板生成, 默认false-不使用, true-使用 (若项目下模板名文件夹不存在,会根据模板名动态生成后台数据库与界面)
		'name' 	=> '', 				   //模板名
		),
	 */
	public function error($msg='', $jumpUrl='', $waitSecond='3',$isAjax=false){
		if($isAjax===false){
			$_is_history_go = $jumpUrl ? false : true;
			include C('template.filePath').C('template.error');  exit();
		}else{
			$data = array(
					'data'=> array('url'=>$jumpUrl),
					'msg'=> $msg,
					'code'=> 1,
			);
			self::ajaxReturn($data,'JSON');
		}
	}

	/**
	 * 成功信息提示  (ok)
	 */
	public function success($msg='', $jumpUrl='', $waitSecond='3',$isAjax=false){
		if($isAjax===false){
			$_is_history_go = $jumpUrl ? false : true;
			include C('template.filePath').C('template.success');  exit();
		}else{
			$data = array(
					'data'=> array('url'=>$jumpUrl),
					'msg'=> $msg,
					'code'=> 0,
			);
			self::ajaxReturn($data,'JSON');
		}
	}

	/**
	 * 重定向URL跳转  (ok)
	 * @tutorial 设计：立即跳转
	 */
	public function redirect($url=''){
		header("Location:$url"); exit();
	}


	/**
	 * Ajax方式返回数据到客户端 (ok)
	 * @access protected
	 * @param mixed $data 要返回的数据
	 * @param String $type AJAX返回数据格式
	 * @param int $json_option 传递给json_encode的option参数, true || false
	 * @return void
	 */
	protected function ajaxReturn($data,$type='JSON',$json_option=0) {
		if( C('Api_is_Cross') === true ){ header('Access-Control-Allow-Origin:*'); }    //授权支持JS跨域访问请求API接口
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
	 * 【内用】-纯静态处理-动态生成$_GET[$token]=$value （OK）
	 * @return array
	 * @example
		Array
		(
		    [0] => _c36ad7834f312c7dbb0a01308a24b5f6         //动态生成的key
		    [1] => 2e90e88b7c2934546020c2f20a5527f9dc8dfc37  //动态生成的value
		)
	 */
	private function _create_token(){
		$_sign = 'berhp'; 				//自定义加密签名
		$key = '_token_'.md5('_token'.date('Ymd').$_sign);
		$value = sha1(md5($key));
		return array(
				0 => $key,
				1 => $value,
		);
	}
	
		
	/**
	 * 【内用】-处理纯静态 (ok)
	 * @tutorial
	 * 	         核心：
	 * 		1.判断常量  APP_HEMLCACHE 是否已开启纯静态缓存功能,开启后才会处理 
	 * 		2.★动态判断$_GET[$_token] 值,是否为有效的,若有效直接调用伪静态缓存,便于输出纯静态内容
	 *     设计:根据当前 url地址 + 含GET传参,转换为小写后在动态生成 纯html文件
	 *     1. 若存在, 判断纯静态文件是否过期,若过期 重新创建， 反之 直接加载纯静态文件 (规则: 当前时间戳-纯静态文件修改时间戳),若源文件进行调整,立即更新纯静态缓存
	 *     2. 不存在,以md5( strtolower($_SERVER['REQUEST_URI']) ).'.html' 创建纯静态文件
	 */
	private function _action_tmpl_html_string(){
		#检查不需要纯静态缓存的
		$check = C('html.no_cache');
		if($check){
			$_r= strtolower(MODULE_NAME.'_'.CONTROLLER_NAME.'_'.ACTION_NAME);
			foreach($check as $v){
				if( $_r== strtolower($v) ) return;
			}
		}
		#特殊情况,内调用生成纯静态缓存
		$check = self::_create_token();
		if( isset( $_GET[$check[0]] ) ){
			if( $_GET[$check[0]] == $check[1] ){
				return;
			}else{
				die('非法操作');
			}
		}
		#其他常规情况,检查是否存在纯静态文件并加载
		$_cache_dir = C('html.filePath');
		is_dir($_cache_dir) or mkdir($_cache_dir, 0755,true);
		$_cache_file = md5( strtolower($_SERVER['REQUEST_URI']) );
		$_filename = $_cache_dir.$_cache_file.'.html';
		if( !is_file( $_filename ) ){
				self::_create_html($_filename );
		}else{
				$c_filemtime = C('html.filemtime');
				if( ( time()-filemtime($_filename) ) > $c_filemtime )  self::_create_html( $_filename );
				include $_filename; exit;
		}
	}

	/**
	 * 【内用】-创建纯静态缓存文件
	 * @param string $_filename 静态文件存放路径,如 Application/Runtime/Html/7d6b53c7429ce0d921d020b80774e8a7.html
	 * @return boolean
	 * @tutorial
	 *   ★核心： 需要在php.ini将 user_agent="PHP" 的前面注释';'去掉, 使用默认的PHP去伪装浏览器请求URL地址获取页面内容
	 * 
	 *  //$url = 'http://192.168.0.200:80/myphp/home/index/index/p/2.html?_token_c36ad7834f312c7dbb0a01308a24b5f6=f0f0780b2f0a79f4b1d0abd84cfabebd3d563e17';
	 *  设计: 根据当前的完整url地址与参数,拼接创建出token内部调用http生成纯静态文件
	 *  核心: url全部转换为小写
	 *  
	 * @tutorial <pre> 在框架内废除,不使用以下的常规方法,不适应框架动态生成纯静态文件:
		$_file = 'test.php';
		ob_start();
		include $_file;
		$info = ob_get_contents();
		ob_end_clean();
		ob_flush();
		echo $info;
	 *  
	 */
	private function _create_html( $_filename ){
		$_token = self::_create_token();
		$_tokenstr = $_token[0].'='.$_token[1];
		$_http = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
		$_url = $_http.$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'].$_SERVER['REQUEST_URI'];
		$_url .= strpos($_url,'?') ? '&'.$_tokenstr : '?'.$_tokenstr;

		$content = file_get_contents($_url);
		$r = file_put_contents($_filename, $content);
		if(!$r) return false;
		return true;
	}



	/**
	 * 【内用】-处理伪静态 (ok)
	 * @tutorial 不存在伪静态缓存,则创建,存在了,先检测是否已过期，没有过期则直接使用  (规则: 源文件修改时间戳-伪静态缓存文件修改时间戳 )
	 * @return boolean
	 */
	private function _action_tmpl_cache_string($_file=''){
		$_cache_dir = C('cache.filePath');
		is_dir($_cache_dir) or mkdir($_cache_dir, 0755,true);
		$_cache_file = md5( strtolower($_file) ); //★统一转换为小写
		$_filename = $_cache_dir.$_cache_file;
		if( !is_file( $_filename ) ){
				self::_create_cache( $_file, $_filename );
		}else{
			$c_filemtime = C('cache.filemtime');
			if( ( filemtime($_file)-filemtime($_filename) ) > $c_filemtime ){}   self::_create_cache( $_file, $_filename );
		}
		
		
		//动态设置变量存数据
		foreach($this->_r as $k=>$v){
		   $$k = $v;
		}

		include $_filename;
	}


	/**
	 * 【内用】-创建伪静态缓存文件 (ok)
	 * @param $_file 原文件路径
	 * @param $_filename 新文件存放路径
	 * @return boolean
	 */
	private function _create_cache( $_file, $_filename ){
		set_time_limit(0);
		$content = file_get_contents( $_file );
		//自定义 HTML 常量替换
		$content = self::_action_replace($content);
		//自定义 HTML 标签替换与解析
		$model = new \MyPHP\Template\HtmlTemplate(  $content );
		$content = $model->getstr();
		$this->_r = array_merge($this->_r, $model->getdata()); //动态创建获取值

		$r = file_put_contents( $_filename, $content);
		if($r===false) return false;
		return true;
	}
	
	
	/**
	 * 【内用】-自定义魔板的字符串替换处理
	 * @param yes string $str 原字符串内容
	 * @tutorial
	 *    设计： 伪静态 /静态缓存的时候，都会经过此方法
	 * @return string
	 */
	private function _action_replace($str=''){
		# 默认预定替换内容
		$replace =  array(
			'__URL__'      =>  __URL__,       // 当前网站url地址
			'__ROOT__'      =>  __ROOT__,       // 当前网站地址
			'__PUBLIC__'    =>  __PUBLIC__,		// 站点公共目录
			'__UPLOADS__'    =>  __UPLOADS__,	// 上传文件目录
		);
		# 允许用户自定义模板的字符串替换
		if(is_array(C('TMPL_PARSE_STRING')) )  $replace =  array_merge($replace,C('TMPL_PARSE_STRING'));
		return str_replace(array_keys($replace),array_values($replace),$str);
	}
	
	


	
}