<?php 
// PHP版本判断
if(version_compare(PHP_VERSION,'5.3.0','<'))  die('require PHP > 5.3.0 !');

// 配置
define('APP_DEBUG',true);
define('APP_PATH', 'Application/');
define('SITE_DIR', __DIR__.'/' );
//define('APP_FIRST', 'first.php');  //项目格外首运行文件,若不设置不会运行,相对于网站根目录
define('APP_HEMLCACHE', false); 	 //开启纯静态缓存功能，默认是 false

// 引用框架
require "./MyPHP/MyPHP.php";