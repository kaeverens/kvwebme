<?php
$plugin=array(
	'name' => 'A/B Testing',
	'admin' => array(
		'body_override' => 'ABTesting_admin'
	),
	'frontend' => array(
		'body_override' => 'ABTesting_frontend'
	),
	'description' => 'Provide different page bodies to readers, '
		.'to see which are more successful',
	'version'=>1
);

function ABTesting_admin($page, $page_vars) {
	if ($page === false) {
		require dirname(__FILE__).'/admin/body-rebuild.php';
	}
	else {
		require dirname(__FILE__).'/admin/body-override.php';
	}
	return $body;
}
function ABTesting_frontend($page) {
	require dirname(__FILE__).'/frontend.php';
	return $body;
}
