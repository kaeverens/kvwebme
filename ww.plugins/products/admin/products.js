$(function() {
	var columns=[
		{'name':'checkboxes',
			'type':'base', 'text':'<input type="checkbox" id="products-selectall"/>'
		},
		{'name':'hasImages', 'type':'base', 'text':'&nbsp;'},
		{'name':'name',
			'type':'field', 'field_name':'name', 'text':'Name', 'edit':1},
		{'name':'stockNumber',
			'type':'field', 'field_name':'stock_number', 'text':'Stock Number',
			'edit':1, 'edit_type':'int'},
		{'name':'stockControlTotal',
			'type':'field', 'field_name':'stockcontrol_total', 'text':'#',
			'title':'Amount In Stock', 'edit':1, 'edit_type':'int'
		},
		{'name':'owner',
			'type':'field', 'field_name':'user_id', 'text':'Owner', 'edit':1,
			'edit_type':'user_list'},
		{'name':'id',
			'type':'field', 'field_name':'id', 'text':'ID'},
		{'name':'enabled',
			'type':'field', 'field_name':'enabled', 'text':'Enabled', 'edit':1,
			'edit_type':'boolean'},
		{'name':'date_created',
			'type':'field', 'field_name':'date_created', 'text':'Date Added', 'edit':0}
	];
	var cols=[
		{'sWidth':'4%', 'bSortable':false}, {'sWidth':'4%'},  {'sWidth':'64%'},
		{'sWidth':'10%'}, {'sWidth':'4%'}, {'sWidth':'10%'}, {'sWidth':'4%'},
		{'sWidth':'4%'}, {'sWidth':'4%'}
	];
	for (var i in extraProductColumns) {
		columns.push(extraProductColumns[i]);
		cols.push({'sWidth':'4%'});
	}
	var table='<table id="products-list"><thead><tr>';
	for (var i=0;i<columns.length;++i) {
		table+='<th>'+columns[i].text+'</th>';
	}
	table+='</tr></thead><tbody/></table>';
	if (adminVars.productCols) {
		adminVars.productCols=JSON.parse(adminVars.productCols);
		if (adminVars.productCols.length) {
			for (var i in adminVars.productCols) {
				$.extend(cols[i], adminVars.productCols[i]);
			}
		}
	}
	window.$pTable=$(table)
		.appendTo('#products-wrapper')
		.on('click', 'tbody input[type=checkbox]', function(e) {
			e.stopPropagation();
		})
		.on('click', 'td', function() {
			var $this=$(this),$tr=$this.closest('tr');
			if ($this.attr('in-edit')) {
				return false;
			}
			$this.attr('in-edit', true);
			var id=+$tr.attr('id').replace('product-row-', '');
			var col=columns[+$this.data('col')];
			if (col.edit===undefined || col.edit_type===undefined) {
				return;
			}
			switch(col.edit_type) {
				case 'float': // {
					var oldVal=$this.text();
					var $inp=$('<input style="width:100%;height:100%;"/>')
						.val(oldVal)
						.blur(function() {
							var newVal=+$inp.val();
							$this.text(newVal).attr('in-edit', null);
							if (newVal!=oldVal) {
								$.post('/a/p=products/f=adminProductEditVal', {
									'name': col.field_name,
									'val': newVal,
									'id': id
								});
							}
						})
						.appendTo($this.empty())
						.focus();
				break; // }
				case 'int': // {
					var oldVal=$this.text();
					var $inp=$('<input style="width:100%;height:100%;"/>')
						.val(oldVal)
						.blur(function() {
							var newVal=parseInt($inp.val());
							$this.text(newVal).attr('in-edit', null);
							if (newVal!=oldVal) {
								$.post('/a/p=products/f=adminProductEditVal', {
									'name': col.field_name,
									'val': newVal,
									'id': id
								});
							}
						})
						.appendTo($this.empty())
						.focus();
				break; // }
				case 'user_list': // {
					var oldVal=+$this.data('uid');
					$.post('/a/f=adminUserNamesGet', function(ret) {
						var opts=['<option>unknown owner</option>'];
						$.each(ret, function(k, v) {
							opts.push('<option value="'+k+'">'+v+'</option>');
						});
						var $inp=$('<select/>')
							.html(opts.join(''))
							.val(oldVal)
							.change(function() {
								var newVal=+$inp.val();
								$this.text(ret[newVal])
									.attr('in-edit', null)
									.data('uid', newVal);
								if (newVal!=oldVal) {
									$.post('/a/p=products/f=adminProductEditVal', {
										'name': col.field_name,
										'val': newVal,
										'id': id
									});
								}
							})
							.appendTo($this.empty())
							.focus();
					});
				break; // }
				case 'boolean': // {
					var oldVal=$this.text()=='Yes'?1:0;
					var $inp=$('<select/>')
						.append('<option value="0">No</option>')
						.append('<option value="1">Yes</option>')
						.val(oldVal)
						.change(function() {
							var newVal=+$inp.val();
							$this.text(newVal?'Yes':'No').attr('in-edit', null);
							if (newVal!=oldVal) {
								$.post('/a/p=products/f=adminProductEditVal', {
									'name': col.field_name,
									'val': newVal,
									'id': id
								});
							}
						})
						.appendTo($this.empty())
						.focus();
				break; // }
				default: // {
					alert('help! I don\'t know how to handle this type');
				// }
			}
		})
		.dataTable({
			'iDisplayLength':10,
			'aLengthMenu':[5, 10, 25, 50, 100, 200, 500, 1000],
			'bProcessing': true,
			'bJQueryUI': true,
			'bServerSide': true,
			'bAutoWidth': false,
			'aoColumns':cols,
			'sAjaxSource': '/a/p=products/f=adminProductsListDT',
			'fnRowCallback': function( nRow, aData, iDisplayIndex ) {
				var tCols=$pTable.fnSettings().aoColumns;
				var vCols={};
				for (var i=0,j=1;i<aData.length;++i) {
					if (tCols[i].bVisible) {
						var col=columns[i];
						$('td:nth-child('+j+')', nRow)
							.data('col', i)
							.addClass('col-'+col.name);
						if (col.name=='id') {
							var id=+aData[i];
						}
						vCols[col.name]=j;
						if (col.edit_type && col.edit_type=='boolean') {
							$('td:nth-child('+j+')', nRow)
								.text((+aData[i])?'Yes':'No');
						}
						if (col.fixed) {
							$('td:nth-child('+j+')', nRow)
								.text((+aData[i]).toFixed(col.fixed));
						}
						j++;
					}
				}
				nRow.id='product-row-'+id;
				$('td:nth-child(1)', nRow).html('<input type="checkbox"/>');
				$('td:nth-child(2)', nRow).html(+aData[1]
					?'<div title="has images" class="ui-icon ui-icon-image"/>'
					:'');
				$('td:nth-child(3)', nRow)
					.css('cursor', 'pointer')
					.html(
						'<a href="/ww.admin/plugin.php?_plugin=products'
						+'&amp;_page=products-edit&amp;id='+id+'">'+aData[2]+'</a>'
					);
				if (vCols.owner) {
					$('td:nth-child('+vCols.owner+')', nRow)
						.data('uid', aData[5].replace(/\|.*/, ''))
						.text(aData[5].replace(/.*\|/, ''));
				}
				return nRow;
			}
		}).fnSetFilteringDelay();
	var oColVis=new ColVis($pTable.fnSettings(), {
		'fnStateChange':function(iColumn, bVisible) {
			if (!adminVars.productCols) {
				adminVars.productCols=[];
			}
			if (!adminVars.productCols[iColumn]) {
				adminVars.productCols[iColumn]={};
			}
			adminVars.productCols[iColumn].bVisible=bVisible;
			$.post('/a/f=adminAdminVarsSave', {
				'name':'productCols',
				'val':JSON.stringify(adminVars.productCols)
			});
		},
		'aiExclude':[0, 1, 2, 6]
	});
	$('#products-wrapper').prepend(oColVis.dom.wrapper);
	oColVis.fnRebuild();
	$('#products-selectall').click(function() {
		$('#products-list_wrapper input').attr(
			'checked',
			$(this).attr('checked')?true:false
		);
	});
	$('#products-action').change(function() {
		var val=+$(this).val();
		var $inps=$('#products-list_wrapper tbody input[type="checkbox"]');
		var ids=[];
		$inps.each(function() {
			if (!$(this).attr('checked')) {
				return;
			}
			var id=+$(this).closest('tr').attr('id').replace('product-row-', '');
			ids.push(id);
		});
		switch(val) {
			case 1: // {
				if (!confirm('Are you sure you want to delete these products?')) {
					return;
				}
				$.post('/a/p=products/f=adminProductsDelete/ids='+ids, function() {
					$('#products-action').val('0');
					$pTable.fnDraw(false);
					$('#products-selectall').attr('checked', false);
				});
			break; // }
			case 2: // {
				$.post('/a/p=products/f=adminProductsDisable/ids='+ids, function() {
					$('#products-action').val('0');
					$pTable.fnDraw(false);
					$('#products-selectall').attr('checked', false);
				});
			break; // }
			case 3: // {
				$.post('/a/p=products/f=adminProductsEnable/ids='+ids, function() {
					$('#products-action').val('0');
					$pTable.fnDraw(false);
					$('#products-selectall').attr('checked', false);
				});
			break; // }
		}
	});
});
