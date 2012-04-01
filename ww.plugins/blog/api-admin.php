<?php

function Blog_adminPostGet() {
	$id=(int)$_REQUEST['id'];
	return dbRow('select * from blog_entry where id='.$id);
}
