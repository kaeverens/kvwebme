function convert_date_to_human_readable(){
	var $this=$(this);
	var	id='date-input-'+Math.random().toString().replace(/\./,'');
	var dparts=$this.val().split(/-/);
	$this
		.datepicker({
			dateFormat:  'yy-mm-dd',
			modal:       true,
			altField:    '#'+id,
			altFormat:   'DD, d MM, yy',
			changeMonth: true,
			changeYear:  true,
			onSelect:    function(dateText,inst){
				this.value=dateText;
			}
		});
	var $wrapper=$this.wrap('<div style="position:relative" />');
	var $input=$('<input id="'+id+'" class="date-human-readable" value="'+date_m2h($this.val())+'" />');
	$input.insertAfter($this);
	$this.css({
		'position':'absolute',
		'opacity':0
	});
	$this
		.datepicker(
			'setDate', new Date(dparts[0],dparts[1]-1,dparts[2])
		);
}
function Core_saveAdminVars(name, val) {
	adminVars[name]=val;
	$.post('/a/f=adminAdminVarsSave', {
		'name':name,
		'val':val
	}, function(ret) {
		console.log(ret);
	});
}
function Core_sidemenu(links, plugin, currentpage) {
	var html='<ul>';
	for (var i=0;i<links.length;++i) {
		html+='<li><a href="javascript:Core_screen(\''
			+plugin+'\', \''+(links[i].replace(/[^a-zA-Z]/g, ''))+'\')"';
		if (links[i]==currentpage) {
			html+=' class="current-page"';
		}
		html+='>'+links[i]+'</a></li>';
	}
	$('#sidebar1').html(html+'</ul>');
}
function Core_screen(plugin, page) {
	var fname=plugin.charAt(0).toUpperCase()+plugin.slice(1)+'_screen';
	if (window[fname]) {
		if (!$('#sidebar1').length) {
			$('#wrapper').html('<div id="sidebar1"/><div id="content"/>');
		}
		window.current_screen=plugin+'|'+page;
		return window[fname](page.replace(/^js:/, ''));
	}
	if (/^Core[A-Z]/.test(plugin)) {
		$('head')
			.append('<link rel="stylesheet" href="/ww.admin/'+plugin+'/admin.css"/>');
		$.getScript('/ww.admin/'+plugin+'/admin.js?'+(new Date()).getTime(), function(){
			if (!window[fname]) {
				return;
			}
			Core_screen(plugin, page);
		});
	}
	else {
		$('head')
			.append('<link rel="stylesheet" href="/ww.plugins/'+plugin+'/admin.css"/>');
		$.getScript('/ww.plugins/'+plugin+'/admin.js?'+(new Date()).getTime(), function(){
			if (!window[fname]) {
				return;
			}
			Core_screen(plugin, page);
		});
	}
}
$(function(){
	$.post('/a/f=adminLoadJSVars', function(ret) {
		jsvars=ret;
		if (!jsvars.datatables) {
			jsvars.datatables=[];
		}
	});
	function keepAlive(){
		setTimeout(keepAlive,1700000);
		$.get('/ww.admin/keepalive.php');
	}
	$('.datatable').each(function(){
		var $this=$(this);
		var id=$this.attr('id')||false;
		var params={};
		if ($this.hasClass('desc')) {
			params["aaSorting"]=[[0,'desc']];
		}
		if (id && jsvars.datatables[id]) {
			params["iDisplayLength"]=jsvars.datatables[id].show;
		}
		$this.dataTable(params);
	});
	$('.dataTables_length select').live('change', function() {
		var $this=$(this);
		var id=$this.closest('.dataTables_wrapper').attr('id').replace(/_wrapper$/, '');
		if (!id) {
			return;
		}
		if (!jsvars.datatables[id]) {
			jsvars.datatables[id]={};
		}
		jsvars.datatables[id].show=$this.val();
		$.post('/a/f=adminSaveJSVar', {
			'datatables':jsvars.datatables
		});
	});
	$('input.date-human').each(convert_date_to_human_readable);
	$('#menu-top>ul>li>a').each(function(){
		if(!(/#/.test(this.href.toString())))return; // only apply menu to links with '#' in them
		$(this).fgmenu({
			content: $(this).next().html(),
			flyOut:true,
			showSpeed: 400,
			callerOnState: '',
			loadingState: '',
			linkHover: '',
			linkHoverSecondary: '',
			flyOutOnState: ''
		});
	});
	if($('.help').length){
		$('<div id="help-opener"></div>')
			.appendTo('#header')
			.toggle(function(){
				$('.help').css('display','block');
			},
			function(){
				$('.help').css('display','none');
			});
		a=$('.help');
		a.each(function(){
			var hpages=this.className.split(' ')[1].split('/');
			if (hpages.length==1) {
				this.rel='/ww.help/'+hpages[0]+'.html';
			}
			if (hpages.length==2) {
				this.rel='/ww.plugins/'+hpages[0]+'/h/'+hpages[1]+'.html';
			}
			if (!this.title) {
				this.title=$(this).text();
			}
		});
		$('.help').cluetip();
	}
	setTimeout(keepAlive,1700000);
	$('input[type=number]').live('keyup', function() {
		var val=this.value;
		if (!/[^\-0-9.]/.test(val)) {
			return;
		}
		this.value=val.replace(/[^\-0-9.]/, '');
	});
	$('.docs').live('click', function() {
		var $this=$(this);
		var page=$this.attr('page');
		$.get(page, function(html) {
			$(html).dialog({
				'modal':true,
				'width':'90%',
				'close':function() {
					$(this).remove();
				}
			});
		});
		return false;
	});
});
var jsvars={
	'datatables':{}
};
function Core_createTranslatableInputs() {
	$('input.translatable').each(function() {
		function showLanguages() {
			if ($button.attr('has-translatable-menu')) {
				$('#translatable-menu').remove();
				$button.removeAttr('has-translatable-menu');
				return;
			}
			var menu='<div id="translatable-menu" style="top:'
				+$button.outerHeight()+'px;" class="menu">';
			for (var i=0;i<languages.length;++i) {
				var lang=languages[i];
				menu+='<a href="#" lang="'+lang.code+'"';
				if (lang.code==$button.attr('translatable-selected-language')) {
					menu+=' class="selected"';
				}
				menu+='>'+lang.name+'</a>';
			}
			menu+='</div>';
			$(menu)
				.appendTo($div)
				.find('a').click(function() {
					var $this=$(this);
					$this.siblings().removeClass('selected');
					$this.addClass('selected');
					$button.attr('translatable-selected-language', $this.attr('lang'));
					selectLanguage();
				});
			$button.attr('has-translatable-menu', true);
		}
		function selectLanguage() {
			$('#translatable-menu').remove();
			$button.removeAttr('has-translatable-menu');
			var lang=$button.attr('translatable-selected-language');
			if (!vals[lang]) {
				vals[lang]=vals[languages[0].code];
			}
			$inp.val(vals[lang]);
		}
		function update() {
			var lang=$button.attr('translatable-selected-language');
			vals[lang]=$inp.val();
			$orig.val($.toJSON(vals));
		}
		var $orig=$(this);
		if ($orig.attr('hasTranslatable')) {
			return;
		}
		// { get the data
		$orig
			.attr('autocomplete', 'off')
			.attr('hasTranslatable', true)
		var val=$orig.val();
		var vals;
		try{
			var vals=$.parseJSON(val);
			if (vals==null) {
				vals={};
				vals[languages[0].code]=val;
			}
		}
		catch(err) {
			var vals={};
			vals[languages[0].code]=val;
		}
		// }
		// { convert the element
		var $div=$('<div style="position:relative;width:'+$orig.width()+'px;"/>');
		var $inp=$('<input style="width:'+($orig.width()-16)+'px'
			+';float:left;display:block"/>')
			.change(update)
			.appendTo($div);
		var $button=$('<span class="ui-icon ui-icon-triangle-1-s '
			+'translatable-button"></span>')
			.click(showLanguages)
			.attr('translatable-selected-language', languages[0].code)
			.appendTo($div);
		$div.insertAfter($orig.css('display', 'none'));
		if (languages.length<2) {
			$inp.css('width', $orig.width()+'px');
			$button.css('display', 'none');
		}
		selectLanguage();
		// }
	});
}
