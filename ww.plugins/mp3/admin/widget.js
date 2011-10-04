$.fn.serializeObject=function(){ // used for submitting the form
	var o={};
	var a=this.serializeArray();
	$.each(a,function(){
		if(o[this.name]!==undefined){
			if(!o[this.name].push){
				o[this.name]=[o[this.name]];
			}
			o[this.name].push(this.value||'');
		}
		else{
			o[this.name]=this.value||'';
		}
	});
	return o;
};
ww.mp3={
	editor_instances:0
};
function mp3_edit(ev){
	ww.mp3.editor_instances++;
	var el=ev.target;
	var id=el.id.replace(/mp3_editlink_/,'');
	$.post('/ww.plugins/mp3/admin/widget-form.php',
		{'get_mp3_files':id},
		function(res){ // content generated here
			var html='<div class="mp3_content">'
				+ '<ul>'
					+ '<li><a href="#files">MP3 Files</li>'
					+ '<li><a href="#template">Display Template</li>'
				+ '</ul>'
				+ '<div id="files">'
				+ '<span style="float:right">'
					+ '<a class="mp3_add_link" href="javascript:;">[+] Add File</a>'
				+ '</span><br style="clear:both"/>'
				+ '<input type="hidden" name="mp3_id" value="'+res.id+'"/>'
				+ '<table class="mp3_table">'
					+ '<tr>'
						+ '<th>Name</th>'
						+ '<th>File</th>'
						+ '<th>Delete</th>'
					+ '</tr>';
			var e=0;
			for(var i in res.fields){
				html+='<tr>'
						+ '<td>'
							+ '<input type="text" name="fileNames['+e+']"'
							+ ' value="'+res.fields[i].name+'" />'
						+ '</td>'
						+ '<td>'
							+ '<input type="text" id="kfm-return-url" name="fileLocations['+e+']"'
							+ ' value="'+res.fields[i].location+'" readonly="true" class="fileput"/>'
							+ '<button class="select-file">Browse</button>'
						+ '</td>'
						+ '<td><a href="javascript:;" class="delete_row">[x]</a></td>'
					+ '</tr>';
				++e;
			}
			var template=(res.template&&res.template!='')?
				res.template:
				'{{LIST link_to_play="true"}}<br/>{{PLAY}}{{PROGRESS show_time="true"}}';
			html+='</table></div>'
				+ '<div id="template">'
				+ '<p><b>Available Tags:{{LIST}} {{PLAY}} {{PROGRESS}}</b></p>'
				+ '<textarea style="width:600px;height:250px;" id="mp3_template'
				+ ww.mp3.editor_instances+'" name="mp3_template'
				+ ww.mp3.editor_instances+'">'+template+'</textarea>'
				+ '</div></div>';
			$('<form id="mp3_dialog">'+html+'</form>')
				.dialog({ // dialog creation
					minWidth:630,
					minHeight:450,
					height:450,
					width:630,
					modal:true,
					beforeclose:function(){
						if(!ww.mp3.rte)return;
						ww.mp3.rte.destroy();
						ww.mp3.rte=null;
					},
					buttons:{
						Save:function(){
							var data=$('#mp3_dialog').serializeObject();
							data.mp3_template=
								CKEDITOR.instances['mp3_template'+ww.mp3.editor_instances]
								.getData();
							$.post('/ww.plugins/mp3/admin/widget-form.php',
								data,
								function(ret){
									if(ret.id!=ret.id_was)
										$('#mp3_editlink_'+ret.id_was)
											.attr('id','mp3_editlink_'+ret.id);
									var id=ret.id;
									var w=$(el).closest('.widget-wrapper');
									var wd=w.data('widget');
									wd.id=id;
									w.data('widget',wd);
									updateWidgets(w.closest('.panel-wrapper'));
								},
								'json'
							);
							$(this).dialog('close').remove();
						},
						Close:function(){
							$(this).dialog('close').remove();
						},
					}
				});
			$('#mp3_dialog .mp3_content').tabs();
			ww.mp3.rte=CKEDITOR.replace(
				'mp3_template'+ww.mp3.editor_instances,
				CKEditor_config
			);
		},
		'json'
	);
}
function mp3_coladd(){
	var row=$('.mp3_table tr').length-1;
	var html='<tr>'	
		+ '<td>'
			+ '<input type="text" name="fileNames['+row+']"/>'
		+ '</td>'
		+ '<td>'
			+ '<input type="text" name="fileLocations['+row+']"'
			+ ' readonly="true" class="fileput"/>'
			+ '<button class="select-file">Browse</button>'
		+ '</td>'
		+ '<td><a href="javascript:;" class="delete_row">[x]</a></td>'
	+ '</tr>';
	$('.mp3_table').append(html);
}
function mp3_delete(){
	$(this).parent().parent().remove();	
}
$('.mp3_editlink').live('click',mp3_edit);
$('.mp3_add_link').live('click',mp3_coladd);
$('.delete_row').live('click',mp3_delete);
$('.fileput').live('focus click',function(){
	$('.select-file',$(this).parent()).trigger('click');
});
$('.select-file').live('click',function(){
	$this=$(this);
	window.SetUrl=(function($this){
		return function(value){
			value=value.replace(/[a-z]*:\/\/[^\/]*/,'');
			var filename=value.substr((value.lastIndexOf('/')+1));
			$('input',$this.parent()).val(value);
			var sel=$('td:first input',$this.parent().parent());
			if(sel.val()=='')
				sel.val(filename);	
		}
	})($this);
	var kfm_url='/j/kfm/';
	window.open(kfm_url,'kfm','modal,width=600,height=400');
	return false;
});
