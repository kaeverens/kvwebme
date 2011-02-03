<?php
/**
	* The import script
	*
	* PHP Version 5
	*
	* Displays a form to upload a file, checks its type and puts its contents
	* into the products database
	*
	* @category   ProductsPlugin
	* @package    WebWorksWebme
	* @subpackage ProductsPlugin
	* @author     Belinda Hamilton <bhamilton@webworks.ie>
	* @license    GPL Version 2
	* @link       www.webworks.ie
	**/
echo '<script src="/ww.plugins/products/admin/products.js"></script>';
$name=array();
if (isset($_POST['import'])) {
	if (isset($_FILES['file'])) {
		$file = $_FILES['file'];
		if (preg_match('/\.csv/', $file['name'])) { // If it has the right extension
			// { build a translator - public to internal names
			$dfs=dbAll('select data_fields from products_types');
			$dfs_translate=array();
			foreach ($dfs as $df) {
				$df=json_decode($df['data_fields']);
				foreach ($df as $f) {
					$dfs_translate[$f->ti]=$f->n;
				}
			}
			// }
			if (isset($_POST['clear_database'])) {
				dbQuery('delete from products');
				dbQuery('delete from products_categories_products');
				if (isset($_POST['remove_associated-files'])) {
					$base = $_SERVER['DOCUMENT_ROOT'];
					require_once $base.'/j/kfm/api/api.php';
					require_once $base.'/j/kfm/classes/kfmDirectory.php';
					$base_dir_name = USERBASE.'f/products/products-images';
					$base_dir_id = kfm_api_getDirectoryId($base_dir_name);
					$base_dir = kfmDirectory::getInstance($base_dir_id);
					$sub_dirs = $base_dir->getSubDirs();
					foreach ($sub_dirs as $sub) {
						$sub->delete();
					}
				}	
			}
			if (isset($_POST['clear_categories_database'])) {
				dbQuery('delete from products_categories');
				dbQuery('delete from products_categories_products');
			}
			$newName = 'webworks_webme_products_import'.time().rand().'.csv';
			$location = USERBASE.'ww.cache/products/imports';
			if (!is_dir($location)) {
				mkdir($location,0777,true);
			}
			move_uploaded_file(
				$file['tmp_name'], 
				$location.'/'.$newName
			);
			$file = fopen($location.'/'.$newName, 'r');
			$tmp = fgetcsv($file);
			// { The headings are the first line.
			foreach ($tmp as $col) {
				// { Assume that leading underscores in the name should be removed
				$col = preg_replace('/^_/', '', $col);
				if (isset($dfs_translate[$col])) {
					$col=$dfs_translate[$col];
				}
				$colNames[] = $col;
				// }
				${$col}= array();
			}
			// }
			$row = fgetcsv($file);
			// { Build the arrays of data
			$numRows=0;
			if (!is_array($data_fields)) {
				//Allow the data fields to be imported as json
				$data_fields=array();
			}
			$cols 
				= array(
					'id', //Needed for updates
					'name', 
					'product_type_id', 
					'image_default', 
					'enabled',
					'date_created',
					'images_directory',
					'categories'
				);
			if ($_POST['data_fields_option']=='json') {
				$cols[] = 'data_fields';
			}
			while ($row && ++$numRows) {
				$i = 0;
				foreach ($colNames as $col) {
					if ($col=='') { // ignore blank column headers
						continue;
					}
					if (isset($dfs_translate[$col])) {
						$col=$dfs_translate[$col];
					}
					for ($i; $i<count($row); $i++) {
						$data = $row[$i];
						break;
					}
					if (!is_array(${$col}) && in_array($col, $cols)) {
						${$col}=array();
					}
					if (is_array(${$col})) {
						if (in_array($col, $cols)) {
							${$col}[$numRows-1] = $data;
						}
						else {
							if (!is_array($data_fields[$numRows-1])) {
								$data_fields[$numRows-1] = array();
							}
							if ($data=='TRUE') {
								$data=1;
							}
							else if ($data=='FALSE') {
								$data=0;
							}
							$data_fields[$numRows-1][] = array(
								'n'=>$col,
								'v'=>$data
							);
						}
					}
					$i++;
				}
				$row = fgetcsv($file);
			}
			if ($_POST['data_fields_option']=='cols') {
				foreach($data_fields as $k=>$v) {
					$data_fields[$k]=json_encode($v);
				}
			}
			// }
			$ids = array();
			$allIds = dbAll('select id from products');
			foreach ($allIds as $num) {
				$ids[] = $num['id'];
			}
			// { Put the data into the products database
			$products_imported=0;
			$ptid=0;
			if (isset($_REQUEST['product_type_id'])) {
				$ptid=dbOne(
					'select id from products_types where id='
					.((int)$_REQUEST['product_type_id']),'id'
				);
			}
			if (!$ptid) {
				$ptid=dbOne('select id from products_types','id');
			}
			for ($i=0; $i<$numRows; $i++) {
				if (!isset($name[$i]) || $name[$i]=='') {
					$name[$i]='NO NAME SUPPLIED';
				}
				if (is_array($id)) {
					if (in_array($id[$i], $ids, false)&&is_numeric($id[$i])) {
						dbQuery(
							'update products 
							set 
								name = 
									\''.addslashes($name[$i]).'\',
								product_type_id = '.(isset($product_type_id[$i])?(int)$product_type_id[$i]:$ptid).',
								image_default = 
									\''.addslashes($image_default[$i]).'\',
								enabled = 1, 
								date_created = 
									\''.addslashes($date_created[$i]).'\',
								data_fields = 
									\''.addslashes($data_fields[$i]).'\',
								images_directory = 
									\''.addslashes($images_directory[$i]).'\'
							where id = '.(int)$id[$i]
						);
					}
					elseif (is_numeric($id[$i])) {
						dbQuery(
							'insert into products 
								set id='.(int)$id[$i].',
								name=\''.addslashes($name[$i]).'\',
								product_type_id=\''.$ptid.'\',
								enabled=\'1\',
								image_default=\''.addslashes($image_default[$i]).'\',
								date_created=\''.addslashes($date_created[$i]).'\',
								data_fields=\''.addslashes($data_fields[$i]).'\',
								images_directory=\''.addslashes($images_directory[$i]).'\'
						');
					}
					elseif ($id[$i]==null) {
						dbQuery(
							'insert into products	
							(
								name,
								product_type_id,
								image_default,
								enabled,
								date_created,
								data_fields,
								images_directory
							)
							values
							(
								'.addslashes($name[$i]).', 
								\''.$ptid.'\',
								\''.addslashes($image_default[$i]).'\',
								\'1\',
								\''.addslashes($date_created[$i]).'\',
								\''.addslashes($data_fields[$i]).'\',
								\''.addslashes($images_directory[$i]).'\'
							)'
						);
					}
				}
				else {
					$sql='insert into products	set name = \''.addslashes($name[$i]).'\', product_type_id = '.$ptid.',
						image_default = \''.addslashes($image_default[$i]).'\', enabled = 1, 
						date_created = \''.(isset($date_created[$i])?addslashes($date_created[$i]):'').'\',
						data_fields = \''.addslashes($data_fields[$i]).'\',
						images_directory = \''.(isset($images_directory[$i])?addslashes($images_directory[$i]):'').'\'';
					dbQuery($sql);
				}
				++$products_imported;
			}
			// }
			if (($_POST['cat_options'])!='') {
				$cids = Products_Import_insertIntoCats($categories, $id);
			}
			if (isset($_POST['prune_cats'])) {
				$allCats = dbAll('select id from products_categories');
				foreach ($allCats as $cat) {
					Products_Import_pruneCats($cat['id']);
				}
			}
			if (isset($_POST['create_page'])&&$_POST['cat_options']!='') {
				Products_Import_createPage($cids);
			}
			if (isset($_POST['prune_cat_pages'])) {
				$cats = dbAll('select id from products_categories');
				foreach ($cats as $cat) {
					Products_Import_pruneCatPages($cat['id']);
				}	
				$query = 'select page_id from page_vars ';
				$query.= 'where name = "products_category_to_show"';
				if (count($cats)) {
					$query.= ' and value not in (';
					foreach ($cats as $cat) {
						$query.= $cat['id'].', ';
					}
					$query = substr_replace($query, ')', strrpos($query, ','));
				}
				$p_ids = dbAll($query);
				foreach ($p_ids as $p_id) {
					Products_Import_deletePagesForCatsThatDontExist(
						$p_id['page_id'], 
						$vals
					);
				}
			};
			fclose($file);
			unlink($location.'/'.$newName);
			$_FILES['file'] = '';
			echo '<em>'.$products_imported.' products imported</em>';
		}
		elseif (!empty($_POST['file'])) {
			echo '<em>Only csv files are permitted</em>';
		}
	}
	cache_clear('products');
}
// { display form
echo '<form method="post" enctype="multipart/form-data"><table>';
// { types
echo '<tr><th>Product type</th><td><select name="product_type_id">';
$types=dbAll('select id,name from products_types order by name');
echo '<option value="0"> -- please choose -- </option>';
foreach ($types as $r) {
	echo '<option value="'.$r['id'].'">'.htmlspecialchars($r['name']).'</option>';
}
echo '</select></td></tr>';
// }
echo '<tr><th>Delete products before import?</th>'
	.'<td><input type="checkbox" id="clear_database" name="clear_database" '
	.'onchange="toggle_remove_associated_files();" /></td></tr>'
	.'<tr><th>The CSV file is formatted as</th>'
	.'<td><select name="data_fields_option">'
	.'<option value="cols">individual columns in file (default)</option>'
	.'<option value="json">data_fields column in file</option>'
	.'</select></td></tr>';
$cats = dbAll('select name, id from products_categories');
$jsonCats = json_encode($cats);
echo '<tr><th>Delete categories before import?</th>'
	.'<td><input type="checkbox" name="clear_categories_database" '
	.'id="clear_categories_database" '
	.'onchange=\'show_hide_cat_options('.$jsonCats.');\' /></td></tr>';
echo '<tr><th>Delete empty categories on import?</th>'
	.'<td><input type="checkbox" name="prune_cats" id = "prune-cats" /></td></tr>';
echo '<tr><th>Import into categories</th>'
	.'<td><select id="cat_options" name="cat_options">'
	.'<option value="">--none--</option>'
	.'<option value="0">In File</option>';
foreach ($cats as $cat) {
	echo '<option value="'.$cat['id'].'">'.$cat['name'].'</option>';
}
echo '</select></td></tr>';
echo '<tr><th>Create pages for imported categories?</th>'
	.'<td><input type="checkbox" name="create_page" /></td></tr>';
echo '<tr><th>Hide created pages?</th>'
	.'<td><input type="checkbox" name="hide_pages" /></td></tr>';
echo '<tr><th>Delete empty category pages on import?</th>'
	.'<td><input type="checkbox" name="prune_cat_pages" /></td></tr>';
echo '<tr><th>Select import file</th>'
	.'<td><input type="file" name="file" /></td></tr>';
echo '</table><input type="submit" name="import" value="Import Data" />';
echo '</form>';
echo '<p>The imported file must be a CSV file, where the headers exaclty match (including letter-case) the product types you are importing into.</p>';
// }

/**
	* Inserts categories into products. 
	* If the category doesn't exist it creates it
	*
	* @param array $categories The category list from the file
	* @param array $id         The product ids
	*
	* @return array $cs The category id's
	**/
function Products_Import_insertIntocats ($categories, $id) {
	$cs = array();
	switch ($_POST['cat_options']) {
		case '0': // { The categories are in the file
			$i = 0;
			foreach ($categories as $cats) { // Create cats
				$cats = explode(',', $cats);
				foreach ($cats as $catList) {
					if (!empty($catList)) {
						$catList = explode('>', $catList);
						$parent = 0;
						foreach ($catList as $cat) {
							$catID
								= dbOne(
									'select id 
									from products_categories
									where name=\''.$cat.'\' 
									and parent_id='.$parent,
									'id'
								);
							if (!$catID) {
								dbQuery(
									'insert into products_categories
									(name, parent_id)
									values(
										\''.addslashes($cat).'\', 
										'.(int)$parent
									.')'
								);
								$parent 
									= dbOne(
										'select last_insert_id()',
										'last_insert_id()'
									);
									$catID = $parent;
							}
							else {
								$parent = $catID;
							}
							$cs[] = $catID;
						}
						if (is_numeric($id[$i])) {
							dbQuery(
								'insert into 
								products_categories_products
								values(
									'.(int)$id[$i].'
									,'.(int)$catID
								.')'
							);

						}
					}
				}
				$i++;
			}
		break; // }
		default: // { The category exists
			if (is_numeric($_POST['cat_options'])) {
				for ($i=0; $i<$numRows; $i++) {
					if (is_numeric($id[$i])) {
						dbQuery(
							'insert into 
							products_categories_products
							values(
								'.(int)$id[$i].'
								,'.(int)$_POST['cat_options']
							.')'
						);
					}
				}
				$cs[] = $_POST['cat_options'];
			}
		break; // }
	}
	return $cs;
}
/**
	* Checks if a category and it's subcategories are empty. If so it deletes them
	*
	* @param int $catID The category id to check
	*
	* @return void The statement is just there to finish quickly if it can
**/
function Products_Import_pruneCats ($catID) {
	$prod_id
		= dbOne(
			'select product_id 
			from products_categories_products
			where category_id = '.$catID
			.' limit 1',
			'product_id'
		);
	if ($prod_id) {
		return;
	}
	// { Check the children
	$children 
		= dbAll(
			'select id
			from products_categories 
			where parent_id = '.$catID
		);
	if (count($children)) {
		foreach ($children as $child) {
			Products_import_pruneCats($child['id']);
		}
		$first_child
			= dbOne(
				'select id 
				from products_categories
				where parent_id = '.$catID
				.' limit 1',
				'id'
			);
	}
	// }
	if (!$first_child) {
		dbQuery('delete from products_categories where id = '.$catID);
	}
}
/**
	* Creates a page for the imported categories
	*
	* @param array $categories The category list
	*
	* @return void
	*
**/
function Products_Import_createPage ($categories) {
	cache_clear('pages');
	$names = array();
	foreach ($categories as $cat) {
		$page_id 
			= dbOne(
				'select page_id 
				from page_vars 
				where value='.(int)$cat,
				'page_id'
			);
		$default 
			= dbOne(
				'select name 
				from products_categories 
				where id="'.(int)$cat.'"',
				'name'
			);
		$name = $default;
		$i=2;
		while (dbOne("select name from pages where name= '$name'", 'name')) {
			$name = $default.$i;
			$i++;
		}
		$names[] = $name;
		if (!$page_id) {
			$hasProducts 
				= dbOne(
					'select product_id 
					from products_categories_products
					where category_id = "'.(int)$cat.'" 
					limit 1',
					'product_id'
				);
			if (!$hasProducts) {
				$query = 'insert into pages ';
				$query.= 'set name = "'.addslashes($name).'", ';
				$query.= 'type = 9, ';
				$query.= 'cdate = now(), ';
				$query.= 'edate = now(), ';
				$query.= 'associated_date = now()';
				if (isset($_POST['hide_pages'])) {
					$query.= ', special = 2';
				}
				dbQuery($query);
				$p_id = dbOne('select last_insert_id()', 'last_insert_id()');
				dbQuery(
					"insert into page_vars 
					values($p_id, 'products_cat_id', '".(int)$cat."')"
				);
			}	
		}
	}
	for ($i=0; $i<count($names); ++$i) {
		$page 
			= dbOne(
				'select page_id 
				from page_vars 
				where value = '.(int)$categories[$i],
				'page_id'
			);
		if (!$page) {
			// { The parent should be before the relevant category in the array
			$parent 
				= dbOne(
					'select parent_id 
					from products_categories 
					where id = '.(int)$categories[$i],
					'parent_id'
				);
			$parentPage 
				= dbOne(
					'select page_id 
					from page_vars 
					where name like "products_cat%" and value ='.$parent,
					'page_id'
				);
			if (!$parentPage) {
				$parentPage = 0;
			}
			// }
			$query = 'insert into pages ';
			$query.= 'set name = "'.addslashes($names[$i]).'", ';
			$query.= 'type = "products", ';
			$query.= 'parent = "'.(int)$parentPage.'", ';
			$query.= 'cdate = now(), ';
			$query.= 'edate = now(), ';
			$query.= 'associated_date = now()';
			if (isset($_POST['hide_pages'])) {
				$query.= ', special=2';
			};
			dbQuery($query);
			$id = dbOne('select last_insert_id()', 'last_insert_id()');
			dbQuery(
				'insert into page_vars 
				values('.$id.', "products_what_to_show", 2)'
			);
			dbQuery(
				'insert into page_vars 
				values(
					"'.$id.'"
					,"products_category_to_show"
					,"'.$categories[$i].'"
				)'
			);
			dbQuery(
				'insert into page_vars
				values(
					"'.(int)$id.'",
					"products_type_to_show",
					0
				)'
			);
			dbQuery(
				'insert into page_vars
				values(
					"'.(int)$id.'",
					"products_product_to_show",
					0
				)'
			);
		}
	}
	dbQuery('delete from page_vars where name = "products_cat_id"');
}
/**
	* Deletes or changes pages of empty categories
	*
	* If a category is empty and has a page it checks if that page has subpages
	* if it does the page type is changed to table of contents.
	* If the page has no subpages it is deleted.
	*
	* @param int $id The category id
	*
	* @return void
	*
**/
function Products_Import_pruneCatPages($id) {
	if (!$id>0) {
		return;
	}
	$page_id
		= dbOne(
			'select page_id
			from page_vars
			where name= "products_category_to_show" and value='.(int)$id.
			' limit 1',
			'page_id'
		);
	if (!$page_id) {
		return;
	}
	$hasProducts 
		= dbOne(
			'select product_id 
			from products_categories_products
			where category_id = '.$id.
			' limit 1',
			'product_id'
		);
	if ($hasProducts) {
		return;
	}
	$subs = dbAll('select id from pages where parent ='.$page_id);
	foreach ($subs as $sub) {
		$cat 
			= dbOne(
				'select value from page_vars 
				where name = "products_category_to_show" 
				and page_id = '.$sub['id'],
				'value'
			);
		if ($cat) {
			Products_Import_pruneCatPages($cat);
		}
	}
	$has_subs 
		= dbOne(
			'select id 
			from pages 
			where parent= '.$page_id.
			' limit 1',
			'id'
		);
	if ($has_subs) {
		dbQuery('update pages set type = 9 where id = '.$page_id);
	}
	else {
		dbQuery('delete from pages where id = '.$page_id);
	}
	dbQuery('delete from page_vars where page_id = '.$page_id);
}
/**
	* Takes a list of categories and a page that contains a category that doesn't
	* exist. If the page has no subpages it will delete it. If it does have subpages
	* it changes its type to table of contents
	*
	* @param int   $page The id of the page we are checking
	* @param array $cats The list of existing categories
	*
	* @return void
	*
**/
function Products_Import_deletePagesForCatsThatDontExist($page, $cats) {
	$subs 
		= dbAll(
			'select id 
			from pages 
			where type = "products" and parent = '.$page
		);
	foreach ($subs as $sub) {
		$displaysCat 
			= dbOne(
				'select value 
				from page_vars where 
				name = "products_category_to_show" and page_id = '.$sub['id'],
				'value'
			);
		if ($displaysCat) {
			if (!in_array($displaysCat, $cats, false)) {
				Products_Import_deletePagesForCatsThatDontExist(
					$sub['id'], 
					$cats
				);
			}
		}
	}
	$has_subs 
		= dbOne(
			'select id from pages where parent = '.$page. ' limit 1',
			'id'
		);
	if ($has_subs) {
		dbQuery('update pages set type = 9 where id = '.$page);
	}
	else {
		dbQuery('delete from pages where id = '.$page);
	}
	dbQuery('delete from page_vars where id = '.$page);
}
