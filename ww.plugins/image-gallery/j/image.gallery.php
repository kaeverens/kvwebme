<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/ww.incs/common.php';
require_once SCRIPTBASE.'ww.incs/kaejax.php';
$kfm_do_not_save_session=true;
require_once KFM_BASE_PATH.'/api/api.php';
require_once KFM_BASE_PATH.'/initialise.php';
function ig_getImages($dirId){
	$files=kfm_loadFiles($dirId);
	return $files['files'];
}
kaejax_export('ig_getImages');
kaejax_handle_client_request();
kaejax_show_javascript();
echo file_get_contents('image.gallery.js');
