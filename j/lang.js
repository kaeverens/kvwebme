$(function() {
	__langInit();
	$('ul.languages a').click(function() {
		var lang=$(this).attr('href').replace(/#/, '');
		$.post('/a/f=nothing', {
			'__LANG':lang
		}, function() {
			document.location=document.location.toString().replace(/#.*/, '');
		});
		return false;
	});
	$('#core-language').change(function() {
		var lang=$(this).val();
		$.post('/a/f=nothing', {
			'__LANG':lang
		}, function() {
			document.location=document.location.toString().replace(/#.*/, '');
		});
		return false;
	});
});
function __e(el) {
	var context=$(el).attr('lang-context')||'unknown';
	// if language is not yet loaded, start it loading
	if (!__lang[context]) {
		__lang[context]={
			"loading":1
		};
		$.post('/a/f=translationsGet', {
			"context":context
		}, function(ret) {
			__lang[ret.context]=ret.strings;
			__langInit();
		});
		return;
	}
	// if it's not yet loaded, return
	if (__lang[context].loading) {
		return;
	}
	// ok - let's do this
	var $el=$(el);
	var str=$el.html();
	$el
		.removeData('lang-context')
		.removeData('lang-params')
		.removeClass('__');
	if (__lang[context][str] && __lang[context][str]!=str) {
		$el.html(__lang[context][str]);
	}
	else if (!__lang[context][str]) {
		__lang[context][str]=str;
		if (window.userdata && userdata.wasAdmin) {
			__langUnknown.push([str, context]);
			window.__reportTimer=setTimeout(__report, 1000);
		}
	}
}
function __langInit() {
	$('.__').each(function() {
		__e(this);
	});
}
function __report() {
	clearTimeout(window.__reportTimer);
	if (__langUnknown.length) {
		$.post('/a/f=languagesAddStrings', {
			strings:__langUnknown
		});
		__langUnknown=[];
	}
	window.__reportTimer=setTimeout(__report, 5000);
}
var __lang={},__langUnknown=[];
function __(str, params, context) {
	if (!context) {
		context='core';
	}
	setTimeout(__langInit, 1);
	return '<translation class="__" lang-context="'+context+'">'+str+'</translation>';
}
