<?php
/**
	* installs or deletes a local theme
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

/**
 * make sure post is set
 */
if (!isset($_POST[ 'install-theme' ]) && !isset($_POST[ 'delete-theme' ])) {
	Core_quit();
}

/**
 * get name
 */
$name = @$_POST[ 'theme_name' ];
if ($name == '') {
	Core_quit();
}

/**
 * install theme if selected
 */
if (isset($_POST[ 'install-theme' ])) {

	$DBVARS['theme'] = $name;

	$variant = @$_POST[ 'theme_variant' ];
	if ($variant != '') {
		$DBVARS['theme_variant'] = $variant;
	}

	Core_configRewrite();
	Core_cacheClear('pages');

}


/**
 * delete theme if selected
 */
if (isset($_POST[ 'delete-theme' ])) {
	if ($DBVARS[ 'theme' ] == $name) {
		header('location: /ww.admin/siteoptions.php?page=themes');
	}
	elseif (is_dir(USERBASE.'/themes-personal/' . $name)) {
		CoreDirectory::delete(USERBASE.'/themes-personal/'.$name);
	}
}

/**
 * redirect to themes personal
 */
echo '<script>document.location="/ww.admin/siteoptions.php?page=themes";</script>';
Core_quit();
