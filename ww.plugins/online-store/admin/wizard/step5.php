<?php
/**
	* finish - this is where everything is actually processed and saved
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

require_once $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
require_once SCRIPTBASE.'ww.admin/admin_libs.php';
require_once SCRIPTBASE.'ww.plugins/online-store/admin/wizard/'
	.'product-types.php';

if (isset($_POST['wizard-products-type'])) { // save post data
	$product_type=$_POST['wizard-products-type'];
}

// { install required plugins
$plugins=$DBVARS['plugins'];
if (!in_array('products', $plugins)) { // install products plugin
	$plugins[]='products';
	$DBVARS['plugins']=$plugins;
	$DBVARS['products|version']=0;
	Core_configRewrite();
	// load products upgrade.php
	$version=0;
	require SCRIPTBASE.'ww.plugins/products/upgrade.php';
}
if (!in_array('panels', $plugins)) { // install panels plugin
	$plugins[]='panels';
	$DBVARS['plugins']=$plugins;
	$DBVARS['panels|version']=0;
	Core_configRewrite();
	// load panels upgrade.php
	$version=0;
	require SCRIPTBASE.'ww.plugins/panels/upgrade.php';
}
if ($_SESSION['wizard']['payment']['login']=='yes'
	&& !in_array('privacy', $plugins)
) { // install privacy plugin
	$plugins[]='privacy';
	$DBVARS['plugins']=$plugins;
	$DBVARS['privacy|version']=0;
	Core_configRewrite();
}
// }
// { add product type
if (in_array($product_type, $types)) {
	$type=${$product_type};
}
else {
	$product_type='default';
	$type=$default;
}
// { generate datafields
$fields=array();
foreach ($type['fields'] as $name=>$input) {
	$tmp=array();
	$tmp['ti']=$name;
	$tmp['n']=str_replace(' ', '', strtolower($name));
	if (is_array($input)) {
		$tmp['t']='selectbox';
		$tmp['e']='';
		foreach ($input as $option) {
			$tmp['e'].=$option."\n";
		}
	}
	else {
		$tmp['t']=$input;
	}
	$tmp['s']=0;
	$tmp['r']=0;
	$tmp['u']=0;
	array_push($fields, $tmp);
}
$fields=json_encode($fields);
// }
dbQuery(
	'insert into products_types set'
	.' name="'.$product_type.'",'
	.' multiview_template="'.addslashes($type['multi']).'",'
	.' singleview_template="'.addslashes($type['single']).'",'
	.' data_fields="'.addslashes($fields).'",'
	.' is_for_sale=1'
);
$product_type_id=dbLastInsertId();
// }
// { add products page to database
	$name=$_SESSION['wizard']['name'];
	dbQuery(
		'insert into pages set'
		.' name="'.addslashes($name).'",'
		.' type="products",'
		.' cdate="date()",'
		.' edate="date()",'
		.' special=0,'
		.' alias="'.addslashes($name).'"'
	);
	$products_id=dbLastInsertId();
// }
// { add products info to page vars
dbQuery(
	'insert into page_vars (page_id,name,value) values'
	.'('.$products_id.',"products_what_to_show","1"),'
	.'('.$products_id.',"products_type_to_show","'.$product_type_id.'")'
);
// }
// { add online-store page to database 
$body=file_get_contents('../body_template_sample.html');
dbQuery(
	'insert into pages set'
	.' name="Checkout",'
	.' body="'.addslashes($body).'",'
	.' original_body="'.addslashes($body).'",'
	.' parent="'.$products_id.'",'
	.' cdate="date()",'
	.' edate="date()",'
	.' special=2,'
	.' type="online-store",'
	.' alias="Checkout"'
);
$store_id=dbLastInsertId();
// }
// { add online store stuff to page vars
$store_vals=array(
	'online_stores_admin_email' => $_SESSION['wizard']['payment']['email'],
	'online_stores_vat_percent' => 0,
	'online_stores_requires_login' => $_SESSION['wizard']['payment']['login'],
);

// { paypal
$store_vals['online_stores_paypal_address']
	=@$_SESSION['wizard']['payment']['paypal']==1
	?$_SESSION['wizard']['payment']['paypal-email']
	:'';
// }
// { bank transfer
$store_vals+=array(
	'online_stores_bank_transfer_bank_name'
		=>	(@$_SESSION['wizard']['payment']['transfer']==1)?
				$_SESSION['wizard']['payment']['transfer-bankname']:
				'',
	'online_stores_bank_transfer_sort_code'
		=>	(@$_SESSION['wizard']['payment']['transfer']==1)?
				$_SESSION['wizard']['payment']['transfer-sortcode']:
				'',
	'online_stores_bank_transfer_account_name'
		=>	(@$_SESSION['wizard']['payment']['transfer']==1)?
				$_SESSION['wizard']['payment']['transfer-accountname']:
				'',
	'online_stores_bank_transfer_account_number'
		=>	(@$_SESSION['wizard']['payment']['transfer']==1)?
				$_SESSION['wizard']['payment']['transfer-number']:
				'',
	'online_stores_bank_transfer_message'
		=>	(@$_SESSION['wizard']['payment']['transfer']==1)?
				$_SESSION['wizard']['payment']['transfer-message']:
				'',
);
// }
// { realex
$store_vals+=array(
	'online_stores_realex_merchantid'
		=>	(@$_SESSION['wizard']['payment']['realex']==1)?
				$_SESSION['wizard']['payment']['realex-merchantid']:
				'',
	'online_stores_realex_sharedsecret'
		=>	(@$_SESSION['wizard']['payment']['realex']==1)?
				$_SESSION['wizard']['payment']['realex-secret']:
				'',
	'online_store_redirect_to'
		=>	(@$_SESSION['wizard']['payment']['realex']==1)?
				$_SESSION['wizard']['payment']['realex-redirect']:
				'',
	'online_stores_realex_testmode'	
		=>	(@$_SESSION['wizard']['payment']['realex']==1)?
				$_SESSION['wizard']['payment']['realex-mode']:
				'',
);
// }
// { form fields
$store_vals['online_stores_fields']='{"FirstName":{"required":"required","s'
	.'how":1},"Surname":{"required":"required","show":1},"Phone":{"required":'
	.'"required","show":1},"Email":{"required":"required","show":1},"Street":'
	.'{"show":1},"Street2":{"show":1},"Town":{"show":1},"County":{"show":1},"'
	.'country":{"show":1},"BillingAddressIsDifferentToDelivery":{"show":1},"B'
	.'illing_FirstName":{"show":1},"Billing_Surname":{"show":1},"Billing_Phon'
	.'e":{"show":1},"Billing_Email":{"show":1},"Billing_Street":{"show":1},"B'
	.'illing_Street2":{"show":1},"Billing_Town":{"show":1},"Billing_County":{'
	.'"show":1},"Billing_Country":{"show":1}}';
// }
// { invoice
$num=($_SESSION['wizard']['company']['invoice']==2)?2:'';
$invoice=file_get_contents('../invoice_template_sample'.$num.'.html');

$defaults=array(
	'COMPANY NAME',
	'Address, Town, County, Country',
	'Tel: 1234 5678',
	'Fax: 1234 5678',
	'Email: sales@example.com',
);

$variables=array(
	@$_SESSION['wizard']['company']['name'],
	@$_SESSION['wizard']['company']['address'],
	(isset($_SESSION['wizard']['company']['phone']))?
		'Tel: '.$_SESSION['wizard']['company']['phone']:
		'',
	(isset($_SESSION['wizard']['company']['fax']))?
		'Fax: '.$_SESSION['wizard']['company']['fax']:
		'',
	(isset($_SESSION['wizard']['company']['email']))?
		'Email: '.$_SESSION['wizard']['company']['email']:
		'',
);

$store_vals['online_stores_invoice']=str_replace(
	$defaults,
	$variables,
	$invoice
);
// }
// { execute query
$query='insert into page_vars (page_id,name,value) values ';
foreach ($store_vals as $name=>$value) {
	$query.=' ('.$store_id.',"'.$name.'","'.addslashes($value).'"),';
}
$query=substr($query, 0, strlen($query)-1); // remove last comma
dbQuery($query);
dbQuery(
	'insert into online_store_vars set name="email_invoice"'
	.',val="'.addslashes($store_vals['online_stores_invoice']).'"'
);
// }

// }
// { add online store widget to sidebar1 if exists
$body=dbOne('select body from panels where name="sidebar1" limit 1', 'body');
if ($body!='') {
	$body=json_decode($body, true);
	$store_widget=array(
		'type' => 'online-store',
		'name' => $_SESSION['wizard']['name'],
		'panel' => '',
		'' => 'on',
		'template' => '',
		'visibility' => array(
			$products_id,
			$store_id,
		),
	);
	array_unshift($body['widgets'], $store_widget);
	$body=addslashes(json_encode($body));
	dbQuery('update panels set body="'.$body.'" where name="sidebar1" limit 1');
}
// }

Core_cacheClear();

echo '<h2>'.__('Wizard Complete').'</h2>'
	.__('Your store has been created! Please click '
	.'<a href="/ww.admin/plugin.php?_plugin=products&_page=products-edit">here</a> '
	.'to start adding products');
