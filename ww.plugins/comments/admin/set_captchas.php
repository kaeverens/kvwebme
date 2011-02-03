<?php

/**
  * Sets captchas of comments
  *
  * PHP Version 5
  *
  * @category   CommentsPlugin
  * @package    WebworksWebme
  * @subpackage CommentsPlugin
  * @author     Belinda Hamilton <bhamilton@webworks.ie>
  * @license    GPL Version 2
  * @link       www.webworks.ie
**/

require_once $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if (!is_admin()) {
	exit ('You do not have permission to do this');
}

dbQuery('delete from site_vars where name="comments_no_captchas"');
if ($_REQUEST['value']=='true') {
	dbQuery(
		'insert into site_vars 
		values("comments_no_captchas", 1)'
	);
}
