<?php
class WW_Directory{
	public static function getSize($directory){
		$size=0;
		foreach(
			new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator($directory)
			)
			as $file
		){
			$size+=$file->getSize();
    }
    return $size;
	}
	public static function delete($directory){
		if(is_dir($directory)) {
			$objects=scandir($directory);
			foreach($objects as $object){
				if($object!='.'&&$object!='..'){
					if(is_dir($directory.'/'.$object))
						WW_Directory::delete($directory.'/'.$object);
					else
						unlink($directory.'/'.$object);
				}
			}
			reset($objects);
			rmdir($directory);
		}
	} 
}
?>
