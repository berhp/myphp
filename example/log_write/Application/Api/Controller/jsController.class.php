<?php 
/**
 * 授权提供给JS调接口获取数据
 * @author berhp
 * @tutorial 设计:
 *  1. js 有安全性,不支持跨域访问
 *  2. 若需要JS跨域访问,需要服务端那边申请授权
 *  3. 此类为授权设计的，客户端JS那边是本地都可以通过服务端接口方式访问
 *  
 *  4. 此类中public的方法,支持URL直接方法
 *  5. 若你并不对外开发JS接口查询,可直接将此文件删除
 */
namespace Api\Controller;
class jsController{
	private $_signstr = 'berhp'; //自定义签名值
	
	
	/**
	 * 授权JS访问方法
	 * @param $api 接口名
	 * @tutorial  设计:
	 *  0. 需要开启 php_curl.dll扩展
	 *  1. 根据U方法,动态获取当前url地址，如  http://127.0.0.1/xx/xx/xx
	 *  2. file_get_contents() 源生PHP方法获取接口返回的数据字符串
	 *  3. 申请头文件为 JSON 返回给客户端,便于JS调用
	 *  4. 客户端,只能做一些简单访问接口,展示在html内容中
	 *  @example
	 *  server: 假设服务器地址为 http://192.168.0.200/myphp/index.php
	 *  		假设有个接口  User/Api/login
	 *  
	 *  cli客户端: index.html
	 *  
		<!DOCTYPE html>
		<html lang="en">
		<html>
		<head>
		    <meta charset="UTF-8">
		    <title>找老师</title>
		    <meta content="width=device-width,user-scalable=no,initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0" name="viewport">
		    <meta name="apple-mobile-web-app-capable" content="yes">
			<link rel="stylesheet" href="css/base.css">
		    <script src="jquery-1.11.3.js"></script>
		</head>
		<body>
			<div id='content'></div>
		</body>
		<script>
		    $.post("http://192.168.0.200/haochengji/index.php/api/js/get?_sign=YmVyaHA=&api=Thirdparty/Api/recruit_list", {partyid:1}, function(data){
			   console.log(data); 		//控制台打印data服务器回返数据
			   alert(data.state.url); 	//直接弹出返回的json中X一个对象值
			   
			   //开始业务处理
			    $('#content').html(data.state.url);
			    
		   });
		</script>
		</html>
	 *  
	 */
	public function get(){
		$api = I('api','');       if(!$api) exit();
		$_sign = I('_sign', '');  if( base64_decode($_sign) != $this->_signstr ) exit(); //签名不正确
		$url = U($api);
		
		//curl_post方法
		$curlPost = $_REQUEST;
		$header = array("Content-Type:text/html;charset=utf-8");  	//自定义curl访问时的头文件,多条头信息用,一维数组
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		$data = curl_exec($ch);
		curl_close($ch);
		
		//输出数据
		header('Content-Type:application/json; charset=utf-8');
		header('Access-Control-Allow-Origin:*'); //授权支持JS跨域访问		
		exit($data);
	}
	
	/**
	 * 签名加密
	 * @tutorial
	 * 1.客户端JS访问时,需要输入相对应正确的加密签名才可以
	 * 2.私有方法,服务端生成提供给客户端JS调用使用,不能直接url访问此方法
	 */
	private function create_sign(){
		return base64_encode($this->_signstr);
	}
	
}