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
	.'<li><a href="#blog-main">'.__('Main', 'core').'</a></li>'
	.'<li><a href="#blog-options">'.__('Options', 'core').'</a></li>'
	.'<li><a href="#blog-header">'.__('Header', 'core').'</a></li>'
	.'<li><a href="#blog-footer">'.__('Footer', 'core').'</a></li>'
	.'</ul>';
// { main tab
$c.='<div id="blog-main"></div>';
// }
// { blog options
$c.='<div id="blog-options"><div id="blog-options-wrapper">';

// { front page options
$c.='<h2><a href="#">'.__('Front page options', 'core').'</a></h2>'
	.'<div>';
// { excerpts per page
$excerpts_per_page=isset($vars['blog_excerpts_per_page'])
	?(int)$vars['blog_excerpts_per_page']:0;
if (!$excerpts_per_page) {
	$excerpts_per_page=10;
}
$c.='<p>'.__('How many blog excerpts should be shown per page?', 'core').'</p>'
	.'<input class="small" name="page_vars[blog_excerpts_per_page]"'
	.' value="'.$excerpts_per_page.'"/>';
// }
// { excerpt length
$excerpt_length=isset($vars['blog_excerpt_length'])
	?(int)$vars['blog_excerpt_length']:0;
if (!$excerpt_length) {
	$excerpt_length=200;
}
$c.='<p>'
	.__(
		'If no excerpt is provided for a blog entry, then one will be automatically'
		.' created by clipping the main article body after a number of characters.'
		.' How many?', 'core'
	)
	.'</p>'
	.'<input class="small" name="page_vars[blog_excerpt_length]"'
	.' value="'.$excerpt_length.'"/>';
// }
// { show a featured-story carousel
$featured_posts=isset($vars['blog_featured_posts'])
	?(int)$vars['blog_featured_posts']:0;
$c.='<p>'.__(
	'If you would like featured posts to appear in a carousel, how many should'
	.' appear?','core'
)
	.'</p>'
	.'<input class="small" name="page_vars[blog_featured_posts]"'
	.' value="'.$featured_posts.'"/>';
// }
// { facebook comments
$fbappid=(int)@$vars['blog_fbappid'];
$c.='<p>'.__('Your Facebook App ID (for comments management)', 'core').'</p>'
	.'<input name="page_vars[blog_fbappid]"'
	.' value="'.$fbappid.'"/>';
// }
$c.='</div>';
// }
// { groups access
$groups=dbAll('select * from groups where id!=1');
if (count($groups)) {
	$c.='<h2><a href="#">'.__('User blog rights', 'core').'</a></h2>'
		.'<div><p>'.__(
			'Along with administrators, what user groups should be allowed to'
			.' create blog entries?', 'core'
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
	.'<p>'.__('This text will appear above <i>all</i> blog entries', 'core').'</p>';
$c.=ckeditor('body', $page['body']);
$c.='</div>';
// }
// { footer
$c.='<div id="blog-footer">'
	.'<p>'.__('This text will appear below <i>all</i> blog entries', 'core').'</p>';
$c.=ckeditor(
	'page_vars[footer]',
	(isset($vars['footer'])?$vars['footer']:'')
);
$c.='</div>';
// }
$c.='</div>';
WW_addCss('/ww.plugins/blog/admin/admin.css');
WW_addScript('blog/admin/admin.js');
