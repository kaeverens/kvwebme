<?php
$dir=$_REQUEST['image_gallery_directory'];

echo '<form action="/j/kfm/upload.php" method="POST" '
	.'id="image-gallery-upload-image-form" enctype="multipart/form-data">'
	.'<input type="file" id="kfm_file" name="kfm_file[]" multiple="multiple" '
	.'onchange="document.getElementById(\'image-gallery-upload-image-form\').submit();">'
	.'<input type="hidden" id="MAX_FILE_SIZE" name="MAX_FILE_SIZE" '
	.'value="9999999999">'
	.'<input type="hidden" name="directory_name" '
	.'value="'.htmlspecialchars($dir).'" />'
	.'</form>';
