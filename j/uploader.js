window.CoreUploaderDefault='uploadify';
function Core_uploader(selector, opts) {
	if (!window.CoreUploaderOpts) {
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
					'formData':{
						'PHPSESSID':window.sessid || window.pagedata.sessid
					}
				}
			}
		}[window.CoreUploaderDefault];
	}
	if (CoreUploaderOpts.requiresLoading) {
		setTimeout(function() {
			Core_uploader(selector, opts);
		}, 100);
		return;
	}
	if (!CoreUploaderOpts.requiresLoaded) {
		$.cachedScript(CoreUploaderOpts.requires, function() {
			CoreUploaderOpts.requiresLoaded=true;
			CoreUploaderOpts.requiresLoading=null;
			doIt();
		});
		CoreUploaderOpts.requiresLoading=true;
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
					$.extend(pluginOpts.formData, opts.postData);
				}
				if (opts.extensions) {
					pluginOpts.fileExt=opts.extensions;
				}
				if (opts.image) {
					pluginOpts.buttonImage=opts.image;
				}
				if (opts.width) {
					pluginOpts.width=opts.width;
				}
				if (opts.height) {
					pluginOpts.height=opts.height;
				}
				pluginOpts.uploader=opts.serverScript;
				$(selector).uploadify(pluginOpts);
			break; // }
		}
	}
	doIt();
}
