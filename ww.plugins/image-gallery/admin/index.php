<?php
$kfm_do_not_save_session=true;
require_once KFM_BASE_PATH.'/api/api.php';
require_once KFM_BASE_PATH.'/initialise.php';
function image_gallery_get_subdirs($base,$dir){
	$arr=array();
	$D=new DirectoryIterator($base.$dir);
	$ds=array();
	foreach($D as $dname){
		$d=$dname.'';
		if($d{0}=='.')continue;
		if(!is_dir($base.$dir.'/'.$d))continue;
		$ds[]=$d;
	}
	asort($ds);
	foreach($ds as $d){
		$arr[]=$dir.'/'.$d;
		$arr=array_merge($arr,image_gallery_get_subdirs($base,$dir.'/'.$d));
	}
	return $arr;
}
$cssurl=false;
$c = '<div id="image-gallery-tabs">';
$c.= '<ul>';
$c.= '<li><a href="#image-gallery-images">Images</a></li>';
$c.= '<li><a href="#image-gallery-template">Gallery Template</a></li>';
$c.= '<li><a href="#image-gallery-header">Header</a></li>';
$c.= '<li><a href="#image-gallery-footer">Footer</a></li>';
$c.= '<li><a href="#image-gallery-advanced">Advanced Settings</a></li>';
if(isset($GLOBALS['PLUGINS']['online-store'])){
	$c.='<li><a href="#image-gallery-shop">Online Store</a></li>';
}
$c.= '</ul>';
// { images
$c.='<div id="image-gallery-images">';
if (!$vars['image_gallery_directory']||!is_dir(USERBASE.'f/'.$vars['image_gallery_directory'])){
	if(!is_dir(USERBASE.'f/image-galleries')) {
		mkdir(USERBASE.'f/image-galleries');
	}
	$invalid='/[^A-Za-z0-9_\-]/';
	$p=Page::getInstance($page['id']);
	$name=preg_replace($invalid, '_', $p->getRelativeUrl());
	$vars['image_gallery_directory']='/image-galleries/page-'.$name;
	$dir=USERBASE.'f/'.$vars['image_gallery_directory'];
	if (!file_exists($dir)){
		mkdir($dir);
	}
}
$dir=preg_replace('/^\//','',$vars['image_gallery_directory']);
$dir_id=kfm_api_getDirectoryID($dir);
$images=kfm_loadFiles($dir_id);
$images=$images['files'];
$n=count($images);
$c.='<iframe src="/ww.plugins/image-gallery/admin/uploader.php'
	.'?image_gallery_directory='.urlencode($vars['image_gallery_directory']).'"'
	.' style="width:400px;height:50px;border:0;overflow:hidden">'
	.'</iframe>'
	.'<script>window.kfm={alert:function(){}};window.kfm_vars={};'
	.'function x_kfm_loadFiles(){}function kfm_dir_openNode(){'
	.'document.location="/ww.admin/pages/form.php?id="+window.page_menu_currentpage;}'
	.'</script>';
if($n){
	$c.='<div id="image-gallery-wrapper">';
	for($i=0;$i<$n;$i++){
		$c.='<div><img src="/kfmget/'.$images[$i]['id'].','
		.'width=64,height=64" id="image-gallery-image'.$images[$i]['id'].'" '
		.'title="'.str_replace('\\\\n','<br />',$images[$i]['caption']).'" />'
		.'<br />'
		.'<input type="checkbox" '
		.'id="image-gallery-dchk-'.$images[$i]['id'].'" />'
		.'<a href="javascript:;" id="image-gallery-dbtn-'.$images[$i]['id'].'"'
		.'class="image-gallery-delete-link">delete</a><br />'
		.'<a href="javascript:;" caption="'.$images[$i]['caption'].'"'
		.'class="image-gallery-caption-link" id="image-gallery-caption-link-'
		.$images[$i]['id'].'">';
		if (isset($images[$i]['caption'])&&!empty($images[$i]['caption'])) {
			$c.='Edit Caption';
		}
		else {
			$c.='Add Caption';
		}
		$c.='</a></div>';
	}
	$c.='</div>';
}
else{
	$c.='<em>no images yet. please upload some.</em>';
}
$c.='</div>';
// }
// { gallery template
$types=array(
	'List',
	'Grid',
	'Simple',
	'Custom',
);
$c.='<div id="image-gallery-template">'
	.'<p>This controls how the gallery is displayed, choose from some of the layouts listed below.</p>'
	.'Gallery Layout: <select name="page_vars[gallery-template-type]'
	.'" id="gallery-template-type">';
foreach($types as $type){
	$c.='<option';
	if(@$vars['gallery-template-type']==strtolower($type))
		$c.=' selected="selected"';
	$c.=' value="'.strtolower($type).'">'.$type.'</option>';
}
$c.='</select><br/>';
if($vars['gallery-template-type']=='')
	$vars['gallery-template-type']='list';
if($vars['gallery-template-type']=='custom'){
	$content=(@$vars['gallery-template']=='')?
		'{{GALLERY_IMAGE}}{{GALLERY_IMAGES}}':
		$vars['gallery-template'];
}
else
	$content=file_get_contents(
		SCRIPTBASE
		.'ww.plugins/image-gallery/admin/types/'.$vars['gallery-template-type'].'.tpl');
$c.=ckeditor('page_vars[gallery-template]',$content,0);
$c.='</div>';
// }
// { header
$c.='<div id="image-gallery-header">'
	.'<p>This text will appear above the gallery.</p>';
$c.=ckeditor('body',$page['body'],0,$cssurl);
$c.='</div>';
// }
// { footer
$c.='<div id="image-gallery-footer">'
	.'<p>This text will appear below the gallery.</p>';
$c
	.=ckeditor(
		'page_vars[footer]',
		(isset($vars['footer'])?$vars['footer']:''),
		0,
		$cssurl
	);
$c.='</div>';
// }
// { advanced settings
$c.='<div id="image-gallery-advanced">';
if(
	!isset($vars['image_gallery_directory']) 
	|| !$vars['image_gallery_directory']
) {
	$vars['image_gallery_directory']='/';
}
$c.='<table><tr><th>Image Directory</th>'
	.'<td><select id="image_gallery_directory" '
	.'name="page_vars[image_gallery_directory]">'
	.'<option value="'.htmlspecialchars($vars['image_gallery_directory']).'">'
	.htmlspecialchars($vars['image_gallery_directory']).'</option>';
foreach(image_gallery_get_subdirs(USERBASE.'f','') as $d){
	$c.='<option value="'.htmlspecialchars($d).'"';
	if($d==$vars['image_gallery_directory'])$c.=' selected="selected"';
	$c.='>'.htmlspecialchars($d).'</option>';
}
$c.='</select></td>';
$c.='<td colspan="2">'
	.'<a style="background:#ff0;font-weight:bold;color:red;display:block;'
	.'text-align:center;" href="#page_vars[image_gallery_directory]" '
	.'onclick="javascript:window.open(\'/j/kfm/'
	.'?startup_folder=\'+$(\'#image_gallery_directory\').attr(\'value\'),'
	.'\'kfm\',\'modal,width=800,height=600\');">Manage Images</a></td></tr>';
// { columns
$c.='<tr><th>Columns</th><td>'
	.'<input name="page_vars[image_gallery_x]" value="'
	.(int)$vars['image_gallery_x'].'" /></td>';
// }
// { main image height
$height=(int)@$vars['image_gallery_image_y'];
$height=($height==0)?350:$height;
$c.='<th>Main Image Height</th>';
$c.='<td><input name="page_vars[image_gallery_image_y]"';
$c.=' value="'.$height.'"/>';
// }
// { rows
$c.='<tr><th>Rows</th><td>'
	.'<input name="page_vars[image_gallery_y]" value="'
	.(int)$vars['image_gallery_y'].'" /></td>';
// }
// { main image width
$width=(int)@$vars['image_gallery_image_x'];
$width=($width==0)?350:$width;
$c.='<th>Main Image Width</th>';
$c.='<td><input name="page_vars[image_gallery_image_x]"';
$c.=' value="'.$width.'"/>';
// }
// { thumbnail size
$ts=(int)$vars['image_gallery_thumbsize'];
$ts=$ts?$ts:150;
$c.='<tr><th>Thumb Size</th><td>'
	.'<input name="page_vars[image_gallery_thumbsize]" value="'.$ts.'" />'
	.'</td>';
// }
// { main image effects
$effects=array(
	'fade',
	'slideUp',
	'slideDown',
);
$width=($width==0)?350:$width;
$c.='<th>Main Image Effects</th>';
$c.='<td><select name="page_vars[image_gallery_effect]">';
foreach($effects as $effect){
	$c.='<option';
	if($effect==@$vars['image_gallery_effect'])
		$c.=' selected="selected"';
	$c.='>'.$effect.'</option>';
}
$c.='</select></td></tr>';
// }
// { hover
$options=array('popup'=>'Popup','zoom'=>'Zoom');
$c.='<tr><th>Images on hover:</th>';
$c.='<td><select name="page_vars[image_gallery_hover]">';
foreach($options as $value=>$option){
	$c.='<option value="'.$value.'"';
	if($value==@$vars['image_gallery_hover'])
		$c.=' selected="selected"';
	$c.='>'.$option.'</option>';
}
$c.='</select></td></tr>';
// }
$c.='</table>';
$c.='</div>';
// }
$c.='</div>';

if (!is_dir(USERBASE.'ww.cache/image-gallery')) {
  mkdir(USERBASE.'ww.cache/image-gallery');
}
if (file_exists(USERBASE.'ww.cache/image-gallery/'.$page['id'])) {
  unlink(USERBASE.'ww.cache/image-gallery/'.$page['id']);
} 
file_put_contents(
  USERBASE.'ww.cache/image-gallery/'.$page['id'],
  $vars['gallery-template']
);
ww_addScript('/ww.plugins/image-gallery/admin/admin.js');
ww_addScript('/ww.plugins/image-gallery/j/make-tabs.js');
$c.='<link rel="stylesheet" href="/ww.plugins/image-gallery/admin/admin.css" />';
