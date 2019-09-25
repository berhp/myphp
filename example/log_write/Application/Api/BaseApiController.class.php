<?php
/**
 * 项目自定义API基础类
 */
namespace Api;
use MyPHP\Api;
defined('IS_API')?:define('IS_API',true);
class BaseApiController extends Api{
    public function __construct(){
        parent::__construct();
    }


}