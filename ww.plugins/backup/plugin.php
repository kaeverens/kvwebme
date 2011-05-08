<?php
/**
  * Backup plugin definition file
  *
  * PHP Version 5
  *
  * @category   Whatever
  * @package    WebworksWebme
  * @subpackage Form
  * @author     Kae Verens <kae@kvsites.ie>
  * @license    GPL Version 2
  * @link       www.kvweb.me
 */

$plugin=array(
	'name' => 'Backup',
	'admin' => array(
			'menu'=>array(
				'Site Options>Backup'=>'backup'
			),
	),
	'description' => 'backup your website, or replace with an old backup',
	'version' => 0
);
