<?php
/**
	* partly from https://www.paypaltech.com/SG2/
	* read the post from PayPal system and add 'cmd'
	*
	* PHP version 5
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     None
*/
$req = 'cmd=_notify-validate';
foreach ($_POST as $key => $value) {
	$value = urlencode(stripslashes($value));
	$req .= "&$key=$value";
}
if ($_POST['payment_status'] == 'Refunded') {
	Core_quit();
}
if ($req=='cmd=_notify-validate') {
	die('please don\'t access this file directly');
}
// post back to PayPal system to validate
$header = "POST /cgi-bin/webscr HTTP/1.0\r\n";
$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
$fp = fsockopen('ssl://www.paypal.com', 443, $errno, $errstr, 30);
if (!$fp) {
	// HTTP ERROR
}
else {
	fputs($fp, $header . $req);
	while (!feof($fp)) {
		$res = fgets($fp, 1024);
		if (strcmp($res, "VERIFIED") == 0) {
			require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
			$id=(int)$_POST['item_number'];
			if ($id<1) {
				Core_quit();
			}
			// create ad
			$data=dbRow('select * from ads_purchase_orders where id='.$id);
			dbQuery('insert into ads set name="ad",customer_id='.$data['user_id']
				.',target_url="'.addslashes($data['target_url']).'",cdate=now()'
				.',target_type="'.addslashes($data['target_type']).'"'
				.',is_active=1,type_id='.$data['type_id']
				.',date_expire=date_add(now(), interval '.$data['days'].' day)');
			$ad_id=dbLastInsertId();
			// { poster 
			$url=false;
			$dirname=USERBASE.'/f/userfiles/'.$data['user_id'].'/ads-upload-poster';
			if (file_exists($dirname)) {
			$dir=new DirectoryIterator($dirname);
			foreach ($dir as $file) {
				if ($file->isDot()) {
					continue;
				}
				$url='userfiles/'.$data['user_id'].'/ads-upload-poster/'.$file->getFilename();
			}
			}
			$newName='/f/userfiles/'.$data['user_id'].'/ad-poster-'.$ad_id.'.'
				.preg_replace('/.*\./', '', $url);
			if ($url) {
				rename(
					USERBASE.'/f/'.$url,
					USERBASE.$newName
				);
				dbQuery(
					'update ads set poster="'.addslashes($newName).'" where id='.$ad_id
				);
			}
			// }
			// { image
			$url=false;
			$dir=new DirectoryIterator(
				USERBASE.'/f/userfiles/'.$data['user_id'].'/ads-upload'
			);
			foreach ($dir as $file) {
				if ($file->isDot()) {
					continue;
				}
				$url='userfiles/'.$data['user_id'].'/ads-upload/'.$file->getFilename();
			}
			$newName='/f/userfiles/'.$data['user_id'].'/ad-'.$ad_id.'.'
				.preg_replace('/.*\./', '', $url);
			rename(
				USERBASE.'/f/'.$url,
				USERBASE.$newName
			);
			dbQuery(
				'update ads set image_url="'.addslashes($newName).'" where id='.$ad_id
			);
			// }
		}
		else if (strcmp($res, "INVALID") == 0) {
		}
	}
	fclose($fp);
}
