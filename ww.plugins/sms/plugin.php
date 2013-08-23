<?php
/**
	* definition file for SMS plugin
	*
	* PHP version 5
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     None
	*/

// { define $plugin
$plugin=array(
	'name' => 'SMS',
	'admin' => array(
		'menu' => array(
			'Communication>SMS'=> 'plugin.php?_plugin=sms&amp;_page=dashboard'
		),
		'widget' => array(
			'form_url' => '/ww.plugins/sms/admin/widget-form.php'
		)
	),
	'frontend' => array(
		'widget' => 'SMS_showWidget'
	),
	'description' => 'Add SMS capabilities to your site, using the textr.mobi '
		.'service.',
	'version' => 1
);
// }

/**
	* show a widget
	*
	* @param array $vars widget parameters
	*
	* @return string $html
	*/
function SMS_showWidget($vars) {
	require_once SCRIPTBASE.'ww.plugins/sms/frontend/widget.php';
	return $html;
}

/**
	* get the ID of a subscriber (adding if necessary)
	*
	* @param string $phone phone number of the subscriber
	* @param string $name  name of the subscriber
	*
	* @return int $sid subscriber ID
	*/
function SMS_getSubscriberId($phone, $name='') {
	$phone=SMS_sanitisePhoneNumber($phone);
	if ($phone===false) {
		return false;
	}
	$sid=dbOne(
		'select id from sms_subscribers where phone="'.$phone.'" limit 1',
		'id'
	);
	if (!$sid) {
		dbQuery(
			'insert into sms_subscribers (name,phone,date_created) values("'
			.addslashes($name).'","'.$phone.'",now())'
		);
		$sid=dbOne(
			'select id from sms_subscribers where phone="'.$phone.'" limit 1',
			'id'
		);
	}
	return (int)$sid;
}

/**
	* clean up a phone number
	*
	* @param string $phone phone number to clean up
	*
	* @return string $phone
	*/
function SMS_sanitisePhoneNumber($phone) {
	$phone=preg_replace('/[^0-9]/', '', $phone);
	if ($phone=='') {
		return false;
	}
	if (strpos($phone, '0')===0) {
		if (strpos($phone, '08')===0) {
			$phone=preg_replace('/^0/', '353', $phone);
		}
		elseif (strpos($phone, '07')===0) {
			$phone=preg_replace('/^0/', '44', $phone);
		}
		else {
			return false;
		}
	}
	return $phone;
}

/**
	* add a subscriber to an addressbook
	*
	* @param int $sid subscriber ID
	* @param int $aid addressbook ID
	*
	* @return null
	*/
function SMS_subscribeToAddressbook($sid, $aid) {
	$subscribers=json_decode(
		dbOne(
			'select subscribers from sms_addressbooks where id='.$aid,
			'subscribers'
		)
	);
	if (in_array($sid, $subscribers)) {
		return;
	}
	$subscribers[]=$sid;
	dbQuery(
		'update sms_addressbooks set subscribers="'
		.addslashes(json_encode($subscribers))
		.'" where id='.$aid
	);
}
