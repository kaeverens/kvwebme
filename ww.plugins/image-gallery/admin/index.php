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
$gvars=array(
	'image_gallery_directory'    =>'',
	'image_gallery_x'            =>3,
	'image_gallery_y'            =>2,
	'image_gallery_autostart'    =>0,
	'image_gallery_slidedelay'   =>5000,
	'image_gallery_thumbsize'    =>150,
	'image_gallery_captionlength'=>100,
	'image_gallery_type'         =>'ad-gallery'
);
foreach($gvars as $n=>$v)if(isset($vars[$n]))$gvars[$n]=$vars[$n];
$cssurl=false;
$c = '<div id="image-gallery-tabs">';
$c.= '<ul>';
$c.= '<li><a href="#image-gallery-images">Images</a></li>';
$c.= '<li><a href="#image-gallery-header">Header</a></li>';
$c.= '<li><a href="#image-gallery-footer">Footer</a></li>';
$c.= '<li><a href="#image-gallery-advanced">Advanced Settings</a></li>';
if (isset($GLOBALS['PLUGINS']['online-store'])) {
	$c.= '<li><a href="#image-gallery-shop">Online Store</a></li>';
}
$c.= '</ul>';
// { images
$c.='<div id="image-gallery-images">';
$invalid = '/[^A-Za-z0-9_\-]/';
$name = preg_replace($invalid, '|', $page['name']);
if(!$gvars['image_gallery_directory'] || !is_dir(USERBASE.'f/'.$gvars['image_gallery_directory'])){
	if (!is_dir(USERBASE.'f/image-galleries')) {
		mkdir(USERBASE.'f/image-galleries');
	}
	$gvars['image_gallery_directory']='/image-galleries/page-'.$name;
	$dir=USERBASE.'f/'.$gvars['image_gallery_directory'];
	if (!file_exists($dir)) {
		mkdir($dir);
	}
}
$dir=preg_replace('/^\//','',$gvars['image_gallery_directory']);
$dir_id=kfm_api_getDirectoryID($dir);
$images=kfm_loadFiles($dir_id);
$images=$images['files'];
$n=count($images);
$c.='<iframe src="/ww.plugins/image-gallery/admin/uploader.php'
	.'?image_gallery_directory='.urlencode($gvars['image_gallery_directory']).'"'
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
	!isset($gvars['image_gallery_directory']) 
	|| !$gvars['image_gallery_directory']
) {
	$gvars['image_gallery_directory']='/';
}
$c.='<table><tr><th>Image Directory</th>'
	.'<td><select id="image_gallery_directory" '
	.'name="page_vars[image_gallery_directory]">'
	.'<option value="'.htmlspecialchars($gvars['image_gallery_directory']).'">'
	.htmlspecialchars($gvars['image_gallery_directory']).'</option>';
foreach(image_gallery_get_subdirs(USERBASE.'f','') as $d){
	$c.='<option value="'.htmlspecialchars($d).'"';
	if($d==$gvars['image_gallery_directory'])$c.=' selected="selected"';
	$c.='>'.htmlspecialchars($d).'</option>';
}
$c.='</select></td>';
$c.='<td colspan="2">'
	.'<a style="background:#ff0;font-weight:bold;color:red;display:block;'
	.'text-align:center;" href="#page_vars[image_gallery_directory]" '
	.'onclick="javascript:window.open(\'/j/kfm/'
	.'?startup_folder=\'+$(\'#image_gallery_directory\').attr(\'value\'),'
	.'\'kfm\',\'modal,width=800,height=600\');">Manage Images</a></td></tr>';
$c.='<tr><th>'.__('Columns').'</th><td>'
	.'<input name="page_vars[image_gallery_x]" value="'
	.(int)$gvars['image_gallery_x'].'" /></td>';
// { gallery type
$c.='<th>'.__('Gallery Type').'</th><td><select name="page_vars[image_gallery_type]">';
$types=array('ad-gallery','simple gallery');
foreach($types as $t){
	$c.='<option value="'.$t.'"';
	if(isset($gvars['image_gallery_type']) && $gvars['image_gallery_type']==$t)$c.=' selected="selected"';
	$c.='>'.$t.'</option>';
}
$c.='</select></td></tr>';
// }
// { rows
$c.='<tr><th>'.__('Rows').'</th><td>'
	.'<input name="page_vars[image_gallery_y]" value="'
	.(int)$gvars['image_gallery_y'].'" /></td>';
// }
// { autostart the slideshow
$c.='<th>Autostart slide-show</th><td>'
	.'<select name="page_vars[image_gallery_autostart]">'
	.'<option value="0">No</option><option value="1"';
if($gvars['image_gallery_autostart'])$c.=' selected="selected"';
$c.='>Yes</option></select></td></tr>';
// }
// { caption length
$cl=(int)$gvars['image_gallery_captionlength'];
$cl=$cl?$cl:100;
$c.='<tr><th>'.__('Caption Length').'</th><td>'
	.'<input name="page_vars[image_gallery_captionlength]" value="'.$cl.'" />'
	.'</td>';
// }
// { slide delay
$sd=(int)$gvars['image_gallery_slidedelay'];
$c.='<th>Slide Delay</th><td>'
	.'<input name="page_vars[image_gallery_slidedelay]" class="small" '
	.'value="'.$sd.'" />ms</td></tr>';
// }
$ts=(int)$gvars['image_gallery_thumbsize'];
$ts=$ts?$ts:150;
$c.='<tr><th>'.__('Thumb Size').'</th><td>'
	.'<input name="page_vars[image_gallery_thumbsize]" value="'.$ts.'" />'
	.'</td></tr>';
$c.='</table>';
$c.='</div>';
// }
// { online store
if(isset($GLOBALS['PLUGINS']['online-store'])){
	$c.='<div id="image-gallery-shop"><table>';
	// { for sale
	$c.='<tr><th>Are these images for sale?</th><td>'
	.'<select name="page_vars[image_gallery_forsale]">'
	.'<option value="">No</option>';
	$c.='<option value="yes"';
	if(
		isset($vars['image_gallery_forsale']) 
		&& $vars['image_gallery_forsale']=='yes'
	) {
		$c.=' selected="selected"';
	}
	$c.='>Yes</option></select></td></tr>';
	// }
	// { prices
	$c.='<tr><th>Prices</th><td>';
	$ps=array();
	for($i=0;isset($vars['image_gallery_prices_'.$i]);++$i){
		$price=preg_replace('/[^0-9.]/','',$vars['image_gallery_prices_'.$i]);
		if(!((float)$price))continue;
		$ps[]
			=array(
				'description'=>$vars['image_gallery_pricedescs_'.$i],
				'price'=>(float)$price
			);
	}
	for($cnt=0;isset($ps[$cnt]);++$cnt){
		$c.='<input class="medium" '
			.'name="page_vars[image_gallery_pricedescs_'.$cnt.']" '
			.'value="'.htmlspecialchars($ps[$cnt]['description']).'" />'
			.'<input class="ig_price small" '
			.'name="page_vars[image_gallery_prices_'.$cnt.']" '
			.'value="'.$ps[$cnt]['price'].'" /><br />';
		}
	$c.='<input class="medium" '
		.'name="page_vars[image_gallery_pricedescs_'.$cnt.']" '
		.'value="description" />'
		.'<input class="ig_price small" '
		.'name="page_vars[image_gallery_prices_'.$cnt.']" value="0" /><br />';
	$c.='<a id="ig_prices_more" href="javascript:image_gallery_add_price()">'
		.'[more]</a></td></tr>';
	// }
	$c.='</table><script>var ig_price_count='.$cnt.';</script></div>';
}
// }
$c.='</div>';
ww_addScript('/ww.plugins/image-gallery/j/admin.js');
ww_addScript('/ww.plugins/image-gallery/j/make-tabs.js');
$c.='<link rel="stylesheet" href="/ww.plugins/image-gallery/admin/admin.css" />';
