<?php
/**
	* frontend/save_user_info.php, KV-Webme Privacy Plugin
	* saves user info
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Conor Mac Aoidh <conormacaoidh@gmail.com>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

require_once '../../../ww.incs/basics.php';

$id = (int)@$_SESSION[ 'userdata' ][ 'id' ];
$name = addslashes(@$_POST[ 'name' ]);
$phone = addslashes(@$_POST[ 'phone' ]);
$address = addslashes(@$_GET[ 'address' ]);
$action=@$_GET['action'];
if ($id == 0) {
	Core_quit();
}

$userdata=dbRow('select * from user_accounts where id='.$id);
if ($action=='delete') {
	$add=json_decode($userdata['address'], true);
	if (isset($add[$address])) {
		unset($add[$address]);
	}
	$add=addslashes(json_encode($add));
	dbQuery('update user_accounts set address="'.$add.'" where id='.$id);
	Core_quit();
}
if ($action=='update') {
	$address=json_decode($userdata['address'], true);
	$address[$name]=array(
		'street'=>$_POST['street'],
		'street2'=>$_POST['street2'],
		'town'=>$_POST['town'],
		'county'=>$_POST['county'],
		'country'=>$_POST['country'],
	);
	$address=addslashes(json_encode($address));
	dbQuery('update user_accounts set address="'.$address.'" where id='.$id);
	Core_quit();
}
if ($action=='default') {
	$name=@$_GET['name'];	
	$address=json_decode($userdata['address'], true);
	foreach ($address as $n=>$add) {
		$address[$n]['default']='no';
		if ($n==$name) {
			$address[$n]['default']='yes';
		}
	}
	$address=addslashes(json_encode($address));
	dbQuery('update user_accounts set address="'.$address.'" where id='.$id);
	Core_quit();
}

$c=json_decode($userdata['contact'], true);
$c['phone']=$phone;

dbQuery(
	'update user_accounts set '
	. 'name="' . $name . '",'
	. 'contact="'.addslashes(json_encode($c)).'",'
	. 'address="' . nl2br($address) . '" '
	. 'where id=' . $id
);
