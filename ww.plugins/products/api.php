<?php
/**
	* API for Products plugin
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

// { Products_arrayToCSV

/**
	* convert an array to a CSV row
	*
	* @param array  $row       row data
	* @param string $delimiter what to separate the fields by
	* @param string $enclosure how should strings be enclosed
	* @param string $eol       what end-of-line character to use
	*
	* @return string the CSV row
	*/
function Products_arrayToCSV(
	$row, $delimiter = ',', $enclosure = '"', $eol = "\n"
) {
	static $fp = false;
	if ($fp === false) {
		$fp = fopen('php://temp', 'r+');
	}
	else {
		rewind($fp);
	}
	if (fputcsv($fp, $row, $delimiter, $enclosure) === false) {
		return false;
	}
	rewind($fp);
	$csv = fgets($fp);
	if ($eol != PHP_EOL) {
		$csv = substr($csv, 0, (0 - strlen(PHP_EOL))) . $eol;
	}
	return $csv;
}

// }
// { Products_categoriesOptionsGet

/**
	* show a list of categories
	*
	* @return null
	*/
function Products_categoriesOptionsGet() {
	$selected=$_REQUEST['selected'];
	$rs=dbAll('select id,name from products_categories where enabled order by name');
	$arr=array();
	foreach ($rs as $r) {
		$arr[$r['id']]=$r['name'];
	}
	return $arr;
}

// }
// { Products_categoriesGetFull

/**
	* return a list of categories and ids, named in full
	*
	* @return array
	*/
function Products_categoriesGetFull() {
	$pid=(int)$_REQUEST['pid'];
	function getFull($pid, $prefix='') {
		$rs=dbAll('select name,id from products_categories where parent_id='.$pid);
		$cats=array();
		foreach ($rs as $r) {
			$cats[$prefix.$r['name']]=$r['id'];
			$cats=array_merge($cats, getFull($r['id'], $prefix.$r['name'].' - '));
		}
		return $cats;
	}
	return getFull($pid);
}

// }
// { Products_categoryUnwatch
/**
	* unwatch this category
	*
	* @return null
	*/

function Products_categoryUnwatch() {
	$cid=(int)$_REQUEST['cid'];
	$uid=(int)$_SESSION['userdata']['id'];
	if (!$uid || !$cid) {
		return array('error'=>'no category selected or not logged in');
	}
	dbQuery(
		'delete from products_watchlists where user_id='.$uid
		.' and category_id='.$cid
	);
	return array('ok'=>1);
}

// }
// { Products_categoryWatch
/**
	* watch this category
	*
	* @return null
	*/

function Products_categoryWatch() {
	$cid=(int)$_REQUEST['cid'];
	$uid=(int)$_SESSION['userdata']['id'];
	if (!$uid || !$cid) {
		return array('error'=>'no category selected or not logged in');
	}
	Products_categoryUnwatch();
	dbQuery(
		'insert into products_watchlists set user_id='.$uid.', category_id='.$cid
	);
	return array('ok'=>1);
}

// }
// { Products_categoryWatches
/**
	* get list of watches
	*
	* DEPRECATED: Kae to find all uses of this and replace with
	*   Products_watchlistsGet then remove this function
	*
	* @return null
	*/

function Products_categoryWatches() {
	$uid=(int)$_SESSION['userdata']['id'];
	if (!$uid) {
		return array('error'=>'not logged in');
	}
	$rs=dbAll(
		'select category_id from products_watchlists where user_id='.$uid
	);
	$arr=array();
	foreach ($rs as $r) {
		$arr[]=$r['category_id'];
	}
	return $arr;
}

// }
// { Products_getImgs
/**
	* get a list of images
	*
	* @return list of images
	*/

function Products_getImgs() {
	$pid=(int)$_REQUEST['id'];
	$product=Product::getInstance($pid);
	$dir=USERBASE.'/f'.$product->vals['images_directory'];
	$imgs=array();
	if (file_exists($dir)) {
		$dir=new DirectoryIterator($dir);
		foreach ($dir as $file) {
			if ($file->isDot()) {
				continue;
			}
			$imgs[]='/f'.$product->vals['images_directory'].'/'.$file->getFilename();
		}
	}
	return $imgs;
}

// }
// { Products_getProductMainDetails

/**
	* utility function to return an array of common product details
	*
	* @param object $p product
	*/
function Products_getProductMainDetails($p) {
	$parr=array(
		'id'=>$p->id,
		'name'=>__FromJson($p->name)
	);
	if ($p->vals['online-store']) {
		$o=$p->vals['online-store'];
		$parr['_price']=$p->getPriceBase();
		$sale_price=$p->getPriceSale();
		if ($sale_price) {
			$parr['_sale_price']=$sale_price;
		}
		if ($o['_bulk_price']) {
			$parr['_bulk_price']=$o['_bulk_price'];
		}
		if ($o['_bulk_amount']) {
			$parr['_bulk_amount']=$o['_bulk_amount'];
		}
		if ($o['_sold_amt']) {
			$parr['_sold_amt']=$o['_sold_amt'];
		}
		if ($o['_stock_amt']) {
			$parr['_stock_amt']=$o['_stock_amt'];
		}
	}
	$parr['link']=$p->getRelativeUrl();
	return $parr;
}

// }
// { Products_getProductOwnersByCoords

/**
	* get a list of users with active products using map coordinates
	*
	* @return array of product IDs
	*/
function Products_getProductOwnersByCoords() {
	$coords=$_REQUEST['coords'];
	// { sanitise coords
	$x1=(float)$coords[0];
	$x2=(float)$coords[2];
	$y1=(float)$coords[1];
	$y2=(float)$coords[3];
	if ($x2<$x1) {
		$t=$x1;
		$x1=$x2;
		$x2=$t;
	}
	if ($y2<$y1) {
		$t=$y1;
		$y1=$y2;
		$y2=$t;
	}
	// }
	// { get list of relevant users
	$users=dbAll(
		"select id,location_lat, location_lng from user_accounts where
		location_lat>$x1 and location_lat<$x2
		and location_lng>$y1 and location_lng<$y2 and active limit 1000",
		'id'
	);
	if (!count($users)) {
		return array();
	}
	$users2=dbAll(
		'select distinct user_id from products where enabled and user_id in ('
		.join(',', array_keys($users)).')'
	);
	foreach ($users2 as $k=>$v) {
		$users2[$k]=$users[$v['user_id']];
	}
	return $users2;
	// }
}

// }
// { Products_getProductsByUser

/**
	* get a list of products (id, name, relativeUrl) owned by a user
	*
	* @return array of products
	*/
function Products_getProductsByUser() {
	$user_id=(int)$_REQUEST['user_id'];
	$products=array();
	$rs=dbAll('select id from products where user_id='.$user_id.' and enabled');
	foreach ($rs as $r) {
		$p=Product::getInstance($r['id']);
		$products[]=array(
			'id'=>$p->id,
			'name'=>__FromJson($p->name),
			'url'=>$p->getRelativeUrl()
		);
	}
	return $products;
}

// }
// { Products_getProduct

/**
	* return a single product's main details
	*
	* @return array
	*/
function Products_getProduct() {
	$p=Product::getInstance((int)$_REQUEST['id']);
	if (!$p || !$p->id) {
		return false;
	}
	$mainDetails=Products_getProductMainDetails($p);
	if ($p->vals['stockcontrol_details']) {
		$mainDetails['stockcontrol']=json_decode(
			$p->vals['stockcontrol_details'], true
		);
	}
	else {
		$mainDetails['stockcontrol']=false;
	}
	return $mainDetails;
}

// }
// { Products_getRelatedProducts

/**
	* return a list of products by relation
	*/
function Products_getRelatedProducts() {
	$pid=(int)$_REQUEST['id'];
	$rs=dbAll(
		'select * from products_relations where from_id='.$pid.' or to_id='.$pid
	);
	$related=array();
	$rtypes=array();
	foreach ($rs as $r) {
		$rid=(int)$r['relation_id'];
		if (!isset($rtypes[$rid])) {
			$rtypes[$rid]=dbOne(
				'select one_way from products_relation_types where id='.$rid, 'one_way'
			);
		}
		if ($rtypes[$rid]!=1) {
			$related[]=$r['from_id']==$pid?$r['to_id']:$r['from_id'];
		}
		elseif ($r['from_id']==$pid) {
			$related[]=$r['to_id'];
		}
	}
	$related=array_unique($related);
	$products=array();
	foreach ($related as $pid) {
		$p=Product::getInstance($pid);
		if (!$p || !$p->id) {
			continue;
		}
		$products[]=Products_getProductMainDetails($p);
	}
	return $products;
}

// }
// { Products_reviewDelete

/**
	* remove a review
	*
	* @return null
	*/
function Products_reviewDelete() {
	$id = (int)$_REQUEST['id'];
	$productid= (int)$_REQUEST['productid'];
	$userid = (int)dbOne(
		'select user_id from products_reviews where id='.$id, 
		'user_id'
	);
	$user= $_SESSION['userdata']['id'];
	if (!Core_isAdmin() || $user!=$userid) {
		die('You do not have permission to delete this review');
	}
	dbQuery('delete from products_reviews where id='.$id);
	if (dbOne('select id from products_reviews where id='.$id, 'id')) {
		return array('status'=>0);
	}
	$numReviews= (int) dbOne(
		'select count(id) 
		from products_reviews 
		where product_id='.$productid, 
		'count(id)'
	);
	$average = (int) dbOne(
		'select avg(rating)
		from products_reviews
		where product_id='.$productid
		.' group by product_id',
		'avg(rating)'
	);
	return array(
		'status'=>1,
		'id'=>$id,
		'user'=>$user,
		'userid'=>$userid,
		'num'=>$numReviews,
		'avg'=>$average,
		'product'=>$productid
	);
}

// }
// { Products_reviewUpdate

/**
	* Updates a review, calculates the new total and average
	*
	* @return array the updated review
	*/
function Products_reviewUpdate() {
	$id= (int)$_REQUEST['id'];
	$loggedInUser= $_SESSION['userdata']['id'];
	$userWhoLeftReview
		= dbOne(
			'select user_id from products_reviews where id='.$id,
			'user_id'
		);
	if (!(Core_isAdmin()||$loggedInUser==$userWhoLeftReview)) {
		die('You do not have sufficent privileges to edit this review');
	}
	$timeExpired 
		= dbOne(
			'select now()>
				date_add("'.$_REQUEST['cdate'].'", interval 15 minute) as can_edit',
			'can_edit'
		);
	if ($timeExpired) {
		return array('status'=>0, 'message'=>'time has expired');
	}
	$body = addslashes($_REQUEST['text']);
	$rating = (int)$_REQUEST['rating'];
	if (($rating<1||$rating>5)||$id<=0) {
		return array('status'=>0, 'message'=>'Invalid Rating');
	}
	dbQuery(
		'update products_reviews set body="'.$body.'", rating='.$rating
		.' where id='.$id
	);
	$productid=dbOne(
		'select product_id from products_reviews where id='.$id,
		'product_id'
	);
	$average=dbOne(
		'select avg(rating) from products_reviews where product_id='
		.$productid.' group by product_id',
		'avg(rating)'
	);
	$total=dbOne(
		'select count(id) from products_reviews where product_id='.$productid,
		'count(id)'
	);
	$review=dbRow(
		'select rating,body,cdate from products_reviews where id = '.$id
	);
	$rating = $review['rating'];
	$body = $review['body'];
	$date = $review['cdate'];
	$name=dbOne(
		'select name from user_accounts where id='.$userWhoLeftReview,
		'name'
	);
	return array(
		'status'=>1,
		'id'=>$id,
		'product'=>$productid,
		'user_id'=>$userWhoLeftReview,
		'user'=>$name,
		'date'=>$date,
		'rating'=>$rating,
		'body'=>$body,
		'avg'=>$average,
		'total'=>$total
	);
}

// }
// { Products_showDefaultImg

/**
	* show the default image of a product
	*
	* @return null
	*/
function Products_showDefaultImg() {
	$id=(int)$_REQUEST['id'];
	$product=Product::getInstance($id);
	$w=(int)@$_REQUEST['w'];
	$h=(int)@$_REQUEST['h'];
	if ($product) {
		$iid=$product->getDefaultImage();
		if ($iid) {
			header('Location: /a/f=getImg/w='.$w.'/h='.$h.'/'.$iid);
			Core_quit();
		}
	}
	header('Location: /i/blank.gif');
}

// }
// { Products_showQrCode

/**
	* show an image of a QR code leading to a product
	*
	* @return null
	*/
function Products_showQrCode() {
	$pid=(int)$_REQUEST['pid'];
	$product=Product::getInstance($pid);
	if (!$product) {
		redirect('/i/blank.gif');
	}
	$fname=USERBASE.'/ww.cache/products/qr'.$pid;
	if (!file_exists($fname)) {
		require_once SCRIPTBASE.'/ww.incs/phpqrcode.php';
		@mkdir(USERBASE.'/ww.cache/products');
		QRcode::png(
			'http://'.$_SERVER['HTTP_HOST'].$product->getRelativeUrl(),
			$fname
		);
	}
	header('Content-type: image/png');
	header('Cache-Control: max-age=2592000, public');
	header('Expires-Active: On');
	header('Expires: Fri, 1 Jan 2500 01:01:01 GMT');
	header('Pragma:');
	header('Content-Length: ' . filesize($fname));
	readfile($fname);
	Core_quit();
}

// }
// { Products_typeGet

/**
	* get details about a specific product type
	*
	* @return array the product type details
	*/
function Products_typeGet() {
	$id=(int)@$_REQUEST['id'];
	$r=Core_cacheLoad('products', 'productTypeDetails_'.$id, -1);
	if ($r===-1) {
		$r=dbRow("select * from products_types where id=$id");
		$r['default_category_name']=dbOne(
			'select name from products_categories where id='.$r['default_category'],
			'name'
		);
		$r['data_fields']=json_decode($r['data_fields']);
		Core_cacheSave('products', 'productTypeDetails_'.$id, $r);
	}
	return $r;
}

// }
// { Products_typesGet

/**
	* get a list of product types
	*
	* @return array list of product types, in DataTables format
	*/
function Products_typesGet() {
	$rs=dbAll('select name,id from products_types order by name');
	$count=count($rs);
	$result=array(
		'sEcho'=>intval(@$_REQUEST['sEcho']),
		'iTotalRecords'=>$count,
		'iTotalDisplayRecords'=>$count,
		'aaData'=>array()
	);
	foreach ($rs as $r) {
		$result['aaData'][]=array(
			$r['name'],
			$r['id'],
			0
		);
	}
	return $result;
}

// }
// { Products_typesTemplatesGet

/**
	* get a list of pre-created product type templates
	*
	* @return array list of types
	*/
function Products_typesTemplatesGet() {
	$dir=new DirectoryIterator(dirname(__FILE__).'/templates');
	$templates=array();
	foreach ($dir as $file) {
		if ($file->isDot() || !preg_match('/\.json$/', $file->getFilename())) {
			continue;
		}
		$templates[]=str_replace('.json', '', $file->getFilename());
	}
	return $templates;
}

// }
// { Products_watchlistsGet

/**
	* retrieve a user's watchlists
	*
	* @return array
	*/
function Products_watchlistsGet() {
	if (!isset($_SESSION['userdata']['id'])) {
		return array('error'=>__('not logged in'));
	}
	$uid=(int)$_SESSION['userdata']['id'];
	return dbAll('select * from products_watchlists where user_id='.$uid);
}

// }
// { Products_watchlistsSave

/**
	* update a user's watchlists
	*
	* @return array
	*/
function Products_watchlistsSave() {
	if (!isset($_SESSION['userdata']['id'])) {
		return array('error'=>__('not logged in'));
	}
	$uid=(int)$_SESSION['userdata']['id'];
	dbQuery('delete from products_watchlists where user_id='.$uid);
	foreach ($_REQUEST['watchlists'] as $w) {
		dbQuery(
			'insert into products_watchlists set category_id='
			.((int)$w['category_id']).', location_id='.((int)$w['location_id'])
			.', user_id='.$uid
		);
	}
	return dbAll('select * from products_watchlists where user_id='.$uid);
}

// }
