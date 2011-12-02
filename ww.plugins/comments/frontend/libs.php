<?php
/**
	* common functions for comments
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

/**
	* retrieve a list of comments for the page
	*
	* @param object $page Page object
	* @param string $dir  sort ascending or descending by date
	* @param int    $num  how many comments to get - 0=all
	* @param array  $also array of comment ids to show whether valid or not
	*
	* @return array list of comments
	**/
function Comments_getListOfComments($page, $dir='', $num=0, $also=array()) {
	$query='select * from comments where objectid='.$page->id.' and (isvalid=1';
	if (count($also)) {
		$query.=' or id in ('.join(',', $also).')';
	}
	$limit=$num?' limit 0,'.$num:'';
	return dbAll($query.') order by cdate '.$dir.$limit);
}
