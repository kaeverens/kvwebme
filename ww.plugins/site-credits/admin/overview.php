<?php

if (!Core_isAdmin()) {
	exit;
}

echo '<p>You have <strong>'.((int)$GLOBALS['DBVARS']['sitecredits-credits'])
	.'</strong> credits.</p><button id="buy-credits">Buy Credits</button>';
WW_addScript('/ww.plugins/site-credits/admin/overview.js');
