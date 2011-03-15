<?php
$dir=dirname(__FILE__);
$webme_start_time=microtime();
header('Content-type: text/html; Charset=utf-8');
date_default_timezone_set('Eire');
require $dir.'/../ww.incs/common.php';
// { if not logged in, show login page
if (!is_admin()) {
	include SCRIPTBASE . 'ww.admin/login.php';
	exit;
}
// }
require $dir.'/admin_libs.php';
$admin_vars=array();
// { common variables
	foreach(array('action','resize') as $v)$$v=getVar($v);
	foreach(array('show_items','start') as $v)$$v=getVar($v,0);
	$id=isset($_REQUEST['id'])?(int)$_REQUEST['id']:0;
// }
WW_addScript('/j/jquery.dataTables-1.7.5/jquery.dataTables.min.js');
WW_addCSS('/j/jquery.dataTables-1.7.5/jquery.dataTables.css');
WW_addScript('/j/jquery.remoteselectoptions.js');
WW_addScript('/j/fg.menu/fg.menu.js');
WW_addScript('/j/ckeditor-3.5/ckeditor.js');
WW_addScript('/j/cluetip/jquery.cluetip.js');
WW_addScript('/ww.admin/j/admin.js');
?>
<html>
	<head>
<?php
	echo Core_getJQueryScripts();
?>
		<?php echo '<script src="/js/'.filemtime(SCRIPTBASE.'j/js.js').'"></script>'; ?>
		<link rel="stylesheet" type="text/css" href="/j/cluetip/jquery.cluetip.css" />
		<link rel="stylesheet" href="/ww.admin/theme/admin.css" type="text/css" />
<?php
foreach($PLUGINS as $pname=>$p){
	if(file_exists(SCRIPTBASE.'/ww.plugins/'.$pname.'/admin/admin.css'))echo '<link rel="stylesheet" href="/ww.plugins/'.$pname.'/admin/admin.css" type="text/css" />';
}
?>
	</head>
	<body<?php
	if(isset($_REQUEST['frontend-admin']))echo ' class="frontend-admin"';
	?>>
		<div id="header"> 
<?php
	// { setup standard menu items
	$menus=array(
		'Pages'=>array(
			'_link'=>'pages.php'
		),
		'Site Options'=>array(
			'General'=> array('_link'=>'siteoptions.php'),
			'Users'  => array('_link'=>'siteoptions.php?page=users'),
			'Themes' => array('_link'=>'siteoptions.php?page=themes'),
			'Plugins'=> array('_link'=>'siteoptions.php?page=plugins')
		)
	);
	// }
	// { add custom items (from plugins)
	foreach($PLUGINS as $pname=>$p){
		if(!isset($p['admin']) || !isset($p['admin']['menu']))continue;
		foreach($p['admin']['menu'] as $name=>$page){
			if(preg_match('/[^a-zA-Z0-9 >]/',$name))continue; # illegal characters in name
			$json='{"'.str_replace('>','":{"',$name).'":{"_link":"plugin.php?_plugin='.$pname.'&amp;_page='.$page.'"}}'.str_repeat('}',substr_count($name,'>'));
			$menus=array_merge_recursive($menus,json_decode($json,true));
		}
	}
	// }
	// { add final items
	$menus['Stats']=    array('_link'=>'/ww.admin/stats.php');
	$menus['View Site']=array( '_link'=>'/', '_target'=>'_blank');
	$menus['Help']=array( '_link'=>'http://kvweb.me/', '_target'=>'_blank');
	$menus['Log Out']=  array('_link'=>'/?logout=1');
	// }
	// { display menu as UL list
	function admin_menu_show($items,$name=false,$prefix,$depth=0){
		$target=(isset($items['_target']))?' target="'.$items['_target'].'"':'';
		if(isset($items['_link']))echo '<a href="'.$items['_link'].'"'.$target.'>'.$name.'</a>';
		else if($name!='top')echo '<a href="#'.$prefix.'-'.$name.'">'.$name.'</a>';
		if(count($items)==1 && isset($items['_link']))return;
		if($depth<2)echo '<div id="'.$prefix.'-'.$name.'">';
		echo '<ul>';
		foreach($items as $iname=>$subitems){
			if($iname=='_link')continue;
			echo '<li>';
			admin_menu_show($subitems,$iname,$prefix.'-'.$name,$depth+1);
			echo '</li>';
		}
		echo '</ul>';
		if($depth<2)echo '</div>';
	}
	admin_menu_show($menus,'top','menu');
	// }
?>
		</div>
		<div id="wrapper">
			<div id="main">
