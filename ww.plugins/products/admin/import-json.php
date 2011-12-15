<?php
/**
	* products import script - JSON variant
	*
	* PHP Version 5
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL Version 2
	* @link     http://kvweb.me/
	**/
if (isset($_POST['import'])) {
	if (isset($_FILES['file'])) {
		$file = $_FILES['file'];
		if (preg_match('/\.json/', $file['name'])) { // If it has the right extension
			$arr=json_decode(file_get_contents($file['tmp_name']));
			if (!is_null($arr)) {
				foreach ($arr as $table=>$rows) {
					switch ($table) {
						case 'products':
						case 'products_categories':
						case 'products_categories_products': // {
							dbQuery('delete from '.$table);
							foreach ($rows as $row) {
								$fields=array();
								foreach ($row as $field_name=>$field_value) {
									$fields[]='`'.addslashes($field_name).'`="'
										.addslashes($field_value).'"';
								}
								$sql='insert into '.$table.' set '.join(',', $fields);
								dbQuery($sql);
							}
							echo '<em>imported '.$table.'</em>';
						break; // }
						case 'products_images': // {
							foreach ($rows as $arr) {
								if (strpos($arr[1], 'http') !==0) { // hack attempt?
									continue;
								}
								$dirs=explode('/', $arr[0]);
								$filename=array_pop($dirs);
								$dirname=USERBASE.'f';
								foreach ($dirs as $dir) {
									$dirname.='/'.$dir;
									if ($dir=='..') { // hack attempt?
										continue;
									}
									if (!file_exists($dirname)) {
										mkdir($dirname);
									}
								}
								if (!file_exists($dirname.'/'.$filename)
									|| !filesize($dirname.'/'.$filename)
								) {
									$url=str_replace(' ', '%20', $arr[1]);
									file_put_contents(
										$dirname.'/'.$filename,
										file_get_contents($url)
									);
								}
							}
							echo '<em>images imported</em>';
						break; // }
						default:
							echo '<em>unknown table '.$table.'</em>';
					}
				}
			}
		}
	}
	Core_cacheClear('products');
}
// { display form
echo '<form method="post" enctype="multipart/form-data">'
	.'<input type="file" name="file" />'
	.'<input type="submit" name="import" value="Import Data" /></form>'
	'<p>This form imports data in JSON format. For example, a '
	.'products export file that has been prepared by the Wordpress WPSC '
	.'export tool.</p>'
	.'<p>If you don\'t know what that is, you are not in the right place ;-)</p>';
// }
