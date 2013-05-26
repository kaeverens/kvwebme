<?php
function Menu_getHtml() {
	require_once SCRIPTBASE.'ww.incs/menus.php';
	require_once SCRIPTBASE.'ww.incs/common.php';
	$vars=null;
	if (isset($_REQUEST['vars'])) {
		$vars=json_decode($_REQUEST['vars']);
	}
	if ($vars && isset($vars->id) && $vars->id) {
		$id=$vars->id;
		$vars=Core_cacheLoad('menus', $id, -1);
		if ($vars===-1) {
			$vars=dbRow('select * from menus where id='.$id);
			Core_cacheSave('menus', $id, $vars);
		}
		if ($vars['cache']) {
			header('Cache-Control: max-age='.$vars['cache'].', public');
			header('Expires: Fri, 1 Jan 2500 01:01:01 GMT');
			header('Expires-Active: On');
			header('Pragma:');
			header('Last-modified: '.gmdate('D, d M Y H:i:s', time()));
		}
		if ($vars['parent']=='-1') {
			global $PAGEDATA;
			$pid=$PAGEDATA->id;
			if ($pid) {
				$n=dbOne('select id from pages where parent='.$pid.' limit 1', id);
				if (!$n) {
					$pid=(int)$PAGEDATA->parent;
					if (!$pid) {
						return '';
					}
				}
			}
			$vars['parent']=$pid;
		}
	}
	header('Content-type: text/javascript');
	echo 'document.write("'.addslashes(Core_menuShowFg($vars)).'");';
	echo join(';', $GLOBALS['scripts_inline']);
	exit;
}
