<?php
function allowedToEditPage($id){
	if(!$id){
		return admin_can_create_top_pages();
	}
	$r=dbRow('select value from permissions where id="'.$id.'" and type=1');
	if(count($r)){
		$lines=explode(',',$r['value']);
		foreach($lines as $bit){
			$smallerbits=explode('=',$bit);
			if($smallerbits[0]==get_userid())return $smallerbits[1]&2;
		}
	}
	return 1;
}
function selectkiddies($i=0,$n=1,$s=0,$id=0){
	$q=dbAll('select name,id from pages where parent="'.$i.'" and id!="'.$id.'" order by ord,name');
	if(count($q)<1)return;
	foreach($q as $r){
		if($r['id']!=''){
			echo '<option value="'.$r['id'].'" title="'.htmlspecialchars($r['name']).'"';
			echo ($s==$r['id'])?' selected="selected">':'>';
			echo str_repeat('&raquo; ', $n);
			$name=$r['name'];
			if (strlen($name)>20) {
				$name=substr($name,0,17).'...';
			}
			echo htmlspecialchars($name).'</option>';
			selectkiddies($r['id'],$n+1,$s,$id);
		}
	}
}
function showshortcuts($id,$parent){
	$q=dbAll('select id,name from pages where parent="'.$parent.'" order by ord desc,name');
	if(count($q)){
		echo '<ul>';
		foreach($q as $r){
			echo '<li>';
			echo wInput('shortcuts['.$r['id'].']','checkbox');
			$r2=dbRow('select id,name from pagelinks where fromid="'.$id.'" and toid="'.$r['id'].'"');
			if(count($r2)){
				echo ' checked="checked"';
				$r['name']=$r2['name'];
			}
			echo ' />';
			echo wInput('shortcutsName['.$r['id'].']','text',htmlspecialchars($r['name']));
			showshortcuts($id,$r['id']);
			echo '</li>';
		}
		echo '</ul>';
	}
}
