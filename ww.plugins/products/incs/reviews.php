<?php
/**
	* Products_reviews
	*
	* PHP version 5
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     None
	*/

WW_addScript('products/frontend/delete.js');
WW_addScript('products/frontend/products-edit-review.js');
$userid = (int)$_SESSION['userdata']['id'];
$product = $smarty->smarty->tpl_vars['product']->value;
$productid = (int)$product->id;
$c='';
$numReviews=dbOne(
	'select count(id) from products_reviews where product_id='.$productid,
	'count(id)'
);
if ($numReviews) {
	$reviews=dbAll(
		'select * from products_reviews where product_id='.$productid
	);
	$query = 'select avg(rating),product_id from products_reviews '
		.'where product_id='.$productid.' group by product_id';
	$average = dbOne($query, 'avg(rating)');
	$c.= '<div id="reviews_display">';
	$c.= '<div id="average'.$productid.'">';
	$c.=__(
		'The average rating for this product over %1 review(s) was %2',
		array(count($reviews), $average), 'core'
	);
	$c.='</div>';
	foreach ($reviews as $review) {
		$name=dbOne(
			'select name from user_accounts where id='.(int)$review['user_id'], 
			'name'
		);
		$c.= '<div id="'.$review['id'].'">';
		$date = $review['cdate'];
		$date = substr_replace($date, '', strpos($date, ' '));
		$c.=__('Posted by %1 on %2', array(htmlspecialchars($name), $date), 'core');
		$body = htmlspecialchars($body);
		$body = str_replace("\n", '<br />', $review['body']);
		$c.= '   ';
		$c.= '<b>'.__('Rated').': </b>'.$review['rating'].'<br/>';
		$c.= ($body).'<br/>';
		if (Core_isAdmin()|| $userid==$review['user_id']) {
			// { Edit Review Link
			$timeReviewMayBeEditedUntil=dbOne(
				'select date_add("'.$review['cdate'].'", interval 15 minute) '
				.'as last_edit_time',
				'last_edit_time'
			);
			$reviewMayBeEdited=dbOne(
				'select "'.$timeReviewMayBeEditedUntil.'">now() as can_edit_review',
				'can_edit_review'
			);
			if ($reviewMayBeEdited) {
				$c.='<a href="javascript:;" onClick="edit_review('.$review['id']
					.', \''.addslashes($body).'\', '.$review['rating'].', \''
					.addslashes($review['cdate']).'\');">'.__('Edit').'</a> ';
			}
			// }
			// { Delete Review Link
			$c.= '<a href="javascript:;" onClick="delete_review('
				.$review['id'].', '.$review['user_id'].', '.$productid
				.');">'.__('[x]').'</a><br/>';
			// }
		}
		$c.= '<br/></div>';
	}
	$c.= '</div>';
	$userHasNotReviewedThisProduct=!dbOne(
		'select id from products_reviews where user_id='.$userid
		.' and product_id='.$productid,
		'id'
	);
	if (isset($_SESSION['userdata']) && $userHasNotReviewedThisProduct) {
		$c.= Products_submitReviewForm($productid, $userid);
	}
}
else {
	$c.= '<em>'.__('Nobody has reviewed this product yet').'</em>';
	$c.= '<br/>';
	if (isset($_SESSION['userdata'])) {
		$c.= Products_submitReviewForm($productid, $userid);
	}
}
