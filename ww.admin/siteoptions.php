<?php
/**
	* front controller for editing site options
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

require_once 'header.php';
echo '<h1>'.__('Site Options').'</h1>';

/**
	* verify that the requested page is an allowed one
	*
	* @param array  $validlist list of allowed pages
	* @param string $default   the default to return of no other valid one
	* @param string $val       page to check
	*
	* @return string valid page
	*/
function Core_verifyAdminPage($validlist, $default, $val) {
	foreach ($validlist as $v) {
		if ($v==$val) {
			return $val;
		}
	}
	return $default;
}
echo Core_adminSideMenu(
	array(
		__('General')=>'siteoptions.php?page=general',
		__('Users')=>'siteoptions.php?page=users',
		__('Themes')=>'siteoptions.php?page=themes',
		__('Plugins')=>'siteoptions.php?page=plugins'
	)
);

$page=Core_verifyAdminPage(
	array('general', 'users', 'themes', 'plugins'),
	'general',
	isset($_REQUEST['page'])?$_REQUEST['page']:''
);

echo '<div class="pages_iframe">';
require 'siteoptions/'.$page.'.php';
echo '</div>';
require 'footer.php';
