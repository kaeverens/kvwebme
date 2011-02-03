<?php
/*
	to use this in your server, edit /etc/php.ini and add this file to auto_prepend_file
	then add coverage-append.php to auto_append_file.
	requires xdebug to be installed.
*/

if (!rand(0, 999)) {
	define('COVERAGE_ON', true);
	xdebug_start_code_coverage();
}
