<?php
/**
  * Main page for controlling backups/imports
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

echo '<p>'
	.__(
		'Backing up your site may use a lot of resources and take a while '
		.'if your site is large. Please only do this once a day.'
	)
	.'</p>'
	.'<form action="/ww.plugins/backup/admin/create-backup.php" method="post">'
	.'<table>'
	.'<tr><th>'.__('Password')
	.'</th><td><input name="password" type="password" /></td>'
	.'<td>'.__('Password to use to encrypt the archive.').'</td></tr>'
	.'<tr><th colspan="3">'
	.'<input type="submit" name="action" value="'.__('Create Backup').'" />'
	.'</th></tr></table></form>';
WW_addScript('backup/admin/backup.js');
