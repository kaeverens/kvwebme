<?php
echo '<h2>'.__('Plugins').'</h2>';
// { handle actions
if($action=='Save'){
	// { get hidden plugins (those that the admin must install manually using teh site config)
	$tmp_hidden=array();
	foreach($PLUGINS as $name=>$plugin){
		if(isset($plugin['hide_from_admin']) && $plugin['hide_from_admin'])$tmp_hidden[]=$name;
	}
	// }
	// { get changes from form
	$tmp=array();
	foreach($_POST['plugins'] as $name=>$var)if(file_exists(SCRIPTBASE . 'ww.plugins/' . $name .'/plugin.php'))$tmp[]=$name;
	// }
	$DBVARS['plugins']=array_merge($tmp,$tmp_hidden);
	config_rewrite();
	echo '<em>'.__('plugins updated').'</em>';
	echo '<a href="./siteoptions.php?page=plugins">reloading page to refresh database</a>';
	echo '<script type="text/javascript">window.setTimeout("document.location=\'./siteoptions.php?page=plugins\'",1000);</script>';
	exit;
}
// }
// { form
echo '<form method="post" action="siteoptions.php?page=plugins"><table>';
echo '<tr><th>Plugin Name</th><th>Version</th><th>Description</th><th>Active</th></tr>';
foreach($PLUGINS as $name=>$plugin){
	if(isset($plugin['hide_from_admin']) && $plugin['hide_from_admin'])continue;
	echo '<tr><th>',htmlspecialchars($plugin['name']),'</th>',
		'<td>',htmlspecialchars($plugin['description']),'</td>',
		'<td><input type="checkbox" name="plugins[',$name,']" checked="checked" /></td>',
		'</tr>';
}
$dir=new DirectoryIterator(SCRIPTBASE . 'ww.plugins');
foreach($dir as $plugin){
	if(strpos($plugin,'.')===0)continue;
	$name=$plugin->getFilename();
	if (!is_dir(SCRIPTBASE . 'ww.plugins/' . $name)
		|| isset($PLUGINS[$name])
	) {
		continue;
	}
	require_once(SCRIPTBASE . 'ww.plugins/' . $name .'/plugin.php');
	if (isset($plugin['hide_from_admin']) && $plugin['hide_from_admin']) {
		continue;
	}
	echo '<tr><th>',htmlspecialchars($plugin['name']),'</th>',
		'<td>',htmlspecialchars($plugin['description']),'</td>',
		'<td><input type="checkbox" name="plugins[',$name,']" /></td>',
		'</tr>';
}
echo '</table><input type="submit" name="action" value="Save" /></form>';
// }
