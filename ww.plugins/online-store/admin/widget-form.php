<?php
/**
	* shopping basket widget admin
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
if (!Core_isAdmin()) {
	die('access denied');
}

// { template
$template=isset($_REQUEST['template'])?$_REQUEST['template']:'';
echo '<strong>Template (leave blank to use a default one)</strong><br />'
	.'<textarea class="small" name="template">'.htmlspecialchars($template)
	.'</textarea>';
// }
