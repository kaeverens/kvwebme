<?php
$kfm_do_not_save_session=true;
require_once KFM_BASE_PATH.'/api/api.php';
require_once KFM_BASE_PATH.'/initialise.php';
if (!file_exists(USERBASE.'/ww.cache/products')) {
	mkdir(USERBASE.'/ww.cache/products');
}
if (!file_exists(USERBASE.'/ww.cache/products/templates')) {
	mkdir(USERBASE.'/ww.cache/products/templates');
	mkdir(USERBASE.'/ww.cache/products/templates_c');
}
function products_categories ($params, &$smarty) {
	$product = $smarty->_tpl_vars['product'];
	$productID = $product->id;
	$categoryIDs 
		= dbAll(
			'select category_id '.
			'from products_categories_products '
			.'where product_id='.$productID);
	if ($categoryIDs) {
		$query
			= 'select count(id) 
				from products_categories 
				where enabled = 1 and id in (';
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
	$directCategoryPages
		= dbAll(
			'select page_id '
			.'from page_vars '
			.'where name= "products_what_to_show" and value=2'
		); 
	foreach ($categoryIDs as $catID) {
		$pageFound = false;
		$cid = $catID['category_id'];
		$catDetails 
			= dbRow(
				'select name, enabled, parent_id '
				.'from products_categories '
				.'where id='.$cid
			);
		$catIsEnabled = $catDetails['enabled'];
		$catName = $catDetails['name'];
		if ($catIsEnabled==1) {
			foreach ($directCategoryPages as $catPage) {
				$pageID = $catPage['page_id'];
				$shownCat 
					= dbOne(
						'select value '
						.'from page_vars '
						.'where name = "products_category_to_show" '
						.'and page_id='.$pageID, 
						'value'
					);
				if ($shownCat==$cid) {
					$page=  Page::getInstance($pageID);
					$c.='<li>'
						.'<a href="'.$page->getRelativeUrl().'">'
						.htmlspecialchars($catName)
						.'</a></li>';
					$pageFound= true;
					break;
				}
			}
			if (!$pageFound) {
				$parent = $catDetails['parent_id'];
				while ($parent>0) {
					foreach ($directCategoryPages as $catPage) {
						$pageID= $catPage['page_id'];
						$shownCat
							= dbOne(
								'select value '
								.'from page_vars '
								.'where name = "products_category_to_show" '
								.'and page_id= '.$pageID, 
								'value'
							);
						if ($parent==$shownCat) {
							$page = Page::getInstance($pageID);
							$c.= '<li><a href="'.$page->getRelativeUrl()
								.'?product_cid='.$cid.'">';
							$c.= htmlspecialchars($catName);
							$c.='</a></li>';
							$pageFound= true;
							break;
						}
					}	
					$parent 
						= dbOne(
							'select parent_id '
							.'from products_categories '
							.'where id = '.$parent,
							'parent_id'
						);

				}
			}
			if (!$pageFound) {
				$c.='<li><a href="/_r?type=products&amp;product_cid='.$cid.'">';
				$c.=htmlspecialchars($catName);
				$c.='</a></li>';
			}
		}
	}
	$c.= '</ul>';
	return $c;
}
function products_datatable ($params, &$smarty) {
	$product= $smarty->_tpl_vars['product'];
	$type= ProductType::getInstance($product->get('product_type_id'));
	if (!$type) {
		return 'Missing Product Type : '.$product->get('product_type_id');
	}
	$datafields= $type->data_fields;
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
					$c.=$product->vals[$data->n];
				break; // }
				default: // {
					if (isset($product->vals[$data->n])) {
						$c.=htmlspecialchars($product->vals[$data->n]);
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
function Product_datatableMultiple (&$products, $direction) {
	$headers=array();
	$header_types=array();
	$data=array();
	foreach ($products as $pid) {
		$row=array();
		$product=Product::getInstance($pid);
		$type=ProductType::getInstance($product->vals['product_type_id']);
		$row['name']=$product->name;
		foreach($type->data_fields as $df){
			switch ($df->t) {
				case 'checkbox': // {
					$row[$df->n] = (isset($product->vals[$df->n])&&$product->vals[$df->n])?'Yes':'No';
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
			if(!in_array($df->n,$headers)){
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
	switch ($direction){
		case 'horizontal': // {
			WW_addScript('/j/jquery.dataTables.min.js');
			WW_addScript('/ww.plugins/products/frontend/show-horizontal.js');
			WW_addCSS('/ww.plugins/products/frontend/show-horizontal.css');
			WW_addCSS('/j/jquery.dataTables.css');
			$html='<table class="product-horizontal">';
			$html.='<thead><tr>';
			foreach($headers as $n=>$v) {
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
function products_get_add_to_cart_button($params,&$smarty) {
	return '<form method="POST">'
		.'<input type="hidden" name="products_action" value="add_to_cart" />'
		.'<input type="submit" value="Add to Cart" />'
		.'<input type="hidden" name="product_id" value="'
		. $smarty->_tpl_vars['product']->id .'" /></form>';
}
function Products_getAddManyToCartButton($params,&$smarty) {
	return '<form method="POST" class="products-addmanytocart">'
		.'<input type="hidden" name="products_action" value="add_to_cart" />'
		.'<input name="products-howmany" value="1" class="add_multiple_widget_amount" style="width:50px" />'
		.'<input type="submit" value="Add to Cart" />'
		.'<input type="hidden" name="product_id" value="'
		. $smarty->_tpl_vars['product']->id .'" /></form>';
}
function products_image($params, &$smarty) {
	$params=array_merge(array(
		'width'=>128,
		'height'=>128,
		'nolink'=>0,
		'magnifier'=>0
	),$params);
	$product=$smarty->_tpl_vars['product'];
	$vals=$product->vals;
	if (!$vals['images_directory']) {
		return products_image_not_found($params,$smarty);
	}
	$iid=0;
	if ($vals['image_default']) {
		$iid=$vals['image_default'];
		$image=kfmImage::getInstance($iid);
		if (!$image->exists())$iid=0;
	}
	if (!$iid) {
		$directory = $vals['images_directory'];
		$dir_id=kfm_api_getDirectoryId(preg_replace('/^\//','',$directory));
		if (!$dir_id)return products_image_not_found($params,$smarty);
		$images=kfm_loadFiles($dir_id);
		if (count($images['files'])) {
			$image=$images['files'][0];
			$iid=$image['id'];
		}
	}
	if (!$iid) {
		return products_image_not_found($params,$smarty);
	}
	$img='<img src="/kfmget/'.$iid
		.'&amp;width='.$params['width'].'&amp;height='.$params['height'].'" />'
		.'<br /><span class="caption">'.htmlspecialchars($image->caption).'</span>';
	if ($params['nolink']) {
		return $img;
	}
	if ($params['magnifier']) {
		WW_addScript(
			'/ww.plugins/products/j/jquery.magnifier.0.2/js/jquery.magnifier/js/'
			.'jquery.magnifier.0.2.js'
		);
		WW_addCSS(
			'/ww.plugins/products/j/jquery.magnifier.0.2/js/jquery.magnifier/css/jquery.magnifier.css'
		);
		WW_addInlineScript('$("a[rel*=magnify]").magnify()');
		return '<a href="/kfmget/'.$iid.'" rel="magnify">'.$img.'</a>';
	}
	return $params['nolink']
		?$img
		:'<a class="products-lightbox" href="/kfmget/'.$iid.'">'.$img.'</a>';
}
function products_image_not_found($params,&$smarty) {
	$s=$params['width']<$params['height']?$params['width']:$params['height'];
	$product=$smarty->_tpl_vars['product'];
	$pt=ProductType::getInstance($product->vals['product_type_id']);
	return $pt->getMissingImage($s);
}
function products_images($params,&$smarty) {
	$params=array_merge(array(
		'width'=>48,
		'height'=>48
	),$params);
	$product=$smarty->_tpl_vars['product'];
	$vals=$product->vals;
	if (!$vals['images_directory'])return ''; // TODO: no-image here
	$directory = $vals['images_directory'];
	$dir_id=kfm_api_getDirectoryId(preg_replace('/^\//','',$directory));
	if (!$dir_id)return ''; // TODO: no-image here
	$images=kfm_loadFiles($dir_id);
	$arr=array();
	if (count($images['files'])<2) {
		return ''; // no point showing only one image
	}
	foreach($images['files'] as $image) {
		$arr[]='<img src="/kfmget/'.$image['id']
			.'&amp;width='.$params['width'].'&amp;height='.$params['height'].'"'
			.' title="'.htmlspecialchars($image['caption']).'" />';
	}
	return '<div class="product-images">'.join('',$arr).'</div>';
}
function products_link ($params, &$smarty) {
	$product= $smarty->_tpl_vars['product'];
	$id= $product->id;
	return $product->getRelativeURL();
}
function _Products_listCategories($params, &$smarty) {
	if (!isset($params['parent'])) {
		$parent=0;
	}
	$cats=dbAll('select * from products_categories where parent_id='.((int)$parent).' and enabled order by name');
	$html='<ul class="products-list-categories sc_subcatnames">';
	foreach ($cats as $cat) {
		$cat=ProductCategory::getInstance($cat['id']);
		$html.='<li><a href="'.$cat->getRelativeUrl().'">'.htmlspecialchars($cat->vals['name']).'</a></li>';
	}
	$html.='</ul>';
	return $html;
}
function _Products_listCategoryContents ($params, &$smarty) {
	if (!isset($params['category'])) {
		$products=Products::getAll();
	}
	else {
		$products=Products::getByCategoryName($params['category']);
	}
	$html='<ul class="products-list-category-contents">';
	foreach ($products->product_ids as $pid) {
		$product=Product::getInstance($pid);
		$html.='<li><a href="'.$product->getRelativeURL().'">'.htmlspecialchars($product->name).'</a></li>';
	}
	$html.='</ul>';
	return $html;
}
function Products_plusVat($params, &$smarty) {
	$product= $smarty->_tpl_vars['product'];
	if (!isset($product->vals['online-store']['_vatfree'])
		|| $product->vals['online-store']['_vatfree'] == '0'
	) {
		return '+ VAT';
	}
}
function products_reviews ($params, &$smarty) {
	WW_addScript('/ww.plugins/products/frontend/delete.js');
	WW_addScript('/ww.plugins/products/frontend/products-edit-review.js');
	$userid = (int)get_userid();
	$product = $smarty->_tpl_vars['product'];
	$productid = (int)$product->id;
	$c='';
	$numReviews
		= dbOne(
			'select count(id) 
			from products_reviews 
			where product_id='.$productid,
			'count(id)'
		);
	if ($numReviews) {
		$reviews 
			= dbAll(
				'select * 
				from products_reviews  
				where product_id ='.$productid
			);
		$query = 'select avg(rating), product_id ';
		$query.= 'from products_reviews ';
		$query.= 'where product_id='.$productid;
		$query.= ' group by product_id';
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
			$name
				= dbOne(
					'select name 
					from user_accounts 
					where id='.(int)$review['user_id'], 
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
			if (is_admin()|| $userid==$review['user_id']) {
				// { Edit Review Link
				$timeReviewMayBeEditedUntil
					= dbOne('select 
						date_add('
							.'\''.$review['cdate'].'\''
							.', interval 15 minute
						)
						as last_edit_time',
						'last_edit_time'
					);
				$reviewMayBeEdited
					= dbOne (
						'select \''.$timeReviewMayBeEditedUntil.'\'>now()
						as can_edit_review',
						'can_edit_review'
					);
				if ($reviewMayBeEdited) {
					$c.= '<a href="javascript:;"';
					$c.= 'onClick="';
					$c.= 'edit_review('.
							$review['id'].', '
							.'\''.addslashes($body).'\''
							.', '.$review['rating']
							.', \''.addslashes($review['cdate']).'\'
						);">';
					$c.= 'edit</a> ';
				}
				// }
				// { Delete Review Link
				$c.= '<a';
				$c.= ' href="javascript:;" ';
				$c.= 'onClick=
					"delete_review('.
						$review['id'].
						', '.$review['user_id'].', '
						.$productid
					.');"';
				$c.='>[x]';
				$c.= '</a><br/>';
				// }
			}
			$c.= '<br/></div>';
		}
		$c.= '</div>';
		$userHasNotReviewedThisProduct
			= !dbOne(
				'select id
				from products_reviews
				where user_id='.$userid.' and product_id='.$productid,
				'id'
			);
		if (is_logged_in() && $userHasNotReviewedThisProduct) {
			$c.= products_submit_review_form($productid, $userid);
		}
	}
	else {
		$c.= '<em>Nobody has reviewed this product yet</em>';
		$c.= '<br/>';
		if (is_logged_in()) {
			$c.= products_submit_review_form($productid, $userid);
		}
	}
	return $c;
}
function products_setup_smarty() {
	$smarty=smarty_setup(USERBASE.'/ww.cache/products/templates_c');
	$smarty->template_dir='/ww.cache/products/templates';
	$smarty->assign('PAGEDATA',$GLOBALS['PAGEDATA']);
	if (isset($_SESSION['userdata'])) {
		$smarty->assign('USERDATA',$_SESSION['userdata']);
	}
	return $smarty;
}
function products_show($PAGEDATA) {
	if (!isset($PAGEDATA->vars['products_what_to_show'])) {
		$PAGEDATA->vars['products_what_to_show']='0';
	}
	WW_addScript('/ww.plugins/products/j/jquery.lightbox-0.5.min.js');
	WW_addCSS('/ww.plugins/products/c/jquery.lightbox-0.5.css');
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
	if(isset($_REQUEST['products_per_page'])) {
		$limit=(int)$_REQUEST['products_per_page'];
	}
	$start=isset($_REQUEST['start'])?(int)$_REQUEST['start']:0;
	if ($start<0)$start=0;
	// }
	// { set order fields
	$order_by=isset($PAGEDATA->vars['products_order_by'])
		?$PAGEDATA->vars['products_order_by']
		:'';
	$order_dir=isset($PAGEDATA->vars['products_order_direction'])
		?(int)$PAGEDATA->vars['products_order_direction']#
		:0;
	// }
	// { export button
	$export='';
	if (isset($PAGEDATA->vars['products_add_export_button'])
		&& $PAGEDATA->vars['products_add_export_button']
	) {
		$export='<form id="products-export" action="/ww.plugins/products/frontend/export.php">'
			.'<input type="hidden" name="pid" value="'.$PAGEDATA->id.'" />'
			.'<input type="submit" value="Export" />'
			.'</form>';
	}
	// }
	switch($PAGEDATA->vars['products_what_to_show']) {
		case '1':
			return $c
				.products_show_by_type(
					$PAGEDATA,
					0,
					$start,
					$limit,
					$order_by,
					$order_dir,
					$search
				)
				.$export;
		case '2':
			return $c
				.products_show_by_category(
					$PAGEDATA,
					0,
					$start,
					$limit,
					$order_by,
					$order_dir,
					$search
				)
				.$export;
		case '3':
			return $c.products_show_by_id($PAGEDATA).$export;
	}
	return $c
		.products_show_all(
			$PAGEDATA,
			$start,
			$limit,
			$order_by,
			$order_dir,
			$search
		)
		.$export;
}
function products_show_by_id($PAGEDATA,$id=0) {
	if ($id==0) {
		$id=(int)$PAGEDATA->vars['products_product_to_show'];
	}
	if ($id<1)return '<em>product '.$id.' does not exist.</em>';
	$product=Product::getInstance($id);
	$typeID = $product->get('product_type_id');
	$type=ProductType::getInstance($typeID);
	if (!$type) {
		return '<em>product type '.$typeID.' does not exist.</em>';
	}
	return $type->render($product);
}
function products_show_by_category($PAGEDATA, $id=0, $start=0, $limit=0, $order_by='', $order_dir=0, $search='') {
	if ($id==0) {
		$id=(int)$PAGEDATA->vars['products_category_to_show'];
	}
	$products=Products::getByCategory($id,$search);
	return $products->render($PAGEDATA,$start,$limit,$order_by,$order_dir);
}
function products_show_by_type($PAGEDATA, $id=0, $start=0, $limit=0, $order_by='', $order_dir=0, $search='') {
	if ($id==0) {
		$id=(int)$PAGEDATA->vars['products_type_to_show'];
	}
	$products=Products::getByType($id,$search);
	return $products->render($PAGEDATA,$start,$limit,$order_by,$order_dir);
}
function products_show_all($PAGEDATA, $start=0, $limit=0, $order_by='', $order_dir=0, $search='') {
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
	return $products->render($PAGEDATA,$start,$limit,$order_by,$order_dir);
}
function Products_showRelatedProducts($params, &$smarty) {
	$product = $smarty->_tpl_vars['product'];
	$productID = $product->id;
	$type='';
	if (isset($params['type'])) {
		$tid=dbOne('select id from products_relation_types where name="'.addslashes($params['type']).'"', 'id');
		if($tid) {
			$type=' and relation_id='.$tid;
		}
	}
	$rs=dbAll('select to_id from products_relations where from_id='.$productID.$type);
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
			$iid=0;
			if ($vals['image_default']) {
				$iid=$vals['image_default'];
				$image=kfmImage::getInstance($iid);
				if (!$image->exists())$iid=0;
			}
			if (!$iid) {
				$directory = $vals['images_directory'];
				$dir_id=kfm_api_getDirectoryId(preg_replace('/^\//','',$directory));
				if ($dir_id) {
					$images=kfm_loadFiles($dir_id);
					if (count($images['files']))$iid=$images['files'][0]['id'];
				}
			}
			if (!$iid) {
				$h.=htmlspecialchars($p->name).'</a>';
				continue;
			}
			$h.='<img src="/kfmget/'.$iid.'&amp;width=150&amp;height=150" /><br />'
				.htmlspecialchars($p->name).'</a>';
		}
		return '<div class="product_list products_'.htmlspecialchars($params['type']).'">'.$h.'</div>';
	}
	return 'none yet';
}
function products_submit_review_form ($productid, $userid) {
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
					.'?&product_id='.$this->id;
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
class Products{
	static $instances=array();
	function __construct(
		$vs, $md5, $search='', $search_arr=array(), $sort_col='', $sort_dir='asc'
	) {
		$this->product_ids=cache_load('products', 'products_'.$md5);
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
			cache_save('products', 'products_'.$md5, $vs);
		}
		self::$instances[$md5]=& $this;
		return $this;
	}
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
	static function getByCategory(
		$id, $search='', $search_arr=array(), $sort_col='', $sort_dir='asc'
	) {
		if (!is_numeric($id)) {
			return false;
		}
		$md5=md5($id.'|'.$search.'|'.join(',',$search_arr));
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
			self::$instances[$md5]->subCategories=dbAll('select id,name from products_categories where parent_id='.$id.' and enabled order by name');
		}
		return self::$instances[$md5];
	}
	static function getByCategoryName($name) {
		$arr=explode('/', $name);
		if ($arr[0]=='') {
			array_shift($arr);
		}
		$cid=0;
		foreach ($arr as $name) {
			$cid=dbOne('select id from products_categories where parent_id='.$cid.' and name="'.addslashes($name).'" limit 1','id');
			if (!$cid) {
				break;
			}
		}
		if (!$cid) {
			return Products::getAll();
		}
		return Products::getByCategory($cid);
	}
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
			break;
			// }
			case 2: // { vertical table, headers on left
				$c.=Product_datatableMultiple($prods, 'vertical');
			break;
			// }
			default: // { use template
				if (count($prods)) { // display the first item's header
					$product=Product::getInstance($prods[0]);
					$type=ProductType::getInstance($product->get('product_type_id'));
					$smarty=products_setup_smarty();
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
							$c.= $type->render($product, 'singleview');
						}
						else {
							$c.=$type->render($product, 'multiview');
						}
					}
				}
				if (count($prods)) { // display the first item's header
					$smarty=products_setup_smarty();
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
				foreach ($this->subCategories as $cat) {
					$categories.='<li><a href="'.$PAGEDATA->getRelativeUrl().'?product_cid='.$cat['id'].'">'.htmlspecialchars($cat['name']).'</a></li>';
				}
				$categories.='</ul>';
			}
		}
		return $categories.$prevnext.'<div class="products">'.$c.'</div>'.$prevnext;
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
		if (!is_numeric($id)) {
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
		foreach ($this->data_fields as $f) {
			$f->n=preg_replace('/[^a-zA-Z0-9\-_]/', '_', $f->n);
			$val=$product->get($f->n);
			switch($f->t) {
				case 'checkbox': // {
					$smarty->assign($f->n, $val?'Yes':'No');
				break; // }
				case 'date': // {
					$smarty->assign($f->n, date_m2h($val));
				break; // }
				case 'selectbox': // {
					if (isset($f->u) && $f->u) {
						$h='<select name="products_values_'.$f->n.'">';
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
					$smarty->assign($f->n, $h);
				break; // }
				default: // { everything else
					if (isset($f->u) && $f->u) {
						$smarty->assign(
							$f->n,
							'<input name="products_values_'.$f->n.'" />'
						);
					}
					else {
						$smarty->assign($f->n, $val);
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
class ProductCategory{
	static $instances=array();
	public $vals;
	function __construct($id) {
		$id=(int)$id;
		$r=dbRow('select * from products_categories where id='.$id);
		if (!count($r)) {
			return false;
		}
		$this->vals=$r;
		self::$instances[$this->vals['id']] =& $this;
		return $this;
	}
	static function getInstance($id=0) {
		if (!is_numeric($id)) {
			return false;
		}
		if (!array_key_exists($id, self::$instances)) {
			new ProductCategory($id);
		}
		return self::$instances[$id];
	}
	function getRelativeUrl(){
		// { see if there are any pages that use this category
		// }
		// { or get at least any product page
		$pid=dbOne('select id from pages where type="products" limit 1', 'id');
		if ($pid) {
			$page=Page::getInstance($pid);
			return $page->getRelativeUrl().'?product_category='.$this->vals['id'];
		}
		// }
		return '/#no-url-available';
	}
}
