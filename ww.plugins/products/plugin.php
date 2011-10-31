<?php
/**
	* definition file for Products plugin
	*
	* PHP version 5
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     None
	*/

// { plugin declaration
$plugin=array(
	'name' => 'Products',
	'dependencies'=>'image-gallery',
	'admin' => array(
		'menu' => array(
			'Products>Products'   => 'products',
			'Products>Categories' => 'categories',
			'Products>Types'=>'js:Types',
			'Products>Relation Types'=> 'relation-types',
			'Products>Export Data' => 'js:ExportData'
		),
		'page_type' => 'Products_adminPage',
		'widget' => array(
			'form_url'   => '/ww.plugins/products/admin/widget.php',
			'js_include' => array(
				'/ww.plugins/products/admin/widget.js'
			),
		)
	),
	'description' => 'Product catalogue.',
	'frontend' => array(
		'page_type' => 'Products_frontend',
		'widget' => 'Products_widget',
		'template_functions' => array(
			'PRODUCTS_BUTTON_ADD_TO_CART' => array(
				'function' => 'products_get_add_to_cart_button'
			),
			'PRODUCTS_BUTTON_ADD_MANY_TO_CART' => array(
				'function' => 'Products_getAddManyToCartButton'
			),
			'PRODUCTS_CATEGORIES' => array(
				'function' => 'products_categories'
			),
			'PRODUCTS_DATATABLE' => array(
				'function' => 'Products_datatable'
			),
			'PRODUCTS_EXPIRY_CLOCK' => array(
				'function' => 'Products_expiryClock'
			),
			'PRODUCTS_IMAGE' => array(
				'function' => 'Products_image'
			),
			'PRODUCTS_IMAGES' => array(
				'function' => 'Products_images'
			),
			'PRODUCTS_LINK' => array (
				'function' => 'Products_link'
			),
			'PRODUCTS_LIST_CATEGORIES' => array(
				'function' => 'Products_listCategories'
			),
			'PRODUCTS_LIST_CATEGORY_CONTENTS' => array(
				'function' => 'Products_listCategoryContents'
			),
			'PRODUCTS_PLUS_VAT' => array (
				'function' => 'Products_plusVat'
			),
			'PRODUCTS_PRICE_BASE' => array (
				'function' => 'Products_priceBase'
			),
			'PRODUCTS_PRICE_DISCOUNT' => array (
				'function' => 'Products_priceDiscount'
			),
			'PRODUCTS_PRICE_DISCOUNT_PERCENT' => array (
				'function' => 'Products_priceDiscountPercent'
			),
			'PRODUCTS_PRICE_SALE' => array (
				'function' => 'Products_priceSale'
			),
			'PRODUCTS_RELATED' => array (
				'function' => 'Products_showRelatedProducts'
			),
			'PRODUCTS_REVIEWS' => array (
				'function' => 'Products_reviews'
			),
			'PRODUCTS_IMAGES_SLIDER' => array (
				'function' => 'Products_imageSlider'
			)
		)
	),
	'triggers' => array(
		'initialisation-completed' => 'Products_addToCart'
	),
	'version' => '25'
);
// }

function Products_imageSlider($params) {
	$width=@$params['width'];
	$height=@$params['height'];
	if ($width=='') {
		$width='100%';
	}
	if ($height=='') {
		$height='100%';
	}
	return '<div class="products-image-slider" style="width:'.$width.';height:'.$height.'"></div>';
}
function Products_expiryClock($params, $smarty) {
	$unlimited=@$params['none'];
	if ($unlimited=='') {
		$unlimited='no expiry date';
	}
	$pid=$smarty->_tpl_vars['product']->id;
	$product=Product::getInstance($pid);
	return '<div class="products-expiry-clock" unlimited="'.htmlspecialchars($unlimited).'">'.$product->vals['expires_on'].'</div>';
}

/**
  * figure out how much a product costs
  *
  * @param object  $product        the product data
	* @param int     $amount         how many are wanted
	* @param string  $md5            unique identifier
	* @param boolean $removefromcart remove any copies of this from the cart first
  *
  * @return object the product instance
  */
function Products_getProductPrice(
	$product,
	$amount,
	$md5,
	$removefromcart=true
) {
	$id=$product->id;
	if (isset($_SESSION['online-store']['items']['products_'.$id.$md5])) {
		$amount+=$_SESSION['online-store']['items']['products_'.$id.$md5]['amt'];
		if ($removefromcart) {
			unset($_SESSION['online-store']['items']['products_'.$id.$md5]);
		}
	}
	// { get price
	if (isset($product->vals['online-store'])) {
		$p=$product->vals['online-store'];
		$price=(float)$p['_price'];
		if (isset($p['_sale_price']) && $p['_sale_price']>0) {
			$price=$p['_sale_price'];
		}
		if (isset($p['_bulk_price'])
			&& $p['_bulk_price']>0
			&& $p['_bulk_price']<$price
			&& $amount>=$p['_bulk_amount']
		) {
			$price=$p['_bulk_price'];
		}
		$vat=(isset($p['_vatfree']) && $p['_vatfree']=='1')?false:true;
	}
	else {
		$price=(float)$product->get('price');
		$vat=true;
	}
	// }
	return array($price, $amount, $vat);
}

/**
  * form for a products admin page
  *
  * @param array $page the page database table
  * @param array $vars the page's vars data
  *
  * @return HTML of the form
  */
function Products_adminPage($page, $vars) {
	$id=$page['id'];
	$c='';
	require_once dirname(__FILE__).'/admin/page-form.php';
	return $c;
}

/**
  * render a product page
  *
  * @param object $PAGEDATA the page instance
  *
  * @return string HTML of the page
  */
function Products_frontend($PAGEDATA) {
	require_once dirname(__FILE__).'/frontend/show.php';
	global $PAGE_UNUSED_URI;
	if ($PAGE_UNUSED_URI) {
		$bits=explode('/', $PAGE_UNUSED_URI);
		$cat_id=0;
		$product_id=0;
		foreach ($bits as $bit) {
			$id=dbOne(
				'select id from products_categories where parent_id='.$cat_id
				.' and name="'.addslashes($bit).'"',
				'id'
			);
			if ($id) {
				$cat_id=$id;
				$_REQUEST['product_cid']=$cat_id;
			}
			else {
				if ($cat_id) {
					$id=dbOne(
						'select product_id,name from products_categories_products,products'
						.' where category_id='.$cat_id.' and name="'.addslashes($bit).'"'
						.' and id=product_id',
						'product_id'
					);
				}
				if (!$id) {
					$id=dbOne(
						'select id from products where name="'.addslashes($bit).'"',
						'id'
					);
				}
				if ($id) {
					$_REQUEST['product_id']=$id;
				}
			}
		}
	}
	if (isset($_REQUEST['product_category'])) {
		$_REQUEST['product_cid']=$_REQUEST['product_category'];
	}
	if (isset($_REQUEST['product_cid'])) {
		$PAGEDATA->vars['products_what_to_show']=2;
		$PAGEDATA->vars['products_category_to_show']=(int)$_REQUEST['product_cid'];
	}
	if (isset($_REQUEST['product_id'])) {
		$PAGEDATA->vars['products_what_to_show']=3;
		$PAGEDATA->vars['products_product_to_show']=(int)$_REQUEST['product_id'];
	}
	if (!isset($PAGEDATA->vars['footer'])) {
		$PAGEDATA->vars['footer']='';
	}
	// first render the products, in case the page needs to know what template was used
	$producthtml=Products_show($PAGEDATA);
	return $PAGEDATA->render()
		.$producthtml
		.$PAGEDATA->vars['footer'];
}

/**
  * check the $_REQUEST array for products to add to the cart
  *
  * @return null
  */
function Products_addToCart() {
	if (!isset($_REQUEST['products_action'])) {
		return;
	}
	$id=(int)$_REQUEST['product_id'];
	require_once dirname(__FILE__).'/frontend/show.php';
	$product=Product::getInstance($id);
	if (!$product) {
		return;
	}
	$amount=1;
	if (isset($_REQUEST['products-howmany'])) {
		$amount=(int)$_REQUEST['products-howmany'];
	}
	// { find "custom" values
	$price_amendments=0;
	$vals=array();
	$md5='';
	$product_type=ProductType::getInstance($product->vals['product_type_id']);
	$long_desc='';
	foreach ($_REQUEST as $k=>$v) {
		if (strpos($k, 'products_values_')===0) {
			$n=str_replace('products_values_', '', $k);
			$data_field=$product_type->getField($n);
			if ($data_field === false // not a real field
				|| $data_field->u!=1    // not a user-choosable field
			) {
				continue;
			}
			switch ($data_field->t) {
				case 'selectbox': // {
					$ok=0;
					if (@$product->vals[$n]) { // if product has custom values
						$strs=explode("\n", $product->vals[$n]);
						foreach ($strs as $a=>$b) {
							$strs[$a]=trim($b);
						}
					}
					else { // else use the product type defaults
						$strs=explode("\n", $data_field->e);
					}
					if (in_array($v, $strs)) {
						if (strpos($v, '|')!==false) {
							$bits=explode('|', $v);
							$price_amendments+=(float)$bits[1];
						}
						$ok=1;
					}
					if (!$ok) {
						continue;
					}
				break; // }
				case 'selected-image': // {
					$v='http://'.$_SERVER['HTTP_HOST'].'/kfmget/'.$v;
					$long_desc='<img style="float:left" src="'.$v.',width=60,height=60"/>';
				break; // }
			}
			$vals[]='<span>'.$n.'</span>: '.$v;
		}
	}
	if (count($vals)) {
		$long_desc.=join("\n", $vals).'<br style="clear:left"/>';
		$md5=','.md5($long_desc.'products_'.$id);
	}
	// }
	list($price, $amount, $vat)=Products_getProductPrice(
		$product, $amount, $md5
	);
	OnlineStore_addToCart(
		$price+$price_amendments,
		$amount,
		$product->get('name'),
		$long_desc,
		'products_'.$id.$md5,
		$_SERVER['HTTP_REFERER'],
		$vat,
		$id,
		(int)(@$product->vals['online-store']['_deliver_free']),
		(int)(@$product->vals['online-store']['_not_discountable'])
	);
}

/**
  * list product categories contained in a parent
  *
  * @param array  $params parameters to pass to the function
  * @param object $smarty the current Smarty instance
  *
  * @return HTML the list of categories
  */
function Products_listCategories($params, $smarty) {
	require_once dirname(__FILE__).'/frontend/show.php';
	return _Products_listCategories($params, $smarty);
}

/**
  * build up a list of the contents of a product category
  *
  * @param array  $params parameters to pass to the function
  * @param object $smarty the current Smarty instance
  *
  * @return HTML the list of contents
  */
function Products_listCategoryContents($params, $smarty) {
	require_once dirname(__FILE__).'/frontend/show.php';
	return _Products_listCategoryContents($params, $smarty);
}

function Products_priceBase($params, $smarty) {
	require_once dirname(__FILE__).'/frontend/smarty-functions.php';
	return Products_priceBase2($params, $smarty);
}
function Products_priceDiscount($params, $smarty) {
	require_once dirname(__FILE__).'/frontend/smarty-functions.php';
	return Products_priceDiscount2($params, $smarty);
}
function Products_priceDiscountPercent($params, $smarty) {
	require_once dirname(__FILE__).'/frontend/smarty-functions.php';
	return Products_priceDiscountPercent2($params, $smarty);
}
function Products_priceSale($params, $smarty) {
	require_once dirname(__FILE__).'/frontend/smarty-functions.php';
	return Products_priceSale2($params, $smarty);
}

/**
  * get HTML for the Products widget
  *
  * @param array $vars any parameters to pass to the widget
  *
  * @return string HTML of the widget
  */
function Products_widget($vars=null) {
	require_once dirname(__FILE__).'/frontend/show.php';
	require dirname(__FILE__).'/frontend/widget.php';
	return $html;
}

/**
	* function for handling timed events
	*
	* @return null
	*/
function Products_cronHandle() {
	dbQuery(
		'update products set enabled=1 where !enabled and activates_on<now() '
		.'and expires_on>now()'
	);
	dbQuery(
		'update products set enabled=0 where enabled and expires_on<now()'
	);
	Core_cacheClear('products');
}

/**
	* function for getting next timed event
	*
	* @return array date, function for next timed event
	*/
function Products_cronGetNext() {
	dbQuery('delete from cron where func="Products_cronHandle"');
	$n1=dbOne(
		'select activates_on from products where !enabled and '
		.'expires_on>now() order by activates_on limit 1', 'activates_on'
	);
	$n2=dbOne(
		'select expires_on from products where enabled order by expires_on '
		.'limit 1', 'expires_on'
	);
	$n=false;
	if ($n1 && $n2) {
		$n=$n1<$n2?$n1:$n2;
	}
	elseif ($n1 || $n2) {
		$n=$n1?$n1:$n2;
	}
	if ($n) {
		dbQuery(
			'insert into cron set name="disable/enable product", notes="disable '
			.'or enable a product", period="day", period_multiplier=1, '
			.'next_date="'.$n.'", func="Products_cronHandle"'
		);
	}
}

class Product{
	static $instances=array();

	/**
	  * constructor for product instances
	  *
	  * @param int $v       the ID of the product that's wanted
	  * @param int $r       pre-built data to use
	  * @param int $enabled only retrieve enabled products?
	  *
	  * @return object the product instance
	  */
	function __construct($v, $r=false, $enabled=true) {
		$v=(int)$v;
		if ($v<1) {
			return false;
		}
		$filter=$enabled?' and enabled ':'';
		if (!$r) {
			$r=dbRow("select * from products where id=$v $filter limit 1");
		}
		if (!count($r) || !is_array($r)) {
			return false;
		}
		$vals=json_decode($r['data_fields']);
		unset($r['data_fields']);
		if (isset($r['online_store_fields'])) {
			$online_store_data=json_decode($r['online_store_fields']);
		}
		unset($r['online_store_fields']);
		$this->vals=array();
		foreach ($r as $k=>$v) {
			$this->vals[$k]=$v;
		}
		foreach ($vals as $k=>$val) {
			if (!is_object($val)) {
				$this->vals[preg_replace('/[^a-zA-Z0-9\-_]/', '_', $k)]=$val;
			}
			else {
				$this->vals[preg_replace('/[^a-zA-Z0-9\-_]/', '_', $val->n)]=$val->v;
			}
		}
		if (isset($online_store_data)) {
			foreach ($online_store_data as $name=>$value) {
				$this->vals['online-store'][$name]=$value;
			}
		}
		$this->id=$r['id'];
		$this->name=$r['name'];
		$this->stock_number=$r['stock_number'];
		self::$instances[$this->id]=&$this;
		return $this;
	}

	/**
	  * retrieves a product instance
	  *
	  * @param int     $id      the ID of the product type that's wanted
	  * @param array   $r       pre-built data to use
	  * @param boolean $enabled only retrieve enabled products?
	  *
	  * @return object the product instance
	  */
	static function getInstance($id=0, $r=false, $enabled=true) {
		if (!is_numeric($id)) {
			return false;
		}
		if (!array_key_exists($id, self::$instances)) {
			return new Product($id, $r, $enabled);
		}
		return self::$instances[$id];
	}

	/**
	  * get teh relative URL of a page for showing this product
	  *
	  * @return string URL of the product's page
	  */
	function getRelativeURL() {
		global $PAGEDATA;
		if ($this->relativeUrl) {
			return $this->relativeUrl;
		}
		// { Does the product have a page assigned to display the product?
		$pageID 
			= dbOne(
				'select page_id '
				.'from page_vars where name= "products_product_to_show" '
				.'and value='.$this->id.' limit 1', 
				'page_id'
			);
		if ($pageID) {
			$this->relativeUrl=Page::getInstance($pageID)->getRelativeUrl();
			return $this->relativeUrl; 
		}
		// }
		// { Is there a page intended to display its category?
		$productCats 
			= dbAll(
				'select category_id '
				.'from products_categories_products '
				.'where product_id='.$this->id
			);
		if (count($productCats)) {
			$pcats=array();
			foreach ($productCats as $productCat) {
				$pcats[]=$productCat['category_id'];
			}
			$rs=dbAll(
				'select page_id '
				.'from page_vars where name="products_category_to_show" '
				.'and value in ('.join(',', $pcats).')'
			);
			$pid=0;
			foreach ($rs as $r) {
				$page=Page::getInstance($r['page_id']);
				if ($page->type!='products') {
					continue;
				}
				$pid=$r['page_id'];
				if ($pid==$PAGEDATA->id) {
					break;
				}
			}
			if ($pid) {
				$page = Page::getInstance($pid);
				$this->relativeUrl=$page->getRelativeUrl()
					.'?product_id='.$this->id;
				return $this->relativeUrl;
			}
		}
		// }
		$cat=0;
		if (count($productCats)) {
			$cat=$productCats[0]['category_id'];
		}
		if (@$_REQUEST['product_cid']) {
			$cat=(int)$_REQUEST['product_cid'];
		}
		if ($cat) {
			$cat=ProductCategory::getInstance($cat);
			return $cat->getRelativeUrl().'/'.urlencode($this->name);
		}
		if (preg_match('/^products(\||$)/', $PAGEDATA->type)) { // TODO
			return $PAGEDATA->getRelativeUrl().'/'.urlencode($this->name);
		}
		$this->relativeUrl='/_r?type=products&amp;product_id='.$this->id;
		return $this->relativeUrl;
	}

	/**
	  * retrieve one of the product's values
	  *
	  * @param string $name the name of the field
	  *
	  * @return string the value
	  */
	function get($name) {
		if (isset($this->vals[$name])) {
			return $this->vals[$name];
		}
		return false;
	}

	/**
		* get KFM ID for default image
		*
		* @return int ID of the image
		*/
	function getDefaultImage() {
		$vals=$this->vals;
		if (!$vals['images_directory']) {
			return 0;
		}
		$iid=0;
		if ($vals['image_default']) {
			$image=kfmImage::getInstance($iid);
			if ($image->exists()) {
				return $vals['image_default'];
			}
		}
		$directory = $vals['images_directory'];
		$dir_id=kfm_api_getDirectoryId(preg_replace('/^\//', '', $directory));
		if (!$dir_id) {
			return 0;
		}
		$images=kfm_loadFiles($dir_id);
		if (count($images['files'])) {
			$image=$images['files'][0];
			return $image['id'];
		}
		return 0;
	}

	/**
	  * retrieve one of the product's values in human-readable form
	  *
	  * @param string $name the name of the field
	  *
	  * @return string the value
	  */
	function getString($name) {
		$type= ProductType::getInstance($this->vals['product_type_id']);
		$datafields= $type->data_fields;
		foreach ($datafields as $data) {
			if ($data->n==$name) {
				switch($data->t) {
					case 'date': // {
						return date_m2h($this->vals[$data->n]);
					break; // }
					case 'checkbox': // {
						if (isset($this->vals[$data->n]) && $this->vals[$data->n]) {
							return 'Yes';
						}
						else {
							return 'No';
						}
					break; // }
					default: // {
						if (isset($this->vals[$data->n])) {
							return $this->vals[$data->n];
						}
						// }
				}
			}
		}
		return '';
	}

	/**
	  * search the product to see if it matches a filter
	  *
	  * @param string $search the keyword to search for
	  * @param string $field  the fieldname to search (leave blank to search all)
	  *
	  * @return boolean true if found, false if not
	  */
	function search($search, $field='') {
		$search=strtolower($search);
		if ($field) {
			$v=strtolower($this->get($field));
			return strpos($v, $search)!==false;
		}
		if (strpos(strtolower($this->stock_number), $search)!==false) {
			return true;
		}
		if (strpos(strtolower($this->name), $search)!==false) {
			return true;
		}
		$product_type=ProductType::getInstance($this->vals['product_type_id']);
		foreach ($product_type->data_fields as $data_field) {
			if ($data_field->s && strpos(strtolower($this->get($data_field->n)), $search)!==false) {
				return true;
			}
		}
		return false;
	}
}
class ProductType{
	static $instances=array();
	/**
	  * constructor for product type instances
	  *
	  * @param int $v the ID of the product type that's wanted
	  *
	  * @return object the product type instance
	  */
	function __construct($v) {
		$v=(int)$v;
		if ($v<1) {
			return false;
		}
		$r=dbRow("select * from products_types where id=$v limit 1");
		if (!count($r)) {
			return false;
		}
		$this->data_fields=json_decode($r['data_fields']);
		$this->meta=json_decode(isset($r['meta'])?$r['meta']:'{}');
		$tpl_cache=USERBASE.'/ww.cache/products/templates/types_multiview_'.$v
			.'_header';
		if (!file_exists($tpl_cache)) {
			file_put_contents($tpl_cache, $r['multiview_template_header']);
		}
		$tpl_cache=USERBASE.'/ww.cache/products/templates/types_multiview_'.$v
			.'_footer';
		if (!file_exists($tpl_cache)) {
			file_put_contents($tpl_cache, $r['multiview_template_footer']);
		}
		$tpl_cache=USERBASE.'/ww.cache/products/templates/types_multiview_'.$v;
		if (!file_exists($tpl_cache)) {
			file_put_contents($tpl_cache, $r['multiview_template']);
		}
		unset($r['multiview_template']);
		$tpl_cache=USERBASE.'/ww.cache/products/templates/types_singleview_'.$v;
		if (!file_exists($tpl_cache)) {
			file_put_contents($tpl_cache, $r['singleview_template']);
		}
		unset($r['singleview_template']);
		$this->id=$r['id'];
		$this->is_for_sale=(int)$r['is_for_sale'];
		$this->is_voucher=(int)$r['is_voucher'];
		$this->voucher_template=$r['voucher_template'];
		self::$instances[$this->id] =& $this;
		return $this;
	}
	/**
	  * returns an instance of a product type
	  *
	  * @param int $id the ID of the product type that's wanted
	  *
	  * @return object the product type instance
	  */
	static function getInstance($id=0) {
		$id=(int)$id;
		if ($id<1) {
			return false;
		}
		if (!array_key_exists($id, self::$instances)) {
			new ProductType($id);
		}
		return self::$instances[$id];
	}
	/**
	  * returns a data field's contents
	  *
	  * @param string $name name of the field to return
	  *
	  * @return string the value
	  */
	function getField($name) {
		foreach ($this->data_fields as $k=>$v) {
			if ($v->n==$name) {
				return $v;
			}
		}
		return false;
	}
	/**
	  * if the product has no associated images, show a "missing image" image
	  *
	  * @param string $maxsize the size of the image it replaces
	  *
	  * @return string html of the image 
	  */
	function getMissingImage($maxsize) {
		return '<img src="/kfmgetfull/products/types/'.$this->id
			.'/image-not-found.png,width='.$maxsize.',height='.$maxsize.'" />';
	}
	/**
	  * produce a HTML version of the product
	  *
	  * @param string $product  the product to render
	  * @param string $template is this to be a multi-view product or single-view?
	  *
	  * @return string html of the product
	  */
	function render($product, $template='singleview') {
		global $DBVARS;
		$GLOBALS['products_template_used']=$template;
		if (isset($DBVARS['online_store_currency'])) {
			$csym=$DBVARS['online_store_currency'];
		}
		$smarty=Products_setupSmarty();
		$smarty->assign('product', $product);
		$smarty->assign('product_id', $product->get('id'));
		if (!is_array(@$this->data_fields)) {
			$this->data_fields=array();
		}
		foreach ($this->data_fields as $f) {
			$f->n=preg_replace('/[^a-zA-Z0-9\-_]/', '_', $f->n);
			$val=$product->get($f->n);
			$required=@$f->r?' required':'';
			switch($f->t) {
				case 'checkbox': // {
					$smarty->assign(
						$f->n,
						($val?'Yes':'No')
					);
				break; // }
				case 'colour': // {
					if (@$f->u) { // user-definable
						WW_addScript(
							'/j/mColorPicker/mColorPicker.js'
						);
						$h='<input class="color-picker" '
							.'name="products_values_'.$f->n.'" '
							.'style="height:20px;width:20px;" '
							.'value="'.htmlspecialchars($val).'" '
							.'data-text="hidden"/>'
							.'<style>#mColorPickerFooter,#mColorPickerImg{display:none}</style>';
						WW_addInlineScript(
							'$(".color-picker")'
							.'.mColorPicker({"imageFolder":"/j/mColorPicker/images/"});'
						);
					}
					else {
						$h='TODO';
					}
					$smarty->assign(
						$f->n,
						$h
					);
				break; // }
				case 'date': // {
					if (@$f->u) { // user-definable
						$smarty->assign(
							$f->n,
							'<input class="product-field date '.$f->n.$required.'" name="'
							.'products_values_'.$f->n.'"/>'
						);
						$format=@$f->e?$f->e:'yy-mm-dd';
						$y=date('Y');
						WW_addInlineScript(
							'$("input[name=products_values_'.$f->n.']").datepicker({'
							.'"dateFormat":"'.$format.'",'
							.'changeYear:true,changeMonth:true,yearRange:"1900:'.$y.'"'
							.'});'
						);
						WW_addInlineScript(
							'$("input.hasDatepicker").each(function() {'
							.'if (this.value!="") return;'
							.'$(this).datepicker("setDate", "+0");'
							.'});'
						);
					}
					else {
						$smarty->assign(
							$f->n,
							date_m2h($val)
						);
					}
				break; // }
				case 'hidden': // {
					$smarty->assign(
						$f->n,
						'<input type="hidden" name="products_values_'.$f->n
						.'" value="'.htmlspecialchars($val).'"/>'
					);
				break; // }
				case 'selectbox': // {
					if (@$f->u) {
						$valid_entries=explode("\n", $val);
						foreach ($valid_entries as $k=>$v) {
							$v=trim($v);
							if ($v=='') {
								unset($valid_entries[$k]);
							}
							else {
								$valid_entries[$k]=$v;
							}
						}
						if (!count($valid_entries)) {
							$valid_entries=explode("\n", $f->e);
						}
						$h='<select name="products_values_'.$f->n.'" class="'.$required.'">';
						foreach ($valid_entries as $e) {
							$e=trim($e);
							if ($e=='' || !in_array($e, $valid_entries)) {
								continue;
							}
							$o=$e;
							$p='';
							if (strpos($e, '|')!==false) {
								$bits=explode('|', $e);
								$e=$bits[0];
								$p='price="'.(int)$bits[1].'"';
							}
							$h.='<option '.$p.' value="'.htmlspecialchars($o).'">'
								.htmlspecialchars($e).'</option>';
						}
						$h.='</select>';
					}
					else {
						$h=$val;
					}
					$smarty->assign(
						$f->n,
						$h
					);
				break; // }
				case 'selected-image': // {
					$smarty->assign(
						$f->n,
						'<input type="hidden" name="products_values_'.$f->n.'" '
						.'class="product-field '.$f->n.$required.'"/>'
					);
				break; // }
				case 'textarea': // { textarea
					if (@$f->u) {
						$smarty->assign(
							$f->n,
							'<textarea class="product-field '.$f->n.$required
							.'" name="products_values_'.$f->n.'">'
							.trim(htmlspecialchars(preg_replace('/<[^>]*>/', '', $val)))
							.'</textarea>'
						);
					}
					else {
						$smarty->assign(
							$f->n,
							$val
						);
					}
				break; // }
				default: // { everything else
					if (@$f->u) {
						$smarty->assign(
							$f->n,
							'<input class="product-field '.$f->n.$required
							.'" value="'.htmlspecialchars($val).'" name="products_values_'.$f->n.'"/>'
						);
					}
					else {
						$smarty->assign(
							$f->n,
							$val
						);
					}
					// }
			}
		}
		$smarty->assign('_name', $product->name);
		$smarty->assign('_stock_number', $product->stock_number);
		return '<div class="products-product" id="products-'.$product->get('id')
			.'">'.$smarty->fetch(
				USERBASE.'/ww.cache/products/templates/types_'.$template.'_'.$this->id
			)
			.'</div>';
	}
}
