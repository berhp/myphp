<?php 
/**
 * GD库
 * @author berhp
 * @link http://php.net/manual/en/book.image.php
 * @tutorial
 * $config = array();
 * $r = new \MyPHP\GD;
 * $r->create_code($config)  功能：生成图片验证码
 * $r->shuiyin($old_imgfile, $new_imgfile, $config)    功能：水印
 * ..
 */
namespace MyPHP;
class GD{
	/**
	 * 参数
	 */
	public $width = ''; 	//宽
	public $height = ''; 	//高
	public $path = ''; 		//存放文件夹路径
		
	/**
	 * 容器
	 */
	private $_img; //图片
	public $code; //生成的验证码
	

	public function __construct(){
		if( !function_exists('gd_info') ) die('需要开启GD库扩展功能');
	}
	

	/**
	 * 获取GD信息 [ok]
	 *  @link http://www.php.net/manual/en/function.gd-info.php
	 *  @return array
			Array
			(
			    [GD Version] => bundled (2.1.0 compatible)
			    [FreeType Support] => 1
			    [FreeType Linkage] => with freetype
			    [GIF Read Support] => 1
			    [GIF Create Support] => 1
			    [JPEG Support] => 1
			    [PNG Support] => 1
			    [WBMP Support] => 1
			    [XPM Support] => 1
			    [XBM Support] => 1
			    [WebP Support] => 1
			    [JIS-mapped Japanese Font Support] => 
			)
	 *  
	 */
	public function gd_info(){
		return gd_info();
	}
	
	
	/**
	 * 微缩图裁切图片 [ok]
	 * @tutorial 设计：根据原图片类型,动态生成微缩图
	 * @param array $config=array(
        	'image_name' 		=> 's_', 			//裁剪图片名称前缀
	        'image_path' 		=> 'Public/', 		//裁剪后的保存目录,''未配置,为当前目录下,★若不存在目录,自动创建(若linux系统,请先将对应存放图片根目录授权)
	        'image_width' 		=> 100, 			//裁剪图片-宽,单位px
	        'image_height' 		=> 100, 			//裁剪图片-高,单位px
	        'is_merge' 			=> false, 			//是否精确裁剪,默认false-不（按百分比缩放裁剪）  true-是
	        'image_merge_type' 	=> 'center', 		//方式,只有开启精确裁剪才会生效: 默认center-居中裁剪    lt- 从左上顶部开始,坐标(0,0), lb-左下底部   rt-右上顶部   rb-右下底部
	   		'is_retain' 		=> false, 			//是否需要保留原图,默认false-不保留 true-保留
	   		'memory_limit' 		=> '-1',			//裁剪时的内存设置,如-1表示无限大,其他时候,如512M 表示最大为512M,请准守 ini_set('memory_limit','512M')) 函数用法
	 	);

	 */
	public function caijian($old_imgfile='',$new_imgName='',$config=array()){
		if(isset($config['memory_limit'])){
			ini_set('memory_limit', $config['memory_limit'] );   //将内存设置大些,避免大图不能裁剪 ini_set('memory_limit','512M'))
		}else{
			ini_set('memory_limit',C('caijian.memory_limit'));   //将内存设置大些,避免大图不能裁剪 ini_set('memory_limit','512M'))
		}
		$config = $config ? $config : C('caijian');
		foreach ($config as $k=>$v){
			$this->$k = $v;
		}
		
		//获取图片后缀名
		$r = self::check_imgtype($old_imgfile); if(is_string($r)) exit($r);
		$r_k = $r[0];  $r_v = $r[1];
		
		//引入图片
		$old_img = $r_k($old_imgfile);
		$old_width = imagesx($old_img);
		$old_height = imagesy($old_img);

		//检查目录是否存在,不存在则创建
		if(!is_dir($this->image_path)) mkdir($this->image_path,0755,true);
		
		//创建新图的宽，高
		$new_width = $this->image_width;
		
		if($this->is_merge===true){
			$new_height = $this->image_height ? $this->image_height : ($new_width/$old_width)*$old_height;
		}else{
			$new_height = ($new_width/$old_width)*$old_height;
		}

		//命名
		if( $new_imgName ){
			$new_img_filename = $this->image_path.$new_imgName;    // xx/xx/xx/s_d72e016f310d7205cec6720b613f6f26.jpg, 根据$new_imgName传什么就拼接什么,包含图片后缀名 - berhp 2018.1.30
		}else{
			$new_img_filename = $this->image_path.$this->image_name.date('YmdHis',time()).'_'.rand(100000,999999).$new_imgName.'.'.$r[2]; // xx/xx/xx_20161114100412_448596.jpg
		}
		//根据配置-动态生成裁剪图: 1.画布  2.缩放 3.生成微缩图
		$new_img = self::imagecreatetruecolor($new_width,$new_height);
		
		//这一句一定要有-设置画布为透明背景
		imagesavealpha($new_img, true);
		
		//拾取一个完全透明的颜色,最后一个参数127为全透明
		$zhibg = imagecolorallocatealpha($new_img, 255, 0, 0, 127);
		imagefill($new_img, 0, 0, $zhibg);
		imagecolortransparent($new_img,$zhibg);
		
		//合成图片
		//self::imagecopyresized($new_img,$old_img,0, 0, 0, 0,$new_width,$new_height,$old_width,$old_height);
		self::imagecopyresampled($new_img,$old_img,0, 0, 0, 0,$new_width,$new_height,$old_width,$old_height);
		$r = $r_v($new_img, $new_img_filename);
		if($r===true){
			$is_retain = isset($this->is_retain) ? $this->is_retain : false;
			if( $is_retain===false ) @unlink($old_imgfile);
			return $new_img_filename;  //裁剪成功-返回图片路径
		}else{
			return '';  //裁剪失败时
		}
	}
	 


	/**
	 * 微缩图按品质压缩图片
	 * @author berhp 2018.5.16
	 * @version 1.0
	 * @tutorial 设计：若没有传裁剪尺寸,尝试按品质压缩图片,若不能压缩的按处理前的图片返回路径;  详细查看php官方GD库
	 * @param array $config=array(
		 'image_name' 		=> 's_', 			//裁剪图片名称前缀
		 'image_path' 		=> 'Public/', 		//裁剪后的保存目录,''未配置,为当前目录下,★若不存在目录,自动创建(若linux系统,请先将对应存放图片根目录授权)
		 //'image_width' 	=> 100, 		    //裁剪图片-宽,单位px  --- 按品质压缩,暂时不支持尺寸控制(berhp 2018.5.16)
		 //'image_height' 	=> 100, 			//裁剪图片-高,单位px  --- 按品质压缩,暂时不支持尺寸控制(berhp 2018.5.16)
		 //'is_merge' 		=> false, 			//是否精确裁剪,默认false-不（按百分比缩放裁剪）  true-是
		 //'image_merge_type' => 'center', 		//方式,只有开启精确裁剪才会生效: 默认center-居中裁剪    lt- 从左上顶部开始,坐标(0,0), lb-左下底部   rt-右上顶部   rb-右下底部
		 'is_retain' 		=> false, 			//是否需要保留原图,默认false-不保留 true-保留
	 );
	 * @tutorial
	 * 原图体积是125k，现在我想看看在不同的等级压缩下会得到什么样的结果。如下为测试记录。
		imagepng($img,null,0); --> Size = 225K
		imagepng($img,null,1); --> Size = 85.9K
		imagepng($img,null,2); --> Size = 83.7K
		imagepng($img,null,3); --> Size = 80.9K
		imagepng($img,null,4); --> Size = 74.6K
		imagepng($img,null,5); --> Size = 73.8K
		imagepng($img,null,6); --> Size = 73K
		imagepng($img,null,7); --> Size = 72.4K
		imagepng($img,null,8); --> Size = 71K
		imagepng($img,null,9); --> Size = 70.6K
	 */
	public function pinzhi($old_imgfile='',$new_imgName='',$config=array()){
		ini_set('memory_limit', '-1'); 		//可使用内存无限大
		$config = $config ? $config : C('caijian');
		foreach ($config as $k=>$v){
			$this->$k = $v;
		}
		//获取图片后缀名
		$r = self::check_imgtype($old_imgfile); if(is_string($r)) exit($r);
		$r_k = $r[0];  $r_v = $r[1];
		
		//检查目录是否存在,不存在则创建
		if(!is_dir($this->image_path)) mkdir($this->image_path,0755,true);
		
		//命名
		if( $new_imgName ){
			$new_img_filename = $this->image_path.$new_imgName;    // xx/xx/xx/s_d72e016f310d7205cec6720b613f6f26.jpg, 根据$new_imgName传什么就拼接什么,包含图片后缀名 - berhp 2018.1.30
		}else{
			$new_img_filename = $this->image_path.$this->image_name.date('YmdHis',time()).'_'.rand(100000,999999).$new_imgName.'.'.$r[2]; // xx/xx/xx_20161114100412_448596.jpg
		}
		//引入图片
		$old_img = $r_k($old_imgfile);
		switch ( $r_v ){
			case "imagejpeg":
				$r = imagejpeg($old_img, $new_img_filename, 50 ); /*第三个参数为:int(0~100)文件最小->文件最大,不赋值默认大约为75*/
				break;
			case "imagepng":
				imagesavealpha($old_img,true); 					//★不要丢了图像的透明色;
				$r = imagepng($old_img, $new_img_filename, 9);  //第三个参数为:品质 int(0~9)文件最大->文件最小: 0-不压缩  */
				break;
			case "imagegif":
				//imagegif($image, $filename);  //无法品质压缩 http://php.net/manual/zh/function.imagegif.php
				$r = false;
				break;
			case "imagewbmp":
				//imagewbmp($image, $filename); //无法品质压缩 http://php.net/manual/zh/function.imagewbmp.php
				$r = false;
				break;
			default:
				$r = false;
		}
		if($r===true){
			$is_retain = isset($this->is_retain) ? $this->is_retain : false;
			if( $is_retain===false ) @unlink($old_imgfile);
			return $new_img_filename;  //裁剪成功-返回图片路径
		}else{
			return $old_imgfile;   //不能品质压缩的按处理前的图片返回
		}
	}
	
	/**
	 * 生成水印
	 * @tutorial 原图片是什么类型,则动态生成对应的水印新图片
	 * @param yes $old_imgfile 原图片路径  如 a.jpg   如 http://pic55.nipic.com/file/20141208/19462408_171130083000_2.jpg
	 * @param yes $new_imgfile 新图片保存路径, 如 b2.png， 如 img/b2.png, 如  http://p4.gexing.com/G1/M00/FB/26/rBACE1I6lnOyS7icAAAVO1bJxDk354_200x200_3.jpg
	 * @tutorial
		$config = array(
	        'image_name' => 'shui_', 			//生成水印后的图片名称前缀
	        'image_path' => '', 				//新图片的保存目录,''未配置,为当前目录下,★若不存在目录,需要先手动创建目录
	        'image_padding_x' => 100, 			//水印,x相对间距,单位px
	        'image_padding_y' => 100, 			//水印,y相对间距,单位px
	        'image_merge_type' => 'ls', 		//方式,默认:lt- 从左上顶部开始,坐标(0,0), lb-左下底部   rt-右上顶部   rb-右下底部
	        'image_opacity' => 60, 				//水印透明度 (0~100)
	        'image_watermarkfile' => 'b.png', 	//水印图片路径
		); 
		@example
		$gd = new \MyPHP\GD;
		$gd->shuiyin('http://pic55.nipic.com/file/20141208/19462408_171130083000_2.jpg','', $config);
		$gd->shuiyin('b.jpg','xx.jpg',$config);

	 */
	public function shuiyin( $old_imgfile='', $new_imgfile='', $config=array() ){
		$config = $config ? $config : C('shuiyin');
		foreach ($config as $k=>$v){
			$this->$k = $v;
		}
		//获取图片后缀名
		$r = self::check_imgtype($old_imgfile); if(is_string($r)) exit($r);
		$r_k = $r[0];  $r_v = $r[1];
		$r2 = self::check_imgtype($this->image_watermarkfile); if(is_string($r2)) exit('水印图片后缀名不支持');
		$r2_k = $r2[0];  $r2_v = $r2[1];

		//引入图片作为底图
		$img_a = $r_k($old_imgfile);
		//获得底图宽和高
		$width = imagesx($img_a);
		$height = imagesy($img_a);

		//引入图片作为水印图
		$img_b = $r2_k( $this->image_watermarkfile );   //如果是.jpg的图片，那么见第3行代码
		//获得水印图宽和高
		$shuiwidth = imagesx($img_b);
		$shuiheight = imagesy($img_b);
		
		//合并图片(底图,水印图,底图水印位置坐标,水印图的起点,水印宽,水印高,透明度)
		switch ( $this->image_merge_type ){
			case 'lt':
				$x = $this->image_padding_x;
				$y = $this->image_padding_y;
				imagecopymerge ($img_a,$img_b,$x,$y,0,0,$shuiwidth,$shuiheight, $this->image_opacity);
				break;
			case 'lb':
				$x = $this->image_padding_x;
				$y = ($height-$shuiheight-$this->image_padding_y);
				imagecopymerge ($img_a,$img_b,$x,$y,0,0,$shuiwidth,$shuiheight, $this->image_opacity);
				break;
			case 'rt':
				$x = ($width-$shuiwidth-$this->image_padding_x);
				$y = $this->image_padding_y;
				imagecopymerge ($img_a,$img_b,$x,$y,0,0,$shuiwidth,$shuiheight, $this->image_opacity);
				break;
			case 'rb': 
				$x = ($width-$shuiwidth-$this->image_padding_x);
				$y = ($height-$shuiheight-$this->image_padding_y);
				imagecopymerge ($img_a,$img_b,$x,$y,0,0,$shuiwidth,$shuiheight, $this->image_opacity);
				break;
			default: break;
		}		
		ob_clean();  //清除缓存

		//根据后缀名动态生成图片
		if(!$new_imgfile){
			$new_imgfile = $this->image_path.$this->image_name.date('YmdHis',time()).'_'.rand(100000,999999).'.'.$r[2];
		}
		$r_v($img_a, $new_imgfile);
		//销毁原来的图
		imagedestroy($img_a);
		imagedestroy($img_b);
	}
	
	
	/**
	 * 【内用】-检查图片后缀名
	 * @return array||string 
	 */
	private function check_imgtype($file=''){
		$_start = strrpos($file,'.');
		$_start = strlen($_start) ? $_start+1 : 0;
		$r = substr($file, $_start );
		$r = strtolower($r); //★转换为小写,便于后续判断 2017-3-11
		switch ( $r ){
			case 'jpg':
			case 'jpeg':
				return array('imagecreatefromjpeg', 'imagejpeg', $r);
				break;
			case 'png':
				return array('imagecreatefrompng', 'imagepng', $r);
				break;
			case 'gif':
				return array('imagecreatefromgif', 'imagegif', $r);
				break;
			case 'bmp':
				return array('imagecreatefromwbmp', 'imagewbmp', $r);
				break;
			default:
				return '原图后缀名不支持';
				break;
		}
	}
	

	/**
	 * 生成验证码临时图片
	 * @param no array $config 配置参数
	 * @return img 图片源码
	 * @tutorial <pre>
		$config = array(
					'width' 		=> 150, 		//图片宽,单位px
					'height' 		=> 50, 			//图片高,单位px
					'background' 	=> '#FFFFFF', 	//背景颜色,默认白色
					'font_size' 	=> 18, 			//字体大小,单位px
					'font_margin' 	=> 5, 			//间距 ,单位px
					'fontfile' 		=> MyPHP_DIR.'/Fonts/elephant.ttf', //字体路径,如 elephant.ttf,若是外部引用,填写index.php的相对路径
					'code_len' 		=> 6, 			//验证码长度
					'code_str' 		=> '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz', //随机字符集
					'is_imagearc' 	=> true, 		//是否画弧线
					'is_imagestring' 	=> true, 	//是否画自定义字符
					'imagestring_str'   => '.', 	//自定义字符,不支持中文
					'imagestring_font' => 10, 		//字符大小,单位px
					'imagestring_count' => 10, 		//字符出现次数
					'session_name'  => 'code', 		//自定义-验证码session存放名名称
					'is_matchcase' => false, 		//是否区分大小写,ture-区分,默认false-不区分(session会存放小写的): strtolower($code)
				);
	 * 	@example <pre> 
	 *   ★使用方法-MyPHP框架中时:
	 *   <控制器:如 home/index/code>
	    public function code(){
		    $gd = new \MyPHP\GD();
		    $gd->create_code();
	    }
	    
	     <控制器-检测code是否正确:如 home/index/check_code>
		public function check_code(){
			if( IS_GET ){
				$this->display('login');
			}else{
				$code = $_POST['code'];
				session_start();  //若没有开启session,需手动开启
				if( $_SESSION['code'] == $code ){
					echo 'yes';
				}else{
					echo 'no';
				}
			}
		}
	    
	 *   
	 *   <视图中:如  login.html>
			<!DOCTYPE html>
			<html>
			<head>
			<meta charset='utf-8'>
			<title>check code</title>
			</head>
			<body>
			<form action='http://127.0.0.1/myphp/index.php/home/index/check_code' method='post'>
			<img src="http://127.0.0.1/myphp/index.php/home/index/code">
			<!-- 等价于
				<form action='<?php echo U() ?>' method='post'>
				<img src="<?php echo U('home/index/code','?a='.time()) ?>" onclick='javascript:this.src+=1'>
			-->
			<input type='text' name='code' placeholder='请输入验证码' required='required'>
			<input type='submit' value='提交'>
			</form>
			</body>
			</html>
	 *   
	 *   
	 *   ★使用方法-外部引用时: 如都在同一目录下
	 *   0. elephant.ttf  字体文件,若放在别出,注意服务器中的路径配置
	 *   
	 *   1. index.html
			<!DOCTYPE html>
			<html>
			<head>
			<meta charset='utf-8'>
			<title>check code</title>
			</head>
			<body>
			<form action='login.php' method='post'>
			<img src="img.php">
			<input type='text' name='code' placeholder='请输入验证码' required='required'>
			<input type='submit' value='提交'>
			</form>
			</body>
			</html>

	 *   
	 *   2. img.php  //这个.PHP实质是个图片
	 *   <?php 
			$config = array(
					'width' 		=> 150, 		//图片宽,单位px
					'height' 		=> 50, 			//图片高,单位px
					'background' 	=> '#FFFFFF', 	//背景颜色,默认白色
					'font_size' 	=> 18, 			//字体大小,单位px
					'font_margin' 	=> 5, 			//间距 ,单位px
					'fontfile' 		=> 'elephant.ttf', //字体路径,如 elephant.ttf
					'code_len' 		=> 6, 			//验证码长度
					'code_str' 		=> 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789', //随机字符集
					'is_imagearc' 	=> true, 		//是否画弧线
					'is_imagestring' 	=> true, 	//是否画自定义字符
					'imagestring_str'   => '*', 	//自定义字符
					'imagestring_font' => 10, 		//字符大小,单位px
					'imagestring_count' => 15, 		//字符出现次数
					'session_name'  => 'code', 		//自定义-验证码session存放名名称
					'is_matchcase' => true, 		//是否区分大小写,默认ture-区分,false-不区分(session会存放小写的)
					);
			require "GD.class.php";
			$r = new \MyPHP\GD();
			$r->create_code($config);
		?>
		
		3. login.php  //检测session
		<?php 
		define('IS_GET', $_SERVER['REQUEST_METHOD']=='GET' ? true: false);
		if( IS_GET ){
			include 'index.html';
		}else{
			session_start();
			//var_dump($_SESSION);
			$r = $_POST;
			if($_SESSION['code'] == $r['code']){
				echo 'ok';
			}else{
				echo 'no';
			}
		}
		?>
	 * 
	 *    
	 */
	public function create_code($config=array()){
		$config = $config ? $config : C('code');
		foreach ($config as $k=>$v){
			$this->$k = $v;
		}
		$this->_img = self::imagecreatetruecolor($this->width, $this->height);
		if (!$this->background) {
			$this->background = self::imagecolorallocate($this->_img, 0, 0, 0);
		} else {
			$this->background = self::imagecolorallocate($this->_img, hexdec(substr($this->background, 1,2)), hexdec(substr($this->background, 3,2)), hexdec(substr($this->background, 5,2)));
		}
		self::imagefilledrectangle($this->_img,0,0,$this->width,$this->height,$this->background); //填充背景色
		$this->code = self::_actionCode($this->code_len, $this->code_str);
		//画验证码
		$x = $this->height/$this->code_len;
		$y = $this->height;
		for ($i=0;$i<$this->code_len;$i++){
			$_font_color = self::imagecolorallocate($this->_img, rand(0,255), rand(0,255), rand(0,255));
			$_x = $x+$i*($this->font_size+$this->font_margin);
			self::imagettftext($this->_img, $this->font_size, rand(0,30), $_x, $y/1.4, $_font_color, $this->fontfile, $this->code[$i]);
		}
		//画弧线
		if($this->is_imagearc === true){
			self::imagearc($this->_img, $this->height*0.2, $this->height, $this->width*2, rand($this->height, $this->height*1.5), 0, 0, $_font_color);
		}
		
		//画字符
		if($this->is_imagestring === true){
			for($i=0;$i<$this->imagestring_count;$i++){
				self::imagestring($this->_img, $this->imagestring_font, rand(0, $this->width-$this->imagestring_font), rand(0, $this->height-$this->imagestring_font), $this->imagestring_str, self::_get_rand_color());
			}
		}
		
		if(!isset($_SESSION)) session_start(); //★需放前面

		//输出图像
		header("content-type:image/png\r\n");
		$r = self::imagepng($this->_img);
		self::imagedestroy($this->_img);		
		if( $r===true ){
			if( isset($this->session_name) ){
				if(!$this->session_name) return;
				$_SESSION[ $this->session_name ] = $this->is_matchcase===true ? $this->code : strtolower($this->code);
			}
		}
	}

	
	/**
	 * 生成随机颜色
	 */
	private function _get_rand_color(){
		return self::imagecolorallocate($this->_img, rand(0,255), rand(0,255), rand(0,255));
	}

	
	/**
	 * 【内用】-生成随机验证码
	 * @param string $len  长度,如 6
	 * @param string $strs 字符集,如 012456asdf
	 * @return string
	 */
	private function _actionCode( $len='', $strs='' ){
		$str = '';
		$_code_str_len = strlen($strs) - 1;
		for($i=0; $i<$len; $i++){
			$r = rand(0, strlen($strs));
			$str .= $strs[$r];
		}
		return $str;
	}
	
	
	/**
	 * 【内用】-创建画布
	 */
	private function imagecreatetruecolor($width, $height){
		return imagecreatetruecolor($width, $height);
	}
	
	/**
	 * 【内用】-缩放
	 * @tutorial 缩放图像的算法比较粗糙
	 * @return boolean
	 */
	private function imagecopyresized($dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h){
		return imagecopyresized($dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
	}
	
	/**
	 * 【内用】-缩放
	 * @tutorial 其像素插值算法得到的图像边缘比较平滑.(但该函数的速度比 imagecopyresized() 慢)
	 * @return boolean
	 */
	private function imagecopyresampled($dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h){
		return imagecopyresampled($dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
	}
	
	/**
	 * 【内用】-裁剪
	 * @return boolean
	 */
	private function imagecopy($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h){
		return imagecopy($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h);
	}

	/**
	 * 【内用】-分配颜色
	 * @param unknown $image  //$this->_img
	 * @param int $red 	  // 0~255
	 * @param int $green  // 0~255
	 * @param int $blue   //0~255
	 */
	private function imagecolorallocate($image, $red, $green, $blue){
		return imagecolorallocate($image, $red, $green, $blue);
	}
	
	/**
	 * 【内用】-画矩形
	 * @return boolean
	 */
	private function imagefilledrectangle($image, $x1, $y1, $x2, $y2, $color){
		return imagefilledrectangle($image, $x1, $y1, $x2, $y2, $color);
	}

	/**
	 * 【内用】-画弧线
	 * @param unknown $image
	 * @param unknown $cx   弧线-圆中心点x
	 * @param unknown $cy	弧线-圆中心点y
	 * @param unknown $width  宽度
	 * @param unknown $height 高度
	 * @param unknown $start  起始度数
	 * @param unknown $end    结束度数
	 * @param unknown $color  颜色
	 * @return boolean
	 * @tutorial
	 *   原点坐标为 (0,0) 为图片的左上角，参数 cx、cy 为椭圆心坐标，参数 w 为水平轴长，参数 h 为垂直轴长，参数 s 及 e 分别为起始角与结束角，参数 col 为弧线的颜色。参数 im 表示图形的 handle。
	 */
	private function imagearc($image, $cx, $cy, $width, $height, $start, $end, $color){
		return imagearc($image, $cx, $cy, $width, $height, $start, $end, $color);
	}
	
	/**
	 * 【内用】 - 画字符
	 * @return boolean
	 */
	private function imagestring($image, $font, $x, $y, $string, $color){
		return imagestring($image, $font, $x, $y, $string, $color);
	}

	
	/**
	 * 【内用】-添加文字
	 * @return boolean
	 */
	private function imagettftext($image, $size, $angle, $x, $y, $color, $fontfile, $text){
		return imagettftext($image, $size, $angle, $x, $y, $color, $fontfile, $text);
	}
	
	/**
	 * 【内用】-生成.png图片
	 * @param yes $image 资源图片
	 * @param no string $filename 存放图片路径,如 1.png 可以不填,则不生成,仅临时图片
	 * @return boolean
	 */
	private function imagepng($image, $filename = null, $quality = null, $filters =null){
		return imagepng($image, $filename = null, $quality = null, $filters =null);
	}
	
	/**
	 * 【内用】-销毁图片
	 *  @return boolean
	 */
	private function imagedestroy($image){
		return imagedestroy($image);
	}
	
}