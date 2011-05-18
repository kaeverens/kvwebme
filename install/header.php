<?php
error_reporting(0);
if(file_exists('../.private/config.php')){
	echo '<p><strong>Config file already exists</strong>. Please remove the /install directory.</p>';
	exit;
}
session_start();

$home_dir = substr( dirname( __FILE__ ), 0, -7);
echo '
<!doctype html>
<html>
<head>
	<title>WebME admin area</title>

	<link rel="stylesheet" type="text/css" href="/j/cluetip/jquery.cluetip.css" />
	<link rel="stylesheet" href="/ww.admin/theme/admin.css" type="text/css" />
	<link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.13/themes/base/jquery-ui.css" />

	<!-- Installer specific javascript -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js"></script>
 	<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.13/jquery-ui.min.js"></script>
	<script src="/j/validate.jquery.min.js"></script>

	<script type="text/javascript">
        function error_handler( msg ){
                $( "#errors" ).html( msg );
        }
	$( document ).ready( function( ){
		// set the current page in install-menu
	        var link= window.location.href.split( "?" );
		link = link[ 0 ].split( "/" );
	        var path = link[ link.length - 1 ];
		$("#install-menu li a[href=\'"+path+"\']").addClass("current");
		$( "#howto" ).click( function( ){
			$( "#dialog" ).dialog( );
		} );
	} );
	</script>

	<!-- Installer specific CSS -->
	<style type="text/css">
		table{
			border-spacing: 6px;
		}
		table th{
			text-align: left;
		}
		#install-menu{
		        margin: 0;
		        padding: 0 0 20px;    
		}
		#install-menu li{ 
		        margin: 0;
		        padding: 0;
		}
		#install-menu li a{
		        border: 0 none;
		        display: block;
		        text-decoration: none;
		        padding: 3px 0 3px 5px;
		}
		#install-menu li a.current{
		        color: #d36042;
		} 
		#content{
			width: 70%;
			margin-left: 190px;
		}
		#errors{
			color:#D36042
		}
		.error{
			border:1px solid #600;
			background:#D36042
		}
	</style>

</head>
<body> 
	<div id="header"> 
	</div>

	<div id="wrapper">
		<div id="main">

		<h1>Webme Installer</h1>

		<div class="left-menu">
			<ul id="install-menu">
				<li><a href="index.php">Requirements</a></li>
				<li><a href="step1.php">Add Database</a></li>
				<li><a href="step3.php">Create User</a></li>
				<li><a href="step4.php">User FIles</a></li>
				<li><a href="step5.php">Finish</a></li>
			</ul>
		</div>

		<div id="pages-wrapper">

			<div id="content">
';
