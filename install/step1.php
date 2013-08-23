<?php
/**
	* installer step 1
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

// get db variables
if (!isset($_SESSION['db_vars'])) { // set up dummy values
	$_SESSION['db_vars']=array(
		'username' => '',
		'password' => '',
		'hostname' => 'localhost',
		'db_name'  => '',
		'passed'   => 0
	);
}

if (isset($_REQUEST['action'])) {
	$_SESSION['db_vars']=array(
		'username' => $_REQUEST['username'],
		'password' => $_REQUEST['password'],
		'hostname' => $_REQUEST['hostname'],
		'db_name'  => $_REQUEST['db_name'],
		'passed'   => 0
	);

	$mysql = mysql_connect(
		$_SESSION['db_vars']['hostname'],
		$_SESSION['db_vars']['username'],
		$_SESSION['db_vars']['password']
	);

	if (!$mysql) {
		echo __('Connect failed:').' '.mysql_error();
		echo '<p>'.__('Please check your values and try again.').'</p>';
	}
	else {
		// if database doesn't exist, try create it
		if (!mysql_select_db($_SESSION['db_vars']['db_name'])) {
			mysql_query('create database `'.addslashes($_REQUEST['db_name']).'`');
		}
		// if it still doesn't exist, fail
		if (!mysql_select_db($_SESSION['db_vars']['db_name'])) {
			echo '<p>'.__('Please provide an existing database name.').'</p>';
		}
		else {
			$_SESSION['db_vars']['passed']=1;
			echo '<script defer="defer">document.location="/install/step2.php";</script>';
			Core_quit();
		}
	}
}

/**
 * add form validation
 */
echo '
<script defer="defer" type="text/javascript">
	$( function( ){
		var options = { "username" : { "required" : true }, "hostname" : { "req'
			.'uired" : true }, "db_name" : { "required" : true } };
		$( "#database-form" ).validate( options, error_handler );
	} );
</script>';

echo '<h3>'.__('Database Details').'</h3>';
echo '<p id="errors"></p>';
echo '<form method="post" id="database-form"><table>';
echo '<tr><th>'.__('Username').'</th>'
	.'<td><input type="text" name="username" value="'
	.htmlspecialchars($_SESSION['db_vars']['username']).'" /></td></tr>';
echo '<tr><th>'.__('Password').'</th>'
	.'<td><input type="text" name="password" value="'
	.htmlspecialchars($_SESSION['db_vars']['password']).'" /></td></tr>';
echo '<tr><th>'.__('HostName').'</th>'
	.'<td><input type="text" name="hostname" value="'
	.htmlspecialchars($_SESSION['db_vars']['hostname']).'" /></td></tr>';
echo '<tr><th>'.__('Database Name').'</th>'
	.'<td><input type="text" name="db_name" value="'
	.htmlspecialchars($_SESSION['db_vars']['db_name']).'" /></td></tr>';
echo '</table><input name="action" type="submit" value="'
	.__('Configure Database').'" /></form>';

require 'footer.php';
