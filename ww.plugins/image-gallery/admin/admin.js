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
			$('#p-description').val(ret.description).ckeditor(CKEditor_config);
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
					var id=$('input[name=id]').val();
					$.post('/a/p=image-gallery/f=adminAddVideo',
						{ 'link':link,"id":id,"image":image }
					);
					if(image==''||image=='http://') {
						image='/ww.plugins/image-gallery/files/video.png';
					}
					var c='<li id="image_'+id+'">'
					+'<img src="/a/f=getImg/w=64/h=64/'+image+'"'
					+' id="image-gallery-image'+id+'"/><br/>'
					+'<a href="javascript:;" class="delete-img" id="'+id+'">'
					+'[x]</a></li>';
					$('#image-gallery-wrapper').append(c);
					$(this).dialog('close').remove();
				},
				Cancel:function(){
					$(this).dialog('close').remove();
				}
			}
		});
	});
	$('#val-image_gallery_frame')
		.change(function() {
			var val=$(this).val();
			$('#image-gallery-frame-custom').find('input').attr('disabled', true);
			if (val=='--custom--') {
				$('#image-gallery-frame-custom').find('input').attr('disabled', false);
			}
			frameDemoUpdate();
		})
		.change();
	$('#image-gallery-frame-custom .border').change(function() {
		var $inps=$('#image-gallery-frame-custom .border'),vals=[];
		$inps.each(function() {
			vals.push(+$(this).val());
		});
		$('input[name="page_vars[image_gallery_frame_custom_border]"]')
			.val(vals.join(' '));
		frameDemoUpdate();
	});
	$('#image-gallery-frame-custom .padding').change(function() {
		var $inps=$('#image-gallery-frame-custom .padding'),vals=[];
		$inps.each(function() {
			vals.push(+$(this).val());
		});
		$('input[name="page_vars[image_gallery_frame_custom_padding]"]')
			.val(vals.join(' '));
		frameDemoUpdate();
	});
	Core_uploader('#uploader', {
		'serverScript': '/ww.plugins/image-gallery/admin/upload.php',
		'successHandler':function(file, data, response){
			$.post("/ww.plugins/image-gallery/admin/new-files.php", {
				"gallery_id":window.page_menu_currentpage,
				"id":data
			}, function(html) {
				$("#image-gallery-wrapper")
					.append(html)
					.find('.error').remove();
			});
		},
		'postData': {
			'gallery_id':window.page_menu_currentpage
		}
	});
	Core_uploader('#frame-uploader', {
		'serverScript': '/a/p=image-gallery/f=adminFrameUpload',
		'postData': {
			'id':window.page_menu_currentpage
		}
	});
	function frameDemoUpdate() {
		var frame=$('#val-image_gallery_frame').val(),
			border=$('input[name="page_vars[image_gallery_frame_custom_border]"]').val(),
			padding=$('input[name="page_vars[image_gallery_frame_custom_padding]"]').val();
		// { padding
		var paddings=padding.split(' ');
		for (var i=0;i<4;++i) {
			paddings[i]=+(paddings[i]||0);
		}
		padding=paddings.join(' ');
		// }
		// { border
		var borders=border.split(' ');
		for (var i=0;i<4;++i) {
			borders[i]=+(borders[i]||0);
		}
		border=borders.join(' ');
		// }
		var $fd1=$('#fd1'), $fd2=$('#fd2'), url;
		// { frame demo 1
		if (frame=='') {
			url='/i/blank.gif';
		}
		else {
			url='/a/p=image-gallery/f=frameGet/w=350/h=350/pa='+padding+'/bo='+border
				+'/image-galleries/frame-'+window.page_menu_currentpage+'.png';
		}
		$fd1.css({
			'width':350+paddings[1]+paddings[3],
			'height':350+paddings[0]+paddings[2],
			'backgroundPosition':paddings[3]+' '+paddings[0]
		})
			.attr('src', url);
		// }
		// { frame demo 2
		var ratio=350/150;
		if (frame=='') {
			url='/i/blank.gif';
		}
		else {
			url='/a/p=image-gallery/f=frameGet/w=150/h=150/pa='+padding+'/bo='+border
				+'/image-galleries/frame-'+window.page_menu_currentpage+'.png/ratio='+ratio;
		}
		$fd2.css({
			'width':150+(paddings[1]+paddings[3])/ratio,
			'height':150+(paddings[0]+paddings[2])/ratio,
			'backgroundPosition':(paddings[3]/ratio)+' '+(paddings[0]/ratio)
		})
			.attr('src', url);
		// }
	}
});
