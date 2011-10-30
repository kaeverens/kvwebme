<?php
/**
	* edit a page
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
	* function for recursively updating a page (and its children) template
	*
	* @param int    $id       the page id
	* @param string $template the template name
	*
	* @return null
	*/
function Core_recursivelyUpdatePageTemplates($id, $template) {
	$pages=Pages::getInstancesByParent($id, false);
	$ids=array();
	foreach ($pages->pages as $page) {
		$ids[]=$page->id;
		Core_recursivelyUpdatePageTemplates($page->id, $template);
	}
	if (!count($ids)) {
		return;
	}
	dbQuery(
		'update pages set template="'.addslashes($template).'" where id in ('
		.join(',', $ids).')'
	);
}
require_once dirname(__FILE__).'/pages.action.common.php';
$pid=dbOne('select parent from pages where id='.$id, 'parent');
$l=dbRow("SELECT * FROM site_vars WHERE name='languages'");
// {
$keywords=$_REQUEST['keywords'];
$description=$_REQUEST['description'];
$associated_date=$_REQUEST['associated_date'];
$date_publish=$_REQUEST['date_publish'];
$date_unpublish=$_REQUEST['date_unpublish'];
$title=$_REQUEST['title'];
$importance=(float)$_REQUEST['importance'];
if ($importance<0.1) {
	$importance=0.5;
}
if ($importance>1) {
	$importance=1;
}
$template=$_REQUEST['template'];
$original_body=(isset($_REQUEST['body']))?$_REQUEST['body']:'';
foreach ($GLOBALS['PLUGINS'] as $plugin) {
	if (isset($plugin['admin']['body_override'])) {
		$original_body=$plugin['admin']['body_override'](false);
	}
}
$body=$original_body;
$body=Core_sanitiseHtml($body);
$name = transcribe($_REQUEST['name']);
$alias = addslashes($_REQUEST['name']);
// { check that name is not duplicate of existing page
$sql='select id from pages where name="'.addslashes($name).'" and parent='
	.$pid.' and id!="'.$id.'"';
if (dbOne($sql, 'id')) {
	$i=2;
	while (dbOne(
		'select id from pages where name="'.addslashes($name.$i).'" and parent='
		.$pid.' and id!="'.$id.'"', 'id'
	)) {
		$i++;
	}
	$msgs.='<em>A page named "'.$name.'" already exists. Page name amended to "'
		.$name.$i.'"</em>';
	$name=$name.$i;
	$alias=$alias.$i;
}
// }
// }
$q='update pages set importance="'.$importance.'"'
	.',template="'.addslashes($template).'",edate=now()'
	.',type="'.addslashes($_POST['type']).'"'
	.',date_unpublish="'.addslashes($date_unpublish).'"'
	.',date_publish="'.addslashes($date_publish).'"'
	.',associated_date="'.addslashes($associated_date).'"'
	.',keywords="'.addslashes($keywords).'"'
	.',description="'.addslashes($description).'"'
	.',name="'.addslashes($name).'",title="'.addslashes($_POST['title']).'"'
	.',original_body="'.addslashes(Core_sanitiseHtmlEssential($original_body)).'"'
	.',body="'.addslashes($body).'"'
	.',special='.$special
	.',alias="'.$alias.'"';
$q.=' where id='.$id;
dbQuery($q);
// { page_vars
dbQuery('delete from page_vars where page_id="'.$id.'"');
$pagevars=isset($_REQUEST['page_vars'])?$_REQUEST['page_vars']:array();
if (@$_REQUEST['short_url']) {
	dbQuery(
		'insert into short_urls set cdate=now(),page_id='.$id.',short_url="'
		.addslashes($_REQUEST['short_url']).'"'
	);
	$pagevars['_short_url']=1;
}
else {
	dbQuery('delete from short_urls where page_id='.$id);
	unset($pagevars['_short_url']);
}
if (is_array($pagevars)) {
	if (isset($pagevars['google-site-verification'])) {
		$pagevars['google-site-verification']=preg_replace(
			'#.*content="([^"]*)".*#',
			'\1',
			$pagevars['google-site-verification']
		);
	}
	foreach ($pagevars as $k=>$v) {
		if (is_array($v)) {
			$v=json_encode($v);
		}
		dbQuery(
			'insert into page_vars (name,value,page_id) values("'.addslashes($k)
			.'","'.addslashes($v).'",'.$id.')'
		);
	}
}
// }
if (isset($_REQUEST['recursively_update_page_templates'])) {
	Core_recursivelyUpdatePageTemplates($id, $template);
}
if ($_POST['type']==4) {
	$r2=dbRow('select * from page_summaries where page_id="'.$id.'"');
	$do=1;
	if ($r2) {
		if (isset($_POST['page_summary_parent'])
			&& $r2['parent_id']!=$_POST['page_summary_parent']
		) {
			dbQuery('delete from page_summaries where page_id="'.$_POST['id'].'"');
		}
		else {
			$do=0;
		}
	}
	if ($do) {
		dbQuery(
			'insert into page_summaries set page_id="'.$id.'",parent_id="'
			.$_POST['page_summary_parent'].'",rss=""'
		);
	}
	require_once SCRIPTBASE.'/ww.incs/page.summaries.php';
	PageSummaries_getHtml($_POST['id']);
}
$msgs.='<em>'.__('The page has been updated.').'</em>';
dbQuery('update page_summaries set rss=""');
Core_cacheClear('pages,menus');
unset($DBVARS['cron-next']);
Core_configRewrite();
echo '<script>window.parent.document.getElementById("page_'.$id.'")'
	.'.childNodes[1].innerHTML=\'<ins class="jstree-icon">&nbsp;</ins>'
	.htmlspecialchars($alias).'\';</script>';
