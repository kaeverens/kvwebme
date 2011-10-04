<?php
/**
	* admin functions for Products
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

function Products_adminCategoryGetFromID($id){
	$ps=dbAll(
		'select product_id from products_categories_products where category_id='
		.$id
	);
	$products=array();
	$pageid= dbOne(
		'select page_id from page_vars where name="products_category_to_show" a'
		.'nd value='.$id, 'page_id'
	);
	foreach ($ps as $p) {
		$products[]=$p['product_id'];
	}
	$data=array(
		'attrs'=>dbRow(
			'select id,associated_colour,name,enabled,parent_id from products_cat'
			.'egories where id='.$id
		),
		'products'=>$products,
		'hasIcon'=>file_exists(USERBASE.'f/products/categories/'.$id.'/icon.png')
	);
	if (isset($pageid)) {
		$page= Page::getInstance($pageid);
		if ($page) {
			$url= $page->getRelativeUrl();
			$data['page']= $url;
		}
	}
	return $data;
}
/**
	* get details about a category
	*
	* @return array the details
	*/
function Products_adminCategoryGet() {
	if (!isset($_REQUEST['id']) || !is_numeric($_REQUEST['id'])) {
		exit;
	}
	return Products_adminCategoryGetFromID($_REQUEST['id']);
}
/**
	* edit a category
	*
	* @return array the category data
	*/
function Products_adminCategoryEdit() {
	if (!is_numeric(@$_REQUEST['id']) || @$_REQUEST['name']==''
		|| strlen(@$_REQUEST['associated_colour'])!=6
	) {
		exit;
	}
	dbQuery(
		'update products_categories set name="'.addslashes($_REQUEST['name']).'"'
		.',enabled="'.((int)$_REQUEST['enabled']).'"'
		.',associated_colour="'.addslashes($_REQUEST['associated_colour']).'"'
		.' where id='.$_REQUEST['id']);
	Core_cacheClear('products');
	$pageid=dbOne(
		'select page_id from page_vars where name="products_category_to_show" '
		.'and value='.$_REQUEST['id'],
		'page_id'
	);
	if ($pageid) {
		dbQuery('update pages set special = special|2 where id='.$pageid);
	}
	$data=Products_adminCategoryGetFromID($_REQUEST['id']);
	return $data;
}
/**
	* edit a category's contained products
	*
	* @return null
	*/
function Products_adminCategoryProductsEdit() {
	if (!is_numeric(@$_REQUEST['id'])) {
		exit;
	}
	dbQuery(
		'delete from products_categories_products where category_id='
		.$_REQUEST['id']
	);
	foreach ($_REQUEST['s'] as $p) {
		dbQuery(
			'insert into products_categories_products set product_id='
			.((int)$p).',category_id='.$_REQUEST['id']
		);
	}
	Core_cacheClear('products');
	return Products_adminCategoryGetFromID($_REQUEST['id']);
}
/**
	* add a new category
	*
	* @return array category data
	*/
function Products_adminCategoryNew() {
	if (!is_numeric(@$_REQUEST['parent_id']) || @$_REQUEST['name']=='') {
		exit;
	}
	dbQuery(
		'insert into products_categories set name="'.addslashes($_REQUEST['name'])
		.'",enabled=1,parent_id='.$_REQUEST['parent_id']
	);
	$id=dbOne('select last_insert_id() as id', 'id');
	$data=Products_adminCategoryGetFromID($id);
	return $data;
}
/**
	* delete a category
	*
	* @return null
	*/
function Products_adminCategoryDelete() {
	if (!isset($_REQUEST['id']) || !is_numeric($_REQUEST['id'])) {
		exit;
	}
	$parent=dbOne(
		'select parent_id from products_categories where id='.$_REQUEST['id'],
		'parent_id'
	);
	dbQuery(
		'update products_categories set parent_id='.$parent.' where parent='
		.$_REQUEST['id']
	);
	dbQuery('delete from products_categories where id='.$_REQUEST['id']);
	return array('status'=>1);
}
/**
	* move a category
	*
	* @return array status of the move
	*/
function Products_adminCategoryMove() {
	$id=(int)$_REQUEST['id'];
	$p_id=(int)$_REQUEST['parent_id'];
	dbQuery('update products_categories set parent_id='.$p_id.' where id='.$id);
	if (isset($_REQUEST['order'])) {
		$order=explode(',', $_REQUEST['order']);
		for ($i=0;$i<count($order);++$i) {
			$id=(int)$order[$i];
			dbQuery('update products_categories set sortNum='.$i.' where id='.$id);
		}
	}
	return Products_adminCategoryGetFromID($id);
}

/**
	* set the icon of a category
	*
	* return result of upload
	*/
function Products_adminCategorySetIcon() {
	$cat_id=(int)$_REQUEST['cat_id'];
	$dir=USERBASE.'f/products/categories/'.$cat_id;
	@mkdir($dir, 0777, true);
	$tmpname=$_FILES['file_upload']['tmp_name'];
	CoreGraphics::resize($tmpname, $dir.'/icon.png', 128, 128);
	return array('ok'=>1);
}

/**
  * Gets the data for all the products and prompts the user to save it
	*
	* @return null
	*/
function Products_adminExport() {
	$filename = 'webme_products_export_'.date('Y-m-d').'.csv';
	header('Content-Type: text/csv');
	header('Content-Disposition: attachment; filename="'.$filename.'"');
	// { Get the headers
	$fields = dbAll('describe products');
	$row = '';
	foreach ($fields as $field) {
	    $row.= '"_'.$field['Field'].'",';
	}
	$row.="\"_categories\"\n";
	$contents = $row;
	// } 
	// { Get the data
	$results = dbAll('select * from products');
	foreach ($results as $product) {
		$row = '';
		foreach ($fields as $field) {
			$row.= '"'.str_replace('"', '""', $product[$field['Field']]).'",';
		}
		$cats 
			= dbAll(
				'select category_id 
				from products_categories_products 
				where product_id = '.$product['id']
			);
			$stringCats = '';
			foreach($cats as $cat) {
				$info
					= dbRow(
						'select name, parent_id 
						from products_categories
						where id ='.$cat['category_id']
					);
				$thisCat = '';
				$catName = $info['name'];
				$thisCat.=$catName.',';
				$parent = $info['parent_id'];
				while ($parent>0) {
					$info 
						= dbRow(
							'select name, parent_id 
							from products_categories
							where id ='.$parent
						);
					$parentName = $info['name'];
					$thisCat = $parentName.'>'.$thisCat;
					$parent = $info['parent_id'];
				}
				$stringCats.= $thisCat;
			}
			$stringCats = substr($stringCats, 0, (strrpos(',', $stringCats)-1));
			$stringCats= '"'.$stringCats.'"';
			$row.= $stringCats;
			$contents.=$row."\n";
	}
	echo $contents;
	// }
}
/**
	* get details about the data fields a product has
	*
	* @return array data fields
	*/
function Products_adminProductDatafieldsGet() {
	$typeID = $_REQUEST['type'];
	$productID = $_REQUEST['product'];
	if (!is_numeric($typeID)||!is_numeric($productID)) {
		exit('Invalid arguments');
	}
	if (!dbOne('select id from products_types where id = '.$typeID, 'id')) {
		return array('status'=>0, 'message'=>'Could not find this type');
	}
	$data = array();
	$typeData = dbRow(
			'select data_fields, is_for_sale '
			.'from products_types '
			.'where id = '.$typeID
		);
	$typeFields = json_decode($typeData['data_fields']);
	$data['type'] = $typeFields;
	$data['isForSale'] = $typeData['is_for_sale'];
	if ($productID != 0) {
		$product 
			= dbRow(
				'select data_fields, product_type_id 
				from products where id = '.$productID
			);
		$productFields = json_decode($product['data_fields']);
		$oldType 
			= dbOne(
				'select data_fields 
				from products_types 
				where id = '.$product['product_type_id'],
				'data_fields'
			);
		$oldType = json_decode($oldType);
		$data['product'] = $productFields;
		$data['oldType'] = $oldType;
	}
	return $data;
}
/**
	* get products in <option> format
	*
	* @return null
	*/
function Products_adminProductsList() {
	$ps=dbAll('select id,name from products order by name');
	$end=count($ps);
	echo "product_names=[\n";
	for($i=0;$i<$end;++$i){
		echo '	["'.addslashes($ps[$i]['name']).'",'.$ps[$i]['id'].']';
		if($i<$end-1)echo ',';
		echo "\n";
	}
	echo "];";
	exit;
}
/**
	* delete a product type
	*
	* @return null
	*/
function Products_adminTypeDelete() {
	$id=(int)$_REQUEST['id'];
	dbQuery("delete from products_types where id=$id");
	Core_cacheClear();
	return true;
}
/**
	* edit a product type
	*
	* @return array
	*/
function Products_adminTypeEdit() {
	$d=$_REQUEST['data'];
	$data_fields=json_encode($d['data_fields']);
	dbQuery(
		'update products_types set name="'.addslashes($d['name'])
		.'",multiview_template="'
		.addslashes(Core_sanitiseHtmlEssential($d['multiview_template']))
		.'",singleview_template="'
		.addslashes(Core_sanitiseHtmlEssential($d['singleview_template']))
		.'",data_fields="'.addslashes($data_fields).'",is_for_sale="'
		.addslashes($d['is_for_sale']).'",prices_based_on_usergroup="'
		.addslashes($d['prices_based_on_usergroup'])
		.'",multiview_template_header="'
		.addslashes(Core_sanitiseHtmlEssential($d['multiview_template_header']))
		.'",multiview_template_footer="'
		.addslashes(Core_sanitiseHtmlEssential($d['multiview_template_footer']))
		.'" where id='.(int)$d['id']
	);
	Core_cacheClear();
	return array('ok'=>1);
}
/**
	* get data fields in <option> format
	*
	* @return null
	*/
function Products_adminDatafieldsList() {
	$fields=array();
	$filter='';
	if ($_REQUEST['other_GET_params']) {
		if (is_numeric($_REQUEST['other_GET_params'])) { // product type
			$filter=' where id='.(int)$_REQUEST['other_GET_params'];
		}
		elseif (strpos($_REQUEST['other_GET_params'], 'c')===0) {
			$cat=(int)str_replace('c', '', $_REQUEST['other_GET_params']);
			if ($cat==0) {
				$rs=dbAll('select distinct product_type_id from products');
			}
			else {
				$rs=dbAll(
					'select product_id from products_categories_products where category_id='
					.$cat
				);
				$arr=array();
				foreach ($rs as $r) {
					$arr[]=$r['product_id'];
				}
				if (!count($arr)) {
					exit;
				}
				$rs=dbAll(
					'select distinct product_type_id from products where id in ('
					.join(',', $arr).')'
				);
			}
			$arr=array();
			foreach ($rs as $r) {
				$arr[]=$r['product_type_id'];
			}
			if (!count($arr)) {
				exit;
			}
			$filter=' where id in ('.join(',', $arr).')';
		}
	}
	$rs=dbAll('select data_fields from products_types'.$filter);
	foreach ($rs as $r) {
		$fs=json_decode($r['data_fields']);
		foreach ($fs as $f) {
			$fields[]=$f->n;
		}
	}
	$fields=array_unique($fields);
	asort($fields);
	$arr=array();
	foreach ($fields as $field) {
		$arr[$field]=$field;
	}
	return $arr;
}
/**
	* upload a new image to mark products that have no uploaded image
	*
	* @return null
	*/
function Products_adminTypeUploadMissingImage() {
	$id=(int)$_REQUEST['id'];
	if (!file_exists(USERBASE.'f/products/types/'.$id)) {
		mkdir(USERBASE.'f/products/types/'.$id, 0777, true);
	}
	$imgs=new DirectoryIterator(USERBASE.'f/products/types/'.$id);
	foreach ($imgs as $img) {
		if ($img->isDot()) {
			continue;
		}
		unlink($img->getPathname());
	}
	$from=$_FILES['Filedata']['tmp_name'];
	$to=USERBASE.'f/products/types/'.$id.'/image-not-found.png';
	move_uploaded_file($from, $to);
	Core_cacheClear();
	echo '/kfmgetfull/products/types/'.$id
		.'/image-not-found.png,width=64,height=64';
	exit;
}
/**
	* copy a product type
	*
	* @return array status of the copy
	*/
function Products_adminTypeCopy() {
	if (is_numeric($_REQUEST['id'])) {
		$id=(int)$_REQUEST['id'];
		$r=dbRow('select * from products_types where id='.$id);
	}
	else {
		$n=$_REQUEST['id'];
		if (strpos($n, '..')!==false) {
			exit;
		}
		$r=json_decode(
			file_get_contents(dirname(__FILE__).'/templates/'.$n.'.json'), true
		);
		$r['data_fields']=json_encode($r['data_fields']);
	}
	dbQuery(
		'insert into products_types set name="'.addslashes($r['name'].' (copy)')
		.'",multiview_template="'.addslashes($r['multiview_template']).'",'
		.'singleview_template="'.addslashes($r['singleview_template']).'",'
		.'data_fields="'.addslashes($r['data_fields']).'",'
		.'is_for_sale='.((int)$r['is_for_sale']).',multiview_template_header="'
		.addslashes($r['multiview_template_header']).'",'
		.'multiview_template_footer="'.addslashes($r['multiview_template_footer'])
		.'",meta="'.addslashes($r['meta']).'"'
	);
	Core_cacheClear();
	return array(
		'id'=>dbLastInsertId()
	);
}
