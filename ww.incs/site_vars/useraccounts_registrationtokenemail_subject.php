<?php
/**
	* useraccounts_registrationtokenemail_subject site_var default
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

$value='['.preg_replace('/^www\./', '', $_SERVER['HTTP_HOST']).']'
	.' user registration token';
