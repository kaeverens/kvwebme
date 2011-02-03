<?php
/**
  * Updates the review, calculates the new total and average
  *
  * PHP Version 5
  *
  * @category   WebworksWebme
  * @package    WebworksWebMe
  * @subpackage Products_Plugin
  * @author     Belinda Hamilton <bhamilton@webworks.ie>
  * @license    GPL Version 2
  * @link       www.webworks.ie
*/
require_once $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
$id= (int)$_REQUEST['id'];
$loggedInUser= get_userid();
$userWhoLeftReview
	= dbOne(
		'select user_id from products_reviews where id='.$id,
		'user_id'
	);
if (!(is_admin()||$loggedInUser==$userWhoLeftReview)) {
	die('You do not have sufficent privileges to edit this review');
}
$timeExpired 
	= dbOne(
		'select now()>
			date_add("'.$_REQUEST['cdate'].'", interval 15 minute) as can_edit',
		'can_edit'
	);
if ($timeExpired) {
	echo '{"status":0, "message":"time has expired"}';
}
else {
	$body = addslashes($_REQUEST['text']);
	$rating = (int)$_REQUEST['rating'];
	if (($rating<1||$rating>5)||$id<=0) {
		echo '{"status":0, "message":"Invalid Rating"}';
	}
	else {
		dbQuery(
			'update products_reviews set
				body=\''.$body.'\',
				rating='.$rating.'
				where id='.$id
		);
		$productid
			= dbOne(
				'select product_id 
				from products_reviews 
				where id='.$id,
				'product_id'
			);
		$average 
			= dbOne(
				'select avg(rating) 
				from products_reviews 
				where product_id='.$productid.
				' group by product_id',
				'avg(rating)'
			);
		$total
			= dbOne(
				'select count(id)
				from products_reviews
				where product_id='.$productid,
				'count(id)'
			);
		$review 
			= dbRow(
				'select rating,body,cdate  
				from products_reviews 
				where id = '.$id
			);
		$rating = $review['rating'];
		$body = $review['body'];
		$date = $review['cdate'];
		$name 
			= dbOne(
				'select name 
				from user_accounts 
				where id='.$userWhoLeftReview, 
				'name'
			);
		echo '{'
				.'"status":1, '
				.'"id":'.$id.', '
				.'"product":'.$productid.', '
				.'"user_id":'.$userWhoLeftReview.', '
				.'"user":"'.$name.'", '
				.'"date":"'.$date.'", '
				.'"rating":'.$rating.', '
				.'"body":'.json_encode($body).', '
				.'"avg":'.$average.', '
				.'"total":'.$total.', '
			.'}';
	}
}

