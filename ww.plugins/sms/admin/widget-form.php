<?php
/**
	* SMS widget form
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

require_once $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if (!Core_isAdmin()) {
	die('access denied');
}

$sms_addressbook_id=@$_REQUEST['sms_addressbook_id'];

// { Addressbook
echo '<strong>Addressbook</strong><br />';
$cs=dbAll('select id,name from sms_addressbooks order by name');
if (!count($cs)) {
	echo 'no sms_addressbook_ids created. please <'
		.'a href="/ww.admin/plugin.php?_plugin=sms&_page=sms_addressbook_ids">'
		.'create one</a> first.';
	Core_quit();
}
else {
	$ids=explode(',', $sms_addressbook_id);
	echo '<div class="sms_addressbooks">'
		.'<input type="hidden" name="sms_addressbook_id" value="'
		.$sms_addressbook_id.'" />';
	foreach ($cs as $v) {
		echo '<input type="checkbox" value="'.$v['id'].'"';
		if (in_array($v['id'], $ids)) {
			echo ' checked="checked"';
		}
		echo '>'.htmlspecialchars($v['name']).'<br />';
	}
	echo '</div>';
}
// }
?>
<script>
if(!window.sms_funcs_loaded){
	window.sms_funcs_loaded=true;
	$('.sms_addressbooks').live('click',function(){
		var $div=$(this).closest('.sms_addressbooks');
		var ids=[];
		$div.find('input:checked').each(function(){
			ids.push($(this).val());
		});
		$div.find('input[name=sms_addressbook_id]').val(ids.join(','));
	});
}
</script>
