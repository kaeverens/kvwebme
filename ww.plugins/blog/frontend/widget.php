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
	$c='';
	global $PAGEDATA;
	$entry_ids=array();
	if ($vars->tag) {
		$rs=dbAll(
			'select entry_id from blog_tags where tag="'.addslashes($vars->tag).'"'
		);
		foreach ($rs as $r) {
			$entry_ids[]=$r['entry_id'];
		}
	}
	$blog_author='';
	$excerpts_offset=(isset($vars->excerpts_offset) && $vars->excerpts_offset)?$vars->excerpts:0;
	$excerpts_per_page=(isset($vars->excerpts) && $vars->excerpts)?$vars->excerpts:2;
	$links_prefix=$PAGEDATA->getRelativeURL();
	if ($PAGEDATA->type!='blog|blog') {
		$page=PAGE::getInstanceByType('blog');
		$links_prefix=$page->getRelativeURL();
	}
	$excerpt_length=100;
	$nobottomlinks=true;
	if (!isset($vars->widgetType)) {
		$vars->widgetType='0';
	}
	switch ($vars->widgetType) {
		case '1': // { featured posts
			require dirname(__FILE__).'/featured-posts.php';
		break; // }
		default: // {
			if (isset($vars->imageSizeX) && $vars->imageSizeX) {
				$excerptImageSizeX=(int)$vars->imageSizeX;
			}
			if (isset($vars->imageSizeY) && $vars->imageSizeY) {
				$excerptImageSizeY=(int)$vars->imageSizeY;
			}
			require dirname(__FILE__).'/excerpts.php';
		break; //}
	}
	return $c;
}

// }
