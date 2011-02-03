/*
Copyright (c) 2003-2010, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

CKEDITOR.editorConfig = function( config )
{
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';
	config.skin="v2";
	config.toolbar="WebME";
	config.toolbar_WebME=[
		['Maximize','Source','Cut','Copy','Paste','PasteText'],
		['Undo','Redo','RemoveFormat','Bold','Italic','Underline','Subscript','Superscript'],
		['NumberedList','BulletedList','Outdent','Indent'],
		['JustifyLeft','JustifyCenter','JustifyRight'],
		['Link','Unlink','Anchor','Image','Flash','Table','SpecialChar','HorizontalRule'],
		['TextColor','BGColor'],
		['Styles','Format','Font','FontSize']
	];
};
