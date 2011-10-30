<?php
/**
	* script for handling timed events
	*
	*/


if (!isset($DBVARS['cron-next']) || $DBVARS['cron-next']<date('Y-m-d H:i:s')) {
	$rs=dbAll('select * from cron where next_date<now()');
	foreach ($rs as $r) {
		$r['func']();
		dbQuery(
			'update cron set next_date=date_add(next_date, interval '
			.$r['period_multiplier'].' '.$r['period'].') where id='.$r['id']
		);
	}
	foreach ($PLUGINS as $n=>$p) { // set up crons for plugins
		$f=preg_replace('/[^a-zA-Z]/', '', ucwords($n)).'_cronGetNext';
		if (function_exists($f)) {
			$f();
		}
	}
	$DBVARS['cron-next']=dbOne(
		'select next_date from cron order by next_date limit 1', 'next_date'
	);
}

Core_configRewrite();
