<?php
/*
	Webme Dynamic Search Plugin v0.3
	File: frontend/switch.php
	Developer: Conor Mac Aoidh <http://macaoidh.name>
	Report Bugs: <conor@macaoidh.name>
*/

$catags=explode(
	',',
	dbOne('select value from site_vars where name="catags"', 'value')
);

require SCRIPTBASE.'ww.plugins/dynamic-search/frontend/search.php';

$sub=@$_GET['dynamic_search_submit'];
require SCRIPTBASE.'ww.plugins/dynamic-search/frontend/'
	.($sub=='')
	?'display.php'
	:'results.php';
