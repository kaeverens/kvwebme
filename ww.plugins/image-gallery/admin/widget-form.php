<?php
require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if (!Core_isAdmin()) {
	die('access denied');
}

function ImageGallery_getSubdirs ($base, $dir) {
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
		$arr=array_merge($arr, ImageGallery_getSubdirs($base, $dir.'/'.$d));
	}
	return $arr;
}

if (isset($_REQUEST['get_image_gallery'])) {
	$r=dbRow(
		'select * from image_gallery_widget where id='
		.(int)$_REQUEST['get_image_gallery']
	);
	$dirs=ImageGallery_getSubdirs(USERBASE.'/f', '');
	if ($r===false) {
		$r=array(
			'gallery_type'=>'List',
			'thumbsize'=>'75',
			'image_size'=>'250',
			'columns'=>'2',
			'rows'=>'3',
		);
	}
	echo json_encode(array( 'data'=>$r, 'directories'=>$dirs));
	Core_quit();
}
if (isset($_REQUEST['action']) && $_REQUEST['action']=='save') {
	$id=(int)$_REQUEST['id'];
	$id_was=$id;
	$directory=addslashes($_REQUEST['directory']);
	$gallery_type=addslashes($_REQUEST['gallery_type']);
	$thumbsize=(int)$_REQUEST['thumbsize'];
	$image_size=(int)$_REQUEST['image_size'];
	$rows=(int)$_REQUEST['rows'];
	$columns=(int)$_REQUEST['columns'];
	$sql='image_gallery_widget set directory="'.$directory.'",gallery_type="'
		.$gallery_type.'",thumbsize="'.$thumbsize.'",image_size="'.$image_size.'",
		rows="'.$rows.'",columns="'.$columns.'"';
	if ($id && dbOne('select id from image_gallery_widget where id='.$id, 'id')) {
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
	Core_cacheClear('image-gallery');
	Core_quit();
}

if (isset($_REQUEST['id'])) {
	$id=(int)$_REQUEST['id'];
}
else {
	$id=0;
}
echo '<a href="javascript:;" id="image_gallery_editlink_'.$id
	.'" class="image_gallery_editlink">'
	.'view or edit image gallery details</a>';
