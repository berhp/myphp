<?php  
/**
 * 演示高并发写log
 * @tutorial
	 测试时：
	 请用浏览器打开多个窗口,
	 在不同的窗口中,传不同的参数,并行访问,注意观察最后的返回结果:
	 如:
	  http://xx.xx.xx/example/log_write/index_demo.php?fun=cs1
	  http://xx.xx.xx/example/log_write/index_demo.php?fun=cs2
	  http://xx.xx.xx/example/log_write/index_demo.php?fun=cs3
	  http://xx.xx.xx/example/log_write/index_demo.php?fun=cs4
	  http://xx.xx.xx/example/log_write/index_demo.php?fun=cs5
	  http://xx.xx.xx/example/log_write/index_demo.php?fun=cs6
	  http://xx.xx.xx/example/log_write/index_demo.php?fun=cs7
	  http://xx.xx.xx/example/log_write/index_demo.php?fun=cs8
	  http://xx.xx.xx/example/log_write/index_demo.php?fun=cs9
 * @tutorial
	运行结果如下:
	//起初内存消耗
	Array
	(
	    [start_time] => 1546417444.2006
	    [start_usage] => 468992
	    [over_time] => 1546417444.2076
	    [over_usage] => 670488
	    [offset_time] => 0.007s
	    [offset_usage] => 196.77kb
	)
	//期末内存消耗
	Array
	(
	    [start_time] => 1546417444.2006
	    [start_usage] => 468992
	    [over_time] => 1546417447.2938
	    [over_usage] => 1217096
	    [offset_time] => 3.0932s
	    [offset_usage] => 730.57kb
	    [last_offset_time] => 3.0862s
	    [last_offset_usage] => 533.76kb
	)
	//高并发下,若写失败,则会有详细循环哪个$i数据失败
	Array
	(
	)
	//仅是一个标识,程序结束了。
	over
 */
class Demo{
	//参数-最大循环次数
	public $i_max = 10000;
	
	//参数-自定义模拟更多字符串
	public $i_str = "xx.xx.xx.xx GET /xx.php/xx/v1/xx/xx/xx?uid=xx&token=MTUyMjc2OTY0MDBfMTU3NjE2MDY3MV9kZjY2YjJjNGJkOThhM2EwNTE5ODdmMWViMmQ2NGRkOA%3D%3D&lng=103.978643&lat=30.614247 T2019-01-02 10:39:53

[sql]:SELECT b.* FROM xx_table  a INNER JOIN rwx_app_icon b on b.id=a.iconid  WHERE a.cityid='1' AND b.is_display=1 AND a.type='30'   ORDER BY a.sort asc

[sql]:SELECT uid,phone,user_name,spread_pid,sex,card_number,names,is_safeagent,insurance_type,is_spreadagent,spread_type,user_head,uid_name,user_platenumber,user_platenumber_date FROM xx_table    WHERE `uid`='xx'    LIMIT 1

[sql]:SELECT count(*) as mcount FROM xx_table    WHERE app='xx' and indent_type=1 and is_del_user=0 and uid=1 and indent_state=10

[sql]:SELECT count(*) as mcount FROM xx_table    WHERE app='xx' and indent_type=1 and is_del_user=0 and uid=1 and indent_state=4101

[sql]:SELECT count(*) as mcount FROM xx_table    WHERE app='xx' and indent_type=1 and is_del_user=0 and uid=1 and indent_state=4103 \n";

	public function cs1(){
		$data = array();
		set_time_limit(0);
		$_num = 100000;
		\Log\get::get_memory_usage();
		$m = new \Log\write();
		for($i=0;$i<$this->i_max;$i++){
			$_r = $_num+$i;
			$r = $m->write( $this->i_str.$_r);
			if($r===false) $data[]=$_r;
		}
		\Log\get::get_memory_usage();
		p($data);
		die('over');
	}

	public function cs2(){
		$data = array();
		set_time_limit(0);
		$_num = 200000;
		\Log\get::get_memory_usage();
		$m = new \Log\write();
		for($i=0;$i<$this->i_max;$i++){
			$_r = $_num+$i;
			$r = $m->write( $this->i_str.$_r);
			if($r===false) $data[]=$_r;
		}
		\Log\get::get_memory_usage();
		p($data);
		die('over');
	}

	public function cs3(){
		$data = array();
		set_time_limit(0);
		$_num = 300000;
		\Log\get::get_memory_usage();
		$m = new \Log\write();
		for($i=0;$i<$this->i_max;$i++){
			$_r = $_num+$i;
			$r = $m->write( $this->i_str.$_r);
			if($r===false) $data[]=$_r;
		}
		\Log\get::get_memory_usage();
		p($data);
		die('over');
	}

	public function cs4(){
		$data = array();
		set_time_limit(0);
		$_num = 400000;
		\Log\get::get_memory_usage();
		$m = new \Log\write();
		for($i=0;$i<$this->i_max;$i++){
			$_r = $_num+$i;
			$r = $m->write( $this->i_str.$_r);
			if($r===false) $data[]=$_r;
		}
		\Log\get::get_memory_usage();
		p($data);
		die('over');
	}

	public function cs5(){
		$data = array();
		set_time_limit(0);
		$_num = 500000;
		\Log\get::get_memory_usage();
		$m = new \Log\write();
		for($i=0;$i<$this->i_max;$i++){
			$_r = $_num+$i;
			$r = $m->write( $this->i_str.$_r);
			if($r===false) $data[]=$_r;
		}
		\Log\get::get_memory_usage();
		p($data);
		die('over');
	}

	public function cs6(){
		$data = array();
		set_time_limit(0);
		$_num = 600000;
		\Log\get::get_memory_usage();
		$m = new \Log\write();
		for($i=0;$i<$this->i_max;$i++){
			$_r = $_num+$i;
			$r = $m->write( $this->i_str.$_r);
			if($r===false) $data[]=$_r;
		}
		\Log\get::get_memory_usage();
		p($data);
		die('over');
	}

	public function cs7(){
		$data = array();
		set_time_limit(0);
		$_num = 700000;
		\Log\get::get_memory_usage();
		$m = new \Log\write();
		for($i=0;$i<$this->i_max;$i++){
			$_r = $_num+$i;
			$r = $m->write( $this->i_str.$_r);
			if($r===false) $data[]=$_r;
		}
		\Log\get::get_memory_usage();
		p($data);
		die('over');
	}

	public function cs8(){
		$data = array();
		set_time_limit(0);
		$_num = 800000;
		\Log\get::get_memory_usage();
		$m = new \Log\write();
		for($i=0;$i<$this->i_max;$i++){
			$_r = $_num+$i;
			$r = $m->write( $this->i_str.$_r);
			if($r===false) $data[]=$_r;
		}
		\Log\get::get_memory_usage();
		p($data);
		die('over');
	}

	public function cs9(){
		$data = array();
		set_time_limit(0);
		$_num = 900000;
		\Log\get::get_memory_usage();
		$m = new \Log\write();
		for($i=0;$i<$this->i_max;$i++){
			$_r = $_num+$i;
			$r = $m->write( $this->i_str.$_r);
			if($r===false) $data[]=$_r;
		}
		\Log\get::get_memory_usage();
		p($data);
		die('over');
	}

}


C('log', array(
	'filePrefix' 	=>'',  							//生成的文件前缀,非必须
	'ext' 			=>'_log', 						//文件后缀,非必须,如.txt
	'filePath' 	    => APP_RUNTIME_PATH.'Logs/',    //存放目录,相对于网站index.php目录,默认为项目Runtime/Logs目录下,文件夹不存在,会自动创建
	'fileSize' 	    =>8,							//文件大小,单位MB,超过了会自动创建新的文件，超过10M Notepad++ 不可以打开
	'timezone_set'  => 'PRC',						//时区,默认北京8点
));
$fun = I('fun','cs1');
$fun_list = array("cs1","cs2","cs3","cs4","cs5","cs6","cs7","cs8","cs9");
if(!in_array($fun, $fun_list) ){
	exit("请按文档正确传参,如http://xx.xx.xx/example/index_demo.php?fun=cs1");
}
$m= new \Demo();
$m->$fun();
die;