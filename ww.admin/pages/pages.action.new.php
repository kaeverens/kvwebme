<?php
include dirname(__FILE__).'/pages.action.common.php';
$name=$_REQUEST['name'];
if(isset($_REQUEST['prefill_body_with_title_as_header']))$body='<h1>'.htmlspecialchars($name).'</h1><p>&nbsp;</p>';
else if(isset($_REQUEST['body']))$body=$_REQUEST['body'];
else $body='';
$name=addslashes($name);
$pid=(int)$_REQUEST['parent'];
if(dbQuery("select id from pages where name='$name' and parent=$pid")->rowCount()){
	$i=2;
	while(dbQuery("select id from pages where name='$name$i' and parent=$pid")->rowCount())$i++;
	$msgs.='<em>'.__('A page named "%1" already exists. Page name amended to "%2"',$name,$name.$i).'</em>';
	$name.=$i;
}
// { variables
$template=isset($_REQUEST['template'])?$_REQUEST['template']:'';
if ($template=='' && $pid) {
	$template=dbOne('select template from pages where id='.$pid, 'template');
}
$type=$_REQUEST['type'];
$title=isset($_REQUEST['title'])?addslashes($_REQUEST['title']):'';
$keywords=isset($_REQUEST['keywords'])?$_REQUEST['keywords']:'';
$associated_date=$_REQUEST['associated_date'];
$description=isset($_REQUEST['description'])?$_REQUEST['description']:'';
$importance=isset($_REQUEST['importance'])?(float)$_REQUEST['importance']:.5;
if($importance<0.1)$importance=0.5;
if($importance>1)$importance=1;
// }
$ord=dbOne('select ord from pages where parent='.$pid.' order by ord desc limit 1','ord')+1;
$original_body=(isset($_REQUEST['body']))?$_REQUEST['body']:'';
$body=$original_body;
$body=sanitise_html($body);
$q='insert into pages set ord="'.$ord.'",importance="'.$importance.'",'
	.'keywords="'.$keywords.'",description="'.$description.'",cdate=now(),'
	.'template="'.$template.'",edate=now(),name="'.$name.'",title="'.$title.'",'
	.'original_body="'.addslashes($original_body).'",'
	.'body="'.addslashes($body).'",type="'.$type.'",'
	.'associated_date="'.addslashes($associated_date).'"';
$q.=',parent='.$pid;
if(has_page_permissions(128))$q.=',special='.$special;else $q.=',special=0';
dbQuery($q);
$id=dbOne('select last_insert_id() as id','id');
dbQuery('insert into permissions set id="'.$id.'", type=1, value="'.get_userid().'=7'."\n\n4".'"');
$msgs.='<em>'.__('New page created.').'</em>';
dbQuery('update page_summaries set rss=""');
cache_clear('menus');
cache_clear('pages');
echo '<script>window.parent.pages_add_node("'.addslashes($name).'",'.$id.','.$pid.');</script>';
