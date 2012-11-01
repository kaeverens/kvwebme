<?php
/**
	* widget for displaying a list of products or categories
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

// { functions
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
		.'where parent_id='.$pid.' and enabled order by sortNum'
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

$html='';
$widget_type=isset($vars->widget_type) && $vars->widget_type
	?$vars->widget_type
	:'List Categories';
$diameter=isset($vars->diameter) && $vars->diameter?$vars->diameter:280;
$parent_cat=isset($vars->parent_cat)?((int)$vars->parent_cat):0;
$cats=dbAll(
	'select id,name,associated_colour as col from products_categories '
	.'where parent_id='.$parent_cat.' and enabled order by sortNum'
);

switch ($widget_type) {
	case 'Pie Chart': // { Pie Chart
		$id='products_categories_'.md5(rand());
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
				.'"><img class="product-widget-img" src="/a/w=200/h=auto/'
				.'/f=getImg/'.$iid.'"/></a>'
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
	case 'Tree View': // { Tree View
		$html='<div class="product-categories-tree"><ul>';
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
		foreach ($cats as $c) {
			$name=$c['name'];
			$html.='<li class="products-cat-'
				.preg_replace('/[^a-zA-Z0-9\-_]/', '', $name).'">'
				.'<a data-cid="'.$c['id'].'"'
				.' href="/_r?type=products&product_cid='.$c['id'].'">'
				.$name.'</a>';
			if (isset($vars->show_products) && $vars->show_products=='1') {
				$sql='select id,name from products,products_categories_products'
					.' where product_id=products.id and category_id='.$c['id'];
				$ps=dbAll($sql);
				$html.='<ul class="products-products">';
				foreach ($ps as $p) {
					$product=Product::getInstance($p['id']);
					$html.='<li><a data-pid="'.$p['id'].'" href="'
						.$product->getRelativeUrl().'">'
						.htmlspecialchars(__FromJson($p['name'])).'</a></li>';
				}
				$html.='</ul>';
			}
			$html.='</li>';
		}
		$html.='</ul>';
		if (@$_SESSION['userdata']['id']) {
			WW_addScript('products/j/watchlists.js');
		}
	break; // }
}
