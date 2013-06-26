<?php

function OnlineStoreEbay_adminCheckEbayCats() {
	$cats=$_REQUEST['cats'];
	$invalids=array();
	foreach ($cats as $c) {
		$r=dbRow('select ebay_id from products_categories where id='.(int)$c);
		if ($r['ebay_id']=='0') {
			$invalids[]=$c;
		}
	}
	return array(
		'invalids'=>$invalids
	);
}
function OnlineStoreEbay_adminGetEbayCats() {
	function import($pid) {
		global $appid;
		$f=file_get_contents('http://open.api.ebay.com/Shopping?callname=GetCategoryInfo&appid='.$appid.'&siteid=205&CategoryID='.$pid.'&version=729&IncludeSelector=ChildCategories');
		$f=str_replace(array("\r", "\n"), '', $f);
		$f=preg_replace('/>\s*</', '><', $f);
		if (strpos($f, 'CategoryArray')===false) {
			return;
		}
		$f=preg_replace(
			'/.*<CategoryArray><Category>|<\/Category><\/CategoryArray>.*/',
			'', $f
		);
		$cats=explode('</Category><Category>', $f);
		foreach ($cats as $cat) {
			$catid=(int)preg_replace('/.*<CategoryID>([^<]*)<.*/', '\1', $cat);
			if ($catid==$pid) {
				continue;
			}
			$level=(int)preg_replace('/.*<CategoryLevel>([^<]*)<.*/', '\1', $cat);
			$name=preg_replace('/.*<CategoryName>([^<]*)<.*/', '\1', $cat);
			$parent_id=(int)preg_replace('/.*<CategoryParentID>([^<]*)<.*/', '\1', $cat);
			$name_path=preg_replace('/.*<CategoryNamePath>([^<]*)<.*/', '\1', $cat);
			$id_path=preg_replace('/.*<CategoryIDPath>([^<]*)<.*/', '\1', $cat);
			$leaf=(int)preg_replace('/.*<LeafCategory>([^<]*)<.*/', '\1', $cat);
			dbQuery('delete from ebay_categories where id='.$catid);
			dbQuery(
				'insert into ebay_categories set id='.$catid
				.', level='.$level
				.', name="'.addslashes($name).'"'
				.', parent_id='.$parent_id
				.', name_path="'.addslashes($name_path).'"'
				.', id_path="'.addslashes($id_path).'"'
				.', leaf='.$leaf
			);
		}
	}
	$pid=(int)$_REQUEST['id'];
	$rs=dbAll('select * from ebay_categories where parent_id='.$pid);
	if (!count($rs)) {
		$GLOBALS['appid']=dbOne(
			'select val from online_store_vars where name="ebay_appid"', 'val'
		);
		import($pid);
		$rs=dbAll('select * from ebay_categories where parent_id='.$pid);
	}
	$arr=array();
	foreach ($rs as $r) {
		$arr[$r['id']]=$r['name'];
	}
	return $arr;
}
function OnlineStoreEbay_adminLinkEbayCat() {
	$id=(int)$_REQUEST['id'];
	$ebay_id=(int)$_REQUEST['ebay_id'];
	dbQuery('update products_categories set ebay_id='.$ebay_id.' where id='.$id);
}
function OnlineStoreEbay_adminPublish() {
	require_once 'eBaySession.php';
	error_reporting(E_ALL);
	$rs=dbAll('select * from online_store_vars where name like "ebay%"');
	$vs=array();
	foreach ($rs as $r) {
		$vs[$r['name']]=$r['val'];
	}
	$production=(int)$vs['ebay_status'];
	if ($production) {
		$devID=$vs['ebay_devid'];
		$appID=$vs['ebay_appid'];
		$certID=$vs['ebay_certid'];
		$serverUrl = 'https://api.ebay.com/ws/api.dll';	  // server URL different for prod and sandbox
		$userToken=$vs['ebay_usertoken'];
	}
	else {  
		$devID=$vs['ebay_sandbox_devid'];
		$appID=$vs['ebay_sandbox_appid'];
		$certID=$vs['ebay_sandbox_certid'];
		$serverUrl='https://api.sandbox.ebay.com/ws/api.dll';
		$userToken=$vs['ebay_sandbox_usertoken'];
	}
	$compatabilityLevel=823;	// eBay API version
	$siteToUseID=205;
	$sess=new eBaySession(
		$userToken, $devID, $appID, $certID, $serverUrl,
		$compatabilityLevel, $siteToUseID, 'AddItem'
	);
	$price=(float)$_REQUEST['buy_now_price'];
	$bidsStartPrice=(float)$_REQUEST['bids_start_at'];
	$countryFrom=$vs['ebay_country_from'];
	$currency='EUR';
	$dispatchDays=$vs['ebay_dispatch_days'];
	$product=Product::getInstance((int)$_REQUEST['id']);
	$description=$product->get('description');
	$paypalAddress=$vs['ebay_paypal_address'];
	$categoryId=dbOne('select ebay_id from products_categories, products_categories_products where product_id='.$product->id.' and category_id=id', 'ebay_id');
	$howMany=1;
	$returnsPolicy=$vs['ebay_returns_policy'];
	$title=$product->get('name');
	$xml='<?xml version="1.0" encoding="utf-8"?>'."\n"
		.'<AddItemRequest xmlns="urn:ebay:apis:eBLBaseComponents">'
		.'<ErrorLanguage>en_US</ErrorLanguage><WarningLevel>High</WarningLevel>';
	// { main details
	$xml.='<Item>'
		.'<Site>Ireland</Site>'
		.'<Title>'.htmlspecialchars($title).'</Title>';
	// }
	// { price
	$xml.='<BuyItNowPrice>'.sprintf('%.2f', $price).'</BuyItNowPrice>'
		.'<Currency>'.$currency.'</Currency>';
	if ($bidsStartPrice>=.01) {
		$xml.='<StartPrice>'.$bidsStartPrice.'</StartPrice>'
			.'<ListingType>Chinese</ListingType>';
	}
	else {
		$xml.='<ListingType>FixedPriceItem</ListingType>';
	}
	// }
	// { pictures
	$xml.='<PictureDetails>';
	$images=$product->getAllImages();
	$xml.='<PictureURL>http://'.$_SERVER['HTTP_HOST'].'/f'.$product->getDefaultImage().'</PictureURL>';
	$images_html='';
	foreach ($images as $img) {
		$imgUrl='http://'.$_SERVER['HTTP_HOST'].'/f'.$img;
		$xml.='<PictureURL>'.$imgUrl.'</PictureURL>';
		$images_html.='<img src="'.$imgUrl.'"/>';
	}
	$xml.='</PictureDetails>';
	// }
	// { other main stuff
	$xml.='<CategoryMappingAllowed>true</CategoryMappingAllowed>'
		.'<ConditionID>1000</ConditionID>'
		.'<Country>'.$countryFrom.'</Country>'
		.'<Location>China</Location>'
		.'<Description>'.htmlspecialchars($description.$images_html).'</Description>'
		.'<DispatchTimeMax>'.$dispatchDays.'</DispatchTimeMax>'
		.'<PayPalEmailAddress>'.$paypalAddress.'</PayPalEmailAddress>'
		.'<ListingDuration>Days_7</ListingDuration>'
		.'<PaymentMethods>PayPal</PaymentMethods>';
	// }
	$xml.='<PrimaryCategory><CategoryID>'.$categoryId.'</CategoryID></PrimaryCategory>'
		.'<Quantity>'.$howMany.'</Quantity>'
		// { refunds and returns
		.'<ReturnPolicy><ReturnsAcceptedOption>ReturnsAccepted</ReturnsAcceptedOption>'
		.'<ReturnsWithinOption>Days_30</ReturnsWithinOption>'
		.'<Description>'.htmlspecialchars($returnsPolicy).'</Description>'
		.'<ShippingCostPaidByOption>Buyer</ShippingCostPaidByOption>'
		.'</ReturnPolicy>'
		// }
		// { shipping
		.'<ShippingDetails>'
		.'<InternationalShippingServiceOption><ShippingService>IE_SellersStandardRateInternational</ShippingService><ShippingServiceAdditionalCost currencyID="EUR">0</ShippingServiceAdditionalCost><ShippingServiceCost currencyID="EUR">0</ShippingServiceCost><ShippingServicePriority>0</ShippingServicePriority><ShipToLocation>Europe</ShipToLocation></InternationalShippingServiceOption>'
		.'<ShippingServiceOptions><FreeShipping>true</FreeShipping><ShippingService>IE_EconomyDeliveryFromAbroad</ShippingService></ShippingServiceOptions>'
		.'</ShippingDetails>'
		// }
		.'</Item>'
		.'<RequesterCredentials>'
		.'<eBayAuthToken>'.$userToken.'</eBayAuthToken>'
		.'</RequesterCredentials>'
		.'<WarningLevel>High</WarningLevel>'
		.'</AddItemRequest>';
	$xmlstr=$sess->sendHttpRequest($xml);
	$reply=new SimpleXMLElement($xmlstr);
	$errors=isset($reply->Errors)?$reply->Errors:false;
	return array(
		'sent'=>$xml,
		'reply'=>new SimpleXMLElement($xmlstr),
		'errors'=>$errors
	);
}
function OnlineStoreEbay_adminLinkProductToEbay() {
	$id=(int)$_REQUEST['id'];
	$ebay_id=(int)$_REQUEST['ebay_id'];
	dbQuery(
		'update products set ebay_currently_active=1,ebay_id='.$ebay_id
		.' where id='.$id
	);
}
function OnlineStoreEbay_adminListShipping() {
	require_once 'eBaySession.php';
	error_reporting(E_ALL);
	$rs=dbAll('select * from online_store_vars where name like "ebay%"');
	$vs=array();
	foreach ($rs as $r) {
		$vs[$r['name']]=$r['val'];
	}
	$production=(int)$vs['ebay_status'];
	if ($production) {
		$devID=$vs['ebay_devid'];
		$appID=$vs['ebay_appid'];
		$certID=$vs['ebay_certid'];
		$serverUrl = 'https://api.ebay.com/ws/api.dll';	  // server URL different for prod and sandbox
		$userToken=$vs['ebay_usertoken'];
	}
	else {  
		$devID=$vs['ebay_sandbox_devid'];
		$appID=$vs['ebay_sandbox_appid'];
		$certID=$vs['ebay_sandbox_certid'];
		$serverUrl='https://api.sandbox.ebay.com/ws/api.dll';
		$userToken=$vs['ebay_sandbox_usertoken'];
	}
	$compatabilityLevel=823;	// eBay API version
	$siteToUseID=205;
	$sess=new eBaySession(
		$userToken, $devID, $appID, $certID, $serverUrl,
		$compatabilityLevel, $siteToUseID, 'AddItem'
	);
	$xml='<?xml version="1.0" encoding="utf-8"?>'."\n"
		.'<GeteBayDetailsRequest xmlns="urn:ebay:apis:eBLBaseComponents">'
		.'<ErrorLanguage>en_US</ErrorLanguage><WarningLevel>High</WarningLevel>'
		.'<RequesterCredentials>'
		.'<eBayAuthToken>'.$userToken.'</eBayAuthToken>'
		.'</RequesterCredentials>'
		.'<DetailName>ShippingServiceDetails</DetailName>'
		.'<WarningLevel>High</WarningLevel>'
		.'</GeteBayDetailsRequest>';
	$xmlstr=$sess->sendHttpRequest($xml);
	$xml=new SimpleXMLElement($xmlstr);
	return $xml;
}
function OnlineStoreEbay_adminListShipTo() {
	require_once 'eBaySession.php';
	error_reporting(E_ALL);
	$rs=dbAll('select * from online_store_vars where name like "ebay%"');
	$vs=array();
	foreach ($rs as $r) {
		$vs[$r['name']]=$r['val'];
	}
	$production=(int)$vs['ebay_status'];
	if ($production) {
		$devID=$vs['ebay_devid'];
		$appID=$vs['ebay_appid'];
		$certID=$vs['ebay_certid'];
		$serverUrl = 'https://api.ebay.com/ws/api.dll';	  // server URL different for prod and sandbox
		$userToken=$vs['ebay_usertoken'];
	}
	else {  
		$devID=$vs['ebay_sandbox_devid'];
		$appID=$vs['ebay_sandbox_appid'];
		$certID=$vs['ebay_sandbox_certid'];
		$serverUrl='https://api.sandbox.ebay.com/ws/api.dll';
		$userToken=$vs['ebay_sandbox_usertoken'];
	}
	$compatabilityLevel=823;	// eBay API version
	$siteToUseID=205;
	$sess=new eBaySession(
		$userToken, $devID, $appID, $certID, $serverUrl,
		$compatabilityLevel, $siteToUseID, 'AddItem'
	);
	$xml='<?xml version="1.0" encoding="utf-8"?>'."\n"
		.'<GeteBayDetailsRequest xmlns="urn:ebay:apis:eBLBaseComponents">'
		.'<ErrorLanguage>en_US</ErrorLanguage><WarningLevel>High</WarningLevel>'
		.'<RequesterCredentials>'
		.'<eBayAuthToken>'.$userToken.'</eBayAuthToken>'
		.'</RequesterCredentials>'
		.'<DetailName>ShippingToLocations</DetailName>'
		.'<WarningLevel>High</WarningLevel>'
		.'</GeteBayDetailsRequest>';
	$xmlstr=$sess->sendHttpRequest($xml);
	$xml=new SimpleXMLElement($xmlstr);
	return $xml;
}
