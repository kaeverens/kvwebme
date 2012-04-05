<?php
/**
  * Blog plugin definition file
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

// { config
$plugin=array(
	'name' => 'Blog',
	'description' => 'Add a blog page-type to your site',
	'admin' => array(
		'page_type' => 'Blog_admin'
	),
	'frontend' => array(
		'page_type' => 'Blog_frontend'
	),
	'version'=>7
);
// }
// { Blog_admin

/**
	* show the admin of the blog
	*
  * @param array $page the page's db row
	* @param array $vars any meta data the page has
	*
	* @return string
	*/
function Blog_admin($page, $vars) {
	require_once dirname(__FILE__).'/admin/page-type.php';
	WW_addScript('blog');
	return $c;
}

// }
// { Blog_frontend

/**
	* show the frontend of the blog
	*
	* @param $PAGEDATA object the page object
	*
	* @return string
	*/
function Blog_frontend($PAGEDATA) {
	global $unused_uri;
	// { parameters
	$excerpts_per_page=(int)$PAGEDATA->vars['blog_excerpts_per_page'];
	if (!$excerpts_per_page) {
		$excerpts_per_page=10;
	}
	// }
	$excerpts_offset=0;
	$blog_author=0;
	WW_addScript('blog');
	WW_addInlineScript(
		'var blog_groups='
		.$PAGEDATA->vars['blog_groupsAllowedToPost'].';'
	);
	if ($unused_uri) {
		// show article if specified
		if (preg_match('#^[0-9]+/[0-9]+-[0-9]+-[0-9]+/[^/]+#', $unused_uri)) {
			require_once dirname(__FILE__).'/frontend/show-article.php';
			return $PAGEDATA->render().$c.@$PAGEDATA->vars['footer'];
		}
		// show a page of excerpts if specified
		if (preg_match('#page[0-9]+#', $unused_uri)) {
			$excerpts_offset=$excerpts_per_page*((int)preg_replace(
				'#page([0-9]+).*#', '\1', $unused_uri
			));
			require_once dirname(__FILE__).'/frontend/excerpts.php';
			return $PAGEDATA->render().$c.@$PAGEDATA->vars['footer'];
		}
		// show list of a specific user's excerpts
		if (preg_match('#^[0-9]+#', $unused_uri)) {
			$blog_author=preg_replace('/^([0-9]+).*/', '\1', $unused_uri);
			require_once dirname(__FILE__).'/frontend/excerpts.php';
			return $PAGEDATA->render().$c.@$PAGEDATA->vars['footer'];
		}
		return $PAGEDATA->render().$unused_uri.@$PAGEDATA->vars['footer'];
	}
	require_once dirname(__FILE__).'/frontend/excerpts.php';
	return $PAGEDATA->render().$c.@$PAGEDATA->vars['footer'];
}

// }
