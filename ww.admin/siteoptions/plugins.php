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

require_once SCRIPTBASE.'ww.incs/api-admin.php';
function removeMenuItem($menus, $bits, $name) {
	$thismenu=$menus[$name];
	$submenus=0;
	foreach ($menus as $key=>$val) {
		if (!preg_match('/^_/', $key)) {
			$submenus++;
		}
	}
	if (!$submenus) {
		return false;
	}
	if (count($bits)) {
		$newname=array_shift($bits);
		$thismenu=removeMenuItem($thismenu, $bits, $newname);
		$menus[$name]=$thismenu;
		if ($thismenu==false) {
			unset($menus[$name]);
		}
	}
	else {
		unset($menus[$name]);
	}
	return $menus;
}
echo '<h2>Plugins</h2>';

if ($action=='Save') {
	$status=Core_adminPluginsSetInstalled();
	$msg=$status['ok']?'updated':'failed';
	$removed=$status['removed'];
	if (count($removed)) { // adjust menus
		foreach ($removed as $plugin) {
			if (!isset($PLUGINS[$plugin]['admin']['menu'])) {
				continue;
			}
			$links=$PLUGINS[$plugin]['admin']['menu'];
			foreach ($links as $path=>$link) {
				$bits=explode('>', $path);
				$name=array_shift($bits);
				$rs=dbAll('select * from admin_vars where varname="admin_menu"');
				foreach ($rs as $r) {
					$menus=json_decode($r['varvalue'], true);
					$menus=removeMenuItem($menus, $bits, $name);
					$sql='update admin_vars set varvalue="'
						.addslashes(json_encode($menus))
						.'" where admin_id='.$r['admin_id'].' and varname="admin_menu"';
					dbQuery($sql);
				}
			}
		}
		Core_cacheClear('admin');
	}
	redirect(
		'/ww.admin/siteoptions.php?page=plugins&message='.$msg
		.'&added='.urlencode(join('|', $status['added']))
	);
}
if (isset($_REQUEST['added'])) { // adjust menus
	$added=explode('|', $_REQUEST['added']);
	foreach ($added as $plugin) {
		if (!isset($PLUGINS[$plugin]['admin']['menu'])) {
			continue;
		}
		$links=$PLUGINS[$plugin]['admin']['menu'];
		foreach ($links as $name=>$link) {
			$json='{"'.str_replace('>', '":{"', $name)
				.'":{"_link":"'.$link.'"}}'
				.str_repeat('}', substr_count($name, '>'));
			$newlink=json_decode($json, true);
			$rs=dbAll('select * from admin_vars where varname="admin_menu"');
			foreach ($rs as $r) {
				$menus=json_decode($r['varvalue'], true);
				$menus=array_merge_recursive($menus, $newlink);
				$sql='update admin_vars set varvalue="'
					.addslashes(json_encode($menus))
					.'" where admin_id='.$r['admin_id'].' and varname="admin_menu"';
				dbQuery($sql);
			}
		}
	}
	Core_cacheClear('admin');
}

$message=@$_GET['message'];
if ($message=='updated') {
	echo '<em>plugins updated</em>';
}
elseif ($message=='failed') {
	echo'<em>update failed</em><p>failed to meet the plugin dependencies</p>';
}

$installed=Core_adminPluginsGetInstalled();
$available=Core_adminPluginsGetAvailable();

// { start form
echo '
<form method="post" action="siteoptions.php?page=plugins">
<div id="tabs">
	<ul>
		<li><a href="#installed">Installed</a></li>
		<li><a href="#available">Available</a></li>
	</ul>';
// }
// { installed
echo '<div id="installed">
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
</div>';
// }
// { available
echo '<div id="available">
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
</div>';
// }
// { end form
echo '</div></form>';
// }
WW_addScript('/ww.admin/siteoptions/plugins.js');
