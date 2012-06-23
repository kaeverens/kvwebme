<?php
/**
  * index.php: This is the main page for quizzes
  *
  * PHP Version 5
  *
  * It displays a menu of the first quizzes, and links for a New Quiz
  * and to the other quizzes
  *
  * @category   Quiz_Plugin
  * @package    Webworks_Webme
  * @subpackage Quiz
  * @author     Belinda Hamilton <bhamilton@webworks.ie>
  * @license    General Purpose License Version 2
  * @link       www.kvweb.me
*/
if (isset($_POST['action'])) {
	$errors=checkInput($_POST);
	if (empty($errors)) {
		unset ($_POST['errors']);
		$quizName=addslashes($_POST['name']);
		$quizTopic=addslashes($_POST['description']);
		$numberOfQuestions= (int)$_POST['number_of_questions'];
		$enabled = (int)$_POST['enabled'];
		if ($id) {
			dbQuery(
				"UPDATE quiz_quizzes 
				SET name = '$quizName',
				description = '$quizTopic',
				number_of_questions = '$numberOfQuestions',
				enabled = $enabled
				WHERE id = '$id'"
			);
		} 
		else {
			dbQuery(
				"INSERT INTO quiz_quizzes
				(
				name,
				description,
				number_of_questions,
				enabled
				)
				VALUES
				(
				'$quizName',
				'$quizTopic',
				'$numberOfQuestions',
				'$enabled'
				)"
			);
			$id= dbOne('SELECT LAST_INSERT_ID() AS id', 'id');
			header(
				'location: /ww.admin/plugin.php'
				.'?_plugin=quiz&_page=index&action=newQuestion&id='.$id
			);
		}
	}
}
echo '<script src="/ww.plugins/quiz/admin/quickDelete.js"></script>';
$menuItems= array ();
$quizzes
	= dbAll(
		"SELECT DISTINCT name, quiz_quizzes.id 
		FROM quiz_quizzes,quiz_questions"
	);
$pageQuizzes= dbAll("SELECT name,id from quiz_quizzes LIMIT 0,15");
foreach ($pageQuizzes as $quiz) {
	$menuItems[$quiz['name']]= $_url.'&amp;action=editQuiz&amp;id='.$quiz['id'];
}
$menuItems['New Quiz']= $_url.'&amp;action=newQuiz';
if (count($quizzes)>count($pageQuizzes)) {
		$menuItems['More Quizzes']= $_url;
}
echo Core_adminSideMenu(
	$menuItems
);
echo '<div class="pages_iframe">';
$dir = dirname(__FILE__);
switch ($action){
	case 'newQuestion':
	case 'editQuestion': // { If the user wants to add or edit a question
		if (isset($_GET['questionid'])) {
			$questionID= $_GET['questionid'];
		}
		require_once $dir.'/form.php';
	break; // }
	// }
	case 'newQuiz':
	case 'editQuiz': // { If the user wants to create or edit a quiz
		require_once $dir.'/form.php';
	break; // }
	case 'deleteQuiz': 
		// { What to do if the user wants to delete a quiz and confirms it
		if (!Core_isAdmin()) {
			die('Insufficent Privlages');
		}
		dbQuery("DELETE FROM quiz_quizzes WHERE id = '$id'");
		dbQuery("DELETE FROM quiz_questions WHERE quiz_id = '$id'");
		//Not breaking because I want the quizzes to display after a quiz is
		//deleted
		// }
	default: // { Display the quizzes
		// {The javascript to display the table
		echo '<script>';
		echo '$(document).ready(function(){';
		echo '$(\'#quizTable\').dataTable();';
		echo '});';
		echo '</script>';
		// }
		// { The quiz Table
		echo '<div id="quiz-table-wrapper" style="width:400px">';
		echo '<table id="quizTable" style="width:100%">';
		echo '<thead>';
		echo '<tr>';
		echo '<th>Name</th>';
		echo '<th>&nbsp;</th>';
		echo '<th>&nbsp</th>';
		echo '</tr></thead>';
		echo '<tbody>';
		foreach ($quizzes as $quiz) {
			echo '<tr>';
			echo '<td>'.htmlspecialchars($quiz['name']).'</td>';
			$quiz['id']=addslashes($quiz['id']);
			echo '<td><a href= "'.$_url.'&amp;action=editQuiz&amp;id='
				.$quiz['id'].'">edit</a></td>';
			echo '<td><a href="'.$_url.'&amp;action=deleteQuiz
				&amp;id='.$quiz['id'].'"'
				.' onclick="return confirm
					(\'are you sure you want to delete this?\');">
				[x]</a></td>';
			echo '</tr>';
		}
		echo '</tbody></table></div>';
		// }
	break; // }
}
echo '</div>';
/**
  * Validates the Add/Edit quiz form
  *
  * @param array $input The supplied values
  *
  * @return array $errors Any error messages
*/
function checkInput($input) {
	$errors= array();
	if (!isset($input['name'])) {
		$errors[]= 'You must give your quiz a name';
	}
	if (!isset($input['number_of_questions'])) {
		$errors[]= 'You must enter the number of questions you want to be asked';
	}
	else if (!is_numeric($input['number_of_questions'])) {
		$errors[]= 'The number of questions must be a number greater than 0';
	}
	else if ($input['number_of_questions']<=0) {
		$errors[]= 'The number of questions must be greater than 0';
	}
	return $errors;
}
