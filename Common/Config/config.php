<?php 
/**
 * //自定义适用于整个架构的公共配置,包含于(api 前端 后端..)
 * @author berhp
 * @version 1.0
 * @tutorial 
 * 	1,若您需要区分配置名,可以将此文件 复制,粘贴,修改文件名为你自定义的,如 config.php->msg.php
 * 	2,程序会自动识别,加载此目录下的所有后缀名为".php"的配置文件,如  msg.php; app.php
 * @example 
	return array(
		'demo_key' => 'demo_value',
	);
 */
//自定义配置$key=>$value
return array(

		'db' => array(
				'DB_TYPE'               => 'mysqli',		// 数据库类型
				'DB_HOST'               => '127.0.0.1', 	// 服务器地址
				'DB_NAME'               => 'demo',  		// 数据库名
				'DB_USER'               => 'root',  		// 用户名
				'DB_PWD'                => 'root',  		// 密码
				'DB_PORT'               => '3306',  		// 端口
				'DB_PREFIX'             => 'demo_',      	// 数据库表前缀
				'DB_CHARSET' 			=> 'utf8', 			// 数据库编码
		),
);