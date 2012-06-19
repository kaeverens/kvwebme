<?php
/**
	* get a list of vouchers
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

require_once $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';

if (!Core_isAdmin()) {
	die(__('access denied'));
}

// { count results
$sql='select count(id) as ids from online_store_vouchers';
$num_results=(int)dbOne($sql, 'ids');
// }
// { select results
$from=(int)$_REQUEST['iDisplayStart'];
$how_many=(int)$_REQUEST['iDisplayLength'];
$sql='select id,name,value,value_type,start_date,end_date '
	.'from online_store_vouchers order by end_date desc,name'
	.' limit '.$from.','.$how_many;
$rs=dbAll($sql);
// }
$results=array();
foreach ($rs as $r) {
	$results[]=array(
		'<a href="/ww.admin/plugin.php?_plugin=online-store&_page=vouchers&'
			.'voucher_id='.$r['id'].'">'.htmlspecialchars($r['name']).'</a>',
		$r['value'].($r['value_type']=='percentage'?'%':' cash'),
		$r['end_date'].' (starts '.$r['start_date'].')'
	);
}

$return=array(
	'sEcho'                => (int)$_REQUEST['sEcho'],
	'iTotalRecords'        => $num_results,
	'iTotalDisplayRecords' => $num_results,
	'aaData'               => $results
);
echo json_encode($return);
