<?php
/**
  * admin page for defining recommend-this-site pages
  *
  * PHP Version 5
  *
  * @category   Whatever
  * @package    WebworksWebme
  * @subpackage RecommendThisSite
  * @author     Kae Verens <kae@kvsites.ie>
  * @license    GPL Version 2
  * @link       www.kvweb.me
 */

// { tabs nav
$c.= '<div class="tabs">'
	.'<ul>'
	.'<li><a href="#f-header">Header</a></li>'
	.'<li><a href="#footer">Footer</a></li>'
	.'<li><a href="#email-to-friend">Friend\'s Email</a></li>'
	.'<li><a href="#email-to-sender">Sender\'s Email</a></li>'
	.'<li><a href="#email-to-admin">Admin\'s Email</a></li>'
	.'<li><a href="#success">Success Message</a></li>'
	.'</ul>';
// }
// { header
$c.='<div id="f-header"><p>Text to be shown above the form</p>'
	.ckeditor('body', $page['body'])
	.'</div>';
// }
// { footer
$c.='<div id="footer"><p>Text to appear below the form.</p>';
$c.=ckeditor(
	'page_vars[footer]',
	(isset($vars['footer'])?$vars['footer']:''),
	0,
	$cssurl
);
$c.='</div>';
// }
// { email to friend
$c.='<div id="email-to-friend"><p>Email to send to the friend.</p>';
if (!isset($vars['recommendthissite_emailtothefriend'])) {
	$vars['recommendthissite_emailtothefriend']='<p>Dear {{$friend_name}}</p>'
		.'<p>We have been asked by {{$sender_name}} to send you this email'
		.' recommending you visit "{{$smarty.server.HTTP_HOST}}" at:</p>'
		.'<a href="http://{{$smarty.server.HTTP_HOST}}">'
		.'{{$smarty.server.HTTP_HOST}}</a>'
		.'<p>Please take some time to look at our site and let us know'
		.' what you think.</p>'
		.'<p>Kind Regards</p><p>{{$smarty.server.HTTP_HOST}}</p>';
	$vars['recommendthissite_emailtothefriend_subject']
		='Website Recommendation From {{$sender_name}}';
}
$c.='<table><tr><th>Subject</th><td><input '
	.'name="page_vars[recommendthissite_emailtothefriend_subject]" value="'
	.htmlspecialchars($vars['recommendthissite_emailtothefriend_subject'])
	.'" /></td></tr>'
	.'<tr><td colspan="2">'.ckeditor(
		'page_vars[recommendthissite_emailtothefriend]',
		$vars['recommendthissite_emailtothefriend']
	).'</td></tr></table>';
$c.='</div>';
// }
// { email to sender
$c.='<div id="email-to-sender"><p>Email to send to the sender.</p>';
if (!isset($vars['recommendthissite_emailtosender'])) {
	$vars['recommendthissite_emailtosender']='<p>Dear {{$sender_name}}</p>'
		.'<p>Thank you for recommending our site to your friend. We really '
		.'appreciate it!</p>'
		.'<p>Kind Regards</p><p>{{$smarty.server.HTTP_HOST}}</p>';
	$vars['recommendthissite_emailtosender_subject']
		='Your recommendation of {{$smarty.server.HTTP_HOST}}';
}
$c.='<table><tr><th>Subject</th><td><input '
	.'name="page_vars[recommendthissite_emailtosender_subject]" value="'
	.htmlspecialchars($vars['recommendthissite_emailtosender_subject'])
	.'" /></td></tr>'
	.'<tr><td colspan="2">'.ckeditor(
		'page_vars[recommendthissite_emailtosender]',
		$vars['recommendthissite_emailtosender']
	).'</td></tr></table>';
$c.='</div>';
// }
// { email to admin
$c.='<div id="email-to-admin"><p>Email to send to the admin.</p>';
if (!isset($vars['recommendthissite_emailtoadmin'])) {
	$vars['recommendthissite_emailtoadmin']='<p>{{$sender_name}} '
		.'({{$sender_email}}) has recommended your site '
		.'{{$smarty.server.HTTP_HOST}} to {{$friend_name}} '
		.'({{$friend_email}})</p>';
	$vars['recommendthissite_emailtoadmin_subject']
		='[{{$smarty.server.HTTP_HOST}}] Website Recommendation';
	$vars['recommendthissite_emailtoadmin_email']='info@'
		.preg_replace('/^www\./', '', $_SERVER['HTTP_HOST']);
}
$c.='<table><tr><th>Admin Email</th><td><input '
	.'name="page_vars[recommendthissite_emailtoadmin_email]" value="'
	.htmlspecialchars($vars['recommendthissite_emailtoadmin_email'])
	.'" /></td></tr>'
	.'<tr><th>Subject</th><td><input '
	.'name="page_vars[recommendthissite_emailtoadmin_subject]" value="'
	.htmlspecialchars($vars['recommendthissite_emailtoadmin_subject'])
	.'" /></td></tr>'
	.'<tr><td colspan="2">'.ckeditor(
		'page_vars[recommendthissite_emailtoadmin]',
		$vars['recommendthissite_emailtoadmin']
	).'</td></tr></table>';
$c.='</div>';
// }
// { success message
$c.= '<div id="success">';
$c.= '<p>What should be displayed on-screen when the message is sent.</p>';
if (!isset($vars['recommendthissite_successmsg'])) {
	$vars['recommendthissite_successmsg']='<p>Thank you for your recommendation.'
		.'</p><p>We really appreciate it!</p>';
}
$c.=ckeditor(
	'page_vars[recommendthissite_successmsg]',
	$vars['recommendthissite_successmsg']
);
$c.= '</div>';
// }
echo '</div>';
