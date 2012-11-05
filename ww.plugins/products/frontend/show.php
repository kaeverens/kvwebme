<?php
/**
	* display products
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

if (!file_exists(USERBASE.'/ww.cache/products')) {
	mkdir(USERBASE.'/ww.cache/products');
}
if (!file_exists(USERBASE.'/ww.cache/products/templates')) {
	mkdir(USERBASE.'/ww.cache/products/templates');
	mkdir(USERBASE.'/ww.cache/products/templates_c');
}

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
			WW_addScript('/j/jquery.dataTables-1.7.5/jquery.dataTables.min.js');
			WW_addCSS('/j/jquery.dataTables-1.7.5/jquery.dataTables.css');
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
// { Products_listCategories2

/**
	* list all categories contained within a parent category
	*
	* @param array  $params array of parameters passed to the Smarty function
	* @param object $smarty the current Smarty object
	*
	* @return string the categories
	*/
function Products_listCategories2($params, $smarty) {
	if (!isset($params['parent'])) {
		$parent=0;
	}
	$cats=dbAll(
		'select * from products_categories where parent_id='
		.((int)$parent).' and enabled order by name'
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
	* display the contents of a product category
	*
	* @param array  $params array of parameters passed to the Smarty function
	* @param object $smarty the current Smarty object
	*
	* @return string the contents
	*/
function Products_listCategoryContents2($params, $smarty) {
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
		$c.='<form action="'.$PAGEDATA->getRelativeUrl()
			.'" class="products-search"><input name="products-search" value="'
			.htmlspecialchars($search)
			.'" /><input type="submit" value="'.__('Search').'" /></form>';
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
				$PAGEDATA->title=$PAGEDATA->vars['products_pagetitleoverride_multiple'];
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
	$products=Products::getByCategory(
		$id, $search, array(), '', 'asc', $location
	);
	$ret=$products->render(
		$PAGEDATA, $start, $limit, $order_by, $order_dir, $limit_start
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

// { Products class

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
				$arr=array();
				foreach ($vs as $v) {
					$p=Product::getInstance($v, false, $enabledFilter);
					if (!$p) {
						continue;
					}
					if ($p->search($search)) {
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
		$location=0
	) {
		if (!is_numeric($id)) {
			return false;
		}
		$locmd5=is_array($location)?join(',', $location):0;
		$md5=md5($id.'|'.$search.'|'.join(',', $search_arr).'|'.$locmd5);
		if (!array_key_exists($md5, self::$instances)) {
			$product_ids=array();
			$locFilter=$location?' and location in ('.$location.')':'';
			$sql='select id from products,products_categories_products'
				.' where id=product_id'.$locFilter.' and enabled and category_id='.$id;
			if ($search=='' && !count($search_arr)) {
				$md5_2=md5($sql);
				$rs=Core_cacheLoad('products', $md5_2, -1);
				if ($rs===-1) {
					$rs=dbAll($sql);
					Core_cacheSave('products', $md5_2, $rs);
				}
			}
			else {
				if ($search!='') {
					$sql.=' and (name like "%'.addslashes($search)
						.'%" or data_fields like "%'.addslashes($search).'%")';
				}
				$rs=dbAll($sql);
				$cats=dbAll('select id from products_categories where parent_id='.$id);
				foreach ($cats as $cat) {
					$ps=Products::getByCategory($cat['id'], $search, $search_arr);
					foreach ($ps->product_ids as $p) {
						$rs[]=array('id'=>$p);
					}
				}
			}
			foreach ($rs as $r) {
				$product_ids[]=$r['id'];
			}
			new Products($product_ids, $md5, $search, $search_arr);
			$pcs=Core_cacheLoad(
				'products', 'productcategoriesenabled_parent_'.$id, -1
			);
			if ($pcs===-1) {
				$pcs=dbAll(
					'select id,name from products_categories where parent_id='.$id
					.' and enabled order by name'
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
	static function getByCategoryName($name, $enabledFilter=0) {
		$arr=explode('/', $name);
		if ($arr[0]=='') {
			array_shift($arr);
		}
		$cid=0;
		foreach ($arr as $name) {
			$cid=dbOne(
				'select id from products_categories where parent_id='.$cid
				.' and name="'.addslashes($name).'" limit 1',
				'id'
			);
			if (!$cid) {
				break;
			}
		}
		if (!$cid) {
			return Products::getAll('', 0, $enabledFilter);
		}
		return Products::getByCategory($cid);
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
				$tmpprods1=array();
				$prods=$this->product_ids;
				$sql='select id,data_fields from products where id in ('
					.join(', ', $this->product_ids).')';
				if ($enabledFilter==0) {
					$sql.=' and enabled';
				}
				if ($enabledFilter==1) {
					$sql='';
				}
				if ($enabledFilter==2) {
					$sql.=' and !enabled';
				}
				if (substr($order_by, 0, 1)==='_') {
					$sql.=' order by '.substr($order_by, 1, strlen($order_by)-1);
					if ($order_dir==1) {
						$sql.=' desc';
					}
				}
				$values=dbAll($sql);
				if (substr($order_by, 0, 1)==='_') {
					$tmpprods=array();
					foreach ($values as $v) {
						$tmpprods[]=$v['id'];
					}
					if ($order_dir==2) {
						shuffle($tmpprods);
					}
				}
				else {
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
				$start=$cnt-$limit-1;
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
			if ($start>$limit_start) {
				$prevnext.='<a class="products-prev" href="'
					.$PAGEDATA->getRelativeUrl().'?start='.($start-$limit)
					.'">'.__('Previous').'</a>';
			}
			if ($limit && $start+$limit<$cnt) {
				if ($start) {
					$prevnext.=' | ';
				}
				$prevnext.='<a class="products-next" href="'
					.$PAGEDATA->getRelativeUrl().'?start='.($start+$limit)
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
		if (!isset($_REQUEST['products-search'])) {
			if (isset($this->subCategories) && count($this->subCategories)) {
				$categories='<ul class="products-categories categories">';
				foreach ($this->subCategories as $cr) {
					$cat=ProductCategory::getInstance($cr['id']);
					$categories.='<li><a href="'.$cat->getRelativeUrl().'">';
					$icon='/products/categories/'.$cr['id'].'/icon.png';
					if (file_exists(USERBASE.'f'.$icon)) {
						$categories.='<img src="/a/f=getImg/w=120/h=120'.$icon.'"/>';
					}
					$categories.='<span>'.htmlspecialchars($cr['name']).'</span>'
						.'</a></li>';
				}
				$categories.='</ul>';
			}
		}
		return $categories.$prevnext.'<div class="products">'.$c.'</div>'.$prevnext;
	}
	// }
}

// }
