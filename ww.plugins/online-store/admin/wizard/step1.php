<?php
/**
	* store details
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Conor Mac Aoidh <conormacaoidh@gmail.com>
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

require_once $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
echo '<h2>'.__('Online Store Wizard').'</h2>'
	.'<p><i>'.__(
		'This wizard will guide you through the process of creating an online'
		.' store and populating it with products.'
	)
	.'</i></p>'
	.'<div style="height:300px;overflow:auto"><table><tr><th>'
	.__('Store Name').'</th><td>'
	.'<input type="text" name="wizard-name" value="'.__('Products').'"/></td>'
	.'<td><i>'.__(
		'This is the name for the page that the products will be shown in.')
	.'</i></p>'
	.'</tr></table></div>'
	.'<input type="submit" value="'.__('Next').'" class="next-link"'
	.' style="float:right"/>';
