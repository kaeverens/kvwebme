<?php

/**
	* Sets moderation of comments
	*
	* PHP Version 5
	*
	* @category   CommentsPlugin
	* @package    WebworksWebme
	* @subpackage CommentsPlugin
	* @author     Belinda Hamilton <bhamilton@webworks.ie>
	* @license    GPL Version 2
	* @link       www.kvweb.me
	**/

require_once $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if (!Core_isAdmin()) {
	Core_quit('You do not have permission to do this');
}

$set=dbOne(
	'select value from site_vars where name="comments_no_moderation"',
	'value'
);
if ($_REQUEST['value']=='true') {
	$val = 1;
}
elseif ($_REQUEST['value']=='false') {
	$val = 0;
}
if (!(isset($set))&&isset($val)) {
	dbQuery(
		'insert into site_vars 
		values("comments_no_moderation", '.$val.')'
	);
}
elseif (isset($val)) {
	dbQuery(
		'update site_vars set value='.$val.
		' where name = "comments_no_moderation"'
	);
}

if ($val==1) {
	dbQuery('update comments set isvalid=2 where isvalid=1');
}
Core_cacheClear('comments');

echo '{"value":"'.$val.'"}';
