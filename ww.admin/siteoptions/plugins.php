<?php
/**
	* shows the plugins available on the cms and allows
	* them to be installed
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Conor Mac Aoidh <conormacaoidh@gmail.com>
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

/**
	* check plugins to find their dependencies
	*
	* @param array $plugins array of plugin names
	*
	* @return array array of needed plugins
	*/
function SiteOptions_dependenciesRecursiveCheck($plugins) {
	$new_plugs=array();
	foreach ($plugins as $plug) {
		if (!is_dir(SCRIPTBASE.'ww.plugins/'.$plug)
			||!file_exists(SCRIPTBASE.'ww.plugins/'.$plug.'/plugin.php')
		) {
			// plugin doesn't exist
			return $plug;
		}
		global $PLUGINS;
		if (isset($PLUGINS[$plug])) { // if installed load from memory
			$plugin=$PLUGINS[$plug];
		}
		else { // else include plugin file
			// if already included then it must be
			// already on the list
			require_once SCRIPTBASE.'ww.plugins/'.$plug.'/plugin.php';
		}
		if (isset($plugin['dependencies'])) {
			$dependencies=(strpos($plugin['dependencies'], ',')===false)
				?array($plugin['dependencies'])
				:explode(',', $plugin['dependencies']);
			foreach ($dependencies as $dependency) {
				if (!in_array($dependency, $plugins)
					&&!in_array($dependency, $new_plugs)
				) {
					array_push($new_plugs, $dependency);
				}
			}
		}
		array_push($new_plugs, $plug);
		$plugin=array();
	}
	$diff=array_diff($new_plugs, $plugins);
	$new_plugs=array_merge($plugins, $new_plugs);
	if (is_array($diff)&&count($diff)!=0) {
		$check=SiteOptions_dependenciesRecursiveCheck($diff);
		if (!is_array($check)) {
			return $check;
		}
		$new_plugs=array_merge($new_plugs, $check);
	}
	return array_unique($new_plugs);
}

echo '<h2>Plugins</h2>';

if ($action=='Save') { // handle actions
	// { get hidden plugins (those the admin installs manually)
	$tmp_hidden=array();
	foreach ($PLUGINS as $name=>$plugin) {
		if (isset($plugin['hide_from_admin']) && $plugin['hide_from_admin']) {
			$tmp_hidden[]=$name;
		}
	}
	// }
	// { get changes from form
	$tmp=array();
	foreach ($_POST['plugins'] as $name=>$var) {
		if (file_exists(SCRIPTBASE . 'ww.plugins/' . $name .'/plugin.php')) {
			$tmp[]=$name;
		}
	}
	// }
	$plugins=array_merge($tmp, $tmp_hidden);
	$plugins=SiteOptions_dependenciesRecursiveCheck($plugins);
	if (is_array($plugins)) {
	  $DBVARS['plugins']=$plugins;
	  Core_configRewrite();
		header('location: siteoptions.php?page=plugins&message=updated');
	}
	// dependency doesn't exist
	header('location: siteoptions.php?page=plugins&message=failed');
}

$message=@$_GET['message'];
if ($message=='updated') {
	echo '<em>plugins updated</em>';
}
elseif ($message=='failed') {
	echo'<em>update failed</em><p>failed to meet the plugin dependencies</p>';
}

// { build array of available and installed plugins
$installed = array();
foreach ($PLUGINS as $name => $plugin) {
	// exclude hidden plugins
	if (isset($plugin[ 'hide_from_admin' ]) && $plugin['hide_from_admin']) {
		continue;
	}
	$installed[ $name ] = array(
		'name' => $plugin[ 'name' ],
		'description' => $plugin[ 'description' ],
		'version' => ( @$plugin[ 'version' ] == 0 ) ? '0' : $plugin[ 'version' ]
	);
}
// }

// { build array of available plugins that aren't instaled
$available = array( );
$dir = new DirectoryIterator(SCRIPTBASE . 'ww.plugins');
foreach ($dir as $plugin) {
	if (strpos($plugin, '.')===0) {
		continue;
	}
	$name = $plugin->getFilename();
	if (!is_dir(SCRIPTBASE.'ww.plugins/'.$name)||isset($PLUGINS[$name])) {
	  continue;
	}
	require_once SCRIPTBASE . 'ww.plugins/' . $name .'/plugin.php';
	if (isset( $plugin[ 'hide_from_admin' ] ) && $plugin[ 'hide_from_admin' ]) {
	  continue;
	}
	$available[ $name ] = array( 
		'name' => $plugin[ 'name' ],
		'description' => @$plugin[ 'description' ],
		'version' => ( @$plugin[ 'version' ] == 0 ) ? '0' : $plugin[ 'version' ]
	);
}	
// }

$script = '
$(function( ){
	$( "#installed_plugins" ).dataTable({ "bPaginate" : false });
	$( "#available_plugins" ).dataTable({ "bPaginate" : false });
	$( "#tabs" ).tabs({
		"show" : function( event, ui ){
			var oTable = $( ".display", ui.panel ).dataTable({ "bRetrieve" : true });
			if ( oTable.length > 0 )
				oTable.fnAdjustColumnSizing();
		}
	});
});
';
WW_addInlineScript($script);

echo '
<form method="post" action="siteoptions.php?page=plugins">
<div id="tabs">
	<ul>
		<li><a href="#installed">Installed</a></li>
		<li><a href="#available">Available</a></li>
	</ul>
	<div id="installed">
		<table id="installed_plugins" class="display" style="width:100%">
			<thead>
				<tr>
					<th>Name</th>
					<th>Installed</th>
					<th style="width:90%">Description</th>
				</tr>
			</thead>
			<tbody>';

foreach ($installed as $name => $plugin) {
	echo '<tr>
		<td>' . $plugin[ 'name' ] . '</td>
		<td><input type="checkbox" name="plugins[' . $name . ']" checked="checked"/></td>
		<td>' . $plugin[ 'description' ] . '</td>
	</tr>';
}

echo '</tbody>
</table>
<input type="submit" name="action" value="Save" style="float:right"/>
<br style="clear:both"/>
</div>
	<div id="available">
		<table id="available_plugins" class="display">
			<thead>
				<tr>
					<th>Name</th>
					<th>Installed</th>
					<th style="width:90%">Description</th>
				</tr>
			</thead>
			<tbody>';

foreach ($available as $name => $plugin) {
	echo '<tr>
		<td>' . $plugin[ 'name' ] . '</td>
		<td><input type="checkbox" name="plugins[' . $name . ']"/></td>
		<td>' . $plugin[ 'description' ] . '</td>
	</tr>';
}

echo '</tbody>
		</table>
<input type="submit" name="action" value="Save" style="float:right"/>
<br style="clear:both"/>
</div>
</div>
</form>';
