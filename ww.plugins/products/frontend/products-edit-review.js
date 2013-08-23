function edit_review (id, text, rating, cdate) {
	text = text.replace(/<br ?\/>/g, "\n");
	var form = '<div style="text-align:left" id="form">'
	form += '<b>Rating: </b>';
	form += '<small><i>higher ratings are better </i></small>';
	form += '<select id="rating">';
	for (i=1; i<=5; i++) {
		form += '<option';
		if (i==rating) {
			form += ' selected="selected"';
		}
		form += '>'+i+'</option>';
	}
	form += '</select>';
	form += '<textarea cols="50" rows="10" id="text">';
	form += text;
	form += '</textarea>';
	form += '<input style="text-align:center" type="button" name="submit"';
	form += 'value="Save Review" onClick="submit_vals('+id+', \''+cdate+'\')"; />';
	form += '</div>';
	$(form).insertBefore('#'+id);
	$('#'+id).remove();
}
function submit_vals(id, cdate) {
	$.post('/a/p=products/f=reviewUpdate',
		{
			"id":id,
			"text":$('#text').val(),
			"rating":$('#rating').val(),
			"cdate":cdate
		},
		products_reviews_display,
		"json"
	);
}
function products_reviews_display(data) {
	if (!data.status) {
		return alert ('Could not edit this review because '+data.message);
	}
	var averageText = '<div id="avg">';
	averageText += 'The average rating for this product over ';
	averageText += data.total;
	averageText += ' review';
	if (data.total!=1) {
		averageText += 's';
	}
	averageText +=' was ' + data.avg;
	averageText +='<br /><br /></div>';
	$(averageText).insertBefore('#average'+data.product);
	var body = data.body;
	while (body.indexOf("\n")>=0) {
		body = body.replace("\n", '<br />');
	}
	var reviewText = '<div id="'+data.id+'">Posted by ' + data.user;
	reviewText += ' on ' + data.date;
	reviewText += ' <b>Rated: </b>' + data.rating;
	reviewText += '<br />' + body + '<br />';
	reviewText += '<a href="javascript:'
		+'edit_review('
			+data.id+', '
			+'\''+body+'\', '
			+data.rating+', '
			+'\''+data.date+'\''+
		')">edit</a> ';
	reviewText += '<a href="javascript:'
		+'delete_review('+data.id+','+data.user_id+' ,'+data.product+')">'
	reviewText = reviewText + '[x]</a>';
	reviewText = reviewText + '</div>';
	$(reviewText).insertAfter('#form');
	$('#form').remove();
	$('#average'+data.product).remove();
	$('#avg').attr('id', 'average'+data.product);
}
