<?php
/**
  * News plugin definition file
  *
  * PHP Version 5
  *
  * @category   None
  * @package    None
  * @subpackage None
  * @author     Kae Verens <kae@kvsites.ie>
  * @author     Conol MacAoidh <conor@macaoidh.name>
  * @license    GPL Version 2
  * @link       www.kvweb.me
 */

// { config

$plugin=array(
	'name' => function() {
		return __('News');
	},
	'admin' => array(
		'page_type' => 'News_admin',
		'widget' => array(
			'form_url' => '/ww.plugins/news/admin/widget-form.php'
		)
	),
	'description' => function() {
		return __('Create news items from sub-pages.');
	},
	'frontend' => array(
		'page_type' => 'News_frontend',
		'widget' => 'News_showWidget'
	),
	'version'=>1
);

// }

// { News_admin

/**
  * show the news admin page
  *
  * @param array $page the page's db row
	* @param array $vars any meta data the page has
  *
  * @return string HTML of the news admin
  */
function News_admin($page, $vars) {
	require SCRIPTBASE.'ww.plugins/news/admin/display.php';
	return $c;
}

// }
// { News_frontend

/**
  * show the news widget
  *
  * @param array $PAGEDATA the page object
  *
  * @return string HTML of the news frontend
  */
function News_frontend($PAGEDATA) {
	require SCRIPTBASE.'ww.plugins/news/frontend/display.php';
	return $PAGEDATA->render()
		.$html
		.@$PAGEDATA->vars['footer'];
}

// }
// { News_showWidget

/**
  * show the news widget
  *
	* @param array $vars any meta data the page has
  *
  * @return string HTML of the widget
  */
function News_showWidget($vars) {
	require SCRIPTBASE.'ww.plugins/news/frontend/widget.php';
	return '<div class="news-wrapper">'.$html.'</div>';
}

// }
