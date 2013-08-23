<?php
/**
  * Translating language with Google API
  *
  * PHP Version 5
  *
  * @category   Whatever
  * @package    None
  * @subpackage None
  * @author     Gabe <gabe@fijiwebdesign.com>
  * @author     Kae Verens <kae@kvsites.ie>
  * @license    Share-Alike 3.0 (http://creativecommons.org/licenses/by-sa/3.0/)
  * @link       www.kvweb.me
  */

class Google_Translate_API{

	/**
	 * Translate a piece of text with the Google Translate API
	 *
	 * @param string $text Text to translate
	 * @param string $from Original language of $text.
	 * @param string $to   Language to translate $text to
	 *
	 * @return String
	 */
	function translate($text, $from = 'en', $to = 'en') {
		$url = 'http://ajax.googleapis.com/ajax/services/language/translate';
		$postdata = array(
			'v'=>'1.0',
			'q'=>$text,
			'langpair'=>$from.'|'.$to,
			'referer'=>'http://'.$_SERVER['HTTP_HOST'].'/'
		);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = str_replace(array("\n", "\r"), ' ', curl_exec($ch));
		$regexp="/{\"translatedText\":\"([^\"]+)\"/i";
		if (preg_match($regexp, $response, $matches)) {
			$r=json_decode($response);
			return $r->responseData->translatedText;
		}
		return false;
	}
}
