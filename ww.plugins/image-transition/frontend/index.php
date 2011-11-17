<?php
function ImageTransition_show($vars) {
	if (!is_array($vars) && isset($vars->id) && $vars->id) {
		$r=Core_cacheLoad('image-transitions', 'id'.$vars->id);
		if ($r===false) {
			$r=dbRow('select * from image_transitions where id='.$vars->id);
			Core_cacheSave('image-transitions', 'id'.$vars->id, $r);
		}
		if ($r && is_array($r)) {
			$width=(int)$r['width'];
			$height=(int)$r['height'];
			$imgs=array();
			$dir=USERBASE.'f'.$r['directory'];
			if (!file_exists($dir) || !is_dir($dir)) {
				return '<!-- '.$dir.' -->';
			}
			$fs=new DirectoryIterator($dir);
			$max=array($width, $height);
			foreach ($fs as $f) {
				if ( $f->isDot()
					|| !preg_match('/\.(jpg|.jpeg|png|gif)$/i', $f->getFilename())
				) {
					continue;
				}
				list($iw, $ih) = getimagesize(
					USERBASE.'f'.$r['directory'].'/'.$f->getFilename()
				);
				if (!$iw || !$ih) { // not an image
					continue;
				}
				if (!$width) { // no size predefined
					if ($iw>$max[0]) {
						$max[0]=$iw;
					}
					if ($ih>$max[1]) {
						$max[1]=$ih;
					}
				}
				$imgs[]=$f->getFilename();
			}
			$width=$max[0];
			$height=$max[1];
			asort($imgs);
			if (!count($imgs)) {
				return '<em>no images in selected directory</em>';
			}
			if ($r['trans_type']=='3dCarousel') {
				$html.='<div id="k3dCarousel'.$vars->id.'" style="height:'
					.($height+30).'px"><img style="display:none" src="'
					.'/a/f=getImg/w='.$width.'/h='.$height
					.$r['directory'].'/'
					.join(
						'" /><img style="display:none" src="'
						.'/a/f=getImg/w='.$width.'/h='.$height
						.$r['directory'].'/',
						$imgs
					)
					.'" />';
				$html.='</div>';
				WW_addScript(
					'/ww.plugins/image-transition/frontend/k3dCarousel/'
					.'jquery.k3dCarousel.js'
				);
				WW_addInlineScript('$(window).load(function(){
					$("#k3dCarousel'.$vars->id.'").k3dCarousel();
				});');
			}
			else {
				if ($r['url']) {
					$url=PAGE::getInstance($r['url'])->getRelativeUrl();
					$html.='<a href="'.$url.'"';
				}
				else {
					$html.='<div';
				}
				$html.=' style="display:block;width:'.$width.'px;height:'.$height
					.'px;" id="image_transitions_'.$vars->id.'">'
					.'<img src="/a/f=getImg/w='.$width.'/h='.$height
					.$r['directory'].'/'
					.join(
						'" /><img style="display:none" src="/a/f=getImg/w='
						.$width.'/h='.$height
						.$r['directory'].'/',
						$imgs
					).'" />';
				if ($r['url']) {
					$html.='</a>';
				}
				else {
					$html.='</div>';
				}
				WW_addScript(
					'/ww.plugins/image-transition/frontend/jquery.cycle.all.min.js'
				);
				WW_addInlineScript('$(window).load(function(){$("#image_transitions_'
					.$vars->id.'").cycle({fx:"'.$r['trans_type'].'",speed:'
					.$r['pause'].'})});');
			}
			return $html;
		}
	}
	return '<p>this Image Transition is not yet defined.</p>';
}
