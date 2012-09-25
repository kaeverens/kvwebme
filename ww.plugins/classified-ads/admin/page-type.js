$(function() {
	var $categoriesWrapper=$('#ads-categories').empty();
	function initCategories() {
		$.post('/a/p=classified-ads/f=categoriesGetAll', function(categories) {
			if (!categories.length) {
				$.post('/a/p=classified-ads/f=adminCategoryUpdate', {
					'id':0,
					'name':'Default',
					'icon':'',
					'parent':0
				}, initCategories);
				return;
			}
			var catByParents=[[]];
			var catByIds=[];
			for (var i=0;i<categories.length;++i) {
				categories[i].id=+categories[i].id
				categories[i].parent=+categories[i].parent;
				catByParents[categories[i].id]=[];
			}
			for (var i=0;i<categories.length;++i) {
				catByParents[categories[i].parent].push(categories[i]);
				catByIds[categories[i].id]=categories[i];
			}
			function populateCategories(pid) {
				if (!catByParents[pid].length) {
					return;
				}
				var items=catByParents[pid];
				var $catWrapper=pid?$('#classified-ads-'+pid):$categoriesWrapper;
				var $ul=$('<ul/>').appendTo($catWrapper);
				for (var i=0;i<items.length;++i) {
					var item=items[i];
					var li=$(
							'<li id="classified-ads-'+item.id+'">'
							+'<a href="#">'+item.name+'</a></li>'
						)
						.appendTo($ul);
					populateCategories(item.id);
				}
			}
			populateCategories(0);
			$categoriesWrapper
				.jstree({
					'contextmenu': {
						'items': {
							'rename':false,
							'ccp':false,
							'remove' : {
								'label'	: "Delete Category", 
								'visible':function (NODE, TREE_OBJ) { 
									if (NODE.length != 1) {
										return 0;
									}
									return TREE_OBJ.check("deletable", NODE); 
								}, 
								'action':function(node,tree){
									if (!confirm("Are you sure you want to delete this category?")) {
										return;
									}
									$.post('/a/p=classified-ads/f=admin/id='+node[0].id.replace(/.*_/, ''), function(ret){
										if(ret.error)
											alert(ret.error);
										else{
											if (node.find('li').length) {
												document.location=document.location.toString();
											}
											else {
												$('#pages-wrapper').jstree('remove', node);
											}
										}
									});
								}
							}
						}
					},
					'dnd':{
						'drop_finish':false,
						'drag_finish':false
					},
					'crrm':{
						'move': {
							'check_move':function(e) {
								return true;
							}
						}
					},
					'plugins': ['themes', 'html_data', 'dnd', 'crrm']
				})
				.bind('move_node.jstree',function(e, ref){
					var data=ref.args[0];
					var node=data.o[0];
					setTimeout(function(){
						var idToMove=+$(node).attr('id').replace(/.*-/, '');
						var p=node.parentNode.parentNode;
						if (p.tagName=='DIV') {
							p=-1;
						}
						$.post('/a/p=classified-ads/f=adminCategoryMove', {
							'id':node.id.replace(/.*-/,''),
							'parent':(p==-1?0:p.id.replace(/.*-/, ''))
						});
					},1);
				})
				.on('click', 'a', function() {
					var id=$(this).closest('li').attr('id').replace(/.*-/, '');
					console.log(catByIds[id]);
				});
			setTimeout(function() {
				$('<button><span>Add Category</span></button>')
					.click(function() {
						var name=prompt('New category name');
						if (!name) {
							return false;
						}
						$.post('/a/p=classified-ads/f=adminCategoryUpdate', {
							'id':0,
							'name':name,
							'icon':'',
							'parent':0
						}, initCategories);
						return false;
					})
					.appendTo($categoriesWrapper);
			}, 1);
		});
	}
	initCategories();
	var $adtypes=$('#ads-types-ids')
		.change(function() {
			var $this=$(this);
			var id=+($this.val());
			var $wrapper=$('#ads-types-wrapper');
			if (!id) {
				return $wrapper.empty();
			}
			$.post('/a/p=classified-ads/f=adminTypeGet', {'id':id}, function(ret) {
				if (ret===false) {
					ret={
						'id':0,
						'name':'',
						'maxchars':0,
						'price_per_day':.1
					};
				}
				$wrapper.html('<table>'
					+'<tr><th>Name</th><td><input id="ads-types-name"/></td></tr>'
					+'<tr><th>Max Chars</th><td><input id="ads-types-maxchars"/></td></tr>'
					+'<tr><th>Price Per Day</th><td><input id="ads-types-price_per_day"/></td></tr>'
					+'<tr><td></td><td><button>Save</button></td></tr>'
					+'</table>');
				$('#ads-types-name').val(ret.name);
				$('#ads-types-maxchars').val(ret.maxchars);
				$('#ads-types-price_per_day').val(ret.price_per_day);
				$wrapper.find('button').click(function() {
					$.post(
						'/a/p=classified-ads/f=adminTypeEdit',
						{
							'id':ret.id,
							'name':$('#ads-types-name').val(),
							'maxchars':$('#ads-types-maxchars').val(),
							'price_per_day':$('#ads-types-price_per_day').val()
						},
						function(ret) {
							$wrapper.val('0').change();
							var opts='<option value="0"> -- choose -- </option>';
							for (var i=0;i<ret.opts.length;++i) {
								var opt=ret.opts[i];
								opts+='<option value="'+opt.id+'"';
								opts+='>'+opt.name+'</option>';
							}
							opts+='<option value="-1">add new</option>';
							$adtypes.html(opts).val(ret.id);
							alert('saved');
						}
					);
					return false;
				});
			});
		});
	// { ads
	var params={
		"sAjaxSource": '/a/p=classified-ads/f=adminAdsGetDT',
		"bProcessing":true,
		"bJQueryUI":true,
		"bServerSide":true,
		"fnRowCallback": function( nRow, aData, iDisplayIndex ) {
			var id=+aData[0];
			nRow.id='users-list-row-'+id;
			$('td:nth-child(2)', nRow).addClass('editable');
			$('td:nth-child(5)', nRow)
				.html('<a href="./siteoptions.php?page=users&id='+id+'">edit</a>');
			return nRow;
		}
	};
	var $adsTable=$('#ads-main>table')
		.dataTable(params);
	$('#ads-main>button').click(function() {
		adEdit(0);
		return false;
	});
	function adEdit(id) {
		$.post('/a/p=classified-ads/f=adminAdGet', {'id':id}, function(ret) {
			if (!ret) {
				var now=new Date();
				var expire=new Date();
				expire.setDate(expire.getDate()+7);
				ret={
					'creation_date':now.toYMD(),
					'expiry_date':expire.toYMD(),
					'user_id':0,
					'cost':0,
					'paid':0,
					'body':''
				};
			}
			var table='<div><table style="width:100%">'
				+'<tr><th>Date Range</th><td><input class="date" id="popup-creation_date"/> to'
				+' <input class="date" id="popup-expiry_date"/></td></tr>'
				+'<tr><th>Body</th><td><textarea id="popup-body" style="width:100%"></textarea>'
				+'<tr><th>Paid</th><td><select id="popup-paid"><option value="0">No</option><option value="1">Yes</option></select></td></tr>'
				+'<tr><th>User</th><td><select id="popup-user_id"/></td></tr>'
				+'<tr><th>Cost</th><td><input id="popup-cost"/></td></tr>'
				+'</td></tr>'
				+'</table></div>';
			var $table=$(table).dialog({
				'modal':true,
				'width':400,
				'close':function() {
					$table.remove();
				},
				'buttons':{
					'Save':function() {
						$.post(
							'/a/p=classified-ads/f=adEdit',
							{
								'id':id,
								'expiry_date':$('#popup-expiry_date').val(),
								'body':$('#popup-body').val(),
								'creation_date':$('#popup-creation_date').val(),
								'paid':$('#popup-paid').val(),
								'user_id':$('#popup-user_id').val(),
								'cost':$('#popup-cost').val()
							},
							function(ret) {
								console.log(ret);
							}
						);
					}
				}
			});
			$table.find('.date').datepicker({
				'dateFormat':'yy-mm-dd'
			}).blur();
			$('#popup-creation_date').val(ret.creation_date);
			$('#popup-expiry_date').val(ret.expiry_date);
			$('#popup-user_id').val(ret.user_id);
			$.post('/a/f=adminUserNamesGet', function(ret) {
				var users=['<option  ></option>'];
				$.each(ret, function(k, v) {
					users.push('<option data-name="'+v+'" value="'+k+'">'+v+'</option>');
				});
				users.sort();
				$('#popup-user_id').html(users.join('')).val(ret.user_id);
			});
			$('#popup-cost').val(ret.cost);
			$('#popup-paid').val(ret.paid);
			$('#popup-body').val(ret.body);
		});
		return false;
	}
	// }
});
