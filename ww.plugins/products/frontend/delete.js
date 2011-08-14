function delete_review(id, userid, productid) {
	if (confirm ('Are you sure you want to delete this review')) {
		$.post('/a/p=products/f=reviewDelete', {
			"id":id,
			"userid":userid,
			"productid":productid
		}, remove_review, "json");
	}
}
function remove_review (data) {
	if (!data.status) {
		return alert('Could not delete the review');
	}
	$('#form').remove();
	if (data.num>0) {
		var averageString= '<div id="avg">';
		averageString+= 'The average rating for this product over ';
		averageString+= data.num;
		averageString+= ' review';
		if (data.num!=1) {
			averageString+= 's';
		}
		averageString+= ' '+data.avg+'</div>';
		$(averageString).insertBefore('#average'+data.product);
	}
	else {
		noReviewsString= '<b>Nobody has reviewed this product yet</b>';
		$(noReviewsString).insertBefore('#average'+data.product);
	}
	$('#average'+data.product).remove();
	$('#avg').attr('id', 'average'+data.product);
	$('#'+data.id).remove();
	if ((data.userid==data.user)) {
		var form= '<form method="post" class="left" id="form"';
		form+= 'action="http://webworks-webme';
		form+= '/ww.plugins/products';
		form+= '/frontend/submit_review.php">';
		form+= '<input type="hidden" name="productid" ';
		form+= 'value="'+data.product+'"/>';
		form+= '<input type="hidden" name="userid" ';
		form+= 'value="'+ data.user+'" />';
		form+= '<b>Rating: </b>';
		form+= '<small><i>The higher the rating the better </i></small>';
		form+= '<select name="rating">';
		for (var i=1; i<=5; ++i) {
			form+= '<option>'+i+'</option>';
		}
		form+= '</select>';
		form+= '<textarea cols="50" rows="10" name="text">';
		form+= 'Put your comments about the product here';
		form+= '</textarea>';
		form+= '<div class="centre">';
		form+= '<input type="submit" name="submit"';
		form+= ' value="Submit Review" />';
		form+= '</div>';
		form+= '</form>';
		$(form).insertAfter('#reviews_display');
	}
}
