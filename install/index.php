<?php
/**
	* installer welcome page
	*
	* PHP version 5.3
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

require 'header.php';

$phpversion = phpversion();
$ver = split("[/ ]", $_SERVER[ 'SERVER_SOFTWARE' ]);
$apacheversion = $ver[ 1 ] . ' ' . $ver[ 2 ];

echo '<div id="dialog" style="display:none" title="'
	.__('Help - Write Access').'">'
	.'<p>'
	.__(
		'The quickest way to get this working is to execute the following'
		.' command, but it is also the <strong>least secure</strong> method:'
	)
	.'</p>'
	.'<i>chmod -R 777 '.$home_dir.'</i></div>'
	.'<h3>'.__('Installation Requirements').'</h3>'
	.'<p><i style="clear:none">'
	.__(
		'The requirements below are the minimum specifications needed to run the'
		.' system reliably. You may install without meeting all of these'
		.' requirements, but some aspects of the system may not function properly.'
	)
	.'</i></p>'
	.'<table class="row-color"><tr><th>'
	.__('Software')
	.'</th><th>&nbsp;</th><td>'
	.__('Installed')
	.'</td></tr>';

// { write access
$access=(is_writable($home_dir))
	?'OK'
	:'<span class="error">'.__('Not Granted').' <a href="#" id="howto">'
		.'(?)</a></span>';
echo '<tr><td>'.__('Write Access').'</td>'
	.'<td><code>'.$home_dir.'</code></td><td>'.$access.'</td></tr>';
// }
// { php version
$php=($phpversion<'5.3')
	?'<span class="error">'.$phpversion.' '
	.__('you need PHP 5.3 or PECL json 1.2').'</span>'
	:'OK: '.$phpversion;
echo '<tr><td>'.__('PHP Version:').'</td><td>'.__('5.3 required').'</td><td>'
	.$php.'</td></tr>';
// }
// { PHP PDO
$ok=class_exists('PDO');
$msg=$ok?
	'OK':
	'<span class="error">'.__(
		'PDO not installed. In RPM-based systems, this is usually the php-pdo RPM'
	).'</span>';
echo '<tr><td>'.__('PDO library (for database)').'</td><td>&nbsp;</td><td>'
	.$msg.'</td></tr>';
if ($ok) {
	$msg=in_array('mysql', PDO::getAvailableDrivers())
		?'OK'
		:'<span class="error">'.__(
			'MySQL driver missing. In RPM-based systems, this is usually php-mysql'
		).'</span>';
	echo '<tr><td>'.__('PDO MySQL driver').'</td><td>&nbsp;</td><td>'
		.$msg.'</td></tr>';
}
// }
// { apache version
$apache=($ver[1]<'2')
	?'<span class="error">'.$apacheversion.'</span>'
	:'OK: '.$apacheversion;
echo '<tr><td>'.__('Apache Version').':</td>'
	.'<td>'.__('2 required').'</td><td>'.$apache.'</td></tr>';
// }
// { mod_rewrite
if (function_exists('apache_get_modules')) {
	$modules=apache_get_modules();
	$mods=(in_array('mod_rewrite', $modules))
		?'OK'
		:'<span id="notgranted" class="error">'.__('Not Installed').'</span>';
}
else {
	$mods=__('Unknown');
}
echo '<tr><td>'.__('Apache mod_rewrite:').'</td><td>&nbsp;</td><td>'
	.$mods.'</td></tr>';
// }

echo '</table><br><p>'.__(
	'Please correct anything noted above and reload this page to make sure'
	.' they have been solved. If they are solved, or you believe they will'
	.' not cause a problem, you can continue.'
)
	.'</p>'
	.'<a href="step1.php">'.__('Continue').'</a>';

require 'footer.php';
