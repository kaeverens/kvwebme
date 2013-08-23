<?php

function SiteCredits_recordTransaction($description, $amt) {
	$amt=(float)$amt;
	$sql='insert into sitecredits_accounts set cdate=now(),'
		.'description="'.addslashes($description).'",amt='.$amt.','
		.'total='.$GLOBALS['DBVARS']['sitecredits-credits'];
	dbQuery($sql);
}

/*
mysql> describe sitecredits_accounts;
+-------------+----------+------+-----+---------+----------------+
| Field       | Type     | Null | Key | Default | Extra          |
+-------------+----------+------+-----+---------+----------------+
| id          | int(11)  | NO   | PRI | NULL    | auto_increment |
| cdate       | datetime | YES  |     | NULL    |                |
| description | text     | YES  |     | NULL    |                |
| amt         | float    | YES  |     | NULL    |                |
| total       | float    | YES  |     | NULL    |                |
+-------------+----------+------+-----+---------+----------------+
*/
