<?php
/**
	* shows the awaiting moderation themes and allows them
	* to be downloaded and moderated
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Conor Mac Aoidh <conormacaoidh@gmail.com>
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

echo '<h2>'.__('Themes Awaiting Moderation').'</h2>';

/**
 * no themes in db
 */
if (count($themes)==0) {
	die(__('Themes database empty!'));
}

/**
 * add themes awaiting moderation to the $moderation array
 */
$moderation = array();
for ($i = 0; $i<count($themes); ++$i) {
	if ($themes[$i]['moderated']=='no') {
		array_push($moderation, $themes[$i]);
	}
}

if (count($moderation)==0) {
	die(__('No themes awaiting moderation!'));
}

/**
 * write javascript and add it to caching scheme
 */
$script='$(".delete").click(function(){var theme_id=$(this).attr("id");var '
	.'user_id=$(this).attr("userid");var hash=Math.floor(Math.random()*1001);'
	.'var dataString="theme_id="+theme_id+"&user_id="+user_id;var $this=$(thi'
	.'s);$.ajax({type:"POST",data:dataString,url:"/ww.plugins/themes-api/admi'
	.'n/delete-theme.php?hash="+hash,success:function(html){if(html=="ok")$th'
	.'is.parent().parent().fadeOut("slow");else alert("'
	.addslashes(__('There was an error deleting the file,please try again'))
	.'");}});});$(".approve").click(function(){var theme_id=$(this).attr("id"'
	.');var user_id=$(this).attr("userid");var hash=Math.floor(Math.random()*'
	.'1001);var dataString="theme_id="+theme_id+"&user_id="+user_id;var $this'
	.'=$(this);$.ajax({type:"POST",data:dataString,url:"/ww.plugins/themes-ap'
	.'i/admin/approve-theme.php?hash="+hash,success:function(html){if(html=="'
	.'ok")$this.parent().parent().fadeOut("slow");else alert("'
	.addslashes(__('There was an error approving the file. Please try again.'))
	.'");}});});';
WW_addInlineScript($script);

echo '<table><tr><th>'.__('Name').'</th><th>'.__('Version').'</th><th>'
	.__('Description').'</th><th>'.__('Download').'</th><th>'
	.__('Submit Date').'</th><th>'.__('Author').'</th><th>'.__('Approve')
	.'</th><th>'.__('Delete').'</th></tr>';

/**
 * print themes in table
 */
foreach ($moderation as $theme) {
	$author=dbOne(
		'select name from user_accounts where id='.$theme['author'],
		'name'
	);
	$d_name = $theme[ 'id' ] . '/' . $theme[ 'id' ] . '.zip';
	echo '<tr>'
		.'<td>' . $theme[ 'name' ] . '</td>'
		.'<td>' . $theme[ 'version' ] . '</td>'
		.'<td>' . substr($theme[ 'description' ], 0, 30) . '...</td>'
		.'<td><a href="/ww.plugins/themes-api/api.php?download=true&id=' 
		. $theme[ 'id' ] . '">' . $d_name . '</a></td>'
		.'<td>' . $theme[ 'last_updated' ] . '</td>'
		.'<td><a href="' . $theme[ 'author_url' ] . '">' . $author . '</a></td>'
		.'<td><a id="' . $theme[ 'id' ] . '" userid="' . $theme[ 'author' ] 
		. '" href="#" class="approve">[-]</a></td>'
		.'<td><a id="' . $theme[ 'id' ] . '" userid="' . $theme[ 'author' ] 
		. '" href="#" class="delete">[x]</a></td>';
}

echo '</table>';
