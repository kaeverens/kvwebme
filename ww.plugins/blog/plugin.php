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
	'name' => function() {
		return __('Blog');
	},
	'description' => function() {
		return __('Add a blog page-type to your site');
	},
	'admin' => array(
		'page_type' => 'Blog_admin'
	),
	'frontend' => array(
		'page_type' => 'Blog_frontend',
		'widget' => 'Blog_widget'
	),
	'version'=>12
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
	if (isset($_SESSION['userdata']['id'])) { // load SaorFM
		WW_addCSS('/j/jquery.saorfm/jquery.saorfm.css');
		WW_addScript('/j/jquery.saorfm/jquery.saorfm.js');
	}
	// { parameters
	$excerpts_per_page=(int)$PAGEDATA->vars['blog_excerpts_per_page'];
	if (!$excerpts_per_page) {
		$excerpts_per_page=10;
	}
	// }
	$excerpts_offset=0;
	$blog_author=0;
	$authors_per_page=10;
	WW_addScript('blog');
	if (isset($PAGEDATA->vars['blog_groupsAllowedToPost'])
		&& $PAGEDATA->vars['blog_groupsAllowedToPost']
	) {
		WW_addInlineScript(
			'var blog_groups='
			.$PAGEDATA->vars['blog_groupsAllowedToPost'].';'
		);
	}
	if ($unused_uri) {
		// { show specific article
		if (preg_match('#^[0-9]+/[0-9]+-[0-9]+-[0-9]+/[^/]+#', $unused_uri)) {
			require_once dirname(__FILE__).'/frontend/show-article.php';
			return $PAGEDATA->render().$c.@$PAGEDATA->vars['footer'];
		}
		// }
		// { show a page of excerpts
		if (preg_match('#page[0-9]+#', $unused_uri)) {
			$excerpts_offset=$excerpts_per_page*((int)preg_replace(
				'#page([0-9]+).*#', '\1', $unused_uri
			));
			require_once dirname(__FILE__).'/frontend/excerpts.php';
			return $PAGEDATA->render().$c.@$PAGEDATA->vars['footer'];
		}
		// }
		// { show list of a specific user's excerpts
		if (preg_match('#^[0-9]+#', $unused_uri)) {
			$blog_author=preg_replace('/^([0-9]+).*/', '\1', $unused_uri);
			require_once dirname(__FILE__).'/frontend/excerpts.php';
			return $PAGEDATA->render().$c.@$PAGEDATA->vars['footer'];
		}
		// }
		// { show list of authors
		if ($unused_uri=='authors/') {
			require_once dirname(__FILE__).'/frontend/authors.php';
			return $PAGEDATA->render().$c.@$PAGEDATA->vars['footer'];
		}
		// }
		// { tags
		if (preg_match('#^tags/#', $unused_uri)) {
			// { show list of tags
			if ($unused_uri=='tags/') {
				require_once dirname(__FILE__).'/frontend/tags.php';
				return $PAGEDATA->render().$c.@$PAGEDATA->vars['footer'];
			}
			// }
			// { show list of excerpts specific to a tag
			$tagname=preg_replace('#^tags/([^/]*).*#', '\1', $unused_uri);
			$tagsql=preg_replace('/[^a-zA-Z0-9]/', '_', $tagname);
			$entry_ids=array();
			$rs=dbAll(
				'select entry_id from blog_tags where tag like "'.$tagsql.'"'
			);
			foreach ($rs as $r) {
				$entry_ids[]=$r['entry_id'];
			}
			require_once dirname(__FILE__).'/frontend/excerpts.php';
			return $PAGEDATA->render().$c.@$PAGEDATA->vars['footer'];
			// }
		}
		// }
		return $PAGEDATA->render().$unused_uri.@$PAGEDATA->vars['footer'];
	}
	require_once dirname(__FILE__).'/frontend/excerpts.php';
	return $PAGEDATA->render().$c.@$PAGEDATA->vars['footer'];
}

// }
// { Blog_widget

/**
	* widget for blog stuff
	*
	* @return html
	*/
function Blog_widget($vars=null) {
	global $PAGEDATA;
	$rs=dbAll(
		'select count(tag) as cnt, tag from blog_tags group by tag order by tag'
	);
	$tags=array();
	foreach ($rs as $r) {
		$h=htmlspecialchars($r['tag']);
		$tags[]='<a href="'.$PAGEDATA->getRelativeUrl().'/tags/'.$h
			.'">'.$h.'</a>';
	}
	
	return '<div class="blog-tags">'.join(', ', $tags).'</div>';
}

// }
