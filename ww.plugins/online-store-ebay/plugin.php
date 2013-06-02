<?php
/**
	* definition file for OnlineStore eBay plugin
	*
	* PHP version 5
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     None
	*/

// { define $plugin
$plugin=array(
	'name'=>function() {
		return __('Online Store eBay integration');
	},
	'description' =>function() {
		return __(
			'sell your products on EBay and still manage them here'
		);
	},
	'admin' => array(
		'menu' => array(
			'Online Store>eBay Options' =>
				'plugin.php?_plugin=online-store-ebay&amp;_page=options'
		)
	),
	'triggers' => array( // {
		'extra-products-columns' => 'OnlineStoreEbay_extraColumnsList',
		'products-show-edit-form-tabs' => 'OnlineStoreEbay_showProductEditFormTabs'
	), // }
	'frontend' => array(
	),
	'version'=>1
);

// }
function OnlineStoreEbay_extraColumnsList() {
	$GLOBALS['product_columns'][]=array(
		'name'=>'active_in_ebay',
		'type'=>'field',
		'field_name'=>'ebay_currently_active',
		'text'=>__('In eBay'),
		'edit_type'=>'boolean'
	);
}
function OnlineStoreEbay_showProductEditFormTabs(
	$pdata=null, $product, $product_type
) {
	echo '<h2>eBay</h2><div><table>'
		.'<tr><th>Currently available in EBay</th><td>';
	echo $product['ebay_currently_active']=='1'
		?'Yes. <a href="http://www.ebay.ie/itm/'.$product['ebay_id'].'"'
		.' target="_blank">View it</a>'
		:'No';
	echo '</td></tr>'
		.'<tr><th>Bids Start At</th><td><input name="productsExtra[ebay_bids_start_at]" value="'.(float)$product['ebay_bids_start_at'].'"/></td></tr>'
		.'<tr><th>Buy Now Price</th><td><input name="productsExtra[ebay_buy_now_price]" value="'.(float)$product['ebay_buy_now_price'].'"/></td></tr>'
		.'</table>'
		.'<div id="ebay-wrapper"></div>';
	WW_addScript('/ww.plugins/online-store-ebay/admin/product-edit.js');
	WW_addScript('/j/jquery.optionTree.js');
	echo '</div>';
}
