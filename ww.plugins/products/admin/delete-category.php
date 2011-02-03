<?php
require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if(!is_admin())die('access denied');

if(!isset($_REQUEST['id']) || !is_numeric($_REQUEST['id']))exit;

include 'libs.php';

$parent=dbOne('select parent_id from products_categories where id='.$_REQUEST['id'],'parent_id');
dbQuery('update products_categories set parent_id='.$parent.' where parent='.$_REQUEST['id']);
dbQuery('delete from products_categories where id='.$_REQUEST['id']);

echo '{"status":1}';
