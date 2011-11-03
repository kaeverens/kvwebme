$(function(){
	$.jstree._themes='/j/jstree/themes/';
	function save(){
		$.post('/a/p=products/f=adminCategoryEdit/id='+window.selected_cat, {
			"name"   :$('#pc_edit_name').val(),
			"enabled":$('#pc_edit_enabled').val(),
			"associated_colour" :$('#pc_colour').val().replace(/#/,'')
		});
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
			// { icon
			table+='<tr id="icon"><th>Icon</th>'
				+'<td><div id="icon-image"/><input type="file" id="uploader"/></td>'
				+'</tr>';
			// }
			// { colour
			table+='<tr id="colour"><th>Colour</th>'
				+'<td><input id="pc_colour" />'
				+'<div id="colour-picker"></div>'
				+'</td></tr>';
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
					$.post('/a/p=products/f=adminCategoryProductsEdit/id='
						+window.selected_cat, { "s[]":selected },show_attributes
					);
				}
			});
			$("#uploader").uploadify({
				"uploader":"/ww.plugins/image-gallery/files/uploadify.swf",
				"script":"/a/p=products/f=adminCategorySetIcon",
				"cancelImg":"/ww.plugins/image-gallery/files/cancel.png",
				"multi":false,
				"buttonText":"Upload Files",
				"removeCompleted":true,
				"fileDataName":"file_upload",
				"onComplete":function(event,ID,fileObj,response,data){
					$('#icon-image').html(
						'<img src="/f/products/categories/'+ret.attrs.id+'/icon.png?'
						+Math.random()+'"/>'
					);
				},
				"onSelect": function() {
					$("#uploader").uploadifySettings('scriptData', {
							"PHPSESSID":window.sessid,
							"cat_id":   window.selected_cat
						}
					);
				},
				"fileExt":"*.jpg;*.jpeg;*.png;*.gif",
				"fileDesc":"Images Only",
				"auto":true
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
		$('#icon-image').html(ret.hasIcon
			?'<img src="/f/products/categories/'+ret.attrs.id+'/icon.png?'
				+Math.random()+'"/>'
			:''
		);
	}
	$('#categories-wrapper')
		.jstree({
			'plugins': ["themes", "html_data", "ui", "dnd", "contextmenu"],
			'selected':'cat_'+window.selected_cat,
			'contextmenu': {
				'items': {
					'rename':false,
					'ccp':false,
					'create' : {
						'label'	: "create sub-category", 
						'visible'	: function (NODE, TREE_OBJ) { 
							if(NODE.length != 1) return 0; 
							return TREE_OBJ.check("creatable", NODE); 
						}, 
						'action':function(node, tree){
							var id=node[0].id.replace(/.*_/,'');
							var name=prompt('what do you want to name this sub-category?');
							if (!name) {
								return;
							}
							$.post('/a/p=products/f=adminCategoryNew', {
								"parent_id":id,
								"name":name
							},function(){
								document.location=document.location;
							});
						},
						'separator_after' : true
					},
					'remove' : {
						'label'	: "delete category", 
						'visible'	: function (NODE, TREE_OBJ) { 
							if(NODE.length != 1) return 0; 
							return TREE_OBJ.check("deletable", NODE); 
						}, 
						'action':function(node, tree){
							if (!confirm("Are you sure you want to delete this category?")) {
								return;
							}
							var id=node[0].id.replace(/.*_/,'');
							$.post(
								'/a/p=products/f=adminCategoryDelete/id='+id,
								function(){
									document.location="/ww.admin/plugin.php?_plugin=products&"
										+"_page=categories";
								}
							);
						},
						'separator_after' : true
					},
					'copy' : false
				}
			},
			'types':{
				'default':{
					icon:{
						image: false
					}
				}
			},
			'callback':{
				"onmove":function(node){
					var p=$.jstree._focused().parent(node);
					$.post(
						'/a/p=products/f=adminCategoryMove/id='+node.id.replace(/.*_/,'')
						+'&parent_id='+(p==-1?0:p[0].id.replace(/.*_/,'')),
						show_attributes
					);
					$.post('/a/p=products/f=adminCategoryMove/id='
						+node.id.replace(/.*_/,'')+'&parent_id='
						+(p==-1?0:p[0].id.replace(/.*_/,'')), show_attributes
					);
				}
			},
			'dnd': {
				'drag_target': false,
				'drop_target': false
			}
		})
		.bind('move_node.jstree',function(e, ref){
			var data=ref.args[0];
			var node=data.o[0];
			setTimeout(function(){
				var p=node.parentNode.parentNode;
				var nodes=$(p).find('>ul>li');
				if(p.tagName=='DIV')p=-1;
				var new_order=[];
				for (var i=0;i<nodes.length;++i) {
					new_order.push(nodes[i].id.replace(/.*_/, ''));
				}
				$.post('/a/p=products/f=adminCategoryMove/id='
					+node.id.replace(/.*_/,'')+'/parent_id='
					+(p==-1?0:p.id.replace(/.*_/,''))+'/order='+new_order);
			},1);
		});
	var div=$('<div style="clear:both;padding-top:20px;" />');
	$('<button>add main category</button>')
		.click(function(){
			var name=prompt('what do you want to name this category?');
			if(!name)return;
			$.post('/a/p=products/f=adminCategoryNew', {
				"parent_id":0,
				"name":name
			},function(){
				document.location=document.location;
			});
		})
		.appendTo(div);
	div.insertAfter('#categories-wrapper');
	$.post('/a/p=products/f=adminCategoryGet/id='+window.selected_cat,
		show_attributes
	);
	$('#pc_edit_name, #pc_edit_enabled, #pc_colour').live('change', save);
	$('#categories-wrapper li>a').live('click', function(){
		$.post('/a/p=products/f=adminCategoryGet/id='
			+$(this).closest('li')[0].id.replace(/.*_/,''), show_attributes
		);
	});
});
