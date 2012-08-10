$j=jQuery;
jQuery.fn.outerHTML = function() {
	return $('<div>').append( this.eq(0).clone() ).html();
};
function Core_dateM2H(d, type){
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
window.ww={
	CKEDITOR:'ckeditor'
};
// { variables
var months=['--','Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
var CKEDITOR_BASEPATH='/j/ckeditor-3.6.2/';
// }
var Json = {
	toString: function(arr) {
		return $.toJSON(arr);
	}
};
$(function(){
	$('a[target=popup]').live('click', function() {
		var $this=$(this);
		var src=$this.attr('href');
		var sequence=[],num=0,id;
		if ($this.data('sequence')) {
			sequence=$this.data('sequence');
		}
		else if (window.Gallery) {
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
	$('#core-language').live('change', function() {
		var val=$(this).val();
		document.location=document.location.toString().replace(/\?.*/, '')
			+'?__LANG='+$(this).val();
	});
	if (!window.console) {
		window.console={
			"log":function(v){}
		};
	}
	// { periodically refresh the user's session
	function refreshSession() {
		setTimeout(refreshSession, 60000); // ping the server every minute
		$.post('/a/f=nothing');
	}
	setTimeout(refreshSession, 60000);
	// }
});	
// { stubs
var stubs=['lightbox_show'];
$.cachedScript=function(url, callback) {
	options={
		'dataType':'script',
		'cache':true,
		'url':url,
		'complete':callback||null
	};
	return $.ajax(options);
};
for (var i=stubs.length;i--;) {
	var n=stubs[i];
	var s="function "+n+"(){var args=arguments;$.cachedScript('/j/funcs/"+n
		+".js.m').done(function(){"+n+".apply(this,args);}).fail(function(){"
		+"$.getScript("+"'/j/funcs/"+n+".js',function(){"+n+".apply(this,args);"
		+"});});}";
	eval(s);
}
function stub(fn, plugin) {
	var s="window."+plugin+'_'+fn+"=function(){var args=arguments;"
		+"$.cachedScript('/ww.plugins/"+plugin.toLowerCase()+"/j/"+fn+".js')"
		+".done(function(){"+plugin+'_'+fn+".apply(this,args);});}";
	eval(s);
}
// }
Date.prototype.toYMD =function() {
	var year, month, day;
	year = String(this.getFullYear());
	month = String(this.getMonth() + 1);
	if (month.length == 1) {
		month = "0" + month;
	}
	day = String(this.getDate());
	if (day.length == 1) {
		day = "0" + day;
	}
	return year + "-" + month + "-" + day;
};
/*
 * jQuery Tiny Pub/Sub - v0.6 - 1/10/2011
 * http://benalman.com/
 *
 * Copyright (c) 2010 "Cowboy" Ben Alman
 * Dual licensed under the MIT and GPL licenses.
 * http://benalman.com/about/license/
 */
(function($){var a=$("<b/>");$.subscribe=function(b,c){function d(){return c.apply(this,Array.prototype.slice.call(arguments,1))}d.guid=c.guid=c.guid||($.guid?$.guid++:$.event.guid++);a.bind(b,d)};$.unsubscribe=function(){a.unbind.apply(a,arguments)};$.publish=function(){a.trigger.apply(a,arguments)}})(jQuery);
var CKEditor_config={
	filebrowserBrowseUrl:"/j/kfm/",
	menu:"WebME",
	scayt_autoStartup:false
};
/* Modernizr 2.0.6 (Custom Build) | MIT & BSD
 * Build: http://www.modernizr.com/download/#-backgroundsize-cssclasses-testprop-testallprops-domprefixes
  */
window.Modernizr=function(a,b,c){function A(a,b){var c=a.charAt(0).toUpperCase()+a.substr(1),d=(a+" "+n.join(c+" ")+c).split(" ");return z(d,b)}function z(a,b){for(var d in a)if(k[a[d]]!==c)return b=="pfx"?a[d]:!0;return!1}function y(a,b){return!!~(""+a).indexOf(b)}function x(a,b){return typeof a===b}function w(a,b){return v(prefixes.join(a+";")+(b||""))}function v(a){k.cssText=a}var d="2.0.6",e={},f=!0,g=b.documentElement,h=b.head||b.getElementsByTagName("head")[0],i="modernizr",j=b.createElement(i),k=j.style,l,m=Object.prototype.toString,n="Webkit Moz O ms Khtml".split(" "),o={},p={},q={},r=[],s,t={}.hasOwnProperty,u;!x(t,c)&&!x(t.call,c)?u=function(a,b){return t.call(a,b)}:u=function(a,b){return b in a&&x(a.constructor.prototype[b],c)},o.backgroundsize=function(){return A("backgroundSize")};for(var B in o)u(o,B)&&(s=B.toLowerCase(),e[s]=o[B](),r.push((e[s]?"":"no-")+s));v(""),j=l=null,e._version=d,e._domPrefixes=n,e.testProp=function(a){return z([a])},e.testAllProps=A,g.className=g.className.replace(/\bno-js\b/,"")+(f?" js "+r.join(" "):"");return e}(this,this.document);
jQuery.fn.center = function () {
	this.css("position","absolute");
	this.css("top", (($(window).height() - this.outerHeight()) / 2) + $(window).scrollTop() + "px");
	this.css("left", (($(window).width() - this.outerWidth()) / 2) + $(window).scrollLeft() + "px");
	return this;
}
