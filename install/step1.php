<?php
require 'header.php';
// get db variables

if(!isset($_SESSION['db_vars'])){ // set up dummy values
	$_SESSION['db_vars']=array(
		'username' => '',
		'password' => '',
		'hostname' => 'localhost',
		'db_name'  => '',
		'passed'   => 0
	);
}

if(isset($_REQUEST['action'])){
	$_SESSION['db_vars']=array(
		'username' => $_REQUEST['username'],
		'password' => $_REQUEST['password'],
		'hostname' => $_REQUEST['hostname'],
		'db_name'  => $_REQUEST['db_name'],
		'passed'   => 0
	);
	$mysql = mysql_connect($_SESSION['db_vars']['hostname'], $_SESSION['db_vars']['username'], $_SESSION['db_vars']['password']);
	if(!$mysql){
		printf("Connect failed: %s\n", mysql_error());
		echo '<p>Please check your values and try again.</p>';
	}
	else{
		if(!mysql_select_db($_SESSION['db_vars']['db_name'])){
			echo '<p>Please provide an existing database name.</p>';
		}
		else{
			$_SESSION['db_vars']['passed']=1;
			echo '<script type="text/javascript">document.location="/install/step2.php";</script>';
			echo '<p>Thank you. Please <a href="step2.php">click here to proceed</a>.</p>';
			exit;
		}
	}
}

echo '<form method="post"><table>';
echo '<tr><th>Username</th><td><input type="text" name="username" value="'.htmlspecialchars($_SESSION['db_vars']['username']).'" /></td></tr>';
echo '<tr><th>Password</th><td><input type="text" name="password" value="'.htmlspecialchars($_SESSION['db_vars']['password']).'" /></td></tr>';
echo '<tr><th>HostName</th><td><input type="text" name="hostname" value="'.htmlspecialchars($_SESSION['db_vars']['hostname']).'" /></td></tr>';
echo '<tr><th>Database Name</th><td><input type="text" name="db_name" value="'.htmlspecialchars($_SESSION['db_vars']['db_name']).'" /></td></tr>';
echo '</table><input name="action" type="submit" value="Configure Database" /></form>';

require 'footer.php';
