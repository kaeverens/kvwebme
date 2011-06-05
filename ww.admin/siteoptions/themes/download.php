<?php

/**
 * ww.admin/siteoptions/themes/download.php, KV-Webme
 *
 * displays themes from the theme server
 *
 * @author  Conor Mac Aoidh <conormacaoidh@gmail.com>
 * @author  Kae Verens <kae@kvsites.ie>
 * @license	GPL 2.0
 * @version	1.0
 */

require '../../../ww.incs/basics.php';
require SCRIPTBASE . 'ww.admin/admin_libs.php';

echo '
<script type="text/javascript" src="/ww.admin/siteoptions/themes/download.js"></script>
<link rel="stylesheet" type="text/css" href="/ww.plugins/themes-api/api.css"/>
';

echo '<div id="public-repository"><p>Choosing a theme here will download it into your private repository. If you already have a copy of the chosen theme there, then your copy will be over-written.</p>';

echo '<div id="themes-carousel">
</div>';

/**
 * build an array of installed themes
 */
$installed = get_template_names( );
echo '<script type="text/javascript">window.installed_themes=' . 
	json_encode( $installed ) . ';</script>';

echo '<br style="clear:both"/>';
?>
