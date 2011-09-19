$(function(){
	$('#image-gallery-tabs').tabs();
	$('#image-gallery-wrapper').sortable({
		stop:function(event,ui){
			var querystring=$('#image-gallery-wrapper').sortable('serialize');
			$.post('/ww.plugins/image-gallery/admin/reorder.php', querystring);
		},
		placeholder:'image-gallery-highlight'
	});
	var previous;
	$('#gallery-template-type').focus(function(){
		previous=this.value;
	}).change(function(){
		var content;
		if(previous=='custom')
			$(this).data('custom',{'value':CKEDITOR.instances['page_vars[gallery-template]'].getData()});
		var tpl=$(this).val();
		if(tpl=='custom'){
			content=($(this).data('custom'))?
				$(this).data('custom').value:
				'';
			CKEDITOR.instances['page_vars[gallery-template]'].setData(content);
		}
		else{
			$.get('/ww.plugins/image-gallery/admin/types/'+tpl+'.tpl',
				function(html){
					CKEDITOR.instances['page_vars[gallery-template]'].setData(html);
				}
			);
		}
	});
	$('.delete-img').live('click',function(){
		var id=$(this).attr('id');
		var result=confirm('Are you sure you wish to delete this image?');
		if(result){
			$(this).parent().fadeOut('slow').remove();
			$.post('/ww.plugins/image-gallery/admin/delete-image.php', {'id':id});
		}
	});
	$('.edit-img').live('click',function(){	
		var id=$(this).attr('id');
		$('<div id="p-dialog" title="Image Properties"><table>'
			+'<tr><th>Caption</th><td style="width:60%"><input id="p-caption"/></td>'
			+'<th>Author</th><td><input id="p-author"/></td></tr>'
			+'<tr><th>Description</th><td colspan="3"><textarea id="p-description"/></td></tr>'
			+'</table></div>'
		).dialog({
			'modal'  : true,
			'width'  : 700,
			'height' : 450,
			'buttons': {
				'Save':function(){
					var caption=$('#p-caption').val();
					$.post('/a/p=image-gallery/f=adminDetailsEdit', {
						'id':id,
						'caption':caption,
						'description':$('#p-description').val(),
						'author':$('#p-author').val()
					});
					$('#image-gallery-image'+id).attr('title',caption);
					$(this).dialog('close').remove();
				}
			},
			'close':function(){
				$('#p-dialog').remove();
			}
		});
		var $img=$('#image-gallery-image'+id);
		$.post('/a/p=image-gallery/f=adminDetailsGet/id='+id, function(ret) {
			if (ret.author==undefined) {
				ret.author='';
			}
			if (ret.caption==undefined) {
				ret.caption='';
			}
			if (ret.description==undefined) {
				ret.description='';
			}
			$('#p-caption').val(ret.caption);
			if (CKEDITOR.instances['p-description']) {
				CKEDITOR.remove(CKEDITOR.instances['p-description']);
			}
			$('#p-description').val(ret.description).ckeditor();
			$('#p-author').val(ret.author);
		});
	});
	$('#video').click(function(){
		$(
			'<div title="Add Video File">'
			+ 'Link: '
			+ '<input type="text" id="link" value="http://"/><br/>'
			+ 'Thumbnail: '
			+ '<input type="text" id="image" value="http://"/>'
			+ '</div>'
		).dialog({
			modal:true,
			buttons:{
				Save:function(){
					var link=$('#link').val();
					var image=$('#image').val();
					var id=$('#id').val();
					$.post('/ww.plugins/image-gallery/admin/new-video.php',
						{ 'link':link,"id":id,"image":image }
					);
					if(image==''||image=='http://') {
						image='/ww.plugins/image-gallery/files/video.png';
					}
					var c='<li id="image_'+id+'">'
					+'<img src="/a/f=getImg/w=64/h=64/'+image+'"'
					+' id="image-gallery-image'+id+'"/><br/>'
					+'<a href="javascript:;" class="delete-img" id="'+id+'">'
					+'Delete</a><br/></li>';
					$('#image-gallery-wrapper').append(c);
					$(this).dialog('close').remove();
				},
				Cancel:function(){
					$(this).dialog('close').remove();
				}
			}
		});
	});
});
