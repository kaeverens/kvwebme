<?php
/**
	* admin footer
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/
echo '</div></div>'.WW_getScripts().WW_getCSS()
	.'<!-- page generated in '.(microtime()-$webme_start_time).' seconds -->'
	.'</body></html>';
