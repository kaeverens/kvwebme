$(function(){
	$('#themeeditor-templates a').each(function(){
		$('<a href="javascript:;">[copy]</a>')
			.click(function(){
				var name=$(this).closest('li').find('>a').text();
				var msg='', newname=name;
				do{
					var newname=prompt(
						msg
						+"what should the new template be called?\n"
						+"alphanumeric characters only.",
						newname
					);
					if (newname==null) {
						return;
					}
					var valid=newname.length
						&& newname.replace(/[a-zA-Z0-9\-_]/g, '')=='';
					if (!valid) {
						msg="invalid name. please use only alphanumeric characters.\n\n";
					}
				}while(!valid);
				$.post('/ww.plugins/theme-editor/admin/copy-template.php?from='
					+name+'&to='+newname,
					function(ret) {
						if (ret.error) {
							return alert(ret.error);
						}
						document.location='/ww.admin/plugin.php?_plugin=theme-editor&'
							+'_page=index&name='+newname+'&type=h';
					},
					"json"
				);
			})
			.appendTo($(this).next());
	});
});
