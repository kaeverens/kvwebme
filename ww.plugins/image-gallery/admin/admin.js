$(function(){
	$('#image-gallery-tabs').tabs();
	$('#image-gallery-wrapper').sortable({
		stop:function(event,ui){
			var querystring=$('#image-gallery-wrapper').sortable('serialize');
			$.post(
				'/ww.plugins/image-gallery/admin/reorder.php',
				querystring
			);
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
			$.get(
				'/ww.plugins/image-gallery/admin/types/'+tpl+'.tpl',
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
			$.post(
				'/ww.plugins/image-gallery/admin/delete-image.php',
				{'id':id}
			);
		}
	});
	$('.edit-img').live('click',function(){	
		var id=$(this).attr('id');
		var capt=$('#image-gallery-image'+id).attr('title');
		if(!capt) {
			capt='';
		}
		$('<div title="Caption">'
				+ 'Caption Text:'
				+ '<input type="text" id="caption-text" value="'+capt+'"/>'
			+ '</div>'
		).dialog({
			'modal':true,
			buttons:{
				'Save':function(){
					var caption=$('#caption-text').val();
					$.post(
						'/ww.plugins/image-gallery/admin/edit-caption.php',
						{
							'id':id,
							'caption':caption
						}
					);
					$('#image-gallery-image'+id).attr('title',caption);
					$(this).dialog('close').remove();
				},
				'Cancel':function(){
					$(this).dialog('close').remove();
				}
			}
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
					$.post(
						'/ww.plugins/image-gallery/admin/new-video.php',
						{ 'link':link,"id":id,"image":image }
					);
					if(image==''||image=='http://') {
						image='/ww.plugins/image-gallery/files/video.png';
					}
					var c='<li class="gallery-image-container" id="image_'+id+'">'
					+'<img src="/ww.plugins/image-gallery/get-image.php?uri='+image+',width=64,height=64"'
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
