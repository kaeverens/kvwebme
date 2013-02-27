<?php

// { ClassifiedAds_categoryTypesGet

/**
	* get prices for ads
	*
	* @return array
	*/
function ClassifiedAds_categoryTypesGet() {
	return dbAll('select * from classifiedads_types order by name');
}

// }
// { ClassifiedAds_categoriesGetAll

/**
	* get list of categories
	*
	* @return array
	*/
function ClassifiedAds_categoriesGetAll() {
	return dbAll('select * from classifiedads_categories order by name');
}

// }
// { ClassifiedAds_fileUpload

/**
	* upload a file
	*
	* @return status
	*/
function ClassifiedAds_fileUpload() {
	$id=isset($_SESSION['userdata']['id'])
		?$_SESSION['userdata']['id']
		:$_SESSION['tmpUID'];
	$fname=USERBASE.'/f/userfiles/'.$id.'/classified-ads-upload/'.$_FILES['Filedata']['name'];
	if (strpos($fname, '..')!==false) {
		return array('message'=>'invalid file url');
	}
	@mkdir(dirname($fname), 0777, true);
	$from=$_FILES['Filedata']['tmp_name'];
	$dir=new DirectoryIterator(USERBASE.'/f/userfiles/'.$id.'/classified-ads-upload');
	foreach ($dir as $file) {
		if ($file->isDot()) {
			continue;
		}
		unlink(USERBASE.'/f/userfiles/'.$id.'/classified-ads-upload/'.$file->getFilename());
	}
	move_uploaded_file($from, $fname);
	return array('ok'=>1);
}

// }
