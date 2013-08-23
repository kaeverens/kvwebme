<?php
/**
  * upgrade script for Publisher
  *
  * PHP Version 5
  *
	* @category   Whatever
  * @package    WebworksWebme
  * @subpackage Publisher
  * @author     Kae Verens <kae@kvsites.ie>
  * @license    GPL Version 2
  * @link       www.kvweb.me
 */

if ($version==0) { // set site to offline mode
	$DBVARS['offline']=1;
	$version=1;
}
