window.$j=jQuery;
jQuery.fn.outerHTML = function() {
	return $('<div>').append( this.eq(0).clone() ).html();
};
function Core_dateM2H(d, type){
	if (d==='' || d=='0000-00-00') {
		return '-';
	}
	if (!type) {
		type='date';
	}
	var date=d.replace(/([0-9]+)-([0-9]+)-([0-9]+).*/,'$3-$2-$1',d)
		.replace(/-0/g,'-');
	var m=months[date.replace(/.*-([0-9]+)-.*/,'$1')];
	date=date.replace(/-[0-9]+-/,'-'+m+'-');
	if (type=='date') {
		return date;
	}
	var time=d.replace(/.* (.*):[0-9]*$/,'$1');
	if (type=='time') {
		return time;
	}
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
			var imgs=window.Gallery.images;
			for (var i=0;i<imgs.length;++i) {
				id=imgs[i].id;
				if (imgs[i].url==src) {
					num=i;
				}
				sequence[i]=imgs[i].url;
			}
		}
		lightbox_show(src, sequence, num);
		return false;
	});
	$('#core-language').live('change', function() {
		document.location=document.location.toString().replace(/\?.*/, '')+
			'?__LANG='+$(this).val();
	});
	if (!window.console) {
		window.console={
			'log':function(){}
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
	var options={
		'dataType':'script',
		'cache':true,
		'url':url,
		'complete':callback||null
	};
	return $.ajax(options);
};
for (var i=stubs.length;i--;) {
	var n=stubs[i];
	var s='function '+n+'(){var args=arguments;$.cachedScript(\'/j/funcs/'+n+
		'.js.m\').done(function(){'+n+'.apply(this,args);}).fail(function(){'+
		'$.getScript(\'/j/funcs/'+n+'.js\',function(){'+n+'.apply(this,args);'+
		'});});}';
	eval(s);
}
function stub(fn, plugin) {
	var s='window.'+plugin+'_'+fn+'=function(){var args=arguments;'+
		'$.cachedScript(\'/ww.plugins/'+plugin.toLowerCase()+'/j/'+fn+'.js\')'+
		'.done(function(){'+plugin+'_'+fn+'.apply(this,args);});}';
	eval(s);
}
// }
Date.prototype.toYMD =function() {
	var year, month, day;
	year = String(this.getFullYear());
	month = String(this.getMonth() + 1);
	if (month.length == 1) {
		month='0'+month;
	}
	day = String(this.getDate());
	if (day.length == 1) {
		day='0' + day;
	}
	return year+'-'+month+'-'+day;
};
var CKEditor_config={
	filebrowserBrowseUrl:'/j/kfm/',
	menu:'WebME',
	scayt_autoStartup:false
};
jQuery.fn.center = function () {
	this.css('position', 'absolute');
	this.css(
		'top',
		(($(window).height()-this.outerHeight())/2)+$(window).scrollTop()+'px'
	);
	this.css(
		'left',
		(($(window).width()-this.outerWidth())/2)+$(window).scrollLeft()+'px');
	return this;
};
