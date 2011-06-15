<?php
if ($page->id && isset($_SESSION['ab_testing_targets']['p'.$page->id])) {
	$sql='delete from abtesting_pages where from_id='
		.$_SESSION['ab_testing_targets']['p'.$page->id]
		.' and ipaddress="'.$_SERVER['REMOTE_ADDR'].'"';
	dbQuery($sql);
	$sql='insert into abtesting_pages set from_id='
		.$_SESSION['ab_testing_targets']['p'.$page->id]
		.',ipaddress="'.$_SERVER['REMOTE_ADDR'].'",'
		.'variant_chosen='.$_SESSION['ab_testing'][
			'p'.$_SESSION['ab_testing_targets']['p'.$page->id]
		];
	dbQuery($sql);
}
