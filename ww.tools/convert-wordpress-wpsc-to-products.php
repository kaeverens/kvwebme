<?php
if ($_SERVER['REMOTE_ADDR']!='127.0.0.1') {
	die(__('You must run this script from your localhost (to stop hackers)'));
}

if (isset($_POST['action']) && $_POST['action']=='convert') {
	$mysqli = new mysqli(
		$_POST['hostname'],
		$_POST['username'],
		$_POST['password'],
		$_POST['database']
	);
	if ($mysqli->connect_error) {
		die(
			__('Connect Error').' (' . $mysqli->connect_errno . ') '
			. $mysqli->connect_error
		);
	}
	// { products
	$query=$mysqli->real_query('select * from wp_wpsc_product_list');
	if (!($result=$mysqli->use_result())) {
		die(__('error retrieving products list'));
	}
	$products=array();
	while ($row=$result->fetch_array()) {
		$products[]=array(
			'id'             => $row['id'],
			'name'           => $row['name'],
			'product_type_id'=> 1,
			'image_default'  => 0,
			'enabled'        => $row['active'],
			'date_created'   => $row['date_added'],
			'data_fields'    => json_encode(
				array(
					array('n'=>'description', 'v'=>$row['description']),
					array(
						'n'=>'additional_description',
						'v'=>$row['additional_description']
					)
				)
			),
			'images_directory'   =>'/products/product-images/'.$row['id'],
			'online_store_fields'=>json_encode(
				array(
					'_price'     =>$row['price'],
					'_weight(kg)'=>$row['weight'],
					'_vatfree'   =>$row['notax']
				)
			)
		);
	}
	// }
	// { images
	$images=array();
	$query2=$mysqli->real_query(
		'select * from wp_wpsc_product_images order by product_id,image_order'
	);
	if (!($result=$mysqli->use_result())) {
		die(__('error retrieving products list'));
	}
	while ($row=$result->fetch_array()) {
		if ($row['image_order']=='') {
			$row['image_order']='_';
		}
		$images[]=array(
			$row['product_id'].'/'.$row['image_order'].$row['image'],
			$_POST['url'].'wp-content/uploads/wpsc/product_images/'.$row['image']
		);
	}
	// }
	// { products_categories
	$products_categories=array();
	$query2=$mysqli->real_query('select * from wp_wpsc_product_categories');
	if (!($result=$mysqli->use_result())) {
		die(__('error retrieving product_categories list'));
	}
	while ($row=$result->fetch_array()) {
		$products_categories[]=array(
			'id'=>$row['id'],
			'name'=>$row['name'],
			'parent_id'=>$row['category_parent'],
			'enabled'=>$row['active']
		);
	}
	// }
	// { products_categories_products
	$products_categories_products=array();
	$query2=$mysqli->real_query('select * from wp_wpsc_item_category_assoc');
	if (!($result=$mysqli->use_result())) {
		die(__('error retrieving wp_wpsc_item_category_assoc list'));
	}
	while ($row=$result->fetch_array()) {
		$products_categories_products[]=array(
			'product_id'=>$row['product_id'],
			'category_id'=>$row['category_id']
		);
	}
	// }
	header('Content-type: text/plain');
	echo json_encode(
		array(
			'products'=>$products,
			'products_images'=>$images,
			'products_categories'=>$products_categories,
			'products_categories_products'=>$products_categories_products
		)
	);
	Core_quit();
}

echo '<form method="post"><table>'
	.'<tr><th>'.__('Hostname').'</th><td><input name="hostname" /></td></tr>'
	.'<tr><th>'.__('Username').'</th><td><input name="username" /></td></tr>'
	.'<tr><th>'.__('Password').'</th><td><input name="password" /></td></tr>'
	.'<tr><th>'.__('Database').'</th><td><input name="database" /></td></tr>'
	.'<tr><th>'.__('Original website').'</th>'
	.'<td><input name="url" placeholder="http://www.whatever.com/" /></td></tr>'
	.'<tr><th colspan="2"><input type="hidden" name="action" value="convert"/>'
	.'<input type="submit" value="'.__('Convert').'" /></th></tr></table></form>';

/*
tables which are not converted

wp_wpsc_also_bought
wp_wpsc_cart_contents
wp_wpsc_cart_item_variations
wp_wpsc_categorisation_groups
wp_wpsc_category_tm
wp_wpsc_checkout_forms
wp_wpsc_claimed_stock
wp_wpsc_coupon_codes
wp_wpsc_currency_list
wp_wpsc_download_status
wp_wpsc_logged_subscriptions
wp_wpsc_meta
wp_wpsc_product_files
wp_wpsc_product_order
wp_wpsc_product_rating
wp_wpsc_product_variations
wp_wpsc_productmeta
wp_wpsc_purchase_logs
wp_wpsc_purchase_statuses
wp_wpsc_region_tax
wp_wpsc_submited_form_data
wp_wpsc_variation_assoc
wp_wpsc_variation_combinations
wp_wpsc_variation_properties
wp_wpsc_variation_values
wp_wpsc_variation_values_assoc
*/
