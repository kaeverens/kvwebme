$(function(){
	$("#page_vars_translate_page_id").remoteselectoptions({url:"/a/f=adminPageParentsList"});
	$("#page_vars_translate_language,#page_vars_translate_language_from").remoteselectoptions({url:"/a/p=translate/f=languagesGet"});
});
