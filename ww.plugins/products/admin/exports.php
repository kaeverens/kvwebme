<?php
/**
  * Links to the page that does the work because doing the work here gives
  * strange results
  *
  * PHP Version 5
  *
  * @category   WebworksWebmeProductPlugin
  * @package    WebworksWebme
  * @subpackage Products_Plugin
  * @author     Belinda Hamilton <bhamilton@webworks.ie>
  * @license    GPL Version 2
  * @link       www.webworks.ie
 */
// { Move to a new location. When I try to use this file I get a strange table

// TODO: kae to check this. what's the point of this file?
$location = 'http://webworks-webme/ww.plugins/products/admin/save-file.php';
header('Location: '.$location);
// }
