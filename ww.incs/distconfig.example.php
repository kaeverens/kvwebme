<?php

$config=array(
	'email'=>'kae@kvsites.ie', // contact email for the distro
	'cms-name'=>'kvWebME',     // name of the CMS
	'credits-email'=>'kae@kvsites.ie', // paypal address for credits purchases
	'themes-api'=>'http://kvweb.me/ww.plugins/themes-api',
	'preferred-language'=>'en', // language to choose when asked by JS scripts
	'installer-userbase'=>realpath($_SERVER['DOCUMENT_ROOT']),
	'installer-private'=>$_SERVER['DOCUMENT_ROOT'].'/.private'
);
