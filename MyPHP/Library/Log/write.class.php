<?php
/**
 * @eg:
   $config = array(
    'filePrefix' 	=>'error_',  	//生成的文件前缀,非必须
    'ext' 			=>'.log', 	        //文件后缀,非必须,如.txt
    'filePath' 	    => APP_RUNTIME_PATH.'Logs/',  //存放目录,相对于网站index.php目录,默认为项目Runtime/Logs目录下
    'fileSize' 	    =>10,			//文件大小,单位MB,超过了会自动创建新的文件
    'timezone_set'  => 'PRC',	//时区,默认北京8点
    );
    $log = new \Log\write($config); //仅框架外单独使用才需传配置 $config,并需先include此类
    $log->write('[file]:404 error ');
 */
namespace Log;
class write{
    /**
     * 【内用】-容器，勿赋值
     * @var string
     */
    private  $dir;

    /**
     * 【内用】-容器，磁盘路径-容器,勿赋值
     * @var string
     */
    private $fileName = '';

    /**
     * write constructor.
     * @param array $config = array(
            'filePrefix' 	=>'error_',  	//生成的文件前缀,非必须
            'ext' 			=>'.log', 	        //文件后缀,非必须,如.txt
            'filePath' 	    => APP_RUNTIME_PATH.'Logs/',  //存放目录,相对于网站index.php目录,默认为项目Runtime/Logs目录下
            'fileSize' 	    =>10,			//文件大小,单位MB,超过了会自动创建新的文件
            'timezone_set'  => 'PRC',	//时区,默认北京8点
      )
     */
    public function __construct($config=array()){
    	$config = $config ? $config : C('log');
    	foreach ($config as $k=>$v){
    		$this->$k = $v;
    	}
    	defined('DATE_DEFAULT_TIMEZONE_SET') ? date_default_timezone_set( DATE_DEFAULT_TIMEZONE_SET ) : date_default_timezone_set( $this->timezone_set );
    	$this->filePrefix .= date('Ymd',time());
        $this->fileName = SITE_DIR.$this->filePath.$this->filePrefix.$this->ext;  //E:\www\myphp/Application/Runtime/Logs/error_20161117.log
    }

    /**
     * 创建多重文件夹
     * @param string $string
     */
    function create_folders($dir, $qx=0777){
        return is_dir($dir) or mkdir($dir, $qx, true);
    }

    /**
     * 执行写日志
     * @param string $string
     * @return boolean
     */
    public  function  write($string=''){
        if(!$string) return true;
        $file = dirname($this->fileName);
        if(!is_dir($file)){
            self::create_folders($file, 0775);
        }
        if( is_file($this->fileName) ){
            $fileSize = abs(filesize($this->fileName));
        }else{
            $fileSize = 0;
        }
        $str='';
        $this->fileSize = $this->fileSize * 1024 * 1024; //转换为字节
        if($string!= '' && $fileSize > 0 && $fileSize <= $this->fileSize ){
            $str.= "\n";
        }else if($fileSize > $this->fileSize ){
            //将之前的文件重命名并保存下来
            rename( $this->fileName, $this->fileName.'_'.date('His',time()).$this->ext );
            file_put_contents($this->fileName,'',FILE_APPEND); //重新生成新文件,避免其他异步写不进去报错。
        }
        $param = '';
        if( $_SERVER['REQUEST_METHOD'] == 'POST' ){
        	$param .= json_encode($_POST);
        }
        $_file = '';
        if( $_FILES ){
        	$_file .= json_encode($_FILES);
        }
        $_str = $_SERVER['REMOTE_ADDR'].' '.$_SERVER['REQUEST_METHOD'].' '.$_SERVER['REQUEST_URI'].' T'.date('Y-m-d H:i:s',time());
        if( $param )  $_str .= "\n[param]:{$param}";
        if( $_file )  $_str .= "\n[upfile]:{$_file}";
        if(count(self::_cache('xx'))<2){
        	$str.= "\n".$_str."\n";
        	$str.= $string;
        }else{
        	$str.= $string."\n";
        }
        $r = file_put_contents($this->fileName,$str,FILE_APPEND);
        if($r===false) return false;
        return true;
    }
    
    private function _cache($str=''){
    	static $_array=array();
    	if( $str ) $_array[] = $str;
    	return $_array;
    }
    

}