<?php

if (!Core_isAdmin()) {
	Core_quit();
}

echo '<h2>Credits</h2><p>You have <strong>'.((int)$GLOBALS['DBVARS']['sitecredits-credits'])
	.'</strong> credits.</p><button id="buy-credits">Buy Credits</button>';
WW_addScript('site-credits/admin/overview.js');

echo '<h2>Account history</h2>';
$rs=dbAll(
	'select cdate, description, amt, total from sitecredits_accounts order by '
	.'cdate desc'
);
if ($rs && count($rs)) {
	echo '<table><tr><th>Date</th><th>Description</th><th>Amt</th><th>Total'
		.'</th></tr>';
	foreach ($rs as $r) {
		echo '<tr><td>'.Core_dateM2H($r['cdate']).'</td>'
			.'<td>'.htmlspecialchars($r['description']).'</td>'
			.'<td>'.$r['amt'].'</td><td>'.$r['total'].'</td></tr>';
	}
	echo '</table>';
}
else {
	echo '<p>No credits accounting history to show.</p>';
}
