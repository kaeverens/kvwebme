<?php
echo '<h2>'.__('Localisation').'</h2>';
// { handle actions
if($action=='Save'){
	dbQuery("DELETE FROM site_vars");
	// { languages
	$langs='';
	$lang_names=getVar('lang_name');
	$lang_isos=getVar('lang_iso');
	if(is_array($lang_names) && is_array($lang_isos)){
		$langs=array();
		for($i=0;$i<count($lang_names);++$i){
			if(!$lang_names[$i] || !$lang_isos[$i])continue;
			$langs[]='{"name":"'.addslashes($lang_names[$i]).'","iso":"'.addslashes($lang_isos[$i]).'"}';
		}
		$langs=addslashes('['.join(',',$langs).']');
	}
	dbQuery("INSERT INTO site_vars SET name='languages',value='$langs'");
	// }
	// { currencies
	$curs='';
	$cur_names=getVar('cur_name');
	$cur_isos=getVar('cur_iso');
	$cur_symbols=getVar('cur_symbol');
	$cur_values=getVar('cur_value');
	if(is_array($cur_names) && is_array($cur_isos) && is_array($cur_values)){
		$curs=array();
		for($i=0;$i<count($cur_names);++$i){
			if(!$cur_names[$i] || !$cur_isos[$i] || !$cur_values[$i] || !$cur_symbols[$i])continue;
			if(!$i)$cur_values[0]=1;
			$curs[]='{"name":"'.addslashes($cur_names[$i]).'","iso":"'.addslashes($cur_isos[$i]).'","symbol":"'.addslashes($cur_symbols[$i]).'","value":"'.addslashes($cur_values[$i]).'"}';
		}
		$curs=addslashes('['.join(',',$curs).']');
	}
	dbQuery("INSERT INTO site_vars SET name='currencies',value='$curs'");
	// }
	// { user discounts
	dbQuery("INSERT INTO site_vars SET name='user_discount',value=".((float)$_REQUEST['user_discount']));
	// }
}
// }
// { form
echo '<form method="post" action="siteoptions.php?page=localisation"><table>';
// { languages
echo '<tr><th>'.__('Languages').'</th><td>'.__('If any languages are entered here, the first row will be treated as the "default" language of the site.');
echo '<table id="siteoptions_languages">';
echo '<tr><th>'.__('Name').'</th><th>'.__('ISO code').' <a href="http://en.wikipedia.org/wiki/List_of_ISO_639-2_codes" class="external">(Alpha-2)</a></th></tr>';
// { draw existing languages
$r=dbRow("SELECT * FROM site_vars WHERE name='languages'");
if(count($r)){
	$langs=json_decode($r['value']);
	for($i=0;$i<count($langs);++$i){
		echo '<tr><td><input name="lang_name['.$i.']" value="'.htmlspecialchars($langs[$i]->name).'" /></td>'
			.'<td><input name="lang_iso['.$i.']" value="'.htmlspecialchars($langs[$i]->iso).'" /></td>'
			.'<td><input name="lang_priority['.$i.']" value="'.htmlspecialchars($langs[$i]->priority).'" /></td></tr>';
	}
}
// }
echo '</table><a href="javascript:addLanguage()">'.__('Add Language').'</a></td></tr>';
// }
// { currencies
echo '<tr><th>'.__('Currencies').'</th><td>'.__('The first row will be treated as the "default" currency of the site. Values of all other currencies are relative to the first row.');
echo '<table id="siteoptions_currencies">';
echo '<tr><th>'.__('Name').'</th><th>'.__('ISO code').' <a href="http://www.iso.org/iso/support/faqs/faqs_widely_used_standards/widely_used_standards_other/currency_codes/currency_codes_list-1.htm" class="external">&nbsp;</a></th><th>Symbol</th><th>Value</th></tr>';
// { draw existing currencies
$r=dbRow("SELECT * FROM site_vars WHERE name='currencies'");
if(!count($r))$r=array('value'=>'[{"name":"Euro","iso":"eur","symbol":"€","value":1}]');
$curs=json_decode($r['value']);
for($i=0;$i<count($curs);++$i){
	echo '<tr><td><input name="cur_name['.$i.']" value="'.htmlspecialchars($curs[$i]->name).'" /></td>'
		.'<td><input name="cur_iso['.$i.']" value="'.htmlspecialchars($curs[$i]->iso).'" /></td>'
		.'<td><input name="cur_symbol['.$i.']" value="'.htmlspecialchars($curs[$i]->symbol).'" /></td>';
	echo '<td><input name="cur_value['.$i.']" value="'.htmlspecialchars($curs[$i]->value).'" /></td></tr>';
}
// }
echo '</table><a href="javascript:addCurrency()">'.__('Add Currency').'</a></td></tr>';
// }
// { user discounts
$r=dbRow("SELECT * FROM site_vars WHERE name='user_discount'");
echo '<tr><th>'.__('User discount').'</th><td>'.__('What discount percentage should new user registrants be set to?').'<br /><input name="user_discount" value="'.((float)$r['value']).'" /></td></tr>';
// }
echo '<tr><td colspan="2" style="text-align:right"><input type="submit" name="action" value="Save" /></td></tr></table></form>';
// }
// { javascripts
?>
<script type="text/javascript">
	function addLanguage(){
		var t=$M('siteoptions_languages'),r,c,langs,cs=0;
		langs=t.rows.length;
		r=t.insertRow(langs);
		c=r.insertCell(cs++);
		c.appendChild(new Element('input',{ 'name':'lang_name['+(langs-1)+']', }));
		c=r.insertCell(cs++);
		c.appendChild(new Element('input',{ 'name':'lang_iso['+(langs-1)+']', }));
	}
	addLanguage();
	function addCurrency(){
		var t=$M('siteoptions_currencies'),r,c,curs,cs=0;
		curs=t.rows.length;
		r=t.insertRow(curs);
		c=r.insertCell(cs++);
		c.appendChild(new Element('input',{ 'name':'cur_name['+(curs-1)+']', }));
		c=r.insertCell(cs++);
		c.appendChild(new Element('input',{ 'name':'cur_iso['+(curs-1)+']', }));
		c=r.insertCell(cs++);
		c.appendChild(new Element('input',{ 'name':'cur_symbol['+(curs-1)+']', }));
		c=r.insertCell(cs++);
		c.appendChild(new Element('input',{ 'name':'cur_value['+(curs-1)+']', }));
	}
	addCurrency();
</script>
<?php
// }
