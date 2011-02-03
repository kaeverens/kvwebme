<?php
function show_content_snippet($vars){
	if(!is_array($vars) && isset($vars->id) && $vars->id){
		$data=cache_load('content_snippets',$vars->id.'-data');
		if($data===false){
			$data=dbRow('select * from content_snippets where id='.$vars->id,'content');
			$data['content']=json_decode($data['content'],true);
			cache_save('content_snippets',$vars->id.'-data',$data);
		}
		if(!is_array($data['content']) || !count($data['content'])) {
			return '<p>this Content Snippet is not yet defined.</p>';
		}

		// { no sub-pages
		if (!$data['accordion']) {
			return '<div class="content-snippet">'
				.$data['content'][0]['html'].'</div>';
		}
		// }
		// { vertical accordion
		$id='cs-'.rand();
		if($data['accordion_direction']=='0'){
			$html='<div id="'.$id.'">';
			foreach($data['content'] as $content){
				$html.='<h3><a href="#">'.htmlspecialchars($content['title']).'</a></h3>';
				$html.='<div>'.$content['html'].'</div>';
			}
			$html.='</div><script>$(function(){$("#'.$id.'").accordion({autoHeight:false});});</script>';
			return $html;
		}
		// }
		// { horizontal accordion
		WW_addScript('/ww.plugins/content-snippet/frontend/jquery.hrzAccordion.js');
		WW_addCss('/ww.plugins/content-snippet/frontend/jquery.hrzAccordion.defaults.css');
		$html='<ul class="hrzAccordion" id="'.$id.'">';
		$imgs=array();
		if($data['images_directory'] && file_exists(USERBASE.'f'.$data['images_directory'])){
			$dir=new DirectoryIterator(USERBASE.'f'.$data['images_directory']);
			foreach($dir as $file){
				if($file->isDot())continue;
				$imgs[]='/f'.$data['images_directory'].'/'.$file->getFilename();
			}
		}
		sort($imgs);
		$i=0;
		foreach($data['content'] as $content){
			$html.='<li><div class="handle">';
			if(count($imgs) && isset($imgs[$i])) {
				$size=getimagesize(USERBASE.$imgs[$i]);
				$html.='<img src="'.htmlspecialchars($imgs[$i]).'" style="width:'.$size[0].'px;height:'.$size[1].'px" />';
			}
			else {
				$html.=htmlspecialchars($content['title']);
			}
			$html.='</div>';
			$html.=$content['html'].'</li>';
			++$i;
		}
		$html.='</ul><script>$(function(){$("#'.$id.'").hrzAccordion({handlePosition:"left",cycle:true,cycleInterval:4000});});</script>';
		return $html;
		return $data['accordion_direction'];
		// }
	}
	return '<p>this Content Snippet is not yet defined.</p>';
}
