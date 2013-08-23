function search_focus(ev){
	if(ev.target.value=='search'){
		var el=$(ev.target);
		el.val('').removeClass('empty');
		el.closest('form').attr('action','/');
	}
}
function search_blur(ev){
	if(ev.target.value==''){
		var el=$(ev.target);
		el.val('search').addClass('empty');
		el.closest('form').attr('action','#');
	}
}
$('document').ready(function(){
	$('input.search').focus(search_focus);
	$('input.search').blur(search_blur);
});
