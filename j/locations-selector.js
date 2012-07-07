$(function() {
	pagedata.locations={
		'all':[],
		'tree':{},
		'opts':$.extend({
			'numselects':1,
			'names':[]
		}, window.locationSelectorOpts||{})
	};
	window.locationSelectorOpts=undefined;
	var roots=[pagedata.locations.tree];
	var isActive=false;
	function update() {
		var $this=$(this);
		var num=+$this.attr('id').replace(/[^0-9]/g, '');
		var $sel;
		for (var i=num+1;$sel=$('#core-location-'+i), $sel.length;++i) {
			$sel.remove();
		}
		var root=roots[num];
		var name=$this.val();
		if (!name) {
			if (!root.id) {
				for (var i=num+1;i<pagedata.locations.opts.numselects;++i) {
					var names=pagedata.locations.opts.names.split(",");
					$('<select id="core-location-'+(num+1)+'"'
						+' disabled="disabled"><option>'+(names[i]||'')
						+'</option></select>')
						.insertAfter($this);
				}
				if (isActive) {
					document.location=document.location.toString().replace(/[?#]?.*/, '')
						+'?__LOCATION=';
				}
				return;
			}
		}
		if (!/[^0-9]/.test(name) && isActive) {
			document.location=document.location.toString().replace(/[?#]?.*/, '')
				+'?__LOCATION='+name;
			return;
		}
		var root=root[name];
		roots[num+1]=root;
		var $newSelect=$('<select id="core-location-'+(num+1)+'">');
		if (root.id!=undefined) {
			var names=pagedata.locations.opts.names.split(",");
			$newSelect.append('<option value="'+root.id+'">'+(names[num+1]||'')+'</option>');
		}
		var count=0;
		$.each(root, function(k, v) {
			if (k=='id') {
				return;
			}
			var $opt=$('<option/>').appendTo($newSelect);
			$opt.attr('value', k).text(k);
			count++;
		});
		if (isActive) {
			document.location=document.location.toString().replace(/[?#]?.*/, '')
				+'?__LOCATION='+root.id;
			return;
		}
		if (count) {
			$newSelect.change(update).insertAfter($this);
		}
		else {
			for (var i=num+1;i<pagedata.locations.opts.numselects;++i) {
				var names=pagedata.locations.opts.names.split(",");
				$('<select id="core-location-'+(num+1)+'"'
					+' disabled="disabled"><option>'+(names[i]||'')
					+'</option></select>')
					.insertAfter($this);
			}
		}
	}
	// { replace selector drop-down with a lot of them
	var $selector=$('#core-location');
	var val=$selector.val();
	$selector.find('option').each(function(k, v) {
		var $this=$(this);
		pagedata.locations.all.push({
			'id':$this.attr('value'),
			'name':$this.text()
		});
	});
	for (var i=0;i<pagedata.locations.all.length;++i) {
		var opt=pagedata.locations.all[i];
		var bits=opt.name.split(/ \/ /);
		var root=pagedata.locations.tree;
		for (var j=0;j<bits.length;++j) {
			var name=bits[j];
			if (root[name]==undefined) {
				root[name]={};
			}
			root=root[name];
		}
		root.id=opt.id;
	}
	var names=pagedata.locations.opts.names.split(",");
	var $newSelect=$('<select id="core-location-0">'
		+'<option value="">'+(names[0]||'')+'</option>');
	$.each(pagedata.locations.tree, function(k, v) {
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
	var bits=pagedata.locationName?pagedata.locationName.split(/ \/ /):[];
	for (var i=0;i<bits.length;++i) {
		$('#core-location-'+i).val(bits[i]).change();
	}
	isActive=true;
	// }
});
