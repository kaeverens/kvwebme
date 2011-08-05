<?php
/**
	* show a list of pages and their sub-pages, including a blank option
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

require_once '../../ww.incs/basics.php';
require_once 'pages.funcs.php';
$selected=isset($_REQUEST['selected'])?$_REQUEST['selected']:0;
$id=isset($_REQUEST['other_GET_params'])?(int)$_REQUEST['other_GET_params']:-1;
echo '<option value="0">--  none  --</option>';
selectkiddies(0, 0, $selected, $id);
