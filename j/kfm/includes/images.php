<?php
function _changeCaption($fid,$newCaption){
	$cwd_id=$GLOBALS['kfm_session']->get('cwd_id');
	$im=kfmImage::getInstance($fid);
	$im->setCaption($newCaption);
	return kfm_loadFiles($cwd_id);
}
function _getThumbnail($fileid,$width,$height){
	$im=kfmImage::getInstance($fileid);
	$im->setThumbnail($width,$height); // Already done in the Image constructor, maybe needed for Thumbnails with different sizes.
	return array($fileid,array('icon'=>$im->thumb_url,'width'=>$im->width,'height'=>$im->height,'caption'=>$im->caption));
}
function _resizeImage($fid,$width,$height){
	$cwd_id=$GLOBALS['kfm_session']->get('cwd_id');
	$im=kfmImage::getInstance($fid);
	$im->resize($width, $height);
	if($im->hasErrors())return $im->getErrors();
	return kfm_loadFiles($cwd_id);
}
function _resizeImages($fs,$width,$height){
	foreach($fs as $f)_resizeImage($f,$width,$height);
}
function _rotateImage($fid,$direction){
	$im=kfmImage::getInstance($fid);
	$im->rotate($direction);
	if($im->hasErrors())return $im->getErrors();
	return $fid;
}
function _setCaption($fid,$caption){
	
}
function _cropToOriginal($fid, $x1, $y1, $width, $height){
	$im=kfmImage::getInstance($fid);
	$im->crop($x1, $y1, $width, $height);
	if($im->hasErrors())return $im->getErrors();
	return $fid;
}
function _cropToNew($fid, $x1, $y1, $width, $height, $newname){
	$cwd_id=$GLOBALS['kfm_session']->get('cwd_id');
	$im=kfmImage::getInstance($fid);
	$im->crop($x1, $y1, $width, $height, $newname);
	if($im->hasErrors())return $im->getErrors();
	return kfm_loadFiles($cwd_id);
}
