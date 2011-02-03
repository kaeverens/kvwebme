function plugin_malsup_media_player(){
	this.name='malsup_media_player';
	this.title='Play as browser-embedded object'; // TODO: string
	this.category='view';
	this.extensions=['asf','avi','mov','mpg','mpeg','mp4','qt','smil','swf','wmv','aif','aac','au','gsm','mid','midi','mov','m4a','snd','rm','wav','wma'];
	this.mode=0;
	this.defaultOpener=1;
	this.writable=2;//all
	this.doFunction=function(files){
		var url='plugins/malsup_media_player/player.php?ids='+files.join();
		kfm_pluginIframeShow(url);
	}
}
kfm_addHook(new plugin_malsup_media_player());
