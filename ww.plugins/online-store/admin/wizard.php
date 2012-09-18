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
	echo '<em>'.__('You already have an online-store checkout page created').'</em>';
	echo '<p>'.__('Maybe you want to').' <a href="./plugin.php?_plugin=products&_page='
		.'products-edit">'.__('add a new product').'</a> '.__('instead?').'</p>';
}

WW_addScript('online-store/admin/wizard.js');
echo '<h1>'.__('Online Store Wizard').'</h1>
<div id="preview-dialog"></div>
<ul class="sub-nav" id="register-progress" style="list-style-type:none">
  <li>'.__('Store').'</li>
	<li>'.__('Payment Details').'</li>
	<li>'.__('Company Details').'</li>
	<li>'.__('Products').'</li>
	<li>'.__('Finish').'</li>
</ul>
</div>
<p id="error"></p>
<div class="pages_iframe" style="position:static">
	<div id="online-store-wizard">
		<div id="slider">
		</div>
	</div>
</div>
';
