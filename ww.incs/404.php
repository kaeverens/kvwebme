<?php
header('HTTP/1.0 404 Not Found');
require_once $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
echo __('Oops! missing file');
echo '<script>setTimeout(function() { document.location="/"; }, 1);</script>';
