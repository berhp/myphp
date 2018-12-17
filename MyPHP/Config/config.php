<?php 
/**
 * 框架基础配置
 */
return array(
	//默认当前数据库
	'db' => array(
			'DB_TYPE'               => 'mysqli',		// 数据库类型
			'DB_HOST'               => '127.0.0.1', 	// 服务器地址
			'DB_NAME'               => '35tf',  		// 数据库名
			'DB_USER'               => 'root',  		// 用户名
			'DB_PWD'                => 'root',  		// 密码
			'DB_PORT'               => '3306',  		// 端口
			'DB_PREFIX'             => '', 				// 数据库表前缀
			'DB_CHARSET' 			=> 'utf8', 			// 数据库编码
	),
	//子数据库(分库设计才需要)
	'db2' => array(
			'DB_TYPE'               => 'mysqli',
			'DB_HOST'               => '127.0.0.1',
			'DB_NAME'               => '', 				//需要根据当前用户动态赋值-数据库,如 35tf_oa_1
			'DB_USER'               => 'root',
			'DB_PWD'                => 'root',
			'DB_PORT'               => '3306',
			'DB_PREFIX'             => '35tf_',
			'DB_CHARSET' 			=> 'utf8',
	),
	//主数据库(分库设计才需要)
	'db3' => array(
			'DB_TYPE'               => 'mysqli',
			'DB_HOST'               => '127.0.0.1',
			'DB_NAME'               => '35tf',
			'DB_USER'               => 'root',
			'DB_PWD'                => 'root',
			'DB_PORT'               => '3306',
			'DB_PREFIX'             => '',
			'DB_CHARSET' 			=> 'utf8',
	),
	
	/* Api接口设置 */
	'Api_is_Cross' 		=> false, 		// Api是否支持JS跨域请求:( true-支持JS跨域请求  false-不支持JS跨域请求 ), 默认 false
	'Api_cache_time' 	=> 60, 			// Api数据缓存有效时间,单位秒 (若 没有特定缓存时间,则读取这个配置时间来默认缓存)
	'Api_cache_param_key_true'  => '', 		// Api数据缓存 - 扩展缓存key参数名,仅一个（ 如 设置uid,则以业务的用户ID的值为特殊key作为扩展储存key ),如 'uid'
	'Api_cache_param_key_false' => '',		// Api数据缓存 - 不需要缓存的参数名,多个用","隔开,如 'lat,lng'
	

	/* Cookie设置 */
	'COOKIE_EXPIRE'         => 0,    	// Coodie有效期
	'COOKIE_DOMAIN'         => '',      // Cookie有效域名
	'COOKIE_PATH'           => '/',     // Cookie路径
	'COOKIE_PREFIX'         => '',      // Cookie前缀 避免冲突
	
	/* SESSION设置 */
	'SESSION_OPEN' 			=> false,  //是否开启session,默认false 不开启（有些地方是不需要的)

	'DEFAULT_AJAX_RETURN'   => 'EVAL', 	//定义默认的ajax返回方式,支持:JSON,XML,EVAL(返回可执行的js脚本)

	/* 上传配置  */
	'upload' => array(
			'maxSize'       =>  0, //上传的文件大小限制 单位M (0-不做限制)
			'exts'          =>  array('jpeg','jpg','png','gif','bmp','txt','sql'), //允许上传的文件后缀
			'savePath'      =>  'Uploads/', //保存路径,相对于单入口index.php,★linux系统下也是'/',用的是相对路径
			'saveName'      =>  '', 		//新的文件名
			'replace'       =>  false, 		//存在同名是否覆盖
			'is_oldname' 	=>  false, 		//是否保留原名
			'is_prefix' 	=>  true, 		//是否开启自定义图片地址返回前缀, 若开启,会在 生成图片后的路径前面加上自定义前缀,便于特殊情况存入数据库
			'prefix_str' 	=>  __ROOT__.'/', 	//自定义前缀值,默认为框架的项目根目录文件夹名
	),

	/* 验证码图片设置 */
	'code' => array(
			'width' 		=> 150, 		//图片宽,单位px
			'height' 		=> 50, 			//图片高,单位px
			'background' 	=> '#FFFFFF', 	//背景颜色,默认白色
			'font_size' 	=> 18, 			//字体大小,单位px
			'font_margin' 	=> 5, 			//间距 ,单位px
			'fontfile' 		=> MyPHP_DIR.'/Fonts/elephant.ttf', //字体路径,如 elephant.ttf,若是外部引用,填写index.php的相对路径
			'code_len' 		=> 6, 			//验证码长度
			'code_str' 		=> '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz', //随机字符集
			'is_imagearc' 	=> true, 		//是否画弧线
			'is_imagestring' 	=> true, 	//是否画自定义字符
			'imagestring_str'   => '.', 	//自定义字符,不支持中文
			'imagestring_font' => 10, 		//字符大小,单位px
			'imagestring_count' => 10, 		//字符出现次数
			'session_name'  => 'code', 		//自定义-验证码session存放名名称
			'is_matchcase' => false, 		//是否区分大小写,ture-区分,默认false-不区分(session会存放小写的): strtolower($code)
	),

	/* 水印设置 */
	'shuiyin' => array(
			'image_name' 			=> 'shui_', 		//生成水印后的图片名称前缀
			'image_path' 			=> 'Uploads/', 		//新图片的保存目录,''未配置,为当前目录下, 如 img/  ★若不存在目录,需要先手动创建目录
			'image_padding_x' 	=> 100, 				//水印,x相对间距,单位px
			'image_padding_y' 	=> 100, 				//水印,y相对间距,单位px
			'image_merge_type' 	=> 'lt', 				//方式,默认:lt- 从左上顶部开始,坐标(0,0), lb-左下底部   rt-右上顶部   rb-右下底部
			'image_opacity' 		=> 60, 				//水印透明度 (0~100)
			'image_watermarkfile' => 'Public/b.png', 	//水印图片本地路径,如 b.png ★不支持外网http图片
	),

	/* 微缩图裁切设置 */
	'caijian' => array(
			'image_name' 			=> 's_', 		//裁剪图片名称前缀
			'image_path' 			=> 'Uploads/', 	//裁剪后的保存目录,''未配置,为当前目录下,★若不存在目录,需要先手动创建目录
			'image_width' 			=> 150, 			//裁剪图片-宽,单位px
			'image_height' 			=> 100, 			//裁剪图片-高,单位px
			'is_merge' 				=> false, 			//是否精确裁剪,默认false-不（按百分比缩放裁剪）  true-是
			'image_merge_type' 		=> 'center', 		//方式,只有开启精确裁剪才会生效: 默认center-居中裁剪    lt- 从左上顶部开始,坐标(0,0), lb-左下底部   rt-右上顶部   rb-右下底部
			'is_retain' 			=> false,			//是否需要保留原图,默认false-不保留 true-保留
			'memory_limit' 			=> '-1',			//裁剪时的内存设置,如-1表示无限大,其他时候,如512M 表示最大为512M,请准守 ini_set('memory_limit','512M')) 函数用法
	),

	/* 伪静态缓存设置 */
	'cache' => array(
			'filePath'  => APP_RUNTIME_PATH.'Cache/', 	//存放目录,默认项目Runtime/Cache目录下,文件夹不存在,会自动创建
			'filemtime' => 0, 							//允许的伪静态延迟更新时间,单位秒,默认0,只要源文件一修改,立即更新对应的伪静态缓存
	),

	/* 伪静态模板-格外自定义常量设置:$k=>$v 如 '__public__'=> 'ssx' */
	'TMPL_PARSE_STRING' => array( ),

	/* 纯静态缓存设置 */
	'html' => array(
			'filePath' => APP_RUNTIME_PATH.'Html/', //存放目录,默认项目Runtime/Html目录下,文件夹不存在,会自动创建
			'filemtime' => 60, 						//允许的纯静态文件的有效时间,单位秒,若过期,重新生成纯静态缓存
			'no_cache' => array(),  				//不需要纯静态缓存的,不用区分大小写,'模块名_控制器名_方法名' 如 array('home_Index_code','Home_index_login')
	),

	/* Logs错误日志设置 */
	'log' => array(
			'filePrefix' 	=>'log_',  	//生成的文件前缀,非必须
			'ext' 			=>'.txt', 	//文件后缀,非必须,如.txt
			'filePath' 	=> APP_RUNTIME_PATH.'Logs/',  //存放目录,相对于网站index.php目录,默认为项目Runtime/Logs目录下,文件夹不存在,会自动创建
			'fileSize' 	=>8,			//文件大小,单位MB,超过了会自动创建新的文件，超过10M Notepad++ 不可以打开
			'timezone_set' => 'PRC',	//时区,默认北京8点
	),

	/* S方法文件缓存设置 */
	'temp' => array(
			//'filePath' 	=> APP_RUNTIME_PATH.'Temp/',  //存放目录,相对于网站index.php目录,默认为项目Runtime/Temp目录下
			'filePath' 	=> SITE_DIR.APP_RUNTIME_PATH.'Temp/',  //存放目录,磁盘路径
	),
	
	/* display设置 */
	'display_open_dirLevel' => false, 	//是否开启目录层次(默认false-不 true-开启),若开启 加载路径为  View/XX/xx.html; 不开启,加载路径为  View/XX_xx.html

	/* 模板设置 */
	'template' => array(
			'filePath' => APP_TEMPLATE_PATH, 			//模板所在目录，默认为 MYPHP框架下的 Template中
			'404' 		=> '404.tpl', 		//404错误文件, 常量 APP_TEMPLATE_PATH 指定的目录下,如 404.tpl'
			'error' 	=> 'error.tpl', 	//错误提示模版页
			'success' 	=> 'success.tpl', //成功提示模版页
			'isUse' 	=> false, 			//是否启动模板生成, 默认false-不使用, true-使用 (若项目下模板名文件夹不存在,会根据模板名动态生成后台数据库与界面)
			'name' 	=> '', 				//模板名
	),
	
	/* 自动加载类设置 */
	'is_spl_autoload_registerOther' => false,  //是否队列继续加载第三方的自动加载类(true-可以 false-不可以),默认 false,当MyPHP框架+项目内类文件不存在,则终止了,若使用第三方类,需要临时配置此参数为true
	
	
	/* 邮件设置  */
	'email' => array(
	    'EMAIL_NICKNAME' 		=> 'huangping', 			//自定义发件人昵称
		'EMAIL_FROM_NAME' 	=> '343217456@qq.com',   // *发件人邮箱
		'EMAIL_SMTP' 			=> 'smtp.qq.com',   	  // *smtp
		'EMAIL_USERNAME' 		=> '343217456@qq.com',   // *账号
		'EMAIL_PASSWORD' 		=> 'vyaolecttfombgef',   // *密码  注意: 163和QQ邮箱是授权码；不是登录的密码
		'EMAIL_SMTP_SECURE' 	=> 'ssl',   			  // 链接方式 如果使用QQ邮箱；需要把此项改为  ssl
		'EMAIL_PORT' 			=> '465', 	  			  // *端口 如果使用QQ邮箱；需要把此项改为  465  或 995
		'EMAIL_CHARSET' 		=> "UTF-8", 			  // *邮件字符集
		'EMAIL_ENCODING' 		=> "base64", 			  // *邮件编码方式
	),

	/*
	'email' => array(
			'EMAIL_NICKNAME' 		=> 'berhp', //自定义发件人昵称
			'EMAIL_FROM_NAME' 	=> 'm15882402263_1@163.com',   // *发件人邮箱
			'EMAIL_SMTP' 			=> 'smtp.163.com',   	  // *smtp
			'EMAIL_USERNAME' 		=> 'm15882402263_1@163.com',   // *账号
			'EMAIL_PASSWORD' 		=> 'AS123CX213DF',   // *密码  注意: 163和QQ邮箱是授权码；不是登录的密码
			'EMAIL_SMTP_SECURE' 	=> '',   			  // 链接方式 如果使用QQ邮箱；需要把此项改为  ssl
			'EMAIL_PORT' 			=> '25', 	  			  // *端口 如果使用QQ邮箱；需要把此项改为  465  或 995
			'EMAIL_CHARSET' 		=> "UTF-8", 			  // *邮件字符集
			'EMAIL_ENCODING' 		=> "base64", 			  // *邮件编码方式
	),
	*/
	
	/* 地图AK设置 */
	'baidu_ak' => 'RCog7P3aO0OZohFBaYvaRZmp2Duzzqp8',

	
	/**
	 * 消息-激光推送设置
	 * @author berhp 2018.4.9
	 * @tutorial 
	 * 激光推送文档
	 *   http://docs.jiguang.cn/jpush/server/push/rest_api_v3_push/
	 * 说明: APP客户端从激光推送下载SDK,然后配置app_key即可; master_secret仅服务器配置调接口发送推送用  
	 */
	'msg_jpush' => array(
			'app_key' 			=> 'eae49a097c87c0a0add02268',
			'master_secret' 	=> 'f891ede25160bf64eb22d47d',
			'apns_production'   => true, 	//推送 true-正式环境 flase-开发环境
	),
	
		
	/**
	 * token验证机制设置
	 * @author berhp 2017.12.21
	 * 
	 * @tutorial
	 *  \MyPHP\Api::create_token();  创建token,在使用
	 *  \MyPHP\Api::checkApiToken(); 校验token,在使用
	 *
	 * @example
	 *   【生成token值】
		    C('token_lifetime', 100); 			//设置有效期为100秒
		    C('token_is_SingleSignOn', true); 	//开启单点登录模式
		    //C('token_key_prefix', 'demoprefix'); 	//设置token前缀,若设置了,那么在验证token模块下也要对应设置前缀。  设置前缀的好处: 如 QQ 已登录 ,用QQ号去登录游戏,那么QQ那边不会被踢开,但是 若重复其他地方登录QQ,之前的QQ则被踢开
		    
		    $uid = '1';
		    $model = new \MyPHP\Api();
		    $token = $model->create_token( $uid );
		    var_dump($token);
	 *   
	 *   【校验】:假设请求地址为http://xx.xx.xx/xx?uid=1&token=MV8xNTEzODQzOTY3X2YyMDY3ZTRhZGM5ZDUwMWU3ZDhjODc3YWYzYzRkMDAw
		   $model = new \MyPHP\Api();
		   $check = $model->checkApiToken();  //若验证不成功,这里已经exit出,返回JSON信息
		   echo 'ok';  //验证成功,才会执行这里的
		   
	 */
	'token_sign' 	 => 'rwx_team', 		//token - 自定义生成token时的程序签名值
	'token_lifetime' => 7200*12*30,  		//token - 自定义有效时间,单位秒( 用户登录之后的 )
		
	'token_is_SingleSignOn' => false, 		//token - ★是否开启,单点登录模式(true-开启 false-关闭); 若开启,第2次登录token生成后,之前旧立即失效 ★单点登录模式,采用S文件缓存方法; 非单点登录模式不会缓存token
	'token_key_prefix' => '', 				//token - 自定义key前缀,生成token_sign 与 开启单点登录模式动态S文件key前缀 都是根据此值动态处理
	
	'token_is_defineTempPath' => true, 		//token - ★是否指定token存放目录(true-开启 false-关闭):默认开启,会根据 token_TempPath配置的目录来存放,且网站所有地方都可以进行单点登录token效验;  若 不开启,会根据C('temp.filePath')的项目Runtime/Temp目录下,且单点登录token效验仅在局部的项目模块下
	'token_TempPath' => '_tokenTemp/', 		//token - 自定义指定token统一存放目录,默认在网站根目录的_tokenTemp/目录下
	
	'token_msg_901' => 'token解析错误',  		//token - 自定义提示语: 校验,加密方式不正确时
	'token_msg_902' => 'token错误', 			//token - 自定义提示语: 校验,加密格式不正确时
	'token_msg_903' => 'token用户不匹配', 	//token - 自定义提示语: 校验,当前访问传参的$uid 和 加密时的 $uid 两者不匹配时
	'token_msg_904' => 'token签名不正确',  	//token - 自定义提示语: 校验,sign签名不正确时
	'token_msg_905' => 'token失效,请重新登录',						//token - 自定义提示语: 校验,sign有效期失效时
	'token_msg_906' => 'token失效,请重新登录,您的账号在其他地方登录',		//token - 自定义提示语: 开启单点登录,其他地方登录时,之前的token都失效的用户那边提示语。

	/**
	 * 随机密码生成设置
	 * @tutorial
	 *  m_createpass() 创建随机密码,提供的方法中在使用 
	 *  m_checkpass()  验证密码,提供的方法中在使用
	 */
	'safe_sign' => '5673a3b9185d6e77f869c4935feab100', 		//自定义-随机密码生成的签名值	

	/** ** ** 以下为您业务中自定义配置,适用于所有模块项目中 ** ** **/
	//'demo_key'=>'demo_value', 	//也可以直接在,网站根目录的Common/Config/ 目录下进行自定义配置;
	
);