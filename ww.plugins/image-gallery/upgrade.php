<?php
if($version==0){ // major upgrade from old gallery type to new type
	$pages=dbAll('select id from pages where type="image-gallery"');
	if(!is_dir(USERBASE.'ww.cache')){
		mkdir(USERBASE.'ww.cache');
	}
	if(!is_dir(USERBASE.'ww.cache/image-gallery')) {
		mkdir(USERBASE.'ww.cache/image-gallery');
	}
	foreach($pages as $page){
		$vars=dbRow(
			'select value from page_vars where name="image_gallery_type" and page_id='.$page['id']);
		if(file_exists(USERBASE.'ww.cache/image-gallery/'.$page['id'])) {
			unlink(USERBASE.'ww.cache/image-gallery/'.$page['id']);
		} 
		switch($vars['value']){ // upgrade gallery type
			case 'simple gallery':
				$newvars['gallery-template-type']='simple';
				$newvars['gallery-template']=file_get_contents(
					SCRIPTBASE.'ww.plugins/image-gallery/admin/types/simple.tpl'
				);
				$newvars['image_gallery_effect']='fade';
			break;
			case 'ad-gallery':
				$newvars['gallery-template-type']='list';
				$newvars['gallery-template']=file_get_contents(
					SCRIPTBASE.'ww.plugins/image-gallery/admin/types/list.tpl'
				);
				$newvars['image_gallery_effect']='slideHorizontal';
			break;
		}
		file_put_contents(
			USERBASE.'ww.cache/image-gallery/'.$page['id'],
			$newvars['gallery-template']
		);
		$query='insert into page_vars set ';
		$query.=' gallery-template-type="'.$newvars['gallery-template-type'].'",';
		$query.=' image_gallery_effect="'.$newvars['image_gallery_effect'].'",';
		$query.=' image_gallery_hover="opacity",';
		$query.=' gallery-template="'.addslashes($newvars['gallery-template']).'"';
		$query.=' where page_id='.$page['id'];
		dbQuery($query);	
	}
	$version=1;
}

// upgrade the $DBVARS array and rewrite the config file
$DBVARS[$pname.'|version']=$version;
config_rewrite();
