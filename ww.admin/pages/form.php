<?php
require_once '../../ww.incs/common.php';
require_once '../admin_libs.php';
if (!is_admin()) {
	exit;
}

if ((!isset($_REQUEST['id']) || $_REQUEST['id']==0)
	&& (!isset($_REQUEST['action']) || $_REQUEST['action']!='Insert Page Details')
) {
	echo '<p>Please use the navigation menu on the left to choose a page or '
		.'to create a new one.</p>';
	exit;
}

function Page_showBody(&$page, &$page_vars) {
	foreach ($GLOBALS['PLUGINS'] as $plugin) {
		if (isset($plugin['admin']['body_override'])) {
			return $plugin['admin']['body_override']($page, $page_vars);
		}
	}
	return ckeditor('body',$page['body']);
}

// { take care of actions
$id=isset($_REQUEST['id'])
	?(int)$_REQUEST['id']
	:0;
$parent=isset($_REQUEST['parent'])?(int)$_REQUEST['parent']:0;
$action=isset($_REQUEST['action'])?$_REQUEST['action']:'';
$msgs='';
include('pages.funcs.php');
if($action=='Insert Page Details' || $action=='Update Page Details'){
	switch($action){
		case 'Insert Page Details': include('pages.action.new.php');    break;
		case 'Update Page Details': include('pages.action.edit.php');   break;
	}
}
$is_an_update=($action=='Insert Page Details' || $action=='Update Page Details');
$edit=($is_an_update || $action=='edit' || $id)?1:0;
// }
// { display header and link in scripts
echo '<html><head>'
	.Core_getJQueryScripts()
	.'<script src="/js/'.filemtime(SCRIPTBASE.'j/js.js').'"></script>'
	.'<script src="/j/ckeditor-3.6/ckeditor.js"></script>'
	.'<script src="/ww.admin/j/admin.js"></script>'
	.'<script src="/j/jquery.dataTables-1.7.5/jquery.dataTables.min.js"></script>'
	.'<link rel="stylesheet" type="text/css" href="/j/jquery.dataTables-1.7.5/jquery.dataTables.css" />'
	.'<script src="/j/jquery.remoteselectoptions.js"></script>'
	.'<script src="/j/cluetip/jquery.cluetip.js"></script>'
	.'<script src="form-20100924.js"></script>'
	.'<link rel="stylesheet" type="text/css" href="/j/cluetip/jquery.cluetip.css" />'
	.'<link rel="stylesheet" href="/ww.admin/theme/admin.css" type="text/css" />'
	.'</head>'
	.'<body class="noheader">';
// }

if($id && $edit){ // check that page exists
	$page=dbRow("SELECT * FROM pages WHERE id=$id");
	if(!$page)$edit=false;
	else $PAGEDATA=Page::getInstance($id);
}
$page_vars=array();
if(isset($msgs) && $msgs!='')echo $msgs;
if($edit){
	if(isset($_REQUEST['newpage_dialog']) && $page['special']&2)$page['special']-=2;
	$pvq=dbAll("SELECT * FROM page_vars WHERE page_id=$id");
	foreach($pvq as $pvr)$page_vars[$pvr['name']]=$pvr['value'];
}
else{
	$parent=isset($_REQUEST['parent'])?(int)$_REQUEST['parent']:0;
	$special=0;
	if(isset($_REQUEST['hidden']))$special+=2;
	$page=array('parent'=>$parent,'type'=>'0','body'=>'','name'=>'','title'=>'','ord'=>0,'description'=>'','id'=>0,'keywords'=>'','special'=>$special,'template'=>'','stylesheet'=>'','importance'=>0.5);
	$id=0;
}
$maxLength = (isset($DBVARS['site_page_length_limit']) && $DBVARS['site_page_length_limit'])
	?$DBVARS['site_page_length_limit']
	:0;
echo '<form enctype="multipart/form-data" id="pages_form" class="pageForm"'
	.' method="post" action="'.$_SERVER['PHP_SELF'].'"'
	.' maxLength="'.$maxLength.'">'
	.'<input type="hidden" name="MAX_FILE_SIZE" value="9999999" />';
echo '<div style="float:right">'.wInput('action','submit',($edit?'Update Page Details':'Insert Page Details')).'</div>';
if($page['special']&2 && !isset($_REQUEST['newpage_dialog']))echo '<em>NOTE: this page is currently hidden from the front-end navigation. Use the "Advanced Options" to un-hide it.</em>';
echo wInput('id','hidden',$page['id']);
echo '<div id="pages-tabs" class="tabs">'
	.'<ul>'
	.'<li><a href="#pages-common">Common Details</a></li>'
	.'<li><a href="#pages-advanced">Advanced Options</a></li>';
foreach($PLUGINS as $n=>$p){
	if(isset($p['admin']['page_panel'])){
		$name = $p['admin']['page_panel']['name'];
		echo '<li><a href="#'.$name.'">'.$name.'</a></li>';
	}
}
echo '</ul>';
// { Common Details
echo '<div id="pages-common">';
// { name, title, url
echo '<table>';
echo '<tr>';
// { name
echo '<th width="6%"><div class="help name"></div>name</th><td width="23%"><input id="name" name="name" value="'.htmlspecialchars($page['alias']).'" /></td>';
// }
// { title
echo '<th width="10%"><div class="help title"></div>title</th><td width="23%">'.wInput('title','',htmlspecialchars($page['title'])).'</td>';
// }
// { url 
echo '<th colspan="2">';
if($edit){
	echo '<a style="font-weight:bold;color:red" href="'
		.$PAGEDATA->getRelativeUrl().'" target="_blank">VIEW PAGE</a>';
}
else echo '&nbsp;';
echo '</th>';
// }
echo '</tr>';
// }
// { page type, parent, associated date
// { type
echo '<tr><th><div class="help type"></div>type</th><td><select name="type">';
$found=0;
if(preg_match('/^[0-9]*$/',$page['type']))foreach($pagetypes as $a){
	if ($a[0]==$page['type']) {
		echo '<option value="',$a[0],'" selected="selected">',htmlspecialchars($a[1]),'</option>';
		$found=1;
	}
}
$plugin=false;
if (!preg_match('/^[0-9]*$/',$page['type']))foreach($PLUGINS as $n=>$p) {
	if (isset($p['admin']['page_type']) ){
		if( is_array( $p[ 'admin' ][ 'page_type' ] ) ){
			foreach( $p[ 'admin' ][ 'page_type' ] as $name => $function ){
				if( $name == $page[ 'type' ] ){
					echo '<option value="',htmlspecialchars($name),'" selected="selected">',htmlspecialchars($name),'</option>';
					$plugin = $p;
					$found=1;
				}
			}
		}
		else if( $page['type']==$n ){
			echo '<option value="',htmlspecialchars($n),'" selected="selected">',htmlspecialchars($n),'</option>';
			$plugin = $p;
			$found=1;
		}
	}
}
if (!$found) {
	$page['type']=0;
}

echo '</select></td>';
// }
// { parent
echo '<th><div class="help parent"></div>parent</th><td><select name="parent">';
if($page['parent']){
	$parent=Page::getInstance($page['parent']);
	echo '<option value="',$parent->id,'">',htmlspecialchars($parent->alias),'</option>';
}
else echo '<option value="0"> -- none -- </option>';
echo '</select></td>';
// }
// { associated date
if(!isset($page['associated_date']) || !preg_match('/^[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9]$/',$page['associated_date']) || $page['associated_date']=='0000-00-00')
	$page['associated_date']=date('Y-m-d');
echo '<th><div class="help associated-date"></div>Associated Date</th><td><input name="associated_date" class="date-human" value="'.$page['associated_date'].'" /></td>';
echo '</tr>';
// }
// }
// { page-type-specific data
if (isset($page['original_body'])) {
	$page['body']=$page['original_body'];
}
switch($page['type']){
	case '0': case '5': // { normal
		echo '<tr><th><div class="help body"></div>body</th><td colspan="5">'
			.Page_showBody($page, $page_vars)
			.'</td></tr>';
		break;
	// }
	case '1': // { redirect
		echo '<tr><th colspan="2">What URL would you like to redirect to</th>'
			.'<td colspan="4"><input name="page_vars[redirect_to]" value="'
			.htmlspecialchars($page_vars['redirect_to'])
			.'" class="large"/></td></tr>';
	break; // }
	case '4': // { page summaries
		echo '<tr><th>pages summarised from</th><td><select name="page_summary_parent"><option value="0">--  none  --</option>';
		$r2=dbRow('select parent_id from page_summaries where page_id="'.$id.'" limit 1');
		if(count($r2)){
			$page_summary_pageid=$r2['parent_id'];
		}
		else $page_summary_pageid=$id;
		selectkiddies(0,0,$page_summary_pageid,-1);
		echo '</select></td>'.
		'<td colspan="4">Where do you want to start summarising your pages from? If you want this summary to list excerpts from all '.
		'the pages on your site, then choose "<strong>none</strong>". Otherwise, choose the page which <strong>contains</strong> '.
		'the pages you want summarised.</td></tr>';
		break;
	// }
	case '9': // { table of contents
		echo '<tr><td colspan="6"><div class="tabs">'
			.'<ul>'
			.'<li><a href="#table-of-contents-header">Header</a></li>'
			.'<li><a href="#table-of-contents-footer">Footer</a></li>'
			.'</ul>'
			.'<div id="table-of-contents-header">'
			.'<p>This will appear above the table of contents.</p>'
			.Page_showBody($page, $page_vars).'</div>'
			.'<div id="table-of-contents-footer">'
			.'<p>This will appear below the table of contents.</p>';
		if (!isset($page_vars['footer'])) {
			$page_vars['footer']='';
		}
		echo ckeditor('page_vars[footer]', $page_vars['footer']).'</div>'
			.'</div></td></tr>';
		break;
	// }
	default: // { plugin
		if ($plugin && isset($plugin['admin']['page_type']) ){
			if( is_array( $plugin[ 'admin' ][ 'page_type' ] ) ){
				foreach( $plugin[ 'admin' ][ 'page_type' ] as $name => $function ){
					if( $name == $page[ 'type' ] && function_exists( $function ) ){
						echo '<tr><td colspan="6">'.$function($page,$page_vars).'</td></tr>';
						break;
					}	
				}
			}
			else if ( function_exists($plugin['admin']['page_type'])) {
				echo '<tr><td colspan="6">'.$plugin['admin']['page_type']($page,$page_vars).'</td></tr>';
			}
		}
	// }
}
// }
echo '</table></div>';
// }
// { Advanced Options
echo '<div id="pages-advanced">';
echo '<table>';
echo '<td>';
// { metadata 
echo '<h4>MetaData</h4><table>';
echo '<tr><th>keywords</th><td>'.wInput('keywords','',htmlspecialchars($page['keywords'])).'</td></tr>';
echo '<tr><th>description</th><td>'.wInput('description','',htmlspecialchars($page['description'])).'</td></tr>';
echo '<tr><th>Short URL</th><td><input name="short_url" value="'.htmlspecialchars(dbOne('select short_url from short_urls where page_id='.$id, 'short_url')).'" /></td></tr>';
$importance=(float)$page['importance'];
if ($importance<.1) {
	$importance=.5;
}
echo '<tr title="used by Google. importance of page relative to other pages on site. values 0.1 to 1.0">'
	.'<th>importance</th><td><input name="importance" value="'.$importance.'" /></td></tr>';
if (!isset($page_vars['google-site-verification'])) {
	$page_vars['google-site-verification']='';
}
echo '<tr><th>Google Site Verification</th><td><input name="page_vars[google-site-verification]" value="'.htmlspecialchars($page_vars['google-site-verification']).'" /></td></tr>';
echo '<tr>';
// { template
echo '<th>template</th><td>';
$d=array();
if(!file_exists(THEME_DIR.'/'.THEME.'/h/')){
	echo 'SELECTED THEME DOES NOT EXIST<br />Please <a href="/ww.admin/siteoptions.php?page=themes">select a theme</a>';
}
else{
	$dir=new DirectoryIterator(THEME_DIR.'/'.THEME.'/h/');
	foreach($dir as $f){
		if($f->isDot())continue;
		$n=$f->getFilename();
		if(preg_match('/\.html$/',$n))$d[]=preg_replace('/\.html$/','',$n);
	}
	asort($d);
	if(count($d)>1){
		echo '<select name="template">';
		foreach($d as $name){
			echo '<option ';
			if($name==$page['template'])echo ' selected="selected"';
			echo '>'.$name.'</option>';
		}
		echo '</select>';
	}
	else echo 'no options available<input type="hidden" name="template" value="'.htmlspecialchars($d[0]).'" />';
}
echo '</td>';
// }
echo '</tr>';
echo '</table>';
// }
echo '</td><td>';
// { special
echo '<h4>Special</h4>';
$specials=array('Is Home Page', 'Does not appear in navigation', 'Is not summarised');
for($i=0;$i<count($specials);++$i){
	if($specials[$i]!=''){
		echo wInput('special['.$i.']','checkbox',($page['special']&pow(2,$i))?1:0).$specials[$i].'<br />';
	}
}
// }
// { other
echo '<h4>Other</h4>';
echo '<table>';
// { order of sub-pages
echo '<tr><th>Order of sub-pages</th><td><select name="page_vars[order_of_sub_pages]">';
$arr=array('as shown in admin menu','alphabetically','by associated date');
foreach($arr as $k=>$v){
	echo '<option value="'.$k.'"';
	if(isset($page_vars['order_of_sub_pages']) && $page_vars['order_of_sub_pages']==$k)echo ' selected="selected"';
	echo '>'.$v.'</option>';
}
echo '</select><select name="page_vars[order_of_sub_pages_dir]"><option value="0">ascending (a-z, 0-9)</option>';
echo '<option value="1"';
if(isset($page_vars['order_of_sub_pages_dir']) && $page_vars['order_of_sub_pages_dir']=='1')echo ' selected="selected"';
echo '>descending (z-a, 9-0)</option></select></td></tr>';
// }
echo '<tr><th>Recursively update page templates</th><td><input type="checkbox" name="recursively_update_page_templates" /></td></tr>';
echo '</table>';
// }
echo '</td></tr></table></div>';
// }
// { tabs added by plugins
foreach($PLUGINS as $n=>$p){
	if(isset($p['admin']['page_panel'])){
		echo '<div id="'.$p['admin']['page_panel']['name'].'">';
		$p['admin']['page_panel']['function']($page,$page_vars);
		echo '</div>';
	}
}
// }
echo '</div>';
echo wInput('action','submit',($edit?'Update Page Details':'Insert Page Details'));
if(isset($_REQUEST['frontend-admin'])){
	echo '<input type="hidden" name="frontend-admin" value="1" />';
}
echo '</form>';
echo WW_getScripts();
echo WW_getCss();
echo '<script>//<![CDATA[
window.page_menu_currentpage='.$id.'
//]]></script></body></html>';
