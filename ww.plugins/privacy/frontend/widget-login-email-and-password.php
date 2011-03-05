<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
$no_redirect=1;
$_REQUEST['action']='login';
require_once $_SERVER['DOCUMENT_ROOT'].'/ww.incs/user-authentication.php';

if (isset($_SESSION['userdata']) && $_SESSION['userdata']['id']) {
	echo json_encode(array(
		'redirect'=>(isset($redirect_url)?$redirect_url:'')
	));
	exit;
}
echo json_encode(array(
	'error'=>'incorrect email or password'
));
