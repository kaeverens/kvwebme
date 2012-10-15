<?php
/**
	* admin area page-type form for Online-Store
	*
	* PHP version 5
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     None
	*/

global $online_store_currencies,$DBVARS;
if (isset($_REQUEST['online_store_currency'])
	&& isset($online_store_currencies[$_REQUEST['online_store_currency']])
) {
	$DBVARS['online_store_currency']=$_REQUEST['online_store_currency'];
	Core_configRewrite();
}
$csym=$online_store_currencies[$DBVARS['online_store_currency']][0];
// { are there any authorised payments?
$authrs=dbAll(
	'select id,date_created,total,status from online_store_orders where authorised'
);
$has_authrs=count($authrs)?1:0;
// }
$c='<div class="tabs mini-tabs">';
// { list of tabs
$c.= '<ul>'
	.'<li><a href="#online-store-form">'.__('Form').'</a></li>'
	.'<li><a href="#online-store-payment">'.__('Payment Details').'</a></li>'
	.'<li><a href="#online-store-delivery">'.__('Postage and Packaging').'</a>'
	.'</li>'
	.'<li><a href="#online-stores-fields">'.__('Fields').'</a></li>'
	.'<li><a href="#online-store-countries">'.__('Countries').'</a></li>'
	.'</ul>';
// }
// { postage and packaging
if (!isset($vars['online_stores_postage'])) {
	$vars['online_stores_postage']='[]';
}
$c.='<div id="online-store-delivery"><br/>'
	.'<div id="postage_wrapper"></div>'
	.'<input type="hidden" name="page_vars[online_stores_postage]" id="postage" value="'
	.htmlspecialchars($vars['online_stores_postage']).'" />';
$c.='</div>';
// }
// { payment details
$c.='<div id="online-store-payment">';
$c.='<table style="width:100%">';
// { admin email address
$c.='<tr><th style="width:20%">'.__('Admin email address').'</th><td>'
	.'<input type="email" name="page_vars[online_stores_admin_email]"';
if (isset($vars['online_stores_admin_email'])) {
	$c.=' value="'.htmlspecialchars($vars['online_stores_admin_email']).'"';
}
$c.=' /></td>';
// }
// { users must log in
if (!isset($vars['online_stores_requires_login'])) {
	$vars['online_stores_requires_login']=0;
}
$c.='<th width="20%">'.__('Users must log in').'</th><td><input type="checkbox"'
	.' name="page_vars[online_stores_requires_login]"'
	.($vars['online_stores_requires_login']?' checked="checked"':'')
	.' /></td></tr>';
// }
// { currency
$c.='<tr><th>'.__('Currency', 'core').'</th>'
	.'<td><select name="online_store_currency">';
foreach ($online_store_currencies as $key=>$val) {
	$c.= '<option value="'.$key.'"';
	if ($key==$DBVARS['online_store_currency']) {
		$c.= ' selected="selected"';
	}
	$c.= '>'.$val[0].': '.htmlspecialchars($val[1]).'</option>';
}
$c.= '</select></td>';
// }
// { register new users without verification
if (!isset($vars['online_stores_user_reg_no_verify'])) {
	$vars['online_stores_user_reg_no_verify']=0;
}
$c.='<th width="20%">'.__('Register users without verification')
	.'</th><td><input type="checkbox"'
	.' name="page_vars[online_stores_user_reg_no_verify]"'
	.($vars['online_stores_user_reg_no_verify']?' checked="checked"':'')
	.' /></td></tr>';
// }
// { VAT
$vat=isset($vars['online_stores_vat_percent'])?
	$vars['online_stores_vat_percent']:
	'';
if ($vat=='') {
	$vat=0;
}
$c.='<tr><th>'.__('VAT').'</th><td>'
	.'<input name="page_vars[online_stores_vat_percent]" value="'
	.((float)$vat).'" /></td></tr>';
// }
// { usergroup to add users to
if (!isset($vars['online_stores_customers_usergroup'])) {
	$vars['online_stores_customers_usergroup']='customers';
}
$c.='<tr><th>'.__('Add logged-in customers to this user group:').'</th>'
	.'<td><input id="onlinestore-customersUsergroup"'
	.' name="page_vars[online_stores_customers_usergroup]"'
	.' value="'.htmlspecialchars($vars['online_stores_customers_usergroup']).'"'
	.' /></td><td><a href="javascript:onlinestoreCustomers();">'
	.__('List Customers').'</a></td></tr>';
// }
// { payment types
$c.='<tr><th>'.__('Payment Types').'</th><td colspan="3"><div class="tabs">';
$c.='<ul>';
$c.='<li><a href="#online-store-payments-paypal">PayPal</a></li>';
$c.='<li><a href="#online-store-payments-bank-transfer">'.__('Bank Transfer')
	.'</a></li>';
$c.='<li><a href="#online-store-payments-realex">Realex</a></li>';
$c.='<li><a href="#online-store-payments-quickpay">QuickPay</a></li>';
$c.='</ul>';
// { paypal
$c.='<div id="online-store-payments-paypal">';
$c.='<table>';
$c.='<tr><th>'.__('Email Address').'</th>';
$c.='<td><input type="email" name="page_vars[online_stores_paypal_address]"';
if (isset($vars['online_stores_paypal_address'])) {
	$c.=' value="'.htmlspecialchars($vars['online_stores_paypal_address']).'"';
}
$c.=' /></td></tr></table></div>';
// }
// { bank transfer
$c.='<div id="online-store-payments-bank-transfer">';
$c.='<table>';
// { bank name
$c.='<tr><th>'.__('Bank Name').'</th>';
$c.='<td><input name="page_vars[online_stores_bank_transfer_bank_name]"';
if (isset($vars['online_stores_bank_transfer_bank_name'])) {
	$c.=' value="'.htmlspecialchars($vars['online_stores_bank_transfer_bank_name']).'"';
}
$c.=' /></td></tr>';
// }
// { sort code
$c.='<tr><th>'.__('Sort Code').'</th>';
$c.='<td><input name="page_vars[online_stores_bank_transfer_sort_code]"';
if (isset($vars['online_stores_bank_transfer_sort_code'])) {
	$c.=' value="'.htmlspecialchars($vars['online_stores_bank_transfer_sort_code']).'"';
}
$c.=' /></td></tr>';
// }
// { account name
$c.='<tr><th>'.__('Account Name').'</th>';
$c.='<td><input name="page_vars[online_stores_bank_transfer_account_name]"';
if (isset($vars['online_stores_bank_transfer_account_name'])) {
	$c.=' value="'
		.htmlspecialchars($vars['online_stores_bank_transfer_account_name']).'"';
}
$c.=' /></td></tr>';
// }
// { account number
$c.='<tr><th>'.__('Account Number').'</th>';
$c.='<td><input name="page_vars[online_stores_bank_transfer_account_number]"';
if (isset($vars['online_stores_bank_transfer_account_number'])) {
	$c.=' value="'
		.htmlspecialchars($vars['online_stores_bank_transfer_account_number']).'"';
}
$c.=' /></td></tr>';
// }
// { message
// add ckeditor
$script='var bbcode_editor=CKEDITOR.replace("bank_transfer_message", { extr'
	.'aPlugins : "bbcode", removePlugins : "bidi,button,dialogadvtab,div,file'
	.'browser,flash,format,forms,horizontalrule,iframe,indent,justify,liststy'
	.'le,pagebreak,showborders,stylescombo,table,tabletools,templates", toolb'
	.'ar : [ ["Source", "-", "Save","NewPage","-","Undo","Redo"], ["Find","Re'
	.'place","-","SelectAll","RemoveFormat"], ["Link", "Unlink", "Image"], "/'
	.'", ["FontSize", "Bold", "Italic","Underline"], ["NumberedList","Bullete'
	.'dList","-","Blockquote"], ["TextColor", "-", "Smiley","SpecialChar", "-'
	.'", "Maximize"] ], smiley_images : [ "regular_smile.gif","sad_smile.gif"'
	.',"wink_smile.gif","teeth_smile.gif","tounge_smile.gif", "embaressed_smi'
	.'le.gif","omg_smile.gif","whatchutalkingabout_smile.gif","angel_smile.gi'
	.'f","shades_smile.gif", "cry_smile.gif","kiss.gif" ], smiley_description'
	.'s : [ "smiley", "sad", "wink", "laugh", "cheeky", "blush", "surprise", '
	.'"indecision", "angel", "cool", "crying", "kiss" ] });';
WW_addScript('/j/ckeditor-3.6.2/ckeditor.js');
WW_addScript('/j/ckeditor-3.6.2/adapters/jquery.js');
WW_addInlineScript($script);
$c.='<tr><th>'.__('Message to buyer').'</th>';
if (!@$vars['online_stores_bank_transfer_message']) {
	$vars['online_stores_bank_transfer_message']='<p>'
		.__(
			'Thank you for your purchase. Please send {{$total}} to the following'
			.' bank account, quoting the invoice number {{$invoice_number}}:'
		)
		.'</p>'."\n<table>\n<tr><th>".__('Bank')."</th>"
		.'<td>{{$bank_name}}</td></tr>'."\n<tr><th>".__('Account Name')."</th><td>"
		.'{{$account_name}}</td></tr>'."\n<tr><th>".__('Sort Code')."</th><td>"
		.'{{$sort_code}}</td></tr>'."\n<tr><th>".__('Account Number')."</th><td>"
		.'{{$account_number}}</td></tr>'."\n</table>";
}
$c.='<td><textarea name="page_vars[online_stores_bank_transfer_message]" id'
	.'="bank_transfer_message">'
	.htmlspecialchars($vars['online_stores_bank_transfer_message'])
	.'</textarea></td></tr>';
// }
$c.='</table></div>';
// }
// { realex
$c.='<div id="online-store-payments-realex">'
	.'<table>';
// { Merchant ID
$c.='<tr><th>'.__('Merchant ID').'</th><td>'
	.'<input name="page_vars[online_stores_realex_merchantid]"';
if (isset($vars['online_stores_realex_merchantid'])) {
	$c.=' value="'.htmlspecialchars($vars['online_stores_realex_merchantid'])
		.'"';
}
$c.=' /></td></tr>';
// }
// { Shared Secret
$c.='<tr><th>'.__('Shared Secret').'</th><td>'
	.'<input name="page_vars[online_stores_realex_sharedsecret]"';
if (isset($vars['online_stores_realex_sharedsecret'])) {
	$c.=' value="'.htmlspecialchars($vars['online_stores_realex_sharedsecret'])
		.'"';
}
$c.=' /></td></tr>';
// }
// { redirect page
$c.='<tr><th>'.__('Redirect after payment').'</th><td>';
$c.='<select id="online_store_redirect_to"'
	.' name="page_vars[online_store_redirect_to]">';
if (isset($vars['online_store_redirect_to'])
	&& $vars['online_store_redirect_to']
) {
	$parent=Page::getInstance($vars['online_store_redirect_to']);
	$c.='<option value="'.$parent->id.'">'
		.htmlspecialchars(__FromJson($parent->name)).'</option>';
}
else{
	$vars['online_store_redirect_to']=0;
	$c.='<option value="0"> -- '.__('None').' -- </option>';
}
$c.='</select></td></tr>';
// }
// { test mode
$c.='<tr><th>'.__('Mode').'</th><td>'
	.'<select name="page_vars[online_stores_realex_testmode]">'
	.'<option value="test">'.__('Test Mode').'</option>'
	.'<option value="live"';
if (isset($vars['online_stores_realex_testmode'])
	&& $vars['online_stores_realex_testmode']=='live'
) {
	$c.=' selected="selected"';
}
$c.='>'.__('Live').'</option></select>'
	.__(
		'In test mode, you can use the realex payment method by adding'
		.' "?testmode=1" to the URL.'
	)
	.'</td></tr>';
// }
// { note
$url='http://'.$_SERVER['HTTP_HOST']
	.'/ww.plugins/online-store/verify/realex.php';
$c.='<tr><td colspan="2">'
	.__(
		'Note that some manual configuration is necessary. '
		.'You will need to provide RealEx with a template (see their Real Auth '
		.'Developers Guide for an example), and with the following Response Script'
		.'URL: <br />%1', array($url), 'core'
	)
	.'</td></tr>';
// }
$c.=' </table></div>';
// }
// { quickpay
$c.='<div id="online-store-payments-quickpay">'
	.'<table>';
// { Merchant ID
$c.='<tr><th>'.__('Merchant ID').'</th><td>'
	.'<input name="page_vars[online_stores_quickpay_merchantid]"';
if (isset($vars['online_stores_quickpay_merchantid'])) {
	$c.=' value="'.htmlspecialchars($vars['online_stores_quickpay_merchantid'])
		.'"';
}
$c.=' /></td></tr>';
// }
// { Shared Secret
$c.='<tr><th>'.__('MD5 Secret').'</th><td>'
	.'<input name="page_vars[online_stores_quickpay_secret]"';
if (isset($vars['online_stores_quickpay_secret'])) {
	$c.=' value="'.htmlspecialchars($vars['online_stores_quickpay_secret'])
		.'"';
}
$c.=' /></td></tr>';
// }
// { redirect page (success)
$c.='<tr><th>'.__('Redirect after successful payment').'</th><td>';
$c.='<select id="online_store_quickpay_redirect_to" name="page_vars[online_'
	.'store_quickpay_redirect_to]">';
if (isset($vars['online_store_quickpay_redirect_to'])
	&& $vars['online_store_quickpay_redirect_to']
) {
	$parent=Page::getInstance($vars['online_store_quickpay_redirect_to']);
	$c.='<option value="'.$parent->id.'">'
		.htmlspecialchars(__FromJson($parent->name)).'</option>';
}
else{
	$vars['online_store_quickpay_redirect_to']=0;
	$c.='<option value="0"> -- '.__('None').' -- </option>';
}
$c.='</select></td></tr>';
// }
// { redirect page (failed)
$c.='<tr><th>'.__('Redirect after cancelled/failed payment').'</th><td>';
$c.='<select id="online_store_quickpay_redirect_failed" name="page_vars[onl'
	.'ine_store_quickpay_redirect_failed]">';
if (isset($vars['online_store_quickpay_redirect_failed'])
	&& $vars['online_store_quickpay_redirect_failed']
) {
	$parent=Page::getInstance($vars['online_store_quickpay_redirect_failed']);
	$c.='<option value="'.$parent->id.'">'
		.htmlspecialchars(__FromJson($parent->name)).'</option>';
}
else{
	$vars['online_store_quickpay_redirect_failed']=0;
	$c.='<option value="0"> -- '.__('None').' -- </option>';
}
$c.='</select></td></tr>';
// }
// { autocapture
$c.='<tr><th>'.__('Autocapture').'</th><td>'
	.'<select name="page_vars[online_stores_quickpay_autocapture]">'
	.'<option value="0">'.__('No').'</option>'
	.'<option value="1"';
if (@$vars['online_stores_quickpay_autocapture']=='1') {
	$c.=' selected="selected"';
}
$c.='>'.__('Yes').'</option></select></td></tr>';
// }
// { test mode
$c.='<tr><th>'.__('Mode').'</th><td>'
	.'<select name="page_vars[online_stores_quickpay_testmode]">'
	.'<option value="test">'.__('Test Mode').'</option>'
	.'<option value="live"';
if (isset($vars['online_stores_quickpay_testmode'])
	&& $vars['online_stores_quickpay_testmode']=='live'
) {
	$c.=' selected="selected"';
}
$c.='>'.__('Live').'</option></select>'
	.__(
		'In test mode, you can use the quickpay payment method by adding'
		.' "?testmode=2" to the URL.'
	)
	.'</td></tr>';
// }
$c.=' </table></div>';
// }
$c.='</div></td></tr>';
// }
$c.='</table></div>';
// }
// { form
$c.='<div id="online-store-form">';
$c.='<p>'.__('This is the form that will be presented as the checkout.').'</p>';
// { checkout view type
$c.='<strong>'.__('Checkout Type').'</strong>'
	.'<select name="page_vars[onlinestore_viewtype]">';
$types=array(
	'All-in-one view',
	'Basket, then All-in-one',
	'5-step',
	'Basket, then 5-step'
);
foreach ($types as $k=>$v) {
	$c.='<option value="'.$k.'"';
	if ($k==@$vars['onlinestore_viewtype']) {
		$c.=' selected="selected"';
	}
	$c.='>'.__($v).'</option>';
}
$c.='</select>';
// }
// { checkout form
$c.='<div class="online-store-checkout-form">';
if ($page['body']==''
	|| $page['body']=='<h1>'.htmlspecialchars($page['name']).'</h1><p>&nbsp;</p>'
) {
	$page['body']
		=file_get_contents(dirname(__FILE__).'/body_template_sample.html');
}
$c.=ckeditor('body', $page['body'])
	.'<a href="#" class="docs" page="/ww.plugins/online-store/docs/form.html">'
	.__('Checkout Template Codes').'</a></div>';
// }
$c.='</div>';
// }
// { form fields
if (!isset($vars['online_stores_fields'])
	|| !$vars['online_stores_fields']
) {
	$vars['online_stores_fields']='{}';
}
$c.='<div id="online-stores-fields">'
	.'<script>var os_fields='.$vars['online_stores_fields'].';</script>'
	.'<input type="hidden" name="page_vars[online_stores_fields]" value="'
	.htmlspecialchars($vars['online_stores_fields']).'" />'
	.'</div>';
// }
// { countries
// { list of countries
$continents=array(
	'Africa'=>array(
		'Algeria', 'Angola', 'Benin', 'Botswana', 'Burkina', 'Burundi',
		'Cameroon', 'Cape Verde', 'Central African Republic', 'Chad', 'Comoros',
		'Congo', 'Congo, Democratic Republic of', 'Djibouti', 'Egypt',
		'Equatorial Guinea', 'Eritrea', 'Ethiopia', 'Gabon', 'Gambia', 'Ghana',
		'Guinea', 'Guinea-Bissau', 'Ivory Coast', 'Kenya', 'Lesotho', 'Liberia',
		'Libya', 'Madagascar', 'Malawi', 'Mali', 'Mauritania', 'Mauritius',
		'Morocco', 'Mozambique', 'Namibia', 'Niger', 'Nigeria', 'Rwanda',
		'Sao Tome and Principe', 'Senegal', 'Seychelles', 'Sierra Leone',
		'Somalia', 'South Africa', 'South Sudan', 'Sudan', 'Swaziland',
		'Tanzania', 'Togo', 'Tunisia', 'Uganda', 'Zambia', 'Zimbabwe'
	),
	'Asia'=>array(
		'Afghanistan', 'Bahrain', 'Bangladesh', 'Bhutan', 'Brunei',
		'Burma (Myanmar)', 'Cambodia', 'China', 'East Timor', 'India',
		'Indonesia', 'Iran', 'Iraq', 'Israel', 'Japan', 'Jordan', 'Kazakhstan',
		'Korea, North', 'Korea, South', 'Kuwait', 'Kyrgyzstan', 'Laos',
		'Lebanon', 'Malaysia', 'Maldives', 'Mongolia', 'Nepal', 'Oman',
		'Pakistan', 'Philippines', 'Qatar', 'Russian Federation',
		'Saudi Arabia', 'Singapore', 'Sri Lanka', 'Syria', 'Tajikistan',
		'Thailand', 'Turkey', 'Turkmenistan', 'United Arab Emirates',
		'Uzbekistan', 'Vietnam', 'Yemen'
	),
	'Europe'=>array(
		'Albania', 'Andorra', 'Armenia', 'Austria', 'Azerbaijan', 'Belarus',
		'Belgium', 'Bosnia and Herzegovina', 'Bulgaria', 'Croatia', 'Cyprus',
		'Czech Republic', 'Denmark', 'Estonia', 'Finland', 'France', 'Georgia',
		'Germany', 'Greece', 'Hungary', 'Iceland', 'Ireland', 'Italy', 'Latvia',
		'Liechtenstein', 'Lithuania', 'Luxembourg', 'Macedonia', 'Malta',
		'Moldova', 'Monaco', 'Montenegro', 'Netherlands', 'Norway', 'Poland',
		'Portugal', 'Romania', 'San Marino', 'Serbia', 'Slovakia', 'Slovenia',
		'Spain', 'Sweden', 'Switzerland', 'Ukraine', 'United Kingdom',
		'Vatican City'
	),
	'North America'=>array(
		'Antigua and Barbuda', 'Bahamas', 'Barbados', 'Belize', 'Canada',
		'Costa Rica', 'Cuba', 'Dominica', 'Dominican Republic', 'El Salvador',
		'Grenada', 'Guatemala', 'Haiti', 'Honduras', 'Jamaica', 'Mexico',
		'Nicaragua', 'Panama', 'Saint Kitts and Nevis', 'Saint Lucia',
		'Saint Vincent and the Grenadines', 'Trinidad and Tobago', 'United States'
	),
	'Oceania'=>array(
		'Australia', 'Fiji', 'Kiribati', 'Marshall Islands', 'Micronesia',
		'Nauru', 'New Zealand', 'Palau', 'Papua New Guinea', 'Samoa',
		'Solomon Islands', 'Tonga', 'Tuvalu', 'Vanuatu'
	),
	'South America'=>array(
		'Argentina', 'Bolivia', 'Brazil', 'Chile', 'Colombia', 'Ecuador',
		'Guyana', 'Paraguay', 'Peru', 'Suriname', 'Uruguay', 'Venezuela'
	)
);
// }
$c.='<div id="online-store-countries"><div class="tabs"><ul>';
$cnum=0;
foreach ($continents as $continent=>$countries) {
	$c.='<li><a href="#online-store-countries-'.$cnum.'">'
		.htmlspecialchars($continent).'</a></li>';
	++$cnum;
}
$c.='</ul>';
$cnum=0;
$keys=array();
if (@$vars['online-store-countries']) {
	$jsonarr=json_decode($vars['online-store-countries']);
	foreach ($jsonarr as $key=>$var) {
		$keys[]=$key;
	}
}
foreach ($continents as $continent=>$countries) {
	$num_countries=count($countries);
	$c.='<div id="online-store-countries-'.$cnum.'">'
		.'<a href="#" class="all">'.__('[select all]').'</a>'
		.' <a href="#" class="none">'.__('[select none]').'</a>'
		.'<table style="width:100%">';
	++$cnum;
	$row=0;
	do {
		$c.='<tr>';
		for ($j=0;$j<3;++$j) {
			$k=$row*3+$j;
			if (isset($countries[$k])) {
				$country=$countries[$k];
				$checked=in_array($country, $keys)?' checked="checked"':'';
				$c.='<td><input name="page_vars[online-store-countries]['
					.$country.']" type="checkbox"'.$checked.'/> '.$country.'</td>';
			}
			else {
				$c.='<td>&nbsp;</td>';
			}
		}
		$c.='</tr>';
		++$row;
	} while ($row*3<=$num_countries);
	$c.='</table></div>';
}
$c.='</div></div>';
// }
$c.='</div>';
WW_addScript('online-store/admin/index.js');
WW_addScript('/j/jquery.inlinemultiselect.js');

if (!file_exists(USERBASE.'/ww.cache/online-store')) {
	mkdir(USERBASE.'/ww.cache/online-store');
}
if (file_exists(USERBASE.'/ww.cache/online-store/'.$page['id'])) {
	unlink(USERBASE.'/ww.cache/online-store/'.$page['id']);
}
file_put_contents(
	USERBASE.'/ww.cache/online-store/'.$page['id'],
	dbOne(
		'select val from online_store_vars where name="email_invoice"', 'val'
	)
);
$c.='<style>@import "/ww.plugins/online-store/admin/styles.css";</style>';
