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
	$product = $smarty->_tpl_vars['product'];
	$productID = $product->id;
	$categoryIDs=dbAll(
		'select category_id from products_categories_products where product_id='
		.$productID
	);
	if ($categoryIDs) {
		$query='select count(id) from products_categories where enabled = 1 and'
			.' id in (';
		foreach ($categoryIDs as $catID) {
			$query.= (int)$catID['category_id'].', ';
		}
		$query= substr_replace($query, '', -2);
		$query.=')';
		$numEnabledCats = dbOne($query, 'count(id)'); 	
	}
	if ($numEnabledCats==0) {
		return '<div class="products-categories">'
			.'No Categories exist for this product</div>';
	}
	$c= '<ul>';
	$directCategoryPages=dbAll(
		'select page_id from page_vars where name= "products_what_to_show" and '
		.'value=2'
	); 
	foreach ($categoryIDs as $catID) {
		$pageFound = false;
		$cid = $catID['category_id'];
		$catDetails=dbRow(
			'select name, enabled, parent_id from products_categories where id='.$cid
		);
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
					$parent=dbOne(
						'select parent_id from products_categories where id = '.$parent,
						'parent_id'
					);
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

/**
	* display a table in simple table format
	*
	* @param array  $params Smarty parameters
	* @param object $smarty the Smarty object
	*
	* @return string the table
	*/
function Products_datatable ($params, $smarty) {
	$product= $smarty->_tpl_vars['product'];
	$type= ProductType::getInstance($product->get('product_type_id'));
	if (!$type) {
		return 'Missing Product Type : '.$product->get('product_type_id');
	}
	$datafields= $type->data_fields;
	if (!is_array($datafields)) {
		$datafields=array();
	}
	$c = '<table>';
	if ($params['align']!='horizontal') {
		foreach ($datafields as $data) {
			$name = $data->ti
				?$data->ti
				:ucwords(str_replace('_', ' ', $data->n));
			$c.= '<tr><th class="left">';
			$c.= htmlspecialchars(ucfirst($name));
			$c.= '</th><td>';
			switch($data->t) {
				case 'date': // {
					$c.= date_m2h($product->vals[$data->n]);
				break; // }
				case 'checkbox': // {
					if (isset($product->vals[$data->n])) {
						$c.='Yes';
					}
					else {
						$c.= 'No';
					}
				break; // }
				case 'textarea': // {
					$c.=__FromJson($product->vals[$data->n]);
				break; // }
				default: // {
					if (isset($product->vals[$data->n])) {
						$c.=htmlspecialchars(__FromJson($product->vals[$data->n]));
					}
					else {
						$c.= '&nbsp;';
					}
					// }
			}
			$c.='</td></tr>';
		}
	}
	else {
		$c.= '<thead>';
		$c.= '<tr>';
		foreach ($datafields as $data) {
			$name = $data->ti
				?$data->ti
				:ucwords(str_replace('_', ' ', $data->n));
			$c.= '<th>'.htmlspecialchars(ucfirst($name)).'</th>';
		}
		$c.= '</tr>';
		$c.= '</thead>';
		$c.='<tbody>';
		$c.= '<tr>';
		foreach ($datafields as $data) {
			$c.= '<td>';
			switch ($data->t) {
				case 'date' : // {
					$c.= date_m2h($product->vals[$data->n]);
				break; // }
				case 'checkbox': // {
					if (isset($product->vals[$data->n])) {
						$c.= 'Yes';
					}
					else{ 
						$c.= 'No';
					}
				break; // }
				case 'textarea': // {
					$c.= $product->vals[$data->n];
				break; // }
				default: // {
					if (isset($product->vals[$data->n])) {
						$c.=htmlspecialchars($product->vals[$data->n]);
					}
					else {
						$c.='&nbsp;';
					}
					// }
			}
			$c.='</td>';
		}
		$c.= '</tr>';
		$c.= '</tbody>';
	}
	$c.= '</table>';
	return $c;
}

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
			return '<em>product type with id '.$product->vals['product_type_id']
				.' does not exist - please alert the admin of this site.</em>';
		}
		$row['name']=$product->name;
		if (!is_array($type->data_fields)) {
			return 'product type "'.$type->name.'" has no data fields.';
		}
		foreach ($type->data_fields as $df) {
			switch ($df->t) {
				case 'checkbox': // {
					$row[$df->n]=isset($product->vals[$df->n])&&$product->vals[$df->n]
						?'Yes'
						:'No';
				break; // }
				case 'date': // {
					$row[$df->n] = date_m2h($product->vals[$df->n]);
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
			WW_addScript('/ww.plugins/products/frontend/show-horizontal.js');
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
						.'<option value="0">No</option><option value="1">Yes</option>'
						.'</select></th>';
				}
				else {
					$html.='<th><input type="text" name="search_'.$name.'" /></th>';
				}
			}
			$html.='</tr></tfoot></table>';
			return $html;
			// }
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
			return $html;
			// }
	}
}

/**
	* get a button for adding single items to a cart
	*
	* @param array  $params array of parameters passed to the Smarty function
	* @param object $smarty the current Smarty object
	*
	* @return string the HTML
	*/
function Products_getAddToCartWidget($params, $smarty) {
	$text=@$params['text'];
	if (!$text) {
		$text='Add to Cart';
	}
	$instock=(int)@$smarty->_tpl_vars['product']->vals['stockcontrol_total'];
	$stockcontrol=$instock
		?'<input type="hidden" class="stock-control-total" value="'
		.((int)@$smarty->_tpl_vars['product']->vals['stockcontrol_total']).'"'
		.' details="'.htmlspecialchars(
			@$smarty->_tpl_vars['product']->vals['stockcontrol_details']
		).'"/>'
		:'';
	return '<form method="POST" class="products-addtocart">'
		.'<input type="hidden" name="products_action" value="add_to_cart" />'
		.$stockcontrol
		.Products_getAddToCartButton(
			$text,
			(float)$smarty->_tpl_vars['product']->vals['online-store']['_price'],
			(float)$smarty->_tpl_vars['product']->vals['online-store']['_sale_price']
		)
		.'<input type="hidden" name="product_id" value="'
		. $smarty->_tpl_vars['product']->id .'" /></form>';
}

/**
	* get a button for adding multiple items to a cart
	*
	* @param array  $params array of parameters passed to the Smarty function
	* @param object $smarty the current Smarty object
	*
	* @return string the HTML
	*/
function Products_getAddManyToCartWidget($params, $smarty) {
	$text=@$params['text'];
	if (!$text) {
		$text='Add to Cart';
	}
	$instock=(int)@$smarty->_tpl_vars['product']->vals['stockcontrol_total'];
	$stockcontrol=$instock
		?'<input type="hidden" class="stock-control-total" value="'
		.((int)@$smarty->_tpl_vars['product']->vals['stockcontrol_total']).'"'
		.' details="'.htmlspecialchars(
			@$smarty->_tpl_vars['product']->vals['stockcontrol_details']
		).'"/>'
		:'';
	return '<form method="POST" class="products-addmanytocart">'
		.'<input type="hidden" name="products_action" value="add_to_cart"/>'
		.'<input name="products-howmany" value="1" '
		.'class="add_multiple_widget_amount" style="width:50px"/>'
		.$stockcontrol
		.Products_getAddToCartButton(
			$text,
			(float)$smarty->_tpl_vars['product']->vals['online-store']['_price'],
			(float)$smarty->_tpl_vars['product']->vals['online-store']['_sale_price']
		)
		.'<input type="hidden" name="product_id" value="'
		. $smarty->_tpl_vars['product']->id .'"/></form>';
}

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
	return '<button class="submit-button __" lang-context="core" price="'
		.$price.'" baseprice="'.$baseprice
		.'" saleprice="'.$saleprice.'">'.$text.'</button>';
}

/**
	* display the default product image
	*
	* @param array  $params array of parameters passed to the Smarty function
	* @param object $smarty the current Smarty object
	*
	* @return string the HTML
	*/
function Products_image($params, $smarty) {
	$params=array_merge(
		array(
			'width'=>128,
			'height'=>128,
		),
		$params
	);
	$product=$smarty->_tpl_vars['product'];
	$iid=$product->getDefaultImage();
	if (!$iid) {
		return Products_imageNotFound($params, $smarty);
	}
	list($link1, $link2)=@$params['nolink']
		?array('', '')
		:array('<a href="/a/f=getImg/'.$iid.'" target="popup">', '</a>');
	return '<div class="products-image" style="width:'.$params['width']
		.'px;height:'.$params['height']
		.'px">'.$link1.'<img src="/a/f=getImg/w='.$params['width'].'/h='
		.$params['height'].'/'.$iid.'"/>'
		.$link2.'</div>';
}

/**
	* display an "image not found" message
	*
	* @param array  $params array of parameters passed to the Smarty function
	* @param object $smarty the current Smarty object
	*
	* @return string the HTML
	*/
function Products_imageNotFound($params, $smarty) {
	$s=$params['width']<$params['height']?$params['width']:$params['height'];
	$product=$smarty->_tpl_vars['product'];
	$pt=ProductType::getInstance($product->vals['product_type_id']);
	return $pt->getMissingImage($s);
}

/**
	* get a list of images for a product
	*
	* @param array  $params array of parameters passed to the Smarty function
	* @param object $smarty the current Smarty object
	*
	* @return string the images HTML
	*/
function Products_images($params, $smarty) {
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
	$product=$smarty->_tpl_vars['product'];
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
			.'height:'.$params['thumbsize'].'px;background:url(\'/a/f=getImg/w='
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

/**
	* get a URL for a product page
	*
	* @param array  $params array of parameters passed to the Smarty function
	* @param object $smarty the current Smarty object
	*
	* @return string the URL
	*/
function Products_link($params, $smarty) {
	return $smarty->_tpl_vars['product']->getRelativeURL();
}

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

/**
	* if VAT applies to the product, return '+ VAT'
	*
	* @param array  $params array of parameters passed to the Smarty function
	* @param object $smarty the current Smarty object
	*
	* @return string VAT string
	*/
function Products_plusVat($params, $smarty) {
	$product= $smarty->_tpl_vars['product'];
	if (!isset($product->vals['online-store']['_vatfree'])
		|| $product->vals['online-store']['_vatfree'] == '0'
	) {
		return '+ VAT';
	}
}

/**
	* display a list of reviews for the product
	*
	* @param array  $params array of parameters passed to the Smarty function
	* @param object $smarty the current Smarty object
	*
	* @return string the list of reviews
	*/
function Products_reviews($params, $smarty) {
	WW_addScript('/ww.plugins/products/frontend/delete.js');
	WW_addScript('/ww.plugins/products/frontend/products-edit-review.js');
	$userid = (int)$_SESSION['userdata']['id'];
	$product = $smarty->_tpl_vars['product'];
	$productid = (int)$product->id;
	$c='';
	$numReviews=dbOne(
		'select count(id) from products_reviews where product_id='.$productid,
		'count(id)'
	);
	if ($numReviews) {
		$reviews=dbAll(
			'select * from products_reviews where product_id='.$productid
		);
		$query = 'select avg(rating),product_id from products_reviews '
			.'where product_id='.$productid.' group by product_id';
		$average = dbOne($query, 'avg(rating)');
		$c.= '<div id="reviews_display">';
		$c.= '<div id="average'.$productid.'">';
		$c.= 'The average rating for this product over '.count($reviews);
		$c.= ' review';
		if (count($reviews)>1) {
			$c.= 's';
		}
		$c.= ' was '.$average.'<br/><br/>';
		$c.='</div>';
		foreach ($reviews as $review) {
			$name=dbOne(
				'select name from user_accounts where id='.(int)$review['user_id'], 
				'name'
			);
			$c.= '<div id="'.$review['id'].'">';
			$date = $review['cdate'];
			$date = substr_replace($date, '', strpos($date, ' '));
			$c.= 'Posted by '.htmlspecialchars($name).' on '.$date;
			$body = htmlspecialchars($body);
			$body = str_replace("\n", '<br />', $review['body']);
			$c.= '   ';
			$c.= '<b>Rated: </b>'.$review['rating'].'<br/>';
			$c.= ($body).'<br/>';
			if (Core_isAdmin()|| $userid==$review['user_id']) {
				// { Edit Review Link
				$timeReviewMayBeEditedUntil=dbOne(
					'select date_add("'.$review['cdate'].'", interval 15 minute) '
					.'as last_edit_time',
					'last_edit_time'
				);
				$reviewMayBeEdited=dbOne(
					'select "'.$timeReviewMayBeEditedUntil.'">now() as can_edit_review',
					'can_edit_review'
				);
				if ($reviewMayBeEdited) {
					$c.='<a href="javascript:;" onClick="edit_review('.$review['id']
						.', \''.addslashes($body).'\', '.$review['rating'].', \''
						.addslashes($review['cdate']).'\');">edit</a> ';
				}
				// }
				// { Delete Review Link
				$c.= '<a href="javascript:;" onClick="delete_review('
					.$review['id'].', '.$review['user_id'].', '.$productid
					.');">[x]</a><br/>';
				// }
			}
			$c.= '<br/></div>';
		}
		$c.= '</div>';
		$userHasNotReviewedThisProduct=!dbOne(
			'select id from products_reviews where user_id='.$userid
			.' and product_id='.$productid,
			'id'
		);
		if (isset($_SESSION['userdata']) && $userHasNotReviewedThisProduct) {
			$c.= Products_submitReviewForm($productid, $userid);
		}
	}
	else {
		$c.= '<em>Nobody has reviewed this product yet</em>';
		$c.= '<br/>';
		if (isset($_SESSION['userdata'])) {
			$c.= Products_submitReviewForm($productid, $userid);
		}
	}
	return $c;
}

/**
	* setup Smarty with Products-specific stuff
	*
	* @return object the Smarty object
	*/
function Products_setupSmarty() {
	$smarty=smarty_setup(USERBASE.'/ww.cache/products/templates_c');
	$smarty->template_dir='/ww.cache/products/templates';
	$smarty->assign('PAGEDATA', $GLOBALS['PAGEDATA']);
	if (isset($_SESSION['userdata'])) {
		$smarty->assign('USERDATA', $_SESSION['userdata']);
	}
	return $smarty;
}

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
	WW_addScript('/ww.plugins/products/j/jquery.lightbox/jquery.lightbox-0.5.js');
	WW_addCSS('/ww.plugins/products/j/jquery.lightbox/jquery.lightbox-0.5.css');
	WW_addScript('/ww.plugins/products/frontend/js.js');
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
			.'" /><input type="submit" value="Search" /></form>';
	}
	// }
	// { set limit variables
	$limit=isset($PAGEDATA->vars['products_per_page'])
		?(int)$PAGEDATA->vars['products_per_page']
		:0;
	if (isset($_REQUEST['products_per_page'])) {
		$limit=(int)$_REQUEST['products_per_page'];
	}
	$start=isset($_REQUEST['start'])?(int)$_REQUEST['start']:0;
	if ($start<0) {
		$start=0;
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
			.'<input type="submit" value="Export" />'
			.'</form>';
	}
	// }
	switch($PAGEDATA->vars['products_what_to_show']) {
		case '1': // { by type
			return $c
				.Products_showByType(
					$PAGEDATA,
					0,
					$start,
					$limit,
					$order_by,
					$order_dir,
					$search
				)
				.$export;
			// }
		case '2': // { by category
			return $c
				.Products_showByCategory(
					$PAGEDATA,
					0,
					$start,
					$limit,
					$order_by,
					$order_dir,
					$search
				)
				.$export;
			// }
		case '3': // { by id
			return $c.Products_showById($PAGEDATA).$export;
			// }
	}
	return $c
		.Products_showAll(
			$PAGEDATA,
			$start,
			$limit,
			$order_by,
			$order_dir,
			$search
		)
		.$export;
}

/**
	* show a specific product in a page
	*
	* @param object $PAGEDATA the page object
	* @param int    $id       the product to show
	*
	* @return string the products
	*/
function Products_showById($PAGEDATA, $id=0) {
	if ($id==0) {
		$id=(int)$PAGEDATA->vars['products_product_to_show'];
	}
	if ($id<1) {
		return '<em>product '.$id.' does not exist.</em>';
	}
	$product=Product::getInstance($id);
	$typeID = $product->get('product_type_id');
	$type=ProductType::getInstance($typeID);
	if (!$type) {
		return '<em>product type '.$typeID.' does not exist.</em>';
	}
	return $type->render($product);
}

/**
	* display all products in a specified category
	*
	* @param object $PAGEDATA  the page object
	* @param int    $id        the category's ID
	* @param int    $start     offset
	* @param int    $limit     how many products to show
	* @param string $order_by  what field to order the search by
	* @param int    $order_dir order ascending or descending
	* @param string $search    search string to filter by
	*
	* @return string HTML of the list of products
	*/
function Products_showByCategory(
	$PAGEDATA, $id=0, $start=0, $limit=0, $order_by='', $order_dir=0, $search=''
) {
	if ($id==0) {
		$id=(int)$PAGEDATA->vars['products_category_to_show'];
	}
	$products=Products::getByCategory($id, $search);
	return $products->render($PAGEDATA, $start, $limit, $order_by, $order_dir);
}

/**
	* display all products of a certain type
	*
	* @param object $PAGEDATA  the page object
	* @param int    $id        the page type's ID
	* @param int    $start     offset
	* @param int    $limit     how many products to show
	* @param string $order_by  what field to order the search by
	* @param int    $order_dir order ascending or descending
	* @param string $search    search string to filter by
	*
	* @return string HTML of the list of products
	*/
function Products_showByType(
	$PAGEDATA, $id=0, $start=0, $limit=0, $order_by='', $order_dir=0, $search=''
) {
	if ($id==0) {
		$id=(int)$PAGEDATA->vars['products_type_to_show'];
	}
	$products=Products::getByType($id, $search);
	return $products->render($PAGEDATA, $start, $limit, $order_by, $order_dir);
}

/**
	* display all products
	*
	* @param object $PAGEDATA  the page object
	* @param int    $start     offset
	* @param int    $limit     how many products to show
	* @param string $order_by  what field to order the search by
	* @param int    $order_dir order ascending or descending
	* @param string $search    search string to filter by
	*
	* @return string HTML of the list of products
	*/
function Products_showAll(
	$PAGEDATA, $start=0, $limit=0, $order_by='', $order_dir=0, $search=''
) {
	if (isset($_REQUEST['product_id'])) {
		$product_id= $_REQUEST['product_id'];
		$products= Products::getAll('', $product_id);
	}
	else if (isset($_REQUEST['product_category'])) {
		$products=Products::getByCategory($_REQUEST['product_category']);
	}
	else {
		$products=Products::getAll($search);
	}
	return $products->render($PAGEDATA, $start, $limit, $order_by, $order_dir);
}

/**
	* get a list of products that are related and show them
	*
	* @param array  $params array of parameters passed to the Smarty function
	* @param object $smarty the current Smarty object
	*
	* @return string the list of products
	*/
function Products_showRelatedProducts($params, $smarty) {
	$product = $smarty->_tpl_vars['product'];
	$productID = $product->id;
	$type='';

	if (isset($params['type'])) {
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
		'select to_id from products_relations where from_id='.$productID.$type
	);
	if (count($rs)) {
		$h='';
		foreach ($rs as $r) {
			$p=Product::getInstance($r['to_id']);
			$h.='<a class="product_related" href="'.$p->getRelativeUrl().'">';
			$vals=$p->vals;
			if (!$vals['images_directory']) {
				$h.=htmlspecialchars($p->name).'</a>';
				continue;
			}
			$iid=$p->getDefaultImage();
			if (!$iid) {
				$h.=htmlspecialchars($p->name).'</a>';
				continue;
			}
			$h.='<img src="/a/w=150/p=150/'.$iid.'" /><br />'
				.htmlspecialchars($p->name).'</a>';
		}
		return '<div class="product_list products_'
			.htmlspecialchars($params['type']).'">'.$h.'</div>';
	}
	return 'none yet';
}

/**
	* submit the form for submitting a review
	*
	* @param int $productid the product being reviewed
	* @param int $userid    the user doing the review
	*
	* @return string the form
	*/
function Products_submitReviewForm ($productid, $userid) {
	$formAction = '"http://webworks-webme';
	$formAction.= '/ww.plugins/products';
	$formAction.= '/frontend/submit_review.php"';
	$c.='<strong>Review This Product</strong><br/>';
	$c.='<form method="post" id= "submit_review" action='.$formAction.'>';
	$c.='<input type="hidden" name="productid" value="'.$productid.'" />';
	$c.='<input type="hidden" name="userid" value="'.$userid.'" />';
	$c.= '<b>Rating: </b>';
	$c.= '<small><i>higher ratings are better </i></small>';
	// { The rating select box
	$c.= '<select name="rating">';
	for ($i=1; $i<=5; $i++) {
		$c.= '<option>'.$i.'</option>';
	}
	$c.= '</select>';
	$c.='<br />';
	// }
	$c.= '<textarea cols="50" rows="10" name="text">';
	$c.= 'Put your comments about the product here';
	$c.= '</textarea>';
	$c.= '<div class="centre">';
	$c.= '<input type="submit" name="submit" 
		value="Submit Review" />';
	$c.= '</div>';
	$c.= '</form>';
	return $c;
}

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
	/**
		* constructor for the class
		*
		* @param array  $vs         variable identifiers for the products
		* @param string $md5        unique identifier for the collection
		* @param string $search     search string to filter by
		* @param array  $search_arr array of search strings to filter by
		* @param string $sort_col   field to sort by
		* @param string $sort_dir   sort direction
		*
		* @return object the category instance
		*/
	function __construct(
		$vs, $md5, $search='', $search_arr=array(), $sort_col='', $sort_dir='asc'
	) {
		$this->product_ids=Core_cacheLoad('products', 'products_'.$md5);
		if ($this->product_ids===false) {
			if ($search!='') {
				$arr=array();
				foreach ($vs as $v) {
					$p=Product::getInstance($v);
					if (!$p) {
						continue;
					}
					if ($p->search($search)) {
						$arr[]=$v;
					}
				}
				$vs = $arr;
			}
			if (count($search_arr)) {
				$arr=array();
				foreach ($vs as $v) {
					$p=Product::getInstance($v);
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
					$p=Product::getInstance($v);
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
	/**
		* get all products
		*
		* @param string $search search string to filter by
		*
		* @return object instance of Products object
		*/
	static function getAll($search='') {
		$id=md5('all|'.$search);
		if (!array_key_exists($id, self::$instances)) {
			$product_ids=array();
			$rs=dbAll('select id from products where enabled');
			foreach ($rs as $r) {
				$product_ids[]=$r['id'];
			}
			new Products($product_ids, $id, $search);
		}
		return self::$instances[$id];
	}
	/**
		* retrieve products within a specified category
		*
		* @param int    $id         the product type's ID
		* @param string $search     search string to filter by
		* @param array  $search_arr array of search strings to filter by
		* @param string $sort_col   field to sort by
		* @param string $sort_dir   sort direction
		*
		* @return object instance of Products object
		*/
	static function getByCategory(
		$id, $search='', $search_arr=array(), $sort_col='', $sort_dir='asc'
	) {
		if (!is_numeric($id)) {
			return false;
		}
		$md5=md5($id.'|'.$search.'|'.join(',', $search_arr));
		if (!array_key_exists($md5, self::$instances)) {
			$product_ids=array();
			if ($search=='' && !count($search_arr)) {
				$rs=dbAll(
					'select id from products,products_categories_products'
					.' where id=product_id and enabled and category_id='.$id
				);
			}
			else {
				$sql='select id from products,products_categories_products'
					.' where id=product_id and enabled and category_id='.$id;
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
			$pcs=Core_cacheLoad('products', 'productcategoriesenabled_parent_'.$id);
			if (!$pcs) {
				$pcs=dbAll(
					'select id,name from products_categories where parent_id='.$id
					.' and enabled order by name'
				);
			}
			self::$instances[$md5]->subCategories=$pcs;
		}
		return self::$instances[$md5];
	}
	/**
		* retrieve products by their category name
		*
		* @param string $name name of the category
		*
		* @return object instance of Products object
		*/
	static function getByCategoryName($name) {
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
			return Products::getAll();
		}
		return Products::getByCategory($cid);
	}
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
		$id, $search='', $search_arr=array(), $sort_col='', $sort_dir='asc'
	) {
		if (!is_numeric($id)) {
			return false;
		}
		$md5=md5(
			$id.'|'.$search.'|'.print_r($search_arr, true).'|'.$sort_col.'|'.$sort_dir
		);
		if (!array_key_exists($md5, self::$instances)) {
			$product_ids=array();
			$rs=dbAll('select id from products where enabled and product_type_id='.$id);
			foreach ($rs as $r) {
				$product_ids[]=$r['id'];
			}
			new Products($product_ids, $md5, $search, $search_arr, $sort_col, $sort_dir);
		}
		return self::$instances[$md5];
	}
	/**
		* render a list of products to HTML
		*
		* @param object $PAGEDATA  the page object
		* @param int    $start     offset
		* @param int    $limit     how many products to show
		* @param string $order_by  what field to order the search by
		* @param int    $order_dir order ascending or descending
		*
		* @return string the HTML of the products list
		*/
	function render($PAGEDATA, $start=0, $limit=0, $order_by='', $order_dir=0) {
		$c='';
		// { sort based on $order_by
		if ($order_by!='') {
			$tmpprods1=array();
			$prods=$this->product_ids;
			foreach ($prods as $key=>$pid) {
				$prod=$product=Product::getInstance($pid);
				if ($product->get($order_by)) {
					if (!isset($tmpprods1[$product->get($order_by)])) {
						$tmpprods1[$product->get($order_by)]=array();
					}
					$tmpprods1[$product->get($order_by)][]=$pid;
					unset($prods[$key]);
				}
			}
			if ($order_dir) {
				krsort($tmpprods1);
			}
			else {
				ksort($tmpprods1);
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
		else {
			$tmpprods=&$this->product_ids;
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
			if ($start) {
				$prevnext.='<a class="products-prev" href="'
					.$PAGEDATA->getRelativeUrl().'?start='.($start-$limit)
					.'">&lt;-- prev</a>';
			}
			if ($limit && $start+$limit<$cnt) {
				if ($start) {
					$prevnext.=' | ';
				}
				$prevnext.='<a class="products-next" href="'
					.$PAGEDATA->getRelativeUrl().'?start='.($start+$limit)
					.'">next --&gt;</a>';
			}
		}
		$prevnext='<div class="products-pagination">'.$prevnext.'</div>';
		// }
		// { see if there are search results
		if (isset($PAGEDATA->vars['products_add_a_search_box'])
			&& $PAGEDATA->vars['products_add_a_search_box']
		) {
			$c.='<div class="products-num-results"><strong>'
				.$total_found.'</strong> results found.</div>';
		}
		// }
		if (!isset($PAGEDATA->vars['products_show_multiple_with'])) {
			$PAGEDATA->vars['products_show_multiple_with']=0;
		}
		switch ($PAGEDATA->vars['products_show_multiple_with']) {
			case 1: // { horizontal table, headers on top
				$c.=Product_datatableMultiple($prods, 'horizontal');
			break; // }
			case 2: // { vertical table, headers on left
				$c.=Product_datatableMultiple($prods, 'vertical');
			break; // }
			case 3: // { map view
				WW_addScript('/ww.plugins/products/frontend/js.js');
				WW_addCSS('/ww.plugins/products/products.css');
				return '<div id="products-mapview"></div>';
				// }
			case 4: // { carousel
				WW_addScript('/ww.plugins/products/frontend/js.js');
				$c='<div id="products-carousel"><ul id="products-carousel-slider">';
				foreach ($prods as $pid) {
					$product=Product::getInstance($pid);
					if ($product && isset($product->id) && $product->id) {
						$typeID = $product->get('product_type_id');
						$type=ProductType::getInstance($typeID);
						if (!$type) {
							$c.='<li>Missing product type: '.$typeID.'</li>';
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
				return $c;
				// }
			default: // { use template
				if (count($prods)) { // display the first item's header
					$product=Product::getInstance($prods[0]);
					$type=ProductType::getInstance($product->get('product_type_id'));
					$smarty=Products_setupSmarty();
					$c.=$smarty->fetch(
						USERBASE.'/ww.cache/products/templates/types_multiview_'.$type->id.'_header'
					);
				}
				foreach ($prods as $pid) {
					$product=Product::getInstance($pid);
					if ($product && isset($product->id) && $product->id) {
						$typeID = $product->get('product_type_id');
						$type=ProductType::getInstance($typeID);
						if (!$type) {
							$c.='Missing product type: '.$typeID;
						}
						else if (isset($_REQUEST['product_id'])) {
							$c.=$type->render($product, 'singleview');
						}
						else {
							$c.=$type->render($product, 'multiview');
						}
					}
				}
				if (count($prods)) { // display the first item's header
					$smarty=Products_setupSmarty();
					$c.=$smarty->fetch(
						USERBASE.'/ww.cache/products/templates/types_multiview_'.$type->id.'_footer'
					);
				}
				// }
		}
		$categories='';
		if (!isset($_REQUEST['products-search'])) {
			if (isset($this->subCategories) && count($this->subCategories)) {
				$categories='<ul class="categories">';
				foreach ($this->subCategories as $cr) {
					$cat=ProductCategory::getInstance($cr['id']);
					$categories.='<li><a href="'.$cat->getRelativeUrl().'">';
					$icon='/f/products/categories/'.$cr['id'].'/icon.png';
					if (file_exists(USERBASE.$icon)) {
						$categories.='<img src="'.$icon.'"/>';
					}
					$categories.='<span>'.htmlspecialchars($cr['name']).'</span>'
						.'</a></li>';
				}
				$categories.='</ul>';
			}
		}
		return $categories.$prevnext.'<div class="products">'.$c.'</div>'.$prevnext;
	}
}
