<?php

/**
 * admin/wizard/step1.php, KV-Webme Online Store Plugin
 *
 * store details
 *
 * @author     Conor Mac Aoidh <conormacaoidh@gmail.com>
 * @license    GPL 2.0
 * @version    1.0
 */

echo '
<h2>Online Store Wizard</h2>
<p><i>This wizard will guide you through the process of creating an online store
and populating it with products.</i></p>
<div style="height:300px;overflow:auto">
<table>
<tr>
	<th>Store Name</th>
	<td><input type="text" name="wizard-name" value="Products"/></td>
	<td><i>This is the name for the page that the products will be shown in</i></p>
</tr>
</table>
</div>
<input type="submit" value="Next" class="next-link" style="float:right"/>';
