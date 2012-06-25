<?php
/**
  * Backup plugin definition file
  *
  * PHP Version 5
  *
  * @category   Whatever
  * @package    None
  * @subpackage None
  * @author     Kae Verens <kae@kvsites.ie>
  * @license    GPL Version 2
  * @link       www.kvweb.me
 */

$plugin=array(
	'name' => 'Backup',
	'admin' => array(
			'menu'=>array(
				'Site Options>Backup'=>'plugin.php?_plugin=backup&amp;_page=backup'
			),
	),
	'description'=>function() {
		return __('backup your website, or replace with an old backup');
	},
	'version' => 0
);
