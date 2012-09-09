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
	'name'=>function() {
		return __('SiteCredits');
	},
	'hide_from_admin'=>	true,
	'description' =>function() {
		return __(
			'admins can pay for credits, which are used to manage site subscriptions'
		);
	},
	'admin' => array(
		'menu' => array(
			'Credits' => 'plugin.php?_plugin=site-credits&amp;_page=overview'
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
/**
	* __('Credits')
	*/
// }

function SiteCredits_isActive() {
	global $DBVARS;
	if (!isset($DBVARS['sitecredits-credits'])) {
		$DBVARS['sitecredits-credits']=0;
		Core_configRewrite();
	}
	if ($DBVARS['sitecredits-credits']<-1) {
		echo '<p>'.__(
			'Website Administrator attention needed.'
			.' Please log into your administration area (and check your email).'
		)
			.'</p>';
		Core_quit();
	}
}
function SiteCredits_adminShowStatus() {
	WW_addScript('/ww.plugins/site-credits/admin-status.js');
}
