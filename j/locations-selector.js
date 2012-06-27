$(function() {
	pagedata.locations={
		'all':[],
		'tree':{}
	};
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
		var $newSelect=$('<select id="core-location-'+(num+1)+'"><option/>');
		if (root.id!=undefined) {
			$newSelect.append('<option value="'+root.id+'">[choose '+name+']</option>');
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
		if (!count && isActive) {
			document.location=document.location.toString().replace(/[?#]?.*/, '')
				+'?__LOCATION='+root.id;
			return;
		}
		if (count) {
			$newSelect.change(update).insertAfter($this);
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
	var $newSelect=$('<select id="core-location-0"><option/>');
	$.each(pagedata.locations.tree, function(k, v) {
		if (k=='id') {
			return;
		}
		var $opt=$('<option/>').appendTo($newSelect);
		$opt.attr('value', k).text(k);
	});
	$selector.replaceWith($newSelect);
	$newSelect.change(update);
	// }
	// { activate the current location
	var bits=pagedata.locationName.split(/ \/ /);
	for (var i=0;i<bits.length;++i) {
		$('#core-location-'+i).val(bits[i]).change();
	}
	isActive=true;
	// }
});
