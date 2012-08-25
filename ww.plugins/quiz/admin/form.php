<?php
/**
  * Displays the form to create and edit quizzes
  * and add, edit and delete questions
  *
  * PHP Version 5.2.
  *
  * @category   Quiz_Plugin
  * @package    Webworks_WebME
  * @subpackage Quiz
  * @author     Belinda Hamilton <bhamilton@webworks.ie>
  * @license    This software is released under General Purpose License Version 2.0
  * @link       www.kvweb.me
*/
$dir = dirname(__FILE__);
$isInvalidInput = false;
$action=$_GET['action'];
echo '<script src="/ww.plugins/quiz/admin/quickDelete.js"></script>';
echo '<script>';
echo '$(function() {';
echo '$("#tabs").tabs({';
echo 'selected:';
if ($action=='newQuestion'||$action=='editQuestion') {
	echo 1;
} 
else {
	echo 0;
}
echo '}';
echo ');';
echo '});';
echo '$(function() {';
echo '$(".deleteLink").click(deleteItem);';
echo '});';
echo '</script>';
echo '<h3>';
if ($id) {
	echo 'Edit Quiz';
	$id= addslashes($id);
	$quiz= dbRow("SELECT * FROM quiz_quizzes WHERE quiz_quizzes.id='$id'");
} else {
 	echo 'New Quiz';
}
echo '</h3>';
// { The Form
echo '<form method="post">';
echo '<div id="tabs">';
echo '<ul>';
echo '<li><a href="#Overview">Overview</a></li>';
echo '<li><a href="#Questions">Questions</a></li>';
echo '</ul>';
// { Main Tab
echo '<div id="Overview">';
// { Quiz Name
echo 'Name   ';
echo '<input type="text" name="name"';
if (isset($_POST['name'])) {
	echo ' value="'.stripslashes(htmlspecialchars($_POST['name'])).'"';
} else if ($id) {
	echo ' value="';
	echo htmlspecialchars($quiz['name']);
	echo '"';
}
echo ' />';
echo '<br/>';
// }
// { Quiz Description
echo 'Description';
echo '<input type="text" name="description"';
if (isset($_POST['description'])) {
	echo ' value="'.stripslashes(htmlspecialchars($_POST['description'])).'"';
} 
else if ($id) {
	echo ' value="';
	echo htmlspecialchars($quiz['description']);
	echo '"';
}
echo '/>';
echo '<br/>';
// }
// { Number of Questions
echo 'Number of Questions to ask the User';
echo pad();
echo '<input type="text" name="number_of_questions" value="';
if (isset($_POST['number_of_questions'])) {
	echo $_POST['number_of_questions'];
} 
else if ($id) {
	echo $quiz['number_of_questions'];
} 
else {
	echo 1;
}
echo '"/>';
echo pad();
// }
// { Enabled?
echo 'Enable Quiz?';
$options= array ('No'=>0, 'Yes'=>1);
echo '<select name="enabled">';
foreach ($options as $displayed=>$val) {
	echo '<option value="'.$val.'"';
	if (isset ($_POST['enabled'])) {
		if ($val==$_POST['enabled']) {
			echo ' selected="selected"';
		}
	} 
	else if ($id) {
		if ($val==$quiz['enabled']) {
			echo ' selected="selected"';
		}
	}
	echo '>'.htmlspecialchars($displayed).'</option>';
}
echo '</select>';
// }
// { Errors
echo '<input type="hidden" name="errors[]"';
if (isset($_POST['errors'])) {
	$val=is_array($_POST)?join(', ', $_POST['errors']):$_POST['errors'];
	echo ' value="'.htmlspecialchars($val).'"';
}
echo '/>';
// }
// { ID
echo '<input type="hidden" name="id" value="'.$id.'"/>';
// }
// { Submit
if (!isset($_GET['questionid'])&&!(isset($_POST['add']))) {
	echo '<br/>';
	echo '<input type="submit" name="action" value="';
	if ($id) {
		echo 'Edit Quiz';
	} 
	else {
		echo 'Add Quiz';
	}
	echo '"/>';
}
// }
echo '</div>';
// }
// { Questions tab
echo '<div id="Questions">';
if ($id) {
	$results=dbAll("SELECT * FROM quiz_questions WHERE quiz_id='".$id."'");
	echo '<ul id="questionLinks">';
	foreach ($results as $result) {
		$questionID = $result['id'];
		echo '<li id="'.$questionID.'">'.$result['question'];
		echo '   <a href="'.$_url
					.'&amp;action=editQuestion&amp;questionid='
					.$questionID.'&amp;id='.$id.'">edit</a>';
		echo '   <a href="#" class="deleteLink" class="deleteLink">x</a></li>';
	}
		echo '</ul>';
	$questionID=null;
	if ($questionID||strcmp($action, 'newQuestion')!=0) {
		echo '<input type="button" 
			value="Add New Question" 
			name="add" 
			onClick="parent.location=
				\''.$_url.'\&amp;action=newQuestion&amp;id='.$id.'\'" 
			/>';
	}
} 
echo '</div>';
// }
// { When the Add/Edit Question form is submitted
if (isset($_POST['questionAction'])) {
	$id = $_POST['quiz_id'];
	$topic= $_POST['topic'];
	$question=addslashes($_POST['question']);
	$answers=$_POST['answers'];
	for ($i=0; $i<count($answers); $i++) {
		$answers[$i]=addslashes($answers[$i]);
	}
	$correctAnswer = $_POST['isCorrect'];
	if (empty($question)) {
		echo 'Please type a question';
		$_POST['questionErrors']=1;
	} 
	else if (empty($answers[0])||empty($answers[1])) {
		echo 'You need to provide at least two possible answers 
			in fields 1 and 2';
		$_POST['questionErrors']=1;
	} 
	else if (!checkCorrectAnswer($answers, $correctAnswer)) {
		echo 'One of the answers must be marked as correct';
		$_POST['questionErrors']=1;
	} 
	else {
		unset ($_POST['questionErrors']);
		if (isset($_GET['questionid'])) {
			$questionID=$_GET['questionid'];
			dbQuery(
				"UPDATE quiz_questions 
				SET 
				question = '$question', 
				topic = '$topic', 
				answer1 = '$answers[0]', 
				answer2 = '$answers[1]', 
				answer3 = '$answers[2]', 
				answer4 = '$answers[3]', 
				correctAnswer = '$correctAnswer' 
				WHERE id = '$questionID'"
			);
		} else {
			dbQuery(
				"INSERT INTO quiz_questions(
					quiz_id, 
					question, 
					topic, 
					answer1,
					answer2, 
					answer3, 
					answer4, 
					correctAnswer
				)
				VALUES(
					'$id', 
					'$question', 
					'$topic', 
					'$answers[0]',
					'$answers[1]', 
					'$answers[2]', 
					'$answers[3]', 
					'$correctAnswer'
				)"
			);
		}
	}
}
echo '</div>';//Ends the tabs div
if ((strcmp($action, 'editQuestion')==0)||(strcmp($action, 'newQuestion')==0)) {
	$addString= addQuestion();
	echo '<script>';
	echo '$("#Questions").append('.json_encode($addString).');';
	echo '</script>';
}
// }
echo '</form>';
// }

/**
  * This checks that a selection was made for the correct answer 
  * and that the selected answer is not null
  *
  * @param array $array         The answers
  * @param bool  $correctAnswer The index of the correct answer
  *
  * @return bool 
  * 	true if there is a non empty selected correct answer
  *     false otherwise
*/	
function checkCorrectAnswer ($array, $correctAnswer) {
	// { First check that a selection was made
	$selectionIsValid=true;
	if (($correctAnswer<0)||($correctAnswer>5)) {
		$selectionIsValid=false;
	} 
	// }
	// { Check that the selection is not empty
	else if (empty($array[$correctAnswer-1])) {
		$selectionIsValid=false;
	}
	// }
	return $selectionIsValid;
}

/**
  * This shows the form to add a new question
  *
  * @return string The form to add a question
*/
function addQuestion () {
	global $id;
	global $questionID;
	global $question;
	// { Question Tab
	$returnString='<h2>';
	if (isset($_GET['questionid'])) {
		$questionID= addslashes($_GET['questionid']);
		$returnString= $returnString.'Edit Question';
		$question= dbAll("SELECT * FROM quiz_questions WHERE id='$questionID'");
	} 
	else {
		$returnString= $returnString.'New Question';
	}
	$returnString= $returnString.'</h2>';
	$returnString= $returnString.'<input type="hidden" name="quiz_id" value="';
	$returnString= $returnString.htmlspecialchars($id).'"/>';
	if (isset($questionID)) {
		$returnString= $returnString.'<input type="hidden"';
		$returnString= $returnString.'name="question_id"';
		$returnString= $returnString.'value="';
		$returnString= $returnString.htmlspecialchars($questionID).'"';
		$returnString= $returnString.'"/>';
	}
	$returnString= $returnString.'<br/>';
	$returnString= $returnString.'Question';
	$returnString= $returnString.pad();
	$returnString= $returnString.'<input type="text" name="question"';
	if (isset($_POST['question'])&&isset($_POST['questionErrors'])) {
		$returnString= $returnString.'value="';
		$returnString= $returnString.htmlspecialchars($_POST['question']).'"';
	}
	if (isset($questionID)) {
		$returnString= $returnString.'value="';
		foreach ($question as $q) {
			$returnString= $returnString.htmlspecialchars($q['question']).'"';
		}
	}
	$returnString= $returnString.'/>';
	$returnString= $returnString.pad();
	$returnString= $returnString.'Topic';
	$returnString= $returnString.'<input type="text" name="topic"'; 
	if (isset($_POST['topic'])&&isset($_POST['questionErrors'])) {	
		$returnString= $returnString.'value="';
		$returnString= $returnString.htmlspecialchars($_POST['topic']).'"';
	}
	if (isset($questionID)) {
		$returnString= $returnString.'value="';
		foreach ($question as $q) {
			$returnString= $returnString.$q['topic'];
		}
		$returnString= $returnString.'"';
	}
	$returnString= $returnString.'/>';
	$returnString= $returnString.'</div>';
	// }
	// { Answers Tab
	$returnString= $returnString.'<h2>';
	if (isset($questionID)) {
		$returnString= $returnString.'Edit Answers';
	} else {
			$returnString= $returnString.'New Answers';
	}
	$returnString= $returnString.'</h2>';
	$returnString= $returnString.'<table>';
	$returnString= $returnString.'<thead>';
	$returnString= $returnString.'<tr>';
	$returnString= $returnString.'<th>';
	$returnString= $returnString.'Possible Answers';
	$returnString= $returnString.'</th>';
	$returnString= $returnString.'<th>';
	$returnString= $returnString.'Correct Answer';
	$returnString= $returnString.'</th>';
	$returnString= $returnString.'</tr>';
	$returnString= $returnString.'</thead>';
	$returnString= $returnString.'<tbody>';
	for ($i=0; $i<'4'; $i++) {
		$num=$i+1;
		$returnString= $returnString.'<tr>';
		$returnString= $returnString.addAnswer($num);
		$returnString= $returnString.'</tr>';
	}
	$returnString= $returnString.'</tbody>';
	$returnString= $returnString.'</table>';
	$returnString= $returnString.'</div>';
	// }
	$returnString= $returnString.'<input type="hidden" name="questionErrors">';
	$returnString= $returnString.'<input type="submit"';
	$returnString= $returnString.'name="questionAction"';
	$returnString= $returnString.'value="';
	if (isset($questionID)) {
		$returnString= $returnString.'Edit';
	} else {
		$returnString= $returnString.'Add';
	}
	$returnString= $returnString.' Question"/>';
	$returnString= $returnString.'</div>';//Ends the tabs div*/
	return $returnString;
}

/**
  * This function adds the answer fields to the form 
  * and the radio buttons to select the correct answer
  *
  * @param int $num The index of the answer
  *
  * @return string: Input fields and radio buttons for the answers to be entered
*/
function addAnswer($num) {
	global $questionID;
	global $question;
	$returnString= '<td>';
	$returnString= $returnString.'<input type="text" name="answers[]"';
	if (isset ($_POST['answers'])&&isset($_POST['questionErrors'])) {
		$answers = $_POST['answers'];
		$i = $num-1;
		if (!empty($answers[$i])) {
			$returnString= $returnString.' value="';
			$returnString= $returnString.htmlspecialchars($answers[$i]).'"';
		}
	}
	if ($questionID) {
		$key= 'answer'.$num;
		$returnString= $returnString.'value="';
		foreach ($question as $q) {
			$returnString= $returnString.$q[$key];
			$returnString= $returnString.'"';
		}
	}
	$returnString= $returnString.'/>';
	$returnString= $returnString.pad();
	$returnString= $returnString.'</td>';
	$returnString= $returnString.'<td>';
	$returnString= $returnString.'<input type="radio"';
	$returnString= $returnString.'name="isCorrect"'; 
	$returnString= $returnString.'value="'.$num.'"';
	if ($questionID) {
		foreach ($question as $q) {
			$correctAnswer=$q['correctAnswer'];
			if ($num==$correctAnswer) {
				$returnString= $returnString.'checked';
			}
		}
	}
	$returnString= $returnString.'/>';
	$returnString= $returnString.'</td>';
	return $returnString;
}

/**
  * A simple function to add spaces between elements in the form
  *
  * @return string three spaces
*/
function pad () {
	return '   ';
}
