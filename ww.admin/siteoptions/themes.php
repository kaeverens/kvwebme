<?php
/**
	* switches between theme operations
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

$action = @$_GET['action'];
switch ($action) {
	case 'install':
		require SCRIPTBASE . 'ww.admin/siteoptions/themes/install.php';
	break;
	case 'download':
		require SCRIPTBASE . 'ww.admin/siteoptions/themes/theme-download.php';
	break;
	default:
		require SCRIPTBASE . 'ww.admin/siteoptions/themes/personal.php';
}
