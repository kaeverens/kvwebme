<?php
require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if(!is_admin())die('access denied');

if(!isset($_REQUEST['id']) || !is_numeric($_REQUEST['id']))exit;

include 'libs.php';

$data=products_categories_get_data($_REQUEST['id']);

echo json_encode($data);
