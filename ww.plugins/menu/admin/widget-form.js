ww.menu={
	selected_dir:'/'
};
function menu_edit(ev){
	var el=ev.target;
	var id=el.id.replace(/menu_editlink_/,'');
	// { build the HTML for the form
	var html='<div id="menu_form">'
		+'<ul><li><a href="#menu_main">Main</a></li><li><a href="#menu_style">'
		+'Sub-menu Style</a></li></ul>'
		// {
		+'<div id="menu_main"><table>'
		+'<tr><th>Parent Page</th><td><select id="menu_parent"></select></td></tr>'
		+'<tr><th>Direction</th><td><select id="menu_direction">'
		+'<option value="1">Vertical</option>'
		+'<option value="0">Horizontal</option>'
		+'</select></td></tr>'
		+'<tr id="row-menu-type"style="display:none"><th>Type</th><td>'
		+'<select name="menu_type_v">'
		+'<option value="0">Drop-down</option>'
		+'<option value="1">Accordion</option>'
		+'<option value="2">Tree-list</option>'
		+'</select></td></tr>'
		+'<tr id="row-menu-state" style="display:none"><th>Initial State</th><td>'
		+'<select name="menu_state_a" id="menu_state">'
		+'<option value="0">Contract All</option>'
		+'<option value="1">Expand All</option>'
		+'<option value="2">Expand Current Page</option>'
		+'</select></td></tr>'
		+'<tr><th>Columns</th><td><input id="menu_columns" class="small" /></td></tr>'
		+'</table></div>'
		// }
		// { styles
		+'<div id="menu_style"><select id="menu_style_from"><option value="0">'
		+'inherit styles from CSS</option><option value="1">define styles here'
		+'</option></select><table><tr><th>Sub-menu Background</th><td><input i'
		+'d="menu_background" /><div id="menu_background_picker" style="width:1'
		+'95px;height:195px;"></div></td></tr><tr><th>Opacity</th><td><input id'
		+'="menu_opacity" /><div id="menu_opacity_slider"></div></td></tr></tab'
		+'le></div>'
		// }
		+'</div>';
	var $d=$(html);
	// }
	$.getJSON('/ww.plugins/menu/admin/widget-form.php',{'get_menu':id},function(res){
		$d.dialog({
			modal:true,
			width:400,
			buttons:{
				'Save':function(){
					var direction=+$('#menu_direction').val();
					$.post('/ww.plugins/menu/admin/widget-form.php',
						{
							'id':id,
							'action':'save',
							'parent':$('#menu_parent').val(),
							'direction':+direction,
							'type':(direction?$('select[name=menu_type_v]').val():''),
							'background':$('#menu_background').val(),
							'opacity':$('#menu_opacity').val(),
							'columns':$('#menu_columns').val(),
							'style_from':$('#menu_style_from').val(),
							'state':$('#menu_state').val()
						},
						function(ret){
							if(ret.id!=ret.was_id){
								el.id='menu_editlink_'+ret.id;
							}
							id=ret.id;
							var w=$(el).closest('.widget-wrapper');
							var wd=w.data('widget');
							wd.id=id;
							w.data('widget',wd);
							updateWidgets(w.closest('.panel-wrapper'));
							$d.dialog('close');
							$('#menu_form').remove();
						}
					,'json');
				},
				'Close':function(){
					$d.dialog('close');
					$('#menu_form').remove();
				}
			}
		});
		// { set up initial values
		$('#menu_form').tabs();
		$('#menu_parent')
			.html('<option value="'+res.parent+'">'
			+htmlspecialchars(res.parent_name)+'</option>');
		$('#menu_direction')
			.val(+res.direction)
			.change(function(){
				var val= +$(this).val();
				$('#row-menu-type').css(
					'display',
					val?'table-row':'none'
				);
			});
		if (res.direction==1) {
			$('#row-menu-type').css('display','table-row');
		}
		$('select[name=menu_type_v]')
			.val(+res.type)
			.change( function( ){
				var val= +$(this).val();
				$('#row-menu-state').css('display', val==1?'table-row':'none');
			} );
		if (res.state!=0) {
			$('#row-menu-state').css('display','table-row');
		}
		$('select[name="menu_state_a"]').val(+res.state);
		if (!res.background) {
			res.background='#ffffff';
		}
		$('#menu_background')
			.val(res.background)
			.css('background-color',res.background);
		$('#menu_background_picker')
			.farbtastic('#menu_background');
		$('#menu_opacity')
			.val(+res.opacity)
			.css('display','none');
		$('#menu_opacity_slider')
			.slider({
				min:0,
				max:1,
				step:.05,
				value:+res.opacity,
				slide:function(ev,ui){
					$('#menu_opacity').val(ui.value);
				}
			});
		$('#menu_columns').val(+res.columns);
		function update_styles_table(){
			$('#menu_style table').css('display',$('#menu_style_from').val()=='0'?'none':'block');
		}
		$('#menu_style_from')
			.val(+res.style_from)
			.change(update_styles_table);
		setTimeout(function(){
			$('#menu_parent').remoteselectoptions({
				url:'/a/f=adminPageParentsList',
				load:function(ret) {
					var arr={
						' -1':' -- current page -- '
					};
					$.each(ret, function(k, v) {
						arr[k]=v;
					});
					return arr;
				}
			});
			update_styles_table();
		},1);
		// }
	});
}
$('.menu_editlink').live('click',menu_edit);
