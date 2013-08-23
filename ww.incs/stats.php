<?php

function Stats_value($type, $duration) {
	switch ($type) {
		case 'unique_visitors':
		case 'page_loads':
		break;
		default:
			return 'invalid type';
	}
	$duration=(int)$duration;
	$sql='select sum('.$type.') as val from logs_archive'
		.' where cdate>date_add(now(), interval -'.$duration.' day)';
	return dbOne($sql, 'val');
}
