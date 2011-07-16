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
	exit;
}

if (!is_dir('../.private')) { // create config directory
	mkdir('../.private');
	if (!is_dir('../.private')) {
		echo '<p><strong>Couldn\'t create <code>'.$_SERVER['DOCUMENT_ROOT']
			.'/.private</code> directory.</strong> Please either:</p><ul><li>'
			.'make the web root <code>'.$_SERVER['DOCUMENT_ROOT'].'</code> '
			.'writable for the web server</li><li>or create the <code>.private'
			.'</code> directory yourself and make it writable to the web server'
			.'</li></ul><p>Then reload this page.</p>';
		exit;
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
	'version'  => 1,
	'maintenance-mode'=>'yes',
	'maintenance-mode-message'=>'<h1>Temporarily Unavailable</h1>
	<p>This website is undergoing maintenance and is temporarily unavailable.
	</p>
	<p>If you are an admin of this site, you can <a href=\"/ww.admin/\">log in
	here</a>.</p>',
);";

file_put_contents('../.private/config.php', $config);

if (!file_exists('../.private/config.php')) {
	echo '<p><strong>Could not create /.private/config.php</strong>. Please '
		.'make /.private/ writable for the web server, then reload this page.</p>';
	exit;
}

$_SESSION[ 'config_written' ] = true;

header('location: step6.php');
