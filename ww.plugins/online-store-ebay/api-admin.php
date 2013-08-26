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
	$productId=(int)$_REQUEST['id'];
	$product=Product::getInstance($productId);
	$description=$product->get('description');
	$paypalAddress=$vs['ebay_paypal_address'];
	$categoryId=dbOne('select ebay_id from products_categories, products_categories_products where product_id='.$productId.' and category_id=id', 'ebay_id');
	$howMany=(int)$_REQUEST['quantity'];
	$returnsPolicy=$vs['ebay_returns_policy'];
	$title=$product->get('name');
	$xml='<?xml version="1.0" encoding="utf-8"?>'."\n"
		.'<AddItemRequest xmlns="urn:ebay:apis:eBLBaseComponents">'
		.'<ErrorLanguage>en_US</ErrorLanguage><WarningLevel>High</WarningLevel>';
	// { main details
	$xml.='<Item>'
		.'<ApplicationData>{"productId":'.$productId.'}</ApplicationData>'
		.'<Site>Ireland</Site>'
		.'<Title>'.htmlspecialchars($title).'</Title>';
	// }
	// { price
	$xml.='<Currency>'.$currency.'</Currency>';
	if ($bidsStartPrice>=.01) {
		$xml.='<BuyItNowPrice>'.sprintf('%.2f', $price).'</BuyItNowPrice>'
			.'<StartPrice>'.sprintf('%.2f', $bidsStartPrice).'</StartPrice>'
			.'<ListingType>Chinese</ListingType>';
	}
	else {
		$xml.='<StartPrice>'.sprintf('%.2f', $price).'</StartPrice>'
			.'<ListingType>FixedPriceItem</ListingType>';
	}
	// }
	// { pictures
	$xml.='<PictureDetails>';
	$images=$product->getAllImages();
	$siteurl=isset($GLOBALS['DBVARS']['cdn'])&&$GLOBALS['DBVARS']['cdn']
		?$GLOBALS['DBVARS']['cdn']:$_SERVER['HTTP_HOST'];
	$xml.='<PictureURL>http://'.$siteurl.'/f'.$product->getDefaultImage().'</PictureURL>';
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
		.'<InternationalShippingServiceOption>'
		.'<ShippingService>IE_SellersStandardRateInternational</ShippingService>'
		.'<ShippingServiceAdditionalCost currencyID="EUR">0</ShippingServiceAdditionalCost><ShippingServiceCost currencyID="EUR">0</ShippingServiceCost><ShippingServicePriority>0</ShippingServicePriority><ShipToLocation>Europe</ShipToLocation></InternationalShippingServiceOption>'
		.'<ShippingServiceOptions><FreeShipping>true</FreeShipping>'
		.'<ShippingService>IE_EconomyDeliveryFromAbroad</ShippingService>'
		.'<ShippingServiceAdditionalCost currencyID="EUR">0</ShippingServiceAdditionalCost>'
		.'</ShippingServiceOptions>'
		.'</ShippingDetails>'
		// }
		// { brand, etc
		.'<ItemSpecifics>'
		.'<NameValueList><Name>Brand</Name><Value>Generic</Value></NameValueList>'
		.'<NameValueList><Name>Size</Name><Value>Free Size</Value></NameValueList>'
		.'</ItemSpecifics>'
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
function OnlineStoreEbay_adminImportOrders() {
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
	$compatabilityLevel=827;	// eBay API version
	$siteToUseID=205;
	$sess=new eBaySession(
		$userToken, $devID, $appID, $certID, $serverUrl,
		$compatabilityLevel, $siteToUseID, 'GetOrders'
	);
	$xml='<?xml version="1.0" encoding="utf-8"?>'
		.'<GetOrdersRequest xmlns="urn:ebay:apis:eBLBaseComponents">'
		.'	<RequesterCredentials>'
		.'		<eBayAuthToken>'.$userToken.'</eBayAuthToken>'
		.'	</RequesterCredentials>'
		.'	<NumberOfDays>10</NumberOfDays>'
		.'	<OrderRole>Seller</OrderRole>'
		.'	<OrderStatus>Completed</OrderStatus>'
		.'	<DetailLevel>ReturnAll</DetailLevel>'
		.'	<SortingOrder>Descending</SortingOrder>'
		#.'	<OutputSelector> string </OutputSelector>'
		.'	<WarningLevel>High</WarningLevel>'
		.'</GetOrdersRequest>';
	$xmlstr=$sess->sendHttpRequest($xml);
	$reply=new SimpleXMLElement($xmlstr);
	if (isset($reply->Errors)) {
		return array(
			'sent'=>$xml,
			'reply'=>new SimpleXMLElement($xmlstr),
			'errors'=>$reply->Errors
		);
	}
	$imported=0;
	foreach ($reply->OrderArray->Order as $order) {
		$order=json_decode(json_encode($order));
		$ebayOrderId=$order->OrderID;
		$r=dbOne(
			'select id from online_store_orders where ebayOrderId="'.$ebayOrderId.'"'
			.' limit 1', 'id'
		);
		if ($r) {
			continue;
		}
		$address=$order->ShippingAddress;
		$form_vals=array(
			'FirstName'=>preg_replace('/ .*/', '', $address->Name),
			'Surname'=>preg_replace('/.*? /', '', $address->Name),
			'Phone'=>$address->Phone,
			'Email'=>'ebay@kaebots.com',
			'Street'=>$address->Street1,
			'Street2'=>$address->Street2,
			'Town'=>$address->CityName,
			'County'=>$address->StateOrProvince,
			'PostCode'=>$address->PostalCode,
			'Country'=>$address->CountryName,
			'CountryCode'=>$address->Country
		);
		$form_vals=json_encode($form_vals);
		$total=(float)$order->Total;
		$date_created=$order->CreatedTime;
		$transactions=array();
		$tArr=$order->TransactionArray->Transaction;
		if (!is_array($tArr)) {
			$transactions=array($tArr);
		}
		else {
			$transactions=$tArr;
		}
		$items=array();
		foreach ($transactions as $transaction) {
			$item=$transaction->Item;
			$appData=json_decode(htmlspecialchars_decode($item->ApplicationData));
			$itemId=$appData->productId;
			$key='products_'.$itemId;
			if (!isset($items[$key])) {
				$items[$key]=array();
				$r=dbRow('select * from products where id='.$itemId.' limit 1');
				$items[$key]=array(
					'short_desc'=>$r['name'],
					'id'=>$itemId,
					'amt'=>0
				);
			}
			$items[$key]['amt']+=$transaction->QuantityPurchased;
		}
		$items=json_encode($items);
		dbQuery(
			'insert into online_store_orders set total="'.$total.'"'
			.', items="'.addslashes($items).'"'
			.', ebayOrderId="'.$ebayOrderId.'"'
			.', form_vals="'.addslashes($form_vals).'"'
			.', date_created="'.addslashes($date_created).'"'
			.', status=1'
		);
		$id=dbLastInsertId();
		dbQuery('update online_store_orders set invoice_num=id where id='.$id);
		$imported++;
	}
	return array(
		'imported'=>$imported,
		'reply'=>new SimpleXMLElement($xmlstr)
	);
}
