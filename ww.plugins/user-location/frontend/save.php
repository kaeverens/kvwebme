<?php
if (!isset($_REQUEST['lng']) || !isset($_REQUEST['lat'])) {
	exit;
}
require_once $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if (!isset($_SESSION['userdata'])) {
	exit;
}

$lat=(double)$_REQUEST['lat'];
$lng=(double)$_REQUEST['lng'];

dbQuery(
	'update user_accounts set longitude='.$lng.', latitude='.$lat
	.' where id='.$_SESSION['userdata']['id']
);

$_SESSION['userdata']['latitude']=$lat;
$_SESSION['userdata']['longitude']=$lng;

header('Location: /');
