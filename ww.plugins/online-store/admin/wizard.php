<?php
/**
	* wizard for creating online store
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Conor Mac Aoidh <conormacaoidh@gmail.com>
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

$p=Pages::getInstancesByType('online-store');
if (@count($p->pages)) {
	echo '<em>You already have an online-store checkout page created.</em>';
	echo '<p>Maybe you want to <a href="./plugin.php?_plugin=products&_page='
		.'products-edit">add a new product</a> instead?</p>';
}

WW_addScript('/ww.plugins/online-store/admin/wizard.js');
echo '<h1>Online Store Wizard</h1>
<div id="preview-dialog"></div>
<ul class="left-menu" id="register-progress" style="list-style-type:none">
  <li>Store</li>
	<li>Payment Details</li>
	<li>Company Details</li>
	<li>Products</li>
	<li>Finish</li>
</ul>
</div>
<p id="error"></p>
<div class="has-left-menu" style="position:static">
	<div id="online-store-wizard">
		<div id="slider">
		</div>
	</div>
</div>
';
