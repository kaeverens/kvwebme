<?php
/**
  * upgrade script for the image gallery plugin
  *
  * PHP Version 5
  *
  * @category   Whatever
  * @package    None
  * @subpackage None
  * @author     Kae Verens <kae@kvsites.ie>
  * @license    GPL Version 2
  * @link       www.kvweb.me
 */

if ($version==0) { // major upgrade from old gallery type to new type
	$pages=dbAll('select id from pages where type="image-gallery"');
	if (!is_dir(USERBASE.'/ww.cache')) {
		mkdir(USERBASE.'/ww.cache');
	}
	if (!is_dir(USERBASE.'/ww.cache/image-gallery')) {
		mkdir(USERBASE.'/ww.cache/image-gallery');
	}
	foreach ($pages as $page) {
		$vars=dbRow(
			'select value from page_vars where name="image_gallery_type" and '
			.'page_id='.$page['id']
		);
		if (file_exists(USERBASE.'/ww.cache/image-gallery/'.$page['id'])) {
			unlink(USERBASE.'/ww.cache/image-gallery/'.$page['id']);
		}
		$newvars=array();
		switch ($vars['value']) { // upgrade gallery type
			case 'simple gallery': // {
				$newvars['gallery-template-type']='simple';
				$newvars['gallery-template']=file_get_contents(
					SCRIPTBASE.'ww.plugins/image-gallery/admin/types/simple.tpl'
				);
				$newvars['image_gallery_effect']='fade';
			break; // }
			case 'ad-gallery': // {
				$newvars['gallery-template-type']='list';
				$newvars['gallery-template']=file_get_contents(
					SCRIPTBASE.'ww.plugins/image-gallery/admin/types/list.tpl'
				);
				$newvars['image_gallery_effect']='slideHorizontal';
			break; // }
		}
		file_put_contents(
			USERBASE.'/ww.cache/image-gallery/'.$page['id'],
			$newvars['gallery-template']
		);
		$query='insert into page_vars set '
			.' gallery-template-type="'.$newvars['gallery-template-type'].'",'
			.' image_gallery_effect="'.$newvars['image_gallery_effect'].'",'
			.' image_gallery_hover="opacity",'
			.' gallery-template="'.addslashes($newvars['gallery-template']).'"'
			.' where page_id='.$page['id'];
		dbQuery($query);	
	}
	$version=1;
}
if ($version==1) { // move from kfm to database orientated
	dbQuery(
		'create table image_gallery (
			id int primary key auto_increment,
			gallery_id int,
			position int,
			media text,
			meta text
		)default charset=utf8;'
	);
	$pages=dbAll('select id from pages where type="image-gallery"');
	if (count($pages)!=0) {
		if (!is_dir(USERBASE.'/ww.cache')) {
			mkdir(USERBASE.'/ww.cache');
		}
		if (!is_dir(USERBASE.'/ww.cache/image-gallery')) {
			mkdir(USERBASE.'/ww.cache/image-gallery');
		}
		$kfm_do_not_save_session=true;
		require_once KFM_BASE_PATH.'/api/api.php';
		require_once KFM_BASE_PATH.'/initialise.php';
		$new_images=array();
		foreach ($pages as $page) {
			$vars=dbAll(
				'select value from page_vars where page_id='
				.$page['id'].' and name="image_gallery_directory" or name="gallery-'
				.'template"'
			);
			if (!$vars) {
				break;
			}
			$image_dir=$vars[0]['value'];
			$dir=preg_replace('/^\//', '', $image_dir);
			$dir_id=kfm_api_getDirectoryID($dir);
			$images=kfm_loadFiles($dir_id);
			$n=count($images);
			if ($n==0) {
				break;
			}
			for ($i=0;$i<count($images['files']);++$i) {
				$new=array(
					'id'=>$images['files'][$i]['id'],
					'gallery_id'=>$page['id'],
					'position'=>$i,
					'media'=>'image',
					'meta'=>addslashes(
						json_encode(
							array(
								'name'=>$images['files'][$i]['name'],
								'width'=>$images['files'][$i]['width'],
								'height'=>$images['files'][$i]['height'],
								'caption'=>$images['files'][$i]['caption']
							)
						)
					)
				);
				array_push($new_images, $new);
			}
			file_put_contents(
				USERBASE.'/ww.cache/image-gallery/'.$page['id'],
				@$vars[1]['value']
			);
		}
		$query='insert into image_gallery (id,gallery_id,position,media,meta) values';
		foreach ($new_images as $image) {
			$query.=' ('
				.$image['id'].','
				.$image['gallery_id'].','
				.$image['position'].','
				.'"'.$image['media'].'",'
				.'"'.$image['meta'].'"'
			.'),';
		}
		$query=substr($query, 0, -1); // remove last comma
		dbQuery($query);
	}
	$version=2;
}
if ($version==2) { // add support for image gallery widget
	dbQuery(
		'create table image_gallery_widget (
			id int primary key auto_increment,
			directory text,
			gallery_type text,
			thumbsize int,
			image_size int,
			rows int,
			columns int
		)default charset=utf8;'
	);
	$version=3;
}
