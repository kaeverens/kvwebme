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
function __(el) {
	var context=$(el).attr('lang-context')||'unknown';
	// if language is not yet loaded, start it loading
console.log(1);
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
console.log(2);
	// if it's not yet loaded, return
	if (__lang[context].loading) {
		return;
	}
console.log(3);
	// ok - let's do this
	var $el=$(el);
	var str=$el.html();
console.log(4);
	$el
		.removeData('lang-context')
		.removeData('lang-params')
		.removeClass('__');
console.log(5);
	if (__lang[context][str] && __lang[context][str]!=str) {
console.log(6);
		$el.html(__lang[context][str]);
	}
	else if (!__lang[context][str]) {
console.log(7);
		__lang[context][str]=str;
console.log(8);
		if (window.userdata && userdata.wasAdmin) {
console.log(9);
			__langUnknown.push([str, context]);
			window.__reportTimer=setTimeout(__report, 1000);
console.log(10);
		}
	}
}
function __langInit() {
	$('.__').each(function() {
		__(this);
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
