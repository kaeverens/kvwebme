<?php
/**
	* displays a catalogue of themes
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

if (!defined('SCRIPTBASE')) { // don't access directly
	Core_quit();
}
require_once SCRIPTBASE.'/ww.plugins/themes-api/api/funcs.php';

WW_addScript('themes-api/carousel.js');
WW_addCSS('/ww.plugins/themes-api/api.css');

$script = '
$( "#carousel" ).themesCarousel({loop:true});
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
WW_addInlineScript($script);

$html='<input type="text" name="themes_search" /><input type="submit" '
	.'name="Search" id="themes_search"/>'
	.'<h1>'.__('Themes Repository').'</h1>'
	.'<div id="carousel"></div>'
	.'<div id="previous">&lt;</div>'
	.'<div id="next" style="position:relative;top:0;background:#000;color:#ff'
	.'f;padding:30px">&gt;</div> <br style="clear:both"/>';
