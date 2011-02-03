<?php
require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if(!is_admin())die('access denied');

if(!isset($_REQUEST['id']) || !is_numeric($_REQUEST['id']))exit;
if(!isset($_REQUEST['parent_id']) || !is_numeric($_REQUEST['parent_id']))exit;

include 'libs.php';

dbQuery('update products_categories set parent_id='.$_REQUEST['parent_id'].' where id='.$_REQUEST['id']);

$data=products_categories_get_data($_REQUEST['id']);

echo json_encode($data);
