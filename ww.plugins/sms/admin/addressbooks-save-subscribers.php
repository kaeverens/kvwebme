<?php
/**
	* addressbook something
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if (!Core_isAdmin()) {
	die('access denied');
}

$id=(int)$_REQUEST['aid'];
$subs=preg_replace('/[^0-9,]/', '', $_REQUEST['subscribers']);
dbQuery('update sms_addressbooks set subscribers="['.$subs.']" where id='.$id);

echo '{"ok":1}';
