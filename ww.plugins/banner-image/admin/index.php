<?php
/**
	* WebME Banner-Image plugin
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Conor Mac Aoidh <conor@mcaoidh.name>
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

// { BannerImage_selectKiddies

/**
	* blah2
	*
	* @param int    $i      whatever
	* @param int    $n      whatever2
	* @param array  $s      whatever3
	* @param int    $id     whatever4
	* @param string $prefix whatever5
	*
	* @return null
	*/
function BannerImage_selectKiddies(
	$i=0, $n=1, $s=array(), $id=0, $prefix=''
) {
	$q=dbAll(
		'select name,id from pages where parent="'.$i.'" and id!="'.$id.'" orde'
		.'r by ord,name'
	);
	if (count($q)<1) {
		return;
	}
	foreach ($q as $r) {
		if ($r['id']!='') {
			echo '<option value="'.$r['id'].'" title="'
				.htmlspecialchars($r['name']).'"';
			echo in_array($r['id'], $s)?' selected="selected">':'>';
			$name=strtolower(str_replace(' ', '-', $r['name']));
			echo htmlspecialchars($prefix.$name).'</option>';
			BannerImage_selectKiddies($r['id'], $n+1, $s, $id, $name.'/');
		}
	}
}

// }
// { BannerImage_drawForm

/**
	* blah
	*
	* @param int $id id of the banner
	*
	* @return null
	*/
function BannerImage_drawForm($id=0) {
	if (!($id)) {
		$fdata=array('id'=>0, 'html'=>'', 'name'=>'banner');
	}
	else {
		$fdata=dbRow("select * from banners_images where id=$id");
	}
	echo '<form method="post" action="/ww.admin/plugin.php?_plugin=banner-ima'
		.'ge&amp;_page=index" enctype="multipart/form-data"><input type="hidden'
		.'" name="id" value="'.(int)$fdata['id'].'" />';
	echo '<table>';
	// {
	echo '<tr><th>Name</th><td><input name="name" value="'
		.htmlspecialchars($fdata['name']).'" /></td></tr>';
	// }
	// { what pages should this be applied to
	echo '<tr><th>Pages</th><td>This banner will only be shown on the <select'
		.' name="pages_'.$fdata['id'].'[]" multiple="multiple" style="max-width'
		.':200px;height:500px">';
	$ps=dbAll('select * from banners_pages where bannerid='.$fdata['id']);
	$pages=array();
	if (count($ps)) {
		foreach ($ps as $p) {
			$pages[]=$p['pageid'];
		}
	}
	BannerImage_selectKiddies(0, 1, $pages);
	echo '</select> pages. <span style="color:red;font-weight:bold">If no pag'
		.'es are specified, then the banner will be shown on all pages.</span><'
		.'/td></tr>';
	// }
	// { show HTML form
	echo '<tr><th>Banner</th><td><div id="banner_image_html">'
		.ckeditor(
			'html_'.$fdata['id'],
			Core_unfixImageResizes($fdata['html']),
			0,
			'',
			180
		)
		.'</div></td></tr>';
	// }
	// { show submit button and end form
	echo '<tr><td><a href="./plugin.php?_plugin=banner-image&_page=index&dele'
		.'te_banner='.$fdata['id'].'" onclick="return confirm(\'are you sure yo'
		.'u want to remove this banner?\');" title="remove banner">[x]</a></td>'
		.'<td><input type="submit" name="save_banner" value="Update" /></td></tr>';
	// }
	echo '</table></form>';
}

// }

$id=0;
if (isset($_GET['delete_banner']) && (int)$_GET['delete_banner']) {
	$id=(int)$_GET['delete_banner'];
	dbQuery("delete from banners_images where id=$id");
	dbQuery("delete from banners_pages where bannerid=$id");
	unlink(USERBASE.'/f/skin_files/banner-image/'.$id.'.png');
	$n=USERBASE.'/f/skin_files/banner-image/'.$id.'_*';
	CoreDirectory::delete($n);
	$updated='Banner Deleted';
	Core_cacheClear('banner-images');
}
if (isset($_POST['save_banner'])) {
	$id=(int)$_POST['id'];
	$pages=@$_POST['pages_'.$id];
	$html=Core_sanitiseHtml($_POST['html_'.$id]);
	$sql='set html="'.addslashes($html).'",name="'.addslashes($_POST['name'])
		.'",pages='.(count($pages)?1:0);
	if ($id) {
		dbQuery("update banners_images $sql where id=$id");
	}
	else {
		dbQuery("insert into banners_images $sql");
		$id=dbOne('select last_insert_id() as id', 'id');
		$_REQUEST['id']=$id;
	}
	dbQuery("delete from banners_pages where bannerid=$id");
	if (is_array($pages)) {
		foreach ($pages as $k=>$v) {
			dbQuery(
				'insert into banners_pages set pageid='.((int)$v).",bannerid=$id"
			);
		}
	}
	$updated='Banner Saved';
	Core_cacheClear('banner-images');
}

if (isset($updated)) {
	echo '<em>'.$updated.'</em>';
}
if (!is_dir(USERBASE.'/f/skin_files')) {
	mkdir(USERBASE.'/f/skin_files');
}
if (!is_dir(USERBASE.'/f/skin_files/banner-image')) {
	mkdir(USERBASE.'/f/skin_files/banner-image');
}

// { show left menu
echo '<div class="sub-nav">';
$rs=dbAll('select id,name from banners_images');
foreach ($rs as $r) {
	echo '<a href="/ww.admin/plugin.php?_plugin=banner-image&id='.$r['id']
		.'&amp;_page=index">'.htmlspecialchars($r['name']).'</a>';
}
echo '<a href="/ww.admin/plugin.php?_plugin=banner-image&amp;_page=index" c'
	.'lass="new">New Banner</a>';
echo '</div>';
// }

if (isset($_REQUEST['id'])) {
	$id=(int)$_REQUEST['id'];
}

BannerImage_drawForm($id);
echo '<script src="http://inlinemultiselect.googlecode.com/files/jquery.inl'
	.'inemultiselect.min.js"></script>';
echo '<script src="/ww.plugins/banner-image/j/admin.js"></script>';
echo '<link rel="stylesheet" type="text/css" href="/ww.plugins/banner-image'
	.'/c/styles.css" />';
