function updateWidgets(panel){
	var id=panel[0].id.replace(/panel/,'');
	var w_els=$('.widget-wrapper',panel);
	var widgets=[];
	for(var i=0;i<w_els.length;++i){
		widgets.push($(w_els[i]).data('widget'));
	}
	panel.data('widgets',widgets);
	var json=Json.toString({'widgets':widgets});
	$.post('/a/p=panels/f=adminSave/id='+id, {'data':json});
}
function showWidgetForm(w){
	if(!w.length)w=$(this).closest('.widget-wrapper');
	var f=$('form',w);
	if(f.length){
		f.remove();
		return;
	}
	var p=w.data('widget');
	var form=$('<form class="panel-'+p.type+'"/>').appendTo(w);
	if(ww.widgetForms[p.type]){
		$('<button style="float:right">Save</button>')
			.click(function(){
				w.find('input,select,textarea').each(function(i,el){
					var $el=$(el);
					p[el.name]=$el.is('input[type=checkbox]')
						?($el.is(':checked')?1:0)
						:$el.val();
				});
				w.data('widget',p);
				updateWidgets(form.closest('.panel-wrapper'));
				return false;
			})
			.appendTo(form);
		var fholder=$('<div style="clear:both;border-bottom:1px solid #416BA7;padding-bottom:15px;">loading...</div>').prependTo(form);
		p.panel=$('h4>span.name',form.closest('.panel-wrapper')).eq(0).text();
		fholder.load(ww.widgetForms[p.type],p);
	}
	else $('<p>no config form needed for this widget</p>').appendTo(form);
	$('<a href="javascript:;">[pages]</a>')
		.click(widget_visibility)
		.appendTo(form);
	$('<a class="disabled" href="javascript:;">['+(p.disabled?'off':'on')+']</a>')
		.click(widget_toggle_disabled)
		.appendTo(form);
	$('<a href="javascript:;" title="remove widget">[x]</a>')
		.click(function(){
			if(!confirm('Are you sure you want to remove this widget from this panel?'))return;
			var panel=w.closest('.panel-wrapper');
			w.remove();
			updateWidgets(panel);
		})
		.appendTo(form);
}
function buildRightWidget(p){
	var widget=$('<div class="widget-wrapper '+(p.disabled?'disabled':'enabled')+'"></div>')
		.data('widget',p);
	var h4=$('<h4/>')
		.appendTo(widget);
	var name=p.name||p.type;
	$('<input type="checkbox" class="widget_header_visibility" title="tick this to show the widget title on the front-end" />')
		.click(widget_header_visibility)
		.appendTo(h4);
	$('<span class="name">'+name+'</span>')
		.click(widget_rename)
		.appendTo(h4);
	$('<span class="panel-opener">&darr;</span>')
		.appendTo(h4)
		.click(showWidgetForm);
	return widget;
}
function widget_header_visibility(ev){
	var el=ev.target,vis=[];
	var w=$(el).closest('.widget-wrapper');
	var p=w.data('widget');
	p.header_visibility=el.checked;
	w.data('widget',p);
	updateWidgets(w.closest('.panel-wrapper'));
}
function widget_toggle_disabled(ev){
	var el=ev.target,vis=[];
	var w=$(el).closest('.widget-wrapper');
	var p=w.data('widget');
	p.disabled=p.disabled?0:1;
	w.removeClass().addClass('widget-wrapper '+(p.disabled?'disabled':'enabled'));
	$('.disabled',w).text(p.disabled?'[off]':'[on]');
	w.data('widget',p);
	updateWidgets(w.closest('.panel-wrapper'));
}
function widget_visibility(ev){
	var el=ev.target,vis=[],hid=[];
	var w=$(el).closest('.widget-wrapper');
	var wd=w.data('widget');
	if (wd.visibility) {
		vis=wd.visibility;
	}
	if (wd.hidden) {
		hid=wd.hidden;
	}
	$.get('/a/p=panels/f=adminVisibilityGet/visibility='+vis+'/hidden='+hid, function(res){
		var d=$('<form><p>If nothing is selected here then the widget is visible on all pages that support it.</p><p>Visible in <select name="panel_visibility_pages[]" multiple="multiple">'+res.visible+'</select></p><p>Hidden in <select name="panel_hidden_pages[]" multiple="multiple">'+res.hidden+'</select></p></form>');
		d.dialog({
			width:300,
			height:400,
			close:function(){
				$('#panel_visibility_pages').remove();
				$('#panel_hidden_pages').remove();
				d.remove();
			},
			buttons:{
				'Save':function(){
					// { visible on
					var arr=[];
					$('input[name="panel_visibility_pages[]"]:checked').each(function(){
						arr.push(this.value);
					});
					wd.visibility=arr;
					// }
					// { hidden on
					arr=[];
					$('input[name="panel_hidden_pages[]"]:checked').each(function(){
						arr.push(this.value);
					});
					wd.hidden=arr;
					// }
					w.data('widget',wd);
					updateWidgets(w.closest('.panel-wrapper'));
					d.dialog('close');
				},
				'Close':function(){
					d.dialog('close');
				}
			}
		});
		$('select').inlinemultiselect({
			'separator':', ',
			'endSeparator':' and '
		});
	}, 'json');
}
function panel_remove(i){
	var p=ww.panels[i];
	var id=p.id;
	if(!confirm('Are you sure you want to delete this panel?'))return;
	if(!confirm('Just double-checking... deleting this panel will remove the configurations of its contained widgets. Are you /sure/ you want to remove this? Note that your panel will be recreated (without its widgets) if the site theme has it defined.'))return;
	$.get('/ww.plugins/panels/admin/remove-panel.php?id='+p.id,function(){
		document.location='/ww.admin/plugin.php?_plugin=panels&_page=index';
	});
}
function panel_visibility(id){
	$.post('/a/p=panels/f=adminVisibilityGet/id='+id, function(res){
		var d=$('<form><p>If nothing is selected here then the page is visible on all pages that support it.</p><p>Visible in <select name="panel_visibility_pages[]" multiple="multiple">'+res.visible+'</select></p><p>Hidden in <select name="panel_hidden_pages[]" multiple="multiple">'+res.hidden+'</select></form>');
		d.dialog({
			modal:true,
			width:300,
			height:400,
			close:function(){
				$('#panel_visibility_pages').remove();
				$('#panel_hidden_pages').remove();
				d.remove();
			},
			buttons:{
				'Save':function(){
					// { visible in
					var vis=[];
					$('input[name="panel_visibility_pages[]"]:checked').each(function(){
						vis.push(this.value);
					});
					// }
					// { hidden in
					var hid=[];
					$('input[name="panel_hidden_pages[]"]:checked').each(function(){
						hid.push(this.value);
					});
					// }
					$.get('/ww.plugins/panels/admin/save-visibility.php?id='+id+'&vis='+vis+'&hid='+hid);
					d.dialog('close');
				},
				'Close':function(){
					d.dialog('close');
				}
			}
		});
		$('select').inlinemultiselect({
			'separator':', ',
			'endSeparator':' and '
		});
	},'json');
}
function panels_init(panel_column){
	for(var i=0;i<ww.panels.length;++i){
		var p=ww.panels[i];
		$('<div class="panel-wrapper '+(p.disabled?'disabled':'enabled')+'" id="panel'+p.id+'">'
				+'<h4><span class="name">'+p.name+'</span></h4>'
				+'<div class="controls" style="display:none;text-align:right">'
					+'<a href="javascript:panel_visibility('
					  +p.id+');" class="visibility">[pages]</a>'
					+'<a href="javascript:panel_toggle_disabled('
					  +i+');" class="disabled">['+(p.disabled?'off':'on')+']</a>'
					+'<a title="remove panel" href="javascript:panel_remove('
					  +i+');" class="remove">[x]</a>'
				+'</div></div>'
			)
			.data('widgets',p.widgets.widgets)
			.appendTo(panel_column);
	}
}
function panel_toggle_disabled(i){
	var p=ww.panels[i];
	p.disabled=p.disabled?0:1;
	var panel=$('#panel'+p.id);
	panel.removeClass().addClass('panel-wrapper '+(p.disabled?'disabled':'enabled'));
	$('.controls .disabled',panel).text(p.disabled?'[off]':'[on]');
	ww.panels[i]=p;
	$.get('/ww.plugins/panels/admin/save-disabled.php?id='+p.id+'&disabled='+p.disabled);
}
function widgets_init(widget_column){
	for(var i=0;i<ww.widgets.length;++i){
		var p=ww.widgets[i];
		$('<div class="widget-wrapper"><h4 widget-type="'+p.type+'">'+p.name+'</h4><p>'+p.description+'</p></div>')
			.appendTo(widget_column)
			.data('widget',p);
		ww.widgetsByName[p.type]=p;
	}
}
function widget_rename(ev){
	var h4=$(ev.target);
	var p=h4.closest('.widget-wrapper').data('widget');
	var newName=prompt('What would you like to rename the widget to?',p.name||p.type);
	if(!newName)return;
	p.name=newName;
	h4.closest('.widget-wrapper').data('widget',p);
	updateWidgets($(h4).closest('.panel-wrapper'));
	h4.text(newName);
}
$(function(){
	var panel_column=$('#panels');
	var widget_column=$('#widgets');
	ww.widgetsByName={};
	panels_init(panel_column);
	widgets_init(widget_column);
	$('<span class="panel-opener">&darr;</span>')
		.appendTo('.panel-wrapper h4')
		.click(function(){
			var $this=$(this);
			var panel=$this.closest('div');
			if($('.panel-body',panel).length){
				$('.controls',panel).css('display','none');
				return $('.panel-body',panel).remove();
			}
			$('.controls',panel).css('display','block');
			var widgets_container=$('<div class="panel-body"></div>');
			widgets_container.appendTo(panel);
			var widgets=panel.data('widgets');
			for(var i=0;i<widgets.length;++i){
				var p=widgets[i];
				var w=buildRightWidget(p);
				w.appendTo(widgets_container);
				if(p.header_visibility)$('input.widget_header_visibility',w)[0].checked=true;
			}
			$('<br style="clear:both;line-height:0" />').appendTo(widgets_container);
			$('.panel-body').sortable({
				'stop':function(){
					updateWidgets($(this).closest('.panel-wrapper'));
				}
			});
		});
	$('#widgets').sortable({
		'connectWith':'.panel-body',
		'stop':function(ev,ui){
			var item=ui.item;
			var panel=item.closest('.panel-wrapper');
			if (!panel.length) {
				return $(this).sortable('cancel');
			}
			var h4=$(ui.item).find('h4');
			var p=ww.widgetsByName[h4.attr('widget-type')];
			var clone=buildRightWidget({'type':p.type,'name':p.name});
			showWidgetForm(clone);
			clone.insertBefore('.panel-body br:last',panel);
			$(this).sortable('cancel');
			updateWidgets(panel);
		}
	})
	$('<br style="clear:both" />').appendTo(widget_column);
});
