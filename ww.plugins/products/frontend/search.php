<?php
/**
	* find products by term
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';

if (!isset($_REQUEST['term']) || $_REQUEST['term']=='') {
	echo '[]';
	Core_quit();
}
$term=$_REQUEST['term'];
$rs=dbAll(
	'select id,name from products where name like "%'.addslashes($term)
	.'%" or data_fields like "%'.addslashes($term).'%" limit 20'
);

$res=array();
foreach ($rs as $r) {
	$res[]=array(
		'id'=>$r['id'],
		'label'=>$r['name'],
		'value'=>$r['name']
	);
}
echo json_encode($res);
