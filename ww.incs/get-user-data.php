<?php

/**
 * ww.incs/get-user-data.php, KV-Webme get user data
 *
 * prints a json string of user data when given an id
 *
 * @author     Conor Mac Aoidh <conormacaoidh@gmail.com>
 * @license    GPL 2.0
 * @version    3.0
 */

require 'basics.php';

if(!isset($_SESSION['userdata'])) // not logged in
	exit;

$id=(int)@$_GET['id'];
if($id=='')
	exit;

$user=dbRow('select id,name,email,phone,address,parent,extras,last_login,last_view,date_created from user_accounts where id='.addslashes($id).' limit 1');
if($user==false)
	exit;

$user['address']=json_decode($user['address'],true);
$user['extras']=json_decode($user['extras'],true);

$groups=dbAll('select groups_id from users_groups where user_accounts_id='.$id.' limit 1');

$g=array();
foreach($groups as $group)
	array_push($g,$group['groups_id']);

$user['groups']=$g;

die(json_encode($user));
?>
