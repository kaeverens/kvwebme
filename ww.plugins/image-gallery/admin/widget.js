if(!ww.image_gallery)ww.image_gallery={
	selected_dir:'/'
};
function image_gallery_file_manager(){
	window.open('/j/kfm/?startup_folder='+$('#image_gallery_folder').val(),'kfm','modal,width=800,height=600');
}
function image_gallery_edit(ev){
	var el=ev.target;
	var id=el.id.replace(/image_gallery_editlink_/,'');
	var gallery_types=["List","Grid","Simple"];
	var d=$('<table id="image_gallery_form"><tr><th>Folder holding the images: </th><td><select id="image_gallery_folder"></select></td></tr><tr><th>Manage images in the folder</th><td><a href="javascript:image_gallery_file_manager()">manage images</a></td></tr><tr><th>Gallery Type</th><td><select id="gallery_type"><option>'+gallery_types.join('</option><option>')+'</select></td></tr><tr><th>Thumbnail Size:</th><td><input class="small" id="image_gallery_thumbnail"/></td></tr><tr><th>Main Image Size:</th><td><input class="small" id="image_gallery_size"></th></tr><tr><th>Rows:</th><td><input class="small" id="image_gallery_rows"></th></tr><tr><th>Columns:</th><td><input class="small" id="image_gallery_columns"></th></tr></table>');
	$.getJSON('/ww.plugins/image-gallery/admin/widget-form.php',{'get_image_gallery':id},function(res){
		d.dialog({
			minWidth:630,
			minHeight:400,
			height:400,
			width:630,
			close:function(){
				d.dialog('destroy');
				$('#image_gallery_form').remove();
			},
			modal:true,
			buttons:{
				'Save':function(){
					$.post('/ww.plugins/image-gallery/admin/widget-form.php',
						{
							'id':id,
							'action':'save',
							'directory':$('#image_gallery_folder').val(),
							'gallery_type':$('#gallery_type').val(),
							'thumbsize':+$('#image_gallery_thumbnail').val(),
							'image_size':+$('#image_gallery_size').val(),
							'rows':+$('#image_gallery_rows').val(),
							'columns':+$('#image_gallery_columns').val()
						},
						function(ret){
							if(ret.id!=ret.was_id){
								el.id='image_gallery_editlink_'+ret.id;
							}
							id=ret.id;
							var w=$(el).closest('.widget-wrapper');
							var wd=w.data('widget');
							wd.id=id;
							w.data('widget',wd);
							updateWidgets(w.closest('.panel-wrapper'));
							d.dialog('close');
						}
					,'json');
				},
				'Close':function(){
					d.dialog('close');
				}
			}
		});
		var sel=$('#image_gallery_folder'),i,dir;
		for(i=0;i<res.directories.length;++i){
			dir=res.directories[i];
			$('<option></option>').text(dir).attr('value',dir).appendTo(sel);
		}
		sel.val(res.data.directory);
		$('#gallery_type').val(res.data.gallery_type);
		$('#image_gallery_thumbnail').val(res.data.thumbsize);
		$('#image_gallery_size').val(res.data.image_size);
		$('#image_gallery_rows').val(res.data.rows);
		$('#image_gallery_columns').val(res.data.columns);
	});
}
$('.image_gallery_editlink').live('click',image_gallery_edit);
