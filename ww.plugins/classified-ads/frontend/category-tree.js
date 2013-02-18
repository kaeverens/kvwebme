$(function() {
	$.jstree._themes='/j/jstree/themes/';
	$('.classifiedads-category-tree-wrapper')
		.jstree({
			'plugins':[
				'themes', 'ui', 'html_data'
			]
		})
		.bind(
			'select_node.jstree', function(n, ch, ev) {
				document.location=$('.jstree-clicked', this).attr('href');
			}
		);
});
