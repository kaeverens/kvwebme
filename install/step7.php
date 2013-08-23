<?php
/**
	* installer step 7
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

require 'header.php';

if (!$_SESSION['theme_selected']&&@$_GET['theme']!='skipped') {
	// user shouldn't be here
  header('Location: /install/step6.php');
	Core_quit();
}

$cmsname=DistConfig::get('cms-name');
echo '<p><strong>'.__('Success!')
	.'</strong> '
	.__('Your %1 installation is complete.', array($cmsname), 'core')
	.' '
	.__('Please <a href="/">click here</a> to go to the root of the site.')
	.'</p>';
unset($_SESSION['db_vars']);

require 'footer.php';
