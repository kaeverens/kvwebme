<?php

/**
 * ww.admin/siteoptions/plugins.php, KV-Webme
 *
 * shows the plugins available on the cms and allows
 * them to be installed
 *
 * @authors    Conor Mac Aoidh <conormacaoidh@gmail.com>,
 *						 Kae Verens <kae@vernes.com>
 * @license    GPL 2.0
 * @version    1.0
 */

echo '<h2>Plugins</h2>';

if($action=='Save'){ // handle actions
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
  echo '<em>plugins updated</em>';
  echo '<a href="./siteoptions.php?page=plugins">reloading page to refresh database</a>';
  echo '<script type="text/javascript">window.setTimeout("document.location=\'./siteoptions.php?page=plugins\'",1000);</script>';
}

// { build array of available and installed plugins
$installed = array( );
foreach( $PLUGINS as $name => $plugin ){
	// exclude hidden plugins
	if( isset( $plugin[ 'hide_from_admin' ] ) && $plugin[ 'hide_from_admin' ] )
		continue;
	$installed[ $name ] = array(
		'name' => $plugin[ 'name' ],
		'description' => $plugin[ 'description' ],
		'version' => ( @$plugin[ 'version' ] == 0 ) ? '0' : $plugin[ 'version' ]
	);
}
// }

// { build array of available plugins that aren't instaled
$available = array( );
$dir = new DirectoryIterator( SCRIPTBASE . 'ww.plugins' );
foreach( $dir as $plugin ){
	if( strpos( $plugin, '.' ) === 0 )
		continue;
	$name = $plugin->getFilename( );
	if( !is_dir( SCRIPTBASE . 'ww.plugins/' . $name )|| isset( $PLUGINS[ $name ] ) )
      continue;
	require_once(SCRIPTBASE . 'ww.plugins/' . $name .'/plugin.php');
	if( isset( $plugin[ 'hide_from_admin' ] ) && $plugin[ 'hide_from_admin' ] )
      continue;
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
WW_addScript( '/j/jquery.dataTables-1.7.5/jquery.dataTables.min.js' );
WW_addCSS( '/j/jquery.dataTables-1.7.5/jquery.dataTables.css' );
WW_addInlineScript( $script );

echo '
<form method="post" action="siteoptions.php?page=plugins">
<div id="tabs">
	<ul>
		<li><a href="#installed">Installed</a></li>
		<li><a href="#available">Available</a></li>
	</ul>
	<div id="installed">
		<table id="installed_plugins" class="display" style="width:600px">
			<thead>
				<tr>
					<th>Name</th>
					<th>Version</th>
					<th style="width:60%">Description</th>
					<th>Installed</th>
				</tr>
			</thead>
			<tbody>';

foreach( $installed as $name => $plugin ){
	echo '<tr>
		<td>' . $plugin[ 'name' ] . '</td>
		<td>' . $plugin[ 'version' ] . '</td>
		<td>' . $plugin[ 'description' ] . '</td>
		<td><input type="checkbox" name="plugins[' . $name . ']" checked="checked"/></td>
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
					<th>Version</th>
					<th>Description</th>
					<th>Installed</th>
				</tr>
			</thead>
			<tbody>';

foreach( $available as $name => $plugin ){
	echo '<tr>
		<td>' . $plugin[ 'name' ] . '</td>
		<td>' . $plugin[ 'version' ] . '</td>
		<td>' . $plugin[ 'description' ] . '</td>
		<td><input type="checkbox" name="plugins[' . $name . ']"/></td>
	</tr>';
}

echo '</tbody>
		</table>
<input type="submit" name="action" value="Save" style="float:right"/>
<br style="clear:both"/>
</div>
</div>
</form>';
