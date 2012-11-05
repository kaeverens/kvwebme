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
	var $input=$('<input id="'+id+'" class="date-human-readable" value="'+Core_dateM2H($this.val())+'" />');
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
function Core_menuShow2(items, name, prefix, depth) {
	function numSubItems(obj) {
		var subitems=0;
		$.each(obj, function(key, val) {
			if (typeof key == 'number') {
				return;
			}
			subitems+=(typeof val!='string') || key=='_link';
		});
		return subitems;
	}
	var html='', link, submenus=0, numlinks=0;
	var target=items._target?' target="'+items._target+'"':'';
	if (items._link) {
		if (items._link.indexOf('javascript:')===0) {
			link='href="#" onclick="'+items._link.replace(/^javascript:/, '')
				+';return false"';
		}
		else {
			link='href="'+items._link+'"';
		}
		var icon=items._icon
			?'<img class="icon" src="/a/f=getImg/w=20/h=20/'+items._icon+'"/>'
			:'';
		html+='<a '+link+target+' class="__" lang-context="core">'+icon+name+'</a>';
	}
	else if (name!='top') {
		html+='<a href="#'+prefix+'-'+name+'" class="__" lang-context="core">'+name+'</a>';
	}
	$.each(items, function(key, val) {
		numlinks++;
	});
	if (numlinks==1 && items._link) {
		return html;
	}
	var menuItems=[], menuOrds=[];
	$.each(items, function(key, val) {
		if (!key || !numSubItems(val) || /^_/.test(key)) {
			return;
		}
		var submenu=Core_menuShow2(val, key, prefix+'-'+name, depth+1);
		menuItems.push('<li>'+submenu+'</li>');
		menuOrds.push(val._ord||0);
	});
	for (var i=0;i<menuOrds.length-1;++i) {
		for (var j=i+1;j<menuOrds.length;++j) {
			if (menuOrds[j]<menuOrds[i]) {
				var tmp=menuOrds[i];
				menuOrds[i]=menuOrds[j];
				menuOrds[j]=tmp;
				tmp=menuItems[i];
				menuItems[i]=menuItems[j];
				menuItems[j]=tmp;
			}
		}
	}
	var submenuhtml=menuItems.join('');
	if (submenuhtml) {
		submenuhtml='<ul>'+submenuhtml+'</ul>';
	}
	if (depth<2 && submenuhtml) {
		submenuhtml='<div id="'+prefix+'-'+name+'">'+submenuhtml+'</div>';
	}
	return html+submenuhtml;
}
function Core_menuShow(items) {
	var html=Core_menuShow2(items, 'top', 'menu', 0);
	$('#header').html(html);
	Core_menuShowInitEvents();
	__langInit();
}
function Core_menuShowInitEvents() {
	$('#menu-top>ul>li>a').each(function(){
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
}
function Core_prompt(text, val, validator, callback) {
	var html='<div class="prompt"><p>'+text+'</p><input/></div>';
	var $prompt=$(html).dialog({
		'close':function() {
			$prompt.remove();
		},
		'modal':true,
		'buttons':{
			'OK':function() {
				var val=$inp.val();
				if (validator && !validator(val)) {
					return;
				}
				$prompt.remove();
				callback(val);
			}
		}
	});
	var $inp=$prompt.find('input');
	$inp.val(val);
}
function Core_saveAdminVars(name, val) {
	adminVars[name]=val;
	$.post('/a/f=adminAdminVarsSave', {
		'name':name,
		'val':val
	});
}
function Core_screen(plugin, page) {
	var bits=plugin.split(/[^a-zA-Z]/);
	for (var fname='', i=0;i<bits.length;++i) {
		fname+=bits[i].charAt(0).toUpperCase()+bits[i].slice(1);
	}
	fname+='_screen';
	if (window[fname]) {
		$('#wrapper').html('<div id="content"/>');
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
function Core_sidemenu(links, plugin, currentpage) {
	var html='<ul>';
	for (var i=0;i<links.length;++i) {
		html+='<li><a href="javascript:Core_screen(\''
			+plugin+'\', \''+(links[i].replace(/[^a-zA-Z]/g, ''))+'\')"'
			+' lang-context="core" class="__';
		if (links[i]==currentpage) {
			html+=' current-page';
		}
		html+='">'+links[i]+'</a></li>';
	}
	$('#sidebar1').html(html+'</ul>');
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
		var params={
			'bJQueryUI':true
		};
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
	$.post('/a/f=adminMenusGet', Core_menuShow);
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
