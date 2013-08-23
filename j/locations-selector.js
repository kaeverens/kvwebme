$(function() {
	var bits;
	window.pagedata.locations={
		'all':[],
		'tree':{},
		'opts':$.extend({
			'numselects':1,
			'names':[]
		}, window.locationSelectorOpts||{})
	};
	window.locationSelectorOpts=undefined;
	var roots=[window.pagedata.locations.tree];
	var isActive=false;
	function update() {
		var $this=$(this), i, num=+$this.attr('id').replace(/[^0-9]/g, ''), names,
			root=roots[num], name=$this.val(), count=0;
		for (i=num+1;$('#core-location-'+i).length;++i) {
			$('#core-location-'+i).remove();
		}
		if (!name && !root.id) {
			for (i=num+1;i<window.pagedata.locations.opts.numselects;++i) {
				names=window.pagedata.locations.opts.names.split(',');
				$('<select id="core-location-'+(num+1)+'"'+
					' disabled="disabled"><option>'+(names[i]||'')+
					'</option></select>')
					.insertAfter($this);
			}
			if (isActive) {
				document.location=document.location.toString().replace(/[?#]?.*/, '')+
					'?__LOCATION=';
			}
			return;
		}
		if (!/[^0-9]/.test(name) && isActive) {
			document.location=document.location.toString().replace(/[?#]?.*/, '')+
				'?__LOCATION='+name;
			return;
		}
		roots[num+1]=root=root[name];
		var $newSelect=$('<select id="core-location-'+(num+1)+'">');
		if (root.id!==undefined) {
			names=window.pagedata.locations.opts.names.split(',');
			$newSelect.append('<option value="'+root.id+'">'+(names[num+1]||'')+'</option>');
		}
		$.each(root, function(k) {
			if (k=='id') {
				return;
			}
			var $opt=$('<option/>').appendTo($newSelect);
			$opt.attr('value', k).text(k);
			count++;
		});
		if (isActive) {
			document.location=document.location.toString().replace(/[?#]?.*/, '')+
				'?__LOCATION='+root.id;
			return;
		}
		if (count) {
			$newSelect.change(update).insertAfter($this);
		}
		else {
			for (i=num+1;i<window.pagedata.locations.opts.numselects;++i) {
				names=window.pagedata.locations.opts.names.split(',');
				$('<select id="core-location-'+(num+1)+'"'+
					' disabled="disabled"><option>'+(names[i]||'')+
					'</option></select>')
					.insertAfter($this);
			}
		}
	}
	// { replace selector drop-down with a lot of them
	var $selector=$('#core-location');
	$selector.find('option').each(function() {
		var $this=$(this);
		window.pagedata.locations.all.push({
			'id':$this.attr('value'),
			'name':$this.text()
		});
	});
	for (var i=0;i<window.pagedata.locations.all.length;++i) {
		var opt=window.pagedata.locations.all[i];
		bits=opt.name.split(/ \/ /);
		var root=window.pagedata.locations.tree;
		for (var j=0;j<bits.length;++j) {
			var name=bits[j];
			if (root[name]===undefined) {
				root[name]={};
			}
			root=root[name];
		}
		root.id=opt.id;
	}
	var names=window.pagedata.locations.opts.names.split(',');
	var $newSelect=$('<select id="core-location-0">'+
		'<option value="">'+(names[0]||'')+'</option>');
	$.each(window.pagedata.locations.tree, function(k) {
		if (k=='id') {
			return;
		}
		var $opt=$('<option/>').appendTo($newSelect);
		$opt.attr('value', k).text(k);
	});
	$selector.replaceWith($newSelect);
	$newSelect.change(update).change();
	// }
	// { activate the current location
	bits=window.pagedata.locationName?window.pagedata.locationName.split(/ \/ /):[];
	for (i=0;i<bits.length;++i) {
		$('#core-location-'+i).val(bits[i]).change();
	}
	isActive=true;
	// }
});
