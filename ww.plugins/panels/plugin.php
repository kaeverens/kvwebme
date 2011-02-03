<?php
$plugin=array(
	'name'=>'Panels',
	'description'=>'Allows content sections to be displayed throughout the site.',
	'admin'=>array(
		'menu'=>array(
			'Misc>Panels'=>'index'
		)
	),
	'frontend'=>array(
		'template_functions'=>array(
			'PANEL'=>array(
				'function' => 'panels_show'
			)
		)
	),
	'version'=>4
);
function panels_show($vars){
	$name=isset($vars['name'])?$vars['name']:'';
	// { load panel data
	$p=cache_load('panels',md5($name));
	if($p===false){
		$p=dbRow('select visibility,disabled,body from panels where name="'.addslashes($name).'" limit 1');
		if(!$p){
			dbQuery("insert into panels (name,body) values('".addslashes($name)."','{\"widgets\":[]}')");
			return '';
		}
		cache_save('panels',md5($name),$p);
	}
	// }
	// { is the panel visible?
	if($p['disabled'])return '';
	if($p['visibility'] && $p['visibility']!='[]'){
		$visibility=json_decode($p['visibility']);
		if(!in_array($GLOBALS['PAGEDATA']->id,$visibility))return '';
	}
	// }
	// { get the panel content
	$widgets=json_decode($p['body']);
	if(!count($widgets->widgets))return '';
	// }
	// { show the panel content
	$h='';
	global $PLUGINS;
	foreach($widgets->widgets as $widget){
		if(isset($widget->disabled) && $widget->disabled)continue;
		if(isset($widget->visibility) && count($widget->visibility)){
			if(!in_array($GLOBALS['PAGEDATA']->id,$widget->visibility))continue;
		}
		if(isset($widget->header_visibility) && $widget->header_visibility)$h.='<h4 class="panel-widget-header '.preg_replace('/[^a-z0-9A-Z\-]/','',$widget->name).'">'.htmlspecialchars($widget->name).'</h4>';
		if(isset($PLUGINS[$widget->type])){
			if(isset($PLUGINS[$widget->type]['frontend']['widget'])){
				$h.=$PLUGINS[$widget->type]['frontend']['widget']($widget);
			}
			else $h.='<em>plugin "'.htmlspecialchars($widget->type).'" does not have a widget interface.</em>';
		}
		else $h.='<em>missing plugin "'.htmlspecialchars($widget->type).'".</em>';
	}
	// }
	if($h=='')return '';
	$name=preg_replace('/[^a-z0-9\-]/','-',$name);
	return '<div class="panel panel-'.$name.'">'.$h.'</div>';
}
