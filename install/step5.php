<?php
require 'header.php';
// write the config to file

if(!$_SESSION['userbase_created']){ // user shouldn't be here
	header('Location: /install/step4.php');
	exit;
}

if(!is_dir('../.private')){ // create config directory
	mkdir('../.private');
	if(!is_dir('../.private')){
		echo '<p><strong>Couldn\'t create <code>'.$_SERVER['DOCUMENT_ROOT'].'/.private</code> directory.</strong> Please either:</p><ul><li>make the web root <code>'.$_SERVER['DOCUMENT_ROOT'].'</code> writable for the web server</li><li>or create the <code>.private</code> directory yourself and make it writable to the web server</li></ul><p>Then reload this page.</p>';
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
	'plugins'  => '',
	'theme_variant' => '',
	'version'  => 1
);";

file_put_contents('../.private/config.php',$config);

if(!file_exists('../.private/config.php')){
	echo '<p><strong>Could not create /.private/config.php</strong>. Please make /.private/ writable for the web server, then reload this page.</p>';
	exit;
}

echo '<p><strong>Success!</strong> Your WebME installation is complete. Please <a href="/">click here</a> to go to the root of the site.</p>';
unset($_SESSION['db_vars']);

require 'footer.php';
