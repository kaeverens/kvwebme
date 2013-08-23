$(document).ready(function(){
	$('select[name=type]').change(function(){
		var id=this.id.replace(/type_/,'');
		switch(this.value){
			case '0': // { image
				$('#banner_image_img_'+id).css({'display':'block'});
				$('#banner_image_html_'+id).css({'display':'none'});
				break;
			// }
			default: // { html
				$('#banner_image_img_'+id).css({'display':'none'});
				$('#banner_image_html_'+id).css({'display':'block'});
				break;
			// }
		}
	});
	$('select').inlinemultiselect({
		'separator':', ',
		'endSeparator':' and '
	});
});
