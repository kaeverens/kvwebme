<?php
/**
	* convert a wordpress theme
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

/**
	* function for removing all PHP files from a directory
	*
	* @param string $dir the directory to clean
	*
	* @return null
	*/
function Theme_removeAllPHPFiles($dir) {
	$files=new DirectoryIterator($dir);
	foreach ($files as $file) {
		if ($file->isDot()) {
			continue;
		}
		if ($file->isDir()) {
			Theme_removeAllPHPFiles($dir.'/'.$file->getFilename());
		}
		elseif (preg_match('/\.php/', $file->getFilename())) {
			unlink($file->getPathname());
		}
	}
}

$failure_message='';
if (!isset($theme_folder)) { // called directly. don't do this.
	Core_quit();
}

// { build template
$h=file_get_contents($theme_folder.'/single.php');
do {
	$oldh=$h;
	// { includes
	if (strpos($h, '<?php get_footer(); ?>')!==false) {
		$h=str_replace(
			'<?php get_footer(); ?>',
			file_get_contents($theme_folder.'/footer.php'),
			$h
		);
	}
	if (strpos($h, '<?php get_search_form(); ?>')!==false) {
		$h=str_replace(
			'<?php get_search_form(); ?>',
			file_get_contents($theme_folder.'/searchform.php'),
			$h
		);
	}
	if (strpos($h, '<?php get_sidebar(); ?>')!==false) {
		$h=str_replace(
			'<?php get_sidebar(); ?>',
			file_get_contents($theme_folder.'/sidebar.php'),
			$h
		);
	}
	if (strpos($h, '<?php get_header(); ?>')!==false) {
		$h=str_replace(
			'<?php get_header(); ?>',
			file_get_contents($theme_folder.'/header.php'),
			$h
		);
	}
	// }
	// { navigation
	$h=str_replace('<?php echo home_url(); ?>', '/', $h);
	$h=str_replace('<?php the_title(); ?>', '{{$pagename}}', $h);
	$h=preg_replace(
		'#<\?php wp_nav_menu\(.*\);\s*\?>#', '{{MENU direction="horizontal"}}', $h
	);
	$h=preg_replace('#(href|src|action)="//#', '\1="/', $h);
	// }
	// { convertable objects
	$h=str_replace('<?php bloginfo(\'name\'); ?>', '{{$WEBSITE_TITLE}}', $h);
	$h=str_replace(
		'<?php bloginfo(\'description\');?>', '{{$WEBSITE_SUBTITLE}}', $h
	);
	$h=str_replace('<?php wp_head(); ?>', '{{$METADATA}}', $h);
	$h=str_replace(
		'<?php echo date("Y"); ?>', '{{$smarty.now|date_format:"%Y"}}', $h
	);
	$h=str_replace('<?php the_content(); ?>', '{{$PAGECONTENT}}', $h);
	$h=str_replace('<?php the_ID(); ?>', '{{$PAGEDATA->id}}', $h);
	// }
	// { translations
	$h=preg_replace('#<\?php _e\(\s*\'([^\']*)\'[^\)]*\);#', '\1<?php ', $h);
	$h=preg_replace('#<\?php __\(\s*\'([^\']*)\'[^\)]*\);#', '\1<?php ', $h);
	$h=preg_replace('#<\?php _e\(\s*"([^"]*)"[^\)]*\);#', '\1<?php ', $h);
	$h=preg_replace('#<\?php __\(\s*"([^"]*)"[^\)]*\);#', '\1<?php ', $h);
	// }
	// { stylesheet
	$h=str_replace(
		'<?php bloginfo( \'stylesheet_url\' ); ?>', '{{$THEMEDIR}}/style.css', $h
	);
	// }
	// { remove empty or meaningless bits
	$h=str_replace('<?php else : ?> <?php endif; ?>', '<?php endif; ?>', $h);
	$h=str_replace(array("\n", "\r", "\t", '  '), ' ', $h);
	$h=str_replace('<?php language_attributes(); ?>', '', $h);
	$h=preg_replace('#<meta[^>]*(Content-Type|charset).*?/>#', '', $h);
	$h=preg_replace('#<\?php\s*wp_link_pages(.*?);\s*\?>#', '', $h);
	$h=preg_replace('#<title.*?</title>#', '', $h);
	$h=str_replace('<?php wp_footer(); ?>', '', $h);
	$h=str_replace(
		'<?php $the_title = wp_title(\' - \', false); if ($the_title != \'\') '
		.': ?> <?php endif; ?>', '', $h
	);
	$h=str_replace(
		'<?php if (is_home()) echo \' class="current_page_item"\'; ?>', '', $h
	);
	$h=preg_replace(
		'#<\?php\s*if\s*\(\s*is_singular\(\)\s*&&\s*get_option\(\s*\'thread_com'
		.'ments\'\s*\)\s*\)\s*wp_enqueue_script\(\s*\'comment-reply\'\s*\);#',
		'<?php ', $h
	);
	$h=preg_replace(
		'#<\?php\s*if\s*\(\s*is_singular\(\)\s*\)\s*wp_enqueue_script\(\s*\'com'
		.'ment-reply\'\s*\);#', '<?php ', $h
	);
	$h=preg_replace(
		'#<span[^>]*><\?php\s*the_tags\(.*?\);\s*\?></span>#', '', $h
	);
	$h=str_replace(
		'<?php if ( is_active_sidebar(\'footer-widget-area\') ) dynamic_sidebar'
		.'(\'footer-widget-area\'); ?>', '', $h
	);
	$h=str_replace(
		'<?php if ( is_singular() ) { if ( is_active_sidebar(\'singular-widget-'
		.'area\') ) dynamic_sidebar(\'singular-widget-area\'); } ?>',
		'',
		$h
	);
	$h=str_replace(
		'<?php if (!is_singular()) { if ( is_active_sidebar(\'not-singular-widg'
		.'et-area\') ) dynamic_sidebar(\'not-singular-widget-area\'); } ?>',
		'',
		$h
	);
	$h=str_replace(
		'Powered by <a href="http://wordpress.org/">WordPress</a>', '', $h
	);
	$h=preg_replace(
		'#<\?php\s*if\(function_exists\([^\)]*\)\)\s*{.*?}\s*\?>#',
		'',
		$h
	);
	$h=preg_replace('#<\?php\s*wp_list_bookmarks\(.*?\);\s*\?>#', '', $h);
	$h=preg_replace('#<\?php\s*wp_get_archives\(.*?\);\s*\?>#', '', $h);
	$h=preg_replace('#<\?php\s*wp_tag_cloud\(.*?\);\s*\?>#', '', $h);
	$h=preg_replace('#\s*foreach\(.*?endforeach;\s*\?>#', ' ?>', $h);
	$h=preg_replace(
		'#<\?php\s*\$[a-z_]*\s*=\s*get_posts\([^\)]*\);\s*\?>#',
		'',
		$h
	);
	$h=preg_replace('#<div[^>]*>\s*</div>#', '', $h);
	$h=preg_replace('#<li[^>]*>\s*</li>#', '', $h);
	$h=preg_replace('#<span[^>]*>\s*</span>#', '', $h);
	$h=preg_replace('#<ul[^>]*>\s*</ul>#', '', $h);
	$h=preg_replace('#<!--.*?-->#', '', $h);
	$h=preg_replace('#/\*.*?\*/#', '', $h);
	$h=preg_replace('#<\?php\s*\?>#', '', $h);
	$h=preg_replace('#<\?php\\s*echo\s*THEME_URL;\s*\?>#', '{{$THEMEDIR}}', $h);
	$h=preg_replace('#<div\s*class="widget">\s*<h3>[^>]*</h3>\s*</div>#', '', $h);
	$h=str_replace(
		'<?php if ( is_singular() ) { ?> <?php } else { ?> <?php } ?>',
		'',
		$h
	);
	$h=preg_replace(
		'#<\?php\s*if\s*\(\s*!dynamic_sidebar\(\'.*?\'\)\s*\)\s*:\s*\?>\s*<\?ph'
		.'p\s*endif;\s*\?>#',
		'',
		$h
	);
	$h=str_replace('<?php echo HEADER_IMAGE_HEIGHT; ?>', '0', $h);
	$h=str_replace('<?php echo HEADER_IMAGE_WIDTH; ?>', '0', $h);
	$h=str_replace('<?php header_image(); ?>', '/i/blank.gif', $h);
	$h=preg_replace('#<link\s*rel="pingback".*?/>#', '', $h);
	$h=str_replace(
		'<img src="/i/blank.gif" width="0" height="0" alt="" />',
		'',
		$h
	);
	$h=preg_replace(
		'#<\?php\s*if\s*\(\s*get_header_image\(\)\s*!=\s*\'\'\s*\)\s*:\s*\?>\s*'
		.'<\?php\s*endif;\s*\?>#',
		'',
		$h
	);
	$h=preg_replace(
		'#<span class="post-info-date">Posted by <\?php.*?\?> on <\?php.*?\?> <'
		.'/span>#',
		'',
		$h
	);
	$h=preg_replace('#<\?php comments_number\(.*?\);\s*\?>#', '', $h);
	$h=str_replace(
		'<?php if($options[\'rss_url\'] <> \'\') { echo($options[\'rss_url\'])'
		.'; } else { bloginfo(\'rss2_url\'); } ?>',
		'',
		$h
	);
	$h=preg_replace(
		'#<\?php\s*echo\(\$options\[\'[a-z]*_url\'\]\);\s*\?>#',
		'',
		$h
	);
	$h=preg_replace('#<a href=""[^>]*>[^<]*</a>#', '', $h);
	$h=preg_replace(
		'#<\?php\s*if\(\$options\[\'[^\']*\'\]\s*<>\s*\'\'\)\s*:\s*\?>\s*<\?php'
		.'\s*endif;\s*\?>#',
		'',
		$h
	);
	$h=preg_replace('#<\?php\s*comments_template\(.*?\);\s*\?>#', '', $h);
	$h=preg_replace(
		'#<\?php\s*\$[a-z_]*\s*=\s*get_option\(.*?\);\s*\?>#',
		'',
		$h
	);
	$h=preg_replace('#<\?php\s*[a-z]*_post_link\(.*?\);\s*\?>#', '', $h);
	$h=preg_replace('#<a href="\#comments"[^>]*>[^<]*</a>#', '', $h);
	$h=preg_replace('#<a href="\#(comments|respond)"[^>]*>[^<]*</a>#', '', $h);
	$h=preg_replace(
		'#<\?php\s*if\s*\(comments_open\(\)\)\s*:\s*\?>\s*<\?php\s*endif;\s*\?>#',
		'',
		$h
	);
	$h=preg_replace('#<\?php\s*the_category\(.*?\);\s*\?>#', '', $h);
	$h=preg_replace('#<\?php\s*the_post\(\);\s*\?>#', '', $h);
	$h=preg_replace('#<\?php\s*post_class\(.*?\);\s*\?>#', '', $h);

	// }
	// { currently doesn't exist in engine
	$h=preg_replace('#<\?php\s*edit_post_link(.*?);\s*\?>#', '', $h);
	$h=str_replace('<?php body_class(); ?>', '', $h);
	$h=str_replace('<?php wp_meta(); ?>', '', $h);
	$h=str_replace('<?php wp_loginout(); ?>', '', $h);
	$h=str_replace('<?php wp_register(); ?>', '', $h);
	// }
} while ($h!=$oldh);
$h=preg_replace('#<!DOCTYPE[^>]*>#', "<!doctype html>\n", $h);
$n=substr_count($h, '<?php');
if ($n) {
	$failure_message=$n.' instances of <code>&lt;?php</code> remaining in template';
	return;
}
mkdir($theme_folder.'/h');
file_put_contents($theme_folder.'/h/_default.html', $h);
// }
// { delete all .php files
$failure_message=Theme_removeAllPHPFiles($theme_folder);
// }
// { convert the CSS
// }
