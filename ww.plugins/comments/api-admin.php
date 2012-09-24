<?php

function Comments_adminCaptchasSet() {
	$checked=isset($_REQUEST['value']);
	dbQuery('delete from site_vars where name="comments_no_captchas"');
	if ($checked) {
		dbQuery('insert into site_vars set name="comments_no_captchas", value="1"');
	}
}
