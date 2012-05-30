(function($) {
	var saorfm_id=0;
	var saorfm_iframes=0;
	function hideSubMenus(el){
		var $el=$(el);
		var $wrapper=$el.data('menu');
		if (!$wrapper) {
			return;
		}
		$wrapper.find('>li').each(function(){
			hideSubMenus(this);
		});
		$wrapper.remove();
		$el.data('menu',null);
		return;
	}
	function menuToggle(el, config){
		stopClearAll();
		var $el=$(el);
		// { if this is a second click, then close the sub-menu and return
		if($el.data('menu')){
			hideSubMenus(el);
			if ($el.is('.saorfm-button')) {
				$(document).off('click', clearAll);
			}
			return;
		}
		// }
		// { hide any other open sub-menus in this level
		if (el.tagName!='BUTTON') {
			$el.closest('ul').find('>li').each(function(){
				hideSubMenus(this);
			});
		}
		// }
		// { retrieve the directory info and show the menu
		$.get(
			config.rpc+'?action=listFiles&directory='+$el.data('directory'),
			function(ret){
				var $wrapper=$(
					'<ul class="saorfm-menu"></ul>'
				);
				var $parent=$el.is('.saorfm-button')
					?$($el.data('input'))
					:$el;
				var offset=$parent.offset();
				// { check is this a top-level menu
				if ($el.is('.saorfm-button')) {
					$wrapper[0].id='saorfm-menu-wrapper-'+(saorfm_id++);
					$wrapper
						.css({
							"top":  offset.top+$parent.outerHeight(),
							"left": offset.left
						})
					if (config.download || config['delete']) {
						var fname=$(el).prev().val();
						if (fname && /^\/.*[^\/]$/.test(fname)) {
							if (config.download) {
								$(
									'<li class="saorfm-clickable" id="'
									+$wrapper[0].id+'-download'
									+'">download file</li>'
								)
									.click(function(){
										stopClearAll();
										$(
											'<iframe src="'+config.rpc+'?action=get&file='+fname
											+'&forcedownload" '
											+'style="border:0;width:0;height:0;"></iframe>'
										)
											.insertAfter(this);
									})
									.appendTo($wrapper);
							}
							if (config['delete']) {
								$(
									'<li class="saorfm-clickable" id="'
									+$wrapper[0].id+'-delete'
									+'">delete file</li>'
								)
									.click(function(){
										stopClearAll();
										if (!confirm('Are you sure you want to delete the selected file?')) {
											return;
										}
										$.get(config.rpc+'?action=delete&file='+fname,
											function(result){
												if (!result.error) {
													var $input=$($el.data('input'));
													$input.val('');
													hideSubMenus($input.next());
												}
												else {
													$(
														'<div id="saorfm-error"><h2>Error</h2>'
														+'<p>'+result.error+'</p></div>'
													).dialog({"modal":true});
												}
											},
											'json'
										);
									})
									.appendTo($wrapper);
							}
						}
					}
				}
				// }
				// { else it's a submenu
				else {
					$wrapper[0].id=$parent[0].id+'-menu';
					$wrapper
						.css({
							"top":  offset.top,
							"left": offset.left+$parent.outerWidth()
						})
				}
				// }
				// { if no files or directories, show "Empty"
				if (!ret.length) {
					$wrapper.append('<li><i>Empty</i></li>');
				}
				// }
				// { else show list of files and directories
				for(var i=0;i<ret.length;++i){
					var f=ret[i];
					if (f.d) { // this is a directory
						var $li=$('<li class="saorfm-directory">')
							.text(f.n)
							.data('directory',$el.data('directory')+f.n+'/')
							.data('input',$el.data('input'))
							.click(function(){
								menuToggle(this,config);
							})
							.append(
								'<span class="ui-icon ui-icon-triangle-1-e '
								+'saorfm-directory-icon">&nbsp;</span>'
							)
							.attr('id',$wrapper[0].id+'-'+i)
							.appendTo($wrapper);
					}
					else { // it's a file
						var $li=$('<li class="saorfm-file">')
							.text(f.n)
							.appendTo($wrapper);
						if (config.select&1) {
							$li
								.click(function(){
									stopClearAll();
									var $input=$($el.data('input'));
									var url=$el.data('directory')+$(this).text();
									$input.val(config.prefix+url);
									hideSubMenus($input.next());
									$input.change();
								})
								.attr('id',$wrapper[0].id+'-'+i)
								.css('cursor','pointer');
						}
					}
				}
				// }
				// { add file uploader if applicable
				if (config.upload) {
					var $li=$(
						'<li class="saorfm-clickable" id="'
						+$wrapper[0].id+'-upload'
						+'"><form style="position:relative;overflow:hidden;padding:0;'
						+'margin:0" target="saorfm-iframe-'+saorfm_iframes
						+'" enctype="multipart/form-data" method="post" '
						+'action="'+config.rpc+'">'
						+'<input name="action" type="hidden" value="upload" />'
						+'<input name="directory" type="hidden" value="'
						+$el.data('directory')+'" />'
						+'upload file</form>'
						+'<iframe style="display:none" name="saorfm-iframe-'
						+(saorfm_iframes++)+'" /></li>'
					)
					$(
						'<input type="file" style="position:absolute;left:0;'
						+'top:0;right:0;bottom:0;opacity:0;" name="file" value="" />'
					)
						.click(function() {
							stopClearAll();
						})
						.change(function(){
							var $form=$(this).closest('form');
							$form.closest('li').find('iframe')[0].onload=function(){
								setTimeout((function($parent,config){
									return function(){
										menuToggle($parent,config);
									};
								})($parent,config), 1);
								menuToggle($parent, config);
							};
							$form
								.css('text-decoration','blink')
								.submit()
								.text('uploading...');
						})
						.appendTo($li.find('form'));
					$li.appendTo($wrapper);
				}
				// }
				// { add directory selector if applicable
				if (config.select&2) {
					$(
						'<li class="saorfm-clickable" id="'
						+$wrapper[0].id+'-dirselect'
						+'">select directory</li>'
					)
						.click(function(){
							stopClearAll();
							var $input=$($el.data('input'));
							var url=$el.data('directory');
							$input.val(url);
							hideSubMenus($input.next());
						})
						.appendTo($wrapper);
				}
				// }
				$wrapper.appendTo(document.body);
				$el.data('menu',$wrapper);
			},
			'json'
		);
		// }
	}
	$.fn.saorfm=function(settings) {
		var config = {
			'download' : true,
			'upload'   : true,
			'prefix'   : ''
		};
		if (settings) {
			$.extend(config, settings);
		}
		switch (config.select) {
			case 'directory': // {
				config.select=2;
			break;
			// }
			case 'file': // {
				config.select=1;
			break;
			// }
			default: // {
				config.select=3;
			// }
		}
		this.each(function() {
			if (!config.rpc) {
				return $(
					'<div id="saorfm-error"><h2>no path to the SaorFM RPC url.</h2>'
					+'<p>please add an "rpc" parameter.</p></div>'
				).dialog({"modal":true});
			}
			var $this=$(this);
			var width=$this.outerWidth(), height=$this.outerHeight();
			var $wrapper=$this
				.wrap('<div class="saorfm"></div>')
				.css({
					'height':height+'px'
				});
			var $wrapper=$this.closest('.saorfm');
			$wrapper.css({
				'height':height+'px'
			});
			var button=$(
					'<button class="saorfm-button">'
					+'<span class="ui-icon ui-icon-triangle-1-s">&nbsp;</span>'
					+'</button>'
				)
				.css({
					'height':(height+2)+'px'
				})
				.insertAfter(this)
				.click(function(){
					stopClearAll();
					$(document).on('click', clearAll);
					menuToggle(this,config);
					return false;
				})
				.data('directory','/')
				.data('input',this);
		});
		return this;
	};
	function clearAll() {
		window.saorfm_hideTimeout=setTimeout(function() {
			var $els=$('button.saorfm-button');
			$els.each(function() {
				var $this=$(this);
				if ($this.data('menu')) {
					menuToggle(this);
				}
			});
		}, 2);
	}
	function stopClearAll() {
		setTimeout(function() {
			clearTimeout(window.saorfm_hideTimeout);
		}, 1);
	}
})(jQuery);
