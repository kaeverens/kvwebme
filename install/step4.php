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
	Core_quit();
}

if (isset($_REQUEST['userbase']) && $_REQUEST['userbase']) {
	$_REQUEST['userbase']=str_replace('//', '/', $_REQUEST['userbase'].'/');
	$d=$_REQUEST['userbase'];
	$hd=htmlspecialchars($d);
	$_SESSION['userbase']=$_REQUEST['userbase'];
	if (!is_dir($d)) {
		echo '<em>'
			.__(
				'<strong><code>%1</code></strong> is not a directory or does not'
				.' exist.',
				array($hd),
				'core'
			)
			.'</em>';
	}
	else {
		if (!is_writable($d)) {
			echo '<em>'
				.__(
					'<strong><code>%1</code></strong> is not writable by the '
					.'web server.',
					array($hd),
					'core'
				)
				.'</em>';
		}
		else {
			$_SESSION['userbase_created']=true;
			echo '<script defer="defer">document.location="/install/step5.php";</script>';
			Core_quit();
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
<script defer="defer" type="text/javascript">
        $( document ).ready( function( ){
                var options = { "userbase" : { "required" : true } };
                $( "#files-form" ).validate( options, error_handler );
        } );
</script>';

echo '<h3>'.__('User Files').'</h3>';
echo '<p id="errors"></p>';
echo '<form method="post" id="files-form"><p>'
	.__(
		'Please type the address of the directory you want to use for your user'
		.' files.'
	)
	.'</p>
	<input name="userbase" value="'.htmlspecialchars($_SESSION['userbase'])
		.'" style="width:90%" /><br /><input type="submit" /></form>';
echo '<p>'
	.__(
		'It is a good idea to place this directory outside the web-accessible'
		.' part of the server.'
	)
	.'</p>';

require 'footer.php';
