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
  * @link       www.kvsites.ie
**/

require_once $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if (!is_admin()) {
	exit ('You do not have permission to do this');
}

$DBVARS['comments_moderatorEmail']=$_REQUEST['email'];
config_rewrite();
