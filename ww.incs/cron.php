<?php
/**
	* script for handling timed events
	*
	*/


if (!isset($DBVARS['cron-next'])) {
	$DBVARS['cron-next']=dbOne(
		'select next_date from cron order by next_date limit 1', 'next_date'
	);
}

if ($DBVARS['cron-next']<date('Y-m-d h:i:s')) {
	$rs=dbAll('select * from cron where next_date<now()');
	foreach ($rs as $r) {
		$r['func']();
		dbQuery(
			'update cron set next_date=date_add(next_date, interval '
			.$r['period_multiplier'].' '.$r['period'].') where id='.$r['id']
		);
	}
	$DBVARS['cron-next']=dbOne(
		'select next_date from cron order by next_date limit 1', 'next_date'
	);
}

Core_configRewrite();
