<?php 
    /**
     * P方法 - 浏览器友好输出 (OK)
     * @author berhp
     */
    function P($data){
    	$output = print_r($data, true);
    	$output = '<pre>' . htmlspecialchars($output) . '</pre>';
    	print $output;
    }
    
    /**
     * unicode 转中文 (OK)
     * @param string $str
     * @param string $encoding
     * @return mixed
     * @author berhp
     * 
     */
    function unicodeString($str, $encoding=null) {
    	return preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/u', create_function('$match', 'return mb_convert_encoding(pack("H*", $match[1]), "utf-8", "UTF-16BE");'), $str);
    }
    
    /**
     * jsonOutput输出 (OK)
     * @author berhp
     * @param yes $data  //返回的数据,数组,字符串,对象,其他
     * @param string no $msg //给用户看的消息提示
     * @param int no $code //0-正常 1-错误 2-需要用户重登录
     * @param array no $page  //分页信息
     * @param string no $debugMsg //给研发人员看的详细错误信息
     * 
     */
    function jsonOutput($data, $msg='', $code=0, $page=array(), $debugMsg='',$CallBackURL=''){
		header('Content-Type:application/json; charset=utf-8');
		if( C('Api_is_Cross') === true ){ header('Access-Control-Allow-Origin:*'); }    //授权支持JS跨域访问请求API接口
		if( isset($data['data']) && isset($data['msg']) && isset($data['code']) ){  //解析showdata()
			$showdata = $data;
			$json = array(
				'data' 		=> $showdata['data'],
				'msg' 		    => $showdata['msg'],
				'code' 		=> $showdata['code'],
				'debugMsg' 	=> $showdata['debugMsg'],
				'CallBackURL' => isset($showdata['CallBackURL']) ? $showdata['CallBackURL'] : '',
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
			$json = array( 'data'=>$data, 'msg'=>$msg, 'code'=>$code, 'debugMsg'=>$debugMsg, 'CallBackURL' => $CallBackURL  );
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
		echo( json_encode($json) );
		exit;
    }
    
    /**
     * page 分页函数 (OK)
	 * @param int yes $total - 总共有多少条数据
	 * @param int no $page   - 当前第几页,默认1
	 * @param int no $pageSize - 每页多少条数据,默认20
 	 * @return array 
 	 * @tutorial
 	 *  1. 当前页小于1时,当前页为 1
 	 *  2. 当前页大于总页数时,当前页为总页数
     */
    function page( $total=0, $page=0, $pageSize=0 ){
    	$r = array();
    	if(!$page){
    		$page = isset( $_REQUEST['page'] ) ? (int)$_REQUEST['page'] : 1;
    	}
    	if(!$pageSize){
    		$pageSize = isset( $_REQUEST['pageSize'] ) ? (int)$_REQUEST['pageSize'] : 20;
    	}
    	$totalPages = ceil( $total/$pageSize );
    	if(!$totalPages) $totalPages =1;
    	if($page<1) $page=1;
		if($page>$totalPages){
			$count = 0;
		}else{
			$count = ($page<$totalPages) ? $pageSize : ($total-($page-1)*$pageSize);
		}
    	$firstRow = ($page==1) ? 0 : ($page-1)*$pageSize;
    	$isMore = ($page<$totalPages)?1:0;
    	$r = array(
    		'page' 			=> $page,
    		'pageSize' 		=> $pageSize,
    		'count' 		=> $count,
    		'firstRow' 		=> $firstRow,
    		'totalRows' 	=> $total,
    		'totalPages' 	=> $totalPages,
    		'isMore' 		=> $isMore,
    		'limit' 		=> $firstRow.','.$pageSize,  //提供给后端sql语句拼接limit
    	);
    	return $r;
    }
    
    /**
     * pc分页
     * @author berhp 2018.2.23
     * @tutorial 设计(动态附加上参数),内调page(),新增返回字段show
     * @param yes number $total     总共多少条数据
     * @param no number $offsetpage  中间提供多少页
     * @param no boolean $is_default_css 是否需要默认的css样式 true-需要生成  false-不需要, 默认 false
     * @return array 
     * @example
     * $page = pageShow( 50, 6, true );
     * echo $page['show'];
     * @throws
		<style>
		.Page a{
			-border:1px solid #ccc;
			padding:2px;
			margin:0 3px;
		}
		.Page a:link {
		 text-decoration: none;
		}
		.Page a:visited {
		 text-decoration: none;
		}
		.Page a:hover {
		 text-decoration: none;
		}
		.Page a:active {
		 text-decoration: none;
		}
		.Page .NumberPage{
			padding:0 3px;
		}
		.Page .NowPage{
			background-color:#cc9900;
		}
		.Page .defaultPageInput{
			width:50px;
		}
		</style>
		<div class='Page'>
		<a>共n条</a>
		<a>n/n页</a>
		<a href='' class='FirstPage'>首页</a>
		<a href='' class='LastPage'>上一页</a>
		<a href='' class='NumberPage'>2</a>
		<a href='' class='NumberPage NowPage'>3</a>
		<a href='' class='NumberPage'>422</a>
		<a href='' class='NextPage'>下一页</a>
		<a href='' class='OverPage'>尾页</a>
		<a class='defaultPage'>第<input type='number' min='1' class='defaultPageInput' onblur='mdefaultPageInput(this.value)'>页</a>
		</div>
		<script>
		function mdefaultPageInput(page){
		 var url='http://www.baidu.com/';
		 if(page) window.location.href=url+'?page='+page;
		}
		</script>
     */
    function pageShow( $total=0, $offsetpage=5, $is_default_css=false ){
    	$r = page($total);
    	$show = '';  $param = $_REQUEST;
		$str='';
		if(!empty($param)){
			if(isset($param['page'])){
				$page=$param['page'];
				unset($param['page']);
			}
			foreach($param as $k=>$v){
				if(is_array($v)){
					foreach($v as $vol){
						$str.='&'.$k.'[]='.$vol;
					}
				}else{
					$str.='&'.$k.'='.$v;
				}
			}
			$str=substr($str,1);
		}
    	$_FirstPage_url = U('').'?'.$str.'&page=1';
		$pre=$r['page']-1;
    	$_LastPage_url = $str==''?U('').'?page='.$pre:U('').'?'.$str.'&page='.$pre;
		$next=$r['page']+1;
    	$_NextPage_url =$str==''?U('').'?page='.$next:U('').'?'.$str.'&page='.$next;
    	$_OverPage_url =U('').'?'.$str.'&page='.$r['totalPages'];
    	$_defaultPageInput_url =$str==''?U('').'?page=':U('').'?'.$str.'&page=';
    	if($is_default_css===true){
    		$show .= "
		    	<style>
				.Page a{
					-border:1px solid #ccc;
					padding:2px;
					margin:0 3px;
				}
				.Page a:link {
				 text-decoration: none;
				}
				.Page a:visited {
				 text-decoration: none;
				}
				.Page a:hover {
				 text-decoration: none;
				}
				.Page a:active {
				 text-decoration: none;
				}
				.Page .NumberPage{
					padding:0 3px;
				}
				.Page .NowPage{
					background-color:#cc9900;
				}
				.Page .defaultPageInput{
					width:50px;
				}
    			.Page .nothing{
    				color:#ccc;
    			}
				</style>
    		";
    	}
    	$show .= '<div class="Page">';
    	$show .= "<a>共{$r['totalRows']}条</a>";
    	$show .= "<a>{$r['page']}/{$r['totalPages']}页</a>";
    	$show .= "<a href='{$_FirstPage_url}' class='FirstPage'>首页</a>";
    	$show .= (($r['page']-1)<1)?"<a class='nothing'>上一页</a>":"<a href='{$_LastPage_url}' class='LastPage'>上一页</a>";
    	#处理中间页数-开始↓↓
    	if( $offsetpage>0 ){
    		$_offset =  floor($offsetpage/2);
    		if( ($r['page']-$_offset)<1 ){
    			$i = 1;
    			$i_max = $i+$offsetpage;
    		}else{
    			$i = $r['page']-$_offset;
    			$i_max = $r['page']+$_offset;
    		}
    		
    		if( $i_max > $r['totalPages'] ){
    			$i = ($r['totalPages'] - $offsetpage)>1 ? (($r['totalPages'] - $offsetpage)+1):1;
    			$i_max = $r['totalPages'];
    		}
    		$y=0;  $_NumberPage_url='';
    		for($i; $i<=$i_max; $i++){
    			$y++;  if($y>$offsetpage) break;
    			$_NumberPage_url = $str==''?U('').'?page='.$i:U('').'?'.$str.'&page='.$i;
    			if($i==$r['page']){ //中间为当前页
    				$show .= "<a href='{$_NumberPage_url}' class='NumberPage NowPage'>{$i}</a>";
    			}else{
    				$show .= "<a href='{$_NumberPage_url}' class='NumberPage'>{$i}</a>";
    			}
    		}
    	}
    	#处理中间页数-结束↑↑
    	$show .= (($r['page']+1)>$r['totalPages'])?"<a class='nothing'>下一页</a>":"<a href='{$_NextPage_url}' class='NextPage'>下一页</a>";
    	$show .= "<a href='{$_OverPage_url}' class='OverPage'>尾页</a>";
    	$show .= 
<<<EOD
		<a class="defaultPage">第<input type="number" min="1" max="{$r['totalPages']}" class="defaultPageInput" onblur="mdefaultPageInput(this.value)">页</a>
		</div>
		<script>
		function mdefaultPageInput(page){
		 var url="{$_defaultPageInput_url}";
		 if(page) window.location.href=url+page;
		}
		</script>
EOD;
    	$r['show'] = $show;
    	return $r;
    }
    
    

/**
 * M方法
 * @param yes string $name  表名
 * @param no bool $isaction  若为false,不会判断和拼接表前缀
 * @param no array $db_config  数据库配置,若不传,则按C('db')配置
 * @return unknown
 */
function M($name='', $isaction=true, $db_config=array()) {
	$db_config = $db_config ? $db_config : C('db');
	if( $isaction === true ){
		$tablePrefix = $db_config['DB_PREFIX']; //表前缀
		if($tablePrefix){
			if( strpos($name, $tablePrefix) === false ){
				$name = $tablePrefix . $name;
			}			
		}
	}
    //$class = '\DB\\'.$db_config['DB_TYPE'];  // \db\mysql
    $res = new \DB\mysqli( $db_config );
    $res->table = $name;
    return $res;
}


/**
 * S方法 - 文件缓存 (ok)
 * @author berhp
 * @param yes string $k 缓存的$key,支持中文
 * @param no string $v 缓存的值,不传,则是读取; 传了则是生成
 * @param no int $time 缓存的有效期,单位秒
 * @param no string $filePath 缓存目录,若不传会读取配置C('temp.filePath');默认在xx模块的/Runtime/Temp 目录下; 若传了,则以传的为准,相对于网站根目录
 * @tutorial
 *    $k=>$v  严谨区分大小写,$v不支持资源null
 *    00000000s:this is string demo
 * @example
	$s = false;
	S('t',$s,20);
	$r = S('t');
	var_dump($r);
 */
function S( $k='', $v=null, $time=0, $filePath='' ){
  $i_max = 8; //支持的缓存时间位数，单位秒
  $dir= $filePath ? $filePath : C('temp.filePath');
  is_dir($dir) or mkdir($dir,0755,true);
  if(!$k) return null;
  $v_type = substr(gettype($v),0,1);
   //echo gettype($v); //string integer double boolean array object NULL
   //非NULL类型, 都会原样存储下来,支持特殊存储:空字符串,空数组,空对象,布尔型,整型(0),浮点型(0.0)
  $file = $dir.md5($k);
  if( $v_type != 'N' ){
    $i_k=$i_max-strlen($time);
    for($i=0;$i<$i_k;$i++){
       $time = '0'.$time;
    }
    switch($v_type){
      case 'b':
      $content = ($v===true)?1:0;  
      break;
      case 'a':
      case 'o':
      $content = json_encode($v);
      break;
      default:
      $content = $v;
    }
      return file_put_contents($file, $time.$v_type.':'.$content);
  }
  
  //若存在缓存，超时清除缓存，反之读取数据
  if(!is_file($file)) return null;
  $filemtime = filemtime($file);
 $filecontents=file_get_contents($file);
  $filemtime_max = (int)mb_substr($filecontents,0,$i_max,'utf-8');
  
  if((time()-$filemtime)>$filemtime_max){
    unlink($file);
    return null;
  }
  $file_type=mb_substr($filecontents,$i_max,1,'utf-8');
  $file_cache=mb_substr($filecontents,$i_max+2, mb_strlen($filecontents,'utf-8')-$i_max-2,'utf-8');
   switch( $file_type ){
      case 's':
        return (string)$file_cache;
        break;
      case 'i':
        return (int)$file_cache;
        break;
      case 'd':
        return (double)$file_cache;
        break;
      case 'b':
        return ($file_cache==1)?true:false;  
      break;
      case 'a':
        return json_decode($file_cache,true);
        break;
      case 'o':
        return json_decode($file_cache);
        break;
      default:
        return $file_cache;
    }
    return null;
}

/**
 * U方法 [ok]
 * @author berhp
 * @param no string $path 如'Home/index/index',不传参,默认为当前的
 * @param no string||array $param 传参(若为字符串,直接拼接参数,如?a=2&b=4)
 * @return string $url
 * @example
	U(); 					//如 http://127.0.0.1/index.php/Home/index/index
	U('','?name=1&pass=2'); //如 http://127.0.0.1/index.php/Home/index/index?name=1&pass=2
	U('',array('name'=>'11','pass'=>'22')); //如 http://127.0.0.1/index.php/Home/index/index/name/11/pass/22
	U('Admin/index/login'); //如 http://127.0.0.1/index.php/Admin/index/login
	U('Home/index/login/a/1/b/2');  //另外的方式
   @example
     <a href='<?php echo U('Admin/index/login') ?>'>demo</a>
     
     <!-- $data 为PHP变量值 -->
     <a href="{:U('aa/bb/cc', array('xx'=>$data))}">demo</a>
 */
function U($path='', $param=''){
	$_http = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
	$url =  $_http.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'];
	if(!$path){
		$url .= '/'.MODULE_NAME.'/'.CONTROLLER_NAME.'/'.ACTION_NAME; 
	}else{
		$url .= '/'.$path;
	}
	if($param){
		if(is_string($param)) $url .= $param;
		if(is_array($param)){
			foreach($param as $k=>$v){
				$url .= '/'.$k.'/'.$v;
			}
		}
	}
	return $url;
}

/**
 * I方法 - 数据过滤 [ok]
 * @author berhp
 * @param yes string $key
 * @param no mixed $value  默认赋值
 * @param no string $filter 用什么方法过滤,默认自定义_mysafe方法(过滤sql,过滤js); 若不想进行过滤,传空字符串
 * @param no array $preg_replaceData 正则替换规则数据,若  $filter='preg_replace'时,此参数必传:如,把a替换为A $preg_replaceData =array('/a/', 'A');
 * 
 * @return mixed || string || array
 * @tutorial
 * eg:
 *  I('get.');  	 //过滤并获取所有$_GET  
 *  I('post.name');  //仅过滤并获取$_POST['name']
 * @tutorial <pre>
 * 设计:
 *   $filter = ''          不进行过滤
 *   $filter = '_mysafe'    过滤SQL， 过滤JS   默认为它
 *   $filter = 'preg_replace' 进行正则替换过滤
 *   $filter = ★其他未预定的方法, 尝试检查源生或自定义的方法是否已设置,若不存在,则不进行过滤! 如 addslashes() 如C(); 调用时,仅需赋值 $filter='addslashes'
 * @tutorial
 print_r($_COOKIE);
 print_r($_ENV);		//环境的变量,不常用,若要用,先将php.ini 中的 variables_order = "GPCS" 修改为 "EGPCS"
 print_r($_FILES);		//上传的信息
 print_r($_GET);
 print_r($_POST);
 print_r($_REQUEST);
 print_r($_SERVER);
 print_r($_SESSION);
   @tutorial  最原初的 $r = file_get_contents("php://input")  这种传值没有进行处理, 因客户端传值类型是随意的,有可能传值为xml, json 或 base64 ..
 */
function I($key='', $value='', $filter='_mysafe', $preg_replaceData=array() ){
	$type = array('cookie','env','files','get','post','request','server','session');
	$key = explode('.', $key);
	if( count($key)>1 ){
		switch($key[0]){
			case 'cookie': 	$data =  $key[1] ? ( isset($_COOKIE[$key[1]]) 	? $_COOKIE[$key[1]] : $_COOKIE[$key[1]]=$value ) : $_COOKIE; break;
			case 'session':	$data =  $key[1] ? ( isset($_SESSION[$key[1]]) 	? $_SESSION[$key[1]] : $_SESSION[$key[1]]=$value ) : $_SESSION; break;
			case 'get':		$data =  $key[1] ? ( isset($_GET[$key[1]]) 		? $_GET[$key[1]] : $_GET[$key[1]]=$value ) : $_GET; break;
			case 'post':	$data =  $key[1] ? ( isset($_POST[$key[1]]) 	? $_POST[$key[1]] : $_POST[$key[1]]=$value ) : $_POST; break;
			default:		$data =  $key[1] ? ( isset($_REQUEST[$key[1]]) 	? $_REQUEST[$key[1]] : $_REQUEST[$key[1]]=$value ) : $_REQUEST; break;
		}		
	}else{
		$data =  isset( $_REQUEST[$key[0]] ) ? $_REQUEST[$key[0]] : null; //若不存在,则为null资源,后续有赋值
	}
	
	#开始过滤与赋值
	if(!$data && !is_string($data)){
		if( count($key)>1 ){    //I('get.');  	I('post.name');
			$data = $key[1] ? $value : array();
		}else{    				//I('name');
			$data = $value;
		}
	}else{
		switch ( $filter ){
			//空的,或false,或null,则不过滤
			case '':
				break;
			//移除字符串两侧的空白字符或其他预定义字符。在预定义的字符前加反斜杠( 单引'  双引"  反斜杠\  NULL  ) 并且  正则替换<为〈
			case '_mysafe':
				if( is_array($data) ){
					foreach ($data as $k=>$v){
						if(is_string($v)){
							$v = trim($v);
							$data[$k] = addslashes($v);
							if(!strlen($data[$k])){$data[$k]=$value;}
						}else{
							if(is_string($v)) $v = trim($v);
							$data[$k] = $v;
						}
					}
				}else{
					if(is_string($data)){
						$data = trim($data);
						$data = addslashes($data);  if(!strlen($data)){$data=$value;}
					}
				}
				$data = preg_replace('/</','〈', $data);
				break;
			case 'preg_replace':
				if( is_array($data) ){
					foreach ($data as $k=>$v){
						$data[$k] = preg_replace($preg_replaceData[0], $preg_replaceData[1], $v);
						if(!strlen($data[$k])){$data[$k]=$value;}
					}
				}else{
					$data = preg_replace($preg_replaceData[0], $preg_replaceData[1], $data); if(!strlen($data)){$data=$value;}
				}
				break;
			default:
				//尝试检查源生或自定义的方法是否已设置,若不存在,则不进行过滤! 如 addslashes() 如C()
				if( function_exists( $filter ) === true ){
					if( is_array($data) ){
						foreach ($data as $k=>$v){
							$data[$k] = $filter($v);
							if(!strlen($data[$k])){$data[$k]=$value;}
						}
					}else{
						$data = $filter($data);
						if(!strlen($data)){$data=$value;}
					}	
				}
			break;
		}
	}
	return $data;
}


/**
 * C方法 - 配置 [ok]
 * @param string|array $name 配置变量
 * @param mixed $value 配置值
 * @param mixed $default 默认值
 * @return mixed
 */
function C($name=null, $value=null,$default=null) {
	static $_config = array(); //★声明下次调用此方法,延续此函数内的变量
	// 无参数时获取所有
	if (empty($name)) {
		return $_config;
	}
	// 优先执行设置获取或赋值
	if (is_string($name)) {
		if (!strpos($name, '.')) {
			$name = strtoupper($name);
			if (is_null($value))
				return isset($_config[$name]) ? $_config[$name] : $default;
			$_config[$name] = $value;
			return $default;
		}
		// 二维数组设置和获取支持
		$name = explode('.', $name);
		$name[0]   =  strtoupper($name[0]);
		if (is_null($value))
			return isset($_config[$name[0]][$name[1]]) ? $_config[$name[0]][$name[1]] : $default;
		$_config[$name[0]][$name[1]] = $value;
		return $default;
	}
	// 批量设置
	if (is_array($name)){
		$_config = array_merge($_config, array_change_key_case($name,CASE_UPPER));
		return $default;
	}
	return $default; // 避免非法参数
}


/**
 * Cookie 设置、获取、删除 (默认为网站全局存储)
 * @param string $name cookie名称
 * @param mixed $value cookie值
 * @param mixed $options cookie参数
 * @return mixed
 * @tutorial  $options
	'COOKIE_EXPIRE'         => 0,    	// Coodie有效期
    'COOKIE_DOMAIN'         => '',      // Cookie有效域名
    'COOKIE_PATH'           => '/',     // Cookie路径
    'COOKIE_PREFIX'         => '',      // Cookie前缀 避免冲突
 *
 *  设计：将$_COOKIE['PHPSESSID'] 也一同清除了,清除cookie后,session也失效, 一般只要用户点击【退出】时才清理COOKIE,直接关闭浏览器不会删除COOKIE
 * @example <pre>
   //删除所有cookie
   cookie(null);

   //设置cookie
   cookie('key','value');

  //获取cookie
    $r = cookie('key');
 *
 */
function cookie($name, $value='', $option=null) {
	// 默认设置
	$config = array(
			'prefix'    =>  C('COOKIE_PREFIX'), // cookie 名称前缀
			'expire'    =>  C('COOKIE_EXPIRE'), // cookie 保存时间
			'path'      =>  C('COOKIE_PATH'), // cookie 保存路径
			'domain'    =>  C('COOKIE_DOMAIN'), // cookie 有效域名
	);
	// 参数设置(会覆盖黙认设置)
	if (!is_null($option)) {
		if (is_numeric($option))
			$option = array('expire' => $option);
		elseif (is_string($option))
			parse_str($option, $option);
		$config     = array_merge($config, array_change_key_case($option));
	}
	// 删除所有cookie
	if (is_null($name)) {
		if(empty($_COOKIE)) return;
		foreach ($_COOKIE as $key => $val){
			setcookie($key, '', time()-3600, $config['path'], $config['domain']);
			unset($_COOKIE[$key]);
		}
		return;
	}
	$name = $config['prefix'] . $name;
	if ('' === $value) {
		if(isset($_COOKIE[$name])){
			$value =    $_COOKIE[$name];
			if(0===strpos($value,'MyPHP:')){
				$value  =   substr($value,6);
//				return array_map('urldecode',json_decode(MAGIC_QUOTES_GPC?stripslashes($value):$value,true));
				return array_map('urldecode',json_decode($value,true));
			}else{
				return $value;
			}
		}else{
			return null;
		}
	} else {
		if (is_null($value)) {
			setcookie($name, '', time() - 3600, $config['path'], $config['domain']);
			unset($_COOKIE[$name]); // 删除指定cookie
		} else {
			// 设置cookie
			if(is_array($value)){
				$value  = 'MyPHP:'.json_encode(array_map('urlencode',$value));
			}
			$expire = !empty($config['expire']) ? time() + intval($config['expire']) : 0;
			setcookie($name, $value, $expire, $config['path'], $config['domain']);
			$_COOKIE[$name] = $value;
		}
	}
}


/**
 * 弹出JS- alert()对话框
 * @param string $str 弹出的对话内容
 * @param string $other 其它js内容
 */
function my_alert($str='',$other=''){
	echo "<script type='text/javascript'>alert('" .$str. "');{$other}</script>";
	exit;
}

/**
 * 返回数据
 * @param unknown $data
 * @param string $msg
 * @param number $code
 * @param string $page
 * @param string $debugMsg
 * @param string $CallBackURL
 * @return array
 */
function showData($data='', $msg='', $code=0, $page='', $debugMsg = '',$CallBackURL=''){
	return array(
			'data'		 	=> $data,
			'msg'  			=> $msg,
			'debugMsg'  	=> $debugMsg,
			'code' 			=> $code,
			'page' 			=> $page,
			'CallBackURL'	=> $CallBackURL
	);
}


/**
 * 检查APP-API接口的必填参数   - 若返回的code=1表示有必填参数没有传
 * @param unknown $datas  - 要检查的数据，数组，可以为  $_POST, $_GET, $_REQUEST, 或自定义数组数据
 * @param unknown $fields  - 必须存在的字段，类型:数组,如array('id','user','name'); 或者 字符串，字符串多个用,隔开，如 "id,user,name"
 * @tutorial
 *  	设计:1. 对数据的第1维的数据进行【键名】判断处理，是否存在【必填的键名数组】中 >> 处理 >>  验证都通过后释放验证内存
 *      		2. 若$fields验证字段数组为空，那么不进行验证
 * @return array
 *  [data] => 数据 [msg] =>错误消息 [debugMsg] =>错误详细消息 [code] => （0-正确 1-错误） [page] =>分页系统
 */
function checkAPPKeyword( $datas=array(), $fields = array(), $error = '',$CallBackURL='' ){
	if( !$fields )  	return showData('');
	$array = array();
	$i = -1;
	foreach ( $datas as $k => $v ) {
		$i++;
		$array[$i] = $k;
	}
	if( is_string($fields) )  $fields = explode( ',' ,  $fields );  //  若为字符串，这转换为数组[逗号分隔]
	foreach ( $fields as $key => $value ) {
		if ( in_array( $value, $array )  )  {
			// 必要字段存在，跳过不处理
			if(  strlen( $datas[$value] ) < 1 ){
				$msg = $error ? $error.$value : \Api\Msg::get('_'.$value);
				if($CallBackURL){
					return showData(array('CallBackURL'=>$CallBackURL), $msg, 1, '', $value.'不能为空' );  // 若必填字段为空长度时
				}else{
					return showData('', $msg, 1, '', $value.'不能为空' );  // 若必填字段为空长度时
				}
			}  
		}
		else {
			$msg = $error ? $error.$value : \Api\Msg::get('_'.$value);
			if($CallBackURL){
				return showData(array('CallBackURL'=>$CallBackURL), $msg, 1, '', $value.'不能为空' );
			}else{
				return showData('', $msg, 1, '', $value.'不能为空' );
			}
			
		}
	}
	unset( $datas, $fields, $i, $k, $v, $key, $value );
	return showData('');
}

    /**
     * 过滤数据
     * @param $data   - 1维数据
     * @return array
     * @tutorial 若数组值不是空长度则保留，反之就清除过滤
     */
    function action_data( $data=array() ){
        if( !$data ) return $data;
        foreach( $data as $k => $v ){
            if( strlen( $v ) < 1 )  unset( $data[$k] );
        }
        return $data;
    }

    /**
     * 处理数据为NULL的情况,将转换为''空字符串
     * @author berhp 2018.5.16
     * @param $data   - 1维数据
     * @return array
     * @tutorial 若数组值不是空长度则保留,反之就清除过滤
     */
    function action_dataNULL( $data=array() ){
    	if( !$data ) return $data;
    	foreach( $data as $k => &$v ){
    		if( strlen( $v ) < 1 ) $v='';
    	}
    	return $data;
    }


    /**
     * 动态获取url
     * @return string 如 http://192.168.0.200/   如 http://192.168.0.200/myphp
     */
    function get_filehttp(){
        $server_url = 'http';
        if(! empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off')    $server_url .= 's';
        $server_url .= '://';
        if ($_SERVER["SERVER_PORT"] != "80"){
            $server_url .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"];
        }else{
            $server_url .= $_SERVER["SERVER_NAME"];
        }
        $server_url .= substr($_SERVER['SCRIPT_NAME'],0,strrpos($_SERVER['SCRIPT_NAME'],'/'));
        return $server_url;
    }
    
	/**
	 * 模拟浏览器进行http请求 支持post和get
	 * 
	 * @param string $url
	 * @param int $timeout  	 设置请求超时(默认0 不限制)
	 * @param array $post_data   ★若超时请求不到,会返回false
	 */
	function curl_request($url = '', $post_data = array(),$ispost=true, $timeout=0) {
		if (empty($url)) {
			return false;
		}
		if($post_data){
            $o = "";
            foreach ( $post_data as $k => $v ) {
                if(is_array($v)){
                    foreach ( $v as $ks => $vs ) {
                        $vs = urlencode( $vs );
                        $o.= "{$k}[{$ks}]={$vs}&" ;
                    }
                }else{
                    $o.= "$k=" . urlencode( $v ). "&" ;
                }
            }
            $post_data = substr($o,0,-1);
            if($ispost){
                //$url=$url;
            }else{
                $url = $url.'?'.$post_data;
            }
		}
		// $curlPost = 'key='.$key;
		header("Content-type: text/html; charset=utf-8");
		$ch = curl_init();                           //初始化curl
		curl_setopt($ch, CURLOPT_URL,$url);          //抓取指定网页
		curl_setopt($ch, CURLOPT_HEADER, 0);         //设置header
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //要求结果为字符串且输出到屏幕上
		if($timeout){
			curl_setopt($ch, CURLOPT_TIMEOUT,$timeout);  //设置超时 (默认 0-无限等待,或直到php.ini的最高配置,比如120秒无影响就失效了)
		}
		if($ispost){
			curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
		}
		$data = curl_exec($ch);//运行curl
		curl_close($ch);
		return $data;
	}


	/**
	 *求两个已知经纬度之间的距离,单位为米
	 *@param lng1,lng2 经度
	 *@param lat1,lat2 纬度
	 *@return float 距离，单位米
	 *@tutorial 与数据库的 get_distance()的方法一致算法
	 **/
	function m_getdistance($lng1,$lat1,$lng2,$lat2){
		/*  方法2 OK
		 //将角度转为狐度 -- 此方法更精确
		$radLat1=deg2rad($lat1);//deg2rad()函数将角度转换为弧度
		$radLat2=deg2rad($lat2);
		$radLng1=deg2rad($lng1);
		$radLng2=deg2rad($lng2);
		$a=$radLat1-$radLat2;
		$b=$radLng1-$radLng2;
		$s=2*asin(sqrt(pow(sin($a/2),2)+cos($radLat1)*cos($radLat2)*pow(sin($b/2),2)))*6378.137*1000;
		return $s;
		*/
		$Flattening=298.257223563002;
		$er=6378.137;
		$pix=3.1415926535;
		$b1 = (0.5-$lat1/180)*$pix;
		$l1 = ($lng1/180)*$pix;
		$b2 = (0.5-$lat2/180)*$pix;
		$l2 = ($lng2/180)*$pix;
	
		$x1 = $er*COS($l1)*SIN($b1);
		$y1 = $er*SIN($l1)*SIN($b1);
		$z1 = $er*COS($b1);
		$x2 = $er*COS($l2)*SIN($b2);
		$y2 = $er*SIN($l2)*SIN($b2);
		$z2 = $er*COS($b2);
		$d1 = sqrt(($x1-$x2)*($x1-$x2)+($y1-$y2)*($y1-$y2)+($z1-$z2)*($z1-$z2));
		$theta= acos(($er*$er+$er*$er-$d1*$d1)/(2*$er*$er));
		$distance= $theta * $er;
		RETURN $distance * 1000;  /*将单位转换为米返回*/
	}

	
	/**
	 * 高德GPS转百度GPS - 来源WEB，berhp 2018.3.28 已测试 OK
	 * @param yes string gd_lon  高德坐标-经度
	 * @param yes string gd_lat  高德坐标-维度
	 * @author berhp 2018.3.28
	 * @returns array  如  array[0]=lng; array[1]=lat;
	 * @throws  高德GPS坐标反查: http://lbs.amap.com/console/show/picker  百度GPS坐标反查: http://api.map.baidu.com/lbsapi/getpoint/index.html
	 */
	function m_gaodeGPStoBaiduGPS( $gd_lon, $gd_lat ){
		$bd_lat_lon = Array();
		$PI = 3.14159265358979324 * 3000.0 / 180.0;
		$x = $gd_lon; $y = $gd_lat;
		$z = sqrt($x * $x + $y * $y) + 0.00002 * sin($y * $PI);
		$theta = atan2($y, $x) + 0.000003 * cos($x * $PI);
		$bd_lat_lon[0] = $z * cos($theta) + 0.0065;  //经度
		$bd_lat_lon[1] = $z * sin($theta) + 0.006;   //维度
		return $bd_lat_lon;
	}
	

	/**
	 * 百度GPS转高德GPS - 来源WEB，berhp 2018.3.28 已测试 OK
	 * @param yes string bd_lon  百度坐标-经度
	 * @param yes string bd_lat  百度坐标-维度
	 * @author berhp 2018.3.28
	 * @returns array  如  array[0]=lng; array[1]=lat;
	 */
	function m_BaiduGPStogaodeGPS( $bd_lon, $bd_lat ){
		$gd_lat_lon = Array();
		$PI = 3.14159265358979324 * 3000.0 / 180.0;
		$x = $bd_lon - 0.0065; $y = $bd_lat - 0.006;
		$z = sqrt($x * $x + $y * $y) - 0.00002 * sin($y * $PI);
		$theta = atan2($y, $x) - 0.000003 * cos($x * $PI);
		$gd_lat_lon[0] = $z * cos($theta);    //经度
		$gd_lat_lon[1] = $z * sin($theta);    //维度
		return $gd_lat_lon;
	}
	

	/**
	 * 通过百度API获取指定地址的GPS位置
	 * @param yes string $address  地址,如:成都市成华区财富又一城
	 * @param no string  $baidu_ak  百度API接口查询ak,若没有传,读取配置C('baidu_ak');
	 * @author berhp 2018.5.4
	 * @returns array  如  array[0]=lng; array[1]=lat;
	 * @url http://lbsyun.baidu.com/index.php?title=webapi/guide/webservice-geocoding
	 */
	function m_getBaiduGPS( $address='', $baidu_ak='' ){
		$data = array(
				0 => '',
				1 => '',
		);
		if(!$baidu_ak) $baidu_ak=C('baidu_ak');
		$url = "http://api.map.baidu.com/geocoder/v2/?address={$address}&output=json&ak={$baidu_ak}";
		$r = file_get_contents($url);
		//var_dump($r);  //{"status":0,"result":{"location":{"lng":104.1018643793368,"lat":30.684394317882},"precise":1,"confidence":75,"level":"地产小区"}}
		$r = json_decode($r,true); if( !is_array($r) ) return $data;
		if($r['status'] != '0') return $data;
		$data = array(
				0 => isset($r['result']['location']['lng']) ? $r['result']['location']['lng'] : '',
				1 => isset($r['result']['location']['lat']) ? $r['result']['location']['lat'] : '',
		); 
		return $data;	
	}
	
	

	/**
	 * 生成唯一编号
	 * @author berhp 2017.12.5
	 * @param no string $name 简称,如 N-主订单号 C-充值 s-子订单号    ..其他自定义扩展
	 * @param no int $serverid 服务器ID,默认为1
	 * @return string
	 * @example  $r=create_unique_number(); echo $r;
	 */
	function create_unique_number(  $name='N', $serverid='1' ){
		date_default_timezone_set('PRC');
		$r = $name.$serverid.date('YmdHis',time()).mt_rand(1000, 9999).mt_rand(10000, 99999).mt_rand(10000, 99999);
		return $r;
	}

	/**
	 * 随机生成非自增的唯一用户UID ( 已采用redis来处理同一微妙,还没有写入数据库时,出现重复可能 )  OK
	 * @author berhp 2017.12.5
	 * @param no int $serverid 服务器ID,默认为1
	 * @return string
	 * @throws
	 *   1.需要配置 redis环境  2.设计为 数据库非自增用户ID 唯一生成函数
	 * @example
			$uid = create_unique_uid();
			$r = '..'; //各种业务处理
			$redis = new \MyPHP\Redis();
			$reids_key = C('app_redis_new_uids_key');  //即 $reids_key = 'newUids';
			$redis->sremove( $reids_key, $uid );
	 *
	 */
	function create_unique_uid( $serverid='' ){
		if(!$serverid) $serverid=C('app_serverid');
		$uid = $serverid.mt_rand(10000, 99999).mt_rand(10000, 99999);
		// redis查下是否存在,若存在,重新生成
		$redis_key = C('app_redis_new_uids_key');
		$redis = new MyPHP\Redis();
		$check = $redis->scontains( $redis_key, $uid ); if( $check === true ) return create_unique_uid($serverid);
		// 数据库查下是否存在,若存在,重新生成
		$user_table 	= C('app_user_table'); 		//user表
		$user_table_key = C('app_user_table_key'); 	//user表的字段uid
		$where = array( $user_table_key => $uid );
		$check = M( $user_table )->field( $user_table_key )->where($where)->find();  if($check) return create_unique_uid($serverid);
		// 内存缓存
		$r = $redis->sAdd( $redis_key, $uid );  if($r===false)  return create_unique_uid($serverid);
		return $uid;
	}




	/**
	 * 动态密码加密 (ok)
	 * @author berhp 2017.10.12
	 * @version 1.0
	 * @param yes string $password 原始密码(未加密的)
	 * @param no int $secretkey_lenth 随机密钥长度,默认 8
	 * @param no boolean $is_admin 是否为管理员账号,默认false-不是 true-是
	 * @return array $data
	 * @example
	$data = array(
	'password' 	=> '',  //加密后密码
	'secretkey' => '',  //生成的-随机密钥
	);
	@tutorial
	用户表-  	md5( 随机密钥前3位+md5(密码) +随机密钥后5位+源码签名)
	管理员表-  	sha1( 随机密钥前3位+md5(密码) +随机密钥后5位+源码签名)
	 */
	function m_createpass( $password='', $secretkey_lenth=8, $is_admin=false ){
		$_str = 'abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$_strlen = strlen($_str);
		$_secretkey = '';
		for($i=0;$i<$secretkey_lenth;$i++){
			$_secretkey .= $_str[rand(0,$_strlen-1)];
		}
		$_str_s = substr($_secretkey,0,3);
		$_str_o = substr($_secretkey,-5);
		$_safe_sign = C('safe_sign');
		if($is_admin===true){
			$_password = sha1( $_str_s.md5($password).$_str_o.$_safe_sign );
		}else{
			$_password = md5( $_str_s.md5($password).$_str_o.$_safe_sign );
		}
		return array('password'=>$_password, 'secretkey'=>$_secretkey );
	}


	/**
	 * 动态密码检查 (ok)
	 * @author berhp 2017.10.12
	 * @version 1.0
	 * @param yes string $password  用户输入的原始密码(未加密的)
	 * @param yes string $secretkey 随机密钥(数据库存储的)
	 * @param yes string $password_db 数据库中的密码加密字符串
	 * @param no boolean $is_admin 是否为管理员账号,默认false-不是 true-是
	 * @return boolean true-密码正确  false-密码错误
	 */
	function m_checkpass( $password='', $secretkey='', $password_db='', $is_admin=false ){
		$_str_s = substr($secretkey,0,3);
		$_str_o = substr($secretkey,-5);
		$_safe_sign = C('safe_sign');
		if($is_admin===true){
			$_password = sha1( $_str_s.md5($password).$_str_o.$_safe_sign );
		}else{
			$_password = md5( $_str_s.md5($password).$_str_o.$_safe_sign );
		}
		if( $_password != $password_db ) return false;
		return true;
	}