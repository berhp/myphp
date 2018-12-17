<?php 
/**
 * 极光推送 核心类
 * @author huangping
 * 极光推送文档：
	http://docs.jiguang.cn/jpush/server/push/rest_api_v3_push/

    php server sdk 下载地址:
    http://docs.jiguang.cn/jpush/resources/
 
	推送地址： json + post 
	https://api.jpush.cn/v3/push
	
	@example
	//先设置$config-框架外使用
	$config=array('app_key'=>'','master_secret'=>'');  //设置你对应的appkey和secret 来推给用户端和司机端
	\MyPHP\Msg\Jpush::setConfig( $config );  		   //切换到xxAPP推送
	 
	//先设置$config-框架内时,可支持以下格式:
	$config=array('app_key'=>'','master_secret'=>'');
	C('msg_jpush', $config);							//C配置,可以直接放在config.php中 如 'msg_jpush'=>array('app_key'=>'demo','master_secret'=>'demo')
	\MyPHP\Msg\Jpush::setConfig();  		   			//切换到xxAPP推送	

	//例如
		//自定义$data消息体
		$data = array(
				'type' => 11,
				'content' => array(
					'orderson' => 's12018040810300646742907586956'
				),
		);
		$uid ='1194';
		$topmsg ='topmsgdemo';
		\MyPHP\Msg\Jpush::setConfig( C('msg_jpush_driver') );  //从 C('msg_jpush')中获取,DEMO时为直接向司机发送推送
//		$r = \MyPHP\Msg\Jpush::pushMsg( $data ); 		 //广播-所有用户 OK
		$r = \MyPHP\Msg\Jpush::pushMsg( $data, $uid, $topmsg );   //指定用户
		var_dump($r);
		var_dump(\MyPHP\Msg\Jpush::$msg_error);
		
 */
namespace MyPHP\Msg;
class Jpush{
	/**
	 * 是否是生成环境
	 * @var boolean true为生成环境  false为开发环境
	 */
	static private $apns_production = false;
	static private $url 			= "https://api.jpush.cn/v3/push";    //推送的地址
	
	/**
	 * 【容器】 $app_key, $master_secret  -勿设置,后续程序处理
	 * @var unknown
	 */
	static private $app_key 		= '';
	static private $master_secret  	= '';
	
	
	/**
	 * 【容器】-若调极光推送发送失败时的错误信息 -勿设置,后续程序处理
	 */
	static public $msg_error  	= '';
	
	
	/**
	 * 【容器】-推送失败错误信息(来源极光文档)
	 * @url http://docs.jiguang.cn/jpush/server/push/rest_api_v3_push/#api
	 */
	static private $msg_code = array(
			'1000' =>'系统内部错误',
			'1001' =>'只支持 HTTP Post方法',
			'1002' =>'缺少了必须的参数',
			'1003' =>'参数值不合法',
			'1004' =>'验证失败',
			'1005' =>'消息体太大',
			'1008' =>'app_key参数非法',
			'1009' =>'推送对象中有不支持的key',
			'1020' =>'只支持 HTTPS 请求',
			'1030' =>'内部服务超时',
			'2002' =>'API调用频率超出该应用的限制',
			'2003' =>'该应用appkey已被限制调用 API',
			'2004' =>'无权限执行当前操作',
			'2005' =>'信息发送量超出合理范围',
	);
	
	/**
	 * Curl post请求
	 * @param string $postUrl
	 * @param string $param
	 * @param string $header
	 * @return boolean|mixed
	 */
	static private function push_curl($postUrl="", $param="",$header="") {
		if (empty($param)) { return false; }
		$curlPost = $param;
		$ch = curl_init();                                    // 初始化curl
		curl_setopt($ch, CURLOPT_URL, $postUrl);              // 抓取指定网页
		curl_setopt($ch, CURLOPT_HEADER, 0);                  // 设置header
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);          // 要求结果为字符串且输出到屏幕上
		curl_setopt($ch, CURLOPT_POST, 1);                    // post提交方式
		curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);        // 增加 HTTP Header（头）里的字段
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);      // 终止从服务端进行验证
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		$data = curl_exec($ch);                               // 运行curl
		curl_close($ch);
		return $data;
	}
	
	/**
	 * 构建header头文件 (ok)
	 * @return array
	 */
	static private function createHeader(){
		if( self::$app_key == '' &&  self::$master_secret == '' ){
			self::setConfig();
		}
		$base64 = base64_encode(  self::$app_key.':'. self::$master_secret );
		return array("Authorization:Basic $base64","Content-Type:application/json");
	}
	
	
	/**
	 * 【业务功能】 - 切换配置 （如切换到司机端推送)
	 * @author berhp 2018.4.13
	 * @param no array $config=array('app_key'=>'','master_secret'=>'','apns_production'=>false);  配置文件,非必传,若没有的时候会读取C('msg_jpush')
	 */
	static public function setConfig( $config=array() ){
		$app_key = '';  $master_secret = '';
		if($config){
			$app_key 		= isset($config['app_key']) 		? $config['app_key'] : '';
			$master_secret 	= isset($config['master_secret']) 	? $config['master_secret'] : '';
			$apns_production = isset($config['apns_production']) ? $config['apns_production'] : false;
		}else{
			$config = C('msg_jpush');
			$app_key 		= isset($config['app_key']) 		? $config['app_key'] : '';
			$master_secret 	= isset($config['master_secret']) 	? $config['master_secret'] : '';
			$apns_production = isset($config['apns_production']) ? $config['apns_production'] : false;
		}
		self::$app_key 			= $app_key;
		self::$master_secret 	= $master_secret;
		self::$apns_production 	= $apns_production;
	}
	
	
	/**
	 * 【内用】 - 获取错误详细中文意思
	 * @author berhp 2018.4.13
	 * @return string
	 */
	static private function getErrorZh( $code='' ){
		$str = isset(self::$msg_code[$code]) ? self::$msg_code[$code] : '';
		return $str;
	}
	
	
	/**
	 *  【项目业务功能】- 方便,避免重复自己组装别名集合
	 *
	 * @tutorial  根据数据库查询出来的数据,组装只有别名ID集,用于方便激光‘别名推送’
	 * @param yes array $data  从数据库查询出来的多维数组
	 * @param no  string $key  标示为别名的表字段,以它为依据进行重组, 默认为 id
	 * @return array  一维数组, 如 array(1,2,3)
	 * @example
	 *   $r = 	\Msg\Jpush::actionids( array('0'=>array('uid'=>1,'username'=>'张三'),'1'=>array('uid'=>2,'username'=>'李四')), 'uid');
	 *   print_r($r);
	 */
	static public function actionids($data=array(), $key='id'){
		$r = array();
		if(!$data) 	return $r;
		if(!$key) 	return $r;
		foreach( $data as $k=>$v ){
			if( isset($v[$key]) )  $r[] = $v[$key];
		}
		return $r;
	}

	/**
	 * 【极光推送】-推送消息
	 * @author berhp 2018.4.13
	 * @version 1.0
	 * @param yes string|array $msg_content 	消息体,支持字符串或数组,如"hello",如 array('type'=>1,'msg'=>'hello')
	 * @param no  string|array $audience   		推送给谁,默认"all"-所有; 如 "all"-推送所有  "1"-仅推送给别名1  array('1')-也仅推送给别名1  array('1','10')-推送给别名为"1","10";    '1,10,20'-也推送给别名为"1","10","20"
	 * @param no  string  	   $alert			手机顶部通知栏内容,默认""-没有内容
	 * @param no  int  	       $time_to_live	离线消息保留时长,单位(秒),默认1天  (若设置为0,当前设备不在线,就收不到消息,若设置后,在有效时间内,设备在连接上的时候可以收到)
	 * @return boolean  ture-推送成功  false-推送失败（失败时 可以调它来查看失败原因  $error_msg = \MyPHP\Msg\Jpush::$msg_error;  echo $error_msg;)
	 * @tutorial 
	 * 		1, 根据极光文档二次封装了常用推送方式
	 * 		2, 极光只支持JOSN+POST方式请求接口,进行推送消息
	 * 		3, 请求推送接口前,需要构建头文件,与激光推送需求的头规则一致,才能接口请求成功。
	 *      4, 设备别名,通常与APP客户端沟通,都取值用户uid
	 * @example
	 	 $uid = I('uid', '');  $msg = I('msg', '');
		 $config = array(
		 	'app_key' 			=> 'd84134cc33bd38535f193861',
		 	'master_secret' 	=> 'e2d1bac857337aedecabb04a',
		 );
		 $data = array(
		 	'type' => 'demo_driver',
		 	'msg'  => $msg,
		 	'uid'  => $uid,
		 );
		
		 //指定用户方式一 OK
		 //$uids = array( $uid );
		
		 //指定用户方式二 OK
		 $uids = array( 0=>$uid );
		
		 		\MyPHP\Msg\Jpush::setConfig( $config );
		 //		\MyPHP\Msg\Jpush::setConfig();  //从 C('msg_jpush')中获取
		 $r = \MyPHP\Msg\Jpush::pushMsg($data);   //广播-所有用户 OK
		 //		$r = \MyPHP\Msg\Jpush::pushMsg($data, $uids);  //指定用户-OK
		 var_dump($r);  //true-发送成功 false-发送失败
		 var_dump(\MyPHP\Msg\Jpush::$msg_error); //打印详细的错误信息,只有失败时才有 string
	 * 
	 */
	static public function pushMsg( $msg_content="", $audience="all", $alert="", $time_to_live=86400  ){
		$data = array();
		
		#推送平台:android,ios,winphone,all
		$data['platform'] = 'all'; 					  //推送到所有平台
//		$data['platform'] = array("android", "ios");  //仅推送到安卓和IOS平台

		#推送目标
//		$data['audience'] = 'all'; //推送全部设置
//		$data['audience']['alias'] = array("4314","892","4531"); //推送别名-(最多一次1000个;这里为推送到别名为4314,892,4531的设备上)
		if( is_string($audience) ){
			if( $audience == 'all' ){
						$data['audience'] = 'all';
			}else{
				if(strpos($audience,',')===false){
						$data['audience']['alias'] = array($audience);
				}else{
						$data['audience']['alias'] = explode(',', $audience);
				}
			}
		}else{
			$data['audience']['alias'] = $audience;
		}
		
		#通知
		if(strlen($alert)){
			$data['notification']['alert'] = $alert;  //手机的顶部通知栏内容,若为空字符串,则没有顶部栏通知
			
			//★因Ios那边是 极光转发至苹果官网apns服务器,然后苹果那边在返回至ios app客户端,为了2个端都可以从['message']中取字段,固我们业务特殊封装了此处内容。
			$data['notification']['ios'] = array(
					"alert"  => $alert,
					"extras" => array(  //★★★★自定义业务封装-便于Ios那边通知栏自定义参数,从苹果官方过来时,从消息体.message中取, 
							"message" => array(
								'msg_content' 	=> $msg_content,
								'title' 		=> '',
								'content_type' 	=> '',
								'extras' 		=> array(),
							)
					),	
			);
			
		}

		#自定义消息-(此部分内容不会展示到通知栏上,JPush SDK收到消息内容后透传给 App,需要 App自行处理)
		$data['message'] = array(
			'msg_content' 	=> $msg_content, 		//★必填,消息内容本身 - (一般APP中只解析这里,服务端传数组（内含了消息的type),客户端解析这里后判断是什么消息,在动态处理自定义的消息内容)
			'title' 		=> '', 					//可选,消息标题
			'content_type' 	=> '', 					//可选,消息内容类型
			'extras' 		=> array(), 			//可选,自定义扩展参数,如array('key1'=>'value1','key2'=>'value2')
		);
		
		#可选参数
		$data["options"] = array(
				"sendno"			=> time(), 					//int,可选,推送序号
				"time_to_live"		=> (int)$time_to_live, 			//int,可选,离线消息保留时长(秒),如“86400”为1天,最长10天,设置为0表示不保留离线消息,只有推送当前在线的用户可以收到
				"apns_production"	=> self::$apns_production,  //boolean,APNs是否生产环境(ture-推送生成环境, false-推送开发环境)
		);
		$param = json_encode($data);
		$url = self::$url;
		$header = self::createHeader();
		$res = self::push_curl( $url, $param, $header );
		//var_dump($res);  //请求失败时: string(87) "{"error":{"code":1003,"message":"time_to_live value should be a non-negative integer"}}";    请求成功时:string(52) "{"sendno":"1523524710","msg_id":"36028798170465673"}"
		$redata = @json_decode($res, true);
		if(isset($redata['error'])){
			self::$msg_error = $redata['error']['code'].':'.self::getErrorZh($redata['error']['code']).','.$redata['error']['message'];
			return false;
		}else{
			return true;
		}
	}	
	
}