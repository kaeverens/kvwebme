$(function(){
	function save(){
		$.post('/ww.plugins/products/admin/save-category-attrs.php?id='+window.selected_cat,{
			"name"   :$('#pc_edit_name').val(),
			"enabled":$('#pc_edit_enabled').val(),
			"associated_colour" :$('#pc_colour').val().replace(/#/,'')
		},'json');
	}
	function show_attributes(ret){
		window.selected_cat=ret.attrs.id;
		var coloursave=false;
		ret.attrs.colour=ret.attrs.associated_colour;
		if (!ret.attrs.colour || ret.attrs.colour.length!=6) {
			ret.attrs.colour='ffffff';
		}
		// { Remove the links so that they don't get added twice
		$('#create_link,#frontend_link').remove();
		// }
		var table=$('#products-categories-attrs>table');
		if(!table.length){
			table='<table id="attrs_table" style="width:100%">'
				+'<tr><th>Name</th><td><input id="pc_edit_name" /></td></tr>'
				+'<tr><th>Enabled</th><td><select id="pc_edit_enabled"><option value="1">Yes</option><option value="0">No</option></td></tr>';
			// { products
			table+='<tr id="products"><th>Products</th><td><form><select style="display:none" name="pc_edit_products[]" id="pc_edit_products" multiple="multiple">';
			for(var i=0;i<window.product_names.length;++i){
				table+='<option value="'+window.product_names[i][1]+'">'+window.product_names[i][0]+'</option>';
			}
			table+='</select></form></td></tr>';
			// }
			// { colour
			table+='<tr id="colour"><th>Colour</th>'
				+'<td><input id="pc_colour" />'
				+'<div id="colour-picker"></div>'
				+'</td></tr>';
			// }
			// { delete
			table+='<tr><th>Delete</th><td><a href="javascript:;" class="delete">[x]</a></td></tr>';
			// }
			table+='</table>';
			table=$(table).appendTo('#products-categories-attrs');
			$('#colour-picker').farbtastic(function(colour){
				$('#colour input').val(colour);
				if (coloursave) {
					save();
				}
			});
			$('#pc_edit_products').inlinemultiselect({
				"endSeparator":", ",
				"onClose":function(){
					var selected=[];
					$('#pc_edit_products input:checked').each(function(i, opt){
				    selected.push($(opt).val());
					});
					$.post('/ww.plugins/products/admin/save-category-products.php?id='+window.selected_cat,{
						"s[]":selected
					},show_attributes,'json');
				}
			});
		}
		$('#cat_'+ret.attrs.id+'>a').text(ret.attrs.name);
		$('#pc_edit_name').val(ret.attrs.name);
		$.farbtastic('#colour-picker')
			.setColor('#'+ret.attrs.colour);
		coloursave=true;
		$('#cat_'+ret.attrs.id+' a').removeClass('disabled');
		if (ret.page==null) {
			$(
				'<tr id="create_link"><th>Link</th>'+
				'<td><a href="javascript:;" id="page_create_link"'+
				'onClick='+
				'"createPopup(\''+ret.attrs.name+'\', '+ret.attrs.id+', 2);"'
				+'>Create a page for this category</a></td></tr>'
			).insertAfter($('#colour'));
		}
		if (ret.page!=null) {
			$(
				'<tr id="frontend_link"><th>Link</th>'+
				'<td><a href="'+ret.page+'" target=_blank>'+
				'View this category on the frontend</a></td></tr>'
			).insertAfter('#products');
		}
		$('#pc_edit_enabled').val(ret.attrs.enabled);
		var selected_names=[];
		$('#pc_edit_products input').each(function(i,opt){
			opt.checked=false;
			for(var i=0;i<ret.products.length;++i){
				if(opt.value==ret.products[i]){
					opt.checked='checked';
					selected_names.push($(opt.parentNode).text());
				}
			}
		});
		$('#pc_edit_productschoices').text(selected_names.join(', '));
	}
	$('#categories-wrapper').jstree({
		selected:'cat_'+window.selected_cat,
		types:{
			"default":{
				icon:{
					image: false
				}
			}
		},
		callback:{
			"onmove":function(node){
				var p=$.jstree._focused().parent(node);
				$.getJSON('/ww.plugins/products/admin/move-category.php?id='+node.id.replace(/.*_/,'')+'&parent_id='+(p==-1?0:p[0].id.replace(/.*_/,'')),show_attributes);
			}
		}
	});
	var div=$('<div style="clear:both;padding-top:20px;" />');
	$('<button>add sub-category</button>')
		.click(function(){
			var name=prompt('what do you want to name this sub-category?');
			if(!name)return;
			$.getJSON('/ww.plugins/products/admin/add-new-category.php',{
				"parent_id":$.jstree._focused().get_selected()[0].id.replace(/.*_/,''),
				"name":name
			},function(){
				document.location=document.location;
			});
		})
		.appendTo(div);
	$('<button>add main category</button>')
		.click(function(){
			var name=prompt('what do you want to name this category?');
			if(!name)return;
			$.getJSON('/ww.plugins/products/admin/add-new-category.php',{
				"parent_id":0,
				"name":name
			},function(){
				document.location=document.location;
			});
		})
		.appendTo(div);
	div.insertAfter('#categories-wrapper');
	$.getJSON('/ww.plugins/products/admin/get-category-attrs.php?id='+window.selected_cat,show_attributes);
	$('#pc_edit_name, #pc_edit_enabled, #pc_colour').live('change', save);
	$('#products-categories-attrs>table .delete').live('click',function(){
		if(!confirm("Are you sure you want to delete this category?"))return;
		$.getJSON('/ww.plugins/products/admin/delete-category.php?id='+window.selected_cat,function(){
			document.location="/ww.admin/plugin.php?_plugin=products&_page=categories";
		});
	});
	$('#categories-wrapper li>a').live('click', function(){
		$.getJSON('/ww.plugins/products/admin/get-category-attrs.php?id='+$(this).closest('li')[0].id.replace(/.*_/,''),show_attributes);
	});
});
