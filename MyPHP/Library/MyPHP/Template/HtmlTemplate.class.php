<?php
/**
 * Class Template 模版-自定义标签库
 * 继承了基础 Template.class.php类
 */
namespace MyPHP\Template;
use MyPHP\Template;
class HtmlTemplate extends  Template{
    //自定义标签库
    public $tagLib = array('mselect','volist','if');

   //自定义实现标签的逻辑，注意需要有'_'前缀, 都返回字符串
    /**
     * demo mselect标签处理
     * @author berhp
     * @tutorial 提供的属性有: table,filed,where,group,order,limit,join,having,alias,id(id是动态创建变量视图好输出,同一个页面需要唯一)
     * @param string $str  固定来源字符串,低层类传值过来,如: <mselect table='user' where='uid<10'>bc</mselect>
     * @param array $param 标签属性参数,低层类传值过来,如 array( 'table'=>'user', 'where'=>'uid<10' )
     * @param string $param 自定义标签中的内容,如 bc
     * @return string
     */
    public function _mselect($str, $param, $content){
        $table  = isset($param['table'])?$param['table']:'';
        $field  = isset($param['field'])?$param['field']:'';
        $where  = isset($param['where'])?$param['where']:'';
        $group  = isset($param['group'])?$param['group']:'';
        $order  = isset($param['order'])?$param['order']:'';
        $limit  = isset($param['limit'])?$param['limit']:'';
        $join   = isset($param['join'])?$param['join']:'';
        $having = isset($param['having'])?$param['having']:'';
        $alias  = isset($param['alias'])?$param['alias']:'';
        $key=$param['id'];
        $db = new \DB\mysqli();
        $r = $db->table($table)->alias($alias)->join($join)->field($field)->where($where)->group($group)->having($having)->order($order)->limit($limit)->select();
        if(!$r) return '';
        //传数据;
        $this->setdata($key,$r);

        $_str = '';
        $_str .= '<?php foreach($'.$key.' as $k=>$'.$key.'){ ?>';
        $_str .= $content;
        $_str .= '<?php } ?>';
        return $_str;
    }
    

    /**
     *  volist标签
     * @author berhp
     * @param yes id $data 变量数据
     * @param yes name 别名 v
     * @param no  key  别名 k,若不写,默认为 k
     * @tutorial 提供的属性有: name,id,key
     * @return string
     * @html:
     *   <volist id='data' name='v' key='k'>html</volist>
     */
	public function _volist($str,$param,$content){
		$data = $param['id'];
		$name = isset($param['name']) ? $param['name'] : 'v';
		$key  = isset($param['key']) ? $param['key'] : 'k';
		$_str = '';
		$_str .= '<?php foreach($'.$data.' as $'.$key.'=>$'.$name.'){ ?>';
		$_str .= $content;
		$_str .= '<?php } ?>';
		return $_str;
	}
	
	
	/**
	 * if标签
	 * @author berhp
	 * @return string
 	 * @tutorial 提供的属性有: where
	 * @tutorial 
	 * 		1. 特殊判断符号  ===  !==  ==  !=  >  >=  <  <=  && ||
	 * 		2. ★仅替换自定义标签中的属性中的： 中文 全角或半角的 〈  替换为 <   将 中文 全角或半角的 〉替换为  > ★处理输出值,不会替换中文的 〈,〉
	 * 		3. PHP语句:
				$a = 1; $b=11;
				if( $a==1 ){
					echo 11;
				}elseif($a>=2){
					echo 22;
				}else{
					echo 33;
				}
			4. 自定义标签属性 where书写格式,如:
				$a==1
				$a>=1
				$a==1&&$b==2
				$a===1
				$a==1||$b==2
			5. where=".." 中间,自定义条件不支持空格.
			6. where=".." 中间,可支持自定义函数或内置函数,如 in_array($a,array(1,2,3))
	   @html:
	   <if where="$a==1">{$b}<elseif where='$a〉=2'>22<else>33</if>
	 */
	public function _if($str,$param,$content){
		$name = $param['where'];  if(!$name) return '';
		$name = str_replace(array('〈','〉','《','》'),array('<','>','<','>'), $name);  //if where条件替换处理
		//判断处理数据
		$_ifData = '';  $_elseifData = array();  $_elseData = '';  $_is_elseif = false;  $_is_else = false;
		
		$_r = mb_stripos( $content, '<elseif', null, 'utf-8');
		if( $_r !== false ) $_is_elseif = true;
		if($_is_elseif === true){
			$_ifData = mb_substr($content, 0, $_r, 'utf-8'); 		//有<elseif时,if处理值
			$_elseifData = self::__action_elseifData( $content );
		}

		$_r = mb_stripos( $content, '<else>', null, 'utf-8');
		if( $_r !== false ) $_is_else = true;
		if( $_is_elseif === false && $_is_else === true ){
			$_ifData = mb_substr($content, 0, $_r, 'utf-8'); 		//无<elseif,有<else>时,if处理值
			$_elseifData = self::__action_elseifData( $content );
		}
		if( $_is_else === true ){
			$_elseData = self::__action_elseData( $content );
		}

		if( $_is_elseif === false && $_is_else === false ){
			$_ifData = mb_substr($content, 0, null, 'utf-8'); 		//无<elseif,有<else>时,if处理值
		}
		
		$_str = '';
		$_str .= '<?php if( '.$name.' ){ ?>';
		$_str .= $_ifData;
		if( $_is_elseif === true ){
			$_str .= $_elseifData;
		}
		if( $_is_else === true ){
			$_str .= '<?php }else{ ?>';
			$_str .= $_elseData;
		}
		$_str .= '<?php } ?>';
		return $_str;
	}
	/**
	 * 【内用】-处理返回elseif内容值
	 * @author berhp
	 * @param yes string $content 来自<if>..</if>中间的源内容 
	 * @return string $_str
	 * @example $str = '';
	   @html:
	   <if where="$a==1">{$b}<elseif where='$a〉=2'>22<else>33>>>>>></if>
	 */
	private function __action_elseifData( $content='' ){
		$preg = '/\<elseif(.*?)\>/is';
		$str = preg_replace_callback( $preg, 'self::__action_elseif_preg', $content );
		$_start = mb_stripos( $str, '<?php }elseif', null, 'utf-8' );
		$check = mb_stripos( $str, '<else>', null, 'utf-8');
		if( $check === false ){
			$_length = null; 	//没有出现<else>时,取全部
		}else{
			$_length = $check - $_start;
		}
		$_str = mb_substr( $str, $_start, $_length, 'utf-8' ); if($_str===false) $_str='';
		return $_str;
	}
	/**
	 * 【内用-正则回调方法】-开始处理解析 <elseif where="">
	 * @author berhp
	 * @param unknown $r 来自正则匹配出的源数据
	 * @return string
	 * @tutorial
	 * 		1. 去除条件中所有空格,替换中文〈,〉
	 * 		2. 过滤,不是<elseif where=''>格式的 <elseif..>标签
	 * 		3. 最后拼接出  }elseif(..){
	 */
	private function __action_elseif_preg( $r ){
		if(!is_array($r)) return '';
		$content = isset( $r[1] ) ? $r[1] : '';
		$str 	= str_replace(array( ' ', '〈', '〉' ),array( '', '<', '>' ), $content);  if(!$str) return '';
		$_r 	= mb_stripos( $str, '=', null, 'utf-8');
		$_key 	= mb_substr( $str, 0,    $_r, 'utf-8' );
		$_value = mb_substr( $str, $_r+2, -1, 'utf-8' );
		if( $_key != 'where' ) return '';
		$_str = ''; 
		$_str .= '<?php }elseif('.$_value.'){ ?>';
		return $_str;
	}
	/**
	 * 【内用】-处理返回else内容值
	 * @author berhp
	 * @param yes string $content 来自<if>..</if>中间的源内容 
	 * @example $str = 'this is example'  
	 * @tutorial 设计:
	 * 		1. else后的值,不替换中文的〉
	 * 		2. 仅取同一个<if>..</if>中最后出现<else>后的值,★从<else>之后截取
	 */
	private function __action_elseData( $content='' ){
		$_r = mb_strrpos( $content, '<else>', null, 'utf-8');  if($_r===false) $_r=0;
		$_start = $_r + 6;
		$str = mb_substr($content, $_start, null, 'utf-8');
		if($str===false) $str='';
		return $str;
	}
	
	

}