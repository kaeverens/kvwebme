<?php
/**
  * Blog plugin authors
  *
  * PHP Version 5
  *
  * @category   None
  * @package    None
  * @subpackage None
  * @author     Kae Verens <kae@kvsites.ie>
  * @license    GPL Version 2
  * @link       www.kvweb.me
 */

$c='<h1>List of Authors</h1>';

$rs=dbAll(
	'select user_id,count(blog_entry.id) as ids from blog_entry,user_accounts'
	.' where user_id=user_accounts.id and blog_entry.status>0 group by user_id'
	.' order by user_accounts.name'
);

$authors=array();
foreach ($rs as $r) {
	$user=User::getInstance($r['user_id']);
	if (!$user) {
		continue;
	}
	$a='<table><tr><td class="blog-author-image"></td><td><h2>';
	$hname=htmlspecialchars($user->get('name'));
	$a.=$hname.'</h2>';
	$a.='number of articles: '.$r['ids'].'<br/>';
	$a.='<a href="'.$PAGEDATA->getRelativeUrl().'/'.$r['user_id'].'">'
		.'read '.$hname.'\'s entries</a><br/>';
	$a.='</td></tr></table>';
	$authors[]=$a;
}

$c.='<div id="blog-authors">'.join('', $authors).'</div>';
