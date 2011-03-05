<?php
echo '<table style="width:95%"><tr>';
echo '<td class="splitter-panel splitter-panel-left"><h3>Widgets</h3><p>Drag a widget into a panel on the right.</p><div id="widgets"></div><br style="clear:both" /></td>';
echo '<td style="width:220px" class="splitter-panel splitter-panel-right"><h3>Panels</h3><p>Click a header to open it.</p><div id="panels"></div><br style="clear:both" /></td></tr>';
echo '</table>';
echo '<link rel="stylesheet" type="text/css" href="/ww.plugins/panels/c/admin.css" />';
// { panel and widget data
echo '<script>';
// { panels
echo 'ww.panels=[';
$ps=array();
$rs=dbAll('select * from panels order by name');
foreach($rs as $r)$ps[]='{id:'.$r['id'].',disabled:'.$r['disabled'].',name:"'.$r['name'].'",widgets:'.$r['body'].'}';
echo join(',',$ps);
echo '];';
// }
// { widgets
echo 'ww.widgets=[';
$ws=array();
foreach($PLUGINS as $n=>$p){
	if (isset($p['frontend']['widget'])) {
		$ws[]=json_encode(array(
			'type'        => $n,
			'description' => $p['description'],
			'name'        => $p['name']
		));
	}
	if (isset($p['admin']['widget']['css_include'])) {
		WW_addCSS($p['admin']['widget']['css_include']);
	}
	if (isset($p['admin']['widget']['js_include'])) {
		if (!is_array($p['admin']['widget']['js_include'])) {
			$p['admin']['widget']['js_include']=
				array($p['admin']['widget']['js_include']);
		}
		foreach ($p['admin']['widget']['js_include'] as $j) {
			WW_addScript($j);
		}
	}
}
echo join(',',$ws);
echo '];';
// }
// { widget forms
echo 'ww.widgetForms={';
$ws=array();
foreach($PLUGINS as $n=>$p){
	if(isset($p['admin']['widget']) && isset($p['admin']['widget']['form_url']))$ws[]='"'.$n.'":"'.addslashes($p['admin']['widget']['form_url']).'"';
}
echo join(',',$ws);
echo '};';
// }
// }
?>
</script><script src="/ww.plugins/panels/j/admin.js"></script>
<script src="/j/jquery.inlinemultiselect.js"></script>
