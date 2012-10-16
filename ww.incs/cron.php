<?php
/**
	* script for handling timed events
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

/**
	* handle page publishing
	*
	* @return null
	*/
function Core_pagesCronHandle() {
	dbQuery(
		'update pages set special=special-2 where special&2 and '
		.'date_publish<now() and date_unpublish>now()'
	);
	dbQuery(
		'update pages set special=special+2 where !(special&2) and '
		.'date_unpublish<now()'
	);
	Core_cacheClear('pages,menus');
}

while (!isset($DBVARS['cron-next'])
	|| ($DBVARS['cron-next']!=false && $DBVARS['cron-next']<date('Y-m-d H:i:s'))
) {
	$rs=dbAll('select * from cron where next_date<now()');
	// { update existing cron entries
	foreach ($rs as $r) {
		if ($r['period']=='never') {
			continue;
		}
		$r['func']();
		$sql='update cron set next_date=date_add(next_date, interval '
			.$r['period_multiplier'].' '.$r['period'].') where id='.$r['id'];
		dbQuery($sql);
	}
	// }
	// { check pages for upcoming changes
	dbQuery('delete from cron where func="Core_pagesCronHandle"');
	$n1=dbOne(
		'select date_publish from pages where special&2 and '
		.'date_unpublish>now() order by date_publish limit 1', 'date_publish'
	);
	$n2=dbOne(
		'select date_unpublish from pages where !(special&2) and '
		.'date_unpublish!="0000-00-00 00:00:00" order by date_unpublish limit 1',
		'date_unpublish'
	);
	$n=false;
	if ($n1 && $n2) {
		$n=$n1<$n2?$n1:$n2;
	}
	elseif ($n1 || $n2) {
		$n=$n1?$n1:$n2;
	}
	if ($n) {
		dbQuery(
			'insert into cron set name="publish/unpublish page", notes="'
			.addslashes(__('show or hide a page in the site menus'))
			.'", period="day", period_multiplier=1, '
			.'next_date="'.$n.'", func="Core_pagesCronHandle"'
		);
	}
	// }
	// { check each plugin for new cron entries
	foreach ($PLUGINS as $n=>$p) { // set up crons for plugins
		$f=preg_replace('/[^a-zA-Z]/', '', ucwords($n)).'_cronGetNext';
		if (function_exists($f)) {
			$f();
		}
	}
	// }
	$DBVARS['cron-next']=dbOne(
		'select next_date from cron where period!="never"'
		.' order by next_date limit 1', 'next_date'
	);
}

Core_configRewrite();
