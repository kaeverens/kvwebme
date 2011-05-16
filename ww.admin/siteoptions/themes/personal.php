<?php

/**
 * ww.admin/siteoptions/themes/personal.php, KV-Webme
 *
 * displays themes from the theme server
 *
 * @author     Conor Mac Aoidh <conormacaoidh@gmail.com>
 * @license    GPL 2.0
 * @version    1.0
 */

$themes = array( );
$theme_dir = USERBASE . 'themes-personal/';

/**
 * scan through theme dirs, gather information
 * on themes
 */
$files = scandir( $theme_dir );
foreach( $files as $file ){

	if( $file == '.' || $file == '..' )
		continue;

	if( is_dir( $theme_dir . $file ) ){

		$theme = array( 'name' => $file );

		/**
		 * get variants
		 */
		if( is_dir( $theme_dir . $file . '/cs' ) ){

			$variants = array( );
			$fs = scandir( $theme_dir . $file . '/cs' );

			foreach( $fs as $f ){

				if( $f == '.' || $f == '..' )
					continue;

		                /**
		                 * get file name and extention
		                 */
		                $fname = explode( '.', $f );
		                $ext = end( $fname );
		                $fname = reset( $fname );

		                /**
		                 * if css files are present, make sure they have
		                 * corresponding png files
		                 */
		                if( $ext == 'css' ){
		                        if( in_array( $fname . '.png', $fs ) ){
						array_push( $variants, $fname );
					}
		                }

			}

			$theme[ 'variants' ] = $variants;

		}

		array_push( $themes, $theme );

	}

}

/**
 * page javascript
 */
$script = '
$( "#tabs" ).tabs( );

$( ".theme_variant" ).each( show_screenshot );
$( ".theme_variant" ).change( show_screenshot );

function show_screenshot( ){
        var screenshot = $( ":selected", this ).attr( "screenshot" );
        $( this ).closest( "div" ).find( "img" ).attr( "src", screenshot );
}

$( ".theme-preview-personal" ).click( function( ){
        $( "#preview-frame" ).attr( "src", "" );
        var name = $( this ).attr( "title" );
	var variant = $( this ).closest( "form" ).find( ".theme_variant" ).val( );
        $( "#preview-dialog" ).attr( "title", name + " - Theme Preview"  );
        $( "#preview-frame" ).attr( "src", "/?__theme=" + name + "&__theme_variant=" + variant );
        $( "#preview-frame" ).attr( "height", $( window ).height( ) - 140 );
        $( "#preview-frame" ).attr( "width", $( window ).width( ) -220 );
        $( "#preview-dialog" ).dialog( { modal: true, width: $( window ).width( ) - 200, height: $( window ).height( ) - 60 } );
} );

';

WW_addInlineScript( $script );

/**
 * display theme
 */
echo '
<div id="preview-dialog" style="display:none">
<iframe src="" id="preview-frame">
        &nbsp;
</iframe>
</div>
<h2>Themes</h2>
<div id="tabs">

	<ul>
		<li><a href="#tabs-1">Personal</a></li>
		<li><a href="/ww.admin/siteoptions/themes/download.php">Download</a></li>
	</ul>

	<div id="tabs-1">
<table id="themes-table"><tr>';

/**
 * loop through themes, print them
 */
for( $i = 0; $i < count( $themes ); ++$i ){

        if( $i % 3 == 0 )
                echo '</tr><tr>';

	$status = ( $DBVARS[ 'theme' ] == $themes[ $i ][ 'name' ] ) ? ' - Current Theme' : '' ;
	$current = ( $DBVARS[ 'theme' ] == $themes[ $i ][ 'name' ] ) ? ' style="background:#FCFFB2"' : '';

        $class = ( !( ( $i - 1 ) % 3 ) ) ? ' class="middle"' : '';

        echo '<td' . $class . $current . '>';

        echo '<div class="theme-container">
                <form action="/ww.admin/siteoptions.php?page=themes&action=install" method="post">
                <input type="hidden" value="' . $themes[ $i ][ 'name' ] . '" name="theme_name"/>
                <h3>' . $themes[ $i ][ 'name' ] . @$status . '</h3>
                <p><img src="/ww.skins/' . $themes[ $i ][ 'name' ] . '/screenshot.png" width="240px" height="172px"/></p>
                <p>Variant: <select name="theme_variant" class="theme_variant">';

                /**
                 * get all variants
                 */
                foreach( $themes[ $i ][ 'variants' ] as $variant ){
                        $cur = ( $DBVARS[ 'theme' ] == $themes[ $i ][ 'name' ] && $DBVARS['theme_variant'] == $variant ) ?
                                ' selected="selected"' :
                                '';
                        echo '<option screenshot="/ww.skins/' . $themes[ $i ][ 'name' ] . '/cs/' . $variant. '.png" ' . $cur . '>' . $variant . '</option>';
                }

        echo '</select></p>
        <p>
                <input type="submit" class="install-theme" name="install-theme" value="Install" />
                <input type="submit" class="install-theme" name="delete-theme" value="Delete"/> 
                <a class="theme-preview theme-preview-personal" title="' . $themes[ $i ][ 'name' ] . '">Preview</a>
        </p></form></td>';

}

echo '</tr></table><br style="clear:both"/>
</div>
</div>
';
?>
