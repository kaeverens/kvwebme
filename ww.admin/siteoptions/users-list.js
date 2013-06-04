$(function() {
	$('table#users-list').dataTable({
		'bJQueryUI':true
	}).fnSetFilteringDelay();
	$('a.button').button();
});
