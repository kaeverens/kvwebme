<?php
require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if(!is_admin())die('access denied');
require $_SERVER['DOCUMENT_ROOT'].'/ww.admin/admin_libs.php';
if(isset($_REQUEST['get_content_snippet'])){
	$id=(int)$_REQUEST['get_content_snippet'];
	$r=dbRow('select * from content_snippets where id='.$id);
	if($r==false || !$r['content'] || $r['content']=='null'){
		echo '{"id":0,"content":[{"html":""}]}';
	}
	else{
		$json=json_decode($r['content']);
		if (!$json) { // sometimes apostrophes break json_decode?
			$json=json_decode(str_replace('\\\'','\\\\\'',$r['content']));
		}
		for ($i=0; $i<count($json); ++$i) {
			$json[$i]->html=html_unfixImageResizes($json[$i]->html);
			while (strpos($json[$i]->html, '/f/.files/image_resizes//f/') !== false) {
				$json[$i]->html=preg_replace(
					'#/f/.files/image_resizes//f/([^\'"]*)/[0-9]*x[0-9]*.jpg#',
					'/f/\1',
					$json[$i]->html
				);
			}
		}
		$r['content']=$json;
		echo json_encode($r);
	}
	exit;
}
if(isset($_REQUEST['action']) && $_REQUEST['action']=='save'){
	$id=(int)$_REQUEST['id'];
	$id_was=$id;
	$content=json_decode(utf8_decode($_REQUEST['html']));
	foreach($content as $k=>$v){
		$content[$k]->html=sanitise_html($v->html);
	}
	$html=json_encode($content);
	$sql='content_snippets set content="'.addslashes($html).'"';
	$sql.=',accordion="'.(int)$_REQUEST['accordion'].'"';
	$sql.=',accordion_direction="'.(int)$_REQUEST['accordion_dir'].'"';
	$sql.=',images_directory="'.addslashes($_REQUEST['accordion_images']).'"';
	if($id){
		$sql="update $sql where id=$id";
		dbQuery($sql);
	}
	else{
		$sql="insert into $sql";
		dbQuery($sql);
		$id=dbOne('select last_insert_id() as id','id');
	}
	cache_clear('content_snippets');
	$ret=array('id'=>$id,'id_was'=>$id_was);
	echo json_encode($ret);
	exit;
}

if(isset($_REQUEST['id']))$id=(int)$_REQUEST['id'];
else $id=0;
echo '<a href="javascript:;" id="content_snippet_editlink_'
	.$id.'" class="content_snippet_editlink">view or edit snippet</a>';
?>
