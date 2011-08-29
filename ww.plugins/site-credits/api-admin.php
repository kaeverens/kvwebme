<?php
function SiteCredits_adminStatusGet() {
	return array(
		'credits'=>(float)@$GLOBALS['DBVARS']['sitecredits-credits'],
		'upcoming'=>dbAll(
			'select description,amt,next_payment_date from sitecredits_recurring '
			.'where next_payment_date<date_add(now(), interval 1 week)'
		)
	);
}
