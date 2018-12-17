<?php 
/**
 * html 相关的
 */
namespace MyPHP;
class Html{
	
	
	/**
	 * Excel 导出
	 * @author berhp 2018.7.26
	 * @param yes array      $data  自定义数据,或 数据库数据
	 * @param yes str||array $title 第一行的标题
	 * @param no str $filename 文件名
	 * @param no str $charset 编码,默认为utf-8 
	 * @example
		    $array = array(
					array(1, '张三', '男', '22', 183, 72),
					array(2, '李四', '女', '18', 170, 50),
					array(3, '王五', '男', '14', 178, 68),
					array(4, '赵六', '女', '34', 163, 48)
			);
		
			$title = 'a,b,c,d';  //方式一
			$title = array('编号', '姓名', '性别', '年龄', '身高', '体重');
			
			$filename = 'csdemo';
			
			$r = new \MyPHP\Html();
			$r->daochu_excel( $array, $title, $filename );
			
	   @tutorial  html table方式导出时,可在td中自定义,如:
		header("Content-type:application/vnd.ms-excel");
		header("Content-Disposition:attachment;filename=export_data.xls");
		header('Pragma: no-cache');
		header('Expires: 0');
		$str=<<<EOF
<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
	<head>
		<meta http-equiv="Content-type" content="text/html;charset={$charset}">
		<meta name=ProgId content=Excel.Sheet>
		<meta name=Generator content="Microsoft Excel 11">
	</head>
	<body>
		<div id="mdiv" align=center -x:publishsource="Excel">
		 <table x:str border=0 cellpadding=0 cellspacing=0 -style="border-collapse: collapse">
			 <tr><td style="background-color: #00CC00;">123456789012345678910548975214654</td><td>Robbin会吐口水</td></tr>
			 <tr><td class="xx">5678</td><td>av测试测试非的</td></tr>
		 </table>
		</div>
	</body>
</html>
EOF;
		echo $str;
	 */
	public function daochu_excel($data=array(), $title='', $filename='demo', $charset='utf-8'){
		ob_clean();
		header("Content-type:application/vnd.ms-excel");
		header("Content-Disposition:attachment;filename={$filename}.xls");
		header('Pragma: no-cache');
		header('Expires: 0');
		$str='
<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
<head>
		<meta http-equiv="Content-type" content="text/html;charset={$charset}">
		<meta name=ProgId content=Excel.Sheet>
		<meta name=Generator content="Microsoft Excel 11">
				
<style>
<!-- @page
	{margin:0.98in 0.75in 0.98in 0.75in; mso-header-margin:0.51in; mso-footer-margin:0.51in;}
tr  {mso-height-source:auto; mso-ruby-visibility:none;}
col {mso-width-source:auto;mso-ruby-visibility:none;}
br  {mso-data-placement:same-cell;}
td
{mso-style-parent:style0;
	padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	mso-number-format:"General";
	text-align:general;
	vertical-align:middle;
	white-space:nowrap;
	mso-rotate:0;
	mso-pattern:auto;
	mso-background-source:auto;
	color:#000000;
	font-size:12.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:宋体;
	mso-generic-font-family:auto;
	mso-font-charset:134;
	border:none;
	mso-protection:locked visible;}
 -->
</style>
				
  <!--[if gte mso 9]>
   <xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>Sheet1</x:Name><x:WorksheetOptions><x:DefaultRowHeight>285</x:DefaultRowHeight><x:Selected/><x:Panes><x:Pane><x:Number>3</x:Number><x:ActiveCol>3</x:ActiveCol><x:ActiveRow>5</x:ActiveRow><x:RangeSelection>D6</x:RangeSelection></x:Pane></x:Panes><x:ProtectContents>False</x:ProtectContents><x:ProtectObjects>False</x:ProtectObjects><x:ProtectScenarios>False</x:ProtectScenarios><x:PageBreakZoom>100</x:PageBreakZoom><x:Print><x:PaperSizeIndex>9</x:PaperSizeIndex></x:Print></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets><x:ProtectStructure>False</x:ProtectStructure><x:ProtectWindows>False</x:ProtectWindows><x:WindowHeight>10350</x:WindowHeight><x:WindowWidth>23280</x:WindowWidth></x:ExcelWorkbook></xml>
  <![endif]-->
	</head>
	<body link="blue" vlink="purple">
		<div align=center x:publishsource="Excel">
		 <table x:str border=0 cellpadding=0 cellspacing=0 style="border-collapse:collapse;table-layout:fixed;">';

		/*  //演示-样式内容: 支持自定义 样式
		$body = "<tr><td style='background-color: #00CC00;'>123456789012345678910548975214654</td><td>Robbin会吐口水</td></tr>
				 <tr><td style='background-color:blue;color:#fff'>xxx</td><td>演示</td></tr>";
		echo $body;
		*/
		$str2 = '</table></div></body></html>';
		
		//输出html表格前部分内容
		echo $str;
		
		//判断输出标题
		if($title){
			if( !is_array($title)){
				$title = explode(',', $title);
			}
			echo "<tr>";
			foreach ($title as $v){
				//echo "<td>{$v}</td>";
				$str = iconv($charset, 'gb2312',$v);  //避免windows offices 是gb2312的编码打不开utf-8的内容
				echo "<td>{$str}</td>";
			}			
			echo "</tr>";
		}

		//判断内容
		foreach ($data as $v) {
			echo "<tr>";
			foreach ($v as $k2=>$v2){
				//echo "<td>{$v2}</td>";
				$str = iconv($charset, 'gb2312',$v2);  //避免windows offices 是gb2312的编码打不开utf-8的内容
				echo "<td>{$str}</td>";
			}
			echo "</tr>";
		}
		//输出html表格后部分内容
		echo $str2;
		
		/*  //旧方法,虽然可以导出,但若出现数字比较长时,会被excel给特殊转译了
		ob_clean();
		header('Content-Type: application/vnd.ms-excel');
		header("Content-Disposition: attachment; filename={$filename}.xls");
		header('Pragma: no-cache');
		header('Expires: 0');
		if( is_array($title)){
			echo iconv('utf-8', 'gb2312', implode("\t", $title)), "\n";
		}else{
			echo iconv('utf-8', 'gb2312', str_replace(',', "\t", $title) ), "\n";
		}
		
		foreach ($data as $value) {
			echo iconv('utf-8', 'gbk', implode("\t", $value)), "\n";
		}
		*/
	}
	
	
	
	
	/**
	 * 导出Word demo
	 */
	public function daochu_word(){
		header('Content-Type: application/doc');
		header('Content-Disposition: attachment; filename=demo.docx');
		header('Pragma: no-cache');
		header('Expires: 0');
		
		$title = '				问卷调查';
		$data = array(
				0 => array(
						'title'=>'1+1=?',
						'content'=>'A:1 B:2 C:3 D:4',
				),
				1 => array(
						'title'=>'1+2=?',
						'content'=>'A:1 B:2 C:3 D:4',
				),
		);
		echo $title;
		foreach ($data as $k=>$v){
	//自定义排版输出-开始
$str=<<<EOF

	{$v['title']}
	{$v['content']}

EOF;
	//自定义排版输出-结束
			echo $str;
		}
	}
	
	
	/**
	 * 获取随机字符串
	 * @param no int 	$length 长度, 默认6
	 * @param no string $_str 字符串源,支持中文,可自定义传承配置, 默认为大小英文和0-9数字
	 * @param no string $charset 字符串源编码,默认 utf-8
	 * @return string
	 */
	public function getRandomStr( $length=6, $_str='abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ', $charset='utf-8' ){
		if($length<1) return '';
		$_str_length = mb_strlen($_str, $charset); if($_str_length<1) return '';
		$str = '';
		for($i=0;$i<$_str_length;$i++){
			$str .= $_str[rand(0, $_str_length-1)];
		}
		return $str;
	}
	
	
}