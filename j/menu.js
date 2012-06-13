function event_target(e) { // taken from http://www.quirksmode.org/js/events_properties.html
	var targ;
	if (!e) {
		e=window.event;
	}
	if (e.target) {
		targ=e.target;
	}
	else {
		if (e.srcElement) {
			targ=e.srcElement;
		}
	}
	return targ;
}
function AjaxMenu_getChildren(a){
	if (!a) {
		return;
	}
	var b=a[0];
	_am.menus[b]=a[1];
	menu_cache[b]=a;
	if (document.getElementById('ajaxmenu'+b)) {
		setTimeout('ajaxmenu_initialise("'+b+'")',1);
	}
	if (_am.onload) {
		if (_am.onload()) {
			_am.onload=null;
		}
	}
}
function ajaxmenu_initialise(p){
	var a=_am.menus[p];
	if (a) {
		var e=document.getElementById('ajaxmenu'+p),i;
		if (!e) {
			return;
		}
		e.innerHTML='';
		e.style.display='none';
		if (e.className.indexOf('accordion')!=-1) {
			_am.accordion=1;
		}
		else if (e.className.indexOf('two-tier')!=-1) {
			_am.two_tier=1;
		}
		if (e.className.indexOf('click_required')!=-1) {
			_am.click_required=1;
			$(e).removeClass('click_required');
		}
		if (!a.length) {
			e.innerHTML='&nbsp;';
		}
		for (var i=0;i<a.length;++i) {
			if (_am.accordion && a[i].numchildren>0) {
				a[i].link='javascript:;';
			}
			var l=document.createElement('a');
			l.href=a[i].link+(p && _am.preopen_menu && a[i].link!='javascript:;'?'#am_open='+p:'');
			l.id='ajaxmenu_link'+a[i].id;
			l.className=a[i].classes;
			l.innerHTML='<span class="l"></span>'+a[i].name+'<span class="r"></span>';
			if (((p==_am.topMenu&&_am.align=='vertical') || (p!=_am.topMenu&&p!='am_top')) && !_am.two_tier) {
				l.style.display='block';
			}
			l.parentId=p;
			e.appendChild(l);
			if (l.offsetWidth>250) {
				l.setStyle('width',120);
			}
			if (!_am.click_required || a[i].numchildren==0) {
				$(l).mouseover(function(){
					var i=this.id.replace('ajaxmenu_link', '');
					ajaxmenu_setActiveMenu(i);
					ajaxmenu_openSubMenus(i);
				});
			}
			else {
				$(l).mouseover(function() {
					ajaxmenu_setActiveMenu(this.id.replace('ajaxmenu_link', ''));
				});
				if (a[i].numchildren) {
					$(l).click(function(e){
						var p=event_target(e);
						var i=p.id.replace('ajaxmenu_link',''),g=p.parentId!=_am.topMenu;
						clearTimeout(_am.activeSetTimeout);
						ajaxmenu_openSubMenus(i);
						e.cancelBubble = true;
						if (e.stopPropagation) {
							e.stopPropagation();
						}
					});
				}
			}
			if (_am.accordion) {
				$(document)
					.unbind('click',ajaxmenu_queueClearMenus)
					.click(ajaxmenu_queueClearMenus);
			}
			else if (!_am.two_tier) {
				$.event.add(l,'mouseout',ajaxmenu_queueClearMenus);
			}
			if (+(a[i].numchildren)) {
				setTimeout('ajaxmenu_initialise(_am.menus["'+p+'"]['+i+'].id)',1);
			}
		}
		if (_am.accordion && e.id!='ajaxmenu'+_am.topMenu) {
			$(e).slideDown(300);
		}
		else {
			e.style.display='block';
			var w=e.width,h=e.offsetHeight,y=e.offsetTop,wh=$(window).height();
			var scrolly = typeof window.pageYOffset != 'undefined' ? window.pageYOffset : (document.documentElement.scrollTop?document.documentElement.scrollTop:document.body.scrollTop);
			if (h+y>wh+scrolly) {
				e.style.top=(wh+scrolly-h)+'px';
			}
		}
	}
	else{
		if (menu_cache[p]) {
			setTimeout('AjaxMenu_getChildren(menu_cache['+p+'])', 1);
		}
		else {
			$.get(
				'/a/f=getMenu/lang='+pagedata.lang+'/pid='+p+'/id='+pagedata.id+'/top_id='+_am.topMenu+'/random='+Math.random(),
				AjaxMenu_getChildren
			);
		}
	}
}
function ajaxmenu_removeInvalidMenus(i) {
	if (_am.noclose) {
		return;
	}
	_am.activeMenu=i;
	var ajaxmenu_validMenus=[i],p=i,r,v;
	while (p!=_am.topMenu&&p!='am_top') {
		p=document.getElementById('ajaxmenu_link'+p);
		if (!p || !p.parentId) {
			return;
		}
		p=p.parentId;
		ajaxmenu_validMenus[ajaxmenu_validMenus.length]=p;
	}
	$('a.menuItemTop.opened,a.menuItem.opened').removeClass('opened');
	for (p=0;p<_am.openMenus.length;++p) {
		r=0;
		for (v=0;v<ajaxmenu_validMenus.length;++v) {
			if (ajaxmenu_validMenus[v]==_am.openMenus[p]) {
				r=1;
			}
		}
		if (!r) {
			var el=$('#ajaxmenu'+_am.openMenus[p]);
			if (!el.length) {
				continue;
			}
			if (_am.accordion) {
				el.slideUp(300);
			}
			else {
				el.remove();
			}
		}
	}
	_am.openMenus=ajaxmenu_validMenus;
	for (p=0;p<ajaxmenu_validMenus.length;++p) {
		$('#ajaxmenu_link'+ajaxmenu_validMenus[p]).addClass('opened');
	}
}
function ajaxmenu_setActiveMenu(i) {
	if (_am.activeSetTimeout) {
		clearTimeout(_am.activeSetTimeout);
		_am.activeSetTimeout=0;
	}
	ajaxmenu_removeInvalidMenus(i);
}
function ajaxmenu_openSubMenus(i) {
	var p=document.getElementById('ajaxmenu_link'+i);
	if ((_am.two_tier && p && p.parentId=='0') || (_am.menus[i]&&_am.menus[i].length)) {
		if (_am.accordion) {
			if (!document.getElementById('ajaxmenu'+i)) {
				var s=document.createElement('div');
				s.id='ajaxmenu'+i;
				s.className='menu';
				s.innerHTML='loading...';
				$(s).insertAfter(p);
			}
		}
		else {
			if (!document.getElementById('ajaxmenu'+i)) {
				var submenuWrapper=document.body;
				if (_am.two_tier) {
					submenuWrapper=p.parentNode.parentNode;
				}
				var x;
				var g=p.parentId!=_am.topMenu;
				var y=$(p).offset().top+(g||(_am.align=='vertical')?0:p.offsetHeight);
				if(_am.two_tier) {
					x=p.parentNode.offsetLeft;
					y=p.parentNode.offsetTop+p.offsetHeight;
				}
				else{
					x=$(p).offset().left;
					if (g||(_am.align=='vertical')) {
						x+=p.offsetWidth;
					}
					if (x+150>$(window).width() && g) {
						x=$(p).offset().left-150;
					}
				}	
				var s=document.createElement('div');
				s.id='ajaxmenu'+i;
				s.className='menu';
				if(_am.two_tier) {
					s.className+=' tier-two';
				}
				else {
					s.style.position='absolute';
					s.style.left=x+'px';
					s.style.top=y+'px';
				}
				s.innerHTML='loading...';
				submenuWrapper.appendChild(s);
			}
		}
		ajaxmenu_initialise(i);
		_am.openMenus.push(i);
		return true;
	}
}
function ajaxmenu_queueClearMenus(){
	_am.activeSetTimeout=setTimeout('ajaxmenu_removeInvalidMenus(_am.topMenu)',300);
}
$(function() {
	// { variables
		window.m=$('.ajaxmenu')[0];
		window._am={
			'accordion':0,
			'two_tier':0,
			'click_required':0,
			'menus':[],
			'noclose':(m.className.indexOf('noclose')!=-1),
			'preopen_menu':0,
			'topMenu':m.id.replace(/ajaxmenu(.*)/,'$1')
		};
		if (document.location.toString().replace(/http:\/\/[^\/]*\//,'').substr(0,5)=='admin') {
			_am.topMenu='am_top';
		}
		_am.align=m.className.indexOf('menuBarLeft')==-1?'horizontal':'vertical';
		_am.activeMenu=_am.topMenu;
		_am.activeSetTimeout=0;
		if (m.className.indexOf('preopen_menu')!=-1) {
			_am.preopen_menu= +(document.location.toString().replace(/.*#am_open=([0-9]*)$/,'$1'));
		}
		_am.openMenus=[_am.topMenu,''+pagedata.ctop];
	// }
	_am.onload=function(){
		if (!_am.two_tier && !_am.preopen_menu) {
			return false;
		}
		pagedata.id=_am.preopen_menu?_am.preopen_menu:pagedata.ctop;
		return ajaxmenu_openSubMenus(pagedata.id);
	};
	
	window.menu_cache=[];
	ajaxmenu_initialise(_am.topMenu);
});
