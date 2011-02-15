function pages_add_subpage(node, tree){
	var p=node[0].id.replace(/.*_/,'');
	pages_new(p);
}
function pages_add_main_page(){
	pages_new(0);
}
function pages_new(p){
	$('<form id="newpage_dialog" action="/ww.admin/pages/form.php" method="post" target="page-form-wrapper"><input type="hidden" name="prefill_body_with_title_as_header" value="1" /><input type="hidden" name="action" value="Insert Page Details" /><input type="hidden" name="special[1]" value="1" /><input type="hidden" name="newpage_dialog" value="1" /><input type="hidden" name="parent" value="'+p+'" /><table><tr><th>Name</th><td><input name="name" /></td></tr><tr><th>Page Type</th><td><select name="type"><option value="0">normal</option></select></td></tr><tr><th>Associated Date</th><td><input name="associated_date" class="date-human" id="newpage_date" /></td></tr></table></form>').dialog({
		modal:true,
		close:function(){
			$(this).closest('div').remove();
			$('#newpage_dialog').remove();
		},
		buttons:{
			'Create Page': function() {
				if($('#newpage_dialog input[name="name"]').val()=='')return alert('Name must be provided');
				document.getElementById('newpage_dialog').submit();
				$(this).dialog('close');
			},
			'Cancel': function() {
				$(this).dialog('close');
			}
		}
	});
	$('#newpage_dialog select[name=type]').remoteselectoptions({url:'/ww.admin/pages/get_types.php'});
	$('#newpage_date').each(convert_date_to_human_readable);
	return false;
}
function pages_copy(node, tree) {
	$.post('/ww.admin/pages/page-copy.php',{
		'id':node[0].id.replace(/.*_/,'')
	}, function(ret){
		pages_add_node(ret.name, ret.id, ret.pid);
		document.getElementById('page-form-wrapper')
			.src="pages/form.php?id="+ret.id;
	}, 'json');
}
function pages_delete(node,tree){
	if(!confirm("Are you sure you want to delete this page?"))return;
	$.getJSON('/ww.admin/pages/delete.php?id='+node[0].id.replace(/.*_/,''),function(){
		if (node.find('li').length) {
			document.location=document.location.toString();
		}
		else {
			$('#pages-wrapper').jstree('remove',node);
		}
	});
}
function pages_add_node(name,id,pid){
	var pel=null;
	var $jstree=$('#pages-wrapper');
	if (pid) {
		pel='#page_'+pid;
	}
	else{
		pel='#pages-wrapper';
	}
	var node=$jstree.jstree(
		'create',
		pel,
		"first",
		{'attr':{'id':'page_'+id},'data':name},
		function(){
			$jstree.jstree('deselect_all');
			$jstree.jstree('select_node','#page_'+id);
		},
		true
	);
}
$(function(){
	$('#pages-wrapper')
		.jstree({
			'plugins': ["themes", "html_data", "ui", "crrm", "contextmenu", "dnd"],
			'contextmenu': {
				'items': {
					'rename':false,
					'ccp':false,
					'create' : {
						'label'	: "Create Page", 
						'visible'	: function (NODE, TREE_OBJ) { 
							if(NODE.length != 1) return 0; 
							return TREE_OBJ.check("creatable", NODE); 
						}, 
						'action':pages_add_subpage,
						'separator_after' : true
					},
					'remove' : {
						'label'	: "Delete Page", 
						'visible'	: function (NODE, TREE_OBJ) { 
							if(NODE.length != 1) return 0; 
							return TREE_OBJ.check("deletable", NODE); 
						}, 
						'action':pages_delete,
						'separator_after' : true
					},
					'copy' : {
						'label'	: "Copy Page", 
						'visible'	: function (NODE, TREE_OBJ) { 
							return true;
						}, 
						'action':pages_copy
					}
				}
			},
			'dnd': {
				'drag_target': false,
				'drop_target': false,
				'drag_finish': function(data) {
					var node=data.o[0];
					setTimeout(function(){
						var p=node.parentNode.parentNode;
						var nodes=$(p).find('>ul>li');
						if(p.tagName=='DIV')p=-1;
						var new_order=[];
						for(var i=0;i<nodes.length;++i)new_order.push(nodes[i].id.replace(/.*_/,''));
						$.getJSON('/ww.admin/pages/move_page.php?id='+node.id.replace(/.*_/,'')+'&parent_id='+(p==-1?0:p.id.replace(/.*_/,''))+'&order='+new_order);
					},1);
				}
			}
		});
	var div=$('<div><i>right-click for options</i><br /><br /></div>');
	$('<button>add main page</button>')
		.click(pages_add_main_page)
		.appendTo(div);
	div.appendTo('div.left-menu');
	$('#pages-wrapper a').live('click',function(e){
		var node=e.target.parentNode;
		document.getElementById('page-form-wrapper')
			.src="pages/form.php?id="+node.id.replace(/.*_/,'');
		$('#pages-wrapper').jstree('select_node',node);
	});
});
