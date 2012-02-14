function CoreSiteoptions_screen(page) {
	Core_sidemenu(
		[ 'General', 'Users', 'Themes', 'Plugins', 'Cron', 'Languages' ],
		'CoreSiteoptions',
		page
	);
	window['CoreSiteoptions_screen'+page]();
}
function CoreSiteoptions_screenCron() {
	var $content=$('#content').empty();
	$.post('/a/f=adminCronGet', function(ret) {
		var table='<table><thead><tr><th>Name</th><th>Period</th>'
			+'<th>Next</th><th>Description</th></tr></thead>'
			+'<tbody>';
		for (var i=0;i<ret.length;++i) {
			var cron=ret[i];
			table+='<tr cid="'+cron.id+'"><td>'+cron.name+'</td><td class="clickable">'
				+cron.period_multiplier+' '+cron.period+'</td><td class="clickable"'
				+'>'+cron.next_date+'</td><td>'+cron.notes+'</td></tr>';
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
				var parts=$this.text().split(' ');
				var periods=['minute', 'hour', 'day', 'week', 'month', 'year'];
				var html='<select>';
				for (var i=1;i<32;++i) {
					html+='<option>'+i+'</option>';
				}
				html+='</select><select>';
				for (var i=0;i<periods.length;++i) {
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
						onClose: function(dateText, inst){
							var url='/a/f=adminCronSave/id='+id+'/field=next_date';
							$.post(url+'/value='+dateText);
							$this.html(dateText).removeAttr('clicked');
						}
					})
					.focus();
				break; // }
		}
		$this.attr('clicked', 1)
	}
}
function CoreSiteoptions_screenLanguages() {
	$('#content').empty().html('<select><option>list languages</option><option>translations</option></select><div id="lang-content"/>');
	var $content=$('#lang-content');
	$('#content select').change(function() {
		switch ($(this).val()) {
			case 'list languages':
				return showLanguages();
			case 'translations':
				return showTranslations();
		}
	});

	function showLanguages() {
		$content.empty();
		$.post('/a/f=languagesGet', function(languages) {
			var table='<table id="languages-table"><thead>'
				+'<tr><th>Name</th><th>Code</th>'
				+'<th>Default</th><th>&nbsp;</th></tr></thead>'
				+'<tbody>';
			for (var i=0;i<languages.length;++i) {
				var lang=languages[i];
				var links=['<a href="#" class="edit">edit</a>'];
				if (!(+lang.is_default)) {
					links.push('<a href="#" class="delete">[x]</a>');
				}
				table+='<tr cid="'+lang.id+'"><td>'+lang.name+'</td>'
					+'<td>'+lang.code+'</td><td>'+(+lang.is_default?'Yes':'')+'</td>'
					+'<td>'+links.join(', ')+'</td></tr>';
			}
			table+='</tbody></table>';
			var $table=$(table)
				.appendTo($content);
			$table.dataTable(
			);
			$('<a href="#">Add Language</a>')
				.click(function() {
					$('<form id="languages-form"><table>'
						+'<tr><th>Name</th><td><input name="name"/></td></tr>'
						+'<tr><th>Code <a href="http://en.wikipedia.org/wiki/List_of_ISO_63'
						+'9-1_codes" target="_blank">#</a></th><td>'
						+'<input name="code" class="small"/></td></tr>'
						+'</table></form>'
					)
						.dialog({
							'modal':true,
							'close':function() {
								$('#languages-form').remove();
							},
							'buttons': {
								'Add': function() {
									$.post('/a/f=adminLanguagesAdd',
										$('#languages-form').serialize(),
										showLanguages
									);
									$('#languages-form').remove();
								},
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
				if (!confirm('are you sure you want to delete this language?')) {
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
				$('<form id="languages-form"><input name="id" type="hidden"/><table>'
					+'<tr><th>Name</th><td><input name="name"/></td></tr>'
					+'<tr><th>Code <a href="http://en.wikipedia.org/wiki/List_of_ISO_63'
					+'9-1_codes" target="_blank">#</a></th><td>'
					+'<input name="code" class="small"/></td></tr>'
					+'<tr><th>Is Default</th><td><select name="is_default">'
					+'<option value="0">No</option><option value="1">Yes</option>'
					+'</select></td></tr>'
					+'</table></form>'
				)
					.dialog({
						'modal':true,
						'close':function() {
							$('#languages-form').remove();
						},
						'buttons': {
							'Save': function() {
								$.post('/a/f=adminLanguagesEdit',
									$('#languages-form').serialize(),
									showLanguages
								);
								$('#languages-form').remove();
							},
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
		var $languages=$('<select><option> -- loading -- </option></select>')
			.appendTo($content.empty());
		var currentTr=[];
		var $languagestable;
		$.post('/a/f=languagesGet', function(languages) {
			var opts='<option value=""> -- choose a language -- </option>';
			for (var i=0;i<languages.length;++i) {
				opts+='<option value="'+languages[i].code+'">'+languages[i].name+'</option>';
			}
			$languages.html(opts).change(changeLanguage);
		});
		$.post('/a/f=adminLanguagesGetStrings', function(strings) {
			var table='<table id="languages-table"><thead>'
				+'<tr><th>String</th><th>Context</th>'
				+'<th>Translation</th></tr></thead>'
				+'<tbody>';
			var links=[];
			for (var i=0;i<strings.length;++i) {
				var str=strings[i];
				table+='<tr><td>'+htmlspecialchars(str.str)+'</td>'
					+'<td>'+str.context+'</td><td class="context">-- choose a language --</td>'
					+'</tr>';
			}
			table+='</tbody></table>';
			$languagestable=$(table)
				.appendTo($content)
				.dataTable({
					fnDrawCallback:updateVisibleTranslations,
					"aoColumns": [
						{ "sWidth": "46%" },
						{ "sWidth": "8%" },
						{ "sWidth": "46%"}
					]
				});
			$languagestable.on('click', 'tbody td:last-child', function() {
				if ($languages.val()=='') {
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
		function changeLanguage() {
			var lang=$(this).val();
			if (lang=='') {
				$languagestable.find('.context')
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
			if ($languages.val()=='') {
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
	}
	showLanguages();
}
function CoreSiteoptions_screenLocations() {
	var $content=$('#content').empty();
	$.post('/a/f=locationsGet', function(locations) {
		var table='<table id="locations-table"><thead>'
			+'<tr><th>Name</th><th>Lat</th><th>Lng</th>'
			+'<th>Default</th><th>&nbsp;</th></tr></thead>'
			+'<tbody>';
		for (var i=0;i<locations.length;++i) {
			var loc=locations[i];
			var links=['<a href="#" class="edit">edit</a>'];
			if (!(+loc.is_default)) {
				links.push('<a href="#" class="delete">[x]</a>');
			}
			table+='<tr cid="'+loc.id+'"><td>'+loc.name+'</td>'
				+'<td>'+loc.lat+'</td><td>'+loc.lng+'</td>'
				+'<td>'+(+loc.is_default?'Yes':'')+'</td>'
				+'<td>'+links.join(', ')+'</td></tr>';
		}
		table+='</tbody></table>';
		var $table=$(table)
			.appendTo($content);
		$table.dataTable();
		$('<a href="#">Add Location</a>')
			.click(function() {
				$('<form id="locations-form"><table>'
					+'<tr><th>Name</th><td><input name="name"/></td></tr>'
					+'<tr><th>Map</th><td><a href="#" class="map-opener" '
					+'lat="#location-lat" lng="#location-lng">click to open</a></td></tr>'
					+'<tr><th>Latitude</th><td><input id="location-lat" name="lat"/></td></tr>'
					+'<tr><th>Longitude</th><td><input id="location-lng" name="lng"/></td></tr>'
					+'</table></form>'
				)
					.dialog({
						'modal':true,
						'close':function() {
							$('#locations-form').remove();
						},
						'buttons': {
							'Add': function() {
								$.post('/a/f=adminLocationsAdd',
									$('#locations-form').serialize(),
									CoreSiteoptions_screenLocations
								);
								$('#locations-form').remove();
							},
							'Cancel': function() {
								$('#locations-form').remove();
							}
						}
					});
				return false;
			})
			.appendTo($content);
		$('#locations-table .delete').click(function() {
			var id=$(this).closest('tr').attr('cid');
			if (!confirm('are you sure you want to delete this location?')) {
				return;
			}
			$.post(
				'/a/f=adminLocationsDelete/id='+id,
				CoreSiteoptions_screenLocations
			);
			return false;
		});
		$('#locations-table .edit').click(function() {
			var id=$(this).closest('tr').attr('cid');
			var location;
			for (var i=0;i<locations.length;++i) {
				if (locations[i].id==id) {
					location=locations[i];
				}
			}
			$('<form id="locations-form"><input name="id" type="hidden"/><table>'
				+'<tr><th>Name</th><td><input name="name"/></td></tr>'
				+'<tr><th>Map</th><td><a href="#" class="map-opener" '
				+'lat="#location-lat" lng="#location-lng">click to open</a></td></tr>'
				+'<tr><th>Latitude</th><td><input id="location-lat" name="lat"/></td></tr>'
				+'<tr><th>Longitude</th><td><input id="location-lng" name="lng"/></td></tr>'
				+'<tr><th>Is Default</th><td><select name="is_default">'
				+'<option value="0">No</option><option value="1">Yes</option>'
				+'</select></td></tr>'
				+'</table></form>'
			)
				.dialog({
					'modal':true,
					'close':function() {
						$('#locations-form').remove();
					},
					'buttons': {
						'Save': function() {
							$.post('/a/f=adminLocationsEdit',
								$('#locations-form').serialize(),
								CoreSiteoptions_screenLocations
							);
							$('#locations-form').remove();
						},
						'Cancel': function() {
							$('#locations-form').remove();
						}
					}
				});
			for (var k in location) {
				$('#locations-form *[name='+k+']').val(location[k]);
			}
			return false;
		});
	});
}
function CoreSiteoptions_screenStats() {
	var $content=$('#content').empty();
	$.post('/a/f=adminStatsGet', function(ret) {
	});
}
$(document).on('click', '.map-opener', function() {
	var $this=$(this);
	var $lat=$($this.attr('lat')), $lng=$($this.attr('lng'));
	if (!window.google || !google.maps) {
		$('<script src="http://maps.googleapis.com/maps/api/js?sensor=false&c'
			+'allback=Core_siteOptions_mapinit"></script>')
			.appendTo(document.body);
		return;
	}
	$('<div id="siteoptions-map" style="width:800px;height:500px">loading...</div>')
		.dialog({
			'modal':'true',
			'close':function() {
				$('#siteoptions-map').remove();
				$(this).remove();
			},
			'width':800,
			'height':550,
			'buttons':{
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
		}, function(res, status) {
			addressWindow.close();
			if (res && res[1]) {
				addressWindow.setContent(res[1].formatted_address);
				addressWindow.open(map, reticleMarker);
			}
		});
	});
});
