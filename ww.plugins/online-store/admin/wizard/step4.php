<?php

/**
 * admin/wizard/step4.php, KV-Webme Online Store Plugin
 *
 * products
 *
 * @author     Conor Mac Aoidh <conormacaoidh@gmail.com>
 * @license    GPL 2.0
 * @version    1.0
 */

require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
require SCRIPTBASE.'ww.plugins/online-store/admin/wizard/product-types.php';

if(isset($_POST['wizard-company-name'])){ // validate post data
	$_SESSION['wizard']['company']['name']=$_POST['wizard-company-name'];
	$_SESSION['wizard']['company']['address']
		=$_POST['wizard-company-address'];
	$_SESSION['wizard']['company']['phone']
		=$_POST['wizard-company-telephone'];
	$_SESSION['wizard']['company']['fax']=$_POST['wizard-company-fax'];
	$_SESSION['wizard']['company']['email']=$_POST['wizard-company-email'];
	$_SESSION['wizard']['company']['vatno']
		=$_POST['wizard-company-vat-number'];
	$_SESSION['wizard']['company']['invoice']
		=$_POST['wizard-company-invoice'];
}

echo '
<h2>Products</h2>
<div style="height:300px;overflow:auto">
<table>
	<tr>
		<th>What type of products are you selling?</th>
		<td><select name="wizard-products-type">';

foreach($types as $type=>$template){
	echo '<option value="'.$template.'">'.$type.'</option>';
}

echo '</select></td>
	</tr>
</table>

<div id="preview-template">
<table>
	<tr>
			<th>Single View Template:</th>
			<td><button mode="single" class="preview-template-mode" id="default">
			Preview</button></td>
  </tr>
  <tr>
		<th>Multi View Template:</th>
		<td><button mode="multi" class="preview-template-mode" id="default">
		Preview</button></td>
	</tr>
</table>
</div></div>
';

echo '<input type="submit" value="Back" class="back-link"/>'
. '<input type="submit" value="Next" class="next-link" style="float:right"/>';
?>
