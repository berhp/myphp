<?php 
// PHP版本判断
if(version_compare(PHP_VERSION,'5.3.0','<'))  die('require PHP > 5.3.0 !');

// 配置
define('APP_DEBUG',true);
define('APP_PATH', 'Api/');
define('SITE_DIR', __DIR__.'/' );
//define('APP_FIRST', 'first.php');  //项目格外首运行文件,若不设置不会运行,相对于网站根目录
define('APP_HEMLCACHE', false); 	 //开启纯静态缓存功能，默认是 false

define('IS_API', true);					//是否开启API接口访问模式(默认false-不  true-开启),API的访问路由不同
define('APP_ROUTE_BEGIN_NUMBER', 2); 	//定义路由,下标从第几个开始解析(模块,控制器,方法),类型int
define('APP_ROUTE_OVER_NUMBER', 5); 	//定义路由,下标从第几个开始,才转换为参数,类型int

//define('DEFAULT_EXT_NAME', '\Home\v1'); //未定义时-默认项目APP + APP接口版本
//define('DEFAULT_MODULE', 'Home'); 		//未定义时-默认项目APP-模块
//define('DEFAULT_CONTROLLER', 'Index'); 	//未定义时-默认项目APP-控制器
//define('DEFAULT_ACTION', 'index');		//未定义时-默认项目APP-方法

// 引用框架
require "./MyPHP/MyPHP.php";