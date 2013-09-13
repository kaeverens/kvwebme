<?php
/**
	* form for editing a product
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

if (!Core_isAdmin()) {
	Core_quit();
}

// { Products_showDataField

/**
	* display a field
	*
	* @param array $datafield the field's data
	* @param array $def       field definition data
	*
	* @return string categories
	*/
function Products_showDataField($datafield, $def) {
	if ($def['t']=='selected-image') {
		return;
	}
	echo '<tr><th>'.htmlspecialchars($def['n']).'</th><td>';
	switch ($def['t']) {
		case 'checkbox': // {
			echo '<input name="data_fields['.htmlspecialchars($def['n']).']" '
				.'type="checkbox"';
			if ($def['r']) {
				echo ' class="required"';
			}
			if ($datafield['v']) {
				echo ' checked="checked"';
			}
			echo ' />';
		break; // }
		case 'date': // {
			if (@$def['u']) {
				echo 'this will be entered by the user';
			}
			else {
				echo '<input class="date-human';
				if ($def['r']) {
					echo ' required';
				}
				echo '" name="data_fields['.htmlspecialchars($def['n']).']" value="'
					.htmlspecialchars($datafield['v']).'" />';
			}
		break; // }
		case 'selectbox': // {
			if (@$def['u']) {
				if ($datafield['v']=='') {
					$datafield['v']=$def['e'];
				}
				echo '<textarea class="selectbox-userdefined" '
					.'name="data_fields['.htmlspecialchars($def['n']).']">'
					.htmlspecialchars($datafield['v'])
					.'</textarea>';
			}
			else {
				$opts=explode("\n", $def['e']);
				echo '<select name="data_fields['.htmlspecialchars($def['n']).']">';
				foreach ($opts as $opt) {
					echo '<option';
					if ($opt==$datafield['v']) {
						echo ' selected="selected"';
					}
					echo '>'.htmlspecialchars($opt).'</option>';
				}
				echo '</select>';
			}
		break; // }
		case 'textarea': // {
			echo ckeditor(
				'data_fields['.htmlspecialchars($def['n']).']',
				$datafield['v'],
				null,
				true
			);
		break; // }
		case 'user': // {
			require_once SCRIPTBASE.'/ww.incs/api-admin.php';
			echo '<select name="data_fields['.htmlspecialchars($def['n']).']">'
				.'<option value="0"> -- '.__('Choose').' -- </option>';
			$groups=explode("\n", $def['e']);
			foreach ($groups as $k=>$v) {
				if ($v=='') {
					unset($groups[$k]);
				}
				else {
					$groups[$k]=addslashes($v);
				}
			}
			$gids=dbAll(
				'select id from groups where name in ("'.join('", "', $groups).'")',
				'id'
			);
			$users=dbAll(
				'select distinct id,name,email from user_accounts,users_groups'
				.' where groups_id in ('.join(',', array_keys($gids)).')'
				.' and user_accounts_id=id order by name,email'
			);
			foreach ($users as $user) {
				echo '<option value="'.$user['id'].'"';
				if ($user['id']==$datafield['v']) {
					echo ' selected="selected"';
				}
				$name=$user['name'];
				$email=$user['email'];
				if ($name) {
					if ($email) {
						$name.=' ('.$email.')';
					}
				}
				else {
					$name=$email;
				}
				echo '>'.htmlspecialchars($name).'</option>';
			}
			echo '</select>';
		break; // }
		default: // { inputbox
			echo '<input name="data_fields['.htmlspecialchars($def['n']).']"';
			if ($def['r'] && !(@$def['u'])) {
				echo ' class="required"';
			}
			echo ' value="'.htmlspecialchars($datafield['v']).'" />';
			// }
	}
	echo '</td></tr>';
}

// }

// { set up initial variables
if (isset($_REQUEST['id']) && is_numeric($_REQUEST['id'])) {
	$id=(int)$_REQUEST['id'];
}
else {
	$id=0;
}
$sql='select * from products_relation_types order by name';
$relations=dbAll($sql, '', 'products_relation_types');
// }
require_once $_SERVER['DOCUMENT_ROOT'].'/j/kfm/includes/directories.php';
if (isset($_REQUEST['action']) && $_REQUEST['action']='save') {
	$errors=array();
	if (!isset($_REQUEST['name']) || $_REQUEST['name']=='') {
		$errors[]='You must fill in the <strong>Name</strong>.';
	}
	if (count($errors)) {
		echo '<em>'.join('<br />', $errors).'</em>';
	}
	else {
		// { make sure image directory exists
		if (!is_dir(USERBASE.'/f'.$_REQUEST['images_directory'])) {    
			if (!is_dir(USERBASE.'/f/products/product-images')) {
				if (!is_dir(USERBASE.'/f/products')) {
					echo 'Creating products directory ';
					$parent_id = kfm_api_getDirectoryId('f');
					_createDirectory($parent_id, 'products');
				}
				echo 'Creating image directory ';
				$parent_id = kfm_api_getDirectoryId('products');
				_createDirectory($parent_id, 'product-images');
			}
			$pos = strrpos($_REQUEST['images_directory'], '/');
			if ($pos===false) {
				$dname.= $_REQUEST['images_directory'];
			}
			else {
				$dname = substr($_REQUEST['images_directory'], $pos+1);
			}
			if (strlen($dname)==0) {
				$dname = rand().microtime();
			}
			$parent_id = kfm_api_getDirectoryId('products/product-images');
			$parent = kfmDirectory::getInstance($parent_id);
			$parent->createSubdir($dname);
		}
		// }
		// { save main data and data fields
		$sql='set name="'.addslashes($_REQUEST['name']).'"'
			.',link="'.addslashes(transcribe(__FromJson($_REQUEST['name'], true))).'"'
			.',ean="'.addslashes(@$_REQUEST['ean']).'"'
			.',user_id='.((int)@$_REQUEST['user_id'])
			.',stock_number="'.addslashes($_REQUEST['stock_number']).'"'
			.',activates_on="'.addslashes($_REQUEST['activates_on']).'"'
			.',expires_on="'.addslashes($_REQUEST['expires_on']).'"'
			.',product_type_id='.((int)$_REQUEST['product_type_id'])
			.',default_category='.((int)$_REQUEST['products_default_category'])
			.',enabled='.(int)$_REQUEST['enabled']
			.',date_edited=now()'
			.', num_of_categories='.count($_REQUEST['product_categories'])
			.',location='.((int)$_REQUEST['location'])
			.',images_directory="'.addslashes($_REQUEST['images_directory']).'"';
		foreach ($_REQUEST['productsExtra'] as $k=>$v) {
			$sql.=', `'.addslashes($k).'`="'.addslashes($v).'"';
		}
		// { add data fields to SQL
		$datafields=array();
		if (!isset($_REQUEST['data_fields'])) {
			$_REQUEST['data_fields']=array();
		}
		foreach ($_REQUEST['data_fields'] as $n=>$v) {
			$datafields[]=array(
				'n'=>$n,
				'v'=>is_array($v)?json_encode($v):$v
			);
		}
		$sql.=',data_fields="'.addslashes(json_encode($datafields)).'"';
		// }
		// { online store stuff
		// { stock control
		$stockcontrol_total=(int)@$_REQUEST['stockcontrol_total'];
		$stockcontrol_detail='false';
		if (isset($_REQUEST['stockcontrol_detail'])) {
			$detail=$_REQUEST['stockcontrol_detail'];
			$stockcontrol_total=0;
			$rows=array();
			foreach ($detail as $row) {
				$empty=0;
				foreach ($row as $k=>$v) {
					if ($k!='_amt' && $v=='') {
						$empty++;
					}
				}
				if (!$empty) {
					$rows[]=$row;
					$stockcontrol_total+=$row['_amt'];
				}
			}
			if (count($rows)) {
				$stockcontrol_detail=json_encode($rows);
			}
		}
		$sql.=',stockcontrol_total='.$stockcontrol_total
			.',stockcontrol_details="'.addslashes($stockcontrol_detail).'"';
		// }
		// }
		if ($id) {
			$sql="update products $sql where id=$id";
			dbQuery($sql);
		}
		else {
			dbQuery("insert into products $sql,date_created=now()");
			$id=dbLastInsertId();
		}
		// }
		// { save categories
		dbQuery('delete from products_categories_products where product_id='.$id);
		if (!isset($_REQUEST['product_categories'])) {
			$type=ProductType::getInstance((int)$_REQUEST['product_type_id']);
			$_REQUEST['product_categories']
				=array((string)$type->default_category=>'on');
		}
		foreach ($_REQUEST['product_categories'] as $key=>$val) {
			dbQuery(
				'insert into products_categories_products set product_id='
				.$id.',category_id='.$key
			);
		}
		// }
		// { save product relations
		$rls=array();
		foreach ($relations as $r) {
			$rls[$r['id']]=$r;
			if ($r['one_way']) {
				dbQuery(
					'delete from products_relations where from_id='.$id
					.' and relation_id='.$r['id']
				);
			}
			else {
				dbQuery(
					'delete from products_relations where (from_id='.$id
					.' or to_id='.$id.') and relation_id='.$r['id']
				);
			}
		}
		if (isset($_REQUEST['product-relations-type'])) {
			foreach ($_REQUEST['product-relations-type'] as $k=>$v) {
				if ($v && $_REQUEST['products-relations-product'][$k]) {
					$rid=(int)$v;
					$pid=(int)$_REQUEST['products-relations-product'][$k];
					if ($pid==$id) { // don't relate a product to itself
						continue;
					}
					dbQuery(
						'delete from products_relations where from_id='.$id
						.' and to_id='.$pid.' and relation_id='.$rid
					);
					dbQuery(
						'insert into products_relations set from_id='.$id
						.',to_id='.$pid.',relation_id='.$rid
					);
					if (!$rls[$rid]['one_way']) {
						dbQuery(
							'delete from products_relations where from_id='.$pid
							.' and to_id='.$id.' and relation_id='.$rid
						);
						dbQuery(
							'insert into products_relations set from_id='.$pid
							.',to_id='.$id.',relation_id='.$rid
						);
					}
				}
			}
		}
		// }
		echo '<em>Product saved</em>';
		unset($DBVARS['cron-next']);
		Core_configRewrite();
	}
	Core_cacheClear('products,pages,products_categories_products,products_relations');
}

if ($id) {
	$sql='select * from products where id='.$id;
	$pdata=dbRow($sql, 'products');
	if (!$pdata) {
		echo '<em>No product with that ID exists.</em>';
		return;
	}
}
else {
	$pdata=array(
		'id'=>0,
		'name'=>'',
		'product_type_id'=>0,
		'image_default'=>0,
		'enabled'=>1,
		'date_created'=>date('Y-m-d'),
		'data_fields'=>'{}',
		'images_directory'=>'',
		'online_store_fields'=>'{}'
	);
}

// { top links
echo '<a href="plugin.php?_plugin=products&amp;_page=products">'
	.__('List all Products').'</a> | '
	.'<a href="plugin.php?_plugin=products&amp;_page=products-edit">'
	.__('Add a Product').'</a> | '
	.'<a href="javascript:Core_screen(\'products\', \'js:Import\');">'
	.__('Import', 'core').'</a>';
// }
// { gather needed data
$sql='select stock_control,data_fields from products_types where id='.$pdata['product_type_id'];
$product_type=dbRow($sql, 'products_types');
// }

// { start form
echo '<form novalidate="novalidate" id="products-form" action="'.$_url
	.'" method="post" onsubmit="products_getData();">'
	.'<input type="hidden" name="id" value="'.$id.'"/>'
	.'<input type="hidden" name="action" value="save" />'
	.'<div id="product-edit-wrapper">';
// }
// { main details
echo '<h2>'.__('Main Details').'</h2><div id="main-details"><table>';
// { name, type, manage images
echo '<tr>';
// { name
echo '<th><div class="help products/name"></div>Name</th><td>';
echo '<input class="not-empty translatable" name="name" value="'
	.htmlspecialchars($pdata['name']).'" /></td>';
// }
// { type
echo '<th><div class="help products/type"></div>Type</th><td>';
$sql='select id,name from products_types order by name';
$ptypes=dbAll($sql, '', 'products_types');
if ($ptypes===false) {
	echo '<em>No product types created yet. '
		.'Please <a href="plugin.php?_plugin=products&amp;_page=types-edit">'
		.'create one</a> before you go any further!</em>';
}
else {
	if (!$pdata['product_type_id'] && count($ptypes)) {
		$pdata['product_type_id']=$ptypes[0]['id'];
	}
	echo '<select id="product_type_id" name="product_type_id" 
		product="'.$pdata['id'].'">';
	foreach ($ptypes as $ptype) {
		echo '<option value="'.$ptype['id'].'"';
		if ($ptype['id']==$pdata['product_type_id']) {
			echo ' selected="selected"';
		}
		echo '>'.htmlspecialchars($ptype['name']).'</option>';
	}
	echo '</select>';
}
echo '</td>';
// }
// { enable/disable dates
// { enable date
if (@!$pdata['activates_on']) {
	@$pdata['activates_on']=date('Y-m-d').' 00:00:00';
}
echo '<td>Enable Date<br /><input class="datetime" name="activates_on" '
	.'value="'.$pdata['activates_on'].'"/></td>';
// }
// { disable date
if (@!$pdata['expires_on']) {
	@$pdata['expires_on']='2100-01-01 00:00:00';
}
echo '<td>Disable Date<br /><input class="datetime" name="expires_on" '
	.'value="'.$pdata['expires_on'].'"/></td>';
// }
// }
echo '</tr>';
// }
echo '<tr>';
// { stock_number
echo '<th><div class="help products/stock-number"></div>Stock Number</th><td>';
echo '<input class="not-empty" name="stock_number" value="'
	.htmlspecialchars(@$pdata['stock_number']).'" /></td>';
// }
// { enabled
echo '<th><div class="help products/enabled"></div>Enabled</th>'
	.'<td><select name="enabled">'
	.'<option value="1">Yes</option>'
	.'<option value="0"';
if (!$pdata['enabled']) {
	echo ' selected="selected"';
}
echo '>No</option></select></td>';
// }
// { page link
if ($id) {
	echo '<td><strong>Page:</strong> <span id="product_table_link_holder">';
	$sql='select page_id from page_vars where name="products_product_to_show" and value='.$id;
	$pageid=dbOne($sql, 'page_id', 'page_vars');
	if (!$pageid) {
		echo '<a href="javascript:;" id="page_create_link" 
			onClick=
				"createPopup(
					\''.htmlspecialchars($pdata['name']).'\','.
					$id.','.
					'3'.
				');"'.
			'>';
		echo 'click to create</a>';
	}
	else {
		$dir= dirname(__FILE__);
		$page= Page::getInstance($pageid);
		$url= $page->getRelativeUrl();
		echo '<a href="'.$url.'" target="_blank" id="view_this_product">'
			.htmlspecialchars($url).'</a> '
			.'[<a title="delete the product\'s page" href="javascript:;" pid="'
			.$id.'" class="delete-product-page">x</a>]';
	}
	echo '</span></td>';
}
else {
	echo '<td>&nbsp;</td>';
}
// }
// { owner
$user_id=(int)@$pdata['user_id'];
if (!$user_id) {
	$user_id=(int)$_SESSION['userdata']['id'];
}
$sql='select name from user_accounts where id='.$user_id;
$user_name=dbOne($sql, 'name', 'user_accounts');
echo '<td><strong>Owner:</strong> <select name="user_id"><option value="'
	.$user_id.'">'.$user_name.'</option></select></td>';
// }
echo '</tr><tr>';
// { EAN
echo '<th>EAN-13 barcode</th><td><input name="ean" value="'
	.htmlspecialchars(@$pdata['ean']).'" /></td>';
// }
// { Location
echo '<th>'.__('Location').'</th><td colspan="2"><select name="location">'
	.'<option value="0"> -- </option>';
require_once $_SERVER['DOCUMENT_ROOT'].'/ww.incs/api-admin.php';
$opts=Core_locationsGetFull();
foreach ($opts as $k=>$v) {
	echo '<option value="'.$v.'"';
	if ($v==$pdata['location']) {
		echo 'selected="selected"';
	}
	echo '>'.htmlspecialchars($k).'</option>';
}
echo '</select></td>';
// }
echo '</tr>';
// { images
if (!isset($pdata['images_directory']) 
	|| !$pdata['images_directory'] 
	|| $pdata['images_directory']=='/'
	|| !is_dir(USERBASE.'/f/'.$pdata['images_directory'])
) {
	if (!is_dir(USERBASE.'/f/products/product-images')) {
		mkdir(USERBASE.'/f/products/product-images', 0777, true);
	}
	$pdata['images_directory']='/products/product-images/'
		.md5(rand().microtime());
	mkdir(USERBASE.'/f'.$pdata['images_directory']);
}
if (!is_dir(USERBASE.'/f'.$pdata['images_directory'])) {    
	$parent_id = kfm_api_getDirectoryId('products/product-images');
	$pos = strrpos($pdata['images_directory'], '/');
	$dname='';
	if ($pos===false) {
		$dname = $pdata['images_directory'];
	}
	elseif (isset($_REQUEST['images_directory'])) {
		$dname = substr($_REQUEST['images_directory'], $pos);
	}
	if ($dname!='') {
		_createDirectory($parent_id, $dname);
	}
}
echo '<tr><th><input type="hidden" name="images_directory" value="'
	.$pdata['images_directory'].'" /><div class="help products/images"></div>'
	.'Images</th><td colspan="4">';
$image_directory=USERBASE.'/f/'.$pdata['images_directory'];
$dir=new DirectoryIterator($image_directory);
$n=0;
$images=array();
foreach ($dir as $f) {
	if ($f->isDot()) {
		continue;
	}
	$images[]=$f->getFilename();
	++$n;
}
echo '<iframe src="/ww.plugins/products/admin/uploader.php?images_directory='
	.urlencode($pdata['images_directory'])
	.'" style="width:400px;height:50px;border:0;overflow:hidden"></iframe>';
echo '<script>window.kfm={alert:function(){}};window.kfm_vars={};'
	.'function x_kfm_loadFiles(){}'
	.'function kfm_dir_openNode(){$("#products-form").submit();}'
	.'var product_id='.$id.';</script>';
if ($n) {
	echo '<div id="product-images-wrapper">';
	for ($i=0;$i<$n;$i++) {
		$default=$images[$i]==basename($pdata['image_default'])
			?' class="default"'
			:'';
		echo '<div'.$default.'>';
		echo '<a href="/f'.$pdata['images_directory'].'/'.$images[$i].'" target="_blank">'
			.'<img id="products-img-'.$n.'" src="/a/f=getImg/w=64/h=64/'.$pdata['images_directory'].'/'.$images[$i].'"/>'
			.'</a><br />'
			.'<input type="checkbox" id="products-dchk-'.$n.'" />'
			.'<a class="delete" href="javascript:;" id="products-dbtn-'.$n.'">delete</a>'
			.'<br />'
			.'<a class="caption" href="javascript:;" id="products-cbtn-'
			.$n.'">edit caption</a><br />'
			.'<a class="mark-as-default" href="javascript:;" '
			.'id="products-dfbtn-'.$n.'" imgsrc="'
			.$pdata['images_directory'].'/'.$images[$i].'"'
			.'>set default</a></div>';
	}
	echo '</div>';
} 
else {
	echo '<em>no images yet. please upload some.</em><!-- '
		.$pdata['images_directory'].' -->';
}
echo '<a style="background:#ff0;font-weight:bold;color:red;display:block;'
	.'text-align:center;" href="#page_vars[images_directory]" '
	.'onclick="javascript:window.open(\'/j/kfm/'
	.'?startup_folder='.addslashes($pdata['images_directory']).'\'+'
	.'\'kfm\',\'modal,width=800,height=600\');">Manage Images</a></td></tr>';
// }
echo '</table></div>';
// }
// { data fields
echo '<h2>'.__('Data Fields').'</h2><div id="data-fields">';
if ($id) {
	echo '<table id="data-fields-table">';
	$datafields=json_decode($pdata['data_fields'], true);
	$datafieldjson=$product_type['data_fields'];
	if ($datafieldjson=='') {
		$datafieldjson='[]';
	}
	$datafieldjson=str_replace(array("\n", "\r"), array('\n', ''), $datafieldjson);
	$datafieldjson=json_decode($datafieldjson, true);
	$datafielddefs=array();
	if (@$datafieldjson) {
		foreach ($datafieldjson as $d) {
			$datafielddefs[$d['n']]=$d;
		}
		foreach ($datafields as $datafield) {
			if (isset($datafield['n']) && isset($datafielddefs[$datafield['n']])) {
				$def=$datafielddefs[$datafield['n']];
				unset($datafielddefs[$datafield['n']]);
				Products_showDataField($datafield, $def);
			}
		}
		foreach ($datafielddefs as $def) {
			Products_showDataField(array('v'=>''), $def);
		}
	}
	else {
		echo '<p><i>No datafields defined in Product Type</i></p>';
	}
	echo '</table>';
}
else {
	echo '<em>'.__(
		'You must save the product\'s main details before editing anything else'
	)
	.'</em>';
}
echo '</div>';
// }
// { online store tabs
if ($id && isset($PLUGINS['online-store'])) {
	// { set up fields
	$online_store_fields 
		= array (
			'base_price' => 'Base Price',
			'trade_price' => 'Trade Price',
			'sale_price' => 'Sale Price',
			'sale_price_type' => array(
				'Sale Type',
				'Options' => array(
					'set actual price',
					'subtract from base price',
					'reduce base price by percentage'
				)
			),
			'bulk_price' =>__('Bulk Price'),
			'bulk_amount' =>__('Bulk Amount'),
			'weight' => __('Weight (kg)'),
			'tax_free'  
				=> array (
					__('Tax-free'),
					'Options' 
						=>array(
							'No',
							'Yes'
						)
				),
			'custom_tax' => __('Custom Tax Amount'),
			'free_delivery' => array(
				__('Free Delivery'), 'Options'=>array(__('No'), __('Yes'))
			),
			'not_discountable' => array(
				__('Not Discountable'), 'Options'=>array(__('No'), __('Yes'))
			),
			'amount_sold' => __('Amount Sold'),
			'amount_in_stock' => __('Amount in Stock'),
			'amount_allowed_per_purchase' => __('Amount allowed per purchase')
		);
	$sql='select is_voucher from products_types where id='.$pdata['product_type_id'];
	if (dbOne($sql, 'is_voucher', 'products_types')=='1') {
		$online_store_fields['_voucher_value']='Voucher Value';
	}
	// }
	$online_store_data = json_decode($pdata['online_store_fields']);
	echo '<h2>'.__('Online Store').'</h2><div id="online-store-fields" class="products-online-store"';
	if (!isset($addOnlineStoreFields)||!$addOnlineStoreFields) {
		echo ' style="display:none"';
	}
	echo '><table>';
	foreach ($online_store_fields as $k=>$display) {
		echo '<tr><th>';
		if (is_array($display)) {
			echo $display[0];
		}
		else {
			echo $display;
		}
		echo '</th><td>';
		if (!is_array($display)) {
			echo '<input class="small" type="number" name="productsExtra[os_'
				.$k.']"';
			if (isset($online_store_data->$k)) {
				echo ' value="'.$online_store_data->$k.'"';
			}
			if ($pdata['os_'.$k]) {
				echo ' value="'.$pdata['os_'.$k].'"';
			}
			echo ' />';
		}
		else {
			echo '<select name="productsExtra[os_'.$k.']">';
			for ($i=0; $i<count($display['Options']); ++$i) {
				echo '<option value="'.$i.'"';
				if ($i==@$online_store_data->$k) {
					echo ' selected="selected"';
				}
				echo '>'.$display['Options'][$i]
					.'</option>';
			}
			echo '</select>';
		}
		echo '</td></tr>';
	}
	echo '</table></div>';
	if ((int)$product_type['stock_control']) {
		echo '<h2>'.__('Stock Control').'</h2><div id="stock-control">';
		// { figure out what kind of stock control we have
		$options=array();
		foreach ($datafieldjson as $datafield) {
			if ($datafield['t']=='selectbox' && $datafield['u']=='1') {
				$options[]=$datafield['n'];
			}
		}
		// }
		// { stock control for simple products
		echo '<label>'.__('Amount in stock').': '
			.'<input class="small" name="stockcontrol_total" value="'
			.(int)@$pdata['stockcontrol_total'].'"/></label>';
		// }
		// { stock control for products which have user-selectable options
		if (count($options)) {
			$detail=@$pdata['stockcontrol_details'];
			if (!$detail) {
				$detail='[]';
			}
			echo '<table id="stockcontrol-complex"></table><script>'
				.'window.stockcontrol_detail='.$detail.';window.stockcontrol_options=["'
				.join('", "', $options).'"];</script><a href="#" id="'
				.'stockcontrol-addrow">add row</a>'
				.'<p>'.__(
					'To remove rows, set their options to "-- Choose --" and save the'
					.' product.'
				)
				.'</p>';
		}
		// }
		echo '</div>';
	}
}
// }
Core_trigger(
	'products-show-edit-form-tabs',
	array(
		$pdata,
		$product_type
	)
);
// { categories
echo '<h2>'.__('Categories').'</h2><div id="categories"><p>'.__('At least one category must be chosen.')
	.'</p>';
// { add selected categories to the list
$sql='select category_id from products_categories_products where product_id='.$id;
$rs=dbAll($sql, '', 'products_categories_products');
echo '<ul id="categories-wrapper">';
foreach ($rs as $r) {
	$cat=ProductCategory::getInstance($r['category_id']);
	if (!$cat) {
		continue;
	}
	echo '<li><input type="checkbox" name="product_categories['.$cat->vals['id'].']" checked="checked"/>'
		.$cat->getBreadcrumbs()
		.'</li>';
}
echo '</ul><button id="category-add">Add Category</button>';
// }
$cid=(int)@$pdata['default_category'];
if (!$cid) {
	$cid=1;
}
$sql='select name from products_categories where id='.$cid;
$default_category=dbOne($sql, 'name', 'products_categories');
echo '<br/><label>Default Category: <select name="products_default_category">'
	.'<option value="'.((int)@$pdata['products_default_category']).'">'
	.$default_category
	.'</option></select></label>';
echo '</div>';
// }
// { related items
if (count($relations)) {
	echo '<h2>'.__('Relations').'</h2><div id="relations">'
		.'<table id="product-relations"><tr><th>Relation Type</th><th>Related P'
		.'roduct</th></tr>';
	foreach ($relations as $relation) {
		$ps=dbAll(
			'select * from products_relations where relation_id='.$relation['id']
			.' and from_id='.$id
		);
		$options='<option value=""> -- '.__('Choose').' -- </option>';
		foreach ($relations as $r) {
			$options.='<option value="'.$r['id'].'"';
			if ($r['id']==$relation['id']) {
				$options.=' selected="selected"';
			}
			$options.='>'
				.htmlspecialchars($r['name'])
				.'</option>';
		}
		foreach ($ps as $p) {
			echo '<tr><td><select name="product-relations-type[]">'
				.$options.'</select></td><td><select class="products-relations-product"'
		    .' name="products-relations-product[]">'
				.'<option value="'.$p['to_id'].'">';
			echo htmlspecialchars(__FromJson(
				dbOne('select name from products where id='.$p['to_id'], 'name')
			))
				.'</option></select></td></tr>';
		}
	}
	echo '<tr><td><select name="product-relations-type[]">'
		.'<option value=""> -- '.__('Choose').' -- </option>';
	foreach ($relations as $relation) {
		echo '<option value="'.$relation['id'].'">'
			.htmlspecialchars($relation['name'])
			.'</option>';
	}
	echo '</select></td>'
		.'<td><select class="products-relations-product"'
		.' name="products-relations-product[]">'
		.'<option value=""> -- '.__('Choose').' -- </option></select>';
	WW_addScript('products/admin/products-edit-related.js');
	echo '</td></tr></table></div>';
}
// }
// { end form and tabs
echo '</div><input type="submit" value="'.__('Save').'" /></form>';
// }
WW_addScript('products/admin/products-edit.js');
WW_addScript('products/admin/create-page.js');
WW_addScript('products/admin/add-category.js');
WW_addCss('/ww.plugins/products/admin.css');
WW_addInlineScript(
	'$(function(){'
	.'$("#onTheFlyParent").remoteselectoptions({'
	.'url:"/a/p=products/f=adminCategoriesGetRecursiveList"'
	.'})'	
	.'})'
);
