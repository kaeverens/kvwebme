function Products_screen(page) {
	Core_sidemenu(
		[ 'Products', 'Categories', 'Types', 'Relation Types', 'Export Data' ],
		'products',
		page
	);
	window['Products_screen'+page]();
}
function Products_screenProducts() {
	document.location="/ww.admin/plugin.php?_plugin=products&_page=products";
}
function Products_screenCategories() {
	document.location="/ww.admin/plugin.php?_plugin=products&_page=categories";
}
function Products_screenTypes() {
	$('#content')
		.html('<button>add new product type</button><table id="product-types-list"><thead>'
			+'<tr><th>Name</th><th>edit</th><th>&nbsp;</th></tr>'
			+'</thead><tbody></tbody></table>');
	$('#content button').click(function() {
		function showAddNewDialog() {
			$('#dialog').remove();
			$.post('/a/p=products/f=typesTemplatesGet', function(ret) {
				var html='<div id="dialog"><strong>Product template to start from'
					+'</strong>';
				for (var i=0;i<ret.length;++i) {
					html+='<br/><button>'+ret[i]+'</button>';
				}
				$(html+'</div>').dialog({'modal':true});
				$('#dialog button').click(function(){
					$.post('/a/p=products/f=adminTypeCopy/id='+$(this).text(),
						function(ret) {
							Products_typeEdit(ret.id);
							$('#dialog').remove();
						}
					);
				});
			});
		}
		function showCopyDialog() {
			$('#dialog').remove();
			$.post('/a/p=products/f=typesGet', function(ret) {
				var html='<div id="dialog"><strong>Which type to copy</strong>';
				for (var i=0;i<ret.aaData.length;++i) {
					var item=ret.aaData[i];
					html+='<br/><button id="b'+item[1]+'">'+item[0]+'</button>'
				}
				$(html+'</div>').dialog({"modal":true});
				$('#dialog button').click(function() {
					var id=$(this).attr('id').replace(/b/, '');
					$.post('/a/p=products/f=adminTypeCopy/id='+id, function(ret) {
						Products_typeEdit(ret.id);
						$('#dialog').remove();
					});
				});
			});
		}
		if ($('#product-types-list .sorting_1').length) {
			$('<div id="dialog"><button class="new">from template</button>'
				+'<br /><button class="copy">copy existing type</button></div>'
			).dialog({"modal":true});
			$('#dialog .new').click(showAddNewDialog);
			$('#dialog .copy').click(showCopyDialog);
		}
		else {
			showAddNewDialog();
		}
	});
	window.openDataTable=$('#product-types-list')
		.dataTable({
			"sAjaxSource": '/a/p=products/f=typesGet',
			"bProcessing": true,
			"bServerSide": true,
			"fnRowCallback": function( nRow, aData, iDisplayIndex ) {
				var id=aData[1];
				nRow.id='product-types-list-row'+id;
				$('td:nth-child(2)', nRow)
					.html('<a href="javascript:Products_typeEdit('+id+');">edit</a>');
				$('td:nth-child(3)', nRow)
					.html('<a href="javascript:Products_typeDelete('+id+');">[x]</a>');
				return nRow;
			}
		});
}
function Products_screenRelationTypes() {
	document.location="/ww.admin/plugin.php?_plugin=products&_page=relation-types";
}
function Products_screenExportData() {
	$('#content')
		.html('<p>Your export should start downloading in a moment.</p>');
	document.location='/a/p=products/f=adminExport';
}

function Products_typeDelete(id) {
	var name=$('#product-types-list-row'+id).find('td:first-child').text();
	if (!confirm('Are you sure you want to remove the product type named "'
		+name+'"?')) {
		return;
	}
	$.post('/a/p=products/f=adminTypeDelete/id='+id, function() {
		window.openDataTable.fnDraw(1);
	});
}
function Products_typeEdit(id) {
	var activeTab=-1, tdata=false;
	function updateValues() {
		switch(activeTab) {
			case 0: // { main
				return updateMain();
				// }
			case 1: // { data fields
				return updateDataFields();
				// }
			case 2: // { multiview
				return updateMultiView();
				// }
			case 3: // { singleview
				return updateSingleView();
				// }
		}
	}
	$.post('/a/p=products/f=typeGet/id='+id, function(res) {
		tdata=res;
		var $content=$('#content')
			.html('<a href="javascript:Products_screenTypes()">Product Types</a>');
		$('<div id="product-types-edit-form"><ul>'
			+'<li><a href="#t0">Main Details</a></li>'
			+'<li><a href="#t1">Data Fields</a></li>'
			+'<li><a href="#t2">Multi-View Template</a></li>'
			+'<li><a href="#t3">Single-View Template</a></li>'
			+'</ul><div id="t0"/><div id="t1"/><div id="t2"/><div id="t3"/></div>'
		)
			.appendTo($content)
			.tabs({
				'select':updateValues,
				'show':function(e, ui) {
					$('#product-types-edit-form>div').empty();
					activeTab=ui.index;
					switch (ui.index) {
						case 0: // { main
							return showMain(ui.panel);
							// }
						case 1: // { data fields
							return showDataFields(ui.panel);
							// }
						case 2: // { multiview
							return showMultiView(ui.panel);
							// }
						case 3: // { singleview
							return showSingleView(ui.panel);
							// }
					}
				}
			});
		$('<button>Save</button>')
			.click(function() {
				updateValues();
				$.post('/a/p=products/f=adminTypeEdit', {
					'data': tdata
				}, function(ret) {
					alert('product type saved');
				});
			})
			.appendTo($content);
	});
	function showDataFields(panel, index) {
		$(panel).empty();
		var fields=tdata.data_fields;
		var html='<div id="df1">';
		for (var i=0;i<fields.length;++i) {
			html+='<h3 id="f'+i+'"><a href="#">'+htmlspecialchars(fields[i].n)
				+'</a></h3><div/>';
		}
		$(html+'</div>')
			.appendTo(panel)
			.accordion({
				'changestart':function(e, ui) {
					updateDataFields();
					$('.product-field-panel').remove();
					if (!ui.newHeader.context) {
						return;
					}
					var index=+ui.newHeader.context.id.replace(/f/, '');
					var field=fields[index];
					field.e=field.e||'';
					var $wrapper=$(ui.newContent.context).next();
					$wrapper
						.append('<table class="product-field-panel wide">'
							+'<tr><th>Name</th><td class="pfp-name"></td>'
							+'<td rowspan="5" id="pfp-type-specific"></td></tr>'
							+'<tr><th>Type</th><td class="pfp-type"></td></tr>'
							+'<tr><th>Required</th><td class="pfp-required"></td></tr>'
							+'<tr><th>User-entered</th><td class="pfp-user-entered"></td>'
							+'</tr><tr><td colspan="2"><a href="javascript:;" id="pfp-delete"'
							+' title="delete">[x]</a></td></tr>'
							+'</table>'
						);
					$('<input/>').val(field.n).appendTo('.pfp-name', $wrapper);
					// { required
					$('<select><option value="0">No</option>'
						+'<option value="1">Yes</option></select>'
					)
						.val(field.r).appendTo('.pfp-required', $wrapper);
					// }
					// { user-entered
					$('<select><option value="0">No</option>'
						+'<option value="1">Yes</option></select>'
					)
						.val(field.u).appendTo('.pfp-user-entered', $wrapper);
					// }
					// { type
					$('<select><option>inputbox</option><option>textarea</option>'
						+'<option>date</option><option>checkbox</option>'
						+'<option>selectbox</option><option>selected-image</option>'
						+'<option>hidden</option><option>colour</option>'
						+'</select>'
					)
						.val(field.t).appendTo('.pfp-type', $wrapper);
					// }
					// { delete button
					$('#pfp-delete').click(function() {
						if (!confirm('are you sure you want to remove this?')) {
							return;
						}
						var dfs=[];
						for (var i=0;i<fields.length;++i) {
							if (i!=index) {
								dfs.push(fields[i]);
							}
						}
						tdata.data_fields=dfs;
						showDataFields(panel, -1);
					});
					// }
					switch (field.t) {
						case 'date': // {
							$('<p>What format should the date be in? '
								+'<a href="http://docs.jquery.com/UI/Datepicker/formatDate" '
								+'target="_blank">examples</a></p>')
								.appendTo('#pfp-type-specific');
							return $('<input/>')
								.val(field.e||'yy-mm-dd')
								.appendTo('#pfp-type-specific');
							// }
						case 'selectbox': // {
							return showExtrasSelectbox(field.e);
							// }
						default: // { text
							// }
					}
				},
				'active':false,
				'autoHeight':false,
				'animated':false,
				'collapsible':true,
				'create':function() {
					if (index) {
						$('#df1').accordion('activate', index);
					}
				}
			});
		$('<button>add field</button>')
			.click(function() {
				var name=prompt('What do you want to name this field?', 'fieldname');
				if (name===false) {
					return;
				}
				tdata.data_fields.push({'n':name,'r':0,'t':'inputbox','u':0});
				showDataFields(panel, tdata.data_fields.length-1);
			})
			.appendTo(panel);
	}
	function showExtrasSelectbox(e) {
		function addRow(opt, val) {
			var $row=$('<tr/>').appendTo('#pfp-type-specific-table');
			var bits=rows[i]?rows[i].split('|'):['', 0],
				$inp1=$('<input class="wide"/>').val(bits[0]).change(checkRows),
				$inp2=$('<input class="number"/>').val(+bits[1]||0);
			$('<td/>').append($inp1).appendTo($row);
			$('<td/>').append($inp2).appendTo($row);
		}
		function checkRows() {
			var empty=0;
			$('#pfp-type-specific-table td:first-child input').each(function() {
				if ($(this).val()=='') {
					empty=1;
				}
			});
			if (!empty) {
				addRow('', 0);
			}
		}
		$(
			'<table id="pfp-type-specific-table" class="wide tight">'
			+'<tr><th>Option</th>'
			+'<th title="how much this adds to the price of a product">$£€</th>'
			+'</tr></table>'
		).appendTo('#pfp-type-specific');
		var rows=e.split("\n");
		for (var i=0;i<rows.length;++i) {
			var bits=rows[i].split('|');
			addRow(bits[0], +bits[1]||0);
		}
		checkRows();
	}
	function showMain(panel) {
		$('<table class="wide">'
			+'<tr><th>Name</th><td id="pte1"></td></tr>'
			+'<tr><th>Are products of this type for sale?</th>'
			+'<td id="pte2"></td></tr>'
			+'<tr><th>If no image is uploaded for the product, what image should '
			+'be shown?</th><td id="pte3"></td></tr>'
			+'</table>'
		).appendTo(panel);
		$('<input/>')
			.change(function(){tdata.name=$(this).val();})
			.val(tdata.name||"default")
			.appendTo('#pte1');
		$('<select><option value="0">No</option><option value="1">Yes</option></select>')
			.change(function(){tdata.is_for_sale=$(this).val();})
			.val(tdata.is_for_sale)
			.appendTo('#pte2');
		var src=id
			?'/kfmgetfull/products/types/'+id
			+'/image-not-found.png,width=64,height=64'
			:'/ww.plugins/products/i/not-found-64.png';
		$('<img id="pte3-img" src="'+src+'?'+Math.random()+'"/>'
			+'<input name="image_not_found" id="pte3-inp"/>'
		)
			.appendTo('#pte3');
		$('#pte3-inp')
			.uploadify({
				'swf':'/j/jquery.uploadify/uploadify.swf',
				'auto':'true',
				'checkExisting':false,
				'cancelImage':'/i/blank.gif',
				'buttonImage':'/i/choose-file.png',
				'uploader':'/a/p=products/f=adminTypeUploadMissingImage/id='+id,
				'postData':{
					'PHPSESSID':sessid
				},
				'upload_success_handler':function(file, data, response){
					$('#pte3-img').attr('src', data+'?'+Math.random());
				}
			});
	}
	function showMultiView(panel) {
		$('<div><ul><li><a href="#ts1">body</a></li>'
			+'<li><a href="#ts2">header</a></li><li><a href="#ts3">footer</a></li>'
			+'</ul><div id="ts1"/><div id="ts2"/><div id="ts3"/></div>')
			.appendTo(panel)
			.tabs();
		$('<textarea>')
			.val(tdata.multiview_template)
			.appendTo('#ts1')
			.ckeditor();
		$('<textarea>')
			.val(tdata.multiview_template_header)
			.appendTo('#ts2')
			.ckeditor();
		$('<textarea>')
			.val(tdata.multiview_template_footer)
			.appendTo('#ts3')
			.ckeditor();
	}
	function showSingleView(panel) {
		$('<textarea/>')
			.val(tdata.singleview_template)
			.appendTo(panel)
			.ckeditor();
	}
	function updateDataFields() {
		var $panel=$('#t1>div>div.ui-accordion-content-active');
		var index=$panel.index('#t1>div>div');
		if (index<0) {
			return;
		}
		tdata.data_fields[index].n=$('.pfp-name input').val();
		tdata.data_fields[index].r=$('.pfp-required select').val();
		tdata.data_fields[index].u=$('.pfp-user-entered select').val();
		switch (tdata.data_fields[index].t) {
			case 'date': // {
				tdata.data_fields[index].e=$('#pfp-type-specific input')
					.val()||'yy-mm-dd';
				break; // }
			case 'selectbox': // {
				var e=[];
				$('#pfp-type-specific tr').each(function() {
					var $inps=$(this).find('input');
					if ($inps.length && $inps[0].value!='') {
						e.push($inps[0].value+'|'+$inps[1].value);
					}
				});
				tdata.data_fields[index].e=e.join("\n");
				break; // }
		}
		tdata.data_fields[index].t=$('.pfp-type select').val();
	}
	function updateMain() {
		tdata.name=$('#pte1 input').val();
		tdata.is_for_sale=$('#pte2 select').val();
	}
	function updateMultiView() {
		tdata.multiview_template=$('#ts1 textarea').val();
		tdata.multiview_template_footer=$('#ts2 textarea').val();
		tdata.multiview_template_header=$('#ts3 textarea').val();
	}
	function updateSingleView() {
		tdata.singleview_template=$('#t3 textarea').val();
	}
}
