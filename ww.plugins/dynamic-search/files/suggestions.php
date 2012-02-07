<?php
/**
	* Webme Dynamic Search Plugin
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Conor Mac Aoidh <conor@macaoidh.name>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

$search=addslashes($_GET['chars']);

require '../../../.private/config.php';

$connect=mysql_connect(
	$DBVARS['hostname'],
	$DBVARS['username'],
	$DBVARS['password']
);
mysql_select_db($DBVARS['db_name'],$connect);

$query=mysql_query(
	'select *, count(search) as occurances from latest_search where search '
	.'like "%'.$search.'%" group by search order by occurances desc limit 10'
);

while ($row=mysql_fetch_assoc($query)) {
	echo '<li><a href="javascript:dynamic_suggestions(\''.$row['search'].'\')">'
		.$row['search'].'</a></li>';
}

?>
