$(function(){
	$('#themeeditor-templates a').each(function(){
		var $copy=$('<a href="javascript:;" class="ui-icon ui-icon-copy" title="copy"></a>')
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
			});
		var $delete=$('<a href="javascript:;" class="ui-icon ui-icon-trash" title="delete"></a>')
			.click(function(){
				var $li=$(this).closest('li');
				var name=$li.find('>a').text();
				if (!confirm('are you sure you want to delete the template file "'+name+'"')
				) {
					return;
				}
				$.post(
					'/a/p=theme-editor/f=adminTemplateDelete',
					{ 'file':name },
					function(ret) {
						if (ret.error) {
							return alert(ret.error);
						}
						$li.remove();
					},
					"json"
				);
			});
		var $controls=$('<div class="controls">')
			.insertBefore(this);
		$controls.append($copy, $delete);
	});
	$('#themeeditor-css a').each(function(){
		var $copy=$('<a href="javascript:;" class="ui-icon ui-icon-copy" title="copy"></a>')
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
			});
		var $delete=$('<a href="javascript:;" class="ui-icon ui-icon-trash" title="delete"></a>')
			.click(function(){
				var $li=$(this).closest('li');
				var name=$li.find('>a').text();
				if (!confirm('are you sure you want to delete the CSS file "'+name+'"')
				) {
					return;
				}
				$.post(
					'/a/p=theme-editor/f=adminCssDelete',
					{ 'file':name },
					function(ret) {
						if (ret.error) {
							return alert(ret.error);
						}
						$li.remove();
					},
					"json"
				);
			});
		var $controls=$('<div class="controls">')
			.insertBefore(this);
		$controls.append($copy, $delete);
	});
});
