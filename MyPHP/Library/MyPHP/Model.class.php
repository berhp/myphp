<?php 
/**
 * 基础model类
 */ 
namespace MyPHP;

class Model{
	
	/**
	 * 【参数】-是否来自PC电脑端,若是的话,分页功能,会新增分页div代码,默认false-不是
	 */
	public $is_pc=false;

    public function __construct(){
        $this->_init();
    }

    /**
     * 【接口】-Model优先执行,这里勿写逻辑,由子类来实现做什么
     */
    public function _init(){}

    /**
     * 接口 获取多条-(有分页) - ok(自定义业务功能)
     * @param string $table  // 表名，支持取别名，支持内链其他表,注意 最前面不要表前缀; 如  user_cameraman_photo A INNER JOIN app_user B ON A.uid=B.uid
     * @param string $where
     * @param string $field
     * @param string $order  // 排序
     * @param string $join   	如 rwx_goods g on i.goods_id=g.goods_id
     * @param string $join_type 链表方式, 支持"INNER JOIN","LEFT JOIN","RIGHT JOIN";默认'INNER JOIN',需写全,避免忘记源生
     * @return array() showData()  // 含有分页的信息
     * @tutorial
     *  1.先查询满足条件的一共有多少条数据，2.然后根据 page信息 动态返回数据
     */
    public function m_db_getlistpage( $table='', $where='', $field='', $order='' ,$join='', $join_type='INNER JOIN' ){
    	$total = M($table)->field($field)->join($join, $join_type)->where($where)->count();
    	$page = ( $this->is_pc===false ) ? page($total) : pageShow($total);
    	if(!$total) return showData( array(), '', 0, $page, '');
    	$limit = $page['limit'];
    	$r = M($table)->field($field)->join($join, $join_type)->where($where)->order($order)->limit($limit)->select();
    	if(!$r){
    		return showData( array(), '', 0, $page, '');  //没有数据时
    	}else{
    		return showData( $r, '', 0, $page, '');
    	}
    }
    
    
    
    
}