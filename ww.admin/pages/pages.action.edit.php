<?php
function recursively_update_page_templates ($id, $template) {
	$pages=Pages::getInstancesByParent($id, false);
	$ids=array();
	foreach ($pages->pages as $page) {
		$ids[]=$page->id;
		recursively_update_page_templates($page->id,$template);
	}
	if (!count($ids)) {
		return;
	}
	dbQuery('update pages set template="'.addslashes($template).'" where id in ('.join(',',$ids).')');
}
require_once dirname(__FILE__).'/pages.action.common.php';
$pid=(int)$_REQUEST['parent'];
$l=dbRow("SELECT * FROM site_vars WHERE name='languages'");
// {
$keywords=$_REQUEST['keywords'];
$description=$_REQUEST['description'];
$associated_date=$_REQUEST['associated_date'];
$title=$_REQUEST['title'];
$importance=(float)$_REQUEST['importance'];
if($importance<0.1)$importance=0.5;
if($importance>1)$importance=1;
$template=$_REQUEST['template'];
$original_body=(isset($_REQUEST['body']))?$_REQUEST['body']:'';
$body=$original_body;
$body=sanitise_html($body);
$name=$_REQUEST['name'];
// { check that name is not duplicate of existing page
if (dbOne('select id from pages where name="'.addslashes($name).'" and parent='.$pid.' and id!="'.$_POST['id'].'"','id')) {
	$i=2;
	while(dbOne('select id from pages where name="'.addslashes($name.$i).'" and parent='.$pid.' and id!="'.$_POST['id'].'"','id'))$i++;
	$msgs.='<em>'.__('A page named "%1" already exists. Page name amended to "%2"',$name,$name.$i).'</em>';
	$name=$name.$i;
}
// }
// }
$q='update pages set importance="'.$importance.'"'
	.',template="'.addslashes($template).'",edate=now()'
	.',type="'.addslashes($_POST['type']).'"'
	.',associated_date="'.addslashes($associated_date).'"'
	.',keywords="'.addslashes($keywords).'"'
	.',description="'.addslashes($description).'"'
	.',name="'.addslashes($name).'",title="'.addslashes($_POST['title']).'"'
	.',original_body="'.addslashes(sanitise_html_essential($original_body)).'"'
	.',body="'.addslashes($body).'",parent='.$pid
	.',special='.$special;
$q.=' where id='.$id;
dbQuery($q);
// { page_vars
dbQuery('delete from page_vars where page_id="'.$id.'"');
$pagevars=isset($_REQUEST['page_vars'])?$_REQUEST['page_vars']:array();
if(is_array($pagevars))foreach($pagevars as $k=>$v){
	if(is_array($v))$v=json_encode($v);
	dbQuery('insert into page_vars (name,value,page_id) values("'.addslashes($k).'","'.addslashes($v).'",'.$id.')');
}
// }
if(isset($_REQUEST['recursively_update_page_templates']))recursively_update_page_templates($id,$template);
if($_POST['type']==4){
	$r2=dbRow('select * from page_summaries where page_id="'.$_POST['id'].'"');
	$do=1;
	if($r2){
		if(isset($_POST['page_summary_parent']) && $r2['parent_id']!=$_POST['page_summary_parent']){
			dbQuery('delete from page_summaries where page_id="'.$_POST['id'].'"');
		}
		else $do=0;
	}
	if($do)dbQuery('insert into page_summaries set page_id="'.$_POST['id'].'",parent_id="'.$_POST['page_summary_parent'].'",rss=""');
	include_once(SCRIPTBASE.'/ww.incs/page.summaries.php');
	displayPageSummaries($_POST['id']);
}
$msgs.='<em>The page has been updated.</em>';
dbQuery('update page_summaries set rss=""');
cache_clear('menus');
cache_clear('pages');
if(isset($_REQUEST['frontend-admin'])){
	echo '<script type="text/javascript">parent.location=parent.location;</script>';
}
else{
	echo '<script>window.parent.document.getElementById("page_'.$id.'")'
		.'.childNodes[1].innerHTML=\'<ins class="jstree-icon">&nbsp;</ins>'
		.htmlspecialchars($name).'\';</script>';
}
