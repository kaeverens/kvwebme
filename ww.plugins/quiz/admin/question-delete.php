<?php
/**
  * Contains code to delete a question. It uses quickDelete.js to remove the row
  *
  * PHP Version 5
  *
  * @category   Quiz_Plugin
  * @package    Webworks_WebME
  * @subpackage QuizPlugin
  * @author     Belinda Hamilton <bhamilton@webworks.ie>
  * @license    This software is released under GPL V 2.0
  * @link       www.kvweb.me
*/
require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if (!Core_isAdmin()) {
	die('access denied');
}
if (!isset($_REQUEST['questionid']) || !is_numeric($_REQUEST['questionid'])) {
	Core_quit();
}
$questionID= $_REQUEST['questionid'];
dbQuery("DELETE FROM quiz_questions WHERE id = '$questionID'");
if (dbOne("SELECT id FROM quiz_questions where id = '$questionID'", 'id')) {
	echo '{"status":0}';
} else {
	echo '{"id":'.$questionID.',"status":1}';
}
