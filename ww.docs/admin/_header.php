<!doctype html>
<html>
	<head>
		<link rel="stylesheet" href="../css/styles.css" />
	</head>
	<body>
		<div id="wrapper">
<?php
$cmsname=DistConfig::get('cms-name');
echo '<h1>'.__('%1 manual - Administration', array($cmsname), 'core').'</h1>';
