<?php
/**
  * blog admin page-type
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

$c = '<div class="tabs">'
	.'<ul>'
	.'<li><a href="#blog-main">Main</a></li>'
	.'<li><a href="#blog-options">Options</a></li>'
	.'<li><a href="#blog-header">Header</a></li>'
	.'<li><a href="#blog-footer">Footer</a></li>'
	.'</ul>';
// { main tab
$c.='<div id="blog-main"></div>';
// }
// { blog options
$c.='<div id="blog-options"><div id="blog-options-wrapper">';

// { front page options
$c.='<h2><a href="#">front page options</a></h2>'
	.'<div>';
// { excerpts per page
$excerpts_per_page=(int)$vars['blog_excerpts_per_page'];
if (!$excerpts_per_page) {
	$excerpts_per_page=10;
}
$c.='<p>'.__('How many blog excerpts should be shown per page?').'</p>'
	.'<input class="small" name="page_vars[blog_excerpts_per_page]"'
	.' value="'.$excerpts_per_page.'"/>';
// }
// { excerpt length
$excerpt_length=(int)$vars['blog_excerpt_length'];
if (!$excerpt_length) {
	$excerpt_length=200;
}
$c.='<p>'
	.__(
		'If no excerpt is provided for a blog entry, then one will be automatically'
		.' created by clipping the main article body after a number of characters.'
		.' How many?'
	)
	.'</p>'
	.'<input class="small" name="page_vars[blog_excerpt_length]"'
	.' value="'.$excerpt_length.'"/>';
// }
// { show a featured-story carousel
$featured_posts=(int)$vars['blog_featured_posts'];
if (!$featured_posts) {
	$featured_posts=0;
}
$c.='<p>'.__(
	'If you would like featured posts to appear in a carousel, how many should'
	.' appear?'
)
	.'</p>'
	.'<input class="small" name="page_vars[blog_featured_posts]"'
	.' value="'.$featured_posts.'"/>';
// }
// { facebook comments
$fbappid=(int)@$vars['blog_fbappid'];
$c.='<p>'.__('Your Facebook App ID (for comments management)').'</p>'
	.'<input name="page_vars[blog_fbappid]"'
	.' value="'.$fbappid.'"/>';
// }
$c.='</div>';
// }
// { groups access
$groups=dbAll('select * from groups where id!=1');
if (count($groups)) {
	$c.='<h2><a href="#">'.__('User blog rights').'</a></h2>'
		.'<div><p>'.__(
			'Along with administrators, what user groups should be allowed to'
			.' create blog entries?'
		)
		.'</p>';
	$allowed=array();
	if ($vars['blog_groupsAllowedToPost']) {
		$allowed=json_decode($vars['blog_groupsAllowedToPost'], true);
	}
	$c.='<ul>';
	foreach ($groups as $g) {
		$c.='<li>'
			.'<input type="checkbox" name="page_vars[blog_groupsAllowedToPost]['
			.$g['id'].']"';
		if (@$allowed[$g['id']]) {
			$c.=' checked="checked"';
		}
		$c.='/>'.htmlspecialchars($g['name']).'</li>';
	}
	$c.='</ul></div>';
}
// }

$c.='</div></div>';
// }
// { header
$c.='<div id="blog-header">'
	.'<p>This text will appear above <i>all</i> blog entries.</p>';
$c.=ckeditor('body', $page['body']);
$c.='</div>';
// }
// { footer
$c.='<div id="blog-footer">'
	.'<p>This text will appear below <i>all</i> blog entries.</p>';
$c.=ckeditor(
	'page_vars[footer]',
	(isset($vars['footer'])?$vars['footer']:'')
);
$c.='</div>';
// }
$c.='</div>';
WW_addCss('/ww.plugins/blog/admin/admin.css');
WW_addScript('blog/admin/admin.js');
