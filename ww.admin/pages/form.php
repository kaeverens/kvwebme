<?php
/**
	* show the admin form for editing a page
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

// { requires
require_once '../../ww.incs/common.php';
require_once '../admin_libs.php';
if (!Core_isAdmin()) {
	Core_quit();
}
// }
// { functions

/**
	* function for showing a page's body, overriding using a plugin if necessary
	*
	* @param array $page      the page's db row
	* @param array $page_vars any meta data for the page
	*
	* @return the page form
	*/
function Page_showBody($page, $page_vars) {
	foreach ($GLOBALS['PLUGINS'] as $plugin) {
		if (isset($plugin['admin']['body_override'])) {
			return $plugin['admin']['body_override']($page, $page_vars);
		}
	}
	return ckeditor('body', $page['body'], null, 1);
}

// }
// { take care of actions
$id=isset($_REQUEST['id'])
	?(int)$_REQUEST['id']
	:0;
$parent=isset($_REQUEST['parent'])?(int)$_REQUEST['parent']:0;
$action=isset($_REQUEST['action'])?$_REQUEST['action']:'';
$msgs='';
require_once 'pages.funcs.php';
if ($action=='Update Page Details') {
	require_once $_SERVER['DOCUMENT_ROOT'].'/ww.incs/api-admin.php';
	$arr=Core_adminPageEdit();
	echo '<script>window.parent.document.getElementById("page_'.$id.'")'
		.'.childNodes[1].innerHTML=\'<ins class="jstree-icon">&nbsp;</ins>'
		.htmlspecialchars(__FromJson($_REQUEST['name'], true)).'\';</script>';
}
$is_an_update=$action=='Insert Page Details'||$action=='Update Page Details';
$edit=($is_an_update || $action=='edit' || $id)?1:0;
// }
// { display header and link in scripts
WW_addScript('/j/js.js');
WW_addScript('/j/jquery.json-2.2.min.js');
WW_addScript('/j/ckeditor-3.6.2/ckeditor.js');
WW_addScript('/j/ckeditor-3.6.2/adapters/jquery.js');
WW_addScript('/ww.admin/j/admin.js');
WW_addScript('/j/jquery.dataTables-1.7.5/jquery.dataTables.min.js');
WW_addScript('/j/jquery.remoteselectoptions.js');
WW_addScript('/j/cluetip/jquery.cluetip.js');
WW_addScript('/j/jquery-ui-timepicker-addon.js');
WW_addScript('/ww.admin/pages/form2.js');
WW_addScript('/j/uploader.js');
WW_addScript('/j/lang.js');
WW_addScript('/j/jstree/jquery.jstree.js');
WW_addScript('/j/jstree/_lib/jquery.cookie.js');
WW_addInlineScript('$.jstree._themes="/j/jstree/themes/";');
echo '<html><head>'
	.Core_getJQueryScripts()
	.'<link rel="stylesheet" href="/j/jquery.dataTables-1.7.5'
	.'/jquery.dataTables.css" />'
	.'<link rel="stylesheet" href="/j/cluetip/jquery.cluetip.'
	.'css" />'
	.'<link rel="stylesheet" href="/ww.admin/theme/admin.css" />'
	.'<link rel="stylesheet" href="/ww.admin/pages/form.css" />'
	.'<title>page form</title>';
// { languages
$langs=dbAll(
	'select code,name from language_names order by is_default desc,code,name'
);
WW_addInlineScript('var languages='.json_encode($langs).';');
// }
echo '</head>'
	.'<body class="noheader">';
// }
// { no page selected?
if ((!isset($_REQUEST['id']) || $_REQUEST['id']==0)
	&& (!isset($_REQUEST['action']) || $_REQUEST['action']!='Insert Page Details')
) {
	echo '<p>'
		.__('Please use the navigation menu on the left to choose a page or to create a new one', 'core')
		.'</p>';
	WW_addInlineScript(
		'this.page_menu_currentpage='.$id.';this.sessid="'.session_id().'";'
		.'this.user_id='.$_SESSION['userdata']['id'].';'
		.'this.userdata={wasAdmin:1}'
	);
	echo WW_getScripts();
	echo WW_getCss();
	echo '</body></html>';
	Core_quit();
}
// }
// { page data
if ($id && $edit) { // check that page exists
	$page=dbRow("SELECT * FROM pages WHERE id=$id");
	if (!$page) {
		$edit=false;
	}
	else {
		$PAGEDATA=Page::getInstance($id);
	}
}
$page_vars=array();
echo @$msgs;
if ($edit) {
	if (isset($_REQUEST['newpage_dialog']) && $page['special']&2) {
		$page['special']-=2;
	}
	$pvq=dbAll("SELECT * FROM page_vars WHERE page_id=$id");
	foreach ($pvq as $pvr) {
		$page_vars[$pvr['name']]=$pvr['value'];
	}
}
else {
	$parent=isset($_REQUEST['parent'])?(int)$_REQUEST['parent']:0;
	$special=0;
	if (isset($_REQUEST['hidden'])) {
		$special+=2;
	}
	$page=array(
		'parent'=>$parent,
		'type'=>'0',
		'body'=>'',
		'name'=>'',
		'title'=>'',
		'ord'=>0,
		'description'=>'',
		'id'=>0,
		'keywords'=>'',
		'special'=>$special,
		'template'=>'',
		'stylesheet'=>'',
		'importance'=>0.5
	);
	$id=0;
}
$page_vars['_body']=$page['body'];
$maxLength = (isset($DBVARS['site_page_length_limit'])
	&& $DBVARS['site_page_length_limit']
)
	?$DBVARS['site_page_length_limit']
	:0;
// }
// { form header
echo '<form enctype="multipart/form-data" id="pages_form" class="pageForm"'
	.' method="post" action="'.$_SERVER['PHP_SELF'].'">'
	.'<input type="hidden" name="MAX_FILE_SIZE" value="9999999" />';
echo '<input type="hidden" name="id" value="'.$page['id'].'"/>';
// }
// { list of tabs
echo '<div id="pages-tabs" class="tabs">'
	.'<ul>'
	.'<li><a href="#pages-common">'.__('Common Details').'</a></li>'
	.'<li><a href="#seo">'.__('SEO / Metadata').'</a></li>'
	.'<li><a href="#misc">'.__('Misc.').'</a></li>';
foreach ($PLUGINS as $n=>$p) {
	if (isset($p['admin']['page_panel'])) {
		$name = $p['admin']['page_panel']['name'];
		echo '<li><a href="#'.$name.'">'.htmlspecialchars(__($name)).'</a></li>';
	}
}
echo '</ul>';
// }
// { Common Details
echo '<div id="pages-common">';
if ($page['special']&2 && !isset($_REQUEST['newpage_dialog'])) {
	echo '<em>'.__(
		'NOTE: this page is currently hidden from the front-end navigation. Use'
		.' the "Misc." tab to un-hide it.'
	).'</em>';
}
// { name, url
echo '<table>';
echo '<tr>';
// { name
echo '<th style="width:6%"><span class="help name"></span>'.__('name')
	.'</th><td style="width:23%">'
	.'<input id="name" name="name" value="'.htmlspecialchars($page['name'])
	.'" class="translatable" /></td>';
// }
// { type
echo '<th><span class="help type"></span>type</th><td><select name="type">';
$found=0;
if (preg_match('/^[0-9]*$/', $page['type'])) {
	foreach ($pagetypes as $a) {
		if ($a[0]==$page['type']) {
			echo '<option value="'.$a[0].'" selected="selected">'
				.htmlspecialchars(__($a[1])).'</option>';
			$found=1;
		}
	}
}
$plugin=false;
if (!preg_match('/^[0-9]*$/', $page['type'])) {
	foreach ($PLUGINS as $n=>$p) {
		if (isset($p['admin']['page_type'])) {
			if (is_array($p['admin']['page_type'])) {
				foreach ($p['admin']['page_type'] as $name => $function) {
					if ($name==$page['type'] || $n.'|'.$name==$page['type']) {
						echo '<option value="'.htmlspecialchars($page['type'])
							.'" selected="selected">'.htmlspecialchars(__($name)).'</option>';
						$plugin = $p;
						$found=1;
					}
				}
			}
			else if ($page['type']==$n || $n.'|'.$n==$page['type']) {
				echo '<option value="'.htmlspecialchars($page['type'])
					.'" selected="selected">'
					.htmlspecialchars(__($n)).'</option>';
				$plugin = $p;
				$found=1;
			}
		}
	}
}
if (!$found) {
	$page['type']=0;
}
echo '</select></td>';
// }
// { url 
echo '<th colspan="2">';
if ($edit) {
	echo '<a style="font-weight:bold;color:red" href="'
		.$PAGEDATA->getRelativeUrl().'" target="_blank">'.__('VIEW PAGE').'</a>';
}
else {
	echo '&nbsp;';
}
echo '</th>';
// }
echo '</tr>';
// }
// { page-type-specific data
if (isset($page['original_body'])) {
	$page['body']=$page['original_body'];
}
$form_type=preg_replace('/.*\|/', '', $page['type']);
switch ($form_type) {
	case '0': case '5': // { normal
		echo '<tr><th><span class="help body"></span>body</th><td colspan="5">'
			.Page_showBody($page, $page_vars)
			.'</td></tr>';
	break; // }
	case '1': // { redirect
		echo '<tr><th colspan="2">'.__('What URL would you like to redirect to')
			.'</th>'
			.'<td colspan="4"><input name="page_vars[redirect_to]" value="'
			.htmlspecialchars($page_vars['redirect_to'])
			.'" class="large"/></td></tr>';
	break; // }
	case '4': // { page summaries
		echo '<tr><th>'.__('pages summarised from')
			.'</th><td><select name="page_summary_parent">'
			.'<option value="0">'.__(' --  none  -- ').'</option>';
		$r2=dbRow(
			'select parent_id from page_summaries where page_id="'.$id.'" limit 1'
		);
		if (count($r2)) {
			$page_summary_pageid=$r2['parent_id'];
		}
		else {
			$page_summary_pageid=$id;
		}
		$opts=selectkiddies(0, 0, $page_summary_pageid, -1);
		foreach ($opts as $k=>$v) {
			echo '<option value="'.$k.'"';
			if ($k==$page_summary_pageid) {
				echo ' selected="selected"';
			}
			echo '>'.htmlspecialchars($v).'</option>';
		}
		echo '</select></td>'
			.'<td colspan="4">'.__(
				'Where do you want to start summarising your p'
				.'ages from? If you want this summary to list excerpts from all the p'
				.'ages on your site, then choose "<strong>none</strong>". Otherwise, '
				.'choose the page which <strong>contains</strong> the pages you want '
				.'summarised.'
			)
			.'</td></tr>';
	break; // }
	case '9': // { table of contents
		echo '<tr><td colspan="6"><div class="tabs">'
			.'<ul>'
			.'<li><a href="#table-of-contents-header">'.__('Header').'</a></li>'
			.'<li><a href="#table-of-contents-footer">'.__('Footer').'</a></li>'
			.'</ul>'
			.'<div id="table-of-contents-header">'
			.'<p>'.__('This will appear above the table of contents.').'</p>'
			.Page_showBody($page, $page_vars).'</div>'
			.'<div id="table-of-contents-footer">'
			.'<p>'.__('This will appear below the table of contents.').'</p>';
		if (!isset($page_vars['footer'])) {
			$page_vars['footer']='';
		}
		echo ckeditor('page_vars[footer]', $page_vars['footer']).'</div>'
			.'</div></td></tr>';
	break; // }
	default: // { plugin
		if ($plugin) {
			if (isset($plugin['admin']['page_type']) ) {
				if (isset($plugin['admin']['page_types'])
					&& in_array($form_type, $plugin['admin']['page_types'])
				) {
					echo '<tr><td colspan="6" id="body-wrapper">';
					$ignore=array(
						'footer', 'google-site-verification', 'order_of_sub_pages',
						'order_of_sub_pages_dir'
					);
					echo '<script>window.page_vars='.json_encode($page_vars).';</script>';
					echo '</td></tr>';
				}
				elseif (isset($plugin['admin']['page_type'][$form_type])
					&& function_exists($plugin['admin']['page_type'][$form_type])
				) {
					echo '<tr><td colspan="6">'
						.$plugin['admin']['page_type'][$form_type]($page, $page_vars)
						.'</td></tr>';
					break;
				}
				elseif ( function_exists($plugin['admin']['page_type'])) {
					echo '<tr><td colspan="6">'
						.$plugin['admin']['page_type']($page, $page_vars).'</td></tr>';
				}
			}
		}
		// }
}
// }
echo '</table></div>';
// }
// { SEO / Metadata
echo '<div id="seo"><table>';
// { title
echo '<tr><th><span class="help title"></span>'.__('title').'</th>'
	.'<td><input name="title" value="'.htmlspecialchars($page['title']).'"/></td>'
	.'</tr>';
// }
// { keywords
echo '<tr><th>'.__('keywords').'</th><td><input name="keywords" value="'
	.htmlspecialchars($page['keywords']).'"/></td></tr>';
// }
// { description
echo '<tr><th>'.__('description').'</th><td><textarea class="large" name="d'
	.'escription">'.htmlspecialchars($page['description']).'</textarea></td><'
	.'/tr>';
// }
// { short URL
echo '<tr><th>'.__('Short URL').'</th><td><input name="short_url" value="'
	.htmlspecialchars(
		dbOne('select short_url from short_urls where page_id='.$id, 'short_url')
	).'" /></td></tr>';
// }
// { sitemap importance
$importance=(float)$page['importance'];
if ($importance<.1) {
	$importance=.5;
}
echo '<tr title="'
	.__(
		'used by Google. importance of page relative to other pages on site. valu'
		.'es 0.1 to 1.0'
	)
	.'"><th>'.__('importance')
	.'</th><td><input name="importance" value="'.$importance.'" /></td></tr>';
if (!isset($page_vars['google-site-verification'])) {
	$page_vars['google-site-verification']='';
}
// }
// { google site verification
echo '<tr><th>'.__('Google Site Verification').'</th><td><input name="page_'
	.'vars[google-site-verification]" value="'
	.htmlspecialchars($page_vars['google-site-verification']).'" /></td></tr>';
// }
// { other <head> HTML
echo '<tr><th>'.__('other &lt;head&gt; HTML').'</th>'
	.'<td><textarea class="large" name="page_vars[header_html]">'
	.htmlspecialchars(@$page_vars['header_html']).'</textarea></td></tr>';
// }
echo '</table></div>';
// }
// { Misc.
echo '<div id="misc"><table><tr><td>';
// { dates, templates
echo '<table>';
// { publish date
echo '<tr><th>'.__('Date to Publish')
	.'</th><td><input name="date_publish" value="'.$page['date_publish']
	.'" class="datetime" title="'.__('year-month-day hour:minute').'"/></td></tr>';
// }
// { unpublish date
echo '<tr><th>'.__('Date to Unpublish')
	.'</th><td><input name="date_unpublish" value="'.$page['date_unpublish']
	.'" class="datetime" title="'.__('year-month-day hour:minute').'"/></td></tr>';
// }
// { associated date
$reg='/^[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9]/';
if (!isset($page['associated_date'])
	|| !preg_match($reg, $page['associated_date'])
	|| $page['associated_date']=='0000-00-00 00:00:00'
) {
	$page['associated_date']=date('Y-m-d 00:00:00');
}
echo '<tr><th>'.__('Associated Date')
	.'</th><td><input name="associated_date" value="'.$page['associated_date']
	.'" class="datetime" title="'.__('year-month-day hour:minute').'"/></td></tr>';
// }
// { order of sub-pages
echo '<tr><th>'.__('Order of sub-pages').'</th><td>'
	.'<select name="page_vars[order_of_sub_pages]">';
$arr=array('as shown in admin menu', 'alphabetically', 'by associated date');
foreach ($arr as $k=>$v) {
	echo '<option value="'.$k.'"';
	if (isset($page_vars['order_of_sub_pages'])
		&& $page_vars['order_of_sub_pages']==$k
	) {
		echo ' selected="selected"';
	}
	echo '>'.__($v).'</option>';
}
echo '</select><select name="page_vars[order_of_sub_pages_dir]"><option val'
	.'ue="0">'.__('ascending (a-z, 0-9)').'</option>';
echo '<option value="1"';
if (isset($page_vars['order_of_sub_pages_dir'])
	&& $page_vars['order_of_sub_pages_dir']=='1'
) {
	echo ' selected="selected"';
}
echo '>'.__('descending (z-a, 9-0)').'</option></select></td></tr>';
// }
// { template
echo '<tr><th>'.__('template').'</th><td>';
$d=array();
if (!file_exists(THEME_DIR.'/'.THEME.'/h/')) {
	echo __(
		'SELECTED THEME DOES NOT EXIST<br />Please <a href="/ww.admin/siteop'
		.'tions.php?page=themes">select a theme</a>'
	);
}
else {
	$dir=new DirectoryIterator(THEME_DIR.'/'.THEME.'/h/');
	foreach ($dir as $f) {
		if ($f->isDot()) {
			continue;
		}
		$n=$f->getFilename();
		if (preg_match('/\.html$/', $n)) {
			$d[]=preg_replace('/\.html$/', '', $n);
		}
	}
	asort($d);
	if (count($d)>1) {
		echo '<select name="template">';
		foreach ($d as $name) {
			echo '<option ';
			if ($name==$page['template']) {
				echo ' selected="selected"';
			}
			echo '>'.$name.'</option>';
		}
		echo '</select>';
	}
	else {
		echo __('no options available')
			.'<input type="hidden" name="template" value="'
			.htmlspecialchars($d[0]).'" />';
	}
}
echo '</td></tr>';
// }
// { recursively update page templates
echo '<tr><th>'.__('Recursively update page templates')
	.'</th><td><input type="checkbox" name="recursively_update_page_templates'
	.'" /></td></tr>';
// }
echo '</table>';
// }
echo '</td><td>';
// { special
echo '<h3>'.__('Special').'</h3>';
$specials=array(
	'Is Home Page',
	'Does not appear in navigation',
	'Is not summarised'
);
for ($i=0;$i<count($specials);++$i) {
	if ($specials[$i]!='') {
		echo '<input type="checkbox" name="special['.$i.']"';
		if ($page['special']&pow(2, $i)) {
			echo ' checked="checked"';
		}
		echo '/>'.__($specials[$i]).'<br />';
	}
}
// }
echo '</td></tr></table></div>';
// }
// { tabs added by plugins
foreach ($PLUGINS as $n=>$p) {
	if (isset($p['admin']['page_panel'])) {
		echo '<div id="'.$p['admin']['page_panel']['name'].'">';
		$p['admin']['page_panel']['function']($page, $page_vars);
		echo '</div>';
	}
}
// }
// { form footer
echo '</div>';
echo '<input type="hidden" name="action" value="Update Page Details"/>';
echo '<div id="submit-row"><input id="pages-submit" type="submit" value="'
	.__('Update Page Details').'"/></div>';
echo '</form>';
// }
// { page footer
WW_addInlineScript(
	'this.page_menu_currentpage='.$id.';this.sessid="'.session_id().'";'
	.'this.user_id='.$_SESSION['userdata']['id'].';'
	.'this.userdata={wasAdmin:1}'
);
echo WW_getScripts();
echo WW_getCss();
// }
echo '<div style="float:right">pageid: '.$id.'</div></body></html>';
