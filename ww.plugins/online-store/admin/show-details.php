<?php
/**
	* show values submitted during Online-Store checkout
	*
	* PHP version 5
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     None
	*/

require_once $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if (!Core_isAdmin()) {
	die(__('access denied'));
}

if (!isset($_REQUEST['id'])) {
	Core_quit();
}
$id=(int)$_REQUEST['id'];

$vals
	=dbOne(
		'select form_vals from online_store_orders where id='.$id, 
		'form_vals'
	);
/* TODO: Remove inline styling and replace with CSS in styleheet */
echo '<html><head><style type="text/css">div{display:inline-block;width:170px;'
	.'margin:5px}</style></head><body>';
$vals=json_decode($vals);
foreach ($vals as $k=>$v) {
/* TODO: Remove inline styling <strong> and replace with CSS in stylesheet */
	echo '<div>'.htmlspecialchars($k).'<br /><strong>'.htmlspecialchars($v)
		.'</strong></div>';
}
echo '</body></html>';
