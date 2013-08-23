<?php
/**
	* panels admin page
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

echo '<table style="width:95%"><tr><td class="splitter-panel-left">'
	.'<h3>'.__('Widgets').'</h3><p>'
	.__('Drag a widget into a panel on the right.')
	.'</p>'
	.'<div id="widgets"></div><br style="clear:both" /></td>'
	.'<td style="width:220px"><h3>'.__('Panels').'</h3>'
	.'<p>'.__('Click a header to open it.').'</p>'
	.'<div id="panels"></div><br style="clear:both" /></td></tr></table>'
	.'<link rel="stylesheet" href="/ww.plugins/panels/c/admin.css"/>';
// { panel and widget data
echo '<script>';
// { panels
$ps=array();
$rs=dbAll('select * from panels order by name');
foreach ($rs as $r) {
	$ps[]=array(
		'id'=>$r['id'],
		'disabled'=>(int)$r['disabled'],
		'name'=>$r['name'],
		'widgets'=>json_decode($r['body'])
	);
}
echo 'ww.panels='.json_encode($ps).';';
// }
// { widgets
echo 'ww.widgets=[';
$ws=array();
foreach ($PLUGINS as $n=>$p) {
	if (isset($p['frontend']['widget'])) {
		$ws[]=json_encode(
			array(
				'type'        => $n,
				'description' => is_callable($p['description'])?$p['description']():$p['description'],
				'name'        => is_callable($p['name'])?$p['name']():$p['name']
			)
		);
	}
	if (isset($p['admin']['widget']['css_include'])) {
		WW_addCSS($p['admin']['widget']['css_include']);
	}
	if (isset($p['admin']['widget']['js_include'])) {
		if (!is_array($p['admin']['widget']['js_include'])) {
			$p['admin']['widget']['js_include']=array(
				$p['admin']['widget']['js_include']
			);
		}
		foreach ($p['admin']['widget']['js_include'] as $j) {
			WW_addScript($j);
		}
	}
}
$ws[]=json_encode(array(
	'type'=>'languages',
	'description' => __('Language selector'),
	'name'        => __('Languages')
));
echo join(',', $ws);
echo '];';
// }
// { widget forms
echo 'ww.widgetForms={';
$ws=array();
foreach ($PLUGINS as $n=>$p) {
	if (isset($p['admin']['widget'])
		&& isset($p['admin']['widget']['form_url'])
	) {
		$ws[]='"'.$n.'":"'.addslashes($p['admin']['widget']['form_url']).'"';
	}
}
echo join(',', $ws);
echo '};';
// }
// }
?>
</script>
<script src="/ww.plugins/panels/admin/index.js"></script>
<script src="/j/jquery.inlinemultiselect.js"></script>
