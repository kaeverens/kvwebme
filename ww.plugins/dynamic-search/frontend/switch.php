<?php
/*
	Webme Dynamic Search Plugin v0.3
	File: frontend/switch.php
	Developer: Conor Mac Aoidh <http://macaoidh.name>
	Report Bugs: <conor@macaoidh.name>
*/

$q=dbAll('select value from site_vars where name="catags"');
$catags=explode(',',$q[0]['value']);

include SCRIPTBASE.'ww.plugins/dynamic-search/frontend/search.php';

$sub=$_GET['dynamic_search_submit'];

if($sub=='') include SCRIPTBASE.'ww.plugins/dynamic-search/frontend/display.php';
else include SCRIPTBASE.'ww.plugins/dynamic-search/frontend/results.php';

?>
