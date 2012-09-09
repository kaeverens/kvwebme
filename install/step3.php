<?php
/**
	* installer step 3
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
// create an admin

if (!$_SESSION['db_vars']['db_installed']) { // user shouldn't be here
	header('Location: /install/step2.php');
	Core_quit();
}

$_SESSION['admin_created']=0;
if (isset($_REQUEST['action'])) {
	$ok=1;
	$_SESSION['user']=array(
		'email'    => $_REQUEST['email'],
		'password' => $_REQUEST['password'],
		'name'     => $_REQUEST['name']
	);
	if ($_REQUEST['password']!=$_REQUEST['password2']
		|| $_REQUEST['password']==''
	) {
		echo '<p>'.__('Passwords do not match or are empty.').'</p>';
		$ok=0;
	}
	if (@$_REQUEST['name']=='') {
		echo '<p>Name is empty.</p>';
		$ok=0;
	}
	if (!filter_var($_REQUEST['email'], FILTER_VALIDATE_EMAIL)) {
		echo '<p>'.__('Email not valid.').'</p>';
		$ok=0;
	}
	if ($ok) {
		mysql_connect(
			$_SESSION['db_vars']['hostname'],
			$_SESSION['db_vars']['username'],
			$_SESSION['db_vars']['password']
		);
		mysql_select_db($_SESSION['db_vars']['db_name']);
		mysql_query(
			'insert into user_accounts set id=1,'
			.'email="'.addslashes($_REQUEST['email']).'",'
			.'password="'.md5($_REQUEST['password']).'",'
			.'name="'.addslashes($_REQUEST['name']).'",active=1,'
			.'parent=0'
		);
		mysql_query("insert into groups (id,name) values(1,'administrators')");
		mysql_query("insert into users_groups values(1,1)");
		$_SESSION['admin_created']=1;
		echo '<script defer="defer">document.location="/install/step4.php";</script>';
		Core_quit();
	}
}
if (!isset($_SESSION['user'])) {
	$_SESSION['user']=array('email'=>'', 'password'=>'', 'name'=>'');
}

/**
 * add form validation
 */
echo '
<script defer="defer" type="text/javascript">
        $( function( ){
                var options = { "name" : { "required" : true }, "email" : { '
								.'"required" : true, "email" : true }, "password" : { "'
								.'required" : true, "match" : "password2" } };
                $( "#user-form" ).validate( options, error_handler );
        } );
</script>';

echo '<h3>'.__('User Account').'</h3>';
echo '<p id="errors"></p>';
echo '<form method="post" id="user-form"><table>';
echo '<tr><th>'.__('Name').'</th><td><input type="text" name="name" value="'
	.htmlspecialchars($_SESSION['user']['name']).'" /></td></tr>';
echo '<tr><th>'.__('Email').'</th><td><input type="text" name="email" value="'
	.htmlspecialchars($_SESSION['user']['email']).'" /></td><td>'
	.__(
		'You will log in with this. Please ensure it is correct. If you forget'
		.' your password, it can be sent to this address.'
	)
	.'</td></tr>';
echo '<tr><th>'.__('Password').'</th><td>'
	.'<input type="password" name="password" value="" /></td></tr>';
echo '<tr><th>'.__('Repeat Password').'</th><td>'
	.'<input type="password" name="password2" value="" /></td></tr>';
echo '</table><input name="action" type="submit" value="'
	.__('Create Admin').'" /></form>';

require 'footer.php';
