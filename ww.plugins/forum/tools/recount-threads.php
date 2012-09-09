<?php
/**
  * recount the threads in each forum
  *
  * PHP Version 5
  *
  * @category   Whatever
  * @package    WebME
  * @subpackage Forum
  * @author     Kae Verens <kae@kvsites.ie>
  * @license    GPL Version 2
  * @link       www.kvweb.me
 */

require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if (!isset($_SESSION['userdata']) || !$_SESSION['userdata']['id']) {
	Core_quit();
}

dbQuery(
	'update forums_threads set num_posts='
	.'(select count(id) as ids from forums_posts '
	.'where thread_id=forums_threads.id)'
);
