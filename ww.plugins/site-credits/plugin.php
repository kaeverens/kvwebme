<?php
/**
	* definition file for SiteCredits plugin
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
	'name' => 'SiteCredits',
	'hide_from_admin'=>	true,
	'description' => 'admins can pay for credits, which are used to manage site subscriptions',
	'admin' => array(
		'menu' => array(
			'Credits' => 'overview'
		)
	),
	'frontend' => array(
	),
	'triggers'      => array(
		'page-object-loaded'=>'SiteCredits_isActive',
		'admin-scripts'=>'SiteCredits_adminShowStatus'
	),
	'version'=>3
);
// }

function SiteCredits_isActive() {
	global $DBVARS;
	if (@$DBVARS['sitecredits-credits']<0) {
		echo '<p>Website Administrator attention needed.'
			.' Please log into your administration area (and check your email).</p>';
		exit;
	}
}
function SiteCredits_adminShowStatus() {
	return '<script src="/ww.plugins/site-credits/admin-status.js"></script>';
}
