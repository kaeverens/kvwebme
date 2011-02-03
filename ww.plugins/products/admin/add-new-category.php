<?php
require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if(!is_admin())die('access denied');

if(!isset($_REQUEST['parent_id']) || !is_numeric($_REQUEST['parent_id']))exit;
if(!isset($_REQUEST['name']) || $_REQUEST['name']=='')exit;

include 'libs.php';

dbQuery('insert into products_categories set name="'.addslashes($_REQUEST['name']).'",enabled=1,parent_id='.$_REQUEST['parent_id']);
$id=dbOne('select last_insert_id() as id','id');

$data=products_categories_get_data($id);

echo json_encode($data);
