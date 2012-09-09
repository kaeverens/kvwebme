<?php
require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if (!Core_isAdmin()) {
	die(__('access denied'));
}
if (isset($_REQUEST['get_banner'])) {
	require '../frontend/index.php';
	$o=new stdClass();
	$o->id=(int)$_REQUEST['get_banner'];
	$ret=array('content'=>show_banner($o));
	echo json_encode($ret);
	Core_quit();
}
if (@$_REQUEST['action']=='save') {
	$id=(int)$_REQUEST['id'];
	$id_was=$id;
	$html=addslashes($_REQUEST['html']);
	$sql="banners set html='$html'";
	if ($id) {
		$sql="update $sql where id=$id";
		dbQuery($sql);
	}
	else {
		$sql="insert into $sql";
		dbQuery($sql);
		$id=dbOne('select last_insert_id() as id', 'id');
	}
	$ret=array('id'=>$id,'id_was'=>$id_was);
	echo json_encode($ret);
	Core_cacheClear('banner-images');
	Core_quit();
}

if (isset($_REQUEST['id'])) {
	$id=(int)$_REQUEST['id'];
}
else {
	$id=0;
}
echo '<a href="javascript:;" id="banner_editlink_'.$id.'" class="banner_edi'
	.'tlink">'.__('view or edit snippet').'</a>';
if ($id) {
	echo '<div id="banner_preview_'.$id.'"></div>';
}
?>
<script>
if(!ww.banner)ww.banner={
	editor_instances:0
};
function banner_edit(ev) {
	var el=ev.target;
	var id=el.id.replace(/banner_editlink_/,'');
	ww.banner.editor_instances++;
	var d=$(
		'<div><textarea style="width:600px;height:300px;" id="banner_html'
		+ww.banner.editor_instances+'" name="banner_html'
		+ww.banner.editor_instances+'"></textarea></div>'
	);
	$.getJSON('/ww.plugins/banner-image/admin/widget-form.php',{
		'get_banner':id
	}, function(res){
		d.dialog({
			minWidth:630,
			minHeight:400,
			height:400,
			width:630,
			beforeclose:function(){
				if(!ww.banner.rte)return;
				ww.banner.rte.destroy();
				ww.banner.rte=null;
			},
			buttons:{
				'<?php echo __('Save'); ?>':function(){
					var html=ww.banner.rte.getData();
					$.post('/ww.plugins/banner-image/admin/widget-form.php', {
						'id':id,'action':'save','html':html
					}, function(ret){
						if(ret.id!=ret.was_id){
							el.id='banner_editlink_'+ret.id;
						}
						id=ret.id;
						var w=$(el).closest('.widget-wrapper');
						var wd=w.data('widget');
						wd.id=id;
						w.data('widget',wd);
						updateWidgets(w.closest('.panel-wrapper'));
						d.dialog('close');
					},'json');
				},
				'<?php echo __('Close'); ?>':function(){
					d.dialog('close');
				}
			}
		});
		ww.banner.rte=CKEDITOR.replace(
			'banner_html'+ww.banner.editor_instances,
			CKEditor_config
		);
		ww.banner.rte.setData(res.content);
	});
}
$('.banner_editlink').each(function(){
	if(this.content_click_added)return;
	$(this).click(banner_edit);
	this.content_click_added=true;
})
<?php
if ($id) {
	echo '$("#banner_preview_'.$id.'").load("/ww.plugins/banner-image/admin/g'
		.'et_text_preview.php?id='.$id.'")';
}
?>
</script>
