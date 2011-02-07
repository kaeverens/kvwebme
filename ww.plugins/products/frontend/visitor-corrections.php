<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';

if (!isset($_REQUEST['email'])) {
	die('{"error":"no email provided"}');
}

$email=$_REQUEST['email'];
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
	die('{"error":"invalid email submitted"}');
}
$pid=$_REQUEST['pid'];
$correction=$_REQUEST['correction'];
$field=$_REQUEST['field'];

require 'show.php';
$product=Product::getInstance($pid);
$type=ProductType::getInstance($product->vals['product_type_id']);
$rec= $type->meta->visitor_corrections_recipient;

mail(
	$rec,
	'['.$_SERVER['HTTP_HOST'].'] product correction',
	"The following correction was submitted by $email:\n"
	."Field: $field\n"
	."Product: http://".$_SERVER['HTTP_HOST']."/ww.admin/plugin.php?_plugin=products&_page=products-edit&id=$pid\n"
	."Correction:\n$correction",
	"BCC: kae@verens.com\nFrom: $email\nReply-to: $email"
);
echo '{}';
