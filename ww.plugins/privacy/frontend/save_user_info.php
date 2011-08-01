<?php

/**
 * frontend/save_user_info.php, KV-Webme Privacy Plugin
 *
 * saves user info
 *
 * @author     Conor Mac Aoidh <conormacaoidh@gmail.com>
 * @license    GPL 2.0
 * @version    1.0
 */


require '../../../ww.incs/basics.php';

$id = addslashes(@$_SESSION[ 'userdata' ][ 'id' ]);
$name = addslashes(@$_POST[ 'name' ]);
$phone = addslashes(@$_POST[ 'phone' ]);
$address = addslashes(@$_GET[ 'address' ]);
$action=@$_GET['action'];
if ($id == 0) {
	exit;
}

if ($action=='delete') {
	$_user=dbRow('select address from user_accounts where id='.$id);
	$add=json_decode($_user['address'], true);
	if (isset($add[$address])) {
		unset($add[$address]);
	}
	$add=addslashes(json_encode($add));
	dbQuery('update user_accounts set address="'.$add.'" where id='.$id);
	exit;
}
if ($action=='update') {
	$user=dbRow('select address from user_accounts where id='.$id);
	$address=json_decode($user['address'],true);
	$address[$name]=array(
		'street'=>$_POST['street'],
		'street2'=>$_POST['street2'],
		'town'=>$_POST['town'],
		'county'=>$_POST['county'],
		'country'=>$_POST['country'],
	);
	$address=addslashes(json_encode($address));
	dbQuery('update user_accounts set address="'.$address.'" where id='.$id);
	exit;
}
if ($action=='default') {
	$name=@$_GET['name'];	
	$user=dbRow('select address from user_accounts where id='.$id);
	$address=json_decode($user['address'], true);
	foreach ($address as $n=>$add) {
		$address[$n]['default']='no';
		if ($n==$name) {
			$address[$n]['default']='yes';
		}
	}
	$address=addslashes(json_encode($address));
	dbQuery('update user_accounts set address="'.$address.'" where id='.$id);
	exit;
}

dbQuery(
	'update user_accounts set '
	. 'name="' . $name . '",'
	. 'phone="' . $phone . '",'
	. 'address="' . nl2br($address) . '" '
	. 'where id=' . $id
);
