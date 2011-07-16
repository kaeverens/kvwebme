<?php
/**
	* installer step 4
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
// get the user directory

if (!$_SESSION['admin_created']) { // user shouldn't be here
	header('Location: /install/step3.php');
	exit;
}

if (isset($_REQUEST['userbase']) && $_REQUEST['userbase']) {
	$_REQUEST['userbase']=str_replace('//', '/', $_REQUEST['userbase'].'/');
	$d=$_REQUEST['userbase'];
	$hd=htmlspecialchars($d);
	$_SESSION['userbase']=$_REQUEST['userbase'];
	if (!is_dir($d)) {
		echo "<em><strong><code>$hd</code></strong> is not a directory or does '
			.'not exist.</em>";
	}
	else {
		if (!is_writable($d)) {
			echo "<em><strong><code>$hd</code></strong> is not writable by the '
				.'web server.</em>";
		}
		else {
			$_SESSION['userbase_created']=true;
			header('Location: /install/step5.php');
		}
	}
}

if (!isset($_SESSION['userbase']) || !$_SESSION['userbase']) {
	$_SESSION['userbase']=$_SERVER['DOCUMENT_ROOT'];
}

/**
 * add form validation
 */
echo '
<script type="text/javascript">
        $( document ).ready( function( ){
                var options = { "userbase" : { "required" : true } };
                $( "#files-form" ).validate( options, error_handler );
        } );
</script>';

echo '<h3>User Files</h3>';
echo '<p id="errors"></p>';
echo '<form method="post" id="files-form"><p>Please type the address of the'
	.' directory you want to use for your user files.</p>
	<input name="userbase" value="'.htmlspecialchars($_SESSION['userbase'])
		.'" style="width:90%" /><br /><input type="submit" /></form>';
echo '<p>It is a good idea to place this directory outside the web-accessib'
	.'le part of the server.</p>';

require 'footer.php';
