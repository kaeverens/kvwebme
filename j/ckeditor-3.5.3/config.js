CKEDITOR.editorConfig = function( config )
{
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';
	config.skin="v2";
	config.extraPlugins='swf';
	config.toolbar="WebME";
	config.toolbar_WebME=[
		['Maximize','Source','Cut','Copy','Paste','PasteText'],
		['Undo','Redo','RemoveFormat','Bold','Italic','Underline','Subscript','Superscript'],
		['NumberedList','BulletedList','Outdent','Indent'],
		['JustifyLeft','JustifyCenter','JustifyRight'],
		['Link','Unlink','Anchor','Image','Flash','jwplayer'],
		['Table','SpecialChar','HorizontalRule'],
		['TextColor','BGColor'],
		['Styles','Format','Font','FontSize']
	];
};
