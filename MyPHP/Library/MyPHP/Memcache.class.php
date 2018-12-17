<?php 
/**
 * @author berhp
 * 使用memcache需要
 * 1. 服务器安装了 memcached服务
 * 2. 服务器安装了 并且  php开启了 memcache扩展
 * 帮助文档,见php官方手册
 * link: http://php.net/manual/zh/book.memcache.php
 * (linux)安装下载地址:
 * link: http://pecl.php.net/package/memcached
 * 
 * (windows)memcached 1.4.13 服务器安装下载地址:
 * link: www.newasp.net/soft/63735.html
 * 
 * (windows)php_memcache.dll 扩展下载地址:
 * link: http://windows.php.net/downloads/pecl/releases/memcache/3.0.8/
 * 
 * 官方memcache类源码与案列
 * link: http://pecl.php.net/package/memcache
 * 
 * @example
 	$d = new \MyPHP\Memcache();
	$data['starttime'] =  microtime(TRUE);
	$r = $d->set('s','this is s2', 0, 3);  //向memcached中写内容,这里为不进行压缩,有效时间为3秒,存$key='s', $value='this is s2';
	var_dump($r);
	
	$r = $d->get('s');  //从memcached中获取内容
	var_dump($r);
	
	$data['count'] = 0;
	while ($b = $d->get('s') ){
		$data['count']++; //模拟统计可以获取到多少次
	}
	$r = $d->get('s');
	var_dump($r);
	
	$data['overtime'] =  microtime(TRUE);
	$data['offtime'] = $data['overtime']-$data['starttime'];
	print_r($data);
 */
namespace MyPHP;
class Memcache{
	public $host = '127.0.0.1'; //连接服务器的IP
	public $port = '11211';		//端口,默认为11211
	public $timeout = 0;		//连接持续（超时）时间,单位秒,默认0-不限链接时间
	public $memcache = '';

	function __construct($host='', $port='', $timeout=''){
		if( !class_exists('memcache') ) die('需要安装memcache功能');
		if($host){
			$this->host = $host;
		}
		if($port){
			$this->port = $port;
		}
		if($timeout){
			$this->timeout = $timeout;
		}
		$this->memcache = new \Memcache();
		$this->memcache->connect($this->host, $this->port, $this->timeout);
	}

	/**
	 * 脚本结束会自动关闭 memcache,也可以控制来关闭
	 */
	function __destruct(){
		//$this->memcache->close();
	}

	/**
	 * 短连接memcached服务器
	 * @param string $host		//memcached服务端监听主机地址。这个参数也可以指定为其他传输方式比如unix:///path/to/memcached.sock 来使用Unix域socket，在这种方式下，port参数必须设置为0。
	 * @param number $port		//memcached服务端监听端口。当使用Unix域socket的时候要设置此参数为0。
	 * @param number $timeout	//连接持续（超时）时间，单位秒。默认值1秒，修改此值之前请三思，过长的连接持续时间可能会导致失去所有的缓存优势。
	 * @tutorial
	 *   说明:此连接方式,在脚本结束后,会自动关闭连接, 也可以在脚本中途指定关闭
	 */
	public function connect ($host, $port = null, $timeout = null){
		$this->memcache->connect($host,$port,$timeout);	
	}
	

	/**
	 * 长连接memcached服务器
	 * @param string $host
	 * @param number $port
	 * @param number $timeout
	 * @tutorial
	 *   说明:此连接方式,在脚本结束后,不会自动关闭连接,除非你指定关闭
	 */
	public function pconnect ($host, $port = null, $timeout = null){
		$this->memcache->pconnect($host,$port,$timeout);
	}

	/**
	 * 关闭连接
	 */
	public function close(){
		$this->memcache->close();
	}
	
	/**
	 * 增 - 增加一个条目到缓存服务器
	 * @param unknown $key	//将要分配给变量的key。
	 * @param unknown $var	//将要被存储的变量。字符串和整型被以原文存储，其他类型序列化后存储。
	 * @param int $flag		//使用MEMCACHE_COMPRESSED标记对数据进行压缩(使用zlib)。 0-不使用 2-使用
	 * @param int $expire 	//当前写入缓存的数据的失效时间,  0-永不过期; 其他数字,如 30-表示 30秒后过期  ★不能超过 2592000秒（30天）
	 * @return boolean  	//成功时返回 TRUE， 或者在失败时返回 FALSE。 如果这个key已经存在返回FALSE。
	 * @tutorial
	 *  ★此方法在缓存服务器之前不存在key时， 以key作为key存储一个变量var到缓存服务器。  ★注意区分set
	 */
	public function add ($key, $var,  $flag = 0, $expire = 0 ) {
		$r = $this->memcache->add($key, $var, $flag, $expire);
		return $r;
	}

	/**
	 * 设置 (常用)
	 * @param unknown $key
	 * @param unknown $var
	 * @param int $flag			// 0-不压缩  2-压缩 ( 即常量  MEMCACHE_COMPRESSED )的值
	 * @param int $expire		// 0-永不过期, 其他 如 30-表示30秒后过期
	 * @return boolean  		// 成功时返回 TRUE， 或者在失败时返回 FALSE
	 * @tutorial
	 * 	   向key存储一个元素key值为var。
	 *   1.谨记：资源类型变量（比如文件或连接）不能被存储在缓存中，因为它们在序列化状态不能被完整描述。
	 *   2.当key已存在时,set会修改他的值
	 */
	public function set ($key, $var, $flag = 0, $expire = 0) {
		$r = $this->memcache->set($key, $var, $flag, $expire);
		return $r;		
	}
	
	
	/**
	 * 删
	 * @param unknown $key		//要删除的元素的key。
	 * @param number $timeout	//删除该元素的执行时间。如果值为0,则该元素立即删除，如果值为30,元素会在30秒内被删除。
	 * @return boolean 			//成功时返回 TRUE， 或者在失败时返回 FALSE。
	 * @tutorial
	 *  从服务端删除一个元素
	 */
	public function delete ($key, $timeout =0 ) {
		$r = $this->memcache->delete($key, $timeout);
		return $r;
	}
	
	/**
	 * 改
	 * @param unknown $key
	 * @param unknown $var
	 * @param int $flag
	 * @param int $expire
	 * @tutorial
	 *  替换已经存在的元素的值, 通过key来查找元素并替换其值。当key 对应的元素不存在时，Memcache::replace()返回FALSE。
	 */
	public function replace ($key, $var, $flag = 0, $expire = 0) {
		$r = $this->memcache->replace($key, $var, $flag, $expire);
		return $r;
	}
	
	/**
	 * 查
	 * @param string | array $key		//key 或者 key数组
	 * @return 
	 * 	返回key对应的存储元素的字符串值或者在失败或key未找到的时候返回FALSE。
	 * @tutorial
	 *   eg:
	 *   $key = 'aa';  //1个key 
	 *   $key = array('aa','bb');  //多个key
	 */
	public function get ($key) {
		$r = $this->memcache->get($key);
		return $r;		
	}	
	
	
	/**
	 * 清除（删除）已存储的所有的元素
	 * @return boolean  //成功时返回 TRUE， 或者在失败时返回 FALSE。
	 * @tutorial
	 *   ★立即使所有已经存在的元素失效。方法Memcache::flush() ★并不会真正的释放任何资源,而是仅仅标记所有元素都失效了,因此已经被使用的内存会被新的元素复写。 
	 */
	public function flush (){
		$r = $this->memcache->flush();
		return $r;
	}
	
	/**
	 * 释放内存,清除所有的缓存数据
	 * @tutorial 
	 * 1.谨慎使用哦
	 * 2.此方法 用 调用 连接 命令行，然后 telnet 127.0.0.1  11211
	 * 3.在memcache中 输入命令 flush_all  来执行的
	 * @tutorial 
	 * 
	 */
	
	
	/**
	 * 获取服务器统计信息
	 */
	public function getStats (){
		$r = $this->memcache->getStats();
		return $r;		
	}
	
	/**
	 * 获取服务器版本信息
	 */
	public function getVersion (){
		$r = $this->memcache->getVersion();
		return $r;
	}
	
	/**
	 * 开启大值自动压缩
	 * @param int $threshold		//控制多大值进行自动压缩的阈值。 如 20000
	 * @param float $min_savings	//指定经过压缩实际存储的值的压缩率，支持的值必须在0和1之间。默认值是0.2表示20%压缩率。如 0.2
	 * @return boolean  成功时返回 TRUE， 或者在失败时返回 FALSE。
	 * @tutorial 
	 *   memcache2.0.0之后才能用
	 */
	public function setCompressThreshold ( $threshold, $min_savings = null ){
		$r = $this->memcache->setCompressThreshold( $threshold, $min_savings );
		return $r;
	}
	

	/**
	 * 【重载方法】-更多未封装的方法,直接调 源生 Memcache类的方法,执行 (ok)
	 */
	public function __call($name, $param){
		$obj = $this->memcache;   //即 $obj = new \Memcache();
		return call_user_func_array( array($obj, $name), $param );
	}

	
}