<?php
/**
	* installer step 2
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
// install basic db tables

if (!$_SESSION['db_vars']['passed']) { // user shouldn't be here
	echo '<script>document.location="/install/step1.php";</script>'
		.__('Should not be here');
	Core_quit();
}

mysql_connect(
	$_SESSION['db_vars']['hostname'],
	$_SESSION['db_vars']['username'],
	$_SESSION['db_vars']['password']
);
mysql_select_db($_SESSION['db_vars']['db_name']);

// { user_accounts
mysql_query(
	'create table user_accounts(
		id int auto_increment not null primary key,
		email text default "",
		name text default "",
		password varchar(32),
		contact text default "",
		active smallint default 0,
		address text,
		parent int default 0
	)default charset=utf8'
);
// }
// { groups
mysql_query(
	'create table groups(
		id int auto_increment not null primary key,
		name text,
		parent int default 0
	)default charset=utf8'
);
// }
// { users_groups
mysql_query(
	'create table users_groups(
		user_accounts_id int default 0,
		groups_id int default 0
	)default charset=utf8'
);
// }

$_SESSION['db_vars']['db_installed']=1;
echo '<script defer="defer">document.location="/install/step3.php";</script>';
