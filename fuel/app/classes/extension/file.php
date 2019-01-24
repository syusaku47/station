<?php

class File extends \Fuel\Core\File
{

	public static function delete_dir($path, $recursive = true, $delete_top = true, $area = null)
	{
		$path = rtrim(static::instance($area)->get_path($path), '\\/') . DS;
		if (! is_dir($path)) {
			// marietta
			// throw new \InvalidPathException('Cannot delete directory: given path: "'.$path.'" is not a directory.');
			return;
		}
		
		$files = static::read_dir($path, - 1, array(), $area);
		
		$not_empty = false;
		$check = true;
		foreach ($files as $dir => $file) {
			if (is_array($file)) {
				if ($recursive) {
					$check = static::delete_dir($path . $dir, true, true, $area);
				} else {
					$not_empty = true;
				}
			} else {
				$check = static::delete($path . $file, $area);
			}
			
			// abort if something went wrong
			if (! $check) {
				// marietta
				// throw new \FileAccessException('Directory deletion aborted prematurely, part of the operation failed.');
			}
		}
		
		if (! $not_empty and $delete_top) {
			return rmdir($path);
		}
		return true;
	}

	public static function zip_dir($dir, $file, $root = "")
	{
		$zip = new ZipArchive();
		$res = $zip->open($file, ZipArchive::CREATE);
		
		if ($res) {
			// $rootが指定されていればその名前のフォルダにファイルをまとめる
			if ($root != '') {
				$zip->addEmptyDir($root);
				$root .= DIRECTORY_SEPARATOR;
			}
			
			$baselen = mb_strlen($dir);
			
			$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS | FilesystemIterator::KEY_AS_PATHNAME | FilesystemIterator::CURRENT_AS_FILEINFO), RecursiveIteratorIterator::SELF_FIRST);
			
			$list = array();
			foreach ($iterator as $pathname => $info) {
				$localpath = $root . mb_substr($pathname, $baselen);
				
				if ($info->isFile()) {
					$zip->addFile($pathname, $localpath);
				} else {
					$res = $zip->addEmptyDir($localpath);
				}
			}
			
			$zip->close();
		} else {
			return false;
		}
	}

	public static function delete_old_file($dir, $expire = '-1 day', $prefix = null)
	{
		if (! is_dir($dir)) {
			return;
		}
		
		foreach (glob($dir . '*.*') as $path) {
			if ($prefix && ! strstr(pathinfo($path, PATHINFO_BASENAME), $prefix)) {
				continue;
			}
			
			if (filemtime($path) < strtotime($expire)) {
				parent::delete($path);
			}
		}
	}
}
