<?php
/**
	* documentation menu
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

require_once '../ww.incs/basics.php';
?>
<!doctype html>
<html>
	<head>
		<style>@import "css/styles.css";</style>
	</head>
	<body>
		<ul id="left-nav">
			<li><strong>administrators</strong><ul>
				<li><a href="admin/upload-images.php"
					target="main-frame">upload images</a></li>
				<li><a href="admin/page-authentication.php"
					target="main-frame">page authentication</a></li>
				<li><strong>plugins</strong><ul>
<?php
foreach ($DBVARS['plugins'] as $n=>$p) {
	if (file_exists('../ww.plugins/'.$p.'/docs/admin.html')) {
		echo '\n<li><a href="plugin.php?t=admin&amp;p='
			.$p.'" target="main-frame">'.$p.'</a></li>';
	}
	else {
		echo "\n<!-- $p -->";
	}
}
?>
				</ul></li>
			</ul></li>
			<li><strong>designers</strong><ul>
				<li><a href="designer/creating-a-theme.php"
					target="main-frame">creating a theme</a></li>
				<li><a href="designer/template-codes.php"
					target="main-frame">template codes</a></li>
				<li><strong>plugins</strong><ul>
<?php
foreach ($DBVARS['plugins'] as $n=>$p) {
	if (file_exists('../ww.plugins/'.$p.'/docs/design.html')) {
		echo '\n<li><a href="plugin.php?t=design&amp;p='
			.$p.'" target="main-frame">'.$p.'</a></li>';
	}
	else {
		echo "\n<!-- $p -->";
	}
}
?>
			</ul></li>
		</ul>
	</body>
</html>
