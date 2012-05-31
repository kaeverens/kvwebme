<?php
/**
  * upgrade script
  *
  * PHP Version 5
  *
	* @category   Whatever
  * @package    Webme
  * @subpackage Form
  * @author     Kae Verens <kae@kvsites.ie>
  * @license    GPL Version 2
  * @link       www.kvweb.me
 */

if ($version==0) { // forms_fields
	dbQuery(
		'CREATE TABLE IF NOT EXISTS `issuetracker_types` (
			`id` int(11) NOT NULL auto_increment,
			`name` text,
			`fields` text,
			PRIMARY KEY  (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8'
	);
	$version=1;
}
