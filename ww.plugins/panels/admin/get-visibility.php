<?php
require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if(!is_admin())die('access denied');

function panel_selectkiddies($i=0,$n=1,$s=array(),$id=0,$prefix=''){
	$q=dbAll('select name,id from pages where parent="'.$i.'" and id!="'.$id.'" order by ord,name');
	if(count($q)<1)return;
	$html='';
	foreach($q as $r){
		if($r['id']!=''){
			$html.='<option value="'.$r['id'].'" title="'.htmlspecialchars($r['name']).'"';
			$html.=(in_array($r['id'],$s))?' selected="selected">':'>';
			$name=strtolower(str_replace(' ','-',$r['name']));
			$html.= htmlspecialchars($prefix.$name).'</option>';
			$html.=panel_selectkiddies($r['id'],$n+1,$s,$id,$name.'/');
		}
	}
	return $html;
}

$s=array();
if(isset($_REQUEST['id'])){
	$id=(int)$_REQUEST['id'];
	$r=dbRow("select visibility from panels where id=$id");
	if(is_array($r) && count($r)){
		if($r['visibility'])$s=json_decode($r['visibility']);
	}
}
if(isset($_REQUEST['visibility']) && $_REQUEST['visibility']){
	$s=explode(',',$_REQUEST['visibility']);
}
echo panel_selectkiddies(0,1,$s,0);
