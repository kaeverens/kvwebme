<?php
/**
	* distribution-specific variables
	* if you create your own version of WebME, this class will help you make
	* it unique
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

class DistConfig{
	static $vals=array();
	// { get

	/**
		* retrieve a value
		*
		* @param string $name the variable to retrieve
		*
		* @return mixed value
		*/
	static function get($name) {
		if (!isset(self::$vals[$name])) {
			if (count(self::$vals)) {
				self::$vals[$name]='';
			}
			if (file_exists($_SERVER['DOCUMENT_ROOT'].'/ww.incs/distconfig.php')) {
				require_once $_SERVER['DOCUMENT_ROOT'].'/ww.incs/distconfig.php';
				self::$vals=$config;
			}
			else {
				require_once $_SERVER['DOCUMENT_ROOT']
					.'/ww.incs/distconfig.example.php';
				self::$vals=$config;
			}
			if (!isset(self::$vals[$name])) {
				self::$vals[$name]='';
			}
		}
		return self::$vals[$name];
	}

	// }
}
