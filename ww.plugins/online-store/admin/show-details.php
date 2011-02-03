<?php
/**
	* show values submitted during Online-Store checkout
	*
	* PHP version 5
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@webworks.ie>
	* @license  GPL 2.0
	* @link     None
	*/

require_once $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if (!is_admin()) {
	die('access denied');
}

if (!isset($_REQUEST['id'])) {
	exit;
}
$id=(int)$_REQUEST['id'];

$vals
	=dbOne(
		'select form_vals from online_store_orders where id='.$id, 
		'form_vals'
	);
echo '<html><head><style type="text/css">div{display:inline-block;width:170px;'
	.'margin:5px}</style></head><body>';
$vals=json_decode($vals);
foreach ($vals as $k=>$v) {
	echo '<div>'.htmlspecialchars($k).'<br /><strong>'.htmlspecialchars($v)
		.'</strong></div>';
}
echo '</body></html>';
