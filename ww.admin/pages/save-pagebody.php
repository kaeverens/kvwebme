<?php
require '../../ww.incs/basics.php';
if(!is_admin())exit;
require '../admin_libs.php';

$id=(int)$_REQUEST['id'];
$body=addslashes($_REQUEST['body']);
$body=sanitise_html($body);
dbQuery("update pages set body='$body' where id=$id");
cache_clear('pages');
dbQuery('update page_summaries set rss=""');
echo 'ok';
