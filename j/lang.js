$(function() {
	__langInit();
});
function __langInit() {
	$('.__').each(function() {
		__(this);
	});
}
function __(el) {
	var context=$(this).data('lang-context')||'core';
	// if language is not yet loaded, start it loading
	if (!__lang[context]) {
		__lang[context]={
			"loading":1
		};
		console.log('loading '+context);
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
}
var __lang={};
