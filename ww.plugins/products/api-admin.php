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
