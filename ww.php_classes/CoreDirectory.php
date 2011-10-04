<?php
class CoreDirectory{
	public static function getSize($directory) {
		$size=0;
		$dirs=new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($directory)
		);
		foreach ($dirs as $file) {
			$size+=$file->getSize();
		}
    return $size;
	}
	public static function delete($directory) {
		if (is_dir($directory)) {
			$objects=scandir($directory);
			foreach ($objects as $object) {
				if ($object!='.'&&$object!='..') {
					if (is_dir($directory.'/'.$object)) {
						CoreDirectory::delete($directory.'/'.$object);
					}
					else {
						unlink($directory.'/'.$object);
					}
				}
			}
			reset($objects);
			rmdir($directory);
		}
	} 
}
