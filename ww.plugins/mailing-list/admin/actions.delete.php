<?php
/*
	Webme Mailing List Plugin v0.2
	File: admin/actions.delete.php
	Developer: Conor Mac Aoidh <http://macaoidh.name>
	Report Bugs: <conor@macaoidh.name>
*/

$id=$_GET['id'];
$id=str_replace('!','',$id);
dbQuery('delete from mailing_list where id="'.$id.'"');
$deleted='Email Deleted';
