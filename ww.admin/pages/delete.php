<?php
$id=(int)$_REQUEST['id'];
if (!$id) {
	exit;
}
require '../../ww.incs/basics.php';
require 'pages.funcs.php';
if (!is_admin()) {
	exit;
}
$no_echo_on_success=true;

$r=dbRow("SELECT COUNT(id) AS pagecount FROM pages");
if ($r['pagecount']<2) {
	$msgs.='<em>Cannot delete page - there must always be at '
		.'least one page.</em>';
}
else{
	$q=dbQuery('select parent from pages where id="'.$id.'"');
	if ($q->rowCount()) {
		$r=dbRow('select parent from pages where id="'.$id.'"');
		dbQuery('delete from page_vars where page_id="'.$id.'"');
		dbQuery('delete from pages where id="'.$id.'"');
		dbQuery(
			'update pages set parent="'.$r['parent'].'" where parent="'.$id.'"'
		);
		if (!isset($no_echo_on_success)) {
			$msgs.='<em>'.__('A page has been deleted.').'</em>';
		}
		cache_clear('menus');
		cache_clear('pages');
		dbQuery('update page_summaries set rss=""');
	}
	else {
		$msgs.='<em>That page does not exist.</em>';
	}
}
