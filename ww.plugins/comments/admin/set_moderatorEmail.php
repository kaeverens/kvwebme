<?php

/**
	* Sets moderator email address
	*
	* PHP Version 5
	*
	* @category   CommentsPlugin
	* @package    WebworksWebme
	* @subpackage CommentsPlugin
	* @author     Kae Verens <kae@kvsites.ie>
	* @license    GPL Version 2
	* @link       www.kvweb.me
	**/

require_once $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if (!Core_isAdmin()) {
	Core_quit('You do not have permission to do this');
}

$DBVARS['comments_moderatorEmail']=$_REQUEST['email'];
Core_configRewrite();
