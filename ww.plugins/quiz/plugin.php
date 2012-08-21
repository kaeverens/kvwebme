<?php
/**
  *  The plugin.php file for the quiz plugin. It describes the plugin.
  *
  *  PHP Version 5.2.6
  *
  *  @category   Quiz_Plugin
  *  @package    Webworks_WebME
  *  @subpackage QuizPlugin
  *  @author     Belinda Hamilton <bhamilton@webworks.ie>
  *  @license    This software is released under a General Purpose License V2
  *  @link       www.kvweb.me
*/
$plugin = array (
	'name' =>'Quizzes',
	'admin'=>array(
		'menu'=> array (
			'Misc>Quiz'=>'plugin.php?_plugin=quiz&amp;_page=index'
		),
		'page_type' => 'quiz'
	),
	'frontend' => array (
		'page_type' => 'Quiz_Display_page'
	),
	'description'=>'Create a quiz with this plugin',
	'version'=>4
);

/**
  *This is a stub function that call a function to generate the HTML for quizzes and 
  *allows the user to take one
  *
  *@return string representing the quizzes
  *@see    frontend/display.php::getPageHtml
  *
*/  
function Quiz_Display_page () {
	$dir= dirname(__FILE__);
	require_once $dir.'/frontend/display.php';
	return getPageHtml();
}
