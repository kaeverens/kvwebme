$(function(){
	$.jstree._themes='/j/jstree/themes/';
	$('.product-categories-tree')
		.jstree({
			'plugins': [
				"themes", "html_data"
			]
		});
	$('#pages-wrapper a').live('click',function(e){
		var node=e.target.parentNode;
		document.getElementById('page-form-wrapper')
			.src="pages/form.php?id="+node.id.replace(/.*_/,'');
		$('#pages-wrapper').jstree('select_node',node);
	});
});
