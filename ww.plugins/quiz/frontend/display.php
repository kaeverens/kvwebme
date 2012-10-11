<?php
/**
  * Controls the frontend display of quizzes
  *
  * PHP version 5
  *
  * @category   Quiz_Plugin
  * @package    Webworks_WebMe
  * @subpackage QuizPlugin
  * @author     Belinda Hamilton <bhamilton@webworks.ie>
  * @license    This software is released under GPL version 2
  * @link       www.kvweb.me
*/
$dir= dirname(__FILE__);
require_once $dir.'/QuizSession.php';
	/**
	  * Displays a line with the quiz name and description 
	  * and a take quiz button
	  *
	  * @param string $name  The name of the quiz
	  * @param string $topic The quiz description
	  * @param int    $id    The id of the quiz
	  *
	  * @return string A row in the table of quizzes
*/
function displayQuizInfo ($name, $topic, $id) {
	$returnString = '<td>'.htmlspecialchars($name).'</td>';
	$returnString = $returnString.'<td>'.htmlspecialchars($topic).'</td>';
	$returnString = $returnString.'<td><button type="submit"'
		.'value="'.htmlspecialchars($id).'"'
		.'name="take">Take Quiz</button></td>';
	return $returnString;
}
/**
  * This is the main function. 
  * It figures out if a list of quizzes, questions
  * or results should be displayed and displays the correct thing
  *
  * @see displayQuizInfo
  * @see [QuizSession::]getScore()
  *
  * @return string $displayString The correct page HTML
*/
function getPageHtml () {
	WW_addScript('/j/jquery.dataTables-1.7.5/jquery.dataTables.min.js');
	WW_addCSS('/j/jquery.dataTables-1.7.5/jquery.dataTables.css');
	// { The Script
	$displayString='<script defer="defer">'
		.'$(function(){'
		.'$(\'#quizzesFrontend\').dataTable();'
		.'});'
		.'</script>';
	// }
	$quizzes
		= dbAll(
			"SELECT DISTINCT 
			quiz_quizzes.id, 
			name, 
			quiz_quizzes.description 
			FROM quiz_quizzes, quiz_questions 
			WHERE quiz_quizzes.id=quiz_questions.quiz_id 
			and quiz_quizzes.enabled=1"
		);
	$displayString= $displayString.'<form method="post">';
	$displayString= $displayString. '<table id="quizzesFrontend" 
										style="{width:100% postion:top}">';
	$displayString= $displayString.'<thead><tr>';
	$displayString= $displayString.'<th>Name</th>';
	$displayString= $displayString.'<th>Description</th>';
	$displayString= $displayString.'<th>&nbsp</th>';
	$displayString= $displayString.'</tr></thead>';
	$displayString= $displayString.'<tbody>';
	foreach ($quizzes as $quiz) {
		$quizId= $quiz['id'];
		$name = $quiz['name'];
		$topic= $quiz['description'];
		$id=$quiz['id'];
		$displayString= $displayString.'<tr>';
		$displayString= $displayString.displayQuizInfo($name, $topic, $id);
		$displayString= $displayString.'</tr>';
	}
	$displayString= $displayString.'</tbody></table>';
	$displayString= $displayString.'</form>';
	if (isset($_POST['take'])) {
		$id= $_POST['take'];
		$id= addSlashes($id);
		$quiz = new QuizSession($id);
		 $_SESSION['id']=$id;
		 $quiz->chooseQuestions();
		 $displayString = $quiz->getQuestionPageHtml();
	}
	if (isset($_POST['check'])) {
		$quiz = new QuizSession ($_SESSION['id']);
		 $displayString= $quiz->checkAnswers($_SESSION['questions'], $_POST);
	}
	return $displayString;
}
