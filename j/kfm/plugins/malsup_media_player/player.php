<?php
require_once('../../initialise.php');
if(!isset($_GET['ids']))die('error: no ids get parameter defined');
$ids=explode(',',$_GET['ids']);
header("content-type:text/html;charset=utf-8");
echo '<html><head>
				<script type="text/javascript" src="../../j/jquery/jquery-1.2.2.pack.js"></script>
				<script type="text/javascript" src="jquery.media.js"></script>
				<script type="text/javascript" src="swfobject.js"></script>
				<script type="text/javascript">$(function() { $("a.media").media({autoplay:true}); });
</script>
			</head><body>';
$size=($_GET['ifx'] && $_GET['ify'])?'w:'.((int)$_GET['ifx']-30).',h:'.((int)$_GET['ify']-60):'width:320,height:200';
foreach($ids as $id){
	$f=kfmFile::getInstance($id);
	if (!$f) continue;
	echo "<a class='media {autoplay:1,$size}' href='".$f->getUrl()."#.".$f->getExtension()."'>".htmlspecialchars($f->name)."</a>";
}
echo '</body></html>';
