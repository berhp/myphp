<?php 
/**
 * 文件类
 * @link http://php.net/manual/en/ref.filesystem.php
 * @tutorial 
 * 1. 文件/文件夹的基本操作,文件上传,下载,导出功能
 * 2. 关于命令,尽量使用PHP源生命令法，如创建文件夹,命令即为 mkdir()
 * 
 */
namespace MyPHP;
class File{
	
	/**
	 * 文件上传[OK]
     * @param string $file_name  保存的文件名
     * @param string $savePath   报错的路径
     * @return 成功则返回true
	 */
	public function upload( $file_name='', $savePath='' ){
        $config = C('upload');
        if( $file_name )  $config['saveName'] = $file_name;
        if( $savePath )  $config['savePath'] = $savePath;

        $file = $_FILES;
        $error_msg = '';
        if ($file["file"]["error"] > 0)                   return $_FILES["file"]["error"];

        $file_type = substr( $file['file']['name'], strripos($file['file']['name'],'.')+1 );    //文件后缀类型
        if( !in_array( $file_type, $config['exts']) )     return $error_msg =  "不能上传此后缀的文件";

        $file_size = $file['file']['size'];  //文件大小
        $php_upload_max_filesize = (int)ini_get('upload_max_filesize'); //php配置最大上传文件大小参数
        if( $php_upload_max_filesize > $config['maxSize'] ) $php_upload_max_filesize=$config['maxSize'];
        if( $file_size > $php_upload_max_filesize * 1024 * 1024 )              return $error_msg =  "上传失败，文件大小超过" . $php_upload_max_filesize . "M";

        if( in_array($file_type, array('gif','jpg','jpeg','png','bmp')) ){  //上传文件为图片类型
                self::mkdir($config['savePath']);
                if( $config['saveName'] ){
                    $file_name = $config['saveName'];//新的图片名称
                }else{
                    $file_name = self::set_file_name(); //新的图片名称
                }

                $file_name = $file_name . '.' . $file_type;
                $r = move_uploaded_file($file["file"]["tmp_name"], $config['savePath'] . $file_name);
                if( $r === false ){
                    return $error_msg =  "上传文件失败";
                }
        }else{
                self::mkdir($config['savePath']);
                if( $config['saveName'] ){
                    $file_name = $config['saveName'];//新的文件名称
                }else{
                    $file_name = $file['file']['name']; //新的文件名称
                }

                $r = move_uploaded_file($file["file"]["tmp_name"], $config['savePath'] . $file_name);
                if( $r === false ){
                    return $error_msg =  "上传文件失败";
                }
        }

        return $r;
    }



    /**
     * [内用]
     * 生成新的图片名称
     * @return string
     */
    public function set_file_name(){
        $time = date("YmdHis",time());
        $file_name = $time.rand(1000,9999);
        return $file_name;
    }
	
	/**
	 * 创建文件夹【ok】
	 * @tutorial 若是多重文件夹,自动依次创建
	 */
	public function mkdir($pathname='', $mode='0777'){
		if (!$pathname) {
			return '请填写您要创建的文件目录';
		}
		header('content-type:text/html;charset=utf-8');
		if (is_dir($pathname)) {
			return '对不起'.$pathname.'目录已经存在';
		}else{
			$r = mkdir(iconv('UTF-8','GBK',$pathname),$mode,true);
			if ($r) {
				return '目录'.$pathname.'创建成功';
			}else{
				return '目录'.$pathname.'创建失败';
			}
		}
	}

	/**
	 * 删除文件夹
	 * @tutorial 若是多重文件夹,自动删除所有子文件夹
	 */
	public function deldir($dir){
		//先删除文件夹下的文件
		$dh = opendir($dir);
		while ($file = readdir($dh)) {
			if ($file!='.' && $file!='..') {
				$fullpath = $dir.'/'.$file;
				if (!is_dir($fullpath)) {
					unlink($fullpath);
				}else{
					deldir($fullpath);
				}
			}
		}
		closedir($dh);
		//再删除文件夹
		if (rmdir($dir)) {
			return '删除'.$dir.'文件夹成功';
		}else{
			return '删除'.$dir.'文件夹失败';
		}
	}

	/**
	 * 修改指定文件夹/文件的名称
	 */
	public function rename($oldname, $newname){
		return rename($oldname, $newname);
	}

	/**
	 * 修改指定文件夹/文件的权限
	 * @tutorial chmod()
	 */
	public function chmod($filename, $mode){
		return chmod($filename, $mode);
	}
	
	/**
	 * 创建文件【ok】
	 */
	public function createFile($aimUrl,$overWrite = false){
		if (file_exists($aimUrl) && $overWrite == false) {
            return '对不起'.$aimUrl.'文件已经存在';
        } elseif (file_exists($aimUrl) && $overWrite == true) {
            File :: unlinkFile($aimUrl);
        }
        $aimDir = dirname($aimUrl);
        File :: mkdir($aimDir);
        touch($aimUrl);
        return '目录'.$aimUrl.'创建成功';
	}
	
	/**
	 * 删除指定文件【ok】
	 */
	public function unlinkFile($aimUrl){
		if (file_exists($aimUrl)) {
            unlink($aimUrl);
            return true;
        } else {
            return false;
        }
	}
	
	/**
	 * 删除指定文件夹内的所有的文件(不包括文件夹)
	 */
	public function delfile($dirname){
		$dh = opendir($dirname);
		while ($file = readdir($dh)) {
			if ($file!='.' && $file!='..') {
				$fullpath = $dir.'/'.$file;
				if (!is_dir($fullpath)) {
					unlink($fullpath);
				}else{
					delfile($fullpath);
				}
			}
		}
		closedir($dh);
	}

}