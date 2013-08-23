<?php
/**
	* admin menu
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/
echo Core_adminSideMenu(
	array(
		'Dashboard'=>'/ww.admin/plugin.php?_plugin=sms&amp;_page=dashboard',
		'Send Message'=>'/ww.admin/plugin.php?_plugin=sms&amp;_page=send-message',
		'Addressbooks'=>'/ww.admin/plugin.php?_plugin=sms&amp;_page=addressbooks',
		'Subscribers'=>'/ww.admin/plugin.php?_plugin=sms&amp;_page=subscribers'
	),
	$_url
);
