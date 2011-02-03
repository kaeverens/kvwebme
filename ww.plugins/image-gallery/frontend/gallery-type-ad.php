<?php
$c.='<style type="text/css">img.ad-loader{width:16px !important;height:16px !important;}</style><div style="visibility:hidden" class="ad-gallery"> <div class="ad-image-wrapper"> </div> <div class="ad-controls"> </div> <div class="ad-nav"> <div class="ad-thumbs"> <ul class="ad-thumb-list">';
for ($i=0;$i<$n;$i++) {
	$c.='<li> <a href="/kfmget/'.$images[$i]['id'].'"> <img src="/kfmget/'.$images[$i]['id'].',width='.$vars['image_gallery_thumbsize'].',height='.$vars['image_gallery_thumbsize'].'"';
	if (isset($images[$i]['caption'])) {
		$c.=' title="'.str_replace('\\\\n','<br />',$images[$i]['caption']).'"';
	}
	$c.='> </a> </li>';
}
$c.='</ul> </div> </div> </div>';
WW_addScript('/ww.plugins/image-gallery/j/ad-gallery/jquery.ad-gallery.pack.js');
WW_addCSS('/ww.plugins/image-gallery/j/ad-gallery/jquery.ad-gallery.css');
$c.='<style type="text/css">.ad-gallery .ad-image-wrapper{	height: 400px;}</style><script>
$(function(){
	$(".ad-gallery").adGallery({
		animate_first_image:true,
		callbacks:{
			"init":function(){
				$("div.ad-gallery").css("visibility","visible");
			}
		},
		loader_image:"/i/throbber.gif",
		slideshow:{';
$slideshowvars=array();
if(isset($vars['image_gallery_autostart']) && $vars['image_gallery_autostart']){
	$slideshowvars[]='enable:true';
	$slideshowvars[]='autostart:true';
}
$sp=(isset($vars['image_gallery_slidedelay']))?(int)$vars['image_gallery_slidedelay']:0;
if($sp)$slideshowvars[]='speed:'.$sp;
$c.=join(',',$slideshowvars);
$c.='}
	});
});</script>';
