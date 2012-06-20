<?php
/**
	* displays themes from the theme server
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Conor Mac Aoidh <conormacaoidh@gmail.com>
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

require_once '../../../ww.incs/basics.php';
require_once SCRIPTBASE . 'ww.admin/admin_libs.php';

/**
	* get a list of the available themes
	*
	* @return array names
	*/
function Theme_getTemplateNames() {
	$themes = array();
	$dir = USERBASE.'/themes-personal/';
	$handler = opendir($dir);
	while ($file = readdir($handler)) {
		if ($file == '.' || $file == '..') {
			continue;
		}
		if (is_dir($dir . $file)) {
			array_push($themes, $file);
		}
	}
	closedir($handler);
	return $themes;
}

echo '<script src="/ww.admin/siteoptions/themes/download.js"></script>'
	.'<link rel="stylesheet" href="'.DistConfig::get('themes-api').'/api.'
	.'css"/>';

echo '<div id="public-repository"><p>'.__(
	'Choosing a theme here will download it into your private repository.'
	.' If you already have a copy of the chosen theme there, then your copy'
	.' will be over-written.'
)
	.'</p>';

echo '<div id="themes-carousel">
</div>';

/**
 * build an array of installed themes
 */
$installed = Theme_getTemplateNames();
echo '<script type="text/javascript">window.installed_themes=' . 
	json_encode($installed) . ';</script>';

echo '<br style="clear:both"/>';
