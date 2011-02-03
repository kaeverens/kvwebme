<?php
require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if(!is_admin())die('access denied');
$id=(int)$_REQUEST['id'];

$addressBooks = dbAll('select id, subscribers from sms_addressbooks');
foreach($addressBooks as $book) {
	$subs = json_decode($book['subscribers']);
	if (!in_array($id, $subs)) {
		continue;
	}
	for ($i=0; $i<count($subs); ++$i) {
		if ($subs[$i]==$id) {
			unset($subs[$i]);
			break;
		}
	}
	$subs = json_encode($subs);
	dbQuery(
		'update sms_addressbooks 
		set subscribers = "'.$subs.'" 
		where id = '.$book['id']
	);
}
dbQuery('delete from sms_subscribers where id='.$id);
echo '{"err":0,"id":'.$id.'}';
