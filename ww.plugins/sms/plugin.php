<?php
/**
	* definition file for SMS plugin
	*
	* PHP version 5
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@webworks.ie>
	* @license  GPL 2.0
	* @link     None
	*/

// { define $plugin
$plugin=array(
	'name' => 'SMS',
	'admin' => array(
		'menu' => array(
			'Communication>SMS'=> 'dashboard'
		),
		'widget' => array(
			'form_url' => '/ww.plugins/sms/admin/widget-form.php'
		)
	),
	'frontend' => array(
		'widget' => 'sms_showWidget'
	),
	'description' => 'Add SMS capabilities to your site, using the textr.mobi service.',
	'version' => 1
);
// }

function sms_showWidget($vars){
	require_once SCRIPTBASE.'ww.plugins/sms/frontend/widget.php';
	return $html;
}
function sms_getSubscriberId($phone,$name=''){
	$phone=sms_sanitisePhoneNumber($phone);
	if($phone===false)return false;
	$sid=dbOne('select id from sms_subscribers where phone="'.$phone.'" limit 1','id');
	if(!$sid){
		dbQuery('insert into sms_subscribers (name,phone,date_created) values("'.addslashes($name).'","'.$phone.'",now())');
		$sid=dbOne('select id from sms_subscribers where phone="'.$phone.'" limit 1','id');
	}
	return (int)$sid;
}
function sms_sanitisePhoneNumber($phone){
	$phone=preg_replace('/[^0-9]/','',$phone);
	if($phone=='')return false;
	if(strpos($phone,'0')===0){
		if(strpos($phone,'08')===0)$phone=preg_replace('/^0/','353',$phone);
		else if(strpos($phone,'07')===0)$phone=preg_replace('/^0/','44',$phone);
		else return false;
	}
	return $phone;
}
function sms_subscribeToAddressbook($sid,$aid){
	$subscribers=json_decode(dbOne('select subscribers from sms_addressbooks where id='.$aid,'subscribers'));
	if(in_array($sid,$subscribers)){
		return;
	}
	$subscribers[]=$sid;
	dbQuery('update sms_addressbooks set subscribers="'.addslashes(json_encode($subscribers)).'" where id='.$aid);
}
