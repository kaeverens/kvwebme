<?php
/**
  * stats lib file
  *
  * PHP Version 5
  *
  * @category Stats
  * @package  WebWorksWebme
  * @author   Kae Verens <kae@kvsites.ie>
  * @license  GPL Version 2
  * @link     www.kvweb.me
 */

/**
  * reads the site log file and updates the database with its contents.
	* then clears the log file.
  *
  * @return void
**/
function WebME_Stats_update() {
	$time=time()+30;
	$domains='kvsites.ie,kvwebme,kvsites.com';
	$domains=explode(',', $domains);
	$f=file(USERBASE.'/log.txt');
	foreach ($f as $l) {
		list(
			$tmp,$type_data,$user_agent,$referer,
			$ram_used,$bandwidth,$time_to_render,$db_calls
		)=explode("	", $l);
		$ram_used=(int)$ram_used;
		$bandwidth=(int)$bandwidth;
		$time_to_render=(float)$time_to_render;
		$db_calls=(int)$db_calls;
		$bits=explode(' ', $tmp);
		list($log_date,$log_type,$ip_address)=array(
			$bits[0].' '.$bits[1],$bits[2],$bits[4]
		);
		dbQuery(
			"insert into logs values('$log_date','$log_type','$ip_address','"
			.addslashes($type_data)."','".addslashes($user_agent)."','"
			.addslashes($referer)
			."',$ram_used,$bandwidth,$time_to_render,$db_calls)"
		);
	}
	file_put_contents(USERBASE.'/log.txt', '');
	do {
		$cdate=dbOne(
			'select date(log_date) as cdate from logs '
			.'where log_date<date(now()) limit 1',
			'cdate'
		);
		if ($cdate) {
			// { logs archive
			$unique_visitors=dbOne(
				'select count(*) as visitors from (select distinct ip_address from'
				.' logs where log_date>"'.$cdate.'" and log_date<"'.$cdate.' 25") as s1',
				'visitors'
			);
			$page_views=dbOne(
				'select count(type_data) as page_views from logs where log_type="page" and '
				.'log_date>"'.$cdate.'" and log_date<"'.$cdate.' 25"',
				'page_views'
			);
			$other=dbRow(
				'select sum(ram_used) as ram,sum(bandwidth) as bandwidth,'
				.'sum(time_to_render) as rendertime,sum(db_calls) as dbcalls '
				.'from logs where log_type="page" and log_date>"'.$cdate.'" '
				.'and log_date<"'.$cdate.' 25"'
			);
			dbQuery(
				'insert into logs_archive set cdate="'.$cdate.'",'
				.'unique_visitors='.$unique_visitors.',page_loads='.$page_views
				.',ram_used='.$other['ram'].',bandwidth_used='.$other['bandwidth']
				.',render_time='.$other['rendertime'].',db_calls='.$other['dbcalls']
			);
			// }
			// { popular pages
			$pages=dbAll(
				'select count(type_data) as amt,type_data from logs where '
				.'log_type="page" and log_date>"'.$cdate.'" and log_date<"'
				.$cdate.' 25" group by type_data order by amt desc limit 50'
			);
			foreach ($pages as $page) {
				$url=preg_replace('/.*\|/', '', $page['type_data']);
				dbQuery(
					'insert into logs_pages set cdate="'.$cdate.'"'
					.',page="'.addslashes($url).'",amt="'.$page['amt'].'"'
				);
			}
			// }
			// { referers
			$notin='referer not like "%'.join('%" and referer not like "%', $domains)
				.'%" and referer not like "%'
				.str_replace('www.', '', $_SERVER['HTTP_HOST']).'%"';
			$referers=dbAll(
				'select count(referer) as amt,referer,type_data from logs where '
				.'referer!="" and log_type="page" and log_date>"'.$cdate.'" and '
				.'log_date<"'.$cdate.' 25" and '.$notin.' group by referer'
				.',type_data order by amt desc limit 50'
			);
			foreach ($referers as $referer) {
				$url=preg_replace('/.*\|/', '', $referer['type_data']);
				dbQuery(
					'insert into logs_referers set cdate="'.$cdate.'",referer="'
					.addslashes($referer['referer']).'",page="'.addslashes($url).'"'
					.',amt='.$referer['amt']
				);
			}
			// }
			dbQuery('delete from logs where log_date like "'.$cdate.'%"');
		}
	} while ($cdate && time()<$time);
}
