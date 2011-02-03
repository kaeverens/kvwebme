function kdnd_addDropHandler(source_class,target_selector,func){
	if(!kdnd_targets[source_class])kdnd_targets[source_class]={};
	kdnd_targets[source_class][target_selector]=func;
}
function kdnd_drag(e){
	if(!window.kdnd_dragging)return;
	var m={x:e.pageX,y:e.pageY};
	clearSelections();
	window.kdnd_drag_wrapper.style.position='absolute';
	window.kdnd_drag_wrapper.style.display ='block';
	window.kdnd_drag_wrapper.style.left    =(m.x+window.kdnd_offset.x)+'px';
	window.kdnd_drag_wrapper.style.top     =(m.y+window.kdnd_offset.y)+'px';
	if($j(kdnd_source_el).hasClass('drag_this')){
		kdnd_source_el.style.visibility='hidden';
	}
}
window.kdnd_dragFinish=function(e,notest){
	clearTimeout(window.dragTrigger);
	if(!window.kdnd_dragging)return;
	if(!notest){ // check for targets and run functions if found
		var a,b,els,m={x:e.pageX,y:e.pageY},el;
		$each(kdnd_targets[window.kdnd_drag_class],function(fn,a){
			$j(a).each(function(key,el){
				if(getOffset(el,'Left')<=m.x&&m.x<getOffset(el,'Left')+el.offsetWidth&&getOffset(el,'Top')<=m.y&&m.y<getOffset(el,'Top')+el.offsetHeight){
					e.sourceElement=kdnd_source_el;
					e.targetElement=el;
					fn(e);
				}
			});
		});
		if($j(kdnd_source_el).hasClass('drag_this')){
			kdnd_source_el.style.left      =(m.x+window.kdnd_offset.x)+'px';
			kdnd_source_el.style.top       =(m.y+window.kdnd_offset.y)+'px';
			kdnd_source_el.style.visibility='visible';
		}
	}
	{ // cleanup
		window.kdnd_dragging=false;
		$j.event.remove(document,'mousemove',kdnd_drag);
		$j.event.remove(document,'mouseup',kdnd_dragFinish);
		$j(window.kdnd_drag_wrapper).remove();
		window.kdnd_drag_wrapper=null;
		window.kdnd_source_el=null;
	}
}
function kdnd_dragInit(el,source_class){
	return function(e){
		if(e.type=="contextmenu" || e.button==2)return;
		$j.event.add(document,'mouseup',function(e){kdnd_dragFinish(e);});
		clearTimeout(window.dragTrigger);
		window.dragTrigger=setTimeout(function(){
			kdnd_dragStart(el,source_class);
		},100);
		window.kdnd_offset={'x':el.offsetLeft-e.pageX,'y':el.offsetTop-e.pageY};
		e.stopPropagation();
	};
}
function kdnd_dragStart(el,source_class){
	window.kdnd_dragging=true;
	window.kdnd_drag_class=source_class;
	window.kdnd_source_el=el;
	var content=el.dragDisplay?el.dragDisplay():el.cloneNode(true);
	if($j(el).css('position')=='absolute' || $j(el).css('position')=='fixed'){
		content.style.position='static';
		content.style.left    =0;
		content.style.top     =0;
	}
	if(!$j(el).hasClass('drag_this'))window.kdnd_offset={'x':16,'y':0};
	window.kdnd_drag_wrapper=document.createElement('div');
	window.kdnd_drag_wrapper.id='kdnd_drag_wrapper';
	window.kdnd_drag_wrapper.style.display='none';
	window.kdnd_drag_wrapper.style.opacity=.7;
	window.kdnd_drag_wrapper.appendChild(content);
	document.body.appendChild(window.kdnd_drag_wrapper);
	$j.event.add(document,'mousemove',kdnd_drag);
}
function kdnd_makeDraggable(source_class){
	if($type(source_class)=='array'){
		return source_class.each(kdnd_makeDraggable);
	}
	$j('.'+source_class).each(function(key,el){
		if(el.kdnd_applied)return;
		el.kdnd_applied=true;
		if(!el.dragevents)el.dragevents=[];
		if(!el.dragevents[source_class])el.dragevents[source_class]=kdnd_dragInit(el,source_class);
		$j.event.add(el,'mousedown',el.dragevents[source_class]);
	});
}
window.kdnd_unmakeDraggable=function(source_class){
	if($type(source_class)=='array'){
		return source_class.each(kdnd_unmakeDraggable);
	}
	var els=$j('.'+source_class),i,el;
	for(i=0;i<els.length;++i){
		el=els[i];
		if(!el.kdnd_applied)continue;
		el.kdnd_applied=false;
		if(!el.dragevents)el.dragevents=[];
		if(!el.dragevents[source_class])el.dragevents[source_class]=kdnd_dragInit(el,source_class);
		$j.event.remove(el,'mousedown',el.dragevents[source_class]);
	}
}
{ // variables
	var kdnd_targets=[];
}
