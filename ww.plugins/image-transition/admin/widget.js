if(!ww.image_transition)ww.image_transition={
	selected_dir:'/'
};
function image_transition_file_manager(){
	window.open('/j/kfm/?startup_folder='+$('#image_transition_folder').val(),'kfm','modal,width=800,height=600');
}
function image_transition_edit(ev){
	var el=ev.target;
	var id=el.id.replace(/image_transition_editlink_/,'');
	var trans_types=["none", "3dCarousel", "fade", "scrollUp", "scrollDown",
		"scrollLeft", "scrollRight", "scrollHorz", "scrollVert", "slideX",
		"slideY", "shuffle", "turnUp", "turnDown", "turnLeft", "turnRight",
		"zoom", "fadeZoom", "blindX", "blindY", "blindZ", "growX", "growY",
		"curtainX", "curtainY", "cover", "uncover", "toss", "wipe"];
	var d=$('<table id="image_transition_form">'
		+'<tr><th>Folder holding the images to transition</th><td>'
		+'<select id="image_transition_folder"></select></td></tr>'
		+'<tr><th>Manage images in the folder</th><td>'
		+'<a href="javascript:image_transition_file_manager()">manage images</a>'
		+'</td></tr>'
		+'<tr><th>Transition Type</th><td><select id="image_transition_type">'
		+'<option>'+trans_types.join('</option><option>')+'</select></td></tr>'
		+'<tr><th>Width</th><td><input class="small" '
		+'id="image_transition-width"/></td></tr>'
		+'<tr><th>Height</th><td><input class="small" '
		+'id="image_transition-height"/></td></tr>'
		+'<tr><th>Pause time in milliseconds</th><td>'
		+'<input class="small" id="image_transition_pause" /></td></tr>'
		+'<tr><th>Link to page</th><td><select id="image_transition_url">'
		+'</select></th></tr>'
		+'</table>');
	$.getJSON('/ww.plugins/image-transition/admin/widget-form.php',{
		'get_image_transition':id
	},function(res) {
		d.dialog({
			minWidth:630,
			minHeight:400,
			height:400,
			width:630,
			close:function(){
				d.dialog('destroy');
				$('#image_transition_form').remove();
			},
			modal:true,
			buttons:{
				'Save':function(){
					$.post('/ww.plugins/image-transition/admin/widget-form.php',
						{
							'id':id,
							'action':'save',
							'directory':$('#image_transition_folder').val(),
							'trans_type':$('#image_transition_type').val(),
							'pause':+$('#image_transition_pause').val(),
							'url':+$('#image_transition_url').val(),
							'width':+$('#image_transition-width').val(),
							'height':+$('#image_transition-height').val()
						},
						function(ret){
							if(ret.id!=ret.was_id){
								el.id='image_transition_editlink_'+ret.id;
							}
							id=ret.id;
							var w=$(el).closest('.widget-wrapper');
							var wd=w.data('widget');
							wd.id=id;
							w.data('widget',wd);
							updateWidgets(w.closest('.panel-wrapper'));
							d.dialog('close');
						}
					,'json');
				},
				'Close':function(){
					d.dialog('close');
				}
			}
		});
		var sel=$('#image_transition_folder'),i,dir;
		for(i=0;i<res.directories.length;++i){
			dir=res.directories[i];
			$('<option></option>').text(dir).attr('value',dir).appendTo(sel);
		}
		sel.val(res.data.directory);
		$('#image_transition_type').val(res.data.trans_type);
		$('#image_transition_pause').val(+res.data.pause);
		$('#image_transition-width').val(+res.data.width);
		$('#image_transition-height').val(+res.data.height);
		$('#image_transition_url').html('<option value="'+res.data.url+'">'+htmlspecialchars(res.data.pagename)+'</option>');
		setTimeout(function(){
			$('#image_transition_url').remoteselectoptions({url:'/a/f=adminPageParentsList'});
		},1);
	});
}
$('.image_transition_editlink').live('click',image_transition_edit);
