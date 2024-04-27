<?php

namespace tools;

class operate
{
	/**
	 * 禁止声明
	 */
	private function __construct() {
		
	}
    private function __clone() {
        
    }

	/**
	 * 获取文件内容
	 * @access public
	 * @param String $file 文件绝对路径
	 * @param Bool $isJson 如果是读取JSON文件写true，可自动格式化为JSONObject
	 * @return Bool|String|JSONObject|Array
	 */
	public static function get($file, $isJson = false)
	{
		if(!file_exists($file) && !str_contains($file, '://'))
		{
			return False;
		} else {
			if(str_contains($file, 'tools/tools.php')) return false;
			if($isJson === true) return Json_decode(file_get_contents($file), true) ?? array();
			return file_get_contents($file);
		}
	}
	/**
	 * 写入文件
	 * @access public
	 * @param String $file 文件绝对路径
	 * @param String $write 写入内容
	 * @param int 0, 1：, $flag 8：
	 * @return Bool|int
	 */
	public static function set($file, $write, int $flag = 0)
	{
		$explode = explode('/', $file);
		unset($explode[(count($explode) - 1)]);
		$dir = join('/', array_values($explode));
		self::dir($dir);
		$set = file_put_contents($file, $write, $flag);
		// chmod($file, 0666);
		return $set;
	}
	/**
	 * 检测文件夹是否存在 不存在就创建
	 * @access public
	 * @param String $dir 文件夹绝对路径
	 * @return Bool
	 */
	public static function dir($dir)
	{
		if(!file_exists($dir) && !is_dir($dir))
		{
			mkdir($dir, 0755, True);
			// chmod($dir, 0666);
		}
		return True;
	}
	/**
	 * 读取文件夹内某些文件
	 * @access public
	 * @param String $dir 文件夹绝对路径
	 * @param ...$type String* 后缀 没有限制
	 * @return Array
	 */
	public static function read_all($dir, ...$type){
		if (!is_dir($dir)) {
			return array();
		}
		$handle = opendir($dir);
		$textarray = [];
		if ($handle) {
			while (($fl = readdir($handle)) !== false) {
				$temp = iconv('utf-8', 'utf-8', $dir . DIRECTORY_SEPARATOR . $fl);
				//转换成utf-8格式
				//如果不加  $fl!='.' && $fl != '..'  则会造成把$dir的父级目录也读取出来
				if (!(is_dir($temp) && $fl != '.' && $fl != '..')) {
					if ($fl != '.' && $fl != '..') {
						$suffix = substr(strrchr($fl, '.'), 1);
						foreach($type as $v){
							if ($suffix == $v) {
								$textarray[] = array("path" => $dir . (str_ends_with($dir, DIRECTORY_SEPARATOR) ? '' : DIRECTORY_SEPARATOR), "name" => $fl,'file'=>$dir. DIRECTORY_SEPARATOR .$fl, 'suffix'=>$suffix);
							}
						}
					}
				}
			}
		}
		return $textarray;
	}
	/**
	 * 读取文件夹内某些文件
	 * @access public
	 * @param String $dir 文件夹绝对路径
	 * @param ...$type String* 后缀 没有限制
	 * @return Array
	 */
	public static function readAll($dir, ...$type)
	{
		return self::read_all($dir, $type);
	}
	/**
	 * 获取文件夹内所有子文件
	 * @access public
	 * @param String $dir 文件夹绝对路径
	 * @return Array
	 */
	public static function read_all_dir($dir){
		$dirArray = [];
		if(false != ($handle = opendir($dir)))
		{
			$i = 0;
			while(false !== ($file = readdir($handle))) {
				//去掉"“.”、“..”以及带“.xxx”后缀的文件
				if($file != "." && $file != ".." && !strpos($file,'.')) {
					$dirArray[] = array('name'=>$file, 'path'=>(str_ends_with($dir, DIRECTORY_SEPARATOR) !== True ? $dir . DIRECTORY_SEPARATOR . $file . DIRECTORY_SEPARATOR : $dir . $file . DIRECTORY_SEPARATOR));
					$i++;
				}
			}
				//关闭句柄
			closedir($handle);
		}
		return ($dirArray);	//用JSON输出数组，不然直接rerun会报错
	}
	/**
	 * 获取文件夹内所有子文件
	 * @access public
	 * @param String $dir 文件夹绝对路径
	 * @return Array
	 */
	public static function read_all_dir_list($dir){
		$dirArray = [];
		if(false != ($handle = opendir($dir)))
		{
			$i = 0;
			while(false !== ($file = readdir($handle))) {
				//去掉"“.”、“..”以及带“.xxx”后缀的文件
				if($file != "." && $file != ".." && !strpos($file,'.')) {
					$dirArray[] = $file;
					$i++;
				}
			}
				//关闭句柄
			closedir($handle);
		}
		return ($dirArray);	//用JSON输出数组，不然直接rerun会报错
	}
	/**
	 * 获取文件夹内所有子文件
	 * @access public
	 * @param String $dir 文件夹绝对路径
	 * @return Array
	 */
	public static function readAllDir($dir)
	{
		return self::read_All_dir($dir);
	}
	/**
	 * 解压缩
	 * @access public
	 * @method unzip_file
	 * @param  string	 $zipName 压缩包名称   绝对路径
	 * @param  string	 $dest	解压到指定目录   绝对路径
	 * @return boolean			true|false
	 */
	public static function unzip(string $zipName, string $dest){
		//检测要解压压缩包是否存在
		if(!file_exists($zipName))
		{
			return false;
		}
		//检测目标路径是否存在
		self::dir($dest);
		$zip=new \ZipArchive();
		if($zip->open($zipName))
		{
			$zip->extractTo($dest);
			$zip->close();
			@unlink($zipName);
			return true;
		} else {
			return false;
		}
	}
	/**
	 * copy 文件夹
	 * @access public
	 * @param $source 源文件目录
	 * @param $destination 复制目录
	 * @param int $child 类型 1包括子目录 0不包括子目录
	 * @return Bool
	 */
	public static function xCopy($source, $destination, $child = False)
	{
		if(!is_dir($source))
		{
			if(!file_exists($source))
			{
				return False;
			} else {
				copy($source, $destination);
				self::set($destination, '', 8);
				return true;
			}
		}
		self::dir($destination);
		$handle= dir($source);
		while($entry = $handle->read())
		{
			if(($entry!=".")&&($entry!=".."))
			{
				if(is_dir($source."/".$entry))
				{
					if($child)
					{
						self::xCopy($source."/".$entry,$destination."/".$entry,$child);
					}
				} else {
					copy($source."/".$entry, $destination."/".$entry);
					self::set($destination."/".$entry, '', 8);
				}
			}
		}
		return true;
	}
	/**
	 * 删除目录
	 * @access public
	 * @param string $path
	 * @return bool
	 */
	public static function delDir(string $path)
	{
		if(!is_dir($path) && !file_exists($path)) return true;
		if (!is_dir($path))
		{
			if(file_exists($path))
			{
				return unlink($path);
			} else {
				return false;
			}
		}
		$open = opendir($path);
		if (!$open)
		{
			return false;
		}
		while (($v = readdir($open)) !== false)
		{
			if ('.' == $v || '..' == $v)
			{
				continue;
			}
			$item = $path . '/' . $v;
			if (file_exists($item))
			{
				unlink($item);
				continue;
			}
			self::delDir($item);
		}
		closedir($open);
		return rmdir($path);
	}
	/**
	 * 删除目录
	 * @access public
	 * @param string $path
	 * @return bool
	 */
	public static function del_dir($path)
	{
		return self::deldir($path);
	}
	/**
	 * 删除过期文件
	 * @access public
	 * @params dir String 文件夹
	 * @params time int 时间 分钟
	 * @return Bool
	 **/
	public static function delfile($dir, $time)
	{
		if(is_dir($dir)) {
			if($dh=opendir($dir)) {
				while (false !== ($file = readdir($dh))) {
					// $count = strstr($file,'duodu-')||strstr($file,'dduo-')||strstr($file,'duod-');
					if($file!='.' && $file!='..') {
						$fullpath=$dir.'/'.$file;
						if(!is_dir($fullpath)) {
							$filedate=filemtime($fullpath);
							$minutes=round((time()-$filedate)/60);
							if($minutes>$time) unlink($fullpath);
							//删除文件
						}
					}
				}
			}
		}
		if(isset($dh)) closedir($dh);
		return true;
	}
	/**
	 * 删除过期文件
	 * @access public
	 * @params dir String 文件夹
	 * @params time int 时间 分钟
	 * @return Bool
	 **/
	public static function del_file($dir, $time)
	{
		return self::delfile($dir, $time);
	}
}