<?php
/*
	Webme Dynamic Search Plugin v0.3
	File: frontend/search.php
	Developer: Conor Mac Aoidh <http://macaoidh.name>
	Report Bugs: <conor@macaoidh.name>
*/

WW_addScript('/ww.plugins/dynamic-search/files/general.js');
WW_addCSS('/ww.plugins/dynamic-search/files/style.css');
$html='
<h1>Search</h1>

<form method="get" id="dynamic_search">
	<table id="dynamic_search_table">
		<tr>
			<td><select name="dynamic_category" id="dynamic_search_select">
			<option>Site Wide</option>';
if($catags!='')
	foreach($catags as $catag)
		$html.='<option>'.$catag.'</option>';
$html.='
                        </select></td>
			<td><input type="text" name="dynamic_search" value="Enter Keywords..." id="dynamic_searchfield"/></td>
			<td><input type="submit" value="Search" id="dynamic_search_submit" name="dynamic_search_submit"/></td>
		</tr>
		<tr><td>&nbsp;</td><td><ul id="dynamic_suggestions"></ul></td><td>&nbsp;</td></tr>
	</table>
</form>
';

?>
