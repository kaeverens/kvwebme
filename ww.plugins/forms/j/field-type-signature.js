$(function() {
	var opts={
		'canvas':'.signature-pad',
		'drawOnly':true,
		'defaultAction':'drawIt',
		'penWidth':2,
		'penColour':'#003',
		'output':'input',
		'clear':'.signature-clear',
		'lineTop':130
	};
	$('.signature-wrapper').signaturePad(opts);
});
