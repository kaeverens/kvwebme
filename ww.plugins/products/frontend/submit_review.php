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
  * @link       www.webworks.ie
*/
require_once $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
$dir= dirname(__FILE__);
$user_id = (int)$_POST['userid'];
$product_id = (int)$_POST['productid'];
$rating = (int)$_POST['rating'];
$text = addslashes($_POST['text']);
$errors= array();
if (empty($text)||$text=='Put your comments about the product here') {
	$errors[]= 'You need to type a comment';
}
if (($rating<1)||($rating>5)) {
	$errors[]= 'Rating must be between 1 and 5';
}
if (!empty($errors)) {
	echo '<script>';
	echo 'alert("There are errors in your form");';
	echo 'history.go(-1);';
	echo '</script>';
}
else {
	dbQuery(
		"insert into products_reviews
		(user_id, product_id, rating, body, cdate)
		values ('$user_id', '$product_id', '$rating', '$text', now())"
	);
	echo '<script>';
	echo 'alert(
			"Thank you for leaving a review for this product\n"
			+"You can edit your review for 15 minutes"
		);';
	echo 'history.go(-1)';
	echo '</script>';
}
