<?php 
/**
 * Redis缓存类
 * @author berhp
 * @tutorial
 *   1. 需要安装redis服务器
 *   2. 需要php.ini配置加载php_redis.dll扩展
 * @tutorial
 *	redis最佳环境为linux,默认的端口为  6379
 *	=============================
 *	PHP官网dll扩展 下载地址
 *	http://pecl.php.net/package/redis
 *
 * 备注：PHP5.6以下（含5.6.X） 地址为: http://pecl.php.net/package/redis/2.2.7/windows
 * 备注：PHP5.6以上从前面选
 *	
 *	redis中文文档:
 *	http://www.cnblogs.com/weafer/archive/2011/09/21/2184059.html
 *	
 *	redis linux下载地址:
 *	https://redis.io/download
 *	
 *	redis windows下载地址:
 *	https://github.com/MSOpenTech/redis/releases
 *	
 *	如 Redis-x64-3.2.100.msi  是安装包
 *	
 *	=====配置，默认端口为 6379===
 *	max memonry为最大内存
 *
 *	=== 其他说明====
 *	redis-server.exe    redis服务器
 *	redis-cli.exe       redis客户端
 * 
 * 官方实例文档
 * @link https://github.com/phpredis/phpredis/blob/develop/tests/
 *
 * @tutorial
 *  1. 可以用hash 哈希算法内存中储存数据
 *  2. 必要时可以把内存中的数据导出到硬盘中
 *  3. set()方法 默认都是数据库0,若切换到数据库1 用select(1); 从数据库互相移动用move($key,$dbindex)
 *  4. redis可以存在多个相同的key名，存放在不同的数据库下,但memcache不存在数据库的说法
 *  5. redis中在同一个数据库中 key名唯一
 * @tutorial
 * 	1. 这里仅仅二次封装了常用的方法
 *  2. 未封装的方法,或直接调用低层源生redis类中的方法执行。
 *  3. 二次封装,只是提供构造方法,连接上了redis服务器。
 *  4. 常用方法可以参考: http://www.jb51.net/article/51884.htm
 */
namespace MyPHP;
class Redis{
	public $host = '127.0.0.1'; //连接服务器的IP
	public $port = '6379';		//端口,默认为6379
	public $timeout = 0;		//连接持续（超时）时间,单位秒,默认0-不限链接时间
	public $redis = '';

	function __construct($host='', $port='', $timeout=''){
		if( !class_exists('redis') )  die('需要安装redis功能');
		if($host){
			$this->host = $host;
		}
		if($port){
			$this->port = $port;
		}
		if($timeout){
			$this->timeout = $timeout;
		}
		$this->redis = new \Redis();
		$this->redis->connect($this->host, $this->port, $this->timeout);
	}
	
	function __destruct(){}


	/**
	 * 【重载方法】-更多未封装的方法,直接调 源生 redis类的方法,执行 (ok)
	 * @author berhp  2017.12.6
	 * @version 1.0
	 * @tutorial
	 *  设计: 用源生PHP的call_user_func_array()方法,动态回调方法名,将$param数组值,依次作为动态方法中的参数1,参数2,参数n, 进行传参后执行。
	 * @eg::
	 *
			$model = new MyPHP\Redis();
			//$_redis_uids = $model->get('new_uids');
			//var_dump($_redis_uids);
			$r = $model->rPush('new_uids', '1');
			var_dump($r);
	 *
	 */
	public function __call($name, $param){
		$obj = $this->redis;   //即 $obj = new \Redis();
		return call_user_func_array( array($obj, $name), $param );
	}
	
	/**
	 * 获取redis信息 (ok)
	 */
	public function info(){
		return $this->redis->info();
	}

	/**
	 * 清空所有缓存数据 (ok)
	 * @return bool
	 */
	public function flushAll(){
		return $this->redis->flushAll();
	}

	/**
	 * 【特色功能】从当前数据库中清空所有缓存数据 (ok)
	 * @return  bool: Always TRUE.
	 * @link    http://redis.io/commands/flushdb
	 * @example $redis->flushDB();
	 */
	public function flushDB( ){
		return $this->redis->flushDB();
	}

	/**
	 * 【特色功能】切换数据库 (ok)
	 * @param   int     $dbindex  数据库标识,默认为0
	 * @return  bool   true-切换成功 false-失败
	 * @link    http://redis.io/commands/select
	 * @example
	 * <pre>
	 * $redis->select(0);       // switch to DB 0
	 * $redis->set('x', '42');  // write 42 to x
	 * $redis->move('x', 1);    // move to DB 1 将数据库0中的x移动到数据库1
	 * $redis->select(1);       // switch to DB 1
	 * $redis->get('x');        // will return 42
	 * </pre>
	 */
	public function select( $dbindex=0 ){
		return $this->redis->select($dbindex);
	}

	/**
	 * 查询已缓存的key键名 (ok)
	 * @param no string $keyname 键名,默认*表示所有
	 * @return array
	 * @link    http://redis.io/commands/keys
	 * @example
	 * <pre>
	 * $allKeys = $redis->keys('*');   // all keys will match this.
	 * $keyWithUserPrefix = $redis->keys('user*');  //只查询以user开头的所有键名
	 * </pre>
	 */
	public function keys( $keyname='*' ){
		return $this->redis->keys( $keyname );
	}

	/**
	 * 增 (ok)
	 * @param string $key
	 * @param string $value
	 * @param number $time  缓存时间,单位秒,0-无限
	 * @tutorial
	 *   1.此方式缓存的key类型为 string
	 *   2. 获取用get()
	 * @example
	 * $redis->delete('key');
	 * $redis->set('key', 'value');
	 * $r = $redis->get('key');
	 */
	public function set($key='',$value='',$time=0){
		return $this->redis->set($key,$value,$time);
	}

	
	/**
	 * 删 (ok)
	 * @param string $key  删除哪个key
	 * @return int 0-删除失败 1-删除成功
	 * @tutorial
	 * $this->redis->delete($key);  //与他一样效果
	 */
	public function del($key=''){
		return $this->redis->del($key);
	}

	/**
	 * 删 (ok)
	 * @param string $key  删除哪个key
	 * @return int 0-删除失败 1-删除成功
	 * @tutorial
	 * $this->redis->del($key);  //与他一样效果
	 */
	public function delete($key=''){
		return $this->redis->delete($key);
	}

	/**
	 * 修改key的名 (ok)
	 * @param   string  $srcKey  //原key名
	 * @param   string  $dstKey   //新key名
	 * @return  bool:   TRUE in case of success, FALSE in case of failure.
	 * @link    http://redis.io/commands/rename
	 * @example
	 * <pre>
	 * $redis->set('x', '42');
	 * $redis->rename('x', 'y');
	 * $redis->get('y');   // → 42
	 * $redis->get('x');   // → `FALSE`
	 * </pre>
	 */
	public function rename( $srcKey, $dstKey ){
		return $this->redis->rename(  $srcKey, $dstKey );
	}

	/**
	 * 查  (ok)
	 * @return bool || string  若没有缓存返回false
	 */
	public function get($key=''){
		return $this->redis->get($key);
	}

	/**
	 * 移动数据 (ok)
	 * @param   string  $key  //key名
	 * @param   int     $dbindex //从当前的数据库移动到哪个数据库
	 * @return  bool:   TRUE in case of success, FALSE in case of failure.
	 * @link    http://redis.io/commands/move
	 * @example
	 * <pre>
	 * $redis->select(0);       // switch to DB 0
	 * $redis->set('x', '42');  // write 42 to x
	 * $redis->move('x', 1);    // move to DB 1
	 * $redis->select(1);       // switch to DB 1
	 * $redis->get('x');        // will return 42
	 * </pre>
	 */
	public function move($key='', $dbindex=0){
		return $this->redis->move( $key, $dbindex );
	}

	/**
	 * 【特色功能】事务开始(ok)
	 * @tutorial
		1.  在一个事务中的所有命令作为单个独立的操作顺序执行。在Redis事务中的执行过程中而另一客户机发出的请求，这是不可以的；
		  Redis事务是原子的。原子意味着要么所有的命令都执行，要么都不执行；
	 *  2. 需要与 self::exec()事务提交  或  self::discard()事务回滚一起使用
	 * @example
	 * <pre>
	 * $ret = $redis->multi()
	 *      ->set('key1', 'val1')
	 *      ->get('key1')
	 *      ->set('key2', 'val2')
	 *      ->get('key2')
	 *      ->exec();
	 * </pre>
	 */
	public function multi(){
		return $this->redis->multi();
	}

	/**
	 * 【特殊功能】事务回滚(ok)
	 */
	public function discard(){
		return $this->redis->discard();
	}

	/**
	 * 【特色功能】事务提交(ok)
	 */
	public function exec(){
		return $this->redis->exec();
	}

	/**
	 * 【特色功能】备份(将数据同步保存到磁盘) (ok)
	 * @return  bool
	 * @tutorial  说明:若备份成功会在redis的安装目录下生成一个  dump.rdb 文件
	 */
	public function save(){
		return $this->redis->save();
	}

	/**
	 * 【特色功能】备份(将数据异步保存到磁盘) (ok)
	 * @return  bool
	 * @tutorial  说明:若备份成功会在redis的安装目录下生成一个  dump.rdb 文件
	 */
	public function bgsave(){
		return $this->redis->bgsave();
	}

	/**
	 * 【特色功能】还原(导入到内存) (ok)
	 * @tutorial
	 *   1. 并不存直接的导入方法
	 *   2. 将之前备份的dump.rdb文件,放入redis安装目录下,重启redis即自动还原
	 */

	/**
	 * 返回上次成功将数据保存到磁盘的Unix时间戳 (ok)
	 * @link    http://redis.io/commands/lastsave
	 * @return int  如  1482736071
	 */
	public function lastSave(){
		return $this->redis->lastSave();
	}



	/**
	 * 返回key的类型值(ok)
	 * @tutorial  ★备注：类型指的是,这个key是用什么方式设置的, 如 set('k','v'); 类型为1;
	 * @param  yes string  $key  要查询的key键名
	 * @return  int 详细值,查看redis低层源代码
	     0 - key不存在   Redis::REDIS_NOT_FOUND
		 1 - string: Redis::REDIS_STRING  如 set('k','v')的类型为1; 获取用 get('k');
		 2 - set:   Redis::REDIS_SET
		 3 - list:  Redis::REDIS_LIST   如 lpush('k3','v1','v2')的类型为3, 获取用 lrange('k3',0,-1)
		 4 - zset:  Redis::REDIS_ZSET
		 5 - hash:  Redis::REDIS_HASH   如 hmset('k5',array('id'=>'1','name'=>'zhang3'))的类型为5
	 * @link    http://redis.io/commands/type
	 * @example $redis->type('key');
	 *
	 */
	public function type($key='k5'){
		return $this->redis->type($key);
	}

}