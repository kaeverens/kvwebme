<?php
/**
	* switched between three side-menu options
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

$action = @$_GET[ 'action' ];

echo '<h1>Themes Repository</h1>
<div class="left-menu">
	<a href="plugin.php?_plugin=themes-api&_page=index">Overview</a>
	<a href="plugin.php?_plugin=themes-api&_page=index&action=moderate">Moderate</a>
	<a href="plugin.php?_plugin=themes-api&_page=index&action=approved">Approved</a>
</div>
<div class="has-left-menu">
';

/**
 * fetch themes from db
 */
$themes=dbAll('select * from themes_api');

switch ($action) {
	case 'approved':
		require SCRIPTBASE . 'ww.plugins/themes-api/admin/approved.php';
	break;
	case 'moderate':
		require SCRIPTBASE . 'ww.plugins/themes-api/admin/moderate.php';
	break;
	default:
		require SCRIPTBASE . 'ww.plugins/themes-api/admin/overview.php';
	break;
}

echo '</div>';
