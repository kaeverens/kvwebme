<?php
require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if (!Core_isAdmin()) {
	die('access denied');
}
if (isset($_POST['get_mp3_files'])) { // get mp3 files via ajax
	$id=(int)$_REQUEST['get_mp3_files'];
	if ($id!=0) {
		$files=dbRow('select fields,template from mp3_plugin where id='.$id);
		$template=$files['template'];
		$files=json_decode($files['fields']);
	}
	if ($id==0||count($files)==0) {
		echo json_encode(
			array(
				'fields'=>array(array('name'=>'', 'location'=>'')),
				'id'=>0,
				'template'=>@$template
			)
		);
		Core_quit();
	}
	echo json_encode(array('fields'=>$files, 'id'=>$id, 'template'=>$template));
	Core_quit();
}
if (isset($_POST['mp3_id'])) { // save mp3 files
	$id=(int)$_POST['mp3_id'];
	$template=$_POST['mp3_template'];
	$files=array();
	for ($i=0;$i<count($_POST['fileNames']);++$i) {
		array_push(
			$files,
			array(
				'name'=>$_POST['fileNames'][$i],
				'location'=>$_POST['fileLocations'][$i],
			)
		);
	}
	$files=addslashes(json_encode($files));
	$last_id=$id;
	if ($id==0) {
		dbQuery(
			'insert into mp3_plugin (id,fields,template) values ("","'.$files
			.'","'.addslashes($template).'")'
		);
		$id=dbOne('select last_insert_id() as id', 'id');
		// create caches
		if (!is_dir(USERBASE.'/ww.cache/mp3')) {
			mkdir(USERBASE.'/ww.cache/mp3');
		}
		if (file_exists(USERBASE.'/ww.cache/mp3/'.$id)) {
			unlink(USERBASE.'/ww.cache/mp3/'.$id);
		} 
	}
	else {
		dbQuery(
			'update mp3_plugin set fields="'.$files
			.'", template="'.addslashes($template).'" where id='.$id
		);
	}
	echo json_encode(
		array('id'=>$id, 'id_was'=>$last_id)
	);
	file_put_contents(
	  USERBASE.'/ww.cache/mp3/'.$id,
	  @$template
	);
	Core_quit();
}
$id=isset($_REQUEST['id'])?$_REQUEST['id']:0;
echo '<a href="javascript:;" id="mp3_editlink_'
	.$id.'" class="mp3_editlink">view or edit mp3 files</a>';
