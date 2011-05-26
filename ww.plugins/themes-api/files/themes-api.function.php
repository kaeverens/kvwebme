<?php

/**
 * files/themes-api.function.php, KV-Webme Themes Repository
 *
 * contains functions used for the plugin
 *
 * @author     Conor Mac Aoidh <conormacaoidh@gmail.com>
 * @license    GPL 2.0
 * @version    1.0
 */

/**
 * calculate_url
 *
 * Accurately calculates the server URL
 * 
 * @access public
 * @return string
 */
function calculate_url( ){
        $url = 'http';

        if( @$_SERVER[ 'HTTPS' ] == 'on' )
                $url .= 's';

        $url .= '://' . $_SERVER[ 'SERVER_NAME' ];

        if( $_SERVER[ 'SERVER_PORT' ] != '80' )
                $url .= ':' . $_SERVER[ 'SERVER_PORT' ];

        return $url;
}

?>
