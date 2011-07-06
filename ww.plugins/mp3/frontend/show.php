<?php
function mp3_frontend_widget($vars=null){
	$files=json_decode(
		dbOne('select fields from mp3_plugin where id='.$vars->id,'fields')
	,true);
	if(count($files)==0)
		return 'No files yet';
	// { display the template
	require_once SCRIPTBASE.'ww.incs/Smarty-2.6.26/libs/Smarty.class.php';
	$smarty=new Smarty;
	$smarty->compile_dir=USERBASE.'/ww.cache/templates_c';
	if(!file_exists(USERBASE.'/ww.cache/templates_c'))
		mkdir(USERBASE.'/ww.cache/templates_c'); 
	if(!file_exists(USERBASE.'/ww.cache/templates_c/image-gallery'))
		mkdir(USERBASE.'/ww.cache/templates_c/image-gallery');
	$smarty->register_function('LIST','mp3_list');
	$smarty->register_function('PLAY','mp3_play');
	$smarty->register_function('PROGRESS','mp3_progress');
	$smarty->left_delimiter='{{';
	$smarty->right_delimiter='}}';
	$smarty->_tpl_vars['mp3_files']=$files;
	$html=$smarty->fetch(
		USERBASE.'ww.cache/mp3/'.$vars->id
	);
	WW_addScript('/ww.plugins/mp3/frontend/jwplayer.js');
	WW_addScript('/ww.plugins/mp3/frontend/widget.js');
	// }
	return $html;
}
function mp3_list($params,&$smarty){
	$files=$smarty->_tpl_vars['mp3_files'];
	$opts=array(
		'play_button'=>false,
		'link_to_play'=>false,
	);
	$opts=$params+$opts;
	$link=($opts['link_to_play'])?' link_to_play="true"':'';
	$play=($opts['play_button'])?' play_button="true"':'';
	echo '<ul class="mp3_playlist"'.$link.$play.'>';
	foreach($files as $file){
		echo '<li>';
		if($opts['play_button']){
			echo '<a class="play_button" href="';
			echo $file['location'].'">';
			echo 'Play</a>';
		}
		if($opts['link_to_play']){
			echo '<a href="'.$file['location'].'" class="link_to_play">';
			echo $file['name'].'</a>';
		}
		else
			echo $file['name'];
		echo '</li>';
	}
	echo '</ul>';
}
function mp3_play($params,&$smarty){
	$opts=array(
		'image_link'=>'false',
	);
	$opts=$params+$opts;
	$image=($opts['image_link'])?' image':'';
	echo '<a href="javascript:;" class="mp3_play_link'.$image.'">';
	if($opts['image_link'])
		echo '&nbsp;';
	else
		echo 'PLAY';
	echo '</a>';
}
function mp3_progress($params,&$smarty){
	$opts=array(
		'show_times'=>false,
	);	
	$opts=$params+$opts;
	$times=($opts['show_times'])?' times="true"':'';
	echo '<div class="progress_wrapper">';
	echo '<div class="mp3_progress"'.$times.'></div>';
	if($opts['show_times']){
		echo	'<div class="mp3_duration">00:00</div>';
		echo	'<div class="mp3_position">00:00</div>';
		echo	'</div>';
	}
}
