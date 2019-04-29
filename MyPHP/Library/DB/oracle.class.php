<?php
/**
 * oracle 数据库操作
 * @author ty
 * @date 2019/2/19
 */
namespace DB;

class oracle{
	private $db='';
	private $host='127.0.0.1'; //数据库主机名
	private $user='root';      //数据库连接用户名
	private $password='myroot123';          //对应的密码
	private $port='1521';      //端口
	private $sid='ORCL';   //数据库唯一标识

	private $table='';
	private $prefix='';
	private $alias='';
	private $z_alias='A'; //子查询特殊表别名
	private $where='';
	private $join='';
	private $field='';
	private $_sql='';
	private $order='';
	private $limit='';
	private $_insert_into_key='';
	private $_insert_into_value='';
	private $_update_str='';

	public function __construct($config=[])
	{
		if(!empty($config)){
			$this->host=$config['host'];
			$this->user=$config['user'];
			$this->password=$config['password'];
			$this->port=$config['port'];
			$this->sid=$config['sid'];
			$this->prefix=$config['prefix'];
		}
		$this->host=isset($config['host'])?$config['host']:$this->host;
		$this->db=new \PDO("oci:dbname=".$this->host.":".$this->port."/{$this->sid}",$this->user,$this->password);
        return $this->db;
	}

	/**
	 * 设置表名
	 * @param string $tableName 表名
	 * @param bool $isaction false 则不判断和拼接表前缀
	 * @return $this
	 */
	public function table($tableName='', $isaction=false){
		if( $isaction === true && strpos($tableName, $this->prefix) === false ){
			$this->table = $this->prefix . $tableName;
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
	 * $where=['uid'=>'1','is_user'=>1];
	 * $where='uid=1';
	 * $where = array(
	     array( "(a=1" ),
	     'b' => '2',
	     array( "and (c=1 or d=2))", "or (e='3')" ),
	   );
	 * @param string $where
	 * @return $this
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
					if(!strpos($k,'.')) $k=$this->z_alias.'."'.$k.'"';
					$create_where.=strlen($create_where)?'AND '.$k.'='.$this->db->quote($v):$k.'='.$this->db->quote($v);
				}
			}
		}else{
			$create_where.=strlen($create_where)?' AND '.$where:$where;
		}
		$this->where=trim($create_where);
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
	 * $m->table('xxx a')->join('xxx b on b.id=a.id','LEFT JOIN')
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
	public function field($field=''){
		$field=strlen($field)?$field:$this->z_alias.'.*';
		if(!strpos($field,'.')) $field=$this->z_alias.'.'.$field;
		$this->field=$field;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function count(){
		$sql = "SELECT count(*) as \"mcount\" FROM {$this->table} {$this->alias} {$this->join} {$this->where}";
		$result = $this->query($sql);
		if($result===false) return false;
		return $result[0]['mcount'];
	}


	/**
	 * @param string $sql
	 * @return array
	 */
	public function find($sql=''){
		if(!$sql){
			if( !$this->field ) $this->field = $this->z_alias.'.*';
			if(!$this->alias) $this->alias=$this->z_alias;
			if($this->where) $this->where=$this->where.' AND ';
			$sql = "SELECT {$this->field} FROM {$this->table} {$this->alias} {$this->join} WHERE {$this->where}  rownum=1 {$this->order}";
		}
		$this->_sql = $sql;
		$result = $this->query($sql);
		if(!$result) return array();
		return $result[0];
	}

	/**
	 * $m->table('xxx')->limit('')->select();
	 * @param string $limit
	 * @return $this
	 */
	public function limit($limit=''){
		if($limit)$this->limit=$limit;
		return $this;
	}

	/**
	 * @param string $sql
	 * @return mixed
	 */
	public function select($sql=''){
		$this->table = '"'.$this->table.'"';
		$limit=$this->limit;

		if(!$sql){
			if( !$this->field ) $this->field = $this->z_alias.'.*';
			if(!$this->alias) $this->alias=$this->z_alias;
			if($this->where) $this->where=$this->where.' AND ';
			if(strlen($limit)){
				if(strpos($limit,',')){
					$limit_data=explode(',',$limit);
					$max=$limit_data[0]+$limit_data[1];
					$min=$limit_data[0];
				}else{
					$max=$limit;
					$min=0;
				}
				$sql=" SELECT * FROM
    (select {$this->field}, rownum rn from {$this->table} {$this->alias} {$this->join} WHERE {$this->where} rownum <= {$max}  {$this->order})
where rn >{$min}";
			}else{
				$sql = "SELECT {$this->field} FROM {$this->table} {$this->alias} {$this->join} {$this->where}  {$this->order} {$this->limit}";
			}
		}
		$this->_sql = $sql;
		echo $sql;
		$result =$this->query($sql);
		return $result;
	}

	/**
	 * @return mixed
	 */
	public function delete(){
		if(!$this->alias) $this->alias=$this->z_alias;
		$sql = "DELETE FROM {$this->table} {$this->alias} WHERE {$this->where}";
		$this->_sql = $sql;
		$r = $this->query($sql);
		return $r;
	}

	/**
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
	 * 添加一条
	 * @param array $data
	 * @return bool
	 */
	public function add($data=array()){
		if(!empty($data))$this->create_insert_data_one($data);
		if(!strlen($this->_insert_into_key)) return false;
		$sql="INSERT INTO {$this->table}  {$this->_insert_into_key}  VALUES  {$this->_insert_into_value}";
		$r = $this->query($sql);
		return $r;
	}

	/**
	 * 添加多条
	 * @param array $data
	 * @return mixed
	 */
	public function addALL($data=array()){
		$sql='';
		if(!empty($data)){
			if($this->arrayLevel($data)==1){
				$this->create_insert_data_one($data);
				$sql = "INSERT INTO {$this->table} {$this->_insert_into_key} VALUES {$this->_insert_into_value}";
			}
			if($this->arrayLevel($data)==2){
				$this->create_insert_data_two($data);
				$sql = "INSERT ALL  {$this->_insert_into_value}";
			}
		}

		$this->_sql =  $sql;
		$r = $this->query($sql);
		return $r;
	}

	/**
	 * @param array $data
	 * @return mixed
	 */
	public function save($data=array()){
		if($data)$this->create_update_data($data);
		if(!strpos($this->table,'.')) $this->table=$this->table.' '.$this->z_alias;
		$sql = "UPDATE {$this->table} SET {$this->_update_str} WHERE {$this->where}";
		$this->_sql =  $sql;
		$r = $this->query($sql);
		return $r;
	}

	/**
	 * 批量修改
	 * @param array $dataList
	 * @param string $fieldkey
	 * @return bool
	 * $data=[
	 *   [
	 *     'id'=>1,
	 *     'name'=>'demo1'
	 *   ],
	 *   [
	 *     'id'=>2,
	 *     'name'=>'demo2'
	 *   ]
	 * ];
	 * $m->table('xxx')->saveAll($data,'id');
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
			$sql.='"'.$key.'" = CASE '.$fieldkey.' ';
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
	 * @param $field
	 * @param int $step
	 * @return mixed
	 */
	public function setInc($field,$step=1) {
		$field='"'.$field.'"';
		$sql = "UPDATE {$this->table} {$this->z_alias} SET {$field}={$field}+{$step} WHERE {$this->where}";
		$this->_sql = $sql;
		echo $sql;
		$result = $this->query($sql);
		return $result;
	}

	/**
	 * @param $field
	 * @param int $step
	 * @return mixed
	 */
	public function setDec($field,$step=1) {
		$field='"'.$field.'"';
		$sql = "UPDATE {$this->table} {$this->z_alias} SET {$field}={$field}-{$step} WHERE {$this->where}";
		$this->_sql =  $sql;
		$result =$this->query($sql);
		return $result;
	}

	/**
	 * 字段拼接内容
	 * name='demo';
	 * $m->table('xxx')->where('id'=>1))->setConcat('name', 'xxx');  //name->demoxxx
	 * @param $field
	 * @param string $content
	 * @return mixed
	 */
	public function setConcat($field,$content='') {
		$field='"'.$field.'"';
		$this->table=$this->table.' '.$this->z_alias;
		$sql = "UPDATE {$this->table} SET {$field}=CONCAT({$field},'{$content}') WHERE {$this->where}";
		$this->_sql = $sql;
		$result = $this->query($sql);
		return $result;
	}


	/**
	 * @param array $data
	 * @return bool
	 */
	private function create_update_data(array $data){
		if($this->arrayLevel($data)>1) return false;
		$this->_update_str='';
		foreach($data as $k=>$v){
			$this->_update_str.='"'.$k.'"=\''.addslashes($v).'\',';
		}
		$this->_update_str=substr($this->_update_str,0,-1);
	}

	/**
	 * @param array $data
	 */
	private function create_insert_data_one(array $data){
		$this->_insert_into_key='(';
		$this->_insert_into_value='(';
		foreach($data as $k=>$v){
			$this->_insert_into_key.='"'.$k.'",';
			$this->_insert_into_value.="'".addslashes($v)."',";;
		}
		$this->_insert_into_key=substr($this->_insert_into_key,0,-1).')';
		$this->_insert_into_value=substr($this->_insert_into_value,0,-1).')';
	}

	/**
	 * @param array $data
	 * @return bool
	 */
	private function create_insert_data_two(array $data){
		$first_key_data=reset($data);
		$str='';
		foreach($first_key_data as $key=>$value){
			$str.='"'.$key.'",';
		}
		$this->_insert_into_key=' ('.substr($str,0,-1).')';
		$this->_insert_into_value='';
		foreach($data as $val){
			if(array_diff_key($first_key_data,$val)) return false;
			$ls_str='INTO '.$this->table.$this->_insert_into_key.' values (';
			foreach($val as $k=>$v){
				$ls_str.="'".addslashes($v)."',";;
			}
			$this->_insert_into_value.=substr($ls_str,0,-1).') ';
		}
		$this->_insert_into_value=substr($this->_insert_into_value,0,-1).' select 1 from dual';
	}

	/**
	 * @return bool
	 */
	public function startTrans(){
		return $this->db->beginTransaction();
	}

	/**
	 * @return bool
	 */
	public function commit(){
		return $this->db->commit();
	}

	/**
	 * @return bool
	 */
	public function rollback(){
		return $this->db->rollBack();
	}

	/**
	 * @param $sql
	 * @return array|bool
	 */
	public function query($sql){
		$this->_sql =$sql;
		$r = $this->db->prepare($sql);
		$check = $r->execute();
		if(stristr($sql,'select')) $check=$r->fetchAll(\PDO::FETCH_ASSOC);
		return $check;
	}
}