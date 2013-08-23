<?php
/**
	* installer step 5
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

require 'header.php';
// write the config to file

if (!$_SESSION['userbase_created']) { // user shouldn't be here
	header('Location: /install/step4.php');
	Core_quit();
}
$privatedir=DistConfig::get('installer-private');
if (!is_dir($privatedir)) { // create config directory
	mkdir($privatedir);
	if (!is_dir($privatedir)) {
		echo '<p><strong>'
			.__(
				'Could not create <code>%1</code> directory.',
				array($privatedir),
				'code'
			)
			.'</strong></p>';
		$webroot=dirname($privatedir);
		echo __(
			'<p>Please either:</p><ul><li>make the web root <code>%1</code> '
			.'writable for the web server</li><li>or create the <code>.private'
			.'</code> directory yourself and make it writable to the web server'
			.'</li></ul><p>Then reload this page.</p>',
			array($webroot),
			'core'
		);
		Core_quit();
	}
}

$config='<'."?php
\$DBVARS=array(
	'username' => '".addslashes($_SESSION['db_vars']['username'])."',
	'password' => '".addslashes($_SESSION['db_vars']['password'])."',
	'hostname' => '".addslashes($_SESSION['db_vars']['hostname'])."',
	'db_name'  => '".addslashes($_SESSION['db_vars']['db_name'])."',
	'userbase' => '".addslashes($_SESSION['userbase'])."',
	'plugins'  => 'panels',
	'theme_variant' => '',
	'version'  => 1
);";

file_put_contents($privatedir.'/config.php', $config);

if (!file_exists($privatedir.'/config.php')) {
	echo '<p>'
		.__(
			'<strong>Could not create /.private/config.php</strong>. Please '
			.'make /.private/ writable for the web server, then reload this page.'
		)
		.'</p>';
	Core_quit();
}

$_SESSION[ 'config_written' ] = true;

echo '<script defer="defer">document.location="/install/step6.php";</script>';
