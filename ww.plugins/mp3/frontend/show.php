<?php
/**
	* MP3 plugin functions
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Conor Mac Aoidh <conor.macaoidh@gmail.com>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

// { MP3_frontendWidget

/**
	* show frontend widget
	*
	* @param array $vars settings
	*
	* @return string html
	*/
function MP3_frontendWidget($vars=null) {
	$db=dbRow('select fields,template from mp3_plugin where id='.$vars->id);
	$files=json_decode($db['fields'], true);
	if (count($files)==0) {
		return 'No files yet';
	}
	// { if template doesnt exist, create it
	$template=USERBASE.'/ww.cache/mp3/';
	if (!is_dir($template)) {
		mkdir($template);
	}
	$template.=$vars->id;
	if (!file_exists($template)) {
		file_put_contents(
			$template,
			$db['template']
		);
	}
	// }
	// { display the template
	require_once SCRIPTBASE.'ww.incs/Smarty-3.1.12/libs/Smarty.class.php';
	$smarty=new Smarty;
	$smarty->compile_dir=USERBASE.'/ww.cache/templates_c';
	if (!file_exists(USERBASE.'/ww.cache/templates_c')) {
		mkdir(USERBASE.'/ww.cache/templates_c'); 
	}
	if (!file_exists(USERBASE.'/ww.cache/templates_c/image-gallery')) {
		mkdir(USERBASE.'/ww.cache/templates_c/image-gallery');
	}
	$smarty->registerPlugin('function', 'LIST', 'MP3_list');
	$smarty->registerPlugin('function', 'PLAY', 'mp3_play');
	$smarty->registerPlugin('function', 'PROGRESS', 'MP3_progress');
	$smarty->left_delimiter='{{';
	$smarty->right_delimiter='}}';
	$smarty->smarty->tpl_vars['mp3_files']->value=$files;
	$html=$smarty->fetch(
		USERBASE.'/ww.cache/mp3/'.$vars->id
	);
	WW_addScript('mp3/frontend/jwplayer.js');
	WW_addScript('mp3/frontend/widget.js');
	// }
	return $html;
}

// }
// { MP3_list

/**
	* show list of MP3s
	*
	* @param array  $params settings
	* @param object $smarty Smarty object
	*
	* @return null
	*/
function MP3_list($params, $smarty) {
	$files=$smarty->smarty->tpl_vars['mp3_files']->value;
	$opts=array(
		'play_button'=>false,
		'link_to_play'=>false,
	);
	$opts=$params+$opts;
	$link=($opts['link_to_play'])?' link_to_play="true"':'';
	$play=($opts['play_button'])?' play_button="true"':'';
	echo '<ul class="mp3_playlist"'.$link.$play.'>';
	foreach ($files as $file) {
		echo '<li>';
		if ($opts['play_button']) {
			echo '<a class="play_button" href="';
			echo $file['location'].'">';
			echo 'Play</a>';
		}
		if ($opts['link_to_play']) {
			echo '<a href="'.$file['location'].'" class="link_to_play">';
			echo $file['name'].'</a>';
		}
		else {
			echo $file['name'];
		}
		echo '</li>';
	}
	echo '</ul>';
}

// }
// { MP3_play

/**
	* get play link
	*
	* @param array  $params settings
	* @param object $smarty Smarty object
	*
	* @return null
	*/
function MP3_play($params, $smarty) {
	$opts=array(
		'image_link'=>'false',
	);
	$opts=$params+$opts;
	$image=($opts['image_link'])?' image':'';
	echo '<a href="javascript:;" class="mp3_play_link'.$image.'">';
	if ($opts['image_link']) {
		echo '&nbsp;';
	}
	else {
		echo 'PLAY';
	}
	echo '</a>';
}

// }
// { MP3_progress

/**
	* show MP3 progress wrapper
	*
	* @param array  $params settings
	* @param object $smarty Smarty object
	*
	* @return null
	*/
function MP3_progress($params, $smarty) {
	$opts=array(
		'show_times'=>false,
	);	
	$opts=$params+$opts;
	$times=($opts['show_times'])?' times="true"':'';
	echo '<div class="progress_wrapper">';
	echo '<div class="mp3_progress"'.$times.'></div>';
	if ($opts['show_times']) {
		echo	'<div class="mp3_duration">00:00</div>';
		echo	'<div class="mp3_position">00:00</div>';
		echo	'</div>';
	}
}

// }
