<?php
/**
	* functions for showing a menu
	*
	* PHP version 5
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@webworks.ie>
	* @license  GPL 2.0
	* @link     None
	*/

require_once '../ww.incs/basics.php';
require_once '../ww.incs/menus.php';
require_once '../ww.incs/kaejax.php';

/**
	* retrieve a list of pages
	*
	* @param int  $parentid       the page which the returned pages are contained in
	* @param int  $currentpage    the id of the page the viewer's currently reading
	* @param int  $topParent      the top page for this menu
	* @param text $search_options filters to search under
	*
	* @return text
	*/
function AjaxMenu_getChildren(
	$parentid,
	$currentpage=0,
	$topParent=0,
	$search_options=0
) {
	return array($parentid, Menu_getChildren(
		$parentid,
		$currentpage,
		0,
		$topParent,
		$search_options
	));
}
kaejax_export('AjaxMenu_getChildren');
kaejax_handle_client_request();
kaejax_show_javascript();

$search_options=isset($_REQUEST['search_options'])
	?$_REQUEST['search_options']
	:0;
if (!isset($_GET['pageid'])) {
	exit;
}
$md5=md5($_GET['pageid'].'|'.$search_options);
$cache=cache_load('menus', $md5);

ob_start();
if ($cache) {
	echo $cache;
}
else {
	$d='var menu_cache=['.json_encode(
		AjaxMenu_getChildren(0, $_GET['pageid'], 0, $search_options)
	).'];';
	$p=Page::getInstance($_GET['pageid']);
	if (is_object($p)) {
		$pid=$p->getTopParentId();
		$d.='var currentTop='.$pid.';';
	}
	cache_save('menus', $md5, $d);
	echo $d;
}
echo file_get_contents('menu.js');
ob_show_and_log('menu');
