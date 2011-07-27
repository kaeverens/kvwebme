<?php

/**
 * ww.php_classes/Theme.php, KV-Webme
 *
 * theme class, gets information on themes from
 * a zip file or the theme directory
 *
 * @author  Conor Mac Aoidh <conormacaoidh@gmail.com>
 * @author  Kae Verens <kae@kvsites.ie>
 * @license GPL 2.0
 * @version 1.0
 */

class Theme{
	var $dir;
	bool $zip;
	/**
	 * __construct
	 *
	 * accepts both paths to directories and
	 * paths to zip files. to use zip files the
	 * second parameter must be true 
	 */
	function __construct($dir, $zip = false) {
		$this->zip = $zip;
		/**
		 * if a zip file is being used, extract
		 * its contents to a temporary directory
		 * NOTE: expects zip directory structure
		 * to be correct
		 */
		if ($zip == true) {
			if (!file_exists($dir)) {
				return false;
			}
			$temp_dir = USERBASE . 'temp_dir';
			shell_exec('mkdir ' . $temp_dir);
			shell_exec('cd ' . $temp_dir . ' && unzip -o ' . $dir);
			$dir = basename($dir, '.zip');
			$this->dir = $dir;
			return true;
		}
		if (!is_dir($dir)) {
			return false;
		}
		$this->dir = $dir;
		return true;
	}

	/**
	 * getVariants
	 *
	 * returns an array of variants in the theme
	 */
	function getVariants() {
		$variant_dir = $this->dir . '/cs/';
		$variants = array();
		/**
		 * if the dir doesn't exist return empty array
		 */
		if (!is_dir($variant_dir)) {
			return $variants;
		}
		/**
		 * loop through theme dir
		 */
		$handler = opendir($variant_dir);
		while ($file = readdir($handler)) {
			if ($file == '.' || $file == '..') {
				continue;
			}
			/**
			 * get file extention
			 */
			$name = explode('.', $file);
			$ext = end($name);
			if ($ext == 'css') {
				$name = reset($name);
				array_push($variants, $name);
			}
		}
		closedir($handler);
		return $variants;
	}

	/**
	 * __destruct
	 *
	 * removes the temp directory when dealing with zip files
	 */
	function __destruct() {
		if ($this->zip == true) {
			shell_exec('rm -rf ' . USERBASE . 'temp_dir');
		}
	}
}
