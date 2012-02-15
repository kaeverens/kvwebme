<?php
/**
	* User management
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

echo '<h2>Users</h2>';
echo '<a href="./siteoptions.php?page=users">List Users</a>'
	.' | <a href="./siteoptions.php?page=users&amp;tab=options">User Options</a>';
$groups=array();
if (@$_REQUEST['tab']=='options') {
	require_once 'siteoptions/users-options.php';
}
else if (@$_REQUEST['id']) {
	require_once 'siteoptions/users-edit.php';
}
else {
	require_once 'siteoptions/users-list.php';
}
