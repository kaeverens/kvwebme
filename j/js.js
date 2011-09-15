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
	var time=d.replace(/.* (.*):[0-9]*$/,'$1');
	if(type=='time')return time;
	return time=='00:00'?date:time+', '+date;
}
function htmlspecialchars(str) {
	if (!str) {
		return '';
	}
	return $('<i>').text(str).html();
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
window.ww={
	CKEDITOR:'ckeditor'
};
// { variables
var function_urls=[],kaejax_is_loaded=0,months=['--','Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
var kaejax_timeouts=[];
var CKEDITOR_BASEPATH='/j/ckeditor-3.6/';
// }
var Json = {
	toString: function(arr) {
		return $.toJSON(arr);
	}
};
function lightbox_show(src, sequence, seq_num) {
	$('#lightbox-image').closest('table').dialog('close');
	var max_width=parseInt($(window).width()*.9),
		max_height=parseInt($(window).height()*.9);
	if (/kfmget\/[0-9]/.test(src)) {
		src=src.replace(/,.*/, '');
		src=src+',width='+max_width+',height='+max_height;
	}
	var left_arrow='',right_arrow='';
	var width_to_add=26;
	sequence=sequence.toString().split(',');
	if (sequence.length>1) {
		var lnum=+seq_num-1;
		if (lnum<0) {
			lnum=sequence.length-1;
		}
		left_arrow='<td><a href="javascript:lightbox_show(\''
			+sequence[lnum]+'\',\''+sequence+'\','+lnum
			+');"><img src="/ww.plugins/image-gallery/frontend/arrow-left.png"/>'
			+'</a></td>';
		var rnum=+seq_num+1;
		if (rnum>=sequence.length) {
			rnum=0;
		}
		right_arrow='<td><a href="javascript:lightbox_show(\''
			+sequence[rnum]+'\',\''+sequence+'\','+rnum
			+');"><img src="/ww.plugins/image-gallery/frontend/arrow-right.png"/>'
			+'</a></td>';
		width_to_add+=60;
	}
	$('<table><tr>'+left_arrow+'<td><img id="lightbox-image" src="'+src+'"/></td>'+right_arrow+'</tr></table>')
		.dialog({
			"modal":true,
			"close":function(){
				$(this).remove();
			}
		});
	$('#lightbox-image').load(function(){
		var $this=$(this);
		while ($this[0].offsetWidth>max_width || $this[0].offsetHeight>max_height) {
			$this[0].width*=.9;
			$this[0].height*=.9;
		}
		$this.closest('table').dialog({
			width:$this[0].offsetWidth+width_to_add
		});
		var $dialog=$this.closest('.ui-dialog');
		$dialog.css({
			"left":$(window).width()/2-$dialog[0].offsetWidth/2,
			"top":$(window).height()/2-$dialog[0].offsetHeight/2+$(document).scrollTop()
		});
	});
}
$(function(){
	$('a[target=popup]').live('click', function(){
		var src=$(this).attr('href');
		var sequence=[],num=0,id;
		if (window.Gallery) {
			for (var i=0;i<Gallery.images.length;++i) {
				id=Gallery.images[i].id;
				if (Gallery.images[i].url==src) {
					num=i;
				}
				sequence[i]=Gallery.images[i].url;
			}
		}
		lightbox_show(src, sequence, num);
		return false;
	});
	if (!window.console) {
		window.console={
			"log":function(v){}
		};
	}
	var el=$('.ajaxmenu')[0];
	if(!el)return;
	var id=el.id.replace(/ajaxmenu/,'');
	if(id && id=='am_top')return;
	$.getScript('/j/menu.php?pageid='+pagedata.id);
});	
