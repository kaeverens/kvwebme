ww.content_snippet={
	editor_instances:0
};
function content_snippet_edit(ev){
	var el=ev.target;
	var id=el.id.replace(/content_snippet_editlink_/,'');
	ww.content_snippet.editor_instances++;
	var d=$(
		'<div>'
		// { accordion options
		+'Is Accordion <select id="content_snippet_accordion">'
		+'<option value="0">No</option><option value="1">Yes</option>'
		+'</select>'
		+'<span id="content_snippet_accordion_opts">'
		+'<select id="content_snippet_accordion_direction">'
		+'<option value="0">Vertical</option>'
		+'<option value="1">Horizontal</option>'
		+'</select><br />'
		+'<select id="content_snippet_page"></select>'
		+'Title <input id="content_snippet_page_title" />'
		+'Images (optional) <select id="content_snippet_image_directory"></select>'
		+'</span>'
		// }
		// { textarea
		+'<textarea style="width:600px;height:250px;" id="content_snippet_html'
		+ww.content_snippet.editor_instances+'" name="content_snippet_html'
		+ww.content_snippet.editor_instances+'"></textarea>'
		// }
		+'</div>'
	);
	$.getJSON(
		'/ww.plugins/content-snippet/admin/widget-form.php',
		{'get_content_snippet':id},
		function(res){
			if (res.content==null) {
				res.content='';
			}
			d.dialog({
				"minWidth":630,
				"minHeight":450,
				"height":450,
				"width":630,
				"beforeclose":function(){
					if (!ww.content_snippet.rte) {
						return;
					}
					ww.content_snippet.rte.destroy();
					ww.content_snippet.rte=null;
				},
				"close":function(){
					$(this).closest('div').remove();
				},
				"buttons":{
					'Save':function(){
						content_snippet_savePage(ww.content_snippet.current_page);
						var html=$.toJSON(ww.content_snippet.data);
						$.post('/ww.plugins/content-snippet/admin/widget-form.php',{
							'id':id,
							'action':'save',
							'html':html,
							'accordion':$('#content_snippet_accordion').val(),
							'accordion_dir':$('#content_snippet_accordion_direction').val(),
							'accordion_images':$('#content_snippet_image_directory').val()
						},function(ret){
							if(ret.id!=ret.was_id){
								el.id='content_snippet_editlink_'+ret.id;
							}
							id=ret.id;
							var w=$(el).closest('.widget-wrapper');
							var wd=w.data('widget');
							wd.id=id;
							w.data('widget',wd);
							updateWidgets(w.closest('.panel-wrapper'));
							d.dialog('close');
						},'json');
					},
					'Close':function(){
						d.dialog('close');
					}
				}
			});
			setTimeout(function(){ // give browser time to update DOM
				$('#content_snippet_accordion_opts').css('display','none');
				$('#content_snippet_accordion')
					.change(function(){
						var val=+($(this).val());
						$('#content_snippet_accordion_opts')
							.css('display',val?'inline':'none');
					})
					.val(res.accordion)
					.trigger('change');
				$('#content_snippet_accordion_direction')
					.val(res.accordion_direction);
				$('#content_snippet_image_directory')
					.html('<option>'+htmlspecialchars(res.images_directory)+'</option>')
					.remoteselectoptions({url:'/a/f=adminDirectoriesGet'});
			},1);
			ww.content_snippet.rte=CKEDITOR.replace(
				'content_snippet_html'+ww.content_snippet.editor_instances,
				CKEditor_config
			);
			var html='';
			for(var i=0;i<res.content.length;++i){
				html+='<option value="'+i+'">page '+(i+1)+'</option>';
			}
			html+='<option>new page</option>';
			$('#content_snippet_page').html(html);
			ww.content_snippet.data=res.content;
			ww.content_snippet.current_page=-1;
			content_snippet_showPage(0);
		}
	);
}
function content_snippet_changePage(){
	var $this=$(this);
	var val=$this.val();
	if(val=='new page'){
		val=$this.find('option').length-1;
		var html='';
		for (var i=0;i<val+1;++i) {
			html+='<option value="'+i+'">page '+(i+1)+'</option>';
		}
		html+='<option>new page</option>';
		$('#content_snippet_page')
			.html(html)
			.val(val);
	}
	content_snippet_showPage(val);
}
function content_snippet_savePage(page){
	if (page<0) {
		return;
	}
	ww.content_snippet.data[page].html=ww.content_snippet.rte.getData();
	ww.content_snippet.data[page].title=$('#content_snippet_page_title').val();
}
function content_snippet_showPage(page){
	content_snippet_savePage(ww.content_snippet.current_page);
	ww.content_snippet.current_page=page;
	if (!ww.content_snippet.data[page]) {
		ww.content_snippet.data[page]={
			"html":'',
			"title":''
		};
	}
	ww.content_snippet.rte.setData(ww.content_snippet.data[page].html);
	$('#content_snippet_page_title').val(ww.content_snippet.data[page].title);
}
$('.content_snippet_editlink').live('click',content_snippet_edit);
$('#content_snippet_page').live('change',content_snippet_changePage);
