<?php 
/**
 * 演示在框架内的文件上传处理;
 */
$m = new \MyPHP\Upload();
$m->exts = array('jpeg','jpg','png','gif','bmp','txt','doc','docx');
p($m);
$r = $m->upload();
p($r);
var_dump($m->_msg);
die;