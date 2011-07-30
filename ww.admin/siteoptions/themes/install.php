<?php

/**
 * ww.admin/siteoptions/themes/install.php, KV-Webme
 *
 * installs or deletes a local theme
 *
 * @author     Conor Mac Aoidh <conormacaoidh@gmail.com>
 * @license    GPL 2.0
 * @version    1.0
 */

/**
 * make sure post is set
 */
if( !isset( $_POST[ 'install-theme' ] ) && !isset( $_POST[ 'delete-theme' ] ) )
        exit;

/**
 * get name
 */
$name = @$_POST[ 'theme_name' ];
if( $name == '' )
	exit;

/**
 * install theme if selected
 */
if( isset( $_POST[ 'install-theme' ] ) ){

        $DBVARS['theme'] = $name;

        $variant = @$_POST[ 'theme_variant' ];
        if( $variant != '' )
                $DBVARS['theme_variant'] = $variant;

        config_rewrite( );
        Core_cacheClear( 'pages' );

}


/**
 * delete theme if selected
 */
if( isset( $_POST[ 'delete-theme' ] ) ){

	if( $DBVARS[ 'theme' ] == $name )
		header( 'location: /ww.admin/siteoptions.php?page=themes' );	
	else{
		if( is_dir( USERBASE . 'themes-personal/' . $name ) )
			shell_exec( 'rm -rf ' . USERBASE . 'themes-personal/' . $name );
	}
}

/**
 * redirect to themes personal
 */
header( 'location: /ww.admin/siteoptions.php?page=themes' );
?>
