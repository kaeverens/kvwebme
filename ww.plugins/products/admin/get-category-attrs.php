<?php
require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if(!Core_isAdmin())die('access denied');

if(!isset($_REQUEST['id']) || !is_numeric($_REQUEST['id']))exit;

include 'libs.php';

$data=products_categories_get_data($_REQUEST['id']);

echo json_encode($data);
