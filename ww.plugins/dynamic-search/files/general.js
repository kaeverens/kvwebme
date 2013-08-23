/*
        Webme Dynamic Search Plugin v0.3
        File: files/general.js
        Developer: Conor Mac Aoidh <http://macaoidh.name>
        Report Bugs: <conor@macaoidh.name>
*/

$(document).ready(function(){
	$('#dynamic_searchfield').focus(dynamic_key);
        $('#dynamic_searchfield').blur(dynamic_key);
	$('#dynamic_searchfield').keyup(suggest);
	$('#dynamic_search').submit(dynamic_search);
	$('.popular').click(popular_search);
	$('#dynamic_searchfield li a').click(dynamic_suggestions);
});

function suggest(){
	var content=$('#dynamic_searchfield').attr('value');
        var suggestions=$('#dynamic_suggestions');
	if(content==''){
		suggestions.fadeOut();
		return;
	}
	var hash=Math.floor(Math.random()*1001);
        $.ajax({
	        url:"/ww.plugins/dynamic-search/files/suggestions.php?chars=" + content + "&hash=" + hash,
		success: function(html){
			suggestions.html(html);
		}
	});
	suggestions.fadeIn();
}

function dynamic_suggestions(sug){
	var search=$('#dynamic_searchfield');
	search.attr('value',sug);
	search.submit();
}

function dynamic_search(){
	$('#dynamic_suggestions').fadeOut();
	var dynamic_search = $('#dynamic_searchfield').attr('value');
	if(dynamic_search==''){
		alert('Please enter search criteria.');
		return false;
	}
        var dynamic_category = $('#dynamic_search_select').attr('value');
        var content = $('#dynamic_search_results');
	content.css({display:'none'});
	var hash=Math.floor(Math.random()*1001);
        $('#stuff').css({display:'none'});
	$('#loading').html('Loading...');
	$.ajax({
		url:"/ww.plugins/dynamic-search/files/jsresults.php?dynamic_search=" + dynamic_search + "&dynamic_category=" + dynamic_category + "&hash=" + hash,
		success: function(html){
			content.html(html);
		}
	});
	content.fadeIn('slow');
        return false;
}

function popular_search(){
	var string=this.href.replace(/.*\?/,'');
        var content = $('#dynamic_search_results');
        $('#stuff').css({display:'none'});
        $.ajax({
                url:"/ww.plugins/dynamic-search/files/jsresults.php?" + string,
                success: function(html){
                        content.html(html);
                }
        });
        content.fadeIn('slow');
	return false;
}

function dynamic_key(){
        var search = $('#dynamic_searchfield');
        if(search.attr('value')=='Enter Keywords...') search.attr('value','');
        else{
		if(search.attr('value')=='')  search.attr('value','Enter Keywords...');
		$('#dynamic_suggestions').fadeOut();
	}
}
