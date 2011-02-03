<?php
require 'header.php';
// get the user directory

if(!$_SESSION['admin_created']){ // user shouldn't be here
	header('Location: /install/step3.php');
	exit;
}

if(isset($_REQUEST['userbase']) && $_REQUEST['userbase']){
	$_REQUEST['userbase']=str_replace('//','/',$_REQUEST['userbase'].'/');
	$d=$_REQUEST['userbase'];
	$hd=htmlspecialchars($d);
	$_SESSION['userbase']=$_REQUEST['userbase'];
	if(!is_dir($d))echo "<em><strong><code>$hd</code></strong> is not a directory or does not exist.</em>";
	else{
		if(!is_writable($d))echo "<em><strong><code>$hd</code></strong> is not writable by the web server.</em>";
		else{
			$_SESSION['userbase_created']=true;
			header('Location: /install/step5.php');
		}
	}
}

if(!isset($_SESSION['userbase']) || !$_SESSION['userbase'])$_SESSION['userbase']=$_SERVER['DOCUMENT_ROOT'];
echo '<form method="post"><p>Please type the address of the directory you want to use for your user files.</p>
	<input name="userbase" value="'.htmlspecialchars($_SESSION['userbase']).'" style="width:90%" /><br /><input type="submit" /></form>';
echo '<p>It is a good idea to place this directory outside the web-accessible part of the server.</p>';

require 'footer.php';
