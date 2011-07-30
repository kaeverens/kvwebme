<?php
require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if (!Core_isAdmin()) {
	die('access denied');
}

// { template
$template=isset($_REQUEST['template'])?$_REQUEST['template']:'';
echo '<strong>Template (leave blank to use a default one)</strong><br />';
echo '<textarea class="small" name="template">'.htmlspecialchars($template).'</textarea>';
// }
