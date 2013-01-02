$(function() {
	function getChildren(a){
		if (!a) {
			return;
		}
		var b=a[0];
		var _am=window._am;
		_am.menus[b]=a[1];
		window.menu_cache[b]=a;
		if (document.getElementById('ajaxmenu'+b)) {
			setTimeout(
				(function(b){
					return function() {
						initialise(b);
					};
				})(b),
				1
			);
		}
		if (_am.onload) {
			if (_am.onload()) {
				_am.onload=null;
			}
		}
		window._am=_am;
	}
	function initialise(p){
		var _am=window._am;
		if (_am.menus[p]) {
			var a=_am.menus[p];
			var e=document.getElementById('ajaxmenu'+p), i;
			if (!e) {
				return;
			}
			var $e=$(e);
			$e.html('').css('display', 'none');
			if (e.className.indexOf('accordion')!=-1) {
				_am.accordion=1;
			}
			else if (e.className.indexOf('two-tier')!=-1) {
				_am.two_tier=1;
			}
			if (e.className.indexOf('click_required')!=-1) {
				_am.click_required=1;
				$e.removeClass('click_required');
			}
			if (!a.length) {
				e.innerHTML='&nbsp;';
			}
			for (i=0;i<a.length;++i) {
				if (_am.accordion && a[i].numchildren>0) {
					a[i].link='#';
				}
				var l=document.createElement('a');
				l.href=a[i].link+(p && _am.preopen_menu && a[i].link!='#'?'#am_open='+p:'');
				l.id='ajaxmenu_link'+a[i].id;
				l.className=a[i].classes;
				l.innerHTML='<span class="l"></span>'+a[i].name+'<span class="r"></span>';
				if (((p==_am.topMenu&&_am.align=='vertical') || (p!=_am.topMenu&&p!='am_top')) && !_am.two_tier) {
					l.style.display='block';
				}
				l.parentId=p;
				e.appendChild(l);
				if (l.offsetWidth>250) {
					l.setStyle('width', 120);
				}
				if (!_am.click_required || a[i].numchildren===0) {
					$(l).mouseover(menuMouseover);
				}
				else {
					$(l).mouseover(menuMouseoverSetActive);
					if (a[i].numchildren) {
						$(l).click(menuClick);
					}
				}
				if (_am.accordion) {
					$(document)
						.unbind('click',queueClearMenus)
						.click(queueClearMenus);
				}
				else if (!_am.two_tier) {
					$.event.add(l,'mouseout',queueClearMenus);
				}
				if (+(a[i].numchildren)) {
					setTimeout(
						initialisePI(p, i),
						1
					);
				}
			}
			if (_am.accordion && e.id!='ajaxmenu'+_am.topMenu) {
				$e.slideDown(300);
			}
			else {
				e.style.display='block';
				var h=e.offsetHeight,y=e.offsetTop,wh=$(window).height();
				var scrolly=typeof window.pageYOffset!='undefined'?
					window.pageYOffset:
					(document.documentElement.scrollTop?
						document.documentElement.scrollTop:
						document.body.scrollTop
					);
				if (h+y>wh+scrolly) {
					e.style.top=(wh+scrolly-h)+'px';
				}
			}
		}
		else{
			if (window.menu_cache[p]) {
				setTimeout('getChildren(menu_cache['+p+'])', 1);
			}
			else {
				$.get(
					'/a/f=getMenu/lang='+window.pagedata.lang+'/pid='+p+'/id='+
					window.pagedata.id+'/top_id='+_am.topMenu+'/random='+Math.random(),
					getChildren
				);
			}
		}
		window._am=_am;
	}
	function initialisePI(p, i) {
		return function() {
			initialise(_am.menus[p][i].id);
		};
	}
	function menuClick(e) {
		var p;
		if (!e) {
			e=window.event;
		}
		if (e.target) {
			p=e.target;
		}
		else {
			if (e.srcElement) {
				p=e.srcElement;
			}
		}
		var i=p.id.replace('ajaxmenu_link','');
		clearTimeout(window._am.activeSetTimeout);
		openSubMenus(i);
		e.cancelBubble = true;
		if (e.stopPropagation) {
			e.stopPropagation();
		}
	}
	function menuMouseover() {
		var i=this.id.replace('ajaxmenu_link', '');
		setActiveMenu(i);
		openSubMenus(i);
	}
	function menuMouseoverSetActive() {
		setActiveMenu(this.id.replace('ajaxmenu_link', ''));
	}
	function openSubMenus(i) {
		var p=document.getElementById('ajaxmenu_link'+i), s;
		var _am=window._am;
		if ((_am.two_tier && p && p.parentId=='0') || (_am.menus[i]&&_am.menus[i].length)) {
			if (_am.accordion) {
				if (!document.getElementById('ajaxmenu'+i)) {
					s=document.createElement('div');
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
					s=document.createElement('div');
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
			initialise(i);
			window._am.openMenus.push(i);
			return true;
		}
	}
	function queueClearMenus(){
		window._am.activeSetTimeout=setTimeout(
			function() {
				removeInvalidMenus(window._am.topMenu);
			},
			300
		);
	}
	function removeInvalidMenus(i) {
		var _am=window._am;
		if (_am.noclose) {
			return;
		}
		window._am.activeMenu=i;
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
		window._am.openMenus=ajaxmenu_validMenus;
		for (p=0;p<ajaxmenu_validMenus.length;++p) {
			$('#ajaxmenu_link'+ajaxmenu_validMenus[p]).addClass('opened');
		}
	}
	function setActiveMenu(i) {
		if (window._am.activeSetTimeout) {
			clearTimeout(window._am.activeSetTimeout);
			window._am.activeSetTimeout=0;
		}
		removeInvalidMenus(i);
	}
	// { variables
	var m=$('.ajaxmenu')[0];
	var _am={
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
	_am.openMenus=[_am.topMenu, ''+window.pagedata.ctop];
	// }
	_am.onload=function(){
		if (!_am.two_tier && !_am.preopen_menu) {
			return false;
		}
		window.pagedata.id=_am.preopen_menu?_am.preopen_menu:window.pagedata.ctop;
		return openSubMenus(window.pagedata.id);
	};
	window.menu_cache=[];
	window._am=_am;
	window.m=m;
	initialise(_am.topMenu);
});
