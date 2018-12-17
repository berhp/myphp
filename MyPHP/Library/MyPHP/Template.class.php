<?php
namespace MyPHP;
/**
 * Class Template 模版-自定义标签基础类
 * @author berhp 2016.11.22
 * @tutorial
 *   1.支持有属性的自定义标签，如 <aa name='a' pass='b'>bc</aa>
 *   2.支持没有属性的自定义标签,如 <bb>cb</bb>
 *   3.标签属性动态获取,写什么获取到什么
 *   4.当前类,暂时只提供开放标签写法
 * @package MyPHP
 */
class Template{

    // 模板页面中引入的标签库列表
    protected $tagLib = array();

    // 当前模板文件
    protected $templateFile = '';

    // 模板变量
    public $config = array();

    // 容器
    private $str = '';
    private $data = array(); //数据
    private $_preg = array();
    private $_pregkey = ''; //当前preg的key,如aa

    public function __construct($str=''){
        if(!defined('MyPHP_DIR')) exit;
        if(!$str) exit;
        $this->str = $str;
        self::_action_first();

        self::_create_str_preg();
        self::_action_str_preg();
    }

    /**
     * 特殊符号转译,便于正则表达式
     * @param $str
     * @return mixed
     */
    private function stripPreg($str){
        return str_replace(
            array('{', '}', '(', ')', '|', '[', ']', '-', '+', '*', '.', '^', '?'),
            array('\{', '\}', '\(', '\)', '\|', '\[', '\]', '\-', '\+', '\*', '\.', '\^', '\?'),
            $str);
    }

    /**
     * 优先处理替换{$a} -> <?php echo $a ?>
     */
    private function _action_first(){
        $preg = array(
            1           => '/\{\$(.*?)\}/is',         		//{$xx}
            2           => '/\{\:(.*?)\}/is',         		//{:U('xx/xx')}
            'include'   => '/\<include(.*?)\/\>/is',   		//<include 'xx.xx'/>
        	'include2'  => '/\<!--#include(.*?)--\>/is',   	//<!--#include src='xx.xx' -->
        );
        foreach($preg as $k=>$v){
            $this->str = preg_replace_callback( $v, 'self::_action_first_preg_'.$k, $this->str );
        }
    }

    /**
     * 【内置方法】-实现替换{} (ok)s
     * @tutorial {$xx}
     * <?php echo $xx ?>
     */
    private function _action_first_preg_1($r){
        $_str = '';
        $_str .= '<?php echo $'.$r[1].' ?>';
        return $_str;
    }

    /**
     * 【内置方法】-实现替换{: } (ok)
     * @tutorial {:U('xx/xx')}
     * <?php echo U('') ?>
     */
    private function _action_first_preg_2($r){
    	//var_dump($r); die;
        $_str = '';
        $_str .= '<?php echo '.$r[1].' ?>';
        return $_str;
    }

    /**
     * 【内置方法】-实现替换include  (读取伪静态缓存,不存在时创建)  (ok)
     * @tutorial <include src="public_head.html"/>
     * <?php include('XX/Runtime/Cache/13f7b7029a28f85ea369aa1d4ebcd544') ?>
     */
    private function _action_first_preg_include($r){
        $param = self::_action_param($r[1]);
        $src = isset($param['src']) ? $param['src'] : '';
        if(!$src) return '';
        $_file = APP_PATH.MODULE_NAME.'/View/'.$src;
        if(!is_file($_file)) return '';
        $_cache_dir = C('cache.filePath');
        is_dir($_cache_dir) or mkdir($_cache_dir, 0755,true);
        $_cache_file = md5( strtolower($_file) ); //★统一转换为小写
        $_filename = $_cache_dir.$_cache_file;
        if( !is_file( $_filename ) ){
            return self::_include_create_cache( $_file, $_filename );
        }else{
            $c_filemtime = C('cache.filemtime');
            if( ( filemtime($_file)-filemtime($_filename) ) > $c_filemtime ){
                return self::_include_create_cache( $_file, $_filename );
            }else{
                return file_get_contents( $_filename );  //缓存未失效时，直接读取内容
            }
        }
    }
    
    /**
     * 【内置方法】-实现替换include  (读取伪静态缓存,不存在时创建)  (ok) 书写方式二
     * @tutorial 
     * 	设计：
     * 		此方式,可以使前端工程师,修改调整代码的时候,不用报'标签不存在'的错误提示。
     * 
     * @tutorial <!--#include src="public_head.html" -->
     * <?php include('XX/Runtime/Cache/13f7b7029a28f85ea369aa1d4ebcd544') ?>
     */
    private function _action_first_preg_include2($r){
    	$param = self::_action_param($r[1]);
    	$src = isset($param['src']) ? $param['src'] : '';
    	if(!$src) return '';
    	$_file = APP_PATH.MODULE_NAME.'/View/'.$src;
    	if(!is_file($_file)) return '';
    	$_cache_dir = C('cache.filePath');
    	is_dir($_cache_dir) or mkdir($_cache_dir, 0755,true);
    	$_cache_file = md5( strtolower($_file) ); //★统一转换为小写
    	$_filename = $_cache_dir.$_cache_file;
    	if( !is_file( $_filename ) ){
    		return self::_include_create_cache( $_file, $_filename );
    	}else{
    		$c_filemtime = C('cache.filemtime');
    		if( ( filemtime($_file)-filemtime($_filename) ) > $c_filemtime ){
    			return self::_include_create_cache( $_file, $_filename );
    		}else{
    			return file_get_contents( $_filename );  //缓存未失效时，直接读取内容
    		}
    	}
    }


    /**
     * 【内用】- ★创建include文件的伪静态缓存 (ok)
     * @tutorial   include源文件中的自定义 HTML 常量替换  +  递归处理 子文件中的自定义标签解析
     * @return string 返回解析后的string
     * @eg: Index_index.html
            <!DOCTYPE html>
            <html lang="en">
            <head>
            <meta charset="UTF-8">
            <title>中立教育</title>
            <link href="__PUBLIC__/css/bootstrap.min.css" rel="stylesheet">
            <link rel="stylesheet" href="__PUBLIC__/css/common.css">
            <link rel="stylesheet" href="__PUBLIC__/css/home.css">
            <script src="__PUBLIC__/js/jquery-1.11.3.js"></script>
            <script src="__PUBLIC__/js/bootstrap.min.js"></script>
            <script src="__PUBLIC__/js/common.js"></script>
            <script src="__PUBLIC__/js/home.js"></script>
            </head>
            <body>
            <div class="full">
            <include src="public_header.html"/>
            </div>
            <div>
            <include src="public_footer.html"/>
            </div>
            </body>
            </html>
     *  @eg: public_header.html
            <!-- 中立教育头部 -->
            <include src='public_other.html'/>  <!--这里为子文件中在include其它文件-->
            <div class="header">
            <div class="nav content oh">
            <div class="fl mid-5"><img src="__PUBLIC__/img/logo.png" alt=""></div>
            <div class="fr mid-5 tr">
            <a class="active" href="{:U('Home/Index/index')}">中立首页</a> 丨
            <a href="{:U('Home/Index/about')}">关于中立</a> 丨
            <a href="{:U('Home/Index/news')}">新闻动态</a> 丨
            <a href="{:U('Home/Index/tech')}">技术支持</a> 丨
            <a href="{:U('Home/Index/contact')}">联系我们{$cs}</a>
            </div>
            </div>
            </div>
     *
     */
    private function _include_create_cache( $_file, $_filename ){
        $content = file_get_contents( $_file );
        $replace =  array(
            '__ROOT__'      =>  __ROOT__,       // 当前网站地址
            '__PUBLIC__'    =>  __PUBLIC__,		// 站点公共目录
            '__UPLOADS__'    =>  __UPLOADS__,	// 上传文件目录
        );
        if(is_array(C('TMPL_PARSE_STRING')) )  $replace =  array_merge($replace,C('TMPL_PARSE_STRING'));  //#允许用户自定义模板的字符串替换
        $content = str_replace(array_keys($replace),array_values($replace),$content);
        $model = new \MyPHP\Template\HtmlTemplate($content);
        $content = $model->str;
        $r = file_put_contents( $_filename, $content);
        if($r===false) return '';
        return $content;
    }

    /**
     * 获取动态自定义标签-属性  (ok)
     * @param $str  //如  name='a' pass='b'
     * @return array
     * @example
     *   $array = array('name'=>'1','pass'=>'2');
     */
    private function _action_param($str){
        $data = array();
        $r = explode(' ', $str); if(!$r) return $data;
        $_key 	= '';
        $_value = '';
        if($r){
            foreach($r as $k=>$v){
                if(!empty($v)){
                	$_r = mb_stripos( $v, '=', null, 'utf-8');
                	if($_r==0){
                		continue;  //跳出本次循环,过滤没有属性名特殊情况:  ="xx"
                	}
                	$_key 	= mb_substr($v,0,$_r,'utf-8');
                	$_value = mb_substr($v,$_r+2,-1,'utf-8');
                	$data[$_key] = $_value;
                }
            }
        }
        return $data;
    }


    /**
     * 获取已替换后的内容
     */
    public function getstr(){
        return $this->str;
    }

    /**
     * 获取当前数据
     */
    public function getdata(){
        return $this->data;
    }

    /**
     * 设置数据
     */
    public function setdata($key='',$data=array()){
        $this->data[$key] = $data;
    }


    /**
     * 自定义标签解析,基础方法  (ok)
     * @retrun string
     */
    private function _action_str_preg(){
        if(!$this->_preg) return '';
        $str = $this->str;
        foreach($this->_preg as $k=>$v){
            $this->_pregkey = $k;
            $str = preg_replace_callback($v, 'self::_preg_replace_callback', $str);
        }
        $this->str = $str;
    }

    /**
     * 自定义回调方法  (ok)
     * @param yes array $r  //正则回调传的参数 preg_replace_callback()回调这里
     * @return string
     * @tutorial 动态获取自定义标签属性值,并动态调用 子类中的方法
     * $r[0]   // 固定来源字符串,低层类传值过来,如: <aa name='a' pass='b'>bc</aa>
     * $r[1]   // 自定义标签中的属性字符串，如  name='a' pass='b'
     * $r[2]   // 自定义标签中的内容,如 bc
     * @eg:
     *     $str="<aa name='a' pass='b'>bc</aa><aa>cc</aa>gg<bb>cb</bb>";
     *     $a = new \MyPHP\Template\HtmlTemplate(  $str );
            var_dump($a);
     */
    private function _preg_replace_callback($r){
        $_fun = '_'.$this->_pregkey;
        $_str = $r[0];
        $_param = self::_action_param($r[1]);
        $_content = $r[2];
        return $this->$_fun( $_str, $_param, $_content );
    }

    /**
     * 根据自定义标签库,动态创建正则表达式 (ok)
     * @return array
     * @example
     *  $preg['aa']='/\<aa(.*?)\>(.*?)\<\/aa\>/is';
        $preg['bb']='/\<bb\>(.*?)\<\/bb\>/is';
     */
    private function _create_str_preg(){
        $preg = array();
        if(!$this->tagLib) return;
        foreach( $this->tagLib as $k=>$v ){
            $preg[$v] = '/\<'.$v.'(.*?)\>(.*?)\<\/'.$v.'\>/is';
        }
        $this->_preg = $preg;
    }


}