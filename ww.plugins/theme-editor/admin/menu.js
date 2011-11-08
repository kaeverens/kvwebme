$(function(){
	$('#themeeditor-templates a').each(function(){
		$('<a href="javascript:;" style="float:right" class="ui-icon ui-icon-copy" title="copy"></a>')
			.click(function(){
				var name=$(this).closest('li').find('>a').text();
				var msg='', newname=name;
				do {
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
				$.post(
					'/a/p=theme-editor/f=adminTemplateCopy',
					{ 'from':name, 'to':newname },
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
			.insertBefore(this);
	});
	$('#themeeditor-css a').each(function(){
		$('<a href="javascript:;" style="float:right" class="ui-icon ui-icon-copy" title="copy"></a>')
			.click(function(){
				var name=$(this).closest('li').find('>a').text();
				var msg='', newname=name;
				do {
					var newname=prompt(
						msg
						+"what should the new css file be called?\n"
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
				$.post(
					'/a/p=theme-editor/f=adminCssCopy',
					{ 'from':name, 'to':newname },
					function(ret) {
						if (ret.error) {
							return alert(ret.error);
						}
						document.location='/ww.admin/plugin.php?_plugin=theme-editor&'
							+'_page=index&name='+newname+'&type=c';
					},
					"json"
				);
			})
			.insertBefore(this);
	});
});
