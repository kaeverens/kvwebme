<?php
/**
  * Deletes reviews
  *
  * Removes the review from the frontend using ajax
  *
  * PHP Version 5
  *
  * @category   Webworks_Webme
  * @package    Webworks_Webme
  * @subpackage Products_Plugin
  * @author     Belinda Hamilton <bhamilton@webworks.ie>
  * @license    GPL Version 2
  * @link       www.webworks.ie
*/
require_once $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
$id = (int)$_REQUEST['id'];
$productid= (int)$_REQUEST['productid'];
$userid
	= (int)dbOne(
		'select user_id 
		from products_reviews 
		where id='.$id, 
		'user_id'
	);
$user= get_userid();
if (!is_admin() || get_userid()!=$userid) {
	die('You do not have permission to delete this review');
}
dbQuery('delete from products_reviews where id='.$id);
if (dbOne('select id from products_reviews where id='.$id, 'id')) {
	echo '{"status":0}';
}
else {
	$numReviews
		= (int) dbOne(
			'select count(id) 
			from products_reviews 
			where product_id='.$productid, 
			'count(id)'
		);
	$average 
		= (int) dbOne(
			'select avg(rating)
			from products_reviews
			where product_id='.$productid
			.' group by product_id',
			'avg(rating)'
		);

	echo '{'
		.'"status":1'
		.', "id":'.$id
		.', "user":'.$user
		.', "userid":'.$userid
		.', "num":'.$numReviews
		.', "avg":'.$average
		.', "product":'.$productid
		.',}';
}
