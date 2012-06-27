function Core_uploader(selector, opts) {
	if (!CoreUploaderOpts.requiresLoaded) {
		$.cachedScript(CoreUploaderOpts.requires, function() {
			CoreUploaderOpts.requiresLoaded=true;
			doIt();
		});
		return;
	}
	function doIt() {
		var pluginOpts=window.CoreUploaderOpts.pluginOpts;
		switch (window.CoreUploaderDefault) {
			case 'uploadify': // {
				if (opts.successHandler) {
					pluginOpts.upload_success_handler=opts.successHandler;
				}
				if (opts.postData) {
					$.extend(pluginOpts.postData, opts.postData);
				}
				if (opts.extensions) {
					pluginOpts.fileExt=opts.extensions;
				}
				pluginOpts.uploader=opts.serverScript;
				$(selector).uploadify(pluginOpts);
			break; // }
		}
	}
	doIt();
}
$(function() {
	window.CoreUploaderDefault='uploadify';
	window.CoreUploaderOpts={
		'uploadify':{
			'requires':'/j/jquery.uploadify/jquery.uploadify.min.js',
			'pluginOpts':{
				'swf':'/j/jquery.uploadify/uploadify.swf',
				'auto':'true',
				'cancelImage':'/i/blank.gif',
				'buttonImage':'/i/choose-file.png',
				'height':20,
				'width':81,
				'postData':{
					'PHPSESSID':window.sessid || window.pagedata.sessid
				}
			}
		}
	}[window.CoreUploaderDefault];
});
