<?php
/**
  * stats lib file
  *
  * PHP Version 5
  *
  * @category Stats
  * @package  WebWorksWebme
  * @author   Kae Verens <kae@webworks.ie>
  * @license  GPL Version 2
  * @link     www.webworks.ie
 */

/**
  * reads the site log file and updates the database with its contents.
	* then clears the log file.
  *
  * @return void
**/
function WebME_Stats_update() {
	$f=file(USERBASE.'log.txt');
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
	file_put_contents(USERBASE.'log.txt', '');
}
