<?php
error_reporting(0);
if(file_exists('../.private/config.php')){
	echo '<p><strong>Config file already exists</strong>. Please remove the /install directory.</p>';
	exit;
}
session_start();
?>
<html>
	<head>
		<title>Webworks WebME installer</title>
	</head>
	<body>
