<?php
require 'header.php';
// install basic db tables

if(!$_SESSION['db_vars']['passed']){ // user shouldn't be here
	header('Location: /install/step1.php');
	exit;
}

mysql_connect($_SESSION['db_vars']['hostname'], $_SESSION['db_vars']['username'], $_SESSION['db_vars']['password']);
mysql_select_db($_SESSION['db_vars']['db_name']);

// { user_accounts
mysql_query('create table user_accounts(
	id int auto_increment not null primary key,
	email text default "",
	name text default "",
	password varchar(32),
	phone text default "",
	active smallint default 0,
	address text,
	parent int default 0
)default charset=utf8');
// }
// { groups
mysql_query('create table groups(
	id int auto_increment not null primary key,
	name text,
	parent int default 0
)default charset=utf8');
// }
// { users_groups
mysql_query('create table users_groups(
	user_accounts_id int default 0,
	groups_id int default 0
)default charset=utf8');
// }

$_SESSION['db_vars']['db_installed']=1;
echo '<script type="text/javascript">setTimeout(function(){document.location="/install/step3.php"},1000);</script>';
echo '<p>Database installed. Please <a href="step3.php">click here to proceed</a>.</p>';
