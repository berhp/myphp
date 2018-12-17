<?php 
/**
 * zip类
 * @author berhp
 * @link http://php.net/manual/zh/book.zip.php
 * @tutorial 
 * windows 需要开启 php_zip.dll扩展
 * linux 必须在编译 PHP 时用 --enable-zip 配置选项来提供 zip 支持。
 * 
 *  扩展安装地址： http://pecl.php.net/package/zip
 *  
 * 	Start of zip v.1.9.1
 */
namespace MyPHP;
class Zip{

	public function __construct(){
		if( !function_exists('zip_open') ) die('需要开启zip扩展功能');
	}
	
	/**
	 * 压缩为zip (  wait...  )
	 * @link http://php.net/manual/zh/class.ziparchive.php
	 * @link http://php.net/manual/zh/ref.dir.php
	 * @return boolean
	 * @tutorial
	 * 	(PHP 5 >= 5.2.0, PHP 7, PECL zip >= 1.8.0)
	 * 	设计命名: 参考为linux命令:# zip all.zip *.jpg    这条命令是将所有.jpg的文件压缩成一个zip包 
	 * @example
		<?php
		$zip = new ZipArchive;
		$res = $zip->open('test.zip', ZipArchive::CREATE);
		if ($res === TRUE) {
		    $zip->addFromString('test.txt', 'file content goes here');
		    $zip->addFile('data.txt', 'entryname.txt');
		    $zip->close();
		    echo 'ok';
		} else {
		    echo 'failed';
		}
		?>
	 */
	public function zip($path='', $filename='test.zip'){
		p($path);
		if(!is_dir($path)) return false;
		
		// 读取目录
		$f = array();
		if ($dh = opendir($path)) {
			while (($file = readdir($dh)) !== false) {
				echo "filename: $file : filetype: " . filetype($path . $file) . "\n";
				if(filetype($path . $file)=='dir'){
					self::get_dir_contents($path . $file);
				}
			}
			closedir($dh);
		}
		
		die;
		// 写zip
		$zip = new \ZipArchive;
		$res = $zip->open($filename, \ZipArchive::CREATE);
		if($res===true){
			$zip->addEmptyDir('demodir');
			$zip->addFromString('test.txt', 'file content goes here');
			$zip->addFile('data.txt', 'entryname.txt');
			$zip->close();
			echo 'ok';
		}else{
			echo 'failed';
		}
	}
	
	/**
	 * 【内用】-遍历文件夹内容,标记是文件还是文件夹 ( wait... )
	 * @param yes string $path 文件夹路径,如 Application/
	 */
	private function get_dir_contents($path=''){
		if ($dh = opendir($path)) {
			while (($file = readdir($dh)) !== false) {
				echo "xxfilename: $file : filetype: " . filetype($path . $file) . "\n";
			}
			closedir($dh);
		}
	}

	/**
	 * 解压zip [ok]
	 * @param string $filename  .zip文件路径
	 * @param string $path		路径,解压到什么文件夹内
	 * @tutorial
	 *   设计命名: 参考为linux命令:# unzip all.zip  这条命令是将all.zip中的所有文件解压出来 
	 *   设计细节: 若文件夹名称中有'.'则认为他为文件,进行创建
	 * @example
	 $r = new \MyPHP\Zip();
	 $r->index('Uploads/Application.zip','Uploads/xx/');
	 */
	public function unzip($filename='', $path=''){
			if(!is_dir($path)){
				mkdir($path, 0775, true);
			}
			$zip = new \ZipArchive;
			$res = $zip->open( $filename );
			if ($res === TRUE) {
				//解压缩到test文件夹
				$zip->extractTo( $path );
				$zip->close();
				return true;
			} else {
				return fasle;
			}
	}
	
}