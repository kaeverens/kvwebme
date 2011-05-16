<?php

/**
 * ww.admin/siteoptions/themes.php, KV-Webme
 *
 * switches between theme operations
 *
 * @author     Conor Mac Aoidh <conormacaoidh@gmail.com>
 * @license    GPL 2.0
 * @version    1.0
 */

$action = @$_GET[ 'action' ];
switch( $action ){

        case 'install':
                require SCRIPTBASE . 'ww.admin/siteoptions/themes/install.php';
        break;;
	case 'download':
		require SCRIPTBASE . 'ww.admin/siteoptions/themes/theme-download.php';
	break;
	default:
		require SCRIPTBASE . 'ww.admin/siteoptions/themes/personal.php';
}

?>
