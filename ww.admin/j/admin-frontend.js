function admin_edit_page(){
	$('<div id="admin-overlay"></div>').appendTo(document.body);
	$('<div id="admin-editor"><a href="javascript:admin_edit_page_close()">[x]</a><div id="admin-iframe-holder"><iframe src="/ww.admin/pages.php?frontend-admin=1&action=edit&id='+pagedata.id+'"></iframe></div></div>').appendTo(document.body);
}
function admin_edit_page_close(){
	$('#admin-overlay,#admin-editor').remove();
}
function admin_menubar_toggle(){
	if(window.admin_menubar_closed){
		$('#admin-menubar').css('display','block');
		window.admin_menubar_closed=false;
		$('#admin-menubar-hider').css('background-position','center right');
	}
	else{
		$('#admin-menubar').css('display','none');
		window.admin_menubar_closed=true;
		$('#admin-menubar-hider').css('background-position','center left');
	}
	$('#admin-menubar-hider')[0].blur();
}
function admin_edit_pagecontent(){
	admin_menubar_init([
		'<a href="javascript:admin_edit_pagecontent_save();">save page content</a>',
		'<a href="javascript:admin_edit_pagecontent_close();">close</a>'
	]);
	document.location=document.location.toString().replace(/(#.*)?$/,'#ww-pagecontent');
	var st=$(document).scrollTop();
	$(document).scrollTop(st-20);
	var pc=$('#ww-pagecontent');
	var height=pc[0].offsetHeight,width=pc[0].offsetWidth;
	$.get('/ww.admin/pages/get-pagebody.php?id='+pagedata.id,function(pagecontent){
		$('#ww-pagecontent').ckeditor(
			function(){
				var editor=$('#ww-pagecontent').ckeditorGet();
				editor.setData(pagecontent);
			},
			{
				width:width,
				height:height,
				filebrowserBrowseUrl:"/j/kfm/",
				menu:"WebME",
				resize_enabled:false,
				scayt_autoStartup:false
			}
		);
	});
}
function admin_edit_pagecontent_save(){
	var editor=$('#ww-pagecontent').ckeditorGet();
	var html=editor.getData();
	if(!html)return;
	$.post('/ww.admin/pages/save-pagebody.php',{
			id:pagedata.id,
			body:html
		},
		function(){
			document.location=document.location.toString().replace(/(#.*)?$/,'');
		}
	);
}
function admin_edit_pagecontent_close(){
	document.location=document.location.toString().replace(/(#.*)?$/,'');
}
function admin_menubar_init(links){
	if(!links)links=[
		'<a href="javascript:admin_edit_pagecontent();">edit page content</a>',
		'<a href="javascript:admin_edit_page();">advanced page editor</a>',
		'<a href="/ww.admin/pages.php?action=edit&id='+pagedata.id+'" target="_blank">admin area</a>',
		'<a href="/?logout=1">logout</a>'
	];
	$('#admin-menubar').remove();
	$('<div id="admin-menubar">'+links.join()+'</div>').appendTo(document.body);
}
$(function(){
	return;
	admin_menubar_init();
	$('<a href="javascript:admin_menubar_toggle()" id="admin-menubar-hider"></a>').appendTo(document.body);
})
