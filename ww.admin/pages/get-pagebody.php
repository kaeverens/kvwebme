<?php
require '../../ww.incs/basics.php';
if (!Core_isAdmin()) {
	exit;
}

$id=(int)$_REQUEST['id'];
echo dbOne('select body from pages where id='.$id,'body');
