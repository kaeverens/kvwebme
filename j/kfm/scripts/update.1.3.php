<?php
$version=$GLOBALS['kfm_parameters']['version'];
if($version==''||$version=='7.0'||$version<'1.3')require KFM_BASE_PATH.'scripts/update.1.2.php';
$GLOBALS['kfm_parameters']['version']='1.3';
$GLOBALS['kfmdb']->query('update '.KFM_DB_PREFIX.'directories set name="root" where id=1');

$GLOBALS['kfmdb']->query('update '.KFM_DB_PREFIX.'parameters set value="'.$GLOBALS['kfm_parameters']['version'].'" where name="version"');
?>
