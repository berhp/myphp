<?php
/**
 * Upload基础类
 * @autor berhp
 */
namespace MyPHP;
use \MyPHP\GD;
class Upload{
	/**
	 * 【参数】-是否根据上传的name名动态创建key返回上传后的路径
	 * @tutorial
	 *  设计,默认为true,生成后的结果如:
		Array
		(
		    [img] => Array
		        (
		            [0] => /Uploads/XX/CC/20161206/a57d0372d4458d83a3f9cb875e26d9b6.jpg
		        )
		
		    [img2] => Array
		        (
		            [0] => /Uploads/XX/CC/20161206/11eb29f66f75e18bbe96715b5ceca7ce.jpg
		        )
		
		    [img3] => Array
		        (
		            [0] => /Uploads/XX/CC/20161206/21b62c2e45bc3ad951fe6dde9b3f0243.jpg
		            [1] => /Uploads/XX/CC/20161206/0e2a5ef5741c7479d20cf34297edb8d3.jpg
		        )
		
		)
		若为fasle,生成的结果为
		Array
		(
		    [0] => /Uploads/XX/CC/20161206/ed0428b80f039251d8f6abff9f5d9948.jpg
		    [1] => /Uploads/XX/CC/20161206/03b6aa06a800daa1a2c1ecdbcbef6981.jpg
		    [2] => /Uploads/XX/CC/20161206/6eb23bf2db444c6bdd2b701f0466545b.jpg
		)
		@example
		$upload = new \MyPHP\Upload();
		$upload->_iskey=false;
		$upload->savePath = 'Uploads/XX/CC/';  //自定义目录,相对于项目中的index.php
		//$upload->savePath .= 'XX/CC/';       //或者这种写法,与上面的设置相同,此时C('upload.savePath')='Uploads/';
		
		//文件上传方式:
		if($_FILES){
		    $img = $upload->upload();
		    var_dump($img); 		 //上传成功的图片路径
		    var_dump($upload->_msg); //上传时出现的错误信息
		}
		
		//base64上传方式:
		$img = $upload->upload_base64('img,img2,img3');
		var_dump($img); 		 //上传成功的图片路径
		var_dump($upload->_msg); //上传时出现的错误信息
	 */
	public $_iskey = true;
	

	/**
	 * 是否需要拼接出,项目根目录
	 * @tutorial
	 * eg: false时
	 * 				./Uploads/commentpic/20170301/be82c07aacbbf35118639dcda198dd57.png
	 * eg: true时
	 * 				/acdd/./Uploads/commentpic/20170301/b14ebbf3daad3683031a3419674321b3.png
	 */
	public $_isroot = false;
	
	/**
	 * 错误信息
	 * @var string
	 */
	public $_msg = '';
	
	/**
	 * 裁剪尺寸  如 200_100 表示长200px高100px
	 * @tutorial 若有值,程序后续会裁剪图片
	 */
	public $_caijiansize = '';
	
	
	/**
	 * 允许上传文件的最大大小配置, 单位MB ( 0表示不限制, 但会受php.ini的配置影响 )
	 * @var Int
	 */
	public $maxSize = 0;
	
	/**
	 * 允许上传文件的后缀名
	 * @var array
	 * @example $exts=array('jpeg','jpg','png','gif','bmp','txt','sql'); 
	 */
	public $exts = array();
	
	
	/**
	 * 【容器】-勿设置值
	 */
	private $_maxSize; 	//允许上传文件的最大大小,单位字节
	private $_filetype; //当前上传文件的后缀名,如jpg
	
	
    /**
     * Upload constructor.
     * @param array $config
        $config => array(
			'maxSize'       =>  2, //上传的文件大小限制 单位M (0-不做限制)
			'exts'          =>  array('jpeg','jpg','png','gif','bmp','txt','sql'), //允许上传的文件后缀
			'savePath'      =>  'Uploads/', //保存路径,相对于单入口index.php,★linux系统下也是'/',用的是相对路径
			'saveName'      =>  '', 		//新的文件名
			'replace'       =>  false, 		//存在同名是否覆盖
			'is_oldname' 	=>  false, 		//是否保留原名
        ),
     */
    public function __construct($config=array()){
        $config = $config ? $config : C('upload');
        foreach($config as $k=>$v){
            $this->$k = $v;
        }
    }

    
    /**
     * 上传处理
     * @return array $data  //没有上传成功,则是空数组,返回的路径相对于网站根目录
     * @param string $caijiansize //裁剪的尺寸：如 300_200 表示 长300px,高200px  （若设置了,会按尺寸裁剪图片)
	   @example html
		<form action="http://xx.xx.xx/xx.php" enctype="multipart/form-data" method="post" >
			img<input type="file" name="img"/>
			img2<input type="file" name="img2" />
			img3<input type="file" name="img3[]" />
			img3<input type="file" name="img3[]" />
		<input type="submit" value="提交" >
		@example php
		$upload = new \MyPHP\Upload();
		$upload->savePath = './Uploads/XX/CC/';  //自定义目录,相对于项目中的index.php
		if($_FILES){
		    $img = $upload->upload();
		    var_dump($img); 		 //上传成功的图片路径
		    var_dump($upload->_msg); //上传时出现的错误信息
		}
		
		@example 有自定义尺寸裁剪的
		if( $_FILES ){
			$upload = new Upload();
			$upload->savePath .= "Goods/demo1/";
			$img = $upload->upload('300_200');    //自定裁剪后的（长:宽)尺寸为 300px:200px
			if( $upload->_msg ) return showData('', '上传图片失败:'.$upload->_msg, 1);
			var_dump($img);
		}
     */
    public function upload( $caijiansize='' ){
    	if( $caijiansize ){
    		$this->_caijiansize = $caijiansize;
    	}
    	self::_action_maxSize();
        $data = array();
        foreach($_FILES as $k=>$v){
        	if(is_array($v['name'])){  //处理同一个key多个文件上传
        		foreach($v['name'] as $kk=>$vv){
        			$_v = array();
        			$_v = array(
        					'name' => $v['name'][$kk],
        					'type' => $v['type'][$kk],
        					'tmp_name' => $v['tmp_name'][$kk],
        					'error' => $v['error'][$kk],
        					'size' => $v['size'][$kk],
        			);
        			$r = self::_action_upload($_v);
        			if($r){ 
        				if($this->_iskey === true){
        					$data[$k][] = $r;
        				}else{
        					$data[] = $r;
        				}
        			}
        		}
        	}else{
        		$r = self::_action_upload($v);
        		if($r){
        			if($this->_iskey === true){
        				$data[$k][] = $r;
        			}else{
        				$data[] = $r;
        			}
        		}
        	}
        }
        return $data;
    }
    
    /**
     * 
     * @param unknown $v
     * @return string
     * @tutorial 
     *  若是同一个key name多张图上传时,需先转换为以下形式
     * $v=array(
     		'name' => '1773545_002859086000_2.jpg',
     		'type' => 'image/jpeg',
     		'tmp_name' => 'C:\Windows\temp\php2D9D.tmp',
     		'error' => 0,
     		'size' => 90505,
        )
     */
    private function _action_upload($v){
    	if($v['error']=='0'){
    		$r = self::_check_file_size($v['size']);  if($r===false) return '';
    	}
    	$r = self::_action_error($v['error'],$v);  if($r===false) return '';
    	$r = self::_check_filetype($v['name']);  if($r===false) return '';
    	if( $this->is_oldname === true ){
    		return self::create_file($v['tmp_name'], $v['name']);
    	}else{
    		return self::create_file($v['tmp_name']);
    	}
    }
    
    
    /**
     * 储存文件
     * @param yes string $tmp_name  原始上传后的临时文件路径,如 C:\Windows\temp\phpB067.tmp
     * @param no string $new_name	新的保存文件名,若参数$this->is_oldname===true 则以上传文件名保存
     * @return string
     * @tutorial
     *  1.检查文件存放目录是否存在,不存在则创建
     *  2.根据上传的文件类型,转移临时文件储存文件
     *  __ROOT__ 自定义常量，如 /myphp
     */
    private function create_file($tmp_name='', $new_name=''){
    	$this->_savePath = $this->savePath.date('Ymd',time()).'/'; 
    	is_dir($this->_savePath) or mkdir($this->_savePath, 0755, true);
    	$_file_type = '.'.$this->_filetype;
    	$file_name = $this->saveName ? $this->saveName.$_file_type : md5(microtime(TRUE).mt_rand(1000,9999)).$_file_type;
    	if($new_name) $file_name=$new_name;
    	
    	$_file_name = $this->_savePath . $file_name;
    	if(file_exists($_file_name)){
    		if($this->replace === false){
    			$_file_name =  str_replace($_file_type,'_'.rand(1000,9999).$_file_type,$_file_name);
    		}
    	}
    	if(IS_WIN) $_file_name = iconv('UTF-8', 'gbk', $_file_name); // windows环境下需要,将utf-8转换为gbk方便存中文名字 
    	$r = move_uploaded_file( $tmp_name, $_file_name);  if($r===false) return '';

    	//判断-特殊处理裁剪尺寸
    	if( $this->_caijiansize ){   //按尺寸压缩
    		//文件后缀名判断:只有图片才可以裁剪
    		$_array = array( 'jpg','jpeg','gif','png','bmp' );
    		if( in_array($this->_filetype, $_array) ){
    			$r = explode('_', $this->_caijiansize);
    			$_width  = isset($r[0]) ? $r[0] : 100;
    			$_height = isset($r[1]) ? $r[1] : 100;
    			
    			/**  
    			//方式一,固定
    			$_config = array(
						'image_name' 			=> 's_', 		//裁剪图片名称前缀
						'image_path' 			=> $this->_savePath, 	//裁剪后的保存目录,''未配置,为当前目录下,★若不存在目录,需要先手动创建目录
						'image_width' 			=> $_width, 			//裁剪图片-宽,单位px
						'image_height' 			=> $_height, 			//裁剪图片-高,单位px
						'is_merge' 				=> false, 			//是否精确裁剪,默认false-不（按百分比缩放裁剪）  true-是
						'image_merge_type' 		=> 'center', 		//方式,只有开启精确裁剪才会生效: 默认center-居中裁剪    lt- 从左上顶部开始,坐标(0,0), lb-左下底部   rt-右上顶部   rb-右下底部
						'is_retain' 			=> true,			//是否需要保留原图,默认false-不保留 true-保留
    			);
    			$model = new \MyPHP\GD();
    			$_file_name = $model->caijian($_file_name, '', $_config);
    			*/
    			
    			//方式二,动态值,同时会受裁剪配置参数影响 -berhp 2018.1.30
    			C( 'caijian.image_path', $this->_savePath 	);
    			C( 'caijian.image_width', $_width 			);
    			C( 'caijian.image_height', $_height 		);
    			$_caijian_file_name = C('caijian.image_name').$file_name;  		//如 s_747dab4c126fca1294f2e223726dba07.jpg
    			$model = new GD();
    			$_file_name = $model->caijian($_file_name, $_caijian_file_name);
    		}
    	}else{  //按品质压缩
    		//文件后缀名判断:只有图片才可以按品质压缩
    		$_array = array( 'jpg','jpeg','gif','png','bmp' );
    		if( in_array($this->_filetype, $_array) ){
    			C( 'caijian.image_path', $this->_savePath );
    			$_caijian_file_name = C('caijian.image_name').$file_name;
    			$model = new GD();
    			$_file_name = $model->pinzhi($_file_name, $_caijian_file_name);
    		}
    	}
    	
    	//★转换路径编码为utf-8,便于存数据库
    	//return  IS_WIN ? iconv('gbk', 'utf-8', $_file_name) : $_file_name; //windows环境下才需要转换为utf-8方便存数据库,linux默认为utf-8编码（相对于项目index.php路径)
    	$_file_name = IS_WIN ? iconv('gbk', 'utf-8', $_file_name) : $_file_name;
    	
    	//自定义图片地址返回前缀处理
    	if( isset( $this->is_prefix ) ){
    		if( $this->is_prefix )  $_file_name = $this->prefix_str. $_file_name;
    	}
    	
  		//是否拼接项目根文件夹
  		if( $this->_isroot === true ){
  			if( defined('__ROOT__') ){
  				if( __ROOT__ ) $_file_name = __ROOT__.'/'.$_file_name;
  			}  			
  		}
  		
    	return $_file_name; //相对于网站根路径
    }
    
    /**
     * 【内用】检查文件大小是否通过配置参数大小
     * @param int $size 当前上传文件大小
     * @return boolean true-没有超过 false-超过
     */
    private function _check_file_size($size){
    	if($this->_maxSize < $size ){
    		$this->_msg .= '文件大小超过'.$this->maxSize.'MB;';
    		return false;
    	}else{
    		return true;
    	}
    }
    
    /**
     * 【内用】检查文件后缀名是否支持
     * @param yes $suffix 后缀名, 如 "jpg"
     * @return boolean true-允许通过 false-不允许通过
     */
    private function _check_file_type($suffix=''){
    	if( in_array($suffix, $this->exts) ){
    		return true;
    	}else{
    		$this->_msg .= "不支持后缀名为'{$suffix}'的文件;";
    		return false;
    	}
    }
    

    /**
     * 【内用】-转换配置参数的文件大小
     */
    private function _action_maxSize(){
    	$this->_maxSize = $this->maxSize ? $this->maxSize*1024*1024 : 99999*1024*1024; 
    }
    
    /**
     * 【内用】获取上传文件后缀名
	 * @param yes string $name 上传文件的文件名
	 * @return string  //如 jpg
     */
    private function _get_filetype($name=''){
    	return substr( $name, strripos($name,'.')+1 );
    }
    
    /**
     * 【内用】-检查文件后缀名是否支持
     * @param yes string $name 上传文件的文件名
     * @return boolean true-支持 false-文件类型后缀不支持
     */
    private function _check_filetype($name=''){
    	$filetype = self::_get_filetype($name);
    	$filetype = strtolower($filetype);          //★转换为小写,便于后续判断 2017-3-11
		$this->_filetype = $filetype;
		return self::_check_file_type($filetype);
    }
    
    /**
     * 【内用】处理错误信息
     * @return boolean $iserror //true-没有错误,后续可以处理文件储存       false-有错误,跳过
     */
    private function _action_error($error=0,$v=array()){
    	switch($error) {
    		case 1:
    			// 文件大小超出了服务器的空间大小
    			$this->_msg .= "[{$v['name']}]文件大小超出限制,";
    			return false;
    			break;
    		case 2:
    			// 要上传的文件大小超出浏览器限制
    			$this->_msg .= "[{$v['name']}]文件大小超出浏览器限制,";
    			return false;
    			break;
    		case 3:
    			// 文件仅部分被上传
    			$this->_msg .= "The file was only partially uploaded,";
    			return false;
    			break;
    		case 4:
    			// 没有找到要上传的文件
    			return false;
    			break;
    		case 5:
    			// 服务器临时文件夹丢失
    			return false;
    			break;
    		case 6:
    			// 文件写入到临时文件夹出错
    			return false;
    			break;
    		default:
    			// 0为正常
    	}
    	return true;
    }

    /**
     * base64图片上传
     * @author berhp 2018.12.14
     * @param yes string||array  $key 多个用','隔开,如"img1,img2"; 或者 传数组 array("img1","img2")
     * @param no string $savePath 存放目录,相对于网站根目录,如 'Uploads/';
     * @return array $path 存放路径
     * @tutorial
     * 		1.用POST方式上传,避免base64过长,服务端接收不到
     */
    public function upload_base64( $key, $savePath='' ){
    	self::_action_maxSize();
    	$key = is_array($key) ? $key : explode(',', $key);  if(!$key) return array();
    	$data = array();
    	foreach ( $key as $k ){
    		if(!isset($_REQUEST[$k])) continue;
    		$file = $_REQUEST[$k];
    		if( is_array($file) ){
    			foreach ($file as $vv){
    				$re = self::_action_upload_base64($vv);  if(!$re) continue;
    				if($this->_iskey==true){
    					$data[$k][] = $re;
    				}else{
    					$data[]   = $re;
    				}
    			}
    		}else{
    			$re = self::_action_upload_base64($file);  if(!$re) continue;
    			if($this->_iskey==true){
    				$data[$k][] = $re;
    			}else{
    				$data[]   = $re;
    			}
    		}
    	}
    	return $data;
    }
    
    
    /**
     * 【内用】-实现base64解码和存储图片
     * @param yes string $base64str 客户端上传的base64源内容: 如 data:image/jpeg;base64,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
     * @return string  图片的存放路径
     * @todo 正则匹配结果如: preg_match('/^(data:(([A-Za-z0-9_\-.]+)\/([A-Za-z0-9_\-.]+)){1};base64,)/'){..}
		$result = array(5) {
			  [0]=>
			  string(22) "data:audio/mp3;base64,"
			  [1]=>
			  string(22) "data:audio/mp3;base64,"
			  [2]=>
			  string(9) "audio/mp3"
			  [3]=>
			  string(5) "audio"
			  [4]=>
			  string(3) "mp3"
		}
		@tutorial 设计,根据匹配出的后缀名,动态生成文件，若个别特殊的后缀名,需单独解析出真实后缀名.
     */
    private function _action_upload_base64( $base64str="" ){
    	//特殊的MIME非按标准命名的,如"application/vnd.android.package-archive"其实就是'apk',如"image/x-icon"其实就是'ico'图标;
    	//★更多百度搜索“MIME类型”,★★JS的base64并不支持所有的类型,如.doc无法通过base64上传。 (后续有方法,客户端配合即可上传.doc等文件)
    	$mime_ext = array(
    			'image/x-icon' 								=> 'ico',
    			'application/vnd.android.package-archive' 	=> 'apk',
    			'application/javascript' 					=> 'js',
    			'text/plain' 								=> 'txt',
    			'application/x-msdownload' 					=> 'exe',
    	);
    	
    	//★若客户端JS拿不到type,则需要客户端自己先去处理拼接出头,然后在base64上传至服务端，  如data:default/rar;base64,UmFy....AQAcA
    	//if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64str, $result)){   //仅图片
    	if (preg_match('/^(data:(([A-Za-z0-9_\-.]+)\/([A-Za-z0-9_\-.]+)){1};base64,)/', $base64str, $result)){
    		$this->_filetype = isset($mime_ext[$result[2]]) ? $mime_ext[$result[2]] : $result[4];  //jpg
    		$this->_filetype = strtolower($this->_filetype);  //统一转小写

    		$check = self::_check_file_type( $this->_filetype );  if($check===false) return "";

    		$_file_type = '.'.$this->_filetype;
    		$file_name = $this->saveName ? $this->saveName.$_file_type : md5(microtime(TRUE).mt_rand(1000,9999)).$_file_type;
    		
    		$this->_savePath = $this->savePath.date('Ymd',time()).'/';
    		is_dir($this->_savePath) or mkdir($this->_savePath, 0755, true);
    		
    		$data = str_replace(' ', '+', $base64str);    //将base64加密的"+"转为空格
    		$_file_name = $this->_savePath.$file_name;
    		$_file_content = base64_decode(str_replace($result[1], '', $data));
    		$_file_content_length = strlen($_file_content);
    		$check = self::_check_file_size( $_file_content_length );  if($check===false) return '';
    		if(  file_put_contents( $_file_name, $_file_content )  ) {
    			
    			// 若为图片,服务端进行品质二次压缩
    			$_array = array( 'jpg','jpeg','gif','png','bmp' );
    			if( in_array($this->_filetype, $_array) ){
    				C( 'caijian.image_path', $this->_savePath );
    				$_caijian_file_name = C('caijian.image_name').$file_name;
    				$model = new GD();
    				$_file_name = $model->pinzhi($_file_name, $_caijian_file_name);
    			}
    			
    			// 自定义图片地址返回前缀处理
    			if( isset( $this->is_prefix ) ){
    				if( $this->is_prefix )  $_file_name = $this->prefix_str. $_file_name;
    			}
    			
    			// 是否拼接项目根文件夹
    			if( $this->_isroot === true ){
    				if( defined('__ROOT__') ){
    					if( __ROOT__ ) $_file_name = __ROOT__.'/'.$_file_name;
    				}
    			}
    			
    			return $_file_name;
    		} else {
    			return "";
    		}
    	}
    	return "";
    }
    
    
    
    
    
    
    


}