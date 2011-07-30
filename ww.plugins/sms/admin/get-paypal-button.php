<?php
require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if(!Core_isAdmin())die('access denied');
require SCRIPTBASE.'ww.plugins/sms/admin/libs.php';

$amt=(int)$_REQUEST['amt'];
if($amt<200)exit;

$return=urlencode('http://'.$_SERVER['HTTP_HOST'].'/ww.admin/plugin.php?_plugin=sms&_page=dashboard');
$ret=SMS_callApi('order-credits','&credits='.$amt.'&return='.$return);
echo json_encode($ret);
