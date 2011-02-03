<?php
require_once '../../ww.incs/basics.php';
require_once 'pages.funcs.php';
$selected=isset($_REQUEST['selected'])?$_REQUEST['selected']:0;
$id=isset($_REQUEST['other_GET_params'])?(int)$_REQUEST['other_GET_params']:-1;
echo '<option value="0">--  '.__('none').'  --</option>';
selectkiddies(0,0,$selected,$id);
