<?php

/**
 * ww.admin/siteoptions/themes/download.php, KV-Webme
 *
 * displays themes from the theme server
 *
 * @author     Conor Mac Aoidh <conormacaoidh@gmail.com>
 * @license    GPL 2.0
 * @version    1.0
 */

require '../../../ww.incs/basics.php';
require SCRIPTBASE . 'ww.admin/admin_libs.php';

echo '
<script type="text/javascript">

$( ".theme_variant" ).each( show_screenshot );
$( ".theme_variant" ).change( show_screenshot );

function show_screenshot( ){
        var screenshot = $( ":selected", this ).attr( "screenshot" );
        $( this ).closest( "div" ).find( "img" ).attr( "src", screenshot );
}

$( ".install-theme" ).click( function( ){
	/**
	 * check if theme is already installed
	 */
	var installed = $( this ).attr( "installed" );
	if( installed == 1 ){
		var conf = confirm( "You already have a local copy of this theme. Proceeding will erase the theme from your local themes and replace it. Do you wish to proceed?" );
		if( conf == false )
			return false;
	}

} );

$( ".theme-preview-download" ).click( function( ){
        $( "#preview-frame" ).attr( "src", "" );
	var name = $( this ).attr( "title" );
        var variant = $( this ).closest( "form" ).find( ".theme_variant" ).val( );
        $( "#preview-dialog" ).attr( "title", name + " - Theme Preview"  );
        $( "#preview-frame" ).attr( "src", "http://kvweb.me/preview?__theme=" + name + "&__theme_variant=" + variant );
	$( "#preview-frame" ).attr( "height", $( window ).height( ) - 140 );
        $( "#preview-frame" ).attr( "width", $( window ).width( ) -220 );
	$( "#preview-dialog" ).dialog( { modal: true, width: $( window ).width( ) - 200, height: $( window ).height( ) - 60 } );
} );
</script>
';

echo '<div id="public-repository"><p>Choosing a theme here will download it into your private repository. If you already have a copy of the chosen theme there, then your copy will be over-written.</p>';

/**
 * build an array of installed themes
 */
$installed = get_template_names( );

/**
 * get themes from api
 */
$themes = curl( 'http://kvweb.me/ww.plugins/themes-api/api.php?recent=true' );
$themes = json_decode( $themes, true );

if( count( $themes ) == 0 )
	die( 'there are no recent themes!' );

echo '
<table id="themes-table">
<tr>
';

/**
 * loop through themes, print them
 */
for( $i = 0; $i < count( $themes ); ++$i ){

	/**
	 * check if already installed
	 */
	$theme_installed = in_array( $themes[ $i ][ 'name' ], $installed ) ? 1 : 0;
	$status = ( $theme_installed ) ? ' - Already Installed' : '';

	if( $i % 3 == 0 )
		echo '</tr><tr>';

        $class = ( !( ( $i - 1 ) % 3 ) ) ? ' class="middle"' : '';

	echo '<td' . @$class . '>';

	echo '<div class="theme-container">
		<form action="/ww.admin/siteoptions.php?page=themes&action=download" method="post">
		<input type="hidden" value="' . $themes[ $i ][ 'id' ] . '" name="theme_id"/>
		<h3> ' . $themes[ $i ][ 'name' ] . $status . '</h3>
		<p><img src="" width="240px" height="172px"/></p>
		<p>' . $themes[ $i ][ 'description' ] . '</p>
		<p>Variant: <select name="theme_variant" class="theme_variant">';

	        /**
	         * get all variants
	         */
	        foreach( $themes[ $i ][ 'variants' ] as $variant ){
	                echo '<option screenshot="' . $themes[ $i ][ 'screenshot' ] . '&variant=' . $variant . '">' . $variant . '</option>';
	        }

	echo '</select></p>
	<p>
		<input type="submit" class="install-theme" name="install-theme" installed="' . $theme_installed . '" value="Download & Install" />
		<input type="submit" class="install-theme" name="download-theme" installed="' . $theme_installed . '" value="Download"/> 
		<a class="theme-preview theme-preview-download" title="' . $themes[ $i ][ 'name' ] . '" >Preview</a>
	</p></form></td>';

}

echo '</tr></table><br style="clear:both"/>
';
?>
