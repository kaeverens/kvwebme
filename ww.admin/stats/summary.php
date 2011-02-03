<?php
/**
  * display an overview graph of the site visitors
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
echo '<!--[if IE]><script language="javascript" type="text/javascript" '
	.'src="/j/flot-0.5/excanvas.pack.js"></script><![endif]-->'
	.'<script type="text/javascript" src="/j/flot-0.5/jquery.flot.pack.js">'
	.'</script>';

echo '<table><tr><td>'
	.'<a href="javascript:show_data(\'all_visitors\')">all visitors</a><br />'
	.'<a href="javascript:show_data(\'all_requests\')">all requests</a><br />'
	.'<a href="javascript:show_data(\'page_requests\')">page requests</a><br />'
	.'<a href="javascript:show_data(\'bandwidth_usage\')">bandwidth usage</a>'
	.'<br />'
	.'</td><td><div id="placeholder" style="width:600px;height:300px;"></div>'
	.'<div id="overview" style="width:600px;height:50px"></div>'
	.'</td></tr></table>';


echo '<script type="text/javascript">';
// { all_visitors
$data=dbAll(
	'select unix_timestamp(log_d2) as log_d,count(log_d2) as '
	.'all_visitors from (select distinct date(log_date) as log_d2,ip_address '
	.'from logs) as d1 group by log_d order by log_d'
);
echo 'window.all_visitors=[';
$max_data=0;
foreach ($data as $visitor) {
	echo '['.$visitor['log_d'].'000,'.$visitor['all_visitors'].'],';
	if ($max_data<$visitor['all_visitors']) {
		$max_data=$visitor['all_visitors'];
	}
}
echo "];window.max_all_visitors=$max_data;";
// }
// { page requests
$data=dbAll(
	'select unix_timestamp(date(log_date)) as log_d,count(log_date) '
	.'as page_requests,log_type from logs where log_type="page" group by '
	.'log_type,log_d order by log_date'
);
echo 'window.page_requests=[';
$max_data=0;
foreach ($data as $requests_per_day) {
	echo '['.$requests_per_day['log_d'].'000,'
		.$requests_per_day['page_requests'].'],';
	if ($max_data<$requests_per_day['page_requests']) {
		$max_data=$requests_per_day['page_requests'];
	}
}
echo "];window.max_page_requests=$max_data;";
// }
// { data requests
$data=dbAll(
	'select unix_timestamp(date(log_date)) as log_d,count(log_date) '
	.'as all_requests from logs group by log_d order by log_date'
);
echo 'window.all_requests=[';
$max_data=0;
foreach ($data as $requests_per_day) {
	echo '['.$requests_per_day['log_d'].'000,'
		.$requests_per_day['all_requests'].'],';
	if ($max_data<$requests_per_day['all_requests']) {
		$max_data=$requests_per_day['all_requests'];
	}
}
echo "];window.max_all_requests=$max_data;";
// }
// { bandwidth usage
$data=dbAll(
	'select unix_timestamp(date(log_date)) as log_d,'
	.'(sum(bandwidth)/1000000) as bandwidth_usage from logs group by log_d '
	.'order by log_date'
);
echo 'window.bandwidth_usage=[';
$max_data=0;
foreach ($data as $requests_per_day) {
	echo '['.$requests_per_day['log_d'].'000,'
		.$requests_per_day['bandwidth_usage'].'],';
	if ($max_data<$requests_per_day['bandwidth_usage']) {
		$max_data=$requests_per_day['bandwidth_usage'];
	}
}
echo "];window.max_bandwidth_usage=$max_data;";
// }
echo '</script>';

echo '<script type="text/javascript" src="stats/summary.js"></script>';
