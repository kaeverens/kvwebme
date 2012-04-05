<?php

if (!$vars->sms_addressbook_id) {
	$rs=dbAll('select id,name from sms_addressbooks order by name');
}
else {
	$vars->sms_addressbook_id=preg_replace(
		'/[^,0-9]/',
		'',
		$vars->sms_addressbook_id
	);
	$rs=dbAll(
		'select id,name from sms_addressbooks where id in ('
		.$vars->sms_addressbook_id.') order by name'
	);
}
if (!count($rs)) {
	$html='<em>Missing SMS addressbook.</em>';
}
else {
	if (count($rs)>1) {
		$list='<tr><th>Lists</th><td><ul class="sms-addressbook-list">';
		foreach ($rs as $r) {
			$list.='<li><input type="checkbox" value="'.$r['id']
				.'" checked="checked" />'
				.htmlspecialchars($r['name'])
				.'</li>';
		}
		$list.'</ul></td></tr>';
	}
	else {
		$list='<input style="display:none" type="checkbox" value="'
			.$rs[0]['id'].'" checked="checked" />';
	}
	$html='<div class="sms-subscribe">'
		.'<p>Subscribe to our SMS list.</p>'
		.'<table>'
		.'<tr><th>Name</th><td><input class="sms-name" /></td></tr>'
		.'<tr><th>Mobile</th><td><input class="sms-phone" /></td></tr>'
		.$list
		.'<tr><th colspan="2"><button>Subscribe</button></th></tr>'
		.'</table>'
		.'</div>';
	WW_addScript('sms/frontend/widget.js');
}
