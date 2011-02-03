<?php
require '../../ww.incs/basics.php';
if(!is_admin())exit;

$id=(int)$_REQUEST['id'];
echo dbOne('select body from pages where id='.$id,'body');
