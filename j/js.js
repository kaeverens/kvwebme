$j=jQuery;
jQuery.fn.outerHTML = function() {
	return $('<div>').append( this.eq(0).clone() ).html();
};
function date_m2h(d,type){
	if(d=='' || d=='0000-00-00')return '-';
	if(!type)type='date';
	date=d.replace(/([0-9]+)-([0-9]+)-([0-9]+).*/,'$3-$2-$1',d).replace(/-0/g,'-');
	var m=months[date.replace(/.*-([0-9]+)-.*/,'$1')];
	date=date.replace(/-[0-9]+-/,'-'+m+'-');
	if(type=='date')return date;
	var time=d.replace(/.* /,'');
	if(type=='time')return time;
	return time+', '+date;
}
function htmlspecialchars(str) {
	return str==''?'':$('<i>').text(str).html();
}
// { kaejax
function kaejax_create_functions(url,f){
	kaejax_is_loaded=1;
	for(var i=0;i<f.length;++i){
		eval('window.x_'+f[i]+'=function(){kaejax_do_call("'+f[i]+'",arguments)}');
		function_urls[f[i]]=url;
	}
}
function kaejax_do_call(func_name,args){
	var uri=function_urls[func_name];
	if(!window.kaejax_timeouts[uri]){
		window.kaejax_timeouts[uri]={t:setTimeout('kaejax_sendRequests("'+uri+'")',1),c:[],callbacks:[]};
	}
	var l=window.kaejax_timeouts[uri].c.length,v2=[];
	for(var i=0;i<args.length-1;++i){
		v2[v2.length]=args[i];
	}
	window.kaejax_timeouts[uri].c[l]={f:func_name,v:v2};
	window.kaejax_timeouts[uri].callbacks[l]=args[args.length-1];
}
function kaejax_sendRequests(uri){
	var t=window.kaejax_timeouts[uri],callbacks=window.kaejax_timeouts[uri].callbacks;
	t.callbacks=null;
	window.kaejax_timeouts[uri]=null;
	var x=new XMLHttpRequest(),post_data="kaejax="+escape(Json.toString(t)).replace(/%([89A-F][A-Z0-9])/g,'%u00$1').replace('+','%2B');
	x.open('POST',uri,true);
	x.setRequestHeader("Method","POST "+uri+" HTTP/1.1");
	x.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
	x.onreadystatechange=function(){
		if(x.readyState!=4)return;
		var v=eval('('+unescape(x.responseText.replace(/%/g,'%25'))+')');
		var f,p;
		for(var i=0;i<t.c.length;++i){
			var f=callbacks[i],p=[];
			p=[];
			if($.isArray(f)){
				p=f;
				f=f[0];
			}
			if(f)f(v[i],p);
		}
	}
	x.send(post_data);
}
// }
function loadScript(url){
	if($.inArray(url,loadedScripts)>-1)return 0;
	loadedScripts.push(url);
	if(kaejax_is_loaded&&/\.php/.test(url))url+=(/\?/.test(url)?'&':'?')+'kaejax_is_loaded';
	$.getScript(url);
	return 1;
}
window.ww={
	CKEDITOR:'ckeditor'
};
// { variables
var function_urls=[],loadedScripts=[],kaejax_is_loaded=0,months=['--','Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
var kaejax_timeouts=[];
// }
var Json = {
	toString: function(arr) {
		return $.toJSON(arr);
	}
};
$(function(){
	var el=$('.ajaxmenu')[0];
	if(!el)return;
	var id=el.id.replace(/ajaxmenu/,'');
	if(id && id=='am_top')return;
	loadScript('/j/menu.php?pageid='+pagedata.id);
});
