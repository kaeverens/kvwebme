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
	'admin' => array( // {
		'menu' => array( // {
			'Products>Products'=> // __('Products')
				'/ww.admin/plugin.php?_plugin=products&amp;_page=products',
			'Products>Categories'=>
				'javascript:Core_screen(\'products\', \'Categories\')',
			'Products>Types'=>'javascript:Core_screen(\'products\', \'Types\')',
			'Products>Relation Types'=> // __('Relation Types')
				'/ww.admin/plugin.php?_plugin=products&amp;_page=relation-types',
			// __('Import')
			'Products>Import'=>'javascript:Core_screen(\'products\', \'Import\')',
			'Products>Export Data'=> // __('Export Data')
				'javascript:Core_screen(\'products\', \'ExportData\')',
			'Products>Brands and Producers' => // __('Brands and Producers')
				'javascript:Core_screen(\'products\', \'BrandsandProducers\')'
		), // }
		'page_type' => 'Products_adminPage',
		'widget' => array(
			'form_url'   => '/ww.plugins/products/admin/widget.php',
			'js_include' => array(
				'/ww.plugins/products/admin/widget.js'
			),
		)
	), // }
	'dependencies'=>'image-gallery',
	'description' =>function() {
		return __('Product catalogue.');
	},
	'frontend' => array( // {
		'breadcrumbs' => 'Products_breadcrumbs',
		'page_type' => 'Products_frontend',
		'widget' => 'Products_widget',
		'template_functions' => array( // {
			'PRODUCTS_AMOUNT_IN_STOCK' => array( // {
				'function' => 'Products_amountInStock'
			), // }
			'PRODUCTS_AMOUNT_SOLD' => array( // {
				'function' => 'Products_soldAmount'
			), // }
			'PRODUCTS_AMOUNT_TO_ADD' => array( // {
				'function' => 'Products_getAmountToAddWidget'
			), // }
			'PRODUCTS_BUTTON_ADD_TO_CART' => array( // {
				'function' => 'Products_getAddToCartWidget'
			), // }
			'PRODUCTS_BUTTON_ADD_MANY_TO_CART' => array( // {
				'function' => 'Products_getAddManyToCartWidget'
			), // }
			'PRODUCTS_CATEGORIES' => array( // {
				'function' => 'Products_categories'
			), // }
			'PRODUCTS_CUSTOM_PRICE' => array( // {
				'function' => 'Products_customPrice'
			), // }
			'PRODUCTS_DATATABLE' => array( // {
				'function' => 'Products_datatable'
			), // }
			'PRODUCTS_EXPIRY_CLOCK' => array( // {
				'function' => 'Products_expiryClock'
			), // }
			'PRODUCTS_IMAGE' => array( // {
				'function' => 'Products_image'
			), // }
			'PRODUCTS_IMAGES' => array( // {
				'function' => 'Products_images'
			), // }
			'PRODUCTS_IMAGES_SLIDER' => array( // {
				'function' => 'Products_imageSlider'
			), // }
			'PRODUCTS_LINK' => array( // {
				'function' => 'Products_link'
			), // }
			'PRODUCTS_LIST_CATEGORIES' => array( // {
				'function' => 'Products_listCategories'
			), // }
			'PRODUCTS_LIST_CATEGORY_CONTENTS' => array( // {
				'function' => 'Products_listCategoryContents'
			), // }
			'PRODUCTS_MAP' => array( // {
				'function' => 'Products_map'
			), // }
			'PRODUCTS_OWNER' => array( // {
				'function' => 'Products_owner'
			), // }
			'PRODUCTS_PLUS_VAT' => array( // {
				'function' => 'Products_plusVat'
			), // }
			'PRODUCTS_PRICE_BASE' => array( // {
				'function' => 'Products_priceBase'
			), // }
			'PRODUCTS_PRICE_BULK' => array( // {
				'function' => 'Products_priceBulk'
			), // }
			'PRODUCTS_PRICE_DISCOUNT' => array( // {
				'function' => 'Products_priceDiscount'
			), // }
			'PRODUCTS_PRICE_DISCOUNT_PERCENT' => array( // {
				'function' => 'Products_priceDiscountPercent'
			), // }
			'PRODUCTS_PRICE_SALE' => array( // {
				'function' => 'Products_priceSale'
			), // }
			'PRODUCTS_QRCODE' => array( // {
				'function' => 'Products_qrCode'
			), // }
			'PRODUCTS_RELATED' => array( // {
				'function' => 'Products_showRelatedProducts'
			), // }
			'PRODUCTS_REVIEWS' => array( // {
				'function' => 'Products_reviews'
			), // }
			'PRODUCTS_USER' => array( // {
				'function' => 'Products_user'
			) // }
		) // }
	), // }
	'name' =>function() {
		return __('Products');
	},
	'search' => 'Products_search',
	'triggers' => array( // {
		'initialisation-completed' => 'Products_addToCart',
		'menu-subpages' => 'Products_getSubCategoriesAsMenu',
		'menu-subpages-html' => 'Products_getSubCategoriesAsMenuHtml'
	), // }
	'version' => '52'
);
// }

@mkdir(USERBASE.'/ww.cache/products');
@mkdir(USERBASE.'/ww.cache/products/templates');
@mkdir(USERBASE.'/ww.cache/products/templates_c');

// { class Product

/**
	* Product object
	*
	* @category WebME
	* @package  WebME
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvweb.me/
	*/
class Product{
	static $instances=array();

	// { __construct

	/**
	  * constructor for product instances
	  *
	  * @param int $v             the ID of the product that's wanted
	  * @param int $r             pre-built data to use
	  * @param int $enabledFilter only retrieve enabled products?
	  *
	  * @return object the product instance
	  */
	function __construct($v, $r=false, $enabledFilter=0) {
		$v=(int)$v;
		if ($v<1) {
			return false;
		}
		if ($enabledFilter==0) {
			$enabledSql='and enabled';
		}
		if ($enabledFilter==1) {
			$enabledSql='';
		}
		if ($enabledFilter==2) {
			$enabledSql='and !enabled';
		}
		if (!$r) {
			$sql="select * from products where id=$v $enabledSql limit 1";
			$r=dbRow($sql, 'products');
		}
		if (!count($r) || !is_array($r)) {
			return false;
		}
		$vals=json_decode($r['data_fields']);
		unset($r['data_fields']);
		unset($r['online_store_fields']);
		$this->vals=array();
		foreach ($r as $k=>$v) {
			$this->vals[$k]=$v;
		}
		if (is_array($vals)) {
			foreach ($vals as $k=>$val) {
				if (!is_object($val)) {
					$this->vals[preg_replace('/[^a-zA-Z0-9\-_]/', '_', $k)]=$val;
				}
				else {
					$this->vals[preg_replace('/[^a-zA-Z0-9\-_]/', '_', $val->n)]=$val->v;
				}
			}
		}
		$this->id=$r['id'];
		$this->name=$r['name'];
		$this->link=$r['link'];
		$this->location=(int)$r['location'];
		if ($this->link==null) {
			$this->link=__FromJson($r['name'], true);
		}
		$this->default_category=isset($r['default_category'])
			?(int)$r['default_category']
			:0;
		if ($this->default_category==0) {
			$this->default_category=1;
		}
		$this->stock_number=isset($r['stock_number'])?$r['stock_number']:'';
		self::$instances[$this->id]=&$this;
		return $this;
	}

	// }
	// { get

	/**
	  * retrieve one of the product's values
	  *
	  * @param string $name the name of the field
	  *
	  * @return string the value
	  */
	function get($name) {
		if (isset($this->vals[$name])) {
			return __FromJSON($this->vals[$name]);
		}
		if (strpos($name, '_')===0) {
			$vname=preg_replace('/^_/', '', $name);
			return isset($this->{$vname})
				?__FromJSON($this->{$vname})
				:'';
		}
		return false;
	}

	// }
	// { getDefaultImage

	/**
		* get default image
		*
		* @return int ID of the image
		*/
	function getDefaultImage() {
		if (isset($this->default_image)) {
			return $this->default_image;
		}
		$vals=$this->vals;
		if ($vals['image_default']
			&& file_exists(USERBASE.'/f/'.$vals['image_default'])
		) {
			return $vals['image_default'];
		}
		$images=$this->getAllImages();
		if (count($images)) {
			$this->default_image=$images[0];
			dbQuery(
				'update products set default_image="'.addslashes($images[0]).'"'
				.' where id='.$this->id
			);
			return $this->default_image;
		}
		$this->default_image=false;
		return false;
	}

	// }
	function getAllImages() {
		$vals=$this->vals;
		if (!isset($vals['images_directory']) || !$vals['images_directory']) {
			$basedir='/products/product-images/';
			$pThousands=(int)($this->id/1000);
			$vals['images_directory']=$basedir.$pThousands.'/'.$this->id.'/';
			$sql='update products'
				.' set images_directory="'.$vals['images_directory'].'"'
				.' where id='.$this->id;
			dbQuery($sql);
		}
		$directory = $vals['images_directory'];
		$images=array();
		if (file_exists(USERBASE.'/f/'.$directory)) {
			$files=new DirectoryIterator(USERBASE.'/f/'.$directory);
			foreach ($files as $file) {
				if ($file->isDot()) {
					continue;
				}
				$images[]=$directory.'/'.$file->getFilename();
			}
		}
		return $images;
	}
	// { getInstance

	/**
	  * retrieves a product instance
	  *
	  * @param int     $id      the ID of the product type that's wanted
	  * @param array   $r       pre-built data to use
	  * @param boolean $enabled only retrieve enabled products?
	  *
	  * @return object the product instance
	  */
	static function getInstance($id=0, $r=false, $enabled=0) {
		if (!is_numeric($id)) {
			return false;
		}
		if (!array_key_exists($id, self::$instances)) {
			return new Product($id, $r, $enabled);
		}
		return self::$instances[$id];
	}

	// }
	// { getPrice

	/**
		* get price
		*
		* @param string $type type of price (base, sale, bulk)
		*
		* @return float price value
		*/
	function getPrice($type='base') {
		switch ($type) {
			case 'sale': // {
			return $this->getPriceSale(); // }
			default: // { base
			return $this->getPriceBase(); // }
		}
	}

	// }
	// { getPriceBase

	/**
		* get base price
		*
		* @return float
		*/
	function getPriceBase() {
		if (!isset($this->vals['os_base_price'])) {
			return 0;
		}
		$bp=isset($this->vals['os_base_price'])?$this->vals['os_base_price']:0;
		if (!is_object($bp)) {
			$bp=(object)array('_default'=>$bp);
			$this->vals['os_base_price']=$bp;
		}
		$lowest=$bp->_default;
		if (!isset($_SESSION['userdata'])) {
			return $lowest;
		}
		foreach ($bp as $k=>$v) {
			if ($k=='_default') {
				continue;
			}
			if (!isset($_SESSION['userdata']['groups'][$k])) {
				continue;
			}
			if ($v<$lowest) {
				$lowest=$v;
			}
		}
		return $lowest;
	}
	
	// }
	// { getPriceBulkAll

	/**
		* get bulk prices
		*
		* @return array
		*/
	function getPriceBulkAll() {
		if (!isset($this->vals['os_bulk_amount'])) {
			return array(0, 0);
		}
		$ba=$this->vals['os_bulk_amount'];
		if (!is_object($ba)) {
			$ba=(object)array('_default'=>$ba);
			$this->vals['os_bulk_amount']=$ba;
		}
		$bp=$this->vals['os_bulk_price'];
		if (!is_object($bp)) {
			$bp=(object)array('_default'=>$bp);
			$this->vals['os_bulk_price']=$bp;
		}
		$lowest=$bp->_default;
		$lowestAmt=$ba->_default;
		if (isset($_SESSION['userdata'])) {
			foreach ($bp as $k=>$v) {
				if ($k=='_default') {
					continue;
				}
				$amt=$ba->{$k};
				if (!$amt) {
					continue;
				}
				if (!isset($_SESSION['userdata']['groups'][$k])) {
					continue;
				}
				if ($v<$lowest) {
					$lowest=$v;
					$lowestAmt=$amt;
				}
			}
		}
		return array($lowest, $lowestAmt);
	}

	// }
	// { getPriceSale

	/**
		* get sale price
		*
		* @return float
		*/
	function getPriceSale() {
		$bp=isset($this->vals['os_sale_price'])
			?$this->vals['os_sale_price']:0;
		if (!is_object($bp)) {
			$bp=(object)array('_default'=>$bp);
			$this->vals['os_sale_price']=$bp;
		}
		$lowest=$bp->_default;
		if (isset($_SESSION['userdata'])) {
			foreach ($bp as $k=>$v) {
				if ($k=='_default') {
					continue;
				}
				if (!isset($_SESSION['userdata']['groups'][$k])) {
					continue;
				}
				if ($v<$lowest) {
					$lowest=$v;
				}
			}
		}
		switch (@$this->vals['os_sale_price_type']) {
			case '1': // discount
			return $this->getPriceBase()-$lowest;
			case '2': // percentage
			return $this->getPriceBase()*(100-$lowest)/100;
			default: // actual amount
			return $lowest;
		}
	}

	// }
	// { getRelativeUrl

	/**
	  * get the relative URL of a page for showing this product
	  *
	  * @return string URL of the product's page
	  */
	function getRelativeUrl() {
		global $PAGEDATA;
		if (isset($this->relativeUrl) && $this->relativeUrl) {
			return $this->relativeUrl;
		}
		// { if this product is disabled, then it can only be shown on special pages
		if ($this->vals['enabled']=='0') {
			$pid=dbOne(
				'select page_id from page_vars'
				.' where name="products_filter_by_status" and value in (1, 2)',
				'page_id'
			);
			$page=Page::getInstance($pid);
			if (!$page) {
				return '/';
			}
			return $page->getRelativeUrl()
				.'/'.$this->id.'-'.preg_replace('/[^a-zA-Z0-9]/', '-', $this->link);
		}
		// }
		// { Does the product have a page assigned to display the product?
		$pageID=Core_cacheLoad('products', 'page_for_product_'.$this->id, -1);
		if ($pageID===-1) {
			$pageID=dbOne(
				'select page_id from page_vars where name="products_product_to_show" '
				.'and value='.$this->id.' limit 1', 
				'page_id'
			);
			Core_cacheSave('products', 'page_for_product_'.$this->id, $pageID);
		}
		if ($pageID) {
			$this->relativeUrl=Page::getInstance($pageID)->getRelativeUrl();
			return $this->relativeUrl; 
		}
		// }
		// { Is there a page intended to display its category?
		$productCats=ProductsCategoriesProducts::getByProductId($this->id);
		$productCats[]=$this->default_category;
		$pcats=array();
		foreach ($productCats as $cid) {
			$cat=ProductCategory::getInstance($cid);
			if ($cat) {
				$url=$cat->getRelativeUrl();
				return $url.'/'.$this->id.'-'
					.preg_replace('/[^a-zA-Z0-9]/', '-', $this->link);
			}
		}
		// }
		$cat=0;
		if (@$_REQUEST['product_cid']) {
			$cat=(int)$_REQUEST['product_cid'];
		}
		if ($cat) {
			$category=ProductCategory::getInstance($cat);
			if ($category) {
				$catdir=$category->getRelativeUrl();
			}
			else {
				$catdir='/missing-category-'.$cat;
				$pids=ProductsCategoriesProducts::getByCategoryId($cat);
				ProductsCategoriesProducts::deleteByCategoryId($cat);
				Products_categoriesRecount($pids);
				return $this->getRelativeUrl();
			}
			return $catdir
				.'/'.$this->id.'-'.preg_replace('/[^a-zA-Z0-9]/', '-', $this->link);
		}
		if (preg_match('/^products(\||$)/', $PAGEDATA->type)) {
			return $PAGEDATA->getRelativeUrl()
				.'/'.$this->id.'-'.preg_replace('/[^a-zA-Z0-9]/', '-', $this->link);
		}
		$this->relativeUrl='/_r?type=products&amp;product_id='.$this->id;
		return $this->relativeUrl;
	}

	// }
	// { getString

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
					return Core_dateM2H($this->vals[$data->n]); // }
					case 'checkbox': // {
						if (isset($this->vals[$data->n]) && $this->vals[$data->n]) {
							return __('Yes');
						}
						else {
							return __('No');
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

	// }
	// { search

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
			if (@$data_field->s
				&& strpos(strtolower($this->get($data_field->n)), $search)!==false
			) {
				return true;
			}
		}
		return false;
	}

	// }
	function set($key, $val, $save=true) {
		if (is_array($key)) {
			return $this->setMultiple($key);
		}
		if (key=='meta') {
			// todo
		}
		else {
			$this->vals[$key]=$val;
		}
		if ($save) {
			$this->save();
		}
	}
	function setMultiple($arr) {
		foreach ($arr as $key=>$val) {
			$this->set($key, $val, false);
		}
		$this->save();
	}
	function save() {
		$bits=array();
		$r=dbRow('select * from products where id='.$this->id);
		$others=array();
		foreach ($this->vals as $key=>$val) {
			if ($key=='date_edited') {
				continue;
			}
			if (!array_key_exists($key, $r)) {
				$others[$key]=$this->vals[$key];
				continue;
			}
			if ($r[$key]==$val) {
				continue;
			}
			$bits[]=$key.'="'.addslashes($val).'"';
		}
		if (count($others)) {
			$oldDataFields=json_decode($r['data_fields'], true);
			$changed=false;
			foreach ($others as $key=>$val) {
				$found=0;
				foreach ($oldDataFields as $k=>$v) {
					if ($v['n']==$key) {
						$found=1;
						if ($v['v']!=$val) {
							$oldDataFields[$k]=array(
								'n'=>$key,
								'v'=>$val
							);
							$changed=1;
						}
					}
					if (!$found) {
						$changed=1;
						$oldDataFields[]=array(
							'n'=>$key,
							'v'=>$val
						);
					}
				}
			}
			if ($changed) {
				$bits[]='data_fields="'.addslashes(json_encode($oldDataFields)).'"';
			}
		}
		if (!count($bits)) {
			return;
		}
		$sql='update products set date_edited=now(), '.join(', ', $bits)
			.' where id='.$this->id;
		dbQuery($sql);
		Core_cacheClear('products');
	}
}

// }
// { class ProductCategory

/**
	* ProductCategory object
	*
	*	@category WebME
	* @package  WebME
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvweb.me/
	*/
class ProductCategory{
	static $instances=array();
	public $vals;

	// { __construct

	/**
		* constructor for the class
		*
		* @param int $id ID of the category
		*
		* @return object the category instance
		*/
	function __construct($id) {
		$id=(int)$id;
		$r=dbRow(
			'select * from products_categories where id='.$id, 'products_categories'
		);
		if (!count($r)) {
			return false;
		}
		$this->vals=$r;
		self::$instances[$this->vals['id']] =& $this;
		return $this;
	}

	// }
	// { getInstance

	/**
		* get a category instance
		*
		* @param int $id ID of the category
		*
		* @return object the instance
		*/
	static function getInstance($id=0) {
		if (!is_numeric($id)) {
			return false;
		}
		if (!array_key_exists($id, self::$instances)) {
			new ProductCategory($id);
		}
		if (!array_key_exists($id, self::$instances)) {
			self::$instances[$id]=false;
		}
		return self::$instances[$id];
	}

	// }
	// { getInstanceByName

	/**
		* get a category by its name
		*
		* @param string $name name of the category
		*
		* @return object the instance
		*/
	static function getInstanceByName($name='') {
		$bits=explode('>', $name);
		$cname=$bits[count($bits)-1];
		$parent=false;
		if (count($bits)>1) {
			$parent=ProductCategory::getInstanceByName(
				preg_replace('/>[^>]*$/', '', $name)
			);
		}
		$md5=md5('categorybyname-'.$name);
		$id=Core_cacheLoad('products', $md5, -1);
		if ($id===-1) {
			$sql='select id from products_categories'
				.' where name="'.addslashes($cname).'"';
			if ($parent) {
				$sql.=' and parent_id='.$parent->vals['id'];
			}
			else {
				$sql.=' and parent_id=0';
			}
			$id=dbOne($sql, 'id', 'products_categories');
			Core_cacheSave('products', $md5, $id);
		}
		if (!array_key_exists($id, self::$instances)) {
			new ProductCategory($id);
		}
		return self::$instances[$id];
	}

	// }
	// { getRelativeUrl

	/**
		* get a URL for showing this category
		*
		* @return string the URL
		*/
	function getRelativeUrl() {
		// { see if there are any pages that use this category
		$ps1=dbAll(
			'select page_id from page_vars where name="products_category_to_show"'
			.' and value='.$this->vals['id'], 'page_id', 'page_vars'
		);
		if ($ps1 && count($ps1)) {
			$sql='select id from pages,page_vars where page_id=pages.id '
				.'and page_vars.name="products_what_to_show" and page_vars.value=2 '
				.'and id in ('.join(', ', array_keys($ps1)).')';
			$pid=dbOne($sql, 'id', 'pages,page_vars');
			if ($pid) {
				$page=Page::getInstance($pid);
				return $page->getRelativeUrl();
			}
		}
		// }
		// { or if there's a category parent, return its URL plus the name appended
		if ($this->vals['parent_id']!=0) {
			$cat=ProductCategory::getInstance($this->vals['parent_id']);
			return $cat->getRelativeUrl()
				.'/'.preg_replace('/[^a-zA-Z0-9]/', '-', $this->vals['name']);
		}
		// }
		// { or get at least any product page
		$url=Core_cacheLoad('products', 'products-page', -1);
		if ($url==-1) {
			$pid=dbOne(
				'select id from pages where type like "products%" limit 1', 'id'
			);
			if ($pid) {
				$page=Page::getInstance($pid);
			}
			$url=$pid?$page->getRelativeUrl().'/':'/#no-url-available';
			Core_cacheSave('products', 'products-page', $url);
		}
		return $url=='/#no-url-available'
			?'/#no-url-available'
			:$url.preg_replace('/[^a-zA-Z0-9]/', '-', $this->vals['name']);
		// }
	}

	// }
	// { getSubCategoryIDs
	
	function getSubCategoryIDs() {
		$ids=Core_cacheLoad('products', 'subCategoryIds-'.$this->vals['id'], -1);
		if ($ids===-1) {
			$ids=array();
			$rs=dbAll(
				'select id from products_categories where parent_id='.$this->vals['id']
				.' and enabled', false, 'products_categories'
			);
			foreach ($rs as $r) {
				$ids[]=$r['id'];
				$cat=ProductCategory::getInstance($r['id']);
				$ids=array_merge($ids, $cat->getSubCategoryIDs());
			}
			Core_cacheSave('products', 'subCategoryIds-'.$this->vals['id'], $ids);
		}
		return $ids;
	}

	// }
	function getBreadcrumbs($delimiter=' &raquo; ') {
		$name='<a href="'.$this->getRelativeUrl().'">'
			.htmlspecialchars($this->vals['name']).'</a>';
		if ($this->vals['parent_id']) {
			$pname=ProductCategory::getInstance($this->vals['parent_id'])
				->getBreadcrumbs($delimiter);
			$name=$pname.$delimiter.$name;
		}
		return $name;
	}
}

// }
// { class Products

/**
	* Products object
	*
	*	@category WebME
	* @package  WebME
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvweb.me/
	*/
class Products{
	static $instances=array();

	// { __construct
	/**
		* constructor for the class
		*
		* @param array  $vs            variable identifiers for the products
		* @param string $md5           unique identifier for the collection
		* @param string $search        search string to filter by
		* @param array  $search_arr    array of search strings to filter by
		* @param string $sort_col      field to sort by
		* @param string $sort_dir      sort direction
		* @param string $location      filter the products by location
		* @param int    $enabledFilter whether to allow enabled/disabled products
		*
		* @return object the category instance
		*/
	function __construct(
		$vs, $md5, $search='', $search_arr=array(), $sort_col='', $sort_dir='asc',
		$location=0, $enabledFilter=0
	) {
		$this->product_ids=Core_cacheLoad('products', 'products_'.$md5, -1);
		if ($this->product_ids===-1) {
			if ($location) {
				$locations=explode(',', $location);
				$arr=array();
				foreach ($vs as $v) {
					$p=Product::getInstance($v, false, $enabledFilter);
					if (!$p) {
						continue;
					}
					if (in_array($p->location, $locations)) {
						$arr[]=$v;
					}
				}
				$vs=$arr;
			}
			if ($search!='') {
				$search=explode(' ', $search);
				$arr=array();
				foreach ($vs as $v) {
					$p=Product::getInstance($v, false, $enabledFilter);
					if (!$p) {
						continue;
					}
					$ok=1;
					foreach ($search as $s) {
						if (!$p->search($s)) {
							$ok=0;
						}
					}
					if ($ok) {
						$arr[]=$v;
					}
				}
				$vs = $arr;
			}
			if (is_array($search_arr) && count($search_arr)) {
				$arr=array();
				foreach ($vs as $v) {
					$p=Product::getInstance($v, false, $enabledFilter);
					$left=count($search_arr);
					foreach ($search_arr as $k=>$s) {
						if ($p->search($s, $k)) {
							$left--;
						}
					}
					if (!$left) {
						$arr[]=$v;
					}
				}
				$vs=$arr;
			}
			if ($sort_col) {
				$vals=array();
				$keys=array();
				foreach ($vs as $v) {
					$keys[]=$v;
					$p=Product::getInstance($v, false, $enabledFilter);
					$vals[]=$p->get($sort_col);
				}
				array_multisort($vals, $keys);
				$vs=$keys;
				if ($sort_dir != 'asc') {
					$vs=array_reverse($vs);
				}
			}
			$this->product_ids=$vs;
			Core_cacheSave('products', 'products_'.$md5, $vs);
		}
		self::$instances[$md5]=& $this;
		return $this;
	}

	// }
	// { getAll

	/**
		* get all products
		*
		* @param string $search        search string to filter by
		* @param string $location      filter the products by location
		* @param int    $enabledFilter whether to allow enabled/disabled products
		*
		* @return object instance of Products object
		*/
	static function getAll($search='', $location=0, $enabledFilter=0) {
		$md5loc=is_array($location)?join(',', $location):$location;
		$id=md5('all|'.$search.'|'.$md5loc.'|'.$enabledFilter);
		if (!array_key_exists($id, self::$instances)) {
			$product_ids=Core_cacheLoad('products', $id, -1);
			if ($product_ids===-1) {
				$product_ids=array();
				$enabledSql='where ';
				if ($enabledFilter==0) {
					$enabledSql.=' enabled';
				}
				if ($enabledFilter==1) {
					$enabledSql='';
				}
				if ($enabledFilter==2) {
					$enabledSql.=' !enabled';
				}
				$rs=dbAll('select id from products '.$enabledSql);
				foreach ($rs as $r) {
					$product_ids[]=$r['id'];
				}
				Core_cacheSave('products', $id, $product_ids);
			}
			new Products(
				$product_ids, $id, $search, array(), '', 'asc', $location,
				$enabledFilter
			);
		}
		return self::$instances[$id];
	}

	// }
	// { getByCategory

	/**
		* retrieve products within a specified category
		*
		* @param int    $id         the product type's ID
		* @param string $search     search string to filter by
		* @param array  $search_arr array of search strings to filter by
		* @param string $sort_col   field to sort by
		* @param string $sort_dir   sort direction
		* @param string $location   filter the products by location
		*
		* @return object instance of Products object
		*/
	static function getByCategory(
		$id, $search='', $search_arr=array(), $sort_col='_name', $sort_dir='asc',
		$location=0, $noRecurse=0
	) {
		if (!is_numeric($id)) {
			return false;
		}
		$locmd5=is_array($location)?join(',', $location):0;
		$md5=md5(
			$id.'|'.$search.'|'.join(',', $search_arr).'|'.$locmd5.'|'.$noRecurse
		);
		if (!array_key_exists($md5, self::$instances)) {
			$product_ids=array();
			$locFilter=$location?' and location in ('.$location.')':'';
			$cats=array($id);
			if (!$noRecurse) {
				$cat=ProductCategory::getInstance($id);
				if ($cat) {
					$cats=array_merge($cats, $cat->getSubCategoryIDs());
				}
			}
			$rs=ProductsCategoriesProducts::getByCategoryIds($cats);
			if (count($rs)) {
				$sql='select id from products'
					.' where id in ('.join(',', $rs).') and enabled';
				if ($search!='') {
					$str=str_replace(' ', '%', $search);
					$sql.=' and (name like "%'.addslashes($str)
						.'%" or data_fields like "%'.addslashes($str).'%")';
				}
				$rs=dbAll($sql, false, 'products');
				foreach ($rs as $r) {
					$product_ids[]=$r['id'];
				}
			}
			new Products($product_ids, $md5, $search, $search_arr);
			$pcs=Core_cacheLoad(
				'products', 'productcategoriesenabled_parent_'.$id, -1
			);
			if ($pcs===-1) {
				$pcs=dbAll(
					'select id,name from products_categories where parent_id='.$id
					.' and enabled order by name', false, 'products_categories'
				);
				Core_cacheSave(
					'products', 'productcategoriesenabled_parent_'.$id, $pcs
				);
			}
			self::$instances[$md5]->subCategories=$pcs;
		}
		return self::$instances[$md5];
	}

	// }
	// { getByCategoryName

	/**
		* retrieve products by their category name
		*
		* @param string $name          name of the category
		* @param int    $enabledFilter whether to allow enabled/disabled products
		*
		* @return object instance of Products object
		*/
	static function getByCategoryName($name, $enabledFilter=0, $noRecurse=0) {
		$arr=explode('/', $name);
		if ($arr[0]=='') {
			array_shift($arr);
		}
		$cid=0;
		foreach ($arr as $name) {
			$cid=dbOne(
				'select id from products_categories where parent_id='.$cid
				.' and name="'.addslashes($name).'" limit 1',
				'id', 'products_categories'
			);
			if (!$cid) {
				break;
			}
		}
		if (!$cid) {
			return Products::getAll('', 0, $enabledFilter);
		}
		return Products::getByCategory($cid, array(), '_name', 'asc', 0, $noRecurse);
	}

	// }
	// { getByIds

	/**
		* get a list of products by their IDs
		*
		* @param array  $ids        list of IDs
		* @param string $search     search string
		* @param array  $search_arr array of search strings to filter by
		* @param string $sort_col   field to sort by
		* @param string $sort_dir   sort direction
		*
		* @return object instance of Products object
		*/
	static function getByIds(
		$ids, $search='', $search_arr=array(), $sort_col='_name', $sort_dir='asc'
	) {
		if (!is_array($ids)) {
			return false;
		}
		$md5=md5(
			join(',', $ids).'|'.$search.'|'.print_r($search_arr, true).'|'
			.$sort_col.'|'.$sort_dir
		);
		if (!array_key_exists($md5, self::$instances)) {
			new Products($ids, $md5, $search, $search_arr, $sort_col, $sort_dir);
		}
		return self::$instances[$md5];
	}

	// }
	// { getByType

	/**
		* retrieve products that have a specific type
		*
		* @param int    $id         the product type's ID
		* @param string $search     search string to filter by
		* @param array  $search_arr array of search strings to filter by
		* @param string $sort_col   field to sort by
		* @param string $sort_dir   sort direction
		*
		* @return object instance of Products object
		*/
	static function getByType(
		$id, $search='', $search_arr=array(), $sort_col='_name', $sort_dir='asc'
	) {
		if (!is_numeric($id)) {
			return false;
		}
		$md5=md5(
			$id.'|'.$search.'|'.print_r($search_arr, true).'|'.$sort_col.'|'.$sort_dir
		);
		if (!array_key_exists($md5, self::$instances)) {
			$product_ids=array();
			$rs=Core_cacheLoad('products', 'productByType-'.$id, -1);
			if ($rs===-1) {
				$rs=dbAll('select id from products where enabled and product_type_id='.$id);
				Core_cacheSave('products', 'productByType-'.$id, $rs);
			}
			foreach ($rs as $r) {
				$product_ids[]=$r['id'];
			}
			new Products($product_ids, $md5, $search, $search_arr, $sort_col, $sort_dir);
		}
		return self::$instances[$md5];
	}

	// }
	// { render

	/**
		* render a list of products to HTML
		*
		* @param object $PAGEDATA      the page object
		* @param int    $start         offset
		* @param int    $limit         how many products to show
		* @param string $order_by      what field to order the search by
		* @param int    $order_dir     order ascending or descending
		* @param int    $limit_start   lowest $start offset allowed
		* @param int    $enabledFilter whether to allow enabled/disabled products
		*
		* @return string the HTML of the products list
		*/
	function render(
		$PAGEDATA, $start=0, $limit=0, $order_by='', $order_dir=0,
		$limit_start=0, $enabledFilter=0
	) {
		global $cdnprefix;
		$c='';
		// { sort based on $order_by
		$md5=md5(
			'ps-sorted-'.join(',', $this->product_ids).'|'.$order_by.'|'
			.$order_dir.'|'.$enabledFilter
		);
		$tmpprods=-1;
		if ($order_dir!=2) {
			$tmpprods=Core_cacheLoad(
				'products',
				$md5,
				-1
			);
		}
		if ($tmpprods==-1) {
			if ($order_by!='') {
				$native=substr($order_by, 0, 1)==='_';
				$tmpprods1=array();
				$prods=$this->product_ids;
				$sql='select id';
				if (!$native) {
					$sql.=',data_fields';
				}
				$sql.=' from products where id in ('
					.join(', ', $this->product_ids).')';
				if ($enabledFilter==0) {
					$sql.=' and enabled';
				}
				if ($enabledFilter==1) {
				}
				if ($enabledFilter==2) {
					$sql.=' and !enabled';
				}
				if ($native) {
					$sql.=' order by '.substr($order_by, 1, strlen($order_by)-1);
					if ($order_dir==1) {
						$sql.=' desc';
					}
				}
				$values=dbAll($sql, '', 'products');
				if ($native) {
					$tmpprods=array();
					if (is_array($values)) {
						foreach ($values as $v) {
							$tmpprods[]=$v['id'];
						}
						if ($order_dir==2) {
							shuffle($tmpprods);
						}
					}
				}
				else {
					if (is_array($values)) {
						foreach ($values as $v) {
							$vals=json_decode($v['data_fields'], true);
							$key2='';
							foreach ($vals as $v2) {
								if ($v2['n']==$order_by) {
									$key2=__FromJSON($v2['v']);
								}
							}
							if (!isset($tmpprods1[$key2])) {
								$tmpprods1[$key2]=array();
							}
							$tmpprods1[$key2][]=$v['id'];
						}
					}
					if ($order_dir==1) {
						krsort($tmpprods1);
					}
					else if ($order_dir==0) {
						ksort($tmpprods1);
					}
					else if ($order_dir==2) {
						shuffle($tmpprods1);
					}
					$tmpprods=array();
					foreach ($tmpprods1 as $pids) {
						foreach ($pids as $pid) {
							$tmpprods[]=$pid;
						}
					}
					foreach ($prods as $key=>$pid) {
						$tmpprods[]=$pid;
					}
				}
			}
			else {
				$tmpprods=$this->product_ids;
			}
			Core_cacheSave(
				'products',
				$md5,
				$tmpprods
			);
		}
		// }
		// { sanitise the limits
		$cnt=count($tmpprods);
		if (!$limit) {
			$limit=$cnt;
			$start=0;
		}
		else{
			if ($start && $start>=count($this->product_ids)) {
				$start=$cnt-$limit;
			}
		}
		// }
		// { build array of items
		$prevnext='';
		$total_found=count($tmpprods);
		if ($cnt==$limit) {
			$prods=&$tmpprods;
		}
		else{
			$prods=array();
			for ($i=$start;$i<$limit+$start;++$i) {
				if (isset($tmpprods[$i])) {
					$prods[]=$tmpprods[$i];
				}
			}
			$prefix='';
			if ($PAGEDATA->vars['products_what_to_show']==2) {
				$cat=ProductCategory::getInstance($PAGEDATA->vars['products_category_to_show']);
				if ($cat) {
					$prefix=$cat->getRelativeUrl();
				}
			}
			if (!$prefix) {
				$prefix=$PAGEDATA->getRelativeUrl();
			}
			if ($start>$limit_start) {
				$prevnext.='<a class="products-prev" href="'
					.$prefix.'?start='.($start-$limit)
					.'">'.__('Previous').'</a>';
			}
			if ($limit && $start+$limit<$cnt) {
				if ($start) {
					$prevnext.=' | ';
				}
				$prevnext.='<a class="products-next" href="'
					.$prefix.'?start='.($start+$limit)
					.'">'.__('Next').'</a>';
			}
		}
		$prevnext='<div class="products-pagination">'.$prevnext.'</div>';
		// }
		// { see if there are search results
		if (isset($PAGEDATA->vars['products_add_a_search_box'])
			&& $PAGEDATA->vars['products_add_a_search_box']
		) {
			$c.='<div class="products-num-results">'
				.__('<strong>%1</strong> results found.', array($total_found), 'core')
				.'</div>';
		}
		// }
		if (!isset($PAGEDATA->vars['products_show_multiple_with'])) {
			$PAGEDATA->vars['products_show_multiple_with']=0;
		}
		$prods=array_unique($prods);
		switch ($PAGEDATA->vars['products_show_multiple_with']) {
			case 1: // { horizontal table, headers on top
				$c.=Product_datatableMultiple($prods, 'horizontal');
			break; // }
			case 2: // { vertical table, headers on left
				$c.=Product_datatableMultiple($prods, 'vertical');
			break; // }
			case 3: // { map view
				WW_addScript('products');
				WW_addCSS('/ww.plugins/products/products.css');
			return '<div id="products-mapview"></div>'; // }
			case 4: // { carousel
				WW_addScript('products');
				$c='<div id="products-carousel"><ul id="products-carousel-slider">';
				foreach ($prods as $pid) {
					$product=Product::getInstance($pid, false, $enabledFilter);
					if ($product && isset($product->id) && $product->id) {
						$typeID = $product->get('product_type_id');
						$type=ProductType::getInstance($typeID);
						if (!$type) {
							$c.='<li>'.__('Missing Product Type: %1', array($typeID), 'core')
								.'</li>';
						}
						else {
							$c.='<li id="products-'.$product->id.'" class="products-product">'
								.$type->render($product, 'multiview', 0).'</li>';
						}
					}
				}
				$c.='</ul></div>';
				WW_addScript('/j/jsor-jcarousel-7bb2e0a/jquery.jcarousel.min.js');
				WW_addCSS('/ww.plugins/products/products.css');
			return $c; // }
			default: // { use template
				if (count($prods)) { // display the first item's header
					$product=Product::getInstance($prods[0], false, $enabledFilter);
					$type=ProductType::getInstance($product->get('product_type_id'));
					if ($type) {
						$smarty=Products_setupSmarty();
						$c.=$smarty->fetch(
							USERBASE.'/ww.cache/products/templates/types_multiview_'
							.$type->id.'_header'
						);
					}
				}
				foreach ($prods as $pid) {
					$product=Product::getInstance($pid, false, $enabledFilter);
					if ($product && isset($product->id) && $product->id) {
						$typeID = $product->get('product_type_id');
						$type=ProductType::getInstance($typeID);
						if (!$type) {
							$c.=__('Missing Product Type: %1', array($typeID), 'core');
						}
						else if (isset($_REQUEST['product_id'])) {
							$c.=$type->render($product, 'singleview');
						}
						else {
							$c.=$type->render($product, 'multiview');
						}
					}
				}
				if (isset($type) && $type && count($prods)) {
					// display first item's header
					$smarty=Products_setupSmarty();
					$c.=$smarty->fetch(
						USERBASE.'/ww.cache/products/templates/types_multiview_'
						.$type->id.'_footer'
					);
				}
				// }
		}
		$categories='';
		if (!isset($_REQUEST['products-search']) && isset($this->subCategories)
			&& count($this->subCategories)
			&& !@$PAGEDATA->vars['products_dont_show_sub_categories']
		) {
			$categories='<ul class="products-categories categories">';
			foreach ($this->subCategories as $cr) {
				$cat=ProductCategory::getInstance($cr['id']);
				$categories.='<li><a href="'.$cat->getRelativeUrl().'">';
				$icon='/products/categories/'.$cr['id'].'/icon.png';
				if (file_exists(USERBASE.'f'.$icon)) {
					$subcatW=(int)$cat->vals['thumbsize_w'];
					$subcatH=(int)$cat->vals['thumbsize_h'];
					$categories.='<img src="'.$cdnprefix
						.'/a/f=getImg/w='.$subcatW.'/h='.$subcatH.'/fmt='.filemtime(USERBASE.'f'.$icon).$icon.'"/>';
				}
				$categories.='<span>'.htmlspecialchars($cr['name']).'</span>'
					.'</a></li>';
			}
			$categories.='</ul>';
		}
		return $categories.$prevnext.'<div class="products">'.$c.'</div>'.$prevnext;
	}
	// }
}

// }
class ProductsCategoriesProducts{
	static $catsByPid=array();
	static $prodsByCid=array();
	static $prodsByCids=array();
	static $activeCategories=false;
	function getByProductId($pid) {
		if (!isset(self::$catsByPid[$pid])) {
			$rs=dbAll(
				'select category_id from products_categories_products where product_id='
				.$pid, false, 'products_categories_products'
			);
			$arr=array();
			foreach ($rs as $r) {
				$arr[]=(int)$r['category_id'];
			}
			self::$catsByPid[$pid]=$arr;
		}
		return self::$catsByPid[$pid];
	}
	function getByCategoryId($cid) {
		if (!isset(self::$prodsByCid[$cid])) {
			$rs=dbAll(
				'select product_id from products_categories_products where category_id='
				.$cid, false, 'products_categories_products'
			);
			$arr=array();
			foreach ($rs as $r) {
				$arr[]=(int)$r['product_id'];
			}
			self::$prodsByCid[$cid]=$arr;
		}
		return self::$prodsByCid[$cid];
	}
	function getByCategoryIds($cids) {
		$idx=join(',', $cids);
		if (!isset(self::$prodsByCids[$idx])) {
			$rs=dbAll(
				'select product_id from products_categories_products'
				.' where category_id in ('.join(',', $cids).')',
				false, 'products_categories_products'
			);
			$arr=array();
			foreach ($rs as $r) {
				$arr[]=(int)$r['product_id'];
			}
			self::$prodsByCids[$idx]=$arr;
		}
		return self::$prodsByCids[$idx];
	}
	function delete($cid, $pid) {
		dbQuery(
			'delete from products_categories_products'
			.' where category_id='.(int)$cid
			.' and product_id='.(int)$pid
		);
		self::clearCache();
	}
	function deleteByCategoryId($id) {
		dbQuery(
			'delete from products_categories_products where category_id='.(int)$id
		);
		self::clearCache();
	}
	function deleteByProductId($id) {
		if (is_array($id)) {
			foreach ($id as $i) {
				self::deleteByProductId($i);
			}
			return;
		}
		$id=(int)$id;
		dbQuery(
			'delete from products_categories_products where product_id='.$id
		);
		self::clearCache();
	}
	function insert($cid, $pid) {
		dbQuery(
			'insert into products_categories_products'
			.' set category_id='.(int)$cid.', product_id='.(int)$pid
		);
		self::clearCache();
	}
	function listActiveCategories() {
		if (self::$activeCategories===false) {
			$sql='select distinct category_id from products_categories_products';
			$rs=dbAll($sql, false, 'products_categories_products');
			$arr=array();
			foreach ($rs as $r) {
				$arr[]=(int)$r['category_id'];
			}
			self::$activeCategories=$arr;
		}
		return self::$activeCategories;
	}
	function listAll() {
		$arr=array();
		$rs=dbAll(
			'select * from products_categories_products', false,
			'products_categories_products'
		);
		foreach ($rs as $r) {
			$arr[]=array(
				$r['category_id'],
				$r['product_id']
			);
		}
		return $arr;
	}
	function listCategoriesByProductCount() {
		return dbAll(
			'select name,category_id,count(product_id) as pids'
			.' from products_categories_products,products_categories'
			.' where category_id=id group by category_id order by pids desc',
			false, 'products_categories_products,products_categories'
		);
	}
	function clearCache() {
		self::$prodsByCid=array();
		self::$catsByPid=array();
		self::$activeCategories=false;
		Core_cacheClear('products_categories_products');
	}
}
// { class ProductType

/**
	* ProductType object
	*
	*	@category WebME
	* @package  WebME
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvweb.me/
	*/
class ProductType{
	static $instances=array();

	// { __construct

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
		$r=dbRow(
			"select * from products_types where id=$v limit 1", 'products_types'
		);
		if (!count($r)) {
			return false;
		}
		$this->data_fields=json_decode($r['data_fields']);
		$this->meta=json_decode(isset($r['meta'])?$r['meta']:'{}');
		@mkdir(USERBASE.'/ww.cache/products/templates', 0777, true);
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
		$this->is_for_sale=(int)@$r['is_for_sale'];
		$this->is_voucher=(int)@$r['is_voucher'];
		$this->stock_control=(int)@$r['stock_control'];
		$this->voucher_template=@$r['voucher_template'];
		$this->default_category=(int)$r['default_category'];
		$this->has_userdefined_price=(int)$r['has_userdefined_price'];
		$this->allow_comments=(int)$r['allowcomments'];
		self::$instances[$this->id] =& $this;
		return $this;
	}

	// }
	// { getInstance

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
			if (!array_key_exists($id, self::$instances)) {
				self::$instances[$id]=false;
			}
		}
		return self::$instances[$id];
	}

	// }
	// { getField

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

	// }
	// { getMissingImage

	/**
	  * if the product has no associated images, show a "missing image" image
	  *
	  * @param string $maxsize the size of the image it replaces
	  *
	  * @return string html of the image 
	  */
	function getMissingImage($maxsize) {
		global $cdnprefix;
		return '<img src="'.$cdnprefix.'/a/f=getImg/w='.$maxsize.'/h='.$maxsize
			.'/products/types/'.$this->id.'/image-not-found.png" />';
	}

	// }
	// { render

	/**
	  * produce a HTML version of the product
	  *
	  * @param string  $product     the product to render
	  * @param string  $template    multi-view product or single-view?
		* @param boolean $add_wrapper wrap in div.products-product before return
	  *
	  * @return string html of the product
	  */
	function render($product, $template='singleview', $add_wrapper=true) {
		global $DBVARS, $PAGEDATA;
		$GLOBALS['products_template_used']=$template;
		if (isset($DBVARS['online_store_currency'])) {
			$csym=$DBVARS['online_store_currency'];
		}
		$smarty=Products_setupSmarty();
		$smarty->assign('product', $product);
		$smarty->assign('product_id', $product->get('id'));
		$smarty->assign('_name', __FromJson($product->name));
		$smarty->assign('_stock_number', $product->stock_number);
		if (!is_array(@$this->data_fields)) {
			$this->data_fields=array();
		}
		$productVals=array();
		foreach ($this->data_fields as $f) {
			$f->n=preg_replace('/[^a-zA-Z0-9\-_]/', '_', $f->n);
			$val=$product->get($f->n);
			$required=@$f->r?' required':'';
			switch($f->t) {
				case 'checkbox': // {
					$val=$val?__('Yes'):__('No');
					$smarty->assign($f->n, $val);
				break; // }
				case 'colour': // {
					if (@$f->u) { // user-definable
						WW_addScript('/j/mColorPicker/mColorPicker.js');
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
						$val=Core_dateM2H($val);
						$smarty->assign($f->n, $val);
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
						$translateable=@$f->tr&&1;
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
							$h.='<option '.$p.' value="'.htmlspecialchars($o).'"';
							if ($translateable) {
								$h.=' class="__"';
							}
							$h.='>'.htmlspecialchars($e).'</option>';
						}
						$h.='</select>';
					}
					else {
						$val=preg_replace('/\|.*/', '', $val);
						$h=$val;
					}
					$smarty->assign($f->n, $h);
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
						$val=trim(preg_replace('/<[^>]*>/', '', $val));
						$smarty->assign(
							$f->n,
							'<textarea class="product-field '.$f->n.$required
							.'" name="products_values_'.$f->n.'">'
							.htmlspecialchars($val)
							.'</textarea>'
						);
					}
					else {
						$smarty->assign($f->n, $val);
					}
				break; // }
				case 'user': // {
					$u=User::getInstance($val, false, false);
					$val=$u?$u->get('name'):'no name';
					$smarty->assign($f->n, $val);
				break; // }
				default: // { everything else
					if (@$f->u) {
						$smarty->assign(
							$f->n,
							'<input class="product-field '.$f->n.$required
							.'" value="'.htmlspecialchars($val)
							.'" name="products_values_'.$f->n.'"/>'
						);
					}
					else {
						$smarty->assign($f->n, $val);
					}
					// }
			}
			$productVals[$f->n]=$val;
			$PAGEDATA->title=str_replace('{{$'.$f->n.'}}', $val, $PAGEDATA->title);
		}
		if (isset($PAGEDATA->vars['products_pagedescriptionoverride'])
			&& $PAGEDATA->vars['products_pagedescriptionoverride']) {
			$desc=preg_replace('/<[^>]*>/', '', $productVals['description']);
			$desc=trim(preg_replace('/\s+/m', ' ', $desc));
			$PAGEDATA->description=substr($desc, 0, 153).'...';
		}
		if (isset($product->ean)) {
			$smarty->assign('_ean', $product->ean);
		}
		// { $_name, $_stock_number, $_ean
		$PAGEDATA->title=str_replace(
			array('{{$_name}}', '{{$_stock_number}}', '{{$_ean}}'),
			array(
				$product->get('_name'), $product->get('_stock_number'),
				$product->vals['ean']
			),
			$PAGEDATA->title
		);
		// }
		$html='';
		if ($add_wrapper) {
			$classes=array('products-product');
			if ($this->stock_control) {
				$classes[]='stock-control';
			}
			$html.='<div class="'.join(' ', $classes).'" id="products-'
				.$product->get('id').'">';
		}
		$html.=$smarty->fetch(
			USERBASE.'/ww.cache/products/templates/types_'.$template.'_'.$this->id
		);
		if ($template=='singleview') {
			$PAGEDATA->vars['header_html']=(isset($PAGEDATA->vars['header_html'])?$PAGEDATA->vars['header_html']:'')
				.'<link rel="canonical" href="'.htmlspecialchars($product->getRelativeUrl()).'" />';
			if ($this->allow_comments) {
				$html.=Core_commentsShow(
					'http://'.$_SERVER['HTTP_HOST'].$product->getRelativeURL()
				);
			}
		}
		if ($add_wrapper) {
			$html.='</div>';
		}
		return $html;
	}

	// }
}

// }
// { class Utf8encode_Filter

/**
	* filter for making sure imported file is UTF8
	*
	*	@category WebME
	* @package  WebME
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvweb.me/
	*/
class Utf8encode_Filter extends php_user_filter{
	/**
		* whatever
		*
		* @param string $in        stuff
		* @param string $out       stuff
		* @param string &$consumed stuff
		* @param string $closing   stuff
		*
		* @return int
		*/
	function filter($in, $out, &$consumed, $closing) { 
		while ($bucket = stream_bucket_make_writeable($in)) { 
			$bucket->data = utf8_encode($bucket->data); 
			$consumed += $bucket->datalen; 
			stream_bucket_append($out, $bucket); 
		} 
		return PSFS_PASS_ON; 
	} 
}

// }

// { Product_datatableMultiple

/**
	* display products in a datatable format
	*
	* @param array  $products  array of product IDS to show
	* @param string $direction the orientation of the table
	*
	* @return string HTML of the table
	*/
function Product_datatableMultiple ($products, $direction) {
	$headers=array();
	$header_types=array();
	$data=array();
	foreach ($products as $pid) {
		$row=array();
		$product=Product::getInstance($pid);
		$type=ProductType::getInstance($product->vals['product_type_id']);
		if (!isset($type)) {
			$ptid=$product->vals['product_type_id'];
			return '<em>'.__(
				'Product Type with ID %1 does not exist - please alert the admin of'
				.' this site.', array($ptid), 'core'
			).'</em>';
		}
		$row['name']=$product->name;
		if (!is_array($type->data_fields)) {
			return __(
				'Product Type "%1" has no data fields.', array($type->name), 'core'
			);
		}
		foreach ($type->data_fields as $df) {
			switch ($df->t) {
				case 'checkbox': // {
					$row[$df->n]=isset($product->vals[$df->n])&&$product->vals[$df->n]
						?__('Yes')
						:__('No');
				break; // }
				case 'date': // {
					$row[$df->n] = Core_dateM2H($product->vals[$df->n]);
				break; // }
				case 'textarea' : // {
					$row[$df->n] = $product->vals[$df->n];
				break; // }
				default : // {
					$row[$df->n] = htmlspecialchars($product->vals[$df->n]);
				break; // }
			}
			if (!in_array($df->n, $headers)) {
				if ($df->ti) {
					$headers[$df->n]=$df->ti;
				}
				else {
					$headers[$df->n]=ucwords($df->n);
				}
				$header_types[$df->n]=$df->t;
			}
		}
		$data[] = $row;
	}
	switch ($direction) {
		case 'horizontal': // {
			// { datatables
			WW_addScript(
				'http://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/'
				.'jquery.dataTables.min.js'
			);
			WW_addScript('/j/datatables-delay.js');
			WW_addCSS(
				'http://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/css/'
				.'jquery.dataTables.css'
			);
			WW_addCSS(
				'http://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/css/'
				.'jquery.dataTables_themeroller.css'
			);
			// }
			WW_addScript('products/frontend/show-horizontal.js');
			WW_addCSS('/ww.plugins/products/frontend/show-horizontal.css');
			$html='<table class="product-horizontal">';
			$html.='<thead><tr>';
			foreach ($headers as $n=>$v) {
				$html.='<th o="'.htmlspecialchars($n).'">'.htmlspecialchars($v).'</th>';
			}
			$html.='</tr></thead><tbody>';
			foreach ($data as $row) {
				$html.='<tr>';
				foreach ($headers as $n=>$d) {
					$html.='<td>'.$row[$n].'</td>';
				}
				$html.='</tr>';
			}
			$html.='</tbody>';
			$html.= '<tfoot><tr>';
			foreach ($headers as $key=>$name) {
				if ($header_types[$key]=='checkbox') {
					$html.='<th><select name="search_'.$name.'"><option></option>'
						.'<option value="0">'.__('No').'</option>'
						.'<option value="1">'.__('Yes').'</option>'
						.'</select></th>';
				}
				else {
					$html.='<th><input type="text" name="search_'.$name.'" /></th>';
				}
			}
			$html.='</tr></tfoot></table>';
		return $html; // }
		case 'vertical': // {
			$html='<table class="product-vertical">';
			foreach ($headers as $n=>$d) {
				$html.='<tr class="'.$n.'"><th>'.$d.'</th>';
				foreach ($data as $row) {
					$html.='<td>'.$row[$n].'</td>';
				}
				$html.='</tr>';
			}
			$html.='</table>';
		return $html; // }
	}
}

// }
// { Products_addToCart

/**
  * check the $_REQUEST array for products to add to the cart
  *
  * @return null
  */
function Products_addToCart() {
	if (!isset($_REQUEST['products_action'])) {
		return;
	}
	require_once SCRIPTBASE.'ww.plugins/products/frontend/addToCart.php';
	return $_SESSION;
}

// }
// { Products_addTemplateToField

/**
	* add template details to a field
	*
	* @param string $str       string to manipulate
	* @param string $tplBody   template to use
	* @param string $tplHeader template header
	*
	* @return whatever
	*/
function Products_addTemplateToField($str, $tplBody='', $tplHeader='') {
	WW_addScript('products/j/variants-popup.js');
	$str=preg_replace(
		'/^(<[^>]*class=")/', '\1product-variants-popup ', $str, 1, $c
	);
	if (!$c) {
		$str=preg_replace('/>/', ' class="product-variants-popup">', $str, 1);
	}
	if ($tplBody) {
		$str=preg_replace(
			'/>/', ' data-variants-template="'.$tplBody.'">', $str, 1
		);
	}
	if ($tplHeader) {
		$str=preg_replace(
			'/>/', ' data-variants-template-header="'.$tplHeader.'">', $str, 1
		);
	}
	return $str;
}

// }
// { Products_adminPage

/**
  * form for a products admin page
  *
  * @param array $page the page database table
  * @param array $vars the page's vars data
  *
  * @return HTML of the form
  */
function Products_adminPage($page, $vars) {
	require_once SCRIPTBASE.'ww.plugins/products/admin/page-form.php';
	return $c;
}

// }
// { Products_amountInStock

/**
	* get amount of product in stock (simple)
	*
  * @param array  $params parameters to pass to the function
  * @param object $smarty the current Smarty instance
	*
	* @return int number in stock
	*/
function Products_amountInStock($params, $smarty) {
	require_once SCRIPTBASE.'ww.plugins/products/frontend/smarty-functions.php';
	return Products_amountInStock2($params, $smarty);
}

// }
// { Products_breadcrumbs

/**
  * show breadcrumbs for nav
  *
  * @param string $baseurl base URL
  *
  * @return string the HTML of the breadcrumbs
  */
function Products_breadcrumbs($baseurl) {
	global $PAGEDATA;
	$breadcrumbs='';
	Products_frontendVarsSetup($PAGEDATA);
	if (isset($_REQUEST['product_cid']) && $_REQUEST['product_cid']) {
		$c=ProductCategory::getInstance($_REQUEST['product_cid']);
		$breadcrumbs.=' &raquo; <a class="product-category" href="'
			.$c->getRelativeUrl().'">'.htmlspecialchars($c->vals['name']).'</a>';
	}
	if (isset($_REQUEST['product_id']) && $_REQUEST['product_id']) {
		$c=Product::getInstance($_REQUEST['product_id'], false, 1);
		$breadcrumbs.=' &raquo; <a class="product-product" href="'
			.$c->getRelativeUrl().'">'.htmlspecialchars($c->get('_name')).'</a>';
	}
	return $breadcrumbs;
}

// }
// { Products_categories

/**
	* get a list of product categories
	*
	* @param array  $params Smarty parameters
	* @param object $smarty the Smarty object
	*
	* @return string the list
	*/
function Products_categories ($params, $smarty) {
	$product = $smarty->smarty->tpl_vars['product']->value;
	$productID = $product->id;
	$categoryIDs=ProductsCategoriesProducts::getByProductId($productID);
	if ($categoryIDs && count($categoryIDs)) {
		$query='select count(id) from products_categories where enabled=1 and'
			.' id in ('.join(', ', $categoryIDs).')';
		$numEnabledCats=dbOne($query, 'count(id)', 'products_categories'); 	
	}
	if ($numEnabledCats==0) {
		return '<div class="products-categories">'
			.__('No Categories exist for this product').'</div>';
	}
	$c= '<ul>';
	$directCategoryPages=dbAll(
		'select page_id from page_vars where name= "products_what_to_show" and '
		.'value=2'
	); 
	foreach ($categoryIDs as $catID) {
		$pageFound = false;
		$cid = $catID['category_id'];
		$catDetails=ProductCategory::getInstance($cid)->vals;
		$catIsEnabled = $catDetails['enabled'];
		$catName = $catDetails['name'];
		if ($catIsEnabled==1) {
			foreach ($directCategoryPages as $catPage) {
				$pageID = $catPage['page_id'];
				$shownCat=dbOne(
					'select value from page_vars where name = "products_category_to_s'
					.'how" and page_id='.$pageID, 'value'
				);
				if ($shownCat==$cid) {
					$page=  Page::getInstance($pageID);
					$c.='<li><a href="'.$page->getRelativeUrl().'">'
						.htmlspecialchars($catName).'</a></li>';
					$pageFound= true;
					break;
				}
			}
			if (!$pageFound) {
				$parent = $catDetails['parent_id'];
				while ($parent>0) {
					foreach ($directCategoryPages as $catPage) {
						$pageID= $catPage['page_id'];
						$shownCat=dbOne(
							'select value from page_vars where name = "prod'
							.'ucts_category_to_show" and page_id= '.$pageID, 'value'
						);
						if ($parent==$shownCat) {
							$page = Page::getInstance($pageID);
							$c.= '<li><a href="'.$page->getRelativeUrl().'?product_cid='
								.$cid.'">'.htmlspecialchars($catName).'</a></li>';
							$pageFound= true;
							break;
						}
					}	
					$parent=ProductCategory::getInstance($parent)->vals['parent_id'];
				}
			}
			if (!$pageFound) {
				$c.='<li><a href="/_r?type=products&amp;product_cid='.$cid.'">'
					.htmlspecialchars($catName).'</a></li>';
			}
		}
	}
	$c.= '</ul>';
	return $c;
}

// }
function Products_categoriesRecount($pids=array()) {
	$cnt=count($pids);
	if (!$cnt) {
		return;
	}
	$ids=array();
	if (is_numeric($pids[0])) {
		$ids=$pids;
	}
	else if (isset($pids[0]['product_id'])) {
		foreach ($pids as $p) {
			$ids[]=$p['product_id'];
		}
	}
	foreach ($ids as $pid) {
		$r=count(ProductsCategoriesProducts::getByProductId($pid));
		dbQuery(
			'update products set num_of_categories='.$r
			.' where id='.$pid
		);
	}
	Core_cacheClear('products');
}
// { Products_categoryWatchesRun

/**
	* pseudonym for Products_categoryWatchesSend
	*
	* @return null
	*/
function Products_categoryWatchesRun() {
	Products_categoryWatchesSend();
}

// }
// { Products_categoryWatchesSend
/**
	* send list of new products to people watching the lists
	*
	* @return null
	*/

function Products_categoryWatchesSend() {
	$rs=dbAll('select * from products_watchlists');
	$users=array();
	if (is_array($rs)) {
		foreach ($rs as $r) {
			if (!isset($users[$r['user_id']])) {
				$users[$r['user_id']]=array();
			}
			$users[$r['user_id']][]=$r['category_id'];
		}
	}
	foreach ($users as $uid=>$cats) {
		$numFound=0;
		$email='';
		foreach ($cats as $cid) {
			$rs=ProductsCategoriesProducts::getByCategoryId($cid);
			$sql='select id from products where id in ('.join(',', $rs).')'
				.' and activates_on>date_add(now(), interval -1 day)';
			$rs=dbAll($sql);
			if (count($rs)) {
				$email.='<h2>'
					.ProductCategory::getInstance($cid)->vals['name']
					.'</h2><table style="width:100%">';
				foreach ($rs as $r) {
					$product=Product::getInstance($r['id']);
					$email.='<tr><td><img src="http://'.$_SERVER['HTTP_HOST']
						.'/a/f=getImg/w=160/h=160/'.$product->getDefaultImage().'"></td>'
						.'<td><h3>'.__FromJSON($product->name).'</h3>'
						.'<a href="http://'.$_SERVER['HTTP_HOST']
						.$product->getRelativeUrl().'">View this product on our website</a>'
						.'</td></tr>';
				}
				$email.'</table>';
			}
		}
		if ($email=='') {
			continue;
		}
		$user=User::getInstance($uid);
		Core_mail(
			$user->email,
			'['.$_SERVER['HTTP_HOST'].'] Watched Categories',
			$email,
			'no-reply@'.$_SERVER['HTTP_HOST']
		);
	}
}

// }
// { Products_cronHandle

/**
	* function for handling timed events
	*
	* @return null
	*/
function Products_cronHandle() {
	dbQuery(
		'update products set enabled=1,date_edited=now()'
		.' where !enabled and activates_on<now()'
		.' and expires_on>now()'
	);
	dbQuery(
		'update products set enabled=0,date_edited=now()'
		.' where enabled and expires_on<now()'
	);
	Core_cacheClear('products');
}

// }
// { Products_cronGetNext

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

// }
// { Products_customPrice

/**
	* custom price
	*
	* @return string
	*/
function Products_customPrice() {
	WW_addScript('/ww.plugins/products/j/custom-price.js');
	return '<input class="products-custom-price"'
		.' name="products_values__custom-price"/>';
}

// }
// { Products_datatable

/**
	* display a table in simple table format
	*
	* @param array  $params Smarty parameters
	* @param object $smarty the Smarty object
	*
	* @return string the table
	*/
function Products_datatable ($params, $smarty) {
	require SCRIPTBASE.'ww.plugins/products/incs/datatable.php';
	return $c;
}

// }
// { Products_expiryClock

/**
  * show expiry clock
  *
  * @param array  $params parameters to pass to the function
  * @param object $smarty the current Smarty instance
  *
  * @return HTML of the expiry clock
  */
function Products_expiryClock($params, $smarty) {
	$unlimited=@$params['none'];
	if ($unlimited=='') {
		$unlimited='no expiry date';
	}
	$pid=$smarty->smarty->tpl_vars['product']->value->id;
	$product=Product::getInstance($pid, false, 1);
	return '<div class="products-expiry-clock" unlimited="'
		.htmlspecialchars($unlimited).'">'.$product->vals['expires_on'].'</div>';
}

// }
// { Products_frontend

/**
  * render a product page
  *
  * @param object $PAGEDATA the page instance
  *
  * @return string HTML of the page
  */
function Products_frontend($PAGEDATA) {
	Products_frontendVarsSetup($PAGEDATA);
	if (!isset($PAGEDATA->vars['footer'])) {
		$PAGEDATA->vars['footer']='';
	}
	// render the products, in case the page needs to know what template was used
	$producthtml=Products_show($PAGEDATA);
	$ret=$PAGEDATA->render()
		.$producthtml
		.__FromJson($PAGEDATA->vars['footer']);
	return $ret;
}

// }
// { Products_frontendVarsSetup

/**
  * figure out what will be shown
  *
  * @param object $PAGEDATA the page instance
  *
  * @return string HTML of the page
  */
function Products_frontendVarsSetup($PAGEDATA) {
	global $PAGE_UNUSED_URI;
	if ($PAGE_UNUSED_URI) {
		$bits=explode('/', $PAGE_UNUSED_URI);
		$cat_id=isset($PAGEDATA->vars['products_category_to_show'])
			?(int)$PAGEDATA->vars['products_category_to_show']:0;
		$product_id=0;
		foreach ($bits as $bit) {
			$n=preg_replace('/[^a-zA-Z0-9]/', '_', $bit);
			$sql='select id from products_categories where parent_id='.$cat_id
				.' and name like "'.$n.'"';
			$id=dbOne($sql, 'id', 'products_categories');
			if ($id) {
				$cat_id=$id;
				$_REQUEST['product_cid']=$cat_id;
			}
			else {
				$prefix=preg_replace('/-.*/', '', $bit);
				if ($bit!=$prefix && is_numeric($prefix)) {
					$pconstraint='id='.(int)$prefix;
				}
				else {
					$n=preg_replace('/[^a-zA-Z0-9]/', '_', $bit);
					if (strpos($n, '_')===false) {
						$pconstraint='link="'.$n.'"';
					}
					else {
						$pconstraint='link like "'.$n.'"';
					}
				}
				if ($cat_id) {
					$pids=ProductsCategoriesProducts::getByCategoryId($cat_id);
					$sql='select id from products where id in ('.join(',', $pids).')'
						.' and '.$pconstraint;
					$id=dbOne(
						$sql, 'id', 'products'
					);
				}
				if (!$id) {
					$id=dbOne(
						'select id from products where '.$pconstraint, 'id', 'products'
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
}

// }
// { Products_getAddManyToCartWidget

/**
	* get a button for adding multiple items to a cart
	*
	* @param array  $params array of parameters passed to the Smarty function
	* @param object $smarty the current Smarty object
	*
	* @return string the HTML
	*/
function Products_getAddManyToCartWidget($params, $smarty) {
	$params=array_merge(
		array(
			'text'=>__('Add to Cart'),
			'redirect'=>'same',
			'type'=>'input',
			'min'=>0,
			'max'=>0
		),
		$params
	);
	if ($params['type']=='select' && $params['max']==0) {
		$params['max']=50;
	}
	$p=$smarty->smarty->tpl_vars['product']->value;
	$instock=(int)$p->vals['stockcontrol_total'];
	$stockcontrol=$instock
		?'<input type="hidden" class="stock-control-total" value="'
		.((int)$p->vals['stockcontrol_total']).'"'
		.' details="'.htmlspecialchars($p->vals['stockcontrol_details']).'"/>'
		:'';
	$redirect=$params['redirect']=='checkout'
		?'<input type="hidden" name="products_redirect" value="checkout"/>'
		:'';
	$howmany=Products_getAmountToAddWidget($params, $smarty);
	return '<form method="POST" class="products-addmanytocart">'
		.$redirect
		.'<input type="hidden" name="products_action" value="add_to_cart"/>'
		.$howmany
		.$stockcontrol
		.Products_getAddToCartButton(
			$params['text'],
			(float)$p->getPriceBase(),
			(float)$p->getPriceSale()
		)
		.'<input type="hidden" name="product_id" value="'
		. $smarty->smarty->tpl_vars['product']->value->id .'"/></form>';
}

// }
// { Products_getAddToCartButton

/**
	* create an "add to cart" button
	*
	* @param string $text      what to show on the button
	* @param float  $baseprice base price of the product
	* @param float  $saleprice sale price of the product
	*
	* @return string html of the button
	*/
function Products_getAddToCartButton($text, $baseprice=0, $saleprice=0) {
	$price=$baseprice;
	if ($saleprice && $saleprice<$baseprice) {
		$price=$saleprice;
	}
	$price=$_SESSION['currency']['symbol'].$price;
	return '<button class="submit-button __" lang-context="core"'
		.' price="'.$price.'" baseprice="'.$baseprice.'"'
		.' saleprice="'.$saleprice.'"'
		.' vat="'.$_SESSION['onlinestore_vat_percent'].'"'
		.'>'.$text.'</button>';
}

// }
// { Products_getAddToCartWidget

/**
	* get a button for adding single items to a cart
	*
	* @param array  $params array of parameters passed to the Smarty function
	* @param object $smarty the current Smarty object
	*
	* @return string the HTML
	*/
function Products_getAddToCartWidget($params, $smarty) {
	$params=array_merge(
		array(
			'text'=>__('Add to Cart'),
			'redirect'=>'same',
		),
		$params
	);
	$p=$smarty->smarty->tpl_vars['product']->value;
	$instock=(int)$p->vals['stockcontrol_total'];
	$stockcontrol=$instock
		?'<input type="hidden" class="stock-control-total" value="'
		.((int)$p->vals['stockcontrol_total']).'"'
		.' details="'.htmlspecialchars($p->vals['stockcontrol_details']).'"/>'
		:'';
	$redirect=$params['redirect']=='checkout'
		?'<input type="hidden" name="products_redirect" value="checkout"/>'
		:'';
	return '<form method="POST" class="products-addtocart">'
		.'<input type="hidden" name="products_action" value="add_to_cart" />'
		.$redirect
		.$stockcontrol
		.Products_getAddToCartButton(
			$params['text'],
			(float)$p->getPriceBase(),
			(float)$p->getPriceSale()
		)
		.'<input type="hidden" name="product_id" value="'
		. $smarty->smarty->tpl_vars['product']->value->id .'" /></form>';
}

// }
// { Products_getAmountToAddWidget

/**
	* get an input box showing the "Amount" input
	*
	* @param array  $params parameters
	* @param object $smarty smarty object
	*
	* @return string the html
	*/
function Products_getAmountToAddWidget($params, $smarty) {
	$params=array_merge(
		array(
			'type'=>'input',
			'min'=>0,
			'max'=>0
		),
		$params
	);
	switch ($params['type']) {
		case 'select': // {
			$howmany='<select name="products-howmany"'
				.' class="add_multiple_widget_amount">';
			for ($i=$params['min'];$i<$params['max'];++$i) {
				$howmany.='<option>'.$i.'</option>';
			}
			$howmany.='</select>';
		break; // }
		default: // {
			$howmany='<input name="products-howmany" value="1"'
				.' class="add_multiple_widget_amount" style="max-width:50px"/>';
		break; // }
	}
	return $howmany;
}

// }
// { Products_getProductPrice

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
	if (isset($product->vals['os_base_price'])) {
		$price=(float)$product->getPriceBase();
		$sale_price=$product->getPriceSale();
		if ($sale_price) {
			$price=$sale_price;
		}
		list($bp, $ba)=$product->getPriceBulkAll();
		if ($bp>0 && $bp<$price && $amount>=$ba) {
			$price=$bp;
		}
		$vat=$product->vals['os_tax_free']=='1'?false:true;
	}
	else {
		$price=(float)$product->get('price');
		$vat=true;
	}
	// }
	return array($price, $amount, $vat);
}

// }
// { Products_getSubCategoriesAsMenu

/**
	* get subcategories of a page as menu items
	*
	* @param misc   $ignore ignore this
	* @param object $page   page object
	*
	* @return array menu items
	*/
function Products_getSubCategoriesAsMenu($ignore, $page) {
	if (is_object($page)) {
		if (!isset($page->type)
			|| ($page->type!='products' && $page->type!='products|products')
		) {
			return false;
		}
		if (!@$page->vars['products_show_subcats_in_menu']) {
			return false;
		}
		$pid=(int)$page->vars['products_category_to_show'];
	}
	else {
		$pid=(int)$page;
	}
	$cats=dbAll(
		'select * from products_categories where parent_id='.$pid.' order by name',
		false, 'products_categories'
	);
	$rs=array();
	foreach ($cats as $cat) {
		$cat=ProductCategory::getInstance($cat['id']);
		$arr=array(
			'id'=>'products_'.$cat->vals['id'],
			'name'=>$cat->vals['name'],
			'type'=>'products|products',
			'classes'=>'menuItem',
			'link'=>$cat->getRelativeUrl(),
			'parent'=>(isset($page->id)?$page->id:0)
		);
		$subcats=dbOne(
			'select id from products_categories where parent_id='.$cat->vals['id'],
			'id', 'products_categories'
		);
		if ($subcats) {
			$arr['classes'].=' ajaxmenu_hasChildren';
			$arr['numchildren']=1;
		}
		$rs[]=$arr;
	}
	return count($rs)?$rs:false;
}

// }
// { Products_getSubCategoriesAsMenuHtml

/**
	* get subcategories of a page as menu items, html format
	*
	* @param misc  $ignore  ignore this
	* @param int   $pid     parent id
	* @param int   $depth   current depth of the menu
	* @param array $options options
	*
	* @return string menu items
	*/
function Products_getSubCategoriesAsMenuHtml(
	$ignore, $pid, $depth, $options
) {
	$s='';
	if (is_object($pid)) {
		if (!$pid->type
			|| ($pid->type!='products' && $pid->type!='products|products')
		) {
			return false;
		}
		if (!@$pid->vars['products_show_subcats_in_menu']) {
			return false;
		}
		$pid=(int)$pid->vars['products_category_to_show'];
	}
	$rs=dbAll(
		'select id,name from products_categories where parent_id='.$pid
		.' and enabled', false, 'products_categories'
	);
	if ($rs===false || !count($rs)) {
		return '';
	}

	$items=array();
	foreach ($rs as $r) {
		$item='<li>';
		$cat=ProductCategory::getInstance($r['id']);
		$item.='<a class="menu-fg menu-pid-product_'.$r['id'].'" href="'
			.$cat->getRelativeUrl().'">'
			.htmlspecialchars(__FromJson($r['name'])).'</a>';
		$item.=Products_getSubCategoriesAsMenuHtml(
			null, $r['id'], $depth+1, $options
		);
		$item.='</li>';
		$items[]=$item;
	}
	$options['columns']=(int)$options['columns'];

	// { return top-level menu
	if (!$depth) {
		return '<ul>'.join('', $items).'</ul>';
	}
	// }
	if ($options['style_from']=='1') {
		$s='';
		if ($options['background']) {
			$s.='background:'.$options['background'].';';
		}
		if ($options['opacity']) {
			$s.='opacity:'.$options['opacity'].';';
		}
		if ($s) {
			$s=' style="'.$s.'"';
		}
	}
	// { return 1-column sub-menu
	if ($options['columns']<2) {
		return '<ul'.$s.'>'.join('', $items).'</ul>';
	}
	// }
	// { return multi-column submenu
	$items_count=count($items);
	$items_per_column=ceil($items_count/$options['columns']);
	$c='<table'.$s.'><tr><td><ul>';
	for ($i=1;$i<$items_count+1;++$i) {
		$c.=$items[$i-1];
		if ($i!=$items_count && !($i%$items_per_column)) {
			$c.='</ul></td><td><ul>';
		}
	}
	$c.='</ul></td></tr></table>';
	return $c;
	// }
}

// }
// { Products_image

/**
	* display the default product image
	*
	* @param array  $params array of parameters passed to the Smarty function
	* @param object $smarty the current Smarty object
	*
	* @return string the HTML
	*/
function Products_image($params, $smarty) {
	global $cdnprefix;
	$params=array_merge(
		array(
			'width'=>200,
			'height'=>200,
			'zoom'=>0,
			'zoompos'=>'right'
		),
		$params
	);
	$imgclasses=array();
	// { zoom
	if ($params['zoom']) {
		WW_addScript('products/zoom.js');
		$imgclasses[]='zoom';
		$imgclasses[]='zoom-pos-'.$params['zoompos'];
	}
	// }
	$product=$smarty->smarty->tpl_vars['product']->value;
	$iid=$product->getDefaultImage();
	if (!$iid) {
		$iid=Core_trigger('product-images-not-found', array($product->id));
	}
	if (!$iid) {
		return Products_imageNotFound($params, $smarty);
	}
	list($link1, $link2)=@$params['nolink']
		?array('', '')
		:array(
			'<a href="'.$cdnprefix.'/a/f=getImg/'.$iid.'" target="popup">', '</a>'
		);
	$imgclasses=count($imgclasses)?' class="'.join(' ', $imgclasses).'"':'';
	return '<div class="products-image" style="width:'.$params['width']
		.'px;height:'.$params['height']
		.'px">'.$link1.'<img'.$imgclasses.' src="'.$cdnprefix.'/a/f=getImg/w='
		.$params['width'].'/h='
		.$params['height'].'/'.$iid.'"/>'
		.$link2.'</div>';
}

// }
// { Products_imageNotFound

/**
	* display an "image not found" message
	*
	* @param array  $params array of parameters passed to the Smarty function
	* @param object $smarty the current Smarty object
	*
	* @return string the HTML
	*/
function Products_imageNotFound($params, $smarty) {
	$params=array_merge(
		array('width'=>200, 'height'=>200),
		$params
	);
	$s=$params['width']<$params['height']?$params['width']:$params['height'];
	$product=$smarty->smarty->tpl_vars['product']->value;
	$pt=ProductType::getInstance($product->vals['product_type_id']);
	return $pt->getMissingImage($s);
}

// }
// { Products_images

/**
	* get a list of images for a product
	*
	* @param array  $params array of parameters passed to the Smarty function
	* @param object $smarty the current Smarty object
	*
	* @return string the images HTML
	*/
function Products_images($params, $smarty) {
	global $cdnprefix;
	$params=array_merge(
		array(
			'thumbsize'=>60,
			'display'=>'list',
			'hover'=>'opacity',
			'columns'=>3,
			'rows'=>1,
		),
		$params
	);
	// { make sure there is at least one image
	$product=$smarty->smarty->tpl_vars['product']->value;
	$defaultImage=$product->getDefaultImage();
	if (!$defaultImage) {
		return Products_imageNotFound($params, $smarty);
	}
	// }
	$vals=$product->vals;
	if (!$vals['images_directory']) {
		return Products_imageNotFound($params, $smarty);
	}
	$directory = $vals['images_directory'];
	$numfiles=0;
	$files=new DirectoryIterator(USERBASE.'/f/'.$directory);
	foreach ($files as $f) {
		if ($f->isDot()) {
			continue;
		}
		$numfiles++;
		if ($numfiles>1) {
			break;
		}
	}
	$carousel=$numfiles>1?' carousel jcarousel-skin-bland':'';
	$html='<ul class="products-images'.$carousel.'" thumbsize="'
		.$params['thumbsize'].'">';
	$files=new DirectoryIterator(USERBASE.'/f/'.$directory);
	foreach ($files as $image) {
		if ($image->isDot()) {
			continue;
		}
		$html.='<li><img src="/i/blank.gif" style="width:'.$params['thumbsize'].'px;'
			.'height:'.$params['thumbsize'].'px;background:url(\''
			.$cdnprefix.'/a/f=getImg/w='
			.$params['thumbsize'].'/h='.$params['thumbsize'].'/'.$directory.'/'
			.urlencode($image->getFilename()).'\') no-repeat center center"/></li>';
	}
	$html.='</ul>';
	if ($carousel) {
		WW_addScript('/j/jsor-jcarousel-7bb2e0a/jquery.jcarousel.min.js');
		WW_addCSS('/j/jsor-jcarousel-7bb2e0a/bland/skin.css');
	}
	return $html;
}

// }
// { Products_imageSlider

/**
	* return all products in a slider
	*
	* @param array $params parameters
	*
	* @return string HTML of the slider
	*/
function Products_imageSlider($params) {
	$width=@$params['width'];
	$height=@$params['height'];
	if ($width=='') {
		$width='100%';
	}
	if ($height=='') {
		$height='100px';
	}
	return '<div class="products-image-slider" style="width:'.$width.';height:'
		.$height.'"></div>';
}

// }
// { Products_importFile

/**
	* import from an uploaded file
	*
	* @param array $vars array of parameters
	*
	* @return status
	*/
function Products_importFile($vars=false) {
	// { set up variables
	if ($vars===false) {
		return false;
	}
	if (!@$vars->productsImportDeleteAfter['varvalue']) {
		$vars->productsImportDeleteAfter=array(
			'varvalue'=>false
		);
	}
	if (!@$vars->productsImportDelimiter['varvalue']) {
		$vars->productsImportDelimiter=array(
			'varvalue'=>','
		);
	}
	if (!@$vars->productsImportFileUrl['varvalue']) {
		$vars->productsImportFileUrl=array(
			'varvalue'=>'ww.cache/products/import.csv'
		);
	}
	if (!@$vars->productsImportImagesDir['varvalue']) {
		$vars->productsImportImagesDir=array(
			'varvalue'=>'ww.cache/products/images'
		);
	}
	$fname=USERBASE.'/'.$vars->productsImportFileUrl['varvalue'];
	// }
	if (strpos($fname, '..')!==false) {
		return array('message'=>__('Invalid file URL'));
	}
	if (!file_exists($fname)) {
		return array('message'=>__('File not uploaded'));
	}
	if (function_exists('mb_detect_encoding')) {
		$charset=mb_detect_encoding(file_get_contents($fname), 'UTF-8', true);
	}
	else {
		$charset='UTF-8';
	}
	$handle=fopen($fname, 'r');
	if ($charset!='UTF-8') {
		stream_filter_register("utf8encode", "Utf8encode_Filter")
			or die(__('Failed to register filter'));
		stream_filter_prepend($handle, "utf8encode");
	}
	$row=fgetcsv($handle, 1000, $vars->productsImportDelimiter['varvalue']);
	// { check the headers
	$headers=array();
	foreach ($row as $k=>$v) {
		if ($v) {
			$headers[$v]=$k;
		}
	}
	if (!isset($headers['_name'])
		|| !isset($headers['_ean'])
		|| !isset($headers['_stocknumber'])
		|| !isset($headers['_type'])
		|| !isset($headers['_categories'])
	) {
		$req='_name, _ean, _stocknumber, _type, _categories';
		return array(
			'message'=>__('Missing required headers (%1)', array($req), 'core').'. '
				.__('Please use the Download link to get a sample import file.'),
			'headers-found'=>$headers
		);
	}
	// }
	$product_types=array();
	$imported=0;
	$categoriesByName=array();
	$preUpload=(int)@$vars->productsImportSetExisting['varvalue'];
	$postUpload=(int)@$vars->productsImportSetImported['varvalue'];
	if ($preUpload) {
		dbQuery(
			'update products set enabled='.($preUpload-1)
			.', date_edited=now()'
		);
	}
	// { do the import
	while (
		($data=fgetcsv(
			$handle, 1000, $vars->productsImportDelimiter['varvalue']
		))!==false
	) {
		$id=0;
		$stocknumber=$data[$headers['_stocknumber']];
		// { stockcontrol_total (how many are in stock)
		$stockcontrol_total='';
		if (isset($headers['_stockcontrol_total'])
			&& isset($data[$headers['_stockcontrol_total']])
		) {
			$stockcontrol_total=',stockcontrol_total='
				.(int)$data[$headers['_stockcontrol_total']];
		}
		// }
		$type=$data[$headers['_type']];
		if (!$type) {
			$type='default';
		}
		if (isset($product_types[$type]) && $product_types[$type]) {
			$type_id=$product_types[$type];
		}
		else {
			$type_id=(int)dbOne(
				'select id from products_types where name="'.addslashes($type).'"',
				'id'
			);
			if (!$type_id) {
				$type_id=(int)dbOne('select id from products_types limit 1', 'id');
			}
			$product_types[$type]=$type_id;
		}
		$name=$data[$headers['_name']];
		$ean=$data[$headers['_ean']];
		if ($stocknumber) {
			$id=(int)dbOne(
				'select id from products where stock_number="'
				.addslashes($stocknumber)
				.'"', 'id'
			);
			if ($id) {
				dbQuery(
					'update products set ean="'.addslashes($ean).'"'
					.',product_type_id='.$type_id
					.',name="'.addslashes($name).'",date_edited=now()'
					.$stockcontrol_total
					.' where id='.$id
				);
			}
		}
		if (!$id) {
			$sql='insert into products set '
				.'stock_number="'.addslashes($stocknumber).'"'
				.$stockcontrol_total
				.',product_type_id='.$type_id
				.',name="'.addslashes($name).'"'
				.',ean="'.addslashes($ean).'"'
				.',date_created=now()'
				.',date_edited=now()'
				.',activates_on=now()'
				.',expires_on="2100-01-01"'
				.',enabled=1'
				.',data_fields="{}"'
				.',online_store_fields="{}"';
			dbQuery($sql);
			$id=dbLastInsertId();
		}
		// { get data from Products table
		$row=dbRow(
			'select data_fields,online_store_fields,activates_on,expires_on'
			.' from products where id='.$id
		);
		// }
		$data_fields=json_decode($row['data_fields'], true);
		$os_fields=json_decode($row['online_store_fields'], true);
		foreach ($headers as $k=>$v) {
			if (preg_match('/^_/', $k)) {
				continue;
			}
			foreach ($data_fields as $k2=>$v2) {
				if ($v2['n']==$k) {
					unset($data_fields[$k2]);
				}
			}
			$data_fields[]=array(
				'n'=>$k,
				'v'=>$data[$v]
			);
		}
		if (@$data[$headers['_price']]) {
			$os_fields['_price']=Products_importParseNumber(
				@$data[$headers['_price']]
			);
			$os_fields['_saleprice']=Products_importParseNumber(
				@$data[$headers['_saleprice']]
			);
			$os_fields['_bulkprice']=Products_importParseNumber(
				@$data[$headers['_bulkprice']]
			);
			$os_fields['_bulkamount']=(int)@$data[$headers['_bulkamount']];
		}
		else {
			$os_fields=array();
		}
		$dates='';
		$now=date('Y-m-d');
		if ($postUpload && ($row['activates_on']>$now || $row['expires_on']<$now)) {
			$dates=',activates_on="'.$now.'",expires_on="2100-01-01"';
		}
		if (!$postUpload && ($row['activates_on']<$now && $row['expires_on']>$now)) {
			$dates=',activates_on="'.$now.'",expires_on="'.$now.'"';
		}
		// { update the product row
		dbQuery(
			'update products set '
			.'data_fields="'.addslashes(json_encode($data_fields)).'"'
			.',online_store_fields="'.addslashes(json_encode($os_fields)).'"'
			.',date_edited=now()'
			.$dates
			.',enabled='.$postUpload
			.' where id='.$id
		);
		// }
		$cid=(int)@$vars->productsImportCategory['varvalue'];
		switch ($cid) {
			case '-1': // { from file
				ProductsCategoriesProducts::deleteByProductId($id);
				dbQuery('update products set num_of_categories=0 where id='.$id);
				Core_cacheClear('products');
				if (@$data[$headers['_categories']]) {
					$catnames=explode('|', $data[$headers['_categories']]);
					foreach ($catnames as $catname) {
						$cat=ProductCategory::getInstanceByName($catname);
						if (!$cat) {
							continue;
						}
						ProductsCategoriesProducts::insert($cat->vals['id'], $id);
						Products_categoriesRecount(array($id));
					}
				}
			break; // }
			case '0':
			break;
			default: // {
				ProductsCategoriesProducts::deleteByProductId($id);
				ProductsCategoriesProducts::insert($cid, $id);
			break; // }
		}
		$imported++;
	}
	// }
	Core_cacheClear('products');
	if ($imported) {
		return array('message'=>__(
			'Imported %1 products', array($imported), 'core'
		));
	}
	return array('message'=>__('No products imported'));
}

// }
// { Products_importFromCron

/**
	* import via cron
	*
	* @return status
	*/
function Products_importFromCron() {
	$vars=(object)AdminVars::getAllStartsWith('productsImport');
	return Products_importFile($vars);
}

// }
// { Products_importParseNumber

/**
	* parse a number, taking localisation into account
	*
	* @param string $num the number to parse
	*
	* @return float parsed number
	*/
function Products_importParseNumber($num) {
	global $DBVARS;
	return (float)str_replace(
		$DBVARS['site_dec_point'],
		'.',
		str_replace($DBVARS['site_thousands_sep'], '', $num)
	);
}

// }
// { Products_link

/**
	* get a URL for a product page
	*
	* @param array  $params array of parameters passed to the Smarty function
	* @param object $smarty the current Smarty object
	*
	* @return string the URL
	*/
function Products_link($params, $smarty) {
	return $smarty->smarty->tpl_vars['product']->value->getRelativeURL();
}

// }
// { Products_listCategories

/**
  * list product categories contained in a parent
  *
  * @param array  $params parameters to pass to the function
  * @param object $smarty the current Smarty instance
  *
  * @return HTML the list of categories
  */
function Products_listCategories($params, $smarty) {
	if (!isset($params['parent'])) {
		$parent=0;
	}
	$cats=dbAll(
		'select * from products_categories where parent_id='
		.((int)$parent).' and enabled order by name', false, 'products_categories'
	);
	$html='<ul class="products-list-categories sc_subcatnames">';
	foreach ($cats as $cat) {
		$cat=ProductCategory::getInstance($cat['id']);
		$html.='<li><a href="'.$cat->getRelativeUrl().'">'
			.htmlspecialchars($cat->vals['name']).'</a></li>';
	}
	$html.='</ul>';
	return $html;
}

// }
// { Products_listCategoryContents 

/**
  * build up a list of the contents of a product category
  *
  * @param array  $params parameters to pass to the function
  * @param object $smarty the current Smarty instance
  *
  * @return HTML the list of contents
  */
function Products_listCategoryContents($params, $smarty) {
	if (!isset($params['category'])) {
		$products=Products::getAll();
	}
	else {
		$products=Products::getByCategoryName($params['category']);
	}
	$html='<ul class="products-list-category-contents">';
	foreach ($products->product_ids as $pid) {
		$product=Product::getInstance($pid);
		$html.='<li><a href="'.$product->getRelativeURL().'">'
			.htmlspecialchars($product->name).'</a></li>';
	}
	$html.='</ul>';
	return $html;
}

// }
// { Products_categoriesListSubCats
/**
	* get a list of sub-categories in UL format
	*
	* @param int $pid product category ID
	*
	* @return string $html the UL
	*/
function Products_categoriesListSubCats($pid) {
	$cats=dbAll(
		'select id,name from products_categories '
		.'where parent_id='.$pid.' and enabled order by sortNum',
		false, 'products_categories'
	);
	if (!$cats || !count($cats)) {
		return '';
	}
	$html='<ul>';
	foreach ($cats as $c) {
		$cat=ProductCategory::getInstance($c['id']);
		$name=$c['name'];
		$html.='<li class="products-cat-'
			.preg_replace('/[^a-zA-Z0-9\-_]/', '', $name).'">'
			.'<a href="'.$cat->getRelativeUrl().'">'.htmlspecialchars($name).'</a>';
		$html.='</li>';
	}
	return $html.'</ul>';
}
// }
// { Products_map

/**
	* get a map centered on the product
	*
	* @param array  $params parameters
	* @param object $smarty the Smarty object
	*
	* @return html of the map
	*/
function Products_map($params, $smarty) {
	require_once SCRIPTBASE.'ww.plugins/products/frontend/smarty-functions.php';
	return Products_map2($params, $smarty);
}

// }
// { Products_owner

/**
	* show the product owner
	*
	* @param array  $params parameters
	* @param object $smarty the Smarty object
	*
	* @return string html of the selected variable
	*/
function Products_owner($params, $smarty) {
	require_once SCRIPTBASE.'ww.plugins/products/frontend/smarty-functions.php';
	return Products_owner2($params, $smarty);
}

// }
// { Products_plusVat

/**
	* if VAT applies to the product, return '+ VAT'
	*
	* @param array  $params array of parameters passed to the Smarty function
	* @param object $smarty the current Smarty object
	*
	* @return string VAT string
	*/
function Products_plusVat($params, $smarty) {
	$product= $smarty->smarty->tpl_vars['product']->value;
	if (!isset($product->vals['os_tax_free'])
		|| $product->vals['os_tax_free'] == '0'
	) {
		return __('+ VAT');
	}
}

// }
// { Products_priceBase

/**
	* show the base price
	*
	* @param array  $params parameters
	* @param object $smarty the Smarty object
	*
	* @return string the base price
	*/
function Products_priceBase($params, $smarty) {
	require_once SCRIPTBASE.'ww.plugins/products/frontend/smarty-functions.php';
	return Products_priceBase2($params, $smarty);
}

// }
// { Products_priceBulk

/**
	* show the bulk price, or base price if not found
	*
	* @param array  $params parameters
	* @param object $smarty the Smarty object
	*
	* @return string the bulk price
	*/
function Products_priceBulk($params, $smarty) {
	require_once SCRIPTBASE.'ww.plugins/products/frontend/smarty-functions.php';
	return Products_priceBulk2($params, $smarty);
}

// }
// { Products_priceDiscount

/**
	* show how much the discount is worth
	*
	* @param array  $params parameters
	* @param object $smarty the Smarty object
	*
	* @return string the discount amount
	*/
function Products_priceDiscount($params, $smarty) {
	require_once SCRIPTBASE.'ww.plugins/products/frontend/smarty-functions.php';
	return Products_priceDiscount2($params, $smarty);
}

// }
// { Products_priceDiscountPercent

/**
	* show the discount percentage
	*
	* @param array  $params parameters
	* @param object $smarty the Smarty object
	*
	* @return string the discount percentage
	*/
function Products_priceDiscountPercent($params, $smarty) {
	require_once SCRIPTBASE.'ww.plugins/products/frontend/smarty-functions.php';
	return Products_priceDiscountPercent2($params, $smarty);
}

// }
// { Products_priceSale

/**
	* show the sale price
	*
	* @param array  $params parameters
	* @param object $smarty the Smarty object
	*
	* @return string the sale price
	*/
function Products_priceSale($params, $smarty) {
	require_once SCRIPTBASE.'ww.plugins/products/frontend/smarty-functions.php';
	return Products_priceSale2($params, $smarty);
}

// }
// { Products_qrCode

/**
	* show a QR code for the product page
	*
	* @param array  $params parameters
	* @param object $smarty the Smarty object
	*
	* @return the QR code
	*/
function Products_qrCode($params, $smarty) {
	require_once SCRIPTBASE.'ww.plugins/products/frontend/smarty-functions.php';
	return Products_qrCode2($params, $smarty);
}

// }
// { Products_reviews

/**
	* display a list of reviews for the product
	*
	* @param array  $params array of parameters passed to the Smarty function
	* @param object $smarty the current Smarty object
	*
	* @return string the list of reviews
	*/
function Products_reviews($params, $smarty) {
	require SCRIPTBASE.'ww.plugins/products/incs/reviews.php';
	return $c;
}

// }
// { Products_search

/**
	* provide search results
	*
	* @return string results
	*/
function Products_search() {
	$keyword=addslashes(@$_REQUEST['search']);
	$rs=dbAll(
		'select * from products where data_fields like "%'.$keyword.'%"'
		.' or name like "%'.$keyword.'"'
	);
	if (!count($rs)) {
		return '';
	}
	$c='<ul class="results products">';
	foreach ($rs as $r) {
		$product=Product::getInstance($r['id'], $r);
		$c.='<li><a href="'.$product->getRelativeUrl().'">'
			.__fromJSON($product->name).'</a></li>';
	}
	$c.='</ul>';
	return $c;
}

// }
// { Products_setupSmarty

/**
	* setup Smarty with Products-specific stuff
	*
	* @return object the Smarty object
	*/
function Products_setupSmarty() {
	global $Products_smartyInstance;
	if (!isset($Products_smartyInstance)) {
		$Products_smartyInstance=Core_smartySetup(
			USERBASE.'/ww.cache/products/templates_c'
		);
	}
	$Products_smartyInstance->template_dir='/ww.cache/products/templates';
	$Products_smartyInstance->assign('PAGEDATA', $GLOBALS['PAGEDATA']);
	if (isset($_SESSION['userdata'])) {
		$Products_smartyInstance->assign('USERDATA', $_SESSION['userdata']);
	}
	if (!isset($Products_smartyInstance->registered_plugins['modifier']['template'])) {
		$Products_smartyInstance->registerPlugin(
			'modifier',
			'template',
			'Products_addTemplateToField'
		);
	}
	return $Products_smartyInstance;
}

// }
// { Products_show

/**
	* show the products in a page
	*
	* @param object $PAGEDATA the page to show
	*
	* @return string the products
	*/
function Products_show($PAGEDATA) {
	if (!isset($PAGEDATA->vars['products_what_to_show'])) {
		$PAGEDATA->vars['products_what_to_show']='0';
	}
	WW_addScript('products');
	$c='';
	// { search
	$search=isset($_REQUEST['products-search'])
		?$_REQUEST['products-search']
		:'';
	if (isset($PAGEDATA->vars['products_add_a_search_box'])) {
		$addASearchBox = $PAGEDATA->vars['products_add_a_search_box'];
	}
	if (isset($addASearchBox) && $addASearchBox) {
		$c.='<form action="'.htmlspecialchars($_SERVER['REQUEST_URI']).'"'
			.'class="products-search">'
			.'<input name="products-search"'
			.' value="'.htmlspecialchars($search).'"'
			.' placeholder="'.__('Search').'" autocomplete="off"/>'
			.'<input type="submit" value="'.__('Search').'" /></form>';
		WW_addCSS('/ww.plugins/products/products.css');
	}
	// }
	// { filter by location
	$locationFilter=0;
	if (isset($PAGEDATA->vars['products_filter_by_users_location'])
		&& $PAGEDATA->vars['products_filter_by_users_location']
	) {
		// { getSubLocations
		/**
			* get sublocations
			*
			* @param int $parent_id the parent location
			*
			* @return array array of locations
			*/
		function getSubLocations($parent_id) {
			$locs=Core_cacheLoad('core', 'locations,sublocations-'.$parent_id, -1);
			if ($locs==-1) {
				$locs=array($parent_id);
				$rs=dbAll('select id from locations where parent_id='.$parent_id);
				if ($rs) {
					foreach ($rs as $r) {
						$locs=array_merge($locs, getSubLocations($r['id']));
					}
				}
				Core_cacheSave('core', 'locations,sublocations-'.$parent_id, $locs);
			}
			return $locs;
		}

		// }
		$locid=isset($_SESSION['location']['id'])?$_SESSION['location']['id']:0;
		$locationFilter=join(',', getSubLocations($locid));
	}
	// }
	// { filter by product status
	$enabled_filter=isset($PAGEDATA->vars['products_filter_by_status'])
		?(int)$PAGEDATA->vars['products_filter_by_status']:0;
	// }
	// { set limit variables
	$limit=isset($PAGEDATA->vars['products_per_page'])
		?(int)$PAGEDATA->vars['products_per_page']
		:0;
	$limit_start=isset($PAGEDATA->vars['products_per_page_offset_min'])
		?(int)$PAGEDATA->vars['products_per_page_offset_min']
		:0;
	if (isset($_REQUEST['products_per_page'])) {
		$limit=(int)$_REQUEST['products_per_page'];
	}
	$start=isset($_REQUEST['start'])?(int)$_REQUEST['start']:$limit_start;
	if ($start<$limit_start) {
		$start=$limit_start;
	}
	// }
	// { set order fields
	$order_by=@$PAGEDATA->vars['products_order_by'];
	$order_dir=(int)@$PAGEDATA->vars['products_order_direction'];
	// }
	// { export button
	$export='';
	if (isset($PAGEDATA->vars['products_add_export_button'])
		&& $PAGEDATA->vars['products_add_export_button']
	) {
		$export='<form id="products-export" action="/ww.plugins/products/fronte'
			.'nd/export.php">'
			.'<input type="hidden" name="pid" value="'.$PAGEDATA->id.'" />'
			.'<input type="submit" value="'.__('Export').'" />'
			.'</form>';
	}
	// }
	switch($PAGEDATA->vars['products_what_to_show']) {
		case '1': // { by type
			if (@$PAGEDATA->vars['products_pagetitleoverride_multiple']) {
				$PAGEDATA->title=$PAGEDATA->vars['products_pagetitleoverride_multiple'];
			}
		return $c
			.Products_showByType(
				$PAGEDATA, 0, $start, $limit, $order_by,
				$order_dir, $search, $locationFilter, $limit_start
			)
			.$export; // }
		case '2': // { by category
			if (@$PAGEDATA->vars['products_pagetitleoverride_multiple']) {
				$GLOBALS['PAGEDATA']->title=$PAGEDATA->vars['products_pagetitleoverride_multiple'];
			}
			$ret=$c
				.Products_showByCategory(
					$PAGEDATA, 0, $start, $limit, $order_by,
					$order_dir, $search, $locationFilter, $limit_start
				)
				.$export;
		return $ret; // }
		case '3': // { by id
			if (@$PAGEDATA->vars['products_pagetitleoverride_single']) {
				$PAGEDATA->title=$PAGEDATA->vars['products_pagetitleoverride_single'];
			}
		return $c.Products_showById($PAGEDATA, 0, $enabled_filter).$export; // }
	}
	if (@$PAGEDATA->vars['products_pagetitleoverride_multiple']) {
		$PAGEDATA->title=$PAGEDATA->vars['products_pagetitleoverride_multiple'];
	}
	$ret=$c
		.Products_showAll(
			$PAGEDATA,
			$start,
			$limit,
			$order_by,
			$order_dir,
			$search,
			$locationFilter,
			$limit_start,
			$enabled_filter
		)
		.$export;
	return $ret;
}

// }
// { Products_showAll

/**
	* display all products
	*
	* @param object $PAGEDATA      the page object
	* @param int    $start         offset
	* @param int    $limit         how many products to show
	* @param string $order_by      what field to order the search by
	* @param int    $order_dir     order ascending or descending
	* @param string $search        search string to filter by
	* @param string $location      filter the products by location
	* @param int    $limit_start   lowest $start offset allowed
	* @param int    $enabledFilter whether to allow enabled/disabled products
	*
	* @return string HTML of the list of products
	*/
function Products_showAll(
	$PAGEDATA, $start=0, $limit=0, $order_by='', $order_dir=0, $search='',
	$location=0, $limit_start=0, $enabledFilter=0
) {
	if (isset($_REQUEST['product_id'])) {
		$product_id=$_REQUEST['product_id'];
		$products=Products::getAll('', $location, $enabledFilter);
	}
	else if (isset($_REQUEST['product_category'])) {
		$products=Products::getByCategory($_REQUEST['product_category']);
	}
	else {
		$products=Products::getAll($search, $location, $enabledFilter);
	}
	return $products->render(
		$PAGEDATA, $start, $limit, $order_by, $order_dir, $limit_start, $enabledFilter
	);
}

// }
// { Products_showByCategory

/**
	* display all products in a specified category
	*
	* @param object $PAGEDATA    the page object
	* @param int    $id          the category's ID
	* @param int    $start       offset
	* @param int    $limit       how many products to show
	* @param string $order_by    what field to order the search by
	* @param int    $order_dir   order ascending or descending
	* @param string $search      search string to filter by
	* @param string $location    filter the products by location
	* @param int    $limit_start lowest $start offset allowed
	*
	* @return string HTML of the list of products
	*/
function Products_showByCategory(
	$PAGEDATA, $id=0, $start=0, $limit=0, $order_by='', $order_dir=0, $search='',
	$location=0, $limit_start=0
) {
	if ($id==0) {
		$id=(int)$PAGEDATA->vars['products_category_to_show'];
	}
	$noRecurse=isset($PAGEDATA->vars['products_category_no_recurse'])
		&& $PAGEDATA->vars['products_category_no_recurse']
		?1:0;
	$products=Products::getByCategory(
		$id, $search, array(), '', 'asc', $location, $noRecurse
	);
	$cat=ProductCategory::getInstance($id);
	if ($GLOBALS['PAGEDATA']->title) {
		$GLOBALS['PAGEDATA']->title=str_replace(
			'{{$_category_name}}',
			preg_replace('/<[^>]*>/', '', $cat->getBreadcrumbs(' | ')),
			$GLOBALS['PAGEDATA']->title
		);
	}
	$ret=$products->render(
		$PAGEDATA, $start, $limit, $order_by, $order_dir, $limit_start, 1
	);
	return $ret;
}

// }
// { Products_showById

/**
	* show a specific product in a page
	*
	* @param object $PAGEDATA      the page object
	* @param int    $id            the product to show
	* @param int    $enabledFilter whether to allow enabled/disabled products
	*
	* @return string the products
	*/
function Products_showById($PAGEDATA, $id=0, $enabledFilter=0) {
	if ($id==0) {
		$id=(int)$PAGEDATA->vars['products_product_to_show'];
	}
	if ($id<1) {
		return '<em>'.__('Product %1 does not exist.', array($id), 'core').'</em>';
	}
	$product=Product::getInstance($id, false, $enabledFilter);
	$typeID = $product->get('product_type_id');
	$type=ProductType::getInstance($typeID);
	if (!$type) {
		return '<em>'
			.__('Product Type %1 does not exist.', array($typeID), 'core').'</em>';
	}
	return $type->render($product);
}

// }
// { Products_showByType

/**
	* display all products of a certain type
	*
	* @param object $PAGEDATA    the page object
	* @param int    $id          the page type's ID
	* @param int    $start       offset
	* @param int    $limit       how many products to show
	* @param string $order_by    what field to order the search by
	* @param int    $order_dir   order ascending or descending
	* @param string $search      search string to filter by
	* @param int    $limit_start results to start at what offset
	*
	* @return string HTML of the list of products
	*/
function Products_showByType(
	$PAGEDATA, $id=0, $start=0, $limit=0, $order_by='', $order_dir=0, $search='',
	$limit_start=0
) {
	if ($id==0) {
		$id=(int)$PAGEDATA->vars['products_type_to_show'];
	}
	$products=Products::getByType($id, $search);
	return $products->render(
		$PAGEDATA, $start, $limit, $order_by, $order_dir, $limit_start
	);
}

// }
// { Products_showRelatedProducts

/**
	* get a list of products that are related and show them
	*
	* @param array  $params array of parameters passed to the Smarty function
	* @param object $smarty the current Smarty object
	*
	* @return string the list of products
	*/
function Products_showRelatedProducts($params, $smarty) {
	global $cdnprefix;
	$params=array_merge(
		array(
			'mode'=>'table',
			'type'=>'',
			'thumb_width'=>180,
			'thumb_height'=>180,
			'button_text'=>__('Related Products'),
			'template_header'=>false,
			'template_body'=>false
		),
		$params
	);
	if ($params['mode']=='popup') {
		WW_addScript('products/j/products-related-popup.js');
		$button='<button class="products-related-popup"';
		if ($params['template_body']) {
			$button.=' data-template-body="'
				.htmlspecialchars($params['template_body'])
			.'"';
		}
		if ($params['template_header']) {
			$button.=' data-template-header="'
				.htmlspecialchars($params['template_header'])
			.'"';
		}
		return $button.'>'
			.$params['button_text']
			.'</button>';
	}
	$product = $smarty->smarty->tpl_vars['product']->value;
	$productID = $product->id;
	$type='';

	if ($params['type']) {
		$tid=dbOne(
			'select id from products_relation_types where name="'
			.addslashes($params['type']).'"',
			'id'
		);
		if ($tid) {
			$type=' and relation_id='.$tid;
		}
	}
	$rs=dbAll(
		'select distinct to_id from products_relations where from_id='.$productID.$type
	);
	if (count($rs)) {
		$h=array();
		$ids=array();
		foreach ($rs as $r) {
			$ids[]=$r['to_id'];
		}
		$products=Products::getbyIds($ids);
		foreach ($products->product_ids as $r) {
			$p=Product::getInstance($r);
			if (!$p || !isset($p->id)) {
				continue;
			}
			$h[]='<a class="product_related" href="'.$p->getRelativeUrl().'">';
			$vals=$p->vals;
			if (!$vals['images_directory']) {
				$h[]=htmlspecialchars(__FromJson($p->name)).'</a>';
				continue;
			}
			$iid=$p->getDefaultImage();
			if (!$iid) {
				$h[]=htmlspecialchars(__FromJson($p->name)).'</a>';
				continue;
			}
			if (!isset($vals['online_store_fields']) || !$vals['online_store_fields']) {
				$pvat = array("vat" => $_SESSION['onlinestore_vat_percent']);
				require_once SCRIPTBASE.'/ww.plugins/online-store/frontend/'
					.'smarty-functions.php';
				$h[]='<img src="'.$cdnprefix.'/a/w='.$params['thumb_width']
					.'/h='.$params['thumb_height']
					.'/f=getImg/'.$iid.'" />'
					.OnlineStore_productPriceFull2($pvat, $smarty)
					.'<p class="product_related_name">'
					.htmlspecialchars(__fromJSON($p->name)).'</p></a>';
				continue;
			}
			
			$h[]='<img src="'.$cdnprefix.'/a/w='.$params['thumb_width']
				.'/h='.$params['thumb_height']
				.'/f=getImg/'.$iid.'"/>'
				.'<br/>'.htmlspecialchars(__fromJSON($p->name)).'</a>';
		}
		return count($h)
			?'<div class="products_related_all">'
			.'<div class="product_list products_'.htmlspecialchars($params['type'])
			.'">'.join('', $h).'</div></div>'
			:__('none yet');
	}
	return '<p class="no_products_related">'.__('none yet').'</p>';
}

// }
// { Products_submitReviewForm

/**
	* submit the form for submitting a review
	*
	* @param int $productid the product being reviewed
	* @param int $userid    the user doing the review
	*
	* @return string the form
	*/
function Products_submitReviewForm($productid, $userid) {
	$formAction = '/ww.plugins/products';
	$formAction.= '/frontend/submit_review.php"';
	$c='<strong>'.__('Review This Product').'</strong><br/>'
		.'<form method="post" id= "submit_review" action='.$formAction.'>'
		.'<input type="hidden" name="productid" value="'.$productid.'" />'
		.'<input type="hidden" name="userid" value="'.$userid.'" />'
		.'<b>'.__('Rating:').' </b>'
		.'<small><i>'.__('Higher ratings are better.').' </i></small>';
	// { The rating select box
	$c.= '<select name="rating">';
	for ($i=1; $i<=5; $i++) {
		$c.= '<option>'.$i.'</option>';
	}
	$c.= '</select>';
	$c.='<br />';
	// }
	$c.= '<textarea cols="50" rows="10" name="text">';
	$c.= __('Put your comments about the product here.');
	$c.= '</textarea>';
	$c.= '<div class="centre">';
	$c.= '<input type="submit" name="submit" value="'.__('Submit Review').'" />';
	$c.= '</div>';
	$c.= '</form>';
	return $c;
}

// }
// { Products_soldAmount

/**
	* show the sold amount
	*
	* @param array  $params parameters
	* @param object $smarty the Smarty object
	*
	* @return string the amount sold
	*/
function Products_soldAmount($params, $smarty) {
	require_once SCRIPTBASE.'ww.plugins/products/frontend/smarty-functions.php';
	return Products_soldAmount2($params, $smarty);
}

// }
// { Products_user

/**
	* show the poduct's user field
	*
	* @param array  $params parameters
	* @param object $smarty the Smarty object
	*
	* @return string html of the selected variable
	*/
function Products_user($params, $smarty) {
	require_once SCRIPTBASE.'ww.plugins/products/frontend/smarty-functions.php';
	return Products_user2($params, $smarty);
}

// }
// { Products_widget

/**
  * get HTML for the Products widget
  *
  * @param array $vars any parameters to pass to the widget
  *
  * @return string HTML of the widget
  */
function Products_widget($vars=null) {
	$html='';
	$widget_type=isset($vars->widget_type) && $vars->widget_type
		?$vars->widget_type
		:'List Categories';
	$diameter=isset($vars->diameter) && $vars->diameter?$vars->diameter:280;
	$parent_cat=isset($vars->parent_cat)?((int)$vars->parent_cat):0;
	switch ($widget_type) {
		case 'Pie Chart': // { Pie Chart
			$id='products_categories_'.md5(rand());
			$cats=dbAll(
				'select id,name,associated_colour as col from products_categories '
				.'where parent_id='.$parent_cat.' and enabled order by sortNum', false,
				'products_categories'
			);
			$html.='<div id="'.$id.'" class="products-widget" style="width:'.$diameter
				.'px;height:'.($diameter+30).'px">'.__('Loading...').'</div>'
				.'<script defer="defer">$(function(){'
				.'products_widget("'.$id.'",'.json_encode($cats).');'
				.'});</script>';
			$html.='<!--[if IE]><script defer="defer" src="/ww.plugins/products/'
				.'frontend/excanvas.js"></script><![endif]-->';
			WW_addScript('products/frontend/jquery.canvas.js');
			WW_addScript('products/frontend/widget.js');
		break; // }
		case 'Products': // { Products
			$html='<div class="products-widget-products">';
			$products=Products::getByCategory($parent_cat);
			foreach ($products->product_ids as $pid) {
				$product=Product::getInstance($pid);
				$iid=$product->getDefaultImage();
				$img=$iid
					?'<a class="product-widget-imglink" href="'.$product->getRelativeURL()
					.'"><img class="product-widget-img" src="'.$GLOBALS['cdnprefix']
					.'/a/w=200/h=auto/f=getImg/'.$iid.'"/></a>'
					:'';
					$pvat = array("vat" => $_SESSION['onlinestore_vat_percent']);
				$html.='<div class="products-widget-inner">'.$img
					.'<p class="products-widget-name">'
					.htmlspecialchars(__FromJson($product->name)).'</p>'				
					.'<div class="products-widget-price">'
					.'<p class="products-widget-price-inner">'
					.OnlineStore_numToPrice(
						$product->getPriceBase()*(1+($pvat['vat'])/100)
					)
					.'</p></div>'
					.'<a class="product-widget-link" href="'.$product->getRelativeURL().'">'
					.__('more info').'</a></div>';
			}
			$html.='</div>';
		break; // }
		case 'Most Popular Products': // {
			$html='';
			$pids=array();
			if ($parent_cat) {
				$products=Products::getByCategory($parent_cat);
				$rs=dbAll(
					'select sum(quantity) as amt,product_id from online_store_sales'
					.' where product_id in ('.join(', ', $products->product_ids).')'
					.' group by product_id order by amt desc limit 8',
					'', 'online_store_sales');
			}
			else {
				$rs=dbAll(
					'select sum(quantity) as amt,product_id from online_store_sales'
					.' group by product_id order by amt desc limit 8',
					'', 'online_store_sales'
				);
			}
			foreach ($rs as $r) {
				$pid=$r['product_id'];
				$product=Product::getInstance($pid);
				$iid=$product->getDefaultImage();
				$img=$iid
					?'<a class="product-widget-imglink" href="'.$product->getRelativeURL()
					.'"><img class="product-widget-img" src="'.$GLOBALS['cdnprefix']
					.'/a/w=200/h=auto/f=getImg/'.$iid.'"/></a>'
					:'';
					$pvat = array("vat" => $_SESSION['onlinestore_vat_percent']);
				$html.='<div class="products-widget-inner">'.$img
					.'<p class="products-widget-name">'
					.htmlspecialchars(__FromJson($product->name)).'</p>'				
					.'<div class="products-widget-price">'
					.'<p class="products-widget-price-inner">'
					.OnlineStore_numToPrice(
						$product->getPriceBase()*(1+($pvat['vat'])/100)
					)
					.'</p></div>'
					.'<a class="product-widget-link" href="'.$product->getRelativeURL().'">'
					.__('more info').'</a></div>';
			}
		break; // }
		case 'Tree View': // { Tree View
			$html='<div class="product-categories-tree"><ul>';
			$cats=dbAll(
				'select id,name,associated_colour as col from products_categories '
				.'where parent_id='.$parent_cat.' and enabled order by sortNum', false,
				'products_categories'
			);
			foreach ($cats as $c) {
				$cat=ProductCategory::getInstance($c['id']);
				$name=$c['name'];
				$html.='<li class="products-cat-'
					.preg_replace('/[^a-zA-Z0-9\-_]/', '', $name).'">'
					.'<a href="'.$cat->getRelativeUrl().'">'.htmlspecialchars($name).'</a>';
				$html.=Products_categoriesListSubCats($c['id']);
				$html.='</li>';
			}
			$html.='</ul></div>';
			WW_addScript('/j/jstree/jquery.jstree.js');
			WW_addScript('products/j/categories-tree.js');
		break; // }
		default: // { List Categories
			$html='<ul class="product-categories">';
			$cats=dbAll(
				'select id,name,associated_colour as col from products_categories '
				.'where parent_id='.$parent_cat.' and enabled order by sortNum', false,
				'products_categories'
			);
			foreach ($cats as $c) {
				$name=$c['name'];
				$html.='<li class="products-cat-'
					.preg_replace('/[^a-zA-Z0-9\-_]/', '', $name).'">'
					.'<a data-cid="'.$c['id'].'"'
					.' href="/_r?type=products&product_cid='.$c['id'].'">'
					.$name.'</a>';
				if (isset($vars->show_products) && $vars->show_products=='1') {
					$cs2=ProductsCategoriesProducts::getByCategoryId($c['id']);
					if (count($cs2)) {
						$ps=dbAll(
							'select id, name from products where id in ('.join(',', $cs2).')'
						);
						$html.='<ul class="products-products">';
						foreach ($ps as $p) {
							$product=Product::getInstance($p['id']);
							$html.='<li><a data-pid="'.$p['id'].'" href="'
								.$product->getRelativeUrl().'">'
								.htmlspecialchars(__FromJson($p['name'])).'</a></li>';
						}
						$html.='</ul>';
					}
				}
				$html.='</li>';
			}
			$html.='</ul>';
			if (@$_SESSION['userdata']['id']) {
				WW_addScript('products/j/watchlists.js');
			}
		break; // }
	}
	return $html;
}

// }
