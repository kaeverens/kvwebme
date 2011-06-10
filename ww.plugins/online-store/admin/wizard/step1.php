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
<table>
<tr>
	<th>Store Name</th>
	<td><input type="text" name="wizard-name" value="My Store"/></td>
</tr>
</table>
<input type="submit" value="Begin" class="next-link" style="float:right"/>';

?>
