/*global __,google,CodeMirror,htmlspecialchars,Core_menuShow,Core_prompt*/
function CoreSiteoptions_screen(page) {
	window['CoreSiteoptions_screen'+page]();
}
function CoreSiteoptions_screenCron() {
	var $content=$('#content').empty();
	$.post('/a/f=adminCronGet', function(ret) {
		var table='<table><thead><tr><th>'+__('Name')+'</th><th>'+__('Period')+'</th>'+
			'<th>'+__('Next')+'</th><th>'+__('Description')+'</th></tr></thead>'+
			'<tbody>';
		for (var i=0;i<ret.length;++i) {
			var cron=ret[i];
			table+='<tr cid="'+cron.id+'"><td>'+cron.name+'</td><td class="clickable">'+
				cron.period_multiplier+' '+cron.period+'</td><td class="clickable"'+
				'>'+cron.next_date+'</td><td>'+cron.notes+'</td></tr>';
		}
		table+='</tbody></table>';
		var $table=$(table)
			.appendTo($content);
		$table.dataTable();
		$table.find('td')
			.click(tdClicked);
	});
	function tdClicked() {
		var $this=$(this);
		if ($this.attr('clicked')) {
			return;
		}
		var $tr=$this.closest('tr');
		var id=+$tr.attr('cid');
		switch ($tr.find('td').index($this)) {
			case 1: // {
				var parts=$this.text().split(' '), i;
				// TODO: translations of periods
				var periods=['never', 'minute', 'hour', 'day', 'week', 'month', 'year'];
				var html='<select>';
				for (i=1;i<32;++i) {
					html+='<option>'+i+'</option>';
				}
				html+='</select><select>';
				for (i=0;i<periods.length;++i) {
					html+='<option>'+periods[i]+'</option>';
				}
				html+='</select>';
				$this
					.html(html)
					.find('select')
					.change(function() {
						var mult=$this.find('select:first-child').val(),
							period=$this.find('select:last-child').val(),
							url='/a/f=adminCronSave/id='+id+'/field=period';
						$.post(url+'/value='+period);
						$.post(url+'_multiplier/value='+mult);
						$this.html(mult+' '+period).removeAttr('clicked');
					});
				$this.find('select:first-child').val(parts[0]);
				$this.find('select:last-child').val(parts[1]);
				break; // }
			case 2: // {
				var $inp=$('<input id="nextrecurrance'+id+'"/>')
					.val($this.text())
					.appendTo($this.empty());
				$inp
					.datetimepicker({
						dateFormat: 'yy-mm-dd',
						timeFormat: 'hh:mm',
						onClose: function(dateText){
							var url='/a/f=adminCronSave/id='+id+'/field=next_date';
							$.post(url+'/value='+dateText);
							$this.html(dateText).removeAttr('clicked');
						}
					})
					.focus();
				break; // }
		}
		$this.attr('clicked', 1);
	}
}
function CoreSiteoptions_screenLanguages() {
	// TODO: translation of options needed
	$('#content').empty().html('<select><option>List languages</option><option>Translations</option></select><div id="lang-content"/>');
	var $content=$('#lang-content');
	$('#content select').change(function() {
		switch ($(this).val()) {
			case 'List languages':
				return showLanguages();
			case 'Translations':
				return showTranslations();
		}
	});

	function showLanguages() {
		$content.empty();
		$.post('/a/f=languagesGet', function(languages) {
			var table='<table id="languages-table"><thead>'+
				'<tr><th>'+__('Name')+'</th><th>'+__('Code')+'</th>'+
				'<th>'+__('Default')+'</th><th>&nbsp;</th></tr></thead>'+
				'<tbody>';
			for (var i=0;i<languages.length;++i) {
				var lang=languages[i];
				var links=['<a href="#" class="edit">'+__('Edit')+'</a>'];
				if (+lang.is_default===0) {
					links.push('<a href="#" class="delete">'+__('[x]')+'</a>');
				}
				table+='<tr cid="'+lang.id+'"><td>'+lang.name+'</td>'+
					'<td>'+lang.code+'</td><td>'+(+lang.is_default?'Yes':'')+'</td>'+
					'<td>'+links.join(', ')+'</td></tr>';
			}
			table+='</tbody></table>';
			var $table=$(table)
				.appendTo($content);
			$table.dataTable(
			);
			$('<a href="#">'+__('Add Language')+'</a>')
				.click(function() {
					$('<form id="languages-form"><table>'+
						'<tr><th>'+__('Name')+'</th><td><input name="name"/></td></tr>'+
						'<tr><th>'+__('Code')+' <a href="'+__('http://en.wikipedia.org/wiki/List_of_ISO_63'+
						'9-1_codes')+'" target="_blank">#</a></th><td>'+
						'<input name="code" class="small"/></td></tr>'+
						'</table></form>'
					)
						.dialog({
							'modal':true,
							'close':function() {
								$('#languages-form').remove();
							},
							'buttons': {
								// TODO: translation needed
								'Add': function() {
									$.post('/a/f=adminLanguagesAdd',
										$('#languages-form').serialize(),
										showLanguages
									);
									$('#languages-form').remove();
								},
								// TODO: translation needed
								'Cancel': function() {
									$('#languages-form').remove();
								}
							}
						});
					return false;
				})
				.appendTo($content);
			$('#languages-table .delete').click(function() {
				var id=$(this).closest('tr').attr('cid');
				// TODO: translation needed
				if (!confirm('Are you sure you want to delete this language?')) {
					return;
				}
				$.post(
					'/a/f=adminLanguagesDelete/id='+id,
					showLanguages
				);
				return false;
			});
			$('#languages-table .edit').click(function() {
				var id=$(this).closest('tr').attr('cid');
				var language;
				for (var i=0;i<languages.length;++i) {
					if (languages[i].id==id) {
						language=languages[i];
					}
				}
				$('<form id="languages-form"><input name="id" type="hidden"/><table>'+
					'<tr><th>'+__('Name')+'</th><td><input name="name"/></td></tr>'+
					'<tr><th>'+__('Code')+' <a href="'+__('http://en.wikipedia.org/wiki/List_of_ISO_63'+
					'9-1_codes')+'" target="_blank">#</a></th><td>'+
					'<input name="code" class="small"/></td></tr>'+
					'<tr><th>'+__('Is Default')+'</th><td><select name="is_default">'+
					'<option value="0">'+__('No')+'</option><option value="1">'+__('Yes')+'</option>'+
					'</select></td></tr>'+
					'</table></form>'
				)
					.dialog({
						'modal':true,
						'close':function() {
							$('#languages-form').remove();
						},
						'buttons': {
							// TODO: translation needed
							'Save': function() {
								$.post('/a/f=adminLanguagesEdit',
									$('#languages-form').serialize(),
									showLanguages
								);
								$('#languages-form').remove();
							},
							// TODO: translation needed
							'Cancel': function() {
								$('#languages-form').remove();
							}
						}
					});
				for (var k in language) {
					$('#languages-form *[name='+k+']').val(language[k]);
				}
				return false;
			});
		});
	}
	function showTranslations() {
		function changeLanguage() {
			var lang=$(this).val();
			if (lang==='') {
				$languagestable.find('.context')
					// TODO: translation needed
					.text('-- choose a language --')
					.css('cursor', 'default');
				return;
			}
			$.post('/a/f=adminLanguagesGetTrStrings', {
				'lang':lang
			}, function(ret) {
				$languagestable.find('.context')
					.text('')
					.css('cursor', 'pointer');
				currentTr=ret;
				updateVisibleTranslations();
			});
		}
		function updateVisibleTranslations() {
			if ($languages.val()==='') {
				return;
			}
			var trs=$languagestable.find('tbody tr');
			trs.find('td:last-child').text('');
			for (var i=0;i<trs.length;++i) {
				var $tr=$(trs[i]);
				var str=$tr.find('td:first-child').text(), context=$tr.find('td:nth-child(2)').text();
				if (!str) {
					continue;
				}
				$tr.find('td:last-child').text(str);
				for (var j=0;j<currentTr.length;++j) {
					var cstr=currentTr[j];
					if (cstr.str==str && cstr.context==context) {
						$tr.find('td:last-child').text(cstr.trstr);
					}
				}
			}
		}
		// { header
		$(
			'<table><tr><td id="lang-selector"/><td>'+__('Export')+':</td>'+
			'<td id="lang-export"/>'+
			'<td>'+__('or')+' '+__('import')+':</td><td id="lang-import"/>'+
			'</tr></table>'
		)
			.appendTo($content.empty());
			// TODO: translation needed
		var $languages=$('<select><option> -- loading -- </option></select>')
			.appendTo('#lang-selector');
		var $export=$('<button>'+__('Export')+'</button>')
			.appendTo('#lang-export');
		var $import=$('<button id="lang-import-button">'+__('Import')+'</button>')
			.appendTo('#lang-import');
		// }
		var currentTr=[];
		var $languagestable;
		$.post('/a/f=languagesGet', function(languages) {
			// TODO: translation needed
			var opts='<option value=""> -- choose a language -- </option>';
			for (var i=0;i<languages.length;++i) {
				opts+='<option value="'+languages[i].code+'">'+languages[i].name+'</option>';
			}
			$languages.html(opts).change(changeLanguage);
		});
		$.post('/a/f=adminLanguagesGetStrings', function(strings) {
			var table='<table id="languages-table"><thead>'+
				'<tr><th>'+__('String')+'</th><th>'+__('Context')+'</th>'+
				'<th>'+__('Translation')+'</th></tr></thead>'+
				'<tbody>';
			for (var i=0;i<strings.length;++i) {
				var str=strings[i];
				table+='<tr><td>'+htmlspecialchars(str.str)+'</td>'+
					// TODO: translation
					'<td>'+str.context+'</td><td class="context">-- choose a language --</td>'+
					'</tr>';
			}
			table+='</tbody></table>';
			$languagestable=$(table)
				.appendTo($content)
				.dataTable({
					fnDrawCallback:function() {
						setTimeout(updateVisibleTranslations, 1);
					},
					'aoColumns': [
						{ 'sWidth': '46%' },
						{ 'sWidth': '8%' },
						{ 'sWidth': '46%'}
					]
				});
			$languagestable.on('click', 'tbody td:last-child', function() {
				if ($languages.val()==='') {
					return;
				}
				var $this=$(this);
				var $tr=$this.closest('tr'), trstr=$this.text();
				var str=$tr.find('td:first-child').text(), context=$tr.find('td:nth-child(2)').text();
				if (!str) {
					return;
				}
				var newtrstr=prompt(str, trstr);
				if (!newtrstr || trstr==newtrstr) {
					return;
				}
				$.post('/a/f=adminLanguagesEditString', {
					'lang': $languages.val(),
					'context': context,
					'str': str,
					'trstr': newtrstr
				}, function() {
					$this.text(newtrstr);
					for (var j=0;j<currentTr.length;++j) {
						var cstr=currentTr[j];
						if (cstr.str==str && cstr.context==context) {
							currentTr[j].trstr=newtrstr;
							return;
						}
					}
					currentTr[j].push({
						'str':str,
						'trstr':newtrstr,
						'context':context
					});
				});
			});
		});
		$export.click(function() {
			var lang=$languages.val();
			if (!lang) {
				return;
			}
			document.location='/a/f=adminLanguagesExportPo?lang='+lang;
		});
		$import.click(function() {
			var $dialog=$('<table>'+
				'<tr><th>'+__('Language')+':</th><td><select id="popup-lang"/>'+
				'</td></tr>'+
				'<tr><th>'+__('Context')+':</th><td><select id="popup-context"/></td>'+
				'</tr>'+
				'<tr><th>'+__('File')+':</th><td><input type="button" class="upload"'+
				' id="popup-file" value="Select and Upload"/>'+
				'</td></tr>'+
				'</table>'
			).dialog({
				'close':function() {
					$dialog.remove();
				}
			});
			// { set up import button
			$('#popup-file')
				.css('height',20)
				.uploadify({
					'swf':'/j/jquery.uploadify/uploadify.swf',
					'auto':'true',
					'checkExisting':false,
					'cancelImage':'/i/blank.gif',
					'height':20,
					'width':81,
					'multi':false,
					'buttonImage':'/i/choose-file.png',
					'uploader':'/a/f=adminLanguagesImportPo',
					'onUploadSuccess':function(file, data){
						var ret=eval('('+data+')');
						if (ret.error) {
							return alert(ret.error);
						}
						updateVisibleTranslations();
						$dialog.remove();
					},
					'onUploadStart':function() {
						$('#popup-file').uploadify('settings', 'formData', {
							'PHPSESSID':window.sessid,
							'lang':$('#popup-lang').val(),
							'context':$('#popup-context').val()
						});
					}
				});
			// }
			$('#popup-lang').html($languages.html()).val($languages.val());
			$.post('/a/f=adminLanguagesGetContexts', function(ret) {
				var opts=['<option value=""></option>'];
				for (var i=0;i<ret.length;++i) {
					opts.push('<option>'+ret[i].context+'</option>');
				}
				$('#popup-context').html(opts.join(''));
			});
		});
	}
	showLanguages();
}
function CoreSiteoptions_screenLocations() {
	var $content=$('#content').empty();
	$.post('/a/f=locationsGet', function(locations) {
		var table='<table id="locations-table"><thead>'+
			'<tr><th>'+__('Name')+'</th><th>'+__('Lat')+'</th><th>'+__('Lng')+'</th>'+
			'<th>'+__('Located In')+'</th>'+
			'<th>'+__('Default')+'</th><th>&nbsp;</th></tr></thead>'+
			'<tbody>';
		function getParent(parent_id) {
			if (parent_id) {
				for (var i=0;i<locations.length;++i) {
					var loc=locations[i];
					if (parent_id==+loc.id) {
						return getParent(+loc.parent_id)+loc.name+' / ';
					}
				}
			}
			return '';
		}
		for (var i=0;i<locations.length;++i) {
			var loc=locations[i];
			var links=['<a href="#" class="edit">'+__('Edit')+'</a>'];
			if (+loc.is_default===0) {
				links.push('<a href="#" class="delete">'+__('[x]')+'</a>');
			}
			var located_in=getParent(+loc.parent_id).replace(/\/ $/, '') || ' - ';
			table+='<tr cid="'+loc.id+'"><td>'+loc.name+'</td>'+
				'<td>'+loc.lat+'</td><td>'+loc.lng+'</td>'+
				'<td>'+located_in+'</td>'+
				// TODO: translation needed
				'<td>'+(+loc.is_default?'Yes':'')+'</td>'+
				'<td>'+links.join(', ')+'</td></tr>';
		}
		table+='</tbody></table>';
		var $table=$(table)
			.appendTo($content);
		$table.dataTable();
		$('<a href="#">'+__('Add Location')+'</a>')
			.click(function() {
				$('<form id="locations-form"><table>'+
					'<tr><th>'+__('Name')+'</th><td><input name="name"/></td></tr>'+
					'<tr><th>'+__('Map')+'</th><td><a href="#" class="map-opener" '+
					'lat="#location-lat" lng="#location-lng">'+__('Click to open')+'</a></td></tr>'+
					'<tr><th>'+__('Latitude')+'</th><td><input id="location-lat" name="lat"/></td></tr>'+
					'<tr><th>'+__('Longitude')+'</th><td><input id="location-lng" name="lng"/></td></tr>'+
					'<tr><th>'+__('Located In')+'</th><td><select id="location-locatedin">'+
					'</select></td></tr>'+
					'</table></form>'
				)
					.dialog({
						'modal':true,
						'close':function() {
							$('#locations-form').remove();
						},
						'buttons': {
							// TODO: translation needed
							'Add': function() {
								$.post('/a/f=adminLocationsAdd',
									$('#locations-form').serialize(),
									CoreSiteoptions_screenLocations
								);
								$('#locations-form').remove();
							},
							// TODO: translation needed
							'Cancel': function() {
								$('#locations-form').remove();
							}
						}
					});
				return false;
			})
			.appendTo($content);
		$('#locations-table').on('click', '.delete', function() {
			var id=$(this).closest('tr').attr('cid');
			// TODO: translation needed
			if (!confirm('Are you sure you want to delete this location?')) {
				return;
			}
			$.post(
				'/a/f=adminLocationDelete/id='+id,
				function(ret) {
					if (ret.error) {
						return alert(ret.error);
					}
					CoreSiteoptions_screenLocations();
				}
			);
			return false;
		});
		$('#locations-table').on('click', '.edit', function() {
			var id=$(this).closest('tr').attr('cid');
			var loc, i;
			for (i=0;i<locations.length;++i) {
				if (locations[i].id==id) {
					loc=locations[i];
				}
			}
			var parents=['<option value="0"> - </option>'];
			for (i=0;i<locations.length;++i) {
				var opt='<option value="'+locations[i].id+'">';
				var name=getParent(+locations[i].parent_id)+locations[i].name;
				opt+=name+'</option>';
				parents.push(opt);
			}
			$('<form id="locations-form"><input name="id" type="hidden"/><table>'+
				'<tr><th>'+__('Name')+'</th><td><input name="name"/></td></tr>'+
				'<tr><th>'+__('Map')+'</th><td><a href="#" class="map-opener" '+
				'lat="#location-lat" lng="#location-lng">'+__('Click to open')+'</a></td></tr>'+
				'<tr><th>'+__('Latitude')+'</th><td><input id="location-lat" name="lat"/></td></tr>'+
				'<tr><th>'+__('Longitude')+'</th><td><input id="location-lng" name="lng"/></td></tr>'+
				'<tr><th>'+__('Located In')+'</th><td><select id="location-parent_id"'+
				' name="parent_id">'+parents.join('')+'</select></td></tr>'+
				'<tr><th>'+__('Is Default')+'</th><td><select name="is_default">'+
				'<option value="0">'+__('No')+'</option><option value="1">'+__('Yes')+'</option>'+
				'</select></td></tr>'+
				'</table></form>'
			)
				.dialog({
					'modal':true,
					'close':function() {
						$('#locations-form').remove();
					},
					'buttons': {
						// TODO: translation needed
						'Save': function() {
							$.post('/a/f=adminLocationsEdit',
								$('#locations-form').serialize(),
								CoreSiteoptions_screenLocations
							);
							$('#locations-form').remove();
						},
						// TODO: translation needed
						'Cancel': function() {
							$('#locations-form').remove();
						}
					}
				});
			for (var k in loc) {
				$('#locations-form *[name='+k+']').val(loc[k]);
			}
			return false;
		});
	});
}
function CoreSiteoptions_screenMenus() {
	function save(callback) {
		$.post('/a/f=adminAdminVarsSave/name=admin_menu', {
			'val':$.toJSON(menus)
		}, function() {
			Core_menuShow(menus);
			if (callback) {
				callback();
			}
		});
	}
	function hasSubItems(obj) {
		var subitems=0;
		$.each(obj, function(key, val) {
			if (typeof key == 'number') {
				return;
			}
			subitems+=typeof val!='string' && !/^_/.test(key);
		});
		return subitems;
	}
	function showMenuNames(path, nodetailschange, select) {
		if (!path) {
			path='';
		}
		var $menuCurrentTop=$('#menu-current-top').empty();
		var currentTop=menus, i;
		var links=['<a href="#" data-path="">top</a>'];
		if (path) {
			var bits=path.split('|');
			var tmpPath='';
			for (i=0;i<bits.length;++i) {
				var name=bits[i];
				currentTop=currentTop[name];
				tmpPath=tmpPath+name;
				links.push('<a href="#" data-path="'+tmpPath+'">'+name+'</a>');
				tmpPath+='|';
			}
		}
		$menuCurrentTop.html(links.join(' &raquo; '));
		var $wrapper=$('#menu-items');
		// { draw menu items
		var menuItems=[], menuOrds=[];
		if (path!=='') {
			path+='|';
		}
		$.each(currentTop, function(key, val) {
			if (/^_/.test(key)) {
				return;
			}
			var ord=val._ord||0;
			var cols=[
				'',
				'<a class="menuname" href="#" data-ord="'+ord+
				'" data-path="'+path+key+'">'+key+'</a>',
				''
			];
			if (val._icon) {
				cols[0]='<img src="/a/f=getImg/w=20/h=20/'+val._icon+'"/>';
			}
			else {
				cols[0]='<img style="width:20px;height:20px" src="/i/blank.gif"/>';
			}
			if (hasSubItems(val)) {
				cols[2]='<a class="subitems" href="#" data-path="'+path+key+'">'+
					'&raquo;</a>';
			}
			else {
				cols[2]='<a class="subitems faded" href="#" data-path="'+path+key+'">'+
					'&raquo;</a>';
			}
			menuItems.push(
				'<tr>'+
				'<td style="width:20px">'+cols[0]+'</td>'+
				'<td>'+cols[1]+'</td>'+
				'<td style="width:20px">'+cols[2]+'</td>'+
				'</tr>'
			);
			menuOrds.push(ord);
		});
		for (i=0;i<menuOrds.length-1;++i) {
			for (var j=i+1;j<menuOrds.length;++j) {
				if (menuOrds[j]<menuOrds[i]) {
					var tmp=menuOrds[i];
					menuOrds[i]=menuOrds[j];
					menuOrds[j]=tmp;
					tmp=menuItems[i];
					menuItems[i]=menuItems[j];
					menuItems[j]=tmp;
				}
			}
		}
		$wrapper.html('<table>'+menuItems.join('')+'</table>');
		// }
		if (!nodetailschange) {
			showDetails('');
		}
		$('#menu-items tbody').sortable({
			'update':function() {
				var $links=$('#menu-items a.menuname');
				$links.each(function(key) {
					var $this=$(this);
					var name=$this.text();
					currentTop[name]._ord=key;
					$this.data('_ord', key);
				});
				save();
			}
		});
		$wrapper.append(
			$('<a class="add-new" href="#">'+__('Add new item')+'</a>')
				.click(function() {
					var names=[];
					$('#menu-items a.menuname').each(function() {
						names.push($(this).text());
					});
					// TODO: translation needed
					var newName='New Item ', i=1;
					while($.inArray(newName+i, names)!=-1) {
						++i;
					}
					newName+=i;
					currentTop[newName]={'_link':'#','_ord':999};
					save(function() {
						showMenuNames(path, false, newName);
					});
				})
		);
		if (select) {
			$('#menu-items a:contains('+select+')').click();
		}
	}
	function showDetails(path) {
		if (path==='') {
			$('#menu-details').html(
				'<div><h3>'+__('No menu item selected')+'</h3>'+
				'<p>'+__('Click an item in the left menu to select it.')+'</p></div>'
			);
			var $list=$('<ul>');
			$list.append(
				$('<li><a href="#">'+__('Set this menu as the default admin menu.')+'</a></li>')
					.click(function() {
						// TODO: translation needed
						if (!confirm(
							'This will set the default admin menu to this one.'+
							' Are you sure?'
						)) {
							return;
						}
						$.post('/a/f=adminMenuSetMineAsDefault', function() {
							// TODO: translation needed
							return alert('Saved');
						});
					}),
				$('<li><a href="#">'+__('Reset my menu to the default admin menu.')+'</a></li>')
					.click(function() {
						// TODO: translation needed
						if (!confirm(
							'This will remove any changes you have made, and reset your'+
							' menu to the default admin menu. Are you sure?'
						)) {
							return;
						}
						$.post('/a/f=adminMenuClearMine', function() {
							CoreSiteoptions_screenMenus();
							// TODO: translation needed
							return alert('Saved');
						});
					}),
				$('<li><a href="#">'+__('Reset all admin menus to the default admin menu.')+'</a></li>')
					.click(function() {
						if (!confirm(
							// TODO: translation needed
							'This will clear all admin\'s menus and set them'+
							' to the default admin menu. Are you sure?'
						)) {
							return;
						}
						$.post('/a/f=adminMenuClearAllAdmins', function() {
							CoreSiteoptions_screenMenus();
							// TODO: translation needed
							return alert('Saved');
						});
					}),
				$('<li><a href="#">'+__('Reset all admin menus to "factory default"')+'</a></li>')
					.click(function() {
						// TODO: translation needed
						if (!confirm(
							'This will reset all menus to the factory default. You probably'+
							' DON\'T want to do this. Are you sure?'
						)) {
							return;
						}
						$.post('/a/f=adminMenuClearAll', function() {
							CoreSiteoptions_screenMenus();
							// TODO: translation needed
							return alert('Saved');
						});
					})
			);
			$list.appendTo('#menu-details>div');
			return;
		}
		var currentTop=menus;
		var currentParent=menus;
		var name='';
		var bits=path.split('|');
		for (var i=0;i<bits.length;++i) {
			name=bits[i];
			currentParent=currentTop;
			currentTop=currentTop[name];
		}
		// { details table
		var deleteLink=hasSubItems(currentTop)?
			'':
			'<a href="#" class="delete">'+__('[x]')+'</a>';
		var imgSrc=currentTop._icon?
			'/a/f=getImg/w=20/h=20/'+currentTop._icon:
			'/i/blank.gif';
		var table='<div>'+deleteLink+'<table>'+
			'<tr><th>'+__('Name')+'</th><td><input name="_name"/></td></tr>'+
			'<tr><th>'+__('Link')+'</th><td><input name="_link"/></td></tr>'+
			'<tr><th>'+__('Icon')+'</th><td><img class="menu-icon"'+
			' src="'+imgSrc+'"/>'+
			'<input class="_icon" name="_icon"/></td></tr>'+
			'<tr><th>'+__('Target')+'</th><td><select name="_target"><option></option>'+
			'<option value="_blank">'+__('New page')+'</option></select></td></tr>'+
			'</table></div>';
		// }
		var $details=$('#menu-details');
		$details.html(table);
		$.each(currentTop, function(key, val) {
			if (typeof key == 'number' || !/^_/.test(key)) {
				return;
			}
			var $inp=$(
				'input[name="'+key+'"],select[name="'+key+'"]',
				'#menu-details'
			);
			if (!$inp.length && key!='_ord') {
				return;
			}
			$inp.val(val);
		});
		$('._icon', $details).saorfm({
			'rpc':'/ww.incs/saorfm/rpc.php',
			'select':'file'
		});
		$('input[name="_name"]', $details).val(name);
		$('select,input', '#menu-details').change(function() {
			var $this=$(this),key=$this.attr('name'), val=$this.val();
			if (key=='_name') {
				currentParent[val]=currentTop;
				delete currentParent[name];
				var ppath=/\|/.test(path)?path.replace(/\|[^|]*/, ''):'';
				showMenuNames(ppath, true);
			}
			else {
				currentTop[key]=val;
			}
			save();
		});
		$('._icon', $details).change(function() {
			var src='/i/blank.gif', $this=$(this);
			if ($this.val()) {
				src='/a/f=getImg/w=20/h=20/'+$this.val();
			}
			$('.menu-icon', $details).attr('src', src);
		});
		$('a.delete', $details).click(function() {
			// TODO: translation needed
			if (!confirm('Are you sure you want to delete this menu item?')) {
				return;
			}
			delete currentParent[name];
			var ppath=/\|/.test(path)?path.replace(/\|[^|]*/, ''):'';
			showMenuNames(ppath);
			save();
		});
		// { "copy details from"
		var $copy=$(
			// TODO: translation needed
			'<select class="menu-copy"><option value="">Copy details from...'+
			'</option></select>'
		).appendTo($details);
		function getOpts(menuItems, path, depth) {
			var html='';
			if (path) {
				path+='|';
			}
			$.each(menuItems, function(key) {
				if (/^_/.test(key)) {
					return;
				}
				html+='<option value="'+path+key+'">'+
					(new Array(depth+1)).join(' > ')+key+
					'</option>';
				html+=getOpts(menuItems[key], path+key, depth+1);
			});
			return html;
		}
		$copy
			.append(getOpts(menusDefault, '', 0))
			.change(function() {
				var val=$(this).val();
				if (val==='' || !confirm('Are you sure you want to over-write this item?')
				) {
					return;
				}
				$.each(currentTop, function(key) {
					if (!/^_/.test(key) || key=='_ord' || key=='_name') {
						return;
					}
					delete currentTop[key];
				});
				var bits=val.split('|');
				var obj=menusDefault;
				for (var i=0;i<bits.length;++i) {
					obj=obj[bits[i]];
				}
				$.each(obj, function(key) {
					if (!/^_/.test(key) || key=='_ord' || key=='_name') {
						return;
					}
					currentTop[key]=obj[key];
				});
				save(function() {
					showDetails(path);
				});
			});
		// }
	}
	// { initialise
	var $content=$('#content').empty();
	var menus, menusDefault;
	$.post('/a/f=adminMenusGet', function(ret) {
		menus=ret;
		Core_menuShow(menus);
		$.post('/a/f=adminMenusGetDefault', function(ret) {
			menusDefault=ret;
			showMenuNames();
		});
	});
	var html='<table id="menus-wrapper">'+
		'<tr style="height:20px;"><td id="menu-current-top" colspan="2"></td></tr>'+
		'<tr><td id="menu-items"></td><td id="menu-details"></td></tr></table>';
	$(html).appendTo($content);
	$('#menu-current-top').on('click', 'a', function() {
		var path=$(this).data('path');
		showMenuNames(path);
	});
	$('#menu-items').on('click', 'a.subitems', function() {
		var path=$(this).data('path');
		showMenuNames(path);
	});
	$('#menu-items').on('click', 'a.menuname', function() {
		var path=$(this).data('path');
		showDetails(path);
	});
	// }
}
function CoreSiteoptions_screenEmails() {
	function showEmailsSent(panel) {
		$(panel).empty()
			.html('<div><table id="emails-sent-datatable">'+
				'<thead>'+
				'<tr><th>'+__('Date')+'</th><th>'+__('Recipient')+
				'</th><th>'+__('Subject')+'</th><th/></tr>'+
				'</thead>'+
				'<tbody/></table></div>');
		var params={
			'sAjaxSource':'/a/f=adminEmailsSentDT',
			'bProcessing':true,
			'bJQueryUI':true,
			'bServerSide':true,
			'fnRowCallback':function( nRow, aData) {
				$('<a href="javascript:" data-id="'+aData[3]+'">'+__('View')+'</a>')
					.appendTo($('td:nth-child(4)', nRow).empty())
					.click(function() {
						var w=$(window).width(), h=$(window).height();
						var html='<div><iframe style="width:100%;height:100%"'+
							' src="/a/f=adminEmailSentGet/id='+$(this).data('id')+
							'"></iframe></div>';
						var $iframe=$(html).dialog({
							'modal':true,
							'width':w-100,
							'height':h-100,
							'close':function() {
								$iframe.remove();
							}
						});
					});
				return nRow;
			}
		};
		$('#emails-sent-datatable').dataTable(params);
	}
	function showTemplates(panel) {
		var $panel=$(panel).empty();
		var html='<select id="email-templates-list">'+
			// TODO: translation needed
			'<option>-- choose --</option></select>'+
			'<button disabled id="email-templates-download">'+__('Download')+'</button>'+
			'<textarea disabled id="email-templates-source"></textarea>'+
			'<button disabled id="email-templates-upload">'+__('Upload')+'</button>'+
			'<button disabled id="email-templates-save">'+__('Save')+'</button>';
		$.post('/a/f=adminEmailTemplatesList', function(ret) {
			// TODO: translation needed
			var opts='<option>-- choose --</option>';
			for (var i=0;i<ret.length;++i) {
				opts+='<option>'+ret[i].name+'</option>';
			}
			opts+='<option class="new" value="-1">'+__('New template')+'</option>';
			$('#email-templates-list').html(opts);
		});
		$panel.html(html);
		$('#email-templates-list').change(function() {
			var val=$(this).val();
			if (val=='-1') {
				editor.setValue('');
				editor.setOption('readOnly', true);
				$('button', $panel).attr('disabled', true);
				$('#email-templates-list').val('');
				return Core_prompt(
					'What should the new template be named?',
					name,
					function(name) {
						if (name==='') {
							return false;
						}
						if (name.replace(/[^a-zA-Z0-9]/g, '')!=name) {
							alert('Invalid name. Please use only letters and numbers.');
							return false;
						}
						return true;
					},
					function(name) {
						$.post('/a/f=adminEmailTemplateSet', {
							'name': name,
							'body': ''
						}, function(ret) {
							if (ret.error) {
								return alert(ret.error);
							}
							$('<option value="'+name+'">'+name+'</option>')
								.insertAfter('#email-templates-list option:first-child');
							$('#email-templates-list').val(name).change();
						});
					}
				);
			}
			// TODO: translation needed
			if (val=='-- choose --') {
				editor.setValue('');
				editor.setOption('readOnly', true);
				$('#email-templates-save').attr('disabled', true);
				return;
			}
			$('button', $panel).attr('disabled', false);
			$.post('/a/f=adminEmailTemplateGet', {
				'name':val
			}, function(ret) {
				editor.setValue(ret);
				editor.setOption('readOnly', false);
			});
		});
		var $textarea=$('#email-templates-source');
		var editor = CodeMirror
			.fromTextArea($textarea[0], {
				mode: {
					name: 'smarty',
					leftDelimiter: '{{',
					rightDelimiter: '}}'
				},
				indentUnit: 1,
				indentWithTabs: true,
				lineWrapping:true,
				lineNumbers:true,
				readOnly:true
			});
		$('.CodeMirror-scroll').css({
			'height':($(window).height()-$('#content').offset().top-150)+'px',
			'border':'1px solid #000'
		});
		$('#email-templates-save').click(function() {
			var val=$('#email-templates-list').val();
			// TODO: translation needed
			if (val=='-- Choose --') {
				return;
			}
			var body=editor.getValue();
			$.post('/a/f=adminEmailTemplateSet', {
				'name': val,
				'body': body
			}, function() {
				// TODO: translation needed
				alert('Saved');
			});
		});
		$('#email-templates-download').click(function() {
			var val=$('#email-templates-list').val();
			// TODO: translation needed
			if (val=='-- Choose --') {
				return;
			}
			document.location='/a/f=adminEmailTemplateDownload/name='+val;
		});
		$('#email-templates-upload')
			.css('height',20)
			.uploadify({
				'swf':'/j/jquery.uploadify/uploadify.swf',
				'auto':'true',
				'checkExisting':false,
				'cancelImage':'/i/blank.gif',
				'buttonImage':'/i/choose-file.png',
				'height':20,
				'width':91,
				'uploader':'/a/f=adminEmailTemplateUpload',
				'postData':{
					'PHPSESSID':window.sessid
				},
				'upload_success_handler':function(file, data){
					var ret=eval('('+data+')');
					if (ret.ok) {
						$('#email-templates-list').change();
					}
				}
			});
	}
	var $content=$('#content').empty().append('<h1>'+__('Emails')+'</h1>');
	// { show tabs
	$('<div><ul>'+
		'<li><a href="#tab-emails-sent">'+__('Emails Sent')+'</a></li>'+
		'<li><a href="#tab-templates">'+__('Templates')+'</a></li>'+
		'</ul>'+
		'<div id="tab-emails-sent"/><div id="tab-templates"/>'+
		'</div>')
		.appendTo($content)
		.tabs({
			'show':function(e, ui) {
				switch(ui.index) {
					case 0: // { emails sent
						showEmailsSent(ui.panel);
					break; // }
					case 1: // { templates
						showTemplates(ui.panel);
					break; // }
				}
			}
		});
	// }
}
$(document).on('click', '.map-opener', function() {
	var $this=$(this);
	var $lat=$($this.attr('lat')), $lng=$($this.attr('lng'));
	if (!window.google || !google.maps) {
		$('<script src="http://maps.googleapis.com/maps/api/js?sensor=false&c'+
			'allback=Core_siteOptions_mapinit"></script>')
			.appendTo(document.body);
		return;
	}
	$('<div id="siteoptions-map" style="width:800px;height:500px">'+__('Loading...')+'</div>')
		.dialog({
			'modal':'true',
			'close':function() {
				$('#siteoptions-map').remove();
				$(this).remove();
			},
			'width':800,
			'height':550,
			'buttons':{
				// TODO: translation needed
				'Save':function() {
					var ctr=map.getCenter();
					$lat.val(ctr.lat());
					$lng.val(ctr.lng());
					$('#siteoptions-map').remove();
					$(this).remove();
				}
			}
		});
	var latlng=[
		$lat.val(),
		$lng.val()
	];
	var myOptions={
		zoom:8,
		center:new google.maps.LatLng(latlng[0], latlng[1]),
		mapTypeId:google.maps.MapTypeId.ROADMAP
	};
	var map=new google.maps.Map($('#siteoptions-map')[0], myOptions);
	var reticleImage=new google.maps.MarkerImage(
		'/i/reticle-32x32.png',
		new google.maps.Size(32,32),
		new google.maps.Point(0,0),
		new google.maps.Point(16,16)
	);
	var reticleShape={
		coords:[16,16,16,16],
		type:'rect'
	};
	var reticleMarker=new google.maps.Marker({
		position:map.getCenter(),
		map: map,
		icon: reticleImage,
		shape: reticleShape,
		optimized: false,
		zIndex:5
	});
	var addressWindow=new google.maps.InfoWindow();
	google.maps.event.addListener(map, 'bounds_changed', function(){
		reticleMarker.setPosition(map.getCenter());
		var geocoder=new google.maps.Geocoder();
		var ctr=map.getCenter();
		geocoder.geocode({
			'latLng': new google.maps.LatLng(ctr.lat(), ctr.lng())
		}, function(res) {
			addressWindow.close();
			if (res && res[1]) {
				addressWindow.setContent(res[1].formatted_address);
				addressWindow.open(map, reticleMarker);
			}
		});
	});
});
