<?php 
/**
 * 演示在框架内的-base64上传处理;
 */
$m = new \MyPHP\Upload();
$m->exts = array('jpeg','jpg','png','gif','bmp','txt','doc','docx');
//$m->is_prefix = true;  //是否拼接地址的前缀
//$m->prefix_str = '/';  //自定义前缀

p($m);
$r = $m->upload_base64('xx,img1,img2,file,file2');
p($r);
var_dump($m->_msg);
die;