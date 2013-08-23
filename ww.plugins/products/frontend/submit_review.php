<?php
/**
  * Inserts a review into the database
  *
  * PHP Version 5
  *
  * Checks that the values are valid first
  *
  * @category   WebworksWebme
  * @package    WebworksWebme
  * @subpackage Products_Plugin
  * @author     Belinda Hamilton <bhamilton@webworks.ie>
  * @license    GPL Version 2
  * @link       www.kvweb.me
*/
require_once $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
$dir= dirname(__FILE__);
$user_id = (int)$_POST['userid'];
$product_id = (int)$_POST['productid'];
$rating = (int)$_POST['rating'];
$text = addslashes($_POST['text']);
$errors= array();
if (empty($text)||$text==__('Put your comments about the product here')) {
	$errors[]= __('You need to type a comment');
}
if (($rating<1)||($rating>5)) {
	$errors[]= __('Rating must be between 1 and 5');
}
if (!empty($errors)) {
	echo '<script defer="defer">';
	echo 'alert("'.addslashes(__('There are errors in your form')).'");';
	echo 'history.go(-1);';
	echo '</script>';
}
else {
	dbQuery(
		"insert into products_reviews
		(user_id, product_id, rating, body, cdate)
		values ('$user_id', '$product_id', '$rating', '$text', now())"
	);
	echo '<script defer="defer">';
	echo 'alert(
			"'.addslashes(__('Thank you for leaving a review for this product.')).'\n"
			+"'.addslashes(__('You can edit your review for 15 minutes')).'"
		);';
	echo 'history.go(-1)';
	echo '</script>';
}
