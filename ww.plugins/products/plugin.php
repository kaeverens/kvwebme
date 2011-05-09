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
	'admin' => array(
		'menu' => array(
			'Products>Products'   => 'products',
			'Products>Categories' => 'categories',
			'Products>Types'=> 'types',
			'Products>Relation Types'=> 'relation-types',
			'Products>Export Data' => 'exports'
		),
		'page_type' => 'products_admin_page_form',
		'widget' => array(
			'form_url'   => '/ww.plugins/products/admin/widget.php',
			'js_include' => array(
				'/ww.plugins/products/admin/widget.js'
			),
		)
	),
	'description' => 'Product catalogue.',
	'frontend' => array(
		'admin-script' => '/ww.plugins/products/j/frontend-admin.js',
		'page_type' => 'products_frontend',
		'widget' => 'Products_widget',
		'template_functions' => array(
			'PRODUCTS_BUTTON_ADD_TO_CART' => array(
				'function' => 'products_get_add_to_cart_button'
			),
			'PRODUCTS_BUTTON_ADD_MANY_TO_CART' => array(
				'function' => 'Products_getAddManyToCartButton'
			),
			'PRODUCTS_CATEGORIES' => array (
				'function' => 'products_categories'
			),
			'PRODUCTS_DATATABLE' => array (
				'function' => 'products_datatable'
			),
			'PRODUCTS_IMAGE' => array(
				'function' => 'products_image'
			),
			'PRODUCTS_IMAGES' => array(
				'function' => 'products_images'
			),
			'PRODUCTS_LINK' => array (
				'function' => 'products_link'
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
			'PRODUCTS_RELATED' => array (
				'function' => 'Products_showRelatedProducts'
			),
			'PRODUCTS_REVIEWS' => array (
				'function' => 'products_reviews'
			)
		)
	),
	'triggers' => array(
		'initialisation-completed' => 'products_add_to_cart'
	),
	'version' => '19'
);
// }

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
		if(isset($p['_sale_price']) && $p['_sale_price']>0)$price=$p['_sale_price'];
	  if(isset($p['_bulk_price']) && $p['_bulk_price']>0 && $p['_bulk_price']<$price && $amount>=$p['_bulk_amount'])$price=$p['_bulk_price'];
		$vat=(isset($p['_vatfree']) && $p['_vatfree']=='1')?false:true;
	}
	else {
		$price=(float)$product->get('price');
		$vat=true;
	}
	// }
	return array($price, $amount, $vat);
}
function products_admin_page_form($page,$vars){
	$id=$page['id'];
	$c='';
	require_once dirname(__FILE__).'/admin/page-form.php';
	return $c;
}
function products_frontend($PAGEDATA){
	require_once dirname(__FILE__).'/frontend/show.php';
	if (isset($_REQUEST['product_id'])) {
		$PAGEDATA->vars['products_what_to_show']=3;
		$PAGEDATA->vars['products_product_to_show']=(int)$_REQUEST['product_id'];
	}
	if (isset($_REQUEST['product_cid'])) {
		$PAGEDATA->vars['products_what_to_show']=2;
		$PAGEDATA->vars['products_category_to_show']=(int)$_REQUEST['product_cid'];
	}
	if(!isset($PAGEDATA->vars['footer']))$PAGEDATA->vars['footer']='';
	// first render the products, in case the page needs to know what template was used
	$producthtml=products_show($PAGEDATA);
	return $PAGEDATA->render()
		.$producthtml
		.$PAGEDATA->vars['footer'];
}
function products_add_to_cart(){
	if (!isset($_REQUEST['products_action'])) {
		return;
	}
	$id=(int)$_REQUEST['product_id'];
	require_once dirname(__FILE__).'/frontend/show.php';
	$product=Product::getInstance($id);
	if(!$product)return;
	$amount=1;
	if (isset($_REQUEST['products-howmany'])) {
		$amount=(int)$_REQUEST['products-howmany'];
	}
	// { find "custom" values
	$price_amendments=0;
	$vals=array();
	$long_desc='';
	$md5='';
	$pt=ProductType::getInstance($product->vals['product_type_id']);
	foreach ($_REQUEST as $k=>$v){
		if (strpos($k, 'products_values_')===0) {
			$n=str_replace('products_values_', '', $k);
			$df=$pt->getField($n);
			if ($df === false // not a real field
				|| $df->u!=1    // not a user-choosable field
			) {
				continue;
			}
			switch ($df->t) {
				case 'selectbox': // {
					$ok=0;
					$strs=explode("\n", $df->e);
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
			}
			$vals[]=$n.': '.$v;
		}
	}
	if (count($vals)) {
		$long_desc=join("\n", $vals);
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
		$vat
	);
}
function Products_listCategories($params, &$smarty){
	require_once dirname(__FILE__).'/frontend/show.php';
	return _Products_listCategories($params, $smarty);
}
function Products_listCategoryContents($params, &$smarty){
	require_once dirname(__FILE__).'/frontend/show.php';
	return _Products_listCategoryContents($params, $smarty);
}
function Products_widget($vars=null){
	require_once dirname(__FILE__).'/frontend/show.php';
	require dirname(__FILE__).'/frontend/widget.php';
	return $html;
}

class Product{
	static $instances=array();
	function __construct($v,$r=false,$enabled=true) {
		$v=(int)$v;
		if ($v<1)return false;
		$filter=$enabled?' and enabled ':'';
		if (!$r)$r=dbRow("select * from products where id=$v $filter limit 1");
		if (!count($r) || !is_array($r))return false;
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
		foreach($vals as $k=>$val) {
			if (!is_object($val)) {
				$this->vals[preg_replace('/[^a-zA-Z0-9\-_]/','_',$k)]=$val;
			}
			else {
				$this->vals[preg_replace('/[^a-zA-Z0-9\-_]/','_',$val->n)]=$val->v;
			}
		}
		if (isset($online_store_data)) {
			foreach ($online_store_data as $name=>$value) {
				$this->vals['online-store'][$name]=$value;
			}
		}
		$this->id=$r['id'];
		$this->name=$r['name'];
		self::$instances[$this->id] =& $this;
		return $this;
	}
	static function getInstance($id=0,$r=false,$enabled=true) {
		if (!is_numeric($id)) return false;
		if (!array_key_exists($id,self::$instances))return new Product($id,$r,$enabled);
		return self::$instances[$id];
	}
	function getRelativeURL () {
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
		// { Is there a page designed to display its category?
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
				.'and value in ('.join(',',$pcats).')'
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
		$this->relativeUrl='/_r?type=products&amp;product_id='.$this->id;
		return $this->relativeUrl;
	}
	function get($name) {
		if (isset($this->vals[$name])) {
			return $this->vals[$name];
		}
		return false;
	}
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
	function search($search, $field='') {
		$search=strtolower($search);
		if ($field) {
			$v=strtolower($this->get($field));
			return strpos($v, $search)!==false;
		}
		if (strpos(strtolower($this->name), $search)!==false) {
			return true;
		}
		$pt=ProductType::getInstance($this->vals['product_type_id']);
		foreach ($pt->data_fields as $df) {
			if ($df->s && strpos(strtolower($this->get($df->n)), $search)!==false) {
				return true;
			}
		}
		return false;
	}
}
class ProductType{
	static $instances=array();
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
		$tpl_cache=USERBASE.'/ww.cache/products/templates/types_multiview_'.$v.'_header';
		if (!file_exists($tpl_cache)) {
			file_put_contents($tpl_cache, $r['multiview_template_header']);
		}
		$tpl_cache=USERBASE.'/ww.cache/products/templates/types_multiview_'.$v.'_footer';
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
		$this->is_for_sale=$r['is_for_sale'];
		self::$instances[$this->id] =& $this;
		return $this;
	}
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
	function getField($name) {
		foreach ($this->data_fields as $k=>$v) {
			if ($v->n==$name) {
				return $v;
			}
		}
		return false;
	}
	function getMissingImage($maxsize) {
		return '<img src="/kfmgetfull/products/types/'.$this->id
			.'/image-not-found.png,width='.$maxsize.',height='.$maxsize.'" />';
	}
	function render($product, $template='singleview') {
		global $DBVARS;
		$GLOBALS['products_template_used']=$template;
		if (isset($DBVARS['online_store_currency'])) {
			$csym=$DBVARS['online_store_currency'];
		}
		$smarty=products_setup_smarty();
		$smarty->assign('product', $product);
		$smarty->assign('product_id', $product->get('id'));
		$corrections=isset($this->meta->allow_visitor_corrections)
			&& $template=='singleview';
		if ($corrections) {
			WW_addScript('/ww.plugins/products/frontend/visitor-corrections.js');
		}
		if (!is_array(@$this->data_fields)) {
			$this->data_fields=array();
		}
		foreach ($this->data_fields as $f) {
			$f->n=preg_replace('/[^a-zA-Z0-9\-_]/', '_', $f->n);
			if ($corrections) {
				$prefix='<span class="product-field '.$f->n.'">';
				$suffix='</span>';
			}
			else {
				$prefix='';
				$suffix='';
			}
			$val=$product->get($f->n);
			switch($f->t) {
				case 'checkbox': // {
					$smarty->assign(
						$f->n,
						$prefix.($val?'Yes':'No').$suffix
					);
				break; // }
				case 'date': // {
					$smarty->assign(
						$f->n,
						$prefix.date_m2h($val).$suffix
					);
				break; // }
				case 'selectbox': // {
					if (isset($f->u) && $f->u) {
						$h='<select name="products_values_'.$f->n.'">';
						if ($f->e=='') {
							$f->e=$val;
						}
						$es=explode("\n", $f->e);
						foreach ($es as $e) {
							$e=trim($e);
							if ($e=='') {
								continue;
							}
							$o=$e;
							if (strpos($e, '|')!==false) {
								$bits=explode('|', $e);
								$p=(float)$bits[1];
								if ($p) {
									$e=$bits[0].' ('
										.($bits[1]>0?'+'.$bits[1]:$bits[1])
										.')';
								}
							}
							$h.='<option value="'.htmlspecialchars($o).'">'
								.htmlspecialchars($e).'</option>';
						}
						$h.='</select>';
					}
					else {
						$h=$val;
					}
					$smarty->assign(
						$f->n,
						$prefix.$h.$suffix
					);
				break; // }
				default: // { everything else
					if (isset($f->u) && $f->u) {
						$smarty->assign(
							$f->n,
							'<input class="product-field '.$f->n
							.'" name="products_values_'.$f->n.'" />'
						);
					}
					else {
						$smarty->assign(
							$f->n,
							$prefix.$val.$suffix
						);
					}
					// }
			}
		}
		$smarty->assign('_name',$product->vals['name']);
		return '<div class="products-product" id="products-'.$product->get('id')
			.'">'.$smarty->fetch(
				USERBASE.'/ww.cache/products/templates/types_'.$template.'_'.$this->id
			)
			.'</div>';
	}
}
