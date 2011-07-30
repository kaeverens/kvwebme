<?php
require '../../ww.incs/basics.php';
if (!Core_isAdmin()) {
	exit;
}
require '../admin_libs.php';

$id=(int)$_REQUEST['id'];
$body=addslashes($_REQUEST['body']);
$body=Core_sanitiseHtml($body);
dbQuery("update pages set body='$body' where id=$id");
Core_cacheClear('pages');
dbQuery('update page_summaries set rss=""');
echo 'ok';
