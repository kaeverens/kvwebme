<?php
/**
	* check page to see if it needs to be translated, and do so if required
	*
	* PHP version 5
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     None
	*/
if (!isset($PAGEDATA->vars['translate_page_id'])
	|| !$PAGEDATA->vars['translate_page_id']
) {
	$PAGEDATA->body='<em>no page chosen to translate</em>';
}
else if (!isset($PAGEDATA->vars['translate_language'])
	|| strlen($PAGEDATA->vars['translate_language'])!=2
) {
	$PAGEDATA->body='<em>invalid language chosen</em>';
}
else {
	$fp=Page::getInstance($PAGEDATA->vars['translate_page_id']);
	if (!$fp) {
		$PAGEDATA->body='<em>page to translate was not found!</em>';
	}
	else {
		$edate=$PAGEDATA->edate;
		$fedate=$fp->edate;
		/* if original document has been updated, or translation page is empty,
		 * then update the translation
		 */
		if (strcmp($edate, $fedate)<0 || strlen($PAGEDATA->body)<10) {
			require_once 'google.translator.php';
			$text=$fp->body;
			$c = Google_Translate_API::translate(
				$text,
				$PAGEDATA->vars['translate_language_from'],
				$PAGEDATA->vars['translate_language']
			);
			if ($c === false) {
				$PAGEDATA->body='<em>translation failed! Original document is <a href="'
					.$fp->getRelativeUrl().'">here</a>. Failed to translate to "'
					.$PAGEDATA->vars['translate_language']
					.'" using Google Translate.</em>';
			}
			else{
				$PAGEDATA->body=$c;
				dbQuery(
					'update pages set body="'
					.addslashes($c).'",edate=now() where id='.$PAGEDATA->id
				);
				Core_cacheClear('pages');
			}
		}
	}
}
