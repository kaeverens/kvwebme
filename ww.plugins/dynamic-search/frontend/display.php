<?php
/**
	* Dynamic Search Plugin v0.1
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Conor Mac Aoidh <conor@macaoidh.name>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

$html.='<div id="dynamic_searches">
        <div id="dynamic_search_results">
        </div>
        <div id="stuff">
                <h1>Most Popular Searches</h1>
';

$q=dbAll(
	'select *, count(search) as occurances from latest_search group by search '
	.'order by occurances desc limit 8'
);
$c=count($q);
if ($c==0) {
	$html.='<p><i>No popular searches found...</i></p>';
}
else {
	$html.='
                <table>
			<colgroup><col style="width:20px"/><col style="width:530px"/><col/>
	';
	foreach ($q as $r) {
		if ($r['search']!='') {
			$html.='<tr><td>'.$r['occurances'].'</td><td><a class="popular" href="'
				.'?dynamic_search_submit=search&dynamic_search='.$r['search']
				.'&dynamic_category='.$r['category'].'">'.$r['search']
				.'</a></td><td>'.$r['category'].'</td></tr>';
		}
	}
	$html.='</table>';
}
$html.='
	</div>
</div>';
