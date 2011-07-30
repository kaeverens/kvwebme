<?php
require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if (!Core_isAdmin()) {
	die('access denied');
}

rename(USERBASE.'/ww.cache/publisher', USERBASE.'/ww.cache/published-site');
$cmd='cd "'.USERBASE.'/ww.cache/" && tar cjf published-site.tar.bz2 published-site';
`$cmd`;

rename(USERBASE.'/ww.cache/published-site.tar.bz2', USERBASE.'/f/.files/published-site.tar.bz2');

$cmd='rm -rf "'.USERBASE.'/ww.cache/published-site"';
`$cmd`;

echo 'ok';
