<?php

require 'header.php';

if(!$_SESSION['theme_selected']&&@$_GET['theme']!='skipped'){ // user shouldn't be here
  header('Location: /install/step6.php');
	exit;
}

echo '<p><strong>Success!</strong> Your WebME installation is complete. Please <a href="/">click here</a> to go to the root of the site.</p>';
unset($_SESSION['db_vars']);

require 'footer.php';

?>
