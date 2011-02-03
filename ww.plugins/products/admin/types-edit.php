<?php
if(!is_admin())exit;
// { set up initial variables
if(isset($_REQUEST['id']) && is_numeric($_REQUEST['id']))$id=(int)$_REQUEST['id'];
else $id=0;
// }

if(isset($_REQUEST['action']) && $_REQUEST['action']='save'){
	cache_clear('products');
	$errors=array();
	if(!isset($_REQUEST['name']) || $_REQUEST['name']=='') {
		$errors[]='You must fill in the <strong>Name</strong>.';
	}
	if(count($errors)){
		echo '<em>'.join('<br />',$errors).'</em>';
	}
	else{
		$data_fields = $_REQUEST['data_fields'];
		$data_fields=str_replace(array("\n","\r"),array('\n',''),$data_fields);
		$singleview = sanitise_html($_REQUEST['singleview_template']);
		if (strlen($singleview)<20) {
			$singleview = '{{PRODUCTS_DATATABLE}}'.$singleview;
		}
		$multiview = $_REQUEST['multiview_template'];
		$multiview_header = $_REQUEST['multiview_template_header'];
		$multiview_footer = $_REQUEST['multiview_template_footer'];
		if (strlen($multiview)<20) {
			$multiview = '{{PRODUCTS_DATATABLE align=horizontal}}';
			$multiview.= '<a href="{{PRODUCTS_LINK}}">more</a>';
		}
		$singleview=str_replace('&quot;', '"', $singleview);
		$multiview=str_replace('&quot;', '"', $multiview);
		$multiview_header=str_replace('&quot;', '"', $multiview_header);
		$multiview_footer=str_replace('&quot;', '"', $multiview_footer);
		$sql='set name="'.addslashes($_REQUEST['name'])
			.'",data_fields="'.addslashes($data_fields)
			.'",multiview_template="'.addslashes($multiview)
			.'",multiview_template_header="'.addslashes($multiview_header)
			.'",multiview_template_footer="'.addslashes($multiview_footer)
			.'",singleview_template="'.addslashes($singleview).'"';
		if (isset($_POST['is_for_sale'])) {
			$sql.=',is_for_sale=1';
		}
		if($id){
			dbQuery("update products_types $sql where id=$id");
		}
		else{
			dbQuery("insert into products_types $sql");
			$id=dbOne('select last_insert_id() as id','id');
		}
		if(isset($_FILES['image_not_found'])){
			if (!file_exists(USERBASE.'f/products/types/'.$id)) {
				mkdir(USERBASE.'f/products/types/'.$id,0777,true);
			}
			$imgs=new DirectoryIterator(USERBASE.'f/products/types/'.$id);
			foreach ($imgs as $img) {
				if ($img->isDot()) {
					continue;
				}
				unlink($img->getPathname());
			}
			$from=$_FILES['image_not_found']['tmp_name'];
			$to=USERBASE.'f/products/types/'.$id.'/image-not-found.png';
		}
		echo '<em>Product Type saved</em>';
		cache_clear('products/templates');
	}
}

if($id){
	$tdata=dbRow("select * from products_types where id=$id");
	if(!$tdata)die('<em>No product type with that ID exists.</em>');
}
else{
	if (isset($_REQUEST['from'])) {
		$tdata=dbRow("select * from products_types where id=".(int)$_REQUEST['from']);
		$tdata['id']=0;
		$tdata['name']='';
	}
	else {
		$tdata=array(
			'id'=>0,
			'name'=>'',
			'show_product_variants'=>0,
			'show_related_products'=>0,
			'show_contained_products'=>0,
			'show_countries'=>0,
			'data_fields'=>'',
			'is_for_sale'=>0,
			'multiview_template'=>'',
			'singleview_template'=>''
		);
	}
}
echo '<form action="'.$_url.'&amp;id='.$id.'" method="POST" '
	.'enctype="multipart/form-data">';
echo '<input type="hidden" name="action" value="save" />';
echo '<div class="tabs">'
	.'<ul>'
	.'<li><a href="#main-details">Main Details</a></li>'
	.'<li><a href="#data-fields">Data Fields</a></li>'
	.'<li><a href="#multiview-template">Multi-View Template</a></li>'
	.'<li><a href="#singleview-template">Single-View Template</a></li></ul>';
// { main details
echo '<div id="main-details"><table>';
// { name
echo '<tr><th>Name</th><td><input class="not-empty" name="name" value="'
	.htmlspecialchars($tdata['name']).'" /></td>';
if (isset($PLUGINS['online-store'])) {
	echo '<th>Are products of this type for sale online?</th>';
	echo '<td><input name="is_for_sale" type="checkbox"';
	if ($tdata['is_for_sale']) {
		echo ' checked="checked"';
	}
	echo ' /></td>';
}
echo '</tr>';
// }
// { management tabs, image not found
echo '<tr>';
// { image not found
echo '<th>image-not-found</th><td><input type="file" name="image_not_found" />';
if($id){
	if(!file_exists(USERBASE.'f/products/types/'.$id.'/image-not-found.png')){
		if (!file_exists(USERBASE.'f/products/types/'.$id)) {
			mkdir(USERBASE.'f/products/types/'.$id,0777,true);
		}
		copy(
			dirname(__FILE__).'/../i/not-found-250.png',
			USERBASE.'f/products/types/'.$id.'/image-not-found.png'
		);
	}
	echo '<img src="/kfmgetfull/products/types/'.$id
		.'/image-not-found.png,width=64,height=64" />';
}
echo '</td>';
// }
echo '</tr>';
// }
echo '</table></div>';
// }
// { data fields
echo '<div id="data-fields">'
	.'<p>Create the data fields of your product type here. '
	.'Examples: colour, size, weight, description.</p>';
$dataFields = $tdata['data_fields'];
echo '<textarea name="data_fields" id="data_fields">'
	.htmlspecialchars(str_replace(array("\n","\r"),array('\n','\r'),$dataFields))
	.'</textarea>';
echo '</div>';
// }
// { multi-view template
echo '<div id="multiview-template">'
	.'<p>This template is for how the product looks when it is in a list '
	.'of products. Leave this blank to have one auto-generated when needed.</p>'
	.'<div class="tabs"><ul>'
	.'<li><a href="#mv-body">Body</a></li><li><a href="#mv-header">Header</a></li>'
	.'<li><a href="#mv-footer">Footer</a></li></ul>'
	.'<div id="mv-body">'
	.ckeditor('multiview_template', $tdata['multiview_template'])
	.'</div><div id="mv-header">'
	.ckeditor('multiview_template_header', $tdata['multiview_template_header'])
	.'</div><div id="mv-footer">'
	.ckeditor('multiview_template_footer', $tdata['multiview_template_footer'])
	.'</div></div>';
// }
// { single-view template
echo '<div id="singleview-template">'
	.'<p>This template is for how the product looks when shown on its own. '
	.'Leave this blank to have one auto-generated when needed.</p>';
echo ckeditor('singleview_template',$tdata['singleview_template']);
echo '</div>';
// }
echo '</div><input type="submit" value="Save" /></form>';
echo '<script src="/ww.plugins/products/admin/types-edit.js"></script>';
