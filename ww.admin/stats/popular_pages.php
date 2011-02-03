<?php
/**
  * list the pages most often visited in the site
  *
  * PHP Version 5
  *
  * @category Stats
  * @package  WebWorksWebme
  * @author   Kae Verens <kae@webworks.ie>
  * @license  GPL Version 2
  * @link     www.webworks.ie
 */
WebME_Stats_update();
echo '<h2>Popular Pages</h2>';
echo '<table width="100%"><tr><td><h3>today</h3>';
// { today's favourites
$data=dbAll(
	'select type_data,count(type_data) as pages from logs where '
	.'log_type="page" and log_date>date_add(now(),interval -1 day) group by '
	.'type_data order by pages desc limit 50'
);
foreach ($data as $line) {
	$page_name=htmlspecialchars(preg_replace('/.*\|/', '', $line['type_data']));
	echo $line['pages'].' <a href="'.$page_name.'">'.$page_name.'</a><br />';
}
// }
echo '</td><td><h3>in last 7 days</h3>';
// { this week's favourites
$data=dbAll(
	'select type_data,count(type_data) as pages from logs where '
	.'log_type="page" and log_date>date_add(now(),interval -7 day) group by '
	.'type_data order by pages desc limit 50'
);
foreach ($data as $line) {
	$page_name=htmlspecialchars(preg_replace('/.*\|/', '', $line['type_data']));
	echo $line['pages'].' <a href="'.$page_name.'">'.$page_name.'</a><br />';
}
// }
echo '</td><td><h3>in last month</h3>';
// { today's favourites
$data=dbAll(
	'select type_data,count(type_data) as pages from logs where '
	.'log_type="page" and log_date>date_add(now(),interval -31 day) group by '
	.'type_data order by pages desc limit 50'
);
foreach ($data as $line) {
	$page_name=htmlspecialchars(preg_replace('/.*\|/', '', $line['type_data']));
	echo $line['pages'].' <a href="'.$page_name.'">'.$page_name.'</a><br />';
}
// }
echo '</td></tr></table>';
