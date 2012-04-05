/*
 * remoteselectoptions plugin for jQuery
 * By Kae Verens (http://verens.com/)
 * Copyright 2009 Kae Verens
 * Released under the MIT and GPL licenses.
 */

(function($){
	$.fn.remoteselectoptions=function(options){
		var opts=$.extend({},$.fn.remoteselectoptions.defaults,options);
		if (!opts.url) {
			return alert(
				'no "url" parameter provided for remoteselectoptions plugin'
			);
		}
		return this.each(function(){
			var $this=$(this);
			var o = $.meta ? $.extend({}, opts, $this.data()) : opts;
			$this.hover(function(){
				if(!o.always_retrieve){
					if ($this.remoteselectoptions_applied) {
						return;
					}
					$this.remoteselectoptions_applied=true;
				}
				var v=$this.val();
				var other=$.isFunction(o.other_GET_params)
					?o.other_GET_params()
					:o.other_GET_params;
				function doIt(res) {
					if (o.cache_id) {
						window[o.cache_id]=res;
					}
					if (res.error && o.errors) {
						return o.errors(res);
					}
					if (o.load) {
						res=o.load(res);
					}
					$.each(res, function(key, value) {   
						$this
							.append($('<option/>', {"value":key})
							.text(value));
					});
					$this.val(v);
					$this.click().click();
				}
				if (!o.cache_id || !window[o.cache_id]) {
					$.get(o.url, {'selected':v,'other_GET_params':other}, doIt);
				}
				else {
					doIt(window[o.cache_id]);
				}
			});
		});
	};
	$.fn.remoteselectoptions.defaults={
		'url':null,
		'other_GET_params':{},
		'always_retrieve':false,
		'cache_id':null,
		'load':false
	};
})(jQuery);
