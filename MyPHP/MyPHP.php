<?php 
/**
 * MyPHP公共入口文件
 * @tutorial
 *
 */
// 常用配置
const MyPHP_DIR = __DIR__;
const MyPHP_VERSION = '1.0.0';


define('APP_CLASS_FILE_EXT', '.class.php');
defined('IS_POST')?:define('IS_POST', ( ($_SERVER['REQUEST_METHOD'] == 'POST')? true:false) );
defined('IS_GET')?:define('IS_GET', ( ($_SERVER['REQUEST_METHOD'] == 'GET')? true:false) );

defined('APP_DEBUG')?:define('APP_DEBUG',false); 						//是否为debug模式,默认false
defined('APP_HEMLCACHE')?:define('APP_HEMLCACHE',false); 				//是否开启html纯静态缓存功能,默认 false

defined('DEFAULT_EXT_NAME')?:define('DEFAULT_EXT_NAME', '\Home\v1'); 	//未定义时-默认扩展-项目名(仅IS_API===true才会生效)
defined('DEFAULT_EXT_NAME_API')?:define('DEFAULT_EXT_NAME_API', 'Home'); 	//未定义时-默认扩展-Api模块(仅IS_API===true才会生效)

defined('DEFAULT_MODULE')?:define('DEFAULT_MODULE', 'Home'); 			//未定义时-默认模块
defined('DEFAULT_CONTROLLER')?:define('DEFAULT_CONTROLLER', 'Index'); 	//未定义时-控制器
defined('DEFAULT_ACTION')?:define('DEFAULT_ACTION', 'index');			//未定义时-方法
defined('APP_VIEW_FILE_EXT')?:define('APP_VIEW_FILE_EXT','.html'); 		//视图文件后缀名
defined('DATE_DEFAULT_TIMEZONE_SET')?:define('DATE_DEFAULT_TIMEZONE_SET','PRC'); //时区,默认北京时间
date_default_timezone_set( DATE_DEFAULT_TIMEZONE_SET );
define('TIMESTR', time());  //当前时间戳
define('DATESTR', date('Y-m-d H:i:s',TIMESTR));  //当前日期 xxxx-xx-xx xx:xx:xx
defined('IS_AJAX')?:define('IS_AJAX',isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'?true:false);
defined('APP_PATH')?:define('APP_PATH','./Application/');   //项目运行目录
defined('APP_PATH_DEFALUE_CONTENTS')?:define('APP_PATH_DEFALUE_CONTENTS', MyPHP_DIR.'/Template/_APP_PATH_DEFALUE_CONTENTS.zip'); //首次运行,默认动态创建项目的基本内容
defined('APP_PATH_DEFALUE_CONTENTS_API')?:define('APP_PATH_DEFALUE_CONTENTS_API', MyPHP_DIR.'/Template/_APP_PATH_DEFALUE_CONTENTS_API.zip'); //IS_API===true接口模式时,首次运行,默认动态创建项目的基本内容

defined('APP_RUNTIME_PATH')?:define('APP_RUNTIME_PATH', APP_PATH.'Runtime/');   //缓存目录
defined('APP_TEMPLATE_PATH')?:define('APP_TEMPLATE_PATH', MyPHP_DIR.'/Template/'); //模版目录


defined('APP_ROUTE_BEGIN_NUMBER')?:define('APP_ROUTE_BEGIN_NUMBER', 0); 		//定义路由,下标从第几个开始解析(模块,控制器,方法),类型int
defined('APP_ROUTE_OVER_NUMBER')?:define('APP_ROUTE_OVER_NUMBER', 3); 			//定义路由,下标从第几个开始,才转换为参数,类型int
defined('IS_API')?:define('IS_API', false); //是否为API接口(默认false-不是 true-是),API路由规则不同

define('IS_CGI',(0 === strpos(PHP_SAPI,'cgi') || false !== strpos(PHP_SAPI,'fcgi')) ? 1 : 0 );
define('IS_WIN',strstr(PHP_OS, 'WIN') ? 1 : 0 ); //是否为WIN环境
define('IS_CLI',PHP_SAPI=='cli'? 1   :   0);  //是否为命令行模式
if(!IS_CLI) {
    // 当前文件名
    if(!defined('_PHP_FILE_')) {
        if(IS_CGI) {
            //CGI/FASTCGI模式下
            $_temp  = explode('.php',$_SERVER['PHP_SELF']);
            define('_PHP_FILE_',    rtrim(str_replace($_SERVER['HTTP_HOST'],'',$_temp[0].'.php'),'/'));
        }else {
            define('_PHP_FILE_',    rtrim($_SERVER['SCRIPT_NAME'],'/'));
        }
    }
    if(!defined('__ROOT__')) {
        $_root  =   rtrim(dirname(_PHP_FILE_),'/');
        define('__ROOT__',  (($_root=='/' || $_root=='\\')?'':$_root));
    }
}
define('__PUBLIC__', __ROOT__.'/Public');
define('__UPLOADS__', __ROOT__.'/Uploads');

$_http = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
//$_url =  $_http.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'];   //http://192.168.0.200:8088/index.php 或如 http://192.168.0.200/rwxphp/index.php
$_url =  $_http.$_SERVER['HTTP_HOST'].__ROOT__.'/'; 		//更新,只保留如: http://192.168.0.200:8088/  或如  http://192.168.0.200/rwxphp/   -- berhp 2017.12.20
define('__URL__', $_url);

// 加载核心文件
require MyPHP_DIR.'/Library/init.class.php';

// 初始化
MyPHP\init::start();