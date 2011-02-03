<?php
require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if(!is_admin())die('access denied');

// { template
$template=$_REQUEST['template'];
echo '<strong>Template (leave blank to use a default one)</strong><br />';
echo '<textarea class="small" name="template">'.htmlspecialchars($template).'</textarea>';
// }
