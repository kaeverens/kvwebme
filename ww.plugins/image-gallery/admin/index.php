<?php
/**
	* admin area for ImageGallery plugin
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

$cssurl=false;
// { form header
$c = '<div id="image-gallery-tabs">'
	.'<ul>'
	.'<li><a href="#image-gallery-images">Images</a></li>'
	.'<li><a href="#image-gallery-template">Gallery Template</a></li>'
	.'<li><a href="#image-gallery-header">Header</a></li>'
	.'<li><a href="#image-gallery-footer">Footer</a></li>'
	.'<li><a href="#image-gallery-frames">Frames</a></li>'
	.'<li><a href="#image-gallery-advanced">Advanced Settings</a></li>'
	.'</ul>';
// }
// { images
$c.='<div id="image-gallery-images">';
// { image gallery directory - if not exists create it
$c.='<a href="javascript:;" style="float:right" id="video">Add Video File</a>';
if (!isset($vars['image_gallery_directory'])
	|| !$vars['image_gallery_directory']
	|| !is_dir(USERBASE.'/f/'.$vars['image_gallery_directory'])
) {
	if (!is_dir(USERBASE.'/f/image-galleries')) {
		mkdir(USERBASE.'/f/image-galleries');
	}
	$vars['image_gallery_directory']='/image-galleries/imagegallery-'.$page['id'];
	$dir=USERBASE.'/f/'.$vars['image_gallery_directory'];
	@mkdir($dir);
}
$c.='<input type="hidden" name="page_vars[image_gallery_directory]" value="';
$c.=$vars['image_gallery_directory'].'"/>';
// }
// { uploader
WW_addScript('image-gallery/files/swfobject.js');
$c.='<div id="upload">'
	.'<input type="file" name="file_upload" id="uploader"/>'
	.'</div>';
// }
$images=dbAll(
	'select * from image_gallery where gallery_id='.$page['id']
	.' order by position asc'
);
// { find images that are not in the database...
$dir=USERBASE.'/f/'.$vars['image_gallery_directory'];
$notfound=array();
$files=new DirectoryIterator($dir);
foreach ($files as $file) {
	if ($file->isDot()) {
		continue;
	}
	$notfound[]=$file->getFilename();
}
$highestposition=0;
foreach ($images as $k=>$image) {
	$images[$k]['meta']=json_decode($images[$k]['meta'], true);
	$name=$images[$k]['meta']['name'];
	if (in_array($name, $notfound)) {
		unset($notfound[array_search($name, $notfound)]);
	}
	if (!file_exists($dir.'/'.$name)) {
		dbQuery('delete from image_gallery where id='.$image['id']);
		unset($images[$k]);
	}
	if ((int)$image['position']>$highestposition) {
		$highestposition=(int)$image['position'];
	}
}
if (count($notfound)) {
	foreach ($notfound as $image) {
		$dimensions=getimagesize($dir.'/'.$image);
		$meta=json_encode(
			array(
				'width'=>$dimensions[0],
				'height'=>$dimensions[1],
				'name'=>$image,
				'caption'=>''
			)
		);
		$query='insert into image_gallery (gallery_id,position,media,meta) values'
			.'('.$page['id'].','.(++$highestposition).',"image","'
			.addslashes($meta).'")';
		dbQuery($query);
	}
}
// }
$n=count($images);
$c.='<ul id="image-gallery-wrapper">';
if ($n) {
	for ($i=0;$i<$n;$i++) {
		$id=$images[$i]['id'];
		$meta=$images[$i]['meta'];
		switch ($images[$i]['media']) {
			case 'image': // {
				$caption=isset($meta['caption'])&&$meta['caption']!=''?
					' title="'.$meta['caption'].'"':
					'';
				$c.='<li id="image_'.$id.'">'
					.'<img alt="" src="/a/f=getImg/w=64/h=64/'
					.$vars['image_gallery_directory'].'/'.urlencode($meta['name']).'"'
					.$caption.' id="image-gallery-image'.$id.'"/><br/>'
					.'<a href="javascript:;" class="edit-img" id="'.$id.'">'
					.__('edit').'</a> or '
					.'<a href="javascript:;" class="delete-img" id="'.$id.'">'
					.'[x]</a>'
					.'</li>';
			break; // }
			case 'video': // {
				$image=($meta['image']=='')?
					'/ww.plugins/image-gallery/files/video.png':
					'/a/f=getImg/w=64/h=64/base64='.base64_encode($meta['image']);
				$c.='<li id="image_'.$id.'">'
					.'<img alt="" src="'.$image.'"'
					.' id="image-gallery-image'.$id.'"/><br/>'
					.'<a href="javascript:;" class="delete-img" id="'.$id.'">'
					.'[x]</a></li>';
			break; // }
		}
	}
}
else {
	$c.='<em class="error">no images yet. please upload some.</em>';
}
$c.='</ul><br style="clear:both"/>';
$c.='</div>';
$c.='<br style="clear:both"/>';
// }
// { gallery template
$types=array(
	'---',
	'List',
	'Grid',
	'Simple',
	'grid-with-wholepage-image'
);
$c.='<div id="image-gallery-template">'
	.'<p>This controls how the gallery is displayed, choose from some of the '
	.'layouts listed below as a starting point. These layouts are only a '
	.'guide, you may change them.</p>'
	.'Gallery Layout: <select'
	.' id="gallery-template-type">';
foreach ($types as $type) {
	$c.='<option value="'.strtolower($type).'">'.$type.'</option>';
}
$c.='</select>';
$c.=' When large image shown: <select name="page_vars[image_gallery-hide-sidebar]" val="'
	.(@$vars['image_gallery-gallery-hide-sidebar']).'">'
	.'<option value="">do nothing</option><option value="1"';
if (@$vars['image_gallery-hide-sidebar']) {
	$c.=' selected="selected"';
}
$c.='>hide sidebar</option></select>';
$content=@$vars['gallery-template'];
$c.=ckeditor('page_vars[gallery-template]', $content, 0);
$c.='</div>';
// }
// { header
$c.='<div id="image-gallery-header">'
	.'<p>This text will appear above the gallery.</p>';
$c.=ckeditor('body', $page['body'], 0, $cssurl);
$c.='</div>';
// }
// { footer
$c.='<div id="image-gallery-footer">'
	.'<p>This text will appear below the gallery.</p>'
	.ckeditor(
		'page_vars[footer]',
		(isset($vars['footer'])?$vars['footer']:''),
		0,
		$cssurl
	);
$c.='</div>';
// }
// { frames
$c.='<div id="image-gallery-frames">'
	.'<p>Picture frames for your images.</p>'
	.'<select id="val-image_gallery_frame" name="page_vars[image_gallery_frame]">'
	.'<option value=""> -- none -- </option>';
$frames=new DirectoryIterator(dirname(__FILE__).'/../frames');
foreach ($frames as $frame) {
	if ($frame->isDot() || $frame->getFilename()=='.svn') {
		continue;
	}
	$fname=$frame->getFilename();
	$c.='<option';
	if ($fname==@$vars['image_gallery_frame']) {
		$c.=' selected="selected"';
	}
	$c.='>'.$fname.'</option>';
}
$c.='<option';
if ('--custom--'==@$vars['image_gallery_frame']) {
	$c.=' selected="selected"';
}
$c.='>--custom--</option>';
$c.='</select>';
// { custom
$c.='<div id="image-gallery-frame-custom">'
	.'<input type="file" id="frame-uploader"/><table><tr><td>';
// { borders
if (!isset($vars['image_gallery_frame_custom_border'])) {
	$vars['image_gallery_frame_custom_border']='10 10 10 10';
}
$borders=explode(' ', $vars['image_gallery_frame_custom_border']);
$c.='<input name="page_vars[image_gallery_frame_custom_border]" value="'
	.$vars['image_gallery_frame_custom_border'].'" type="hidden"/>'
	.'<table>'
	.'<tr><th>Border width top</th><td><input class="border" value="'
	.$borders[0].'"/></td></tr>'
	.'<tr><th>Border width right</th><td><input class="border" value="'
	.$borders[1].'"/></td></tr>'
	.'<tr><th>Border width bottom</th><td><input class="border" value="'
	.$borders[2].'"/></td></tr>'
	.'<tr><th>Border width left</th><td><input class="border" value="'
	.$borders[3].'"/></td></tr>'
	.'</table>';
// }
$c.='</td><td>';
// { paddings
if (!isset($vars['image_gallery_frame_custom_padding'])) {
	$vars['image_gallery_frame_custom_padding']='10 10 10 10';
}
$paddings=explode(' ', $vars['image_gallery_frame_custom_padding']);
$c.='<input name="page_vars[image_gallery_frame_custom_padding]" value="'
	.$vars['image_gallery_frame_custom_padding'].'" type="hidden"/>'
	.'<table>'
	.'<tr><th>Padding width top</th><td><input class="padding" value="'
	.$paddings[0].'"/></td></tr>'
	.'<tr><th>Padding width right</th><td><input class="padding" value="'
	.$paddings[1].'"/></td></tr>'
	.'<tr><th>Padding width bottom</th><td><input class="padding" value="'
	.$paddings[2].'"/></td></tr>'
	.'<tr><th>Padding width left</th><td><input class="padding" value="'
	.$paddings[3].'"/></td></tr>'
	.'</table>';
// }
$c.='</td><td><b>350x350 example</b><br /><img src="/i/blank.gif" '
	.'id="fd1" style="width:350px;height:350px;background:url('
	.'/ww.plugins/image-gallery/i/frame-demo.jpg) no-repeat;" alt=""/>'
	.'</td><td><b>150x150 example</b><br /><img src="/i/blank.gif" '
	.'id="fd2" style="width:150px;height:150px;background:url('
	.'/ww.plugins/image-gallery/i/frame-demo-small.jpg) no-repeat;" alt=""/>'
	;
$c.='</td></tr></table></div>';
// }
$c.='</div>';
// }
// { advanced settings
$c.='<div id="image-gallery-advanced">';
$c.='<table>';
// { columns
$c.='<tr><th>Columns</th><td>'
	.'<input name="page_vars[image_gallery_x]" value="'
	.((isset($vars['image_gallery_x']))?$vars['image_gallery_x']:3).'" /></td>';
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
	.((isset($vars['image_gallery_y']))?$vars['image_gallery_y']:2).'" /></td>';
// }
// { main image width
$width=(int)@$vars['image_gallery_image_x'];
$width=($width==0)?350:$width;
$c.='<th>Main Image Width</th>';
$c.='<td><input name="page_vars[image_gallery_image_x]"';
$c.=' value="'.$width.'"/>';
// }
// { thumbnail size
$ts=@$vars['image_gallery_thumbsize'];
$ts=$ts?$ts:150;
$c.='<tr><th>Thumb Size</th><td>'
	.'<input name="page_vars[image_gallery_thumbsize]" value="'
	.htmlspecialchars($ts).'" title="examples: 100, 100x80"/></td>';
// }
// { main image effects
$effects=array(
	'fade',
	'slideVertical',
	'slideHorizontal',
);
$width=($width==0)?350:$width;
$c.='<th>Main Image Effects</th>';
$c.='<td><select name="page_vars[image_gallery_effect]">';
foreach ($effects as $effect) {
	$c.='<option';
	if ($effect==@$vars['image_gallery_effect']) {
		$c.=' selected="selected"';
	}
	$c.='>'.$effect.'</option>';
}
$c.='</select></td></tr>';
// }
// { hover
$options=array(
	'none'=>__(' -- none -- '),
	'opacity'=>__('Opacity'),
	'zoom'=>__('Zoom'),
	'popup'=>__('Popup')
);
$c.='<tr><th>Effect when image is hovered:</th>';
$c.='<td><select name="page_vars[image_gallery_hover]">';
foreach ($options as $value=>$option) {
	$c.='<option value="'.$value.'"';
	if ($value==@$vars['image_gallery_hover']) {
		$c.=' selected="selected"';
	}
	$c.='>'.$option.'</option>';
}
$c.='</select></td>';
// }
// { ratio crop/normal
$options=array('normal'=>'Normal','crop'=>'Crop');
$c.='<th>Thumbnail Ratio:</th>';
$c.='<td><select name="page_vars[image_gallery_ratio]">';
foreach ($options as $value=>$option) {
	$c.='<option value="'.$value.'"';
	if ($value==@$vars['image_gallery_ratio']) {
		$c.=' selected="selected"';
	}
	$c.='>'.$option.'</option>';
}
$c.='</select></td></tr>';
// }
// { slideshow
$options=array('false'=>'No','true'=>'Yes');
$c.='<tr><th>Autostart Slide-show:</th>';
$c.='<td><select name="page_vars[image_gallery_autostart]">';
foreach ($options as $value=>$option) {
	$c.='<option value="'.$value.'"';
	if ($value==@$vars['image_gallery_autostart']) {
		$c.=' selected="selected"';
	}
	$c.='>'.$option.'</option>';
}
$time=(isset($vars['image_gallery_slidedelay']))?
	$vars['image_gallery_slidedelay']:
	2500;
$c.='</select></td>';
$c.='<th>Slide delay:</th>';
$c.='<td><input type="text" name="page_vars[image_gallery_slidedelay]" ';
$c.=' value="'.$time.'"/> ms</td></tr>';
// }
// { show links
$options=array('true'=>'Yes','false'=>'No');
$c.='<tr><th>Show Prev/Next links:</th>';
$c.='<td><select name="page_vars[image_gallery_links]">';
foreach ($options as $value=>$option) {
	$c.='<option value="'.$value.'"';
	if ($value==@$vars['image_gallery_links']) {
		$c.=' selected="selected"';
	}
	$c.='>'.$option.'</option>';
}
$c.='</select></td>';
// }
// { show captions in slider
$c.='<th>Show Captions in Slider</th><td>'
	.'<select name="page_vars[image_gallery_captions_in_slider]">'
	.'<option>No</option><option value="1"';
if ('1'==@$vars['image_gallery_captions_in_slider']) {
	$c.=' selected="selected"';
}
$c.='">Yes</option></select></td></tr>';
// }
$c.='<tr><th>Gallery Container Width:</th>';
$c.='<td><input type="text" name="page_vars[image_gallery_width]" ';
$c.=' value="'.@$vars['image_gallery_width'].'"/></td>';
$c.='<td colspan="2"><i>If left blank this value will be calculated manually, this is '
	.'the recommended method.</i></td>';
$c.='</tr>';
// }
// }
// { form footer
$c.='</table>';
$c.='</div>';
$c.='</div>';
if (!is_dir(USERBASE.'/ww.cache/image-gallery')) {
	mkdir(USERBASE.'/ww.cache/image-gallery');
}
if (file_exists(USERBASE.'/ww.cache/image-gallery/'.$page['id'])) {
	unlink(USERBASE.'/ww.cache/image-gallery/'.$page['id']);
} 
file_put_contents(
	USERBASE.'/ww.cache/image-gallery/'.$page['id'],
	@$vars['gallery-template']
);
ww_addScript('/ww.plugins/image-gallery/admin/admin.js');
WW_addCSS('/ww.plugins/image-gallery/admin/admin.css');
// }
