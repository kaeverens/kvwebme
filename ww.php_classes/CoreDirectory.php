<?php
/**
	* CoreDirectory class
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

// { class CoreDirectory

/**
	* CoreDirectory class
	*
	*	@category WebME
	* @package  WebME
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvweb.me/
	*/
class CoreDirectory{
	// { getSize

	/**
		* get the size in bytes of a directory
		*
		* @param string $directory directory to check
		*
		* @return int size
		*/
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

	// }
	// { delete

	/**
		* recursively delete a directory
		*
		* @param string $directory directory to check
		*
		* @return null
		*/
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
	// }
}

// }
