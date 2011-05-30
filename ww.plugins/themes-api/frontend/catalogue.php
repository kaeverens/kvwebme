<?php

/**
 * frontend/catalogue.php, KV-Webme Themes Repository
 *
 * displays a catalogue of themes
 *
 * @author     Conor Mac Aoidh <conormacaoidh@gmail.com>
 * @license    GPL 2.0
 * @version    1.0
 */

require SCRIPTBASE . 'ww.plugins/themes-api/api/funcs.php';

WW_addScript( '/ww.plugins/themes-api/api.js' );
WW_addCSS( '/ww.plugins/themes-api/api.css' );

$script = '
$( "#carousel" ).themesCarousel( );
$( "#next" ).click( function( ){
	$( "#carousel" ).themesCarousel( "next" );
} );
$( "#prev" ).click( function( ){
	$( "#carousel" ).themesCarousel( "previous" );
} );
$( "#themes_search" ).click( function( ){
	var value = $( this ).val( );
	$( "#carousel" ).themesCarousel( "search", value );
});
';
WW_addInlineScript( $script );

$html = '
<input type="text" name="themes_search" /><input type="submit" name="Search" id="themes_search"/>
<h1>Themes Repository</h1>
<div id="carousel"></div>
<a id="prev">Previous</a>
<a id="next">Next</a>
<br style="clear:both"/>
';

?>
