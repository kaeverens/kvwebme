<?php
/**
  * website publisher definition file
  *
  * PHP Version 5
  *
  * @category   Whatever
  * @package    Webme
  * @subpackage Whatever
  * @author     Kae Verens <kae@kvsites.ie>
  * @license    GPL Version 2
  * @link       www.kvweb.me
 */

$plugin=array(
	'name' => 'Publisher',
	'admin' => array(
			'menu'=>array(
				'Site Options>Publisher'=>'plugin.php?_plugin=publisher&amp;_page=publish'
			)
	),
	'description' => 'download a static version of your website',
	'version'     => 1
);
