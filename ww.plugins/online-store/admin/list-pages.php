<?php
/**
	* if only one online store is found, redirects to that page
	*
	* PHP version 5
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     None
	*/

require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if (!is_admin()) {
	die('access denied');
}

$id=dbOne('select id from pages where type="online-store" limit 1', 'id');
redirect('/ww.admin/pages.php?id='.$id);
