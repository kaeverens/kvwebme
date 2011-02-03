<?php
/*
	Webme News Plugin v0.1
	File: upgrade.php
	Developer: Conor Mac Aoidh <http://macaoidh.name>
	Report Bugs: <conor@macaoidh.name>
*/

if($version==0)$version=1;

$DBVARS[$pname.'|version']=$version;
config_rewrite();
