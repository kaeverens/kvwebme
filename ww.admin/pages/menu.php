<?php
WW_addScript('/j/jstree-1.0rc2/jquery.jstree.js');
WW_addScript('/j/jstree-1.0rc2/_lib/jquery.cookie.js');
WW_addScript('/j/jquery.remoteselectoptions.js');
WW_addScript('/ww.admin/pages/menu.js');
echo '<div id="pages-wrapper">';
$rs=dbAll('select id,special&2 as disabled,type,name,parent from pages order by ord,name');
$pages=array();
foreach($rs as $r){
	if(!isset($pages[$r['parent']]))$pages[$r['parent']]=array();
	$pages[$r['parent']][]=$r;
}
function show_pages($id){
	global $pages;
	if(!isset($pages[$id]))return;
	echo '<ul>';
	foreach($pages[$id] as $page){
		if($page['name']=='')$page['name']='NO NAME';
		echo '<li id="page_'.$page['id'].'"><a';
		if($page['disabled']=='1')echo ' class="disabled"';
		echo '>'.htmlspecialchars($page['name']).'</a>';
		show_pages($page['id']);
		echo '</li>';
	}
	echo '</ul>';
}
show_pages(0);
if(count($pages))echo '<script>selected_page='.$pages[0][0]['id'].';</script></div>';
