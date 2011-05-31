<?php
/**
 * api/funcs.php, KV-Webme Themes API
 *
 * function library for the api
 *
 * @author     Conor Mac Aoidh <conormacaoidh@gmail.com>
 * @license    GPL 2.0
 * @version    1.0
 */

/**
 * themes_api_get_screenshot
 *
 * @param $id - id of theme
 *
 * returns the url of the theme screenshot
 */
function themes_api_get_screenshot( $id ){
	return themes_api_calculate_url( ) . '/ww.plugins/themes-api/api.php?screenshot=true&id=' . $id;
}

/**
 * themes_api_get_variants
 * 
 * returns an array of css files associated
 * with the theme
 */
function themes_api_get_variants( $id ){

	$variant_dir = USERBASE . 'f/themes_api/themes/' . $id . '/' . $id . '/cs/';
	$variants = array( );

	/**
	 * if the dir doesn't exist return empty array
	 */
	if( !is_dir( $variant_dir ) )
		return $variants;

        /**
         * loop through theme dir
         */
        $handler = opendir( $variant_dir );
        while( $file = readdir( $handler ) ){

                if( $file == '.' || $file == '..' )
                        continue;

                /**
                 * get file extention
                 */
		$name = explode( '.', $file );
                $ext = end( $name );

                if( $ext == 'css' ){
			$name = reset( $name );
                        array_push( $variants, $name );
		}

        }
        closedir( $handler );

        return $variants;

}

/**
 * themes_api_calculate_url
 *
 * Accurately calculates the server URL
 * 
 * @access public
 * @return string
 */
function themes_api_calculate_url( ){
        $url = 'http';

        if( @$_SERVER[ 'HTTPS' ] == 'on' )
                $url .= 's';

        $url .= '://' . $_SERVER[ 'SERVER_NAME' ];

        if( $_SERVER[ 'SERVER_PORT' ] != '80' )
                $url .= ':' . $_SERVER[ 'SERVER_PORT' ];

        return $url;
}

/**
 * themes_api_error
 * 
 * dies with an error message, before doing so
 * it cleans the contents of the themes-api/extract
 * directory and removes the theme from the server
 * and the database 
 */
function themes_api_error( $msg, $id = null ){
        /**
         * remove temporary extract stuff
         */
        shell_exec( 'rm -rf ' . USERBASE . 'f/themes_api/extract/*' );

        if( $id != 0 ){

                /**
                 * remove theme from server
                 */
                shell_exec( 'rm -rf ' . USERBASE . 'f/themes_api/themes/' . $id );

                /**
                 * remove theme from database
                 */
                dbQuery( 'delete from themes_api where id=' . $id );

        }

        die( $msg );
}

/**
 * themes_api_download_link
 *
 * given the id and name of the theme this function
 * will return the download URL
 */
function themes_api_download_link( $id ){
	return themes_api_calculate_url( ) . '/ww.plugins/themes-api/api.php?download=true&id=' . $id;
}

/**
 * themes_api_display_image
 *
 * display an image to the screen
 */
function themes_api_display_image( $file ){

	if (!file_exists($file) || !filesize($file)) {
		die('file '.$file.' does not exist');
	}

	$arr=getimagesize($file);
	if ($arr[0]>240 || $arr[1]>172) {
		$md5=USERBASE.'/ww.cache/screenshots/'.md5($file).'.png';
		if (!file_exists($md5)) {
			@mkdir(USERBASE.'/ww.cache/screenshots');
			`convert $file -resize 240x172 $md5`;
		}
		$file=$md5;
	}

	/**
	 * set headers and read file
	 */
	header( 'Content-type: image/png' );
	header( 'Content-Transfer-Encoding: Binary' );
	header( 'Content-length: ' . filesize( $file ) );
	readfile( $file );
}

/**
 * themes_api_curl
 * 
 * gets the contents of a url
 */
function themes_api_curl( $url ){
        $ch = curl_init( );
        curl_setopt( $ch, CURLOPT_URL, $url );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
        $response = curl_exec( $ch );
        curl_close( $ch );
        return $response;
}

function themes_api_get_theme_from_id( $themes, $id ){
	foreach( $themes as $theme ){
		if( $theme[ 'id' ] == $id )
			return $theme;
	}
}

function themes_api_add_download_count( $themes ){
	$ids = array( );
	foreach( $themes as $theme )
		array_push( $ids, $theme[ 'id' ] );

	$downloads = dbAll( 'select count(id),theme from themes_downloads where theme='
	. implode( ' or theme=', $ids ) . ' group by theme'  );  

	for( $i = 0; $i < count( $themes ); ++$i ){
		foreach( $downloads as $download ){
			if( $download[ 'theme' ] == $themes[ $i ][ 'id' ] )
				$themes[ $i ][ 'downloads' ] = $download[ 'count(id)' ];
		}
		if( !isset( $themes[ $i ][ 'downloads' ] ) )
			$theme[ $i ][ 'downloads' ] = 0;
	}
	
	return $themes;
}
?>
