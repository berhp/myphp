<?php 
/**
 * pdo类
 * @author berhp,ty
 * @tutorial
 * 	1. 这里仅仅二次封装了常用的方法
 *  2. 未封装的方法,或直接调用低层源生pdo类中的方法执行。
 *  3. 二次封装,只是提供构造方法,pdo连接上数据库。
 *  4. 常用方法可以参考PHP官网pdo文档: http://php.net/manual/en/book.pdo.php
 *  5. 连贯方法,待后续扩展提供: find(),select(),add(),addALL(),delete(),save(),saveALL(),count(),setInc(),setDec(),setConcat()
 * @example
		 $config=array(
				'DB_TYPE'               => 'pdo',			// 数据库类型
				'DB_HOST'               => '127.0.0.1', 	// 服务器地址
				'DB_NAME'               => 'demo',  		// 数据库名
				'DB_USER'               => 'root',  		// 用户名
				'DB_PWD'                => 'root',  		// 密码
				'DB_PORT'               => '3306',  		// 端口
				'DB_PREFIX'             => '', 				// 数据库表前缀
				'DB_CHARSET' 			=> 'utf8', 			// 数据库编码
				'DB_PDO_TYPE' 			=> 'mysql', 	    // 告之PDO连接什么数据库驱动
		 );
		 $model = new \DB\pdo($config);
		 $sql = "select * from yourtable limit 5";
		 $r = $model->query($sql);
		 var_dump($r); 
 *  
 */
namespace DB;
class pdo{
	protected $db;
	public $host 		= '127.0.0.1';
	public $user 		= 'my_user';
	public $password 	= 'my_password';
	public $database 	= 'my_db';   //当前数据库
	public $port 		= '3306';
	public $charset 	= 'utf8';
	public $pdo_type    = 'mysql';   //pdo连接的数据库驱动类型,如mysql,详细见: http://php.net/manual/en/pdo.drivers.php
	public $join='';
	public $table='';
	public $where='';
	public $_sql='';
	public $alias='';
	public $group='';
	public $having='';
	public $field='';
	public $order='';
	public $limit='';
	public $data='';
	private $_insert_into_key='';
	private $_insert_into_value='';
	private $_update_str='';

	/**
	 * 构造
	 * @author berhp 2018.12.26
	 * @param yes array $config
	 */
	public function __construct( $config=array() ){
		if( !class_exists('PDO') ){
			die('需要安装PDO功能');
		}
		$config = $config ? $config : C('db');
		$this->host 	   = $config['DB_HOST'];
		$this->user 	   = $config['DB_USER'];
		$this->password    = $config['DB_PWD'];
		$this->database    = $config['DB_NAME'];
		$this->port 	   = $config['DB_PORT'];
		$this->charset     = $config['DB_CHARSET'];
		$this->tablePrefix = $config['DB_PREFIX'];
		if(isset($config['DB_PDO_TYPE'])){
			$this->pdo_type = $config['DB_PDO_TYPE'] ? $config['DB_PDO_TYPE'] : "mysql";
		}
		try{
			$this->db=new \PDO("{$this->pdo_type}:host={$this->host};dbname={$this->database}",  $this->user,  $this->password );
			$this->db->exec("SET NAMES {$this->charset}");
		}catch( \PDOException $e ){   //若失败拿到pdo类中的PDOException类异常信息
			die('PDO连接数据库失败:'.$e->getMessage() );
		}
	}
	
	
	/**
	 * 析构
	 * @tutorial (程序执行完后,自动关闭数据库,可不写业务逻辑)
	 */
	public function __destruct(){
		
	}
	
	
	/**
	 * 重载 - 更多未封装的方法,直接调 源生 POD类的方法,执行 (ok)
	 * @author berhp 2018.12.26
	 */
	public function __call($name, $param){
		$obj = $this->db;
		return call_user_func_array( array($obj, $name), $param );
	}


	/**
	 * 设置表名
	 * @param string $tableName 表名
	 * @param bool $isaction false 则不判断和拼接表前缀
	 * @return $this
	 */
	public function table($tableName='', $isaction=true){
		if( $isaction === true && strpos($tableName, $this->tablePrefix) === false ){
			$this->table = $this->tablePrefix . $tableName;
		}else {
			$this->table = $tableName;
		}
		return $this;
	}

	/**
	 * @param string $alias
	 * @return $this
	 */
	public function alias($alias=''){
		$this->alias=$alias;
		return $this;
	}

	/**
	 * @param string $where where 条件
	 * 如 $where=['uid'=>'1','is_user'=>1];
	 *    $where='uid=1';
	 *    where条件为 为字符串时建议使用如下
	 * 一维数组默认已采用参数绑定的预处理语句形式，二维数组不支持
	 *【type3 混合数组】例如,条件为: (a=1  AND `b`='2' and (c=1 or d=2))  or (e='3')
	 *    【源码】：
	$where = array(
	array( "(a=1" ),
	'b' => '2',
	array( "and (c=1 or d=2))", "or (e='3')" ),
	);
	 * @return $this|bool
	 */
	public function where($where=''){
		if(!$where) return $this;
		$create_where='';
		if(is_array($where)){
			foreach($where as $k=>$v){
				if(is_array($v)){
				   foreach($v as $vol){
					   $create_where.=' '.$vol.' ';
				   }
				}else{
					$create_where.=strlen($create_where)?'AND '.$k.'='.$this->db->quote($v):$k.'='.$this->db->quote($v);
				}
			}
		}else{
			$create_where.=strlen($create_where)?' AND '.$where:$where;
		}
		//$this->where.=strlen($this->where)?' AND '.trim($create_where):'WHERE '.trim($create_where);
		$this->where='WHERE '.trim($create_where);
		return $this;
	}

	/**
	 * @param string $order
	 * @return $this
	 */
    public function order($order=''){
		if($order)$this->order='ORDER BY '.$order;
		return $this;
	}
	/**
	 * M('xxx a')->join('xxx b on b.id=a.id','LEFT JOIN')
	 * @param string $join
	 * @param string $join_type
	 * @return $this
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
	 * 字段 默认*
	 */
	public function field($field='*'){
		if(!$field) $field='*';
		$this->field=$field;
		return $this;
	}

	/**
	 * 查询满足条件的总个数
	 * @return boolean || int 没有则是 0,sql错误返回false
	 */
	public function count(){
		$sql = "SELECT count(*) as mcount FROM {$this->table} {$this->alias} {$this->join} {$this->where} {$this->group} {$this->having}";
		$result = $this->query($sql);
		if($result===false) return false;
		return $result[0]['mcount'];
	}

	/**
	 * 查询一条数据
	 * @param string $sql 如传sql则按该sql执行查询
	 * @return array
	 */
	public function find($sql=''){
		if(!$sql){
			if( !$this->field ) $this->field = '*';
			$sql = "SELECT {$this->field} FROM {$this->table} {$this->alias} {$this->join} {$this->where} {$this->group} {$this->having} {$this->order} LIMIT 1";
		}
		$this->_sql = $sql;
		$result = $this->query($sql);
		if(!$result) return array();
		return $result[0];
	}

	/**
	 * @param string $limit
	 * @return $this
	 */
	public function limit($limit=''){
		if($limit)$this->limit='LIMIT '.$limit;
		return $this;
	}

	/**
	 * @param string $group
	 * @return $this
	 */
	public function group($group=''){
		if($group)$this->group='GROUP BY '.$group;
		return $this;
	}

	/**
	 * 查询多条
	 * @param string $sql 如传sql则按该sql语句执行
	 * @return array|bool
	 */
	public function select($sql=''){
		if(!$sql){
			if( !$this->field ) $this->field = '*';
			$sql = "SELECT {$this->field} FROM {$this->table} {$this->alias} {$this->join} {$this->where} {$this->group} {$this->having} {$this->order} {$this->limit}";
		}
		$this->_sql = $sql;
		$result =$this->query($sql);
		return $result;
	}

	/**
	 * 删除
	 * @return array
	 */
	public function delete(){
		$sql = "DELETE FROM {$this->table} {$this->where}";
		$this->_sql = $sql;
		$r = $this->query($sql);
		return $r;
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
	public function data(array $data, $isMoreArray=false, $isUpdate=false){
		if(!empty($data)){
			if($isUpdate){
				$this->create_update_data($data);
			}else{
				if($isMoreArray){
					$this->create_insert_data_two($data);
				}else{
					$this->create_insert_data_one($data);
				}
			}
		}
		return $this;
	}



	/**
	 * 判断数组维度
	 * @param $vDim
	 * @return int
	 */
	private function arrayLevel($vDim){
		if(!is_array($vDim)){
			return 0;
		}else{
			$max1 = 0;
			foreach($vDim as $item1){
				$t1 = $this->arrayLevel($item1);
				if( $t1 > $max1) {
					$max1 = $t1;
				}
			}
			return $max1 + 1;
		}
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
		if(!empty($data))$this->create_insert_data_one($data);
		if(!strlen($this->_insert_into_key)) return false;
		$sql="INSERT INTO {$this->table}  {$this->_insert_into_key}  VALUES  {$this->_insert_into_value}";
		$r = $this->query($sql);
		return $r;
	}

	/**
	 * 增多条 [ok]
	 * @param no array $dataList 二维数组
	 * @return boolean
	 * @example
	 * $db = new \DB\mysqli();
	 * $db->table($table)->data($data,true)->addALL(); //多条
	 * $db->table($table)->addALL($data); //多条 此处data可为一维数组或二位数组
	 */
	public function addALL($data=array()){
		if(!empty($data)){
			if($this->arrayLevel($data)==1) $this->create_insert_data_one($data);
			if($this->arrayLevel($data)==2) $this->create_insert_data_two($data);
		}
		$sql = "INSERT INTO {$this->table} {$this->_insert_into_key} VALUES {$this->_insert_into_value}";
		$this->_sql =  $sql;
		$r = $this->query($sql);
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
		if($data)$this->create_update_data($data);
		$sql = "UPDATE {$this->table} SET {$this->_update_str} {$this->where}";
		$this->_sql =  $sql;
		$r = $this->query($sql);
		return $r;
	}

	/**
	 * @param array $dataList 修改的数据
	 * @param string $fieldkey 一般传标的主键 如id
	 * [
	'0'=>[
	'id'=>'12',
	'title'=>'wfw',
	'info'=>"'gergrgr'"
	],
	'1'=>[
	'id'=>'13',
	'title'=>'gggg',
	'info'=>'ggrggg'
	]
	];
	 *  UPDATE categories
	SET display_order = CASE id
	WHEN 1 THEN 3
	WHEN 2 THEN 4
	WHEN 3 THEN 5
	END,
	title = CASE id
	WHEN 1 THEN 'New Title 1'
	WHEN 2 THEN 'New Title 2'
	WHEN 3 THEN 'New Title 3'
	END
	WHERE id IN (1,2,3)
	 */
	public function saveAll($dataList=array(), $fieldkey=''){
		if(empty($dataList) || $this->arrayLevel($dataList)<2) return false;
		$fieldkey_list=array();
		$ls_data=array();
		$where_in='';
		foreach($dataList as $val){
			$fieldkey_list[]=$val[$fieldkey];
			$ls_data[$val[$fieldkey]]=$val;
			$where_in.="'".$val[$fieldkey]."',";
		}
		$data=reset($ls_data);
		unset($data[$fieldkey]);
		$sql="UPDATE {$this->table} SET ";
		foreach($data as $key=>$vol){
              $sql.="{$key} = CASE {$fieldkey} ";
			  foreach($fieldkey_list as $v){
				 $sql.="WHEN {$v} THEN '".addslashes($ls_data[$v][$key])."' ";
			  }
			$sql.="END,";
		}
		$sql=substr($sql,0,-1)." WHERE id IN (".substr($where_in,0,-1).")";
		$this->_sql =  $sql;
		$r = $this->query($sql);
		return $r;
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
		$result = $this->query($sql);
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
		$this->_sql =  $sql;
		$result =$this->query($sql);
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
		$result = $this->query($sql);
		return $result;
	}


	/**
	 * 修改数组 构造修改数据sql字符
	 * @param array $data
	 */
	private function create_update_data(array $data){
		if($this->arrayLevel($data)>1) return false;
		$this->_update_str='';
		foreach($data as $k=>$v){
			$this->_update_str.=$k."='".addslashes($v)."',";
		}
		$this->_update_str=substr($this->_update_str,0,-1);
	}
	/**
	 * 添加数据 一维数组 构造添加sql字符
	 * @param array $data
	 */
	private function create_insert_data_one(array $data){
		$this->_insert_into_key='(';
		$this->_insert_into_value='(';
		foreach($data as $k=>$v){
			$this->_insert_into_key.=$k.',';
			$this->_insert_into_value.="'".addslashes($v)."',";;
		}
		$this->_insert_into_key=substr($this->_insert_into_key,0,-1).')';
		$this->_insert_into_value=substr($this->_insert_into_value,0,-1).')';
	}

	/**
	 * 添加数据 多维数组 构造添加sql字符
	 * @param array $data
	 * @return bool
	 */
	private function create_insert_data_two(array $data){
		$first_key_data=reset($data);
		$this->_insert_into_key='('.implode(',',array_keys($first_key_data)).')';
		$this->_insert_into_value='';
		foreach($data as $val){
			if(array_diff_key($first_key_data,$val)) return false;
			$ls_str='(';
			foreach($val as $k=>$v){
				$ls_str.="'".addslashes($v)."',";;
			}
			$this->_insert_into_value.=substr($ls_str,0,-1).'),';
		}
		$this->_insert_into_value=substr($this->_insert_into_value,0,-1);
	}
	
	/**
	 * 【自定义】开启事务
	 * @link http://www.php.net/manual/en/pdo.begintransaction.php
	 * @return bool Returns true on success or false on failure.
	 * @tutorial  
	 * 		1、为了兼容DB\mysqli类中的写法提供自定义方法  
	 *  	2、你用PDO类原生beginTransaction()也可以
	 *  	3、★★★使用事务,需要表的类型为"InnoDB"
	 * @example
		$sql = "insert into rwx_cs (a,b) values ('c1','1')";   			//正确的
		$sql2 = "insert into rwx_cs_error (a,b) values ('c11','11')";   //错误sql,表rwx_cs_error不存在
		$sql3 = "insert into rwx_cs (a,b) values ('c111','111')";       //正确的
		$model->startTrans();
		$r = $model->query($sql);  
		if($r===false){
			$model->rollback();
			exit("操作失败a1");
		}
		$r = $model->query($sql2);
		if($r===false){
			$model->rollback();
			exit("操作失败a11");
		}
		$r = $model->query($sql3);
		if($r===false){
			$model->rollback();
			exit("操作失败a111");
		}
		$model->commit();
		echo "操作成功";
	 */
	public function startTrans(){
		return $this->db->beginTransaction();
	}
	
	
	/**
	 * 提交事务
	 * @link http://www.php.net/manual/en/pdo.commit.php
	 * @return bool Returns true on success or false on failure. 
	 */
	public function commit(){
		return $this->db->commit();
	}
	
		
	/**
	 * 事务回滚
	 * @link http://www.php.net/manual/en/pdo.rollback.php
	 * @return bool Returns true on success or false on failure. 
	 */
	public function rollback(){
		return $this->db->rollBack();
	}

	
	/**
	 * 【自定义】query原样执行
	 * @param yes string $sql 完整的sql语句
	 * @author berhp 2018.12.26
	 * @return array || boolean
	 * @tutorial
	 *   设计:
	 *      1.Select,Insert,Update,Referenece,Delete,Create,  (不区分大小写)
	 *   	2.所有执行失败时,固定返回  false;
	 *      3.select 查,返回数据array,不存在则是空数组,存在则是二维数组;
	 *      4.insert 增,若表存在自增ID, 则返回受影响的自增ID;  不存在自增,则返回  true;  ★若一次仅插入1条ID正确, 一次插入多条数据,仅返回最早插入的那条自增ID
	 */
	public function query($sql){
		$this->_sql =$sql;
		$r = $this->db->prepare($sql);
		$check = $r->execute();
		self::_error();
		if($check===true){
			if(stristr($sql,"Select")){
				return $r->fetchAll( \PDO::FETCH_ASSOC );  //array
			}
			if(stristr($sql,"Insert")){
				return $this->db->lastInsertId('id') ? $this->db->lastInsertId('id') : true;  //string自增ID || true
			}
			return true;
		}else{
			$errorInfo = $r->errorInfo();    //Array([0] => 42S02,[1] => 1146,[2] => Table 'demo.rwx_userx' doesn't exist)
			self::_error($errorInfo[1].' - '.$errorInfo[2]);
			return false;
		}
	}


	
	
	/**
	 * 【自定义】- 错误记录处理
	 * @param yes string $msg 即将写录的错误信息，如: 1146 - Table 'demo.rwx_userx' doesn't exist
	 * @tutorial
	 *   1. 写错误log记录 (若在框架内)
	 */
	private function _error( $errorInfo="" ){
		if( defined('MyPHP_DIR') && $errorInfo ){
			$msg = '[action_error]:'.MODULE_NAME.'/'.CONTROLLER_NAME.'/'.ACTION_NAME."\n";
			$msg .= "[sql]:{$this->_sql}\n";
			$msg .= "[sql_error]:{$errorInfo}";
			$log = new \Log\write();
			$log->write($msg);
		}
		if(APP_DEBUG===true){
			$msg = "[sql]:{$this->_sql}";
			$log = new \Log\write();
			$log->write($msg);
		}
	}
	
	
	
	
	
	
}