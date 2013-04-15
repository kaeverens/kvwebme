<?php
/**
  * Blog widget
  *
  * PHP Version 5
  *
  * @category   None
  * @package    None
  * @subpackage None
  * @author     Kae Verens <kae@kvsites.ie>
  * @license    GPL Version 2
  * @link       www.kvweb.me
 */

// { Blog_widget2

/**
	* widget for blog stuff
	*
	* @param array $vars variables
	*
	* @return html
	*/
function Blog_widget2($vars=null) {
	global $PAGEDATA;
	$entry_ids=array();
	if ($vars->tag) {
		$rs=dbAll(
			'select entry_id from blog_tags where tag="'.addslashes($vars->tag).'"'
		);
	}
	foreach ($rs as $r) {
		$entry_ids[]=$r['entry_id'];
	}
	$blog_author='';
	$excerpts_offset=0;
	$excerpts_per_page=2;
	$links_prefix=$PAGEDATA->getRelativeURL();
	$excerpt_length=100;
	$nobottomlinks=true;
	require dirname(__FILE__).'/excerpts.php';
	return $c;
}

// }
