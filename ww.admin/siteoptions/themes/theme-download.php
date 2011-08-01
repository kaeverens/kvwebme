<?php

/**
 * ww.admin/siteoptions/themes/theme-download.php, KV-Webme
 *
 * downloads a theme from the server and can
 * install themes as well
 *
 * @author     Conor Mac Aoidh <conormacaoidh@gmail.com>
 * @license    GPL 2.0
 * @version    1.0
 */

/**
 * make sure post is set
 */
if (!isset($_POST[ 'install-theme' ]) && !isset($_POST[ 'download-theme' ])) {
	exit;
}

/**
 * get id
 */
$id = (int) @$_POST[ 'theme_id' ];
if ($id == 0) {
	exit;
}

/**
 * get theme from api
 */
$theme=Core_getExternalFile(
	'http://kvweb.me/ww.plugins/themes-api/api.php?theme=' . $id
);

if ($theme == false) {
	die('theme does not exist');
}

$theme = json_decode($theme, true);

echo '<h2>Downloading Theme</h2>';

/**
 * downloading
 */
echo 'Downloading...<br/>';
$zipfile = Core_getExternalFile($theme[ 'download' ]);
$theme_dir = USERBASE . 'themes-personal/';
file_put_contents($theme_dir . $theme[ 'name' ] . '.zip', $zipfile);

/**
 * extracting
 */
echo 'Extracting...<br/>';
shell_exec('cd ' . $theme_dir . ' && unzip -o ' .  $theme[ 'name' ] . '.zip');

/**
 * cleaning
 */
echo 'Removing Zip File..<br/>';
shell_exec('rm -rf ' . $theme_dir . $theme[ 'name' ] . '.zip');

echo 'Theme Download Successful<br/>';

/**
 * install theme if selected
 */
if (isset($_POST[ 'install-theme' ])) {

	$DBVARS['theme'] = $theme[ 'name' ];

	$variant = @$_POST[ 'theme_variant' ];
	if ($variant != '') {
		$DBVARS['theme_variant'] = $variant;
	}

	Core_configRewrite();
	Core_cacheClear('pages');

}

/**
 * redirect to themes personal
 */
header('location: /ww.admin/siteoptions.php?page=themes');
