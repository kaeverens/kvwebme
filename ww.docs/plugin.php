<?php
require_once '../ww.incs/basics.php';
if (!isset($_REQUEST['t']) || !isset($_REQUEST['p'])) {
	echo __('You must include a plugin name and documentation type.');
	exit;
}
$p=$_REQUEST['p'];
$t=$_REQUEST['t'];
if (!in_array($p, $DBVARS['plugins'])) {
	echo __('You don\'t have that plugin installed.');
	exit;
}
switch ($t) {
	case 'admin':
		$title=__('Administraton').' - '.$p;
	break;
	case 'design':
		$title=__('Design').' - '.$p;
	break;
	default:
		echo __('No such documentation type.');
		exit;
}
if (!file_exists('../ww.plugins/'.$p.'/docs/'.$t.'.html')) {
	echo __('That documentation page does not exist.');
	exit;
}
?>
<!doctype html>
<html>
	<head>
		<style>@import "css/styles.css";</style>
	</head>
	<body>
		<a name="top"></a>
<?php
	echo '<h1>'.$title.'</h1>';
	echo file_get_contents('../ww.plugins/'.$p.'/docs/'.$t.'.html');
?>
	</body>
</html>
