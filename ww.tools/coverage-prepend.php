<?php
/**
	* turn on code coverage
	*
	* to use this in your server, edit /etc/php.ini and add this file to
	* auto_prepend_file then add coverage-append.php to auto_append_file.
	* requires xdebug to be installed.
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

if (!rand(0, 999)) {
	define('COVERAGE_ON', true);
	xdebug_start_code_coverage();
}
