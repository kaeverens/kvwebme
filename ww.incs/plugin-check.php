<?php
if (isset($plugin['version']) && $plugin['version']
	&& (!isset($DBVARS[$pname.'|version'])
	|| $DBVARS[$pname.'|version']!=$plugin['version'] )
) {
	$version=isset($DBVARS[$pname.'|version'])
		?(int)$DBVARS[$pname.'|version']
		:0;
	require SCRIPTBASE . 'ww.plugins/'.$pname.'/upgrade.php';
	$DBVARS[$pname.'|version']=$version;
	Core_configRewrite();
	Core_cacheClear();
	header('Location: '.$_SERVER['REQUEST_URI']);
	Core_quit();
}
$PLUGINS[$pname]=$plugin;
if (isset($plugin['triggers'])) {
	foreach ($plugin['triggers'] as $name=>$fn) {
		if (!isset($PLUGIN_TRIGGERS[$name])) {
			$PLUGIN_TRIGGERS[$name]=array();
		}
		$PLUGIN_TRIGGERS[$name][]=$fn;
	}
}
