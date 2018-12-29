<?php 
/**
 * mysqli二次封装类
 * @author huangping
 * @link http://php.net/manual/zh/set.mysqlinfo.php
 * @tutorial
 *  php 需要开启 mysqli.dll扩展
 *
 */
namespace DB;
class mysqli{
	protected $db;
	public $host 		= '127.0.0.1';
	public $user 		= 'my_user';
	public $password 	= 'my_password';
	public $database 	= 'my_db';  //当前数据库
	public $port 		= '3306';
	public $charset 	= 'utf8';
	
	/**
	 * 【容器】-其他属性
	 */
	public $tablePrefix = ''; //表前缀
	public $table = ''; 	//当前表名
	public $field = '';
	public $where = '';
	public $group = '';
	public $order = '';
	public $limit = '';
	public $join = '';
	public $having = '';
	public $data = '';
	public $alias = '';  	//对表取别名
	public $_sql = ''; 		//最终执行的sql语句
	public $errormsg = ''; 	//sql错误信息
	private $_insert_into_key = ''; //(`phone`,`password`)    ..  增才使用
	private $_insert_into_value = ''; //('1','xx'),('2','xx')  .. 增才使用
	private $_update_set_string = ''; //`phone`='123',`password`='123'   .. 改才使用
	

	//构造函数
	public function __construct( $config=array() ){
		$config = $config ? $config : C('db');
		$this->host 	= $config['DB_HOST'];
		$this->user 	= $config['DB_USER'];
		$this->password = $config['DB_PWD'];
		$this->database = $config['DB_NAME'];
		$this->port 	= $config['DB_PORT'];
		$this->charset 	= $config['DB_CHARSET'];
		$this->tablePrefix = $config['DB_PREFIX'];
		$this->db 		= new \mysqli($this->host, $this->user, $this->password, $this->database, $this->port );  if( $this->db->connect_error ) die('mysqli connect_error:'.$this->db->connect_error );
		$this->db->set_charset($this->charset);
	}
	 
	//析构函数
	public function __destruct(){
		$this->db->close();
	}

	/**
	 * 【内用】- 错误记录处理
	 * @tutorial
	 *   1. 赋值 $this->errormsg
	 *   2. 写错误log记录 (若在框架内)
	 */
	private function _error(){
		if( $this->db->error ) $this->errormsg = $this->db->error;
		if( defined('MyPHP_DIR') && $this->errormsg ){
			$msg = '[action_error]:'.MODULE_NAME.'/'.CONTROLLER_NAME.'/'.ACTION_NAME."\n";
			$msg .= "[sql]:{$this->_sql}\n";
			$msg .= "[sql_error]:{$this->errormsg}";
			$log = new \Log\write();
			$log->write($msg);
		}
		if(APP_DEBUG===true){
			$msg = "[sql]:{$this->_sql}";
			$log = new \Log\write();
			$log->write($msg);			
		}
	}

	/**
	 * 获取错误信息
	 */
	public function error(){
		return $this->db->error;
	}


	/**
	 * 重载方法 - 自动填充
	 * @param yes string $funName  方法名
	 * @param no string||array $data 传参数,无论传什么,最终都是二维数组
	 */
	public function __call( $funName, $datas ){
		if(!$datas) return $this; 		//未传参
		if(!$datas[0]) return $this; 	//'',0,null
		$funName = strtolower($funName);
		if( in_array($funName, array('group','order')) ){
			$this->$funName = strtoupper($funName).' BY '.$datas[0];  return $this;
		}
		if( in_array($funName, array('limit','having')) ){ 
			$this->$funName = strtoupper($funName).' '.$datas[0];  return $this;
		}
		if( in_array($funName, array('alias','field')) ){
			$this->$funName = ' '.$datas[0];  return $this;
		}
		
		//更多直接调用php Mysqli类的方法
		$obj = $this->db;
		return call_user_func_array( array($obj, $funName), $datas );
	}

	/**
	 * 表名
	 * @param yes string $tableName 表名
	 * @param no bool $isaction  若为false,不会判断和拼接表前缀
	 * @tutorial
	 *    会重置$this->join避免异常
	 */
	public function table($tableName='', $isaction=true){
		$this->join = '';
		if( $isaction === true && $this->tablePrefix ){
			if( strpos($tableName, $this->tablePrefix) === false ){
				$this->table = $this->tablePrefix . $tableName;
				return $this;
			}
		}
		$this->table = $tableName;
	    return $this;
	}
	

	/**
	 * 条件
	 * @author berhp 2018.12.27
	 * @param string||array $where 字符串 或 数组
	 * @tutorial <pre> 
	 *   1. $where为字符串时,按原样拼接。                详见: 【type1  字符串】
	 *   2. $where为一维数组时,都按 and 拼接;   详见: 【type2 单纯数组】
	 *   3. $where为二维数组时, $k=>$v
	 *        若$v为字符串,则按 and 拼接,       详见: 【type3 混合数组】的'b'=>'2'
	 *        若$v为数组时,则按原样拼接,        详见: 【type3 混合数组】的array( "and (c=1 or d=2))", "or (e='3')" )
	 * @example
	 *    ★字符串: 原样拼接
	 *    【type1 字符串 】例如,条件为: (a=1 and b=2 and (c=1 or d=2)) or (e=3)
	 *    【源码】：
	 		$where = "(a=1 and b=2 and (c=1 or d=2)) or (e=3)";
	 
	 *    ★一维数组: $k=>$v都是AND拼接!
	 *    【type2 单纯数组】例如,条件为: `a`='1' AND `b`='2' AND e.ddd='4'
	 *    【源码】：
			$where = array(
					'a'=>'1',
					'b'=>'2',
					'e.ddd' => '4',
			);

	 *    ★二维数组: $k=>$v,  $v是数组时,原样拼接;  $v是字符串时,AND拼接!
	 *    ★此特殊写法,支持 'or','and','between','>','<','>=','<=','like' 等等; 遇见非常非常复杂的sql条件语句,建议用【type1字符串】方式
	 *    【type3 混合数组】例如,条件为: (a=1  AND `b`='2' and (c=1 or d=2))  or (e='3')
	 *    【源码】：
			$where = array(
					array( "(a=1" ),
					'b' => '2',
					array( "and (c=1 or d=2))", "or (e='3')" ),
			);
	 */
	public function where($where=''){
		if( is_array($where) ){
			$r = '';
			foreach ($where as $k=>$v){
				if(is_array($v)){
					foreach ($v as $vv){
						if($vv)  $r.= ' '.$vv.' ';
					}
				}else{
					$_r = $r ? ' AND ' : '';
					if( strpos($k,'.') ){
						$r.= $_r."{$k}='{$v}'";
					}else{
						$r.= $_r."`{$k}`='{$v}'";
					}
				}
			}
		}else{
			$r = $where;
		}
		$this->where = $r?(' WHERE '.$r):'';
		return $this;
	}

	
	/**
	 * join
	 * @param no string $join
	 * @param no string $join_type, 支持"INNER JOIN","LEFT JOIN","RIGHT JOIN";默认'INNER JOIN',需写全,避免忘记源生
	 * @tutorial <pre>
	 * 1. 空字符串,则不会拼接
	 * 2. 默认按 INNER JOIN 来拼接, 注意 需要手写表前缀
	 * 3. 若之前已有join内容,会动态追加
	 * @example
	 *  $join = 'app_user B ON B.uid=A.id'; 
	 * @tutorial  如有A表 B表，用A去链表B查询
		left join是以A表的记录为基础的,A可以看成左表,B可以看成右表,左表(A)的记录将会全部表示出来,而右表(B)只会显示符合搜索条件的记录(如 A.aID = B.bID),B表记录不足的地方均为NULL.
		right join是与left join的结果刚好相反,这次是以右表(B)为基础的,A表不足的地方用NULL填充.
		inner join并不以谁为基础,它只显示符合条件的记录.如(A.aID = B.bID)
		@example sql
		select * from a inner join b on a.id=b=id
		select * from a left join b on a.id=b=id
		select * from a right join b on a.id=b=id
	 */
	public function join($join='', $join_type='INNER JOIN'){
		if(!$join) return $this;
		if(!$this->join){
			$this->join = $join_type.' '.$join;
		}else{
			$this->join .= ' '.$join_type.' '.$join;
		}
		return $this;
	}
	
	
	/**
	 * data
	 * @param yes array $data  $data一维数组,则是新增一条; $dataList多维数组,则是多条数据,注意多维数组时,前后的key,value 一 一对应,且字段数量一致
	 * @param no boole $isMoreArray 是否为多维数组, true-是  fasle-不是; 默认 false
	 * @param no boole $isUpdate 是否为‘改’, true-是 false-不是;  默认 false
	 * @tutorial <pre>
	 *    1. 增,支持一维 或 二维数组 如 array('name'=>'1','pass'=>'123') 或  如 array('0'=>array('name'=>'1','pass'=>'123'),'1'=>array('name'=>'wangwu','pass'=>'333') )
	 *         
	 *    2. 改,仅支持一维数组 如 array('name'=>'1','pass'=>'123')
	 * @tutorial <pre>
	 *    增
	 *    insert into demo_user (`phone`,`password`) VALUES ('1','xx'),('2','xx')
	 *    
	 * 	private $_insert_into_key = ''; //(`phone`,`password`)    ..
	 *	private $_insert_into_value = ''; //('1','xx'),('2','xx')  ..    
	 *    
	 *    改
	 *    update demo_user set `phone`='123',`password`='123' where `uid`=30
	 *    
	 * 	private $_update_set_string = ''; //`phone`='123',`password`='123'   .. 
	 */
	public function data($data, $isMoreArray=false, $isUpdate=false){
		if(!$data) return $this;
		if( $isUpdate === true ){
				self::_action_update_set_string($data);
				return $this;
		}else{
			if( $isMoreArray === true ){  //多维时
					self::_action_insert_into_key($data);
					self::_action_insert_into_value($data);
			}else{				
					self::_action_insert_into($data);
			}
			return $this;
		}
	}
	
	/**
	 * 处理update_set_string(含赋值) - 改 [ok]
	 * @return string   `phone`='123',`password`='123' ..
	 */
	private function _action_update_set_string($data){
		$str = '';
		foreach ($data as $k=>$v){
			if(is_array($v)) die('update时,data数据只支持一维数组');
			$v = addslashes($v);  //★安全过滤内容值,添加反斜杠,避免sql错误,如:  张三"xx"说'xxx'
			$str .= "`{$k}`='{$v}',";
		}	$str = mb_substr($str, 0, -1, 'utf-8');
		$this->_update_set_string = $str;
	}
	
	/**
	 * 【内用】取出key(含赋值) - 增-多条数据时
	 * @return string   `phone`,`password` ..
	 */
	private function _action_insert_into_key($data){
		$key = '';
		foreach ($data as $k=>$v){
			foreach ( $v as $k2=>$v2 ){
				$key .= "`{$k2}`,";
			}	$key 	= '('.mb_substr($key, 0, -1, 'utf-8').')';
			break; 	 //仅取key
		}
		$this->_insert_into_key = &$key;
	}
	
	/**
	 * 【内用】拼接value(含赋值) - 增-多条数据时
	 * @return string  ('1','xx'),('2','xx') ..
	 */
	private function _action_insert_into_value($data){
		$_value=''; $value='';
		foreach ($data as $k=>$v){
			foreach ( $v as $k2=>$v2 ){
				$v2 = addslashes($v2);  //★安全过滤内容值,添加反斜杠,避免sql错误,如:  张三"xx"说'xxx' 
				$_value .= "'{$v2}',";
			}	$_value = mb_substr($_value, 0, -1, 'utf-8');
			$value .= '('.$_value.'),';
			$_value = ''; //重置,便于下次循环赋值
		}
		$value = mb_substr($value, 0, -1, 'utf-8');
		$this->_insert_into_value = &$value;
	}
	
	/**
	 * 【内用】处理key与value(含赋值) - 增-仅一条数据时 
	 * @return string  ('1','xx'),('2','xx') ..
	 */
	private function _action_insert_into($data){
		$key=''; $value='';
		foreach ($data as $k=>$v){
				$key .= "`{$k}`,";
				$v = addslashes($v);  //★安全过滤内容值,添加反斜杠,避免sql错误,如:  张三"xx"说'xxx'
				$value .= "'{$v}',";
		}
		$key 	= '('.mb_substr($key, 0, -1, 'utf-8').')';
		$value 	= '('.mb_substr($value, 0, -1, 'utf-8').')';
		$this->_insert_into_key = &$key;
		$this->_insert_into_value = &$value;
	}

	/**
	 * 增一条 [ok]
	 * @param no array $data 一维数组 
	 * @return boolean || int  返回false或者自动增长的id值
	 * @tutorial
	 *  insert into demo_user (`phone`,`password`) VALUES ('1','xx'),('2','xx')
	 *  @example
	 *  $db = new \DB\mysqli();
	 *  $db->table($table)->data($dataList,true)->add(); //多条,$dataList为二维数组
	 *  $db->table($table)->data($data)->add(); //一条,$data必须为一维数组
	 *  $db->table($table)->add($data); //一条,$data必须为一维数组
	 */
	public function add($data=array()){
		if($data){
			self::_action_insert_into($data);
		}
		$sql = "INSERT INTO {$this->table} {$this->_insert_into_key} VALUES {$this->_insert_into_value}";
		$this->_sql = $sql;
		$r = self::_query($sql);
		$insert_id = $this->db->insert_id;  //自动ID
		self::_error();
		if($r===false) return $r;
		return $insert_id?$insert_id:true;
	}
	
	/**
	 * 增多条 [ok]
	 * @param no array $dataList 二维数组
	 * @return boolean
	 * @example
	 * $db = new \DB\mysqli();
	 * $db->table($table)->data($dataList,true)->addALL(); //多条
	 * $db->table($table)->addALL($dataList); //多条
	 */
	public function addALL($dataList=array()){
		if($dataList){
			self::_action_insert_into_key($dataList);
			self::_action_insert_into_value($dataList);
		}
		$sql = "INSERT INTO {$this->table} {$this->_insert_into_key} VALUES {$this->_insert_into_value}";
		$this->_sql = $sql;
		$r = self::_query($sql);
		self::_error();
		return $r;
	}	
	
	/**
	 * 删 [ok]
	 * @tutorial
	 *    DELETE FROM demo_user WHERE uid=30
	 * @return boolean
	 */
	public function delete(){
		$sql = "DELETE FROM {$this->table} {$this->where}";
		$this->_sql = $sql;
		$r = self::_query($sql);
		self::_error();
		return $r;
	}
	
	/**
	 * 改 【ok】
	 * @param no array $data 一维数组 
	 * @return boolean
	 * @tutorial
	 *    update demo_user set `phone`='123',`password`='123' where `uid`=30
	 * @example
	 * $db = new \DB\mysqli();
	 * $db->table($table)->data($data,false,true)->where('uid=55')->save();  //方式一,$data一维数组,如 array('name'=>'xx','pass'=>'123')
	 * $db->table($table)->where(array('uid'=>55))->save($data); //方式二,$data一维数组
	 */
	public function save($data=array()){
		if($data){
			self::_action_update_set_string($data);
		}
		$sql = "UPDATE {$this->table} SET {$this->_update_set_string} {$this->where}";
		$this->_sql = $sql;
		$r = self::_query($sql);
		self::_error();
		return $r;
	}
	
	
	/**
	 * 改多条(根据主键作为条件,一次修改多条,分别赋值) 【ok】
	 * @author berhp 2018.7.9
	 * @param yes array $dataList 二维数组： array(0=>array('uid'=>1,'name'=>'demo1'), 1=>array('uid'=>2,'name'=>'demo2'))
	 * @param yes string $fieldkey ★主键字段,如 uid (便于程序自动拼接字段赋值,自动拼接条件)
	 * @tutorial 
	 * 		1. $dataList 中,需要存在主键字段,便于程序自动拼接sql
	 * 		2. 若$sql语句比较长,请先将mysql的配置文件: my.ini中的max_allowed_packet = 6M 设置大点, 如20M; 避免执行出现"Could not execute JDBC batch update"的错误;
	 * 		3. 若不能配置 my.ini; 请控制要更新的字段个数 和 一次执行的数据数量;
	 * @return boolean
	 * @tutorial
		UPDATE mytable
		 SET myfield1 = CASE id
		 WHEN 1 THEN 'myvalue11'
		 WHEN 2 THEN 'myvalue12'
		 WHEN 3 THEN 'myvalue13'
		 END,
		 myfield2 = CASE id
		 WHEN 1 THEN 'myvalue21'
		 WHEN 2 THEN 'myvalue22'
		 WHEN 3 THEN 'myvalue23'
		 END
		WHERE id IN (1,2,3)
	 * @example
	 * $dataList = array(0=>array('uid'=>1,'name'=>'demo1'), 1=>array('uid'=>2,'name'=>'demo2'));
	 * $db = new \DB\mysqli();
	 * $db->table($table)->saveALL($dataList, 'uid);
	 */
	public function saveALL( $dataList=array(), $fieldkey='' ){
		$_where_in = '';
		$_fieldData = array();
		foreach ( $dataList as $k=>$v ){
			$_where_in .= "'{$v[$fieldkey]}',";
			foreach ($v as $kk=>$vv){
				if( !isset($_fieldData[ $kk ]['_sql']) )  $_fieldData[ $kk ]['_sql']='';
				$_fieldData[ $kk ]['_sql'] .= "WHEN '{$v[$fieldkey]}' THEN '{$v[$kk]}' ";
			}
		}
		$_where_in = substr( $_where_in, 0, -1 );
		$_str = '';  
		foreach ( $_fieldData as $k=>$v ){
			$_str .= "{$k} = CASE {$fieldkey} ";
			$_str .= $v['_sql'];
			$_str .= ' END,';
		}   $_str = substr($_str, 0, -1);
		$this->_update_set_string = $_str;
		$sql = "UPDATE {$this->table} SET {$this->_update_set_string} WHERE {$fieldkey} IN ({$_where_in})";
		$this->_sql = $sql;
		$r = self::_query($sql);
		self::_error();
		return $r;
	}	
	
	
	
	/**
	 * 查询满足条件的总个数
	 * @return boolean || int 没有则是 0,sql错误返回false
	 */
	public function count(){
		$sql = "SELECT count(*) as mcount FROM {$this->table} {$this->alias} {$this->join} {$this->where} {$this->group} {$this->having}";
		$this->_sql = $sql;
		$result = self::_query($sql);
		self::_error();
		if($result===false) return false;
		$data = array();		
		if( !$result->num_rows ) return $data;
		$data =  (array)$result->fetch_object();
		return $data['mcount'];
	}
	
	/**
	 * [ok]查一条 【OK】
	 * @param no string $sql 若传了则按源sql执行返回 
	 * @return array 一维数组
	 * @tutorial
	 * SELECT * from demo_user a inner join demo_pass b on a.uid=b.uid where a.uid>0  GROUP BY a.roleid HAVING a.roleid<10 ORDER BY a.phone LIMIT 1
	 * @eg:
	 * $db=new new \DB\mysqli();
	 * $r = $db->table($table)->where(array('roleid'=>2))->find();  //方式一
	 * $r = $db->find($sql);  //方式二
	 */
	public function find($sql=''){
		if(!$sql){
			if( !$this->field ) $this->field = '*';
			$sql = "SELECT {$this->field} FROM {$this->table} {$this->alias} {$this->join} {$this->where} {$this->group} {$this->having} {$this->order} LIMIT 1";
		}
		$this->_sql = $sql;
		$result = self::_query($sql);
		self::_error();
		$data = array();
		if($result===false) return $data;
		if( !$result->num_rows ) return $data;
		$data =  (array)$result->fetch_object();
		return $data;
	}
	
	/**
	 * [ok]查多条
	 * @return array 二维数组
	 * @tutorial
	 * SELECT * from demo_user a inner join demo_pass b on a.uid=b.uid where a.uid>0  GROUP BY a.roleid HAVING a.roleid<10 ORDER BY a.phone LIMIT 20
	 * @eg:
	 * $db=new new \DB\mysqli();
	 * $r = $db->table($table)->alias('A')->field('A.uid,A.phone')->where(array('A.roleid'=>2))->select();  //方式一
	 * $r = $db->find($sql);  //方式二
	 */
	public function select($sql=''){
		if(!$sql){
			if( !$this->field ) $this->field = '*';
			$sql = "SELECT {$this->field} FROM {$this->table} {$this->alias} {$this->join} {$this->where} {$this->group} {$this->having} {$this->order} {$this->limit}";
		}
		$this->_sql = $sql;
		$result = self::_query($sql);
		self::_error();
		$data = array();
		if($result===false) return $data;
		if( !$result->num_rows )  	return $data;
		for($i=0; $i< $result->num_rows; $i++ ){
			$data[$i] =  (array)$result->fetch_object();
		}
		return $data;
	}
	
	/**
	 * 按sql原样执行
	 * @param string $sql
	 * @tutorial
	 *   设计: 动态返回数据,若是 select 返回空数组或（★二维数组), delete,update ..等其他返回boolean
	 * @return mixed
	 */
	public function query($sql=''){
		$r = self::_query($sql);
		$this->_sql = $sql;
		self::_error();
		if( is_object($r) ){
			$data = array();
			if( !$r->num_rows ) return $data;
			for($i=0; $i< $r->num_rows; $i++ ){
				$data[$i] =  (array)$r->fetch_object();
			}
			return $data;
		}else{
			return $r;
		}
	}
	
	/**
	 * 【内用】query执行sql
	 * @param string $sql
	 * @return mixed 
	 */
	private function _query($sql=''){
		return $this->db->query($sql);
	}
	
	/**
	 * 开始事务 [ok]
	 * @link http://php.net/manual/zh/book.mysqli.php
	 * @tutorial <pre>数据引擎 需要为 InnoDB
	 * 	 0. php>=5.5的写法: $mysqli->begin_transaction( MYSQLI_TRANS_START_READ_ONLY );
	 *    	MYSQLI_TRANS_START_READ_ONLY  只读事务启动
	 *    	MYSQLI_TRANS_START_READ_WRITE  开始事务读写
	 *    	MYSQLI_TRANS_START_WITH_CONSISTENT_SNAPSHOT 开启事务一致 （★实际选这个最佳: 测试时 php7, mysqli:5.5.47 )
	 *   1. php<5.5的写法:
	 *      mysqli_query($db, "START TRANSACTION");
	 *      或
	 *      $mysqli->query('START TRANSACTION');
	 *      
	 *   2. mysql原生sql命令为: START TRANSACTION   或者命令    begin
	 * @tutorial 事务mysql的sql源生命令如,需要先连接上数据库:
			START TRANSACTION;   #或者用另外写法,一样的效果   begin;
			DELETE from demo_user where uid=76;
			#ROLLBACK;   #回滚,#为注释
			COMMIT;      #提交
     * @example:
            $check = checkAPPKeyword($_REQUEST, 'id,userid'); if($check['code']) return $check;
            $data=I('request.');
            $m = M();
            $m->startTrans();
            //将用户所有收货地址全部改为不是默认地址
            $row=array(
                'is_mo'=>0
            );
            $chan=$m->table('user_address')
                ->where(array('uid'=>$data['userid']))
                ->save($row);
            //将当前用户选中的地址设为默认地址
            $row_now=array(
                'is_mo'=>1
            );
            $chan_now=$m->table('user_address')
                    ->where(array('id'=>$data['id'],'uid'=>$data['userid']))
                    ->save($row_now);
            if($chan && $chan_now){
                $m->commit();
                return showData('', \Api\Msg::get(205), 0);
            }else{
                $m->rollback();
                return showData('', \Api\Msg::get(500), 0);
            }
	 */
	public function startTrans(){
		if( PHP_VERSION < 5.5 ){
			return $this->db->query("START TRANSACTION");
		}else{
			return $this->db->begin_transaction( MYSQLI_TRANS_START_WITH_CONSISTENT_SNAPSHOT );
		}
	}

	
	/**
	 * 事务回滚 [ok]
	 * @since php>5.0
	 * @tutorial
	 * mysql原生sql命令为: ROLLBACK
	 */
	public function rollback(){
		return $this->db->rollback();
	}
	
	/**
	 * 提交事务 [ok]
	 * @since php>5.0
	 * @tutorial
	 * mysql原生sql命令为: COMMIT
	 */
	public function commit(){
		return $this->db->commit();
	}
	
	
	/**
	 * 创建并切换数据库 [ok]
	 * @param yes string $dbname  数据库名
	 * @param no string $tablePrefix  表前缀,默认切换时为空字符串
	 * @param no string $charset 数据库编码,默认utf8
	 * @return boolean
	 * @tutorial  设计:: 若已存在了数据库，则不创建，直接切换; 不存在数据库先创建,在切换至它
	 */
	public function set_dbName( $dbname='', $tablePrefix='', $charset='utf8'){
		$r = $this->db->select_db($dbname);
		if($r===true){
			$this->database 	= $dbname;
			$this->tablePrefix 	= $tablePrefix;
			$this->charset 		= $charset;
			return true;
		}else{
			$sql = "create database `{$dbname}` charset {$charset}";
			$r = self::_query($sql);
			if($r===true){
				$this->db->select_db($dbname);
				self::_query("use {$dbname}");
				$this->database 	= $dbname;
				$this->tablePrefix 	= $tablePrefix;
				$this->charset 		= $charset;
				return true;
			}else{
				return false;	
			}
		}
	}
	
	/**
	 * 仅创建数据库 [ok]
	 * @param yes string $dbname  数据库名
	 * @param no string $charset 数据库编码,默认utf8
	 * @return boolean
	 */
	public function create_database($dbname='',$charset='utf8'){
		$sql = "create database `{$dbname}` charset {$charset}";
		$this->_sql = $sql;
		$r = self::_query($sql);
		self::_error();
		return $r;
	}

	/**
	 * 删除指定数据库 [ok]
	 * @param yes string $dbname  数据库名
	 * @return boolean
	 * @tutorial
	 * drop database `库名`;
	 */
	public function delete_database($dbname=''){
		$sql = "DROP DATABASE IF EXISTS `{$dbname}`;";
		$this->_sql = $sql;
		$r = self::_query($sql);
		self::_error();
		return $r;
	}
	
	
	/**
	 * 仅切换数据库 [ok]
	 * @param yes string $dbname  数据库名
	 * @param no string $tablePrefix  表前缀,默认切换时为空字符串
	 * @param no string $charset 数据库编码,默认utf8
	 * @return boolean
	 */
	public function use_database($dbname='',$tablePrefix='', $charset='utf8'){
		$sql = "use `{$dbname}`";
		$this->_sql = $sql;
		$r = self::_query($sql);
		self::_error();
		$this->database 	= $dbname;
		$this->tablePrefix 	= $tablePrefix;
		$this->charset 		= $charset;
		return $r;
	}

	/**
	 * 创建表  -- 未提供此方法，写sql语句,执行即可
	 * @sql: <pre>
		create table `new` (
		`id` int(11) not null auto_increment comment '自动id',
		`name` varchar(20) not null default '' comment '名称',
		`number` double(30,2) NOT NULL DEFAULT '0.00',
		primary key (`id`),
		key `name2` (`name`,`number`) using btree,
		key `number` (`number`) using btree,
		) engine=myisam default charset=utf8;

	 * @tutorial
	 * 说明:
		not null 不为空
		auto_increment 自动增长
		comment '备注内容'
		default '默认值'
		primary key (`id`)  主键为字段id,主键是特殊的索引内容唯一
		key `name2` (`name`,`number`) using btree,  建立索引名为name2的索引字段为name,number 索引方式为btree, ( 注 hash方式索引仅仅能满足"=","IN"和"<=>"查询，不能使用范围查询 )
		`number` double(30,2) default '0.00' 字段number类型为double宽度为30小数点位数为2,默认值为 0.00
		engine=myisam  default charset=utf8 表类型为MyISAM,字段和表的字符编码都为utf8, 若要支持事务,表的类型要为 InnoDB, 创建时不用区分大小写innodb
	 */
	public function create_table(){}

	/**
	 * 删除表 [OK]
	 * @param yes string $file sql文件路径, 如 xx/xx.sql
	 * @return boolean
	 * @tutorial  drop table if exists `表名`;
	 */
	public function delete_table($tableName=''){
		$sql = "drop table if exists `{$tableName}`";
		$this->_sql = $sql;
		$r = self::_query($sql);
		self::_error();
		return $r;
 	}

	/**
	 * 获取mysql数据库的版本 [ok]
	 * @return string 版本号,如 5.5.47
	 */
	public function get_version(){
		$sql = "select version() as version";
		$this->_sql = $sql;
		$result = self::_query($sql);
		self::_error();
		if( !$result->num_rows ) return '';
		$data =  (array)$result->fetch_object();
		return $data['version'];
	}

	/**
	 * 修改表名 [ok]
	 * @param yes string $old_tableName 原表名
	 * @param yes string $new_tableName 新表名
	 * @return boolean
	 * @eg:
	  	$db = new \DB\mysqli();
		$r = $db->edit_tableName('b.app_ad','b.app_ad2');  //修改其他数据库b的表名(若有权利)
	    $r = $db->edit_tableName('demo_user','demo_user2');  //修改当前数据库的表名
	    $r = $db->set_dbName('b');  	//	切换到数据库b
	    $r = $db->edit_tableName('app_ad2','app_ad');  //修改当前数据库的表名
	 */
	public function edit_tableName($old_tableName='',$new_tableName=''){
		if( !$old_tableName && !$new_tableName ) return true;
		$sql = "rename table {$old_tableName} to {$new_tableName}";
		$this->_sql = $sql;
		$r = self::_query($sql);
		self::_error();
		return $r;
	}

	/**
	 * 导入sql文件 [ok]
	 * @param yes string $file sql文件路径, 如 xx/xx.sql
	 * @tutorial
	 *  设计：以';'分割为数组,依次执行sql语句
	 */
	public function create_tableNames_file($file=''){
		if(!$file) return false;
		set_time_limit(0);
		$str = file_get_contents($file);
		$str = explode(';', $str);
		foreach($str as $k=>$v){
			self::_query($v);
			unset($str[$k]);
		}
		return true;
	}
	
	
	/**
	 * 字段值增长 (ok)
	 * @param yes string $field  字段名
	 * @param no integer||double $step  增长值,默认1
	 * @return boolean
	 */
	public function setInc($field,$step=1) {
		$sql = "UPDATE {$this->table} SET {$field}={$field}+{$step} {$this->where}";
		$this->_sql = $sql;
		$result = self::_query($sql);
		self::_error();
		return $result;
	}
	
	/**
	 * 字段值减少 (ok)
	 * @access public
	 * @param yes string $field  字段名
	 * @param no integer||double $step  减少值,默认1
	 * @return boolean
	 */
	public function setDec($field,$step=1) {
		$sql = "UPDATE {$this->table} SET {$field}={$field}-{$step} {$this->where}";
		$this->_sql = $sql;
		$result = self::_query($sql);
		self::_error();
		return $result;
	}
	
	/**
	 * 字段值拼接字符串 (ok)
	 * @access public
	 * @param yes string $field  字段名
	 * @param no string $content 要拼接的字符串内容,如'aabbcc'; 默认为"",表示不拼接任何内容
	 * @return boolean
	 * @sql
	 *  UPDATE rwx_indent SET ext_ls=CONCAT(ext_ls,'2018-04-17 09:53:24 用户取消订单;') WHERE orderson='d12018041709542359563371572181';
	 * @example
	  	$r = M('indent')->where(array('orderson'=>'d12018041709542359563371572181'))->setConcat('ext_ls', 'aabbcc');
		var_dump($r);
	 */
	public function setConcat($field,$content='') {
		$sql = "UPDATE {$this->table} SET {$field}=CONCAT({$field},'{$content}') {$this->where}";
		$this->_sql = $sql;
		$result = self::_query($sql);
		self::_error();
		return $result;
	}

}