<?php
/*
	to use this in your server, edit /etc/php.ini and add this file to auto_append_file
	then add coverage-prepend.php to auto_prepend_file.
	requires xdebug to be installed.
*/
if (defined('COVERAGE_ON')) {
	/*
		create this file, then chown it so Apache can edit it.
	*/
	$stats_file='/var/log/php-coverage';
	foreach (xdebug_get_code_coverage() as $k=>$v) {
		$str=$k.' | ';
		$str.=join(',', array_keys($v));
		file_put_contents($stats_file, $str."\n", FILE_APPEND);
	}
}
