<?php
require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if (!is_admin()) {
	die('access denied');
}

function ImageTransition_getSubdirs ($base, $dir) {
	$arr=array();
	$D=new DirectoryIterator($base.$dir);
	$ds=array();
	foreach ($D as $dname) {
		$d=$dname.'';
		if (substr($d, 0, 1)=='.'
			|| !is_dir($base.$dir.'/'.$d)
		) {
			continue;
		}
		$ds[]=$d;
	}
	asort($ds);
	foreach ($ds as $d) {
		$arr[]=$dir.'/'.$d;
		$arr=array_merge($arr, ImageTransition_getSubdirs($base, $dir.'/'.$d));
	}
	return $arr;
}

if (isset($_REQUEST['get_image_transition'])) {
	$r=dbRow(
		'select * from image_transitions where id='
		.(int)$_REQUEST['get_image_transition']
	);
	if (!$r['url']) {
		$r['pagename']=' -- none -- ';
	}
	else {
		$r['pagename']=Page::getInstance($r['url'])->name;
	}
	$dirs=ImageTransition_getSubdirs(USERBASE.'f', '');
	if ($r===false) {
		$r=array('pause'=>3000);
	}
	echo json_encode(array( 'data'=>$r, 'directories'=>$dirs));
	exit;
}
if (isset($_REQUEST['action']) && $_REQUEST['action']=='save') {
	$id=(int)$_REQUEST['id'];
	$id_was=$id;
	$directory=addslashes($_REQUEST['directory']);
	$trans_type=addslashes($_REQUEST['trans_type']);
	$pause=(int)$_REQUEST['pause'];
	$url=(int)$_REQUEST['url'];
	if (!$pause) {
		$pause=3000;
	}
	$sql='image_transitions set directory="'.$directory.'",trans_type="'
		.$trans_type.'",pause="'.$pause.'",url="'.$url.'"';
	if ($id && dbOne('select id from image_transitions where id='.$id, 'id')) {
		$sql="update $sql where id=$id";
		dbQuery($sql);
	}
	else {
		$sql="insert into $sql";
		dbQuery($sql);
		$id=dbOne('select last_insert_id() as id', 'id');
	}
	$ret=array('id'=>$id, 'id_was'=>$id_was);
	echo json_encode($ret);
	cache_clear('image-transitions');
	exit;
}

if (isset($_REQUEST['id'])) {
	$id=(int)$_REQUEST['id'];
}
else {
	$id=0;
}
echo '<a href="javascript:;" id="image_transition_editlink_'.$id
	.'" class="image_transition_editlink">'
	.'view or edit image transition details</a>';
